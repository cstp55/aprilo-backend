<?php

namespace Tests\Unit;

use App\Exceptions\PaymentServiceException;
use App\Services\RazorpaySubscriptionService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RazorpaySubscriptionServiceTest extends TestCase
{
    public function test_real_payment_api_failure_is_not_converted_to_fake_success(): void
    {
        Config::set('services.razorpay.key_id', 'rzp_live_test');
        Config::set('services.razorpay.key_secret', 'secret');
        Http::fake([
            'https://api.razorpay.com/v1/customers' => Http::response(['error' => ['description' => 'unavailable']], 503),
        ]);

        $this->expectException(PaymentServiceException::class);

        app(RazorpaySubscriptionService::class)->createCustomer([
            'name' => 'Customer',
            'email' => 'customer@example.com',
        ]);
    }
}
