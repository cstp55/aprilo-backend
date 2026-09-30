@extends('admin.layout')

@section('title', 'Organization Details - ' . $organization->name)

@section('content')
    <div style="margin-bottom: 16px;">
        <a class="button" style="width: auto; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); font-size: 13px; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 8px 14px; border-radius: 6px;" href="{{ route('admin.super.organizations') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Organizations
        </a>
    </div>

    <div class="header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; flex-wrap: wrap;">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Organization profile</div>
            <h1>{{ $organization->name }}</h1>
            <p class="help" style="margin-bottom: 0;">Tenant ID: {{ $organization->id }}</p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a class="button" style="width: auto;" href="{{ route('admin.super.organizations.subscriptions', $organization) }}">Manage subscription</a>
            <a class="button" style="width: auto;" href="{{ route('admin.super.organizations.widget', $organization) }}">Widget & publish</a>
            <a class="button" style="width: auto;" href="{{ route('admin.super.organizations.knowledge', $organization) }}">Knowledge base</a>
        </div>
    </div>

    <section class="card" style="margin-bottom: 20px;">
        <h2 style="font-size: 18px; margin: 0 0 16px;">Company details</h2>
        <div class="grid grid-3">
            <div><strong>Name</strong><div>{{ $organization->name }}</div></div>
            <div><strong>Organization email</strong><div>{{ $organization->email ?: 'Not provided' }}</div></div>
            <div><strong>Website</strong><div>{{ $organization->website ?: 'Not provided' }}</div></div>
            <div><strong>Country</strong><div>{{ $organization->country ?: 'Not provided' }}</div></div>
            <div><strong>Industry</strong><div>{{ $organization->industry ?: 'Not provided' }}</div></div>
            <div><strong>Team size</strong><div>{{ $organization->team_size ?: 'Not provided' }}</div></div>
            <div><strong>Timezone</strong><div>{{ $organization->timezone ?: 'Not provided' }}</div></div>
            <div><strong>Status</strong><div>{{ ucfirst($organization->status ?: 'unknown') }}</div></div>
            <div><strong>Legacy plan</strong><div>{{ $organization->plan ?: 'Not set' }}</div></div>
            <div><strong>Created</strong><div>{{ $organization->created_at?->format('M d, Y H:i') ?: 'Unknown' }}</div></div>
            <div><strong>Last updated</strong><div>{{ $organization->updated_at?->format('M d, Y H:i') ?: 'Unknown' }}</div></div>
        </div>
    </section>

    <section>
        <div style="margin-bottom: 14px;">
            <h2 style="font-size: 18px; margin: 0;">Organization owner accounts</h2>
            <p class="help" style="margin: 5px 0 0;">Update login identifiers or set a new password. Passwords are never displayed.</p>
        </div>

        @forelse ($owners as $owner)
            <div class="card" style="margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; gap: 12px; align-items: baseline; margin-bottom: 14px;">
                    <strong>{{ $owner->name }}</strong>
                    <span style="font-size: 12px; color: var(--muted);">{{ $owner->status ?: 'unknown' }}</span>
                </div>
                <form method="POST" action="{{ route('admin.super.organizations.owners.credentials', [$organization, $owner]) }}" style="margin: 0; padding: 0; border: 0; box-shadow: none;">
                    @csrf
                    @method('PATCH')
                    <div class="grid grid-2">
                        <label class="field">Username
                            <input name="username" value="{{ old('username', $owner->username) }}" autocomplete="username" required>
                        </label>
                        <label class="field">Email
                            <input name="email" type="email" value="{{ old('email', $owner->email) }}" autocomplete="email" required>
                        </label>
                        <label class="field">New password
                            <input name="password" type="password" minlength="8" autocomplete="new-password" placeholder="Leave blank to keep current">
                        </label>
                        <label class="field">Confirm new password
                            <input name="password_confirmation" type="password" minlength="8" autocomplete="new-password">
                        </label>
                    </div>
                    <button class="button" type="submit" style="width: auto; margin-top: 16px;">Update owner credentials</button>
                </form>
            </div>
        @empty
            <div class="card" style="color: var(--muted);">No organization owner account is associated with this tenant.</div>
        @endforelse
    </section>
@endsection