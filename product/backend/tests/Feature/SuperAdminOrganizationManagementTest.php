<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationEntitlement;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
        $this->assertTrue(Hash::check('SecurePass123!', $owner->password));
        $this->assertDatabaseHas('organization_settings', ['organization_id' => $organization->id]);
        $this->assertDatabaseHas('audit_events', [
            'organization_id' => $organization->id,
            'event_type' => 'ORGANIZATION_CREATED_MANUALLY',
        ]);
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
