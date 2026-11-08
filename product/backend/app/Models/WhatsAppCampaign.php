<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppCampaign extends Model
{
    use HasUuids;

    protected $table = 'whatsapp_campaigns';

    protected $fillable = [
        'created_by',
        'name',
        'target_product',
        'audience_segment', // all, leads, converted, manual
        'message_type',
        'template_name',
        'template_language',
        'template_parameters',
        'message_text',
        'consent_confirmed_at',
        'status',
        'completed_at',
        'resend_count',
        'last_resent_at',
        'parent_campaign_id',
        'ai_generated',
        'ai_prompt_notes',
        'is_automated',
    ];

    protected function casts(): array
    {
        return [
            'template_parameters' => 'array',
            'consent_confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_resent_at' => 'datetime',
            'resend_count' => 'integer',
            'ai_generated' => 'boolean',
            'is_automated' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(WhatsAppCampaignRecipient::class, 'campaign_id');
    }

    public function parentCampaign(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_campaign_id');
    }

    public function resends(): HasMany
    {
        return $this->hasMany(self::class, 'parent_campaign_id');
    }

    public function cronLogs(): HasMany
    {
        return $this->hasMany(MarketingCronLog::class, 'campaign_id');
    }

    public function isResendable(): bool
    {
        return in_array($this->status, ['completed', 'partial', 'failed', 'queued'], true);
    }

    public function failedRecipientsCount(): int
    {
        return $this->recipients()->where('status', 'failed')->count();
    }

    public function acceptedRecipientsCount(): int
    {
        return $this->recipients()->where('status', 'accepted')->count();
    }
}
