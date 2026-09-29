<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessKnowledgeSource;
use App\Models\Invoice;
use App\Models\AuditEvent;
use App\Models\KnowledgeSource;
use App\Models\Organization;
use App\Models\OrganizationEntitlement;
use App\Models\OrganizationSetting;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Question;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function dashboard(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        $orgCount = Organization::count();
        $totalUsers = User::count();
        $agentCount = \App\Models\AgentSupport::count();
        $activeAgentCount = \App\Models\AgentSupport::where('is_active', true)->count();
        $hrAdminCount = User::whereHas('roleModel', fn ($q) => $q->where('slug', 'hr_admin'))
            ->orWhere('role', 'hr_admin')
            ->count();

        $ecomAdminCount = User::whereHas('roleModel', fn ($q) => $q->where('slug', 'ecommerce_admin'))
            ->orWhere('role', 'ecommerce_admin')
            ->count();

        $employeeCount = User::where('role', 'employee')->count();
        $totalQuestions = Question::count();
        $resolvedByAi = Question::where('status', 'answered')->count();
        $resolvedByAgent = \App\Models\Escalation::whereIn('status', ['resolved', 'closed'])->count();
        $openEscalations = \App\Models\Escalation::whereIn('status', ['open', 'assigned', 'in_progress'])->count();
        $whatsappOrganizations = OrganizationSetting::where('whatsapp_connected', true)->count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('amount');
        if ($totalRevenue == 0) {
            $totalRevenue = $orgCount * 299.00; // Demo fallback revenue calculation
        }

        $organizationQuestionCounts = Question::query()
            ->select('organization_id', DB::raw('count(*) as total'))
            ->groupBy('organization_id')
            ->pluck('total', 'organization_id');
        $organizationResolvedCounts = Question::query()
            ->select('organization_id', DB::raw('count(*) as total'))
            ->where('status', 'answered')
            ->groupBy('organization_id')
            ->pluck('total', 'organization_id');
        $organizationRevenue = Invoice::query()
            ->select('organization_id', DB::raw('sum(amount) as total'))
            ->where('status', 'paid')
            ->groupBy('organization_id')
            ->pluck('total', 'organization_id');

        $organizations = Organization::withCount(['users', 'knowledgeSources', 'questions', 'agentSupports'])
            ->with(['settings'])
            ->latest()
            ->get();

        $organizationAnalytics = $organizations->map(fn (Organization $organization): array => [
            'name' => $organization->name,
            'users' => $organization->users_count,
            'agents' => $organization->agent_supports_count,
            'questions' => (int) ($organizationQuestionCounts[$organization->id] ?? 0),
            'resolved' => (int) ($organizationResolvedCounts[$organization->id] ?? 0),
            'revenue' => (float) ($organizationRevenue[$organization->id] ?? 0),
            'whatsapp' => (bool) ($organization->settings?->whatsapp_connected ?? false),
        ])->values()->all();

        return view('admin.super.dashboard', compact(
            'orgCount',
            'totalUsers',
            'agentCount',
            'activeAgentCount',
            'hrAdminCount',
            'ecomAdminCount',
            'employeeCount',
            'totalQuestions',
            'resolvedByAi',
            'resolvedByAgent',
            'openEscalations',
            'whatsappOrganizations',
            'totalRevenue',
            'organizations',
            'organizationAnalytics'
        ));
    }

    public function organizations(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        $organizations = Organization::withCount(['users', 'knowledgeSources', 'questions'])
            ->with(['settings', 'users'])
            ->latest()
            ->paginate(15);

        return view('admin.super.organizations', compact('organizations'));
    }

    public function organizationDetails(Request $request, Organization $organization): View
    {
        $this->authorizeSuperAdmin($request);

        $owners = $organization->users()
            ->with('roleModel')
            ->get()
            ->filter(fn (User $user): bool => $user->role_slug === 'owner')
            ->values();

        return view('admin.super.organization-details', compact('organization', 'owners'));
    }

    public function organizationWidget(Request $request, Organization $organization): View
    {
        $this->authorizeSuperAdmin($request);
        $settings = OrganizationSetting::firstOrCreate(
            ['organization_id' => $organization->id],
            [
                'assistant_name' => $organization->name . ' Assistant',
                'assistant_status' => 'active',
                'chatbot_color_palette' => '#d22630',
                'chatbot_icon' => 'robot',
            ]
        );
        $widgetKey = $settings->ensureWidgetPublicKey();
        $apiUrl = rtrim((string) config('app.url'), '/');

        return view('admin.super.organization-widget', compact('organization', 'settings', 'widgetKey', 'apiUrl'));
    }

    public function updateOrganizationWidget(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);
        $settings = OrganizationSetting::firstOrCreate(['organization_id' => $organization->id]);
        $validated = $request->validate([
            'assistant_name' => ['required', 'string', 'max:255'],
            'assistant_status' => ['required', Rule::in(['active', 'paused'])],
            'chatbot_color_palette' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'chatbot_icon' => ['required', Rule::in(['robot', 'support', 'star', 'chat'])],
            'live_chat_enabled' => ['nullable', 'boolean'],
        ]);

        $validated['live_chat_enabled'] = $request->boolean('live_chat_enabled');
        $settings->update($validated);

        AuditEvent::create([
            'organization_id' => $organization->id,
            'actor_user_id' => $request->user()->id,
            'event_type' => 'ORGANIZATION_WIDGET_SETTINGS_UPDATED',
            'entity_type' => 'organization_setting',
            'entity_id' => $settings->id,
            'metadata' => [
                'assistant_name' => $settings->assistant_name,
                'assistant_status' => $settings->assistant_status,
                'chatbot_color_palette' => $settings->chatbot_color_palette,
                'chatbot_icon' => $settings->chatbot_icon,
                'live_chat_enabled' => $settings->live_chat_enabled,
            ],
        ]);

        return redirect()->route('admin.super.organizations.widget', $organization)
            ->with('status', 'Widget settings updated for ' . $organization->name . '.');
    }

    public function rotateOrganizationWidgetKey(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);
        $settings = OrganizationSetting::where('organization_id', $organization->id)->firstOrFail();
        $environment = app()->environment('production') ? 'live' : 'test';
        $settings->update(['public_widget_key' => 'pk_' . $environment . '_' . Str::random(32)]);

        AuditEvent::create([
            'organization_id' => $organization->id,
            'actor_user_id' => $request->user()->id,
            'event_type' => 'ORGANIZATION_WIDGET_KEY_ROTATED',
            'entity_type' => 'organization_setting',
            'entity_id' => $settings->id,
            'metadata' => ['environment' => $environment],
        ]);

        return redirect()->route('admin.super.organizations.widget', $organization)
            ->with('status', 'The widget public key was rotated. Update the embed on the customer website.');
    }

    public function organizationKnowledge(Request $request, Organization $organization): View
    {
        $this->authorizeSuperAdmin($request);
        $sources = $organization->knowledgeSources()->withCount('chunks')->latest()->get();

        return view('admin.super.organization-knowledge', compact('organization', 'sources'));
    }

    public function storeOrganizationKnowledge(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'access_scope' => ['required', Rule::in(['all_employees', 'hr_only'])],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $sourceType = $extension === 'md' ? 'markdown' : $extension;
        if (! in_array($sourceType, ['pdf', 'docx', 'txt', 'markdown'], true)) {
            return back()->withErrors(['file' => 'Only PDF, DOCX, TXT, and Markdown files are supported.'])->withInput();
        }

        $source = KnowledgeSource::create([
            'organization_id' => $organization->id,
            'title' => $validated['title'],
            'source_type' => $sourceType,
            'file_path' => $file->store('knowledge-sources'),
            'status' => 'uploaded',
            'access_scope' => $validated['access_scope'],
            'uploaded_by' => $request->user()->id,
            'metadata' => [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'uploaded_by_super_admin' => true,
            ],
        ]);

        if (App::environment(['local', 'testing'])) {
            ProcessKnowledgeSource::dispatchSync($source->id);
        } else {
            ProcessKnowledgeSource::dispatch($source->id);
        }

        AuditEvent::create([
            'organization_id' => $organization->id,
            'actor_user_id' => $request->user()->id,
            'event_type' => 'ORGANIZATION_KNOWLEDGE_SOURCE_UPLOADED',
            'entity_type' => 'knowledge_source',
            'entity_id' => $source->id,
            'metadata' => ['title' => $source->title, 'source_type' => $sourceType],
        ]);

        return redirect()->route('admin.super.organizations.knowledge', $organization)
            ->with('status', 'Knowledge source uploaded for ' . $organization->name . '.');
    }

    public function updateOwnerCredentials(Request $request, Organization $organization, User $user): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);
        abort_unless($user->organization_id === $organization->id && $user->role_slug === 'owner', 404);

        $validated = $request->validate([
            'username' => [
                'nullable', 'required_without_all:email,password', 'string', 'min:3', 'max:30',
                'regex:/^[a-zA-Z0-9_.-]+$/', Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'nullable', 'required_without_all:username,password', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'required_without_all:username,email', 'string', 'min:8', 'confirmed'],
        ]);

        $updates = [];
        $changedFields = [];

        if (filled($validated['username'] ?? null) && strtolower($validated['username']) !== strtolower((string) $user->username)) {
            $updates['username'] = strtolower($validated['username']);
            $changedFields[] = 'username';
        }

        if (filled($validated['email'] ?? null) && strtolower($validated['email']) !== strtolower($user->email)) {
            $updates['email'] = strtolower($validated['email']);
            $updates['email_verified_at'] = null;
            $changedFields[] = 'email';
        }

        if (filled($validated['password'] ?? null)) {
            $updates['password'] = Hash::make($validated['password']);
            $changedFields[] = 'password';
        }

        if (empty($updates)) {
            return back()->withErrors(['credentials' => 'Enter a new username, email, or password.'])->withInput();
        }

        $user->forceFill($updates)->save();

        AuditEvent::create([
            'organization_id' => $organization->id,
            'actor_user_id' => $request->user()->id,
            'event_type' => 'ORGANIZATION_OWNER_CREDENTIALS_UPDATED',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'metadata' => ['updated_fields' => $changedFields],
        ]);

        return redirect()->route('admin.super.organizations.show', $organization)
            ->with('status', 'Organization owner credentials updated.');
    }

    public function storeOrganization(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:190'],
            'country' => ['nullable', 'string', 'max:100'],
            'industry' => ['nullable', 'string', 'max:100'],
            'team_size' => ['nullable', 'string', 'max:50'],
            'timezone' => ['nullable', 'string', 'max:80'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_.-]+$/', 'unique:users,username'],
            'owner_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'owner_password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['active', 'trial', 'suspended'])],
        ]);

        $organization = DB::transaction(function () use ($validated): Organization {
            $organization = Organization::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'website' => $validated['website'] ?? null,
                'country' => $validated['country'] ?? null,
                'industry' => $validated['industry'] ?? null,
                'team_size' => $validated['team_size'] ?? null,
                'timezone' => $validated['timezone'] ?? null,
                'status' => $validated['status'],
            ]);

            OrganizationSetting::create([
                'organization_id' => $organization->id,
                'assistant_name' => $organization->name . ' Assistant',
                'assistant_status' => 'active',
                'live_chat_enabled' => true,
            ]);

            User::create([
                'organization_id' => $organization->id,
                'name' => $validated['owner_name'],
                'username' => strtolower($validated['owner_username']),
                'email' => strtolower($validated['owner_email']),
                'password' => Hash::make($validated['owner_password']),
                'role' => 'owner',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            return $organization;
        });

        AuditEvent::create([
            'organization_id' => $organization->id,
            'actor_user_id' => $request->user()->id,
            'event_type' => 'ORGANIZATION_CREATED_MANUALLY',
            'entity_type' => 'organization',
            'entity_id' => $organization->id,
            'metadata' => ['status' => $organization->status],
        ]);

        return redirect()->route('admin.super.organizations.subscriptions', $organization)
            ->with('status', "Organization '{$organization->name}' created. Grant a subscription to enable features.");
    }

    public function updateOrgStatus(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'status' => ['required', 'in:active,suspended,trial'],
            'plan' => ['required', 'in:starter,growth,enterprise'],
        ]);

        $organization->update($validated);

        return back()->with('status', "Organization '{$organization->name}' updated successfully.");
    }

    public function subscriptionAccess(Request $request, Organization $organization): View
    {
        $this->authorizeSuperAdmin($request);

        return view('admin.super.subscription-access', [
            'organization' => $organization,
            'products' => Product::where('is_active', true)->orderBy('sort_order')->get(),
            'plans' => Plan::with('product')->where('is_active', true)->orderBy('sort_order')->get(),
            'subscriptions' => $organization->subscriptions()->with(['product', 'plan'])->latest()->get(),
            'entitlements' => $organization->entitlements()->where('status', 'active')->latest()->get(),
            'featureOptions' => [
                'support' => 'Support platform',
                'support.ai' => 'AI support assistant',
                'support.agents' => 'Live chat support agents',
                'support.website_chat' => 'Website chat',
                'support.whatsapp' => 'WhatsApp support',
                'support.analytics' => 'Support analytics',
                'ecommerce' => 'E-commerce tools',
            ],
        ]);
    }

    public function grantSubscription(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'plan_id' => ['required', 'uuid', Rule::exists('plans', 'id')->where(fn ($query) => $query->where('is_active', true))],
            'status' => ['required', Rule::in(['active', 'trialing', 'past_due', 'cancelled'])],
            'ends_at' => ['nullable', 'date'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', Rule::in(['support', 'support.ai', 'support.agents', 'support.website_chat', 'support.whatsapp', 'support.analytics', 'ecommerce'])],
            'max_agents' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $plan = Plan::where('product_id', $product->id)->findOrFail($validated['plan_id']);
        $features = $validated['features'] ?? [];
        $features = array_values(array_unique(array_merge($features, $product->category === 'ai_support' ? ['support'] : [])));

        $subscription = DB::transaction(function () use ($organization, $product, $plan, $validated, $features, $request): Subscription {
            $subscription = Subscription::create([
                'organization_id' => $organization->id,
                'product_id' => $product->id,
                'plan_id' => $plan->id,
                'razorpay_subscription_id' => 'manual_' . Str::lower(Str::random(32)),
                'razorpay_plan_id' => $plan->razorpay_plan_id,
                'status' => $validated['status'],
                'trial_start' => $validated['status'] === 'trialing' ? now() : null,
                'trial_end' => $validated['status'] === 'trialing' ? ($validated['ends_at'] ?? now()->addDays($plan->trial_period_days ?: 30)) : null,
                'current_cycle_start' => now(),
                'current_cycle_end' => $validated['ends_at'] ?? now()->addMonth(),
                'price' => $plan->price,
                'currency' => $plan->currency,
                'request_limit' => $plan->request_limit,
                'auto_renew' => false,
                'metadata' => ['manual_grant' => true, 'granted_by' => $request->user()->id],
            ]);

            foreach ($features as $feature) {
                OrganizationEntitlement::create([
                    'organization_id' => $organization->id,
                    'subscription_id' => $subscription->id,
                    'product_id' => $product->id,
                    'feature_key' => $feature,
                    'status' => $validated['status'] === 'cancelled' ? 'suspended' : 'active',
                    'limits' => $feature === 'support.agents' && isset($validated['max_agents']) ? ['max_agents' => $validated['max_agents']] : null,
                    'starts_at' => now(),
                    'ends_at' => $validated['ends_at'] ?? null,
                ]);
            }

            return $subscription;
        });

        AuditEvent::create([
            'organization_id' => $organization->id,
            'actor_user_id' => $request->user()->id,
            'event_type' => 'MANUAL_SUBSCRIPTION_GRANTED',
            'entity_type' => 'subscription',
            'entity_id' => $subscription->id,
            'metadata' => [
                'product_id' => $product->id,
                'plan_id' => $plan->id,
                'features' => $features,
            ],
        ]);

        return redirect()->route('admin.super.organizations.subscriptions', $organization)
            ->with('status', "Subscription access granted to '{$organization->name}'.");
    }

    public function updatePaymentStatus(Request $request, Organization $organization, Subscription $subscription): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);
        abort_unless($subscription->organization_id === $organization->id, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'trialing', 'past_due', 'cancelled', 'halted'])],
        ]);

        DB::transaction(function () use ($subscription, $validated): void {
            $subscription->update(['status' => $validated['status']]);
            $subscription->organization->entitlements()
                ->where('subscription_id', $subscription->id)
                ->update(['status' => in_array($validated['status'], ['active', 'trialing'], true) ? 'active' : 'suspended']);
        });

        AuditEvent::create([
            'organization_id' => $organization->id,
            'actor_user_id' => $request->user()->id,
            'event_type' => 'PAYMENT_STATUS_CHANGED_MANUALLY',
            'entity_type' => 'subscription',
            'entity_id' => $subscription->id,
            'metadata' => ['status' => $validated['status']],
        ]);

        return back()->with('status', 'Subscription payment status updated.');
    }

    public function revenue(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        $invoices = Invoice::with('organization')->latest()->paginate(20);
        $totalPaid = Invoice::where('status', 'paid')->sum('amount');
        $totalPending = Invoice::whereIn('status', ['open', 'unpaid'])->sum('amount');

        return view('admin.super.revenue', compact('invoices', 'totalPaid', 'totalPending'));
    }

    public function logs(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        $logs = Question::with(['organization', 'user', 'answers'])
            ->latest()
            ->paginate(20);

        return view('admin.super.logs', compact('logs'));
    }

    public function cache(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        return view('admin.super.cache');
    }

    public function clearCache(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'cache' => ['required', Rule::in(['application', 'config', 'route', 'view', 'event', 'all'])],
        ]);

        $commands = match ($validated['cache']) {
            'application' => ['cache:clear'],
            'config' => ['config:clear'],
            'route' => ['route:clear'],
            'view' => ['view:clear'],
            'event' => ['event:clear'],
            'all' => ['optimize:clear'],
        };

        foreach ($commands as $command) {
            Artisan::call($command);
        }

        $labels = [
            'application' => 'application cache',
            'config' => 'configuration cache',
            'route' => 'route cache',
            'view' => 'compiled view cache',
            'event' => 'event cache',
            'all' => 'all Laravel caches',
        ];

        return redirect()->route('admin.super.cache')
            ->with('status', ucfirst($labels[$validated['cache']]) . ' cleared successfully.');
    }

    private function authorizeSuperAdmin(Request $request): void
    {
        abort_unless(
            $request->user() && $request->user()->isSuperAdmin(),
            403,
            'Super Administrator access is required.'
        );
    }
}
