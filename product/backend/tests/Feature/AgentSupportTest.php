<?php

namespace Tests\Feature;

use App\Models\AgentSupport;
use App\Models\Organization;
use App\Models\OrganizationEntitlement;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AgentSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_user_can_manage_agents_when_live_chat_is_subscribed(): void
    {
        [$organization, $manager] = $this->organizationWithLiveChat();

        $create = $this->actingAs($manager, 'sanctum')->postJson('/api/agents', [
            'full_name' => 'Support Agent',
            'username' => 'support.agent',
            'email' => 'agent@example.com',
            'password' => 'SecurePass123!',
            'nickname' => 'Alex',
            'availability_status' => 'away',
            'availability_slots' => ['09:00-13:00', '14:00-18:00'],
        ]);

        $create->assertCreated()->assertJsonPath('agent.nickname', 'Alex');
        $agentId = $create->json('agent.id');

        $this->assertDatabaseHas('agent_supports', [
            'id' => $agentId,
            'organization_id' => $organization->id,
            'is_active' => true,
        ]);

        $this->actingAs($manager, 'sanctum')->patchJson('/api/agents/' . $agentId, [
            'nickname' => 'Alex Support',
            'password' => 'NewSecurePass123!',
            'is_active' => false,
        ])->assertOk()->assertJsonPath('agent.is_active', false);

        $this->postJson('/api/auth/agent-login', [
            'username' => 'support.agent',
            'password' => 'SecurePass123!',
        ])->assertStatus(422);

        $this->actingAs($manager, 'sanctum')->patchJson('/api/agents/' . $agentId, [
            'is_active' => true,
        ])->assertOk();

        $this->postJson('/api/auth/agent-login', [
            'username' => 'support.agent',
            'password' => 'NewSecurePass123!',
        ])->assertOk()->assertJsonPath('agent.nickname', 'Alex Support');

        $this->actingAs($manager, 'sanctum')->deleteJson('/api/agents/' . $agentId)
            ->assertOk();

        $this->assertDatabaseMissing('agent_supports', ['id' => $agentId]);
    }

    public function test_agent_management_is_blocked_without_live_chat_subscription(): void
    {
        [$organization, $manager] = $this->organizationWithLiveChat(false);

        $this->actingAs($manager, 'sanctum')->postJson('/api/agents', [
            'full_name' => 'Blocked Agent',
            'username' => 'blocked.agent',
            'password' => 'SecurePass123!',
            'nickname' => 'Blocked',
        ])->assertForbidden();

        $this->assertDatabaseMissing('agent_supports', [
            'organization_id' => $organization->id,
        ]);
    }

    public function test_agent_username_is_unique_regardless_of_letter_case(): void
    {
        [$organization, $manager] = $this->organizationWithLiveChat();

        $this->actingAs($manager, 'sanctum')->postJson('/api/agents', [
            'full_name' => 'First Agent',
            'username' => 'Support.Agent',
            'password' => 'SecurePass123!',
            'nickname' => 'First',
        ])->assertCreated();

        $this->actingAs($manager, 'sanctum')->postJson('/api/agents', [
            'full_name' => 'Second Agent',
            'username' => ' support.agent ',
            'password' => 'SecurePass123!',
            'nickname' => 'Second',
        ])->assertStatus(422)->assertJsonValidationErrors(['username']);

        $this->assertDatabaseHas('users', [
            'organization_id' => $organization->id,
            'username' => 'support.agent',
        ]);
    }

    public function test_agent_cannot_manage_other_agents(): void
    {
        [$organization] = $this->organizationWithLiveChat();
        $agentUser = User::create([
            'organization_id' => $organization->id,
            'name' => 'Existing Agent',
            'username' => 'existing.agent',
            'email' => 'existing@example.com',
            'password' => Hash::make('SecurePass123!'),
            'role' => 'employee',
            'status' => 'active',
        ]);
        AgentSupport::create([
            'organization_id' => $organization->id,
            'user_id' => $agentUser->id,
            'nickname' => 'Existing',
        ]);

        $this->actingAs($agentUser, 'sanctum')->getJson('/api/agents')
            ->assertForbidden();
    }

    public function test_organization_user_can_manage_agents_from_the_laravel_blade_page(): void
    {
        [$organization, $manager] = $this->organizationWithLiveChat();

        $this->actingAs($manager)
            ->get('/admin/agents')
            ->assertOk()
            ->assertViewIs('admin.agents')
            ->assertSee('Create support agent');

        $this->actingAs($manager)
            ->post('/admin/agents', [
                'full_name' => 'Blade Agent',
                'username' => 'blade.agent',
                'email' => 'blade@example.com',
                'password' => 'SecurePass123!',
                'nickname' => 'Blade',
                'availability_status' => 'offline',
                'availability_slots' => '09:00-17:00',
            ])
            ->assertRedirect('/admin/agents');

        $this->assertDatabaseHas('agent_supports', [
            'organization_id' => $organization->id,
            'nickname' => 'Blade',
        ]);
    }

    private function organizationWithLiveChat(bool $withSubscription = true): array
    {
        $organization = Organization::create(['name' => 'Support Organization']);
        $manager = User::create([
            'organization_id' => $organization->id,
            'name' => 'Organization User',
            'username' => 'organization.manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('SecurePass123!'),
            'role' => 'employee',
            'status' => 'active',
        ]);

        if ($withSubscription) {
            $product = Product::create([
                'slug' => 'support-product-' . uniqid(),
                'name' => 'Support Product',
                'category' => 'ai_support',
                'product_type' => 'subscription',
                'status' => 'active',
                'is_active' => true,
            ]);
            $plan = Plan::create([
                'product_id' => $product->id,
                'slug' => 'support-plan',
                'name' => 'Support Plan',
                'is_active' => true,
            ]);
            $subscription = Subscription::create([
                'organization_id' => $organization->id,
                'product_id' => $product->id,
                'plan_id' => $plan->id,
                'razorpay_subscription_id' => 'sub_' . uniqid(),
                'status' => 'active',
            ]);
            OrganizationEntitlement::create([
                'organization_id' => $organization->id,
                'subscription_id' => $subscription->id,
                'product_id' => $product->id,
                'feature_key' => 'support.agents',
                'status' => 'active',
                'limits' => ['max_agents' => 10],
            ]);
        }

        return [$organization, $manager];
    }
}
