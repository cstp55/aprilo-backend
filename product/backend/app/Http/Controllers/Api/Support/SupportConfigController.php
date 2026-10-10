<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportSlaPolicy;
use App\Models\Support\SupportTicketCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportConfigController extends Controller
{
    /**
     * Get Aprilo Support configuration for organization.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $org = $user->organization;
        abort_unless($org, 403);

        $categories = SupportTicketCategory::where('organization_id', $org->id)
            ->orderBy('name')
            ->get();

        $slaPolicies = SupportSlaPolicy::where('organization_id', $org->id)
            ->get()
            ->keyBy('priority');

        // Default SLAs if not yet set
        $defaultPriorities = [
            'urgent' => ['name' => 'Urgent SLA', 'response_time_minutes' => 60, 'resolution_time_minutes' => 240],
            'high' => ['name' => 'High Priority SLA', 'response_time_minutes' => 120, 'resolution_time_minutes' => 480],
            'normal' => ['name' => 'Normal SLA', 'response_time_minutes' => 240, 'resolution_time_minutes' => 1440],
            'low' => ['name' => 'Low Priority SLA', 'response_time_minutes' => 480, 'resolution_time_minutes' => 2880],
        ];

        $slaList = [];
        foreach ($defaultPriorities as $priority => $defaults) {
            $policy = $slaPolicies->get($priority);
            $slaList[$priority] = [
                'priority' => $priority,
                'name' => $policy?->name ?? $defaults['name'],
                'response_time_minutes' => $policy?->response_time_minutes ?? $defaults['response_time_minutes'],
                'resolution_time_minutes' => $policy?->resolution_time_minutes ?? $defaults['resolution_time_minutes'],
                'is_active' => $policy?->is_active ?? true,
            ];
        }

        return response()->json([
            'success' => true,
            'organization_id' => $org->id,
            'categories' => $categories,
            'sla_policies' => $slaList,
            'channels' => [
                'website' => (bool) ($org->settings?->is_widget_enabled ?? true),
                'email' => (bool) ($org->settings?->connect_mail ?? false),
                'whatsapp' => (bool) ($org->settings?->connect_whatsapp ?? false),
            ],
        ]);
    }

    /**
     * Update Aprilo Support configuration and SLA policies.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $org = $user->organization;
        abort_unless($org, 403);
        abort_unless($user->hasPermission('support.sla.manage') || $user->role_slug === 'owner' || $user->isSuperAdmin(), 403, 'Permission denied.');

        $validated = $request->validate([
            'sla_policies' => ['nullable', 'array'],
            'sla_policies.*.priority' => ['required', 'string', 'in:low,normal,high,urgent'],
            'sla_policies.*.response_time_minutes' => ['required', 'integer', 'min:1'],
            'sla_policies.*.resolution_time_minutes' => ['required', 'integer', 'min:1'],
            'categories' => ['nullable', 'array'],
            'categories.*.name' => ['required', 'string', 'max:100'],
            'categories.*.description' => ['nullable', 'string', 'max:255'],
        ]);

        // Upsert SLA policies
        if (! empty($validated['sla_policies'])) {
            foreach ($validated['sla_policies'] as $slaData) {
                SupportSlaPolicy::updateOrCreate(
                    [
                        'organization_id' => $org->id,
                        'priority' => $slaData['priority'],
                    ],
                    [
                        'name' => ucfirst($slaData['priority']) . ' Priority SLA',
                        'response_time_minutes' => $slaData['response_time_minutes'],
                        'resolution_time_minutes' => $slaData['resolution_time_minutes'],
                        'is_active' => true,
                    ]
                );
            }
        }

        // Add or update categories
        if (! empty($validated['categories'])) {
            foreach ($validated['categories'] as $catData) {
                $slug = Str::slug($catData['name']);
                SupportTicketCategory::updateOrCreate(
                    [
                        'organization_id' => $org->id,
                        'slug' => $slug,
                    ],
                    [
                        'name' => $catData['name'],
                        'description' => $catData['description'] ?? null,
                        'is_active' => true,
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Support configuration updated successfully.',
        ]);
    }
}
