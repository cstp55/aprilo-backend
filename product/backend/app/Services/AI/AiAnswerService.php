<?php

namespace App\Services\AI;

class AiAnswerService
{
    public function __construct(private readonly AiProviderInterface $provider)
    {
    }

    public function answer(string $question, array $context, array $policy = []): array
    {
        return $this->provider->generateAnswer($question, $context, $policy);
    }
}
