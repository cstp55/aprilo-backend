<?php

namespace App\Services\Knowledge;

use App\Models\KnowledgeChunk;
use App\Models\User;
use App\Services\AI\EmbeddingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RetrievalService
{
    public function __construct(
        private readonly SourceAccessService $access,
        private readonly EmbeddingService $embeddings
    ) {
    }

    public function retrieve(User $user, string $question, int $limit = 5): array
    {
        $terms = $this->terms($question);

        // Generate query embedding using the EmbeddingService
        $queryEmbedding = [];
        try {
            $queryEmbedding = $this->embeddings->embed($question);
        } catch (\Throwable $e) {
            Log::warning('Failed to generate embedding for retrieval: ' . $e->getMessage());
        }

        $driver = DB::getDriverName();
        $chunks = collect();

        if ($driver === 'pgsql' && !empty($queryEmbedding)) {
            $embeddingString = '[' . implode(',', $queryEmbedding) . ']';
            
            try {
                // Perform pgvector distance query
                $chunks = KnowledgeChunk::query()
                    ->with('source')
                    ->where('organization_id', $user->organization_id)
                    ->whereHas('source', fn ($query) => $query->where('status', 'indexed'))
                    ->selectRaw('*, (embedding <=> ?::vector) as distance', [$embeddingString])
                    ->orderByRaw('embedding <=> ?::vector', [$embeddingString])
                    ->limit(50)
                    ->get();
            } catch (\Throwable $e) {
                Log::error('pgvector search failed, falling back to database retrieval: ' . $e->getMessage());
            }
        }

        // If pgsql failed, driver is sqlite/mysql, or embedding was empty, fetch candidates
        if ($chunks->isEmpty()) {
            $chunks = KnowledgeChunk::query()
                ->with('source')
                ->where('organization_id', $user->organization_id)
                ->whereHas('source', fn ($query) => $query->where('status', 'indexed'))
                ->latest('created_at')
                ->limit(200)
                ->get();
        }

        $ranked = [];

        foreach ($chunks as $chunk) {
            if (!$chunk->source || !$this->access->canAccess($user, $chunk->source)) {
                continue;
            }

            // Keyword Matching Score
            $keywordScore = $this->score($terms, $question, $chunk->chunk_text, $chunk->source->title);

            // Vector Similarity Score
            $vectorSimilarity = 0.0;
            if (isset($chunk->distance)) {
                // Cosine distance is returned. Similarity = 1 - distance.
                $vectorSimilarity = 1.0 - (float)$chunk->distance;
            } elseif (!empty($queryEmbedding) && !empty($chunk->embedding)) {
                $vectorSimilarity = $this->cosineSimilarity($queryEmbedding, $chunk->embedding);
            }

            // Hybrid score calculation: 70% vector score + 30% keyword score
            $vectorWeight = 0.7;
            $keywordWeight = 0.3;

            // Normalize scores to [0.0, 1.0] range
            $normalizedKeyword = min($keywordScore / 10.0, 1.0);
            $normalizedVector = max(0.0, $vectorSimilarity);

            $hybridScore = ($normalizedKeyword * $keywordWeight) + ($normalizedVector * $vectorWeight);

            // Filter out low-matching results
            if ($hybridScore <= 0.05) {
                continue;
            }

            $ranked[] = [
                'chunk' => $chunk,
                'source' => $chunk->source,
                'score' => round($hybridScore, 4),
                'semantic_score' => round($vectorSimilarity, 4),
                'lexical_score' => round($keywordScore, 4),
                'excerpt' => $this->excerpt($chunk->chunk_text, $terms),
                'matched_terms' => $this->matchedTerms($terms, $chunk->chunk_text . ' ' . $chunk->source->title),
            ];
        }

        usort($ranked, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        return array_slice($ranked, 0, $limit);
    }

    private function cosineSimilarity(array $vec1, array $vec2): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        $count = count($vec1);

        if ($count === 0 || count($vec2) !== $count) {
            return 0.0;
        }

        for ($i = 0; $i < $count; $i++) {
            $dotProduct += (float)$vec1[$i] * (float)$vec2[$i];
            $normA += (float)$vec1[$i] * (float)$vec1[$i];
            $normB += (float)$vec2[$i] * (float)$vec2[$i];
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }

    private function score(array $terms, string $question, string $chunkText, string $sourceTitle): float
    {
        $haystack = strtolower($chunkText);
        $title = strtolower($sourceTitle);
        $score = 0.0;

        foreach ($terms as $term) {
            $count = substr_count($haystack, $term);

            if ($count > 0) {
                $score += 1 + min($count, 5) * 0.35;
            }

            if (str_contains($title, $term)) {
                $score += 0.6;
            }
        }

        $normalizedQuestion = strtolower(trim(preg_replace('/\s+/', ' ', $question) ?? $question));

        if ($normalizedQuestion !== '' && str_contains($haystack, $normalizedQuestion)) {
            $score += 4;
        }

        return round($score, 4);
    }

    private function excerpt(string $text, array $terms, int $length = 420): string
    {
        $lower = strtolower($text);
        $position = null;

        foreach ($terms as $term) {
            $found = strpos($lower, $term);

            if ($found !== false) {
                $position = $position === null ? $found : min($position, $found);
            }
        }

        $start = max(0, ($position ?? 0) - 80);
        $excerpt = trim(substr($text, $start, $length));

        if ($start > 0) {
            $excerpt = '...' . $excerpt;
        }

        if ($start + $length < strlen($text)) {
            $excerpt .= '...';
        }

        return $excerpt;
    }

    private function matchedTerms(array $terms, string $text): array
    {
        $text = strtolower($text);

        return array_values(array_filter($terms, fn (string $term) => str_contains($text, $term)));
    }

    private function terms(string $text): array
    {
        $words = preg_split('/[^a-z0-9]+/', strtolower($text)) ?: [];
        $stopwords = [
            'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'can', 'do', 'does', 'for', 'from',
            'how', 'i', 'in', 'is', 'it', 'may', 'of', 'on', 'or', 'our', 'please', 'policy',
            'should', 'the', 'their', 'to', 'we', 'what', 'when', 'where', 'who', 'with',
        ];

        $terms = array_filter($words, fn (string $word) => strlen($word) >= 3 && ! in_array($word, $stopwords, true));

        return array_values(array_unique($terms));
    }
}
