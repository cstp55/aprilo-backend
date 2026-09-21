@extends('admin.layout')

@section('title', 'Roles & Permissions Management')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Access Control (RBAC)</div>
            <h1>Roles & Feature Permissions</h1>
            <p class="help">Define role-based services, configure who can access HR live agent chat, knowledge bases, e-commerce sync, and billing.</p>
        </div>
        <div>
            <a href="{{ route('admin.roles.create') }}" class="button" style="width: auto; padding: 10px 18px; background: #6366f1;">+ Create Custom Role</a>
        </div>
    </div>

    <!-- Roles Directory -->
    <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 16px;">Available Roles</h2>
    <div class="grid grid-2" style="margin-bottom: 28px;">
        @foreach ($roles as $role)
            <div class="card" style="border-top: 4px solid {{ $role->is_system ? '#6366f1' : '#10b981' }};">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h3 style="font-size: 18px; font-weight: 700; margin: 0;">{{ $role->name }}</h3>
                            @if ($role->is_system)
                                <span class="pill" style="background: rgba(99, 102, 241, 0.15); color: #4f46e5; font-size: 10px; font-weight: 800; text-transform: uppercase;">System Role</span>
                            @else
                                <span class="pill" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-size: 10px; font-weight: 800; text-transform: uppercase;">Custom Tenant Role</span>
                            @endif
                        </div>
                        <p style="font-size: 13px; color: var(--muted); margin: 6px 0 12px 0;">{{ $role->description ?? 'No description provided.' }}</p>
                    </div>
                </div>

                <div style="display: flex; gap: 16px; margin: 12px 0; background: #f8fafc; padding: 10px 14px; border-radius: 6px;">
                    <div>
                        <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700;">Granted Permissions</div>
                        <div style="font-size: 16px; font-weight: 800; color: #4f46e5;">{{ $role->permissions_count }} features</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700;">Assigned Users</div>
                        <div style="font-size: 16px; font-weight: 800; color: var(--ink);">{{ $role->users_count }} members</div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 12px;">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="button" style="width: auto; padding: 6px 14px; font-size: 12px; background: transparent; color: #6366f1; border: 1px solid #6366f1;">
                        ⚙️ Configure Permissions
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- User Role Assignments Table -->
    <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 16px;">User Role Assignments</h2>
    <table>
        <thead>
            <tr>
                <th>User Name</th>
                <th>Email</th>
                <th>Current Role</th>
                <th>Change Role Assignment</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $u)
                <tr>
                    <td><strong>{{ $u->name }}</strong></td>
                    <td>{{ $u->email }}</td>
                    <td>
                        <span class="pill" style="background: rgba(99, 102, 241, 0.12); color: #4f46e5; font-weight: 700; text-transform: uppercase; font-size: 11px;">
                            {{ $u->roleModel->name ?? ucfirst($u->role_slug) }}
                        </span>
                    </td>
                    <td>
                        <form class="row-form" method="POST" action="{{ route('admin.roles.assign', $u) }}">
                            @csrf
                            <select name="role_id" style="width: auto; padding: 6px 10px; font-size: 12px; border-radius: 6px; border: 1px solid var(--line);">
                                @foreach ($roles as $r)
                                    <option value="{{ $r->id }}" @selected($u->role_id === $r->id || $u->role === $r->slug)>
                                        {{ $r->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="button" type="submit" style="width: auto; padding: 6px 12px; font-size: 12px; background: #6366f1;">Assign</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
