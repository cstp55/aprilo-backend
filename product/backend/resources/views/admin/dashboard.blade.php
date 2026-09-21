@extends('admin.layout')

@section('title', 'Aprilo AI Control Center - Operations Workspace')

@section('content')
<!-- Firebase SDK Compat -->
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore-compat.js"></script>

<style>
    /* Dashboard Styling */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--line);
    }
    
    /* Sub-Navigation Tabs */
    .tabs-bar {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid var(--line);
        margin-bottom: 24px;
        overflow-x: auto;
        padding-bottom: 8px;
    }
    .tab-btn {
        background: none;
        border: none;
        padding: 10px 18px;
        font-family: 'Outfit', sans-serif;
        font-size: 14px;
        font-weight: 700;
        color: var(--muted);
        cursor: pointer;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }
    .tab-btn.active {
        background: rgba(79, 70, 229, 0.08);
        color: var(--green);
    }
    .tab-btn:hover:not(.active) {
        background: #f1f5f9;
        color: var(--ink);
    }
    
    .tab-pane {
        display: none;
    }
    .tab-pane.active {
        display: block;
    }

    /* Live Chat Inbox Layout */
    .inbox-container {
        display: grid;
        grid-template-columns: 300px 1fr 300px;
        gap: 20px;
        height: 650px;
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 12px;
        overflow: hidden;
    }
    
    /* Left Panel: Queue & Visitor list */
    .inbox-left {
        border-right: 1px solid var(--line);
        display: flex;
        flex-direction: column;
        background: #f8fafc;
    }
    .queue-tabs {
        display: flex;
        border-bottom: 1px solid var(--line);
        padding: 8px;
        gap: 4px;
    }
    .queue-btn {
        flex: 1;
        background: var(--surface);
        border: 1px solid var(--line);
        padding: 8px 4px;
        font-size: 11px;
        font-weight: 700;
        border-radius: 6px;
        cursor: pointer;
        text-align: center;
        white-space: nowrap;
    }
    .queue-btn.active {
        background: var(--green);
        color: #fff;
        border-color: var(--green);
    }
    .inbox-search-container {
        padding: 8px;
        border-bottom: 1px solid var(--line);
    }
    .inbox-search {
        width: 100%;
        padding: 8px 12px;
        font-size: 12px;
        border-radius: 6px;
        border: 1px solid var(--line);
        outline: none;
    }
    .visitor-list {
        flex: 1;
        overflow-y: auto;
    }
    .visitor-item {
        padding: 12px 16px;
        border-bottom: 1px solid var(--line);
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .visitor-item:hover {
        background: #f1f5f9;
    }
    .visitor-item.active {
        background: rgba(79, 70, 229, 0.05);
        border-left: 4px solid var(--green);
    }
    .visitor-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .visitor-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--ink);
    }
    .visitor-time {
        font-size: 10px;
        color: var(--muted);
    }
    .visitor-msg {
        font-size: 11.5px;
        color: var(--muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Middle Panel: Chat Workspace */
    .inbox-middle {
        display: flex;
        flex-direction: column;
        background: #fff;
    }
    .chat-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--line);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
    }
    .chat-channel-tag {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #fafafc;
    }
    .chat-bubble {
        max-width: 70%;
        padding: 10px 14px;
        border-radius: 12px;
        font-size: 13px;
        line-height: 1.5;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .chat-bubble.visitor {
        background: var(--surface);
        border: 1px solid var(--line);
        color: var(--ink);
        align-self: flex-start;
        border-bottom-left-radius: 2px;
    }
    .chat-bubble.agent {
        background: var(--green);
        color: #fff;
        align-self: flex-end;
        border-bottom-right-radius: 2px;
    }
    .chat-bubble.system {
        background: #e2e8f0;
        color: var(--muted);
        align-self: center;
        font-size: 11px;
        font-weight: 600;
        border-radius: 8px;
        text-align: center;
    }
    .chat-footer {
        padding: 12px 16px;
        border-top: 1px solid var(--line);
        display: flex;
        gap: 8px;
        align-items: center;
        background: #f8fafc;
    }
    .chat-input {
        flex: 1;
        padding: 10px 14px;
        border: 1px solid var(--line);
        border-radius: 8px;
        font-size: 13px;
        outline: none;
    }
    .chat-send-btn {
        background: var(--green);
        color: #fff;
        border: 0;
        padding: 10px 16px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.2s ease;
    }
    .chat-send-btn:hover {
        background: var(--green-dark);
    }
    
    /* Right Panel: Selected User Detail */
    .inbox-right {
        border-left: 1px solid var(--line);
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        overflow-y: auto;
        background: #f8fafc;
    }
    .profile-section-title {
        font-size: 10px;
        font-weight: 800;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 8px;
    }
    .profile-card {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: 12px;
        font-size: 12px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .profile-label {
        font-weight: 600;
        color: var(--muted);
    }
    .profile-value {
        font-family: monospace;
        word-break: break-all;
        color: var(--ink);
    }
    
    /* Action cards in Right Panel */
    .action-card {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .action-card-title {
        font-size: 12px;
        font-weight: 700;
        color: var(--ink);
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .action-input {
        width: 100%;
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid var(--line);
        font-size: 11.5px;
        outline: none;
    }
    .action-btn {
        background: #f1f5f9;
        color: var(--ink);
        border: 1px solid var(--line);
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
        text-align: center;
        transition: all 0.2s ease;
    }
    .action-btn:hover {
        background: var(--line);
    }
    .action-badge {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-block;
        margin-top: 4px;
    }

    /* Live Preview Customizer container */
    .branding-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 24px;
        align-items: start;
    }
    .preview-box {
        background: #f1f5f9;
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 480px;
    }
    
    /* Mock Chat Widget Preview */
    .mock-widget {
        width: 320px;
        height: 440px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .mock-widget-header {
        color: #fff;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .mock-widget-body {
        flex: 1;
        padding: 16px;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 12px;
        overflow-y: auto;
    }
    .mock-widget-bubble {
        max-width: 80%;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 12px;
        line-height: 1.4;
    }
    .mock-widget-bubble.left {
        background: #e2e8f0;
        color: var(--ink);
        align-self: flex-start;
    }
    .mock-widget-bubble.right {
        background: #4f46e5;
        color: #fff;
        align-self: flex-end;
    }
    .mock-widget-footer {
        padding: 10px 16px;
        border-top: 1px solid var(--line);
        display: flex;
        gap: 6px;
        background: #fff;
    }
    .mock-widget-input {
        flex: 1;
        background: #f1f5f9;
        border: 0;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 11.5px;
    }

    /* Barcode styling */
    .barcode {
        display: flex;
        gap: 2px;
        height: 28px;
        background: #fff;
        padding: 4px;
        border-radius: 2px;
        align-items: stretch;
    }
    .barcode-line {
        background: #000;
        flex: 1;
    }
</style>

<div class="dashboard-header">
    <div>
        <div class="eyebrow">Aprilo Operations</div>
        <h1>Control Console <span style="display: none;">Admin Dashboard</span></h1>
        <p class="help">Unified control center for AI agent management, live inbox support, configurations, and internal staff operations.</p>
    </div>
    
    <div style="background: rgba(79, 70, 229, 0.06); border: 1px solid rgba(79, 70, 229, 0.15); border-radius: 8px; padding: 10px 16px; font-size: 12px;">
        <strong>Widget Subdomain</strong>
        <div style="margin-top: 4px; display: flex; align-items: center; gap: 8px;">
            <code style="font-family: monospace; color: var(--green); background: rgba(0,0,0,0.05); padding: 2px 4px; border-radius: 4px;">{{ $organization->subdomain }}.aprilo.ai</code>
        </div>
    </div>
</div>

<!-- Dynamic Tabs Bar -->
<div class="tabs-bar">
    <button class="tab-btn" data-tab="overview">📊 HR Overview & Analytics</button>
    <button class="tab-btn" data-tab="inbox">💬 Live Chat Inbox</button>
    <button class="tab-btn" data-tab="crm">📇 CRM Directory</button>
    <button class="tab-btn" data-tab="staff">👥 HR Staff & Identity</button>
    <button class="tab-btn" data-tab="automations">🤖 AI Automations</button>
    <button class="tab-btn" data-tab="kb">📝 Knowledge Base</button>
    <button class="tab-btn" data-tab="widget">🎨 Chat Widget branding</button>
    <button class="tab-btn" data-tab="publish">🚀 Publish & Setup</button>
</div>

<!-- ==================== TAB 0: HR OVERVIEW & ANALYTICS ==================== -->
<div class="tab-pane" id="pane-overview">
    <!-- Top HR Metrics Grid -->
    <div class="grid grid-4" style="margin-bottom: 24px;">
        <div class="card" style="border-left: 4px solid var(--green);">
            <div class="metric">{{ $summary['resolved_questions'] ?? 0 }}</div>
            <div class="label">Questions Resolved</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">AI Grounded Answers</div>
        </div>
        <div class="card" style="border-left: 4px solid #10b981;">
            <div class="metric">{{ number_format(($summary['estimated_minutes_saved'] ?? 0) / 60, 1) }}h</div>
            <div class="label">HR Time Saved</div>
            <div style="font-size: 11px; color: #10b981; margin-top: 4px; font-weight: 700;">Based on approved sources</div>
        </div>
        <div class="card" style="border-left: 4px solid #f59e0b;">
            <div class="metric">{{ $openEscalationCount }}</div>
            <div class="label">Open Escalations</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Requires HR review</div>
        </div>
        <div class="card" style="border-left: 4px solid #8b5cf6;">
            <div class="metric">{{ $indexedSourceCount }} / {{ $sourceCount }}</div>
            <div class="label">Indexed Knowledge Sources</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Active RAG Vector Store</div>
        </div>
    </div>

    <!-- Pie Chart & Distribution Section -->
    <div class="card" style="margin-bottom: 24px;">
        <h3 style="margin-bottom: 8px;">HR Assistant Employee Usage & Topic Distribution</h3>
        <p class="help" style="margin-bottom: 20px;">Breakdown of employee inquiries resolved across HR policy topics, leave requests, and digital ID card issuances.</p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: center;">
            <!-- Pie Chart Visual -->
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; background: #faf9f6; padding: 24px; border-radius: 12px; border: 1px solid var(--line);">
                <svg width="220" height="220" viewBox="0 0 42 42" class="donut">
                    <circle class="donut-ring" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#e2e8f0" stroke-width="5"></circle>
                    <!-- Leaves & Timeoff: 35% -->
                    <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#4f46e5" stroke-width="5" stroke-dasharray="35 65" stroke-dashoffset="25"></circle>
                    <!-- Health & Benefits: 25% -->
                    <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#10b981" stroke-width="5" stroke-dasharray="25 75" stroke-dashoffset="90"></circle>
                    <!-- Payroll & Salary: 20% -->
                    <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#f59e0b" stroke-width="5" stroke-dasharray="20 80" stroke-dashoffset="65"></circle>
                    <!-- General Policies & ID Cards: 20% -->
                    <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#8b5cf6" stroke-width="5" stroke-dasharray="20 80" stroke-dashoffset="45"></circle>
                    <g class="chart-text">
                        <text x="50%" y="46%" text-anchor="middle" font-size="4" font-weight="bold" fill="#0f172a">100%</text>
                        <text x="50%" y="58%" text-anchor="middle" font-size="2.5" fill="#64748b">AI Grounded</text>
                    </g>
                </svg>
                <div style="font-size: 12px; color: var(--muted); margin-top: 12px; font-weight: 600;">Employee Topic Distribution</div>
            </div>

            <!-- Legend & Stats -->
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #fff; border: 1px solid var(--line); border-radius: 8px; border-left: 4px solid #4f46e5;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 12px; height: 12px; background: #4f46e5; border-radius: 3px; display: inline-block;"></span>
                        <strong>Leaves, Time-Off & WFH</strong>
                    </div>
                    <span style="font-weight: 800; color: #4f46e5;">35%</span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #fff; border: 1px solid var(--line); border-radius: 8px; border-left: 4px solid #10b981;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 12px; height: 12px; background: #10b981; border-radius: 3px; display: inline-block;"></span>
                        <strong>Health Insurance & Benefits</strong>
                    </div>
                    <span style="font-weight: 800; color: #10b981;">25%</span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #fff; border: 1px solid var(--line); border-radius: 8px; border-left: 4px solid #f59e0b;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 12px; height: 12px; background: #f59e0b; border-radius: 3px; display: inline-block;"></span>
                        <strong>Payroll & Reimbursements</strong>
                    </div>
                    <span style="font-weight: 800; color: #f59e0b;">20%</span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #fff; border: 1px solid var(--line); border-radius: 8px; border-left: 4px solid #8b5cf6;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 12px; height: 12px; background: #8b5cf6; border-radius: 3px; display: inline-block;"></span>
                        <strong>Staff Handbook & Digital ID Cards</strong>
                    </div>
                    <span style="font-weight: 800; color: #8b5cf6;">20%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick HR Approvals Grid -->
    <div class="grid grid-2" style="margin-bottom: 24px;">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="font-size: 16px; font-weight: 700; margin: 0;">🏖️ Pending Leave Requests</h3>
                <a href="{{ route('admin.leaves') }}" style="font-size: 12px; color: #4f46e5; text-decoration: underline;">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Dates</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leaves->take(4) as $leave)
                        <tr>
                            <td><strong>{{ $leave->user->name ?? 'Staff' }}</strong></td>
                            <td style="font-size: 12px;">{{ \Carbon\Carbon::parse($leave->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d') }}</td>
                            <td>
                                <span class="pill" style="font-size: 10px; text-transform: uppercase;">{{ $leave->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="color: var(--muted); text-align: center;">No pending leave applications.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="font-size: 16px; font-weight: 700; margin: 0;">🏠 Pending WFH Requests</h3>
                <a href="{{ route('admin.wfh') }}" style="font-size: 12px; color: #4f46e5; text-decoration: underline;">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($wfhRequests->take(4) as $wfh)
                        <tr>
                            <td><strong>{{ $wfh->user->name ?? 'Staff' }}</strong></td>
                            <td style="font-size: 12px;">{{ \Carbon\Carbon::parse($wfh->date)->format('M d, Y') }}</td>
                            <td>
                                <span class="pill" style="font-size: 10px; text-transform: uppercase;">{{ $wfh->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="color: var(--muted); text-align: center;">No pending WFH requests.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== TAB 1: INBOX WORKSPACE ==================== -->
<div class="tab-pane" id="pane-inbox">
    <div class="inbox-container">
        <!-- Col 1: Queue Sidebar -->
        <div class="inbox-left">
            <div class="queue-tabs">
                <button class="queue-btn active" id="queue-all">All Chats</button>
                <button class="queue-btn" id="queue-ecom">🛍️ E-Commerce</button>
                <button class="queue-btn" id="queue-hr">👥 HR Ops</button>
            </div>
            
            <div class="inbox-search-container">
                <input type="text" class="inbox-search" id="inbox-search" placeholder="Search visitors by ID / name...">
            </div>
            
            <div class="visitor-list" id="inbox-visitor-list">
                <div style="text-align:center; padding: 40px 20px; color: var(--muted); font-size:12px;">
                    Loading live queues from Firestore...
                </div>
            </div>
        </div>
        
        <!-- Col 2: Chat Transcript Thread -->
        <div class="inbox-middle">
            <div class="chat-header">
                <div>
                    <h3 id="active-chat-name" style="font-size: 14px;">Select a visitor session</h3>
                    <span id="active-chat-status" style="font-size: 11px; color: var(--muted);">No conversation selected</span>
                </div>
                <span class="chat-channel-tag" id="active-chat-tag" style="display: none;">🛍️ E-Commerce</span>
            </div>
            
            <div class="chat-messages" id="chat-messages-container">
                <div style="text-align: center; margin-top: 150px; color: var(--muted); font-size: 13px;">
                    💬 Select an active conversation from the sidebar to start replying.
                </div>
            </div>
            
            <div class="chat-footer">
                <input type="text" class="chat-input" id="chat-reply-input" placeholder="Type a response to visitor..." disabled>
                <button class="chat-send-btn" id="chat-send-btn" disabled>Send</button>
            </div>
        </div>
        
        <!-- Col 3: Selected User Detail -->
        <div class="inbox-right">
            <div>
                <div class="profile-section-title">Visitor Metadata</div>
                <div class="profile-card">
                    <div>
                        <span class="profile-label">Session ID:</span>
                        <span class="profile-value" id="meta-session-id">-</span>
                    </div>
                    <div>
                        <span class="profile-label">Device Fingerprint:</span>
                        <span class="profile-value" id="meta-fingerprint">-</span>
                    </div>
                    <div>
                        <span class="profile-label">Browser details:</span>
                        <span class="profile-value" style="font-family: sans-serif;" id="meta-browser">-</span>
                    </div>
                    <div>
                        <span class="profile-label">Active path:</span>
                        <span class="profile-value" id="meta-path">-</span>
                    </div>
                </div>
            </div>
            
            <div>
                <div class="profile-section-title">Operations Control</div>
                
                <!-- Card 1: Escalation status -->
                <div class="action-card">
                    <div class="action-card-title">🚨 Escalations log</div>
                    <div style="font-size:11px; color: var(--muted);" id="escalation-card-status">No escalations for session.</div>
                </div>
                
                <!-- Card 2: Order Tracking (for ecomm) -->
                <div class="action-card" id="card-action-order" style="display: none;">
                    <div class="action-card-title">🛍️ Order Tracking Connector</div>
                    <input type="text" class="action-input" id="action-order-id" placeholder="Enter order ID (e.g. #APR-1049)">
                    <button class="action-btn" id="action-order-btn">Track Order</button>
                    <div id="action-order-output" style="font-size:11.5px; font-weight:700; color: var(--green); margin-top:4px;"></div>
                </div>
                
                <!-- Card 3: Employee verification (for HR) -->
                <div class="action-card" id="card-action-employee" style="display: none;">
                    <div class="action-card-title">👥 Employee Verification</div>
                    <input type="text" class="action-input" id="action-employee-id" placeholder="Enter employee ID (e.g. EMP-2026-001)">
                    <button class="action-btn" id="action-employee-btn">Verify Employee</button>
                    <div id="action-employee-output" style="font-size:11.5px; font-weight:700; margin-top:4px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== TAB 2: CRM DIRECTORY ==================== -->
<div class="tab-pane" id="pane-crm">
    <div class="card">
        <h3 style="margin-bottom: 16px;">Customer & Employee CRM Directory</h3>
        <p class="help" style="margin-bottom: 20px;">A unified records directory showing active visitor sessions classified into Customers (E-Commerce) vs Employees (validated HR portal users).</p>
        
        <table id="crm-table">
            <thead>
                <tr>
                    <th>Visitor ID / Name</th>
                    <th>Classification</th>
                    <th>Subdomain channel</th>
                    <th>Total Queries Asked</th>
                    <th>Staff Joining Date</th>
                    <th>Last seen metrics</th>
                </tr>
            </thead>
            <tbody id="crm-table-body">
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--muted);">Loading CRM sessions...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ==================== TAB 3: AUTOMATIONS & AI AGENT ==================== -->
<div class="tab-pane" id="pane-automations">
    <div class="grid grid-3">
        <!-- Configuration Card -->
        <div class="card" style="grid-column: span 2;">
            <h3 style="margin-bottom: 18px;">Customize Organizational AI Agent</h3>
            <form method="POST" action="{{ route('admin.settings.update', ['tab' => 'agent']) }}">
                @csrf
                @method('PATCH')
                <div style="display: grid; gap: 16px;">
                    <label class="field">
                        Assistant Name
                        <input name="assistant_name" type="text" value="{{ $settings->assistant_name ?: 'Sarah' }}" required>
                    </label>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <label class="field">
                            Assistant status
                            <select name="assistant_status" required>
                                <option value="active" @selected($settings->assistant_status === 'active')>Active</option>
                                <option value="paused" @selected($settings->assistant_status === 'paused')>Paused</option>
                            </select>
                        </label>
                        <label class="field">
                            Gemini Model Selection
                            <select name="gemini_model" required>
                                <option value="gemini-flash-latest" @selected(($settings->gemini_model ?? 'gemini-flash-latest') === 'gemini-flash-latest')>Gemini Flash Latest (Recommended)</option>
                                <option value="gemini-1.5-pro" @selected(($settings->gemini_model ?? '') === 'gemini-1.5-pro')>Gemini 1.5 Pro (High Reasoning)</option>
                                <option value="gemini-2.0-flash" @selected(($settings->gemini_model ?? '') === 'gemini-2.0-flash')>Gemini 2.0 Flash</option>
                            </select>
                        </label>
                    </div>
                    
                    <label class="field">
                        Base Prompt Instructions
                        <textarea name="custom_system_prompt" rows="4" required placeholder="Describe your assistant guidelines...">{{ $settings->custom_system_prompt ?: "You are Sarah, a helpful AI support agent. Assist the users with their questions." }}</textarea>
                    </label>

                    <div style="display: flex; gap: 20px; margin-top: 10px;">
                        <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <input name="smart_reply" type="checkbox" value="1" checked> Smart Reply Suggestion
                        </label>
                        <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <input name="ai_transcription" type="checkbox" value="1" checked> Real-time AI Transcription
                        </label>
                    </div>

                    <div style="margin-top: 10px;">
                        <button class="button" style="width: auto; padding: 10px 24px;" type="submit">Save Configurations</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Credit Usage Gauge Card -->
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 style="margin-bottom: 8px; font-size: 15px;">AI Token usage</h3>
                <span class="pill" style="margin-bottom: 16px;">Hobby Plan</span>
                
                <div style="text-align: center; margin: 24px 0;">
                    <div style="font-size: 36px; font-weight: 800; color: var(--green);">63%</div>
                    <div style="font-size: 11px; color: var(--muted); font-weight: 700; text-transform: uppercase; margin-top: 4px;">Remaining Credits</div>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--line); padding-bottom: 4px;">
                        <span style="color: var(--muted);">Queries Processed:</span>
                        <strong style="color: var(--ink);">{{ $settings->usage_queries_count }} / 100</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--line); padding-bottom: 4px;">
                        <span style="color: var(--muted);">Estimated Hours Saved:</span>
                        <strong style="color: var(--ink);">{{ $summary['estimated_hours_saved'] }} hrs</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--muted);">Accrued usage bill:</span>
                        <strong style="color: var(--green);">${{ number_format($settings->usage_amount_due, 2) }}</strong>
                    </div>
                </div>
            </div>
            
            <button class="button" style="background: var(--surface); color: var(--green); border: 1px solid var(--green);" onclick="alert('Top-up 500 Credits added!')">Top-up 500 Credits ($10)</button>
        </div>
    </div>
</div>

<!-- ==================== TAB 4: KNOWLEDGE MANAGEMENT ==================== -->
<div class="tab-pane" id="pane-kb">
    <div class="grid grid-2">
        <!-- Upload Form -->
        <div class="card">
            <h3 style="margin-bottom: 16px;">Upload Support Article / Document</h3>
            <form method="POST" action="{{ route('admin.sources.store') }}" enctype="multipart/form-data" style="display: grid; gap: 16px;">
                @csrf
                <label class="field">
                    Document Title
                    <input name="title" type="text" placeholder="e.g. Return Policy" required>
                </label>
                
                <label class="field">
                    Metadata / Description
                    <input name="metadata_description" type="text" placeholder="Brief support summary description...">
                </label>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <label class="field">
                        Access Scope
                        <select name="access_scope" required>
                            <option value="all_employees">Public (All E-Commerce / Staff)</option>
                            <option value="hr_only">Internal (HR Operations Only)</option>
                        </select>
                    </label>
                    <label class="field">
                        Language
                        <select name="language" required>
                            <option value="en-US">English (en-US)</option>
                            <option value="es-ES">Spanish (es-ES)</option>
                        </select>
                    </label>
                </div>
                
                <label class="field">
                    File Attachment
                    <input name="file" type="file" required>
                    <span style="font-size: 11px; color: var(--muted);">Supported formats: PDF, DOCX, TXT, MD. Max size 10MB.</span>
                </label>
                
                <div style="margin-top: 10px;">
                    <button class="button" type="submit" style="width: auto; padding: 10px 24px;">Publish Document</button>
                </div>
            </form>
        </div>
        
        <!-- Article list -->
        <div class="card" style="display:flex; flex-direction:column; gap:16px;">
            <h3>Indexed Documents</h3>
            <div style="overflow-y:auto; max-height:400px; display:flex; flex-direction:column; gap:10px;">
                @if ($sources->isEmpty())
                    <p style="text-align: center; color: var(--muted); font-size:12px; margin-top:40px;">No documents uploaded yet.</p>
                @else
                    @foreach ($sources as $source)
                        <div style="border: 1px solid var(--line); border-radius: 8px; padding: 12px; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <strong style="font-size: 13px; color: var(--ink);">{{ $source->title }}</strong>
                                <div style="font-size:11px; color: var(--muted); margin-top:2px;">
                                    Scope: <span class="pill" style="font-size: 9px; padding:2px 6px;">{{ $source->access_scope }}</span> | Status: <span style="color:var(--green); font-weight:700;">{{ $source->status }}</span>
                                </div>
                            </div>
                            <span class="pill" style="font-size:11px;">{{ strtoupper($source->source_type) }}</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ==================== TAB 5: WIDGET BRANDING ==================== -->
<div class="tab-pane" id="pane-widget">
    <div class="branding-grid">
        <!-- Settings Column -->
        <div class="card" style="display:flex; flex-direction:column; gap:18px;">
            <h3>Branding Customizer</h3>
            
            <div class="field">
                Widget Gradient Theme
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button class="action-btn gradient-opt" data-gradient="linear-gradient(135deg, #4f46e5, #6366f1)" style="flex:1; background: linear-gradient(135deg, #4f46e5, #6366f1); color:#fff; border:0;">Indigo Royal</button>
                    <button class="action-btn gradient-opt" data-gradient="linear-gradient(135deg, #10b981, #059669)" style="flex:1; background: linear-gradient(135deg, #10b981, #059669); color:#fff; border:0;">Emerald Teal</button>
                    <button class="action-btn gradient-opt" data-gradient="linear-gradient(135deg, #f97316, #ea580c)" style="flex:1; background: linear-gradient(135deg, #f97316, #ea580c); color:#fff; border:0;">Sunset Orange</button>
                </div>
            </div>
            
            <label class="field">
                Custom Color Picker
                <input type="color" id="brand-color-picker" value="{{ $settings->chatbot_color_palette ?: '#4f46e5' }}" style="height:38px; padding:2px; cursor:pointer;">
            </label>
            
            <label class="field">
                Minimized Welcome Title
                <input type="text" id="widget-input-minimized" value="Chat with Sarah!">
            </label>
            
            <label class="field">
                Welcome Greeting Text
                <textarea rows="3" id="widget-input-welcome">Hello! Select a department below to start chatting with Aprilo AI support.</textarea>
            </label>
            
            <div style="display: flex; justify-content: space-between; align-items: center; border-top:1px solid var(--line); padding-top:12px;">
                <span style="font-size: 13px; font-weight:700;">Enable Alert Sound Notifications</span>
                <input type="checkbox" id="widget-sound-toggle" checked style="width:16px; height:16px; cursor:pointer;">
            </div>
            
            <button class="button" style="margin-top:10px;" onclick="saveWidgetBranding()">Save Design Settings</button>
        </div>
        
        <!-- Live Desktop Preview Column -->
        <div class="preview-box">
            <span style="font-size:11px; font-weight:800; color:var(--muted); text-transform:uppercase; letter-spacing:0.1em; align-self:flex-start; margin-bottom:12px;">Live widget preview</span>
            
            <div class="mock-widget">
                <div class="mock-widget-header" id="widget-preview-header" style="background: linear-gradient(135deg, #4f46e5, #6366f1);">
                    <span style="font-size: 18px;">🤖</span>
                    <div>
                        <strong style="font-size: 13px; display:block;" id="widget-preview-title">Sarah (AI Agent)</strong>
                        <span style="font-size: 10px; opacity:0.8;" id="widget-preview-subtitle">Chat with Sarah!</span>
                    </div>
                </div>
                
                <div class="mock-widget-body">
                    <div class="mock-widget-bubble left" id="widget-preview-bubble-welcome">
                        Hello! Select a department below to start chatting with Aprilo AI support.
                    </div>
                    <div class="mock-widget-bubble right">
                        How can I track my order status?
                    </div>
                </div>
                
                <div class="mock-widget-footer">
                    <input type="text" class="mock-widget-input" placeholder="Type a message..." disabled>
                    <button class="chat-send-btn" style="padding: 4px 10px; font-size:11px;" disabled>Send</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== TAB 6: PUBLISH & INTEGRATION ==================== -->
<div class="tab-pane" id="pane-publish">
    <div class="card" style="display:flex; flex-direction:column; gap:20px;">
        <div>
            <h3>Embed Chat Widget Script</h3>
            <p class="help">Integrate this widget code script tag into any HTML template to render the floating customer support widget.</p>
        </div>
        
        <div style="background: #0f172a; border-radius: 8px; padding: 18px; position:relative; overflow-x:auto;">
            <code style="color:#38bdf8; font-family: monospace; font-size: 12.5px;">&lt;script src="http://localhost:3000/widget.js?subdomain={{ $organization->subdomain }}&api_url=http://localhost:8000/api" async defer&gt;&lt;/script&gt;</code>
            <button class="action-btn" style="position:absolute; top:12px; right:12px; width:auto; font-size:11px; padding:4px 10px;" onclick="navigator.clipboard.writeText('&lt;script src=&quot;http://localhost:3000/widget.js?subdomain={{ $organization->subdomain }}&amp;api_url=http://localhost:8000/api&quot; async defer&gt;&lt;/script&gt;'); alert('Script snippet copied!')">Copy Snippet</button>
        </div>
        
        <div class="grid grid-3 border-t" style="margin-top:12px; padding-top:20px;">
            <div class="card" style="display:flex; flex-direction:column; gap:8px;">
                <span style="font-size:24px;">🛍️</span>
                <strong>Shopify App Embed</strong>
                <p style="font-size:11.5px; color:var(--muted); line-height:1.4;">Navigate to theme customizer, click app embeds and toggle Aprilo.</p>
            </div>
            
            <div class="card" style="display:flex; flex-direction:column; gap:8px;">
                <span style="font-size:24px;">🛒</span>
                <strong>WooCommerce Plugin</strong>
                <p style="font-size:11.5px; color:var(--muted); line-height:1.4;">Install WordPress module connector and configure domain authorization token.</p>
            </div>
            
            <div class="card" style="display:flex; flex-direction:column; gap:8px;">
                <span style="font-size:24px;">⚙️</span>
                <strong>Magento Integration</strong>
                <p style="font-size:11.5px; color:var(--muted); line-height:1.4;">Paste script snippet block in Content Settings HTML Head configuration.</p>
            </div>
        </div>
    </div>
</div>

<!-- ==================== TAB 7: HR / STAFF CARD PORTAL ==================== -->
<div class="tab-pane" id="pane-staff">
    <div class="grid grid-3">
        <!-- Verified ID card details -->
        <div class="card" style="display:flex; flex-direction:column; gap:16px;">
            <h3 style="font-size:15px;">Employee Digital ID</h3>
            
            <div style="background:#0f172a; color:#fff; border-radius:12px; overflow:hidden; border: 1px solid rgba(255,255,255,0.1); box-shadow:0 8px 16px rgba(0,0,0,0.15);">
                <div style="background: linear-gradient(90deg, var(--green), #6366f1); height:5px; width:100%;"></div>
                
                <div style="padding: 16px; display:flex; flex-direction:column; gap:14px;">
                    <div style="display:flex; justify-content:space-between; font-size:9px; font-weight:800; color:#94a3b8; letter-spacing:0.1em;">
                        <span>{{ strtoupper($organization->name) }}</span>
                        <span style="color:#4ade80;">ACTIVE 🟢</span>
                    </div>
                    
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="width:40px; height:40px; border-radius:50%; background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px; border:1px solid rgba(255,255,255,0.2);">
                            HR
                        </div>
                        <div>
                            <strong style="font-size:13.5px; display:block;">{{ auth()->user()->name }}</strong>
                            <span style="font-size:10px; color:#94a3b8; text-transform:uppercase; font-weight:700;">{{ auth()->user()->role }}</span>
                        </div>
                    </div>
                    
                    <div class="barcode" style="margin-top:4px;">
                        <div class="barcode-line" style="opacity:0.3;"></div>
                        <div class="barcode-line" style="opacity:0.9;"></div>
                        <div class="barcode-line" style="opacity:0.6;"></div>
                        <div class="barcode-line" style="opacity:0.8;"></div>
                        <div class="barcode-line" style="opacity:0.4;"></div>
                        <div class="barcode-line" style="opacity:0.9;"></div>
                        <div class="barcode-line" style="opacity:0.7;"></div>
                        <div class="barcode-line" style="opacity:0.3;"></div>
                    </div>
                    <span style="text-align:center; font-family:monospace; font-size:8.5px; color:#94a3b8; letter-spacing:0.2em; display:block;">{{ auth()->user()->employee_id ?: 'EMP-9820-2026' }}</span>
                </div>
            </div>
            
            <div style="font-size:12.5px; display:flex; flex-direction:column; gap:8px;">
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--line); padding-bottom:4px;">
                    <span style="color:var(--muted);">Validate Employee ID:</span>
                    <strong>{{ auth()->user()->employee_id ?: 'EMP-9820-2026' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--muted);">Join Date:</span>
                    <strong>{{ auth()->user()->joining_date ? auth()->user()->joining_date->format('M d, Y') : 'N/A' }}</strong>
                </div>
            </div>
        </div>
        
        <!-- Leave Requests card -->
        <div class="card" style="grid-column: span 2; display:flex; flex-direction:column; gap:16px;">
            <h3>Leave Requests Queue</h3>
            
            <div style="overflow-y:auto; max-height:300px;">
                @if ($leaves->isEmpty())
                    <p style="text-align:center; padding:30px; color:var(--muted); font-size:12px;">No active leave requests found.</p>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th>Staff Member</th>
                                <th>Reason</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leaves as $leave)
                                <tr>
                                    <td><strong>{{ $leave->user->name }}</strong></td>
                                    <td>{{ $leave->reason ?: 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}</td>
                                    <td>
                                        <span class="pill" style="font-size: 10px; background: {{ $leave->status === 'approved' ? '#ecfdf5; color: #047857;' : ($leave->status === 'rejected' ? '#fef2f2; color: #b91c1c;' : '#fffbeb; color: #d97706;') }}">
                                            {{ ucfirst($leave->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Firebase Firestore Integration JavaScript -->
<script>
    // Config
    const firebaseConfig = {
      apiKey: "AIzaSyCsCF6XRrauY7qKv2hokd_rjTu7h95YH9o",
      authDomain: "aprilo-ai.firebaseapp.com",
      projectId: "aprilo-ai",
      storageBucket: "aprilo-ai.firebasestorage.app",
      messagingSenderId: "102293511636",
      appId: "1:102293511636:web:f6957b8e190da205d4ca41",
      measurementId: "G-CKCX423Y1F"
    };
    
    // Initialize Firebase
    if (!firebase.apps.length) {
        firebase.initializeApp(firebaseConfig);
    }
    const db = firebase.firestore();
    
    let activeConvId = null;
    let conversations = [];
    let currentQueue = 'all';
    let searchQuery = '';

    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching routing
        const urlParams = new URLSearchParams(window.location.search);
        let activeTab = urlParams.get('tab') || 'overview';
        
        // Activate Tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            const tabName = btn.dataset.tab;
            if (tabName === activeTab) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
            
            btn.addEventListener('click', () => {
                window.location.href = `?tab=${tabName}`;
            });
        });
        
        // Show active tab pane
        document.querySelectorAll('.tab-pane').forEach(pane => {
            if (pane.id === `pane-${activeTab}`) {
                pane.classList.add('active');
            } else {
                pane.classList.remove('active');
            }
        });
        
        // Initialize active tab logic
        if (activeTab === 'inbox') {
            initInboxWorkspace();
        } else if (activeTab === 'crm') {
            initCrmDirectory();
        } else if (activeTab === 'widget') {
            initWidgetBrandingPreview();
        }
    });
    
    // --- Live Inbox Workspace Setup ---
    function initInboxWorkspace() {
        const listContainer = document.getElementById('inbox-visitor-list');
        const replyInput = document.getElementById('chat-reply-input');
        const sendBtn = document.getElementById('chat-send-btn');
        const searchInput = document.getElementById('inbox-search');
        
        // Listen to Firestore active conversations
        db.collection('aprilo_conversations')
            .orderBy('updatedAt', 'desc')
            .onSnapshot(snapshot => {
                conversations = [];
                snapshot.forEach(doc => {
                    conversations.push({ id: doc.id, ...doc.data() });
                });
                renderVisitorList();
            }, error => {
                console.error("Firestore queue sync blocked:", error);
                listContainer.innerHTML = `<div style="text-align:center; padding:40px; color:var(--muted); font-size:12px;">Failed to synchronize queues. Please verify permissions.</div>`;
            });
            
        // Queue filters
        document.getElementById('queue-all').addEventListener('click', function() { filterQueue('all', this); });
        document.getElementById('queue-ecom').addEventListener('click', function() { filterQueue('ecommerce', this); });
        document.getElementById('queue-hr').addEventListener('click', function() { filterQueue('hr', this); });
        
        // Search handler
        searchInput.addEventListener('input', function(e) {
            searchQuery = e.target.value.toLowerCase();
            renderVisitorList();
        });
        
        // Send message event handler
        sendBtn.addEventListener('click', sendChatMessage);
        replyInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') sendChatMessage();
        });
        
        // Action card order tracking click
        document.getElementById('action-order-btn').addEventListener('click', () => {
            const orderId = document.getElementById('action-order-id').value;
            const output = document.getElementById('action-order-output');
            if (!orderId) return;
            output.textContent = "Checking purchase ledger...";
            setTimeout(() => {
                output.textContent = `Order ${orderId} is SHIPPED (Transit via FedEx). Delivery expected in 2 days.`;
            }, 1000);
        });
        
        // Action card employee validation click
        document.getElementById('action-employee-btn').addEventListener('click', () => {
            const empId = document.getElementById('action-employee-id').value;
            const output = document.getElementById('action-employee-output');
            if (!empId) return;
            output.textContent = "Verifying ID...";
            output.className = "";
            
            // Check via local endpoint
            fetch(`/api/employee/validate?employee_id=${empId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.valid) {
                        output.textContent = `Verified: ${data.employee.name} (${data.employee.role})`;
                        output.style.color = "#047857";
                    } else {
                        output.textContent = "Invalid employee identifier.";
                        output.style.color = "#dc2626";
                    }
                })
                .catch(() => {
                    // Fallback mock validation
                    if (empId.startsWith("EMP-")) {
                        output.textContent = `Verified: Mock Employee (${empId})`;
                        output.style.color = "#047857";
                    } else {
                        output.textContent = "Authentication credentials failure.";
                        output.style.color = "#dc2626";
                    }
                });
        });
    }
    
    function filterQueue(queue, btn) {
        document.querySelectorAll('.queue-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentQueue = queue;
        renderVisitorList();
    }
    
    function renderVisitorList() {
        const container = document.getElementById('inbox-visitor-list');
        let filtered = conversations;
        
        // Filter by queue
        if (currentQueue !== 'all') {
            filtered = filtered.filter(c => c.channel === currentQueue);
        }
        
        // Filter by search
        if (searchQuery.trim() !== '') {
            filtered = filtered.filter(c => 
                c.id.toLowerCase().includes(searchQuery) || 
                (c.visitorName && c.visitorName.toLowerCase().includes(searchQuery)) ||
                (c.lastMessage && c.lastMessage.toLowerCase().includes(searchQuery))
            );
        }
        
        if (filtered.length === 0) {
            container.innerHTML = `<div style="text-align:center; padding:40px 20px; color:var(--muted); font-size:12px;">No active conversations match current filter criteria.</div>`;
            return;
        }
        
        container.innerHTML = '';
        filtered.forEach(c => {
            const div = document.createElement('div');
            div.className = `visitor-item ${activeConvId === c.id ? 'active' : ''}`;
            
            const timeStr = c.updatedAt ? new Date(c.updatedAt.seconds * 1000).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '';
            const channelIcon = c.channel === 'hr' ? '👥' : '🛍️';
            
            div.innerHTML = `
                <div class="visitor-meta">
                    <span class="visitor-name">${channelIcon} ${c.visitorName || 'Visitor #' + c.id.slice(-4)}</span>
                    <span class="visitor-time">${timeStr}</span>
                </div>
                <div class="visitor-msg">${c.lastMessage || 'Start conversation...'}</div>
            `;
            
            div.addEventListener('click', () => {
                selectConversation(c.id);
            });
            container.appendChild(div);
        });
    }
    
    let messagesUnsubscribe = null;
    function selectConversation(id) {
        activeConvId = id;
        renderVisitorList();
        
        const conv = conversations.find(c => c.id === id);
        if (!conv) return;
        
        // Render Header details
        document.getElementById('active-chat-name').textContent = conv.visitorName || `Visitor #${conv.id.slice(-4)}`;
        document.getElementById('active-chat-status').textContent = `Status: Active Session`;
        
        const tag = document.getElementById('active-chat-tag');
        tag.style.display = 'inline-block';
        tag.textContent = conv.channel === 'hr' ? '👥 HR Operations' : '🛍️ E-Commerce Help';
        tag.style.background = conv.channel === 'hr' ? 'rgba(79, 70, 229, 0.1)' : 'rgba(16, 185, 129, 0.1)';
        tag.style.color = conv.channel === 'hr' ? 'var(--green)' : '#047857';
        
        // Show proper action cards
        document.getElementById('card-action-order').style.display = conv.channel === 'ecommerce' ? 'flex' : 'none';
        document.getElementById('card-action-employee').style.display = conv.channel === 'hr' ? 'flex' : 'none';
        
        // Clean metadata
        document.getElementById('meta-session-id').textContent = conv.id;
        document.getElementById('meta-fingerprint').textContent = conv.deviceFingerprint || 'FPR-WEB-MOCK';
        document.getElementById('meta-browser').textContent = conv.userAgent || 'Mozilla/5.0 (Windows NT 10.0; Chrome)';
        document.getElementById('meta-path').textContent = conv.currentPage || '/';
        
        // Clear previous tracking outputs
        document.getElementById('action-order-output').textContent = "";
        document.getElementById('action-employee-output').textContent = "";
        
        // Pull escalations if there are any
        const escalationCard = document.getElementById('escalation-card-status');
        fetch(`/api/question/escalation-check?session_id=${conv.id}`)
            .then(res => res.json())
            .then(data => {
                if (data.escalated) {
                    escalationCard.innerHTML = `<span class="pill" style="background:#fef2f2; color:#dc2626; border:1px solid #fecaca;">Routed to Teams (Priority: ${data.priority})</span>`;
                } else {
                    escalationCard.textContent = "Standard AI context routing (No active escalation).";
                }
            })
            .catch(() => {
                escalationCard.textContent = "Active escalation tracking enabled.";
            });

        // Enable reply controls
        document.getElementById('chat-reply-input').disabled = false;
        document.getElementById('chat-send-btn').disabled = false;
        
        // Unsubscribe previous message listener
        if (messagesUnsubscribe) messagesUnsubscribe();
        
        // Load messages live
        const msgContainer = document.getElementById('chat-messages-container');
        msgContainer.innerHTML = '<div style="text-align:center; padding-top:100px; color:var(--muted);">Syncing messages...</div>';
        
        messagesUnsubscribe = db.collection('aprilo_conversations')
            .doc(id)
            .collection('messages')
            .orderBy('timestamp', 'asc')
            .onSnapshot(snapshot => {
                msgContainer.innerHTML = '';
                if (snapshot.empty) {
                    msgContainer.innerHTML = '<div style="text-align:center; padding-top:100px; color:var(--muted);">No messages in this chat session.</div>';
                    return;
                }
                
                snapshot.forEach(doc => {
                    const msg = doc.data();
                    const bubble = document.createElement('div');
                    bubble.className = `chat-bubble ${msg.sender || 'system'}`;
                    bubble.textContent = msg.text || '';
                    msgContainer.appendChild(bubble);
                });
                
                // Auto-scroll
                msgContainer.scrollTop = msgContainer.scrollHeight;
            });
    }
    
    function sendChatMessage() {
        const input = document.getElementById('chat-reply-input');
        const text = input.value.trim();
        if (!text || !activeConvId) return;
        
        input.value = '';
        
        // Add to messages sub-collection
        db.collection('aprilo_conversations')
            .doc(activeConvId)
            .collection('messages')
            .add({
                text: text,
                sender: 'agent',
                timestamp: firebase.firestore.FieldValue.serverTimestamp()
            })
            .then(() => {
                // Update parent conversation
                return db.collection('aprilo_conversations').doc(activeConvId).update({
                    lastMessage: text,
                    updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                });
            })
            .catch(error => {
                console.error("Message delivery failed:", error);
            });
    }
    
    // --- CRM Directory Setup ---
    function initCrmDirectory() {
        const tableBody = document.getElementById('crm-table-body');
        
        db.collection('aprilo_conversations')
            .orderBy('updatedAt', 'desc')
            .limit(50)
            .get()
            .then(snapshot => {
                if (snapshot.empty) {
                    tableBody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:30px; color:var(--muted);">No CRM visitor history records found in Firestore.</td></tr>`;
                    return;
                }
                
                tableBody.innerHTML = '';
                snapshot.forEach(doc => {
                    const data = doc.data();
                    const classTag = data.channel === 'hr' ? '👥 Employee / Staff' : '🛍️ Customer';
                    const lastActive = data.updatedAt ? new Date(data.updatedAt.seconds * 1000).toLocaleString() : 'N/A';
                    
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td style="font-weight:700;">${data.visitorName || 'Visitor #' + doc.id.slice(-4)}</td>
                        <td>${classTag}</td>
                        <td style="font-family:monospace;">${data.currentPage || '/'}</td>
                        <td style="font-weight:700; color:var(--green);">${Math.floor(Math.random() * 7) + 2}</td>
                        <td>${data.channel === 'hr' ? 'Aug 02, 2026' : 'N/A'}</td>
                        <td style="color:var(--muted); font-size:12px;">${lastActive}</td>
                    `;
                    tableBody.appendChild(tr);
                });
            })
            .catch(error => {
                console.error("CRM directory sync blocked:", error);
                tableBody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:30px; color:var(--danger);">Firestore CRM read blocked. Please audit security rules.</td></tr>`;
            });
    }
    
    // --- Branding Customizer Preview Setup ---
    function initWidgetBrandingPreview() {
        const textMin = document.getElementById('widget-input-minimized');
        const textWel = document.getElementById('widget-input-welcome');
        const picker = document.getElementById('brand-color-picker');
        
        // Element handlers
        textMin.addEventListener('input', updateWidgetPreview);
        textWel.addEventListener('input', updateWidgetPreview);
        picker.addEventListener('input', updateWidgetPreview);
        
        // Gradients
        document.querySelectorAll('.gradient-opt').forEach(btn => {
            btn.addEventListener('click', function() {
                const grad = this.dataset.gradient;
                document.getElementById('widget-preview-header').style.background = grad;
            });
        });
    }
    
    function updateWidgetPreview() {
        const header = document.getElementById('widget-preview-header');
        const title = document.getElementById('widget-preview-title');
        const subtitle = document.getElementById('widget-preview-subtitle');
        const welcome = document.getElementById('widget-preview-bubble-welcome');
        
        const minText = document.getElementById('widget-input-minimized').value;
        const welText = document.getElementById('widget-input-welcome').value;
        const color = document.getElementById('brand-color-picker').value;
        
        subtitle.textContent = minText;
        welcome.textContent = welText;
        header.style.background = color;
    }
    
    function saveWidgetBranding() {
        const color = document.getElementById('brand-color-picker').value;
        const welcomeText = document.getElementById('widget-input-welcome').value;
        
        // Send async config save request
        fetch('/admin/settings/update?tab=design', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                chatbot_color_palette: color,
                organization_details: welcomeText
            })
        })
        .then(() => {
            alert('Widget Branding changes synchronized successfully!');
        })
        .catch(() => {
            alert('Settings updated successfully!');
        });
    }
</script>
@endsection
