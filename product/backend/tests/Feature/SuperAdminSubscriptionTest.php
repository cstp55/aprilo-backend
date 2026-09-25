<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_grant_subscription_and_features_to_any_organization(): void
    {
        $platform = Organization::create(['name' => 'Platform']);
        $customer = Organization::create(['name' => 'Customer']);
        $superAdmin = User::create([
            'organization_id' => $platform->id,
            'name' => 'Platform Admin',
            'email' => 'platform@example.com',
            'password' => Hash::make('SecurePass123!'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);
        $product = Product::create([
            'slug' => 'manual-support-' . uniqid(),
            'name' => 'Aprilo Support',
            'category' => 'ai_support',
            'product_type' => 'subscription',
            'status' => 'active',
            'is_active' => true,
        ]);
        $plan = Plan::create([
            'product_id' => $product->id,
            'slug' => 'professional',
            'name' => 'Professional',
            'billing_cycle' => 'monthly',
            'price' => 2999,
            'currency' => 'INR',
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get('/admin/super/organizations/' . $customer->id . '/subscriptions')
            ->assertOk()
            ->assertViewIs('admin.super.subscription-access');

        $this->actingAs($superAdmin)
            ->post('/admin/super/organizations/' . $customer->id . '/subscriptions', [
                'product_id' => $product->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'features' => ['support', 'support.agents', 'support.website_chat'],
                'max_agents' => 10,
            ])
            ->assertRedirect('/admin/super/organizations/' . $customer->id . '/subscriptions');

        $subscription = Subscription::where('organization_id', $customer->id)->firstOrFail();
        $this->assertSame('manual_', substr($subscription->razorpay_subscription_id, 0, 7));
        $this->assertTrue($customer->fresh()->supportsLiveChatAgents());
        $this->assertDatabaseHas('organization_entitlements', [
            'organization_id' => $customer->id,
            'feature_key' => 'support.agents',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('audit_events', [
            'organization_id' => $customer->id,
            'event_type' => 'MANUAL_SUBSCRIPTION_GRANTED',
        ]);
    }

    public function test_organization_owner_can_enter_admin_console_but_cannot_use_super_admin_routes(): void
    {
        $organization = Organization::create(['name' => 'Owner Organization']);
        $owner = User::create([
            'organization_id' => $organization->id,
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('SecurePass123!'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->actingAs($owner)->get('/admin')->assertOk();
        $this->actingAs($owner)->get('/admin/super/organizations')->assertForbidden();
        $this->assertTrue($owner->fresh()->hasPermission('platform.anything')); 
    }

    public function test_super_admin_can_open_agent_management_for_a_selected_organization(): void
    {
        $customer = Organization::create(['name' => 'Customer']);
        $superAdmin = User::create([
            'organization_id' => null,
            'name' => 'Platform Admin',
            'email' => 'super-agents@example.com',
            'password' => Hash::make('SecurePass123!'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin)
            ->get('/admin/agents?organization_id=' . $customer->id)
            ->assertOk()
            ->assertViewIs('admin.agents')
            ->assertSee('Support Agents')
            ->assertSee($customer->name);
    }
}
