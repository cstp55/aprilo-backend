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

        // Verify Aprilo Support AI has 30-day free trial on starter plan
        $supportProduct = collect($products)->firstWhere('slug', 'aprilo-support');
        $this->assertNotNull($supportProduct);

        $starterPlan = collect($supportProduct['plans'])->firstWhere('slug', 'starter');
        $this->assertNotNull($starterPlan);
        $this->assertEquals(30, $starterPlan['trial_period_days']);
        $this->assertTrue($starterPlan['has_free_trial']);
        $this->assertEquals(499, $starterPlan['price']);
        $this->assertEquals(40000, $starterPlan['request_limit']);
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

    public function test_initiate_and_verify_onboarding_flow(): void
    {
        $product = Product::where('slug', 'aprilo-support')->firstOrFail();
        $plan = Plan::where('product_id', $product->id)->where('slug', 'starter')->firstOrFail();

        $username = 'testcorp_' . rand(1000, 9999);
        $email = 'admin@' . $username . '.com';

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
                'email' => 'contact@' . $username . '.com',
                'website' => 'https://acme.example.com',
                'country' => 'India',
                'industry' => 'E-Commerce',
                'team_size' => '11-50',
                'timezone' => 'Asia/Kolkata',
            ],
            'autopay_consent' => true,
        ]);

        $initiateResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'token',
                'subscription_id',
                'product',
                'plan',
                'due_today',
                'next_billing_date',
            ]);

        $token = $initiateResponse->json('token');
        $subscriptionId = $initiateResponse->json('subscription_id');

        // Verify pending request in database
        $this->assertDatabaseHas('onboarding_requests', [
            'token' => $token,
            'status' => 'pending',
            'razorpay_subscription_id' => $subscriptionId,
        ]);

        // 2. Verify Mandate / Activation
        $verifyResponse = $this->postJson('/api/onboarding/verify', [
            'token' => $token,
            'razorpay_subscription_id' => $subscriptionId,
            'razorpay_payment_id' => 'pay_test_' . rand(10000, 99999),
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
                'auth_token',
                'sso_redirect_url',
            ]);

        $orgId = $verifyResponse->json('organization.id');
        $userId = $verifyResponse->json('user.id');

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