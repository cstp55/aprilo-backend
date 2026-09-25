<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationEntitlement extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'subscription_id',
        'product_id',
        'feature_key',
        'status',
        'limits',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'limits' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
