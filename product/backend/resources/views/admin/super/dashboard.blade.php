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

    <div class="grid grid-4" style="margin-bottom: 24px;">
        <div class="card" style="border-left: 4px solid #6366f1;">
            <div class="metric">{{ $orgCount }}</div>
            <div class="label">Total Organizations</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">{{ $whatsappOrganizations }} WhatsApp connections</div>
        </div>
        <div class="card" style="border-left: 4px solid #10b981;">
            <div class="metric">${{ number_format($totalRevenue, 2) }}</div>
            <div class="label">Gross Revenue</div>
            <div style="font-size: 11px; color: #10b981; margin-top: 4px; font-weight: 700;">Paid invoices</div>
        </div>
        <div class="card" style="border-left: 4px solid #3b82f6;">
            <div class="metric">{{ $totalUsers }}</div>
            <div class="label">Total Users</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">{{ $agentCount }} agents ({{ $activeAgentCount }} active)</div>
        </div>
        <div class="card" style="border-left: 4px solid #f59e0b;">
            <div class="metric">{{ $totalQuestions }}</div>
            <div class="label">Chat Requests</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">{{ $openEscalations }} open escalations</div>
        </div>
    </div>

    <div class="grid grid-2" style="align-items: stretch; margin-bottom: 24px;">
        <div class="card"><h2 style="font-size: 18px; margin-bottom: 4px;">Resolution performance</h2><p class="help" style="margin-bottom: 14px;">Requests resolved by the AI pipeline versus a human agent.</p><div style="height: 250px;"><canvas id="resolutionChart" aria-label="AI and agent resolved chat requests"></canvas></div></div>
        <div class="card"><h2 style="font-size: 18px; margin-bottom: 4px;">Platform mix</h2><p class="help" style="margin-bottom: 14px;">People, organizations, agents, and connected WhatsApp tenants.</p><div style="height: 250px;"><canvas id="platformChart" aria-label="Platform user and organization counts"></canvas></div></div>
    </div>

    <div class="grid grid-2" style="align-items: stretch; margin-bottom: 28px;">
        <div class="card"><h2 style="font-size: 18px; margin-bottom: 4px;">Organization analytics</h2><p class="help" style="margin-bottom: 14px;">Compare demand, resolution, users, and agents across tenants.</p><div style="height: 300px;"><canvas id="organizationChart" aria-label="Organization chat and user analytics"></canvas></div></div>
        <div class="card"><h2 style="font-size: 18px; margin-bottom: 4px;">Revenue by organization</h2><p class="help" style="margin-bottom: 14px;">Paid invoice totals from the platform billing ledger.</p><div style="height: 300px;"><canvas id="revenueChart" aria-label="Revenue by organization"></canvas></div></div>
    </div>

    <div class="card" style="margin-bottom: 28px;">
        <div style="display: flex; justify-content: space-between; gap: 16px; align-items: start; flex-wrap: wrap;"><div><h2 style="font-size: 18px; margin-bottom: 4px;">Firebase conversation analytics</h2><p class="help" style="margin: 0;">Live counts from <code>aprilo_conversations</code>. The collection is read-only here.</p></div><span id="firebaseStatus" class="pill" style="background: #fef3c7; color: #92400e;">Connecting...</span></div>
        <div class="grid grid-4" style="margin-top: 20px;"><div><div id="firebaseConversations" class="metric" style="font-size: 24px;">--</div><div class="label">Conversations</div></div><div><div id="firebaseMessages" class="metric" style="font-size: 24px;">--</div><div class="label">Messages</div></div><div><div id="firebaseWhatsApp" class="metric" style="font-size: 24px;">--</div><div class="label">WhatsApp requests</div></div><div><div id="firebaseActive" class="metric" style="font-size: 24px;">--</div><div class="label">Active conversations</div></div></div>
        <div style="height: 230px; margin-top: 18px;"><canvas id="channelChart" aria-label="Firebase conversation channels"></canvas></div>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        const organizationAnalytics = @json($organizationAnalytics);
        const chartColors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#0ea5e9', '#8b5cf6'];
        function makeChart(id, config) { const canvas = document.getElementById(id); if (canvas && window.Chart) new Chart(canvas, config); }
        makeChart('resolutionChart', { type: 'doughnut', data: { labels: ['Resolved by AI', 'Resolved by agent', 'Open escalations'], datasets: [{ data: [{{ $resolvedByAi }}, {{ $resolvedByAgent }}, {{ $openEscalations }}], backgroundColor: ['#4f46e5', '#10b981', '#f59e0b'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } });
        makeChart('platformChart', { type: 'bar', data: { labels: ['Organizations', 'Users', 'Agents', 'Employees', 'WhatsApp'], datasets: [{ label: 'Count', data: [{{ $orgCount }}, {{ $totalUsers }}, {{ $agentCount }}, {{ $employeeCount }}, {{ $whatsappOrganizations }}], backgroundColor: chartColors, borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }, plugins: { legend: { display: false } } } });
        makeChart('organizationChart', { type: 'bar', data: { labels: organizationAnalytics.map((item) => item.name), datasets: [{ label: 'Chat requests', data: organizationAnalytics.map((item) => item.questions), backgroundColor: '#4f46e5', borderRadius: 5 }, { label: 'Resolved', data: organizationAnalytics.map((item) => item.resolved), backgroundColor: '#0ea5e9', borderRadius: 5 }, { label: 'Users', data: organizationAnalytics.map((item) => item.users), backgroundColor: '#10b981', borderRadius: 5 }, { label: 'Agents', data: organizationAnalytics.map((item) => item.agents), backgroundColor: '#f59e0b', borderRadius: 5 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
        makeChart('revenueChart', { type: 'bar', data: { labels: organizationAnalytics.map((item) => item.name), datasets: [{ label: 'Paid revenue', data: organizationAnalytics.map((item) => item.revenue), backgroundColor: '#10b981', borderRadius: 5 }] }, options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', scales: { x: { beginAtZero: true } }, plugins: { legend: { display: false } } } });
        async function loadFirebaseAnalytics() { const status = document.getElementById('firebaseStatus'); try { const [{ initializeApp }, firestore] = await Promise.all([import('https://www.gstatic.com/firebasejs/12.16.0/firebase-app.js'), import('https://www.gstatic.com/firebasejs/12.16.0/firebase-firestore.js')]); const app = initializeApp({ apiKey: 'AIzaSyCsCF6XRrauY7qKv2hokd_rjTu7h95YH9o', authDomain: 'aprilo-ai.firebaseapp.com', projectId: 'aprilo-ai', storageBucket: 'aprilo-ai.firebasestorage.app', messagingSenderId: '102293511636', appId: '1:102293511636:web:f6957b8e190da205d4ca41' }, 'super-admin-analytics'); const db = firestore.getFirestore(app); const snapshot = await firestore.getDocs(firestore.collection(db, 'aprilo_conversations')); const conversations = snapshot.docs.map((item) => ({ id: item.id, ...item.data() })); const messageSnapshots = await Promise.all(conversations.map((item) => firestore.getDocs(firestore.collection(db, 'aprilo_conversations', item.id, 'messages')))); const channels = conversations.reduce((result, item) => { const channel = String(item.channel || 'unknown').toLowerCase(); result[channel] = (result[channel] || 0) + 1; return result; }, {}); document.getElementById('firebaseConversations').textContent = conversations.length.toLocaleString(); document.getElementById('firebaseMessages').textContent = messageSnapshots.reduce((total, item) => total + item.size, 0).toLocaleString(); document.getElementById('firebaseWhatsApp').textContent = (channels.whatsapp || 0).toLocaleString(); document.getElementById('firebaseActive').textContent = conversations.filter((item) => item.status === 'active').length.toLocaleString(); status.textContent = 'Live from Firebase'; status.style.background = '#dcfce7'; status.style.color = '#166534'; makeChart('channelChart', { type: 'doughnut', data: { labels: Object.keys(channels), datasets: [{ data: Object.values(channels), backgroundColor: chartColors, borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } }); } catch (error) { console.warn('Firebase analytics unavailable', error); status.textContent = 'Firebase unavailable'; status.style.background = '#fee2e2'; status.style.color = '#991b1b'; } }
        loadFirebaseAnalytics();

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
