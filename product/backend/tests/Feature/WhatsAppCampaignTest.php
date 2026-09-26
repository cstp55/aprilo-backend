<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppCampaignRecipient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WhatsAppCampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_queue_a_template_campaign_for_selected_users(): void
    {
        Queue::fake();
        $this->fakeApprovedTemplate();
        $superAdmin = $this->user('super_admin');
        $customer = $this->user('owner', ['name' => 'Customer One', 'phone' => '+14155552671']);

        $this->actingAs($superAdmin)
            ->post(route('admin.super.whatsapp-campaigns.store'), [
                'name' => 'April update',
                'delivery_mode' => 'queued',
                'recipient_mode' => 'users',
                'user_ids' => [$customer->id],
                'message_type' => 'template',
                'template_name' => 'monthly_update',
                'template_language' => 'en_US',
                'template_parameters' => "April\nCustomer One",
                'consent_confirmed' => '1',
            ])
            ->assertRedirect(route('admin.super.whatsapp-campaigns'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('whatsapp_campaigns', [
            'name' => 'April update',
            'message_type' => 'template',
            'template_name' => 'monthly_update',
            'status' => 'queued',
        ]);
        $this->assertDatabaseHas('whatsapp_campaign_recipients', [
            'user_id' => $customer->id,
            'recipient_phone' => '14155552671',
            'status' => 'pending',
        ]);
        Queue::assertPushed(SendWhatsAppCampaignRecipient::class, 1);
    }

    public function test_campaign_page_is_restricted_to_super_admins(): void
    {
        $this->actingAs($this->user('owner'))
            ->get(route('admin.super.whatsapp-campaigns'))
            ->assertForbidden();
    }

    public function test_super_admin_can_load_only_approved_meta_templates(): void
    {
        $this->fakeApprovedTemplate(true);

        $this->actingAs($this->user('super_admin'))
            ->getJson(route('admin.super.whatsapp-campaigns.templates'))
            ->assertOk()
            ->assertJsonCount(1, 'templates')
            ->assertJsonPath('templates.0.name', 'welcome_customer')
            ->assertJsonPath('templates.0.language', 'en_US')
            ->assertJsonPath('templates.0.parameter_count', 1)
            ->assertJsonMissing(['name' => 'pending_offer']);

        Http::assertSent(fn ($request) =>
            str_starts_with($request->url(), 'https://graph.facebook.com/v25.0/business-123/message_templates?') &&
            $request->hasHeader('Authorization', 'Bearer test-access-token')
        );
    }

    public function test_template_lookup_is_restricted_to_super_admins(): void
    {
        $this->actingAs($this->user('owner'))
            ->getJson(route('admin.super.whatsapp-campaigns.templates'))
            ->assertForbidden();
    }

    public function test_manual_campaign_requires_recipient_opt_in_confirmation(): void
    {
        $this->actingAs($this->user('super_admin'))
            ->from(route('admin.super.whatsapp-campaigns'))
            ->post(route('admin.super.whatsapp-campaigns.store'), [
                'name' => 'Manual send',
                'delivery_mode' => 'queued',
                'recipient_mode' => 'manual',
                'recipient_phone' => '+14155552671',
                'message_type' => 'text',
                'message_text' => 'Hello there',
            ])
            ->assertSessionHasErrors('consent_confirmed');

        $this->assertDatabaseCount('whatsapp_campaigns', 0);
    }

    public function test_instant_send_posts_to_meta_and_records_result_without_queueing(): void
    {
        Queue::fake();
        config([
            'services.whatsapp.phone_number_id' => 'phone-123',
            'services.whatsapp.access_token' => 'test-access-token',
        ]);
        Http::fake([
            'https://graph.facebook.com/v25.0/phone-123/messages' => Http::response([
                'messages' => [['id' => 'wamid.instant-123']],
            ]),
        ]);

        $this->actingAs($this->user('super_admin'))
            ->post(route('admin.super.whatsapp-campaigns.store'), [
                'name' => 'Instant support reply',
                'delivery_mode' => 'instant',
                'recipient_mode' => 'manual',
                'recipient_phone' => '+14155552671',
                'message_type' => 'text',
                'message_text' => 'Your support request is being handled.',
                'consent_confirmed' => '1',
            ])
            ->assertRedirect(route('admin.super.whatsapp-campaigns'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('whatsapp_campaigns', [
            'name' => 'Instant support reply',
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('whatsapp_campaign_recipients', [
            'recipient_phone' => '14155552671',
            'status' => 'accepted',
            'whatsapp_message_id' => 'wamid.instant-123',
        ]);
        Queue::assertNothingPushed();
        Http::assertSent(fn ($request) =>
            $request->url() === 'https://graph.facebook.com/v25.0/phone-123/messages' &&
            $request['text']['body'] === 'Your support request is being handled.'
        );
    }

    private function user(string $role, array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => ucfirst($role),
            'email' => $role . '-' . uniqid() . '@example.com',
            'password' => Hash::make('SecurePass123!'),
            'role' => $role,
            'status' => 'active',
        ], $attributes));
    }

    private function fakeApprovedTemplate(bool $includePending = false): void
    {
        config([
            'services.whatsapp.business_account_id' => 'business-123',
            'services.whatsapp.access_token' => 'test-access-token',
            'services.whatsapp.api_version' => 'v25.0',
        ]);
        Cache::forget('whatsapp.approved_templates.' . hash('sha256', 'business-123'));

        $templates = [[
            'name' => 'monthly_update',
            'status' => 'APPROVED',
            'language' => 'en_US',
            'category' => 'UTILITY',
            'components' => [['type' => 'BODY', 'text' => 'Hello {{1}}, your update is ready.']],
        ]];
        if ($includePending) {
            $templates[] = [
                'name' => 'pending_offer',
                'status' => 'PENDING',
                'language' => 'en_US',
                'category' => 'MARKETING',
                'components' => [['type' => 'BODY', 'text' => 'A pending offer']],
            ];
        }

        Http::fake([
            'https://graph.facebook.com/v25.0/business-123/message_templates*' => Http::response([
                'data' => $templates,
            ]),
        ]);
    }
}