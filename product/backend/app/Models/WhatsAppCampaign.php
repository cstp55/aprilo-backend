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
        'message_type',
        'template_name',
        'template_language',
        'template_parameters',
        'message_text',
        'consent_confirmed_at',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'template_parameters' => 'array',
            'consent_confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
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
}