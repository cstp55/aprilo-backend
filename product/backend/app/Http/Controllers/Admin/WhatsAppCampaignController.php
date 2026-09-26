<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppCampaignRecipient;
use App\Models\User;
use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignRecipient;
use App\Services\WhatsAppCloudApi;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class WhatsAppCampaignController extends Controller
{
    public function templates(Request $request, WhatsAppCloudApi $whatsApp): JsonResponse
    {
        $this->authorizeSuperAdmin($request);

        try {
            $templates = $this->approvedTemplates($whatsApp);
        } catch (ConnectionException|RuntimeException $exception) {
            $status = config('services.whatsapp.business_account_id') ? 502 : 503;

            return response()->json(['message' => $exception->getMessage()], $status);
        }

        return response()->json(['templates' => $templates]);
    }

    public function index(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        $eligibleUsers = User::query()
            ->where('status', 'active')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where(function ($query): void {
                $query->whereNull('role')->orWhereNotIn('role', ['super_admin', 'hr_admin', 'ecommerce_admin']);
            })
            ->whereDoesntHave('roleModel', fn ($query) => $query->whereIn('slug', ['super_admin', 'hr_admin', 'ecommerce_admin']))
            ->orderBy('name')
            ->get(['id', 'name', 'phone'])
            ->filter(fn (User $user): bool => $this->normalizePhone($user->phone) !== null)
            ->values();

        $campaigns = WhatsAppCampaign::query()
            ->with('creator:id,name')
            ->withCount([
                'recipients',
                'recipients as accepted_count' => fn ($query) => $query->where('status', 'accepted'),
                'recipients as failed_count' => fn ($query) => $query->where('status', 'failed'),
            ])
            ->latest()
            ->paginate(10, ['*'], 'campaigns_page');

        $deliveries = WhatsAppCampaignRecipient::query()
            ->with('campaign:id,name,message_type,template_name,template_parameters,message_text')
            ->latest('created_at')
            ->paginate(20, ['*'], 'deliveries_page');

        return view('admin.super.whatsapp-campaigns', [
            'eligibleUsers' => $eligibleUsers,
            'campaigns' => $campaigns,
            'deliveries' => $deliveries,
            'whatsappReady' => filled(config('services.whatsapp.phone_number_id')) && filled(config('services.whatsapp.access_token')),
        ]);
    }

    public function store(Request $request, WhatsAppCloudApi $whatsApp): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'delivery_mode' => ['required', 'in:queued,instant'],
            'recipient_mode' => ['required', 'in:users,manual'],
            'user_ids' => ['required_if:recipient_mode,users', 'array', 'min:1'],
            'user_ids.*' => ['required', 'uuid', 'distinct', 'exists:users,id'],
            'recipient_phone' => ['required_if:recipient_mode,manual', 'nullable', 'string', 'max:30'],
            'message_type' => ['required', 'in:template,text'],
            'template_name' => ['required_if:message_type,template', 'nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_]+$/'],
            'template_language' => ['required_if:message_type,template', 'nullable', 'string', 'max:20', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'template_parameters' => ['nullable', 'string', 'max:2000'],
            'message_text' => ['required_if:message_type,text', 'nullable', 'string', 'max:4096'],
            'consent_confirmed' => ['accepted'],
        ]);

        if ($validated['delivery_mode'] === 'instant' && $validated['recipient_mode'] !== 'manual') {
            throw ValidationException::withMessages([
                'delivery_mode' => 'Instant sends are available only for one manually entered WhatsApp number.',
            ]);
        }

        $expectedParameterCount = 0;
        if ($validated['message_type'] === 'template') {
            try {
                $templates = $this->approvedTemplates($whatsApp);
            } catch (ConnectionException|RuntimeException) {
                throw ValidationException::withMessages([
                    'template_name' => 'Could not verify templates with Meta. Reload the page and try again.',
                ]);
            }

            $selectedTemplate = collect($templates)->first(fn (array $template): bool =>
                $template['name'] === $validated['template_name'] &&
                $template['language'] === $validated['template_language']
            );

            if (! $selectedTemplate) {
                throw ValidationException::withMessages([
                    'template_name' => 'Select an approved template from the Meta template list.',
                ]);
            }

            $expectedParameterCount = $selectedTemplate['parameter_count'];
        }

        if ($validated['recipient_mode'] === 'manual') {
            $phone = $this->normalizePhone($validated['recipient_phone']);
            if (! $phone) {
                throw ValidationException::withMessages([
                    'recipient_phone' => 'Enter a valid WhatsApp number with country code, such as +14155552671.',
                ]);
            }

            $recipients = [[
                'user_id' => null,
                'recipient_name' => null,
                'recipient_phone' => $phone,
            ]];
        } else {
            $users = User::query()
                ->whereIn('id', $validated['user_ids'])
                ->where('status', 'active')
                ->where(function ($query): void {
                    $query->whereNull('role')->orWhereNotIn('role', ['super_admin', 'hr_admin', 'ecommerce_admin']);
                })
                ->whereDoesntHave('roleModel', fn ($query) => $query->whereIn('slug', ['super_admin', 'hr_admin', 'ecommerce_admin']))
                ->get(['id', 'name', 'phone']);

            if ($users->count() !== count($validated['user_ids'])) {
                throw ValidationException::withMessages([
                    'user_ids' => 'Only active non-admin users with a valid phone number can be selected.',
                ]);
            }

            $recipients = $users->map(function (User $user): array {
                $phone = $this->normalizePhone($user->phone);
                if (! $phone) {
                    throw ValidationException::withMessages([
                        'user_ids' => "{$user->name} does not have a valid WhatsApp number.",
                    ]);
                }

                return [
                    'user_id' => $user->id,
                    'recipient_name' => $user->name,
                    'recipient_phone' => $phone,
                ];
            })->all();
        }

        $parameters = trim($validated['template_parameters'] ?? '') === ''
            ? []
            : preg_split('/\r\n|\r|\n/', trim($validated['template_parameters']));

        if ($validated['message_type'] === 'template' && count($parameters) !== $expectedParameterCount) {
            throw ValidationException::withMessages([
                'template_parameters' => "The selected template expects {$expectedParameterCount} body parameter(s).",
            ]);
        }

        $campaign = DB::transaction(function () use ($request, $validated, $parameters, $recipients): WhatsAppCampaign {
            $campaign = WhatsAppCampaign::create([
                'created_by' => $request->user()->id,
                'name' => $validated['name'],
                'message_type' => $validated['message_type'],
                'template_name' => $validated['message_type'] === 'template' ? $validated['template_name'] : null,
                'template_language' => $validated['message_type'] === 'template' ? $validated['template_language'] : null,
                'template_parameters' => $validated['message_type'] === 'template' ? $parameters : null,
                'message_text' => $validated['message_type'] === 'text' ? $validated['message_text'] : null,
                'consent_confirmed_at' => now(),
                'status' => 'queued',
            ]);

            $campaign->recipients()->createMany($recipients);

            return $campaign;
        });

        foreach ($campaign->recipients as $recipient) {
            if ($validated['delivery_mode'] === 'instant') {
                try {
                    $result = $whatsApp->send($campaign, $recipient);
                    $recipient->update([
                        'status' => 'accepted',
                        'whatsapp_message_id' => data_get($result, 'messages.0.id'),
                        'response_json' => $result,
                        'processed_at' => now(),
                    ]);
                    $campaign->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                } catch (ConnectionException|RuntimeException $exception) {
                    $recipient->update([
                        'status' => 'failed',
                        'error_message' => mb_substr($exception->getMessage(), 0, 2000),
                        'processed_at' => now(),
                    ]);
                    $campaign->update([
                        'status' => 'failed',
                        'completed_at' => now(),
                    ]);

                    return redirect()->route('admin.super.whatsapp-campaigns')
                        ->with('error', "Instant send failed: {$exception->getMessage()}");
                }
            } else {
                SendWhatsAppCampaignRecipient::dispatch($recipient);
            }
        }

        $message = $validated['delivery_mode'] === 'instant'
            ? "WhatsApp accepted the instant message for {$campaign->recipients()->first()->recipient_phone}."
            : "Campaign '{$campaign->name}' queued for {$campaign->recipients()->count()} recipient(s).";

        return redirect()->route('admin.super.whatsapp-campaigns')->with('status', $message);
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '');

        return preg_match('/^[1-9][0-9]{7,14}$/', $digits) ? $digits : null;
    }

    private function approvedTemplates(WhatsAppCloudApi $whatsApp): array
    {
        $businessAccountId = config('services.whatsapp.business_account_id');
        if (! $businessAccountId) {
            throw new RuntimeException('WhatsApp business account ID is not configured.');
        }

        return Cache::remember(
            'whatsapp.approved_templates.' . hash('sha256', $businessAccountId),
            now()->addMinutes(5),
            fn (): array => $whatsApp->approvedTemplates()
        );
    }

    private function authorizeSuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Super Administrator access is required.');
    }
}