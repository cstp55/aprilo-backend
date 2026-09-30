<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrganizationChatUsage extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'usage_date',
        'queries_count',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_estimate',
    ];

    protected function casts(): array
    {
        return [
            'usage_date' => 'date',
            'queries_count' => 'integer',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'total_tokens' => 'integer',
            'cost_estimate' => 'decimal:4',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Atomically log query and token usage for today.
     */
    public static function recordUsage(
        string $organizationId,
        int $promptTokens = 0,
        int $completionTokens = 0,
        int $totalTokens = 0,
        float $cost = 0.0
    ): self {
        $today = Carbon::today()->toDateString();
        $totalTokens = $totalTokens > 0 ? $totalTokens : ($promptTokens + $completionTokens);

        // Fallback default calculation if cost is 0
        if ($cost <= 0 && $totalTokens > 0) {
            // ~$0.0005 per 1,000 tokens as a reasonable baseline
            $cost = round(($totalTokens / 1000) * 0.0005, 4);
        }

        /** @var self $usage */
        $usage = static::firstOrCreate(
            [
                'organization_id' => $organizationId,
                'usage_date' => $today,
            ],
            [
                'queries_count' => 0,
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'cost_estimate' => 0.0000,
            ]
        );

        $usage->increment('queries_count', 1);
        if ($promptTokens > 0) {
            $usage->increment('prompt_tokens', $promptTokens);
        }
        if ($completionTokens > 0) {
            $usage->increment('completion_tokens', $completionTokens);
        }
        if ($totalTokens > 0) {
            $usage->increment('total_tokens', $totalTokens);
        }
        if ($cost > 0) {
            $usage->increment('cost_estimate', $cost);
        }

        // Also increment aggregate counts on organization_settings
        $settings = OrganizationSetting::where('organization_id', $organizationId)->first();
        if ($settings) {
            $settings->increment('usage_queries_count', 1);
            if ($totalTokens > 0 && \Illuminate\Support\Facades\Schema::hasColumn('organization_settings', 'total_tokens_used')) {
                $settings->increment('total_tokens_used', $totalTokens);
            }
            if ($cost > 0 && \Illuminate\Support\Facades\Schema::hasColumn('organization_settings', 'usage_amount_due')) {
                $settings->increment('usage_amount_due', $cost);
            }
        }

        return $usage;
    }
}
