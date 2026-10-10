<?php

namespace App\Services\Support\Ai;

use App\Models\Support\SupportAiDraft;
use App\Models\Support\SupportTicketComment;
use App\Models\Support\SupportTicketEvent;
use App\Models\User;
use App\Services\Support\TicketLifecycleService;
use App\Services\Support\Channels\EmailSupportChannelAdapter;
use App\Services\Support\Channels\WebsiteSupportChannelAdapter;
use App\Services\Support\Channels\WhatsAppSupportChannelAdapter;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class DraftApprovalService
{
    public function __construct(
        private readonly TicketLifecycleService $lifecycleService,
        private readonly WhatsAppSupportChannelAdapter $whatsAppAdapter,
        private readonly EmailSupportChannelAdapter $emailAdapter,
        private readonly WebsiteSupportChannelAdapter $websiteAdapter,
    ) {
    }

    /**
     * Explicit Human Review & Approval Action.
     * MANDATORY SAFETY INVARIANT: Only an authenticated, authorized human action can dispatch a draft.
     */
    public function approveAndDispatch(
        SupportAiDraft $draft,
        User $approver,
        ?string $editedContent = null,
        ?string $channel = null
    ): SupportTicketComment {
        // 1. Verify organization scope
        if ($draft->organization_id !== $approver->organization_id) {
            throw new InvalidArgumentException('Cross-tenant action forbidden.');
        }

        // 2. Perform atomic approval and customer dispatch within a database transaction
        return DB::transaction(function () use ($draft, $approver, $editedContent, $channel) {
            // Lock the draft row for concurrency safety (prevents duplicate dispatch race condition)
            $lockedDraft = SupportAiDraft::where('id', $draft->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedDraft->status !== 'pending_review') {
                throw new RuntimeException("Draft cannot be approved because status is '{$lockedDraft->status}'.");
            }

            $ticket = $lockedDraft->ticket;
            if (! $ticket || in_array($ticket->status, ['closed'], true)) {
                throw new RuntimeException('Cannot dispatch reply to a closed ticket.');
            }

            // Update edited content if supplied by reviewer
            if ($editedContent !== null) {
                $lockedDraft->edited_reply = trim($editedContent);
            }

            $finalContent = $lockedDraft->effective_reply;
            if (empty($finalContent)) {
                throw new InvalidArgumentException('Cannot dispatch an empty reply.');
            }

            $targetChannel = $channel ?: ($ticket->channel ?? 'portal');

            // 3. Dispatch customer-facing comment (publicly visible)
            $dispatchedComment = SupportTicketComment::create([
                'ticket_id' => $ticket->id,
                'organization_id' => $ticket->organization_id,
                'user_id' => $approver->id,
                'author_type' => 'agent',
                'author_name' => $approver->name,
                'body' => $finalContent,
                'is_internal' => false, // Strictly public / customer-visible
                'channel' => $targetChannel,
                'metadata' => [
                    'generated_by_ai' => true,
                    'approved_by_user_id' => $approver->id,
                    'ai_draft_id' => $lockedDraft->id,
                    'was_edited' => ! empty($lockedDraft->edited_reply),
                ],
            ]);

            // 4. Update Draft state to 'approved'
            $lockedDraft->update([
                'status' => 'approved',
                'reviewed_by' => $approver->id,
                'reviewed_at' => now(),
                'dispatched_at' => now(),
                'dispatched_comment_id' => $dispatchedComment->id,
                'dispatch_channel' => $targetChannel,
                'dispatch_status' => 'delivered',
            ]);

            // 5. Record immutable lifecycle audit event
            SupportTicketEvent::create([
                'ticket_id' => $ticket->id,
                'organization_id' => $ticket->organization_id,
                'actor_id' => $approver->id,
                'actor_type' => 'user',
                'event_type' => 'draft_approved_and_dispatched',
                'new_value' => [
                    'draft_id' => $lockedDraft->id,
                    'comment_id' => $dispatchedComment->id,
                    'was_edited' => ! empty($lockedDraft->edited_reply),
                    'channel' => $targetChannel,
                ],
                'note' => 'AI draft reviewed, approved, and dispatched by human agent.',
            ]);

            // 6. Update ticket lifecycle
            $this->lifecycleService->recordFirstResponse($ticket);

            if (in_array($ticket->status, ['new', 'open', 'in_progress'], true)) {
                $this->lifecycleService->transitionStatus(
                    $ticket,
                    'pending_customer',
                    $approver,
                    'Awaiting customer feedback following approved AI response.'
                );
            }

            // 7. Dispatch to external channel adapter if applicable
            if (! $dispatchedComment->is_internal) {
                if ($targetChannel === 'whatsapp' && $this->whatsAppAdapter) {
                    $this->whatsAppAdapter->dispatchOutbound($dispatchedComment);
                } elseif ($targetChannel === 'email' && $this->emailAdapter) {
                    $this->emailAdapter->dispatchOutbound($dispatchedComment);
                } elseif ($targetChannel === 'website' && $this->websiteAdapter) {
                    $this->websiteAdapter->dispatchOutbound($dispatchedComment);
                }
            }

            return $dispatchedComment;
        });
    }

    /**
     * Explicit Human Rejection of AI Draft.
     */
    public function rejectDraft(
        SupportAiDraft $draft,
        User $reviewer,
        ?string $rejectionReason = null
    ): SupportAiDraft {
        if ($draft->organization_id !== $reviewer->organization_id) {
            throw new InvalidArgumentException('Cross-tenant action forbidden.');
        }

        return DB::transaction(function () use ($draft, $reviewer, $rejectionReason) {
            $lockedDraft = SupportAiDraft::where('id', $draft->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedDraft->status !== 'pending_review') {
                throw new RuntimeException("Draft cannot be rejected because status is '{$lockedDraft->status}'.");
            }

            $lockedDraft->update([
                'status' => 'rejected',
                'rejection_reason' => $rejectionReason,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            SupportTicketEvent::create([
                'ticket_id' => $lockedDraft->ticket_id,
                'organization_id' => $lockedDraft->organization_id,
                'actor_id' => $reviewer->id,
                'actor_type' => 'user',
                'event_type' => 'draft_rejected',
                'new_value' => [
                    'draft_id' => $lockedDraft->id,
                    'rejection_reason' => $rejectionReason,
                ],
                'note' => 'AI draft rejected by human agent.',
            ]);

            return $lockedDraft->fresh();
        });
    }

    /**
     * Human edits the proposed reply prior to approval.
     */
    public function editDraft(
        SupportAiDraft $draft,
        User $editor,
        string $editedContent
    ): SupportAiDraft {
        if ($draft->organization_id !== $editor->organization_id) {
            throw new InvalidArgumentException('Cross-tenant action forbidden.');
        }

        if ($draft->status !== 'pending_review') {
            throw new RuntimeException('Only drafts pending review can be edited.');
        }

        $draft->update([
            'edited_reply' => trim($editedContent),
        ]);

        return $draft->fresh();
    }
}
