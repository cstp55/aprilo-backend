<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Support\SupportPortalSetting;
use App\Models\Support\SupportSlaPolicy;
use App\Models\Support\SupportTicketCategory;
use App\Models\Support\SupportTicketForm;
use App\Models\Support\SupportTicketFormVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

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

        // For Super Admin, allow optional organization switching via query param
        if ($user->isSuperAdmin() && $request->filled('organization_id')) {
            $org = Organization::find($request->query('organization_id'));
            if ($org) {
                return $org;
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
        $organization = $this->authorizeSupportAdmin($request);

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

        return view('admin.support.settings', [
            'organization' => $organization,
            'portalSettings' => $portalSettings,
            'categories' => $categories,
            'forms' => $forms,
            'slaPolicies' => $slaPolicies,
            'settings' => $organization->settings,
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

        return redirect()->route('admin.support.settings')
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

        return redirect()->route('admin.support.settings')->with('status', $msg);
    }

    /**
     * Delete Ticket Category.
     */
    public function deleteCategory(Request $request, SupportTicketCategory $category): RedirectResponse
    {
        $organization = $this->authorizeSupportAdmin($request);
        abort_unless($category->organization_id === $organization->id, 403);

        $category->delete();

        return redirect()->route('admin.support.settings')->with('status', 'Category deleted successfully.');
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
}
