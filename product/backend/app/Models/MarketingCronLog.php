<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingCronLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'marketing_cron_logs';

    protected $fillable = [
        'command_name',
        'status', // success, failed, skipped, running
        'target_product',
        'segment_targeted',
        'recipients_count',
        'accepted_count',
        'failed_count',
        'campaign_id',
        'ai_strategy_summary',
        'ai_generated_copy',
        'error_message',
        'execution_time_seconds',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'recipients_count' => 'integer',
            'accepted_count' => 'integer',
            'failed_count' => 'integer',
            'execution_time_seconds' => 'decimal:2',
            'executed_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WhatsAppCampaign::class, 'campaign_id');
    }
}

