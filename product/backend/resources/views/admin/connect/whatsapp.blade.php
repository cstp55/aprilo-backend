@extends('admin.layout')

@section('title', 'Configure WhatsApp - Aprilo AI')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow">HR Operations</div>
            <h1>Configure WhatsApp Integration</h1>
            <p class="help">Route employee tickets and alerts directly to WhatsApp. You can choose to connect using Aprilo's default sandbox or register your custom WhatsApp Business credentials.</p>
        </div>
    </div>

    <div style="max-width: 680px; display: grid; gap: 20px;">
        @if ($settings->whatsapp_connected)
            <!-- Connection Status Banner -->
            <div class="notice" style="display: flex; justify-content: space-between; align-items: center; background: #e6f7ed; color: #1f7a3f; border: 1px solid #ccefd8; padding: 16px; border-radius: 8px;">
                <div>
                    <strong>WhatsApp Connected 🟢</strong>
                    <div style="font-size: 12px; opacity: 0.9; margin-top: 4px;">
                        @if ($settings->whatsapp_use_sandbox)
                            Using shared Aprilo WhatsApp Sandbox.
                        @else
                            Using custom WhatsApp Business Account. Brand Approval Status: <strong>{{ ucfirst($settings->whatsapp_brand_approval_status) }}</strong>
                        @endif
                    </div>
                </div>
                
                <form method="POST" action="{{ route('admin.settings.connect.update', 'whatsapp') }}" style="margin: 0; padding: 0; border: 0; box-shadow: none; width: auto;">
                    @csrf
                    <input type="hidden" name="disconnect" value="1">
                    <button type="submit" style="background: #b82c2c; font-size: 12px; padding: 8px 16px; margin: 0; width: auto;">Disconnect Channel</button>
                </form>
            </div>
            
            @if (!$settings->whatsapp_use_sandbox && $settings->whatsapp_brand_approval_status === 'pending')
                <div class="notice" style="background: #fff7e6; color: #815600; border: 1px solid #ffe9cc; padding: 14px; border-radius: 8px; font-size: 13px;">
                    ⏳ <strong>WhatsApp Brand Approval Pending</strong>: Your request has been forwarded to Meta Business Support. Approval typically takes 24-48 hours. In the meantime, you can toggle Sandbox mode on to continue testing.
                </div>
            @endif
        @endif

        <form class="card" method="POST" action="{{ route('admin.settings.connect.update', 'whatsapp') }}">
            @csrf
            
            <div style="display: grid; gap: 16px; margin-bottom: 20px;">
                <!-- SANDBOX OPTION -->
                <div style="background: #faf8f5; border: 1px solid var(--line); padding: 16px; border-radius: 8px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; font-weight: 700; font-size: 14px; color: var(--ink); cursor: pointer;">
                        <input type="checkbox" name="whatsapp_use_sandbox" id="whatsapp_use_sandbox" value="1" @checked($settings->whatsapp_use_sandbox) style="margin-top: 4px;">
                        <div>
                            Use Aprilo Shared Sandbox
                            <div style="font-weight: 400; font-size: 12px; color: var(--muted); margin-top: 2px;">
                                Recommended for instant setup. Aprilo hosts a shared WhatsApp Business phone number so you don't need customized Facebook/Meta API registrations to start testing.
                            </div>
                        </div>
                    </label>
                </div>

                <!-- CUSTOM CREDENTIALS -->
                <div id="custom-whatsapp-fields" style="display: {{ $settings->whatsapp_use_sandbox ? 'none' : 'grid' }}; gap: 16px; border-top: 1px dashed var(--line); padding-top: 16px;">
                    <h3 style="font-size: 14px; color: var(--ink); margin-bottom: 8px;">Custom WhatsApp Business Credentials</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <label class="field">
                            WhatsApp Phone Number ID
                            <input name="whatsapp_phone_number_id" id="whatsapp_phone_number_id" type="text" value="{{ $settings->whatsapp_phone_number_id }}" placeholder="e.g. 109876543210" @required(!$settings->whatsapp_use_sandbox)>
                        </label>
                        <label class="field">
                            WhatsApp Business Account ID
                            <input name="whatsapp_business_account_id" id="whatsapp_business_account_id" type="text" value="{{ $settings->whatsapp_business_account_id }}" placeholder="e.g. 1234567890123" @required(!$settings->whatsapp_use_sandbox)>
                        </label>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <label class="field">
                            Sender Phone Number
                            <input name="whatsapp_sender_phone" id="whatsapp_sender_phone" type="text" value="{{ $settings->whatsapp_sender_phone }}" placeholder="e.g. +14155552671" @required(!$settings->whatsapp_use_sandbox)>
                        </label>
                        <label class="field">
                            Approved Message Template
                            <input name="whatsapp_message_template" id="whatsapp_message_template" type="text" value="{{ $settings->whatsapp_message_template }}" placeholder="e.g. escalation_alert_template" @required(!$settings->whatsapp_use_sandbox)>
                        </label>
                    </div>

                    <label class="field">
                        System User Access Token
                        <input name="whatsapp_access_token" id="whatsapp_access_token" type="password" value="{{ $settings->whatsapp_access_token }}" placeholder="e.g. EAAGy123..." @required(!$settings->whatsapp_use_sandbox)>
                    </label>

                    <div style="background: rgba(210,38,48,0.04); border: 1px solid rgba(210,38,48,0.1); padding: 12px; border-radius: 8px; margin-top: 8px;">
                        <label style="display: flex; align-items: flex-start; gap: 8px; font-weight: 700; font-size: 13px; color: var(--ink); cursor: pointer;">
                            <input type="checkbox" name="request_approval" value="1" @checked($settings->whatsapp_brand_approval_status === 'pending') style="margin-top: 3px;">
                            <div>
                                Request WhatsApp Brand Approval
                                <div style="font-weight: 400; font-size: 11px; color: var(--muted); margin-top: 2px;">
                                    Submit configured business details to Facebook Developer Support for official whitelist whitelabel whitelisting. Required before taking the channel to live production.
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; max-width: 320px;">
                <button type="submit" style="flex: 1;">Save Credentials</button>
                <a href="{{ route('admin.settings', ['tab' => 'connect']) }}" style="flex: 1; border: 1px solid var(--line); border-radius: 6px; color: var(--ink); display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 14px; font-weight: 700;">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sandboxCheckbox = document.getElementById('whatsapp_use_sandbox');
            const customFieldsContainer = document.getElementById('custom-whatsapp-fields');
            const requiredFields = [
                'whatsapp_phone_number_id',
                'whatsapp_business_account_id',
                'whatsapp_sender_phone',
                'whatsapp_message_template',
                'whatsapp_access_token'
            ];

            sandboxCheckbox.addEventListener('change', function() {
                if (sandboxCheckbox.checked) {
                    customFieldsContainer.style.display = 'none';
                    requiredFields.forEach(id => {
                        document.getElementById(id).removeAttribute('required');
                    });
                } else {
                    customFieldsContainer.style.display = 'grid';
                    requiredFields.forEach(id => {
                        document.getElementById(id).setAttribute('required', 'required');
                    });
                }
            });
        });
    </script>
@endsection
