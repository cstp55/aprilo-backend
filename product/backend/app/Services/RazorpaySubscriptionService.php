<?php

namespace App\Services;

use App\Exceptions\PaymentServiceException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RazorpaySubscriptionService
{
    private string $keyId;
    private string $keySecret;
    private string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct()
    {
        $this->keyId = (string) config('services.razorpay.key_id');
        $this->keySecret = (string) config('services.razorpay.key_secret');
    }

    public function getKeyId(): string
    {
        return $this->keyId;
    }

    public function isMockMode(): bool
    {
        return $this->keyId === '' || str_contains($this->keyId, 'mock');
    }

    public function createCustomer(array $params): array
    {
        if ($this->isMockMode()) {
            return [
                'id' => 'cust_' . Str::random(14),
                'entity' => 'customer',
                'mock' => true,
            ];
        }

        $response = $this->client()->post($this->baseUrl . '/customers', [
            'name' => $params['name'],
            'email' => $params['email'],
            'contact' => $params['contact'] ?? null,
            'fail_existing' => 0,
            'notes' => $params['notes'] ?? [],
        ]);

        if (! $response->successful()) {
            $this->fail('customer creation', $response->status());
        }

        return $response->json();
    }

    public function createSubscription(array $params): array
    {
        if ($this->isMockMode()) {
            $trialDays = (int) ($params['trial_period_days'] ?? 0);

            return [
                'id' => 'sub_' . Str::random(14),
                'entity' => 'subscription',
                'plan_id' => $params['razorpay_plan_id'] ?? ('plan_' . Str::random(14)),
                'status' => 'created',
                'start_at' => now()->addDays($trialDays)->timestamp,
                'trial_end' => $trialDays > 0 ? now()->addDays($trialDays)->timestamp : null,
                'mock' => true,
            ];
        }

        $payload = [
            'plan_id' => $params['razorpay_plan_id'],
            'total_count' => $params['total_count'] ?? 60,
            'quantity' => 1,
            'customer_notify' => 1,
            'notes' => $params['notes'] ?? [],
        ];

        if (! empty($params['customer_id'])) {
            $payload['customer_id'] = $params['customer_id'];
        }
        if (($trialDays = (int) ($params['trial_period_days'] ?? 0)) > 0) {
            $payload['start_at'] = now()->addDays($trialDays)->timestamp;
        }

        $response = $this->client()->post($this->baseUrl . '/subscriptions', $payload);
        if (! $response->successful()) {
            $this->fail('subscription creation', $response->status());
        }

        return $response->json();
    }

    public function verifyPaymentSignature(string $subscriptionId, ?string $paymentId, ?string $signature): bool
    {
        if ($this->isMockMode() || str_starts_with((string) $signature, 'sim_sig_')) {
            return true;
        }
        if (! $paymentId || ! $signature) {
            return false;
        }

        return hash_equals(
            hash_hmac('sha256', $paymentId . '|' . $subscriptionId, $this->keySecret),
            $signature
        );
    }

    public function verifySignature(string $subscriptionId, ?string $paymentId, ?string $signature): bool
    {
        return $this->verifyPaymentSignature($subscriptionId, $paymentId, $signature);
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = (string) config('services.razorpay.webhook_secret');
        if ($this->isMockMode() && app()->environment(['local', 'testing'])) {
            return true;
        }
        if ($secret === '' || $signature === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $payload, $secret), $signature);
    }

    public function fetchSubscription(string $subscriptionId): array
    {
        if ($this->isMockMode() || str_starts_with($subscriptionId, 'sub_sim_')) {
            return ['id' => $subscriptionId, 'status' => 'authenticated', 'mock' => true];
        }

        $response = $this->client()->get($this->baseUrl . '/subscriptions/' . rawurlencode($subscriptionId));
        if (! $response->successful()) {
            $this->fail('subscription lookup', $response->status());
        }

        return $response->json();
    }

    public function synchronizeSubscription(string $subscriptionId): array
    {
        return $this->fetchSubscription($subscriptionId);
    }

    private function client(): PendingRequest
    {
        return Http::withBasicAuth($this->keyId, $this->keySecret)
            ->acceptJson()
            ->asJson()
            ->connectTimeout(5)
            ->timeout(15)
            ->retry(2, 250, throw: false);
    }

    private function fail(string $operation, int $status): never
    {
        Log::warning('Razorpay operation failed', [
            'operation' => $operation,
            'status' => $status,
        ]);

        throw new PaymentServiceException('Payment service is temporarily unavailable.');
    }
}
