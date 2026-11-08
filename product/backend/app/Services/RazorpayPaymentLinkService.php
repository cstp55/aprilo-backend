<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RazorpayPaymentLinkService
{
    private string $keyId;
    private string $keySecret;
    private string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct()
    {
        $this->keyId = trim((string) config('services.razorpay.key_id'));
        $this->keySecret = trim((string) config('services.razorpay.key_secret'));
    }

    public function getKeyId(): string
    {
        return $this->keyId;
    }

    public function isConfigured(): bool
    {
        return filled($this->keyId) && filled($this->keySecret);
    }

    public function isMockMode(): bool
    {
        return empty($this->keyId) || str_contains($this->keyId, 'mock');
    }

    public function isSandbox(): bool
    {
        return str_starts_with($this->keyId, 'rzp_test_');
    }

    public function isProduction(): bool
    {
        return str_starts_with($this->keyId, 'rzp_live_');
    }

    /**
     * Create real Razorpay Order for one-time payment.
     */
    public function createOrder(float $amount, string $currency = 'INR', string $receipt = '', array $notes = []): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Razorpay credentials are not configured. Please set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in your .env file.');
        }

        $amountInPaise = (int) round($amount * 100);

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->timeout(15)
                ->post($this->baseUrl . '/orders', [
                    'amount' => $amountInPaise,
                    'currency' => $currency,
                    'receipt' => $receipt ?: ('rcpt_' . bin2hex(random_bytes(4))),
                    'notes' => $notes,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            $errorMsg = $response->json('error.description') ?: "Razorpay Order API returned HTTP {$response->status()}";
            Log::error('Razorpay Order creation error: ' . $response->body());
            throw new RuntimeException($errorMsg);
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Razorpay Order connection failure: ' . $e->getMessage());
            throw new RuntimeException('Could not connect to Razorpay gateway: ' . $e->getMessage());
        }
    }

    /**
     * Verify real Razorpay HMAC-SHA256 payment signature.
     */
    public function verifyPaymentSignature(string $orderId, ?string $paymentId, ?string $signature): bool
    {
        if (blank($paymentId) || blank($signature) || blank($this->keySecret)) {
            return false;
        }

        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);

        return hash_equals($expected, $signature);
    }
}
