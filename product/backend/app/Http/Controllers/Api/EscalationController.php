<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Escalation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EscalationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $query = Escalation::query()
            ->with('question')
            ->where('organization_id', $request->user()->organization_id)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json(['escalations' => $query->get()]);
    }

    public function show(Request $request, Escalation $escalation): JsonResponse
    {
        $this->authorizeAdmin($request);
        $this->authorizeOrganization($request, $escalation);

        return response()->json(['escalation' => $escalation->load('question')]);
    }

    public function update(Request $request, Escalation $escalation): JsonResponse
    {
        $this->authorizeAdmin($request);
        $this->authorizeOrganization($request, $escalation);

        $validated = $request->validate([
            'status' => ['sometimes', Rule::in(['open', 'in_progress', 'resolved'])],
            'resolution_note' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        if (($validated['status'] ?? null) === 'resolved') {
            $validated['resolved_at'] = now();
        }

        $escalation->update($validated);

        return response()->json(['escalation' => $escalation->fresh()]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            in_array($request->user()->role, [UserRole::Owner->value, UserRole::HrAdmin->value], true),
            403,
            'Admin access is required.'
        );
    }

    private function authorizeOrganization(Request $request, Escalation $escalation): void
    {
        abort_unless($escalation->organization_id === $request->user()->organization_id, 404);
    }
}
