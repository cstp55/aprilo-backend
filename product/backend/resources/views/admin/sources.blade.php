@extends('admin.layout')

@section('title', 'Aprilo AI - Knowledge Base')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow">Knowledge operations</div>
            <h1>Knowledge Base</h1>
            <p class="help">Upload approved HR knowledge. Indexed sources are used by the customer-facing Aprilo AI question experience.</p>
        </div>
    </div>

    <!-- 2-Column Grid Layout -->
    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
        
        <!-- Left Column: Sources Upload & List -->
        <div style="min-width: 0; display: grid; gap: 20px;">
            <form class="card" method="POST" action="{{ route('admin.sources.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-2">
                    <label class="field">
                        Source title
                        <input name="title" value="{{ old('title', 'Employee Handbook') }}" required>
                    </label>
                    <label class="field">
                        Access scope
                        <select name="access_scope" required>
                            <option value="all_employees">All employees</option>
                            <option value="hr_only">HR only</option>
                        </select>
                    </label>
                    <label class="field">
                        Source file
                        <input name="file" type="file" required>
                    </label>
                    <div style="display:flex;align-items:end;">
                        <button class="button" type="submit">Upload and index</button>
                    </div>
                </div>
            </form>

            <div>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Chunks</th>
                            <th>Scope</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sources as $source)
                            @php
                                $pillStyle = match($source->status) {
                                    'indexed' => 'background: rgba(46, 204, 113, 0.15); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.3); padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase;',
                                    'failed' => 'background: rgba(231, 76, 60, 0.15); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.3); padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase;',
                                    'indexing', 'uploaded' => 'background: rgba(241, 196, 15, 0.15); color: #f1c40f; border: 1px solid rgba(241, 196, 15, 0.3); padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase;',
                                    'inactive' => 'background: rgba(149, 165, 166, 0.15); color: #7f8c8d; border: 1px solid rgba(149, 165, 166, 0.3); padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase;',
                                    default => 'background: rgba(149, 165, 166, 0.15); color: #7f8c8d; padding: 2px 8px; border-radius: 99px; font-size: 11px;'
                                };
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $source->title }}</strong>
                                    @if (!empty($source->metadata['version']) && $source->metadata['version'] > 1)
                                        <span style="font-size: 10px; opacity: 0.6; margin-left: 4px;">(v{{ $source->metadata['version'] }})</span>
                                    @endif
                                </td>
                                <td><span style="font-family: monospace; font-size: 12px; text-transform: uppercase; background: #eef1ea; padding: 2px 6px; border-radius: 4px;">{{ $source->source_type }}</span></td>
                                <td>
                                    <span class="pill" style="{{ $pillStyle }}">{{ $source->status }}</span>
                                    @if ($source->status === 'failed' && !empty($source->metadata['indexing_error']))
                                        <div style="font-size: 11px; color: #e74c3c; margin-top: 5px; max-width: 280px; white-space: normal; line-height: 1.4; font-weight: 500;">
                                            ⚠️ {{ $source->metadata['indexing_error'] }}
                                        </div>
                                    @endif
                                </td>
                                <td style="font-weight: 600;">{{ $source->chunks_count }}</td>
                                <td>
                                    <span style="font-size: 12px; font-weight: 600; text-transform: capitalize; color: #58625f;">
                                        {{ str_replace('_', ' ', $source->access_scope) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No sources uploaded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Column: Live Chat Widget Simulator (Consistent with settings view) -->
        <div class="card" style="padding: 0; overflow: hidden; border-radius: 12px; position: sticky; top: 20px;">
            <div style="padding: 12px 14px 8px; font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; background: #faf9f6; border-bottom: 1px solid var(--line);">
                Widget Simulator
            </div>
            <div style="border: 0; background: #faf9f6;">
                <div id="preview-header" style="background: {{ $settings->chatbot_color_palette ?: '#d22630' }}; color: #ffffff; padding: 12px; display: flex; align-items: center; gap: 8px; transition: background 0.3s ease;">
                    <span id="preview-header-icon" style="font-size: 16px; background: rgba(255,255,255,0.2); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        {{ $settings->chatbot_icon === 'robot' ? '🤖' : ($settings->chatbot_icon === 'support' ? '👤' : ($settings->chatbot_icon === 'star' ? '✨' : '💬')) }}
                    </span>
                    <div>
                        <div style="font-weight: 700; font-size: 12px;" id="preview-bot-name">{{ $settings->assistant_name ?: 'Aprilo Bot' }}</div>
                        <div style="font-size: 9px; opacity: 0.8;">Active & Live</div>
                    </div>
                </div>

                <div id="preview-chat-body" style="padding: 12px; display: flex; flex-direction: column; gap: 10px; min-height: 220px; max-height: 220px; overflow-y: auto; font-size: 11px; line-height: 1.4; background: #faf9f6;">
                    <div style="align-self: flex-start; background: #ffffff; border: 1px solid var(--line); padding: 6px 10px; border-radius: 10px 10px 10px 2px; max-width: 85%; color: var(--ink);">
                        Hi! I am {{ $settings->assistant_name ?: 'Aprilo Bot' }}. How can I assist you with HR policies today?
                    </div>
                </div>

                <div style="padding: 8px; background: #ffffff; border-top: 1px solid var(--line); display: flex; gap: 4px; align-items: center;">
                    <input type="text" id="preview-input" placeholder="Type message to test..." style="flex: 1; border: 1px solid #cfc7b8; border-radius: 99px; padding: 5px 10px; font-size: 11px; background: #faf9f6; outline: none; color: var(--ink);" autocomplete="off">
                    <button id="preview-send-btn" type="button" style="background: {{ $settings->chatbot_color_palette ?: '#d22630' }}; border: 0; width: 24px; height: 24px; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.3s ease;">
                        ➜
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Include Live Simulator Preview script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const previewHeader = document.getElementById('preview-header');
            const previewSendBtn = document.getElementById('preview-send-btn');
            const previewHeaderIcon = document.getElementById('preview-header-icon');
            const previewInput = document.getElementById('preview-input');
            const previewChatBody = document.getElementById('preview-chat-body');

            // Live Testing RAG Preview handler
            function sendPreviewMessage() {
                const questionText = previewInput.value.trim();
                if (!questionText) return;

                // Append User bubble
                const userMsg = document.createElement('div');
                userMsg.style.cssText = "align-self: flex-end; background: #eef1ea; padding: 6px 10px; border-radius: 10px 10px 2px 10px; max-width: 85%; color: var(--ink); border: 1px solid rgba(0,0,0,0.05); word-break: break-word;";
                userMsg.textContent = questionText;
                previewChatBody.appendChild(userMsg);

                // Clear input
                previewInput.value = '';

                // Append Typing Loader
                const loaderMsg = document.createElement('div');
                loaderMsg.id = 'preview-chat-loader';
                loaderMsg.style.cssText = "align-self: flex-start; background: #ffffff; border: 1px solid var(--line); padding: 6px 10px; border-radius: 10px 10px 10px 2px; max-width: 85%; color: var(--muted); font-style: italic;";
                loaderMsg.textContent = 'Thinking...';
                previewChatBody.appendChild(loaderMsg);
                previewChatBody.scrollTop = previewChatBody.scrollHeight;

                // Fetch Response via web session route
                const csrfToken = '{{ csrf_token() }}';
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
                    botMsg.style.cssText = "align-self: flex-start; background: #ffffff; border: 1px solid var(--line); padding: 6px 10px; border-radius: 10px 10px 10px 2px; max-width: 85%; color: var(--ink); display: flex; flex-direction: column; gap: 6px; word-break: break-word;";
                    
                    const textDiv = document.createElement('div');
                    textDiv.textContent = data.answer_text;
                    botMsg.appendChild(textDiv);

                    if (data.sources && data.sources.length > 0) {
                        const citationsDiv = document.createElement('div');
                        citationsDiv.style.cssText = "border-top: 1px solid var(--line); margin-top: 4px; padding-top: 6px; font-size: 9px; color: var(--muted); display: flex; flex-direction: column; gap: 4px;";
                        citationsDiv.innerHTML = '<strong>Citations:</strong>';
                        
                        const themeColor = '{{ $settings->chatbot_color_palette ?: "#d22630" }}';
                        
                        data.sources.forEach(src => {
                            const srcSpan = document.createElement('div');
                            srcSpan.style.cssText = "background: rgba(0,0,0,0.02); border: 1px solid var(--line); padding: 3px 5px; border-radius: 4px;";
                            srcSpan.innerHTML = `<span style="font-weight: 700; color: ${themeColor};">${src.citation_label}</span> <strong>${src.title}</strong>: <span style="font-style: italic;">"${src.chunk_text.substring(0, 50)}..."</span>`;
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
                    errorMsg.style.cssText = "align-self: flex-start; background: #fff1f0; border: 1px solid #ffd5d2; padding: 6px 10px; border-radius: 10px; max-width: 85%; color: #9d2b22;";
                    errorMsg.textContent = 'Error: Failed to get response.';
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
@endsection
