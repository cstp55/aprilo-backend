@extends('admin.layout')

@section('title', 'Aprilo Support - Portal & SLA Settings')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow font-bold" style="color: var(--green); text-transform: uppercase; letter-spacing: 0.1em; font-size: 11px;">Aprilo Support Product</div>
            <h1 style="display: flex; align-items: center; gap: 12px; margin-top: 4px;">
                Support Portal & SLA Configuration
                <span class="pill" style="font-size: 11px; background: rgba(34, 197, 94, 0.1); color: var(--green); border: 1px solid rgba(34, 197, 94, 0.2); text-transform: uppercase; padding: 2px 8px; border-radius: 9999px;">
                    Active Product
                </span>
            </h1>
            <p class="help" style="color: var(--muted); margin-top: 4px; font-size: 14px;">
                @if ($organization)
                    Configure your public customer support portal, branding, authentication policies, categories, and SLA response targets for <strong>{{ $organization->name }}</strong>.
                @else
                    Select an organization below to view and configure its public support portal, branding, categories, and SLA policies.
                @endif
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('admin.support.reports', $organization ? ['organization_id' => $organization->id] : []) }}" class="button" style="background: #fff; color: var(--ink); border: 1px solid var(--line); box-shadow: none; display: inline-flex; align-items: center; gap: 6px;">
                <span>📊</span> Ticket Monitor & Reports
            </a>
            @if ($organization && $portalSettings)
                <a href="{{ route('admin.support.forms', $isSuperAdmin ? ['organization_id' => $organization->id] : []) }}" class="button" style="background: #fff; color: var(--ink); border: 1px solid var(--line); box-shadow: none; display: inline-flex; align-items: center; gap: 6px;">
                    <span>📋</span> Manage Ticket Forms
                </a>
                <a href="{{ $portalSettings->getPublicPortalUrl() }}" target="_blank" class="button" style="display: inline-flex; align-items: center; gap: 6px;">
                    <span>🌐</span> Preview Portal
                </a>
            @endif
        </div>
    </div>

    <!-- Organization Selector Bar -->
    <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 18px 24px; margin-top: 20px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(34, 197, 94, 0.1); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                🏢
            </div>
            <div>
                <div style="font-size: 14px; font-weight: 700; color: var(--ink);">Select Organization</div>
                <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">
                    @if ($isSuperAdmin)
                        Super Administrator: Choose any organization from the platform to configure its support settings.
                    @else
                        Owner: Managing support portal and SLA policies for {{ $organization->name ?? 'your organization' }}.
                    @endif
                </div>
            </div>
        </div>

        <div style="min-width: 320px; flex: 1; max-width: 460px;">
            <form method="GET" action="{{ route('admin.support.settings') }}" id="orgFilterForm" style="margin: 0; padding: 0; border: 0; box-shadow: none; display: flex; gap: 8px;">
                <select name="organization_id" id="organization_id" onchange="this.form.submit()" style="width: 100%; padding: 10px 14px; border: 1px solid var(--line); border-radius: 8px; font-size: 13.5px; font-weight: 600; color: var(--ink); background: #f8fafc; cursor: pointer; outline: none;">
                    <option value="">-- Select Organization --</option>
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

    @if ($organization && $portalSettings)
        <!-- Selected Organization Banner -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 20px; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="pill" style="font-size: 12px; background: rgba(34, 197, 94, 0.12); color: #16a34a; font-weight: 700; border: 1px solid rgba(34, 197, 94, 0.2);">
                    ✓ Active Organization: {{ $organization->name }}
                </span>
                @if ($isSuperAdmin)
                    <span style="font-size: 12px; color: var(--muted);">(Switch organization anytime using the selector above)</span>
                @endif
            </div>
        </div>

        <!-- Main Grid Layout -->
        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start; margin-top: 16px;">
            
            <!-- Left Column: Forms & Settings -->
            <div style="display: flex; flex-direction: column; gap: 24px; min-width: 0;">
                
                <!-- CARD 1: Public URL & Customer Access -->
                <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; color: var(--ink); margin: 0 0 4px;">Public Support Portal URL</h3>
                            <p style="font-size: 13px; color: var(--muted); margin: 0;">This is the public URL where customers of {{ $organization->name }} can submit tickets and view knowledge articles.</p>
                        </div>
                        <span class="pill" style="background: {{ $portalSettings->is_portal_active ? '#ecfdf5' : '#f1f5f9' }}; color: {{ $portalSettings->is_portal_active ? '#16a34a' : '#64748b' }};">
                            {{ $portalSettings->is_portal_active ? 'Active' : 'Disabled' }}
                        </span>
                    </div>

                    <div style="display: flex; gap: 8px; align-items: center; background: #f8fafc; border: 1px solid var(--line); border-radius: 8px; padding: 10px 14px; margin-bottom: 16px;">
                        <span style="font-size: 16px;">🔗</span>
                        <input id="publicPortalUrlInput" type="text" readonly value="{{ $portalSettings->getPublicPortalUrl() }}" style="border: 0; background: transparent; font-family: monospace; font-size: 13px; color: var(--ink); width: 100%; outline: none;">
                        <button type="button" onclick="copyPortalUrl()" class="button" style="width: auto; padding: 6px 14px; font-size: 12px; border-radius: 6px;">
                            Copy URL
                        </button>
                    </div>
                    <div id="copySuccessMsg" style="display: none; font-size: 12px; color: #16a34a; font-weight: 600; margin-top: -8px; margin-bottom: 12px;">
                        ✓ Copied public portal URL to clipboard!
                    </div>
                </div>

                <!-- CARD 2: Support Portal Branding & Customer Authentication -->
                <form class="card" method="POST" action="{{ route('admin.support.portal.update') }}" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 24px;">
                    @csrf
                    <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                    <div style="border-bottom: 1px solid var(--line); padding-bottom: 12px; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; font-weight: 700; color: var(--ink); margin: 0 0 4px;">Portal Branding & Appearance</h3>
                        <p style="font-size: 13px; color: var(--muted); margin: 0;">Customize the header, logo, brand colors, and contact info shown on the customer portal for {{ $organization->name }}.</p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label class="label" style="font-size: 12px; font-weight: 700; color: #475569;">Portal Title / Help Center Name</label>
                            <input type="text" name="portal_title" value="{{ old('portal_title', $portalSettings->portal_title) }}" required style="width: 100%; padding: 9px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                        </div>
                        <div>
                            <label class="label" style="font-size: 12px; font-weight: 700; color: #475569;">Portal URL Slug</label>
                            <div style="display: flex; align-items: center; gap: 4px; margin-top: 4px;">
                                <span style="font-size: 12px; color: var(--muted); font-family: monospace;">/portal/</span>
                                <input type="text" name="portal_slug" value="{{ old('portal_slug', $portalSettings->portal_slug) }}" required style="flex: 1; padding: 9px 12px; border: 1px solid var(--line); border-radius: 6px; font-family: monospace; font-size: 13px;">
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label class="label" style="font-size: 12px; font-weight: 700; color: #475569;">Subheading / Welcome Message</label>
                        <input type="text" name="portal_subheading" value="{{ old('portal_subheading', $portalSettings->portal_subheading) }}" style="width: 100%; padding: 9px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label class="label" style="font-size: 12px; font-weight: 700; color: #475569;">Primary Brand Color</label>
                            <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px;">
                                <input type="color" name="primary_color" value="{{ old('primary_color', $portalSettings->primary_color) }}" style="width: 40px; height: 38px; border: 0; padding: 0; cursor: pointer; border-radius: 4px;">
                                <input type="text" value="{{ old('primary_color', $portalSettings->primary_color) }}" readonly style="flex: 1; padding: 9px 10px; border: 1px solid var(--line); border-radius: 6px; font-family: monospace; font-size: 12px;">
                            </div>
                        </div>
                        <div>
                            <label class="label" style="font-size: 12px; font-weight: 700; color: #475569;">Secondary Accent Color</label>
                            <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px;">
                                <input type="color" name="secondary_color" value="{{ old('secondary_color', $portalSettings->secondary_color ?: '#0F172A') }}" style="width: 40px; height: 38px; border: 0; padding: 0; cursor: pointer; border-radius: 4px;">
                                <input type="text" value="{{ old('secondary_color', $portalSettings->secondary_color ?: '#0F172A') }}" readonly style="flex: 1; padding: 9px 10px; border: 1px solid var(--line); border-radius: 6px; font-family: monospace; font-size: 12px;">
                            </div>
                        </div>
                        <div>
                            <label class="label" style="font-size: 12px; font-weight: 700; color: #475569;">Support Contact Email</label>
                            <input type="email" name="support_email" value="{{ old('support_email', $portalSettings->support_email) }}" placeholder="support@company.com" style="width: 100%; padding: 9px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                        <div>
                            <label class="label" style="font-size: 12px; font-weight: 700; color: #475569;">Brand Logo URL</label>
                            <input type="url" name="logo_url" value="{{ old('logo_url', $portalSettings->logo_url) }}" placeholder="https://example.com/logo.png" style="width: 100%; padding: 9px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                        </div>
                        <div>
                            <label class="label" style="font-size: 12px; font-weight: 700; color: #475569;">Default Intake Ticket Form</label>
                            <select name="default_form_id" style="width: 100%; padding: 9px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                                <option value="">-- Standard Built-in Intake Form --</option>
                                @foreach ($forms as $form)
                                    <option value="{{ $form->id }}" {{ $portalSettings->default_form_id === $form->id ? 'selected' : '' }}>
                                        {{ $form->name }} (v{{ $form->version }} · {{ ucfirst($form->status) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Customer Authentication & Tracking Options -->
                    <div style="border-top: 1px solid var(--line); padding-top: 16px; margin-bottom: 24px;">
                        <h4 style="font-size: 14px; font-weight: 700; color: var(--ink); margin: 0 0 12px;">Customer Intake & Authentication Rules</h4>
                        <div style="display: grid; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="allow_anonymous_tickets" value="1" {{ $portalSettings->allow_anonymous_tickets ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--green);">
                                <div>
                                    <span style="font-size: 13px; font-weight: 600; color: var(--ink);">Allow anonymous submissions</span>
                                    <span style="display: block; font-size: 12px; color: var(--muted);">Customers can submit tickets without logging in or verifying email up-front</span>
                                </div>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="require_email_verification" value="1" {{ $portalSettings->require_email_verification ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--green);">
                                <div>
                                    <span style="font-size: 13px; font-weight: 600; color: var(--ink);">Require one-time email OTP verification</span>
                                    <span style="display: block; font-size: 12px; color: var(--muted);">Enforce verification code sent to customer email before accepting ticket</span>
                                </div>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="enable_customer_portal_auth" value="1" {{ $portalSettings->enable_customer_portal_auth ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--green);">
                                <div>
                                    <span style="font-size: 13px; font-weight: 600; color: var(--ink);">Enable Customer Portal Login / SSO</span>
                                    <span style="display: block; font-size: 12px; color: var(--muted);">Allows authenticated customers to review ticket history and post follow-ups</span>
                                </div>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="is_portal_active" value="1" {{ $portalSettings->is_portal_active ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--green);">
                                <div>
                                    <span style="font-size: 13px; font-weight: 600; color: var(--ink);">Publish Support Portal publicly</span>
                                    <span style="display: block; font-size: 12px; color: var(--muted);">Unchecking disables public intake without affecting internal admin workflows</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="button" style="width: 100%;">Save Portal Branding & Access Settings</button>
                </form>

                <!-- CARD 3: Support Categories & Departments -->
                <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; color: var(--ink); margin: 0 0 4px;">Support Categories & Departments</h3>
                            <p style="font-size: 13px; color: var(--muted); margin: 0;">Organize incoming tickets by department and map each category to a custom dynamic form for {{ $organization->name }}.</p>
                        </div>
                        <button type="button" onclick="openAddCategoryModal()" class="button" style="width: auto; padding: 7px 16px; font-size: 12.5px;">
                            + Add Category
                        </button>
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--line); text-align: left; font-size: 11px; text-transform: uppercase; color: var(--muted);">
                                    <th style="padding: 10px 12px;">Category Name</th>
                                    <th style="padding: 10px 12px;">Department</th>
                                    <th style="padding: 10px 12px;">Mapped Form</th>
                                    <th style="padding: 10px 12px;">Status</th>
                                    <th style="padding: 10px 12px; text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $cat)
                                    <tr style="border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                                        <td style="padding: 12px; font-weight: 600; color: var(--ink);">
                                            {{ $cat->name }}
                                            <div style="font-size: 11px; color: var(--muted); font-family: monospace;">{{ $cat->slug }}</div>
                                        </td>
                                        <td style="padding: 12px; color: #475569;">
                                            {{ $cat->department ?? 'General' }}
                                        </td>
                                        <td style="padding: 12px;">
                                            @php
                                                $mappedForm = $forms->firstWhere('id', $cat->default_form_id);
                                            @endphp
                                            @if ($mappedForm)
                                                <span class="pill" style="background: #eff6ff; color: #2563eb;">{{ $mappedForm->name }}</span>
                                            @else
                                                <span style="color: var(--muted); font-size: 12px;">Default Form</span>
                                            @endif
                                        </td>
                                        <td style="padding: 12px;">
                                            <span class="pill" style="background: #ecfdf5; color: #16a34a;">Active</span>
                                        </td>
                                        <td style="padding: 12px; text-align: right;">
                                            <form method="POST" action="{{ route('admin.support.categories.delete', $cat->id) }}" onsubmit="return confirm('Delete category {{ $cat->name }}?')" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                                                <button type="submit" style="background: transparent; border: 0; color: #dc2626; cursor: pointer; font-size: 12px; font-weight: 600;">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="padding: 24px; text-align: center; color: var(--muted); font-size: 13px;">
                                            No categories configured yet for {{ $organization->name }}. Click "+ Add Category" above to create departments like Technical, Billing, or Sales.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- CARD 4: SLA Targets -->
                <form class="card" method="POST" action="{{ route('admin.support.settings.update') }}" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 24px;">
                    @csrf
                    <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                    <div style="margin-bottom: 20px;">
                        <h3 style="font-size: 16px; font-weight: 700; color: var(--ink); margin: 0 0 6px;">SLA Priority Targets</h3>
                        <p style="font-size: 13px; color: var(--muted); margin: 0;">Define targeted first-response and full-resolution deadlines by ticket priority level for {{ $organization->name }}.</p>
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

            <!-- Right Column: Sidebar & Workspace Shortcuts -->
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
                        Authenticated agents for {{ $organization->name }} can log in directly using organization credentials via Sanctum API tokens or SSO.
                    </div>
                </div>

                <!-- Dynamic Forms Status -->
                <div class="card" style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: var(--ink);">Dynamic Forms</h4>
                        <a href="{{ route('admin.support.forms', $isSuperAdmin ? ['organization_id' => $organization->id] : []) }}" style="font-size: 12px; color: var(--green); font-weight: 700; text-decoration: underline;">
                            Manage ({{ $forms->count() }})
                        </a>
                    </div>
                    <p style="font-size: 12.5px; color: var(--muted); margin: 0 0 12px;">
                        Customize questions, conditional branching, and file upload fields for customer tickets.
                    </p>
                    <a href="{{ route('admin.support.forms.create', $isSuperAdmin ? ['organization_id' => $organization->id] : []) }}" class="button" style="display: block; text-align: center; font-size: 12.5px; padding: 8px 12px;">
                        + Build New Ticket Form
                    </a>
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

        <!-- Modal: Add Support Category -->
        <div id="addCategoryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 50; align-items: center; justify-content: center;">
            <div style="background: #fff; border-radius: 12px; max-width: 480px; width: 90%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Add Support Category ({{ $organization->name }})</h3>
                    <button type="button" onclick="closeAddCategoryModal()" style="border: 0; background: transparent; font-size: 20px; cursor: pointer;">✕</button>
                </div>
                <form method="POST" action="{{ route('admin.support.categories.store') }}">
                    @csrf
                    <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                    <div style="display: grid; gap: 14px; margin-bottom: 20px;">
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #475569;">Category Name</label>
                            <input type="text" name="name" required placeholder="e.g. Technical Bugs, Cloud Outage" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #475569;">Department</label>
                            <input type="text" name="department" placeholder="e.g. Engineering, IT Operations, Billing" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #475569;">Map to Dynamic Form (Optional)</label>
                            <select name="default_form_id" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;">
                                <option value="">-- Use Default Portal Form --</option>
                                @foreach ($forms as $form)
                                    <option value="{{ $form->id }}">{{ $form->name }} (v{{ $form->version }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #475569;">Description</label>
                            <textarea name="description" rows="2" placeholder="Brief description of this issue classification" style="width: 100%; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; margin-top: 4px;"></textarea>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" onclick="closeAddCategoryModal()" class="button" style="background: transparent; color: var(--ink); border: 1px solid var(--line); box-shadow: none;">Cancel</button>
                        <button type="submit" class="button">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <!-- Empty State When No Organization is Selected -->
        <div class="card" style="background: #fff; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 56px 24px; text-align: center; margin-top: 24px;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 16px;">
                🏢
            </div>
            <h3 style="font-size: 19px; font-weight: 700; color: var(--ink); margin: 0 0 8px;">Please Select an Organization</h3>
            <p style="font-size: 14px; color: var(--muted); max-width: 520px; margin: 0 auto 20px; line-height: 1.6;">
                Support desk portal branding, departments, ticket categories, dynamic forms, and SLA response targets vary for each organization. Please select an organization from the dropdown above to view and configure its settings.
            </p>
        </div>
    @endif

    <script>
        function copyPortalUrl() {
            const input = document.getElementById('publicPortalUrlInput');
            if (!input) return;
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value);
            const msg = document.getElementById('copySuccessMsg');
            if (msg) {
                msg.style.display = 'block';
                setTimeout(() => { msg.style.display = 'none'; }, 4000);
            }
        }

        function openAddCategoryModal() {
            const modal = document.getElementById('addCategoryModal');
            if (modal) modal.style.display = 'flex';
        }

        function closeAddCategoryModal() {
            const modal = document.getElementById('addCategoryModal');
            if (modal) modal.style.display = 'none';
        }
    </script>
@endsection
