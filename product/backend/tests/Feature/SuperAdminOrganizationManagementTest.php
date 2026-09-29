<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationEntitlement;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use App\Models\KnowledgeSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuperAdminOrganizationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_an_organization_and_owner_account(): void
    {
        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)
            ->post('/admin/super/organizations', [
                'name' => 'Created Organization',
                'email' => 'org@example.com',
                'website' => 'https://created.example.com',
                'country' => 'India',
                'industry' => 'Retail',
                'team_size' => '11-50',
                'timezone' => 'Asia/Kolkata',
                'owner_name' => 'Organization Owner',
                'owner_username' => 'created.owner',
                'owner_email' => 'owner-created@example.com',
                'owner_password' => 'SecurePass123!',
                'owner_password_confirmation' => 'SecurePass123!',
                'status' => 'active',
            ])
            ->assertRedirect();

        $organization = Organization::where('email', 'org@example.com')->firstOrFail();
        $owner = User::where('username', 'created.owner')->firstOrFail();

        $this->assertSame($organization->id, $owner->organization_id);
        $this->assertSame('https://created.example.com', $organization->website);
        $this->assertSame('India', $organization->country);
        $this->assertSame('Retail', $organization->industry);
        $this->assertSame('11-50', $organization->team_size);
        $this->assertSame('Asia/Kolkata', $organization->timezone);
        $this->assertTrue(Hash::check('SecurePass123!', $owner->password));
        $this->assertDatabaseHas('organization_settings', ['organization_id' => $organization->id]);
        $this->assertDatabaseHas('audit_events', [
            'organization_id' => $organization->id,
            'event_type' => 'ORGANIZATION_CREATED_MANUALLY',
        ]);
    }

    public function test_super_admin_can_view_company_details_and_reset_owner_credentials(): void
    {
        $superAdmin = $this->superAdmin();
        $organization = Organization::create([
            'name' => 'Detailed Organization',
            'email' => 'company@example.com',
            'website' => 'https://company.example.com',
            'country' => 'India',
            'industry' => 'Technology',
            'team_size' => '51-200',
            'timezone' => 'Asia/Kolkata',
            'status' => 'active',
        ]);
        $owner = User::create([
            'organization_id' => $organization->id,
            'name' => 'Company Owner',
            'username' => 'company.owner',
            'email' => 'company-owner@example.com',
            'password' => Hash::make('OldPassword123!'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin)
            ->get('/admin/super/organizations/' . $organization->id)
            ->assertOk()
            ->assertViewIs('admin.super.organization-details')
            ->assertSee('https://company.example.com')
            ->assertSee('Asia/Kolkata')
            ->assertSee('company.owner');

        $this->patch('/admin/super/organizations/' . $organization->id . '/owners/' . $owner->id . '/credentials', [
            'username' => 'new.company.owner',
            'email' => 'new-company-owner@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertRedirect(route('admin.super.organizations.show', $organization));

        $owner->refresh();
        $this->assertSame('new.company.owner', $owner->username);
        $this->assertSame('new-company-owner@example.com', $owner->email);
        $this->assertTrue(Hash::check('NewPassword123!', $owner->password));
        $this->assertDatabaseHas('audit_events', [
            'organization_id' => $organization->id,
            'actor_user_id' => $superAdmin->id,
            'event_type' => 'ORGANIZATION_OWNER_CREDENTIALS_UPDATED',
            'entity_id' => $owner->id,
        ]);

        $otherOrganization = Organization::create(['name' => 'Other Organization']);
        $otherOwner = User::create([
            'organization_id' => $otherOrganization->id,
            'name' => 'Other Owner',
            'username' => 'other.owner',
            'email' => 'other-owner@example.com',
            'password' => Hash::make('OldPassword123!'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->patch('/admin/super/organizations/' . $organization->id . '/owners/' . $otherOwner->id . '/credentials', [
            'username' => 'hijacked.owner',
        ])->assertNotFound();
        $this->assertSame('other.owner', $otherOwner->fresh()->username);
    }

    public function test_super_admin_can_change_payment_status_and_entitlements_follow_it(): void
    {
        $superAdmin = $this->superAdmin();
        $organization = Organization::create(['name' => 'Payment Organization']);
        $product = Product::create([
            'slug' => 'payment-product-' . uniqid(),
            'name' => 'Support',
            'category' => 'ai_support',
            'product_type' => 'subscription',
            'status' => 'active',
            'is_active' => true,
        ]);
        $plan = Plan::create([
            'product_id' => $product->id,
            'slug' => 'manual-plan',
            'name' => 'Manual Plan',
            'is_active' => true,
        ]);
        $subscription = Subscription::create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'razorpay_subscription_id' => 'manual_' . uniqid(),
            'status' => 'active',
        ]);
        $entitlement = OrganizationEntitlement::create([
            'organization_id' => $organization->id,
            'subscription_id' => $subscription->id,
            'product_id' => $product->id,
            'feature_key' => 'support.agents',
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin)
            ->patch('/admin/super/organizations/' . $organization->id . '/subscriptions/' . $subscription->id . '/payment-status', [
                'status' => 'past_due',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'status' => 'past_due']);
        $this->assertDatabaseHas('organization_entitlements', ['id' => $entitlement->id, 'status' => 'suspended']);
        $this->assertDatabaseHas('audit_events', [
            'organization_id' => $organization->id,
            'event_type' => 'PAYMENT_STATUS_CHANGED_MANUALLY',
        ]);
    }

    public function test_super_admin_can_update_widget_design_rotate_key_and_index_tenant_knowledge(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'embedContent')) {
                return Http::response(['embedding' => ['values' => array_fill(0, 1536, 0.15)]], 200);
            }

            return Http::response(['mocked' => true], 200);
        });
        Storage::fake('local');

        $superAdmin = $this->superAdmin();
        $organization = Organization::create(['name' => 'Support Organization', 'status' => 'active']);
        $this->actingAs($superAdmin);

        $this->get(route('admin.super.organizations.widget', $organization))
            ->assertOk()
            ->assertViewIs('admin.super.organization-widget')
            ->assertSee('data-widget-key=');

        $settings = $organization->settings()->firstOrFail();
        $originalKey = $settings->public_widget_key;
        $this->patch(route('admin.super.organizations.widget.update', $organization), [
            'assistant_name' => 'Support Desk',
            'assistant_status' => 'active',
            'chatbot_color_palette' => '#16805d',
            'chatbot_icon' => 'support',
            'live_chat_enabled' => '1',
        ])->assertRedirect(route('admin.super.organizations.widget', $organization));

        $this->assertDatabaseHas('organization_settings', [
            'organization_id' => $organization->id,
            'assistant_name' => 'Support Desk',
            'chatbot_color_palette' => '#16805d',
            'chatbot_icon' => 'support',
            'live_chat_enabled' => true,
        ]);

        $this->post(route('admin.super.organizations.widget.rotate-key', $organization))
            ->assertRedirect(route('admin.super.organizations.widget', $organization));
        $this->assertNotSame($originalKey, $settings->fresh()->public_widget_key);

        $this->get(route('admin.super.organizations.knowledge', $organization))
            ->assertOk()
            ->assertViewIs('admin.super.organization-knowledge');

        $file = UploadedFile::fake()->createWithContent(
            'returns.txt',
            'Customers may return eligible items within thirty days after delivery.'
        );
        $this->post(route('admin.super.organizations.knowledge.store', $organization), [
            'title' => 'Returns policy',
            'access_scope' => 'all_employees',
            'file' => $file,
        ])->assertRedirect(route('admin.super.organizations.knowledge', $organization));

        $source = KnowledgeSource::where('organization_id', $organization->id)
            ->where('title', 'Returns policy')
            ->firstOrFail();
        $this->assertSame('indexed', $source->status);
        $this->assertGreaterThan(0, $source->chunks()->count());
        $this->assertDatabaseHas('audit_events', [
            'organization_id' => $organization->id,
            'actor_user_id' => $superAdmin->id,
            'event_type' => 'ORGANIZATION_KNOWLEDGE_SOURCE_UPLOADED',
            'entity_id' => $source->id,
        ]);
    }

    private function superAdmin(): User
    {
        return User::create([
            'organization_id' => null,
            'name' => 'Super Admin',
            'email' => 'super-' . uniqid() . '@example.com',
            'password' => Hash::make('SecurePass123!'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);
    }
}
