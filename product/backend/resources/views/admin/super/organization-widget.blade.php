@extends('admin.layout')

@section('title', 'Widget Support - ' . $organization->name)

@section('content')
    <div class="header" style="display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; flex-wrap: wrap;">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Super-admin support</div>
            <h1>Widget settings · {{ $organization->name }}</h1>
            <p class="help">Changes apply to this organization's customer-facing widget.</p>
        </div>
        <a class="button" style="width: auto; background: transparent; color: var(--ink); border: 1px solid var(--line); box-shadow: none;" href="{{ route('admin.super.organizations.show', $organization) }}">Organization details</a>
    </div>

    <div class="grid grid-2" style="align-items: start;">
        <div>
            <form class="card" method="POST" action="{{ route('admin.super.organizations.widget.update', $organization) }}">
                @csrf
                @method('PATCH')
                <h2 style="font-size: 18px; margin: 0 0 18px;">Widget settings and design</h2>
                <div class="grid grid-2">
                    <label class="field">Assistant name
                        <input name="assistant_name" value="{{ old('assistant_name', $settings->assistant_name ?: $organization->name . ' Assistant') }}" required maxlength="255">
                    </label>
                    <label class="field">Assistant status
                        <select name="assistant_status" required>
                            <option value="active" @selected(old('assistant_status', $settings->assistant_status) === 'active')>Active</option>
                            <option value="paused" @selected(old('assistant_status', $settings->assistant_status) === 'paused')>Paused</option>
                        </select>
                    </label>
                    <label class="field">Brand color
                        <input name="chatbot_color_palette" type="color" value="{{ old('chatbot_color_palette', $settings->chatbot_color_palette ?: '#d22630') }}" required>
                    </label>
                    <label class="field">Widget icon
                        <select name="chatbot_icon" required>
                            @foreach (['robot' => 'Robot', 'support' => 'Support', 'star' => 'Star', 'chat' => 'Chat'] as $icon => $label)
                                <option value="{{ $icon }}" @selected(old('chatbot_icon', $settings->chatbot_icon ?: 'robot') === $icon)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <label style="display: flex; align-items: center; gap: 9px; margin-top: 16px; font-size: 13px; font-weight: 600;">
                    <input type="checkbox" name="live_chat_enabled" value="1" @checked(old('live_chat_enabled', $settings->live_chat_enabled ?? true))>
                    Allow live-agent support
                </label>
                <button class="button" type="submit" style="width: auto; margin-top: 18px;">Save widget settings</button>
            </form>

            <section class="card" style="margin-top: 16px;">
                <h2 style="font-size: 18px; margin: 0 0 8px;">Public widget key</h2>
                <p class="help" style="margin-top: 0;">This key identifies the organization to the published CDN widget. It is public and does not grant admin access.</p>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <code id="widget-key" style="padding: 8px 10px; background: #f4f5f5; border: 1px solid var(--line); border-radius: 4px; overflow-wrap: anywhere;">{{ $widgetKey }}</code>
                    <button class="button" type="button" onclick="navigator.clipboard.writeText(document.getElementById('widget-key').textContent.trim())" style="width: auto;">Copy key</button>
                </div>
                <form method="POST" action="{{ route('admin.super.organizations.widget.rotate-key', $organization) }}" style="margin: 14px 0 0; padding: 0; border: 0; box-shadow: none;" onsubmit="return confirm('Rotating this key will disable the old embed until the customer site is updated. Continue?')">
                    @csrf
                    <button class="button" type="submit" style="width: auto; background: #fff; color: #9f2727; border: 1px solid #e8caca; box-shadow: none;">Rotate key</button>
                </form>
            </section>

            <section class="card" style="margin-top: 16px;">
                <h2 style="font-size: 18px; margin: 0 0 8px;">Publish snippet</h2>
                <p class="help" style="margin-top: 0;">Install this snippet on the organization's website.</p>
                <textarea id="widget-snippet" readonly rows="5" style="width: 100%; font-family: monospace; font-size: 12px;">&lt;script
  src="https://cdn.apriloinfotech.com/chat-widget/v1/widget.js"
  data-widget-key="{{ $widgetKey }}"
  data-api-url="{{ $apiUrl }}"
  data-auto-open="false">
&lt;/script&gt;</textarea>
                <button class="button" type="button" onclick="navigator.clipboard.writeText(document.getElementById('widget-snippet').value)" style="width: auto; margin-top: 10px;">Copy embed snippet</button>
            </section>
        </div>

        <section class="card" style="padding: 0; overflow: hidden; min-height: 380px;">
            <div style="padding: 14px 16px; border-bottom: 1px solid var(--line);">
                <h2 style="font-size: 16px; margin: 0;">Live widget preview</h2>
                @if ($organization->hasActiveProductCategory('ai_support'))
                    <p class="help" style="margin: 5px 0 0;">Preview uses this organization's live widget settings and knowledge.</p>
                @else
                    <p class="help" style="margin: 5px 0 0;">Aprilo AI is not active for this organization, so live chat is unavailable. Settings and the embed key can still be prepared here.</p>
                @endif
            </div>
            @if ($organization->hasActiveProductCategory('ai_support'))
                <div style="padding: 16px; min-height: 320px; position: relative;">
                    <script src="https://cdn.apriloinfotech.com/chat-widget/v1/widget.js" data-widget-key="{{ $widgetKey }}" data-api-url="{{ $apiUrl }}" data-auto-open="false"></script>
                    <span style="color: var(--muted); font-size: 13px;">Use the chat launcher in the lower corner to test this organization's widget.</span>
                </div>
            @endif
        </section>
    </div>
@endsection
