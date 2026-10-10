<?php

namespace App\Models\Support;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportOutboxEvent extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'ticket_id',
        'comment_id',
        'channel',
        'recipient',
        'payload',
        'status',
        'retry_count',
        'external_message_id',
        'error_message',
        'dispatched_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'retry_count' => 'integer',
        'dispatched_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(SupportTicketComment::class, 'comment_id');
    }
}
