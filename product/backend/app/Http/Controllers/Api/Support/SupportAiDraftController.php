<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportAiDraft;
use App\Models\Support\SupportAiFeedback;
use App\Models\Support\SupportTicket;
use App\Services\Support\Ai\DraftApprovalService;
use App\Services\Support\Ai\SupportAiDraftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class SupportAiDraftController extends Controller
{
    public function __construct(
        private readonly SupportAiDraftService $draftService,
        private readonly DraftApprovalService $approvalService,
    ) {
    }

    /**
     * Request an AI draft proposal for a ticket.
     */
    public function generate(Request $request, SupportTicket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($ticket->organization_id === $user->organization_id, 403);
        $this->authorizeSupportDraftAccess($user);

        $draft = $this->draftService->generateDraft($ticket, $user);

        return response()->json([
            'success' => true,
            'message' => 'AI draft proposed successfully. Pending human review.',
            'draft' => $draft->load('sources'),
        ], 201);
    }

    /**
     * List drafts for a ticket.
     */
    public function index(Request $request, SupportTicket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($ticket->organization_id === $user->organization_id, 403);
        $this->authorizeSupportDraftAccess($user);

        $drafts = $ticket->aiDrafts()->with(['sources', 'reviewer:id,name,email'])->get();

        return response()->json([
            'success' => true,
            'drafts' => $drafts,
        ]);
    }

    /**
     * Get specific draft details and citations.
     */
    public function show(Request $request, SupportAiDraft $draft): JsonResponse
    {
        $user = $request->user();
        abort_unless($draft->organization_id === $user->organization_id, 403);
        $this->authorizeSupportDraftAccess($user);

        return response()->json([
            'success' => true,
            'draft' => $draft->load(['sources', 'reviewer:id,name,email', 'dispatchedComment']),
        ]);
    }

    /**
     * Human agent edits the draft content.
     */
    public function edit(Request $request, SupportAiDraft $draft): JsonResponse
    {
        $user = $request->user();
        abort_unless($draft->organization_id === $user->organization_id, 403);
        $this->authorizeSupportDraftAccess($user);

        $validated = $request->validate([
            'edited_reply' => ['required', 'string'],
        ]);

        try {
            $updatedDraft = $this->approvalService->editDraft($draft, $user, $validated['edited_reply']);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Draft edited successfully.',
            'draft' => $updatedDraft->load('sources'),
        ]);
    }

    /**
     * Human agent rejects the AI draft.
     */
    public function reject(Request $request, SupportAiDraft $draft): JsonResponse
    {
        $user = $request->user();
        abort_unless($draft->organization_id === $user->organization_id, 403);
        $this->authorizeSupportDraftAccess($user);

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $rejectedDraft = $this->approvalService->rejectDraft(
                $draft,
                $user,
                $validated['rejection_reason'] ?? null
            );
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Draft rejected.',
            'draft' => $rejectedDraft,
        ]);
    }

    /**
     * Human agent explicitly approves and dispatches the draft to the customer.
     * MANDATORY SAFETY INVARIANT: Strictly requires authenticated user action.
     */
    public function approve(Request $request, SupportAiDraft $draft): JsonResponse
    {
        $user = $request->user();
        abort_unless($draft->organization_id === $user->organization_id, 403);
        $this->authorizeSupportDraftAccess($user);

        $validated = $request->validate([
            'edited_reply' => ['nullable', 'string'],
            'channel' => ['nullable', 'string', 'in:portal,email,whatsapp,website'],
        ]);

        try {
            $comment = $this->approvalService->approveAndDispatch(
                $draft,
                $user,
                $validated['edited_reply'] ?? null,
                $validated['channel'] ?? null
            );
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Draft approved and dispatched to customer.',
            'comment' => $comment->load('user:id,name,email'),
            'draft' => $draft->fresh(['sources', 'reviewer:id,name,email']),
        ]);
    }

    /**
     * Submit agent feedback on the draft quality.
     */
    public function feedback(Request $request, SupportAiDraft $draft): JsonResponse
    {
        $user = $request->user();
        abort_unless($draft->organization_id === $user->organization_id, 403);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'in:-1,1'],
            'feedback_text' => ['nullable', 'string', 'max:1000'],
        ]);

        $feedback = SupportAiFeedback::create([
            'organization_id' => $draft->organization_id,
            'draft_id' => $draft->id,
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'feedback_text' => $validated['feedback_text'] ?? null,
            'was_edited' => ! empty($draft->edited_reply),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted.',
            'feedback' => $feedback,
        ], 201);
    }

    private function authorizeSupportDraftAccess(\App\Models\User $user): void
    {
        $org = $user->organization;
        $hasProduct = $org && (
            $org->hasActiveProductCategory('support_saas') ||
            $org->hasActiveProductCategory('aprilo-support') ||
            $org->hasActiveProductCategory('ai_support') ||
            $org->hasActiveProductCategory('aprilo-ai')
        );

        abort_unless($hasProduct, 403, 'Aprilo Support is not active for this organization.');
        abort_unless($user->hasPermission('support.tickets.manage') || $user->hasPermission('support.tickets.view'), 403, 'Permission denied.');
    }
}
