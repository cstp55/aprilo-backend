<?php

namespace App\Jobs;

use App\Models\WhatsAppCampaignRecipient;
use App\Services\WhatsAppCloudApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SendWhatsAppCampaignRecipient implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public WhatsAppCampaignRecipient $recipient) {}

    public function handle(WhatsAppCloudApi $api): void
    {
        $campaign = $this->recipient->campaign;
        $campaign->update(['status' => 'processing']);

        try {
            $result = $api->send($campaign, $this->recipient);
            $this->recipient->update([
                'status' => 'accepted',
                'whatsapp_message_id' => data_get($result, 'messages.0.id'),
                'response_json' => $result,
                'error_message' => null,
                'processed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $this->recipient->update([
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 2000),
                'processed_at' => now(),
            ]);
        }

        $this->updateCampaignStatus();
    }

    private function updateCampaignStatus(): void
    {
        $campaign = $this->recipient->campaign;
        $pendingCount = $campaign->recipients()->where('status', 'pending')->count();

        if ($pendingCount > 0) {
            return;
        }

        $acceptedCount = $campaign->recipients()->where('status', 'accepted')->count();
        $failedCount = $campaign->recipients()->where('status', 'failed')->count();
        $status = match (true) {
            $acceptedCount === 0 => 'failed',
            $failedCount > 0 => 'partial',
            default => 'completed',
        };

        DB::transaction(fn () => $campaign->update([
            'status' => $status,
            'completed_at' => now(),
        ]));
    }
}