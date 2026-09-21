<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcommerceProduct extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'connection_id',
        'external_product_id',
        'title',
        'sku',
        'description',
        'price',
        'inventory_quantity',
        'status',
        'raw_data',
        'embedding',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'inventory_quantity' => 'integer',
            'raw_data' => 'array',
            'embedding' => 'array',
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
