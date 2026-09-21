<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SprintFourConnectIntegrationsTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::create([
            'name' => 'Integration Business',
            'plan' => 'enterprise', // Whitelist all enterprise channels
            'status' => 'active',
        ]);

        $this->admin = User::create([
            'organization_id' => $this->organization->id,
            'name' => 'Channel Owner',
            'email' => 'owner-chan@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Owner->value,
            'status' => 'active',
        ]);

        OrganizationSetting::create([
            'organization_id' => $this->organization->id,
            'assistant_name' => 'Channel Bot',
        ]);
    }

    public function test_whatsapp_use_sandbox_vs_custom_credentials(): void
    {
        $this->actingAs($this->admin);

        // 1. Save with Sandbox turned on
        $this->post(route('admin.settings.connect.update', 'whatsapp'), [
            'whatsapp_use_sandbox' => '1',
        ])->assertRedirect(route('admin.settings.connect'));

        $settings = OrganizationSetting::where('organization_id', $this->organization->id)->firstOrFail();
        $this->assertTrue($settings->whatsapp_use_sandbox);
        $this->assertTrue($settings->whatsapp_connected);
        $this->assertEquals('approved', $settings->whatsapp_brand_approval_status);
        $this->assertNull($settings->whatsapp_phone_number_id);

        // 2. Save with Custom credentials and request brand approval
        $this->post(route('admin.settings.connect.update', 'whatsapp'), [
            'whatsapp_phone_number_id' => '109876543210',
            'whatsapp_business_account_id' => '1234567890123',
            'whatsapp_sender_phone' => '+14155552671',
            'whatsapp_message_template' => 'custom_escalation_alert',
            'whatsapp_access_token' => 'EAAGy123_token',
            'request_approval' => '1',
        ])->assertRedirect(route('admin.settings.connect'));

        $settings->refresh();
        $this->assertFalse($settings->whatsapp_use_sandbox);
        $this->assertTrue($settings->whatsapp_connected);
        $this->assertEquals('pending', $settings->whatsapp_brand_approval_status);
        $this->assertEquals('109876543210', $settings->whatsapp_phone_number_id);
        $this->assertEquals('+14155552671', $settings->whatsapp_sender_phone);
        $this->assertEquals('custom_escalation_alert', $settings->whatsapp_message_template);
        $this->assertEquals('EAAGy123_token', $settings->whatsapp_access_token);
    }

    public function test_teams_use_webhook_vs_bot_credentials(): void
    {
        $this->actingAs($this->admin);

        // 1. Save with Webhook turned on
        $this->post(route('admin.settings.connect.update', 'teams'), [
            'teams_use_webhook' => '1',
            'teams_webhook_url' => 'https://company.webhook.office.com/webhookb2/teams_test',
        ])->assertRedirect(route('admin.settings.connect'));

        $settings = OrganizationSetting::where('organization_id', $this->organization->id)->firstOrFail();
        $this->assertTrue($settings->teams_use_webhook);
        $this->assertTrue($settings->teams_connected);
        $this->assertEquals('https://company.webhook.office.com/webhookb2/teams_test', $settings->teams_webhook_url);
        $this->assertNull($settings->teams_tenant_id);

        // 2. Save with Azure AD bot application details
        $this->post(route('admin.settings.connect.update', 'teams'), [
            'teams_tenant_id' => '3a1f9a2b-tenant-uuid',
            'teams_app_id' => '5b2e8a1c-app-uuid',
            'teams_app_password' => 'super_secret_password',
        ])->assertRedirect(route('admin.settings.connect'));

        $settings->refresh();
        $this->assertFalse($settings->teams_use_webhook);
        $this->assertTrue($settings->teams_connected);
        $this->assertNull($settings->teams_webhook_url);
        $this->assertEquals('3a1f9a2b-tenant-uuid', $settings->teams_tenant_id);
        $this->assertEquals('5b2e8a1c-app-uuid', $settings->teams_app_id);
        $this->assertEquals('super_secret_password', $settings->teams_app_password);
    }

    public function test_mail_custom_smtp_and_encryption(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.settings.connect.update', 'mail'), [
            'mail_smtp_host' => 'smtp.custom.io',
            'mail_smtp_port' => '465',
            'mail_smtp_username' => 'hr-admin@custom.io',
            'mail_smtp_password' => 'secret_smtp_pass',
            'mail_smtp_encryption' => 'ssl',
            'hr_desk_email' => 'help@custom.io',
        ])->assertRedirect(route('admin.settings.connect'));

        $settings = OrganizationSetting::where('organization_id', $this->organization->id)->firstOrFail();
        $this->assertTrue($settings->mail_connected);
        $this->assertEquals('smtp.custom.io', $settings->mail_smtp_host);
        $this->assertEquals(465, $settings->mail_smtp_port);
        $this->assertEquals('hr-admin@custom.io', $settings->mail_smtp_username);
        $this->assertEquals('ssl', $settings->mail_smtp_encryption);
        $this->assertEquals('help@custom.io', $settings->hr_desk_email);
    }

    public function test_disconnect_channel_clears_attributes(): void
    {
        $this->actingAs($this->admin);

        // Pre-populate whatsapp settings
        $settings = OrganizationSetting::where('organization_id', $this->organization->id)->firstOrFail();
        $settings->update([
            'whatsapp_connected' => true,
            'connect_whatsapp' => true,
            'whatsapp_phone_number_id' => '109876543210',
            'whatsapp_use_sandbox' => false,
        ]);

        // Disconnect
        $this->post(route('admin.settings.connect.update', 'whatsapp'), [
            'disconnect' => '1',
        ])->assertRedirect(route('admin.settings.connect'));

        $settings->refresh();
        $this->assertFalse($settings->whatsapp_connected);
        $this->assertFalse($settings->connect_whatsapp);
        $this->assertNull($settings->whatsapp_phone_number_id);
        $this->assertTrue($settings->whatsapp_use_sandbox); // defaults back to true
    }
}
