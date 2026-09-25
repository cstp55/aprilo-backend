<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\PaymentEvent;
use App\Models\Subscription;
use App\Services\RazorpaySubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

class RazorpayWebhookController extends Controller
{
    public function __construct(private RazorpaySubscriptionService $razorpayService)
    {
    }

    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('X-Razorpay-Signature', '');
        $payload = $request->getContent();

        if (! $this->razorpayService->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = $request->input('event');
        $data = $request->input('payload');
        $eventKey = $request->header('X-Razorpay-Event-Id') ?: hash('sha256', $payload);
        $subscriptionId = $data['subscription']['entity']['id'] ?? null;

        try {
            $paymentEvent = PaymentEvent::create([
                'event_key' => $eventKey,
                'event_type' => (string) $event,
                'razorpay_subscription_id' => $subscriptionId,
                'status' => 'received',
                'metadata' => ['source' => 'razorpay_webhook'],
            ]);
        } catch (UniqueConstraintViolationException) {
            return response()->json(['status' => 'already_handled']);
        }

        $status = match ($event) {
            'subscription.authenticated', 'subscription.activated', 'subscription.charged' => 'active',
            'subscription.halted' => 'halted',
            'subscription.cancelled' => 'cancelled',
            'subscription.pending', 'subscription.charged.failed' => 'past_due',
            default => null,
        };

        if ($subscriptionId && $status) {
            $subscription = Subscription::where('razorpay_subscription_id', $subscriptionId)->first();
            if ($subscription) {
                $subscription->update(array_filter([
                    'status' => $status,
                    'current_cycle_end' => isset($data['subscription']['entity']['current_end'])
                        ? date('Y-m-d H:i:s', $data['subscription']['entity']['current_end'])
                        : null,
                ], fn ($value) => $value !== null));
                $subscription->organization->entitlements()->where('subscription_id', $subscription->id)->update([
                    'status' => $status === 'active' ? 'active' : 'suspended',
                ]);

                AuditEvent::create([
                    'organization_id' => $subscription->organization_id,
                    'actor_user_id' => null,
                    'event_type' => 'SUBSCRIPTION_' . Str::upper(str_replace('.', '_', (string) $event)),
                    'entity_type' => 'subscription',
                    'entity_id' => $subscription->id,
                    'metadata' => ['payment_event_id' => $paymentEvent->id, 'status' => $status],
                ]);
            }
        }

        $paymentEvent->update(['status' => 'processed', 'processed_at' => now()]);

        return response()->json(['status' => 'handled']);
    }
}