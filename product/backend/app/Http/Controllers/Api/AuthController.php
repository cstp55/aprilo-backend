<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
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
