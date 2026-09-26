<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppCampaignRecipient extends Model
{
    use HasUuids;

    protected $table = 'whatsapp_campaign_recipients';

    protected $fillable = [
        'campaign_id',
        'user_id',
        'recipient_name',
        'recipient_phone',
        'status',
        'whatsapp_message_id',
        'response_json',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'response_json' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WhatsAppCampaign::class, 'campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}