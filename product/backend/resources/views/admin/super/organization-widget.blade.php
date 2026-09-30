@extends('admin.layout')

@section('title', 'Widget & AI Assistant Settings - ' . $organization->name)

@section('content')
    <div style="margin-bottom: 16px;">
        <a class="button" style="width: auto; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); font-size: 13px; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 8px 14px; border-radius: 6px;" href="{{ route('admin.super.organizations.show', $organization) }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Organization Details
        </a>
    </div>

    <div class="header" style="display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; flex-wrap: wrap;">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Super-admin support</div>
            <h1>Widget Settings & AI Agent · {{ $organization->name }}</h1>
            <p class="help">Configure this organization's branding, welcome greeting, knowledge metadata, and AI role limitations.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a class="button" style="width: auto; background: transparent; color: var(--ink); border: 1px solid var(--line); box-shadow: none;" href="{{ route('admin.super.organizations.monitoring', $organization) }}">Chat & Token Usage</a>
            <a class="button" style="width: auto; background: transparent; color: var(--ink); border: 1px solid var(--line); box-shadow: none;" href="{{ route('admin.super.organizations.knowledge', $organization) }}">Knowledge Base</a>
            <a class="button" style="width: auto; background: transparent; color: var(--ink); border: 1px solid var(--line); box-shadow: none;" href="{{ route('admin.super.organizations.show', $organization) }}">Organization Profile</a>
        </div>
    </div>

    @if (session('status'))
        <div style="margin-bottom: 20px; padding: 12px 16px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 8px; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-2" style="align-items: start; gap: 24px;">
        <!-- Left Column: Form & Keys -->
        <div>
            <form class="card" method="POST" action="{{ route('admin.super.organizations.widget.update', $organization) }}" id="widget-settings-form">
                @csrf
                @method('PATCH')

                <!-- Master Widget Enable / Disable Switch -->
                @php
                    $isWidgetEnabled = (bool) old('is_widget_enabled', $settings->is_widget_enabled ?? true);
                @endphp
                <div style="background: {{ $isWidgetEnabled ? '#f8fafc' : '#fef2f2' }}; border: 1px solid {{ $isWidgetEnabled ? 'var(--line)' : '#fecaca' }}; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap;">
                    <div>
                        <div style="font-weight: 700; font-size: 14px; color: var(--ink); display: flex; align-items: center; gap: 8px;">
                            <span id="master-toggle-indicator" style="width: 8px; height: 8px; border-radius: 50%; background: {{ $isWidgetEnabled ? '#22c55e' : '#ef4444' }};"></span>
                            <span>Chatbot Widget Master Switch</span>
                        </div>
                        <p class="help" style="margin: 2px 0 0; font-size: 12px;">When disabled, the widget will not respond or will display offline/unavailable status on visitor websites.</p>
                    </div>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 600; cursor: pointer; background: #ffffff; border: 1px solid var(--line); padding: 6px 12px; border-radius: 6px;">
                        <input type="checkbox" id="input-widget-enabled" name="is_widget_enabled" value="1" @checked($isWidgetEnabled)>
                        <span id="label-widget-enabled">{{ $isWidgetEnabled ? 'Widget Enabled' : 'Widget Disabled' }}</span>
                    </label>
                </div>
                
                <!-- 1. Assistant Identity & Design -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--line); padding-bottom: 12px;">
                    <div>
                        <h2 style="font-size: 17px; margin: 0;">Assistant Identity & Appearance</h2>
                        <p class="help" style="margin: 3px 0 0;">Basic appearance settings visible to website visitors.</p>
                    </div>
                    <span style="font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 12px; background: #e0e7ff; color: #4338ca;">Branding</span>
                </div>

                <div class="grid grid-2">
                    <label class="field">Assistant name
                        <input id="input-assistant-name" name="assistant_name" value="{{ old('assistant_name', $settings->assistant_name ?: $organization->name . ' Assistant') }}" required maxlength="255">
                    </label>
                    <label class="field">Assistant status
                        <select id="select-assistant-status" name="assistant_status" required>
                            <option value="active" @selected(old('assistant_status', $settings->assistant_status) === 'active')>Active (Online)</option>
                            <option value="paused" @selected(old('assistant_status', $settings->assistant_status) === 'paused')>Paused (Offline)</option>
                        </select>
                    </label>
                    <label class="field">Brand color
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px;">
                            <input id="input-brand-color" name="chatbot_color_palette" type="color" value="{{ old('chatbot_color_palette', $settings->chatbot_color_palette ?: '#d22630') }}" required style="width: 48px; height: 38px; padding: 2px; cursor: pointer; border-radius: 6px;">
                            <input id="input-brand-color-hex" type="text" value="{{ old('chatbot_color_palette', $settings->chatbot_color_palette ?: '#d22630') }}" style="max-width: 110px; font-family: monospace; font-size: 13px;" maxlength="7">
                        </div>
                    </label>
                    <label class="field">Widget icon
                        <select id="select-widget-icon" name="chatbot_icon" required>
                            @foreach (['robot' => 'Robot Assistant', 'support' => 'Customer Support', 'star' => 'Star / AI', 'chat' => 'Chat Bubble'] as $icon => $label)
                                <option value="{{ $icon }}" @selected(old('chatbot_icon', $settings->chatbot_icon ?: 'robot') === $icon)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <label style="display: flex; align-items: center; gap: 9px; margin-top: 14px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    <input type="checkbox" name="live_chat_enabled" value="1" @checked(old('live_chat_enabled', $settings->live_chat_enabled ?? true))>
                    Allow live-agent support escalation
                </label>

                <!-- 2. Organization Context & Metadata -->
                <div style="margin-top: 28px; padding-top: 18px; border-top: 1px solid var(--line);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                        <div>
                            <h2 style="font-size: 17px; margin: 0;">Organization Metadata & Greetings</h2>
                            <p class="help" style="margin: 3px 0 0;">Welcome message and business overview used to greet and assist users.</p>
                        </div>
                        <span style="font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 12px; background: #ecfdf5; color: #065f46;">Metadata</span>
                    </div>

                    <label class="field" style="margin-bottom: 14px;">Welcome greeting message
                        <textarea id="input-welcome-message" name="welcome_message" rows="2" style="width: 100%; font-size: 13px; resize: vertical;" placeholder="{{ $defaultWelcomeMessage }}">{{ old('welcome_message', $settings->welcome_message ?: $defaultWelcomeMessage) }}</textarea>
                        <span class="help" style="font-size: 11.5px; margin-top: 4px;">Displayed to customers at the top of the chat panel when the widget opens.</span>
                    </label>

                    <label class="field">Organization description & metadata
                        <textarea name="organization_details" rows="3" style="width: 100%; font-size: 13px; resize: vertical;" placeholder="{{ $defaultOrgDetails }}">{{ old('organization_details', $settings->organization_details ?: $defaultOrgDetails) }}</textarea>
                        <span class="help" style="font-size: 11.5px; margin-top: 4px;">Overview of {{ $organization->name }}'s services, specialties, and business context grounding the AI answers.</span>
                    </label>
                </div>

                <!-- 3. AI Role & Limitations -->
                <div style="margin-top: 28px; padding-top: 18px; border-top: 1px solid var(--line);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                        <div>
                            <h2 style="font-size: 17px; margin: 0;">AI Role, Guardrails & Limitations</h2>
                            <p class="help" style="margin: 3px 0 0;">Control how the assistant behaves, what it can discuss, and its operational limits.</p>
                        </div>
                        <span style="font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 12px; background: #fef3c7; color: #92400e;">Guardrails</span>
                    </div>

                    <label class="field" style="margin-bottom: 14px;">Restriction template
                        <select name="restriction_template" required>
                            <option value="general_faq" @selected(old('restriction_template', $settings->restriction_template ?: 'general_faq') === 'general_faq')>General FAQ & Service Guidance (Recommended)</option>
                            <option value="strict_retrieval" @selected(old('restriction_template', $settings->restriction_template) === 'strict_retrieval')>Strict Retrieval (Verified Knowledge & Metadata Only)</option>
                            <option value="hr_policy" @selected(old('restriction_template', $settings->restriction_template) === 'hr_policy')>HR & Internal Policies</option>
                            <option value="custom" @selected(old('restriction_template', $settings->restriction_template) === 'custom')>Custom System Instructions</option>
                        </select>
                    </label>

                    <label class="field" style="margin-bottom: 14px;">Assistant role definition (System Prompt)
                        <textarea name="custom_system_prompt" rows="3" style="width: 100%; font-size: 13px; resize: vertical;" placeholder="{{ $defaultSystemPrompt }}">{{ old('custom_system_prompt', $settings->custom_system_prompt ?: $defaultSystemPrompt) }}</textarea>
                        <span class="help" style="font-size: 11.5px; margin-top: 4px;">Persona instructions defining how the AI identifies itself and addresses inquiries.</span>
                    </label>

                    <label class="field" style="margin-bottom: 14px;">Role limitations & guardrails
                        <textarea name="eligibility_criteria" rows="3" style="width: 100%; font-size: 13px; resize: vertical;" placeholder="{{ $defaultEligibility }}">{{ old('eligibility_criteria', $settings->eligibility_criteria ?: $defaultEligibility) }}</textarea>
                        <span class="help" style="font-size: 11.5px; margin-top: 4px;">Boundaries preventing disclosure of internal secrets, off-topic subjects, or unsupported advice.</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 9px; font-size: 13px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="strict_context_enforcement" value="1" @checked(old('strict_context_enforcement', $settings->strict_context_enforcement ?? true))>
                        Strict context grounding (prevent external hallucinations outside organization knowledge)
                    </label>
                </div>

                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--line); display: flex; justify-content: flex-end;">
                    <button class="button" type="submit" style="width: auto; padding: 10px 24px; font-weight: 600;">Save widget settings</button>
                </div>
            </form>

            <!-- Public Widget Key Card -->
            <section class="card" style="margin-top: 20px;">
                <h2 style="font-size: 17px; margin: 0 0 6px;">Public widget key</h2>
                <p class="help" style="margin-top: 0;">This public identifier links client sites to this organization's widget configuration.</p>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <code id="widget-key" style="padding: 8px 12px; background: #f8fafc; border: 1px solid var(--line); border-radius: 6px; font-family: monospace; font-size: 13px; overflow-wrap: anywhere;">{{ $widgetKey }}</code>
                    <button class="button" type="button" onclick="navigator.clipboard.writeText(document.getElementById('widget-key').textContent.trim()); alert('Public widget key copied!')" style="width: auto; padding: 7px 14px;">Copy key</button>
                </div>
                <form method="POST" action="{{ route('admin.super.organizations.widget.rotate-key', $organization) }}" style="margin: 14px 0 0; padding: 0; border: 0; box-shadow: none;" onsubmit="return confirm('Rotating this key will disconnect existing embeds until the customer website script tag is updated. Continue?')">
                    @csrf
                    <button class="button" type="submit" style="width: auto; background: #fff; color: #b91c1c; border: 1px solid #fecaca; box-shadow: none; font-size: 12.5px; padding: 6px 12px;">Rotate key</button>
                </form>
            </section>

            <!-- Publish Snippet Card -->
            <section class="card" style="margin-top: 20px;">
                <h2 style="font-size: 17px; margin: 0 0 6px;">Publish snippet</h2>
                <p class="help" style="margin-top: 0;">Paste this snippet into the HTML before the closing <code>&lt;/body&gt;</code> tag on the organization's website.</p>
                <textarea id="widget-snippet" readonly rows="6" style="width: 100%; font-family: monospace; font-size: 12px; background: #f8fafc; border: 1px solid var(--line); border-radius: 6px; padding: 10px; line-height: 1.5;">&lt;script
  src="https://cdn.apriloinfotech.com/chat-widget/v1/widget.js"
  data-widget-key="{{ $widgetKey }}"
  data-api-url="{{ $apiUrl }}"
  data-auto-open="false"&gt;
