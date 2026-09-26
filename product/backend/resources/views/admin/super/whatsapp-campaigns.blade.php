@extends('admin.layout')

@section('title', 'WhatsApp Campaigns - Super Admin')

@section('content')
    <style>
        .wa-form { display: grid; gap: 16px; }
        .wa-form label { display: grid; gap: 7px; color: var(--ink); font-size: 13px; font-weight: 700; }
        .wa-form input, .wa-form select, .wa-form textarea { width: 100%; min-width: 0; padding: 10px 12px; border: 1px solid var(--line); border-radius: 6px; background: #fff; color: var(--ink); }
        .wa-form textarea { min-height: 104px; resize: vertical; }
        .wa-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .wa-note { padding: 12px 14px; border-left: 3px solid #e59b16; background: #fff8e8; color: #614813; font-size: 13px; line-height: 1.5; }
        .wa-table-wrap { overflow-x: auto; }
        .wa-status { display: inline-block; padding: 4px 8px; border-radius: 4px; background: #eef2f5; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .wa-status.accepted, .wa-status.completed { background: #e5f6eb; color: #17643a; }
        .wa-status.failed { background: #fde9e7; color: #9c241b; }
        @media (max-width: 760px) { .wa-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="header">
        <div>
            <div class="eyebrow">Platform outreach</div>
            <h1>WhatsApp campaigns</h1>
            <p class="help">Send an approved WhatsApp template to opted-in users, or a text message during an active 24-hour support window.</p>
        </div>
        <span class="wa-status {{ $whatsappReady ? 'accepted' : 'failed' }}">
            {{ $whatsappReady ? 'Cloud API configured' : 'Cloud API not configured' }}
        </span>
    </div>

    @if (session('status'))
        <div class="card" style="margin-bottom: 18px; border-left: 4px solid var(--green);">{{ session('status') }}</div>
    @endif

    @if (! $whatsappReady)
        <div class="wa-note" style="margin-bottom: 18px;">Configure <code>WHATSAPP_PHONE_NUMBER_ID</code> and <code>WHATSAPP_ACCESS_TOKEN</code> in the server environment before queuing campaigns.</div>
    @endif

    <div class="card" style="margin-bottom: 24px;">
        <h2 style="font-size: 19px; margin-bottom: 16px;">Create campaign</h2>
        <form method="POST" action="{{ route('admin.super.whatsapp-campaigns.store') }}" class="wa-form">
            @csrf
            <div class="wa-grid">
                <label>Campaign name
                    <input name="name" maxlength="120" value="{{ old('name') }}" required>
                    @error('name') <span class="help">{{ $message }}</span> @enderror
                </label>
                <label>Message format
                    <select name="message_type" id="messageType" required>
                        <option value="template" @selected(old('message_type', 'template') === 'template')>Approved template</option>
                        <option value="text" @selected(old('message_type') === 'text')>Freeform text (24-hour window)</option>
                    </select>
                </label>
            </div>

            <div class="wa-grid">
                <label>Recipients
                    <select name="recipient_mode" id="recipientMode" required>
                        <option value="users" @selected(old('recipient_mode', 'users') === 'users')>Select active users</option>
                        <option value="manual" @selected(old('recipient_mode') === 'manual')>Enter one WhatsApp number</option>
                    </select>
                </label>
                <label id="userRecipients">Active users with a valid phone
                    <select name="user_ids[]" multiple size="6" {{ $eligibleUsers->isEmpty() ? 'disabled' : '' }}>
                        @foreach ($eligibleUsers as $user)
                            <option value="{{ $user->id }}" @selected(in_array($user->id, old('user_ids', []), true))>{{ $user->name }} · {{ $user->phone }}</option>
                        @endforeach
                    </select>
                    <span class="help" style="margin: 0;">{{ $eligibleUsers->count() }} eligible users. Use Ctrl/Command to select more than one.</span>
                    @error('user_ids') <span class="help">{{ $message }}</span> @enderror
                </label>
                <label id="manualRecipient" hidden>WhatsApp number with country code
                    <input name="recipient_phone" type="tel" inputmode="tel" placeholder="+14155552671" value="{{ old('recipient_phone') }}">
                    @error('recipient_phone') <span class="help">{{ $message }}</span> @enderror
                </label>
            </div>

            <div id="templateFields" class="wa-grid">
                <label>Approved template name
                    <input name="template_name" value="{{ old('template_name') }}" maxlength="255" placeholder="e.g. monthly_update">
                    @error('template_name') <span class="help">{{ $message }}</span> @enderror
                </label>
                <label>Template language code
                    <input name="template_language" value="{{ old('template_language', 'en_US') }}" maxlength="20" placeholder="en_US">
                    @error('template_language') <span class="help">{{ $message }}</span> @enderror
                </label>
            </div>
            <label id="templateParameters">Body parameters, one value per line
                <textarea name="template_parameters" maxlength="2000" placeholder="First variable&#10;Second variable">{{ old('template_parameters') }}</textarea>
            </label>
            <label id="textMessage" hidden>Message
                <textarea name="message_text" maxlength="4096" placeholder="Write the message to send">{{ old('message_text') }}</textarea>
                @error('message_text') <span class="help">{{ $message }}</span> @enderror
            </label>

            <div class="wa-note">WhatsApp requires prior opt-in from every recipient. Use only a template approved for the selected language. Freeform text is accepted only while the recipient's 24-hour customer-service window is open.</div>
            <label style="display: flex; grid-template-columns: 18px 1fr; align-items: start; gap: 10px;">
                <input type="checkbox" name="consent_confirmed" value="1" required @checked(old('consent_confirmed')) style="width: 18px; margin: 2px 0 0;">
                <span>I confirm every selected recipient opted in to receive WhatsApp messages from Aprilo.</span>
            </label>
            @error('consent_confirmed') <span class="help">{{ $message }}</span> @enderror

            <button class="button" type="submit" style="width: auto; justify-self: start;" {{ $whatsappReady ? '' : 'disabled' }}>Queue campaign</button>
        </form>
    </div>

    <div class="card" style="margin-bottom: 24px;">
        <h2 style="font-size: 19px; margin-bottom: 14px;">Campaign history</h2>
        <div class="wa-table-wrap">
            <table>
                <thead><tr><th>Campaign</th><th>Created</th><th>Format</th><th>Recipients</th><th>Accepted</th><th>Failed</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($campaigns as $campaign)
                        <tr>
                            <td><strong>{{ $campaign->name }}</strong><div style="font-size: 11px; color: var(--muted);">{{ $campaign->creator?->name ?? 'Deleted admin' }}</div></td>
                            <td>{{ $campaign->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $campaign->message_type === 'template' ? $campaign->template_name : 'Freeform text' }}</td>
                            <td>{{ $campaign->recipients_count }}</td>
                            <td>{{ $campaign->accepted_count }}</td>
                            <td>{{ $campaign->failed_count }}</td>
                            <td><span class="wa-status {{ $campaign->status }}">{{ $campaign->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No campaigns yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 14px;">{{ $campaigns->links() }}</div>
    </div>

    <div class="card">
        <h2 style="font-size: 19px; margin-bottom: 14px;">Recipient send history</h2>
        <div class="wa-table-wrap">
            <table>
                <thead><tr><th>Time</th><th>Campaign</th><th>Content sent</th><th>Recipient</th><th>Phone</th><th>Message ID</th><th>Status / error</th></tr></thead>
                <tbody>
                    @forelse ($deliveries as $delivery)
                        <tr>
                            <td>{{ $delivery->processed_at?->format('Y-m-d H:i') ?? $delivery->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $delivery->campaign?->name ?? 'Deleted campaign' }}</td>
                            <td style="max-width: 280px; white-space: normal;">{{ $delivery->campaign?->message_text ?? $delivery->campaign?->template_name ?? 'Unavailable' }}@if ($delivery->campaign?->template_parameters)<div style="color: var(--muted); font-size: 11px; margin-top: 4px;">{{ implode(' · ', $delivery->campaign->template_parameters) }}</div>@endif</td>
                            <td>{{ $delivery->recipient_name ?? 'Manual recipient' }}</td>
                            <td>{{ $delivery->recipient_phone }}</td>
                            <td>{{ $delivery->whatsapp_message_id ?? '—' }}</td>
                            <td><span class="wa-status {{ $delivery->status }}">{{ $delivery->status }}</span>@if ($delivery->error_message)<div style="max-width: 280px; color: var(--danger); font-size: 11px; margin-top: 4px;">{{ $delivery->error_message }}</div>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No recipient sends yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 14px;">{{ $deliveries->links() }}</div>
    </div>

    <script>
        const recipientMode = document.getElementById('recipientMode');
        const messageType = document.getElementById('messageType');
        const setVisible = (element, visible) => {
            element.hidden = !visible;
            element.querySelectorAll('input, select, textarea').forEach((input) => {
                input.disabled = !visible || input.options?.length === 0;
                input.required = visible && input.name !== 'template_parameters';
            });
        };
        const updateForm = () => {
            const manual = recipientMode.value === 'manual';
            const isTemplate = messageType.value === 'template';
            setVisible(document.getElementById('userRecipients'), !manual);
            setVisible(document.getElementById('manualRecipient'), manual);
            setVisible(document.getElementById('templateFields'), isTemplate);
            setVisible(document.getElementById('templateParameters'), isTemplate);
            setVisible(document.getElementById('textMessage'), !isTemplate);
        };
        recipientMode.addEventListener('change', updateForm);
        messageType.addEventListener('change', updateForm);
        updateForm();
    </script>
@endsection