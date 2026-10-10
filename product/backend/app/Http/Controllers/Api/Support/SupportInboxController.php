<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportInboxController extends Controller
{
    /**
     * Get shared support inbox summary and queue counts.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $org = $user->organization;

        abort_unless($org, 403);
        $hasProduct = (
            $org->hasActiveProductCategory('support_saas') ||
            $org->hasActiveProductCategory('aprilo-support') ||
            $org->hasActiveProductCategory('ai_support') ||
            $org->hasActiveProductCategory('aprilo-ai')
        );
        abort_unless($hasProduct, 403, 'Aprilo Support product is not active for this organization.');

        $baseQuery = SupportTicket::where('organization_id', $org->id);

        $counts = [
            'all_open' => (clone $baseQuery)->whereNotIn('status', ['resolved', 'closed'])->count(),
            'unassigned' => (clone $baseQuery)->unassigned()->whereNotIn('status', ['resolved', 'closed'])->count(),
            'assigned_to_me' => (clone $baseQuery)->where('assigned_to', $user->id)->whereNotIn('status', ['resolved', 'closed'])->count(),
            'urgent' => (clone $baseQuery)->where('priority', 'urgent')->whereNotIn('status', ['resolved', 'closed'])->count(),
            'pending' => (clone $baseQuery)->whereIn('status', ['pending_customer', 'pending_internal'])->count(),
            'resolved' => (clone $baseQuery)->whereIn('status', ['resolved', 'closed'])->count(),
            'by_status' => [
                'new' => (clone $baseQuery)->where('status', 'new')->count(),
                'open' => (clone $baseQuery)->where('status', 'open')->count(),
                'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
                'pending_customer' => (clone $baseQuery)->where('status', 'pending_customer')->count(),
                'pending_internal' => (clone $baseQuery)->where('status', 'pending_internal')->count(),
                'resolved' => (clone $baseQuery)->where('status', 'resolved')->count(),
                'closed' => (clone $baseQuery)->where('status', 'closed')->count(),
            ],
            'by_priority' => [
                'low' => (clone $baseQuery)->where('priority', 'low')->count(),
                'normal' => (clone $baseQuery)->where('priority', 'normal')->count(),
                'high' => (clone $baseQuery)->where('priority', 'high')->count(),
                'urgent' => (clone $baseQuery)->where('priority', 'urgent')->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'summary' => $counts,
        ]);
    }
}
