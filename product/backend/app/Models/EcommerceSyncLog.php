<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcommerceSyncLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'connection_id',
        'sync_type',
        'status',
        'items_processed',
        'items_failed',
        'error_details',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'items_processed' => 'integer',
            'items_failed' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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
