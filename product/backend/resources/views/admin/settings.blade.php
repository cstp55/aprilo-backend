@extends('admin.layout')

@section('title', 'Aprilo AI - Settings & Customization')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow font-bold">Workspace Controls</div>
            <h1 style="display: flex; align-items: center; gap: 12px;">
                Settings
                <span class="pill" style="font-size: 11px; background: rgba(227,38,38,0.1); color: var(--green); border: 1px solid rgba(227,38,38,0.2); text-transform: uppercase;">
                    {{ $organization->plan }} plan
                </span>
            </h1>
            <p class="help">Configure your assistant's identity, branding design, connected channels, deployment script, and plan subscriptions.</p>
        </div>
    </div>

    <!-- Sub-tab Navigation Menu -->
    <div style="display: flex; gap: 4px; border-bottom: 1px solid var(--line); margin-bottom: 24px; padding-bottom: 2px;">
        <a href="{{ route('admin.settings', ['tab' => 'agent']) }}" 
           style="padding: 10px 20px; font-size: 14px; font-weight: 600; color: {{ $tab === 'agent' ? 'var(--green)' : 'var(--muted)' }}; border-bottom: 2px solid {{ $tab === 'agent' ? 'var(--green)' : 'transparent' }}; border-radius: 4px 4px 0 0; transition: all 0.2s;">
            🤖 Agent Settings
        </a>
        <a href="{{ route('admin.settings', ['tab' => 'design']) }}" 
           style="padding: 10px 20px; font-size: 14px; font-weight: 600; color: {{ $tab === 'design' ? 'var(--green)' : 'var(--muted)' }}; border-bottom: 2px solid {{ $tab === 'design' ? 'var(--green)' : 'transparent' }}; border-radius: 4px 4px 0 0; transition: all 0.2s;">
            🎨 Widget Design
        </a>
        <a href="{{ route('admin.settings', ['tab' => 'connect']) }}" 
           style="padding: 10px 20px; font-size: 14px; font-weight: 600; color: {{ $tab === 'connect' ? 'var(--green)' : 'var(--muted)' }}; border-bottom: 2px solid {{ $tab === 'connect' ? 'var(--green)' : 'transparent' }}; border-radius: 4px 4px 0 0; transition: all 0.2s;">
            🔌 Connect Channels
        </a>
        <a href="{{ route('admin.settings', ['tab' => 'deploy']) }}" 
           style="padding: 10px 20px; font-size: 14px; font-weight: 600; color: {{ $tab === 'deploy' ? 'var(--green)' : 'var(--muted)' }}; border-bottom: 2px solid {{ $tab === 'deploy' ? 'var(--green)' : 'transparent' }}; border-radius: 4px 4px 0 0; transition: all 0.2s;">
            🌐 Deploy Script
        </a>
        <a href="{{ route('admin.settings', ['tab' => 'pricing']) }}" 
           style="padding: 10px 20px; font-size: 14px; font-weight: 600; color: {{ $tab === 'pricing' ? 'var(--green)' : 'var(--muted)' }}; border-bottom: 2px solid {{ $tab === 'pricing' ? 'var(--green)' : 'transparent' }}; border-radius: 4px 4px 0 0; transition: all 0.2s;">
            💳 Plans & Pricing
        </a>
    </div>

    <!-- Active Tab Panel Content -->
    <div>
        <!-- TAB 1: AGENT SETTINGS -->
        @if ($tab === 'agent')
            <form class="card" method="POST" action="{{ route('admin.settings.update', ['tab' => 'agent']) }}" style="max-width: 720px;">
                @csrf
                @method('PATCH')
                <h3 style="margin-bottom: 16px; font-size: 16px; border-bottom: 1px solid var(--line); padding-bottom: 8px;">Identity & Logic Configuration</h3>
                
                <div style="display: grid; gap: 18px; margin-bottom: 20px;">
                    <label class="field">
                        Assistant Name
                        <input name="assistant_name" type="text" value="{{ $settings->assistant_name ?: 'Aprilo Bot' }}" required placeholder="e.g. Aprilo Bot">
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
                            Minutes saved per resolved question
                            <input name="minutes_saved_per_resolved_question" type="number" min="1" max="60" value="{{ $settings->minutes_saved_per_resolved_question }}" required>
                        </label>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <label class="field">
                            Data Source Scope
                            <select name="chatbot_data_source" required>
                                <option value="documents" @selected($settings->chatbot_data_source === 'documents')>Documents & Rules Only</option>
                                <option value="web" @selected($settings->chatbot_data_source === 'web')>Web Search</option>
                                <option value="hybrid" @selected($settings->chatbot_data_source === 'hybrid')>Hybrid Model</option>
                            </select>
                        </label>
                        <label class="field">
                            Intelligence Level
                            <select name="chatbot_intelligence_level" required>
                                <option value="conservative" @selected($settings->chatbot_intelligence_level === 'conservative')>Conservative (Strict Facts)</option>
                                <option value="standard" @selected($settings->chatbot_intelligence_level === 'standard')>Standard (Recommended)</option>
                                <option value="creative" @selected($settings->chatbot_intelligence_level === 'creative') 
                                        @disabled($organization->plan !== 'enterprise')>
                                    High Reasoning (Creative) @if($organization->plan !== 'enterprise') 🔒 (Requires Enterprise) @endif
                                </option>
                            </select>
                        </label>
                    </div>

                    <label class="field">
                        Ticket Creation Hook
                        <select name="chatbot_ticket_creation" required>
                            <option value="feedback-based" @selected($settings->chatbot_ticket_creation === 'feedback-based')>On Negative User Feedback (Helpful/Not Helpful)</option>
                            <option value="automatic" @selected($settings->chatbot_ticket_creation === 'automatic')>Automatic (Instantly when citations are low)</option>
                            <option value="manual" @selected($settings->chatbot_ticket_creation === 'manual')>Manual Link Offer Only</option>
                        </select>
                    </label>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <label class="field">
                            Chatbot Operational Mode
                            <select name="chatbot_mode" required>
                                <option value="document_only" @selected(($settings->chatbot_mode ?? 'document_only') === 'document_only')>Document Search Only</option>
                                <option value="interactive_hr" @selected(($settings->chatbot_mode ?? 'document_only') === 'interactive_hr')>Interactive HR Agent (ID Check)</option>
                            </select>
                        </label>
                        <div style="display: flex; flex-direction: column; justify-content: center;">
                            <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; cursor: pointer; margin-top: 18px;">
                                <input name="hr_api_connected" type="checkbox" value="1" @checked($settings->hr_api_connected)>
                                Connect HR database API (bambooHR / local)
                            </label>
                        </div>
                    </div>

                    <div>
                        <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; cursor: pointer;">
                            <input name="chatbot_escalation_enabled" type="checkbox" value="1" @checked($settings->chatbot_escalation_enabled)>
                            Enable fallback ticket escalation
                        </label>
                    </div>

                    <h3 style="margin-top: 24px; margin-bottom: 12px; font-size: 15px; border-bottom: 1px solid var(--line); padding-bottom: 8px; color: var(--green);">🤖 Gemini LLM & Restrictive AI Settings</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <label class="field">
                            Gemini API Key
                            <input name="gemini_api_key" type="password" value="{{ $settings->gemini_api_key }}" placeholder="Default (System Env Key)" style="width: 100%;">
                        </label>
                        <label class="field">
                            Gemini Model Selection
                            <select name="gemini_model" required>
                                <option value="gemini-flash-latest" @selected(($settings->gemini_model ?? 'gemini-flash-latest') === 'gemini-flash-latest')>Gemini Flash Latest (Recommended)</option>
                                <option value="gemini-1.5-flash" @selected(($settings->gemini_model ?? '') === 'gemini-1.5-flash')>Gemini 1.5 Flash</option>
                                <option value="gemini-1.5-pro" @selected(($settings->gemini_model ?? '') === 'gemini-1.5-pro')>Gemini 1.5 Pro (High Reasoning)</option>
                                <option value="gemini-2.0-flash" @selected(($settings->gemini_model ?? '') === 'gemini-2.0-flash')>Gemini 2.0 Flash</option>
                            </select>
                        </label>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <label class="field">
                            Restriction Template
                            <select id="restriction-template-select" name="restriction_template" required>
                                <option value="strict_retrieval" @selected(($settings->restriction_template ?? 'strict_retrieval') === 'strict_retrieval')>Strict Document Grounding</option>
                                <option value="hr_policy" @selected(($settings->restriction_template ?? '') === 'hr_policy')>HR Policy Assistant</option>
                                <option value="general_faq" @selected(($settings->restriction_template ?? '') === 'general_faq')>General FAQ Bot</option>
                                <option value="custom" @selected(($settings->restriction_template ?? '') === 'custom')>Custom System Instructions</option>
                            </select>
                        </label>
                        <div style="display: flex; flex-direction: column; justify-content: center;">
                            <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; cursor: pointer; margin-top: 18px;">
                                <input type="hidden" name="strict_context_enforcement" value="0">
                                <input id="strict-context-checkbox" name="strict_context_enforcement" type="checkbox" value="1" @checked($settings->strict_context_enforcement ?? true)>
                                Strict Context Grounding (Cannot answer out of org data)
                            </label>
                        </div>
                    </div>

                    <label class="field">
                        Custom System Instructions
                        <textarea id="custom-prompt-textarea" name="custom_system_prompt" rows="3" style="width: 100%; border: 1px solid var(--line); border-radius: 6px; padding: 10px; font-family: sans-serif; font-size: 13px; resize: vertical;" placeholder="Define instructions for the LLM behavior...">{{ $settings->custom_system_prompt }}</textarea>
                        <span class="help" style="margin-top: 4px; display: block; font-size: 11px; color: var(--muted);">Choose a template above to prefill, or enter custom instructions.</span>
                    </label>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <label class="field">
                            Organization Context & Details
                            <textarea name="organization_details" rows="3" style="width: 100%; border: 1px solid var(--line); border-radius: 6px; padding: 10px; font-family: sans-serif; font-size: 13px; resize: vertical;" placeholder="Enter organization details to embed in chatbot memory (e.g. name, industry, branch offices)...">{{ $settings->organization_details }}</textarea>
                            <span class="help" style="margin-top: 4px; display: block; font-size: 11px; color: var(--muted);">Tell the AI details about your organization to prefill into conversation contexts.</span>
                        </label>
                        <label class="field">
                            Eligibility Rules & Guidelines
                            <textarea name="eligibility_criteria" rows="3" style="width: 100%; border: 1px solid var(--line); border-radius: 6px; padding: 10px; font-family: sans-serif; font-size: 13px; resize: vertical;" placeholder="Enter specific eligibility rules (e.g. leave criteria, remote work guidelines)...">{{ $settings->eligibility_criteria }}</textarea>
                            <span class="help" style="margin-top: 4px; display: block; font-size: 11px; color: var(--muted);">Define constraints and policies regarding user permissions or eligibility checks.</span>
                        </label>
                    </div>
                </div>

                <div style="max-width: 200px;">
                    <button class="button" type="submit">Save Agent Settings</button>
                </div>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const templateSelect = document.getElementById('restriction-template-select');
                    const promptTextarea = document.getElementById('custom-prompt-textarea');
                    const strictCheckbox = document.getElementById('strict-context-checkbox');

                    const prompts = {
                        strict_retrieval: "You are a strict organizational assistant. Answer the user's question strictly using the provided context. Do not use external knowledge or general facts.",
                        hr_policy: "You are an HR Assistant. Answer questions regarding employee benefits, leaves, and policies using the provided context. Keep it direct and professional.",
                        general_faq: "You are a helpful customer FAQ assistant. Use the provided context to answer questions about our services and guidelines."
                    };

                    templateSelect.addEventListener('change', function() {
                        const val = templateSelect.value;
                        if (prompts[val] !== undefined) {
                            promptTextarea.value = prompts[val];
                            if (val === 'strict_retrieval' || val === 'hr_policy') {
                                strictCheckbox.checked = true;
                            } else {
                                strictCheckbox.checked = false;
                            }
                        } else if (val === 'custom') {
                            promptTextarea.focus();
                        }
                    });
                });
            </script>
        @endif

        <!-- TAB 2: WIDGET DESIGN -->
        @if ($tab === 'design')
            <form method="POST" action="{{ route('admin.settings.update', ['tab' => 'design']) }}">
                @csrf
                @method('PATCH')
                <!-- Hidden inputs to maintain agent settings -->
                <input type="hidden" name="assistant_name" value="{{ $settings->assistant_name }}">
                <input type="hidden" name="minutes_saved_per_resolved_question" value="{{ $settings->minutes_saved_per_resolved_question }}">
                <input type="hidden" name="assistant_status" value="{{ $settings->assistant_status }}">
                <input type="hidden" name="chatbot_data_source" value="{{ $settings->chatbot_data_source }}">
                <input type="hidden" name="chatbot_intelligence_level" value="{{ $settings->chatbot_intelligence_level }}">
                <input type="hidden" name="chatbot_ticket_creation" value="{{ $settings->chatbot_ticket_creation }}">
                <input type="hidden" name="chatbot_mode" value="{{ $settings->chatbot_mode }}">
                @if($settings->chatbot_escalation_enabled)
                    <input type="hidden" name="chatbot_escalation_enabled" value="1">
                @endif
                @if($settings->hr_api_connected)
                    <input type="hidden" name="hr_api_connected" value="1">
                @endif
                
                <input type="hidden" name="gemini_api_key" value="{{ $settings->gemini_api_key }}">
                <input type="hidden" name="gemini_model" value="{{ $settings->gemini_model }}">
                <input type="hidden" name="restriction_template" value="{{ $settings->restriction_template }}">
                <input type="hidden" name="custom_system_prompt" value="{{ $settings->custom_system_prompt }}">
                <input type="hidden" name="organization_details" value="{{ $settings->organization_details }}">
                <input type="hidden" name="eligibility_criteria" value="{{ $settings->eligibility_criteria }}">
                @if($settings->strict_context_enforcement)
                    <input type="hidden" name="strict_context_enforcement" value="1">
                @endif
                
                <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 24px; align-items: start;">
                    <div class="card">
                        <h3 style="margin-bottom: 16px; font-size: 16px; border-bottom: 1px solid var(--line); padding-bottom: 8px;">Branding Customizer</h3>
                        
                        <div style="display: grid; gap: 18px; margin-bottom: 24px;">
                            <label class="field">
                                Widget Theme Color
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <input id="color-picker" type="color" value="{{ $settings->chatbot_color_palette ?: '#d22630' }}" style="width: 42px; height: 38px; padding: 2px; cursor: pointer; border-radius: 6px;">
                                    <input id="color-hex" name="chatbot_color_palette" type="text" value="{{ $settings->chatbot_color_palette ?: '#d22630' }}" style="flex: 1;" required>
                                </div>
                            </label>

                            <label class="field">
                                Widget Icon
                                <select name="chatbot_icon" id="widget-icon" required>
                                    <option value="robot" @selected($settings->chatbot_icon === 'robot')>🤖 Robot Avatar</option>
                                    <option value="chat" @selected($settings->chatbot_icon === 'chat')>💬 Chat Bubble</option>
                                    <option value="support" @selected($settings->chatbot_icon === 'support')>👤 HR Support Agent</option>
                                    <option value="star" @selected($settings->chatbot_icon === 'star')>✨ Sparkle Star</option>
                                </select>
                            </label>
                        </div>

                        <div style="max-width: 200px;">
                            <button class="button" type="submit">Save Design</button>
                        </div>
                    </div>

                    <!-- Chat Widget Live Preview -->
                    <div class="card">
                        <h3 style="margin-bottom: 12px; font-size: 15px; border-bottom: 1px solid var(--line); padding-bottom: 8px;">Branding Live Preview</h3>
                        
                        <div style="border: 1px solid var(--line); border-radius: 12px; overflow: hidden; background: #faf9f6; box-shadow: 0 8px 30px rgba(0,0,0,0.06);">
                            <div id="preview-header" style="background: {{ $settings->chatbot_color_palette ?: '#d22630' }}; color: #ffffff; padding: 14px; display: flex; align-items: center; gap: 10px; transition: background 0.3s ease;">
                                <span id="preview-header-icon" style="font-size: 20px; background: rgba(255,255,255,0.2); width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                    {{ $settings->chatbot_icon === 'robot' ? '🤖' : ($settings->chatbot_icon === 'support' ? '👤' : ($settings->chatbot_icon === 'star' ? '✨' : '💬')) }}
                                </span>
                                <div>
                                    <div style="font-weight: 700; font-size: 13.5px;" id="preview-bot-name">{{ $settings->assistant_name ?: 'Aprilo Bot' }}</div>
                                    <div style="font-size: 10px; opacity: 0.8;">Active Now</div>
                                </div>
                            </div>

                            <div id="preview-chat-body" style="padding: 16px; display: flex; flex-direction: column; gap: 12px; min-height: 250px; max-height: 280px; overflow-y: auto; font-size: 12px; line-height: 1.5; background: #faf9f6;">
                                <div style="align-self: flex-start; background: #ffffff; border: 1px solid var(--line); padding: 8px 12px; border-radius: 12px 12px 12px 2px; max-width: 85%; color: var(--ink);">
                                    Hi there! I am your AI HR Assistant. Feel free to ask about company documents and guidelines!
                                </div>
                            </div>

                            <div style="padding: 10px; background: #ffffff; border-top: 1px solid var(--line); display: flex; gap: 6px; align-items: center;">
                                <input type="text" id="preview-input" placeholder="Ask a question to test..." style="flex: 1; border: 1px solid #cfc7b8; border-radius: 99px; padding: 6px 12px; font-size: 11.5px; background: #faf9f6; outline: none; color: var(--ink);" autocomplete="off">
                                <button id="preview-send-btn" type="button" style="background: {{ $settings->chatbot_color_palette ?: '#d22630' }}; border: 0; width: 28px; height: 28px; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.3s ease;">
                                    ➜
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const colorPicker = document.getElementById('color-picker');
                    const colorHex = document.getElementById('color-hex');
                    const widgetIconSelect = document.getElementById('widget-icon');
                    const previewHeader = document.getElementById('preview-header');
                    const previewSendBtn = document.getElementById('preview-send-btn');
                    const previewHeaderIcon = document.getElementById('preview-header-icon');
                    const previewInput = document.getElementById('preview-input');
                    const previewChatBody = document.getElementById('preview-chat-body');

                    // Sync widget styling inputs with preview
                    colorPicker.addEventListener('input', function() {
                        colorHex.value = colorPicker.value;
                        previewHeader.style.background = colorPicker.value;
                        previewSendBtn.style.background = colorPicker.value;
                    });

                    colorHex.addEventListener('input', function() {
                        if (colorHex.value.match(/^#[0-9A-Fa-f]{6}$/)) {
                            colorPicker.value = colorHex.value;
                            previewHeader.style.background = colorHex.value;
                            previewSendBtn.style.background = colorHex.value;
                        }
                    });

                    widgetIconSelect.addEventListener('change', function() {
                        const val = widgetIconSelect.value;
                        let emoji = '🤖';
                        if (val === 'chat') emoji = '💬';
                        if (val === 'support') emoji = '👤';
                        if (val === 'star') emoji = '✨';
                        previewHeaderIcon.textContent = emoji;
                    });

                    // Live Testing RAG Preview handler
                    function sendPreviewMessage() {
                        const questionText = previewInput.value.trim();
                        if (!questionText) return;

                        // Append User bubble
                        const userMsg = document.createElement('div');
                        userMsg.style.cssText = "align-self: flex-end; background: #eef1ea; padding: 8px 12px; border-radius: 12px 12px 2px 12px; max-width: 85%; color: var(--ink); border: 1px solid rgba(0,0,0,0.05); word-break: break-word;";
                        userMsg.textContent = questionText;
                        previewChatBody.appendChild(userMsg);

                        // Clear input
                        previewInput.value = '';

                        // Append Typing Loader
                        const loaderMsg = document.createElement('div');
                        loaderMsg.id = 'preview-chat-loader';
                        loaderMsg.style.cssText = "align-self: flex-start; background: #ffffff; border: 1px solid var(--line); padding: 8px 12px; border-radius: 12px 12px 12px 2px; max-width: 85%; color: var(--muted); font-style: italic;";
                        loaderMsg.textContent = 'Thinking...';
                        previewChatBody.appendChild(loaderMsg);
                        previewChatBody.scrollTop = previewChatBody.scrollHeight;

                        // Fetch Response via web session route
                        const csrfToken = document.querySelector('input[name="_token"]').value;
                        fetch('{{ route("admin.settings.preview") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ question_text: questionText })
                        })
                        .then(res => res.json())
                        .then(data => {
                            const loader = document.getElementById('preview-chat-loader');
                            if (loader) loader.remove();

                            const botMsg = document.createElement('div');
                            botMsg.style.cssText = "align-self: flex-start; background: #ffffff; border: 1px solid var(--line); padding: 8px 12px; border-radius: 12px 12px 12px 2px; max-width: 85%; color: var(--ink); display: flex; flex-direction: column; gap: 8px; word-break: break-word;";
                            
                            const textDiv = document.createElement('div');
                            textDiv.textContent = data.answer_text;
                            botMsg.appendChild(textDiv);

                            if (data.sources && data.sources.length > 0) {
                                const citationsDiv = document.createElement('div');
                                citationsDiv.style.cssText = "border-top: 1px solid var(--line); margin-top: 4px; padding-top: 6px; font-size: 10px; color: var(--muted); display: flex; flex-direction: column; gap: 4px;";
                                citationsDiv.innerHTML = '<strong>Citations:</strong>';
                                
                                data.sources.forEach(src => {
                                    const srcSpan = document.createElement('div');
                                    srcSpan.style.cssText = "background: rgba(0,0,0,0.02); border: 1px solid var(--line); padding: 4px 6px; border-radius: 4px;";
                                    srcSpan.innerHTML = `<span style="font-weight: 700; color: ${colorPicker.value};">${src.citation_label}</span> <strong>${src.title}</strong>: <span style="font-style: italic;">"${src.chunk_text.substring(0, 75)}..."</span>`;
                                    citationsDiv.appendChild(srcSpan);
                                });
                                botMsg.appendChild(citationsDiv);
                            }

                            previewChatBody.appendChild(botMsg);
                            previewChatBody.scrollTop = previewChatBody.scrollHeight;
                        })
                        .catch(err => {
                            const loader = document.getElementById('preview-chat-loader');
                            if (loader) loader.remove();

                            const errorMsg = document.createElement('div');
                            errorMsg.style.cssText = "align-self: flex-start; background: #fff1f0; border: 1px solid #ffd5d2; padding: 8px 12px; border-radius: 12px; max-width: 85%; color: #9d2b22;";
                            errorMsg.textContent = 'Error: Failed to get AI response.';
                            previewChatBody.appendChild(errorMsg);
                            previewChatBody.scrollTop = previewChatBody.scrollHeight;
                        });
                    }

                    previewSendBtn.addEventListener('click', sendPreviewMessage);
                    previewInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            sendPreviewMessage();
                        }
                    });
                });
            </script>
        @endif

        <!-- TAB 3: CONNECT INTEGRATIONS -->
        @if ($tab === 'connect')
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <!-- Integration Card 1: Teams -->
                <div class="card" style="position: relative;">
                    @if (!in_array($organization->plan, ['pro', 'enterprise'], true))
                        <div style="position: absolute; inset: 0; background: rgba(255,255,255,0.7); z-index: 10; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(2px); border-radius: 8px;">
                            <span style="font-size: 24px; margin-bottom: 8px;">🔒</span>
                            <strong style="color: var(--ink);">Requires Pro Plan</strong>
                            <a href="{{ route('admin.settings', ['tab' => 'pricing']) }}" style="margin-top: 10px; font-size: 12px; color: var(--green); text-decoration: underline; font-weight: 700;">Upgrade Plan</a>
                        </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div>
                            <span style="font-size: 28px;">👥</span>
                            <h3 style="font-size: 16px; margin: 8px 0 4px;">Microsoft Teams</h3>
                        </div>
                        @if ($settings->teams_connected)
                            <span class="pill" style="background: #e6f7ed; color: #1f7a3f; font-size: 10px;">Connected</span>
                        @else
                            <span class="pill" style="background: #f4f6f7; color: var(--muted); font-size: 10px;">Not Configured</span>
                        @endif
                    </div>
                    <p style="font-size: 13px; color: var(--muted); line-height: 1.5; margin-bottom: 16px;">
                        Directly stream fallback tickets to your Microsoft Teams HR channel via incoming webhook.
                    </p>
                    <a href="{{ route('admin.settings.connect', 'teams') }}" class="button" style="display: inline-block; text-align: center; font-size: 12px; width: auto; padding: 8px 16px;">
                        {{ $settings->teams_connected ? 'Edit Connection' : 'Configure Setup' }}
                    </a>
                </div>

                <!-- Integration Card 2: Skype -->
                <div class="card" style="position: relative;">
                    @if ($organization->plan !== 'enterprise')
                        <div style="position: absolute; inset: 0; background: rgba(255,255,255,0.7); z-index: 10; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(2px); border-radius: 8px;">
                            <span style="font-size: 24px; margin-bottom: 8px;">🔒</span>
                            <strong style="color: var(--ink);">Requires Enterprise Plan</strong>
                            <a href="{{ route('admin.settings', ['tab' => 'pricing']) }}" style="margin-top: 10px; font-size: 12px; color: var(--green); text-decoration: underline; font-weight: 700;">Upgrade Plan</a>
                        </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div>
                            <span style="font-size: 28px;">💬</span>
                            <h3 style="font-size: 16px; margin: 8px 0 4px;">Skype Channel</h3>
                        </div>
                        @if ($settings->skype_connected)
                            <span class="pill" style="background: #e6f7ed; color: #1f7a3f; font-size: 10px;">Connected</span>
                        @else
                            <span class="pill" style="background: #f4f6f7; color: var(--muted); font-size: 10px;">Not Configured</span>
                        @endif
                    </div>
                    <p style="font-size: 13px; color: var(--muted); line-height: 1.5; margin-bottom: 16px;">
                        Route tickets and connect your company bot with Skype's native user API.
                    </p>
                    <a href="{{ route('admin.settings.connect', 'skype') }}" class="button" style="display: inline-block; text-align: center; font-size: 12px; width: auto; padding: 8px 16px;">
                        {{ $settings->skype_connected ? 'Edit Connection' : 'Configure Setup' }}
                    </a>
                </div>

                <!-- Integration Card 3: WhatsApp -->
                <div class="card" style="position: relative;">
                    @if ($organization->plan !== 'enterprise')
                        <div style="position: absolute; inset: 0; background: rgba(255,255,255,0.7); z-index: 10; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(2px); border-radius: 8px;">
                            <span style="font-size: 24px; margin-bottom: 8px;">🔒</span>
                            <strong style="color: var(--ink);">Requires Enterprise Plan</strong>
                            <a href="{{ route('admin.settings', ['tab' => 'pricing']) }}" style="margin-top: 10px; font-size: 12px; color: var(--green); text-decoration: underline; font-weight: 700;">Upgrade Plan</a>
                        </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div>
                            <span style="font-size: 28px;">🟢</span>
                            <h3 style="font-size: 16px; margin: 8px 0 4px;">WhatsApp for Business</h3>
                        </div>
                        @if ($settings->whatsapp_connected)
                            <span class="pill" style="background: #e6f7ed; color: #1f7a3f; font-size: 10px;">Connected</span>
                        @else
                            <span class="pill" style="background: #f4f6f7; color: var(--muted); font-size: 10px;">Not Configured</span>
                        @endif
                    </div>
                    <p style="font-size: 13px; color: var(--muted); line-height: 1.5; margin-bottom: 16px;">
                        Send automated ticket creation notices and resolution statuses over WhatsApp API.
                    </p>
                    <a href="{{ route('admin.settings.connect', 'whatsapp') }}" class="button" style="display: inline-block; text-align: center; font-size: 12px; width: auto; padding: 8px 16px;">
                        {{ $settings->whatsapp_connected ? 'Edit Connection' : 'Configure Setup' }}
                    </a>
                </div>

                <!-- Integration Card 4: Mail SMTP -->
                <div class="card" style="position: relative;">
                    @if (!in_array($organization->plan, ['pro', 'enterprise'], true))
                        <div style="position: absolute; inset: 0; background: rgba(255,255,255,0.7); z-index: 10; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(2px); border-radius: 8px;">
                            <span style="font-size: 24px; margin-bottom: 8px;">🔒</span>
                            <strong style="color: var(--ink);">Requires Pro Plan</strong>
                            <a href="{{ route('admin.settings', ['tab' => 'pricing']) }}" style="margin-top: 10px; font-size: 12px; color: var(--green); text-decoration: underline; font-weight: 700;">Upgrade Plan</a>
                        </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div>
                            <span style="font-size: 28px;">✉️</span>
                            <h3 style="font-size: 16px; margin: 8px 0 4px;">SMTP Email Alerts</h3>
                        </div>
                        @if ($settings->mail_connected)
                            <span class="pill" style="background: #e6f7ed; color: #1f7a3f; font-size: 10px;">Connected</span>
                        @else
                            <span class="pill" style="background: #f4f6f7; color: var(--muted); font-size: 10px;">Not Configured</span>
                        @endif
                    </div>
                    <p style="font-size: 13px; color: var(--muted); line-height: 1.5; margin-bottom: 16px;">
                        Dispatch structured email notifications directly to your corporate HR support desk.
                    </p>
                    <a href="{{ route('admin.settings.connect', 'mail') }}" class="button" style="display: inline-block; text-align: center; font-size: 12px; width: auto; padding: 8px 16px;">
                        {{ $settings->mail_connected ? 'Edit Connection' : 'Configure Setup' }}
                    </a>
                </div>
            </div>
        @endif

        <!-- TAB 4: DEPLOY SCRIPT -->
        @if ($tab === 'deploy')
            <div class="card" style="max-width: 800px;">
                <h3 style="margin-bottom: 12px; font-size: 16px; border-bottom: 1px solid var(--line); padding-bottom: 8px;">Web Script Deployment</h3>
                <p class="help" style="margin-top: 0; margin-bottom: 16px;">
                    Integrate the Aprilo AI Chatbot widget directly into any HTML webpage. Simply copy and paste the snippet below before the closing <code>&lt;/body&gt;</code> tag.
                </p>

                <div style="margin-bottom: 20px;">
                    <textarea id="deploy-script" readonly style="width: 100%; min-height: 150px; font-family: monospace; font-size: 12px; padding: 12px; border-radius: 8px; border: 1px solid var(--line); background: #fdfcf9; outline: none; line-height: 1.6;"><!-- Aprilo AI Chatbot Integration Script -->