&lt;/script&gt;</textarea>
                <button class="button" type="button" onclick="navigator.clipboard.writeText(document.getElementById('widget-snippet').value); alert('Embed snippet copied!')" style="width: auto; margin-top: 10px; padding: 8px 16px;">Copy embed snippet</button>
            </section>
        </div>

        <!-- Right Column: Interactive Live Preview -->
        <div>
            <section class="card" style="padding: 0; overflow: hidden; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                <div style="padding: 14px 18px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; background: #fafafa;">
                    <div>
                        <h2 style="font-size: 16px; margin: 0; font-weight: 600;">Live Widget Preview</h2>
                        <span style="font-size: 12px; color: var(--muted);">Real-time simulation of changes & AI testing</span>
                    </div>
                    <span id="preview-badge" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 12px; background: #dcfce7; color: #15803d; display: inline-flex; align-items: center; gap: 5px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #22c55e;"></span>
                        Live Sync
                    </span>
                </div>

                <div style="padding: 20px; background: #f1f5f9; display: flex; justify-content: center; align-items: flex-start;">
                    <!-- Simulated Chat Widget Panel -->
                    <div id="sim-widget-panel" style="width: 100%; max-width: 380px; height: 530px; background: #ffffff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.12); display: flex; flex-direction: column; overflow: hidden; border: 1px solid #e2e8f0; position: relative;">
                        
                        <!-- Simulated Header -->
                        <div id="sim-widget-header" style="background: {{ old('chatbot_color_palette', $settings->chatbot_color_palette ?: '#d22630') }}; color: #ffffff; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; transition: background 0.25s ease;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div id="sim-header-icon" style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.22); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                    @if (($settings->chatbot_icon ?: 'robot') === 'support')
                                        🎧
                                    @elseif (($settings->chatbot_icon ?: 'robot') === 'star')
                                        ⭐
                                    @elseif (($settings->chatbot_icon ?: 'robot') === 'chat')
                                        💬
                                    @else
                                        🤖
                                    @endif
                                </div>
                                <div>
                                    <div id="sim-assistant-name" style="font-weight: 700; font-size: 14px; line-height: 1.2;">
                                        {{ old('assistant_name', $settings->assistant_name ?: $organization->name . ' Assistant') }}
                                    </div>
                                    <div id="sim-status-label" style="font-size: 11px; opacity: 0.9; display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                                        <span id="sim-status-dot" style="width: 6px; height: 6px; border-radius: 50%; background: #4ade80;"></span>
                                        <span id="sim-status-text">Online</span>
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 6px; opacity: 0.85;">
                                <div style="width: 24px; height: 24px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 12px; cursor: pointer;">&minus;</div>
                                <div style="width: 24px; height: 24px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 12px; cursor: pointer;">&times;</div>
                            </div>
                        </div>

                        <!-- Simulated Messages Scroll Area -->
                        <div id="sim-chat-body" style="flex: 1; padding: 14px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: #f8fafc;">
                            
                            <!-- Welcome Banner inside Widget -->
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                                <div style="font-weight: 700; font-size: 13px; color: #1e293b; margin-bottom: 4px;">
                                    Welcome to {{ $organization->name }}
                                </div>
                                <div id="sim-welcome-text" style="font-size: 12px; color: #475569; line-height: 1.4;">
                                    {{ old('welcome_message', $settings->welcome_message ?: $defaultWelcomeMessage) }}
                                </div>
                                
                                <!-- Quick Action Pills -->
                                <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 10px;">
                                    <button type="button" class="sim-quick-btn" onclick="sendSimulatedQuery('What services does {{ addslashes($organization->name) }} offer?')" style="text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; font-size: 11.5px; color: #334155; cursor: pointer; transition: all 0.15s ease; display: flex; justify-content: space-between; align-items: center;">
                                        <span>Our Services & Solutions</span>
                                        <span style="color: #94a3b8;">&rarr;</span>
                                    </button>
                                    <button type="button" class="sim-quick-btn" onclick="sendSimulatedQuery('How can I contact {{ addslashes($organization->name) }} support?')" style="text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; font-size: 11.5px; color: #334155; cursor: pointer; transition: all 0.15s ease; display: flex; justify-content: space-between; align-items: center;">
                                        <span>Contact Support Team</span>
                                        <span style="color: #94a3b8;">&rarr;</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Initial Assistant Greeting Bubble -->
                            <div style="display: flex; gap: 8px; align-items: flex-start;">
                                <div id="sim-avatar-small" style="width: 26px; height: 26px; border-radius: 50%; background: {{ old('chatbot_color_palette', $settings->chatbot_color_palette ?: '#d22630') }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; margin-top: 2px;">
                                    @if (($settings->chatbot_icon ?: 'robot') === 'support')
                                        🎧
                                    @elseif (($settings->chatbot_icon ?: 'robot') === 'star')
                                        ⭐
                                    @elseif (($settings->chatbot_icon ?: 'robot') === 'chat')
                                        💬
                                    @else
                                        🤖
                                    @endif
                                </div>
                                <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 9px 12px; border-radius: 12px 12px 12px 3px; font-size: 12px; color: #1e293b; max-width: 82%; line-height: 1.4; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                    Hi there! I am ready to assist you with inquiries regarding <strong>{{ $organization->name }}</strong>. Feel free to type a test question below!
                                </div>
                            </div>

                        </div>

                        <!-- Typing Indicator -->
                        <div id="sim-typing" style="display: none; padding: 6px 16px; font-size: 11px; color: #64748b; background: #f8fafc; border-top: 1px solid #f1f5f9; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #94a3b8; animation: pulse 1s infinite;"></span>
                            Assistant is thinking...
                        </div>

                        <!-- Simulated Input Bar -->
                        <div style="padding: 10px 12px; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; gap: 8px; align-items: center;">
                            <input id="sim-test-input" type="text" placeholder="Type a test question..." style="flex: 1; padding: 8px 12px; font-size: 12.5px; border: 1px solid #cbd5e1; border-radius: 20px; outline: none;" onkeydown="if(event.key === 'Enter'){ event.preventDefault(); sendSimulatedQuery(); }">
                            <button id="sim-send-btn" type="button" onclick="sendSimulatedQuery()" style="width: 34px; height: 34px; border-radius: 50%; background: {{ old('chatbot_color_palette', $settings->chatbot_color_palette ?: '#d22630') }}; color: #ffffff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background 0.2s ease;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Preview Controls Footer -->
                <div style="padding: 12px 16px; background: #fafafa; border-top: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <div style="font-size: 12px; color: var(--muted);">
                        Preview communicates directly with <code>/api/widget/chat</code> using key <code>{{ substr($widgetKey, 0, 16) }}...</code>
                    </div>
                    <button type="button" class="button" onclick="resetSimulatedChat()" style="width: auto; padding: 4px 10px; font-size: 12px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); box-shadow: none;">
                        Clear Chat History
                    </button>
                </div>
            </section>
        </div>
    </div>

    <!-- Live Widget Floating Launcher with Verified API URL -->
    @if ($organization->hasActiveProductCategory('ai_support'))
        <script
            src="https://cdn.apriloinfotech.com/chat-widget/v1/widget.js"
            data-widget-key="{{ $widgetKey }}"
            data-api-url="{{ $apiUrl }}"
            data-auto-open="false">
        </script>
    @endif

    <script>
        (function() {
            // Live Real-Time Form Listeners
            const nameInput = document.getElementById('input-assistant-name');
            const statusSelect = document.getElementById('select-assistant-status');
            const colorInput = document.getElementById('input-brand-color');
            const colorHex = document.getElementById('input-brand-color-hex');
            const iconSelect = document.getElementById('select-widget-icon');
            const welcomeInput = document.getElementById('input-welcome-message');

            const simName = document.getElementById('sim-assistant-name');
            const simHeader = document.getElementById('sim-widget-header');
            const simAvatar = document.getElementById('sim-header-icon');
            const simAvatarSmall = document.getElementById('sim-avatar-small');
            const simSendBtn = document.getElementById('sim-send-btn');
            const simStatusText = document.getElementById('sim-status-text');
            const simStatusDot = document.getElementById('sim-status-dot');
            const simWelcomeText = document.getElementById('sim-welcome-text');

            const iconMap = {
                'robot': '🤖',
                'support': '🎧',
                'star': '⭐',
                'chat': '💬'
            };

            function updateBrandColor(color) {
                if (!color) return;
                simHeader.style.background = color;
                simAvatarSmall.style.background = color;
                simSendBtn.style.background = color;
            }

            if (nameInput) {
                nameInput.addEventListener('input', function() {
                    simName.textContent = this.value.trim() || 'Assistant';
                });
            }

            if (colorInput) {
                colorInput.addEventListener('input', function() {
                    colorHex.value = this.value;
                    updateBrandColor(this.value);
                });
            }

            if (colorHex) {
                colorHex.addEventListener('input', function() {
                    if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                        colorInput.value = this.value;
                        updateBrandColor(this.value);
                    }
                });
            }

            if (iconSelect) {
                iconSelect.addEventListener('change', function() {
                    const iconEmoji = iconMap[this.value] || '🤖';
                    simAvatar.textContent = iconEmoji;
                    simAvatarSmall.textContent = iconEmoji;
                });
            }

            const widgetEnabledCheckbox = document.getElementById('input-widget-enabled');
            const widgetEnabledLabel = document.getElementById('label-widget-enabled');
            const masterToggleIndicator = document.getElementById('master-toggle-indicator');

            function syncWidgetStatus() {
                const isEnabled = widgetEnabledCheckbox ? widgetEnabledCheckbox.checked : true;
                const isPaused = statusSelect ? statusSelect.value === 'paused' : false;

                if (widgetEnabledLabel) {
                    widgetEnabledLabel.textContent = isEnabled ? 'Widget Enabled' : 'Widget Disabled';
                }
                if (masterToggleIndicator) {
                    masterToggleIndicator.style.background = isEnabled ? '#22c55e' : '#ef4444';
                }

                if (!isEnabled) {
                    simStatusText.textContent = 'Disabled (Offline)';
                    simStatusDot.style.background = '#ef4444';
                } else if (isPaused) {
                    simStatusText.textContent = 'Paused (Offline)';
                    simStatusDot.style.background = '#f59e0b';
                } else {
                    simStatusText.textContent = 'Online';
                    simStatusDot.style.background = '#4ade80';
                }
            }

            if (widgetEnabledCheckbox) {
                widgetEnabledCheckbox.addEventListener('change', syncWidgetStatus);
            }

            if (statusSelect) {
                statusSelect.addEventListener('change', syncWidgetStatus);
            }

            if (welcomeInput) {
                welcomeInput.addEventListener('input', function() {
                    simWelcomeText.textContent = this.value.trim() || 'Welcome! How can we assist you?';
                });
            }
        })();

        // Interactive Live Simulated Chat Engine
        const widgetKey = @json($widgetKey);
        const orgName = @json($organization->name);
        const apiUrl = @json($apiUrl);

        window.sendSimulatedQuery = async function(presetText) {
            const input = document.getElementById('sim-test-input');
            const query = presetText || input.value.trim();
            if (!query) return;

            if (!presetText) {
                input.value = '';
            }

            const body = document.getElementById('sim-chat-body');
            const typing = document.getElementById('sim-typing');

            // Render User Bubble
            const userMsgDiv = document.createElement('div');
            userMsgDiv.style.cssText = 'display: flex; justify-content: flex-end; margin-top: 4px;';
            userMsgDiv.innerHTML = `
                <div style="background: #2563eb; color: #ffffff; padding: 8px 12px; border-radius: 12px 12px 3px 12px; font-size: 12px; max-width: 80%; line-height: 1.4; word-break: break-word;">
                    ${escapeHtml(query)}
                </div>
            `;
            body.appendChild(userMsgDiv);
            body.scrollTop = body.scrollHeight;

            typing.style.display = 'flex';
            body.scrollTop = body.scrollHeight;

            try {
                // Call live backend endpoint
                const endpoint = `${apiUrl.replace(/\/+$/, '')}/api/widget/chat?widget_key=${encodeURIComponent(widgetKey)}`;
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ question_text: query })
                });

                typing.style.display = 'none';

                if (res.ok) {
                    const data = await res.json();
                    renderAssistantResponse(data.answer_text || 'Thank you for your message.', data.sources || []);
                } else {
                    // Fallback intelligent simulation
                    simulateLocalResponse(query);
                }
            } catch (err) {
                typing.style.display = 'none';
                simulateLocalResponse(query);
            }
        };

        function simulateLocalResponse(query) {
            const nameInput = document.getElementById('input-assistant-name');
            const assistantName = (nameInput ? nameInput.value.trim() : '') || `${orgName} Assistant`;
            const lower = query.toLowerCase();

            let reply = '';
            if (lower.includes('hi') || lower.includes('hello') || lower.includes('hey')) {
                reply = `Hello! I am ${assistantName} for ${orgName}. How can I assist you with our services today?`;
            } else if (lower.includes('service') || lower.includes('what') || lower.includes('solution')) {
                reply = `${orgName} provides professional solutions and customer support. Please feel free to ask about our specific offerings, consultations, or assistance procedures.`;
            } else if (lower.includes('contact') || lower.includes('support') || lower.includes('call')) {
                reply = `You can reach ${orgName} support directly through our official channels. We are also happy to escalate your inquiry to an active agent.`;
            } else {
                reply = `Thank you for your question about ${orgName}. I am configured to assist you with verified organization information and service inquiries.`;
            }

            renderAssistantResponse(reply, []);
        }

        function renderAssistantResponse(text, sources) {
            const body = document.getElementById('sim-chat-body');
            const currentColor = document.getElementById('input-brand-color').value || '#d22630';
            const iconSelect = document.getElementById('select-widget-icon');
            const iconMap = { 'robot': '🤖', 'support': '🎧', 'star': '⭐', 'chat': '💬' };
            const currentEmoji = iconMap[iconSelect ? iconSelect.value : 'robot'] || '🤖';

            let sourcesHtml = '';
            if (sources && sources.length > 0) {
                sourcesHtml = '<div style="margin-top: 6px; font-size: 10.5px; opacity: 0.85; border-top: 1px solid rgba(0,0,0,0.06); padding-top: 4px;"><strong>Sources:</strong> ' +
                    sources.map(s => s.citation_label ? s.citation_label : '[Doc]').join(', ') + '</div>';
            }

            const botMsgDiv = document.createElement('div');
            botMsgDiv.style.cssText = 'display: flex; gap: 8px; align-items: flex-start; margin-top: 4px;';
            botMsgDiv.innerHTML = `
                <div style="width: 26px; height: 26px; border-radius: 50%; background: ${currentColor}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; margin-top: 2px;">
                    ${currentEmoji}
                </div>
                <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 9px 12px; border-radius: 12px 12px 12px 3px; font-size: 12px; color: #1e293b; max-width: 82%; line-height: 1.4; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                    ${escapeHtml(text)}
                    ${sourcesHtml}
                </div>
            `;
            body.appendChild(botMsgDiv);
            body.scrollTop = body.scrollHeight;
        }

        window.resetSimulatedChat = function() {
            const body = document.getElementById('sim-chat-body');
            const welcomeText = document.getElementById('input-welcome-message').value.trim();
            const currentColor = document.getElementById('input-brand-color').value || '#d22630';
            const iconSelect = document.getElementById('select-widget-icon');
            const iconMap = { 'robot': '🤖', 'support': '🎧', 'star': '⭐', 'chat': '💬' };
            const currentEmoji = iconMap[iconSelect ? iconSelect.value : 'robot'] || '🤖';

            body.innerHTML = `
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <div style="font-weight: 700; font-size: 13px; color: #1e293b; margin-bottom: 4px;">
                        Welcome to ${escapeHtml(orgName)}
                    </div>
                    <div id="sim-welcome-text" style="font-size: 12px; color: #475569; line-height: 1.4;">
                        ${escapeHtml(welcomeText)}
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 10px;">
                        <button type="button" class="sim-quick-btn" onclick="sendSimulatedQuery('What services does ${escapeHtml(orgName)} offer?')" style="text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; font-size: 11.5px; color: #334155; cursor: pointer; transition: all 0.15s ease; display: flex; justify-content: space-between; align-items: center;">
                            <span>Our Services & Solutions</span>
                            <span style="color: #94a3b8;">&rarr;</span>
                        </button>
                        <button type="button" class="sim-quick-btn" onclick="sendSimulatedQuery('How can I contact ${escapeHtml(orgName)} support?')" style="text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; font-size: 11.5px; color: #334155; cursor: pointer; transition: all 0.15s ease; display: flex; justify-content: space-between; align-items: center;">
                            <span>Contact Support Team</span>
                            <span style="color: #94a3b8;">&rarr;</span>
                        </button>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; align-items: flex-start;">
                    <div id="sim-avatar-small" style="width: 26px; height: 26px; border-radius: 50%; background: ${currentColor}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; margin-top: 2px;">
                        ${currentEmoji}
                    </div>
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 9px 12px; border-radius: 12px 12px 12px 3px; font-size: 12px; color: #1e293b; max-width: 82%; line-height: 1.4; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                        Hi there! I am ready to assist you with inquiries regarding <strong>${escapeHtml(orgName)}</strong>. Feel free to type a test question below!
                    </div>
                </div>
            `;
        };

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
@endsection
