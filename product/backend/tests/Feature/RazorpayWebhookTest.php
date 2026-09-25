<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationEntitlement;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RazorpayWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_updates_subscription_and_is_idempotent(): void
    {
        $organization = Organization::create(['name' => 'Webhook Organization']);
        $product = Product::create([
            'slug' => 'webhook-support-' . uniqid(),
            'name' => 'Support',
            'category' => 'ai_support',
            'product_type' => 'subscription',
            'status' => 'active',
            'is_active' => true,
        ]);
        $plan = Plan::create([
            'product_id' => $product->id,
            'slug' => 'professional',
            'name' => 'Professional',
            'is_active' => true,
        ]);
        $subscription = Subscription::create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'razorpay_subscription_id' => 'sub_webhook_test',
            'status' => 'trialing',
        ]);
        $entitlement = OrganizationEntitlement::create([
            'organization_id' => $organization->id,
            'subscription_id' => $subscription->id,
            'product_id' => $product->id,
            'feature_key' => 'support.agents',
            'status' => 'active',
        ]);
        $payload = [
            'event' => 'subscription.cancelled',
            'payload' => [
                'subscription' => [
                    'entity' => ['id' => 'sub_webhook_test'],
                ],
            ],
        ];

        $this->postJson('/api/webhooks/razorpay', $payload, ['X-Razorpay-Event-Id' => 'evt_webhook_1'])
            ->assertOk()
            ->assertJson(['status' => 'handled']);
        $this->postJson('/api/webhooks/razorpay', $payload, ['X-Razorpay-Event-Id' => 'evt_webhook_1'])
            ->assertOk()
            ->assertJson(['status' => 'already_handled']);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'cancelled',
        ]);
        $this->assertDatabaseHas('organization_entitlements', [
            'id' => $entitlement->id,
            'status' => 'suspended',
        ]);
        $this->assertDatabaseHas('payment_events', [
            'event_key' => 'evt_webhook_1',
            'status' => 'processed',
        ]);
        $this->assertDatabaseHas('audit_events', [
            'organization_id' => $organization->id,
            'event_type' => 'SUBSCRIPTION_SUBSCRIPTION_CANCELLED',
        ]);
    }
}
