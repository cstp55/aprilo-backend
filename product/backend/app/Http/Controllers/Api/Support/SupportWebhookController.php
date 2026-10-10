<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Services\Support\Channels\EmailSupportChannelAdapter;
use App\Services\Support\Channels\WebsiteSupportChannelAdapter;
use App\Services\Support\Channels\WhatsAppSupportChannelAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SupportWebhookController extends Controller
{
    public function __construct(
        private readonly WhatsAppSupportChannelAdapter $whatsAppAdapter,
        private readonly EmailSupportChannelAdapter $emailAdapter,
        private readonly WebsiteSupportChannelAdapter $websiteAdapter,
    ) {
    }

    /**
     * WhatsApp Webhook Verification Handshake (GET).
     */
    public function verifyWhatsApp(Request $request): Response
    {
        $mode = $request->query('hub_mode') ?: $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?: $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?: $request->query('hub.challenge');

        $expectedToken = config('services.whatsapp.verify_token', env('WHATSAPP_VERIFY_TOKEN', 'aprilo_support_token'));

        if ($mode === 'subscribe' && $token === $expectedToken) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /**
     * WhatsApp Inbound Webhook Listener (POST).
     * Enforces HMAC-SHA256 signature verification.
     */
    public function handleWhatsApp(Request $request): JsonResponse
    {
        $signature = $request->header('X-Hub-Signature-256');
        $rawPayload = $request->getContent();

        // 1. Signature Verification
        if (! $this->whatsAppAdapter->verifyWebhookSignature($rawPayload, $signature)) {
            Log::warning('SupportWebhookController: Invalid WhatsApp HMAC signature rejected.');
            return response()->json(['error' => 'Invalid signature.'], 401);
        }

        $payload = $request->json()->all();

        // 2. Resolve Organization from WhatsApp Phone Number ID
        $phoneNumberId = $payload['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'] ?? null;
        $organization = null;

        if ($phoneNumberId) {
            $setting = OrganizationSetting::where('whatsapp_phone_number_id', $phoneNumberId)->first();
            $organization = $setting?->organization;
        }

        if (! $organization) {
            $organization = Organization::where('status', 'active')->first();
        }

        if (! $organization) {
            return response()->json(['error' => 'No active organization found for WhatsApp receiver.'], 404);
        }

        // 3. Process Inbound
        $comment = $this->whatsAppAdapter->processInbound($organization, $payload);

        return response()->json([
            'success' => true,
            'processed' => $comment !== null,
            'ticket_id' => $comment?->ticket_id,
        ]);
    }

    /**
     * Inbound Email Webhook.
     */
    public function handleEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sender' => ['required', 'email'],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'subject' => ['required', 'string'],
            'body' => ['required', 'string'],
            'message_id' => ['nullable', 'string'],
            'in_reply_to' => ['nullable', 'string'],
            'references' => ['nullable', 'string'],
            'organization_id' => ['nullable', 'uuid'],
        ]);

        $orgId = $validated['organization_id'] ?? $request->header('X-Organization-Id');
        $organization = $orgId ? Organization::find($orgId) : Organization::where('status', 'active')->first();

        if (! $organization) {
            return response()->json(['error' => 'Organization not resolved.'], 404);
        }

        $comment = $this->emailAdapter->processInbound($organization, $validated);

        return response()->json([
            'success' => true,
            'processed' => $comment !== null,
            'ticket_id' => $comment?->ticket_id,
        ]);
    }

    /**
     * Inbound Website Message Webhook / Bridge.
     */
    public function handleWebsite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'widget_key' => ['required', 'string'],
            'conversation_id' => ['nullable', 'string'],
            'customer_id' => ['nullable', 'string'],
            'customer_name' => ['nullable', 'string'],
            'customer_email' => ['nullable', 'email'],
            'message' => ['required', 'string'],
        ]);

        $setting = OrganizationSetting::where('public_widget_key', $validated['widget_key'])->first();
        if (! $setting || ! $setting->organization) {
            return response()->json(['error' => 'Invalid widget key.'], 404);
        }

        $comment = $this->websiteAdapter->processInbound($setting->organization, $validated);

        return response()->json([
            'success' => true,
            'processed' => $comment !== null,
            'ticket_id' => $comment?->ticket_id,
        ]);
    }
}
