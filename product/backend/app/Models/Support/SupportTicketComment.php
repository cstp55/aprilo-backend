<?php

namespace App\Models\Support;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketComment extends Model
{
    use HasUuids;

    protected $fillable = [
        'ticket_id',
        'organization_id',
        'user_id',
        'customer_id',
        'author_type',
        'author_name',
        'body',
        'is_internal',
        'channel',
        'external_message_id',
        'attachments',
        'metadata',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'attachments' => 'array',
        'metadata' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(SupportCustomer::class, 'customer_id');
    }

    public function scopeCustomerVisible(Builder $query): Builder
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternalNotes(Builder $query): Builder
    {
        return $query->where('is_internal', true);
    }
}
