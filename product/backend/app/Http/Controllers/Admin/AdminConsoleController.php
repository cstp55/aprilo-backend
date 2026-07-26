<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessKnowledgeSource;
use App\Models\Escalation;
use App\Models\KnowledgeSource;
use App\Models\OrganizationSetting;
use App\Models\Question;
use App\Services\Metrics\MetricsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminConsoleController extends Controller
{
    public function dashboard(Request $request, MetricsService $metrics): View
    {
        $this->authorizeAdmin($request);

        $organization = $request->user()->organization;

        $settings = OrganizationSetting::firstOrCreate(
            ['organization_id' => $organization->id],
            [
                'minutes_saved_per_resolved_question' => 5,
                'assistant_status' => 'active',
                'assistant_name' => 'Aprilo Bot',
            ]
        );

        $invoices = \App\Models\Invoice::where('organization_id', $organization->id)->latest()->get();

        return view('admin.dashboard', [
            'summary' => $metrics->summary($organization),
            'sourceCount' => $organization->knowledgeSources()->count(),
            'indexedSourceCount' => $organization->knowledgeSources()->where('status', 'indexed')->count(),
            'openEscalationCount' => Escalation::query()
                ->where('organization_id', $organization->id)
                ->where('status', 'open')
                ->count(),
            'settings' => $settings,
            'organization' => $organization,
            'invoices' => $invoices,
        ]);
    }

    public function sources(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.sources', [
            'sources' => KnowledgeSource::query()
                ->withCount('chunks')
                ->where('organization_id', $request->user()->organization_id)
                ->latest()
                ->get(),
        ]);
    }

    public function storeSource(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'access_scope' => ['required', Rule::in(['all_employees', 'hr_only'])],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $sourceType = $extension === 'md' ? 'markdown' : $extension;

        if (! in_array($sourceType, ['pdf', 'docx', 'txt', 'markdown'], true)) {
            throw ValidationException::withMessages([
                'file' => 'Only PDF, DOCX, TXT, and Markdown files are supported.',
            ]);
        }

        // Version control duplicate handling
        $originalName = $file->getClientOriginalName();
        $title = $validated['title'];
        $existing = KnowledgeSource::where('organization_id', $request->user()->organization_id)
            ->where(function ($query) use ($title, $originalName) {
                $query->where('title', $title)
                    ->orWhere('metadata->original_name', $originalName);
            })
            ->where('status', '!=', 'inactive')
            ->get();

        $maxVersion = 1;
        foreach ($existing as $oldSource) {
            $oldMeta = $oldSource->metadata ?? [];
            $version = $oldMeta['version'] ?? 1;
            if ($version >= $maxVersion) {
                $maxVersion = $version + 1;
            }

            $oldSource->update([
                'status' => 'inactive',
                'title' => $oldSource->title . " [Archived V{$version}]",
                'metadata' => array_merge($oldMeta, [
                    'replaced_by_version' => $maxVersion,
                    'replaced_at' => now()->toIso8601String(),
                ])
            ]);
        }

        $source = KnowledgeSource::create([
            'organization_id' => $request->user()->organization_id,
            'title' => $title,
            'source_type' => $sourceType,
            'file_path' => $file->store('knowledge-sources'),
            'status' => 'uploaded',
            'access_scope' => $validated['access_scope'],
            'uploaded_by' => $request->user()->id,
            'metadata' => [
                'original_name' => $originalName,
                'size' => $file->getSize(),
                'version' => $maxVersion,
            ],
        ]);

        if (App::environment(['local', 'testing'])) {
            ProcessKnowledgeSource::dispatchSync($source->id);
        } else {
            ProcessKnowledgeSource::dispatch($source->id);
        }

        return redirect()
            ->route('admin.sources')
            ->with('status', 'Source uploaded and indexing started.');
    }

    public function settings(Request $request): View
    {
        $this->authorizeAdmin($request);
        
        $tab = $request->query('tab', 'agent');
        $organization = $request->user()->organization;

        $settings = OrganizationSetting::firstOrCreate(
            ['organization_id' => $request->user()->organization_id],
            [
                'minutes_saved_per_resolved_question' => 5,
                'assistant_status' => 'active',
                'assistant_name' => 'Aprilo Bot',
            ]
        );

        $invoices = \App\Models\Invoice::where('organization_id', $organization->id)->latest()->get();

        return view('admin.settings', compact('settings', 'tab', 'organization', 'invoices'));
    }

    public function updateBillingSettings(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $organization = $request->user()->organization;

        $validated = $request->validate([
            'billing_mode' => ['required', Rule::in(['subscription', 'pay_as_you_go'])],
        ]);

        $settings = OrganizationSetting::where('organization_id', $organization->id)->firstOrFail();
        $settings->update($validated);

        return redirect()
            ->route('admin.settings', ['tab' => 'pricing'])
            ->with('status', 'Billing mode updated successfully!');
    }

    public function closeBillingCycle(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $organization = $request->user()->organization;
        $settings = OrganizationSetting::where('organization_id', $organization->id)->firstOrFail();

        $amount = 0.00;
        if ($settings->billing_mode === 'pay_as_you_go') {
            $amount = floatval($settings->usage_amount_due);
        } else {
            // Subscription fees based on current plan
            $amount = match ($organization->plan) {
                'pro' => 49.00,
                'enterprise' => 199.00,
                default => 0.00,
            };
        }

        // Generate unique invoice number
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        \App\Models\Invoice::create([
            'organization_id' => $organization->id,
            'invoice_number' => $invoiceNumber,
            'billing_mode' => $settings->billing_mode,
            'amount' => $amount,
            'status' => $amount > 0 ? 'paid' : 'paid',
            'billing_period_start' => now()->startOfMonth(),
            'billing_period_end' => now()->endOfMonth(),
            'due_date' => now()->addDays(15),
            'paid_at' => now(),
        ]);

        // Reset usage counts
        $settings->update([
            'usage_queries_count' => 0,
            'usage_amount_due' => 0.00,
        ]);

        return redirect()
            ->route('admin.settings', ['tab' => 'pricing'])
            ->with('status', 'Billing cycle closed. Invoice ' . $invoiceNumber . ' generated!');
    }

    public function showInvoice(Request $request, \App\Models\Invoice $invoice): View
    {
        $this->authorizeAdmin($request);
        abort_unless($invoice->organization_id === $request->user()->organization_id, 404);

        $organization = $request->user()->organization;

        return view('admin.invoice', compact('invoice', 'organization'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $organization = $request->user()->organization;

        $validated = $request->validate([
            'assistant_name' => ['required', 'string', 'max:255'],
            'minutes_saved_per_resolved_question' => ['required', 'integer', 'min:1', 'max:60'],
            'assistant_status' => ['required', Rule::in(['active', 'paused'])],
            'chatbot_color_palette' => ['required', 'string', 'max:50'],
            'chatbot_icon' => ['required', 'string', 'max:50'],
            'chatbot_data_source' => ['required', Rule::in(['documents', 'web', 'hybrid'])],
            'chatbot_intelligence_level' => ['required', Rule::in(['conservative', 'standard', 'creative'])],
            'chatbot_ticket_creation' => ['required', Rule::in(['automatic', 'feedback-based', 'manual'])],
            'hr_desk_email' => ['nullable', 'email', 'max:255'],
            'chatbot_mode' => ['required', Rule::in(['document_only', 'interactive_hr'])],
        ]);

        // Enforce pricing plan restrictions
        if ($validated['chatbot_intelligence_level'] === 'creative' && $organization->plan !== 'enterprise') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'chatbot_intelligence_level' => 'High Reasoning intelligence level requires an Enterprise subscription plan.'
            ]);
        }

        // Default unchecked checkboxes to false
        $validated['chatbot_escalation_enabled'] = $request->has('chatbot_escalation_enabled');
        $validated['hr_api_connected'] = $request->has('hr_api_connected');

        $settings = OrganizationSetting::firstOrCreate(
            ['organization_id' => $request->user()->organization_id],
            [
                'minutes_saved_per_resolved_question' => 5,
                'assistant_status' => 'active',
                'assistant_name' => 'Aprilo Bot',
            ]
        );

        $settings->update($validated);

        return redirect()
            ->route('admin.settings', ['tab' => $request->query('tab', 'agent')])
            ->with('status', 'Settings updated.');
    }

    public function connectPlatform(Request $request, string $platform): View|\Illuminate\Http\RedirectResponse
    {
        $this->authorizeAdmin($request);
        $organization = $request->user()->organization;
        $plan = $organization->plan;

        // Plan Validation
        if (in_array($platform, ['skype', 'whatsapp'], true) && $plan !== 'enterprise') {
            return redirect()
                ->route('admin.settings', ['tab' => 'pricing'])
                ->withErrors(['plan' => 'Skype and WhatsApp integrations require an Enterprise subscription plan.']);
        }

        if (in_array($platform, ['teams', 'mail'], true) && !in_array($plan, ['pro', 'enterprise'], true)) {
            return redirect()
                ->route('admin.settings', ['tab' => 'pricing'])
                ->withErrors(['plan' => 'Microsoft Teams and SMTP Email integrations require a Pro or Enterprise subscription plan.']);
        }

        if (!in_array($platform, ['teams', 'skype', 'whatsapp', 'mail'], true)) {
            abort(404);
        }

        $settings = OrganizationSetting::firstOrCreate(
            ['organization_id' => $request->user()->organization_id],
            [
                'minutes_saved_per_resolved_question' => 5,
                'assistant_status' => 'active',
                'assistant_name' => 'Aprilo Bot',
            ]
        );

        return view("admin.connect.{$platform}", compact('settings', 'organization'));
    }

    public function updateConnection(Request $request, string $platform): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $organization = $request->user()->organization;
        $plan = $organization->plan;

        // Plan Validation
        if (in_array($platform, ['skype', 'whatsapp'], true) && $plan !== 'enterprise') {
            return redirect()->route('admin.settings', ['tab' => 'pricing']);
        }

        if (in_array($platform, ['teams', 'mail'], true) && !in_array($plan, ['pro', 'enterprise'], true)) {
            return redirect()->route('admin.settings', ['tab' => 'pricing']);
        }

        $settings = OrganizationSetting::where('organization_id', $organization->id)->firstOrFail();

        // Check if user is disconnecting
        if ($request->has('disconnect')) {
            if ($platform === 'teams') {
                $settings->update([
                    'teams_webhook_url' => null,
                    'teams_tenant_id' => null,
                    'teams_app_id' => null,
                    'teams_app_password' => null,
                    'teams_use_webhook' => true,
                    'teams_connected' => false,
                    'connect_teams' => false,
                ]);
            } elseif ($platform === 'skype') {
                $settings->update([
                    'skype_bot_id' => null,
                    'skype_client_secret' => null,
                    'skype_connected' => false,
                    'connect_skype' => false,
                ]);
            } elseif ($platform === 'whatsapp') {
                $settings->update([
                    'whatsapp_phone_number_id' => null,
                    'whatsapp_business_account_id' => null,
                    'whatsapp_access_token' => null,
                    'whatsapp_sender_phone' => null,
                    'whatsapp_message_template' => null,
                    'whatsapp_use_sandbox' => true,
                    'whatsapp_brand_approval_status' => 'pending',
                    'whatsapp_connected' => false,
                    'connect_whatsapp' => false,
                ]);
            } elseif ($platform === 'mail') {
                $settings->update([
                    'mail_smtp_host' => null,
                    'mail_smtp_port' => null,
                    'mail_smtp_username' => null,
                    'mail_smtp_password' => null,
                    'mail_smtp_encryption' => 'tls',
                    'mail_connected' => false,
                    'connect_mail' => false,
                ]);
            }

            return redirect()
                ->route('admin.settings', ['tab' => 'connect'])
                ->with('status', ucfirst($platform) . ' integration disconnected.');
        }

        // Validate platform specific credentials
        if ($platform === 'teams') {
            $useWebhook = $request->has('teams_use_webhook');
            if ($useWebhook) {
                $validated = $request->validate([
                    'teams_webhook_url' => ['required', 'url'],
                ]);
                $validated['teams_use_webhook'] = true;
                $validated['teams_tenant_id'] = null;
                $validated['teams_app_id'] = null;
                $validated['teams_app_password'] = null;
            } else {
                $validated = $request->validate([
                    'teams_tenant_id' => ['required', 'string', 'max:255'],
                    'teams_app_id' => ['required', 'string', 'max:255'],
                    'teams_app_password' => ['required', 'string', 'max:255'],
                ]);
                $validated['teams_use_webhook'] = false;
                $validated['teams_webhook_url'] = null;
            }
            $validated['teams_connected'] = true;
            $validated['connect_teams'] = true;
        } elseif ($platform === 'skype') {
            $validated = $request->validate([
                'skype_bot_id' => ['required', 'string', 'max:255'],
                'skype_client_secret' => ['required', 'string', 'max:500'],
            ]);
            $validated['skype_connected'] = true;
            $validated['connect_skype'] = true;
        } elseif ($platform === 'whatsapp') {
            $useSandbox = $request->has('whatsapp_use_sandbox');
            if ($useSandbox) {
                $validated = [
                    'whatsapp_use_sandbox' => true,
                    'whatsapp_phone_number_id' => null,
                    'whatsapp_business_account_id' => null,
                    'whatsapp_access_token' => null,
                    'whatsapp_sender_phone' => null,
                    'whatsapp_message_template' => null,
                    'whatsapp_brand_approval_status' => 'approved',
                ];
            } else {
                $validated = $request->validate([
                    'whatsapp_phone_number_id' => ['required', 'string', 'max:255'],
                    'whatsapp_business_account_id' => ['required', 'string', 'max:255'],
                    'whatsapp_access_token' => ['required', 'string', 'max:500'],
                    'whatsapp_sender_phone' => ['required', 'string', 'max:255'],
                    'whatsapp_message_template' => ['required', 'string', 'max:255'],
                ]);
                $validated['whatsapp_use_sandbox'] = false;
                
                // If they request approval or save it
                if ($request->has('request_approval')) {
                    $validated['whatsapp_brand_approval_status'] = 'pending';
                } else {
                    $validated['whatsapp_brand_approval_status'] = 'approved';
                }
            }
            $validated['whatsapp_connected'] = true;
            $validated['connect_whatsapp'] = true;
        } elseif ($platform === 'mail') {
            $validated = $request->validate([
                'mail_smtp_host' => ['required', 'string', 'max:255'],
                'mail_smtp_port' => ['required', 'integer', 'min:1', 'max:65535'],
                'mail_smtp_username' => ['required', 'string', 'max:255'],
                'mail_smtp_password' => ['required', 'string', 'max:500'],
                'mail_smtp_encryption' => ['required', Rule::in(['tls', 'ssl', 'none'])],
                'hr_desk_email' => ['required', 'email', 'max:255'],
            ]);
            $validated['mail_connected'] = true;
            $validated['connect_mail'] = true;
        } else {
            abort(400);
        }

        $settings->update($validated);

        return redirect()
            ->route('admin.settings', ['tab' => 'connect'])
            ->with('status', ucfirst($platform) . ' integration successfully configured!');
    }

    public function upgradePlan(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $organization = $request->user()->organization;

        $validated = $request->validate([
            'plan' => ['required', Rule::in(['basic', 'pro', 'enterprise'])],
            'card_number' => ['required', 'string', 'regex:/^\d{16}$/'],
            'card_expiry' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'card_cvv' => ['required', 'string', 'regex:/^\d{3}$/'],
        ], [
            'card_number.regex' => 'Please enter a valid 16-digit credit card number.',
            'card_expiry.regex' => 'Please enter a valid expiration date in MM/YY format.',
            'card_cvv.regex' => 'Please enter a valid 3-digit CVV.',
        ]);

        $organization->update([
            'plan' => $validated['plan'],
        ]);

        return redirect()
            ->route('admin.settings', ['tab' => 'pricing'])
            ->with('status', 'Subscription upgraded successfully! Your plan is now: ' . ucfirst($validated['plan']));
    }

    public function escalations(Request $request): View
    {
        $this->authorizeAdmin($request);

        $hrUsers = \App\Models\User::where('organization_id', $request->user()->organization_id)
            ->whereIn('role', [UserRole::Owner->value, UserRole::HrAdmin->value])
            ->get();

        return view('admin.escalations', [
            'escalations' => Escalation::query()
                ->with(['question.user'])
                ->where('organization_id', $request->user()->organization_id)
                ->latest()
                ->get(),
            'hrUsers' => $hrUsers,
        ]);
    }

    public function updateEscalation(Request $request, Escalation $escalation): RedirectResponse
    {
        $this->authorizeAdmin($request);
        abort_unless($escalation->organization_id === $request->user()->organization_id, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_review', 'resolved', 'closed'])],
            'assigned_to' => ['nullable', 'uuid', Rule::exists('users', 'id')->where('organization_id', $request->user()->organization_id)],
            'resolution_note' => ['nullable', 'string', 'max:5000'],
        ]);

        if (in_array($validated['status'], ['resolved', 'closed'], true) && !in_array($escalation->status, ['resolved', 'closed'], true)) {
            $validated['resolved_at'] = now();
        } elseif (!in_array($validated['status'], ['resolved', 'closed'], true)) {
            $validated['resolved_at'] = null;
        }

        $escalation->update($validated);

        return redirect()
            ->route('admin.escalations')
            ->with('status', 'Escalation updated.');
    }

    public function logs(Request $request): View
    {
        $this->authorizeAdmin($request);

        $query = \App\Models\Question::query()
            ->with(['user', 'answers.sources.source', 'answers.feedback'])
            ->where('organization_id', $request->user()->organization_id);

        if ($request->filled('sensitivity_status')) {
            $query->where('sensitivity_status', $request->input('sensitivity_status'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $logs = $query->latest()->paginate(15)->withQueryString();

        return view('admin.logs', compact('logs'));
    }

    public function leaves(Request $request): View
    {
        $this->authorizeAdmin($request);

        $leaves = \App\Models\LeaveRequest::query()
            ->with(['user', 'approver'])
            ->where('organization_id', $request->user()->organization_id)
            ->latest()
            ->get();

        return view('admin.leaves', compact('leaves'));
    }

    public function updateLeave(Request $request, \App\Models\LeaveRequest $leave): RedirectResponse
    {
        $this->authorizeAdmin($request);
        abort_unless($leave->organization_id === $request->user()->organization_id, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'pending'])],
        ]);

        $leave->update([
            'status' => $validated['status'],
            'approved_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.leaves')
            ->with('status', 'Leave request updated.');
    }

    public function wfh(Request $request): View
    {
        $this->authorizeAdmin($request);

        $wfhRequests = \App\Models\WfhRequest::query()
            ->with(['user', 'approver'])
            ->where('organization_id', $request->user()->organization_id)
            ->latest()
            ->get();

        return view('admin.wfh', compact('wfhRequests'));
    }

    public function updateWfh(Request $request, \App\Models\WfhRequest $wfh): RedirectResponse
    {
        $this->authorizeAdmin($request);
        abort_unless($wfh->organization_id === $request->user()->organization_id, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'pending'])],
        ]);

        $wfh->update([
            'status' => $validated['status'],
            'approved_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.wfh')
            ->with('status', 'WFH request updated.');
    }

    public function employees(Request $request): View
    {
        $this->authorizeAdmin($request);

        $employees = \App\Models\User::query()
            ->with('idCard')
            ->where('organization_id', $request->user()->organization_id)
            ->get();

        return view('admin.employees', [
            'employees' => $employees,
            'searchedEmployee' => null,
            'searchError' => null,
            'validatedEmployee' => null,
        ]);
    }

    public function validateEmployee(Request $request): View
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'employee_id' => ['required', 'string'],
        ]);

        $employee = \App\Models\User::query()
            ->with('idCard')
            ->where('organization_id', $request->user()->organization_id)
            ->where('employee_id', $validated['employee_id'])
            ->first();

        $employees = \App\Models\User::query()
            ->with('idCard')
            ->where('organization_id', $request->user()->organization_id)
            ->get();

        return view('admin.employees', [
            'employees' => $employees,
            'searchedEmployee' => $validated['employee_id'],
            'validatedEmployee' => $employee,
            'searchError' => $employee ? null : 'No employee found with this ID.',
        ]);
    }

    public function updateEmployee(Request $request, \App\Models\User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);
        abort_unless($user->organization_id === $request->user()->organization_id, 404);

        $validated = $request->validate([
            'teams_user_id' => ['nullable', 'string', 'max:255', Rule::unique('users', 'teams_user_id')->ignore($user->id)],
            'escalation_priority' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $validated['escalation_routing_active'] = $request->has('escalation_routing_active');

        $user->update($validated);

        return redirect()
            ->route('admin.employees')
            ->with('status', 'Employee Teams settings updated successfully.');
    }

    public function idcards(Request $request): View
    {
        $this->authorizeAdmin($request);

        $idcards = \App\Models\IdCard::query()
            ->with('user')
            ->where('organization_id', $request->user()->organization_id)
            ->latest()
            ->get();

        // Users who don't have an ID card
        $usersWithoutCard = \App\Models\User::query()
            ->where('organization_id', $request->user()->organization_id)
            ->whereDoesntHave('idCard')
            ->get();

        return view('admin.idcards', compact('idcards', 'usersWithoutCard'));
    }

    public function storeIdCard(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'user_id' => ['required', 'uuid', Rule::exists('users', 'id')->where('organization_id', $request->user()->organization_id)],
            'card_number' => ['required', 'string', 'unique:id_cards,card_number'],
            'issue_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after:issue_date'],
        ]);

        \App\Models\IdCard::create([
            'organization_id' => $request->user()->organization_id,
            'user_id' => $validated['user_id'],
            'card_number' => $validated['card_number'],
            'issue_date' => $validated['issue_date'],
            'expiry_date' => $validated['expiry_date'],
            'status' => 'active',
        ]);

        return redirect()
            ->route('admin.idcards')
            ->with('status', 'ID card issued successfully.');
    }

    public function updateIdCard(Request $request, \App\Models\IdCard $idcard): RedirectResponse
    {
        $this->authorizeAdmin($request);
        abort_unless($idcard->organization_id === $request->user()->organization_id, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'suspended', 'expired'])],
        ]);

        $idcard->update(['status' => $validated['status']]);

        return redirect()
            ->route('admin.idcards')
            ->with('status', 'ID card status updated.');
    }

    public function previewQuestion(
        Request $request,
        \App\Services\AI\SensitivityClassifier $classifier,
        \App\Services\Knowledge\RetrievalService $retrieval,
        \App\Services\AI\AiAnswerService $answers
    ): \Illuminate\Http\JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'question_text' => ['required', 'string', 'max:2000'],
        ]);

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

        $sources = [];
        foreach ($answerPayload['sources'] ?? [] as $src) {
            $sources[] = [
                'citation_label' => $src['citation_label'],
                'title' => $src['source']->title ?? 'Unknown',
                'chunk_text' => $src['chunk']->chunk_text ?? '',
            ];
        }

        return response()->json([
            'answer_text' => $answerPayload['answer_text'],
            'answer_status' => $answerPayload['answer_status'],
            'confidence_label' => $answerPayload['confidence_label'],
            'sources' => $sources,
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            $request->user()
            && in_array($request->user()->role, [UserRole::Owner->value, UserRole::HrAdmin->value], true),
            403,
            'Admin access is required.'
        );
    }
}
