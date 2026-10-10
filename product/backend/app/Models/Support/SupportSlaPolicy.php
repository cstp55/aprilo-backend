<?php

namespace App\Models\Support;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportSlaPolicy extends Model
{
    use HasUuids;

    protected $table = 'support_ticket_sla_policies';

    protected $fillable = [
        'organization_id',
        'name',
        'priority',
        'response_time_minutes',
        'resolution_time_minutes',
        'is_active',
        'business_hours',
    ];

    protected $casts = [
        'response_time_minutes' => 'integer',
        'resolution_time_minutes' => 'integer',
        'is_active' => 'boolean',
        'business_hours' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
