<?php

namespace App\Console\Commands;

use App\Mail\TrialLifecycleReminderMail;
use App\Models\Organization;
use App\Models\WidgetConversation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTrialLifecycleNotificationsCommand extends Command
{
    protected $signature = 'aprilo:send-trial-notifications';
    protected $description = 'Dispatches automated trial lifecycle notifications (Day 3, 10, 27, 30)';

    public function handle(): int
    {
        $this->info('Starting automated trial lifecycle notifications scan...');

        $organizations = Organization::with(['owner', 'subscriptions', 'settings'])->get();
        $sentCount = 0;

        foreach ($organizations as $org) {
            $user = $org->owner ?: $org->users()->first();
            if (!$user || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Check if organization has an active paid subscription
            $hasPaidSub = $org->subscriptions()
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('current_cycle_end')->orWhere('current_cycle_end', '>=', now());
                })
                ->exists();

            if ($hasPaidSub) {
                continue; // Already a paying customer
            }

            $createdDate = Carbon::parse($org->created_at)->startOfDay();
            $ageInDays = (int) $createdDate->diffInDays(now()->startOfDay());

            $stage = null;
            $daysRemaining = 30 - $ageInDays;

            if ($ageInDays === 3) {
                $stage = 'day_3';
            } elseif ($ageInDays === 10) {
                $stage = 'day_10';
            } elseif ($ageInDays === 27) {
                $stage = 'day_27';
            } elseif ($ageInDays >= 30 && $ageInDays <= 32) {
                $stage = 'day_30';
            }

            if (!$stage) {
                continue;
            }

            try {
                $convCount = WidgetConversation::where('organization_id', $org->id)->count();

                Mail::to($user->email)->send(
                    new TrialLifecycleReminderMail(
                        organization: $org,
                        user: $user,
                        stage: $stage,
                        daysRemaining: max(0, $daysRemaining),
                        conversationsCount: $convCount
                    )
                );

                $sentCount++;
                $this->info("Dispatched {$stage} notification to {$user->email} ({$org->name})");
            } catch (\Throwable $e) {
                Log::warning("Failed to send trial notification to {$user->email}: " . $e->getMessage());
            }
        }

        $this->info("Completed. Total notifications sent: {$sentCount}");
        return 0;
    }
}
