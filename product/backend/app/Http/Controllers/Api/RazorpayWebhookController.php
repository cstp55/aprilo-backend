<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OnboardingRequest;
use App\Models\Subscription;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function __construct(private RazorpayService $razorpayService)
    {
    }

    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('X-Razorpay-Signature', '');
        $payload = $request->getContent();

        if (! $this->razorpayService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Razorpay Webhook: Invalid Signature');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = $request->input('event');
        $data = $request->input('payload');

        Log::info("Razorpay Webhook received: {$event}", ['payload' => $data]);

        switch ($event) {
            case 'subscription.authenticated':
            case 'subscription.activated':
                $subId = $data['subscription']['entity']['id'] ?? null;
                if ($subId) {
                    Subscription::where('razorpay_subscription_id', $subId)->update([
                        'status' => 'active',
                    ]);
                }
                break;

            case 'subscription.charged':
                $subId = $data['subscription']['entity']['id'] ?? null;
                if ($subId) {
                    $cycleEnd = isset($data['subscription']['entity']['current_end']) 
                        ? date('Y-m-d H:i:s', $data['subscription']['entity']['current_end']) 
                        : now()->addDays(30);

                    Subscription::where('razorpay_subscription_id', $subId)->update([
                        'status' => 'active',
                        'current_cycle_end' => $cycleEnd,
                    ]);
                }
                break;

            case 'subscription.halted':
            case 'subscription.cancelled':
                $subId = $data['subscription']['entity']['id'] ?? null;
                if ($subId) {
                    Subscription::where('razorpay_subscription_id', $subId)->update([
                        'status' => 'cancelled',
                    ]);
                }
                break;
        }

        return response()->json(['status' => 'handled']);
    }
}