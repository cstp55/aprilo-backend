<?php

namespace App\Models\Support;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportCustomer extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'phone',
        'company',
        'external_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function channelIdentities(): HasMany
    {
        return $this->hasMany(SupportChannelIdentity::class, 'customer_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'customer_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SupportTicketComment::class, 'customer_id');
    }
}
