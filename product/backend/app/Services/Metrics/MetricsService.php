<?php

namespace App\Services\Metrics;

use App\Models\Organization;

class MetricsService
{
    public function summary(Organization $organization): array
    {
        $total = $organization->questions()->count();
        $resolved = $organization->questions()->where('status', 'answered')->count();
        $escalated = $organization->questions()->where('status', 'escalated')->count();
        $unsupported = $organization->questions()->where('status', 'unsupported')->count();
        $minutesPerResolved = $organization->settings?->minutes_saved_per_resolved_question ?? 5;
        $estimatedMinutesSaved = $resolved * $minutesPerResolved;

        return [
            'total_questions' => $total,
            'resolved_questions' => $resolved,
            'escalated_questions' => $escalated,
            'unsupported_questions' => $unsupported,
            'resolution_rate' => $total > 0 ? round($resolved / $total, 4) : 0,
            'helpful_rate' => 0,
            'estimated_minutes_saved' => $estimatedMinutesSaved,
            'estimated_hours_saved' => round($estimatedMinutesSaved / 60, 2),
        ];
    }
}
