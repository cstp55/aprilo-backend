<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLinkTransaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'payment_link_id',
        'invoice_id',
        'transaction_reference',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'status',
        'error_message',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'customer_company',
        'customer_gstin',
        'base_amount',
        'coupon_code',
        'discount_amount',
        'amount_paid',
        'currency',
        'payment_method',
        'whatsapp_sent',
        'whatsapp_message_id',
        'whatsapp_error',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'whatsapp_sent' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    public function paymentLink(): BelongsTo
    {
        return $this->belongsTo(PaymentLink::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function scopePassed(Builder $query): Builder
    {
        return $query->where('status', 'passed');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    public function isPassed(): bool
    {
        return $this->status === 'passed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}

