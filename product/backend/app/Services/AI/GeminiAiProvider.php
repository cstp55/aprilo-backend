<?php

namespace App\Services\AI;

use App\Models\Organization;
use App\Models\OrganizationSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiProvider implements AiProviderInterface
{
    public function generateAnswer(string $question, array $context, array $policy = []): array
    {
        $settings = $this->getSettings();
        
        // Strict context enforcement: if active and no context is retrieved, fail back immediately
        if ($settings->strict_context_enforcement && empty($context)) {
            return [
                'answer_text' => 'I cannot confirm this from approved sources yet. Please escalate to support.',
                'answer_status' => 'fallback',
                'confidence_label' => 'low',
                'sources' => [],
            ];
        }

        // Limit context to top 3 items to optimize token usage and keep context summarized
        $slicedContext = array_slice($context, 0, 3);
        $contextText = "";
        $sources = [];

        foreach ($slicedContext as $index => $item) {
            $label = '[' . ($index + 1) . ']';
            $contextText .= "$label Document Title: " . ($item['source']->title ?? 'Unknown') . "\n";
            // Clean excerpt and limit to a concise length to save input tokens
            $excerpt = $this->cleanExcerpt($item['excerpt'] ?? '');
            if (strlen($excerpt) > 200) {
                $excerpt = substr($excerpt, 0, 200) . '...';
            }
            $contextText .= "Excerpt: $excerpt\n\n";

            $sources[] = [
                'source' => $item['source'] ?? null,
                'chunk' => $item['chunk'] ?? null,
                'citation_label' => $label,
                'score' => $item['score'] ?? 0,
                'semantic_score' => $item['semantic_score'] ?? 0,
                'lexical_score' => $item['lexical_score'] ?? 0,
                'excerpt' => $excerpt,
            ];
        }

        // Resolve System Instruction prompt based on template or custom value
        $systemPrompt = $this->getSystemPromptFromTemplate($settings);

        // Inject Organization Details & Eligibility Criteria if configured
        $orgDetails = trim($settings->organization_details ?? '');
        $eligibility = trim($settings->eligibility_criteria ?? '');

        $prompt = "System Instructions:\n";
        $prompt .= "$systemPrompt\n";
        if (!empty($orgDetails)) {
            $prompt .= "Organization Context:\n$orgDetails\n";
        }
        if (!empty($eligibility)) {
            $prompt .= "Eligibility Rules:\n$eligibility\n";
        }

        // Add strict grounding behavior & token economy guidelines
        $prompt .= "\nRules for Response:\n";
        $prompt .= "- Answer the question based strictly on the provided context, organization context, and eligibility rules.\n";
        if ($settings->strict_context_enforcement) {
            $prompt .= "- Do not hallucinate or use external general facts. If the answer cannot be found in the provided details, respond exactly with:\n";
            $prompt .= "\"I cannot confirm this from approved sources yet. Please escalate to support.\"\n";
        }
        $prompt .= "- Be extremely concise and direct. Use the minimum number of tokens possible to answer.\n";
        $prompt .= "- Avoid introductory text, conversational filler, greetings, and lengthy descriptions. Summarize the facts.\n";
        $prompt .= "- Always cite your sources in your answer using bracketed numbers like [1], [2], etc.\n\n";

        $prompt .= "--- CONTEXT ---\n$contextText\n";
        $prompt .= "--- QUESTION ---\n$question\n\n";
        $prompt .= "Answer:";

        // Call Gemini API using dynamic configurations
        $apiKey = $settings->gemini_api_key ?: env('GEMINI_API_KEY', 'AQ.Ab8RN6IjC579cJiR1wboeMiUG06Ijkk_EY6ZwK-WnOaDqmeohQ');
        $model = $settings->gemini_model ?: 'gemini-flash-latest';

        $answerText = '';
        $answerStatus = 'answered';

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
            
            $response = Http::timeout(8)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-goog-api-key' => $apiKey,
                ])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $json = $response->json();
                $generatedText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if (trim($generatedText) !== '') {
                    $answerText = trim($generatedText);
                    if (str_contains($answerText, "I cannot confirm this from approved sources")) {
                        $answerStatus = 'fallback';
                    }
                }
            } else {
                Log::warning("Gemini API request failed: " . $response->status() . " " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Exception calling Gemini: " . $e->getMessage());
        }

        // Fallback lexical generator if Gemini fails or is not configured
        if (empty($answerText)) {
            if (empty($slicedContext)) {
                $answerText = "I cannot confirm this from approved sources yet. Please escalate to support.";
                $answerStatus = 'fallback';
            } else {
                $bullets = [];
                foreach ($slicedContext as $index => $item) {
                    $label = '[' . ($index + 1) . ']';
                    $bullets[] = '- ' . ($item['excerpt'] ?? '') . ' ' . $label;
                }
                $answerText = "Based on approved sources, here is the summarized guidance:\n" . implode("\n", $bullets);
                $answerStatus = 'answered';
            }
        }

        $maxSimilarity = 0.0;
        foreach ($slicedContext as $item) {
            $sim = (float)($item['semantic_score'] ?? 0.0);
            if ($sim > $maxSimilarity) {
                $maxSimilarity = $sim;
            }
        }

        $confidenceLabel = 'low';
        if ($answerStatus !== 'fallback' && !empty($slicedContext)) {
            if ($maxSimilarity >= 0.82) {
                $confidenceLabel = 'high';
            } elseif ($maxSimilarity >= 0.70) {
                $confidenceLabel = 'medium';
            }
        }

        return [
            'answer_text' => $answerText,
            'answer_status' => $answerStatus,
            'confidence_label' => $confidenceLabel,
            'sources' => $sources,
        ];
    }

    public function createEmbedding(string $text): array
    {
        $settings = $this->getSettings();
        $apiKey = $settings->gemini_api_key ?: env('GEMINI_API_KEY', 'AQ.Ab8RN6IjC579cJiR1wboeMiUG06Ijkk_EY6ZwK-WnOaDqmeohQ');
        $model = 'text-embedding-004';

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:embedContent";
            $response = Http::timeout(8)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-goog-api-key' => $apiKey,
                ])
                ->post($url, [
                    'model' => "models/{$model}",
                    'content' => [
                        'parts' => [
                            ['text' => $text]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $json = $response->json();
                return $json['embedding']['values'] ?? [];
            } else {
                Log::warning("Gemini Embedding API request failed: " . $response->status() . " " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Exception calling Gemini Embedding: " . $e->getMessage());
        }

        return [];
    }

    public function classifySensitivity(string $question): array
    {
        return ['status' => 'normal', 'reason' => 'gemini_classifier_not_implemented'];
    }

    /**
     * Resolves the current organization settings from the active request context.
     */
    private function getSettings(): OrganizationSetting
    {
        // 1. Check authenticated user
        if (auth()->check()) {
            $user = auth()->user();
            if ($user && $user->organization) {
                return $user->organization->settings ?? OrganizationSetting::firstOrCreate([
                    'organization_id' => $user->organization_id
                ]);
            }
        }

        // 2. Check subdomain parameter
        $subdomain = request()->input('subdomain');
        if ($subdomain) {
            $org = Organization::where('name', 'like', "%$subdomain%")->first();
            if ($org) {
                return $org->settings ?? OrganizationSetting::firstOrCreate([
                    'organization_id' => $org->id
                ]);
            }
        }

        // 3. Fallback to first organization in database
        $firstOrg = Organization::first();
        if ($firstOrg) {
            return $firstOrg->settings ?? OrganizationSetting::firstOrCreate([
                'organization_id' => $firstOrg->id
            ]);
        }

        // 4. Default configuration
        return new OrganizationSetting([
            'gemini_model' => 'gemini-flash-latest',
            'restriction_template' => 'strict_retrieval',
            'strict_context_enforcement' => true,
        ]);
    }

    /**
     * Generates a base system prompt matching the selected template.
     */
    private function getSystemPromptFromTemplate(OrganizationSetting $settings): string
    {
        $assistantName = $settings->assistant_name ?: 'Aprilo Bot';
        
        switch ($settings->restriction_template) {
            case 'hr_policy':
                return "You are an HR Assistant named {$assistantName}. Answer questions regarding employee benefits, leaves, and policies using the provided context. Keep it direct and professional.";
            
            case 'general_faq':
                return "You are a helpful customer FAQ assistant named {$assistantName}. Use the provided context to answer questions about our services and guidelines.";
            
            case 'custom':
                return $settings->custom_system_prompt ?: "You are {$assistantName}, a helpful organizational assistant.";
            
            case 'strict_retrieval':
            default:
                return "You are {$assistantName}, representing the organization. Answer the user's question strictly using the provided context. Do not use external knowledge or general facts.";
        }
    }

    private function cleanExcerpt(string $excerpt): string
    {
        $excerpt = trim(preg_replace('/\s+/', ' ', $excerpt) ?? $excerpt);
        return rtrim($excerpt, '.') . '.';
    }
}
