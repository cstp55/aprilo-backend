<?php

namespace App\Services\Support;

use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TicketAssignmentService
{
    /**
     * Assign ticket to a support agent.
     */
    public function assignToUser(
        SupportTicket $ticket,
        ?User $newAssignee,
        ?User $actor = null,
        ?string $note = null
    ): SupportTicket {
        if ($newAssignee && $newAssignee->organization_id !== $ticket->organization_id) {
            throw new InvalidArgumentException('Assignee does not belong to the ticket organization.');
        }

        $previousAssigneeId = $ticket->assigned_to;
        $newAssigneeId = $newAssignee?->id;

        if ($previousAssigneeId === $newAssigneeId) {
            return $ticket;
        }

        return DB::transaction(function () use ($ticket, $previousAssigneeId, $newAssigneeId, $actor, $note) {
            $ticket->update(['assigned_to' => $newAssigneeId]);

            // If ticket was 'new', automatically set to 'open' upon assignment
            if ($ticket->status === 'new' && $newAssigneeId !== null) {
                $ticket->update(['status' => 'open']);
            }

            SupportTicketEvent::create([
                'ticket_id' => $ticket->id,
                'organization_id' => $ticket->organization_id,
                'actor_id' => $actor?->id,
                'actor_type' => $actor ? 'user' : 'system',
                'event_type' => 'assigned',
                'previous_value' => ['assigned_to' => $previousAssigneeId],
                'new_value' => ['assigned_to' => $newAssigneeId],
                'note' => $note,
            ]);

            return $ticket->fresh();
        });
    }

    /**
     * Assign ticket to a department.
     */
    public function assignToDepartment(
        SupportTicket $ticket,
        ?string $department,
        ?User $actor = null,
        ?string $note = null
    ): SupportTicket {
        $previousDept = $ticket->department;

        if ($previousDept === $department) {
            return $ticket;
        }

        return DB::transaction(function () use ($ticket, $previousDept, $department, $actor, $note) {
            $ticket->update(['department' => $department]);

            SupportTicketEvent::create([
                'ticket_id' => $ticket->id,
                'organization_id' => $ticket->organization_id,
                'actor_id' => $actor?->id,
                'actor_type' => $actor ? 'user' : 'system',
                'event_type' => 'department_changed',
                'previous_value' => ['department' => $previousDept],
                'new_value' => ['department' => $department],
                'note' => $note,
            ]);

            return $ticket->fresh();
        });
    }
}
