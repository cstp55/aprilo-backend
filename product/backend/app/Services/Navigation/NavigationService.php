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

        // 1. Super Admin Platform Group
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
                        'title' => 'All Organizations',
                        'route' => 'admin.super.organizations',
                        'icon' => 'buildings',
                        'active' => request()->routeIs('admin.super.organizations*'),
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
                ],
            ];
        }

        // 2. Dashboards Group (HR vs E-commerce vs General)
        $dashboardItems = [];

        if (! $user->isSuperAdmin()) {
            if ($user->hasPermission('hr.dashboard.view') || $user->isHrAdmin()) {
                $dashboardItems[] = [
                    'title' => 'HR Dashboard',
                    'route' => 'admin.dashboard',
                    'icon' => 'users',
                    'active' => request()->routeIs('admin.dashboard'),
                ];
            }

            if ($user->hasPermission('ecommerce.dashboard.view') || $user->isEcommerceAdmin()) {
                $dashboardItems[] = [
                    'title' => 'E-commerce Dashboard',
                    'route' => 'admin.ecommerce.dashboard',
                    'icon' => 'shopping-cart',
                    'active' => request()->routeIs('admin.ecommerce.dashboard'),
                ];
            }
        }

        if (! empty($dashboardItems)) {
            $menu[] = [
                'header' => 'Overview',
                'items' => $dashboardItems,
            ];
        }

        // 3. Knowledge & AI Operations
        $aiItems = [];

        if ($user->hasPermission('knowledge.sources.view')) {
            $aiItems[] = [
                'title' => 'Knowledge Sources',
                'route' => 'admin.sources',
                'icon' => 'book',
                'active' => request()->routeIs('admin.sources*'),
            ];
        }

        if ($user->hasPermission('widget.settings')) {
            $aiItems[] = [
                'title' => 'Widget AI & Restrictions',
                'route' => 'admin.settings',
                'params' => ['tab' => 'agent'],
                'icon' => 'bot',
                'active' => request()->routeIs('admin.settings') && request('tab', 'agent') === 'agent',
            ];
        }

        if ($user->hasPermission('live_chat.view')) {
            $aiItems[] = [
                'title' => 'Live Agent Chat',
                'route' => 'admin.chat',
                'icon' => 'chat',
                'active' => request()->routeIs('admin.chat*'),
            ];
        }

        if ($user->hasPermission('widget.script')) {
            $aiItems[] = [
                'title' => 'Embed Script (WG)',
                'route' => 'admin.settings',
                'params' => ['tab' => 'script'],
                'icon' => 'code',
                'active' => request()->routeIs('admin.settings') && request('tab') === 'script',
            ];
        }

        if (! empty($aiItems)) {
            $menu[] = [
                'header' => 'AI & Knowledge',
                'items' => $aiItems,
            ];
        }

        // 4. Human Review & Auditing
        $auditItems = [];

        if ($user->hasPermission('escalations.view')) {
            $auditItems[] = [
                'title' => 'Escalations Queue',
                'route' => 'admin.escalations',
                'icon' => 'alert-triangle',
                'active' => request()->routeIs('admin.escalations*'),
            ];
        }

        if ($user->hasPermission('logs.view')) {
            $auditItems[] = [
                'title' => 'Interaction Logs',
                'route' => 'admin.logs',
                'icon' => 'file-text',
                'active' => request()->routeIs('admin.logs*'),
            ];
        }

        if (! empty($auditItems)) {
            $menu[] = [
                'header' => 'Review & Auditing',
                'items' => $auditItems,
            ];
        }

        // 5. HR Operations
        $hrItems = [];

        if ($user->hasPermission('employee.leaves.manage')) {
            $hrItems[] = [
                'title' => 'Leave Requests',
                'route' => 'admin.leaves',
                'icon' => 'calendar',
                'active' => request()->routeIs('admin.leaves*'),
            ];
        }

        if ($user->hasPermission('employee.wfh.manage')) {
            $hrItems[] = [
                'title' => 'WFH Requests',
                'route' => 'admin.wfh',
                'icon' => 'home',
                'active' => request()->routeIs('admin.wfh*'),
            ];
        }

        if ($user->hasPermission('employee.validate')) {
            $hrItems[] = [
                'title' => 'Validate Employee',
                'route' => 'admin.employees',
                'icon' => 'check-circle',
                'active' => request()->routeIs('admin.employees*'),
            ];
        }

        if ($user->hasPermission('employee.idcards.manage')) {
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

        // 6. E-commerce Hub
        $ecomItems = [];

        if ($user->hasPermission('ecommerce.platforms.connect')) {
            $ecomItems[] = [
                'title' => 'Store Connectors',
                'route' => 'admin.ecommerce.platforms',
                'icon' => 'link',
                'active' => request()->routeIs('admin.ecommerce.platforms*'),
            ];
        }

        if ($user->hasPermission('ecommerce.products.sync')) {
            $ecomItems[] = [
                'title' => 'Product Catalog & Sync',
                'route' => 'admin.ecommerce.products',
                'icon' => 'package',
                'active' => request()->routeIs('admin.ecommerce.products*'),
            ];
        }

        if ($user->hasPermission('ecommerce.orders.sync')) {
            $ecomItems[] = [
                'title' => 'Orders Sync',
                'route' => 'admin.ecommerce.orders',
                'icon' => 'shopping-bag',
                'active' => request()->routeIs('admin.ecommerce.orders*'),
            ];
        }

        if ($user->hasPermission('ecommerce.licenses.manage')) {
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

        // 7. Organization Administration & RBAC
        $adminItems = [];

        if ($user->hasPermission('roles.manage')) {
            $adminItems[] = [
                'title' => 'Roles & Permissions',
                'route' => 'admin.roles',
                'icon' => 'shield',
                'active' => request()->routeIs('admin.roles*'),
            ];
        }

        if ($user->hasPermission('plugins.connect')) {
            $adminItems[] = [
                'title' => 'Channel Connectors',
                'route' => 'admin.settings',
                'params' => ['tab' => 'connect'],
                'icon' => 'cpu',
                'active' => request()->routeIs('admin.settings') && request('tab') === 'connect',
            ];
        }

        if ($user->hasPermission('billing.view')) {
            $adminItems[] = [
                'title' => 'Billing & Plans',
                'route' => 'admin.settings',
                'params' => ['tab' => 'pricing'],
                'icon' => 'dollar-sign',
                'active' => request()->routeIs('admin.settings') && request('tab') === 'pricing',
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
