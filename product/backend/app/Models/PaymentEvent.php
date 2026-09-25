<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentEvent extends Model
{
    use HasUuids;

    protected $fillable = [
        'provider',
        'event_key',
        'event_type',
        'razorpay_subscription_id',
        'status',
        'metadata',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
