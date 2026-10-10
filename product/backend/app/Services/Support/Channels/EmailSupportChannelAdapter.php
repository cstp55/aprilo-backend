<?php

namespace App\Services\Support\Channels;

use App\Models\Organization;
use App\Models\Support\SupportChannelEvent;
use App\Models\Support\SupportOutboxEvent;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketComment;
use App\Models\Support\SupportTicketEvent;
use App\Services\Support\Ai\SupportAiDraftService;
use App\Services\Support\SupportCustomerService;
use App\Services\Support\TicketNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailSupportChannelAdapter implements SupportChannelAdapterInterface
{
    public function __construct(
        private readonly SupportCustomerService $customerService,
        private readonly TicketNumberService $numberService,
        private readonly SupportAiDraftService $draftService,
    ) {
    }

    public function getChannel(): string
    {
        return 'email';
    }

    /**
     * Process verified inbound email.
     */
    public function processInbound(Organization $organization, array $payload): ?SupportTicketComment
    {
        $senderEmail = strtolower(trim($payload['sender'] ?? ($payload['from'] ?? '')));
        $senderName = trim($payload['sender_name'] ?? ($payload['from_name'] ?? ''));
        $subject = trim($payload['subject'] ?? 'Support Inquiry');
        $body = trim($payload['body'] ?? ($payload['text'] ?? ''));
        $messageId = trim($payload['message_id'] ?? ($payload['headers']['message-id'] ?? (string) Str::uuid()));
        $inReplyTo = trim($payload['in_reply_to'] ?? ($payload['headers']['in-reply-to'] ?? ''));
        $references = trim($payload['references'] ?? ($payload['headers']['references'] ?? ''));

        if (empty($senderEmail) || empty($body)) {
            return null;
        }

        return DB::transaction(function () use ($organization, $senderEmail, $senderName, $subject, $body, $messageId, $inReplyTo, $references, $payload) {
            // 1. Deduplication check
            $existing = SupportChannelEvent::where('organization_id', $organization->id)
                ->where('channel', 'email')
                ->where('event_id', $messageId)
                ->first();

            if ($existing) {
                return null; // Duplicate skipped
            }

            // 2. Resolve or create customer
            $customer = $this->customerService->findOrCreateCustomer(
                $organization,
                'email',
                $senderEmail,
                [
                    'name' => ! empty($senderName) ? $senderName : explode('@', $senderEmail)[0],
                    'email' => $senderEmail,
                ]
            );

            // 3. Match existing ticket by Threading Headers or Subject Ticket Number
            $ticket = null;

            // Priority A: In-Reply-To matching
            if (! empty($inReplyTo)) {
                $matchedComment = SupportTicketComment::where('organization_id', $organization->id)
                    ->where('external_message_id', $inReplyTo)
                    ->first();

                if ($matchedComment && $matchedComment->ticket && ! in_array($matchedComment->ticket->status, ['closed'], true)) {
                    $ticket = $matchedComment->ticket;
                }
            }

            // Priority B: Match ticket number in subject (e.g. TIC-1001)
            if (! $ticket && preg_match('/TIC-\d+/i', $subject, $matches)) {
                $ticketNum = strtoupper($matches[0]);
                $ticket = SupportTicket::where('organization_id', $organization->id)
                    ->where('ticket_number', $ticketNum)
                    ->whereNotIn('status', ['closed'])
                    ->first();
            }

            // Priority C: Create new ticket if no match
            if (! $ticket) {
                $seq = $this->numberService->generate($organization);
                $ticket = SupportTicket::create([
                    'organization_id' => $organization->id,
                    'ticket_number' => $seq['ticket_number'],
                    'ticket_sequence' => $seq['ticket_sequence'],
                    'customer_id' => $customer->id,
                    'subject' => $subject,
                    'description' => $body,
                    'channel' => 'email',
                    'priority' => 'normal',
                    'status' => 'new',
                    'metadata' => [
                        'initial_message_id' => $messageId,
                    ],
                ]);

                SupportTicketEvent::create([
                    'ticket_id' => $ticket->id,
                    'organization_id' => $organization->id,
                    'actor_type' => 'customer',
                    'event_type' => 'created',
                    'new_value' => [
                        'ticket_number' => $ticket->ticket_number,
                        'channel' => 'email',
                        'sender' => $senderEmail,
                    ],
                    'note' => 'Ticket created from inbound email',
                ]);
            }

            // 4. Create customer comment
            $comment = SupportTicketComment::create([
                'ticket_id' => $ticket->id,
                'organization_id' => $organization->id,
                'customer_id' => $customer->id,
                'author_type' => 'customer',
                'author_name' => $customer->name,
                'body' => $body,
                'is_internal' => false,
                'channel' => 'email',
                'external_message_id' => $messageId,
                'metadata' => [
                    'in_reply_to' => $inReplyTo,
                    'references' => $references,
                ],
            ]);

            // If ticket was pending_customer, transition back to open
            if ($ticket->status === 'pending_customer') {
                $ticket->update(['status' => 'open']);
            }

            // Mark any pending AI drafts as stale
            $this->draftService->markDraftsAsStale($ticket);

            // 5. Record channel event for deduplication
            SupportChannelEvent::create([
                'organization_id' => $organization->id,
                'channel' => 'email',
                'event_id' => $messageId,
                'event_type' => 'message_received',
                'ticket_id' => $ticket->id,
                'payload' => $payload,
                'processed_at' => now(),
            ]);

            return $comment;
        });
    }

    /**
     * Dispatch outbound agent reply to customer email.
     */
    public function dispatchOutbound(SupportTicketComment $comment): bool
    {
        // Safety invariant: Internal notes must NEVER be sent by email
        if ($comment->is_internal) {
            return false;
        }

        $ticket = $comment->ticket;
        $customer = $ticket?->customer;
        $toEmail = $customer?->email;

        if (! $ticket || ! $toEmail) {
            return false;
        }

        $subject = str_starts_with(strtolower($ticket->subject), 're:')
            ? "[{$ticket->ticket_number}] {$ticket->subject}"
            : "[{$ticket->ticket_number}] Re: {$ticket->subject}";

        $outbox = SupportOutboxEvent::create([
            'organization_id' => $ticket->organization_id,
            'ticket_id' => $ticket->id,
            'comment_id' => $comment->id,
            'channel' => 'email',
            'recipient' => $toEmail,
            'payload' => [
                'to' => $toEmail,
                'subject' => $subject,
                'body' => $comment->body,
                'in_reply_to' => $comment->external_message_id ?? ($ticket->metadata['initial_message_id'] ?? null),
            ],
            'status' => 'sent',
            'dispatched_at' => now(),
            'external_message_id' => '<' . Str::uuid() . '@aprilo.ai>',
        ]);

        return true;
    }
}
