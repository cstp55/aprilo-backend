<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportCustomer;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketCategory;
use App\Models\Support\SupportTicketEvent;
use App\Services\Support\SupportCustomerService;
use App\Services\Support\TicketAssignmentService;
use App\Services\Support\TicketLifecycleService;
use App\Services\Support\TicketNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{
    public function __construct(
        private readonly TicketNumberService $numberService,
        private readonly TicketLifecycleService $lifecycleService,
        private readonly TicketAssignmentService $assignmentService,
        private readonly SupportCustomerService $customerService,
    ) {
    }

    /**
     * List, filter, search, and paginate support tickets.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $org = $user->organization;

        abort_unless($org, 403, 'User does not belong to an active organization.');
        $this->authorizeSupportAccess($user);

        $query = SupportTicket::query()
            ->with(['customer', 'category', 'assignedAgent:id,name,email', 'tags'])
            ->where('organization_id', $org->id);

        // Queue filter
        $queue = $request->query('queue');
        if ($queue === 'unassigned') {
            $query->unassigned()->whereNotIn('status', ['resolved', 'closed']);
        } elseif ($queue === 'assigned_to_me') {
            $query->where('assigned_to', $user->id)->whereNotIn('status', ['resolved', 'closed']);
        } elseif ($queue === 'urgent') {
            $query->where('priority', 'urgent')->whereNotIn('status', ['resolved', 'closed']);
        } elseif ($queue === 'pending') {
            $query->whereIn('status', ['pending_customer', 'pending_internal']);
        } elseif ($queue === 'resolved') {
            $query->whereIn('status', ['resolved', 'closed']);
        }

        // Direct filters
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->query('priority'));
        }

        if ($request->filled('assigned_to')) {
            $assignedTo = $request->query('assigned_to');
            if ($assignedTo === 'unassigned') {
                $query->unassigned();
            } else {
                $query->where('assigned_to', $assignedTo);
            }
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->query('channel'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->query('customer_id'));
        }

        if ($request->filled('search')) {
            $query->search($request->query('search'));
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = strtolower($request->query('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (in_array($sortBy, ['created_at', 'ticket_sequence', 'priority', 'status', 'sla_due_at'], true)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest('created_at');
        }

        $perPage = min(max((int) $request->query('per_page', 20), 1), 100);
        $tickets = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tickets->items(),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    /**
     * View detailed ticket info with comments, conversation, and event timeline.
     */
    public function show(Request $request, SupportTicket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($ticket->organization_id === $user->organization_id, 403, 'Unauthorized ticket access.');
        $this->authorizeSupportAccess($user);

        $ticket->load([
            'customer.channelIdentities',
            'category',
            'assignedAgent:id,name,email',
            'creator:id,name,email',
            'tags',
            'comments.user:id,name,email',
            'comments.customer',
            'events.actor:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
            'allowed_transitions' => $this->lifecycleService->getAllowedTransitions($ticket->status),
        ]);
    }

    /**
     * Create a new support ticket.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $org = $user->organization;
        abort_unless($org, 403);
        $this->authorizeSupportAccess($user);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['nullable', 'string', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'channel' => ['nullable', 'string', 'max:50'],
            'category_id' => ['nullable', 'uuid', Rule::exists('support_ticket_categories', 'id')->where('organization_id', $org->id)],
            'department' => ['nullable', 'string', 'max:100'],
            'assigned_to' => ['nullable', 'uuid', Rule::exists('users', 'id')->where('organization_id', $org->id)],
            'customer_id' => ['nullable', 'uuid', Rule::exists('support_customers', 'id')->where('organization_id', $org->id)],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'conversation_id' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ]);

        $ticket = DB::transaction(function () use ($org, $user, $validated) {
            // Resolve or create customer
            $customerId = $validated['customer_id'] ?? null;
            $customerEmail = $validated['customer_email'] ?? null;
            $customerName = $validated['customer_name'] ?? null;
            $customerPhone = $validated['customer_phone'] ?? null;

            if (! $customerId && (! empty($customerEmail) || ! empty($customerName))) {
                $channel = $validated['channel'] ?? 'portal';
                $ident = $customerEmail ?: ($customerPhone ?: 'customer_' . uniqid());
                $customer = $this->customerService->findOrCreateCustomer($org, $channel, $ident, [
                    'name' => $customerName ?? 'Support Customer',
                    'email' => $customerEmail,
                    'phone' => $customerPhone,
                ]);
                $customerId = $customer->id;
            }

            // Generate atomic ticket number
            $seq = $this->numberService->generate($org);

            $ticket = SupportTicket::create([
                'organization_id' => $org->id,
                'ticket_number' => $seq['ticket_number'],
                'ticket_sequence' => $seq['ticket_sequence'],
                'customer_id' => $customerId,
                'conversation_id' => $validated['conversation_id'] ?? null,
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'channel' => $validated['channel'] ?? 'portal',
                'category_id' => $validated['category_id'] ?? null,
                'department' => $validated['department'] ?? null,
                'priority' => $validated['priority'] ?? 'normal',
                'status' => 'new',
                'assigned_to' => $validated['assigned_to'] ?? null,
                'created_by' => $user->id,
                'metadata' => $validated['metadata'] ?? null,
            ]);

            SupportTicketEvent::create([
                'ticket_id' => $ticket->id,
                'organization_id' => $org->id,
                'actor_id' => $user->id,
                'actor_type' => 'user',
                'event_type' => 'created',
                'new_value' => [
                    'ticket_number' => $ticket->ticket_number,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority,
                    'assigned_to' => $ticket->assigned_to,
                ],
                'note' => 'Ticket created',
            ]);

            return $ticket;
        });

        $ticket->load(['customer', 'category', 'assignedAgent:id,name,email']);

        return response()->json([
            'success' => true,
            'message' => "Ticket {$ticket->ticket_number} created successfully.",
            'ticket' => $ticket,
        ], 201);
    }

    /**
     * Transition ticket status.
     */
    public function transition(Request $request, SupportTicket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($ticket->organization_id === $user->organization_id, 403);
        $this->authorizeSupportAccess($user);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['new', 'open', 'in_progress', 'pending_customer', 'pending_internal', 'resolved', 'closed'])],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $updatedTicket = $this->lifecycleService->transitionStatus(
                $ticket,
                $validated['status'],
                $user,
                $validated['note'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Status transitioned to '{$updatedTicket->status}'.",
            'ticket' => $updatedTicket->load(['customer', 'category', 'assignedAgent:id,name,email']),
        ]);
    }

    /**
     * Update ticket priority.
     */
    public function updatePriority(Request $request, SupportTicket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($ticket->organization_id === $user->organization_id, 403);
        $this->authorizeSupportAccess($user);

        $validated = $request->validate([
            'priority' => ['required', 'string', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $updatedTicket = $this->lifecycleService->updatePriority(
            $ticket,
            $validated['priority'],
            $user,
            $validated['note'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => "Priority updated to '{$updatedTicket->priority}'.",
            'ticket' => $updatedTicket->load(['customer', 'category', 'assignedAgent:id,name,email']),
        ]);
    }

    /**
     * Assign or reassign ticket.
     */
    public function assign(Request $request, SupportTicket $ticket): JsonResponse
    {
        $user = $request->user();
        $org = $user->organization;
        abort_unless($ticket->organization_id === $org->id, 403);
        $this->authorizeSupportAccess($user);

        $validated = $request->validate([
            'assigned_to' => ['nullable', 'uuid', Rule::exists('users', 'id')->where('organization_id', $org->id)],
            'department' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if (array_key_exists('assigned_to', $validated)) {
            $newAssignee = $validated['assigned_to'] ? \App\Models\User::find($validated['assigned_to']) : null;
            $ticket = $this->assignmentService->assignToUser($ticket, $newAssignee, $user, $validated['note'] ?? null);
        }

        if (array_key_exists('department', $validated)) {
            $ticket = $this->assignmentService->assignToDepartment($ticket, $validated['department'], $user, $validated['note'] ?? null);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ticket assignment updated successfully.',
            'ticket' => $ticket->load(['customer', 'category', 'assignedAgent:id,name,email']),
        ]);
    }

    /**
     * List categories for organization.
     */
    public function categories(Request $request): JsonResponse
    {
        $user = $request->user();
        $org = $user->organization;
        abort_unless($org, 403);
        $this->authorizeSupportAccess($user);

        $categories = SupportTicketCategory::where('organization_id', $org->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    /**
     * Verify user and organization have access to Aprilo Support.
     */
    private function authorizeSupportAccess(\App\Models\User $user): void
    {
        $org = $user->organization;
        $hasProduct = $org && (
            $org->hasActiveProductCategory('support_saas') ||
            $org->hasActiveProductCategory('aprilo-support') ||
            $org->hasActiveProductCategory('ai_support') ||
            $org->hasActiveProductCategory('aprilo-ai')
        );

        abort_unless($hasProduct, 403, 'Aprilo Support product is not active for this organization.');
        abort_unless($user->hasPermission('support.tickets.view'), 403, 'Permission denied.');
    }
}
