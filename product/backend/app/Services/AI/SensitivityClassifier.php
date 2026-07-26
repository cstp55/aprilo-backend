<?php

namespace App\Services\AI;

class SensitivityClassifier
{
    private const SENSITIVE_TERMS = [
        'termination',
        'harassment',
        'discrimination',
        'payroll dispute',
        'medical leave',
        'legal',
        'immigration',
        'investigation',
    ];

    public function classify(string $question): array
    {
        $normalized = mb_strtolower($question);

        foreach (self::SENSITIVE_TERMS as $term) {
            if (str_contains($normalized, $term)) {
                return ['status' => 'sensitive', 'reason' => $term];
            }
        }

        return ['status' => 'normal', 'reason' => null];
    }
}
