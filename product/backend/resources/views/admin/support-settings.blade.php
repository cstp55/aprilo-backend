@extends('admin.layout')

@section('title', 'Aprilo Support - Desk & SLA Settings')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow font-bold" style="color: var(--green); text-transform: uppercase; letter-spacing: 0.1em; font-size: 11px;">Aprilo Support Product</div>
            <h1 style="display: flex; align-items: center; gap: 12px; margin-top: 4px;">
                Support Desk & SLA Configuration
                <span class="pill" style="font-size: 11px; background: rgba(34, 197, 94, 0.1); color: var(--green); border: 1px solid rgba(34, 197, 94, 0.2); text-transform: uppercase; padding: 2px 8px; border-radius: 9999px;">
                    Active Product
                </span>
            </h1>
            <p class="help" style="color: var(--muted); margin-top: 4px; font-size: 14px;">
                Configure Service Level Agreements (SLAs), response thresholds, and omnichannel routing for IT & SaaS customer tickets.
            </p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start; margin-top: 24px;">
        <!-- Left: SLA Policies Form -->
        <div style="min-width: 0;">
            <form class="card" method="POST" action="{{ route('admin.support.settings.update') }}" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 24px;">
                @csrf
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: var(--ink); margin: 0 0 6px;">SLA Priority Targets</h3>
                    <p style="font-size: 13px; color: var(--muted); margin: 0;">Define targeted first-response and full-resolution deadlines by ticket priority level.</p>
                </div>

                <!-- Urgent Priority -->
                <div style="padding: 16px; background: rgba(239, 68, 68, 0.04); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 8px; margin-bottom: 16px;">
                    <div style="font-weight: 700; color: #dc2626; font-size: 14px; margin-bottom: 8px;">🚨 Urgent Priority (System Down / P1)</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">First Response (minutes)</label>
                            <input type="number" name="urgent_response_time" value="{{ old('urgent_response_time', $slaPolicies['urgent']->response_time_minutes ?? 60) }}" min="5" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Resolution Target (minutes)</label>
                            <input type="number" name="urgent_resolution_time" value="{{ old('urgent_resolution_time', $slaPolicies['urgent']->resolution_time_minutes ?? 240) }}" min="10" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                        </div>
                    </div>
                </div>

                <!-- High Priority -->
                <div style="padding: 16px; background: rgba(249, 115, 22, 0.04); border: 1px solid rgba(249, 115, 22, 0.2); border-radius: 8px; margin-bottom: 16px;">
                    <div style="font-weight: 700; color: #ea580c; font-size: 14px; margin-bottom: 8px;">⚠️ High Priority (Major Feature Failure / P2)</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">First Response (minutes)</label>
                            <input type="number" name="high_response_time" value="{{ old('high_response_time', $slaPolicies['high']->response_time_minutes ?? 120) }}" min="10" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Resolution Target (minutes)</label>
                            <input type="number" name="high_resolution_time" value="{{ old('high_resolution_time', $slaPolicies['high']->resolution_time_minutes ?? 480) }}" min="30" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                        </div>
                    </div>
                </div>

                <!-- Normal Priority -->
                <div style="padding: 16px; background: rgba(59, 130, 246, 0.04); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 8px; margin-bottom: 16px;">
                    <div style="font-weight: 700; color: #2563eb; font-size: 14px; margin-bottom: 8px;">ℹ️ Normal Priority (Standard Inquiry / P3)</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">First Response (minutes)</label>
                            <input type="number" name="normal_response_time" value="{{ old('normal_response_time', $slaPolicies['normal']->response_time_minutes ?? 240) }}" min="15" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Resolution Target (minutes)</label>
                            <input type="number" name="normal_resolution_time" value="{{ old('normal_resolution_time', $slaPolicies['normal']->resolution_time_minutes ?? 1440) }}" min="60" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                        </div>
                    </div>
                </div>

                <!-- Low Priority -->
                <div style="padding: 16px; background: rgba(100, 116, 139, 0.04); border: 1px solid rgba(100, 116, 139, 0.2); border-radius: 8px; margin-bottom: 24px;">
                    <div style="font-weight: 700; color: #475569; font-size: 14px; margin-bottom: 8px;">📋 Low Priority (General Question / P4)</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">First Response (minutes)</label>
                            <input type="number" name="low_response_time" value="{{ old('low_response_time', $slaPolicies['low']->response_time_minutes ?? 480) }}" min="30" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Resolution Target (minutes)</label>
                            <input type="number" name="low_resolution_time" value="{{ old('low_resolution_time', $slaPolicies['low']->resolution_time_minutes ?? 2880) }}" min="120" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                        </div>
                    </div>
                </div>

                <button type="submit" class="button" style="width: 100%;">Save SLA Policies</button>
            </form>
        </div>

        <!-- Right Sidebar: Workspace Links & Channels -->
        <div>
            <!-- Dedicated App Card -->
            <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--green); letter-spacing: 0.08em; margin-bottom: 6px;">Dedicated Frontend App</div>
                <h4 style="margin: 0 0 8px; font-size: 15px; font-weight: 700; color: var(--ink);">Aprilo Support Workspace</h4>
                <p style="font-size: 12.5px; color: var(--muted); margin: 0 0 16px; line-height: 1.5;">
                    Support engineers access the unified inbox, ticket review queues, and AI copilot drafting at:
                </p>
                <div style="padding: 10px; background: #f8fafc; border: 1px solid var(--line); border-radius: 6px; font-family: monospace; font-size: 12px; word-break: break-all; margin-bottom: 14px;">
                    https://support.apriloinfotech.com
                </div>
                <div style="font-size: 11.5px; color: var(--muted);">
                    Authenticated agents can log in directly using organization credentials via Sanctum API tokens or SSO.
                </div>
            </div>

            <!-- Enabled Channels -->
            <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px;">
                <h4 style="margin: 0 0 14px; font-size: 14px; font-weight: 700; color: var(--ink);">Channel Status</h4>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px;">
                        <span>🌐 Website Widget</span>
                        <span style="color: #16a34a; font-weight: 600;">Active</span>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px;">
                        <span>📱 WhatsApp Cloud API</span>
                        <span style="color: {{ !empty($settings->whatsapp_phone_number_id) ? '#16a34a' : '#64748b' }}; font-weight: 600;">
                            {{ !empty($settings->whatsapp_phone_number_id) ? 'Configured' : 'Inactive' }}
                        </span>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px;">
                        <span>✉️ Support Email</span>
                        <span style="color: {{ ($settings->connect_mail ?? false) ? '#16a34a' : '#64748b' }}; font-weight: 600;">
                            {{ ($settings->connect_mail ?? false) ? 'Active' : 'Disabled' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
