@extends('admin.layout')

@section('title', 'Create Custom Role')

@section('content')
    <div style="margin-bottom: 16px;">
        <a class="button" style="width: auto; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); font-size: 13px; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 8px 14px; border-radius: 6px;" href="{{ route('admin.roles') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Roles Directory
        </a>
    </div>

    <div class="header">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Role Builder</div>
            <h1>Create Custom Role</h1>
            <p class="help">Define a new role and choose the exact product features, dashboards, and operational controls it can access.</p>
        </div>
    </div>

    <form class="card" method="POST" action="{{ route('admin.roles.store') }}">
        @csrf
        
        <div class="grid grid-2" style="gap: 20px; margin-bottom: 24px;">
            <label class="field">
                Role Name *
                <input name="name" value="{{ old('name') }}" placeholder="e.g. HR Support Specialist, IT Helpdesk Tier 2, E-commerce Catalog Editor" required>
            </label>
            <label class="field">
                Description (Optional)
                <input name="description" value="{{ old('description') }}" placeholder="Brief summary of duties and service scope">
            </label>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; margin: 24px 0 16px 0; border-top: 1px solid var(--line); padding-top: 16px;">
            <h2 style="font-size: 18px; font-weight: 800;">Product & Module Permissions Matrix</h2>
            <span style="font-size: 12px; color: var(--muted);">Select permissions granted to this role</span>
        </div>

        <div style="display: grid; gap: 20px; margin-bottom: 24px;">
            @foreach ($permissions as $category => $categoryPerms)
                @php
                    $categoryTitle = match($category) {
                        'core' => 'Platform Core & Administration',
                        'chatbot' => 'Aprilo AI & Chatbot Suite',
                        'support' => 'Aprilo Support & IT Services',
                        'hr' => 'Aprilo HR Panel (Actions & Views)',
                        'sales' => 'Aprilo Sales & CRM Marketing',
                        'ecommerce' => 'Aprilo Commerce & D2C Suite',
                        'infrastructure' => 'Aprilo IT Infrastructure & Data Centers',
                        'financial' => 'Aprilo FinTech & Banking Services',
                        'super_admin' => 'Global Platform Super Administrator',
                        default => ucfirst($category) . ' Domain'
                    };
                    $categoryColor = match($category) {
                        'core' => '#64748b',
                        'chatbot' => '#2563eb',
                        'support' => '#059669',
                        'hr' => '#db2777',
                        'sales' => '#d97706',
                        'ecommerce' => '#7c3aed',
                        'infrastructure' => '#0891b2',
                        'financial' => '#dc2626',
                        'super_admin' => '#4338ca',
                        default => '#4b5563'
                    };
                @endphp
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                        <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: {{ $categoryColor }};"></span>
                        <h3 style="font-size: 15px; font-weight: 800; color: {{ $categoryColor }}; margin: 0;">
                            {{ $categoryTitle }}
                        </h3>
                        <span style="font-size: 11px; color: var(--muted); font-weight: 600;">({{ $categoryPerms->count() }} permissions)</span>
                    </div>

                    @if ($category === 'hr')
                        <div style="background: #fdf2f8; border: 1px solid #fbcfe8; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; font-size: 12.5px; color: #9d174d;">
                            💡 <strong>HR Panel Customization:</strong> Super admin decides whether HR views, approval actions (leaves, WFH, ID cards, badge validation), and employee self-service are common or restricted to specific specialist roles.
                        </div>
                    @endif

                    <div class="grid grid-2" style="gap: 10px;">
                        @foreach ($categoryPerms as $perm)
                            <label style="display: flex; align-items: flex-start; gap: 10px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 9px 12px; cursor: pointer;">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }} style="width: auto; margin-top: 3px;">
                                <div>
                                    <div style="font-weight: 700; font-size: 13px; color: var(--ink);">{{ $perm->name }}</div>
                                    <div style="font-size: 11px; font-family: monospace; color: #64748b;">{{ $perm->slug }}</div>
                                    <div style="font-size: 11.5px; color: var(--muted); margin-top: 2px;">{{ $perm->description }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--line); padding-top: 18px;">
            <a href="{{ route('admin.roles') }}" class="button" style="width: auto; padding: 10px 20px; background: transparent; border: 1px solid var(--line); color: var(--ink);">Cancel</a>
            <button class="button" type="submit" style="width: auto; padding: 10px 28px; background: #6366f1;">Save Role & Permissions</button>
        </div>
    </form>
@endsection
