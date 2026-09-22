<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'subscriptions';

    protected $fillable = [
        'organization_id',
        'product_id',
        'plan_id',
        'razorpay_subscription_id',
        'razorpay_plan_id',
        'razorpay_customer_id',
        'status',
        'trial_start',
        'trial_end',
        'current_cycle_start',
        'current_cycle_end',
        'price',
        'currency',
        'request_limit',
        'auto_renew',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'trial_start' => 'datetime',
            'trial_end' => 'datetime',
            'current_cycle_start' => 'datetime',
            'current_cycle_end' => 'datetime',
            'price' => 'decimal:2',
            'request_limit' => 'integer',
            'auto_renew' => 'boolean',
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}