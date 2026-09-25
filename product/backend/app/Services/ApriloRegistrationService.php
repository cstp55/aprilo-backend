<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ApriloRegistrationService
{
    public function register(array $account, array $organization, array $product, array $plan, string $idempotencyKey): array
    {
        $url = (string) config('services.aprilo.registration_url');
        if ($url === '') {
            if (app()->environment(['local', 'testing'])) {
                return ['skipped' => true, 'reason' => 'registration_url_not_configured'];
            }

            throw new RuntimeException('The external Aprilo registration service is not configured.');
        }
        if (parse_url($url, PHP_URL_SCHEME) !== 'https' && ! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('The external Aprilo registration service must use HTTPS.');
        }

        $response = $this->client($idempotencyKey)->post($url, [
            'account' => [
                'name' => $account['name'],
                'username' => $account['username'],
                'email' => $account['email'],
                'phone' => $account['phone'] ?? null,
                'password' => $account['password'] ?? null,
            ],
            'organization' => $organization,
            'product' => $product,
            'plan' => $plan,
        ]);

        if (! $response->successful()) {
            Log::warning('Aprilo registration API failed', [
                'status' => $response->status(),
                'idempotency_key' => hash('sha256', $idempotencyKey),
            ]);
            throw new RuntimeException('The external Aprilo registration service is temporarily unavailable.');
        }

        return $response->json() ?: [];
    }

    private function client(string $idempotencyKey): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->withHeaders([
                'Idempotency-Key' => $idempotencyKey,
            ])
            ->when(config('services.aprilo.registration_token'), function (PendingRequest $client, $token) {
                return $client->withToken($token);
            })
            ->connectTimeout(5)
            ->timeout(15)
            ->retry(2, 250, throw: false);
    }
}
