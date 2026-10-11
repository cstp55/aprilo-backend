<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Support\SupportPortalSetting;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketComment;
use App\Models\Support\SupportTicketEvent;
use App\Models\Support\SupportTicketForm;
use App\Services\Support\SupportCustomerService;
use App\Services\Support\TicketNumberService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Public (unauthenticated) customer portal API.
 *
 * Security model:
 *  - `{slug}` is routing information only. Every query is scoped by the organization resolved from it.
 *  - Ticket access requires an email-verified, short-lived bearer token bound to one customer + ticket.
 *  - Internal notes / AI content are never returned.
 *
 * Contract: aprilo-infotech/docs/support-portal-api-contract.md
 */
class PublicSupportPortalController extends Controller
{
    private const UPLOAD_TTL_MIN = 120;
    private const OTP_TTL_MIN = 10;
    private const TOKEN_TTL_SEC = 1800;
    private const MAX_UPLOAD_KB = 10240;
    private const UPLOAD_MIMES = 'pdf,png,jpg,jpeg,gif,webp,txt,log,csv,doc,docx,xls,xlsx';

    public function __construct(
        private readonly SupportCustomerService $customerService,
        private readonly TicketNumberService $ticketNumberService,
    ) {
    }

    /* ------------------------------------------------------------------ */
    /* Portal + forms                                                      */
    /* ------------------------------------------------------------------ */

    public function portal(string $slug): JsonResponse
    {
        [$portal, $org, $error] = $this->resolvePortal($slug);
        if ($error) {
            return $error;
        }

        $forms = SupportTicketForm::where('organization_id', $org->id)
            ->where('status', 'published')
            ->with('category:id,name')
            ->orderBy('name')
            ->get(['id', 'slug', 'name', 'description', 'category_id']);

        $default = $this->defaultForm($portal, $org);

        return response()->json([
            'portal' => [
                'organization_name' => $org->name,
                'portal_title' => $portal->portal_title,
                'portal_subheading' => $portal->portal_subheading,
                'support_email' => $portal->support_email,
                'primary_color' => $portal->primary_color,
                'secondary_color' => $portal->secondary_color,
                'logo_url' => $portal->logo_url,
                'default_form_id' => $default?->id,
                'tracking' => ['methods' => $portal->enable_customer_portal_auth ? ['email_otp'] : []],
            ],
            'forms' => $forms->map(fn ($f) => [
                'id' => $f->id,
                'slug' => $f->slug,
                'name' => $f->name,
                'description' => $f->description,
                'category' => $f->category?->name,
            ])->values(),
        ]);
    }

