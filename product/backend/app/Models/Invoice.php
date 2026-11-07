<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'invoice_number',
        'document_type',
        'billing_mode',
        'currency',
        'amount',
        'status',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_address',
        'product_name',
        'product_type',
        'plan_name',
        'order_id',
        'billing_period_text',
        'quantity',
        'unit_amount',
        'discount_amount',
        'subtotal',
        'gst_rate',
        'gst_amount',
        'total_amount',
        'payment_method',
        'payment_reference',
        'notes',
        'metadata',
        'billing_period_start',
        'billing_period_end',
        'due_date',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'unit_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'gst_rate' => 'decimal:2',
            'gst_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'quantity' => 'integer',
            'metadata' => 'array',
            'billing_period_start' => 'date',
            'billing_period_end' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Retrieve stored attribute from model attribute, metadata array, or fallback storage cache.
     */
    public function getStoredMeta(string $key, $default = null)
    {
        if (isset($this->attributes[$key]) && $this->attributes[$key] !== null && $this->attributes[$key] !== '') {
            return $this->attributes[$key];
        }

        if (! empty($this->metadata) && is_array($this->metadata) && isset($this->metadata[$key]) && $this->metadata[$key] !== null && $this->metadata[$key] !== '') {
            return $this->metadata[$key];
        }

        $metaFile = storage_path('invoices_meta.json');
        if (file_exists($metaFile)) {
            static $cachedMeta = null;
            if ($cachedMeta === null) {
                $cachedMeta = json_decode(@file_get_contents($metaFile), true) ?: [];
            }
            $numKey = $this->invoice_number;
            $idKey = $this->id;
            if ($numKey && isset($cachedMeta[$numKey][$key]) && $cachedMeta[$numKey][$key] !== null && $cachedMeta[$numKey][$key] !== '') {
                return $cachedMeta[$numKey][$key];
            }
            if ($idKey && isset($cachedMeta[$idKey][$key]) && $cachedMeta[$idKey][$key] !== null && $cachedMeta[$idKey][$key] !== '') {
                return $cachedMeta[$idKey][$key];
            }
        }

        return $default;
    }

    /**
     * Generate the next central invoice/document sequence across all products.
     */
    public static function generateNumber(string $documentType = 'sales_invoice'): string
    {
        $prefix = match ($documentType) {
            'credit_note' => 'CN',
            'refund_receipt' => 'REF',
            'quotation' => 'QT',
            'payment_receipt' => 'RCPT',
            default => 'INV',
        };

        $year = date('Y');
        $pattern = "{$prefix}-{$year}-%";

        $latest = self::where('invoice_number', 'LIKE', $pattern)
            ->orderBy('invoice_number', 'desc')
            ->value('invoice_number');

        $nextSeq = 1;
        if ($latest && preg_match("/" . preg_quote($prefix, '/') . "-{$year}-(\\d+)/", $latest, $matches)) {
            $nextSeq = intval($matches[1]) + 1;
        }

        return sprintf('%s-%s-%03d', $prefix, $year, $nextSeq);
    }

    // Accessors for reliable, elegant template display
    public function getDisplayCustomerNameAttribute(): string
    {
        return $this->getStoredMeta('customer_name') ?? $this->organization?->name ?? 'Valued Customer';
    }

    public function getDisplayCustomerEmailAttribute(): string
    {
        return $this->getStoredMeta('customer_email') ?? $this->organization?->owner?->email ?? ($this->organization?->email ?? 'contact@customer.com');
    }

    public function getDisplayCustomerAddressAttribute(): string
    {
        return $this->getStoredMeta('customer_address') ?? ($this->organization?->settings?->business_address ?? 'Jaunpur, Uttar Pradesh, India');
    }

    public function getDisplayCustomerIdAttribute(): string
    {
        return $this->getStoredMeta('customer_id') ?? ('CUST-' . strtoupper(substr(str_replace('-', '', $this->organization_id ?? '1001'), 0, 4)));
    }

    public function getDisplayOrderIdAttribute(): string
    {
        return $this->getStoredMeta('order_id') ?? ('APR-ORD-' . strtoupper(substr(str_replace('-', '', $this->id ?? '1001'), 0, 4)));
    }

    public function getDisplayProductNameAttribute(): string
    {
        return $this->getStoredMeta('product_name') ?? ($this->organization ? 'Aprilo ' . ucfirst($this->organization->plan ?? 'POS') . ' – Annual Subscription' : 'Aprilo POS – Annual Subscription');
    }

    public function getDisplayProductTypeAttribute(): string
    {
        return $this->getStoredMeta('product_type') ?? 'SaaS Subscription';
    }

    public function getDisplayPlanNameAttribute(): string
    {
        return $this->getStoredMeta('plan_name') ?? ucfirst($this->organization?->plan ?? 'Professional');
    }

    public function getDisplayBillingPeriodAttribute(): string
    {
        if ($val = $this->getStoredMeta('billing_period_text')) {
            return $val;
        }
        if ($this->billing_period_start && $this->billing_period_end) {
            return $this->billing_period_start->format('d M Y') . ' – ' . $this->billing_period_end->format('d M Y');
        }
        return date('d M Y') . ' – ' . date('d M Y', strtotime('+1 year'));
    }

    public function getDisplayUnitAmountAttribute(): float
    {
        return floatval($this->getStoredMeta('unit_amount', $this->amount));
    }

    public function getDisplayDiscountAmountAttribute(): float
    {
        return floatval($this->getStoredMeta('discount_amount', 0.00));
    }

    public function getDisplaySubtotalAttribute(): float
    {
        $sub = $this->getStoredMeta('subtotal');
        if ($sub !== null && $sub > 0) {
            return floatval($sub);
        }
        $calc = ($this->display_unit_amount * ($this->quantity ?: 1)) - $this->display_discount_amount;
        return max(0.00, $calc);
    }

    public function getDisplayGstAmountAttribute(): float
    {
        return floatval($this->getStoredMeta('gst_amount', 0.00));
    }

    public function getDisplayTotalAmountAttribute(): float
    {
        $tot = $this->getStoredMeta('total_amount');
        if ($tot !== null && $tot > 0) {
            return floatval($tot);
        }
        return floatval($this->amount ?: $this->display_subtotal);
    }

    public function getDisplayPaymentMethodAttribute(): string
    {
        return $this->getStoredMeta('payment_method') ?? 'UPI / Online Payment';
    }

    public function getDisplayPaymentReferenceAttribute(): string
    {
        return $this->getStoredMeta('payment_reference') ?? ('TXN-' . strtoupper(substr(md5($this->id ?? 'aprilo'), 0, 8)));
    }

    public function getDisplayDocumentTitleAttribute(): string
    {
        return match ($this->document_type ?? 'sales_invoice') {
            'quotation' => 'QUOTATION',
            'payment_receipt' => 'PAYMENT RECEIPT',
            'credit_note' => 'CREDIT NOTE',
            'refund_receipt' => 'REFUND RECEIPT',
            default => 'SALES INVOICE',
        };
    }
}
