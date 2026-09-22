<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\OnboardingRequest;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function __construct(private RazorpayService $razorpayService)
    {
    }

    /**
     * GET /api/onboarding/catalog
     * Returns active products and plans with trial periods, quotas, and pricing.
     */
    public function catalog(): JsonResponse
    {
        $products = Product::with(['plans' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order', 'asc');
        }])
        ->where('is_active', true)
        ->orderBy('sort_order', 'asc')
        ->get()
        ->map(function ($product) {
            return [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'category' => $product->category,
                'product_type' => $product->product_type,
                'platform' => $product->platform,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'icon' => $product->icon,
                'featured' => $product->featured,
                'best_seller' => $product->best_seller,
                'features' => $product->features ?? [],
                'metadata' => $product->metadata ?? [],
                'plans' => $product->plans->map(function ($plan) {
                    return [
                        'id' => $plan->id,
                        'slug' => $plan->slug,
                        'name' => $plan->name,
                        'billing_cycle' => $plan->billing_cycle,
                        'price' => (float) $plan->price,
                        'currency' => $plan->currency,
                        'trial_period_days' => $plan->trial_period_days,
                        'has_free_trial' => $plan->trial_period_days > 0,
                        'request_limit' => $plan->request_limit,
                        'is_popular' => $plan->is_popular,
                        'features' => $plan->features ?? [],
                        'metadata' => $plan->metadata ?? [],
                        'autopay_notice' => $plan->trial_period_days > 0 
                            ? "Includes a 1-month ({$plan->trial_period_days}-day) 100% free trial. An AutoPay recurring mandate will begin after the trial ends unless cancelled."
                            : "Standard recurring subscription.",
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'products' => $products,
            'razorpay_key_id' => $this->razorpayService->getKeyId(),
            'trial_policy' => [
                'highlight' => '1-Month Free Trial available on AI Support Services',
                'starter_price' => 499,
                'starter_quota' => 40000,
                'currency' => 'INR',
                'autopay_terms' => 'The first 30 days are free of charge. Your payment method will be registered with Razorpay AutoPay mandate. Recurring billing commences automatically after 30 days. You may cancel at any time from your admin dashboard without charge.',
            ],
        ]);
    }

    /**
     * POST /api/onboarding/check-username
     * Checks username availability in real-time.
     */
    public function checkUsername(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'available' => false,
                'message' => $validator->errors()->first('username'),
            ], 422);
        }

        $username = strtolower(trim($request->input('username')));
        $exists = User::whereRaw('LOWER(username) = ?', [$username])->exists();

        return response()->json([
            'available' => ! $exists,
            'username' => $username,
            'message' => $exists ? 'Username is already taken' : 'Username is available',
        ]);
    }

    /**
     * POST /api/onboarding/initiate
     * Validates account and organization details, sets up pending onboarding request and Razorpay mandate subscription.
     */
    public function initiate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['required', 'string', 'exists:products,id'],
            'plan_id' => ['required', 'string', 'exists:plans,id'],
            
            // Account fields
            'account.name' => ['required', 'string', 'max:120'],
            'account.username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_\-\.]+$/', 'unique:users,username'],
            'account.email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'account.phone' => ['required', 'string', 'min:8', 'max:20'],
            'account.password' => ['required', 'string', 'min:8', 'confirmed'],

            // Organization fields
            'organization.name' => ['required', 'string', 'max:150'],
            'organization.email' => ['required', 'email', 'max:190'],
            'organization.website' => ['nullable', 'string', 'max:190'],
            'organization.country' => ['nullable', 'string', 'max:100'],
            'organization.industry' => ['nullable', 'string', 'max:100'],
            'organization.team_size' => ['nullable', 'string', 'max:50'],
            'organization.timezone' => ['nullable', 'string', 'max:80'],

            // AutoPay Consent
            'autopay_consent' => ['required', 'accepted'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = Product::findOrFail($request->input('product_id'));
        $plan = Plan::where('product_id', $product->id)->where('id', $request->input('plan_id'))->firstOrFail();

        $accountData = $request->input('account');
        $organizationData = $request->input('organization');

        // Create Razorpay recurring subscription
        $razorpaySub = $this->razorpayService->createSubscription([
            'razorpay_plan_id' => $plan->razorpay_plan_id,
            'price' => (float) $plan->price,
            'currency' => $plan->currency,
            'trial_period_days' => $plan->trial_period_days,
            'email' => $accountData['email'],
            'org_name' => $organizationData['name'],
        ]);

        $token = Str::random(64);
        $trialDays = (int) $plan->trial_period_days;
        $trialEndsAt = $trialDays > 0 ? now()->addDays($trialDays) : null;

        $onboardingRequest = OnboardingRequest::create([
            'token' => $token,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'account_data' => [
                'name' => trim($accountData['name']),
                'username' => strtolower(trim($accountData['username'])),
                'email' => strtolower(trim($accountData['email'])),
                'phone' => trim($accountData['phone']),
                'password_hash' => Hash::make($accountData['password']),
            ],
            'organization_data' => [
                'name' => trim($organizationData['name']),
                'email' => strtolower(trim($organizationData['email'])),
                'website' => $organizationData['website'] ?? null,
                'country' => $organizationData['country'] ?? 'India',
                'industry' => $organizationData['industry'] ?? 'Technology',
                'team_size' => $organizationData['team_size'] ?? '1-10',
                'timezone' => $organizationData['timezone'] ?? 'Asia/Kolkata',
            ],
            'razorpay_subscription_id' => $razorpaySub['id'],
            'status' => 'pending',
            'trial_ends_at' => $trialEndsAt,
            'autopay_authorized' => true,
        ]);

        return response()->json([
            'success' => true,
            'token' => $onboardingRequest->token,
            'subscription_id' => $razorpaySub['id'],
            'razorpay_key_id' => $this->razorpayService->getKeyId(),
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category,
            ],
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => (float) $plan->price,
                'currency' => $plan->currency,
                'trial_period_days' => $plan->trial_period_days,
                'request_limit' => $plan->request_limit,
            ],
            'due_today' => 0.00,
            'next_billing_date' => $trialEndsAt ? $trialEndsAt->toIso8601String() : now()->toIso8601String(),
            'autopay_disclosure' => "Your 1-month ({$trialDays}-day) free trial begins immediately. Recurring billing of ₹{$plan->price}/month will begin automatically on {$trialEndsAt?->format('F d, Y')}.",
        ]);
    }

    /**
     * POST /api/onboarding/verify
     * Securely verifies mandate/payment authorization and provisions organization, user, and subscription.
     */
    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string', 'exists:onboarding_requests,token'],
            'razorpay_subscription_id' => ['required', 'string'],
            'razorpay_payment_id' => ['nullable', 'string'],
            'razorpay_signature' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification parameters',
                'errors' => $validator->errors(),
            ], 422);
        }

        $onboarding = OnboardingRequest::where('token', $request->input('token'))->firstOrFail();

        // Idempotency: If already completed, return existing credentials
        if ($onboarding->status === 'completed' && $onboarding->organization_id && $onboarding->user_id) {
            $user = User::find($onboarding->user_id);
            $org = Organization::find($onboarding->organization_id);
            $token = $user->createToken('onboarding-session')->plainTextToken;

            return response()->json([
                'success' => true,
                'already_completed' => true,
                'organization' => [
                    'id' => $org->id,
                    'name' => $org->name,
                    'plan' => $org->plan,
                    'status' => $org->status,
                ],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                ],
                'auth_token' => $token,
                'admin_url' => url('/admin'),
                'sso_redirect_url' => url('/admin/sso?token=' . $onboarding->sso_token),
            ]);
        }

        $subscriptionId = $request->input('razorpay_subscription_id');
        $paymentId = $request->input('razorpay_payment_id');
        $signature = $request->input('razorpay_signature');

        // Verify Razorpay signature or mandate status
        $isValid = $this->razorpayService->verifySignature($subscriptionId, $paymentId, $signature);
        if (! $isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization verification failed. Please check your payment details or contact support.',
            ], 400);
        }

        $product = Product::findOrFail($onboarding->product_id);
        $plan = Plan::findOrFail($onboarding->plan_id);
        $account = $onboarding->account_data;
        $orgData = $onboarding->organization_data;

        $ssoToken = Str::random(64);

        $result = DB::transaction(function () use ($onboarding, $product, $plan, $account, $orgData, $subscriptionId, $paymentId, $signature, $ssoToken) {
            // 1. Create Organization
            $organization = Organization::create([
                'name' => $orgData['name'],
                'email' => $orgData['email'],
                'website' => $orgData['website'] ?? null,
                'country' => $orgData['country'] ?? 'India',
                'industry' => $orgData['industry'] ?? 'Technology',
                'team_size' => $orgData['team_size'] ?? '1-10',
                'timezone' => $orgData['timezone'] ?? 'Asia/Kolkata',
                'status' => 'active',
                'plan' => $plan->slug,
            ]);

            // 2. Create Organization Settings with Support Agent defaults
            OrganizationSetting::create([
                'organization_id' => $organization->id,
                'assistant_name' => $organization->name . ' Assistant',
                'billing_mode' => 'subscription',
                'usage_queries_count' => 0,
                'usage_amount_due' => 0.00,
                'chatbot_mode' => 'customer_support',
                'chatbot_intelligence_level' => 'standard',
                'organization_details' => json_encode($orgData),
            ]);

            // 3. Find Owner Role
            $ownerRole = Role::where('slug', 'owner')->first();

            // 4. Create Owner Admin User
            $user = User::create([
                'organization_id' => $organization->id,
                'name' => $account['name'],
                'username' => $account['username'],
                'email' => $account['email'],
                'phone' => $account['phone'] ?? null,
                'password' => $account['password_hash'],
                'role' => UserRole::Owner->value,
                'role_id' => $ownerRole?->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            // 5. Create Subscription record
            $trialDays = (int) $plan->trial_period_days;
            $subscription = Subscription::create([
                'organization_id' => $organization->id,
                'product_id' => $product->id,
                'plan_id' => $plan->id,
                'razorpay_subscription_id' => $subscriptionId,
                'razorpay_plan_id' => $plan->razorpay_plan_id,
                'status' => $trialDays > 0 ? 'trialing' : 'active',
                'trial_start' => $trialDays > 0 ? now() : null,
                'trial_end' => $trialDays > 0 ? now()->addDays($trialDays) : null,
                'current_cycle_start' => now(),
                'current_cycle_end' => now()->addDays(30),
                'price' => (float) $plan->price,
                'currency' => $plan->currency,
                'request_limit' => $plan->request_limit,
                'auto_renew' => true,
                'metadata' => [
                    'payment_id' => $paymentId,
                    'signature_verified' => true,
                ],
            ]);

            // 6. Update OnboardingRequest
            $onboarding->update([
                'status' => 'completed',
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'sso_token' => $ssoToken,
                'sso_token_expires_at' => now()->addMinutes(30),
            ]);

            $sanctumToken = $user->createToken('onboarding-session')->plainTextToken;

            return [
                'organization' => $organization,
                'user' => $user,
                'subscription' => $subscription,
                'token' => $sanctumToken,
                'sso_token' => $ssoToken,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Organization account successfully activated!',
            'organization' => [
                'id' => $result['organization']->id,
                'name' => $result['organization']->name,
                'plan' => $result['organization']->plan,
                'status' => $result['organization']->status,
                'created_at' => $result['organization']->created_at->toIso8601String(),
            ],
            'user' => [
                'id' => $result['user']->id,
                'name' => $result['user']->name,
                'username' => $result['user']->username,
                'email' => $result['user']->email,
                'role' => $result['user']->role,
            ],
            'subscription' => [
                'id' => $result['subscription']->id,
                'status' => $result['subscription']->status,
                'trial_end' => $result['subscription']->trial_end?->toIso8601String(),
                'request_limit' => $result['subscription']->request_limit,
                'price' => $result['subscription']->price,
                'currency' => $result['subscription']->currency,
            ],
            'auth_token' => $result['token'],
            'admin_url' => url('/admin'),
            'sso_redirect_url' => url('/admin/sso?token=' . $result['sso_token']),
        ]);
    }
}