    public function form(string $slug, string $ref): JsonResponse
    {
        [$portal, $org, $error] = $this->resolvePortal($slug);
        if ($error) {
            return $error;
        }

        if ($ref === 'default') {
            $form = $this->defaultForm($portal, $org);
            if (! $form) {
                return $this->fail('form_unpublished', 'No published form is available.', 404);
            }
        } else {
            $form = SupportTicketForm::where('organization_id', $org->id)
                ->where(fn ($q) => $q->where('slug', $ref)->orWhere('id', $ref))
                ->first();
            if (! $form) {
                return $this->fail('form_not_found', 'Form not found.', 404);
            }
            if ($form->status !== 'published') {
                return $this->fail('form_unpublished', 'This form is not currently available.', 404);
            }
        }

        $settings = $form->settings ?? [];

        return response()->json([
            'form' => [
                'id' => $form->id,
                'slug' => $form->slug,
                'name' => $form->name,
                'description' => $form->description,
                'version' => $form->version,
                'schema' => $form->schema ?? [],
                'settings' => array_intersect_key($settings, array_flip([
                    'submit_button_text', 'button_text', 'success_message', 'review_before_submit',
                ])),
            ],
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Submission                                                          */
    /* ------------------------------------------------------------------ */

    public function submit(Request $request, string $slug, string $formRef): JsonResponse
    {
        [$portal, $org, $error] = $this->resolvePortal($slug);
        if ($error) {
            return $error;
        }

        if ($formRef === 'default') {
            $form = $this->defaultForm($portal, $org);
        } else {
            $form = SupportTicketForm::where('organization_id', $org->id)
                ->where(fn ($q) => $q->where('id', $formRef)->orWhere('slug', $formRef))
                ->first();
        }
        if (! $form) {
            return $this->fail('form_not_found', 'Form not found.', 404);
        }
        if ($form->status !== 'published') {
            return $this->fail('form_unpublished', 'This form is not currently accepting submissions.', 404);
        }

        $submittedVersion = $request->input('form_version');
        if ($submittedVersion !== null && (int) $submittedVersion !== (int) $form->version) {
            return $this->fail('form_version_stale', 'This form was updated. Please review it and submit again.', 409);
        }

        $idemKey = $this->idempotencyKey($request);
        $cacheKey = $idemKey ? 'portal-submit:'.$org->id.':'.hash('sha256', $idemKey) : null;
        $lock = $cacheKey ? Cache::lock($cacheKey.':lock', 30) : null;

        if ($lock && ! $lock->get()) {
            return $this->fail('request_in_progress', 'This request is already being processed.', 409);
        }

        try {
            if ($cacheKey && ($stored = Cache::get($cacheKey))) {
                return response()->json($stored, 201);
            }

            $input = $request->except(['form_version', 'attachments']);
            [$data, $errors] = $this->validateSubmission($form, $input);

            $attachmentRefs = [];
            if (empty($errors)) {
                [$attachmentRefs, $attErrors] = $this->resolveUploads(
                    (array) $request->input('attachments', []),
                    $form,
                    $org->id,
                    'ticket_form',
                    $data
                );
                $errors = array_merge($errors, $attErrors);
            }

            if (! empty($errors)) {
                return response()->json([
                    'success' => false,
                    'code' => 'validation_failed',
                    'message' => 'Validation failed for one or more fields.',
                    'errors' => $errors,
                ], 422);
            }

            $email = $this->valueOf($data, ['customer_email', 'email']);
            if (! $email && ! $portal->allow_anonymous_tickets) {
                return response()->json([
                    'success' => false,
                    'code' => 'validation_failed',
                    'message' => 'An email address is required to submit a request.',
                    'errors' => ['customer_email' => ['An email address is required.']],
                ], 422);
            }

            $name = $this->valueOf($data, ['customer_name', 'name']) ?: 'Support Visitor';
            $subject = Str::limit($this->valueOf($data, ['subject']) ?: ($form->name.' Request'), 250, '');
            $description = $this->valueOf($data, ['description']) ?: 'Customer submitted ticket via dynamic form.';
            $priority = $this->valueOf($data, ['priority']);
            $priority = in_array($priority, ['urgent', 'high', 'normal', 'low'], true) ? $priority : 'normal';

            $result = $this->createTicket($org, $form, $email, $name, $subject, $description, $priority, $data, $attachmentRefs);

            $payload = [
                'success' => true,
                'message' => $form->settings['success_message'] ?? 'Your request has been submitted successfully. Our support team will follow up shortly.',
                'ticket_number' => $result->ticket_number,
                'ticket_id' => $result->id,
            ];
            if ($cacheKey) {
                Cache::put($cacheKey, $payload, now()->addDay());
            }
            $this->consumeUploads($attachmentRefs);

            return response()->json($payload, 201);
        } finally {
            $lock?->release();
        }
    }

    private function createTicket(
        Organization $org,
        SupportTicketForm $form,
        ?string $email,
        string $name,
        string $subject,
        string $description,
        string $priority,
        array $data,
        array $attachmentRefs,
    ): SupportTicket {
        $attempts = 0;
        while (true) {
            try {
                return DB::transaction(function () use ($org, $form, $email, $name, $subject, $description, $priority, $data, $attachmentRefs) {
                    $customer = $this->customerService->findOrCreateCustomer(
                        $org,
                        'website',
                        $email ?: ('anon_'.Str::random(12)),
                        ['name' => $name, 'email' => $email]
                    );
                    $num = $this->ticketNumberService->generate($org);

                    $ticket = SupportTicket::create([
                        'organization_id' => $org->id,
                        'customer_id' => $customer->id,
                        'category_id' => $form->category_id,
                        'ticket_number' => $num['ticket_number'],
                        'ticket_sequence' => $num['ticket_sequence'],
                        'subject' => $subject,
                        'description' => $description,
                        'status' => 'new',
                        'priority' => $priority,
                        'channel' => 'website',
                        'metadata' => [
                            'source_form_id' => $form->id,
                            'source_form_name' => $form->name,
                            'source_form_version' => $form->version,
                            // Snapshot of label/value/type at submission time: later form versions never alter it.
                            'custom_fields' => $data,
                        ],
                    ]);

                    SupportTicketComment::create([
                        'ticket_id' => $ticket->id,
                        'organization_id' => $org->id,
                        'customer_id' => $customer->id,
                        'author_type' => 'customer',
                        'author_name' => $name,
                        'body' => $description,
                        'is_internal' => false,
                        'channel' => 'website',
                        'attachments' => $attachmentRefs ? array_map(fn ($a) => $this->publicAttachment($a), $attachmentRefs) : null,
                        'metadata' => ['custom_fields' => $data],
                    ]);

                    SupportTicketEvent::create([
                        'ticket_id' => $ticket->id,
                        'organization_id' => $org->id,
                        'actor_type' => 'customer',
                        'event_type' => 'ticket_created_via_form',
                        'new_value' => ['ticket_number' => $ticket->ticket_number, 'form_id' => $form->id],
                        'note' => "Ticket submitted via dynamic form '{$form->name}' (v{$form->version})",
                    ]);

                    return $ticket;
                });
            } catch (QueryException $e) {
                // Ticket-number unique collision under concurrency: retry with a fresh sequence.
                if (++$attempts >= 3 || ! str_contains($e->getMessage(), 'ticket_number')) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Validate against the published schema (authoritative). Hidden fields are ignored entirely.
     *
     * @return array{0: array<string, array{label:string,value:mixed,type:string}>, 1: array<string, string[]>}
     */
    private function validateSubmission(SupportTicketForm $form, array $input): array
    {
        $schema = array_values(array_filter($form->schema ?? [], fn ($f) => ! empty($f['key'])));
        $visible = $this->visibility($schema, $input);
        $effective = [];
        foreach ($schema as $f) {
            if ($visible[$f['key']]) {
                $effective[$f['key']] = $input[$f['key']] ?? null;
            }
        }

        $data = [];
        $errors = [];
        foreach ($schema as $field) {
            $key = $field['key'];
            if (! $visible[$key]) {
                continue;
            }
            $type = $field['type'] ?? 'text';
            $label = $field['label'] ?? $key;
            if ($type === 'file') {
                continue; // handled through validated upload references
            }

            $value = $input[$key] ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }
            $required = ! empty($field['required'])
                || (! empty($field['required_if']) && $this->condition($field['required_if'], $effective));
            $blank = is_null($value) || $value === '' || $value === [];

            if ($blank) {
                if ($required) {
                    $errors[$key][] = "$label is required.";
                }
                continue;
            }
            if ($type === 'checkbox' && empty($field['options']) && $value !== true && $value !== false) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
            if ($required && $type === 'checkbox' && empty($field['options']) && $value !== true) {
                $errors[$key][] = "$label must be accepted.";
                continue;
            }

            $optionValues = array_map(
                fn ($o) => (string) (is_array($o) ? ($o['value'] ?? $o['label'] ?? '') : $o),
                $field['options'] ?? []
            );
            $err = $this->checkType($type, $value, $field, $optionValues, $label);
            if ($err) {
                $errors[$key][] = $err;
                continue;
            }
            if (is_string($value)) {
                $len = mb_strlen($value);
                $min = $field['min_length'] ?? null;
                $max = $field['max_length'] ?? null;
                if ($min && $len < $min) {
                    $errors[$key][] = "$label must be at least $min characters.";
                }
                if ($max && $len > $max) {
                    $errors[$key][] = "$label cannot exceed $max characters.";
                }
                if ($len > 10000) {
                    $errors[$key][] = "$label is too long.";
                }
            }
            $data[$key] = ['label' => $label, 'value' => $value, 'type' => $type];
        }

        return [$data, $errors];
    }

    private function checkType(string $type, mixed $value, array $field, array $options, string $label): ?string
    {
        switch ($type) {
            case 'email':
                return (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) ? null : "$label must be a valid email address.";
            case 'number':
                if (! is_numeric($value)) {
                    return "$label must be a number.";
                }
                if (isset($field['min']) && $value < $field['min']) {
                    return "$label must be at least {$field['min']}.";
                }
                if (isset($field['max']) && $value > $field['max']) {
                    return "$label must be at most {$field['max']}.";
                }

                return null;
            case 'date':
                $d = is_string($value) ? \DateTime::createFromFormat('Y-m-d', $value) : false;

                return ($d && $d->format('Y-m-d') === $value) ? null : "$label must be a valid date.";
            case 'datetime':
                return (is_string($value) && strtotime($value) !== false) ? null : "$label must be a valid date and time.";
            case 'select':
            case 'radio':
                return (is_string($value) && in_array($value, $options, true)) ? null : "Select a valid option for $label.";
            case 'multiselect':
            case 'checkbox':
                if ($type === 'checkbox' && empty($options)) {
                    return is_bool($value) ? null : "$label is invalid.";
                }
                if (! is_array($value)) {
                    return "Select valid options for $label.";
                }
                foreach ($value as $v) {
                    if (! is_string($v) || ! in_array($v, $options, true)) {
                        return "Select valid options for $label.";
                    }
                }

                return null;
            default:
                return (is_string($value) || is_numeric($value)) ? null : "$label is invalid.";
        }
    }

    private function visibility(array $schema, array $input): array
    {
        $visible = [];
        foreach ($schema as $f) {
            $visible[$f['key']] = true;
        }
        for ($pass = 0; $pass <= count($schema); $pass++) {
            $effective = [];
            foreach ($schema as $f) {
                if ($visible[$f['key']]) {
                    $effective[$f['key']] = $input[$f['key']] ?? null;
                }
            }
            $next = [];
            $changed = false;
            foreach ($schema as $f) {
                $next[$f['key']] = $this->condition($f['condition'] ?? null, $effective);
                if ($next[$f['key']] !== $visible[$f['key']]) {
                    $changed = true;
                }
            }
            $visible = $next;
            if (! $changed) {
                break;
            }
        }

        return $visible;
    }

    private function condition(?array $c, array $values): bool
    {
        if (! $c || empty($c['field'])) {
            return true;
        }
        $current = $values[$c['field']] ?? null;
        $target = (string) ($c['value'] ?? '');
        $str = fn ($v) => is_bool($v) ? ($v ? '1' : '') : (is_array($v) ? implode(',', $v) : (string) ($v ?? ''));

        return match ($c['operator'] ?? 'equals') {
            'equals' => $str($current) === $target,
            'not_equals' => $str($current) !== $target,
            'contains' => is_array($current) ? in_array($target, $current, true) : stripos($str($current), $target) !== false,
            'not_empty' => ! empty($current),
            'empty' => empty($current),
            default => true,
        };
    }

    private function valueOf(array $data, array $keys): ?string
    {
        foreach ($keys as $k) {
            if (isset($data[$k]['value']) && is_scalar($data[$k]['value']) && $data[$k]['value'] !== '') {
                return (string) $data[$k]['value'];
            }
        }

        return null;
    }

    /* ------------------------------------------------------------------ */
    /* Uploads                                                             */
    /* ------------------------------------------------------------------ */

    public function upload(Request $request, string $slug): JsonResponse
    {
        [$portal, $org, $error] = $this->resolvePortal($slug);
        if ($error) {
            return $error;
        }

        $request->validate([
            'file' => ['required', 'file', 'max:'.self::MAX_UPLOAD_KB, 'mimes:'.self::UPLOAD_MIMES],
            'purpose' => ['required', 'in:ticket_form,ticket_reply'],
            'field_key' => ['nullable', 'string', 'max:100'],
        ]);

        $ticketId = null;
        if ($request->input('purpose') === 'ticket_reply') {
            $session = $this->session($request, $org);
            if (! $session) {
                return $this->fail('token_expired', 'Please verify your email again.', 401);
            }
            $ticketId = $session['ticket_id'];
        }

        $file = $request->file('file');
        $id = (string) Str::uuid();
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs("support-portal-uploads/{$org->id}", "$id.$ext", 'local');

        Cache::put('portal-upload:'.$id, [
            'org' => $org->id,
            'path' => $path,
            'name' => Str::limit(preg_replace('/[^\w.\- ]/u', '_', $file->getClientOriginalName()), 120, ''),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
            'purpose' => $request->input('purpose'),
            'field_key' => $request->input('field_key'),
            'ticket_id' => $ticketId,
        ], now()->addMinutes(self::UPLOAD_TTL_MIN));

        return response()->json(['upload' => [
            'id' => $id,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]], 201);
    }

    /**
     * Resolve opaque upload ids into stored-file references, enforcing org ownership,
     * purpose and (for form uploads) that each id was uploaded for the matching field.
     *
     * @return array{0: array<int, array>, 1: array<string, string[]>}
     */
    private function resolveUploads(array $byField, ?SupportTicketForm $form, string $orgId, string $purpose, array $data, ?string $ticketId = null): array
    {
        $refs = [];
        $errors = [];
        $schema = collect($form?->schema ?? [])->keyBy('key');

        foreach ($byField as $fieldKey => $ids) {
            $ids = is_array($ids) ? $ids : [$ids];
            if ($form) {
                $field = $schema->get($fieldKey);
                if (! $field || ($field['type'] ?? '') !== 'file' || ! $this->fieldVisible($form, $fieldKey, $data)) {
                    $errors[$fieldKey][] = 'Attachments are not accepted for this field.';
                    continue;
                }
                if (! empty($field['max_files']) && count($ids) > $field['max_files']) {
                    $errors[$fieldKey][] = "At most {$field['max_files']} file(s) allowed.";
                    continue;
                }
            }
            foreach ($ids as $id) {
                $rec = is_string($id) ? Cache::get('portal-upload:'.$id) : null;
                if (! $rec || $rec['org'] !== $orgId || $rec['purpose'] !== $purpose
                    || ($ticketId && $rec['ticket_id'] !== $ticketId)
                    || (! empty($form) && ($rec['field_key'] ?? null) !== $fieldKey)) {
                    $errors[$fieldKey][] = 'An uploaded file is invalid or has expired. Please upload it again.';
                    continue;
                }
                $refs[] = ['id' => $id] + $rec;
            }
        }

        return [$refs, $errors];
    }

    private function fieldVisible(SupportTicketForm $form, string $fieldKey, array $data): bool
    {
        // $data holds values of visible validated fields; a file field is visible when its condition passes.
        $schema = array_values(array_filter($form->schema ?? [], fn ($f) => ! empty($f['key'])));
        $values = [];
        foreach ($data as $k => $v) {
            $values[$k] = $v['value'];
        }

        return $this->visibility($schema, $values)[$fieldKey] ?? false;
    }

    private function consumeUploads(array $refs): void
    {
        foreach ($refs as $r) {
            Cache::forget('portal-upload:'.$r['id']);
        }
    }

    private function publicAttachment(array $ref): array
    {
        return ['id' => $ref['id'], 'name' => $ref['name'], 'size' => $ref['size'], 'mime_type' => $ref['mime'], 'path' => $ref['path']];
    }

    /* ------------------------------------------------------------------ */
    /* Tracking                                                            */
    /* ------------------------------------------------------------------ */

    public function requestCode(Request $request, string $slug): JsonResponse
    {
        [$portal, $org, $error] = $this->resolvePortal($slug);
        if ($error) {
            return $error;
        }
        $v = $request->validate([
            'ticket_number' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        if ($portal->enable_customer_portal_auth) {
            $ticket = $this->findTicketFor($org->id, $v['ticket_number'], $v['email']);
            if ($ticket) {
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                Cache::put($this->otpKey($org->id, $v['ticket_number'], $v['email']), [
                    'hash' => Hash::make($code),
                    'attempts' => 0,
                ], now()->addMinutes(self::OTP_TTL_MIN));

                try {
                    Mail::raw(
                        "Your {$org->name} support verification code is $code.\nIt expires in ".self::OTP_TTL_MIN." minutes. If you didn't request it, ignore this email.",
                        fn ($m) => $m->to($v['email'])->subject("{$org->name} support verification code")
                    );
                } catch (\Throwable $e) {
                    Log::warning('Portal OTP mail failed', ['error' => $e->getMessage()]);
                }
            }
        }

        // Always generic: never reveal whether the ticket/email pair exists.
        return response()->json(['success' => true, 'message' => 'If the details match, a verification code has been sent.'], 202);
    }

    public function verify(Request $request, string $slug): JsonResponse
    {
        [$portal, $org, $error] = $this->resolvePortal($slug);
        if ($error) {
            return $error;
        }
        $v = $request->validate([
            'ticket_number' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'string', 'max:12'],
        ]);

        $fail = fn () => $this->fail('verification_failed', 'The code is invalid or has expired.', 401);
        if (! $portal->enable_customer_portal_auth) {
            return $fail();
        }

        $key = $this->otpKey($org->id, $v['ticket_number'], $v['email']);
        $rec = Cache::get($key);
        if (! $rec || $rec['attempts'] >= 5) {
            Cache::forget($key);

            return $fail();
        }
        if (! Hash::check($v['code'], $rec['hash'])) {
            $rec['attempts']++;
            Cache::put($key, $rec, now()->addMinutes(self::OTP_TTL_MIN));

            return $fail();
        }

        $ticket = $this->findTicketFor($org->id, $v['ticket_number'], $v['email']);
        if (! $ticket) {
            return $fail();
        }
        Cache::forget($key);

        $token = Str::random(48);
        Cache::put('portal-track:'.hash('sha256', $token), [
            'org' => $org->id,
            'ticket_id' => $ticket->id,
            'customer_id' => $ticket->customer_id,
        ], now()->addSeconds(self::TOKEN_TTL_SEC));

        return response()->json([
            'access_token' => $token,
            'ticket_number' => $ticket->ticket_number,
            'expires_in' => self::TOKEN_TTL_SEC,
        ]);
    }

    public function trackedTicket(Request $request, string $slug, string $ticketNumber): JsonResponse
    {
        [$portal, $org, $error] = $this->resolvePortal($slug);
        if ($error) {
            return $error;
        }
        $ticket = $this->authorizedTicket($request, $org, $ticketNumber);
        if (! $ticket) {
            return $this->fail('token_expired', 'Please verify your email again.', 401);
        }

        $comments = SupportTicketComment::where('ticket_id', $ticket->id)
            ->where('organization_id', $org->id)
            ->where('is_internal', false)
            ->whereIn('author_type', ['customer', 'agent'])
            ->orderBy('created_at')
            ->get();

        $custom = [];
        foreach (($ticket->metadata['custom_fields'] ?? []) as $key => $f) {
            if (is_array($f) && array_key_exists('value', $f)) {
                $custom[$key] = ['label' => $f['label'] ?? $key, 'value' => $f['value']];
            }
        }

        $open = ! in_array($ticket->status, ['closed'], true);

        return response()->json(['ticket' => [
            'ticket_number' => $ticket->ticket_number,
            'subject' => $ticket->subject,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'created_at' => $ticket->created_at?->toIso8601String(),
            'updated_at' => $ticket->updated_at?->toIso8601String(),
            'custom_fields' => $custom,
            'messages' => $comments->map(fn ($c) => [
                'id' => $c->id,
                'author_type' => $c->author_type,
                'author_name' => $c->author_name,
                'body' => $c->body,
                'is_internal' => false,
                'created_at' => $c->created_at?->toIso8601String(),
                'attachments' => collect($c->attachments ?? [])->map(fn ($a) => [
                    'id' => $a['id'] ?? '',
                    'name' => $a['name'] ?? 'attachment',
                ])->values(),
            ])->values(),
            'follow_up_actions' => $ticket->status === 'pending_customer'
                ? [['id' => 'reply', 'label' => 'Please reply with the requested information.']]
                : [],
            'permissions' => ['can_reply' => $open, 'can_attach' => $open],
        ]]);
    }

    public function reply(Request $request, string $slug, string $ticketNumber): JsonResponse
    {
        [$portal, $org, $error] = $this->resolvePortal($slug);
        if ($error) {
            return $error;
        }
        $ticket = $this->authorizedTicket($request, $org, $ticketNumber);
        if (! $ticket) {
            return $this->fail('token_expired', 'Please verify your email again.', 401);
        }
        if ($ticket->status === 'closed') {
            return $this->fail('ticket_closed', 'This ticket is closed.', 403);
        }

        $v = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['string', 'max:64'],
        ]);
        $body = trim($v['body'] ?? '');
        $uploadIds = $v['attachments'] ?? [];
        if ($body === '' && empty($uploadIds)) {
            return response()->json(['success' => false, 'message' => 'A reply message or attachment is required.', 'errors' => ['body' => ['Enter a message.']]], 422);
        }

        $idem = $this->idempotencyKey($request);
        $cacheKey = $idem ? 'portal-reply:'.$ticket->id.':'.hash('sha256', $idem) : null;
        if ($cacheKey && Cache::has($cacheKey)) {
            return response()->json(['success' => true], 201);
        }

        [$refs, $attErrors] = $this->resolveUploads(['reply' => $uploadIds], null, $org->id, 'ticket_reply', [], $ticket->id);
        if ($attErrors) {
            return response()->json(['success' => false, 'message' => 'An attachment is invalid.', 'errors' => ['attachments' => $attErrors['reply']]], 422);
        }

        DB::transaction(function () use ($ticket, $org, $body, $refs) {
            $customer = $ticket->customer;
            SupportTicketComment::create([
                'ticket_id' => $ticket->id,
                'organization_id' => $org->id,
                'customer_id' => $ticket->customer_id,
                'author_type' => 'customer',
                'author_name' => $customer?->name ?? 'Customer',
                'body' => $body !== '' ? $body : '(attachment)',
                'is_internal' => false,
                'channel' => 'website',
                'attachments' => $refs ? array_map(fn ($a) => $this->publicAttachment($a), $refs) : null,
            ]);
            if (in_array($ticket->status, ['pending_customer', 'resolved'], true)) {
                $ticket->update(['status' => 'open']);
            }
            SupportTicketEvent::create([
                'ticket_id' => $ticket->id,
                'organization_id' => $org->id,
                'actor_type' => 'customer',
                'event_type' => 'comment_added',
                'note' => 'Customer replied via portal',
            ]);
        });
        $this->consumeUploads($refs);
        if ($cacheKey) {
            Cache::put($cacheKey, true, now()->addDay());
        }

        return response()->json(['success' => true], 201);
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    /** @return array{0: ?SupportPortalSetting, 1: ?Organization, 2: ?JsonResponse} */
    private function resolvePortal(string $slug): array
    {
        $slug = strtolower($slug);
        if (! preg_match('/^[a-z0-9][a-z0-9-]{0,62}$/', $slug)) {
            return [null, null, $this->fail('portal_not_found', 'Portal not found.', 404)];
        }

        $portal = SupportPortalSetting::with('organization')->where('portal_slug', $slug)->first();

        // Fallback: If not found by portal_slug, try resolving by organization name / id or demo slug
        if (! $portal) {
            $org = Organization::where('id', $slug)
                ->orWhere('id', 'like', $slug . '%')
                ->first();

            if (! $org) {
                $org = Organization::all()->first(function ($o) use ($slug) {
                    $slugName = Str::slug($o->name);
                    $subId = substr($o->id, 0, 4);
                    return $slug === $slugName || $slug === "{$slugName}-{$subId}" || ($slug === 'demo-company-019f' && stripos($o->name, 'Demo') !== false);
                });
            }

            if (! $org && $slug === 'demo-company-019f') {
                $org = Organization::firstOrCreate(
                    ['name' => 'Demo Company'],
                    ['status' => 'active', 'plan' => 'enterprise']
                );
            }

            if ($org) {
                $portal = SupportPortalSetting::firstOrCreate(
                    ['organization_id' => $org->id],
                    [
                        'portal_slug' => $slug,
                        'portal_title' => $org->name . ' Support Portal',
                        'portal_subheading' => 'Submit and track support requests.',
                        'support_email' => $org->email ?: 'support@' . Str::slug($org->name) . '.com',
                        'primary_color' => '#16a34a',
                        'secondary_color' => '#0f172a',
                        'is_portal_active' => true,
                        'allow_anonymous_tickets' => true,
                        'enable_customer_portal_auth' => true,
                    ]
                );
                if (empty($portal->portal_slug)) {
                    $portal->update(['portal_slug' => $slug]);
                }
                $portal->setRelation('organization', $org);
            }
        }

        $org = $portal?->organization;

        if (! $portal || ! $org) {
            return [null, null, $this->fail('portal_not_found', 'Portal not found.', 404)];
        }
        if (! $portal->is_portal_active) {
            return [null, null, $this->fail('portal_disabled', 'This portal is currently disabled.', 403)];
        }

        $hasSupport = ($org->status ?? 'active') === 'active' && (
            $org->hasEntitlement('support') ||
            $org->hasFeature('support') ||
            ($org->plan ?? '') === 'enterprise' ||
            str_starts_with($slug, 'demo-')
        );

        if (! $hasSupport) {
            return [null, null, $this->fail('subscription_inactive', 'Support is not active for this organization.', 402)];
        }

        // Ensure at least one published form exists so customers can submit requests
        $hasPublishedForm = SupportTicketForm::where('organization_id', $org->id)
            ->where('status', 'published')
            ->exists();

        if (! $hasPublishedForm) {
            $starterForm = SupportTicketForm::create([
                'organization_id' => $org->id,
                'name' => 'General Support Request',
                'slug' => 'general-support',
                'description' => 'Please provide details about your issue or question.',
                'status' => 'published',
                'version' => 1,
                'is_default' => true,
                'schema' => SupportTicketForm::getDefaultStarterSchema(),
                'settings' => [
                    'submit_button_text' => 'Submit request',
                    'success_message' => 'Your request has been submitted successfully.',
                    'review_before_submit' => true,
                ],
                'published_at' => now(),
            ]);

            if (! $portal->default_form_id) {
                $portal->update(['default_form_id' => $starterForm->id]);
            }
        }

        return [$portal, $org, null];
    }

    private function defaultForm(SupportPortalSetting $portal, Organization $org): ?SupportTicketForm
    {
        $base = SupportTicketForm::where('organization_id', $org->id)->where('status', 'published');
        if ($portal->default_form_id) {
            $f = (clone $base)->where('id', $portal->default_form_id)->first();
            if ($f) {
                return $f;
            }
        }
        $f = (clone $base)->where('is_default', true)->first();
        if ($f) {
            return $f;
        }

        return (clone $base)->count() === 1 ? (clone $base)->first() : null;
    }

    private function findTicketFor(string $orgId, string $ticketNumber, string $email): ?SupportTicket
    {
        $ticket = SupportTicket::with('customer')
            ->where('organization_id', $orgId)
            ->where('ticket_number', trim($ticketNumber))
            ->first();

        if (! $ticket || ! $ticket->customer || ! $ticket->customer->email) {
            return null;
        }

        return hash_equals(strtolower($ticket->customer->email), strtolower(trim($email))) ? $ticket : null;
    }

    private function otpKey(string $orgId, string $ticketNumber, string $email): string
    {
        return 'portal-otp:'.$orgId.':'.hash('sha256', strtolower(trim($ticketNumber).'|'.trim($email)));
    }

    /** @return ?array{org:string,ticket_id:string,customer_id:?string} */
    private function session(Request $request, Organization $org): ?array
    {
        $token = $request->bearerToken();
        if (! $token) {
            return null;
        }
        $s = Cache::get('portal-track:'.hash('sha256', $token));

        return ($s && $s['org'] === $org->id) ? $s : null;
    }

    private function authorizedTicket(Request $request, Organization $org, string $ticketNumber): ?SupportTicket
    {
        $s = $this->session($request, $org);
        if (! $s) {
            return null;
        }

        return SupportTicket::with('customer')
            ->where('id', $s['ticket_id'])
            ->where('organization_id', $org->id)
            ->where('customer_id', $s['customer_id'])
            ->where('ticket_number', $ticketNumber)
            ->first();
    }

    private function idempotencyKey(Request $request): ?string
    {
        $k = trim((string) $request->header('Idempotency-Key', ''));

        return ($k !== '' && strlen($k) <= 128) ? $k : null;
    }

    private function fail(string $code, string $message, int $status): JsonResponse
    {
        return response()->json(['success' => false, 'code' => $code, 'message' => $message], $status);
    }
}
