<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingRequest extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'onboarding_requests';

    protected $fillable = [
        'token',
        'product_id',
        'plan_id',
        'account_data',
        'organization_data',
        'razorpay_subscription_id',
        'razorpay_customer_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'status',
        'provisioning_status',
        'provisioning_attempts',
        'provisioning_error',
        'provisioned_at',
        'trial_ends_at',
        'autopay_authorized',
        'organization_id',
        'user_id',
        'sso_token',
        'sso_token_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'account_data' => 'array',
            'organization_data' => 'array',
            'trial_ends_at' => 'datetime',
            'provisioning_attempts' => 'integer',
            'provisioned_at' => 'datetime',
            'sso_token_expires_at' => 'datetime',
            'autopay_authorized' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
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