@extends('admin.layout')

@section('title', 'Super Admin - Organizations Directory')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Platform Directory</div>
            <h1>All Registered Organizations</h1>
            <p class="help">Detailed inspection of client tenant settings, user rosters, and service allocations.</p>
        </div>
    </div>

    <div class="card" style="margin-bottom: 24px;">
        <h2 style="font-size: 20px; margin-bottom: 8px;">Add organization</h2>
        <p class="help" style="margin: 0 0 18px;">Create a tenant and its owner account. Subscription access can be granted after creation.</p>
        <form method="POST" action="{{ route('admin.super.organizations.store') }}" style="margin: 0; padding: 0; border: 0; box-shadow: none;">
            @csrf
            <div class="grid grid-3">
                <label class="field">Organization name<input name="name" value="{{ old('name') }}" required></label>
                <label class="field">Organization email<input name="email" type="email" value="{{ old('email') }}" required></label>
                <label class="field">Organization status<select name="status" required><option value="active">Active</option><option value="trial">Trial</option><option value="suspended">Suspended</option></select></label>
                <label class="field">Owner full name<input name="owner_name" value="{{ old('owner_name') }}" required></label>
                <label class="field">Owner username<input name="owner_username" value="{{ old('owner_username') }}" required></label>
                <label class="field">Owner email<input name="owner_email" type="email" value="{{ old('owner_email') }}" required></label>
                <label class="field">Owner password<input name="owner_password" type="password" minlength="8" required></label>
                <label class="field">Confirm password<input name="owner_password_confirmation" type="password" minlength="8" required></label>
            </div>
            <button class="button" type="submit" style="width: auto; margin-top: 18px;">Create organization</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Organization</th>
                <th>Users Count</th>
                <th>Knowledge Chunks</th>
                <th>Questions Count</th>
                <th>Billing Plan</th>
                <th>Access</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($organizations as $org)
                <tr>
                    <td>
                        <strong>{{ $org->name }}</strong>
                        <div style="font-size: 11px; color: var(--muted);">UUID: {{ $org->id }}</div>
                    </td>
                    <td>{{ $org->users_count }} users</td>
                    <td>{{ $org->knowledge_sources_count }} sources</td>
                    <td>{{ $org->questions_count }} queries</td>
                    <td>
                        <span class="pill" style="background: rgba(99, 102, 241, 0.15); color: #4f46e5; font-weight: 700; text-transform: uppercase;">
                            {{ $org->plan ?? 'Enterprise' }}
                        </span>
                    </td>
                    <td>
                        <a class="button" style="display: inline-block; width: auto; padding: 8px 12px; font-size: 12px;" href="{{ route('admin.super.organizations.subscriptions', $org) }}">Manage access</a>
                    </td>
                    <td>{{ $org->created_at ? $org->created_at->format('M d, Y') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No organizations found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 18px;">
        {{ $organizations->links() }}
    </div>
@endsection
