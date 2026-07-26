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
        'billing_mode',
        'amount',
        'status',
        'billing_period_start',
        'billing_period_end',
        'due_date',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
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
}
