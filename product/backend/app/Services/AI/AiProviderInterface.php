<?php

namespace App\Services\AI;

interface AiProviderInterface
{
    public function generateAnswer(string $question, array $context, array $policy = []): array;

    public function createEmbedding(string $text): array;

    public function classifySensitivity(string $question): array;
}
