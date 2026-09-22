<?php

namespace Tests\Feature;

use App\Models\AgentSupport;
use App\Models\Organization;
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
            Subscription::create([
                'organization_id' => $organization->id,
                'product_id' => $product->id,
                'plan_id' => $plan->id,
                'razorpay_subscription_id' => 'sub_' . uniqid(),
                'status' => 'active',
            ]);
        }

        return [$organization, $manager];
    }
}
