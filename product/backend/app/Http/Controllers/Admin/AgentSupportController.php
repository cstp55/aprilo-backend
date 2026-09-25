<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentSupport;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgentSupportController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $this->organizationFor($request);
        $this->authorizeOrganizationUser($request, $organization);
        $settings = $this->settingsFor($organization);

        return view('admin.agents', [
            'organization' => $organization,
            'organizations' => $request->user()->isSuperAdmin()
                ? Organization::orderBy('name')->get(['id', 'name'])
                : collect([$organization]),
            'isSuperAdmin' => $request->user()->isSuperAdmin(),
            'settings' => $settings,
            'agentLimit' => $organization->supportAgentLimit() ?? $settings->max_support_agents,
            'featureEnabled' => $organization->supportsLiveChatAgents(),
            'agents' => $organization->agentSupports()->with('user')->latest()->get(),
        ]);
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $organization = $this->organizationFor($request);
        $this->authorizeOrganizationUser($request, $organization);
        $this->ensureFeature($organization);
        $this->ensureAgentCapacity($organization);

        $request->merge([
            'username' => strtolower(trim((string) $request->input('username'))),
        ]);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_.-]+$/', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'nickname' => ['required', 'string', 'max:80'],
            'availability_status' => ['required', Rule::in(['online', 'away', 'offline'])],
            'availability_slots' => ['nullable', 'string', 'max:1000'],
        ]);

        $agent = DB::transaction(function () use ($validated, $organization): AgentSupport {
            $user = User::create([
                'organization_id' => $organization->id,
                'name' => $validated['full_name'],
                'username' => strtolower($validated['username']),
                'email' => $validated['email'] ?: null,
                'password' => Hash::make($validated['password']),
                'role' => 'employee',
                'status' => 'active',
            ]);

            return AgentSupport::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'nickname' => $validated['nickname'],
                'availability_status' => $validated['availability_status'],
                'availability_slots' => $this->slots($validated['availability_slots'] ?? null),
                'is_active' => true,
            ]);
        });
        $audit->log($request->user(), 'AGENT_CREATED', 'agent_support', $agent->id);

        return redirect()->route('admin.agents', $request->user()->isSuperAdmin() ? ['organization_id' => $organization->id] : [])
            ->with('status', 'Support agent created successfully.');
    }

    public function update(Request $request, AgentSupport $agentSupport, AuditLogger $audit): RedirectResponse
    {
        $organization = $this->organizationFor($request);
        $this->authorizeOrganizationUser($request, $organization);
        abort_unless($agentSupport->organization_id === $organization->id, 404);
        $this->ensureFeature($organization);

        $request->merge([
            'username' => strtolower(trim((string) $request->input('username'))),
        ]);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_.-]+$/', Rule::unique('users', 'username')->ignore($agentSupport->user_id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($agentSupport->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'nickname' => ['required', 'string', 'max:80'],
            'availability_status' => ['required', Rule::in(['online', 'away', 'offline'])],
            'availability_slots' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $agentSupport): void {
            $agentSupport->user->update([
                'name' => $validated['full_name'],
                'username' => strtolower($validated['username']),
                'email' => $validated['email'] ?: null,
                'status' => $validated['is_active'] ? 'active' : 'inactive',
                ...($validated['password'] ? ['password' => Hash::make($validated['password'])] : []),
            ]);
            $agentSupport->update([
                'nickname' => $validated['nickname'],
                'availability_status' => $validated['availability_status'],
                'availability_slots' => $this->slots($validated['availability_slots'] ?? null),
                'is_active' => $validated['is_active'],
            ]);
        });
        $audit->log($request->user(), $validated['is_active'] ? 'AGENT_UPDATED' : 'AGENT_DEACTIVATED', 'agent_support', $agentSupport->id);

        return redirect()->route('admin.agents', $request->user()->isSuperAdmin() ? ['organization_id' => $organization->id] : [])
            ->with('status', 'Support agent updated successfully.');
    }

    public function destroy(Request $request, AgentSupport $agentSupport, AuditLogger $audit): RedirectResponse
    {
        $organization = $this->organizationFor($request);
        $this->authorizeOrganizationUser($request, $organization);
        abort_unless($agentSupport->organization_id === $organization->id, 404);
        $this->ensureFeature($organization);
        $agentSupport->user()->delete();
        $audit->log($request->user(), 'AGENT_DELETED', 'agent_support', $agentSupport->id);

        return redirect()->route('admin.agents', $request->user()->isSuperAdmin() ? ['organization_id' => $organization->id] : [])
            ->with('status', 'Support agent deleted successfully.');
    }

    private function organizationFor(Request $request): Organization
    {
        if ($request->user()?->isSuperAdmin()) {
            abort_unless($request->filled('organization_id'), 422, 'Select an organization to manage agents.');

            return Organization::findOrFail($request->input('organization_id'));
        }

        abort_unless($request->user()?->organization_id, 403, 'An organization is required.');

        return Organization::findOrFail($request->user()->organization_id);
    }

    private function authorizeOrganizationUser(Request $request, Organization $organization): void
    {
        abort_unless(
            $request->user()->isSuperAdmin()
                || ($request->user()->organization_id === $organization->id
                    && ! $request->user()->agentSupport()->exists()),
            403,
            'Organization user access is required.'
        );
    }

    private function ensureFeature(Organization $organization): void
    {
        abort_unless($organization->supportsLiveChatAgents(), 403, 'Live chat agents are not included in the active subscription.');
    }

    private function ensureAgentCapacity(Organization $organization): void
    {
        $settings = $this->settingsFor($organization);
        $agentLimit = $organization->supportAgentLimit() ?? $settings->max_support_agents;
        abort_if(
            $agentLimit !== null
                && $organization->agentSupports()->count() >= $agentLimit,
            422,
            'The organization has reached its support agent limit.'
        );
    }

    private function settingsFor(Organization $organization): OrganizationSetting
    {
        return OrganizationSetting::firstOrCreate(
            ['organization_id' => $organization->id],
            ['minutes_saved_per_resolved_question' => 5, 'assistant_status' => 'active']
        );
    }

    private function slots(?string $slots): array
    {
        return collect(explode(',', (string) $slots))
            ->map(fn (string $slot) => trim($slot))
            ->filter()
            ->values()
            ->all();
    }
}
