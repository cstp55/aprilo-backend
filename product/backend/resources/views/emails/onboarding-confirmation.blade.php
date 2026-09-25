<!doctype html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <h1>Your Aprilo organization is ready</h1>
    <p>Hello {{ $onboarding->user?->name ?? $onboarding->account_data['name'] ?? 'there' }},</p>
    <p>Your Aprilo onboarding is complete.</p>
    <dl>
        <dt><strong>Organization</strong></dt>
        <dd>{{ $onboarding->organization?->name ?? $onboarding->organization_data['name'] }}</dd>
        <dt><strong>Product</strong></dt>
        <dd>{{ $onboarding->product?->name }}</dd>
        <dt><strong>Plan</strong></dt>
        <dd>{{ $onboarding->plan?->name }}</dd>
        <dt><strong>Trial start</strong></dt>
        <dd>{{ $onboarding->trial_ends_at ? $onboarding->trial_ends_at->copy()->subDays($onboarding->plan?->trial_period_days ?? 0)->toDateString() : 'Not applicable' }}</dd>
        <dt><strong>Trial end</strong></dt>
        <dd>{{ $onboarding->trial_ends_at?->toDateString() ?? 'Not applicable' }}</dd>
        <dt><strong>Billing</strong></dt>
        <dd>{{ $onboarding->plan?->price }} {{ $onboarding->plan?->currency }} / {{ $onboarding->plan?->billing_cycle }}</dd>
    </dl>
    <p><a href="{{ url('/admin') }}">Open your dashboard</a></p>
    <p>Need help? Contact {{ config('services.aprilo.support_email') }}.</p>
    <p>Password credentials are not included in email.</p>
</body>
</html>
