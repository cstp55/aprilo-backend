<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppCampaignRecipient;
use App\Models\MarketingCronLog;
use App\Models\MarketingLead;
use App\Models\User;
use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignRecipient;
use App\Services\AI\CampaignAiService;
use App\Services\WhatsAppCloudApi;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class WhatsAppCampaignController extends Controller
{
    /**
     * Self-heal runtime database schema for marketing leads, cron logs, and campaign metadata.
     */
    protected function ensureSchema(): void
    {
        try {
            // 1. Marketing Leads Table
            if (! Schema::hasTable('marketing_leads')) {
                Schema::create('marketing_leads', function (Blueprint $table) {
                    $table->uuid('id')->primary();
                    $table->foreignUuid('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
                    $table->string('name', 120);
                    $table->string('phone', 30)->nullable()->index();
                    $table->string('email', 150)->nullable()->index();
                    $table->string('company_name', 150)->nullable();
                    $table->string('status', 30)->default('lead')->index();
                    $table->string('target_product', 100)->nullable();
                    $table->string('preferred_channel', 20)->default('whatsapp');
                    $table->timestamp('last_contacted_at')->nullable();
                    $table->string('last_campaign_name', 150)->nullable();
                    $table->text('conversion_notes')->nullable();
                    $table->json('tags')->nullable();
                    $table->json('metadata')->nullable();
                    $table->timestamps();
                    $table->index(['status', 'target_product']);
                });
            }

            // 2. Marketing Cron Logs Table
            if (! Schema::hasTable('marketing_cron_logs')) {
                Schema::create('marketing_cron_logs', function (Blueprint $table) {
                    $table->uuid('id')->primary();
                    $table->string('command_name', 100)->default('aprilo:weekly-marketing-campaign');
                    $table->string('status', 30)->default('success')->index();
                    $table->string('target_product', 100)->nullable();
                    $table->string('segment_targeted', 50)->nullable();
                    $table->unsignedInteger('recipients_count')->default(0);
                    $table->unsignedInteger('accepted_count')->default(0);
                    $table->unsignedInteger('failed_count')->default(0);
                    $table->foreignUuid('campaign_id')->nullable()->constrained('whatsapp_campaigns')->nullOnDelete();
                    $table->text('ai_strategy_summary')->nullable();
                    $table->longText('ai_generated_copy')->nullable();
                    $table->text('error_message')->nullable();
                    $table->decimal('execution_time_seconds', 8, 2)->default(0.00);
                    $table->timestamp('executed_at')->nullable();
                    $table->timestamps();
                });
            }

            // 3. Extend whatsapp_campaigns
            if (Schema::hasTable('whatsapp_campaigns')) {
                Schema::table('whatsapp_campaigns', function (Blueprint $table) {
                    if (! Schema::hasColumn('whatsapp_campaigns', 'target_product')) {
                        $table->string('target_product', 100)->nullable();
                    }
                    if (! Schema::hasColumn('whatsapp_campaigns', 'audience_segment')) {
                        $table->string('audience_segment', 50)->default('all');
                    }
                    if (! Schema::hasColumn('whatsapp_campaigns', 'resend_count')) {
                        $table->unsignedInteger('resend_count')->default(0);
                    }
                    if (! Schema::hasColumn('whatsapp_campaigns', 'last_resent_at')) {
                        $table->timestamp('last_resent_at')->nullable();
                    }
                    if (! Schema::hasColumn('whatsapp_campaigns', 'parent_campaign_id')) {
                        $table->foreignUuid('parent_campaign_id')->nullable()->constrained('whatsapp_campaigns')->nullOnDelete();
                    }
                    if (! Schema::hasColumn('whatsapp_campaigns', 'ai_generated')) {
                        $table->boolean('ai_generated')->default(false);
                    }
                    if (! Schema::hasColumn('whatsapp_campaigns', 'ai_prompt_notes')) {
                        $table->text('ai_prompt_notes')->nullable();
                    }
                    if (! Schema::hasColumn('whatsapp_campaigns', 'is_automated')) {
                        $table->boolean('is_automated')->default(false);
                    }
                });
            }

            // 4. Extend whatsapp_campaign_recipients
            if (Schema::hasTable('whatsapp_campaign_recipients')) {
                Schema::table('whatsapp_campaign_recipients', function (Blueprint $table) {
                    if (! Schema::hasColumn('whatsapp_campaign_recipients', 'lead_id')) {
                        $table->foreignUuid('lead_id')->nullable()->constrained('marketing_leads')->nullOnDelete();
                    }
                    if (! Schema::hasColumn('whatsapp_campaign_recipients', 'resend_attempt')) {
                        $table->unsignedInteger('resend_attempt')->default(0);
                    }
                    if (! Schema::hasColumn('whatsapp_campaign_recipients', 'last_attempted_at')) {
                        $table->timestamp('last_attempted_at')->nullable();
                    }
                });
            }
        } catch (\Throwable $e) {
            Log::info('Schema self-healing note: ' . $e->getMessage());
        }
    }

    /**
     * Pre-populate demo leads if marketing_leads table is empty.
     */
    protected function syncDefaultLeadsIfEmpty(): void
    {
        try {
            if (MarketingLead::count() > 0) {
                return;
            }

            // Seed initial sample leads for Aprilo products
            MarketingLead::create([
                'name' => 'Amit Verma',
                'phone' => '919876543210',
                'email' => 'amit@vermatech.in',
                'company_name' => 'Verma Retail Solutions',
                'status' => 'lead',
                'target_product' => 'Aprilo POS',
                'conversion_notes' => 'Looking for barcode billing and inventory sync. Follow-up pending.',
            ]);

            MarketingLead::create([
                'name' => 'Pooja Sharma',
                'phone' => '919812345678',
                'email' => 'pooja@sharmaenterprises.com',
                'company_name' => 'Sharma Enterprises',
                'status' => 'converted',
                'target_product' => 'Aprilo Chatbot',
                'conversion_notes' => 'Active subscriber of Aprilo Chatbot. Highly interested in TradeFlow cross-sell.',
            ]);

            MarketingLead::create([
                'name' => 'Suresh Patel',
                'phone' => '919898989898',
                'email' => 'suresh@pateltraders.com',
                'company_name' => 'Patel Wholesale Hub',
                'status' => 'lead',
                'target_product' => 'TradeFlow',
                'conversion_notes' => 'Requested live demo of wholesale order tracking and batch inventory.',
            ]);

            MarketingLead::create([
                'name' => 'Vikram Malhotra',
                'phone' => '919765432109',
                'email' => 'vikram@malhotratextiles.com',
                'company_name' => 'Malhotra Textiles & Retail',
                'status' => 'converted',
                'target_product' => 'Aprilo POS',
                'conversion_notes' => 'Paid subscriber of Aprilo POS. Good candidate for Aprilo Agent AI assistant.',
            ]);
        } catch (\Throwable $e) {
            // Fail safely if database table is not accessible yet
            Log::info('Default lead seeding note: ' . $e->getMessage());
        }
    }

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

    public function index(Request $request, CampaignAiService $aiService): View
    {
        $this->authorizeSuperAdmin($request);
        $this->ensureSchema();
        $this->syncDefaultLeadsIfEmpty();

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
            ->with(['creator:id,name', 'parentCampaign:id,name'])
            ->withCount([
                'recipients',
                'recipients as accepted_count' => fn ($query) => $query->where('status', 'accepted'),
                'recipients as failed_count' => fn ($query) => $query->where('status', 'failed'),
            ])
            ->latest()
            ->paginate(10, ['*'], 'campaigns_page');

        $deliveries = WhatsAppCampaignRecipient::query()
            ->with('campaign:id,name,message_type,template_name,template_parameters,message_text,target_product')
            ->latest('created_at')
            ->paginate(20, ['*'], 'deliveries_page');

        // Marketing Leads & Customer conversion
        $leadsQuery = MarketingLead::query()->latest();
        if ($filterStatus = $request->query('lead_status')) {
            $leadsQuery->where('status', $filterStatus);
        }
        if ($searchLead = $request->query('lead_search')) {
            $leadsQuery->where(function ($q) use ($searchLead) {
                $q->where('name', 'like', "%{$searchLead}%")
                    ->orWhere('company_name', 'like', "%{$searchLead}%")
                    ->orWhere('phone', 'like', "%{$searchLead}%")
                    ->orWhere('email', 'like', "%{$searchLead}%");
            });
        }
        $leads = $leadsQuery->paginate(15, ['*'], 'leads_page');

        $leadStats = [
            'total' => MarketingLead::count(),
            'leads' => MarketingLead::where('status', 'lead')->count(),
            'converted' => MarketingLead::where('status', 'converted')->count(),
            'lost' => MarketingLead::where('status', 'lost')->count(),
        ];

        // Marketing Cron Logs
        $cronLogs = MarketingCronLog::query()
            ->with('campaign:id,name')
            ->latest('executed_at')
            ->paginate(8, ['*'], 'cron_page');

        $supportedProducts = $aiService->getAvailableProducts();

        return view('admin.super.whatsapp-campaigns', [
            'eligibleUsers' => $eligibleUsers,
            'campaigns' => $campaigns,
            'deliveries' => $deliveries,
            'leads' => $leads,
            'leadStats' => $leadStats,
            'cronLogs' => $cronLogs,
            'supportedProducts' => $supportedProducts,
            'whatsappReady' => filled(config('services.whatsapp.phone_number_id')) && filled(config('services.whatsapp.access_token')),
        ]);
    }

    public function store(Request $request, WhatsAppCloudApi $whatsApp): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);
        $this->ensureSchema();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'target_product' => ['nullable', 'string', 'max:100'],
            'delivery_mode' => ['required', 'in:queued,instant'],
            'recipient_mode' => ['required', 'in:users,manual,leads,converted,all_leads'],
            'user_ids' => ['required_if:recipient_mode,users', 'array'],
            'user_ids.*' => ['nullable', 'uuid', 'distinct', 'exists:users,id'],
            'recipient_phone' => ['required_if:recipient_mode,manual', 'nullable', 'string', 'max:30'],
            'message_type' => ['required', 'in:template,text'],
            'template_name' => ['required_if:message_type,template', 'nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_]+$/'],
            'template_language' => ['required_if:message_type,template', 'nullable', 'string', 'max:20', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'template_parameters' => ['nullable', 'string', 'max:2000'],
            'message_text' => ['required_if:message_type,text', 'nullable', 'string', 'max:4096'],
            'ai_generated' => ['nullable', 'boolean'],
            'ai_prompt_notes' => ['nullable', 'string', 'max:1000'],
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

        $recipients = [];
        $audienceSegment = 'manual';

        if ($validated['recipient_mode'] === 'manual') {
            $phone = $this->normalizePhone($validated['recipient_phone']);
            if (! $phone) {
                throw ValidationException::withMessages([
                    'recipient_phone' => 'Enter a valid WhatsApp number with country code, such as +14155552671 or 919876543210.',
                ]);
            }

            $recipients = [[
                'user_id' => null,
                'lead_id' => null,
                'recipient_name' => null,
                'recipient_phone' => $phone,
            ]];
            $audienceSegment = 'manual';
        } elseif ($validated['recipient_mode'] === 'users') {
            $audienceSegment = 'users';
            $users = User::query()
                ->whereIn('id', $validated['user_ids'] ?? [])
                ->where('status', 'active')
                ->where(function ($query): void {
                    $query->whereNull('role')->orWhereNotIn('role', ['super_admin', 'hr_admin', 'ecommerce_admin']);
                })
                ->whereDoesntHave('roleModel', fn ($query) => $query->whereIn('slug', ['super_admin', 'hr_admin', 'ecommerce_admin']))
                ->get(['id', 'name', 'phone']);

            if ($users->isEmpty()) {
                throw ValidationException::withMessages([
                    'user_ids' => 'Select at least one active user.',
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
                    'lead_id' => null,
                    'recipient_name' => $user->name,
                    'recipient_phone' => $phone,
                ];
            })->all();
        } elseif (in_array($validated['recipient_mode'], ['leads', 'converted', 'all_leads'], true)) {
            $leadQuery = MarketingLead::query()->whereNotNull('phone')->where('phone', '!=', '');
            if ($validated['recipient_mode'] === 'leads') {
                $leadQuery->where('status', 'lead');
                $audienceSegment = 'unconverted_leads';
            } elseif ($validated['recipient_mode'] === 'converted') {
                $leadQuery->where('status', 'converted');
                $audienceSegment = 'converted_customers';
            } else {
                $audienceSegment = 'all_leads';
            }

            $marketingLeads = $leadQuery->get();
            if ($marketingLeads->isEmpty()) {
                throw ValidationException::withMessages([
                    'recipient_mode' => 'No contacts with valid phone numbers found in the selected audience segment.',
                ]);
            }

            $recipients = $marketingLeads->map(function (MarketingLead $lead): array {
                $phone = $this->normalizePhone($lead->phone);
                return [
                    'user_id' => null,
                    'lead_id' => $lead->id,
                    'recipient_name' => $lead->name,
                    'recipient_phone' => $phone,
                ];
            })->filter(fn ($r) => filled($r['recipient_phone']))->values()->all();

            if (empty($recipients)) {
                throw ValidationException::withMessages([
                    'recipient_mode' => 'None of the contacts in this segment had a valid WhatsApp phone number.',
                ]);
            }
        }

        $parameters = trim($validated['template_parameters'] ?? '') === ''
            ? []
            : preg_split('/\r\n|\r|\n/', trim($validated['template_parameters']));

        if ($validated['message_type'] === 'template' && count($parameters) !== $expectedParameterCount) {
            throw ValidationException::withMessages([
                'template_parameters' => "The selected template expects {$expectedParameterCount} body parameter(s).",
            ]);
        }

        $campaign = DB::transaction(function () use ($request, $validated, $parameters, $recipients, $audienceSegment): WhatsAppCampaign {
            $campaign = WhatsAppCampaign::create([
                'created_by' => $request->user()->id,
                'name' => $validated['name'],
                'target_product' => $validated['target_product'] ?? null,
                'audience_segment' => $audienceSegment,
                'message_type' => $validated['message_type'],
                'template_name' => $validated['message_type'] === 'template' ? $validated['template_name'] : null,
                'template_language' => $validated['message_type'] === 'template' ? $validated['template_language'] : null,
                'template_parameters' => $validated['message_type'] === 'template' ? $parameters : null,
                'message_text' => $validated['message_type'] === 'text' ? $validated['message_text'] : null,
                'consent_confirmed_at' => now(),
                'status' => 'queued',
                'ai_generated' => (bool) ($validated['ai_generated'] ?? false),
                'ai_prompt_notes' => $validated['ai_prompt_notes'] ?? null,
            ]);

            $campaign->recipients()->createMany($recipients);

            // Update leads last contacted info
            foreach ($recipients as $item) {
                if (! empty($item['lead_id'])) {
                    MarketingLead::where('id', $item['lead_id'])->update([
                        'last_contacted_at' => now(),
                        'last_campaign_name' => $campaign->name,
                    ]);
                }
            }

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

    /**
     * Resend an existing campaign (all recipients, failed recipients only, or unconverted leads).
     */
    public function resend(Request $request, WhatsAppCampaign $campaign, WhatsAppCloudApi $whatsApp): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);
        $this->ensureSchema();

        $validated = $request->validate([
            'resend_target' => ['required', 'in:failed_only,all,unconverted'],
            'custom_message' => ['nullable', 'string', 'max:4096'],
        ]);

        $query = $campaign->recipients();

        if ($validated['resend_target'] === 'failed_only') {
            $query->where('status', 'failed');
        } elseif ($validated['resend_target'] === 'unconverted') {
            $query->where(function ($q) {
                $q->whereHas('lead', fn ($lq) => $lq->where('status', 'lead'))
                    ->orWhereNull('lead_id');
            });
        }

        $targetRecipients = $query->get();

        if ($targetRecipients->isEmpty()) {
            return redirect()->route('admin.super.whatsapp-campaigns')
                ->with('error', "No recipients match the resend criteria '{$validated['resend_target']}'.");
        }

        // Clone/create a child resend campaign record for clean tracking
        $resendIndex = ($campaign->resend_count ?? 0) + 1;
        $campaign->increment('resend_count');
        $campaign->update(['last_resent_at' => now()]);

        $newCampaign = WhatsAppCampaign::create([
            'created_by' => $request->user()->id,
            'name' => "{$campaign->name} [Resend #{$resendIndex}]",
            'target_product' => $campaign->target_product,
            'audience_segment' => $campaign->audience_segment,
            'message_type' => $campaign->message_type,
            'template_name' => $campaign->template_name,
            'template_language' => $campaign->template_language,
            'template_parameters' => $campaign->template_parameters,
            'message_text' => filled($validated['custom_message']) ? $validated['custom_message'] : $campaign->message_text,
            'consent_confirmed_at' => now(),
            'status' => 'queued',
            'parent_campaign_id' => $campaign->id,
            'ai_generated' => $campaign->ai_generated,
            'ai_prompt_notes' => "Resend #{$resendIndex} of parent campaign: {$campaign->id}",
        ]);

        $newRecipientRecords = [];
        foreach ($targetRecipients as $oldRecipient) {
            $newRecipientRecords[] = [
                'campaign_id' => $newCampaign->id,
                'user_id' => $oldRecipient->user_id,
                'lead_id' => $oldRecipient->lead_id,
                'recipient_name' => $oldRecipient->recipient_name,
                'recipient_phone' => $oldRecipient->recipient_phone,
                'status' => 'pending',
                'resend_attempt' => ($oldRecipient->resend_attempt ?? 0) + 1,
                'last_attempted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $newCampaign->recipients()->createMany($newRecipientRecords);

        // Dispatch all new recipients into queue
        foreach ($newCampaign->recipients as $recipient) {
            SendWhatsAppCampaignRecipient::dispatch($recipient);
        }

        return redirect()->route('admin.super.whatsapp-campaigns')
            ->with('status', "Resend campaign '{$newCampaign->name}' successfully dispatched for {$targetRecipients->count()} recipient(s).");
    }

    /**
     * AI Campaign Copy Generator endpoint.
     */
    public function generateAiCopy(Request $request, CampaignAiService $aiService): JsonResponse
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:100'],
            'target_audience' => ['required', 'in:unconverted_leads,converted_customers,all'],
            'campaign_goal' => ['nullable', 'string', 'max:100'],
            'discount_code' => ['nullable', 'string', 'max:50'],
            'custom_notes' => ['nullable', 'string', 'max:1000'],
            'tone' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $copy = $aiService->generateCampaignCopy($validated);

            return response()->json([
                'status' => 'success',
                'data' => $copy,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate campaign copy: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new Marketing Lead / Customer.
     */
    public function storeLead(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);
        $this->ensureSchema();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'in:lead,converted,lost'],
            'target_product' => ['nullable', 'string', 'max:100'],
            'conversion_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        if (! $phone) {
            throw ValidationException::withMessages([
                'phone' => 'Please enter a valid phone number with country code (e.g. 919876543210).',
            ]);
        }

        MarketingLead::create([
            'name' => $validated['name'],
            'phone' => $phone,
            'email' => $validated['email'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'status' => $validated['status'],
            'target_product' => $validated['target_product'] ?? null,
            'conversion_notes' => $validated['conversion_notes'] ?? null,
        ]);

        return redirect()->route('admin.super.whatsapp-campaigns', ['tab' => 'leads'])
            ->with('status', "Lead '{$validated['name']}' saved successfully.");
    }

    /**
     * Update Lead conversion status (lead -> converted or converted -> lead).
     */
    public function updateLeadStatus(Request $request, MarketingLead $lead): RedirectResponse|JsonResponse
    {
        $this->authorizeSuperAdmin($request);
        $this->ensureSchema();

        $validated = $request->validate([
            'status' => ['required', 'in:lead,converted,lost'],
            'conversion_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $lead->update([
            'status' => $validated['status'],
            'conversion_notes' => $validated['conversion_notes'] ?? $lead->conversion_notes,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'lead_id' => $lead->id,
                'new_status' => $lead->status,
            ]);
        }

        return redirect()->route('admin.super.whatsapp-campaigns', ['tab' => 'leads'])
            ->with('status', "Updated conversion status for '{$lead->name}' to '{$lead->status}'.");
    }

    /**
     * Delete marketing lead.
     */
    public function destroyLead(Request $request, MarketingLead $lead): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);
        $name = $lead->name;
        $lead->delete();

        return redirect()->route('admin.super.whatsapp-campaigns', ['tab' => 'leads'])
            ->with('status', "Lead '{$name}' deleted.");
    }

    /**
     * On-demand trigger for automated weekly marketing campaign cron command.
     */
    public function triggerWeeklyCron(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);
        $this->ensureSchema();

        try {
            Artisan::call('aprilo:weekly-marketing-campaign');
            $output = Artisan::output();

            return redirect()->route('admin.super.whatsapp-campaigns', ['tab' => 'cron'])
                ->with('status', "Weekly Automated Marketing Campaign executed! Log generated. Summary: " . mb_substr($output, 0, 240));
        } catch (\Throwable $e) {
            return redirect()->route('admin.super.whatsapp-campaigns', ['tab' => 'cron'])
                ->with('error', "Cron execution failed: " . $e->getMessage());
        }
    }

    protected function normalizePhone(?string $phone): ?string
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
