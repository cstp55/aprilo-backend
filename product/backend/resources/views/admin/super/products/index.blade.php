@extends('admin.layout')

@section('title', 'Super Admin - Products & Permissions Catalog')

@section('content')
    <div style="margin-bottom: 16px;">
        <a class="button" style="width: auto; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); font-size: 13px; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 8px 14px; border-radius: 6px;" href="{{ route('admin.super.dashboard') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Super Dashboard
        </a>
    </div>

    <div class="header">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Platform Infrastructure & RBAC</div>
            <h1>Products & Permissions Catalog</h1>
            <p class="help">Define commercial products, configure granular permission bundles, and grant product licenses to client tenant organizations.</p>
        </div>
        <div>
            <a class="button" href="{{ route('admin.super.products.create') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #4f46e5; border-color: #4338ca; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Create New Product
            </a>
        </div>
    </div>

    <!-- Stat Metrics -->
    <div class="grid grid-4" style="margin-bottom: 28px;">
        <div class="card" style="border-top: 4px solid #4f46e5;">
            <div class="metric">{{ $products->count() }}</div>
            <div class="label">Total Products Defined</div>
        </div>
        <div class="card" style="border-top: 4px solid #10b981;">
            <div class="metric">{{ $totalPermissionsCount }}</div>
            <div class="label">Granular RBAC Permissions</div>
        </div>
        <div class="card" style="border-top: 4px solid #f59e0b;">
            <div class="metric">{{ $totalActiveOrgsCount }}</div>
            <div class="label">Active Tenant Organizations</div>
        </div>
        <div class="card" style="border-top: 4px solid #06b6d4;">
            <div class="metric">8</div>
            <div class="label">Specialized Industry Verticals</div>
        </div>
    </div>

    <!-- Commercial Products Grid -->
    <div style="margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
        <h2 style="font-size: 20px; font-weight: 800;">Commercial Product Offerings</h2>
        <span style="font-size: 13px; color: var(--muted); font-weight: 600;">Superadmin has full unrestricted access; tenant users access based on licensed products.</span>
    </div>

    <div class="grid grid-2" style="margin-bottom: 36px; gap: 20px;">
        @foreach ($products as $prod)
            @php
                $categoryBadgeColor = match($prod->category) {
                    'ai_support' => '#4f46e5',
                    'chatbot' => '#2563eb',
                    'support_saas' => '#059669',
                    'sales_crm' => '#d97706',
                    'hr' => '#db2777',
                    'ecommerce' => '#7c3aed',
                    'infrastructure' => '#0891b2',
                    'financial' => '#dc2626',
                    default => '#4b5563'
                };
            @endphp
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; position: relative; border: 1px solid var(--line); border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s;">
                <div>
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <span style="display: inline-block; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; padding: 3px 8px; border-radius: 6px; background: {{ $categoryBadgeColor }}15; color: {{ $categoryBadgeColor }}; border: 1px solid {{ $categoryBadgeColor }}30; margin-bottom: 6px;">
                                {{ ucfirst(str_replace('_', ' ', $prod->category)) }}
                            </span>
                            <h3 style="font-size: 19px; font-weight: 800; color: var(--ink); margin: 0;">{{ $prod->name }}</h3>
                            <div style="font-size: 12px; color: var(--muted); font-family: monospace; margin-top: 2px;">slug: {{ $prod->slug }}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 18px; font-weight: 800; color: var(--ink);">
                                {{ $prod->currency }} {{ number_format($prod->price, 0) }}
                            </div>
                            <span style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700;">
                                {{ str_replace('_', ' ', $prod->product_type) }}
                            </span>
                        </div>
                    </div>

                    <p style="font-size: 13.5px; color: #475569; line-height: 1.5; margin: 0 0 16px;">
                        {{ $prod->short_description ?: 'No description provided.' }}
                    </p>

                    <!-- Feature Tags -->
                    @if (! empty($prod->features))
                        <div style="margin-bottom: 16px;">
                            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--muted); letter-spacing: 0.06em; margin-bottom: 6px;">Key Capabilities:</div>
                            <ul style="margin: 0; padding-left: 18px; font-size: 12.5px; color: #334155; line-height: 1.5;">
                                @foreach (array_slice($prod->features, 0, 4) as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Linked Permissions Summary -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-size: 12px; font-weight: 800; color: #1e293b;">
                                Bundled Permissions ({{ $prod->permissions->count() }})
                            </span>
                            <span style="font-size: 11px; font-weight: 700; color: {{ $prod->is_active ? '#16a34a' : '#dc2626' }};">
                                {{ $prod->is_active ? '● Active in Catalog' : '○ Disabled' }}
                            </span>
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 4px; max-height: 80px; overflow-y: auto;">
                            @forelse ($prod->permissions as $p)
                                <span style="font-size: 11px; background: #ffffff; border: 1px solid #cbd5e1; color: #475569; padding: 2px 7px; border-radius: 4px; font-family: monospace;">
                                    {{ $p->slug }}
                                </span>
                            @empty
                                <span style="font-size: 12px; color: var(--muted); font-style: italic;">No permissions linked yet.</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--line); padding-top: 14px; margin-top: auto;">
                    <span style="font-size: 12px; font-weight: 600; color: var(--muted);">
                        Licensed Tenants: <strong style="color: var(--ink);">{{ $prod->organizations_count }}</strong>
                    </span>
                    <a class="button" href="{{ route('admin.super.products.edit', $prod) }}" style="width: auto; padding: 7px 14px; font-size: 12.5px; background: #ffffff; color: #4f46e5; border: 1px solid #cbd5e1;">
                        Edit & Permissions →
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Complete Permissions Catalog Matrix -->
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <div>
                <h2 style="font-size: 20px; font-weight: 800;">Complete Granular Permissions Matrix</h2>
                <p class="help" style="margin: 4px 0 0;">All platform permissions organized by product domain. Super admin controls which roles and products grant access.</p>
            </div>
            <a class="button" href="{{ route('admin.roles') }}" style="width: auto; padding: 8px 16px; font-size: 13px; background: #0f172a;">
                Manage Roles (RBAC) →
            </a>
        </div>

        @foreach ($allPermissions as $category => $perms)
            <div style="margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; border-bottom: 2px solid #f1f5f9; padding-bottom: 6px;">
                    <span style="font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #4f46e5;">
                        {{ strtoupper($category) }} DOMAIN
                    </span>
                    <span style="font-size: 12px; color: var(--muted);">({{ $perms->count() }} permissions)</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 28%;">Permission Slug</th>
                            <th style="width: 25%;">Display Name</th>
                            <th style="width: 47%;">Description & Scope</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($perms as $perm)
                            <tr>
                                <td>
                                    <code style="font-size: 12.5px; font-weight: 700; color: #0f172a; background: #f1f5f9; padding: 3px 6px; border-radius: 4px;">{{ $perm->slug }}</code>
                                </td>
                                <td style="font-weight: 600; color: var(--ink);">{{ $perm->name }}</td>
                                <td style="color: #64748b; font-size: 13px;">{{ $perm->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
@endsection
