@extends('admin.layout')

@section('title', 'Chat & Token Usage - ' . $organization->name)

@section('content')
    <div style="margin-bottom: 16px;">
        <a class="button" style="width: auto; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); font-size: 13px; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 8px 14px; border-radius: 6px;" href="{{ route('admin.dashboard') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Dashboard
        </a>
    </div>

    @php
        $isEnabled = $settings ? ($settings->is_widget_enabled ?? true) : true;
        $isPaused = $settings && $settings->assistant_status === 'paused';
    @endphp

    <div class="header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
        <div>
            <div class="eyebrow" style="color: #6366f1;">AI Usage & Quota Monitoring</div>
            <h1>Chat & Token Usage</h1>
            <p class="help">Monitor your daily chat interactions, AI token consumption, and manage your live chatbot status.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <form method="POST" action="{{ route('admin.monitoring.sync-firebase') }}" style="margin: 0; padding: 0; border: 0; box-shadow: none;">
                @csrf
                <button type="submit" class="button" style="width: auto; display: inline-flex; align-items: center; gap: 6px; background: #6366f1; color: #fff; font-size: 13px; padding: 8px 16px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
                    Sync Live from Firestore
                </button>
            </form>
            <a class="button" style="width: auto; background: transparent; color: var(--ink); border: 1px solid var(--line); box-shadow: none;" href="{{ route('admin.settings.agent') }}">
                Agent Settings
            </a>
            <form method="POST" action="{{ route('admin.settings.toggle-widget') }}" style="display: inline; margin: 0; padding: 0; border: 0; box-shadow: none;">
                @csrf
                @if ($isEnabled)
                    <button class="button" type="submit" style="width: auto; background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3;" title="Disable widget so it shows offline">
                        Disable Chatbot
                    </button>
                @else
                    <button class="button" type="submit" style="width: auto; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;" title="Enable chatbot so it answers inquiries">
                        Enable Chatbot
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

    <!-- Widget Live Status & Master Switch Banner -->
    <div class="card" style="margin-bottom: 22px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; background: {{ $isEnabled && ! $isPaused ? '#f0fdf4; border: 1px solid #bbf7d0;' : '#fef2f2; border: 1px solid #fecaca;' }}; border-radius: 10px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <span style="width: 12px; height: 12px; border-radius: 50%; background: {{ $isEnabled && ! $isPaused ? '#22c55e;' : '#ef4444;' }}"></span>
            <div>
                <strong style="font-size: 15px; color: {{ $isEnabled && ! $isPaused ? '#15803d;' : '#b91c1c;' }}">
                    Chatbot Status: {{ $isEnabled && ! $isPaused ? 'ONLINE & ANSWERING' : ($isPaused ? 'PAUSED' : 'DISABLED / OFFLINE') }}
                </strong>
                <div style="font-size: 12.5px; color: var(--muted); margin-top: 3px;">
                    @if (! $isEnabled)
                        The chatbot widget is currently disabled. Website visitors will see an offline/unavailable state or the launcher will be hidden, and no automated AI replies will be processed.
                    @elseif ($isPaused)
                        The assistant is currently paused. It will respond with an offline maintenance message.
                    @else
                        The chatbot is live on your website and answering customer questions according to your knowledge base.
                    @endif
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.settings.toggle-widget') }}" style="margin: 0; padding: 0; border: 0; box-shadow: none;">
            @csrf
            <button class="button" type="submit" style="width: auto; padding: 8px 18px; font-size: 13px; font-weight: 600;">
                {{ $isEnabled ? 'Disable Chatbot' : 'Enable Chatbot' }}
            </button>
        </form>
    </div>

    <!-- Multi-Collection Firestore Live Sync Status Banner -->
    <div class="card" style="margin-bottom: 22px; padding: 18px 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: flex-start; gap: 14px;">
                <span style="width: 12px; height: 12px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 10px rgba(34, 197, 94, 0.6); margin-top: 4px;"></span>
                <div>
                    <div style="font-weight: 700; font-size: 15px; color: var(--ink);">
                        Cloud Firestore & Realtime Database Connected · Project: <code>aprilo-infotech</code>
                    </div>
                    <div style="font-size: 13px; color: var(--muted); margin-top: 3px;">
                        Organization ID: <code style="font-weight: 600; color: #4338ca;">{{ $organization->id }}</code> ({{ $organization->name }})
                        · Realtime Database Agent Sync: <span class="pill" style="background: #dcfce7; color: #166534; font-size: 11px; font-weight: 600;">ACTIVE (tenants/{{ $organization->id }})</span>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <span class="pill" style="background: #eff6ff; color: #1d4ed8; font-size: 12px; font-weight: 600;">
                    ✓ Synchronized with Cloud Firestore
                </span>
            </div>
        </div>
    </div>

    <!-- Firestore Multi-Collection Exact Inventory Cards -->
    <div style="margin-bottom: 24px;">
        <div style="margin-bottom: 12px;">
            <h2 style="font-size: 16px; margin: 0; font-weight: 600;">Firestore Collections Count ({{ $organization->name }})</h2>
            <p class="help" style="margin: 2px 0 0;">Exact document counts queried from Firestore collections belonging to this organization.</p>
        </div>
        <div class="grid grid-6" style="gap: 12px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
            <div class="card" style="border-top: 3px solid #6366f1; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Conversations</div>
                <div style="font-size: 26px; font-weight: 800; color: #6366f1; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['conversations'] ?? ($firestoreSummary['total_conversations'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Live chat threads</div>
            </div>

            <div class="card" style="border-top: 3px solid #0ea5e9; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Messages</div>
                <div style="font-size: 26px; font-weight: 800; color: #0ea5e9; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['messages'] ?? ($firestoreSummary['total_messages'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Inbound & bot replies</div>
            </div>

            <div class="card" style="border-top: 3px solid #10b981; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Customers / Visitors</div>
                <div style="font-size: 26px; font-weight: 800; color: #10b981; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['customers'] ?? ($firestoreSummary['total_customers'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Identified visitor profiles</div>
            </div>

            <div class="card" style="border-top: 3px solid #f59e0b; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Leads</div>
                <div style="font-size: 26px; font-weight: 800; color: #f59e0b; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['leads'] ?? ($firestoreSummary['total_leads'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Captured sales leads</div>
            </div>

            <div class="card" style="border-top: 3px solid #8b5cf6; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Bookings</div>
                <div style="font-size: 26px; font-weight: 800; color: #8b5cf6; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['bookings'] ?? ($firestoreSummary['total_bookings'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Appointments booked</div>
            </div>

            <div class="card" style="border-top: 3px solid #ec4899; padding: 14px 16px; text-align: center;">
                <div style="font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Mail / Inquiries</div>
                <div style="font-size: 26px; font-weight: 800; color: #ec4899; margin: 4px 0 2px;">{{ number_format($firestoreSummary['collections_counts']['mail'] ?? ($firestoreSummary['total_mail'] ?? 0)) }}</div>
                <div style="font-size: 11px; color: var(--muted);">Email & form contacts</div>
            </div>
        </div>
    </div>

    <!-- Usage Metric Cards -->
    <div class="grid grid-4" style="margin-bottom: 24px; gap: 16px;">
        <div class="card" style="border-left: 4px solid #6366f1; padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">AI Queries Tracked</div>
            <div class="metric" style="font-size: 28px; margin: 6px 0 2px;">{{ number_format($firestoreSummary['total_ai_queries'] ?? $totalQueries) }}</div>
            <div style="font-size: 12px; color: var(--muted);">Auto AI Answers: <strong>{{ number_format($firestoreSummary['total_ai_resolved'] ?? 0) }}</strong></div>
        </div>

        <div class="card" style="border-left: 4px solid #0ea5e9; padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Total AI Tokens</div>
            <div class="metric" style="font-size: 28px; margin: 6px 0 2px;">{{ number_format($totalTokens) }}</div>
            <div style="font-size: 12px; color: var(--muted);">Prompt: <strong>{{ number_format($totalPromptTokens) }}</strong> | Comp: <strong>{{ number_format($totalCompletionTokens) }}</strong></div>
        </div>

        <div class="card" style="border-left: 4px solid #10b981; padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Human Agent Handoffs</div>
            <div class="metric" style="font-size: 28px; margin: 6px 0 2px;">{{ number_format(($firestoreSummary['total_human_pending'] ?? 0) + ($firestoreSummary['total_human_connected'] ?? 0)) }}</div>
            <div style="font-size: 12px; color: var(--muted);">
                Pending: <strong>{{ $firestoreSummary['total_human_pending'] ?? 0 }}</strong> | Connected: <strong>{{ $firestoreSummary['total_human_connected'] ?? 0 }}</strong>
                @if (($firestoreSummary['avg_wait_time_seconds'] ?? 0) > 0)
                    · Avg wait: {{ $firestoreSummary['avg_wait_time_seconds'] }}s
                @endif
            </div>
        </div>

        <div class="card" style="border-left: 4px solid #f59e0b; padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px;">Estimated Cost</div>
            <div class="metric" style="font-size: 28px; margin: 6px 0 2px;">${{ number_format($totalCost, 4) }}</div>
            <div style="font-size: 12px; color: var(--muted);">Today Cost: <strong>${{ number_format($todayCost, 4) }}</strong></div>
        </div>
    </div>

    <!-- Daily Usage Breakdown -->
    <section class="card" style="margin-bottom: 24px;">
        <div style="margin-bottom: 16px;">
            <h2 style="font-size: 18px; margin: 0;">Daily Chat & Token History</h2>
            <p class="help" style="margin: 3px 0 0;">Review your day-by-day conversation volume and AI token consumption from Cloud Firestore.</p>
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
                        <th>Est. Cost ($)</th>
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
                            <td colspan="6" style="text-align: center; color: var(--muted); padding: 24px;">No daily chat activity recorded yet. Inbound visitor inquiries will appear here. Click "Sync Live from Firestore" above.</td>
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
                    <h2 style="font-size: 18px; margin: 0;">Recent Visitor Conversations ({{ count($firestoreSummary['conversations']) }})</h2>
                    <p class="help" style="margin: 3px 0 0;">Live documents pulled directly from your organization's Firestore data.</p>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Conversation ID</th>
                            <th>Channel</th>
                            <th>Mode / Status</th>
                            <th>Page / Source</th>
                            <th>AI Queries</th>
                            <th>AI Resolved</th>
                            <th>Assigned Agent</th>
                            <th>Wait Time</th>
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
                                        $channel = strtolower($conv['channel'] ?? 'website');
                                    @endphp
                                    <span class="pill" style="{{ $channel === 'whatsapp' ? 'background: #dcfce7; color: #15803d;' : ($channel === 'email' ? 'background: #fef3c7; color: #b45309;' : 'background: #e0f2fe; color: #0369a1;') }} font-size: 11px; font-weight: 600;">
                                        {{ ucfirst($channel) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $mode = $conv['mode'] ?? 'ai';
                                        $st = $conv['status'] ?? 'open';
                                    @endphp
                                    <span class="pill" style="{{ $mode === 'human' ? 'background: #eff6ff; color: #1d4ed8;' : ($mode === 'human_pending' ? 'background: #fef3c7; color: #b45309;' : 'background: #f0fdf4; color: #15803d;') }} font-size: 11px; font-weight: 600; margin-right: 4px;">
                                        {{ strtoupper(str_replace('_', ' ', $mode)) }}
                                    </span>
                                    <span class="pill" style="background: #f1f5f9; color: #475569; font-size: 11px;">
                                        {{ $st }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 12.5px; font-weight: 500; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $conv['page_title'] ?? '' }}">
                                        {{ $conv['page_title'] ?: 'Storefront' }}
                                    </div>
                                    @if (! empty($conv['page_url']))
                                        <a href="{{ $conv['page_url'] }}" target="_blank" style="font-size: 11px; color: #6366f1; text-decoration: none; max-width: 200px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $conv['page_url'] }}
                                        </a>
                                    @endif
                                </td>
                                <td style="font-weight: 600; text-align: center;">{{ $conv['ai_query_count'] ?? 0 }}</td>
                                <td style="text-align: center;">
                                    @if (! empty($conv['ai_resolved']))
                                        <span style="color: #16a34a; font-weight: 600;">✓ Yes</span>
                                    @else
                                        <span style="color: var(--muted);">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if (! empty($conv['assigned_agent_name']) || ! empty($conv['assigned_agent_id']))
                                        <span class="pill" style="background: #f3e8ff; color: #7e22ce; font-size: 11.5px; font-weight: 600;">
                                            {{ $conv['assigned_agent_name'] ?? $conv['assigned_agent_id'] }}
                                        </span>
                                    @else
                                        <span style="font-size: 11.5px; color: var(--muted); font-style: italic;">Unassigned (AI)</span>
                                    @endif
                                </td>
                                <td>
                                    @if (isset($conv['wait_time_seconds']) && is_numeric($conv['wait_time_seconds']) && (int)$conv['wait_time_seconds'] > 0)
                                        <span style="font-family: monospace; font-size: 12px; color: #b45309; font-weight: 600;">{{ $conv['wait_time_seconds'] }}s</span>
                                    @else
                                        <span style="color: var(--muted); font-size: 11.5px;">-</span>
                                    @endif
                                </td>
                                <td style="max-width: 260px; font-size: 12px; color: #334155;">
                                    <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $conv['last_message_preview'] ?? '' }}">
                                        {{ $conv['last_message_preview'] ?: 'No preview available' }}
                                    </div>
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
