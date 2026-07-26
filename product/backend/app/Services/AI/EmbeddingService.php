<?php

namespace App\Services\AI;

class EmbeddingService
{
    public function __construct(private readonly AiProviderInterface $provider)
    {
    }

    public function embed(string $text): array
    {
        return $this->provider->createEmbedding($text);
    }
}
