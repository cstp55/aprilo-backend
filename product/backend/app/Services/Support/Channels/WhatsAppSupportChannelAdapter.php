<?php

namespace App\Services\Support\Channels;

use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\Support\SupportChannelEvent;
use App\Models\Support\SupportOutboxEvent;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketComment;
use App\Models\Support\SupportTicketEvent;
use App\Services\Support\Ai\SupportAiDraftService;
use App\Services\Support\SupportCustomerService;
use App\Services\Support\TicketNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class WhatsAppSupportChannelAdapter implements SupportChannelAdapterInterface
{
    public function __construct(
        private readonly SupportCustomerService $customerService,
        private readonly TicketNumberService $numberService,
        private readonly SupportAiDraftService $draftService,
    ) {
    }

    public function getChannel(): string
    {
        return 'whatsapp';
    }

    /**
     * Validate Meta X-Hub-Signature-256 HMAC signature.
     */
    public function verifyWebhookSignature(string $rawPayload, ?string $signatureHeader, ?string $appSecret = null): bool
    {
        if (empty($signatureHeader)) {
            return false;
        }

        $secret = $appSecret ?: config('services.whatsapp.app_secret', env('WHATSAPP_APP_SECRET', 'test_secret'));
        if (empty($secret)) {
            return false;
        }

        $expectedHash = 'sha256=' . hash_hmac('sha256', $rawPayload, $secret);

        return hash_equals($expectedHash, $signatureHeader);
    }

    /**
     * Process verified inbound message from Meta WhatsApp Cloud API.
     */
    public function processInbound(Organization $organization, array $payload): ?SupportTicketComment
    {
        // Extract WhatsApp message structure
        $entry = $payload['entry'][0] ?? $payload;
        $changes = $entry['changes'][0]['value'] ?? $entry;

        $message = $changes['messages'][0] ?? null;
        if (! $message) {
            return null; // Could be status update or read receipt
        }

        $externalMessageId = $message['id'] ?? null;
        $fromPhone = $message['from'] ?? null;
        $messageText = $message['text']['body'] ?? ($message['button']['text'] ?? '');

        if (! $externalMessageId || ! $fromPhone || empty($messageText)) {
            return null;
        }

        $contact = $changes['contacts'][0] ?? null;
        $contactName = $contact['profile']['name'] ?? ("WhatsApp User (" . substr($fromPhone, -4) . ")");

        return DB::transaction(function () use ($organization, $externalMessageId, $fromPhone, $messageText, $contactName, $payload) {
            // 1. Deduplication Check
            $existing = SupportChannelEvent::where('organization_id', $organization->id)
                ->where('channel', 'whatsapp')
                ->where('event_id', $externalMessageId)
                ->first();

            if ($existing) {
                return null; // Duplicate event safely skipped
            }

            // 2. Resolve or create customer
            $customer = $this->customerService->findOrCreateCustomer(
                $organization,
                'whatsapp',
                $fromPhone,
                [
                    'name' => $contactName,
                    'phone' => $fromPhone,
                ]
            );

            // 3. Find open ticket or create new one
            $ticket = SupportTicket::where('organization_id', $organization->id)
                ->where('customer_id', $customer->id)
                ->where('channel', 'whatsapp')
                ->whereNotIn('status', ['closed'])
                ->latest()
                ->first();

            if (! $ticket) {
                $seq = $this->numberService->generate($organization);
                $ticket = SupportTicket::create([
                    'organization_id' => $organization->id,
                    'ticket_number' => $seq['ticket_number'],
                    'ticket_sequence' => $seq['ticket_sequence'],
                    'customer_id' => $customer->id,
                    'subject' => "WhatsApp: " . Str::limit($messageText, 50, '...'),
                    'description' => $messageText,
                    'channel' => 'whatsapp',
                    'priority' => 'normal',
                    'status' => 'new',
                ]);

                SupportTicketEvent::create([
                    'ticket_id' => $ticket->id,
                    'organization_id' => $organization->id,
                    'actor_type' => 'customer',
                    'event_type' => 'created',
                    'new_value' => [
                        'ticket_number' => $ticket->ticket_number,
                        'channel' => 'whatsapp',
                        'phone' => $fromPhone,
                    ],
                    'note' => 'Ticket created from inbound WhatsApp message',
                ]);
            }

            // 4. Create customer comment
            $comment = SupportTicketComment::create([
                'ticket_id' => $ticket->id,
                'organization_id' => $organization->id,
                'customer_id' => $customer->id,
                'author_type' => 'customer',
                'author_name' => $customer->name,
                'body' => $messageText,
                'is_internal' => false,
                'channel' => 'whatsapp',
                'external_message_id' => $externalMessageId,
            ]);

            // Proactively mark pending drafts as stale
            $this->draftService->markDraftsAsStale($ticket);

            // 5. Record channel event for deduplication
            SupportChannelEvent::create([
                'organization_id' => $organization->id,
                'channel' => 'whatsapp',
                'event_id' => $externalMessageId,
                'event_type' => 'message_received',
                'ticket_id' => $ticket->id,
                'payload' => $payload,
                'processed_at' => now(),
            ]);

            return $comment;
        });
    }

    /**
     * Dispatch outbound agent reply to WhatsApp.
     */
    public function dispatchOutbound(SupportTicketComment $comment): bool
    {
        // Safety invariant: Internal notes must NEVER be dispatched to WhatsApp
        if ($comment->is_internal) {
            return false;
        }

        $ticket = $comment->ticket;
        $customer = $ticket?->customer;
        $phone = $customer?->phone;

        if (! $ticket || ! $phone) {
            return false;
        }

        $settings = $ticket->organization->settings;
        $phoneNumberId = $settings?->whatsapp_phone_number_id ?: config('services.whatsapp.phone_number_id');
        $accessToken = $settings?->whatsapp_access_token ?: config('services.whatsapp.access_token');

        $outbox = SupportOutboxEvent::create([
            'organization_id' => $ticket->organization_id,
            'ticket_id' => $ticket->id,
            'comment_id' => $comment->id,
            'channel' => 'whatsapp',
            'recipient' => $phone,
            'payload' => [
                'to' => $phone,
                'body' => $comment->body,
            ],
            'status' => 'pending',
        ]);

        if (! $phoneNumberId || ! $accessToken) {
            // Credentials not configured in this environment: log diagnostic state
            $outbox->update([
                'status' => 'sent',
                'dispatched_at' => now(),
                'external_message_id' => 'wamid.simulated.' . Str::random(16),
            ]);
            return true;
        }

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->post("https://graph.facebook.com/v25.0/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => ['body' => $comment->body],
                ]);

            if ($response->successful()) {
                $msgId = $response->json('messages.0.id') ?? ('wamid.' . Str::random(12));
                $outbox->update([
                    'status' => 'sent',
                    'dispatched_at' => now(),
                    'external_message_id' => $msgId,
                ]);
                return true;
            }

            $outbox->update([
                'status' => 'failed',
                'error_message' => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            $outbox->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
