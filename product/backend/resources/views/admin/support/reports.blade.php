@extends('admin.layout')

@section('title', 'Aprilo Support - Ticket Reports & Monitor')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow font-bold" style="color: var(--green); text-transform: uppercase; letter-spacing: 0.1em; font-size: 11px;">
                Aprilo Support Product · Analytics & Operations
            </div>
            <h1 style="display: flex; align-items: center; gap: 12px; margin-top: 4px;">
                Support Ticket Reports & Monitor
                <span class="pill" style="font-size: 11px; background: rgba(34, 197, 94, 0.1); color: var(--green); border: 1px solid rgba(34, 197, 94, 0.2); text-transform: uppercase; padding: 2px 8px; border-radius: 9999px;">
                    Live Monitor
                </span>
            </h1>
            <p class="help" style="color: var(--muted); margin-top: 4px; font-size: 14px;">
                Real-time operational dashboard for ticket intake volumes, resolution velocity, SLA targets, and team workload.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('admin.support.reports', array_merge(request()->query(), ['export' => 'csv'])) }}" class="button" style="background: #fff; color: var(--ink); border: 1px solid var(--line); box-shadow: none; display: inline-flex; align-items: center; gap: 6px;">
                <span>📥</span> Export CSV Report
            </a>
            <a href="{{ route('admin.support.settings', $organization ? ['organization_id' => $organization->id] : []) }}" class="button" style="display: inline-flex; align-items: center; gap: 6px;">
                <span>⚙️</span> Support Settings
            </a>
        </div>
    </div>

    <!-- Organization Selector Bar -->
    <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 18px 24px; margin-top: 20px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(34, 197, 94, 0.1); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                🏢
            </div>
            <div>
                <div style="font-size: 14px; font-weight: 700; color: var(--ink);">Organization Scope</div>
                <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">
                    @if ($isSuperAdmin)
                        Super Administrator: Filter reports by specific organization or view platform-wide totals.
                    @else
                        Viewing support metrics for <strong>{{ $organization->name ?? 'your organization' }}</strong>.
                    @endif
                </div>
            </div>
        </div>

        <div style="min-width: 320px; flex: 1; max-width: 460px;">
            <form method="GET" action="{{ route('admin.support.reports') }}" id="orgFilterForm" style="margin: 0; padding: 0; border: 0; box-shadow: none; display: flex; gap: 8px;">
                <input type="hidden" name="range" value="{{ $range }}">
                @if ($statusFilter)
                    <input type="hidden" name="status_filter" value="{{ $statusFilter }}">
                @endif
                <select name="organization_id" id="organization_id" onchange="this.form.submit()" style="width: 100%; padding: 10px 14px; border: 1px solid var(--line); border-radius: 8px; font-size: 13.5px; font-weight: 600; color: var(--ink); background: #f8fafc; cursor: pointer; outline: none;">
                    @if ($isSuperAdmin)
                        <option value="" {{ ! $organization ? 'selected' : '' }}>-- All Organizations (Global Monitor) --</option>
                    @else
                        <option value="">-- Select Organization --</option>
                    @endif
                    @foreach ($organizations as $orgOption)
                        <option value="{{ $orgOption->id }}" {{ ($organization && $organization->id === $orgOption->id) ? 'selected' : '' }}>
                            {{ $orgOption->name }}
                        </option>
                    @endforeach
                </select>
                <noscript>
                    <button type="submit" class="button" style="width: auto; padding: 9px 14px; font-size: 12px;">Go</button>
                </noscript>
            </form>
        </div>
    </div>

    <!-- Time Range Filter & Scope Banner -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; gap: 16px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span class="pill" style="font-size: 12px; background: rgba(34, 197, 94, 0.12); color: #16a34a; font-weight: 700; border: 1px solid rgba(34, 197, 94, 0.2);">
                ✓ Scope: {{ $organization ? $organization->name : 'All Organizations (Global Overview)' }}
            </span>
        </div>

        <!-- Range Pills -->
        <div style="display: flex; gap: 6px; background: #fff; padding: 4px; border: 1px solid var(--line); border-radius: 8px;">
            @php
                $ranges = [
                    'today' => 'Today',
                    '7d' => 'Last 7 Days',
                    '30d' => 'Last 30 Days',
                    '90d' => 'Last 90 Days',
                    'all' => 'All Time',
                ];
            @endphp
            @foreach ($ranges as $rKey => $rLabel)
                <a href="{{ route('admin.support.reports', array_merge(request()->query(), ['range' => $rKey])) }}" 
                   style="padding: 6px 12px; font-size: 12px; font-weight: 600; border-radius: 6px; text-decoration: none; transition: all 0.2s; {{ $range === $rKey ? 'background: var(--green); color: #fff;' : 'color: var(--muted);' }}">
                    {{ $rLabel }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Executive KPI Metrics Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-top: 20px;">
        
        <!-- Card 1: Created Tickets -->
        <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #0284c7; letter-spacing: 0.05em;">Tickets Created</div>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #f0f9ff; display: flex; align-items: center; justify-content: center; font-size: 16px;">🎟️</div>
            </div>
            <div style="font-size: 32px; font-weight: 800; color: var(--ink); margin: 10px 0 4px; font-family: 'Outfit', sans-serif;">
                {{ number_format($createdInPeriod) }}
            </div>
            <div style="font-size: 12px; color: var(--muted);">
                In period ({{ strtoupper($range) }}) · <strong>{{ number_format($totalTickets) }}</strong> all-time
            </div>
        </div>

        <!-- Card 2: Resolved Tickets & Rate -->
        <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #16a34a; letter-spacing: 0.05em;">Resolved Tickets</div>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #ecfdf5; display: flex; align-items: center; justify-content: center; font-size: 16px;">✅</div>
            </div>
            <div style="display: flex; align-items: baseline; gap: 8px; margin: 10px 0 4px;">
                <div style="font-size: 32px; font-weight: 800; color: var(--ink); font-family: 'Outfit', sans-serif;">
                    {{ number_format($resolvedInPeriod) }}
                </div>
                <span class="pill" style="background: #ecfdf5; color: #16a34a; font-weight: 700; font-size: 11px;">
                    {{ $resolutionRate }}% Rate
                </span>
            </div>
            <div style="font-size: 12px; color: var(--muted);">
                <strong>{{ number_format($totalResolved) }}</strong> total tickets closed & resolved
            </div>
        </div>

        <!-- Card 3: Active Open Queue -->
        <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #ea580c; letter-spacing: 0.05em;">Active Open Queue</div>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #fff7ed; display: flex; align-items: center; justify-content: center; font-size: 16px;">⏳</div>
            </div>
            <div style="font-size: 32px; font-weight: 800; color: var(--ink); margin: 10px 0 4px; font-family: 'Outfit', sans-serif;">
                {{ number_format($openTickets) }}
            </div>
            <div style="font-size: 12px; color: var(--muted);">
                New: <strong>{{ $statusCounts['new'] }}</strong> · In Progress: <strong>{{ $statusCounts['in_progress'] }}</strong>
            </div>
        </div>

        <!-- Card 4: Urgent / Critical (P1) -->
        <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #dc2626; letter-spacing: 0.05em;">Urgent Attention</div>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #fef2f2; display: flex; align-items: center; justify-content: center; font-size: 16px;">🚨</div>
            </div>
            <div style="font-size: 32px; font-weight: 800; color: #dc2626; margin: 10px 0 4px; font-family: 'Outfit', sans-serif;">
                {{ number_format($urgentTickets) }}
            </div>
            <div style="font-size: 12px; color: var(--muted);">
                Plus <strong>{{ $highTickets }}</strong> High priority (P2) unresolved
            </div>
        </div>

        <!-- Card 5: SLA Compliance -->
        <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #6366f1; letter-spacing: 0.05em;">SLA Compliance</div>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #eef2ff; display: flex; align-items: center; justify-content: center; font-size: 16px;">⏱️</div>
            </div>
            <div style="display: flex; align-items: baseline; gap: 8px; margin: 10px 0 4px;">
                <div style="font-size: 32px; font-weight: 800; color: var(--ink); font-family: 'Outfit', sans-serif;">
                    {{ $slaComplianceRate }}%
                </div>
                @if ($slaBreached > 0)
                    <span class="pill" style="background: #fef2f2; color: #dc2626; font-size: 11px; font-weight: 700;">
                        {{ $slaBreached }} Breached
                    </span>
                @else
                    <span class="pill" style="background: #ecfdf5; color: #16a34a; font-size: 11px; font-weight: 700;">
                        Target Met
                    </span>
                @endif
            </div>
            <div style="font-size: 12px; color: var(--muted);">
                Target deadlines met before SLA expiry
            </div>
        </div>

        <!-- Card 6: Average Resolution Time -->
        <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #8b5cf6; letter-spacing: 0.05em;">Avg Velocity</div>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #f5f3ff; display: flex; align-items: center; justify-content: center; font-size: 16px;">⚡</div>
            </div>
            <div style="font-size: 32px; font-weight: 800; color: var(--ink); margin: 10px 0 4px; font-family: 'Outfit', sans-serif;">
                {{ $avgResolutionHours }}h
            </div>
            <div style="font-size: 12px; color: var(--muted);">
                Full resolution · First response avg: <strong>{{ $avgFirstResponseHours }}h</strong>
            </div>
        </div>

    </div>

    <!-- Main Analytics Section (2 Columns) -->
    <div style="display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start; margin-top: 24px;">
        
        <!-- Left Column: Daily Created vs Resolved & Breakdown -->
        <div style="display: flex; flex-direction: column; gap: 24px; min-width: 0;">
            
            <!-- Created vs Resolved Daily Timeline -->
            <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h3 style="font-size: 16px; font-weight: 700; color: var(--ink); margin: 0 0 4px;">Created vs Resolved Trend (Last 7 Days)</h3>
                        <p style="font-size: 13px; color: var(--muted); margin: 0;">Compare ticket inflow velocity against agent resolution output.</p>
                    </div>
                    <div style="display: flex; gap: 16px; font-size: 12px; font-weight: 600;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 10px; height: 10px; border-radius: 2px; background: #0284c7;"></span> Created Tickets
                        </span>
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 10px; height: 10px; border-radius: 2px; background: #16a34a;"></span> Resolved Tickets
                        </span>
                    </div>
                </div>

                <!-- Daily Bar Comparison -->
                <div style="display: grid; gap: 14px;">
                    @php
                        $maxDayCount = max(1, collect($dailyTrend)->max(fn($d) => max($d['created'], $d['resolved'])));
                    @endphp
                    @foreach ($dailyTrend as $dayData)
                        @php
                            $createdPct = round(($dayData['created'] / $maxDayCount) * 100);
                            $resolvedPct = round(($dayData['resolved'] / $maxDayCount) * 100);
                        @endphp
                        <div style="display: grid; grid-template-columns: 80px 1fr 80px; gap: 12px; align-items: center; font-size: 13px;">
                            <div style="font-weight: 600; color: #475569;">
                                {{ $dayData['day'] }} <span style="font-size: 11px; color: var(--muted);">{{ $dayData['date'] }}</span>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <!-- Created Bar -->
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="background: #e0f2fe; border-radius: 4px; height: 12px; width: 100%; overflow: hidden; display: flex;">
                                        <div style="background: #0284c7; width: {{ max(4, $createdPct) }}%; height: 100%; border-radius: 4px;"></div>
                                    </div>
                                    <span style="font-size: 11px; font-weight: 700; color: #0284c7; min-width: 24px;">{{ $dayData['created'] }}</span>
                                </div>
                                <!-- Resolved Bar -->
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="background: #dcfce7; border-radius: 4px; height: 12px; width: 100%; overflow: hidden; display: flex;">
                                        <div style="background: #16a34a; width: {{ max(4, $resolvedPct) }}%; height: 100%; border-radius: 4px;"></div>
                                    </div>
                                    <span style="font-size: 11px; font-weight: 700; color: #16a34a; min-width: 24px;">{{ $dayData['resolved'] }}</span>
                                </div>
                            </div>
                            <div style="text-align: right; font-size: 11px; font-weight: 600;">
                                @if ($dayData['resolved'] >= $dayData['created'])
                                    <span style="color: #16a34a;">+{{ $dayData['resolved'] - $dayData['created'] }} cleared</span>
                                @else
                                    <span style="color: #ea580c;">-{{ $dayData['created'] - $dayData['resolved'] }} backlog</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Ticket Status Distribution Bar -->
            <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 24px;">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--ink); margin: 0 0 4px;">Ticket Status Lifecycle Breakdown</h3>
                <p style="font-size: 13px; color: var(--muted); margin: 0 0 16px;">Distribution of tickets across their workflow stages.</p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px;">
                    <div style="padding: 12px; background: #f8fafc; border: 1px solid var(--line); border-radius: 8px;">
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">New</div>
                        <div style="font-size: 20px; font-weight: 800; color: #0284c7; margin-top: 4px;">{{ $statusCounts['new'] }}</div>
                        <div style="font-size: 11px; color: var(--muted);">Awaiting triage</div>
                    </div>
                    <div style="padding: 12px; background: #f8fafc; border: 1px solid var(--line); border-radius: 8px;">
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Open</div>
                        <div style="font-size: 20px; font-weight: 800; color: #2563eb; margin-top: 4px;">{{ $statusCounts['open'] }}</div>
                        <div style="font-size: 11px; color: var(--muted);">Assigned & active</div>
                    </div>
                    <div style="padding: 12px; background: #f8fafc; border: 1px solid var(--line); border-radius: 8px;">
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">In Progress</div>
                        <div style="font-size: 20px; font-weight: 800; color: #7c3aed; margin-top: 4px;">{{ $statusCounts['in_progress'] }}</div>
                        <div style="font-size: 11px; color: var(--muted);">Being diagnosed</div>
                    </div>
                    <div style="padding: 12px; background: #f8fafc; border: 1px solid var(--line); border-radius: 8px;">
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Pending Customer</div>
                        <div style="font-size: 20px; font-weight: 800; color: #ea580c; margin-top: 4px;">{{ $statusCounts['pending_customer'] }}</div>
                        <div style="font-size: 11px; color: var(--muted);">Waiting feedback</div>
                    </div>
                    <div style="padding: 12px; background: #f8fafc; border: 1px solid var(--line); border-radius: 8px;">
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Resolved</div>
                        <div style="font-size: 20px; font-weight: 800; color: #16a34a; margin-top: 4px;">{{ $statusCounts['resolved'] }}</div>
                        <div style="font-size: 11px; color: var(--muted);">Solution verified</div>
                    </div>
                    <div style="padding: 12px; background: #f8fafc; border: 1px solid var(--line); border-radius: 8px;">
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Closed</div>
                        <div style="font-size: 20px; font-weight: 800; color: #475569; margin-top: 4px;">{{ $statusCounts['closed'] }}</div>
                        <div style="font-size: 11px; color: var(--muted);">Archived</div>
                    </div>
                </div>
            </div>

            <!-- Intake Channels & Department Performance -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                
                <!-- Channels Card -->
                <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px;">
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--ink); margin: 0 0 14px;">Intake Channels</h4>
                    <div style="display: grid; gap: 12px;">
                        @php
                            $channelLabels = [
                                'portal' => ['name' => 'Support Portal', 'icon' => '🌐', 'color' => '#0284c7'],
                                'website' => ['name' => 'Website Live Chat', 'icon' => '💬', 'color' => '#16a34a'],
                                'email' => ['name' => 'Support Mail', 'icon' => '✉️', 'color' => '#ea580c'],
                                'whatsapp' => ['name' => 'WhatsApp Cloud', 'icon' => '📱', 'color' => '#25d366'],
                            ];
                        @endphp
                        @foreach ($channelLabels as $cKey => $cInfo)
                            @php
                                $cCount = $channelCounts[$cKey] ?? 0;
                                $cPct = $totalTickets > 0 ? round(($cCount / $totalTickets) * 100) : 0;
                            @endphp
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 4px;">
                                    <span>{{ $cInfo['icon'] }} <strong>{{ $cInfo['name'] }}</strong></span>
                                    <span><strong>{{ $cCount }}</strong> ({{ $cPct }}%)</span>
                                </div>
                                <div style="background: #f1f5f9; border-radius: 4px; height: 6px; overflow: hidden;">
                                    <div style="background: {{ $cInfo['color'] }}; width: {{ $cPct }}%; height: 100%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Priority Levels Card -->
                <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px;">
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--ink); margin: 0 0 14px;">Priority Levels</h4>
                    <div style="display: grid; gap: 12px;">
                        @php
                            $priLabels = [
                                'urgent' => ['name' => '🚨 Urgent (P1)', 'count' => $priorityCounts['urgent'], 'color' => '#dc2626'],
                                'high' => ['name' => '⚠️ High (P2)', 'count' => $priorityCounts['high'], 'color' => '#ea580c'],
                                'normal' => ['name' => 'ℹ️ Normal (P3)', 'count' => $priorityCounts['normal'], 'color' => '#2563eb'],
                                'low' => ['name' => '📋 Low (P4)', 'count' => $priorityCounts['low'], 'color' => '#64748b'],
                            ];
                        @endphp
                        @foreach ($priLabels as $pKey => $pInfo)
                            @php
                                $pPct = $totalTickets > 0 ? round(($pInfo['count'] / $totalTickets) * 100) : 0;
                            @endphp
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 4px;">
                                    <span><strong>{{ $pInfo['name'] }}</strong></span>
                                    <span><strong>{{ $pInfo['count'] }}</strong> ({{ $pPct }}%)</span>
                                </div>
                                <div style="background: #f1f5f9; border-radius: 4px; height: 6px; overflow: hidden;">
                                    <div style="background: {{ $pInfo['color'] }}; width: {{ $pPct }}%; height: 100%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

        </div>

        <!-- Right Column: Departments & Team Workload -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <!-- Category & Department Performance -->
            <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px;">
                <h4 style="font-size: 14px; font-weight: 700; color: var(--ink); margin: 0 0 12px;">Department Performance</h4>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @forelse ($categoryStats->take(6) as $cStat)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px; font-size: 12.5px;">
                            <div>
                                <strong style="color: var(--ink);">{{ $cStat['name'] }}</strong>
                                <div style="font-size: 11px; color: var(--muted);">{{ $cStat['department'] }}</div>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-weight: 700; color: var(--ink);">{{ $cStat['resolved'] }}/{{ $cStat['total'] }}</span>
                                <div style="font-size: 11px; color: #16a34a; font-weight: 600;">{{ $cStat['rate'] }}% resolved</div>
                            </div>
                        </div>
                    @empty
                        <div style="font-size: 12.5px; color: var(--muted); text-align: center; padding: 12px;">
                            No category data recorded yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Agent Performance & Workload -->
            <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px;">
                <h4 style="font-size: 14px; font-weight: 700; color: var(--ink); margin: 0 0 12px;">Support Team Workload</h4>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @forelse ($agentStats->take(6) as $aStat)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px; font-size: 12.5px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: #e0f2fe; color: #0284c7; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                    {{ substr($aStat['name'], 0, 1) }}
                                </div>
                                <div>
                                    <strong style="color: var(--ink);">{{ $aStat['name'] }}</strong>
                                    <div style="font-size: 11px; color: var(--muted);">{{ $aStat['open'] }} active tickets</div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <span class="pill" style="background: #ecfdf5; color: #16a34a; font-size: 11px; font-weight: 700;">
                                    {{ $aStat['resolved'] }} Resolved
                                </span>
                            </div>
                        </div>
                    @empty
                        <div style="font-size: 12.5px; color: var(--muted); text-align: center; padding: 12px;">
                            No agents currently assigned to tickets.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card" style="background: #f8fafc; border: 1px solid var(--line); border-radius: 12px; padding: 18px;">
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--green); letter-spacing: 0.08em; margin-bottom: 6px;">Support Hub</div>
                <h4 style="margin: 0 0 6px; font-size: 14px; font-weight: 700;">Aprilo Customer Desk</h4>
                <p style="font-size: 12px; color: var(--muted); margin: 0 0 12px; line-height: 1.5;">
                    Manage dynamic intake forms, customer authentication policies, and SLA resolution rules.
                </p>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('admin.support.forms', $organization ? ['organization_id' => $organization->id] : []) }}" style="font-size: 12.5px; font-weight: 600; color: var(--ink); text-decoration: underline;">
                        📋 Manage Dynamic Ticket Forms
                    </a>
                    <a href="{{ route('admin.support.settings', $organization ? ['organization_id' => $organization->id] : []) }}" style="font-size: 12.5px; font-weight: 600; color: var(--ink); text-decoration: underline;">
                        ⚙️ Configure Portal Branding & SLA
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- Live Ticket Monitor Queue Table -->
    <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 24px; margin-top: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: var(--ink); margin: 0 0 4px;">Live Ticket Queue Monitor</h3>
                <p style="font-size: 13px; color: var(--muted); margin: 0;">Real-time feed of all incoming support requests, SLA target timers, and assignment states.</p>
            </div>

            <!-- Search form -->
            <form method="GET" action="{{ route('admin.support.reports') }}" style="display: flex; gap: 8px; margin: 0; padding: 0; border: 0; box-shadow: none;">
                @if ($organization)
                    <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                @endif
                <input type="hidden" name="range" value="{{ $range }}">
                @if ($statusFilter)
                    <input type="hidden" name="status_filter" value="{{ $statusFilter }}">
                @endif
                <input type="text" name="search" value="{{ $search }}" placeholder="Search ticket #, subject, customer..." style="padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; font-size: 12.5px; width: 240px;">
                <button type="submit" class="button" style="width: auto; padding: 8px 14px; font-size: 12.5px;">Search</button>
            </form>
        </div>

        <!-- Filter Tabs -->
        <div style="display: flex; gap: 8px; border-bottom: 1px solid var(--line); padding-bottom: 12px; margin-bottom: 16px; overflow-x: auto;">
            @php
                $tabFilters = [
                    '' => 'All Tickets (' . $totalTickets . ')',
                    'open' => 'Active Open (' . $openTickets . ')',
                    'in_progress' => 'In Progress (' . $statusCounts['in_progress'] . ')',
                    'urgent' => 'Urgent (' . $priorityCounts['urgent'] . ')',
                    'unassigned' => 'Unassigned',
                    'resolved' => 'Resolved (' . $totalResolved . ')',
                ];
            @endphp
            @foreach ($tabFilters as $tfKey => $tfLabel)
                <a href="{{ route('admin.support.reports', array_merge(request()->query(), ['status_filter' => $tfKey])) }}"
                   style="padding: 6px 12px; font-size: 12.5px; font-weight: 600; border-radius: 6px; text-decoration: none; {{ ($statusFilter === $tfKey || (!$statusFilter && $tfKey === '')) ? 'background: #eff6ff; color: #2563eb; font-weight: 700;' : 'color: var(--muted);' }}">
                    {{ $tfLabel }}
                </a>
            @endforeach
        </div>

        <!-- Table -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--line); text-align: left; font-size: 11px; text-transform: uppercase; color: var(--muted);">
                        <th style="padding: 10px 12px;">Ticket # & Subject</th>
                        @if (! $organization)
                            <th style="padding: 10px 12px;">Organization</th>
                        @endif
                        <th style="padding: 10px 12px;">Customer</th>
                        <th style="padding: 10px 12px;">Channel</th>
                        <th style="padding: 10px 12px;">Priority</th>
                        <th style="padding: 10px 12px;">Status</th>
                        <th style="padding: 10px 12px;">Assigned Agent</th>
                        <th style="padding: 10px 12px;">SLA Health</th>
                        <th style="padding: 10px 12px; text-align: right;">Created / Resolved</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr style="border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                            <td style="padding: 12px;">
                                <div style="font-weight: 700; color: var(--ink);">
                                    {{ $ticket->ticket_number }}
                                </div>
                                <div style="color: #475569; font-size: 12.5px; margin-top: 2px;">
                                    {{ Str::limit($ticket->subject, 42) }}
                                </div>
                                @if ($ticket->department)
                                    <span style="font-size: 11px; color: var(--muted); font-family: monospace;">{{ $ticket->department }}</span>
                                @endif
                            </td>
                            @if (! $organization)
                                <td style="padding: 12px; font-size: 12px; color: #475569;">
                                    {{ $ticket->organization?->name ?? 'Global' }}
                                </td>
                            @endif
                            <td style="padding: 12px;">
                                <div style="font-weight: 600; color: var(--ink);">
                                    {{ $ticket->customer?->name ?? 'Anonymous Customer' }}
                                </div>
                                @if ($ticket->customer?->email)
                                    <div style="font-size: 11px; color: var(--muted);">{{ $ticket->customer->email }}</div>
                                @endif
                            </td>
                            <td style="padding: 12px;">
                                <span class="pill" style="font-size: 11px; text-transform: capitalize; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;">
                                    {{ $ticket->channel }}
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                @php
                                    $priColors = [
                                        'urgent' => ['bg' => '#fef2f2', 'color' => '#dc2626'],
                                        'high' => ['bg' => '#fff7ed', 'color' => '#ea580c'],
                                        'normal' => ['bg' => '#eff6ff', 'color' => '#2563eb'],
                                        'low' => ['bg' => '#f8fafc', 'color' => '#64748b'],
                                    ];
                                    $priStyle = $priColors[$ticket->priority] ?? ['bg' => '#f8fafc', 'color' => '#64748b'];
                                @endphp
                                <span class="pill" style="background: {{ $priStyle['bg'] }}; color: {{ $priStyle['color'] }}; font-weight: 700; text-transform: uppercase; font-size: 11px;">
                                    {{ $ticket->priority }}
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                @php
                                    $statusColors = [
                                        'new' => ['bg' => '#f0f9ff', 'color' => '#0284c7'],
                                        'open' => ['bg' => '#eff6ff', 'color' => '#2563eb'],
                                        'in_progress' => ['bg' => '#f5f3ff', 'color' => '#7c3aed'],
                                        'pending_customer' => ['bg' => '#fff7ed', 'color' => '#ea580c'],
                                        'pending_internal' => ['bg' => '#fffbeb', 'color' => '#d97706'],
                                        'resolved' => ['bg' => '#ecfdf5', 'color' => '#16a34a'],
                                        'closed' => ['bg' => '#f1f5f9', 'color' => '#475569'],
                                    ];
                                    $stStyle = $statusColors[$ticket->status] ?? ['bg' => '#f1f5f9', 'color' => '#475569'];
                                @endphp
                                <span class="pill" style="background: {{ $stStyle['bg'] }}; color: {{ $stStyle['color'] }}; font-weight: 700; font-size: 11px;">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                @if ($ticket->assignedAgent)
                                    <div style="font-weight: 600; color: var(--ink);">{{ $ticket->assignedAgent->name }}</div>
                                    <div style="font-size: 11px; color: var(--muted);">{{ $ticket->assignedAgent->email }}</div>
                                @else
                                    <span style="color: #ea580c; font-size: 12px; font-weight: 600;">⚠️ Unassigned</span>
                                @endif
                            </td>
                            <td style="padding: 12px;">
                                @if ($ticket->status === 'resolved' || $ticket->status === 'closed')
                                    <span style="color: #16a34a; font-size: 12px; font-weight: 600;">✓ Resolved</span>
                                @elseif ($ticket->sla_due_at && $ticket->sla_due_at < now())
                                    <span class="pill" style="background: #fef2f2; color: #dc2626; font-size: 11px; font-weight: 700;">
                                        Breached
                                    </span>
                                @elseif ($ticket->sla_due_at)
                                    <span style="color: #2563eb; font-size: 11px; font-weight: 600;">
                                        Due in {{ now()->diffForHumans($ticket->sla_due_at, true) }}
                                    </span>
                                @else
                                    <span style="color: var(--muted); font-size: 11px;">No target</span>
                                @endif
                            </td>
                            <td style="padding: 12px; text-align: right;">
                                <div style="color: var(--ink); font-weight: 500;">
                                    {{ $ticket->created_at?->format('M d, Y') }}
                                </div>
                                <div style="font-size: 11px; color: var(--muted);">
                                    {{ $ticket->created_at?->format('H:i') }}
                                    @if ($ticket->resolved_at)
                                        · <span style="color: #16a34a;">Res: {{ $ticket->resolved_at->format('M d') }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ! $organization ? 9 : 8 }}" style="padding: 32px; text-align: center; color: var(--muted); font-size: 14px;">
                                No support tickets match the current query and filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="margin-top: 16px;">
            {{ $tickets->links() }}
        </div>
    </div>
@endsection
