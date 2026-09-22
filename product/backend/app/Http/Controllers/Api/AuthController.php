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
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function agentLogin(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $agent = AgentSupport::with(['user', 'organization'])
            ->whereHas('user', fn ($query) => $query->where('username', strtolower($credentials['username'])))
            ->first();

        if (! $agent || ! $agent->is_active || $agent->user->status !== 'active' || ! Hash::check($credentials['password'], $agent->user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided agent credentials are incorrect or inactive.'],
            ]);
        }

        abort_unless($agent->organization->supportsLiveChatAgents(), 403, 'Live chat agents are not included in the active subscription.');

        $agent->update(['availability_status' => 'online', 'last_seen_at' => now()]);

        return response()->json([
            'token' => $agent->user->createToken('agent-support')->plainTextToken,
            'organization' => ['id' => $agent->organization->id, 'name' => $agent->organization->name],
            'agent' => [
                'id' => $agent->id,
                'user_id' => $agent->user_id,
                'full_name' => $agent->user->name,
                'username' => $agent->user->username,
                'nickname' => $agent->nickname,
                'availability_status' => $agent->availability_status,
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'organization_name' => ['required', 'string', 'max:255'],
        ]);

        $result = DB::transaction(function () use ($validated): array {
            $organization = Organization::create([
                'name' => $validated['organization_name'],
                'status' => 'active',
            ]);

            $user = User::create([
                'organization_id' => $organization->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'owner',
                'status' => 'active',
            ]);

            return compact('organization', 'user');
        });

        $user = $result['user'];
        $user->load('organization');

        return response()->json([
            'token' => $user->createToken('frontend')->plainTextToken,
            'user' => $this->userPayload($user),
            'organization' => $result['organization'],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->with('organization')
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['This user is not active.'],
            ]);
        }

        return response()->json([
            'token' => $user->createToken('frontend')->plainTextToken,
            'user' => $this->userPayload($user),
            'organization' => $user->organization,
            'settings' => $user->organization?->settings,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['organization.settings', 'idCard', 'leaveRequests', 'wfhRequests']);

        return response()->json([
            'user' => $this->userPayload($user),
            'organization' => $user->organization,
            'settings' => $user->organization?->settings,
        ]);
    }

    public function validateEmployee(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (empty($user->employee_id) || strtolower($user->employee_id) !== strtolower($validated['employee_id'])) {
            return response()->json([
                'message' => 'The provided Employee ID does not match our records for this account.'
            ], 422);
        }

        $user->load(['idCard', 'leaveRequests', 'wfhRequests']);

        return response()->json([
            'success' => true,
            'user' => $this->userPayload($user),
        ]);
    }

    private function userPayload(User $user): array
    {
        if (!$user->relationLoaded('idCard') || !$user->relationLoaded('leaveRequests') || !$user->relationLoaded('wfhRequests')) {
            $user->loadMissing(['idCard', 'leaveRequests', 'wfhRequests']);
        }

        $totalQuestions = $user->questions()->count();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'organization_id' => $user->organization_id,
            'employee_id' => $user->employee_id,
            'joining_date' => $user->idCard?->issue_date ?? $user->created_at->format('Y-m-d'),
            'id_card' => $user->idCard ? [
                'card_number' => $user->idCard->card_number,
                'issue_date' => $user->idCard->issue_date,
                'expiry_date' => $user->idCard->expiry_date,
                'status' => $user->idCard->status,
            ] : null,
            'leave_requests_count' => $user->leaveRequests->count(),
            'wfh_requests_count' => $user->wfhRequests->count(),
            'total_questions_count' => $totalQuestions,
        ];
    }
}
