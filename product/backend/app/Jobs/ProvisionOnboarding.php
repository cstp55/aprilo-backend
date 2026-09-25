<?php

namespace App\Jobs;

use App\Mail\OnboardingConfirmation;
use App\Models\AuditEvent;
use App\Models\OnboardingRequest;
use App\Services\ApriloRegistrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ProvisionOnboarding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public string $onboardingId)
    {
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(ApriloRegistrationService $registration): void
    {
        $onboarding = OnboardingRequest::with(['organization', 'user', 'product', 'plan'])->findOrFail($this->onboardingId);

        if ($onboarding->provisioning_status === 'completed') {
            return;
        }

        $onboarding->update([
            'provisioning_status' => 'processing',
            'provisioning_attempts' => $onboarding->provisioning_attempts + 1,
            'provisioning_error' => null,
        ]);

        $account = $onboarding->account_data;
        $password = ! empty($account['password_encrypted'])
            ? Crypt::decryptString($account['password_encrypted'])
            : null;

        try {
            $registration->register(
                array_merge($account, ['password' => $password]),
                $onboarding->organization_data,
                ['id' => $onboarding->product?->id, 'slug' => $onboarding->product?->slug, 'name' => $onboarding->product?->name],
                ['id' => $onboarding->plan?->id, 'slug' => $onboarding->plan?->slug, 'name' => $onboarding->plan?->name],
                $onboarding->token
            );

            unset($account['password_encrypted']);
            $onboarding->update([
                'account_data' => $account,
                'provisioning_status' => 'completed',
                'provisioned_at' => now(),
            ]);

            $this->audit($onboarding, 'PROVISIONING_COMPLETED');
            Mail::to($onboarding->user?->email ?? $account['email'])->queue(new OnboardingConfirmation($onboarding->fresh(['organization', 'user', 'product', 'plan'])));
        } catch (Throwable $exception) {
            $onboarding->update([
                'provisioning_status' => 'retrying',
                'provisioning_error' => 'Provisioning could not be completed. The system will retry automatically.',
            ]);
            $this->audit($onboarding, 'PROVISIONING_RETRY_SCHEDULED');
            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        $onboarding = OnboardingRequest::find($this->onboardingId);
        if (! $onboarding) {
            return;
        }

        $onboarding->update([
            'provisioning_status' => 'failed',
            'provisioning_error' => 'Provisioning failed after the configured retries. Support has been notified.',
        ]);
        $this->audit($onboarding, 'PROVISIONING_FAILED');
    }

    private function audit(OnboardingRequest $onboarding, string $event): void
    {
        if (! $onboarding->organization_id) {
            return;
        }

        AuditEvent::create([
            'organization_id' => $onboarding->organization_id,
            'actor_user_id' => $onboarding->user_id,
            'event_type' => $event,
            'entity_type' => 'onboarding_request',
            'entity_id' => $onboarding->id,
            'metadata' => ['onboarding_status' => $onboarding->provisioning_status],
        ]);
    }
}
