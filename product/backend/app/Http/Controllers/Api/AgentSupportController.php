<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentSupport;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AgentSupportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $organization = $this->organizationFor($request);
        $this->authorizeManager($request, $organization);
        $featureEnabled = $organization->supportsLiveChatAgents();

        return response()->json([
            'organization' => ['id' => $organization->id, 'name' => $organization->name],
            'feature_enabled' => $featureEnabled,
            'agents' => $featureEnabled
                ? $organization->agentSupports()->with('user')->latest()->get()->map(fn (AgentSupport $agent) => $this->payload($agent))
                : [],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $organization = $this->organizationFor($request);
        $this->authorizeManager($request, $organization);
        $this->ensureFeature($organization);

        abort_if(
            $organization->settings?->max_support_agents !== null
                && $organization->agentSupports()->count() >= $organization->settings->max_support_agents,
            422,
            'The organization has reached its support agent limit.'
        );

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_.-]+$/', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'nickname' => ['required', 'string', 'max:80'],
            'availability_status' => ['sometimes', Rule::in(['online', 'away', 'offline'])],
            'availability_slots' => ['nullable', 'array'],
        ]);

        $agent = DB::transaction(function () use ($validated, $organization): AgentSupport {
            $user = User::create([
                'organization_id' => $organization->id,
                'name' => $validated['full_name'],
                'username' => strtolower($validated['username']),
                'email' => $validated['email'] ?? null,
                'password' => Hash::make($validated['password']),
                'role' => 'employee',
                'status' => 'active',
            ]);

            return AgentSupport::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'nickname' => $validated['nickname'],
                'availability_status' => $validated['availability_status'] ?? 'offline',
                'availability_slots' => $validated['availability_slots'] ?? [],
                'is_active' => true,
            ]);
        });

        return response()->json(['agent' => $this->payload($agent->load('user'))], 201);
    }

    public function update(Request $request, AgentSupport $agentSupport): JsonResponse
    {
        $this->ensureAgentOrganization($request, $agentSupport);
        $this->ensureFeature($agentSupport->organization);

        $validated = $request->validate([
            'full_name' => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_.-]+$/', Rule::unique('users', 'username')->ignore($agentSupport->user_id)],
            'email' => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($agentSupport->user_id)],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'nickname' => ['sometimes', 'string', 'max:80'],
            'availability_status' => ['sometimes', Rule::in(['online', 'away', 'offline'])],
            'availability_slots' => ['sometimes', 'nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $agentSupport): void {
            $userData = array_filter([
                'name' => $validated['full_name'] ?? null,
                'username' => isset($validated['username']) ? strtolower($validated['username']) : null,
                'email' => array_key_exists('email', $validated) ? $validated['email'] : null,
                'password' => isset($validated['password']) ? Hash::make($validated['password']) : null,
            ], fn ($value) => $value !== null);
            $agentSupport->user->update($userData);
            $agentSupport->update(array_filter([
                'nickname' => $validated['nickname'] ?? null,
                'availability_status' => $validated['availability_status'] ?? null,
                'availability_slots' => array_key_exists('availability_slots', $validated) ? $validated['availability_slots'] : null,
                'is_active' => $validated['is_active'] ?? null,
            ], fn ($value) => $value !== null));

            if (array_key_exists('is_active', $validated)) {
                $agentSupport->user->update(['status' => $validated['is_active'] ? 'active' : 'inactive']);
            }
        });

        return response()->json(['agent' => $this->payload($agentSupport->fresh('user'))]);
    }

    public function destroy(Request $request, AgentSupport $agentSupport): JsonResponse
    {
        $this->ensureAgentOrganization($request, $agentSupport);
        $this->ensureFeature($agentSupport->organization);
        $agentSupport->user()->delete();

        return response()->json(['message' => 'Agent deleted.']);
    }

    private function organizationFor(Request $request): Organization
    {
        $user = $request->user();
        $organizationId = $user->isSuperAdmin() ? $request->input('organization_id') : $user->organization_id;

        abort_unless($organizationId, 422, 'An organization is required.');

        return Organization::findOrFail($organizationId);
    }

    private function authorizeManager(Request $request, Organization $organization): void
    {
        abort_unless(
            $request->user()->isSuperAdmin()
                || ($request->user()->organization_id === $organization->id && ! $request->user()->agentSupport()->exists()),
            403,
            'Organization user access is required.'
        );
    }

    private function ensureAgentOrganization(Request $request, AgentSupport $agentSupport): void
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->organization_id === $agentSupport->organization_id, 403, 'This agent belongs to another organization.');
        $this->authorizeManager($request, $agentSupport->organization);
    }

    private function ensureFeature(Organization $organization): void
    {
        abort_unless($organization->supportsLiveChatAgents(), 403, 'Live chat agents are not included in the active subscription.');
    }

    private function payload(AgentSupport $agent): array
    {
        return [
            'id' => $agent->id,
            'user_id' => $agent->user_id,
            'full_name' => $agent->user?->name,
            'username' => $agent->user?->username,
            'email' => $agent->user?->email,
            'nickname' => $agent->nickname,
            'availability_status' => $agent->availability_status,
            'availability_slots' => $agent->availability_slots ?? [],
            'is_active' => $agent->is_active && $agent->user?->status === 'active',
            'last_seen_at' => $agent->last_seen_at?->toIso8601String(),
        ];
    }
}
