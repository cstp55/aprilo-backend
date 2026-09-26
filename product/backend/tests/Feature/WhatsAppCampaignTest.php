<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppCampaignRecipient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WhatsAppCampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_queue_a_template_campaign_for_selected_users(): void
    {
        Queue::fake();
        $superAdmin = $this->user('super_admin');
        $customer = $this->user('owner', ['name' => 'Customer One', 'phone' => '+14155552671']);

        $this->actingAs($superAdmin)
            ->post(route('admin.super.whatsapp-campaigns.store'), [
                'name' => 'April update',
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

    public function test_manual_campaign_requires_recipient_opt_in_confirmation(): void
    {
        $this->actingAs($this->user('super_admin'))
            ->from(route('admin.super.whatsapp-campaigns'))
            ->post(route('admin.super.whatsapp-campaigns.store'), [
                'name' => 'Manual send',
                'recipient_mode' => 'manual',
                'recipient_phone' => '+14155552671',
                'message_type' => 'text',
                'message_text' => 'Hello there',
            ])
            ->assertSessionHasErrors('consent_confirmed');

        $this->assertDatabaseCount('whatsapp_campaigns', 0);
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
}