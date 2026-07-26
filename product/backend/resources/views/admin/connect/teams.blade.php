<div style="max-width: 680px; display: grid; gap: 20px;">
    @if ($settings->teams_connected)
        <!-- Connection Status Banner -->
        <div class="notice" style="display: flex; justify-content: space-between; align-items: center; background: #e6f7ed; color: #1f7a3f; border: 1px solid #ccefd8; padding: 16px; border-radius: 8px;">
            <div>
                <strong>Microsoft Teams Connected 🟢</strong>
                <div style="font-size: 12px; opacity: 0.9; margin-top: 4px;">
                    Using @if($settings->teams_use_webhook) Incoming Webhook @else Interactive Microsoft Bot App @endif routing.
                </div>
            </div>
            
            <form method="POST" action="{{ route('admin.settings.connect.update', 'teams') }}" style="margin: 0; padding: 0; border: 0; box-shadow: none; width: auto;">
                @csrf
                <input type="hidden" name="disconnect" value="1">
                <button type="submit" style="background: #b82c2c; font-size: 12px; padding: 8px 16px; margin: 0; width: auto;">Disconnect Channel</button>
            </form>
        </div>
    @endif

    <form class="card" method="POST" action="{{ route('admin.settings.connect.update', 'teams') }}">
        @csrf
        
        <div style="display: grid; gap: 16px; margin-bottom: 20px;">
            <!-- TEAMS MODE TOGGLE -->
            <div style="background: #faf8f5; border: 1px solid var(--line); padding: 16px; border-radius: 8px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; font-weight: 700; font-size: 14px; color: var(--ink); cursor: pointer;">
                    <input type="checkbox" name="teams_use_webhook" id="teams_use_webhook" value="1" @checked($settings->teams_use_webhook) style="margin-top: 4px;">
                    <div>
                        Use Simple Incoming Webhook
                        <div style="font-weight: 400; font-size: 12px; color: var(--muted); margin-top: 2px;">
                            Recommended for one-way alerts. Simply create an incoming webhook inside your MS Teams Channel settings and copy the webhook URL here.
                        </div>
                    </div>
                </label>
            </div>

            <!-- WEBHOOK FIELD -->
            <div id="webhook-fields" style="display: {{ $settings->teams_use_webhook ? 'block' : 'none' }};">
                <label class="field">
                    Teams Incoming Webhook URL
                    <input name="teams_webhook_url" id="teams_webhook_url" type="url" value="{{ $settings->teams_webhook_url }}" placeholder="https://company.webhook.office.com/webhookb2/..." @required($settings->teams_use_webhook)>
                </label>
            </div>

            <!-- DIRECT BOT APP FIELDS -->
            <div id="bot-app-fields" style="display: {{ $settings->teams_use_webhook ? 'none' : 'grid' }}; gap: 16px;">
                <h3 style="font-size: 14px; color: var(--ink); margin-bottom: 8px; border-top: 1px dashed var(--line); padding-top: 16px;">Microsoft Bot App Configuration</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <label class="field">
                        Microsoft Tenant ID
                        <input name="teams_tenant_id" id="teams_tenant_id" type="text" value="{{ $settings->teams_tenant_id }}" placeholder="e.g. 3a1f9a2b-..." @required(!$settings->teams_use_webhook)>
                    </label>
                    <label class="field">
                        Teams App ID (Client ID)
                        <input name="teams_app_id" id="teams_app_id" type="text" value="{{ $settings->teams_app_id }}" placeholder="e.g. 5b2e8a1c-..." @required(!$settings->teams_use_webhook)>
                    </label>
                </div>

                <label class="field">
                    Teams App Client Secret (Password)
                    <input name="teams_app_password" id="teams_app_password" type="password" value="{{ $settings->teams_app_password }}" placeholder="••••••••" @required(!$settings->teams_use_webhook)>
                </label>

                <div style="background: rgba(0,0,0,0.02); border: 1px solid var(--line); padding: 12px; border-radius: 8px; font-size: 11.5px; line-height: 1.6; color: var(--muted);">
                    <strong>How to register a custom Teams Bot:</strong>
                    <ol style="margin: 4px 0 0 16px; padding: 0;">
                        <li>Log into the Azure Portal with your Microsoft 365 Tenant Admin account.</li>
                        <li>Go to <strong>App Registrations</strong> and register a new Web App registration.</li>
                        <li>Go to Certificates & Secrets, create a client secret key, and paste it here.</li>
                        <li>Configure the registered application inside your MS Teams developer portal to enable direct, two-way employee chats.</li>
                        <li style="margin-top: 4px;">To route escalations, retrieve the <strong>Object ID</strong> of each HR representative from the Azure Portal (under Microsoft Entra ID > Users), and assign it as their **Teams User ID** in the <em>Validate Employee</em> directory.</li>
                    </ol>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 10px; max-width: 320px;">
            <button type="submit" style="flex: 1;">Save Credentials</button>
            <a href="{{ route('admin.settings.connect') }}" style="flex: 1; border: 1px solid var(--line); border-radius: 6px; color: var(--ink); display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 14px; font-weight: 700;">Cancel</a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const webhookCheckbox = document.getElementById('teams_use_webhook');
        const webhookFields = document.getElementById('webhook-fields');
        const botFields = document.getElementById('bot-app-fields');
        
        const webhookUrl = document.getElementById('teams_webhook_url');
        const tenantId = document.getElementById('teams_tenant_id');
        const appId = document.getElementById('teams_app_id');
        const appPass = document.getElementById('teams_app_password');

        if (webhookCheckbox) {
            webhookCheckbox.addEventListener('change', function() {
                if (webhookCheckbox.checked) {
                    webhookFields.style.display = 'block';
                    botFields.style.display = 'none';
                    
                    webhookUrl.setAttribute('required', 'required');
                    tenantId.removeAttribute('required');
                    appId.removeAttribute('required');
                    appPass.removeAttribute('required');
                } else {
                    webhookFields.style.display = 'none';
                    botFields.style.display = 'grid';
                    
                    webhookUrl.removeAttribute('required');
                    tenantId.setAttribute('required', 'required');
                    appId.setAttribute('required', 'required');
                    appPass.setAttribute('required', 'required');
                }
            });
        }
    });
</script>
