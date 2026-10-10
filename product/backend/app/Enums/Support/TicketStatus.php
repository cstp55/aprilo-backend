<?php

namespace App\Enums\Support;

enum TicketStatus: string
{
    case New = 'new';
    case Open = 'open';
    case InProgress = 'in_progress';
    case PendingCustomer = 'pending_customer';
    case PendingInternal = 'pending_internal';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::PendingCustomer => 'Pending Customer',
            self::PendingInternal => 'Pending Internal',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }
}
