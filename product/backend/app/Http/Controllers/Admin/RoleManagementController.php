<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeRoles($request);

        $orgId = $request->user()->organization_id;

        $roles = Role::whereNull('organization_id')
            ->orWhere('organization_id', $orgId)
            ->withCount(['permissions', 'users'])
            ->orderBy('is_system', 'desc')
            ->latest()
            ->get();

        $users = User::where('organization_id', $orgId)->with('roleModel')->get();

        return view('admin.roles.index', compact('roles', 'users'));
    }

    public function create(Request $request): View
    {
        $this->authorizeRoles($request);

        $permissions = Permission::all()->groupBy('category');

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeRoles($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['array'],
            'permissions.*' => ['uuid', 'exists:permissions,id'],
        ]);

        $slug = Str::slug($validated['name']) . '_' . Str::random(4);

        $role = Role::create([
            'organization_id' => $request->user()->organization_id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        if (! empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('admin.roles')
            ->with('status', "Custom role '{$role->name}' created successfully with " . count($validated['permissions'] ?? []) . ' permissions!');
    }

    public function edit(Request $request, Role $role): View
    {
        $this->authorizeRoles($request);
        abort_if($role->organization_id && $role->organization_id !== $request->user()->organization_id, 404);

        $permissions = Permission::all()->groupBy('category');
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorizeRoles($request);
        abort_if($role->organization_id && $role->organization_id !== $request->user()->organization_id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['array'],
            'permissions.*' => ['uuid', 'exists:permissions,id'],
        ]);

        if (! $role->is_system) {
            $role->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);
        }

        // Sync permissions
        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('admin.roles')
            ->with('status', "Role '{$role->name}' permissions updated successfully!");
    }

    public function assignUserRole(Request $request, User $user): RedirectResponse
    {
        $this->authorizeRoles($request);
        abort_unless($user->organization_id === $request->user()->organization_id, 404);

        $validated = $request->validate([
            'role_id' => ['required', 'uuid', 'exists:roles,id'],
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $user->update([
            'role_id' => $role->id,
            'role' => $role->slug,
        ]);

        return back()->with('status', "Assigned role '{$role->name}' to {$user->name} ({$user->email})");
    }

    private function authorizeRoles(Request $request): void
    {
        abort_unless(
            $request->user() && ($request->user()->hasPermission('roles.manage') || $request->user()->isSuperAdmin() || $request->user()->isHrAdmin()),
            403,
            'Role management permission is required.'
        );
    }
}
