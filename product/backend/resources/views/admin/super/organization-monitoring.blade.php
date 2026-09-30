@extends('admin.layout')

@section('title', 'Chat & Token Usage - ' . $organization->name)

@section('content')
    <div style="margin-bottom: 16px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <a class="button" style="width: auto; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); font-size: 13px; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 8px 14px; border-radius: 6px;" href="{{ route('admin.super.monitoring') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Global Monitoring
        </a>
        <a class="button" style="width: auto; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); font-size: 13px; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 8px 14px; border-radius: 6px;" href="{{ route('admin.super.organizations.show', $organization) }}">
            Organization Details
        </a>
    </div>

    @php
        $isEnabled = $settings ? ($settings->is_widget_enabled ?? true) : true;
        $isPaused = $settings && $settings->assistant_status === 'paused';
    @endphp

    <div class="header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Organization Chat Analytics</div>
            <h1>Chat & Token Usage · {{ $organization->name }}</h1>
            <p class="help">Daily query traffic, prompt/completion token consumption, and live Firestore conversation tracking.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <form method="POST" action="{{ route('admin.super.monitoring.sync-firebase') }}" style="margin: 0; padding: 0; border: 0; box-shadow: none;">
                @csrf
                <button type="submit" class="button" style="width: auto; display: inline-flex; align-items: center; gap: 6px; background: #6366f1; color: #fff; font-size: 13px; padding: 8px 16px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
                    Sync Live from Firestore
                </button>
            </form>
            <a class="button" style="width: auto; background: transparent; color: var(--ink); border: 1px solid var(--line); box-shadow: none;" href="{{ route('admin.super.organizations.widget', $organization) }}">
                Widget Settings
            </a>
            <form method="POST" action="{{ route('admin.super.organizations.toggle-widget', $organization) }}" style="display: inline; margin: 0; padding: 0; border: 0; box-shadow: none;">
                @csrf
                @if ($isEnabled)
                    <button class="button" type="submit" style="width: auto; background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3;" title="Disable widget so it shows offline">
                        Turn Widget OFF
                    </button>
                @else
                    <button class="button" type="submit" style="width: auto; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;" title="Enable widget so it responds to queries">
                        Turn Widget ON
                    </button>
                @endif
            </form>
        </div>
    </div>

    @if (session('status'))
        <div style="margin-bottom: 20px; padding: 12px 16px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 8px; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('status') }}
        </div>
    @endif

    <!-- Widget Status Alert Banner -->
    <div class="card" style="margin-bottom: 20px; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; background: {{ $isEnabled && ! $isPaused ? '#f0fdf4; border: 1px solid #bbf7d0;' : '#fef2f2; border: 1px solid #fecaca;' }}; border-radius: 10px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="width: 12px; height: 12px; border-radius: 50%; background: {{ $isEnabled && ! $isPaused ? '#22c55e;' : '#ef4444;' }}"></span>
            <div>
                <strong style="color: {{ $isEnabled && ! $isPaused ? '#15803d;' : '#b91c1c;' }}">
                    Chatbot Widget Status: {{ $isEnabled && ! $isPaused ? 'ONLINE & ACTIVE' : ($isPaused ? 'PAUSED' : 'DISABLED / OFFLINE') }}
                </strong>
                <div style="font-size: 12.5px; color: var(--muted); margin-top: 2px;">
                    @if (! $isEnabled)
                        The chatbot widget is currently disabled by superadmin/owner. On customer websites, it will either be hidden or show an offline/unavailable state, and will not execute AI queries.
                    @elseif ($isPaused)
                        The assistant status is set to Paused in widget settings. It will display a temporary offline message.
                    @else
                        The chatbot widget is active, responsive, and available for website visitors.
                    @endif
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.super.organizations.toggle-widget', $organization) }}" style="margin: 0; padding: 0; border: 0; box-shadow: none;">
            @csrf
            <button class="button" type="submit" style="width: auto; padding: 7px 16px; font-size: 12.5px; font-weight: 600;">
                {{ $isEnabled ? 'Disable Chatbot' : 'Enable Chatbot' }}
            </button>
        </form>
    </div>

    <!-- Firestore Multi-Collection Exact Inventory Cards -->
    <div style="margin-bottom: 24px;">
        <div style="margin-bottom: 12px;">
            <h2 style="font-size: 16px; margin: 0; font-weight: 600;">Firestore Collections Count ({{ $organization->name }})</h2>
            <p class="help" style="margin: 2px 0 0;">Live documents stored in Cloud Firestore project <code>aprilo-infotech</code> for organization <code>{{ $organization->id }}</code>.</p>
        </div>
        <div class="grid grid-6" style="gap: 12px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
            <div class="card" style="border-top: 3px solid #6366f1; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Conversations</div>
                <div style="font-size: 26px; font-weight: 800; color: #6366f1; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['conversations'] ?? ($firestoreSummary['total_conversations'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Chat sessions</div>
            </div>

            <div class="card" style="border-top: 3px solid #0ea5e9; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Messages</div>
                <div style="font-size: 26px; font-weight: 800; color: #0ea5e9; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['messages'] ?? ($firestoreSummary['total_messages'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Inbound & replies</div>
            </div>

            <div class="card" style="border-top: 3px solid #10b981; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Customers / Visitors</div>
                <div style="font-size: 26px; font-weight: 800; color: #10b981; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['customers'] ?? ($firestoreSummary['total_customers'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Profiles tracked</div>
            </div>

            <div class="card" style="border-top: 3px solid #f59e0b; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Leads</div>
                <div style="font-size: 26px; font-weight: 800; color: #f59e0b; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['leads'] ?? ($firestoreSummary['total_leads'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Sales inquiries</div>
            </div>

            <div class="card" style="border-top: 3px solid #8b5cf6; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Bookings</div>
                <div style="font-size: 26px; font-weight: 800; color: #8b5cf6; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['bookings'] ?? ($firestoreSummary['total_bookings'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Appointments</div>
            </div>

            <div class="card" style="border-top: 3px solid #ec4899; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Mail / Inquiries</div>
                <div style="font-size: 26px; font-weight: 800; color: #ec4899; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['mail'] ?? ($firestoreSummary['total_mail'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Email records</div>
            </div>
        </div>
    </div>

    <!-- Organization Metrics Cards -->
    <div class="grid grid-4" style="margin-bottom: 24px; gap: 16px;">
        <div class="card" style="border-left: 4px solid #6366f1; padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">AI Queries Tracked</div>
            <div class="metric" style="font-size: 28px; margin: 6px 0 2px;">{{ number_format($firestoreSummary['total_ai_queries'] ?? $totalQueries) }}</div>
            <div style="font-size: 12px; color: var(--muted);">AI Resolved: <strong>{{ number_format($firestoreSummary['total_ai_resolved'] ?? 0) }}</strong></div>
        </div>

        <div class="card" style="border-left: 4px solid #0ea5e9; padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Total AI Tokens</div>
            <div class="metric" style="font-size: 28px; margin: 6px 0 2px;">{{ number_format($totalTokens) }}</div>
            <div style="font-size: 12px; color: var(--muted);">Prompt: <strong>{{ number_format($totalPromptTokens) }}</strong> | Comp: <strong>{{ number_format($totalCompletionTokens) }}</strong></div>
        </div>

        <div class="card" style="border-left: 4px solid #10b981; padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">AI Resolved / Escalated</div>
            <div class="metric" style="font-size: 28px; margin: 6px 0 2px;">{{ number_format($firestoreSummary['total_ai_resolved'] ?? 0) }}</div>
            <div style="font-size: 12px; color: var(--muted);">
                Human Requests: <strong>{{ ($firestoreSummary['total_human_pending'] ?? 0) + ($firestoreSummary['total_human_connected'] ?? 0) }}</strong>
                @if (($firestoreSummary['avg_wait_time_seconds'] ?? 0) > 0)
                    · Avg wait: {{ $firestoreSummary['avg_wait_time_seconds'] }}s
                @endif
            </div>
        </div>

        <div class="card" style="border-left: 4px solid #f59e0b; padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Estimated Cost</div>
            <div class="metric" style="font-size: 28px; margin: 6px 0 2px;">${{ number_format($totalCost, 4) }}</div>
            <div style="font-size: 12px; color: var(--muted);">Today: <strong>${{ number_format($todayCost, 4) }}</strong></div>
        </div>
    </div>

    <!-- Daily Usage Breakdown Table -->
    <section class="card" style="margin-bottom: 24px;">
        <div style="margin-bottom: 16px;">
            <h2 style="font-size: 18px; margin: 0;">Daily Usage History</h2>
            <p class="help" style="margin: 3px 0 0;">Day-by-day record of queries answered and Gemini tokens consumed by {{ $organization->name }}.</p>
        </div>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Queries Answered</th>
                        <th>Prompt (Input) Tokens</th>
                        <th>Completion (Output) Tokens</th>
                        <th>Total Tokens</th>
                        <th>Estimated Cost ($)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dailyUsages as $day)
                        <tr>
                            <td style="font-weight: 600; font-family: monospace;">{{ $day->usage_date ? $day->usage_date->format('Y-m-d (D)') : 'N/A' }}</td>
                            <td style="font-weight: 600;">{{ number_format($day->queries_count) }}</td>
                            <td>{{ number_format($day->prompt_tokens) }}</td>
                            <td>{{ number_format($day->completion_tokens) }}</td>
                            <td style="font-weight: 700; color: #2563eb;">{{ number_format($day->total_tokens) }}</td>
                            <td style="font-family: monospace; font-weight: 600;">${{ number_format((float) $day->cost_estimate, 4) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--muted); padding: 24px;">No daily chat usage recorded for this organization yet. Click "Sync Live from Firestore" above.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 18px;">
            {{ $dailyUsages->links() }}
        </div>
    </section>

    <!-- Recent Live Firestore Conversations for this Organization -->
    @if (! empty($firestoreSummary['conversations']))
        <section class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h2 style="font-size: 18px; margin: 0;">Firestore Conversation Documents ({{ count($firestoreSummary['conversations']) }})</h2>
                    <p class="help" style="margin: 3px 0 0;">Visitor conversations originating from this organization's widget.</p>
                </div>
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
                        @foreach ($firestoreSummary['conversations'] as $conv)
                            <tr>
                                <td>
                                    <div style="font-family: monospace; font-weight: 600; font-size: 12.5px; color: var(--ink);">
                                        {{ $conv['id'] ?? 'N/A' }}
                                    </div>
                                    <span style="font-size: 11px; color: var(--muted);">Customer: {{ substr($conv['customer_id'] ?? 'unknown', 0, 14) }}...</span>
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
                                    <div style="font-size: 12.5px; font-weight: 500; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $conv['page_title'] ?? '' }}">
                                        {{ $conv['page_title'] ?? 'Storefront' }}
                                    </div>
                                    <a href="{{ $conv['page_url'] ?? '#' }}" target="_blank" style="font-size: 11px; color: #6366f1; text-decoration: none; max-width: 200px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $conv['page_url'] ?? '' }}
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
                                    <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $conv['last_message_preview'] ?? '' }}">
                                        {{ $conv['last_message_preview'] ?? 'No preview available' }}
                                    </div>
                                    @if (! empty($conv['assigned_agent_name']))
                                        <span style="font-size: 10.5px; color: var(--muted);">Agent: {{ $conv['assigned_agent_name'] }}</span>
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
