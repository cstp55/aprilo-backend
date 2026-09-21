@extends('admin.layout')

@section('title', 'Super Admin Global Control')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Platform Command Center</div>
            <h1>Super Administrator Dashboard</h1>
            <p class="help">Cross-tenant overview of all organizations, users, revenue, live agent inquiries, and AI services.</p>
        </div>
    </div>

    <!-- Aggregate Stats Grid -->
    <div class="grid grid-4" style="margin-bottom: 24px;">
        <div class="card" style="border-left: 4px solid #6366f1;">
            <div class="metric">{{ $orgCount }}</div>
            <div class="label">Total Organizations</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Across all service plans</div>
        </div>
        <div class="card" style="border-left: 4px solid #10b981;">
            <div class="metric">${{ number_format($totalRevenue, 2) }}</div>
            <div class="label">Gross Revenue</div>
            <div style="font-size: 11px; color: #10b981; margin-top: 4px; font-weight: 700;">Active SaaS Billing</div>
        </div>
        <div class="card" style="border-left: 4px solid #3b82f6;">
            <div class="metric">{{ $hrAdminCount }} / {{ $ecomAdminCount }}</div>
            <div class="label">HR vs E-commerce Admins</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">{{ $employeeCount }} Total Employees</div>
        </div>
        <div class="card" style="border-left: 4px solid #f59e0b;">
            <div class="metric">{{ $totalQuestions }}</div>
            <div class="label">Global AI Queries</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">100% Grounded RAG Search</div>
        </div>
    </div>

    <!-- Active Organizations Directory -->
    <div style="margin-top: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h2 style="font-size: 18px; font-weight: 700; margin: 0;">Connected Client Organizations</h2>
                <p style="font-size: 13px; color: var(--muted); margin: 4px 0 0 0;">Inspect individual tenant settings, user counts, and active assistants.</p>
            </div>
            <a href="{{ route('admin.super.organizations') }}" class="button" style="width: auto; padding: 8px 16px; font-size: 13px; background: #6366f1;">View All Directory</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Organization Name</th>
                    <th>Status</th>
                    <th>Plan</th>
                    <th>Users</th>
                    <th>AI Questions</th>
                    <th>Bot Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($organizations as $org)
                    <tr>
                        <td>
                            <strong>{{ $org->name }}</strong>
                            <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">ID: {{ substr($org->id, 0, 8) }}...</div>
                        </td>
                        <td>
                            @php
                                $statusStyle = match($org->status) {
                                    'active' => 'background: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3);',
                                    'suspended' => 'background: rgba(239, 68, 68, 0.15); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.3);',
                                    default => 'background: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3);'
                                };
                            @endphp
                            <span class="pill" style="{{ $statusStyle }} font-size: 11px; text-transform: uppercase;">{{ $org->status }}</span>
                        </td>
                        <td>
                            <span class="pill" style="background: rgba(99, 102, 241, 0.12); color: #4f46e5; border: 1px solid rgba(99, 102, 241, 0.25); font-size: 11px; text-transform: uppercase; font-weight: 700;">
                                {{ $org->plan ?? 'Enterprise' }}
                            </span>
                        </td>
                        <td style="font-weight: 600;">{{ $org->users_count }}</td>
                        <td style="font-weight: 600;">{{ $org->questions_count }}</td>
                        <td>
                            <span style="font-size: 12px; color: var(--ink); font-weight: 600;">
                                {{ $org->settings->assistant_name ?? 'Aprilo Bot' }}
                            </span>
                        </td>
                        <td>
                            <button class="button" style="width: auto; padding: 6px 12px; font-size: 12px; background: transparent; color: #6366f1; border: 1px solid #6366f1; cursor: pointer;" onclick="toggleOrgInspector('{{ $org->id }}')">
                                Quick Edit
                            </button>
                        </td>
                    </tr>

                    <!-- Inline Inspector Form -->
                    <tr id="inspector-{{ $org->id }}" style="display: none; background: #faf9f6;">
                        <td colspan="7" style="padding: 20px; border-left: 4px solid #6366f1; border-bottom: 1px solid var(--line);">
                            <form method="POST" action="{{ route('admin.super.organizations.update', $org) }}">
                                @csrf
                                @method('PATCH')
                                <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 16px; align-items: end;">
                                    <label class="field">
                                        Organization Status
                                        <select name="status" style="background: #fff;">
                                            <option value="active" @selected($org->status === 'active')>Active</option>
                                            <option value="suspended" @selected($org->status === 'suspended')>Suspended</option>
                                            <option value="trial" @selected($org->status === 'trial')>Free Trial</option>
                                        </select>
                                    </label>
                                    <label class="field">
                                        Subscribed Plan
                                        <select name="plan" style="background: #fff;">
                                            <option value="starter" @selected($org->plan === 'starter')>Starter Tier</option>
                                            <option value="growth" @selected($org->plan === 'growth')>Growth Tier</option>
                                            <option value="enterprise" @selected($org->plan === 'enterprise')>Enterprise Tier</option>
                                        </select>
                                    </label>
                                    <div style="display: flex; gap: 8px;">
                                        <button class="button" type="submit" style="width: auto; padding: 10px 18px; background: #6366f1;">Update Organization</button>
                                        <button class="button" type="button" style="width: auto; padding: 10px 18px; background: transparent; border: 1px solid var(--line); color: var(--ink);" onclick="toggleOrgInspector('{{ $org->id }}')">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No organizations registered yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        function toggleOrgInspector(id) {
            const row = document.getElementById('inspector-' + id);
            if (row.style.display === 'none') {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        }
    </script>
@endsection
