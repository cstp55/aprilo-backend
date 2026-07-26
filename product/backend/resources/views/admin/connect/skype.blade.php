<div style="max-width: 680px; display: grid; gap: 20px;">
    @if ($settings->skype_connected)
        <!-- Connection Status Banner -->
        <div class="notice" style="display: flex; justify-content: space-between; align-items: center; background: #e6f7ed; color: #1f7a3f; border: 1px solid #ccefd8; padding: 16px; border-radius: 8px;">
            <div>
                <strong>Skype Connected 🟢</strong>
                <div style="font-size: 12px; opacity: 0.9; margin-top: 4px;">
                    Aprilo is routing tickets to your Skype bot with ID: <code>{{ $settings->skype_bot_id }}</code>.
                </div>
            </div>
            
            <form method="POST" action="{{ route('admin.settings.connect.update', 'skype') }}" style="margin: 0; padding: 0; border: 0; box-shadow: none; width: auto;">
                @csrf
                <input type="hidden" name="disconnect" value="1">
                <button type="submit" style="background: #b82c2c; font-size: 12px; padding: 8px 16px; margin: 0; width: auto;">Disconnect Channel</button>
            </form>
        </div>
    @endif

    <form class="card" method="POST" action="{{ route('admin.settings.connect.update', 'skype') }}">
        @csrf
        
        <div style="display: grid; gap: 16px; margin-bottom: 20px;">
            <label class="field">
                Skype Bot Microsoft App ID
                <input name="skype_bot_id" type="text" value="{{ $settings->skype_bot_id }}" required placeholder="e.g. 2e4b8a1c-...">
            </label>
            
            <label class="field">
                Skype Bot Password / Client Secret
                <input name="skype_client_secret" type="password" value="{{ $settings->skype_client_secret }}" required placeholder="••••••••">
            </label>

            <div style="background: rgba(0,0,0,0.02); border: 1px solid var(--line); padding: 12px; border-radius: 8px; font-size: 11.5px; line-height: 1.6; color: var(--muted);">
                <strong>Skype Connection Steps:</strong>
                <ol style="margin: 4px 0 0 16px; padding: 0;">
                    <li>Go to the <strong>Microsoft Bot Framework Portal</strong> or Azure Bot Service.</li>
                    <li>Create a bot channel registration.</li>
                    <li>Add the <strong>Skype</strong> channel under your channels configuration.</li>
                    <li>Retrieve the Microsoft App ID and password (Client Secret) and enter them above to complete whitelabel routing.</li>
                </ol>
            </div>
        </div>

        <div style="display: flex; gap: 10px; max-width: 320px;">
            <button type="submit" style="flex: 1;">Save Credentials</button>
            <a href="{{ route('admin.settings.connect') }}" style="flex: 1; border: 1px solid var(--line); border-radius: 6px; color: var(--ink); display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 14px; font-weight: 700;">Cancel</a>
        </div>
    </form>
</div>
