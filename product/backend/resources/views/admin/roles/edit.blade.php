@extends('admin.layout')

@section('title', 'Edit Role Permissions')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Role Editor</div>
            <h1>Configure Permissions: {{ $role->name }}</h1>
            <p class="help">Modify permissions and feature access for this role. Active users with this role will receive immediate permission updates.</p>
        </div>
    </div>

    <form class="card" method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PATCH')
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <label class="field">
                Role Name
                <input name="name" value="{{ $role->name }}" {{ $role->is_system ? 'readonly' : '' }} required>
            </label>
            <label class="field">
                Description
                <input name="description" value="{{ $role->description }}" {{ $role->is_system ? 'readonly' : '' }}>
            </label>
        </div>

        <h2 style="font-size: 16px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); margin: 24px 0 16px 0;">
            Feature & Service Permissions Matrix
        </h2>

        <div style="display: grid; gap: 24px; margin-bottom: 24px;">
            @foreach ($permissions as $category => $categoryPerms)
                <div style="background: #faf9f6; border: 1px solid var(--line); border-radius: 8px; padding: 18px;">
                    <h3 style="font-size: 15px; font-weight: 800; text-transform: uppercase; color: #4f46e5; margin: 0 0 12px 0;">
                        {{ strtoupper($category) }} Feature Permissions
                    </h3>

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                        @foreach ($categoryPerms as $perm)
                            <label style="display: flex; align-items: flex-start; gap: 10px; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; cursor: pointer;">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" @checked(in_array($perm->id, $rolePermissions, true)) style="width: auto; margin-top: 3px;">
                                <div>
                                    <div style="font-weight: 700; font-size: 13px; color: var(--ink);">{{ $perm->name }}</div>
                                    <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">{{ $perm->description }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--line); padding-top: 18px;">
            <a href="{{ route('admin.roles') }}" class="button" style="width: auto; padding: 10px 20px; background: transparent; border: 1px solid var(--line); color: var(--ink);">Cancel</a>
            <button class="button" type="submit" style="width: auto; padding: 10px 24px; background: #6366f1;">Save Changes</button>
        </div>
    </form>
@endsection
