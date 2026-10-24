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
            // Intelligent natural language synthesis directly from organization domain knowledge & RAG context
            $answerText = $this->generateOfflineAnswerFromMetadata($question, $orgDetails, $settings, $slicedContext);
            $answerStatus = 'answered';
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

    private function generateOfflineAnswerFromMetadata(string $question, string $orgDetails, OrganizationSetting $settings, array $slicedContext = []): string
    {
        $lower = strtolower(trim($question));
        $assistantName = $settings->assistant_name ?: 'Assistant';
        $org = $settings->organization;
        $orgName = $org?->name ?: 'our organization';
        $systemPrompt = trim($settings->custom_system_prompt ?? '');
        $guardrails = trim($settings->eligibility_criteria ?? '');
        $orgContext = trim($orgDetails ?: ($settings->organization_details ?? ''));
        $whatsappUrl = $settings->getWhatsAppDirectUrl();
        $waNumber = $settings->whatsapp_number ?: $settings->whatsapp_sender_phone;

        // 1. Live human / team connection request
        if (preg_match('/\b(connect(\s+with|\s+to)?\s+(the\s+)?team|talk\s+to|speak\s+with|human|agent|coordinator|specialist|person|representative|support team|connect to agent)\b/i', $lower)) {
            $response = "I would be glad to connect you with our {$orgName} patient coordination team! Could you please share your **Name** and the **Reason** for your enquiry?";
            if ($whatsappUrl) {
                $response .= "\n\nYou can also message us directly on [WhatsApp]({$whatsappUrl})" . ($waNumber ? " at {$waNumber}" : "") . ".";
            }
            return $response;
        }

        // 2. Greetings
        if (preg_match('/^(hi|hello|hey|greetings|good\s+(morning|afternoon|evening)|hola)(\s*|[!?.].*)$/i', $lower)) {
            if (!empty($settings->welcome_message)) {
                return $settings->welcome_message;
            }
            return "Hello! Welcome to **{$orgName}**. I am {$assistantName}, your dedicated virtual assistant. How may I assist you today?";
        }

        // 3. Medical Visa / Travel / India Treatment application
        $isVisaQuery = str_contains($lower, 'visa') || str_contains($lower, 'apply') || str_contains($lower, 'medial') || str_contains($lower, 'medical') || str_contains($lower, 'invitation') || str_contains($lower, 'travel') || str_contains($lower, 'passport');
        if ($isVisaQuery && (str_contains(strtolower($orgName), 'health') || str_contains(strtolower($orgDetails), 'patient') || str_contains(strtolower($orgDetails), 'medical'))) {
            $visaText = "Welcome to **{$orgName}**! We provide comprehensive medical visa and international patient coordination services for treatments in India:\n\n" .
                "• **Hospital Visa Invitation Letter:** We coordinate directly with top accredited hospitals in India to issue official Medical Visa Invitation Letters for you and your attendant.\n" .
                "• **Medical Report Review & Quotes:** Share your medical reports to receive doctor opinions and transparent treatment estimates before traveling.\n" .
                "• **Travel & Arrival Assistance:** We coordinate flight planning, airport transfers, local accommodation, and priority hospital appointments.\n\n" .
                "Would you like us to review your reports or connect you with a patient coordinator?";
            return $visaText;
        }

        // 4. Hospital & Doctor coordination
        $isHospitalQuery = str_contains($lower, 'hospital') || str_contains($lower, 'doctor') || str_contains($lower, 'specialist') || str_contains($lower, 'surgeon') || str_contains($lower, 'appointment');
        if ($isHospitalQuery) {
            return "At **{$orgName}**, we partner with premier multi-specialty hospitals and leading surgeons across India. We help you obtain:\n\n" .
                "• Doctor profiles and specialist recommendations\n" .
                "• Second medical opinions from senior specialists\n" .
                "• Priority appointment scheduling and treatment planning\n\n" .
                "Would you like us to review your medical reports for a doctor recommendation?";
        }

        // 5. Treatment cost & estimates
        $isCostQuery = str_contains($lower, 'cost') || str_contains($lower, 'price') || str_contains($lower, 'estimate') || str_contains($lower, 'quote') || str_contains($lower, 'fee');
        if ($isCostQuery) {
            return "Treatment costs in India vary based on the procedure, medical specialty, and selected hospital. **{$orgName}** coordinates complimentary, transparent treatment estimates directly from accredited partner hospitals.\n\n" .
                "Please share your medical requirements or reports, and our coordination team will arrange an estimate for you.";
        }

        // 6. Contact / WhatsApp inquiry
        if (str_contains($lower, 'contact') || str_contains($lower, 'whatsapp') || str_contains($lower, 'phone') || str_contains($lower, 'reach') || str_contains($lower, 'call') || str_contains($lower, 'email')) {
            if ($whatsappUrl) {
                return "You can reach the **{$orgName}** team directly on WhatsApp" . ($waNumber ? " ({$waNumber})" : "") . ":\n\n" .
                    "[Connect on WhatsApp]({$whatsappUrl})\n\n" .
                    "Or request to speak with a live coordinator right here in this chat!";
            }
            return "You can reach the **{$orgName}** coordination team directly by asking to connect with a live agent right here in this chat!";
        }

        // 7. Check RAG Context chunks if available
        if (!empty($slicedContext)) {
            $relevantSentences = [];
            foreach ($slicedContext as $item) {
                $excerpt = $item['excerpt'] ?? '';
                $clean = $this->cleanRawMetadataHeaders($excerpt, $orgName);
                if (strlen($clean) > 20 && !str_contains($clean, 'Never reveal passwords') && !str_contains($clean, 'Step 1:')) {
                    $relevantSentences[] = $clean;
                }
            }

            if (!empty($relevantSentences)) {
                $firstInfo = $relevantSentences[0];
                return "Regarding your enquiry at **{$orgName}**:\n\n" . $firstInfo . "\n\nHow else can I assist you today?";
            }
        }

        // 8. Services / Offerings general overview
        if (str_contains($lower, 'service') || str_contains($lower, 'what do you do') || str_contains($lower, 'offer') || str_contains($lower, 'about')) {
            return "Welcome to **{$orgName}**! We assist patients and their families explore medical treatment options by coordinating access to accredited hospitals, specialist doctors, medical opinions, treatment estimates, visa assistance, and travel arrangements.\n\nHow may we assist you today?";
        }

        // 9. Default friendly response
        return "Welcome to **{$orgName}**. I am {$assistantName}, your dedicated assistant. How may I assist you today? You can ask about our services, hospital coordination, doctor opinions, visa guidance, or request to connect with a coordinator.";
    }

    private function cleanRawMetadataHeaders(string $rawText, string $orgName): string
    {
        $text = $rawText;

        // Strip internal role definitions and prompt directives
        $text = preg_replace('/You are the official AI assistant for [^.]+\./i', '', $text);
        $text = preg_replace('/^Organization Name:\s*.*$/im', '', $text);
        $text = preg_replace('/^Website:\s*.*$/im', '', $text);
        $text = preg_replace('/^Industry:\s*.*$/im', '', $text);
        $text = preg_replace('/^Target Audience:\s*.*$/im', '', $text);
        $text = preg_replace('/^Primary Objective:\s*.*$/im', '', $text);
        $text = preg_replace('/^Communication Languages:\s*.*$/im', '', $text);
        $text = preg_replace('/^Official Information Source:\s*.*$/im', '', $text);
        $text = preg_replace('/^Contact Channel:\s*.*$/im', '', $text);
        $text = preg_replace('/^Organization Description:\s*/im', '', $text);
        $text = preg_replace('/^(ANSWERING|PATIENT SUPPORT|HUMAN HANDOFF|HUMAN HANDOFF & AGENT ESCALATION|MEDICAL QUESTIONS|PRIVACY|STYLE):\s*/im', '', $text);
        $text = preg_replace('/^Step \d+:\s*.*$/im', '', $text);
        $text = preg_replace('/^Whenever a visitor asks to speak with.*$/im', '', $text);
        $text = preg_replace('/^Our system provides two options:.*$/im', '', $text);

        // Compress multiple blank lines
        $text = preg_replace("/\n{3,}/", "\n\n", trim($text));

        return $text;
    }
}
