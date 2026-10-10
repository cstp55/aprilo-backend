<?php

namespace App\Services\Navigation;

use App\Models\User;

class NavigationService
{
    public function getSidebarMenu(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $menu = [];
        $owner = $user->role_slug === 'owner';
        $organization = $user->organization;

        // 1. Super Admin Platform Administration Group
        if ($user->isSuperAdmin()) {
            $menu[] = [
                'header' => 'Platform Administration',
                'items' => [
                    [
                        'title' => 'Super Dashboard',
                        'route' => 'admin.super.dashboard',
                        'icon' => 'globe',
                        'active' => request()->routeIs('admin.super.dashboard'),
                    ],
                    [
                        'title' => 'Organizations',
                        'route' => 'admin.super.organizations',
                        'icon' => 'buildings',
                        'active' => request()->routeIs('admin.super.organizations*'),
                    ],
                    [
                        'title' => 'Products & Permissions',
                        'route' => 'admin.super.products',
                        'icon' => 'layers',
                        'active' => request()->routeIs('admin.super.products*'),
                    ],
                    [
                        'title' => 'Roles & RBAC',
                        'route' => 'admin.roles',
                        'icon' => 'shield',
                        'active' => request()->routeIs('admin.roles*'),
                    ],
                    [
                        'title' => 'Chat & Token Monitoring',
                        'route' => 'admin.super.monitoring',
                        'icon' => 'activity',
                        'active' => request()->routeIs('admin.super.monitoring*'),
                    ],
                    [
                        'title' => 'Global Revenue',
                        'route' => 'admin.super.revenue',
                        'icon' => 'chart',
                        'active' => request()->routeIs('admin.super.revenue*'),
                    ],
                    [
                        'title' => 'Global Audit Logs',
                        'route' => 'admin.super.logs',
                        'icon' => 'history',
                        'active' => request()->routeIs('admin.super.logs*'),
                    ],
                    [
                        'title' => 'System Cache',
                        'route' => 'admin.super.cache',
                        'icon' => 'settings',
                        'active' => request()->routeIs('admin.super.cache*'),
                    ],
                    [
                        'title' => 'WhatsApp Campaigns',
                        'route' => 'admin.super.whatsapp-campaigns',
                        'icon' => 'message-circle',
                        'active' => request()->routeIs('admin.super.whatsapp-campaigns*'),
                    ],
                ],
            ];
        }

        // 2. Overview / Dashboards Group
        $dashboardItems = [];

        if (! $user->isSuperAdmin()) {
            $hasAi = ! $owner || ($organization?->hasActiveProductCategory('ai_support') ?? false) || ($organization?->hasActiveProductCategory('chatbot') ?? false);
            $hasEcom = ! $owner || ($organization?->hasActiveProductCategory('ecommerce') ?? false);
            $hasHr = ! $owner || ($organization?->hasActiveProductCategory('hr') ?? false) || ($organization?->hasActiveProductCategory('aprilo-hr') ?? false);

            if (! $owner && ($user->hasPermission('hr.dashboard.view') || $user->isHrAdmin())) {
                $dashboardItems[] = [
                    'title' => 'HR Dashboard',
                    'route' => 'admin.dashboard',
                    'icon' => 'users',
                    'active' => request()->routeIs('admin.dashboard') && request('view') === 'hr',
                ];
            }

            if (($hasEcom && $user->hasPermission('ecommerce.dashboard.view')) || $user->isEcommerceAdmin()) {
                $dashboardItems[] = [
                    'title' => 'E-commerce Dashboard',
                    'route' => 'admin.ecommerce.dashboard',
                    'icon' => 'shopping-cart',
                    'active' => request()->routeIs('admin.ecommerce.dashboard'),
                ];
            }

            if ($owner && $hasAi) {
                $dashboardItems[] = [
                    'title' => 'Aprilo AI Dashboard',
                    'route' => 'admin.dashboard',
                    'icon' => 'bot',
                    'active' => request()->routeIs('admin.dashboard'),
                ];
            } elseif ($owner && $hasHr && ! $hasAi) {
                $dashboardItems[] = [
                    'title' => 'HR Dashboard',
                    'route' => 'admin.dashboard',
                    'icon' => 'users',
                    'active' => request()->routeIs('admin.dashboard'),
                ];
            } elseif (! $owner && $user->hasPermission('dashboard.view')) {
                $dashboardItems[] = [
                    'title' => 'Main Dashboard',
                    'route' => 'admin.dashboard',
                    'icon' => 'layout-dashboard',
                    'active' => request()->routeIs('admin.dashboard') && ! request()->has('tab'),
                ];
            }
        } else {
            $dashboardItems[] = [
                'title' => 'Operations Console',
                'route' => 'admin.dashboard',
                'icon' => 'layout-dashboard',
                'active' => request()->routeIs('admin.dashboard'),
            ];
        }

        if (! empty($dashboardItems)) {
            $menu[] = [
                'header' => 'Overview',
                'items' => $dashboardItems,
            ];
        }

        // 3. AI & Knowledge (Aprilo AI & Chatbot)
        $aiItems = [];

        // For owner, only show if they have an active AI/chatbot/support product (not ecommerce-only)
        $canAccessAiKnowledge = ! $owner || ($organization?->hasActiveProductCategory('ai_support') || $organization?->hasActiveProductCategory('chatbot') || $organization?->hasActiveProductCategory('support_saas'));

        if ($canAccessAiKnowledge && $user->hasPermission('knowledge.sources.view')) {
            $aiItems[] = [
                'title' => 'Knowledge Sources',
                'route' => 'admin.sources',
                'icon' => 'book',
                'active' => request()->routeIs('admin.sources*'),
            ];
        }

        if ($canAccessAiKnowledge && $user->hasPermission('widget.settings')) {
            $aiItems[] = [
                'title' => 'Widget AI & Prompts',
                'route' => 'admin.settings.agent',
                'icon' => 'bot',
                'active' => request()->routeIs('admin.settings.agent'),
            ];
        }

        if ($canAccessAiKnowledge && $user->hasPermission('live_chat.view')) {
            $aiItems[] = [
                'title' => 'Live Agent Chat',
                'route' => 'admin.chat',
                'icon' => 'chat',
                'active' => request()->routeIs('admin.chat*'),
            ];
        }

        if ($canAccessAiKnowledge && $user->hasPermission('widget.script')) {
            $aiItems[] = [
                'title' => 'Embed Script (WG)',
                'route' => 'admin.settings.deploy',
                'icon' => 'code',
                'active' => request()->routeIs('admin.settings.deploy'),
            ];
        }

        if (! empty($aiItems)) {
            $menu[] = [
                'header' => 'AI & Knowledge',
                'items' => $aiItems,
            ];
        }

        // 4. Support & Human Review
        $supportItems = [];

        if ($canAccessAiKnowledge && $user->hasPermission('escalations.view')) {
            $supportItems[] = [
                'title' => 'Escalations Queue',
                'route' => 'admin.escalations',
                'icon' => 'alert-triangle',
                'active' => request()->routeIs('admin.escalations*'),
            ];
        }

        if ($canAccessAiKnowledge && $user->hasPermission('logs.view')) {
            $supportItems[] = [
                'title' => 'Interaction Logs',
                'route' => 'admin.logs',
                'icon' => 'file-text',
                'active' => request()->routeIs('admin.logs*'),
            ];
        }

        if ($canAccessAiKnowledge && ($owner || $user->hasPermission('analytics.view'))) {
            $supportItems[] = [
                'title' => 'Chat & Token Usage',
                'route' => 'admin.monitoring',
                'icon' => 'activity',
                'active' => request()->routeIs('admin.monitoring*'),
            ];
        }

        if (! empty($supportItems)) {
            $menu[] = [
                'header' => 'Review & Auditing',
                'items' => $supportItems,
            ];
        }

        // 5. HR Operations
        $hrItems = [];
        $canAccessHr = ! $owner || ($organization?->hasActiveProductCategory('hr') || $organization?->hasActiveProductCategory('aprilo-hr'));

        if ($canAccessHr && $user->hasPermission('employee.leaves.manage')) {
            $hrItems[] = [
                'title' => 'Leave Requests',
                'route' => 'admin.leaves',
                'icon' => 'calendar',
                'active' => request()->routeIs('admin.leaves*'),
            ];
        }

        if ($canAccessHr && $user->hasPermission('employee.wfh.manage')) {
            $hrItems[] = [
                'title' => 'WFH Requests',
                'route' => 'admin.wfh',
                'icon' => 'home',
                'active' => request()->routeIs('admin.wfh*'),
            ];
        }

        if ($canAccessHr && $user->hasPermission('employee.validate')) {
            $hrItems[] = [
                'title' => 'Validate Employee',
                'route' => 'admin.employees',
                'icon' => 'check-circle',
                'active' => request()->routeIs('admin.employees*'),
            ];
        }

        if ($canAccessHr && $user->hasPermission('employee.idcards.manage')) {
            $hrItems[] = [
                'title' => 'ID Cards',
                'route' => 'admin.idcards',
                'icon' => 'credit-card',
                'active' => request()->routeIs('admin.idcards*'),
            ];
        }

        if (! empty($hrItems)) {
            $menu[] = [
                'header' => 'HR Operations',
                'items' => $hrItems,
            ];
        }

        // 6. E-commerce Ops
        $ecomItems = [];
        $canAccessEcom = ! $owner || ($organization?->hasActiveProductCategory('ecommerce'));

        if ($canAccessEcom && $user->hasPermission('ecommerce.platforms.connect')) {
            $ecomItems[] = [
                'title' => 'Store Connectors',
                'route' => 'admin.ecommerce.platforms',
                'icon' => 'link',
                'active' => request()->routeIs('admin.ecommerce.platforms*'),
            ];
        }

        if ($canAccessEcom && $user->hasPermission('ecommerce.products.sync')) {
            $ecomItems[] = [
                'title' => 'Product Catalog & Sync',
                'route' => 'admin.ecommerce.products',
                'icon' => 'package',
                'active' => request()->routeIs('admin.ecommerce.products*'),
            ];
        }

        if ($canAccessEcom && $user->hasPermission('ecommerce.orders.sync')) {
            $ecomItems[] = [
                'title' => 'Orders Sync',
                'route' => 'admin.ecommerce.orders',
                'icon' => 'shopping-bag',
                'active' => request()->routeIs('admin.ecommerce.orders*'),
            ];
        }

        if ($canAccessEcom && $user->hasPermission('ecommerce.licenses.manage')) {
            $ecomItems[] = [
                'title' => 'Plugin License Keys',
                'route' => 'admin.ecommerce.licenses',
                'icon' => 'key',
                'active' => request()->routeIs('admin.ecommerce.licenses*'),
            ];
        }

        if (! empty($ecomItems)) {
            $menu[] = [
                'header' => 'E-commerce Ops',
                'items' => $ecomItems,
            ];
        }

        // 7. Administration & Settings
        $adminItems = [];

        if ((! $owner || $canAccessAiKnowledge) && $user->hasPermission('roles.manage')) {
            $adminItems[] = [
                'title' => 'Roles & Permissions',
                'route' => 'admin.roles',
                'icon' => 'shield',
                'active' => request()->routeIs('admin.roles*'),
            ];
        }

        if ($canAccessAiKnowledge && $user->hasPermission('plugins.connect')) {
            $adminItems[] = [
                'title' => 'Channel Connectors',
                'route' => 'admin.settings.connect',
                'icon' => 'cpu',
                'active' => request()->routeIs('admin.settings.connect*'),
            ];
        }

        if ($user->hasPermission('billing.view')) {
            $adminItems[] = [
                'title' => 'Billing & Plans',
                'route' => 'admin.settings.pricing',
                'icon' => 'dollar-sign',
                'active' => request()->routeIs('admin.settings.pricing'),
            ];
        }

        if (! empty($adminItems)) {
            $menu[] = [
                'header' => 'Administration',
                'items' => $adminItems,
            ];
        }

        return $menu;
    }
}
