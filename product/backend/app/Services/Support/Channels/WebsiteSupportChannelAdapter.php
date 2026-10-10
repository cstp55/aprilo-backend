<?php

namespace App\Services\Support\Channels;

use App\Models\Organization;
use App\Models\Support\SupportChannelEvent;
use App\Models\Support\SupportOutboxEvent;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketComment;
use App\Models\Support\SupportTicketEvent;
use App\Services\Firebase\FirebaseSyncService;
use App\Services\Support\Ai\SupportAiDraftService;
use App\Services\Support\SupportCustomerService;
use App\Services\Support\TicketNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WebsiteSupportChannelAdapter implements SupportChannelAdapterInterface
{
    public function __construct(
        private readonly SupportCustomerService $customerService,
        private readonly TicketNumberService $numberService,
        private readonly SupportAiDraftService $draftService,
        private readonly FirebaseSyncService $firebaseService,
    ) {
    }

    public function getChannel(): string
    {
        return 'website';
    }

    /**
     * Process inbound message from website chat widget.
     */
    public function processInbound(Organization $organization, array $payload): ?SupportTicketComment
    {
        $conversationId = $payload['conversation_id'] ?? null;
        $messageText = trim($payload['message'] ?? ($payload['content'] ?? ''));
        $externalEventId = $payload['event_id'] ?? ($payload['message_id'] ?? (string) Str::uuid());

        if (empty($messageText)) {
            return null;
        }

        return DB::transaction(function () use ($organization, $conversationId, $messageText, $externalEventId, $payload) {
            // 1. Deduplication check
            $existingEvent = SupportChannelEvent::where('organization_id', $organization->id)
                ->where('channel', 'website')
                ->where('event_id', $externalEventId)
                ->first();

            if ($existingEvent) {
                return null; // Already processed
            }

            // 2. Resolve Customer
            $visitorId = $payload['customer_id'] ?? ($payload['session_id'] ?? 'web_' . uniqid());
            $customerName = $payload['customer_name'] ?? 'Website Visitor';
            $customerEmail = $payload['customer_email'] ?? null;

            $customer = $this->customerService->findOrCreateCustomer(
                $organization,
                'website',
                $visitorId,
                [
                    'name' => $customerName,
                    'email' => $customerEmail,
                ]
            );

            // 3. Find active ticket linked to this conversation, or create one
            $ticket = null;
            if ($conversationId) {
                $ticket = SupportTicket::where('organization_id', $organization->id)
                    ->where('conversation_id', $conversationId)
                    ->whereNotIn('status', ['closed'])
                    ->latest()
                    ->first();
            }

            if (! $ticket) {
                $seq = $this->numberService->generate($organization);
                $ticket = SupportTicket::create([
                    'organization_id' => $organization->id,
                    'ticket_number' => $seq['ticket_number'],
                    'ticket_sequence' => $seq['ticket_sequence'],
                    'customer_id' => $customer->id,
                    'conversation_id' => $conversationId,
                    'subject' => Str::limit($messageText, 80, '...'),
                    'description' => $messageText,
                    'channel' => 'website',
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
                        'channel' => 'website',
                    ],
                    'note' => 'Ticket created from website widget conversation',
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
                'channel' => 'website',
                'external_message_id' => $externalEventId,
                'metadata' => [
                    'conversation_id' => $conversationId,
                    'page_url' => $payload['page_url'] ?? null,
                ],
            ]);

            // Proactively mark any pending drafts as stale when new customer message arrives
            $this->draftService->markDraftsAsStale($ticket);

            // 5. Record channel event for deduplication
            SupportChannelEvent::create([
                'organization_id' => $organization->id,
                'channel' => 'website',
                'event_id' => $externalEventId,
                'event_type' => 'message_received',
                'ticket_id' => $ticket->id,
                'payload' => $payload,
                'processed_at' => now(),
            ]);

            return $comment;
        });
    }

    /**
     * Dispatch outbound agent reply to website conversation / Firebase.
     */
    public function dispatchOutbound(SupportTicketComment $comment): bool
    {
        // Safety: Internal notes must NEVER be dispatched to website visitors
        if ($comment->is_internal) {
            return false;
        }

        $ticket = $comment->ticket;
        if (! $ticket) {
            return false;
        }

        return DB::transaction(function () use ($comment, $ticket) {
            SupportOutboxEvent::create([
                'organization_id' => $comment->organization_id,
                'ticket_id' => $ticket->id,
                'comment_id' => $comment->id,
                'channel' => 'website',
                'recipient' => $ticket->conversation_id ?: ($ticket->customer?->name ?? 'Website Visitor'),
                'payload' => [
                    'conversation_id' => $ticket->conversation_id,
                    'text' => $comment->body,
                    'author_name' => $comment->author_name,
                ],
                'status' => 'sent',
                'dispatched_at' => now(),
            ]);

            return true;
        });
    }
}
