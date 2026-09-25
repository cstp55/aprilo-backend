@extends('admin.layout')

@section('title', 'Subscription Access - ' . $organization->name)

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Super Admin Control</div>
            <h1>Subscription Access</h1>
            <p class="help">Grant a product plan and feature entitlements to <strong>{{ $organization->name }}</strong>. This action is recorded in the audit log.</p>
        </div>
        <a class="button" style="width: auto; background: transparent; color: var(--ink); border: 1px solid var(--line); box-shadow: none;" href="{{ route('admin.super.organizations') }}">Back to organizations</a>
    </div>

    <div class="grid grid-2" style="align-items: start;">
        <div class="card">
            <h2 style="font-size: 20px; margin-bottom: 16px;">Grant subscription</h2>
            <form method="POST" action="{{ route('admin.super.organizations.subscriptions.store', $organization) }}" style="margin: 0; padding: 0; border: 0; box-shadow: none;">
                @csrf
                <div class="grid grid-2">
                    <label class="field">Product
                        <select name="product_id" required>
                            <option value="">Select product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->slug }})</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field">Plan
                        <select name="plan_id" required>
                            <option value="">Select plan</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->product->name }} / {{ $plan->name }} - {{ $plan->price }} {{ $plan->currency }} / {{ $plan->billing_cycle }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field">Status
                        <select name="status" required>
                            <option value="active">Active</option>
                            <option value="trialing">Trialing</option>
                            <option value="past_due">Past due</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </label>
                    <label class="field">Access ends <span style="font-weight: 400; color: var(--muted);">optional</span>
                        <input name="ends_at" type="datetime-local">
                    </label>
                </div>

                <div style="margin-top: 20px;">
                    <div class="field" style="margin-bottom: 10px;">Feature entitlements</div>
                    <div style="display: grid; gap: 9px;">
                        @foreach ($featureOptions as $feature => $label)
                            <label style="display: flex; align-items: center; gap: 9px; font-size: 13px; font-weight: 600;">
                                <input type="checkbox" name="features[]" value="{{ $feature }}" @checked($feature === 'support.agents' || $feature === 'support')>
                                {{ $label }} <code style="color: var(--muted);">{{ $feature }}</code>
                            </label>
                        @endforeach
                    </div>
                </div>

                <label class="field" style="margin-top: 18px;">Maximum support agents <span style="font-weight: 400; color: var(--muted);">optional</span>
                    <input name="max_agents" type="number" min="1" max="100000" placeholder="Unlimited">
                </label>

                <button class="button" type="submit" style="width: auto; margin-top: 20px;">Grant access</button>
            </form>
        </div>

        <div class="card">
            <h2 style="font-size: 20px; margin-bottom: 16px;">Current access</h2>
            @forelse ($subscriptions as $subscription)
                <div style="border-bottom: 1px solid var(--line); padding: 0 0 16px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; gap: 10px;">
                        <strong>{{ $subscription->product->name }} / {{ $subscription->plan->name }}</strong>
                        <span class="pill">{{ $subscription->status }}</span>
                    </div>
                    <p style="margin: 7px 0 0; color: var(--muted); font-size: 12px;">{{ $subscription->price }} {{ $subscription->currency }} / {{ $subscription->plan->billing_cycle }} · {{ $subscription->created_at?->format('M d, Y') }}</p>
                    @if ($subscription->metadata['manual_grant'] ?? false)
                        <p style="margin: 5px 0 0; color: #4f46e5; font-size: 12px; font-weight: 700;">Manual superadmin grant</p>
                    @endif
                    <form method="POST" action="{{ route('admin.super.organizations.subscriptions.payment-status', [$organization, $subscription]) }}" style="display: flex; gap: 8px; align-items: end; margin: 12px 0 0; padding: 0; border: 0; box-shadow: none;">
                        @csrf
                        @method('PATCH')
                        <label class="field" style="flex: 1; font-size: 11px;">Manual payment status
                            <select name="status">
                                @foreach (['active', 'trialing', 'past_due', 'cancelled', 'halted'] as $status)
                                    <option value="{{ $status }}" @selected($subscription->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button class="button" type="submit" style="width: auto; padding: 10px 13px;">Update status</button>
                    </form>
                </div>
            @empty
                <p style="margin: 0; color: var(--muted); font-size: 14px;">No subscriptions found.</p>
            @endforelse

            <h3 style="font-size: 15px; margin: 22px 0 10px;">Active entitlements</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                @forelse ($entitlements as $entitlement)
                    <span class="pill" style="background: #ecfdf5; color: #047857;">{{ $entitlement->feature_key }}{{ data_get($entitlement->limits, 'max_agents') ? ' (' . data_get($entitlement->limits, 'max_agents') . ' seats)' : '' }}</span>
                @empty
                    <span style="color: var(--muted); font-size: 13px;">No active entitlements.</span>
                @endforelse
            </div>
        </div>
    </div>
@endsection
