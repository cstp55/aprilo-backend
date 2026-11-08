@extends('admin.layout')

@section('title', 'Marketing & Campaigns Suite - Aprilo Infotech')

@section('content')
    @php
        $activeTab = request()->query('tab', 'campaigns');
    @endphp

    <style>
        .mkt-tabs {
            display: flex;
            gap: 8px;
            border-bottom: 2px solid var(--line);
            margin-bottom: 24px;
            overflow-x: auto;
        }
        .mkt-tab-btn {
            background: none;
            border: none;
            padding: 12px 18px;
            font-size: 14px;
            font-weight: 700;
            color: var(--muted);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .mkt-tab-btn:hover {
            color: var(--ink);
        }
        .mkt-tab-btn.active {
            color: var(--green);
            border-bottom-color: var(--green);
        }
        .mkt-badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
        }
        .mkt-badge.lead { background: #fef3c7; color: #92400e; }
        .mkt-badge.converted { background: #dcfce7; color: #166534; }
        .mkt-badge.lost { background: #f1f5f9; color: #64748b; }
        .mkt-badge.product { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
        .mkt-badge.cron-success { background: #dcfce7; color: #166534; }
        .mkt-badge.cron-skipped { background: #fef3c7; color: #92400e; }
        .mkt-badge.cron-failed { background: #fee2e2; color: #991b1b; }

        .wa-form { display: grid; gap: 16px; }
        .wa-form label { display: grid; gap: 7px; color: var(--ink); font-size: 13px; font-weight: 700; }
        .wa-form input, .wa-form select, .wa-form textarea { width: 100%; min-width: 0; padding: 10px 12px; border: 1px solid var(--line); border-radius: 6px; background: #fff; color: var(--ink); }
        .wa-form textarea { min-height: 104px; resize: vertical; }
        .wa-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .wa-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .wa-note { padding: 12px 14px; border-left: 3px solid #e59b16; background: #fff8e8; color: #614813; font-size: 13px; line-height: 1.5; border-radius: 0 6px 6px 0; }
        .wa-note.info { border-left-color: #2563eb; background: #eff6ff; color: #1e40af; }
        .wa-note.ai { border-left-color: var(--green); background: #f0fdf4; color: #14532d; }
        .wa-table-wrap { overflow-x: auto; }
        .wa-status { display: inline-block; padding: 4px 8px; border-radius: 4px; background: #eef2f5; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .wa-status.accepted, .wa-status.completed { background: #e5f6eb; color: #17643a; }
        .wa-status.failed { background: #fde9e7; color: #9c241b; }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-card {
            background: #fff;
            width: 100%;
            max-width: 620px;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--line);
            max-height: 90vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .modal-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-header h3 { margin: 0; font-size: 17px; font-weight: 700; color: var(--ink); }
        .modal-body { padding: 20px 24px; }
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--line);
            background: #f8fafc;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            border-radius: 0 0 12px 12px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
        }
        .stat-card .val {
            font-size: 26px;
            font-weight: 800;
            color: var(--ink);
            font-family: 'Outfit', sans-serif;
            margin-top: 4px;
        }
        .stat-card .lbl {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
        }

        .ai-banner {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        @media (max-width: 860px) {
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
            .wa-grid-3 { grid-template-columns: 1fr; }
            .wa-grid { grid-template-columns: 1fr; }
            .ai-banner { flex-direction: column; align-items: flex-start; }
        }
    </style>

    <div style="margin-bottom: 16px;">
        <a class="button" style="width: auto; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); font-size: 13px; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 8px 14px; border-radius: 6px;" href="{{ route('admin.super.dashboard') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Dashboard
        </a>
    </div>

    <div class="header">
        <div>
            <div class="eyebrow">Marketing & Outreach Suite</div>
            <h1>Marketing Campaigns & Customer Pipeline</h1>
            <p class="help">Automated AI campaigns for Aprilo products, WhatsApp outreach, Resend campaigns, lead conversion tracking & cron monitor.</p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="wa-status {{ $whatsappReady ? 'accepted' : 'failed' }}">
                {{ $whatsappReady ? 'Cloud API Ready' : 'Cloud API Not Configured' }}
            </span>
        </div>
    </div>

    @if (session('status'))
        <div class="card" style="margin-bottom: 18px; border-left: 4px solid var(--green);">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="card" style="margin-bottom: 18px; border-left: 4px solid var(--danger); color: var(--danger);">{{ session('error') }}</div>
    @endif

    @if (! $whatsappReady)
        <div class="wa-note" style="margin-bottom: 18px;">
            Configure <code>WHATSAPP_PHONE_NUMBER_ID</code> and <code>WHATSAPP_ACCESS_TOKEN</code> in your environment before queuing live WhatsApp campaigns.
        </div>
    @endif

    <!-- Navigation Tabs -->
    <nav class="mkt-tabs">
        <a href="{{ route('admin.super.whatsapp-campaigns', ['tab' => 'campaigns']) }}" class="mkt-tab-btn {{ $activeTab === 'campaigns' ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            WhatsApp Campaigns & Outreach
        </a>
        <a href="{{ route('admin.super.whatsapp-campaigns', ['tab' => 'leads']) }}" class="mkt-tab-btn {{ $activeTab === 'leads' ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            Leads & Customer Pipeline
            <span class="mkt-badge lead">{{ $leadStats['leads'] ?? 0 }} Leads</span>
        </a>
        <a href="{{ route('admin.super.whatsapp-campaigns', ['tab' => 'cron']) }}" class="mkt-tab-btn {{ $activeTab === 'cron' ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            Weekly AI Automation & Cron Monitor
            <span class="mkt-badge converted">Active Weekly</span>
        </a>
    </nav>

    {{-- ======================================================== --}}
    {{-- TAB 1: WHATSAPP CAMPAIGNS & RESEND ENGINE                 --}}
    {{-- ======================================================== --}}
    @if ($activeTab === 'campaigns')
        <!-- AI Assistant Banner -->
        <div class="ai-banner">
            <div>
                <strong style="color: #166534; font-size: 15px; display: flex; align-items: center; gap: 6px;">
                    ✨ Aprilo AI Campaign Copywriter
                </strong>
                <p style="margin: 4px 0 0; font-size: 13px; color: #15803d;">
                    Need a persuasive, high-converting WhatsApp message tailored for Aprilo POS, Chatbot, Agent, or TradeFlow? Let AI write it for you.
                </p>
            </div>
            <button type="button" class="button" style="width: auto; background: #16a34a; white-space: nowrap;" onclick="openAiGeneratorModal()">
                ✨ Write Campaign with AI
            </button>
        </div>

        <!-- Create Campaign Card -->
        <div class="card" style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                <h2 style="font-size: 19px; margin: 0;">Create campaign</h2>
                <button type="button" class="button" style="width: auto; background: #fff; color: var(--ink); border: 1px solid var(--line); font-size: 12px; padding: 6px 12px;" onclick="openAiGeneratorModal()">
                    ✨ Auto-Fill Copy with AI
                </button>
            </div>

            <form method="POST" action="{{ route('admin.super.whatsapp-campaigns.store') }}" class="wa-form" id="campaignForm" data-api-ready="{{ $whatsappReady ? '1' : '0' }}">
                @csrf
                <div class="wa-grid-3">
                    <label>Campaign name
                        <input name="name" id="campaignNameInput" maxlength="120" value="{{ old('name') }}" placeholder="e.g. Aprilo POS — Festive Retail Upgrade" required>
                        @error('name') <span class="help">{{ $message }}</span> @enderror
                    </label>

                    <label>Target Software Product
                        <select name="target_product" id="targetProductSelect">
                            <option value="">-- Generic / Brand Awareness --</option>
                            @foreach ($supportedProducts as $prod)
                                <option value="{{ $prod }}" @selected(old('target_product') === $prod)>{{ $prod }}</option>
                            @endforeach
                        </select>
                        <span class="help" style="margin: 0;">Helps segment recipients and track product campaign ROI.</span>
                    </label>

                    <label>Message format
                        <select name="message_type" id="messageType" required>
                            <option value="text" @selected(old('message_type', 'text') === 'text')>Body text (24-hr window / rich copy)</option>
                            <option value="template" @selected(old('message_type') === 'template')>Approved Meta template</option>
                        </select>
                    </label>
                </div>

                <div class="wa-grid">
                    <label>Audience / Recipients
                        <select name="recipient_mode" id="recipientMode" required>
                            <option value="leads" @selected(old('recipient_mode') === 'leads')>
                                🎯 Unconverted Leads ({{ $leadStats['leads'] ?? 0 }} in pipeline) — Nurture & Offer
                            </option>
                            <option value="converted" @selected(old('recipient_mode') === 'converted')>
                                🌟 Converted Customers ({{ $leadStats['converted'] ?? 0 }} active) — Product Awareness & Upsell
                            </option>
                            <option value="all_leads" @selected(old('recipient_mode') === 'all_leads')>
                                👥 All Marketing Contacts ({{ $leadStats['total'] ?? 0 }} total)
                            </option>
                            <option value="users" @selected(old('recipient_mode') === 'users')>
                                💻 Select Registered Users ({{ $eligibleUsers->count() }} active)
                            </option>
                            <option value="manual" @selected(old('recipient_mode') === 'manual')>
                                📱 Single WhatsApp Number (Test / VIP)
                            </option>
                        </select>
                    </label>

                    <label>Delivery Mode
                        <select name="delivery_mode" id="deliveryMode" required>
                            <option value="queued" @selected(old('delivery_mode', 'queued') === 'queued')>Queue campaign (Async background dispatch)</option>
                            <option value="instant" @selected(old('delivery_mode') === 'instant')>Send now to single number</option>
                        </select>
                    </label>
                </div>

                <!-- User Multiselect (Conditional) -->
                <label id="userRecipients" hidden>Select Active Platform Users
                    <select name="user_ids[]" multiple size="5" {{ $eligibleUsers->isEmpty() ? 'disabled' : '' }}>
                        @foreach ($eligibleUsers as $user)
                            <option value="{{ $user->id }}" @selected(in_array($user->id, old('user_ids', []), true))>{{ $user->name }} · {{ $user->phone }}</option>
                        @endforeach
                    </select>
                    <span class="help" style="margin: 0;">{{ $eligibleUsers->count() }} eligible users. Hold Ctrl / Command to select multiple.</span>
                    @error('user_ids') <span class="help">{{ $message }}</span> @enderror
                </label>

                <!-- Single Phone (Conditional) -->
                <label id="manualRecipient" hidden>WhatsApp Phone with Country Code
                    <input name="recipient_phone" type="tel" inputmode="tel" placeholder="e.g. 919876543210 or +14155552671" value="{{ old('recipient_phone') }}">
                    <span class="help" style="margin: 0;">Format with country code (e.g. 91 for India, 1 for USA).</span>
                    @error('recipient_phone') <span class="help">{{ $message }}</span> @enderror
                </label>

                <!-- Meta Template Options (Conditional) -->
                <div id="templateFields" class="wa-grid" hidden>
                    <label>Approved WhatsApp Template
                        <select id="templateSelector" data-templates-url="{{ route('admin.super.whatsapp-campaigns.templates') }}" data-selected-name="{{ old('template_name') }}" data-selected-language="{{ old('template_language') }}" disabled>
                            <option value="">Loading approved templates...</option>
                        </select>
                        <span id="templateStatus" class="help" role="status" style="margin: 0;">Loading templates from Meta...</span>
                        @error('template_name') <span class="help">{{ $message }}</span> @enderror
                        @error('template_language') <span class="help">{{ $message }}</span> @enderror
                    </label>
                    <div id="templatePreview" class="wa-note" style="align-self: start; min-height: 42px;">Select a template to preview its approved body.</div>
                </div>
                <input type="hidden" name="template_name" id="templateName" value="{{ old('template_name') }}">
                <input type="hidden" name="template_language" id="templateLanguage" value="{{ old('template_language') }}">
                <label id="templateParameters" hidden>Template Parameters (one per line)
                    <textarea name="template_parameters" id="templateParametersInput" maxlength="2000" placeholder="One value per template variable placeholder">{{ old('template_parameters') }}</textarea>
                    <span id="templateParameterHint" class="help" style="margin: 0;"></span>
                </label>

                <!-- Freeform Rich Message Text Area -->
                <label id="textMessage">Campaign Message Text
                    <div style="font-size: 11px; color: var(--muted); margin-bottom: 4px;">
                        Supports WhatsApp markdown (*bold*, _italic_, ~strikethrough~) and dynamic tags: <code>{customer_name}</code>, <code>{company_name}</code>, <code>{product_name}</code>.
                    </div>
                    <textarea name="message_text" id="campaignMessageText" maxlength="4096" placeholder="Type or click '✨ Write Campaign with AI' to generate rich product copy...">{{ old('message_text') }}</textarea>
                    @error('message_text') <span class="help">{{ $message }}</span> @enderror
                </label>

                <!-- Hidden AI tracking flags -->
                <input type="hidden" name="ai_generated" id="aiGeneratedField" value="{{ old('ai_generated', '0') }}">
                <input type="hidden" name="ai_prompt_notes" id="aiPromptNotesField" value="{{ old('ai_prompt_notes', '') }}">

                <div class="wa-note">
                    WhatsApp policy requires prior opt-in for outbound business marketing messages. Template messages can be delivered anytime; freeform messages can be delivered during active 24-hour service windows or direct opt-in dialogues.
                </div>

                <label style="display: flex; grid-template-columns: 18px 1fr; align-items: start; gap: 10px;">
                    <input type="checkbox" name="consent_confirmed" value="1" required @checked(old('consent_confirmed')) style="width: 18px; margin: 2px 0 0;">
                    <span>I confirm the targeted contacts / leads have opted in to receive communication from Aprilo Infotech.</span>
                </label>
                @error('consent_confirmed') <span class="help">{{ $message }}</span> @enderror

                <button class="button" id="submitCampaign" type="submit" style="width: auto; justify-self: start;" {{ $whatsappReady ? '' : 'disabled' }}>
                    Queue Campaign
                </button>
            </form>
        </div>

        <!-- Campaign History Table with RESEND Feature -->
        <div class="card" style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                <h2 style="font-size: 19px; margin: 0;">Campaign History & Resend Engine</h2>
                <span class="help" style="margin: 0;">Click "🔁 Resend" on any campaign to re-engage bounced recipients or unconverted leads.</span>
            </div>

            <div class="wa-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Product</th>
                            <th>Audience</th>
                            <th>Created</th>
                            <th>Format</th>
                            <th>Recipients</th>
                            <th>Accepted</th>
                            <th>Failed</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($campaigns as $campaign)
                            <tr>
                                <td>
                                    <strong>{{ $campaign->name }}</strong>
                                    @if ($campaign->parentCampaign)
                                        <div style="font-size: 11px; color: var(--muted);">Resend of: {{ $campaign->parentCampaign->name }}</div>
                                    @endif
                                    @if ($campaign->resend_count > 0)
                                        <span class="mkt-badge converted" style="font-size: 10px; margin-top: 2px;">Resent {{ $campaign->resend_count }}x</span>
                                    @endif
                                    @if ($campaign->ai_generated)
                                        <span class="mkt-badge product" style="font-size: 10px; margin-top: 2px;">✨ AI Crafted</span>
                                    @endif
                                    <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">{{ $campaign->creator?->name ?? 'System/Cron' }}</div>
                                </td>
                                <td>
                                    @if ($campaign->target_product)
                                        <span class="mkt-badge product">{{ $campaign->target_product }}</span>
                                    @else
                                        <span style="color: var(--muted); font-size: 12px;">General</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size: 12px; font-weight: 600; text-transform: capitalize;">
                                        {{ str_replace('_', ' ', $campaign->audience_segment ?? 'all') }}
                                    </span>
                                </td>
                                <td>{{ $campaign->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $campaign->message_type === 'template' ? $campaign->template_name : 'Rich Text' }}</td>
                                <td><strong>{{ $campaign->recipients_count }}</strong></td>
                                <td style="color: #166534; font-weight: 700;">{{ $campaign->accepted_count }}</td>
                                <td style="color: {{ $campaign->failed_count > 0 ? '#dc2626' : 'var(--muted)' }}; font-weight: 700;">
                                    {{ $campaign->failed_count }}
                                </td>
                                <td>
                                    <span class="wa-status {{ $campaign->status }}">{{ $campaign->status }}</span>
                                </td>
                                <td>
                                    <button type="button" class="button" style="width: auto; padding: 6px 12px; font-size: 12px; background: #fff; color: var(--ink); border: 1px solid var(--line); display: inline-flex; align-items: center; gap: 5px;" onclick="openResendModal('{{ $campaign->id }}', '{{ addslashes($campaign->name) }}', '{{ addslashes($campaign->target_product ?? '') }}', {{ $campaign->failed_count }}, {{ $campaign->recipients_count }}, '{{ addslashes($campaign->message_text ?? '') }}')">
                                        🔁 Resend
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align: center; color: var(--muted); padding: 24px;">No campaigns sent yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 14px;">{{ $campaigns->links() }}</div>
        </div>

        <!-- Recipient Send History Log -->
        <div class="card">
            <h2 style="font-size: 19px; margin-bottom: 14px;">Recipient Delivery Audit Log</h2>
            <div class="wa-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Campaign</th>
                            <th>Product</th>
                            <th>Recipient</th>
                            <th>Phone</th>
                            <th>Resend #</th>
                            <th>Status / Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($deliveries as $delivery)
                            <tr>
                                <td>{{ $delivery->processed_at?->format('Y-m-d H:i') ?? $delivery->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $delivery->campaign?->name ?? 'Deleted campaign' }}</td>
                                <td>{{ $delivery->campaign?->target_product ?? '—' }}</td>
                                <td>
                                    <strong>{{ $delivery->recipient_name ?? 'Contact' }}</strong>
                                    @if ($delivery->lead_id)
                                        <div style="font-size: 10px; color: var(--green); font-weight: 700;">Marketing Lead</div>
                                    @endif
                                </td>
                                <td><code>{{ $delivery->recipient_phone }}</code></td>
                                <td>
                                    @if ($delivery->resend_attempt > 0)
                                        <span class="mkt-badge converted">Att #{{ $delivery->resend_attempt + 1 }}</span>
                                    @else
                                        <span style="color: var(--muted); font-size: 11px;">Initial</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="wa-status {{ $delivery->status }}">{{ $delivery->status }}</span>
                                    @if ($delivery->error_message)
                                        <div style="max-width: 260px; color: var(--danger); font-size: 11px; margin-top: 4px;">
                                            {{ $delivery->error_message }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align: center; color: var(--muted); padding: 24px;">No delivery logs yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 14px;">{{ $deliveries->links() }}</div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- TAB 2: LEADS & CUSTOMER CONVERSION PIPELINE               --}}
    {{-- ======================================================== --}}
    @if ($activeTab === 'leads')
        <!-- Metric Cards -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="lbl">Total Tracked Contacts</div>
                <div class="val">{{ $leadStats['total'] ?? 0 }}</div>
            </div>
            <div class="stat-card" style="border-left: 3px solid #f59e0b;">
                <div class="lbl" style="color: #b45309;">Unconverted Leads</div>
                <div class="val" style="color: #b45309;">{{ $leadStats['leads'] ?? 0 }}</div>
                <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Nurture with demo & follow-up</div>
            </div>
            <div class="stat-card" style="border-left: 3px solid #16a34a;">
                <div class="lbl" style="color: #15803d;">Converted Customers</div>
                <div class="val" style="color: #15803d;">{{ $leadStats['converted'] ?? 0 }}</div>
                <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Target for new product awareness</div>
            </div>
            <div class="stat-card">
                <div class="lbl">Lost / Dormant</div>
                <div class="val" style="color: #64748b;">{{ $leadStats['lost'] ?? 0 }}</div>
                <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Re-engagement pool</div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h2 style="font-size: 19px; margin: 0;">Customer & Lead Records</h2>
                    <div style="display: flex; gap: 6px;">
                        <a href="{{ route('admin.super.whatsapp-campaigns', ['tab' => 'leads']) }}" class="button" style="width: auto; padding: 4px 10px; font-size: 12px; background: {{ !request('lead_status') ? 'var(--green)' : '#f1f5f9' }}; color: {{ !request('lead_status') ? '#fff' : 'var(--ink)' }};">All</a>
                        <a href="{{ route('admin.super.whatsapp-campaigns', ['tab' => 'leads', 'lead_status' => 'lead']) }}" class="button" style="width: auto; padding: 4px 10px; font-size: 12px; background: {{ request('lead_status') === 'lead' ? '#d97706' : '#f1f5f9' }}; color: {{ request('lead_status') === 'lead' ? '#fff' : 'var(--ink)' }};">Leads Only</a>
                        <a href="{{ route('admin.super.whatsapp-campaigns', ['tab' => 'leads', 'lead_status' => 'converted']) }}" class="button" style="width: auto; padding: 4px 10px; font-size: 12px; background: {{ request('lead_status') === 'converted' ? 'var(--green)' : '#f1f5f9' }}; color: {{ request('lead_status') === 'converted' ? '#fff' : 'var(--ink)' }};">Converted Only</a>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <form method="GET" action="{{ route('admin.super.whatsapp-campaigns') }}" style="display: flex; gap: 6px;">
                        <input type="hidden" name="tab" value="leads">
                        <input type="text" name="lead_search" value="{{ request('lead_search') }}" placeholder="Search name, phone, email..." style="padding: 7px 12px; border: 1px solid var(--line); border-radius: 6px; font-size: 13px;">
                        <button type="submit" class="button" style="width: auto; padding: 7px 12px; font-size: 13px;">Search</button>
                    </form>
                    <button type="button" class="button" style="width: auto; background: var(--green); white-space: nowrap;" onclick="openAddLeadModal()">
                        + Add Lead / Customer
                    </button>
                </div>
            </div>

            <div class="wa-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Contact / Company</th>
                            <th>WhatsApp / Phone</th>
                            <th>Status (Click to Toggle)</th>
                            <th>Target Product</th>
                            <th>Last Contacted</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            <tr>
                                <td>
                                    <strong>{{ $lead->name }}</strong>
                                    @if ($lead->company_name)
                                        <div style="font-size: 12px; color: var(--muted);">{{ $lead->company_name }}</div>
                                    @endif
                                    @if ($lead->email)
                                        <div style="font-size: 11px; color: var(--muted);">{{ $lead->email }}</div>
                                    @endif
                                </td>
                                <td>
                                    <code>{{ $lead->phone }}</code>
                                </td>
                                <td>
                                    @if ($lead->status === 'lead')
                                        <form method="POST" action="{{ route('admin.super.marketing.leads.status', $lead->id) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="converted">
                                            <button type="submit" title="Click to convert this lead to paying customer" class="button" style="width: auto; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 4px 8px; font-size: 11px; font-weight: 700;">
                                                Unconverted Lead ➔ [Mark Converted]
                                            </button>
                                        </form>
                                    @elseif ($lead->status === 'converted')
                                        <form method="POST" action="{{ route('admin.super.marketing.leads.status', $lead->id) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="lead">
                                            <button type="submit" title="Click to switch back to lead" class="button" style="width: auto; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; padding: 4px 8px; font-size: 11px; font-weight: 700;">
                                                ✓ Converted Customer ➔ [Set Lead]
                                            </button>
                                        </form>
                                    @else
                                        <span class="mkt-badge lost">{{ $lead->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($lead->target_product)
                                        <span class="mkt-badge product">{{ $lead->target_product }}</span>
                                    @else
                                        <span style="color: var(--muted); font-size: 12px;">Not Specified</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($lead->last_contacted_at)
                                        <div>{{ $lead->last_contacted_at->format('Y-m-d') }}</div>
                                        <div style="font-size: 11px; color: var(--muted);">{{ $lead->last_campaign_name ?? 'Outreach' }}</div>
                                    @else
                                        <span style="color: var(--muted); font-size: 12px;">Never Contacted</span>
                                    @endif
                                </td>
                                <td style="max-width: 200px; font-size: 12px; color: var(--muted);">
                                    {{ $lead->conversion_notes ?? '—' }}
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.super.marketing.leads.destroy', $lead->id) }}" onsubmit="return confirm('Delete this contact?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button" style="width: auto; background: none; border: 0; color: #dc2626; padding: 4px; font-size: 12px; box-shadow: none;">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align: center; color: var(--muted); padding: 24px;">No contacts registered in this view.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 14px;">{{ $leads->links() }}</div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- TAB 3: WEEKLY AI CRON MONITOR & AUTOMATION               --}}
    {{-- ======================================================== --}}
    @if ($activeTab === 'cron')
        <!-- Cron Status Information Banner -->
        <div class="card" style="margin-bottom: 24px; border-left: 4px solid var(--green);">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h2 style="font-size: 18px; margin: 0 0 6px 0; color: var(--ink);">Automated Weekly Marketing Campaign Engine</h2>
                    <p style="margin: 0; font-size: 13.5px; color: var(--muted); line-height: 1.6; max-width: 720px;">
                        The system automatically executes weekly on <strong>Monday at 09:00 IST</strong> via <code>php artisan aprilo:weekly-marketing-campaign</code>.
                        Prior to sending, it consults our AI strategist to evaluate live database metrics (unconverted leads vs paying customers), selects the highest-leverage Aprilo product, writes persuasive copy, and safely schedules or dispatches messages.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.super.marketing.trigger-cron') }}">
                    @csrf
                    <button type="submit" class="button" style="width: auto; background: var(--green); font-size: 13px; font-weight: 700; padding: 10px 18px; display: inline-flex; align-items: center; gap: 8px;" onclick="return confirm('Initiate automated weekly AI marketing campaign right now? AI will formulate today\'s strategy and dispatch messages.');">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                        ▶ Run Automated Campaign Now (Test / Trigger)
                    </button>
                </form>
            </div>
        </div>

        <!-- Cron Execution History Table -->
        <div class="card">
            <h2 style="font-size: 19px; margin-bottom: 14px;">Automated Cron Execution & Status Log</h2>
            <div class="wa-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Execution Time</th>
                            <th>Status</th>
                            <th>Target Product</th>
                            <th>Audience Segment</th>
                            <th>Recipients</th>
                            <th>Duration</th>
                            <th>AI Strategy & Copy</th>
                            <th>Error / Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cronLogs as $log)
                            <tr>
                                <td>
                                    <strong>{{ $log->executed_at?->format('Y-m-d H:i') ?? $log->created_at->format('Y-m-d H:i') }}</strong>
                                    <div style="font-size: 11px; color: var(--muted);">{{ $log->command_name }}</div>
                                </td>
                                <td>
                                    <span class="mkt-badge cron-{{ $log->status }}">
                                        {{ strtoupper($log->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="mkt-badge product">{{ $log->target_product ?? 'Automated' }}</span>
                                </td>
                                <td>
                                    <span style="font-size: 12px; font-weight: 600; text-transform: capitalize;">
                                        {{ str_replace('_', ' ', $log->segment_targeted ?? 'auto') }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $log->recipients_count }}</strong>
                                    <div style="font-size: 11px; color: var(--green);">Accepted: {{ $log->accepted_count }}</div>
                                    @if ($log->failed_count > 0)
                                        <div style="font-size: 11px; color: #dc2626;">Failed: {{ $log->failed_count }}</div>
                                    @endif
                                </td>
                                <td>{{ $log->execution_time_seconds }}s</td>
                                <td style="max-width: 280px;">
                                    <div style="font-size: 12px; font-weight: 600; color: var(--ink);">
                                        {{ $log->ai_strategy_summary ?? 'Strategy analyzed by Gemini AI' }}
                                    </div>
                                    @if ($log->ai_generated_copy)
                                        <details style="margin-top: 4px; font-size: 11px; color: var(--muted); cursor: pointer;">
                                            <summary>View AI message copy</summary>
                                            <div style="margin-top: 6px; padding: 8px; background: #f8fafc; border: 1px solid var(--line); border-radius: 6px; white-space: pre-wrap; font-family: monospace;">{{ $log->ai_generated_copy }}</div>
                                        </details>
                                    @endif
                                </td>
                                <td>
                                    @if ($log->error_message)
                                        <span style="color: #dc2626; font-size: 11px;">{{ $log->error_message }}</span>
                                    @else
                                        <span style="color: #166534; font-size: 12px;">✓ Completed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" style="text-align: center; color: var(--muted); padding: 24px;">No cron executions logged yet. Use "Run Automated Campaign Now" above to run the first test.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 14px;">{{ $cronLogs->links() }}</div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- MODAL 1: RESEND CAMPAIGN MODAL                            --}}
    {{-- ======================================================== --}}
    <div id="resendModal" class="modal-overlay" style="display: none;">
        <div class="modal-card">
            <div class="modal-header">
                <h3>🔁 Resend Campaign</h3>
                <button type="button" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted);" onclick="closeResendModal()">&times;</button>
            </div>
            <form id="resendForm" method="POST" action="">
                @csrf
                <div class="modal-body wa-form">
                    <div class="wa-note info">
                        Campaign: <strong id="resendCampaignName"></strong><br>
                        Product: <span id="resendTargetProduct" class="mkt-badge product"></span>
                    </div>

                    <label>Select Resend Target
                        <select name="resend_target" id="resendTargetSelect" required>
                            <option value="failed_only" id="resendFailedOption">
                                ❌ Failed recipients only (Recover failed / bounced sends)
                            </option>
                            <option value="unconverted">
                                🎯 Unconverted leads only (Nudge prospects who have not converted yet)
                            </option>
                            <option value="all">
                                👥 All original recipients (<span id="resendTotalCount"></span> contacts)
                            </option>
                        </select>
                    </label>

                    <label>Updated Message Copy (Optional)
                        <div style="font-size: 11px; color: var(--muted); margin-bottom: 4px;">
                            Leave unchanged to dispatch the original copy, or refine the message hook.
                        </div>
                        <textarea name="custom_message" id="resendCustomMessage" placeholder="Optional updated copy..."></textarea>
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button" style="width: auto; background: #fff; color: var(--ink); border: 1px solid var(--line);" onclick="closeResendModal()">Cancel</button>
                    <button type="submit" class="button" style="width: auto; background: var(--green);">Dispatch Resend Campaign</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL 2: AI COPYWRITER GENERATOR MODAL                   --}}
    {{-- ======================================================== --}}
    <div id="aiGeneratorModal" class="modal-overlay" style="display: none;">
        <div class="modal-card" style="max-width: 680px;">
            <div class="modal-header">
                <h3>✨ Aprilo AI Campaign Copywriter</h3>
                <button type="button" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted);" onclick="closeAiGeneratorModal()">&times;</button>
            </div>
            <div class="modal-body wa-form">
                <div class="wa-grid">
                    <label>Select Product
                        <select id="aiProductSelect">
                            @foreach ($supportedProducts as $prod)
                                <option value="{{ $prod }}">{{ $prod }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>Audience Segment
                        <select id="aiAudienceSelect">
                            <option value="unconverted_leads">Unconverted Leads (Nurture & Demo Offer)</option>
                            <option value="converted_customers">Converted Customers (Cross-Sell / Product Expansion)</option>
                            <option value="all">General Announcement</option>
                        </select>
                    </label>
                </div>

                <div class="wa-grid">
                    <label>Campaign Goal & Hook
                        <select id="aiGoalSelect">
                            <option value="lead_followup">Solve Objection & Offer Demo</option>
                            <option value="discount_offer">Limited-Time Onboarding Discount</option>
                            <option value="feature_announcement">Product Upgrade / Feature Launch</option>
                            <option value="roi_pitch">ROI & Operational Efficiency Pitch</option>
                        </select>
                    </label>

                    <label>Special Discount Code (Optional)
                        <input type="text" id="aiDiscountCode" placeholder="e.g. APRILO20 or DEMO100">
                    </label>
                </div>

                <label>Custom Key Points or Tone (Optional)
                    <input type="text" id="aiCustomNotes" placeholder="e.g. Emphasize GST invoice download and offline barcode billing">
                </label>

                <button type="button" id="aiGenerateBtn" class="button" style="background: #16a34a;" onclick="generateAiCopy()">
                    ✨ Generate High-Converting Copy
                </button>

                <!-- AI Output Preview Area -->
                <div id="aiOutputArea" style="display: none; margin-top: 10px;">
                    <div style="font-size: 13px; font-weight: 700; color: var(--ink); margin-bottom: 6px;">
                        Generated WhatsApp Copy Preview:
                    </div>
                    <div id="aiStrategyNotes" class="wa-note ai" style="margin-bottom: 10px;"></div>
                    <textarea id="aiGeneratedMessage" style="min-height: 180px; font-family: monospace; font-size: 12.5px;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="button" style="width: auto; background: #fff; color: var(--ink); border: 1px solid var(--line);" onclick="closeAiGeneratorModal()">Close</button>
                <button type="button" id="aiApplyBtn" class="button" style="width: auto; background: var(--green); display: none;" onclick="applyAiCopy()">
                    ✓ Use This Copy in Campaign Form
                </button>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL 3: ADD LEAD / CUSTOMER MODAL                        --}}
    {{-- ======================================================== --}}
    <div id="addLeadModal" class="modal-overlay" style="display: none;">
        <div class="modal-card">
            <div class="modal-header">
                <h3>+ Add Lead or Customer</h3>
                <button type="button" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted);" onclick="closeAddLeadModal()">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.super.marketing.leads.store') }}">
                @csrf
                <div class="modal-body wa-form">
                    <div class="wa-grid">
                        <label>Contact Full Name
                            <input name="name" required placeholder="e.g. Rajesh Kumar">
                        </label>
                        <label>WhatsApp Phone Number
                            <input name="phone" required placeholder="e.g. 919876543210">
                            <span class="help" style="margin:0;">Include country code without '+' or spaces.</span>
                        </label>
                    </div>

                    <div class="wa-grid">
                        <label>Email Address (Optional)
                            <input name="email" type="email" placeholder="e.g. rajesh@company.com">
                        </label>
                        <label>Company / Organization Name
                            <input name="company_name" placeholder="e.g. Kumar Textiles & Retail">
                        </label>
                    </div>

                    <div class="wa-grid">
                        <label>Status
                            <select name="status" required>
                                <option value="lead">Lead (Unconverted / Inquired)</option>
                                <option value="converted">Converted (Paying Customer)</option>
                                <option value="lost">Lost / Uninterested</option>
                            </select>
                        </label>
                        <label>Target Product
                            <select name="target_product">
                                <option value="">-- Generic / Undecided --</option>
                                @foreach ($supportedProducts as $prod)
                                    <option value="{{ $prod }}">{{ $prod }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <label>Conversion Notes / Interaction History
                        <textarea name="conversion_notes" placeholder="e.g. Inquired about barcode billing & POS inventory sync; requested demo."></textarea>
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button" style="width: auto; background: #fff; color: var(--ink); border: 1px solid var(--line);" onclick="closeAddLeadModal()">Cancel</button>
                    <button type="submit" class="button" style="width: auto; background: var(--green);">Save Contact</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- CLIENT JAVASCRIPT LOGIC                                   --}}
    {{-- ======================================================== --}}
    <script>
        // Form & Recipient Mode Switching
        const recipientMode = document.getElementById('recipientMode');
        const messageType = document.getElementById('messageType');
        const deliveryMode = document.getElementById('deliveryMode');
        const templateSelector = document.getElementById('templateSelector');
        const templateName = document.getElementById('templateName');
        const templateLanguage = document.getElementById('templateLanguage');
        const templateStatus = document.getElementById('templateStatus');
        const templatePreview = document.getElementById('templatePreview');
        const templateParameterHint = document.getElementById('templateParameterHint');
        const submitCampaign = document.getElementById('submitCampaign');
        const apiReady = document.getElementById('campaignForm')?.dataset.apiReady === '1';

        let templatesLoaded = false;
        let templatesLoading = false;

        const setVisible = (element, visible) => {
            if (!element) return;
            element.hidden = !visible;
            element.querySelectorAll('input, select, textarea').forEach((input) => {
                input.disabled = !visible || input.options?.length === 0 || (input.id === 'templateSelector' && input.options.length <= 1);
                input.required = visible && input.name !== 'template_parameters' && input.name !== 'recipient_phone';
            });
        };

        const updateForm = () => {
            if (!recipientMode || !messageType || !deliveryMode) return;
            const instant = deliveryMode.value === 'instant';
            if (instant) recipientMode.value = 'manual';

            // Disable multi-user modes if instant
            const usersOpt = recipientMode.querySelector('option[value="users"]');
            const leadsOpt = recipientMode.querySelector('option[value="leads"]');
            const convertedOpt = recipientMode.querySelector('option[value="converted"]');
            const allLeadsOpt = recipientMode.querySelector('option[value="all_leads"]');
            if (usersOpt) usersOpt.disabled = instant;
            if (leadsOpt) leadsOpt.disabled = instant;
            if (convertedOpt) convertedOpt.disabled = instant;
            if (allLeadsOpt) allLeadsOpt.disabled = instant;

            const mode = recipientMode.value;
            const isManual = mode === 'manual';
            const isUsers = mode === 'users';
            const isTemplate = messageType.value === 'template';

            setVisible(document.getElementById('userRecipients'), isUsers);
            setVisible(document.getElementById('manualRecipient'), isManual);
            setVisible(document.getElementById('templateFields'), isTemplate);
            setVisible(document.getElementById('templateParameters'), isTemplate);
            setVisible(document.getElementById('textMessage'), !isTemplate);

            if (submitCampaign) {
                submitCampaign.textContent = instant ? 'Send Now' : 'Queue Campaign';
                submitCampaign.disabled = !apiReady || (isTemplate && !templateName.value);
            }
        };

        const updateSelectedTemplate = () => {
            if (!templateSelector) return;
            const selected = templateSelector.selectedOptions[0];
            templateName.value = selected?.dataset.name || '';
            templateLanguage.value = selected?.dataset.language || '';
            templatePreview.textContent = selected?.dataset.body || 'Select a template to preview its approved body.';
            const parameterCount = Number(selected?.dataset.parameterCount || 0);
            templateParameterHint.textContent = parameterCount
                ? `This template expects ${parameterCount} body parameter(s), one per line.`
                : 'This template has no body parameters.';
            updateForm();
        };

        const loadApprovedTemplates = async () => {
            if (!templateSelector || templatesLoaded || templatesLoading || messageType?.value !== 'template') return;

            templatesLoading = true;
            templateSelector.disabled = true;
            templateStatus.textContent = 'Loading approved templates from Meta...';
            try {
                const response = await fetch(templateSelector.dataset.templatesUrl, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });

                if (response.redirected || response.url.includes('/admin/login')) {
                    throw new Error('Your admin session may have expired. Refresh the page.');
                }

                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Could not load WhatsApp templates.');
                if (!Array.isArray(result.templates)) throw new Error('Template lookup returned an unexpected response.');

                const placeholderOption = document.createElement('option');
                placeholderOption.textContent = 'Select an approved template';
                templateSelector.replaceChildren(placeholderOption);

                result.templates.forEach((template) => {
                    const option = document.createElement('option');
                    option.textContent = `${template.name} (${template.language}) · ${template.category || 'Template'}`;
                    option.value = `${template.name}:${template.language}`;
                    option.dataset.name = template.name;
                    option.dataset.language = template.language;
                    option.dataset.body = template.body;
                    option.dataset.parameterCount = template.parameter_count;
                    templateSelector.add(option);
                });

                templateSelector.disabled = result.templates.length === 0;
                templateStatus.textContent = result.templates.length
                    ? `${result.templates.length} approved template(s) available.`
                    : 'No approved templates were found.';
                templatesLoaded = true;
                updateSelectedTemplate();
            } catch (error) {
                templateName.value = '';
                templateLanguage.value = '';
                templateStatus.textContent = error.message;
                templatePreview.textContent = 'Check Meta API configuration.';
                templateSelector.disabled = true;
                updateForm();
            } finally {
                templatesLoading = false;
            }
        };

        if (recipientMode) recipientMode.addEventListener('change', updateForm);
        if (deliveryMode) deliveryMode.addEventListener('change', updateForm);
        if (messageType) messageType.addEventListener('change', () => {
            updateForm();
            loadApprovedTemplates();
        });
        if (templateSelector) templateSelector.addEventListener('change', updateSelectedTemplate);

        // Run initial form setup on page load
        updateForm();

        // ----------------------------------------------------
        // Resend Campaign Modal Functions
        // ----------------------------------------------------
        function openResendModal(campaignId, campaignName, targetProduct, failedCount, totalCount, messageText) {
            const modal = document.getElementById('resendModal');
            const form = document.getElementById('resendForm');
            form.action = `/admin/super/whatsapp-campaigns/${campaignId}/resend`;

            document.getElementById('resendCampaignName').textContent = campaignName;
            document.getElementById('resendTargetProduct').textContent = targetProduct || 'Generic';
            document.getElementById('resendFailedOption').textContent = `❌ Failed recipients only (${failedCount} failed of ${totalCount})`;
            document.getElementById('resendTotalCount').textContent = totalCount;
            document.getElementById('resendCustomMessage').value = messageText || '';

            modal.style.display = 'flex';
        }

        function closeResendModal() {
            document.getElementById('resendModal').style.display = 'none';
        }

        // ----------------------------------------------------
        // AI Generator Modal & AJAX
        // ----------------------------------------------------
        function openAiGeneratorModal() {
            const currentProduct = document.getElementById('targetProductSelect')?.value;
            if (currentProduct) {
                document.getElementById('aiProductSelect').value = currentProduct;
            }
            document.getElementById('aiGeneratorModal').style.display = 'flex';
        }

        function closeAiGeneratorModal() {
            document.getElementById('aiGeneratorModal').style.display = 'none';
        }

        let lastGeneratedCopy = null;

        async function generateAiCopy() {
            const btn = document.getElementById('aiGenerateBtn');
            const product = document.getElementById('aiProductSelect').value;
            const audience = document.getElementById('aiAudienceSelect').value;
            const goal = document.getElementById('aiGoalSelect').value;
            const discount = document.getElementById('aiDiscountCode').value;
            const notes = document.getElementById('aiCustomNotes').value;

            btn.disabled = true;
            btn.textContent = '⏳ AI is crafting copy...';

            try {
                const response = await fetch("{{ route('admin.super.marketing.ai-generate') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        product_name: product,
                        target_audience: audience,
                        campaign_goal: goal,
                        discount_code: discount,
                        custom_notes: notes,
                    })
                });

                const res = await response.json();
                if (!response.ok || res.status !== 'success') {
                    throw new Error(res.message || 'Generation failed.');
                }

                lastGeneratedCopy = res.data;
                document.getElementById('aiStrategyNotes').textContent = res.data.strategy_notes;
                document.getElementById('aiGeneratedMessage').value = res.data.whatsapp_message;
                document.getElementById('aiOutputArea').style.display = 'block';
                document.getElementById('aiApplyBtn').style.display = 'inline-block';
            } catch (err) {
                alert('Could not generate AI copy: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.textContent = '✨ Generate High-Converting Copy';
            }
        }

        function applyAiCopy() {
            if (!lastGeneratedCopy) return;
            const nameInput = document.getElementById('campaignNameInput');
            const msgInput = document.getElementById('campaignMessageText');
            const prodSelect = document.getElementById('targetProductSelect');
            const aiGenField = document.getElementById('aiGeneratedField');
            const aiNotesField = document.getElementById('aiPromptNotesField');
            const messageType = document.getElementById('messageType');

            if (nameInput) nameInput.value = lastGeneratedCopy.campaign_name;
            if (msgInput) msgInput.value = document.getElementById('aiGeneratedMessage').value;
            if (prodSelect) prodSelect.value = document.getElementById('aiProductSelect').value;
            if (aiGenField) aiGenField.value = '1';
            if (aiNotesField) aiNotesField.value = lastGeneratedCopy.strategy_notes;
            if (messageType) {
                messageType.value = 'text';
                updateForm();
            }

            closeAiGeneratorModal();
            msgInput?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // ----------------------------------------------------
        // Add Lead Modal
        // ----------------------------------------------------
        function openAddLeadModal() {
            document.getElementById('addLeadModal').style.display = 'flex';
        }

        function closeAddLeadModal() {
            document.getElementById('addLeadModal').style.display = 'none';
        }

        // Close modals on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeResendModal();
                closeAiGeneratorModal();
                closeAddLeadModal();
            }
        });
    </script>
@endsection
