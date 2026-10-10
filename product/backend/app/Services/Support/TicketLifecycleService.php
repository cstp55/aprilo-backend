<?php

namespace App\Services\Support;

use App\Enums\Support\TicketPriority;
use App\Enums\Support\TicketStatus;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketEvent;
use App\Models\User;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class TicketLifecycleService
{
    /**
     * Allowed status transitions matrix.
     */
    protected const ALLOWED_TRANSITIONS = [
        TicketStatus::New->value => [
            TicketStatus::Open->value,
            TicketStatus::InProgress->value,
            TicketStatus::PendingCustomer->value,
            TicketStatus::Closed->value,
        ],
        TicketStatus::Open->value => [
            TicketStatus::InProgress->value,
            TicketStatus::PendingCustomer->value,
            TicketStatus::PendingInternal->value,
            TicketStatus::Resolved->value,
            TicketStatus::Closed->value,
        ],
        TicketStatus::InProgress->value => [
            TicketStatus::PendingCustomer->value,
            TicketStatus::PendingInternal->value,
            TicketStatus::Resolved->value,
            TicketStatus::Closed->value,
        ],
        TicketStatus::PendingCustomer->value => [
            TicketStatus::Open->value,
            TicketStatus::InProgress->value,
            TicketStatus::Resolved->value,
            TicketStatus::Closed->value,
        ],
        TicketStatus::PendingInternal->value => [
            TicketStatus::Open->value,
            TicketStatus::InProgress->value,
            TicketStatus::Resolved->value,
            TicketStatus::Closed->value,
        ],
        TicketStatus::Resolved->value => [
            TicketStatus::Open->value, // Reopen
            TicketStatus::Closed->value,
        ],
        TicketStatus::Closed->value => [
            TicketStatus::Open->value, // Reopen
        ],
    ];

    /**
     * Check if a transition is allowed.
     */
    public function canTransition(string $currentStatus, string $newStatus): bool
    {
        if ($currentStatus === $newStatus) {
            return true;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];

        return in_array($newStatus, $allowed, true);
    }

    /**
     * Get allowed next statuses for current status.
     */
    public function getAllowedTransitions(string $currentStatus): array
    {
        return self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];
    }

    /**
     * Perform status transition with lifecycle event recording and timestamp updates.
     */
    public function transitionStatus(
        SupportTicket $ticket,
        string $newStatus,
        ?User $actor = null,
        ?string $note = null
    ): SupportTicket {
        if (! in_array($newStatus, TicketStatus::values(), true)) {
            throw new InvalidArgumentException("Invalid ticket status: {$newStatus}");
        }

        $currentStatus = $ticket->status;

        if ($currentStatus === $newStatus) {
            return $ticket;
        }

        if (! $this->canTransition($currentStatus, $newStatus)) {
            throw new InvalidArgumentException(
                "Transition from '{$currentStatus}' to '{$newStatus}' is not allowed."
            );
        }

        return DB::transaction(function () use ($ticket, $currentStatus, $newStatus, $actor, $note) {
            $updates = ['status' => $newStatus];

            if ($newStatus === TicketStatus::Resolved->value) {
                $updates['resolved_at'] = now();
            } elseif ($newStatus === TicketStatus::Closed->value) {
                $updates['closed_at'] = now();
            } elseif (in_array($currentStatus, [TicketStatus::Resolved->value, TicketStatus::Closed->value], true)
                && $newStatus === TicketStatus::Open->value) {
                $updates['reopened_at'] = now();
                $updates['resolved_at'] = null;
                $updates['closed_at'] = null;
            }

            $ticket->update($updates);

            SupportTicketEvent::create([
                'ticket_id' => $ticket->id,
                'organization_id' => $ticket->organization_id,
                'actor_id' => $actor?->id,
                'actor_type' => $actor ? 'user' : 'system',
                'event_type' => 'status_changed',
                'previous_value' => ['status' => $currentStatus],
                'new_value' => ['status' => $newStatus],
                'note' => $note,
            ]);

            return $ticket->fresh();
        });
    }

    /**
     * Update priority with lifecycle event recording.
     */
    public function updatePriority(
        SupportTicket $ticket,
        string $newPriority,
        ?User $actor = null,
        ?string $note = null
    ): SupportTicket {
        if (! in_array($newPriority, TicketPriority::values(), true)) {
            throw new InvalidArgumentException("Invalid ticket priority: {$newPriority}");
        }

        $currentPriority = $ticket->priority;

        if ($currentPriority === $newPriority) {
            return $ticket;
        }

        return DB::transaction(function () use ($ticket, $currentPriority, $newPriority, $actor, $note) {
            $ticket->update(['priority' => $newPriority]);

            SupportTicketEvent::create([
                'ticket_id' => $ticket->id,
                'organization_id' => $ticket->organization_id,
                'actor_id' => $actor?->id,
                'actor_type' => $actor ? 'user' : 'system',
                'event_type' => 'priority_changed',
                'previous_value' => ['priority' => $currentPriority],
                'new_value' => ['priority' => $newPriority],
                'note' => $note,
            ]);

            return $ticket->fresh();
        });
    }

    /**
     * Mark first response timestamp if not already set.
     */
    public function recordFirstResponse(SupportTicket $ticket): void
    {
        if ($ticket->first_response_at === null) {
            $ticket->update(['first_response_at' => now()]);
        }
    }
}
