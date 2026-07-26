<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Feedback;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    public function store(Request $request, Answer $answer, AuditLogger $audit): JsonResponse
    {
        abort_unless($answer->organization_id === $request->user()->organization_id, 404);

        $validated = $request->validate([
            'rating' => ['required', Rule::in(['helpful', 'not_helpful'])],
            'comment' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $feedback = Feedback::create([
            'organization_id' => $request->user()->organization_id,
            'answer_id' => $answer->id,
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        $audit->log($request->user(), 'answer_rated', 'answer', $answer->id, [
            'rating' => $feedback->rating,
        ]);

        $settings = \App\Models\OrganizationSetting::where('organization_id', $answer->organization_id)->first();

        if ($feedback->rating === 'not_helpful') {
            $escalationEnabled = $settings ? $settings->chatbot_escalation_enabled : true;

            if ($escalationEnabled) {
                $escalation = \App\Models\Escalation::create([
                    'organization_id' => $answer->organization_id,
                    'question_id' => $answer->question_id,
                    'user_id' => $request->user()->id,
                    'category' => 'unresolved_feedback',
                    'status' => 'open',
                ]);

                // Send mock notification
                $platforms = [];
                if ($settings) {
                    if ($settings->connect_teams) $platforms[] = 'Microsoft Teams';
                    if ($settings->connect_skype) $platforms[] = 'Skype';
                    if ($settings->connect_whatsapp) $platforms[] = 'WhatsApp';
                    if ($settings->connect_mail && $settings->hr_desk_email) {
                        $platforms[] = 'Mail to ' . $settings->hr_desk_email;
                    }
                }

                if (count($platforms) > 0) {
                    \Illuminate\Support\Facades\Log::info("HR Escalation Alert Sent! Ticket #{$escalation->id} for user {$request->user()->name} was dispatched via: " . implode(', ', $platforms));
                } else {
                    \Illuminate\Support\Facades\Log::info("HR Escalation Created! Ticket #{$escalation->id} is pending (no integrations enabled).");
                }
            }
        } else {
            // Close any open escalations for this question
            \App\Models\Escalation::where('question_id', $answer->question_id)
                ->where('status', 'open')
                ->update([
                    'status' => 'closed',
                    'resolution_note' => 'Resolved via positive user feedback.',
                    'resolved_at' => now(),
                ]);
        }

        return response()->json(['feedback' => $feedback], 201);
    }
}
