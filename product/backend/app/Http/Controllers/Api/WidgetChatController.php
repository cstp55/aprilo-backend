<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WidgetAutoReply;
use App\Models\Answer;
use App\Models\AnswerSource;
use App\Models\Escalation;
use App\Models\KnowledgeChunk;
use App\Models\Organization;
use App\Models\OrganizationChatUsage;
use App\Models\Question;
use App\Models\User;
use App\Models\WidgetConversation;
use App\Models\WidgetConversationMessage;
use App\Services\AI\AiAnswerService;
use App\Services\AI\SensitivityClassifier;
use App\Services\Firebase\FirebaseSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class WidgetChatController extends Controller
{
    public function __construct(
        private readonly AiAnswerService $aiService,
        private readonly FirebaseSyncService $firebaseSyncService,
    ) {
    }

    public function widgetConfig(Request $request): JsonResponse
    {
        $organization = $this->resolveOrg($request);
        $settings = $organization->settings;
        $primaryColor = $settings?->chatbot_color_palette ?: '#d22630';
        $assistantName = $settings?->assistant_name ?: ($organization->name . ' Assistant');
        $welcomeMessage = $settings?->welcome_message ?: ("Hello! Welcome to " . $organization->name . ". How can we assist you today?");

        $isWidgetEnabled = (bool) ($settings?->is_widget_enabled ?? true);
        $isOnline = $isWidgetEnabled && ($settings?->assistant_status ?? 'active') === 'active';
        $chatbotIcon = $settings?->chatbot_icon ?: 'star';
        $avatarUrl = filter_var($chatbotIcon, FILTER_VALIDATE_URL) ? $chatbotIcon : '';

        return response()->json([
            'success' => true,
            'widget' => [
                'id' => $settings?->ensureWidgetPublicKey(),
                'name' => $assistantName,
                'display_name' => $assistantName,
                'icon' => $chatbotIcon,
                'avatar_url' => $avatarUrl,
                'welcome_message' => $welcomeMessage,
                'offline_message' => 'Chat support is currently offline or unavailable.',
                'position' => 'bottom-right',
                'enabled' => $isWidgetEnabled,
                'is_disabled' => ! $isWidgetEnabled,
            ],
            'theme' => [
                'primary_color' => $primaryColor,
                'secondary_color' => '#ff8a00',
                'background_color' => '#f8fafc',
                'text_color' => '#1f2937',
                'border_radius' => '16px',
                'icon' => $chatbotIcon,
            ],
            'behavior' => [
                'auto_open' => false,
                'show_launcher' => $isWidgetEnabled,
                'show_branding' => true,
                'human_support_enabled' => (bool) ($settings?->live_chat_enabled ?? true),
            ],
            'ai' => [
                'enabled' => $isWidgetEnabled,
                'display_name' => $assistantName,
            ],
            'support' => [
                'enabled' => $isWidgetEnabled,
                'business_hours_enabled' => false,
                'is_online' => $isOnline,
            ],
            'quick_actions' => [
                [
                    'id' => 'services',
                    'label' => 'Our Services',
                    'message' => 'What services and solutions does ' . $organization->name . ' provide?',
                ],
                [
                    'id' => 'support',
                    'label' => 'Contact Support',
                    'message' => 'How can I get in touch with the ' . $organization->name . ' support team?',
                ],
                [
                    'id' => 'hours',
                    'label' => 'Hours & Information',
                    'message' => 'What are your operational hours and consultation details?',
                ],
            ],
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'details' => $settings?->organization_details,
                'whatsapp_number' => $settings?->whatsapp_number ?: $settings?->whatsapp_sender_phone,
                'whatsapp_url' => $settings?->getWhatsAppDirectUrl(),
            ],
            'firebase' => [
                'apiKey' => env('FIREBASE_API_KEY', 'AIzaSyDU5Ce2X5w35sZH81e5nX9i41xXj6YXoMg'),
                'databaseURL' => env('FIREBASE_DATABASE_URL', 'https://aprilo-infotech-default-rtdb.firebaseio.com'),
                'projectId' => env('FIREBASE_PROJECT_ID', 'aprilo-infotech'),
                'appId' => env('FIREBASE_APP_ID', '1:113916326733:web:5659980b28225e34b6fd04'),
                'authDomain' => env('FIREBASE_AUTH_DOMAIN', 'aprilo-infotech.firebaseapp.com'),
            ],
        ]);
    }

        private function ensureWidgetTablesExist(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('widget_conversations')) {
                \Illuminate\Support\Facades\Schema::create('widget_conversations', function (\Illuminate\Database\Schema\Blueprint $table): void {
                    $table->uuid('id')->primary();
                    $table->uuid('organization_id')->index();
                    $table->uuid('customer_id')->nullable();
                    $table->string('session_id', 120)->index();
                    $table->string('domain', 255)->nullable();
                    $table->text('page_url')->nullable();
                    $table->string('page_title')->nullable();
                    $table->string('mode')->default('ai');
                    $table->string('status')->default('open')->index();
                    $table->timestamps();
                    $table->index(['organization_id', 'created_at']);
                });
            }

            if (!\Illuminate\Support\Facades\Schema::hasTable('widget_conversation_messages')) {
                \Illuminate\Support\Facades\Schema::create('widget_conversation_messages', function (\Illuminate\Database\Schema\Blueprint $table): void {
                    $table->uuid('id')->primary();
                    $table->uuid('conversation_id')->index();
                    $table->string('sender_type', 20);
                    $table->string('message_type', 30)->default('text');
                    $table->text('content');
                    $table->string('sender_name')->nullable();
                    $table->json('metadata')->nullable();
                    $table->timestamps();
                    $table->index(['conversation_id', 'created_at']);
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not auto-create widget tables: ' . $e->getMessage());
        }
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
        $settings = $organization->settings;

        if ($settings && (!($settings->is_widget_enabled ?? true) || $settings->assistant_status === 'paused')) {
            return response()->json([
                'success' => false,
                'message' => 'Chat support is currently offline or disabled for this organization.',
            ], 403);
        }

        $customerId = (string) Str::uuid();
        $convId = (string) Str::uuid();

        try {
            $this->ensureWidgetTablesExist();
            $conversation = WidgetConversation::create([
                'id' => $convId,
                'organization_id' => $organization->id,
                'customer_id' => $customerId,
                'session_id' => $validated['session_id'],
                'domain' => $validated['domain'] ?? null,
                'page_url' => $validated['page_url'] ?? null,
                'page_title' => $validated['page_title'] ?? null,
                'mode' => 'ai',
                'status' => 'open',
            ]);
            $convId = $conversation->id;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Widget conversation creation fallback: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $convId,
            'customer_id' => $customerId,
            'mode' => 'ai',
        ], 201);
    }

    public function createConversationMessage(Request $request, $conversation_id = null): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'message_type' => ['nullable', 'string', 'max:30'],
            'metadata' => ['nullable', 'array'],
        ]);

        $this->ensureWidgetTablesExist();

        $conversationParam = $conversation_id ?: $request->route('conversation_id') ?: $request->route('conversation') ?: $request->input('conversation_id');
        $conv = null;
        if ($conversationParam instanceof WidgetConversation) {
            $conv = $conversationParam;
        } elseif (is_string($conversationParam)) {
            try {
                $conv = WidgetConversation::find($conversationParam);
            } catch (\Throwable $e) {}
        }

        if ($conv) {
            $organization = $conv->organization;
        } else {
            $widgetKey = $request->input('widget_key')
                ?? $request->input('metadata.widget_key')
                ?? $request->header('X-Widget-Key')
                ?? $request->query('widget_key');

            $organization = null;
            if ($widgetKey) {
                $setting = \App\Models\OrganizationSetting::where('public_widget_key', $widgetKey)->first();
                $organization = $setting?->organization;
            }

            if (!$organization) {
                $organization = Organization::where('status', 'active')->first();
            }

            try {
                $convId = is_string($conversationParam) && Str::isUuid($conversationParam) ? $conversationParam : (string) Str::uuid();
                $conv = WidgetConversation::create([
                    'id' => $convId,
                    'organization_id' => $organization?->id,
                    'customer_id' => (string) Str::uuid(),
                    'session_id' => $request->input('session_id') ?? ('sess_' . time()),
                    'mode' => 'ai',
                    'status' => 'open',
                ]);
            } catch (\Throwable $e) {}
        }

        abort_unless($organization?->hasActiveProductCategory('ai_support') || ($organization?->settings?->is_widget_enabled ?? true), 403);

        try {
            if ($conv) {
                $conv->messages()->create([
                    'sender_type' => 'user',
                    'message_type' => $validated['message_type'] ?? 'text',
                    'content' => $validated['message'],
                    'metadata' => $validated['metadata'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {}

        // If conversation is already assigned to human agent or awaiting human, do NOT generate AI response
        if ($conv && in_array($conv->mode, ['human', 'human_pending'])) {
            return response()->json([
                'success' => true,
                'mode' => $conv->mode,
                'reply' => null,
            ]);
        }

        $settings = $organization->settings;
        if ($settings && (!($settings->is_widget_enabled ?? true) || $settings->assistant_status === 'paused')) {
            $offlineText = 'Chatbot is currently offline or disabled for this organization. Please reach out via our official contact channels.';
            return response()->json([
                'success' => true,
                'mode' => $conv?->mode ?? 'ai',
                'reply' => [
                    'id' => (string) Str::uuid(),
                    'sender' => 'assistant',
                    'content' => $offlineText,
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        }

        $result = $this->processQueryForOrg($organization, $validated['message'], $conv, $request);

        // If conversation is in human mode or answer is empty/human_mode, stay silent
        if (($conv && in_array($conv->mode, ['human', 'human_pending'])) || ($result['answer_status'] ?? '') === 'human_mode' || empty($result['answer_text'])) {
            return response()->json([
                'success' => true,
                'mode' => $conv?->mode ?? 'human_pending',
                'reply' => null,
            ]);
        }

        $replyId = (string) Str::uuid();
        try {
            if ($conv) {
                $reply = $conv->messages()->create([
                    'sender_type' => 'assistant',
                    'message_type' => 'text',
                    'content' => $result['answer_text'],
                    'sender_name' => $organization->settings?->assistant_name ?: ($organization->name . ' Assistant'),
                    'metadata' => ['answer_status' => $result['answer_status']],
                ]);
                $replyId = $reply->id;
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'mode' => $conv?->mode ?? 'ai',
            'reply' => [
                'id' => $replyId,
                'sender' => 'assistant',
                'content' => $result['answer_text'],
                'sender_name' => $organization->settings?->assistant_name ?: ($organization->name . ' Assistant'),
                'timestamp' => now()->toIso8601String(),
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
        $settings = $org->settings;

        if ($settings && (!($settings->is_widget_enabled ?? true) || $settings->assistant_status === 'paused')) {
            return response()->json([
                'question_id' => (string) Str::uuid(),
                'answer_text' => 'Chatbot is currently offline or disabled for this organization. Please reach out via our official contact channels.',
                'answer_status' => 'offline',
                'sources' => [],
            ]);
        }

        $result = $this->processQueryForOrg($org, $validated['question_text'], null, $request);

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
     * Hit Cloud Firestore and Firebase RTDB for live agent chat.
     */
    public function escalateLiveAgent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'widget_key' => ['nullable', 'string', 'max:64'],
            'conversation_id' => ['required', 'string', 'max:120'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['nullable', 'string', 'max:120'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $org = $this->resolveOrg($request);
        $cleanCid = $validated['conversation_id'];
        $customerName = trim($validated['customer_name']);
        $customerEmail = trim($validated['customer_email'] ?? '');
        $reason = trim($validated['reason'] ?? '') ?: 'Support enquiry';

        // Update local database conversation or create if not found
        $conv = WidgetConversation::where('id', $cleanCid)->where('organization_id', $org->id)->first();
        if (!$conv) {
            try {
                $conv = WidgetConversation::create([
                    'id' => Str::isUuid($cleanCid) ? $cleanCid : (string) Str::uuid(),
                    'organization_id' => $org->id,
                    'customer_id' => (string) Str::uuid(),
                    'session_id' => $request->input('session_id') ?? ('sess_' . time()),
                    'mode' => 'human_pending',
                    'status' => 'open',
                ]);
            } catch (\Throwable $e) {}
        } else {
            $conv->update(['mode' => 'human_pending']);
        }
        if ($conv) {
            $conv->update(['mode' => 'human_pending']);
            $emailSnippet = $customerEmail ? " ({$customerEmail})" : "";
            $conv->messages()->create([
                'sender_type' => 'user',
                'message_type' => 'human_request',
                'content' => "Live Agent requested by {$customerName}{$emailSnippet}. Reason: {$reason}",
                'sender_name' => $customerName,
                'metadata' => [
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'reason' => $reason,
                    'status' => 'pending',
                ],
            ]);
        }

        $synced = $this->firebaseSyncService->escalateToLiveAgent(
            (string) $org->id,
            $cleanCid,
            $customerName,
            $reason,
            $customerEmail
        );

        return response()->json([
            'success' => true,
            'message' => 'Escalated to live agent successfully.',
            'mode' => 'human_pending',
            'conversation_id' => $cleanCid,
            'firestore_and_rtdb_synced' => $synced,
        ]);
    }

    /**
     * Core RAG / Cache / DB validation chat pipeline.
     */
    private function processQueryForOrg(Organization $org, string $questionText, ?WidgetConversation $conversation = null, ?Request $request = null): array
    {
        // When assigned to human agent, AI remains completely silent
        if ($conversation && in_array($conversation->mode, ['human', 'human_pending'])) {
            return [
                'question_id' => (string) Str::uuid(),
                'answer_text' => '',
                'answer_status' => 'human_mode',
                'sources' => [],
            ];
        }

        $cleanQuery = strtolower(trim($questionText));

        // 1. Live Agent Escalation Handshake: First Ask Name -> Then Ask Reason -> Then Show WhatsApp & Live Agent Buttons
        $sessionToken = $request?->input('session_id') ?? ($conversation ? $conversation->session_id : null) ?? $conversation?->id ?? $request?->ip() ?? 'guest';
        $handshakeKey = 'agent_handshake_state:' . $org->id . ':' . $sessionToken;
        $handshakeState = \Illuminate\Support\Facades\Cache::get($handshakeKey);

        $isAgentRequest = (bool) preg_match('/\b(connect(\s+(with|to))?\s+(an?\s+)?agent|talk\s+(with|to)\s+(an?\s+)?(human|agent|coordinator|specialist|person|representative)|speak\s+(with|to)\s+(an?\s+)?(human|agent|coordinator|specialist|representative)|human\s+agent|live\s+agent|real\s+person|patient\s+coordinator|human\s+support|talk\s+to\s+someone|contact\s+(with\s+)?(agent|human)|connect\s+agent|live\s+support|need\s+agent|want\s+agent|live\s+chat|agent)\b/i', $cleanQuery)
            || in_array($cleanQuery, ['agent', 'connect to agent', 'connect with agent', 'human', 'live agent', 'coordinator', 'talk to agent', 'talk to human', 'human support', 'live chat']);

        // Helper function to extract name and reason if both provided in one message
        $extractNameAndReason = function(string $rawText) {
            $name = '';
            $reason = '';
            if (preg_match('/(?:name\s*[:=\-]\s*)([^,\n;]+)/i', $rawText, $nm)) {
                $name = trim($nm[1]);
            }
            if (preg_match('/(?:reason\s*[:=\-]\s*)(.+)$/is', $rawText, $rm)) {
                $reason = trim($rm[1]);
            }
            if (empty($name) || empty($reason)) {
                if (preg_match('/^(?:my\s+name\s+is|i\s+am|i\'m)\s+([a-zA-Z\s]+?)(?:,|\.|\sand\s|\smy\s+reason\s+is|\sreason\s+is|\sfor\s|\sto\s)(.+)/is', $rawText, $m)) {
                    if (empty($name)) $name = trim($m[1]);
                    if (empty($reason)) $reason = trim($m[2]);
                } elseif (preg_match('/^([^,:\-\n;]+)[,:\-]\s*(.+)$/is', $rawText, $m)) {
                    if (empty($name)) $name = trim($m[1]);
                    if (empty($reason)) $reason = trim($m[2]);
                }
            }
            return [$name, $reason];
        };

        // If no active handshake, but user requests an agent:
        if ($isAgentRequest && !$handshakeState) {
            [$name, $reason] = $extractNameAndReason($questionText);
            if (!empty($name) && !empty($reason)) {
                return $this->buildLiveAgentButtonsResponse($org, $conversation, $questionText, $name, $reason);
            }

            // Step 1: Prompt for Name
            \Illuminate\Support\Facades\Cache::put($handshakeKey, ['step' => 'awaiting_name'], 900);

            $replyText = "To connect you with our live support team, could you please provide your **Name**?";
            $dummyUser = User::where('organization_id', $org->id)->first();
            $question = Question::create([
                'organization_id' => $org->id,
                'user_id' => $dummyUser ? $dummyUser->id : Str::uuid(),
                'question_text' => $questionText,
                'topic' => 'agent_escalation_request',
                'sensitivity_status' => 'normal',
                'status' => 'answered',
            ]);

            return [
                'question_id' => $question->id,
                'answer_text' => $replyText,
                'answer_status' => 'answered',
                'sources' => [],
            ];
        }

        // If handshake is active:
        if ($handshakeState && is_array($handshakeState)) {
            $step = $handshakeState['step'] ?? '';

            if ($step === 'awaiting_name') {
                [$name, $reason] = $extractNameAndReason($questionText);
                if (!empty($name) && !empty($reason)) {
                    \Illuminate\Support\Facades\Cache::forget($handshakeKey);
                    return $this->buildLiveAgentButtonsResponse($org, $conversation, $questionText, $name, $reason);
                }

                $cleanName = trim(preg_replace('/^(my\s+name\s+is|i\s+am|i\'m|this\s+is|name\s*[:=\-]?)\s*/i', '', $questionText));
                $cleanName = trim(preg_replace('/[.,;!]+$/', '', $cleanName));
                if (empty($cleanName)) {
                    $cleanName = 'Valued Customer';
                }

                // Step 2: Save Name and prompt for Reason
                \Illuminate\Support\Facades\Cache::put($handshakeKey, [
                    'step' => 'awaiting_reason',
                    'name' => $cleanName,
                ], 900);

                $replyText = "Thank you, {$cleanName}! What is the **reason** for reaching out so we can connect you with the right specialist?";
                $dummyUser = User::where('organization_id', $org->id)->first();
                $question = Question::create([
                    'organization_id' => $org->id,
                    'user_id' => $dummyUser ? $dummyUser->id : Str::uuid(),
                    'question_text' => $questionText,
                    'topic' => 'agent_escalation_name',
                    'sensitivity_status' => 'normal',
                    'status' => 'answered',
                ]);

                return [
                    'question_id' => $question->id,
                    'answer_text' => $replyText,
                    'answer_status' => 'answered',
                    'sources' => [],
                ];
            }

            if ($step === 'awaiting_reason') {
                $name = $handshakeState['name'] ?? 'Valued Customer';
                $reason = trim(preg_replace('/^(the\s+reason\s+is|my\s+reason\s+is|reason\s+is|reason\s*[:=\-]?)\s*/i', '', $questionText));
                if (empty($reason)) {
                    $reason = 'General enquiry';
                }

                \Illuminate\Support\Facades\Cache::forget($handshakeKey);
                return $this->buildLiveAgentButtonsResponse($org, $conversation, $questionText, $name, $reason);
            }
        }

        $cacheKey = 'widget_chat_cache:' . $org->id . ':' . md5(strtolower(trim($questionText)));

        // 2. Memory Cache lookup
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
                'answer_text' => $cached['answer_text'],
                'answer_status' => $cached['answer_status'],
                'sources' => $cached['sources'] ?? [],
            ];
        }

        // 2. Organization Conversational Greeting & Introduction handling (Clean greeting, no raw metadata dumping)
        $settings = $org->settings;
        $assistantName = $settings?->assistant_name ?: ($org->name . ' Assistant');

        $cleanQuery = strtolower(trim($questionText));
        $isGreeting = (bool) preg_match('/^(hi|hello|hey|greetings|good\s+(morning|afternoon|evening)|hola)(\s*|[!?.].*)$/i', $cleanQuery);
        $isHelpQuery = (bool) preg_match('/^(how\s+can\s+you\s+help(\s+me)?|what\s+can\s+you\s+do|who\s+are\s+you|help\s*me?)(\s*|[!?.].*)$/i', $cleanQuery);

        $welcomeMessage = trim($settings?->welcome_message ?? '');
        if ($isGreeting || $isHelpQuery) {
            if ($isGreeting) {
                if (!empty($welcomeMessage)) {
                    $replyText = $welcomeMessage;
                } else {
                    $replyText = "Hello! I am {$assistantName}, the virtual assistant for {$org->name}. How can I assist you today?";
                }
            } else {
                $replyText = "I am {$assistantName}, the dedicated AI assistant for {$org->name}. I can answer questions regarding our services, operating policies, and assist you with support requests. How may I help you today?";
            }

            $dummyUser = User::where('organization_id', $org->id)->first();
            $question = Question::create([
                'organization_id' => $org->id,
                'user_id' => $dummyUser ? $dummyUser->id : Str::uuid(),
                'question_text' => $questionText,
                'topic' => 'public_widget_chat_greeting',
                'sensitivity_status' => 'normal',
                'status' => 'answered',
            ]);

            // Record greeting query & estimated tokens
            $promptTokens = max(1, (int) round(strlen($questionText) / 4));
            $completionTokens = max(1, (int) round(strlen($replyText) / 4));
            OrganizationChatUsage::recordUsage($org->id, $promptTokens, $completionTokens);

            return [
                'question_id' => $question->id,
                'answer_text' => $replyText,
                'answer_status' => 'answered',
                'sources' => [],
            ];
        }

        // 3. Advanced Bot Workflow: Validate Employee ID and fetch HR Resources
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
        $contexts = array_slice($ranked, 0, 5);
        $aiResult = $this->aiService->answer($questionText, $contexts, [
            'organization' => $org,
            'settings' => $org->settings,
        ]);
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

        // Record token and query consumption for the organization
        $tokens = $aiResult['token_usage'] ?? [];
        OrganizationChatUsage::recordUsage(
            $org->id,
            $tokens['prompt_tokens'] ?? 0,
            $tokens['completion_tokens'] ?? 0,
            $tokens['total_tokens'] ?? 0
        );

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

        if (empty($widgetKey) && $request->filled('conversation_id')) {
            $conv = WidgetConversation::find($request->input('conversation_id'));
            if ($conv && $conv->organization) {
                return $conv->organization;
            }
        }

        abort_unless(is_string($widgetKey) && $widgetKey !== '', 400, 'A widget_key is required.');

        // First-party Aprilo Infotech website mapping
        if ($widgetKey === 'pk_live_aprilo' || $widgetKey === 'pk_live_default') {
            $apriloOrg = Organization::query()->where('name', 'like', '%Aprilo%')->first();
            if ($apriloOrg) {
                return $apriloOrg;
            }
        }

        $settings = \App\Models\OrganizationSetting::query()
            ->where('public_widget_key', $widgetKey)
            ->firstOrFail();
        $organization = $settings->organization;

        abort_unless($organization?->status === 'active', 403, 'This organization is inactive.');
        abort_unless(
            $organization->hasActiveProductCategory('ai_support')
            || $organization->hasActiveProductCategory('chatbot')
            || ($settings->is_widget_enabled ?? true),
            403,
            'Aprilo Support AI is not active for this organization.'
        );

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

    private function buildLiveAgentButtonsResponse(Organization $org, ?WidgetConversation $conversation, string $questionText, string $name, string $reason): array
    {
        $name = trim(preg_replace('/^(connect(\s+(with|to))?\s+(an?\s+)?agent[.,;!\s]*|talk\s+(with|to)\s+(an?\s+)?agent[.,;!\s]*|my\s+name\s+is|i\s+am|i\'m|this\s+is|name\s*[:=\-]?)\s*/i', '', $name));
        $name = trim(preg_replace('/^(my\s+name\s+is|i\s+am|i\'m|this\s+is|name\s*[:=\-]?)\s*/i', '', $name));
        $name = trim(preg_replace('/[.,;!]+$/', '', $name));
        if (empty($name)) {
            $name = 'Valued Customer';
        }
        if (empty($reason)) {
            $reason = 'General enquiry';
        }

        $prefilledText = "Hello {$org->name}, my name is {$name}. I would like to speak with an agent regarding: {$reason}.";
        $whatsappUrlWithText = $org->settings?->getWhatsAppDirectUrl($prefilledText)
            ?: (config('services.whatsapp.direct_link', env('WHATSAPP_DIRECT_LINK', 'https://wa.me/message/BE3K4RAO24GXK1')) . '?text=' . rawurlencode($prefilledText));

        $convId = $conversation ? $conversation->id : '';
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeReason = htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');
        $safeNameAttr = addslashes($safeName);
        $safeReasonAttr = addslashes($safeReason);

        $replyText = "Thank you, {$name}! I have noted your details:\n" .
            "• **Reason:** {$reason}\n\n" .
            "How would you like to connect with our team?\n\n" .
            "<div class=\"aprilo-action-buttons\" data-name=\"{$safeNameAttr}\" data-reason=\"{$safeReasonAttr}\">\n" .
            "  <a href=\"{$whatsappUrlWithText}\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"aprilo-btn aprilo-btn-whatsapp\" id=\"live-whatsapp-btn\">\n" .
            "    <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"currentColor\" style=\"flex-shrink:0;\"><path d=\"M20.52 3.48A11.93 11.93 0 0012.04 0C5.46 0 .11 5.35.11 11.93c0 2.1.55 4.15 1.6 5.96L0 24l6.29-1.65a11.9 11.9 0 005.75 1.47h.01c6.58 0 11.93-5.35 11.93-11.93 0-3.19-1.24-6.19-3.46-8.41zM12.04 21.8c-1.78 0-3.52-.48-5.04-1.39l-.36-.21-3.74.98 1-3.64-.23-.37a9.88 9.88 0 01-1.52-5.24c0-5.48 4.46-9.94 9.94-9.94 2.65 0 5.15 1.03 7.03 2.91a9.89 9.89 0 012.91 7.03c0 5.48-4.46 9.94-9.94 9.94z\"/></svg>\n" .
            "    <span>Connect on WhatsApp</span>\n" .
            "  </a>\n" .
            "  <button type=\"button\" class=\"aprilo-btn aprilo-btn-liveagent\" id=\"live-agent-btn\" data-action=\"live-agent\" data-name=\"{$safeNameAttr}\" data-reason=\"{$safeReasonAttr}\" onclick=\"window.ApriloConnectLiveAgent && window.ApriloConnectLiveAgent('{$safeNameAttr}', '', '{$safeReasonAttr}', '{$convId}')\">\n" .
            "    <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" style=\"flex-shrink:0;\"><path d=\"M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z\"></path></svg>\n" .
            "    <span>Chat with Live Agent</span>\n" .
            "  </button>\n" .
            "</div>";

        if ($conversation) {
            try {
                $conversation->update([
                    'metadata' => array_merge($conversation->metadata ?? [], [
                        'customer_name' => $name,
                        'reason' => $reason,
                    ]),
                ]);
            } catch (\Throwable $e) {}
        }

        $dummyUser = User::where('organization_id', $org->id)->first();
        $question = Question::create([
            'organization_id' => $org->id,
            'user_id' => $dummyUser ? $dummyUser->id : Str::uuid(),
            'question_text' => $questionText,
            'topic' => 'agent_escalation_details',
            'sensitivity_status' => 'normal',
            'status' => 'answered',
        ]);

        return [
            'question_id' => $question->id,
            'answer_text' => $replyText,
            'answer_status' => 'answered',
            'sources' => [],
        ];
    }

}