<script
  src="https://cdn.aprilo.ai/widget.js"
  data-subdomain="{{ $organization->subdomain ?: 'demo-brand' }}"
  data-bot-name="{{ $settings->assistant_name ?: 'Aprilo Bot' }}"
  data-color-palette="{{ $settings->chatbot_color_palette ?: '#d22630' }}"
  data-icon="{{ $settings->chatbot_icon ?: 'robot' }}"
  async
></script></textarea>
                </div>

                <div style="display: flex; gap: 8px; align-items: center;">
                    <button class="button" onclick="copyScriptCode()" style="width: auto; padding: 10px 20px; font-size: 13px;">Copy Snippet Code</button>
                    <span id="copy-success" style="font-size: 12px; color: #1f7a3f; font-weight: 700; display: none;">✓ Snippet copied!</span>
                </div>
            </div>

            <script>
                function copyScriptCode() {
                    const text = document.getElementById('deploy-script');
                    text.select();
                    document.execCommand('copy');
                    
                    const success = document.getElementById('copy-success');
                    success.style.display = 'inline';
                    setTimeout(() => {
                        success.style.display = 'none';
                    }, 2500);
                }
            </script>
        @endif

        <!-- TAB 5: PLANS & PRICING -->
        @if ($tab === 'pricing')
            <div style="display: grid; gap: 24px;">
                <!-- Billing Plan Mode Selector Card -->
                <div class="card">
                    <h3 style="margin-bottom: 8px; font-size: 16px;">Billing Options</h3>
                    <p class="help" style="margin-top: 0; margin-bottom: 16px;">Select your preferred billing model. You can pay a fixed monthly subscription or choose a pay-as-you-go model based on the number of resolved queries.</p>
                    
                    <form method="POST" action="{{ route('admin.settings.billing.update') }}" style="display: grid; gap: 16px; max-width: 500px;">
                        @csrf
                        <div style="display: flex; gap: 20px;">
                            <label style="flex: 1; border: 1px solid var(--line); border-radius: 8px; padding: 14px; display: flex; align-items: flex-start; gap: 10px; cursor: pointer; background: {{ $settings->billing_mode === 'subscription' ? 'rgba(210,38,48,0.03)' : '#ffffff' }}; border-color: {{ $settings->billing_mode === 'subscription' ? 'var(--green)' : 'var(--line)' }};">
                                <input type="radio" name="billing_mode" value="subscription" @checked($settings->billing_mode === 'subscription') style="margin-top: 4px;">
                                <div>
                                    <strong>Subscription Plan</strong>
                                    <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">Fixed monthly fee. Best for high volume organizations.</div>
                                </div>
                            </label>
                            
                            <label style="flex: 1; border: 1px solid var(--line); border-radius: 8px; padding: 14px; display: flex; align-items: flex-start; gap: 10px; cursor: pointer; background: {{ $settings->billing_mode === 'pay_as_you_go' ? 'rgba(210,38,48,0.03)' : '#ffffff' }}; border-color: {{ $settings->billing_mode === 'pay_as_you_go' ? 'var(--green)' : 'var(--line)' }};">
                                <input type="radio" name="billing_mode" value="pay_as_you_go" @checked($settings->billing_mode === 'pay_as_you_go') style="margin-top: 4px;">
                                <div>
                                    <strong>Pay-As-You-Go</strong>
                                    <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">$0.05 per resolved query. Ideal for light testing and small setups.</div>
                                </div>
                            </label>
                        </div>
                        <button type="submit" class="button" style="width: auto; padding: 10px 20px;">Save Billing Option</button>
                    </form>
                </div>

                <!-- Current Metered Usage Statistics -->
                <div class="card" style="background: #faf8f5; border-color: var(--line);">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--line); padding-bottom: 12px; margin-bottom: 16px;">
                        <div>
                            <h3 style="margin: 0; font-size: 15px;">Current Billing Cycle Usage</h3>
                            <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">Metered usage stats for the current calendar month.</div>
                        </div>
                        <form method="POST" action="{{ route('admin.settings.billing.close-cycle') }}" style="margin: 0; padding: 0; border: 0; box-shadow: none; width: auto;">
                            @csrf
                            <button type="submit" style="background: var(--green); color: #fff; border: 0; border-radius: 6px; font-size: 12px; padding: 8px 16px; cursor: pointer; font-weight: 700; transition: background 0.2s ease;">Generate Invoice & Close Cycle</button>
                        </form>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                        <div style="background: #ffffff; border: 1px solid var(--line); padding: 16px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 26px; font-weight: 800; color: var(--green);">
                                {{ $settings->billing_mode === 'pay_as_you_go' ? $settings->usage_queries_count : 'Fixed Rate' }}
                            </div>
                            <div style="font-size: 11px; color: var(--muted); margin-top: 4px; text-transform: uppercase; font-weight: 700;">Queries Processed</div>
                        </div>
                        <div style="background: #ffffff; border: 1px solid var(--line); padding: 16px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 26px; font-weight: 800; color: var(--green);">
                                {{ $settings->billing_mode === 'pay_as_you_go' ? '$' . number_format($settings->usage_amount_due, 2) : '$' . number_format($organization->plan === 'pro' ? 49.00 : 199.00, 2) }}
                            </div>
                            <div style="font-size: 11px; color: var(--muted); margin-top: 4px; text-transform: uppercase; font-weight: 700;">Accrued Bill</div>
                        </div>
                        <div style="background: #ffffff; border: 1px solid var(--line); padding: 16px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 26px; font-weight: 800; color: var(--green);">
                                {{ $settings->billing_mode === 'pay_as_you_go' ? '$0.05 / query' : 'Monthly Sub' }}
                            </div>
                            <div style="font-size: 11px; color: var(--muted); margin-top: 4px; text-transform: uppercase; font-weight: 700;">Rate Type</div>
                        </div>
                    </div>
                </div>

                <!-- Payment History / Invoice Records Table -->
                <div class="card">
                    <h3 style="margin-bottom: 12px; font-size: 15px; border-bottom: 1px solid var(--line); padding-bottom: 8px;">Payment & Invoice History</h3>
                    @if ($invoices->isEmpty())
                        <div style="text-align: center; padding: 30px; color: var(--muted); font-size: 13px;">
                            No invoices generated yet. Accrue query traffic and click "Generate Invoice & Close Cycle" above to generate a downloadable bill.
                        </div>
                    @else
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--line); color: var(--muted);">
                                    <th style="padding: 10px 8px;">Invoice Number</th>
                                    <th style="padding: 10px 8px;">Billing Period</th>
                                    <th style="padding: 10px 8px;">Billing Mode</th>
                                    <th style="padding: 10px 8px;">Amount</th>
                                    <th style="padding: 10px 8px;">Status</th>
                                    <th style="padding: 10px 8px; text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                    <tr style="border-bottom: 1px solid var(--line);">
                                        <td style="padding: 12px 8px; font-weight: 700;">{{ $invoice->invoice_number }}</td>
                                        <td style="padding: 12px 8px; color: var(--muted);">
                                            {{ $invoice->billing_period_start->format('M d') }} - {{ $invoice->billing_period_end->format('M d, Y') }}
                                        </td>
                                        <td style="padding: 12px 8px; text-transform: capitalize;">{{ str_replace('_', ' ', $invoice->billing_mode) }}</td>
                                        <td style="padding: 12px 8px; font-weight: 700; color: var(--green);">${{ number_format($invoice->amount, 2) }}</td>
                                        <td style="padding: 12px 8px;">
                                            <span style="background: #e6f7ed; color: #1f7a3f; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 99px;">{{ ucfirst($invoice->status) }}</span>
                                        </td>
                                        <td style="padding: 12px 8px; text-align: right;">
                                            <a href="{{ route('admin.settings.invoices.show', $invoice->id) }}" class="button btn-secondary" style="font-size: 11px; padding: 6px 12px; width: auto; display: inline-block; text-decoration: none;">View invoice PDF</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <!-- Plans Grid (Original Plans UI kept for completeness and upgrades) -->
                <div class="card">
                    <h3 style="margin-bottom: 16px; font-size: 15px;">Available Subscription Tiers</h3>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                        <!-- Plan 1: Free -->
                        <div style="border: 1px solid {{ $organization->plan === 'basic' ? 'var(--green)' : 'var(--line)' }}; padding: 20px; border-radius: 10px; display: flex; flex-direction: column; justify-content: space-between; min-height: 280px; position: relative; background: #ffffff;">
                            <div>
                                <h4 style="font-size: 16px; margin: 0 0 8px;">Basic Free</h4>
                                <div style="font-size: 24px; font-weight: 700; margin-bottom: 12px;">$0 <span style="font-size: 11px; color: var(--muted); font-weight: 400;">/mo</span></div>
                                <ul style="list-style: none; margin: 0; padding: 0; font-size: 12px; line-height: 1.6;">
                                    <li>✓ Web Widget</li>
                                    <li>✓ Standard Logic</li>
                                    <li style="color: var(--muted); text-decoration: line-through;">✗ Teams & Skype</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Plan 2: Pro -->
                        <div style="border: 1px solid {{ $organization->plan === 'pro' ? 'var(--green)' : 'var(--line)' }}; padding: 20px; border-radius: 10px; display: flex; flex-direction: column; justify-content: space-between; min-height: 280px; position: relative; background: #ffffff;">
                            @if ($organization->plan === 'pro')
                                <span style="position: absolute; top: -10px; right: 15px; background: var(--green); color: #fff; font-size: 9px; font-weight: 700; padding: 2px 8px; border-radius: 99px;">ACTIVE</span>
                            @endif
                            <div>
                                <h4 style="font-size: 16px; margin: 0 0 8px;">Pro Suite</h4>
                                <div style="font-size: 24px; font-weight: 700; margin-bottom: 12px;">$49 <span style="font-size: 11px; color: var(--muted); font-weight: 400;">/mo</span></div>
                                <ul style="list-style: none; margin: 0; padding: 0; font-size: 12px; line-height: 1.6;">
                                    <li>✓ Teams Channels</li>
                                    <li>✓ Email Alerts</li>
                                    <li style="color: var(--muted); text-decoration: line-through;">✗ Skype & WhatsApp</li>
                                </ul>
                            </div>
                            @if ($organization->plan !== 'pro' && $organization->plan !== 'enterprise')
                                <button type="button" onclick="openPaymentModal('pro', 49)" class="button" style="font-size: 12px; padding: 6px 12px; margin-top: 10px;">Upgrade</button>
                            @endif
                        </div>

                        <!-- Plan 3: Enterprise -->
                        <div style="border: 1px solid {{ $organization->plan === 'enterprise' ? 'var(--green)' : 'var(--line)' }}; padding: 20px; border-radius: 10px; display: flex; flex-direction: column; justify-content: space-between; min-height: 280px; position: relative; background: #ffffff;">
                            @if ($organization->plan === 'enterprise')
                                <span style="position: absolute; top: -10px; right: 15px; background: var(--green); color: #fff; font-size: 9px; font-weight: 700; padding: 2px 8px; border-radius: 99px;">ACTIVE</span>
                            @endif
                            <div>
                                <h4 style="font-size: 16px; margin: 0 0 8px;">Enterprise</h4>
                                <div style="font-size: 24px; font-weight: 700; margin-bottom: 12px;">$199 <span style="font-size: 11px; color: var(--muted); font-weight: 400;">/mo</span></div>
                                <ul style="list-style: none; margin: 0; padding: 0; font-size: 12px; line-height: 1.6;">
                                    <li>✓ Teams & SMTP</li>
                                    <li>✓ Skype & WhatsApp</li>
                                    <li>✓ High Reasoning LLM</li>
                                </ul>
                            </div>
                            @if ($organization->plan !== 'enterprise')
                                <button type="button" onclick="openPaymentModal('enterprise', 199)" class="button" style="font-size: 12px; padding: 6px 12px; margin-top: 10px;">Upgrade</button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment Checkout Modal Container -->
                <div id="payment-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                    <form class="card" method="POST" action="{{ route('admin.settings.upgrade') }}" style="width: min(440px, 90%); background: #ffffff; padding: 26px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.25);">
                        @csrf
                        <input type="hidden" name="plan" id="selected-plan-input">
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--line); padding-bottom: 12px; margin-bottom: 16px;">
                            <h3 style="margin: 0; font-size: 16px;">Checkout Payment</h3>
                            <span onclick="closePaymentModal()" style="font-size: 20px; font-weight: 700; cursor: pointer; color: var(--muted);">&times;</span>
                        </div>

                        <div style="background: #faf9f6; border: 1px solid var(--line); border-radius: 8px; padding: 12px; font-size: 13px; margin-bottom: 18px;">
                            Upgrading subscription to: <strong id="modal-plan-name" style="text-transform: capitalize;">Plan</strong>
                            <br>Monthly fee: <strong id="modal-plan-price" style="color: var(--green);">$0.00</strong>
                        </div>

                        <div style="display: grid; gap: 14px; margin-bottom: 20px;">
                            <label class="field">
                                Credit Card Number
                                <input name="card_number" type="text" placeholder="1234 5678 1234 5678" required maxlength="16" pattern="\d{16}" title="Please enter 16 digits without spaces.">
                            </label>
                            
                            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 12px;">
                                <label class="field">
                                    Expiration (MM/YY)
                                    <input name="card_expiry" type="text" placeholder="12/28" required maxlength="5" pattern="(0[1-9]|1[0-2])\/\d{2}" title="Expiration date in MM/YY format.">
                                </label>
                                <label class="field">
                                    CVV
                                    <input name="card_cvv" type="password" placeholder="***" required maxlength="3" pattern="\d{3}" title="3 digit security code.">
                                </label>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <button type="submit" style="flex: 1;">Submit Payment</button>
                            <button type="button" onclick="closePaymentModal()" style="flex: 1; background: transparent; border: 1px solid var(--line); color: var(--ink);">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function openPaymentModal(planKey, price) {
                    document.getElementById('selected-plan-input').value = planKey;
                    document.getElementById('modal-plan-name').textContent = planKey;
                    document.getElementById('modal-plan-price').textContent = '$' + price + '.00 /month';
                    document.getElementById('payment-modal').style.display = 'flex';
                }

                function closePaymentModal() {
                    document.getElementById('payment-modal').style.display = 'none';
                }
            </script>
        @endif
    </div>
@endsection
