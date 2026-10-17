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
        $settings = $policy['settings'] ?? ($policy['organization']?->settings ?? $this->getSettings());
        $orgDetails = trim($settings->organization_details ?? '');
        
        // Strict context enforcement: if active, no retrieved documents, AND no organization metadata, fail back immediately
        if ($settings->strict_context_enforcement && empty($context) && empty($orgDetails)) {
            return [
                'answer_text' => 'I cannot confirm this from approved sources yet. Please escalate to support.',
                'answer_status' => 'fallback',
                'confidence_label' => 'low',
                'sources' => [],
            ];
        }

        // Limit context to top 3 items to optimize token usage and keep context summarized
        $slicedContext = array_values(array_slice($context, 0, 3));
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
        $prompt .= "$systemPrompt\n\n";
        if (!empty($orgDetails)) {
            $prompt .= "Organization Context & Services:\n$orgDetails\n\n";
        }
        if (!empty($eligibility)) {
            $prompt .= "Role Limitations, Guardrails & Policies:\n$eligibility\n\n";
        }

        if (!empty($contextText)) {
            $prompt .= "--- RELEVANT KNOWLEDGE BASE CONTEXT ---\n$contextText\n\n";
        }

        $prompt .= "Rules for Response:\n";
        $prompt .= "- Answer the user's question using the provided System Instructions, Organization Context, Role Limitations, and Knowledge Base as your source of truth.\n";
        if (!empty($slicedContext)) {
            $prompt .= "- Cite your document sources in your answer using bracketed numbers like [1], [2] when referencing information from the knowledge base.\n";
        }
        if ($settings->strict_context_enforcement) {
            $prompt .= "- Do not invent medical claims, pricing, doctor credentials, or hospital affiliations not supported by the context.\n";
            $prompt .= "- If a specific fact cannot be answered from the provided sources, follow the human handoff or limitation rules defined in the System Instructions.\n";
        }
        $prompt .= "- Follow the persona, style, and tone specified in the System Instructions.\n\n";

        $prompt .= "--- QUESTION ---\n$question\n\n";
        $prompt .= "Answer:";

        // Call Gemini API using dynamic configurations
        $apiKey = $settings->gemini_api_key ?: env('GEMINI_API_KEY', 'AQ.Ab8RN6IjC579cJiR1wboeMiUG06Ijkk_EY6ZwK-WnOaDqmeohQ');
        $model = $settings->gemini_model ?: 'gemini-1.5-flash';

        $answerText = '';
        $answerStatus = 'answered';

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
            
            $response = Http::timeout(10)
                ->withoutVerifying()
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
                $promptTokens = (int) ($json['usageMetadata']['promptTokenCount'] ?? 0);
                $candidatesTokens = (int) ($json['usageMetadata']['candidatesTokenCount'] ?? 0);
                $totalTokens = (int) ($json['usageMetadata']['totalTokenCount'] ?? ($promptTokens + $candidatesTokens));
            } else {
                Log::warning("Gemini API request failed: " . $response->status() . " " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Exception calling Gemini: " . $e->getMessage());
        }

        // Fallback generator if Gemini fails or is not configured
        if (empty($answerText)) {
            if (!empty($slicedContext)) {
                $bullets = [];
                foreach ($slicedContext as $index => $item) {
                    $label = '[' . ($index + 1) . ']';
                    $bullets[] = '- ' . ($item['excerpt'] ?? '') . ' ' . $label;
                }
                $answerText = "Based on approved sources, here is the summarized guidance:\n" . implode("\n", $bullets);
                $answerStatus = 'answered';
            } elseif (!empty($orgDetails)) {
                // Intelligent fallback generated directly from the configured Organization metadata & fields
                $answerText = $this->generateOfflineAnswerFromMetadata($question, $orgDetails, $settings);
                $answerStatus = 'answered';
            } else {
                $answerText = "I cannot confirm this from approved sources yet. Please escalate to support.";
                $answerStatus = 'fallback';
            }
        }

        if (!isset($totalTokens) || $totalTokens === 0) {
            $promptTokens = max(1, (int) round(strlen($prompt) / 4));
            $candidatesTokens = max(1, (int) round(strlen($answerText) / 4));
            $totalTokens = $promptTokens + $candidatesTokens;
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
            'token_usage' => [
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $candidatesTokens,
                'total_tokens' => $totalTokens,
            ],
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

        // 2. Check widget_key from request
        $widgetKey = request()->input('widget_key') ?? request()->header('X-Widget-Key');
        if ($widgetKey) {
            $setting = OrganizationSetting::where('public_widget_key', $widgetKey)->first();
            if ($setting) {
                return $setting;
            }
        }

        // 3. Check subdomain parameter
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
        
        // Always prioritize user-provided custom system prompt if filled in
        if (!empty(trim($settings->custom_system_prompt ?? ''))) {
            return trim($settings->custom_system_prompt);
        }

        switch ($settings->restriction_template) {
            case 'hr_policy':
                return "You are an HR Assistant named {$assistantName}. Answer questions regarding employee benefits, leaves, and policies using the provided context. Keep it direct and professional.";
            
            case 'general_faq':
                return "You are a helpful customer FAQ assistant named {$assistantName}. Use the provided context to answer questions about our services and guidelines.";
            
            case 'custom':
                return "You are {$assistantName}, a helpful organizational assistant.";
            
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

    private function generateOfflineAnswerFromMetadata(string $question, string $orgDetails, OrganizationSetting $settings): string
    {
        $lower = strtolower($question);
        $assistantName = $settings->assistant_name ?: 'Assistant';
        $orgName = $settings->organization?->name ?: 'Baxtiyor India Healthcare';

        if (str_contains($lower, 'transplant') || str_contains($lower, 'kidney') || str_contains($lower, 'procedure') || str_contains($lower, 'treatment') || str_contains($lower, 'hospital') || str_contains($lower, 'doctor')) {
            return "At {$orgName}, we help international patients explore and coordinate medical treatment options in India, including specialized procedures such as kidney transplants. \n\nOur patient coordination services include:\n• Connecting you with top accredited hospitals and transplant specialists in India\n• Medical opinion and treatment estimates\n• Medical visa assistance and travel guidance\n• Airport transfers and local accommodation guidance\n• Dedicated coordinator support throughout your stay\n\nClinical decisions require qualified medical professionals. Would you like to connect with a patient coordinator on WhatsApp or request a treatment estimate?";
        }

        if (str_contains($lower, 'service') || str_contains($lower, 'what') || str_contains($lower, 'offer') || str_contains($lower, 'help')) {
            return "At {$orgName}, we provide comprehensive patient coordination services for international medical travel to India, including:\n• Hospital & specialist coordination\n• Medical opinion and treatment cost estimates\n• Medical visa assistance\n• Airport pickup and travel planning\n• Accommodation guidance and follow-up coordination.\n\nHow may we assist your medical enquiry today?";
        }

        if (str_contains($lower, 'human') || str_contains($lower, 'coordinator') || str_contains($lower, 'contact') || str_contains($lower, 'agent') || str_contains($lower, 'person') || str_contains($lower, 'connect to agent')) {
            return "Could you please provide your name and reason for speaking with our team?";
        }

        return "Welcome to {$orgName}. We assist international patients exploring medical treatment options in India, including hospital coordination, treatment estimates, medical visas, and travel arrangements.\n\nHow may we help you today? You can ask about our services or request to speak with a patient coordinator.";
    }
}
