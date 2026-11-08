<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PaymentLink extends Model
{
    use HasUuids;

    protected $fillable = [
        'created_by',
        'title',
        'slug',
        'product_name',
        'product_type',
        'description',
        'features',
        'base_amount',
        'allow_custom_amount',
        'min_amount',
        'coupon_code',
        'coupon_discount_type',
        'coupon_discount_value',
        'max_coupon_uses',
        'coupon_uses_count',
        'is_reusable',
        'status',
        'views_count',
        'payments_count',
        'total_collected_amount',
        'expires_at',
        'customer_name_preset',
        'customer_email_preset',
        'customer_phone_preset',
        'notify_whatsapp',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'coupon_discount_value' => 'decimal:2',
            'total_collected_amount' => 'decimal:2',
            'allow_custom_amount' => 'boolean',
            'is_reusable' => 'boolean',
            'notify_whatsapp' => 'boolean',
            'features' => 'array',
            'metadata' => 'array',
            'expires_at' => 'datetime',
            'views_count' => 'integer',
            'payments_count' => 'integer',
            'coupon_uses_count' => 'integer',
            'max_coupon_uses' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentLinkTransaction::class);
    }

    public function passedTransactions(): HasMany
    {
        return $this->hasMany(PaymentLinkTransaction::class)->where('status', 'passed');
    }

    public function failedTransactions(): HasMany
    {
        return $this->hasMany(PaymentLinkTransaction::class)->where('status', 'failed');
    }

    /**
     * Check if payment link is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Check if coupon code provided matches and is eligible.
     */
    public function isValidCoupon(?string $code): bool
    {
        if (blank($this->coupon_code) || blank($code)) {
            return false;
        }

        if (strcasecmp(trim($this->coupon_code), trim($code)) !== 0) {
            return false;
        }

        if ($this->max_coupon_uses !== null && $this->coupon_uses_count >= $this->max_coupon_uses) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount for a given base amount and optional coupon.
     */
    public function calculateDiscount(?string $couponCode, ?float $amount = null): float
    {
        if (! $this->isValidCoupon($couponCode)) {
            return 0.00;
        }

        $base = $amount !== null && $amount > 0 ? $amount : (float) $this->base_amount;

        if ($this->coupon_discount_type === 'percentage') {
            $pct = min(100, max(0, (float) $this->coupon_discount_value));
            $disc = ($base * $pct) / 100.0;
        } else {
            $disc = (float) $this->coupon_discount_value;
        }

        return round(min($base, max(0.00, $disc)), 2);
    }

    /**
     * Calculate final payable amount.
     */
    public function calculateFinalAmount(?string $couponCode, ?float $amount = null): float
    {
        $base = $amount !== null && $amount > 0 ? $amount : (float) $this->base_amount;
        $disc = $this->calculateDiscount($couponCode, $base);

        return round(max(0.00, $base - $disc), 2);
    }

    /**
     * Generate unique slug for public link.
     */
    public static function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        if (blank($baseSlug)) {
            $baseSlug = 'pay-aprilo';
        }

        $random = Str::lower(Str::random(5));
        $candidate = "{$baseSlug}-{$random}";

        while (static::where('slug', $candidate)->exists()) {
            $random = Str::lower(Str::random(5));
            $candidate = "{$baseSlug}-{$random}";
        }

        return $candidate;
    }

    /**
     * Public checkout URL.
     */
    public function getCheckoutUrl(): string
    {
        return url("/pay/{$this->slug}");
    }
}

