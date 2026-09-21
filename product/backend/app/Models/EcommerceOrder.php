<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcommerceOrder extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'connection_id',
        'external_order_id',
        'order_number',
        'customer_name',
        'customer_email',
        'total_amount',
        'currency',
        'order_status',
        'financial_status',
        'raw_data',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'raw_data' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(EcommerceConnection::class, 'connection_id');
    }
}
