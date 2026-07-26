<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Services\Metrics\MetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetricsController extends Controller
{
    public function summary(Request $request, MetricsService $metrics): JsonResponse
    {
        $this->authorizeAdmin($request);

        return response()->json([
            'summary' => $metrics->summary($request->user()->organization),
        ]);
    }

    public function topics(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        return response()->json([
            'topics' => [],
            'knowledge_gaps' => [],
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            in_array($request->user()->role, [UserRole::Owner->value, UserRole::HrAdmin->value], true),
            403,
            'Admin access is required.'
        );
    }
}
