<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Support\SupportPortalSetting;
use App\Models\Support\SupportSlaPolicy;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketCategory;
use App\Models\Support\SupportTicketForm;
use App\Models\Support\SupportTicketFormVersion;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportAdminController extends Controller
{
    /**
     * Authorize support administration access.
     */
    private function authorizeSupportAdmin(Request $request): Organization
    {
        $user = $request->user();
        abort_unless($user, 401);

        $isAuthorized = $user->isSuperAdmin()
            || $user->role_slug === 'owner'
            || $user->hasPermission('support.settings.manage')
            || $user->hasPermission('support.forms.manage')
            || $user->hasPermission('support.sla.manage')
            || $user->hasPermission('support.dashboard.view')
            || $user->hasPermission('dashboard.view');

        abort_unless($isAuthorized, 403, 'Unauthorized. Support administrator privileges required.');

        $orgId = $request->input('organization_id', $request->query('organization_id'));

        // For Super Admin, allow organization switching via input or query param
        if ($user->isSuperAdmin()) {
            if ($orgId) {
                $org = Organization::find($orgId);
                if ($org) {
                    return $org;
                }
            }
        } else {
            // For Owner or non-super admin, enforce organization boundary
            if ($orgId) {
                abort_unless($user->organization_id === $orgId, 403, 'Unauthorized access to another organization.');
            }
        }

        abort_unless($user->organization, 403, 'User does not belong to an active organization.');

        return $user->organization;
    }

    /**
     * View Support Portal Settings & SLA Policies.
     */
    public function settings(Request $request): View
    {
        $user = $request->user();
        abort_unless($user, 401);

        $isAuthorized = $user->isSuperAdmin()
            || $user->role_slug === 'owner'
            || $user->hasPermission('support.settings.manage')
            || $user->hasPermission('support.forms.manage')
            || $user->hasPermission('support.sla.manage')
            || $user->hasPermission('support.dashboard.view')
            || $user->hasPermission('dashboard.view');

        abort_unless($isAuthorized, 403, 'Unauthorized. Support administrator privileges required.');

        $isSuperAdmin = $user->isSuperAdmin();

        // Super Admin sees all organizations in the select field; Owner sees only their own organization
        if ($isSuperAdmin) {
            $organizations = Organization::orderBy('name')->get(['id', 'name']);
        } else {
            $organizations = $user->organization ? collect([$user->organization]) : collect();
        }

        $organization = null;
        $orgId = $request->input('organization_id', $request->query('organization_id'));

        if ($isSuperAdmin) {
            if ($orgId) {
                $organization = Organization::find($orgId);
            }
        } else {
            if ($orgId) {
                abort_unless($user->organization_id === $orgId, 403, 'Unauthorized access to another organization.');
                $organization = $user->organization;
            } elseif (! $request->has('organization_id')) {
                // Default to owner's organization if not explicitly unselected
                $organization = $user->organization;
            }
        }

        $portalSettings = null;
        $categories = collect();
        $forms = collect();
        $slaPolicies = collect();

        if ($organization) {
            $portalSettings = SupportPortalSetting::firstOrCreate(
                ['organization_id' => $organization->id],
                [
                    'portal_title' => $organization->name . ' Support Center',
                    'portal_subheading' => 'Submit a support ticket or track your requests',
                    'portal_slug' => Str::slug($organization->name ?: 'support') . '-' . substr($organization->id, 0, 4),
                    'support_email' => $organization->email,
                    'primary_color' => '#22C55E',
                    'secondary_color' => '#0F172A',
                    'allow_anonymous_tickets' => true,
                    'require_email_verification' => false,
                    'enable_customer_portal_auth' => true,
                    'is_portal_active' => true,
                ]
            );

            $categories = SupportTicketCategory::where('organization_id', $organization->id)->orderBy('name')->get();
            $forms = SupportTicketForm::where('organization_id', $organization->id)->orderBy('name')->get();
            $slaPolicies = SupportSlaPolicy::where('organization_id', $organization->id)->get()->keyBy('priority');
        }

        return view('admin.support.settings', [
            'organization' => $organization,
            'organizations' => $organizations,
            'isSuperAdmin' => $isSuperAdmin,
            'portalSettings' => $portalSettings,
            'categories' => $categories,
            'forms' => $forms,
            'slaPolicies' => $slaPolicies,
            'settings' => $organization?->settings,
        ]);
    }

    /**
     * Update Support Portal Branding & Customer Tracking Settings.
     */
    public function updatePortalSettings(Request $request): RedirectResponse
    {
        $organization = $this->authorizeSupportAdmin($request);

        $validated = $request->validate([
            'portal_title' => ['required', 'string', 'max:120'],
            'portal_subheading' => ['nullable', 'string', 'max:255'],
            'portal_slug' => ['required', 'string', 'max:64', 'alpha_dash'],
            'support_email' => ['nullable', 'email', 'max:120'],
            'primary_color' => ['required', 'string', 'max:32'],
            'secondary_color' => ['nullable', 'string', 'max:32'],
            'logo_url' => ['nullable', 'url', 'max:255'],
            'favicon_url' => ['nullable', 'url', 'max:255'],
            'default_form_id' => ['nullable', 'uuid'],
            'allow_anonymous_tickets' => ['nullable', 'boolean'],
            'require_email_verification' => ['nullable', 'boolean'],
            'enable_customer_portal_auth' => ['nullable', 'boolean'],
            'is_portal_active' => ['nullable', 'boolean'],
        ]);

        // Check portal slug uniqueness across other organizations
        $existingSlug = SupportPortalSetting::where('portal_slug', $validated['portal_slug'])
            ->where('organization_id', '!=', $organization->id)
            ->first();

        if ($existingSlug) {
            return back()->withInput()->withErrors(['portal_slug' => 'This portal URL slug is already taken. Please choose another.']);
        }

        $portalSettings = SupportPortalSetting::firstOrCreate(['organization_id' => $organization->id]);
        $portalSettings->update([
            'portal_title' => $validated['portal_title'],
            'portal_subheading' => $validated['portal_subheading'] ?? null,
            'portal_slug' => strtolower($validated['portal_slug']),
            'support_email' => $validated['support_email'] ?? null,
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'] ?: '#0F172A',
            'logo_url' => $validated['logo_url'] ?? null,
            'favicon_url' => $validated['favicon_url'] ?? null,
            'default_form_id' => $validated['default_form_id'] ?? null,
            'allow_anonymous_tickets' => $request->boolean('allow_anonymous_tickets'),
            'require_email_verification' => $request->boolean('require_email_verification'),
            'enable_customer_portal_auth' => $request->boolean('enable_customer_portal_auth'),
            'is_portal_active' => $request->boolean('is_portal_active', true),
        ]);

        return redirect()->route('admin.support.settings', $request->user()->isSuperAdmin() ? ['organization_id' => $organization->id] : [])
            ->with('status', 'Support Portal settings and branding successfully saved.');
    }

    /**
     * Store or Update Ticket Category & Department Mapping.
     */
    public function storeCategory(Request $request): RedirectResponse
    {
        $organization = $this->authorizeSupportAdmin($request);

        $validated = $request->validate([
            'category_id' => ['nullable', 'uuid'],
            'name' => ['required', 'string', 'max:80'],
            'department' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
            'default_form_id' => ['nullable', 'uuid'],
        ]);

        $slug = Str::slug($validated['name']);

        if (! empty($validated['category_id'])) {
            $category = SupportTicketCategory::where('organization_id', $organization->id)
                ->where('id', $validated['category_id'])
                ->firstOrFail();

            $category->update([
                'name' => $validated['name'],
                'department' => $validated['department'] ?? null,
                'description' => $validated['description'] ?? null,
                'default_form_id' => $validated['default_form_id'] ?? null,
            ]);
            $msg = 'Support category updated.';
        } else {
            SupportTicketCategory::create([
                'organization_id' => $organization->id,
                'name' => $validated['name'],
                'slug' => $slug,
                'department' => $validated['department'] ?? 'General Support',
                'description' => $validated['description'] ?? null,
                'default_form_id' => $validated['default_form_id'] ?? null,
                'is_active' => true,
            ]);
            $msg = 'Support category created successfully.';
        }

        return redirect()->route('admin.support.settings', $request->user()->isSuperAdmin() ? ['organization_id' => $organization->id] : [])->with('status', $msg);
    }

    /**
     * Delete Ticket Category.
     */
    public function deleteCategory(Request $request, SupportTicketCategory $category): RedirectResponse
    {
        $organization = $this->authorizeSupportAdmin($request);
        abort_unless($category->organization_id === $organization->id, 403);

        $category->delete();

        return redirect()->route('admin.support.settings', $request->user()->isSuperAdmin() ? ['organization_id' => $organization->id] : [])->with('status', 'Category deleted successfully.');
    }

    /**
     * Forms Management: List Dynamic Forms.
     */
    public function formsIndex(Request $request): View
    {
        $organization = $this->authorizeSupportAdmin($request);

        $forms = SupportTicketForm::where('organization_id', $organization->id)
            ->with(['category', 'createdBy', 'updatedBy'])
            ->orderByDesc('updated_at')
            ->get();

        $portalSettings = SupportPortalSetting::firstOrCreate(['organization_id' => $organization->id]);

        return view('admin.support.forms.index', [
            'organization' => $organization,
            'forms' => $forms,
            'portalSettings' => $portalSettings,
        ]);
    }

    /**
     * Render Form Builder for creating a new form.
     */
    public function createForm(Request $request): View
    {
        $organization = $this->authorizeSupportAdmin($request);
        $categories = SupportTicketCategory::where('organization_id', $organization->id)->orderBy('name')->get();

        return view('admin.support.forms.builder', [
            'organization' => $organization,
            'form' => new SupportTicketForm([
                'name' => 'General Support Inquiry',
                'description' => 'Default customer support request form',
                'schema' => SupportTicketForm::getDefaultStarterSchema(),
                'status' => 'draft',
                'version' => 1,
            ]),
            'categories' => $categories,
            'isNew' => true,
        ]);
    }

    /**
     * Save newly created dynamic form.
     */
    public function storeForm(Request $request): RedirectResponse
    {
        $organization = $this->authorizeSupportAdmin($request);
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'uuid'],
            'status' => ['required', 'in:draft,published'],
            'schema' => ['required', 'string'], // JSON encoded field definitions
            'settings' => ['nullable', 'string'], // JSON encoded settings
            'is_default' => ['nullable', 'boolean'],
        ]);

        $schemaArray = json_decode($validated['schema'], true) ?: [];
        $settingsArray = ! empty($validated['settings']) ? json_decode($validated['settings'], true) : [];

        $slug = Str::slug($validated['name']) . '-' . Str::random(6);

        $form = SupportTicketForm::create([
            'organization_id' => $organization->id,
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'version' => 1,
            'is_default' => $request->boolean('is_default'),
            'schema' => $schemaArray,
            'settings' => $settingsArray,
            'published_at' => $validated['status'] === 'published' ? now() : null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        if ($form->status === 'published') {
            SupportTicketFormVersion::create([
                'form_id' => $form->id,
                'organization_id' => $organization->id,
                'version' => 1,
                'schema' => $schemaArray,
                'change_summary' => 'Initial publication',
                'published_by' => $user->id,
            ]);
        }

        return redirect()->route('admin.support.forms')
            ->with('status', "Form '{$form->name}' created successfully.");
    }

    /**
     * Render Form Builder for an existing form.
     */
    public function editForm(Request $request, SupportTicketForm $form): View
    {
        $organization = $this->authorizeSupportAdmin($request);
        abort_unless($form->organization_id === $organization->id, 403);

        $categories = SupportTicketCategory::where('organization_id', $organization->id)->orderBy('name')->get();

        return view('admin.support.forms.builder', [
            'organization' => $organization,
            'form' => $form,
            'categories' => $categories,
            'isNew' => false,
        ]);
    }

    /**
     * Update an existing dynamic form.
     */
    public function updateForm(Request $request, SupportTicketForm $form): RedirectResponse
    {
        $organization = $this->authorizeSupportAdmin($request);
        abort_unless($form->organization_id === $organization->id, 403);
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'uuid'],
            'status' => ['required', 'in:draft,published,archived'],
            'schema' => ['required', 'string'],
            'settings' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
            'change_summary' => ['nullable', 'string', 'max:255'],
        ]);

        $schemaArray = json_decode($validated['schema'], true) ?: [];
        $settingsArray = ! empty($validated['settings']) ? json_decode($validated['settings'], true) : [];

        $wasPublished = $form->status === 'published';
        $nowPublished = $validated['status'] === 'published';
        $newVersion = $form->version;

        if ($nowPublished && (! $wasPublished || $schemaArray !== $form->schema)) {
            $newVersion = $form->version + 1;

            SupportTicketFormVersion::create([
                'form_id' => $form->id,
                'organization_id' => $organization->id,
                'version' => $newVersion,
                'schema' => $schemaArray,
                'change_summary' => $validated['change_summary'] ?? "Updated version v{$newVersion}",
                'published_by' => $user->id,
            ]);
        }

        $form->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'status' => $validated['status'],
            'version' => $newVersion,
            'is_default' => $request->boolean('is_default'),
            'schema' => $schemaArray,
            'settings' => $settingsArray,
            'published_at' => $nowPublished && ! $form->published_at ? now() : $form->published_at,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('admin.support.forms')
            ->with('status', "Form '{$form->name}' updated successfully.");
    }

    /**
     * Duplicate an existing form.
     */
    public function duplicateForm(Request $request, SupportTicketForm $form): RedirectResponse
    {
        $organization = $this->authorizeSupportAdmin($request);
        abort_unless($form->organization_id === $organization->id, 403);
        $user = $request->user();

        $newForm = SupportTicketForm::create([
            'organization_id' => $organization->id,
            'category_id' => $form->category_id,
            'name' => $form->name . ' (Copy)',
            'slug' => Str::slug($form->name) . '-copy-' . Str::random(5),
            'description' => $form->description,
            'status' => 'draft',
            'version' => 1,
            'is_default' => false,
            'schema' => $form->schema,
            'settings' => $form->settings,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('admin.support.forms')
            ->with('status', "Duplicated form as draft: '{$newForm->name}'.");
    }

    /**
     * Publish a draft form.
     */
    public function publishForm(Request $request, SupportTicketForm $form): RedirectResponse
    {
        $organization = $this->authorizeSupportAdmin($request);
        abort_unless($form->organization_id === $organization->id, 403);
        $user = $request->user();

        $newVersion = $form->version + 1;
        $form->update([
            'status' => 'published',
            'version' => $newVersion,
            'published_at' => now(),
            'updated_by' => $user->id,
        ]);

        SupportTicketFormVersion::create([
            'form_id' => $form->id,
            'organization_id' => $organization->id,
            'version' => $newVersion,
            'schema' => $form->schema,
            'change_summary' => "Published version v{$newVersion}",
            'published_by' => $user->id,
        ]);

        return redirect()->route('admin.support.forms')
            ->with('status', "Form '{$form->name}' published successfully as v{$newVersion}.");
    }

    /**
     * Archive an existing form.
     */
    public function archiveForm(Request $request, SupportTicketForm $form): RedirectResponse
    {
        $organization = $this->authorizeSupportAdmin($request);
        abort_unless($form->organization_id === $organization->id, 403);

        $form->update([
            'status' => 'archived',
            'archived_at' => now(),
            'is_default' => false,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.support.forms')
            ->with('status', "Form '{$form->name}' archived successfully.");
    }

    /**
     * Interactive Form Preview.
     */
    public function previewForm(Request $request, SupportTicketForm $form): View
    {
        $organization = $this->authorizeSupportAdmin($request);
        abort_unless($form->organization_id === $organization->id, 403);

        $portalSettings = SupportPortalSetting::firstOrCreate(['organization_id' => $organization->id]);

        return view('admin.support.forms.preview', [
            'organization' => $organization,
            'form' => $form,
            'portalSettings' => $portalSettings,
        ]);
    }

    /**
     * View Support Ticket Reports & Live Queue Monitor.
     */
    public function ticketReports(Request $request): View|StreamedResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $isAuthorized = $user->isSuperAdmin()
            || $user->role_slug === 'owner'
            || $user->hasPermission('support.dashboard.view')
            || $user->hasPermission('support.tickets.view')
            || $user->hasPermission('support.settings.manage')
            || $user->hasPermission('dashboard.view');

        abort_unless($isAuthorized, 403, 'Unauthorized. Support administrator privileges required.');

        $isSuperAdmin = $user->isSuperAdmin();

        if ($isSuperAdmin) {
            $organizations = Organization::orderBy('name')->get(['id', 'name']);
        } else {
            $organizations = $user->organization ? collect([$user->organization]) : collect();
        }

        $orgId = $request->input('organization_id', $request->query('organization_id'));
        $organization = null;

        if ($isSuperAdmin) {
            if ($orgId) {
                $organization = Organization::find($orgId);
            }
        } else {
            if ($orgId) {
                abort_unless($user->organization_id === $orgId, 403, 'Unauthorized access to another organization.');
                $organization = $user->organization;
            } else {
                $organization = $user->organization;
            }
        }

        // Date range filter
        $range = $request->query('range', '30d');
        $rangeStart = match ($range) {
            'today' => now()->startOfDay(),
            '7d' => now()->subDays(7)->startOfDay(),
            '30d' => now()->subDays(30)->startOfDay(),
            '90d' => now()->subDays(90)->startOfDay(),
            default => null, // 'all'
        };

        $baseQuery = SupportTicket::query();
        if ($organization) {
            $baseQuery->where('organization_id', $organization->id);
        }

        // Core volume counts
        $totalTickets = (clone $baseQuery)->count();
        $createdInPeriod = $rangeStart ? (clone $baseQuery)->where('created_at', '>=', $rangeStart)->count() : $totalTickets;

        $resolvedQuery = (clone $baseQuery)->whereIn('status', ['resolved', 'closed']);
        $totalResolved = (clone $resolvedQuery)->count();
        $resolvedInPeriod = $rangeStart ? (clone $resolvedQuery)->where(function ($q) use ($rangeStart) {
            $q->where('resolved_at', '>=', $rangeStart)
              ->orWhere(function ($sq) use ($rangeStart) {
                  $sq->whereNull('resolved_at')->where('updated_at', '>=', $rangeStart);
              });
        })->count() : $totalResolved;

        $resolutionRate = $totalTickets > 0 ? round(($totalResolved / $totalTickets) * 100, 1) : 0;

        $openTickets = (clone $baseQuery)->whereIn('status', ['new', 'open', 'in_progress'])->count();
        $pendingTickets = (clone $baseQuery)->whereIn('status', ['pending_customer', 'pending_internal'])->count();
        $urgentTickets = (clone $baseQuery)->where('priority', 'urgent')->whereNotIn('status', ['resolved', 'closed'])->count();
        $highTickets = (clone $baseQuery)->where('priority', 'high')->whereNotIn('status', ['resolved', 'closed'])->count();

        // SLA Performance metrics
        $slaBreached = (clone $baseQuery)->whereNotIn('status', ['resolved', 'closed'])
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', now())
            ->count();

        $slaTickets = (clone $baseQuery)->whereNotNull('sla_due_at')->get(['resolved_at', 'sla_due_at', 'created_at', 'first_response_at']);
        $slaMet = 0;
        $slaTotal = 0;
        $totalResponseMin = 0;
        $responseCount = 0;
        $totalResolutionMin = 0;
        $resolutionCount = 0;

        foreach ($slaTickets as $t) {
            if ($t->resolved_at) {
                $slaTotal++;
                if ($t->resolved_at <= $t->sla_due_at) {
                    $slaMet++;
                }
                if ($t->created_at) {
                    $totalResolutionMin += $t->created_at->diffInMinutes($t->resolved_at);
                    $resolutionCount++;
                }
            }
            if ($t->first_response_at && $t->created_at) {
                $totalResponseMin += $t->created_at->diffInMinutes($t->first_response_at);
                $responseCount++;
            }
        }

        $slaComplianceRate = $slaTotal > 0 ? round(($slaMet / $slaTotal) * 100, 1) : 100;
        $avgFirstResponseHours = $responseCount > 0 ? round(($totalResponseMin / $responseCount) / 60, 1) : 0;
        $avgResolutionHours = $resolutionCount > 0 ? round(($totalResolutionMin / $resolutionCount) / 60, 1) : 0;

        // Status Counts
        $statusCounts = [
            'new' => (clone $baseQuery)->where('status', 'new')->count(),
            'open' => (clone $baseQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'pending_customer' => (clone $baseQuery)->where('status', 'pending_customer')->count(),
            'pending_internal' => (clone $baseQuery)->where('status', 'pending_internal')->count(),
            'resolved' => (clone $baseQuery)->where('status', 'resolved')->count(),
            'closed' => (clone $baseQuery)->where('status', 'closed')->count(),
        ];

        // Priority Counts
        $priorityCounts = [
            'urgent' => (clone $baseQuery)->where('priority', 'urgent')->count(),
            'high' => (clone $baseQuery)->where('priority', 'high')->count(),
            'normal' => (clone $baseQuery)->where('priority', 'normal')->count(),
            'low' => (clone $baseQuery)->where('priority', 'low')->count(),
        ];

        // Channel Counts
        $channelCounts = [
            'portal' => (clone $baseQuery)->where('channel', 'portal')->count(),
            'website' => (clone $baseQuery)->where('channel', 'website')->count(),
            'email' => (clone $baseQuery)->where('channel', 'email')->count(),
            'whatsapp' => (clone $baseQuery)->where('channel', 'whatsapp')->count(),
        ];

        // Category breakdown
        $categories = SupportTicketCategory::where(function ($q) use ($organization) {
            if ($organization) {
                $q->where('organization_id', $organization->id);
            }
        })->get();

        $categoryStats = $categories->map(function ($cat) use ($baseQuery) {
            $catTotal = (clone $baseQuery)->where('category_id', $cat->id)->count();
            $catResolved = (clone $baseQuery)->where('category_id', $cat->id)->whereIn('status', ['resolved', 'closed'])->count();
            return [
                'name' => $cat->name,
                'department' => $cat->department ?? 'General',
                'total' => $catTotal,
                'resolved' => $catResolved,
                'rate' => $catTotal > 0 ? round(($catResolved / $catTotal) * 100, 1) : 0,
            ];
        })->sortByDesc('total')->values();

        // Agent workload
        $agentIds = (clone $baseQuery)->whereNotNull('assigned_to')->distinct()->pluck('assigned_to');
        $agents = User::whereIn('id', $agentIds)->get();
        $agentStats = $agents->map(function ($agent) use ($baseQuery) {
            $assigned = (clone $baseQuery)->where('assigned_to', $agent->id)->count();
            $resolved = (clone $baseQuery)->where('assigned_to', $agent->id)->whereIn('status', ['resolved', 'closed'])->count();
            $open = (clone $baseQuery)->where('assigned_to', $agent->id)->whereNotIn('status', ['resolved', 'closed'])->count();
            return [
                'id' => $agent->id,
                'name' => $agent->name,
                'email' => $agent->email,
                'assigned' => $assigned,
                'open' => $open,
                'resolved' => $resolved,
                'rate' => $assigned > 0 ? round(($resolved / $assigned) * 100, 1) : 0,
            ];
        })->sortByDesc('assigned')->values();

        // 7-day daily trend (created vs resolved)
        $dailyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayStart = now()->subDays($i)->startOfDay();
            $dayEnd = now()->subDays($i)->endOfDay();

            $created = (clone $baseQuery)->whereBetween('created_at', [$dayStart, $dayEnd])->count();
            $resolved = (clone $baseQuery)->whereIn('status', ['resolved', 'closed'])
                ->where(function ($q) use ($dayStart, $dayEnd) {
                    $q->whereBetween('resolved_at', [$dayStart, $dayEnd])
                      ->orWhere(function ($sq) use ($dayStart, $dayEnd) {
                          $sq->whereNull('resolved_at')->whereBetween('updated_at', [$dayStart, $dayEnd]);
                      });
                })->count();

            $dailyTrend[] = [
                'date' => now()->subDays($i)->format('M d'),
                'day' => now()->subDays($i)->format('D'),
                'created' => $created,
                'resolved' => $resolved,
            ];
        }

        // Live monitor query with filters
        $monitorQuery = (clone $baseQuery)
            ->with(['customer', 'category', 'assignedAgent', 'organization'])
            ->orderByDesc('created_at');

        $statusFilter = $request->query('status_filter');
        if ($statusFilter) {
            if ($statusFilter === 'open') {
                $monitorQuery->whereNotIn('status', ['resolved', 'closed']);
            } elseif ($statusFilter === 'urgent') {
                $monitorQuery->where('priority', 'urgent')->whereNotIn('status', ['resolved', 'closed']);
            } elseif ($statusFilter === 'unassigned') {
                $monitorQuery->whereNull('assigned_to')->whereNotIn('status', ['resolved', 'closed']);
            } elseif ($statusFilter === 'resolved') {
                $monitorQuery->whereIn('status', ['resolved', 'closed']);
            } else {
                $monitorQuery->where('status', $statusFilter);
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $monitorQuery->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Handle CSV Export
        if ($request->query('export') === 'csv') {
            $ticketsToExport = $monitorQuery->limit(1000)->get();
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="support-tickets-report-' . now()->format('Y-m-d') . '.csv"',
            ];

            return response()->stream(function () use ($ticketsToExport) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Ticket Number', 'Organization', 'Subject', 'Customer', 'Channel', 'Department', 'Priority', 'Status', 'Assigned To', 'Created At', 'Resolved At', 'SLA Due At']);
                foreach ($ticketsToExport as $ticket) {
                    fputcsv($handle, [
                        $ticket->ticket_number,
                        $ticket->organization?->name ?? 'N/A',
                        $ticket->subject,
                        $ticket->customer?->name ?? 'Anonymous',
                        $ticket->channel,
                        $ticket->department ?? 'General',
                        ucfirst($ticket->priority),
                        ucfirst(str_replace('_', ' ', $ticket->status)),
                        $ticket->assignedAgent?->name ?? 'Unassigned',
                        $ticket->created_at?->format('Y-m-d H:i:s'),
                        $ticket->resolved_at?->format('Y-m-d H:i:s'),
                        $ticket->sla_due_at?->format('Y-m-d H:i:s'),
                    ]);
                }
                fclose($handle);
            }, 200, $headers);
        }

        $tickets = $monitorQuery->paginate(15)->withQueryString();

        return view('admin.support.reports', [
            'organization' => $organization,
            'organizations' => $organizations,
            'isSuperAdmin' => $isSuperAdmin,
            'range' => $range,
            'statusFilter' => $statusFilter,
            'search' => $request->query('search', ''),
            'totalTickets' => $totalTickets,
            'createdInPeriod' => $createdInPeriod,
            'totalResolved' => $totalResolved,
            'resolvedInPeriod' => $resolvedInPeriod,
            'resolutionRate' => $resolutionRate,
            'openTickets' => $openTickets,
            'pendingTickets' => $pendingTickets,
            'urgentTickets' => $urgentTickets,
            'highTickets' => $highTickets,
            'slaBreached' => $slaBreached,
            'slaComplianceRate' => $slaComplianceRate,
            'avgFirstResponseHours' => $avgFirstResponseHours,
            'avgResolutionHours' => $avgResolutionHours,
            'statusCounts' => $statusCounts,
            'priorityCounts' => $priorityCounts,
            'channelCounts' => $channelCounts,
            'categoryStats' => $categoryStats,
            'agentStats' => $agentStats,
            'dailyTrend' => $dailyTrend,
            'tickets' => $tickets,
        ]);
    }
}
