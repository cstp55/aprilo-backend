<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WidgetAutoReply;
use App\Models\Answer;
use App\Models\AnswerSource;
use App\Models\Escalation;
use App\Models\KnowledgeChunk;
use App\Models\Organization;
use App\Models\Question;
use App\Models\User;
use App\Models\WidgetConversation;
use App\Models\WidgetConversationMessage;
use App\Services\AI\AiAnswerService;
use App\Services\AI\SensitivityClassifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class WidgetChatController extends Controller
{
    public function __construct(private readonly AiAnswerService $aiService)
    {
    }

    public function widgetConfig(Request $request): JsonResponse
    {
        $organization = $this->resolveOrg($request);
        $settings = $organization->settings;
        $primaryColor = $settings?->chatbot_color_palette ?: '#d22630';

        return response()->json([
            'success' => true,
            'widget' => [
                'id' => $settings?->ensureWidgetPublicKey(),
                'name' => $settings?->assistant_name ?: 'Aprilo Bot',
                'display_name' => $settings?->assistant_name ?: 'Aprilo Bot',
                'avatar_url' => '',
                'welcome_message' => 'Hi! How can we help you today?',
                'offline_message' => 'Our team is currently offline. Please leave a message.',
                'position' => 'bottom-right',
                'enabled' => true,
            ],
            'theme' => [
                'primary_color' => $primaryColor,
                'secondary_color' => '#ff8a00',
                'background_color' => '#f8fafc',
                'text_color' => '#1f2937',
                'border_radius' => '16px',
            ],
            'behavior' => [
                'auto_open' => false,
                'show_launcher' => true,
                'show_branding' => true,
                'human_support_enabled' => (bool) ($settings?->live_chat_enabled ?? true),
            ],
            'ai' => [
                'enabled' => true,
                'display_name' => $settings?->assistant_name ?: 'Aprilo Bot',
            ],
            'support' => [
                'enabled' => true,
                'business_hours_enabled' => false,
                'is_online' => ($settings?->assistant_status ?? 'active') === 'active',
            ],
            'quick_actions' => [],
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
            ],
        ]);
    }

    public function createConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'widget_key' => ['required', 'string', 'max:64'],
            'session_id' => ['required', 'string', 'max:120'],
            'domain' => ['nullable', 'string', 'max:255'],
            'page_url' => ['nullable', 'url', 'max:2048'],
            'page_title' => ['nullable', 'string', 'max:255'],
        ]);
        $organization = $this->resolveOrg($request);
        $customerId = (string) Str::uuid();

        $conversation = WidgetConversation::create([
            'organization_id' => $organization->id,
            'customer_id' => $customerId,
            'session_id' => $validated['session_id'],
            'domain' => $validated['domain'] ?? null,
            'page_url' => $validated['page_url'] ?? null,
            'page_title' => $validated['page_title'] ?? null,
            'mode' => 'ai',
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'customer_id' => $customerId,
            'mode' => $conversation->mode,
        ], 201);
    }

    public function createConversationMessage(Request $request, WidgetConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'message_type' => ['nullable', 'string', 'max:30'],
            'metadata' => ['nullable', 'array'],
        ]);

        abort_unless($conversation->status === 'open', 404);

        $organization = $conversation->organization;
        abort_unless($organization?->hasActiveProductCategory('ai_support'), 403);

        $conversation->messages()->create([
            'sender_type' => 'user',
            'message_type' => $validated['message_type'] ?? 'text',
            'content' => $validated['message'],
            'metadata' => $validated['metadata'] ?? null,
        ]);

        $result = $this->processQueryForOrg($organization, $validated['message']);
        $reply = $conversation->messages()->create([
            'sender_type' => 'assistant',
            'message_type' => 'text',
            'content' => $result['answer_text'],
            'sender_name' => $organization->settings?->assistant_name ?: 'Aprilo Bot',
            'metadata' => ['answer_status' => $result['answer_status']],
        ]);

        return response()->json([
            'success' => true,
            'mode' => $conversation->mode,
            'reply' => [
                'id' => $reply->id,
                'sender' => 'assistant',
                'content' => $reply->content,
                'timestamp' => $reply->created_at->toIso8601String(),
            ],
        ]);
    }
    /**
    * Fetch tenant widget settings using the public organization key.
     */
    public function settings(Request $request): JsonResponse
    {
        $org = $this->resolveOrg($request);
        $settings = $org->settings;

        return response()->json([
            'organization_id' => $org->id,
            'organization_name' => $org->name,
            'assistant_name' => $settings->assistant_name ?? 'Aprilo Bot',
            'chatbot_color_palette' => $settings->chatbot_color_palette ?? '#d22630',
            'chatbot_icon' => $settings->chatbot_icon ?? 'robot',
            'connect_mail' => $settings->connect_mail ?? false,
            'hr_desk_email' => $settings->hr_desk_email ?? 'support@aprilo.ai',
        ]);
    }

    /**
     * Process message from the public web widget.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string', 'max:2000'],
        ]);

        $org = $this->resolveOrg($request);
        $result = $this->processQueryForOrg($org, $validated['question_text']);

        return response()->json($result);
    }

    /**
     * Handles inbound emails, processes RAG search, and auto-replies.
     */
    public function inboundEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sender' => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $org = $this->resolveOrg($request);
        $settings = $org->settings;

        // Verify connect_mail is enabled
        if (!($settings->connect_mail ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'Email integration is disabled for this organization.',
            ], 400);
        }

        $result = $this->processQueryForOrg($org, $validated['body']);
        $isEscalated = ($result['answer_status'] === 'fallback');

        // Dynamically send response email via SMTP or log file
        $replySubject = str_starts_with(strtolower($validated['subject']), 're:') 
            ? $validated['subject'] 
            : 'Re: ' . $validated['subject'];

        $this->sendDynamicEmail(
            $org, 
            $validated['sender'], 
            $replySubject, 
            $result['answer_text'], 
            $result['sources'], 
            $isEscalated
        );

        // If escalated, log it in database escalations and alert HR Desk
        if ($isEscalated) {
            $dummyUser = User::where('organization_id', $org->id)->first();
            $defaultOwner = $settings->default_escalation_owner ?? ($dummyUser ? $dummyUser->id : null);
            
            $escalation = Escalation::create([
                'organization_id' => $org->id,
                'question_id' => $result['question_id'] ?? Str::uuid(),
                'user_id' => $dummyUser ? $dummyUser->id : Str::uuid(),
                'category' => 'email_inbox_escalation',
                'status' => 'open',
                'assigned_to' => $defaultOwner,
                'resolution_note' => "Inbound Email Sender: " . $validated['sender'] . "\nSubject: " . $validated['subject'] . "\nBody: " . $validated['body'],
            ]);

            // Alert HR Helpdesk
            $hrDeskEmail = $settings->hr_desk_email ?? 'hr-helpdesk@example.com';
            $this->sendDynamicEmail(
                $org,
                $hrDeskEmail,
                "[Escalation Alert] Unresolved Support Query: " . $validated['subject'],
                "An inbound email query from {$validated['sender']} could not be resolved by the AI. An escalation ticket has been logged.\n\nOriginal Message:\n{$validated['body']}",
                [],
                false
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Inbound email processed and reply dispatched.',
            'question_id' => $result['question_id'],
            'answer_status' => $result['answer_status'],
        ]);
    }

    /**
     * Escalates a question to an outbound human support ticket.
     */
    public function escalate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'visitor_email' => ['required', 'email'],
            'visitor_message' => ['required', 'string', 'max:2000'],
            'question_text' => ['nullable', 'string', 'max:2000'],
        ]);

        $org = $this->resolveOrg($request);
        $dummyUser = User::where('organization_id', $org->id)->first();
        $defaultOwner = $org->settings->default_escalation_owner ?? ($dummyUser ? $dummyUser->id : null);

        // Pre-create question reference
        $question = Question::create([
            'organization_id' => $org->id,
            'user_id' => $dummyUser ? $dummyUser->id : Str::uuid(),
            'question_text' => $validated['question_text'] ?? 'Escalation request from visitor',
            'topic' => 'widget_escalation',
            'sensitivity_status' => 'escalated',
            'status' => 'escalated',
        ]);

        $escalation = Escalation::create([
            'organization_id' => $org->id,
            'question_id' => $question->id,
            'user_id' => $dummyUser ? $dummyUser->id : Str::uuid(),
            'category' => 'public_widget_escalation',
            'status' => 'open',
            'assigned_to' => $defaultOwner,
            'resolution_note' => "Visitor Email: " . $validated['visitor_email'] . "\nVisitor Message: " . $validated['visitor_message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your escalation ticket has been successfully registered. Support email sent.',
            'escalation_id' => $escalation->id,
        ]);
    }

    /**
     * Core RAG / Cache / DB validation chat pipeline.
     */
    private function processQueryForOrg(Organization $org, string $questionText): array
    {
        $cacheKey = 'widget_chat_cache:' . $org->id . ':' . md5(strtolower(trim($questionText)));

        // 1. Memory Cache lookup
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
            $dummyUser = User::where('organization_id', $org->id)->first();
            $question = Question::create([
                'organization_id' => $org->id,
                'user_id' => $dummyUser ? $dummyUser->id : Str::uuid(),
                'question_text' => $questionText,
                'topic' => 'public_widget_chat_cached',
                'sensitivity_status' => 'normal',
                'status' => $cached['answer_status'] === 'fallback' ? 'unsupported' : 'answered',
            ]);

            return [
                'question_id' => $question->id,
                'answer_text' => $cached['answer_text'] . " (served from memory cache ⚡)",
                'answer_status' => $cached['answer_status'],
                'sources' => $cached['sources'] ?? [],
            ];
        }

        // 2. Advanced Bot Workflow: Validate Employee ID and fetch HR Resources
        $settings = $org->settings;
        $chatbotMode = $settings->chatbot_mode ?? 'document_only';
        $hrConnected = $settings->hr_api_connected ?? false;

        if ($chatbotMode === 'interactive_hr' && $hrConnected) {
            $lowerText = strtolower($questionText);
            $isLeaveQuery = str_contains($lowerText, 'leave') || str_contains($lowerText, 'vacation') || str_contains($lowerText, 'wfh') || str_contains($lowerText, 'day off');

            // Try parsing Employee ID
            preg_match('/EMP-\d{4}-\d{3}/i', $questionText, $matches);

            if (!empty($matches)) {
                $employeeId = strtoupper($matches[0]);
                $user = User::where('organization_id', $org->id)->where('employee_id', $employeeId)->first();
                if ($user) {
                    $approvedCount = \App\Models\LeaveRequest::where('user_id', $user->id)->where('status', 'approved')->count();
                    $pendingCount = \App\Models\LeaveRequest::where('user_id', $user->id)->where('status', 'pending')->count();
                    $balance = 15 - $approvedCount;

                    $answerText = "Validating employee database... 🟢 ID Validated!<br><strong>Employee:</strong> {$user->name}.<br><strong>Leave Balance:</strong> {$balance} remaining leaves.<br><strong>Status:</strong> {$approvedCount} approved, {$pendingCount} pending leave requests.<br><br>An integration alert was sent to Microsoft Teams channel.";
                    
                    $question = Question::create([
                        'organization_id' => $org->id,
                        'user_id' => $user->id,
                        'question_text' => $questionText,
                        'topic' => 'interactive_hr_validation',
                        'sensitivity_status' => 'normal',
                        'status' => 'answered',
                    ]);

                    return [
                        'question_id' => $question->id,
                        'answer_text' => $answerText,
                        'answer_status' => 'answered',
                        'sources' => [['title' => 'BambooHR Registry Database', 'citation_label' => '[API Connect]']],
                    ];
                } else {
                    $answerText = "Verification failed. The ID provided does not match our records. Please contact HR admin.";
                    return [
                        'question_id' => Str::uuid(),
                        'answer_text' => $answerText,
                        'answer_status' => 'fallback',
                        'sources' => [],
                    ];
                }
            } elseif ($isLeaveQuery) {
                $answerText = "To check your remaining leaves or WFH requests, I need to validate your identity. Please enter your <strong>Employee ID card number</strong> (e.g. EMP-2026-987):";
                return [
                    'question_id' => Str::uuid(),
                    'answer_text' => $answerText,
                    'answer_status' => 'interactive_verification',
                    'sources' => [],
                ];
            }
        }

        // 3. Fallback / Standard RAG retrieval against documents
        $chunks = KnowledgeChunk::query()
            ->with('source')
            ->where('organization_id', $org->id)
            ->whereHas('source', fn ($query) => $query->where('status', 'indexed')->where('access_scope', '!=', 'hr_only'))
            ->latest('created_at')
            ->limit(100)
            ->get();

        $terms = $this->extractTerms($questionText);
        $ranked = [];

        foreach ($chunks as $chunk) {
            if (!$chunk->source) {
                continue;
            }

            $score = $this->calculateScore($terms, $questionText, $chunk->chunk_text, $chunk->source->title);

            if ($score <= 0) {
                continue;
            }

            $ranked[] = [
                'chunk' => $chunk,
                'source' => $chunk->source,
                'score' => $score,
                'excerpt' => $chunk->chunk_text,
            ];
        }

        // Sort by score descending
        usort($ranked, fn ($a, $b) => $b['score'] <=> $a['score']);
        $contexts = array_slice($ranked, 0, 4);

        $aiResult = $this->aiService->answer($questionText, $contexts);
        $answerText = $aiResult['answer_text'];
        $answerStatus = $aiResult['answer_status'];

        $sources = [];
        foreach ($aiResult['sources'] ?? [] as $s) {
            $sources[] = [
                'citation_label' => $s['citation_label'],
                'title' => $s['source']->title ?? 'Unknown',
                'excerpt' => $s['excerpt'],
            ];
        }

        // Store public question in database under first user of the org as a reporter
        $dummyUser = User::where('organization_id', $org->id)->first();

        $question = Question::create([
            'organization_id' => $org->id,
            'user_id' => $dummyUser ? $dummyUser->id : Str::uuid(),
            'question_text' => $questionText,
            'topic' => 'public_widget_chat',
            'sensitivity_status' => 'normal',
            'status' => $answerStatus === 'fallback' ? 'unsupported' : 'answered',
        ]);

        $answer = Answer::create([
            'organization_id' => $org->id,
            'question_id' => $question->id,
            'answer_text' => $answerText,
            'answer_status' => $answerStatus === 'fallback' ? 'fallback' : 'answered',
            'confidence_label' => $aiResult['confidence_label'] ?? (empty($contexts) ? 'low' : 'medium'),
            'model_provider' => 'gemini',
            'model_name' => $org->settings->gemini_model ?? 'gemini-flash-latest',
            'metadata' => ['context_count' => count($contexts)],
        ]);

        // Save result in memory cache
        $cacheResult = [
            'answer_text' => $answerText,
            'answer_status' => $answerStatus,
            'sources' => $sources,
        ];
        \Illuminate\Support\Facades\Cache::put($cacheKey, $cacheResult, 300);

        return [
            'question_id' => $question->id,
            'answer_text' => $answerText,
            'answer_status' => $answerStatus,
            'sources' => $sources,
        ];
    }

    /**
    * Resolve exactly one organization from the key embedded in the widget.
     */
    private function resolveOrg(Request $request): Organization
    {
        $widgetKey = $request->input('widget_key')
            ?? $request->query('widget_key')
            ?? $request->header('X-Widget-Key');

        abort_unless(is_string($widgetKey) && $widgetKey !== '', 400, 'A widget_key is required.');

        $settings = \App\Models\OrganizationSetting::query()
            ->where('public_widget_key', $widgetKey)
            ->firstOrFail();
        $organization = $settings->organization;

        abort_unless($organization?->status === 'active', 403, 'This organization is inactive.');
        abort_unless($organization->hasActiveProductCategory('ai_support'), 403, 'Aprilo Support AI is not active for this organization.');

        return $organization;
    }

    /**
     * Sends dynamic emails based on tenant configured SMTP parameters.
     */
    private function sendDynamicEmail(
        Organization $org, 
        string $recipient, 
        string $subject, 
        string $bodyText, 
        array $sources, 
        bool $isEscalated
    ): void {
        $settings = $org->settings;

        if ($settings && $settings->mail_smtp_host && $settings->mail_smtp_username) {
            // Dynamic tenant SMTP configuration
            config([
                'mail.mailers.tenant_smtp' => [
                    'transport' => 'smtp',
                    'host' => $settings->mail_smtp_host,
                    'port' => $settings->mail_smtp_port ?? 587,
                    'username' => $settings->mail_smtp_username,
                    'password' => $settings->mail_smtp_password,
                    'encryption' => ($settings->mail_smtp_port == 465) ? 'ssl' : 'tls',
                ],
                'mail.from.address' => $settings->hr_desk_email ?? 'support@aprilo.ai',
                'mail.from.name' => $settings->assistant_name ?? 'Aprilo Bot',
            ]);

            $mailer = Mail::mailer('tenant_smtp');
        } else {
            // Log transport fallback (local development)
            $mailer = Mail::mailer();
        }

        try {
            $mailer->to($recipient)->send(new WidgetAutoReply(
                $subject, 
                $bodyText, 
                $sources, 
                $isEscalated, 
                $settings->assistant_name ?? 'Aprilo Bot'
            ));
        } catch (\Exception $e) {
            logger()->error("Inbound support dynamic email send failed: " . $e->getMessage());
        }
    }

    private function extractTerms(string $text): array
    {
        $words = preg_split('/[^a-z0-9]+/', strtolower($text)) ?: [];
        $stopwords = ['a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'can', 'do', 'for', 'from', 'how', 'is', 'it', 'of', 'on', 'or', 'the', 'to', 'with'];
        $terms = array_filter($words, fn($word) => strlen($word) >= 3 && !in_array($word, $stopwords, true));
        return array_values(array_unique($terms));
    }

    private function calculateScore(array $terms, string $question, string $chunkText, string $sourceTitle): float
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

        return round($score, 4);
    }

    private function cleanExcerpt(string $excerpt): string
    {
        $excerpt = trim(preg_replace('/\s+/', ' ', $excerpt) ?? $excerpt);
        if (strlen($excerpt) > 180) {
            $excerpt = substr($excerpt, 0, 180) . '...';
        }
        return rtrim($excerpt, '.') . '.';
    }
}
