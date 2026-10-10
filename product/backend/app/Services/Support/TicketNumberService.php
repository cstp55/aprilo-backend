<?php

namespace App\Services\Support;

use App\Models\Organization;
use App\Models\Support\SupportTicket;
use Illuminate\Support\Facades\DB;

class TicketNumberService
{
    /**
     * Atomically generate next unique ticket number for the organization.
     * Format: TIC-1001, TIC-1002, etc.
     */
    public function generate(Organization $organization, string $prefix = 'TIC'): array
    {
        return DB::transaction(function () use ($organization, $prefix) {
            $latestSequence = SupportTicket::where('organization_id', $organization->id)
                ->lockForUpdate()
                ->max('ticket_sequence');

            $nextSequence = ($latestSequence ? (int) $latestSequence : 1000) + 1;
            $ticketNumber = sprintf('%s-%d', $prefix, $nextSequence);

            return [
                'ticket_number' => $ticketNumber,
                'ticket_sequence' => $nextSequence,
            ];
        });
    }
}
