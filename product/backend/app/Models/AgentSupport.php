<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentSupport extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'user_id',
        'nickname',
        'availability_status',
        'availability_slots',
        'is_active',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'availability_slots' => 'array',
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
