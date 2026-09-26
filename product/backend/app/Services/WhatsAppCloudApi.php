<?php

namespace App\Services;

use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignRecipient;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppCloudApi
{
    public function send(WhatsAppCampaign $campaign, WhatsAppCampaignRecipient $recipient): array
    {
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $accessToken = config('services.whatsapp.access_token');

        if (! $phoneNumberId || ! $accessToken) {
            throw new RuntimeException('WhatsApp Cloud API credentials are not configured.');
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $recipient->recipient_phone,
            'type' => $campaign->message_type,
        ];

        if ($campaign->message_type === 'template') {
            $template = [
                'name' => $campaign->template_name,
                'language' => ['code' => $campaign->template_language],
            ];

            if ($campaign->template_parameters) {
                $template['components'] = [[
                    'type' => 'body',
                    'parameters' => array_map(
                        fn (string $text): array => ['type' => 'text', 'text' => $text],
                        $campaign->template_parameters
                    ),
                ]];
            }

            $payload['template'] = $template;
        } else {
            $payload['text'] = [
                'preview_url' => false,
                'body' => $campaign->message_text,
            ];
        }

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(20)
            ->post(sprintf(
                'https://graph.facebook.com/%s/%s/messages',
                config('services.whatsapp.api_version', 'v25.0'),
                $phoneNumberId
            ), $payload);

        if (! $response->successful()) {
            $message = $response->json('error.message') ?: 'Meta rejected the WhatsApp message.';
            throw new RuntimeException(sprintf('Meta API error (%d): %s', $response->status(), $message));
        }

        return $response->json();
    }
}