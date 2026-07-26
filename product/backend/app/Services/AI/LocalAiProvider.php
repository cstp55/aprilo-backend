<?php

namespace App\Services\AI;

class LocalAiProvider implements AiProviderInterface
{
    public function generateAnswer(string $question, array $context, array $policy = []): array
    {
        if ($context === []) {
            return [
                'answer_text' => 'I cannot confirm this from approved HR sources yet. Upload and index the relevant policy document, then ask again.',
                'answer_status' => 'fallback',
                'confidence_label' => 'low',
                'sources' => [],
            ];
        }

        $bullets = [];
        $sources = [];

        foreach (array_slice($context, 0, 3) as $index => $item) {
            $label = '['.($index + 1).']';
            $bullets[] = '- '.$this->cleanExcerpt($item['excerpt']).' '.$label;
            $sources[] = [
                'source' => $item['source'],
                'chunk' => $item['chunk'],
                'citation_label' => $label,
                'score' => $item['score'],
                'excerpt' => $item['excerpt'],
            ];
        }

        return [
            'answer_text' => "Based on approved HR sources, here is the most relevant guidance:\n\n".implode("\n", $bullets),
            'answer_status' => 'answered',
            'confidence_label' => count($context) >= 2 ? 'medium' : 'low',
            'sources' => $sources,
        ];
    }

    public function createEmbedding(string $text): array
    {
        return [];
    }

    public function classifySensitivity(string $question): array
    {
        return ['status' => 'normal', 'reason' => 'local_provider_not_used_for_classification'];
    }

    private function cleanExcerpt(string $excerpt): string
    {
        $excerpt = trim(preg_replace('/\s+/', ' ', $excerpt) ?? $excerpt);

        return rtrim($excerpt, '.').'.';
    }
}
