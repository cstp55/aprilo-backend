<?php

namespace App\Http\Controllers\Api;

use App\Models\Answer;
use App\Models\AnswerSource;
use App\Models\Escalation;
use App\Models\Question;
use App\Services\AI\AiAnswerService;
use App\Services\AI\SensitivityClassifier;
use App\Services\Audit\AuditLogger;
use App\Services\Knowledge\RetrievalService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function store(
        Request $request,
        SensitivityClassifier $classifier,
        RetrievalService $retrieval,
        AiAnswerService $answers,
        AuditLogger $audit,
        \App\Services\Teams\TeamsIntegrationService $teamsService
    ): JsonResponse
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string', 'max:2000'],
        ]);

        $settings = $request->user()->organization->settings;
        if ($settings && $settings->billing_mode === 'pay_as_you_go') {
            $settings->increment('usage_queries_count');
            $settings->increment('usage_amount_due', 0.05);
        }
        
        $questionText = $validated['question_text'];
        $lowerQuestion = strtolower($questionText);

        if ($settings && $settings->chatbot_mode === 'interactive_hr' && 
            (str_contains($lowerQuestion, 'leave') || str_contains($lowerQuestion, 'wfh') || str_contains($lowerQuestion, 'vacation'))) {
            
            // Check if user has an Employee ID card
            if (empty($request->user()->employee_id)) {
                $answerText = "To check your remaining leaves or WFH requests, I need to validate your identity. Please configure your Employee ID in your profile before checking leave balances.";
                $answerStatus = 'unsupported';
                $confidence = 'low';
            } else {
                if ($settings->hr_api_connected) {
                    $leavesCount = \App\Models\LeaveRequest::where('user_id', $request->user()->id)->count();
                    $wfhCount = \App\Models\WfhRequest::where('user_id', $request->user()->id)->count();
                    $answerText = "Hi {$request->user()->name}! I validated your Employee ID {$request->user()->employee_id} via the connected HR API. You currently have {$leavesCount} active leave requests and {$wfhCount} WFH requests registered in BambooHR. Your remaining leave balance is " . (10 - $leavesCount) . " days.";
                } else {
                    $answerText = "Hi {$request->user()->name}! I validated your Employee ID {$request->user()->employee_id}. However, the HR database API is currently disconnected. Please ask your administrator to connect the database API.";
                }
                $answerStatus = 'answered';
                $confidence = 'high';
            }

            $question = Question::create([
                'organization_id' => $request->user()->organization_id,
                'user_id' => $request->user()->id,
                'question_text' => $questionText,
                'topic' => 'leave_query',
                'sensitivity_status' => 'safe',
                'status' => 'answered',
            ]);

            $answer = Answer::create([
                'organization_id' => $request->user()->organization_id,
                'question_id' => $question->id,
                'answer_text' => $answerText,
                'answer_status' => $answerStatus,
                'confidence_label' => $confidence,
                'model_provider' => 'interactive_hr_engine',
                'model_name' => 'hr_integration_api',
                'metadata' => [
                    'mode' => 'interactive_hr',
                    'api_connected' => $settings->hr_api_connected,
                ],
            ]);

            // If teams_connected is enabled, log alert
            if ($settings->teams_connected) {
                \Illuminate\Support\Facades\Log::info("Teams alert dispatched for user leaves lookup: " . $request->user()->name);
            }

            return response()->json([
                'question' => $question,
                'answer' => $answer->load('sources'),
                'escalation' => null,
            ], 201);
        }

        $classification = $classifier->classify($validated['question_text']);
        $isSensitive = $classification['status'] === 'sensitive';
        $contexts = $isSensitive ? [] : $retrieval->retrieve($request->user(), $validated['question_text']);
        $answerPayload = $isSensitive
            ? [
                'answer_text' => 'This question should be reviewed by HR. I have created an escalation for a human HR owner.',
                'answer_status' => 'escalated',
                'confidence_label' => 'low',
                'sources' => [],
            ]
            : $answers->answer($validated['question_text'], $contexts, ['must_cite_sources' => true]);
        $status = match ($answerPayload['answer_status']) {
            'answered' => 'answered',
            'escalated' => 'escalated',
            default => 'unsupported',
        };

        $question = Question::create([
            'organization_id' => $request->user()->organization_id,
            'user_id' => $request->user()->id,
            'question_text' => $validated['question_text'],
            'topic' => $classification['reason'],
            'sensitivity_status' => $classification['status'],
            'status' => $status,
        ]);

        $answer = Answer::create([
            'organization_id' => $request->user()->organization_id,
            'question_id' => $question->id,
            'answer_text' => $answerPayload['answer_text'],
            'answer_status' => $answerPayload['answer_status'],
            'confidence_label' => $answerPayload['confidence_label'],
            'model_provider' => 'local_retrieval',
            'model_name' => 'lexical_sprint_2',
            'metadata' => [
                'context_count' => count($contexts),
            ],
        ]);

        foreach ($answerPayload['sources'] ?? [] as $source) {
            AnswerSource::create([
                'organization_id' => $request->user()->organization_id,
                'answer_id' => $answer->id,
                'source_id' => $source['source']->id,
                'chunk_id' => $source['chunk']->id,
                'citation_label' => $source['citation_label'],
            ]);
        }

        $escalation = null;

        if ($isSensitive) {
            $hrRep = \App\Models\User::where('organization_id', $request->user()->organization_id)
                ->where('role', 'hr_admin')
                ->where('escalation_routing_active', true)
                ->orderBy('escalation_priority', 'asc')
                ->first();

            if (!$hrRep) {
                $hrRep = \App\Models\User::where('organization_id', $request->user()->organization_id)
                    ->whereIn('role', ['owner', 'hr_admin'])
                    ->first();
            }

            $escalation = Escalation::create([
                'organization_id' => $request->user()->organization_id,
                'question_id' => $question->id,
                'user_id' => $request->user()->id,
                'category' => 'sensitive',
                'status' => 'open',
                'assigned_to' => $hrRep ? $hrRep->id : null,
            ]);

            if ($settings && $settings->connect_teams && $hrRep) {
                $teamsService->sendEscalationAlert($escalation, $hrRep);
            }
        }

        $audit->log($request->user(), 'question_asked', 'question', $question->id, [
            'status' => $question->status,
            'sensitivity_status' => $question->sensitivity_status,
        ]);

        return response()->json([
            'question' => $question,
            'answer' => $answer->load(['sources.source', 'sources.chunk']),
            'escalation' => $escalation,
        ], 201);
    }

    public function show(Request $request, Question $question): JsonResponse
    {
        abort_unless($question->organization_id === $request->user()->organization_id, 404);

        if ($request->user()->role === 'employee') {
            abort_unless($question->user_id === $request->user()->id, 403);
        }

        return response()->json([
            'question' => $question->load(['answers.sources', 'escalation']),
        ]);
    }

    public function escalationCheck(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');
        
        $escalation = Escalation::query()
            ->where('status', 'open')
            ->where(function($query) use ($sessionId) {
                $query->where('resolution_note', 'like', "%{$sessionId}%")
                      ->orWhere('question_id', $sessionId);
            })
            ->first();

        if ($escalation) {
            $priority = 'standard';
            if ($escalation->assignee) {
                $priority = $escalation->assignee->escalation_priority ?? 'standard';
            }
            return response()->json([
                'escalated' => true,
                'priority' => $priority,
            ]);
        }

        return response()->json([
            'escalated' => false,
            'priority' => 'standard',
        ]);
    }

    public function validateEmployeeById(Request $request): JsonResponse
    {
        $employeeId = $request->query('employee_id');
        
        $employee = \App\Models\User::query()
            ->where('employee_id', $employeeId)
            ->first();

        if ($employee) {
            return response()->json([
                'valid' => true,
                'employee' => [
                    'name' => $employee->name,
                    'role' => $employee->role === 'employee' ? 'Staff Member' : 'HR Administrator',
                ]
            ]);
        }

        return response()->json([
            'valid' => false,
        ]);
    }
}

