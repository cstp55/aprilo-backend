<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingLead extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'marketing_leads';

    protected $fillable = [
        'organization_id',
        'name',
        'phone',
        'email',
        'company_name',
        'status', // lead, converted, lost
        'target_product',
        'preferred_channel',
        'last_contacted_at',
        'last_campaign_name',
        'conversion_notes',
        'tags',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'metadata' => 'array',
            'last_contacted_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function campaignRecipients(): HasMany
    {
        return $this->hasMany(WhatsAppCampaignRecipient::class, 'lead_id');
    }

    /**
     * Scope for unconverted leads (ideal for sales followup, trial offers, discount campaigns)
     */
    public function scopeUnconverted(Builder $query): Builder
    {
        return $query->where('status', 'lead');
    }

    /**
     * Scope for converted paying customers (ideal for new product awareness, cross-sells, feature updates)
     */
    public function scopeConverted(Builder $query): Builder
    {
        return $query->where('status', 'converted');
    }

    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    public function isLead(): bool
    {
        return $this->status === 'lead';
    }

    public function markAsConverted(?string $notes = null): void
    {
        $this->update([
            'status' => 'converted',
            'conversion_notes' => $notes ?: ($this->conversion_notes ? $this->conversion_notes . "\nConverted on " . now()->toFormattedDateString() : 'Converted on ' . now()->toFormattedDateString()),
        ]);
    }

    public function markAsLead(?string $notes = null): void
    {
        $this->update([
            'status' => 'lead',
            'conversion_notes' => $notes ?: $this->conversion_notes,
        ]);
    }
}

