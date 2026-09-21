<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EcommerceLicense extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'connection_id',
        'license_key',
        'platform',
        'domain',
        'status',
        'max_stores',
        'expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'max_stores' => 'integer',
        ];
    }

    public static function generateKey(string $platform = 'magento'): string
    {
        return strtoupper('APR-' . substr($platform, 0, 3) . '-' . Str::random(5) . '-' . Str::random(5) . '-' . Str::random(5));
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(EcommerceConnection::class, 'connection_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isValidForDomain(string $domain): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if (empty($this->domain)) {
            return true;
        }

        return strtolower(trim($this->domain)) === strtolower(trim($domain));
    }
}
