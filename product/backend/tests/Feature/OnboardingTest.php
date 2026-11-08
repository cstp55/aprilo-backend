<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\OnboardingRequest;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\ProductsAndPlansSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductsAndPlansSeeder::class);
    }

    public function test_catalog_endpoint_returns_dynamic_products_and_plans_with_trial(): void
    {
        $response = $this->getJson('/api/onboarding/catalog');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'products',
                'razorpay_key_id',
                'trial_policy' => [
                    'highlight',
                    'starter_price',
                    'starter_quota',
                    'currency',
                    'autopay_terms',
                ],
            ]);

        $products = $response->json('products');
        $this->assertNotEmpty($products);

        // Verify Aprilo AI / Support has 30-day free trial on starter plan
        $supportProduct = collect($products)->firstWhere('slug', 'aprilo-ai')
            ?? collect($products)->firstWhere('slug', 'aprilo-support');
        $this->assertNotNull($supportProduct);

        $starterPlan = collect($supportProduct['plans'])->firstWhere('slug', 'starter')
            ?? $supportProduct['plans'][0];
        $this->assertNotNull($starterPlan);
        $this->assertEquals(30, $starterPlan['trial_period_days']);
        $this->assertTrue($starterPlan['has_free_trial']);

        $commerceProduct = collect($products)->firstWhere('slug', 'aprilo-magento-suite');
        $this->assertSame('one_time_license', $commerceProduct['product_type']);
        $this->assertSame('one_time', $commerceProduct['plans'][0]['billing_cycle']);
        $this->assertStringContainsString('No recurring subscription', $commerceProduct['plans'][0]['autopay_notice']);
    }

    public function test_username_check_endpoint(): void
    {
        $response = $this->postJson('/api/onboarding/check-username', [
            'username' => 'fresh_unique_user_2026',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'available' => true,
            ]);
    }

    public function test_one_time_ecommerce_products_are_not_started_as_subscriptions(): void
    {
        $product = Product::where('slug', 'aprilo-magento-suite')->firstOrFail();
        $plan = Plan::where('product_id', $product->id)->where('slug', 'single-store')->firstOrFail();

        $this->postJson('/api/onboarding/initiate', [
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'account' => [
                'name' => 'Commerce Owner',
                'username' => 'commerce_' . rand(1000, 9999),
                'email' => 'commerce-' . rand(1000, 9999) . '@example.com',
                'phone' => '+919876543210',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
            ],
            'organization' => [
                'name' => 'Commerce Customer',
                'email' => 'customer-' . rand(1000, 9999) . '@example.com',
            ],
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'This product requires one-time license checkout. Recurring onboarding is not available for it.');
    }

    public function test_initiate_and_verify_onboarding_flow(): void
    {
        $product = Product::where('slug', 'aprilo-ai')->first() ?? Product::where('slug', 'aprilo-support')->firstOrFail();
        $plan = Plan::where('product_id', $product->id)->where('slug', 'starter')->first() ?? Plan::where('product_id', $product->id)->firstOrFail();
        $plan->update(['razorpay_plan_id' => 'plan_test_starter_mock']);

        $username = 'testcorp_' . rand(1000, 9999);
        $email = 'admin@' . str_replace('_', '-', $username) . '.com';

        // 1. Initiate
        $initiateResponse = $this->postJson('/api/onboarding/initiate', [
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'account' => [
                'name' => 'John Enterprise',
                'username' => $username,
                'email' => $email,
                'phone' => '+919876543210',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
            ],
            'organization' => [
                'name' => 'Acme Corp ' . rand(100, 999),
                'email' => 'contact@' . str_replace('_', '-', $username) . '.com',
                'website' => 'https://acme.example.com',
                'country' => 'India',
                'industry' => 'E-Commerce',
                'team_size' => '11-50',
                'timezone' => 'Asia/Kolkata',
            ],
            'autopay_consent' => true,
        ]);

        if ($initiateResponse->status() !== 200) {
            dump($initiateResponse->json());
        }

        $initiateResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'token',
                'subscription_id',
                'razorpay_order_id',
                'product',
                'plan',
                'platform_fee' => [
                    'amount',
                    'currency',
                    'base_amount',
                    'gst_amount',
                    'gst_rate',
                    'order_id',
                ],
                'due_today',
                'next_billing_date',
            ]);

        $this->assertEquals(19.00, $initiateResponse->json('due_today'));
        $this->assertEquals(19.00, $initiateResponse->json('platform_fee.amount'));

        $token = $initiateResponse->json('token');
        $subscriptionId = $initiateResponse->json('subscription_id');
        $orderId = $initiateResponse->json('razorpay_order_id');

        // Verify pending request in database with ₹19 platform fee and order ID
        $this->assertDatabaseHas('onboarding_requests', [
            'token' => $token,
            'status' => 'pending',
            'razorpay_subscription_id' => $subscriptionId,
            'razorpay_order_id' => $orderId,
            'platform_fee_amount' => 19.00,
        ]);

        // 2. Verify Mandate / Activation
        $paymentId = 'pay_test_' . rand(10000, 99999);
        $verifyResponse = $this->postJson('/api/onboarding/verify', [
            'token' => $token,
            'razorpay_subscription_id' => $subscriptionId,
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => 'sim_sig_valid_test',
        ]);

        $verifyResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'organization' => ['id', 'name', 'status'],
                'user' => ['id', 'name', 'username', 'email'],
                'subscription' => ['id', 'status', 'trial_end', 'request_limit'],
                'payment' => ['id', 'reference', 'amount', 'currency', 'status', 'gateway_payment_id'],
                'invoice' => ['id', 'invoice_number', 'amount', 'subtotal', 'gst_amount', 'currency', 'status'],
                'auth_token',
                'sso_redirect_url',
            ]);

        $this->assertEquals(19.00, $verifyResponse->json('payment.amount'));
        $this->assertEquals('paid', $verifyResponse->json('payment.status'));
        $this->assertEquals(19.00, $verifyResponse->json('invoice.amount'));

        $orgId = $verifyResponse->json('organization.id');
        $userId = $verifyResponse->json('user.id');

        // Assert Payment stored in payments table
        $this->assertDatabaseHas('payments', [
            'organization_id' => $orgId,
            'user_id' => $userId,
            'payment_type' => 'platform_fee',
            'gateway' => 'razorpay',
            'gateway_order_id' => $orderId,
            'gateway_payment_id' => $paymentId,
            'amount' => 19.00,
            'currency' => 'INR',
            'status' => 'paid',
        ]);

        // Assert Invoice stored in invoices table
        $this->assertDatabaseHas('invoices', [
            'organization_id' => $orgId,
            'document_type' => 'sales_invoice',
            'amount' => 19.00,
            'subtotal' => 16.10,
            'gst_amount' => 2.90,
            'total_amount' => 19.00,
            'product_type' => 'platform_charge',
            'status' => 'paid',
        ]);

        // Check Organization created
        $this->assertDatabaseHas('organizations', [
            'id' => $orgId,
            'status' => 'active',
            'plan' => 'starter',
        ]);

        // Check Owner User created
        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'organization_id' => $orgId,
            'username' => $username,
            'email' => $email,
            'role' => UserRole::Owner->value,
            'status' => 'active',
        ]);

        // Check Subscription trialing
        $this->assertDatabaseHas('subscriptions', [
            'organization_id' => $orgId,
            'razorpay_subscription_id' => $subscriptionId,
            'status' => 'trialing',
            'request_limit' => 40000,
        ]);

        // 3. Test SSO Single-Use Launch
        $ssoUrl = $verifyResponse->json('sso_redirect_url');
        $parsed = parse_url($ssoUrl);
        parse_str($parsed['query'] ?? '', $queryParams);
        $ssoToken = $queryParams['token'] ?? null;

        $this->assertNotNull($ssoToken);

        $ssoResponse = $this->get('/admin/sso?token=' . $ssoToken);
        $ssoResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs(User::find($userId));

        // Ensure SSO token was invalidated
        $this->assertDatabaseHas('onboarding_requests', [
            'token' => $token,
            'sso_token' => null,
        ]);
    }
}