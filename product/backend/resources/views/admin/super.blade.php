@extends('admin.layout')

@section('title', 'Aprilo AI - Platform Super Admin Console')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow font-bold">Platform Control Room</div>
            <h1>Super Admin Console</h1>
            <p class="help">Monitor all business tenants, monthly recurring revenue, cross-tenant file uploads, and global vector database metrics.</p>
        </div>
    </div>

    <!-- Metrics Row -->
    <div class="grid grid-4" style="margin-bottom: 24px;">
        <div class="card">
            <div class="metric">{{ $totalOrganizations }}</div>
            <div class="label">🏢 Active Tenants</div>
        </div>
        <div class="card">
            <div class="metric">{{ $totalUsers }}</div>
            <div class="label">👥 Total System Users</div>
        </div>
        <div class="card" style="border-left: 4px solid var(--green);">
            <div class="metric">${{ number_format($mrr, 2) }}</div>
            <div class="label">📈 Est. Monthly Revenue (MRR)</div>
        </div>
        <div class="card">
            <div class="metric">{{ $totalChunks }}</div>
            <div class="label">🧬 Indexed Vector Chunks</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr; gap: 24px;">
        <!-- SECTION 1: TENANT SUBSCRIPTION & PRICING -->
        <div class="card">
            <h3 style="margin-bottom: 14px; font-size: 16px; border-bottom: 1px solid var(--line); padding-bottom: 8px;">🏢 B2B Tenant Management</h3>
            <table>
                <thead>
                    <tr>
                        <th>Organization Name</th>
                        <th>Subscription Plan</th>
                        <th>User Count</th>
                        <th>Monthly Fee</th>
                        <th>Usage Dues (PAYG)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($organizations as $org)
                        @php
                            $fee = match ($org->plan) {
                                'pro' => 49.00,
                                'enterprise' => 199.00,
                                default => 0.00,
                            };
                            $planClass = match ($org->plan) {
                                'enterprise' => 'background: rgba(154,95,48,0.15); color: var(--amber); border: 1px solid rgba(154,95,48,0.3);',
                                'pro' => 'background: rgba(46,204,113,0.15); color: #2ecc71; border: 1px solid rgba(46,204,113,0.3);',
                                default => 'background: #eef1ea; color: #34413c; border: 1px solid var(--line);'
                            };
                        @endphp
                        <tr>
                            <td><strong>{{ $org->name }}</strong></td>
                            <td>
                                <span class="pill" style="{{ $planClass }} padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                    {{ $org->plan }}
                                </span>
                            </td>
                            <td style="font-weight: 600;">{{ $org->users->count() }} users</td>
                            <td style="font-weight: 700; color: var(--green);">${{ number_format($fee, 2) }}/mo</td>
                            <td style="font-weight: 600; color: var(--muted);">${{ number_format($org->settings->usage_amount_due ?? 0, 2) }} due</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No registered organizations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- SECTION 2: CROSS-TENANT UPLOADS -->
        <div class="card">
            <h3 style="margin-bottom: 14px; font-size: 16px; border-bottom: 1px solid var(--line); padding-bottom: 8px;">📚 Cross-Tenant Uploaded Files</h3>
            <table>
                <thead>
                    <tr>
                        <th>File Title</th>
                        <th>Organization</th>
                        <th>File Size</th>
                        <th>Vector Chunks</th>
                        <th>Status</th>
                        <th>Uploader</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($uploads as $source)
                        @php
                            $sizeKB = ($source->metadata['size'] ?? 0) / 1024;
                            $sizeStr = $sizeKB > 1024 
                                ? number_format($sizeKB / 1024, 2) . ' MB' 
                                : number_format($sizeKB, 1) . ' KB';

                            $statusStyle = match($source->status) {
                                'indexed' => 'background: rgba(46, 204, 113, 0.15); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.3);',
                                'failed' => 'background: rgba(231, 76, 60, 0.15); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.3);',
                                'indexing', 'uploaded' => 'background: rgba(241, 196, 15, 0.15); color: #f1c40f; border: 1px solid rgba(241, 196, 15, 0.3);',
                                'inactive' => 'background: rgba(149, 165, 166, 0.15); color: #7f8c8d; border: 1px solid rgba(149, 165, 166, 0.3);',
                                default => 'background: #eef1ea; color: #34413c;'
                            };
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $source->title }}</strong>
                                <div style="font-size: 11px; color: var(--muted); font-family: monospace;">{{ $source->metadata['original_name'] ?? 'N/A' }}</div>
                            </td>
                            <td><span style="font-weight: 600; color: var(--amber);">{{ $source->organization->name }}</span></td>
                            <td><span style="font-family: monospace; font-size: 12px; background: #eef1ea; padding: 2px 6px; border-radius: 4px;">{{ $sizeStr }}</span></td>
                            <td style="font-weight: 600;">{{ $source->chunks()->count() }} chunks</td>
                            <td>
                                <span class="pill" style="{{ $statusStyle }} padding: 2px 8px; border-radius: 99px; font-size: 10px; font-weight: 700; text-transform: uppercase;">
                                    {{ $source->status }}
                                </span>
                            </td>
                            <td><span style="font-size: 13px; font-weight: 600;">{{ $source->uploader->name ?? 'System' }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No files have been uploaded to the platform yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
