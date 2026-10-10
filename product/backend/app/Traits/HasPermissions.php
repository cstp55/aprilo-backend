<?php

namespace App\Traits;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasPermissions
{
    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getRoleSlugAttribute(): string
    {
        if ($this->roleModel) {
            return $this->roleModel->slug;
        }

        return (string) ($this->attributes['role'] ?? 'employee');
    }

    public function hasRole(string|array $roles): bool
    {
        $currentSlug = $this->role_slug;

        if (is_string($roles)) {
            $roles = [$roles];
        }

        // super_admin matches any admin role check
        if ($currentSlug === 'super_admin') {
            return true;
        }

        return in_array($currentSlug, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role_slug === 'super_admin';
    }

    public function isHrAdmin(): bool
    {
        return in_array($this->role_slug, ['hr_admin', 'super_admin'], true);
    }

    public function isEcommerceAdmin(): bool
    {
        return in_array($this->role_slug, ['ecommerce_admin', 'super_admin'], true);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        // 1. Super Admin has unrestricted system-wide access
        if ($this->isSuperAdmin()) {
            return true;
        }

        // 2. Organization Owner dynamically inherits permissions for licensed products
        if ($this->role_slug === 'owner') {
            // Core management permissions granted to owners
            if (str_starts_with($permissionSlug, 'billing.') ||
                str_starts_with($permissionSlug, 'roles.') ||
                str_starts_with($permissionSlug, 'users.')) {
                return true;
            }

            if (! $this->organization) {
                return false;
            }

            // Check if any active product for this organization has this permission
            $directProducts = $this->organization->products()
                ->wherePivot('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('organization_products.expires_at')
                        ->orWhere('organization_products.expires_at', '>=', now());
                })
                ->with('permissions')
                ->get();

            foreach ($directProducts as $product) {
                if ($product->hasPermission($permissionSlug)) {
                    return true;
                }
            }

            // Category fallbacks
            if (str_starts_with($permissionSlug, 'ecommerce.')) {
                return $this->organization->hasActiveProductCategory('ecommerce');
            }
            if (str_starts_with($permissionSlug, 'hr.') || str_starts_with($permissionSlug, 'employee.')) {
                return $this->organization->hasActiveProductCategory('hr')
                    || $this->organization->hasActiveProductCategory('aprilo-hr');
            }
            if (str_starts_with($permissionSlug, 'support.') || str_starts_with($permissionSlug, 'escalations.')) {
                return $this->organization->hasActiveProductCategory('support_saas')
                    || $this->organization->hasActiveProductCategory('ai_support')
                    || $this->organization->hasActiveProductCategory('aprilo-support')
                    || $this->organization->hasActiveProductCategory('aprilo-ai');
            }
            if (str_starts_with($permissionSlug, 'widget.') || str_starts_with($permissionSlug, 'live_chat.') || str_starts_with($permissionSlug, 'plugins.') || str_starts_with($permissionSlug, 'chatbot.')) {
                return $this->organization->hasActiveProductCategory('ai_support')
                    || $this->organization->hasActiveProductCategory('chatbot')
                    || $this->organization->hasActiveProductCategory('aprilo-ai')
                    || $this->organization->hasActiveProductCategory('aprilo-chatbot');
            }
            if (str_starts_with($permissionSlug, 'sales.') || str_starts_with($permissionSlug, 'whatsapp.')) {
                return $this->organization->hasActiveProductCategory('sales_crm')
                    || $this->organization->hasActiveProductCategory('aprilo-sales');
            }
            if (str_starts_with($permissionSlug, 'infra.')) {
                return $this->organization->hasActiveProductCategory('infrastructure')
                    || $this->organization->hasActiveProductCategory('aprilo-infrastructure');
            }
            if (str_starts_with($permissionSlug, 'banking.')) {
                return $this->organization->hasActiveProductCategory('financial')
                    || $this->organization->hasActiveProductCategory('aprilo-fintech');
            }

            if ($permissionSlug === 'dashboard.view' || $permissionSlug === 'analytics.view' || $permissionSlug === 'logs.view') {
                return $this->organization->hasActiveProductCategory('ai_support')
                    || $this->organization->hasActiveProductCategory('ecommerce')
                    || $this->organization->hasActiveProductCategory('chatbot')
                    || $this->organization->hasActiveProductCategory('support_saas')
                    || $this->organization->hasActiveProductCategory('hr')
                    || $this->organization->hasActiveProductCategory('sales_crm');
            }

            return false;
        }

        // 3. For non-owners, check explicitly assigned Role permissions
        if ($this->roleModel && $this->roleModel->hasPermission($permissionSlug)) {
            return true;
        }

        // 4. Fallback compatibility with legacy role strings if role_id not linked
        $legacyRole = $this->attributes['role'] ?? 'employee';

        if ($legacyRole === 'hr_admin') {
            // HR Admins have core & HR permissions by default
            return str_starts_with($permissionSlug, 'core.')
                || str_starts_with($permissionSlug, 'hr.')
                || str_starts_with($permissionSlug, 'widget.')
                || str_starts_with($permissionSlug, 'knowledge.')
                || str_starts_with($permissionSlug, 'escalations.')
                || str_starts_with($permissionSlug, 'employee.')
                || str_starts_with($permissionSlug, 'billing.')
                || str_starts_with($permissionSlug, 'logs.')
                || str_starts_with($permissionSlug, 'dashboard.')
                || str_starts_with($permissionSlug, 'analytics.')
                || str_starts_with($permissionSlug, 'roles.')
                || str_starts_with($permissionSlug, 'plugins.');
        }

        if ($legacyRole === 'ecommerce_admin') {
            return str_starts_with($permissionSlug, 'ecommerce.')
                || str_starts_with($permissionSlug, 'widget.')
                || str_starts_with($permissionSlug, 'billing.')
                || str_starts_with($permissionSlug, 'logs.')
                || str_starts_with($permissionSlug, 'dashboard.')
                || str_starts_with($permissionSlug, 'analytics.');
        }

        return false;
    }

    public function hasAnyPermission(array $permissionSlugs): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissionSlugs as $slug) {
            if ($this->hasPermission($slug)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions(array $permissionSlugs): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissionSlugs as $slug) {
            if (! $this->hasPermission($slug)) {
                return false;
            }
        }

        return true;
    }
}
