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
        return in_array($this->role_slug, ['hr_admin', 'owner', 'super_admin'], true);
    }

    public function isEcommerceAdmin(): bool
    {
        return in_array($this->role_slug, ['ecommerce_admin', 'owner', 'super_admin'], true);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        // 1. Super Admin has unrestricted system-wide access
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Organization owners have full access within their own tenant.
        if (($this->attributes['role'] ?? null) === 'owner') {
            return true;
        }

        // 2. Check assigned Role permissions
        if ($this->roleModel) {
            return $this->roleModel->hasPermission($permissionSlug);
        }

        // 3. Fallback compatibility with legacy role strings if role_id not linked
        $legacyRole = $this->attributes['role'] ?? 'employee';

        if (in_array($legacyRole, ['owner', 'hr_admin'], true)) {
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
