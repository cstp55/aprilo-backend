<?php

namespace App\Models\Support;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketEvent extends Model
{
    use HasUuids;

    protected $fillable = [
        'ticket_id',
        'organization_id',
        'actor_id',
        'actor_type',
        'event_type',
        'previous_value',
        'new_value',
        'note',
    ];

    protected $casts = [
        'previous_value' => 'array',
        'new_value' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
