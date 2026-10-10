<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Support\SupportCustomer;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketCategory;
use App\Models\Support\SupportTicketComment;
use App\Models\Support\SupportTicketEvent;
use App\Models\Support\SupportTicketForm;
use App\Models\Support\SupportTicketFormVersion;
use App\Services\Support\SupportCustomerService;
use App\Services\Support\TicketNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SupportFormController extends Controller
{
    public function __construct(
        private readonly SupportCustomerService $customerService,
        private readonly TicketNumberService $ticketNumberService,
    ) {
    }

    /**
     * List forms for organization.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $orgId = $user?->organization_id ?: $request->query('organization_id');

        abort_unless($orgId, 400, 'Organization identifier is required.');

        $query = SupportTicketForm::where('organization_id', $orgId);

        // If public/unauthenticated or customer, only return published forms
        if (! $user || ! ($user->isSuperAdmin() || $user->hasPermission('support.forms.manage'))) {
            $query->where('status', 'published');
        }

        $forms = $query->with('category')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'forms' => $forms,
        ]);
    }

    /**
     * Get a specific form schema and settings.
     */
    public function show(Request $request, string $formIdOrSlug): JsonResponse
    {
        $form = SupportTicketForm::where('id', $formIdOrSlug)
            ->orWhere('slug', $formIdOrSlug)
            ->with(['category', 'versions'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'form' => $form,
        ]);
    }

    /**
     * Public or customer intake submission of a dynamic ticket form.
     */
    public function submit(Request $request, string $formIdOrSlug): JsonResponse
    {
        $form = SupportTicketForm::where('id', $formIdOrSlug)
            ->orWhere('slug', $formIdOrSlug)
            ->firstOrFail();

        if ($form->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'This form is currently not accepting submissions.',
            ], 403);
        }

        $organization = $form->organization;
        $schema = $form->schema ?: [];
        $input = $request->all();

        // 1. Evaluate Conditional Visibility & Validate Fields
        $validatedData = [];
        $errors = [];

        foreach ($schema as $field) {
            $key = $field['key'] ?? null;
            if (! $key) continue;

            $isVisible = $this->evaluateCondition($field, $input);

            // If conditionally hidden, do NOT enforce requiredness and skip value unless requested
            if (! $isVisible) {
                continue;
            }

            $value = $input[$key] ?? null;

            // Required check
            if (! empty($field['required']) && (is_null($value) || $value === '' || $value === [])) {
                $errors[$key][] = ($field['label'] ?? $key) . ' is required.';
                continue;
            }

            // Type and length constraints
            if (! is_null($value) && $value !== '') {
                $type = $field['type'] ?? 'text';
                if ($type === 'email' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$key][] = ($field['label'] ?? $key) . ' must be a valid email address.';
                } elseif ($type === 'number' && ! is_numeric($value)) {
                    $errors[$key][] = ($field['label'] ?? $key) . ' must be a number.';
                }

                if (! empty($field['min_length']) && strlen((string)$value) < $field['min_length']) {
                    $errors[$key][] = ($field['label'] ?? $key) . " must be at least {$field['min_length']} characters.";
                }
                if (! empty($field['max_length']) && strlen((string)$value) > $field['max_length']) {
                    $errors[$key][] = ($field['label'] ?? $key) . " cannot exceed {$field['max_length']} characters.";
                }
            }

            $validatedData[$key] = [
                'label' => $field['label'] ?? $key,
                'value' => $value,
                'type' => $field['type'] ?? 'text',
            ];
        }

        if (! empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed for one or more fields.',
                'errors' => $errors,
            ], 422);
        }

        // 2. Extract standard fields
        $customerEmail = $input['customer_email'] ?? ($input['email'] ?? null);
        $customerName = $input['customer_name'] ?? ($input['name'] ?? 'Support Visitor');
        $subject = $input['subject'] ?? ($validatedData['subject']['value'] ?? ($form->name . ' Request'));
        $description = $input['description'] ?? ($validatedData['description']['value'] ?? 'Customer submitted ticket via dynamic form.');
        $priority = in_array($input['priority'] ?? '', ['urgent', 'high', 'normal', 'low'], true) ? $input['priority'] : 'normal';

        // 3. Create customer and ticket atomically
        return DB::transaction(function () use ($organization, $form, $customerEmail, $customerName, $subject, $description, $priority, $validatedData, $input) {
            $customer = $this->customerService->findOrCreateCustomer(
                $organization,
                'website',
                $customerEmail ?: ('anon_' . Str::random(12)),
                [
                    'name' => $customerName,
                    'email' => $customerEmail,
                ]
            );

            $ticketGen = $this->ticketNumberService->generate($organization);
            $ticketNumber = $ticketGen['ticket_number'];
            $ticketSequence = $ticketGen['ticket_sequence'];

            $ticket = SupportTicket::create([
                'organization_id' => $organization->id,
                'customer_id' => $customer->id,
                'category_id' => $form->category_id,
                'ticket_number' => $ticketNumber,
                'ticket_sequence' => $ticketSequence,
                'subject' => $subject,
                'description' => $description,
                'status' => 'new',
                'priority' => $priority,
                'channel' => 'website',
                'metadata' => [
                    'source_form_id' => $form->id,
                    'source_form_name' => $form->name,
                    'source_form_version' => $form->version,
                    'custom_fields' => $validatedData,
                ],
            ]);

            // Create initial customer comment
            SupportTicketComment::create([
                'ticket_id' => $ticket->id,
                'organization_id' => $organization->id,
                'author_type' => 'customer',
                'author_name' => $customerName,
                'body' => $description,
                'is_internal' => false,
                'channel' => 'website',
                'metadata' => ['custom_fields' => $validatedData],
            ]);

            SupportTicketEvent::create([
                'ticket_id' => $ticket->id,
                'organization_id' => $organization->id,
                'actor_type' => 'customer',
                'event_type' => 'ticket_created_via_form',
                'new_value' => [
                    'ticket_number' => $ticket->ticket_number,
                    'form_id' => $form->id,
                ],
                'note' => "Ticket submitted via dynamic form '{$form->name}' (v{$form->version})",
            ]);

            return response()->json([
                'success' => true,
                'message' => $form->settings['success_message'] ?? 'Your request has been submitted successfully. Our support team will follow up shortly.',
                'ticket' => [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority,
                ],
                'ticket_number' => $ticket->ticket_number,
                'ticket_id' => $ticket->id,
            ], 201);
        });
    }

    /**
     * Deterministic evaluation of conditional visibility rule.
     */
    private function evaluateCondition(array $field, array $input): bool
    {
        $condition = $field['condition'] ?? null;
        if (! $condition || empty($condition['field'])) {
            return true; // No condition means always visible
        }

        $targetField = $condition['field'];
        $operator = $condition['operator'] ?? 'equals';
        $targetValue = $condition['value'] ?? null;

        $currentValue = $input[$targetField] ?? null;

        return match ($operator) {
            'equals' => (string)$currentValue === (string)$targetValue,
            'not_equals' => (string)$currentValue !== (string)$targetValue,
            'contains' => is_array($currentValue)
                ? in_array($targetValue, $currentValue, false)
                : stripos((string)$currentValue, (string)$targetValue) !== false,
            'not_empty' => ! empty($currentValue),
            'empty' => empty($currentValue),
            default => true,
        };
    }
}
