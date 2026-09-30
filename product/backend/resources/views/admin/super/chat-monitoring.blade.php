@extends('admin.layout')

@section('title', 'Super Admin - Chat & Token Monitoring')

@section('content')
    <div style="margin-bottom: 16px;">
        <a class="button" style="width: auto; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); font-size: 13px; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 8px 14px; border-radius: 6px;" href="{{ route('admin.super.dashboard') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Dashboard
        </a>
    </div>

    <div class="header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
        <div>
            <div class="eyebrow" style="color: #6366f1;">AI Usage & Token Consumption</div>
            <h1>Global Chat & Token Monitoring</h1>
            <p class="help">Live monitoring of customer conversations from Cloud Firestore (<code>aprilo-infotech</code>), AI query demand, and Gemini token costs.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <form method="POST" action="{{ route('admin.super.monitoring.sync-firebase') }}" style="margin: 0; padding: 0; border: 0; box-shadow: none;">
                @csrf
                <button type="submit" class="button" style="width: auto; display: inline-flex; align-items: center; gap: 6px; background: #6366f1; color: #fff; font-size: 13px; padding: 8px 16px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
                    Sync Live from Firestore
                </button>
            </form>
            <a class="button" style="width: auto; background: transparent; color: var(--ink); border: 1px solid var(--line); box-shadow: none;" href="{{ route('admin.super.organizations') }}">
                All Organizations
            </a>
        </div>
    </div>

    @if (session('status'))
        <div style="margin-bottom: 20px; padding: 12px 16px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 8px; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('status') }}
        </div>
    @endif

    <!-- Firestore Live Sync Health Status Banner -->
    <div class="card" style="margin-bottom: 24px; padding: 16px 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 8px rgba(34, 197, 94, 0.6);"></span>
                <div>
                    <div style="font-weight: 700; font-size: 14px; color: var(--ink);">
                        Cloud Firestore Live Sync Active · Project: <code>aprilo-infotech</code>
                    </div>
                    <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">
                        Tracking collections: <code>conversations</code> ({{ $firestoreSummary['total_conversations'] ?? 0 }} records), <code>customers</code> ({{ $firebaseSync['total_customers'] ?? 0 }} visitors), <code>messages</code> ({{ $firebaseSync['total_messages'] ?? 0 }} items).
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <span class="pill" style="background: #dcfce7; color: #166534; font-size: 11.5px; font-weight: 600;">
                    ✓ Synced with Firebase
                </span>
                <span style="font-size: 11.5px; color: var(--muted);">
                    Last sync: {{ isset($firebaseSync['last_synced_at']) ? \Illuminate\Support\Carbon::parse($firebaseSync['last_synced_at'])->diffForHumans() : 'Just now' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Firestore Multi-Collection Global Live Inventory Cards -->
    <div style="margin-bottom: 24px;">
        <div style="margin-bottom: 12px;">
            <h2 style="font-size: 16px; margin: 0; font-weight: 600;">Firestore Global Collections Inventory (<code>aprilo-infotech</code>)</h2>
            <p class="help" style="margin: 2px 0 0;">Total records detected and monitored in real-time across Firestore collections.</p>
        </div>
        <div class="grid grid-6" style="gap: 12px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
            <div class="card" style="border-top: 3px solid #6366f1; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Conversations</div>
                <div style="font-size: 26px; font-weight: 800; color: #6366f1; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['conversations'] ?? ($firestoreSummary['total_conversations'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Conversations total</div>
            </div>

            <div class="card" style="border-top: 3px solid #0ea5e9; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Messages</div>
                <div style="font-size: 26px; font-weight: 800; color: #0ea5e9; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['messages'] ?? ($firestoreSummary['total_messages'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Messages total</div>
            </div>

            <div class="card" style="border-top: 3px solid #10b981; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Customers / Visitors</div>
                <div style="font-size: 26px; font-weight: 800; color: #10b981; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['customers'] ?? ($firestoreSummary['total_customers'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Visitors tracked</div>
            </div>

            <div class="card" style="border-top: 3px solid #f59e0b; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Leads</div>
                <div style="font-size: 26px; font-weight: 800; color: #f59e0b; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['leads'] ?? ($firestoreSummary['total_leads'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Customer leads</div>
            </div>

            <div class="card" style="border-top: 3px solid #8b5cf6; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Bookings</div>
                <div style="font-size: 26px; font-weight: 800; color: #8b5cf6; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['bookings'] ?? ($firestoreSummary['total_bookings'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Consultation bookings</div>
            </div>

            <div class="card" style="border-top: 3px solid #ec4899; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Mail / Inquiries</div>
                <div style="font-size: 26px; font-weight: 800; color: #ec4899; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['mail'] ?? ($firestoreSummary['total_mail'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Inbound mail contacts</div>
            </div>
        </div>
    </div>

    <!-- Top Metrics Overview -->
    <div class="grid grid-4" style="margin-bottom: 24px; gap: 16px;">
        <div class="card" style="border-left: 4px solid #6366f1; padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Total AI Queries</div>
            <div class="metric" style="font-size: 28px; margin: 6px 0 2px;">{{ number_format($firestoreSummary['total_ai_queries'] ?? $totalQueries) }}</div>
            <div style="font-size: 12px; color: var(--muted);">Auto AI Resolved: <strong>{{ number_format($firestoreSummary['total_ai_resolved'] ?? 0) }}</strong></div>
        </div>

        <div class="card" style="border-left: 4px solid #0ea5e9; padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Total Tokens Consumed</div>
            <div class="metric" style="font-size: 28px; margin: 6px 0 2px;">{{ number_format($totalTokens) }}</div>
            <div style="font-size: 12px; color: var(--muted);">Prompt: <strong>{{ number_format($totalPromptTokens) }}</strong> | Comp: <strong>{{ number_format($totalCompletionTokens) }}</strong></div>
        </div>

        <div class="card" style="border-left: 4px solid #10b981; padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">AI Resolved / Handoffs</div>
            <div class="metric" style="font-size: 28px; margin: 6px 0 2px;">{{ number_format($firestoreSummary['total_ai_resolved'] ?? 0) }}</div>
            <div style="font-size: 12px; color: var(--muted);">
                Human Requests: <strong>{{ ($firestoreSummary['total_human_pending'] ?? 0) + ($firestoreSummary['total_human_connected'] ?? 0) }}</strong>
                @if (($firestoreSummary['avg_wait_time_seconds'] ?? 0) > 0)
                    · Avg wait: {{ $firestoreSummary['avg_wait_time_seconds'] }}s
                @endif
            </div>
        </div>

        <div class="card" style="border-left: 4px solid #f59e0b; padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Estimated Cost / Active</div>
            <div class="metric" style="font-size: 28px; margin: 6px 0 2px;">${{ number_format($totalCost, 4) }}</div>
            <div style="font-size: 12px; color: var(--muted);">Active Orgs: <strong>{{ $activeOrgsCount }}</strong> of {{ $totalOrgsCount }}</div>
        </div>
    </div>

    <!-- Section 1: Organizations Breakdown & Widget Controls -->
    <section class="card" style="margin-bottom: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h2 style="font-size: 18px; margin: 0;">Organization Usage & Chatbot Controls</h2>
                <p class="help" style="margin: 3px 0 0;">View each organization's Firestore chat volume, token usage, and quickly enable or disable their live chatbot widget.</p>
            </div>
            <form method="GET" action="{{ route('admin.super.monitoring') }}" style="display: flex; gap: 8px; margin: 0; padding: 0; border: 0; box-shadow: none;">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search organization..." style="font-size: 13px; padding: 6px 12px; width: 220px; border-radius: 6px;">
                <button class="button" type="submit" style="width: auto; padding: 6px 14px; font-size: 13px;">Filter</button>
                @if ($search)
                    <a class="button" style="width: auto; padding: 6px 12px; font-size: 13px; background: transparent; color: var(--ink); border: 1px solid var(--line); box-shadow: none;" href="{{ route('admin.super.monitoring') }}">Reset</a>
                @endif
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Organization</th>
                        <th>Widget Status</th>
                        <th>Total Queries</th>
                        <th>Total Tokens</th>
                        <th>Today's Queries</th>
                        <th>Today's Tokens</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($organizations as $org)
                        @php
                            $settings = $org->settings;
                            $isEnabled = $settings ? ($settings->is_widget_enabled ?? true) : true;
                            $isPaused = $settings && $settings->assistant_status === 'paused';
                            $todayOrg = $todayUsages[$org->id] ?? null;
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--ink); font-size: 14px;">{{ $org->name }}</div>
                                <span style="font-size: 11px; color: var(--muted); font-family: monospace;">{{ $org->id }}</span>
                            </td>
                            <td>
                                @if (! $isEnabled)
                                    <span class="pill" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; font-size: 11.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #dc2626;"></span>
                                        Disabled (Offline)
                                    </span>
                                @elseif ($isPaused)
                                    <span class="pill" style="background: rgba(245, 158, 11, 0.12); color: #d97706; font-size: 11.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #d97706;"></span>
                                        Paused (Offline)
                                    </span>
                                @else
                                    <span class="pill" style="background: rgba(16, 185, 129, 0.12); color: #059669; font-size: 11.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #059669;"></span>
                                        Active (Online)
                                    </span>
                                @endif
                            </td>
                            <td style="font-weight: 600;">{{ number_format($org->total_queries ?? 0) }}</td>
                            <td>
                                <div style="font-weight: 600;">{{ number_format($org->total_tokens ?? 0) }}</div>
                            </td>
                            <td>{{ number_format($todayOrg->queries_count ?? 0) }}</td>
                            <td>{{ number_format($todayOrg->total_tokens ?? 0) }}</td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px; align-items: center; justify-content: flex-end;">
                                    <a class="button" style="width: auto; padding: 5px 10px; font-size: 12px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); box-shadow: none;" href="{{ route('admin.super.organizations.monitoring', $org) }}">
                                        Daily Stats
                                    </a>
                                    <a class="button" style="width: auto; padding: 5px 10px; font-size: 12px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); box-shadow: none;" href="{{ route('admin.super.organizations.widget', $org) }}">
                                        Settings
                                    </a>
                                    <form method="POST" action="{{ route('admin.super.organizations.toggle-widget', $org) }}" style="display: inline; margin: 0; padding: 0; border: 0; box-shadow: none;">
                                        @csrf
                                        @if ($isEnabled)
                                            <button class="button" type="submit" style="width: auto; padding: 5px 10px; font-size: 12px; background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; box-shadow: none;" title="Disable widget so it shows offline status">
                                                Disable
                                            </button>
                                        @else
                                            <button class="button" type="submit" style="width: auto; padding: 5px 10px; font-size: 12px; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; box-shadow: none;" title="Enable widget so it answers queries">
                                                Enable
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--muted); padding: 24px;">No organizations found matching search criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 18px;">
            {{ $organizations->links() }}
        </div>
    </section>

    <!-- Section 2: Platform-Wide Daily Usage History (Last 30 Days) -->
    <section class="card" style="margin-bottom: 28px;">
        <div style="margin-bottom: 16px;">
            <h2 style="font-size: 18px; margin: 0;">Platform Daily Usage History (Last 30 Days)</h2>
            <p class="help" style="margin: 3px 0 0;">Day-by-day aggregate volume of queries, prompt tokens, completion tokens, and active tenant organizations from Firestore.</p>
        </div>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Active Orgs</th>
                        <th>Total Queries</th>
                        <th>Prompt (Input) Tokens</th>
                        <th>Completion (Output) Tokens</th>
                        <th>Total Tokens</th>
                        <th>Est. Cost ($)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dailyUsages as $day)
                        <tr>
                            <td style="font-weight: 600; font-family: monospace;">{{ \Illuminate\Support\Carbon::parse($day->usage_date)->format('Y-m-d (D)') }}</td>
                            <td><span class="pill" style="background: #eef2ff; color: #4338ca; font-size: 11.5px;">{{ $day->active_orgs }} orgs</span></td>
                            <td style="font-weight: 600;">{{ number_format($day->total_queries) }}</td>
                            <td>{{ number_format($day->total_prompt_tokens) }}</td>
                            <td>{{ number_format($day->total_completion_tokens) }}</td>
                            <td style="font-weight: 700; color: #2563eb;">{{ number_format($day->total_tokens) }}</td>
                            <td style="font-family: monospace; font-weight: 600;">${{ number_format((float) $day->total_cost, 4) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--muted); padding: 24px;">No daily usage recorded yet. Click "Sync Live from Firestore" to import.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Section 3: Recent Live Conversations from Firestore -->
    @if (! empty($firestoreSummary['conversations']))
        <section class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h2 style="font-size: 18px; margin: 0;">Recent Firestore Conversations Log</h2>
                    <p class="help" style="margin: 3px 0 0;">Live documents pulled directly from Firestore <code>conversations</code> collection.</p>
                </div>
                <span class="pill" style="background: #e0e7ff; color: #3730a3; font-size: 12px; font-weight: 600;">
                    Showing {{ count(array_slice($firestoreSummary['conversations'], 0, 15)) }} of {{ count($firestoreSummary['conversations']) }} conversations
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Conversation ID</th>
                            <th>Mode / Status</th>
                            <th>Page / Source</th>
                            <th>AI Queries</th>
                            <th>AI Resolved</th>
                            <th>Last Message Preview</th>
                            <th>Started At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (array_slice($firestoreSummary['conversations'], 0, 15) as $conv)
                            <tr>
                                <td>
                                    <div style="font-family: monospace; font-weight: 600; font-size: 12.5px; color: var(--ink);">
                                        {{ $conv['id'] ?? 'N/A' }}
                                    </div>
                                    <span style="font-size: 11px; color: var(--muted);">Org: {{ substr($conv['organization_id'] ?? 'unknown', 0, 8) }}...</span>
                                </td>
                                <td>
                                    @php
                                        $mode = $conv['mode'] ?? 'ai';
                                        $st = $conv['status'] ?? 'open';
                                    @endphp
                                    <span class="pill" style="{{ $mode === 'human' ? 'background: #eff6ff; color: #1d4ed8;' : ($mode === 'human_pending' ? 'background: #fef3c7; color: #b45309;' : 'background: #f0fdf4; color: #15803d;') }} font-size: 11px; font-weight: 600; margin-right: 4px;">
                                        {{ strtoupper($mode) }}
                                    </span>
                                    <span class="pill" style="background: #f1f5f9; color: #475569; font-size: 11px;">
                                        {{ $st }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 12.5px; font-weight: 500; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ is_array($conv['page_title'] ?? '') ? implode(' ', $conv['page_title']) : ($conv['page_title'] ?? '') }}">
                                        {{ is_array($conv['page_title'] ?? null) ? implode(' ', $conv['page_title']) : ($conv['page_title'] ?? 'Storefront') }}
                                    </div>
                                    @php $pageUrl = is_array($conv['page_url'] ?? null) ? implode('', $conv['page_url']) : ($conv['page_url'] ?? ''); @endphp
                                    <a href="{{ $pageUrl ?: '#' }}" target="_blank" style="font-size: 11px; color: #6366f1; text-decoration: none; max-width: 200px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $pageUrl }}
                                    </a>
                                </td>
                                <td style="font-weight: 600;">{{ $conv['ai_query_count'] ?? 0 }}</td>
                                <td>
                                    @if (! empty($conv['ai_resolved']))
                                        <span style="color: #16a34a; font-weight: 600;">✓ Yes</span>
                                    @else
                                        <span style="color: var(--muted);">No</span>
                                    @endif
                                </td>
                                <td style="max-width: 280px; font-size: 12px; color: #334155;">
                                    <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ is_array($conv['last_message_preview'] ?? '') ? implode(' ', $conv['last_message_preview']) : ($conv['last_message_preview'] ?? '') }}">
                                        {{ is_array($conv['last_message_preview'] ?? null) ? implode(' ', $conv['last_message_preview']) : ($conv['last_message_preview'] ?? 'No preview available') }}
                                    </div>
                                    @if (! empty($conv['assigned_agent_name']))
                                        <span style="font-size: 10.5px; color: var(--muted);">Agent: {{ is_array($conv['assigned_agent_name']) ? implode(', ', $conv['assigned_agent_name']) : $conv['assigned_agent_name'] }}</span>
                                    @endif
                                </td>
                                <td style="font-size: 12px; font-family: monospace; white-space: nowrap;">
                                    {{ isset($conv['session_started_at']) ? \Illuminate\Support\Carbon::parse($conv['session_started_at'])->format('M d, H:i') : (isset($conv['created_at']) ? \Illuminate\Support\Carbon::parse($conv['created_at'])->format('M d, H:i') : 'N/A') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
