@extends('admin.layout')

@section('title', 'Configure Email - Aprilo AI')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow">HR Operations</div>
            <h1>Configure SMTP Email Integration</h1>
            <p class="help">Configure SMTP email server credentials to route support escalations and automated ticket details directly via email.</p>
        </div>
    </div>

    <div style="max-width: 680px; display: grid; gap: 20px;">
        @if ($settings->mail_connected)
            <!-- Connection Status Banner -->
            <div class="notice" style="display: flex; justify-content: space-between; align-items: center; background: #e6f7ed; color: #1f7a3f; border: 1px solid #ccefd8; padding: 16px; border-radius: 8px;">
                <div>
                    <strong>SMTP Server Connected 🟢</strong>
                    <div style="font-size: 12px; opacity: 0.9; margin-top: 4px;">
                        Aprilo is routing escalations from <code>{{ $settings->mail_smtp_username }}</code> to help desk <code>{{ $settings->hr_desk_email }}</code>.
                    </div>
                </div>
                
                <form method="POST" action="{{ route('admin.settings.connect.update', 'mail') }}" style="margin: 0; padding: 0; border: 0; box-shadow: none; width: auto;">
                    @csrf
                    <input type="hidden" name="disconnect" value="1">
                    <button type="submit" style="background: #b82c2c; font-size: 12px; padding: 8px 16px; margin: 0; width: auto;">Disconnect Channel</button>
                </form>
            </div>
        @endif

        <form class="card" method="POST" action="{{ route('admin.settings.connect.update', 'mail') }}">
            @csrf
            
            <div style="display: grid; gap: 16px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 16px;">
                    <label class="field">
                        SMTP Server Host
                        <input name="mail_smtp_host" type="text" value="{{ $settings->mail_smtp_host ?: 'smtp.mailtrap.io' }}" required placeholder="smtp.company.com">
                    </label>
                    <label class="field">
                        SMTP Server Port
                        <input name="mail_smtp_port" type="number" min="1" max="65535" value="{{ $settings->mail_smtp_port ?: 587 }}" required placeholder="587">
                    </label>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <label class="field">
                        SMTP Username
                        <input name="mail_smtp_username" type="text" value="{{ $settings->mail_smtp_username }}" required placeholder="hr-bot@company.com">
                    </label>
                    <label class="field">
                        SMTP Password
                        <input name="mail_smtp_password" type="password" value="{{ $settings->mail_smtp_password }}" required placeholder="••••••••">
                    </label>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <label class="field">
                        SMTP Encryption Type
                        <select name="mail_smtp_encryption" required>
                            <option value="tls" @selected($settings->mail_smtp_encryption === 'tls')>TLS (Port 587 / STARTTLS)</option>
                            <option value="ssl" @selected($settings->mail_smtp_encryption === 'ssl')>SSL (Port 465)</option>
                            <option value="none" @selected($settings->mail_smtp_encryption === 'none')>None (Port 25 / Unencrypted)</option>
                        </select>
                    </label>
                    <label class="field">
                        HR Help Desk Dispatch Email
                        <input name="hr_desk_email" type="email" value="{{ $settings->hr_desk_email ?: 'hr-helpdesk@example.com' }}" required placeholder="hr-support@company.com">
                    </label>
                </div>
            </div>

            <div style="display: flex; gap: 10px; max-width: 320px;">
                <button type="submit" style="flex: 1;">Save Credentials</button>
                <a href="{{ route('admin.settings', ['tab' => 'connect']) }}" style="flex: 1; border: 1px solid var(--line); border-radius: 6px; color: var(--ink); display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 14px; font-weight: 700;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
