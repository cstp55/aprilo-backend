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

    <!-- Licensed Products & Entitlements -->
    <section class="card" style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h2 style="font-size: 18px; margin: 0;">Licensed Products & Feature Access</h2>
                <p class="help" style="margin: 4px 0 0;">Products currently active for this tenant. Unlocks corresponding features for tenant owners and roles.</p>
            </div>
            <a class="button" style="width: auto; font-size: 13px; padding: 7px 14px; background: #ffffff; color: var(--ink); border: 1px solid var(--line);" href="{{ route('admin.super.products') }}">
                Product Catalog →
            </a>
        </div>

        <div style="display: grid; gap: 12px; margin-bottom: 20px;">
            @forelse ($organization->products as $orgProd)
                <div style="display: flex; justify-content: space-between; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <strong style="font-size: 15px; color: var(--ink);">{{ $orgProd->name }}</strong>
                            <span style="font-size: 11px; background: #e0e7ff; color: #4338ca; padding: 2px 6px; border-radius: 4px; font-weight: 700;">
                                {{ ucfirst(str_replace('_', ' ', $orgProd->category)) }}
                            </span>
                            <span style="font-size: 11px; background: #dcfce7; color: #15803d; padding: 2px 6px; border-radius: 4px; font-weight: 700;">
                                ● Active
                            </span>
                        </div>
                        <div style="font-size: 12px; color: var(--muted); margin-top: 3px;">
                            {{ $orgProd->permissions->count() }} bundled permissions · Valid until: {{ $orgProd->pivot->expires_at ? \Carbon\Carbon::parse($orgProd->pivot->expires_at)->format('M d, Y') : 'Permanent' }}
                        </div>
                    </div>
                    <div>
                        <form method="POST" action="{{ route('admin.super.organizations.products.destroy', [$organization, $orgProd]) }}" onsubmit="return confirm('Revoke access to this product for {{ $organization->name }}?');" style="margin: 0; padding: 0; border: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: transparent; border: 1px solid #fca5a5; color: #dc2626; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                Revoke License
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="color: var(--muted); font-size: 13.5px; padding: 12px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                    No direct products currently licensed. Check subscriptions or grant access below.
                </div>
            @endforelse
        </div>

        <!-- Grant Product Form -->
        <div style="border-top: 1px solid var(--line); padding-top: 16px;">
            <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 12px;">Grant Product License to Tenant</h3>
            <form method="POST" action="{{ route('admin.super.organizations.products.store', $organization) }}" style="margin: 0; padding: 0; border: 0; box-shadow: none;">
                @csrf
                <div class="grid grid-3" style="align-items: end;">
                    <label class="field">Select Product
                        <select name="product_id" required>
                            <option value="">-- Choose Product --</option>
                            @foreach ($allProducts as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ ucfirst($p->category) }})</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field">License Expiration Date (Optional)
                        <input name="expires_at" type="date" value="{{ now()->addYear()->format('Y-m-d') }}">
                    </label>
                    <div>
                        <button class="button" type="submit" style="width: auto; background: #4f46e5; border-color: #4338ca;">
                            + Grant Product License
                        </button>
                    </div>
                </div>
            </form>
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