<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketComment;
use App\Models\Support\SupportTicketEvent;
use App\Services\Support\TicketLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportTicketCommentController extends Controller
{
    public function __construct(private readonly TicketLifecycleService $lifecycleService)
    {
    }

    /**
     * List comments and notes for a ticket.
     */
    public function index(Request $request, SupportTicket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($ticket->organization_id === $user->organization_id, 403);

        $includeInternal = $request->boolean('include_internal', true);

        $query = $ticket->comments()->with(['user:id,name,email', 'customer']);

        if (! $includeInternal) {
            $query->customerVisible();
        }

        $comments = $query->get();

        return response()->json([
            'success' => true,
            'comments' => $comments,
        ]);
    }

    /**
     * Add an internal note or customer-visible comment to a ticket.
     */
    public function store(Request $request, SupportTicket $ticket): JsonResponse
    {
        $user = $request->user();
        $org = $user->organization;
        abort_unless($ticket->organization_id === $user->organization_id, 403);

        $validated = $request->validate([
            'body' => ['required', 'string'],
            'is_internal' => ['required', 'boolean'], // Strict requirement: explicitly declare note vs public reply
            'attachments' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ]);

        $comment = DB::transaction(function () use ($ticket, $user, $validated) {
            $isInternal = (bool) $validated['is_internal'];

            $comment = SupportTicketComment::create([
                'ticket_id' => $ticket->id,
                'organization_id' => $ticket->organization_id,
                'user_id' => $user->id,
                'author_type' => 'agent',
                'author_name' => $user->name,
                'body' => $validated['body'],
                'is_internal' => $isInternal,
                'channel' => 'portal',
                'attachments' => $validated['attachments'] ?? null,
                'metadata' => $validated['metadata'] ?? null,
            ]);

            if ($isInternal) {
                // Internal Note audit event
                SupportTicketEvent::create([
                    'ticket_id' => $ticket->id,
                    'organization_id' => $ticket->organization_id,
                    'actor_id' => $user->id,
                    'actor_type' => 'user',
                    'event_type' => 'note_added',
                    'new_value' => ['comment_id' => $comment->id],
                    'note' => 'Added internal note',
                ]);
            } else {
                // Customer-visible reply audit event
                SupportTicketEvent::create([
                    'ticket_id' => $ticket->id,
                    'organization_id' => $ticket->organization_id,
                    'actor_id' => $user->id,
                    'actor_type' => 'user',
                    'event_type' => 'comment_added',
                    'new_value' => ['comment_id' => $comment->id],
                    'note' => 'Sent reply to customer',
                ]);

                // Track SLA first response timestamp
                $this->lifecycleService->recordFirstResponse($ticket);

                // If currently open or in progress, automatically advance to pending_customer
                if (in_array($ticket->status, ['new', 'open', 'in_progress'], true)) {
                    $this->lifecycleService->transitionStatus(
                        $ticket,
                        'pending_customer',
                        $user,
                        'Awaiting customer response following agent reply'
                    );
                }
            }

            return $comment;
        });

        $comment->load('user:id,name,email');

        return response()->json([
            'success' => true,
            'message' => $comment->is_internal ? 'Internal note added.' : 'Reply sent successfully.',
            'comment' => $comment,
        ], 201);
    }
}
