<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EcommerceConnection extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'platform',
        'store_name',
        'store_url',
        'api_key',
        'api_secret',
        'access_token',
        'status',
        'auto_sync_enabled',
        'sync_interval',
        'last_synced_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'auto_sync_enabled' => 'boolean',
            'last_synced_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(EcommerceProduct::class, 'connection_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(EcommerceOrder::class, 'connection_id');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(EcommerceLicense::class, 'connection_id');
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(EcommerceSyncLog::class, 'connection_id');
    }
}
