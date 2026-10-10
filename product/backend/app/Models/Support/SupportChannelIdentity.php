<?php

namespace App\Models\Support;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportChannelIdentity extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'customer_id',
        'channel',
        'channel_identifier',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(SupportCustomer::class, 'customer_id');
    }
}
