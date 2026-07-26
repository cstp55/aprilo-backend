<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Escalation;
use App\Models\OrganizationSetting;
use App\Models\Question;
use App\Models\User;
use App\Services\AI\AiAnswerService;
use App\Services\AI\SensitivityClassifier;
use App\Services\Knowledge\RetrievalService;
use App\Services\Teams\TeamsIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeamsBotController extends Controller
{
    protected TeamsIntegrationService $teamsService;

    public function __construct(TeamsIntegrationService $teamsService)
    {
        $this->teamsService = $teamsService;
    }

    /**
     * Entry point for Microsoft Teams Bot activities webhook.
     */
    public function handleMessage(
        Request $request,
        SensitivityClassifier $classifier,
        RetrievalService $retrieval,
        AiAnswerService $answers
    ): JsonResponse {
        $activity = $request->all();

        // Log the incoming Teams activity payload for debugging
        Log::info("Teams Bot incoming activity: " . json_encode($activity));

        $activityType = $activity['type'] ?? '';

        if ($activityType !== 'message') {
            return response()->json(['status' => 'ignored_type']);
        }

        // 1. Check if this is an Adaptive Card submission (from an HR representative)
        if (isset($activity['value']) && is_array($activity['value'])) {
            $submitData = $activity['value'];
            if (($submitData['action'] ?? '') === 'escalation_reply') {
                return $this->handleEscalationReply($activity, $submitData);
            }
        }

        // 2. Identify the user (Employee or HR Representative)
        $teamsUserId = $activity['from']['id'] ?? null;
        $user = null;

        if ($teamsUserId) {
            $user = User::where('teams_user_id', $teamsUserId)->first();
        }

        if (!$user) {
            // Fallback: match by email in the Name field or principal name
            // Often Microsoft Teams places the user principal name/email in the name or user ID properties
            $emailCandidate = $activity['from']['name'] ?? '';
            if (filter_var($emailCandidate, FILTER_VALIDATE_EMAIL)) {
                $user = User::where('email', $emailCandidate)->first();
            } else {
                // Secondary check: look up by UPN/Email in other custom attributes
                // We also check 'aadObjectId' to see if we can match any mapping in the future.
            }

            if ($user && $teamsUserId) {
                // Update and link Teams mapping
                $user->update([
                    'teams_user_id' => $teamsUserId,
                    'teams_conversation_id' => $activity['conversation']['id'] ?? null,
                    'teams_service_url' => $activity['serviceUrl'] ?? null,
                ]);
            }
        }

        if (!$user) {
            Log::warning("Unidentified Teams user messaged bot: UName: " . ($activity['from']['name'] ?? 'Unknown') . " UID: " . $teamsUserId);
            $this->sendBotReply($activity, "I couldn't locate your Aprilo account. Please ask your HR administrator to configure your Microsoft Teams User ID in the Aprilo Console.");
            return response()->json(['status' => 'user_not_found']);
        }

        // Cache/Update conversation details for proactive mapping
        $user->update([
            'teams_conversation_id' => $activity['conversation']['id'] ?? null,
            'teams_service_url' => $activity['serviceUrl'] ?? null,
        ]);

        $messageText = trim($activity['text'] ?? '');
        // Clean Teams @mentions out of the text if it's in a channel
        $messageText = preg_replace('/<at>.*?<\/at>/', '', $messageText);
        $messageText = trim(strip_tags($messageText));

        if (empty($messageText)) {
            return response()->json(['status' => 'empty_message']);
        }

        // 3. Check for existing active escalations for this user
        $activeEscalation = Escalation::where('user_id', $user->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->first();

        if ($activeEscalation) {
            $this->sendBotReply($activity, "Your question has already been escalated to HR. An HR representative will reach out to you directly as soon as possible. Ref ID: " . substr($activeEscalation->id, 0, 8));
            return response()->json(['status' => 'already_escalated']);
        }

        // 4. Classify sensitivity & invoke AI Q&A engine
        $classification = $classifier->classify($messageText);
        $isSensitive = $classification['status'] === 'sensitive';

        if ($isSensitive) {
            // Find assigned HR rep using priority routing
            $hrRep = User::where('organization_id', $user->organization_id)
                ->where('role', 'hr_admin')
                ->where('escalation_routing_active', true)
                ->orderBy('escalation_priority', 'asc')
                ->first();

            // Fallback to owner if no active HR reps configured
            if (!$hrRep) {
                $hrRep = User::where('organization_id', $user->organization_id)
                    ->whereIn('role', ['owner', 'hr_admin'])
                    ->first();
            }

            if (!$hrRep) {
                Log::error("No HR administrators or owners found to route escalation for organization ID: {$user->organization_id}");
                $this->sendBotReply($activity, "This query involves sensitive topics requiring HR review, but no active HR owner is configured. Please contact support.");
                return response()->json(['status' => 'no_hr_configured']);
            }

            // Create Escalation record
            $question = Question::create([
                'organization_id' => $user->organization_id,
                'user_id' => $user->id,
                'question_text' => $messageText,
                'topic' => $classification['reason'] ?? 'sensitive',
                'sensitivity_status' => 'sensitive',
                'status' => 'escalated',
            ]);

            $escalation = Escalation::create([
                'organization_id' => $user->organization_id,
                'question_id' => $question->id,
                'user_id' => $user->id,
                'category' => 'sensitive',
                'status' => 'open',
                'assigned_to' => $hrRep->id,
            ]);

            // Dispatch alert to HR Rep via Teams (proactive message)
            $dispatched = $this->teamsService->sendEscalationAlert($escalation, $hrRep);

            if ($dispatched) {
                $this->sendBotReply($activity, "This query involves sensitive topics. I have escalated it to HR. HR Representative {$hrRep->name} has been notified and will reply to you shortly.");
            } else {
                $this->sendBotReply($activity, "This query is sensitive and has been logged in the HR escalation queue. However, we couldn't send an automated Teams alert to the representative. They will review it from the web console.");
            }

            return response()->json(['status' => 'escalated']);
        }

        // 5. Standard non-sensitive Q&A Flow
        $contexts = $retrieval->retrieve($user, $messageText);
        $answerPayload = $answers->answer($messageText, $contexts, ['must_cite_sources' => true]);

        $question = Question::create([
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
            'question_text' => $messageText,
            'topic' => $classification['reason'] ?? 'general',
            'sensitivity_status' => 'safe',
            'status' => 'answered',
        ]);

        $answer = Answer::create([
            'organization_id' => $user->organization_id,
            'question_id' => $question->id,
            'answer_text' => $answerPayload['answer_text'],
            'answer_status' => 'answered',
            'confidence_label' => $answerPayload['confidence_label'] ?? 'high',
            'model_provider' => 'gemini',
            'model_name' => 'teams_retrieval_agent',
        ]);

        $responseText = $answer->answer_text;

        // Append citations if available
        if (!empty($answerPayload['sources'])) {
            $responseText .= "\n\n**Sources:**";
            foreach ($answerPayload['sources'] as $source) {
                $responseText .= "\n- " . ($source['citation_label'] ?? 'Doc') . ": " . ($source['source']->title ?? 'Knowledge Source');
            }
        }

        $this->sendBotReply($activity, $responseText);

        return response()->json(['status' => 'answered']);
    }

    /**
     * Handle submission action from Adaptive Cards (HR Rep response).
     */
    protected function handleEscalationReply(array $activity, array $submitData): JsonResponse
    {
        $escalationId = $submitData['escalation_id'] ?? null;
        $replyText = $submitData['replyText'] ?? null;

        if (!$escalationId || !$replyText) {
            return response()->json(['error' => 'Missing parameter'], 400);
        }

        $escalation = Escalation::with(['question', 'user', 'organization.settings'])->find($escalationId);

        if (!$escalation) {
            return response()->json(['error' => 'Escalation not found'], 404);
        }

        // Update the escalation record
        $escalation->update([
            'status' => 'resolved',
            'resolution_note' => $replyText,
            'resolved_at' => now(),
        ]);

        // Broker reply back to the Employee via proactive messaging
        $employee = $escalation->user;
        $settings = $escalation->organization->settings;

        if ($employee && $employee->teams_conversation_id && $employee->teams_service_url && $settings) {
            $accessToken = $this->teamsService->getAccessToken($settings);
            if ($accessToken) {
                $msg = [
                    'type' => 'message',
                    'text' => "✉️ **Response from HR Representative:**\n\n{$replyText}\n\n*Your escalation has been marked as resolved.*",
                ];
                $this->teamsService->sendActivity($employee->teams_service_url, $employee->teams_conversation_id, $msg, $accessToken);
            }
        }

        // Return a replacement Adaptive Card status update for the HR Representative's view
        $updatedCard = [
            'type' => 'message',
            'attachments' => [
                [
                    'contentType' => 'application/vnd.microsoft.card.adaptive',
                    'content' => [
                        'type' => 'AdaptiveCard',
                        'version' => '1.4',
                        'body' => [
                            [
                                'type' => 'TextBlock',
                                'text' => "✅ Escalation Resolved",
                                'weight' => 'bolder',
                                'size' => 'large',
                                'color' => 'good',
                            ],
                            [
                                'type' => 'FactSet',
                                'facts' => [
                                    ['title' => 'Employee:', 'value' => $escalation->user->name],
                                    ['title' => 'Question:', 'value' => $escalation->question->question_text],
                                    ['title' => 'Your Response:', 'value' => $replyText],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return response()->json($updatedCard);
    }

    /**
     * Send helper to respond asynchronously using Bot activity endpoint.
     */
    protected function sendBotReply(array $incomingActivity, string $text): void
    {
        $settings = null;
        // Lookup organization credentials using the incoming tenant ID
        $tenantId = $incomingActivity['conversation']['tenantId'] ?? null;
        if ($tenantId) {
            $settings = OrganizationSetting::where('teams_tenant_id', $tenantId)->first();
        }

        if (!$settings) {
            $settings = OrganizationSetting::where('teams_connected', true)->first();
        }

        if (!$settings) {
            Log::error("Unable to find active Teams organization settings for replying to Bot message.");
            return;
        }

        $accessToken = $this->teamsService->getAccessToken($settings);
        if (!$accessToken) {
            return;
        }

        $replyActivity = [
            'type' => 'message',
            'text' => $text,
            'recipient' => $incomingActivity['from'],
            'from' => $incomingActivity['recipient'],
            'conversation' => $incomingActivity['conversation'],
            'replyToId' => $incomingActivity['id'],
        ];

        $this->teamsService->sendActivity($incomingActivity['serviceUrl'], $incomingActivity['conversation']['id'], $replyActivity, $accessToken);
    }
}
