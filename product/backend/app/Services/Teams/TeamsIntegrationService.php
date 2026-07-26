<?php

namespace App\Services\Teams;

use App\Models\Escalation;
use App\Models\OrganizationSetting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeamsIntegrationService
{
    /**
     * Get OAuth token from Microsoft Bot Framework.
     */
    public function getAccessToken(OrganizationSetting $settings): ?string
    {
        $appId = $settings->teams_app_id;
        $password = $settings->teams_app_password;

        if (!$appId || !$password) {
            Log::error("Teams app credentials missing for organization settings ID: {$settings->id}");
            return null;
        }

        try {
            $response = Http::asForm()->post('https://login.microsoftonline.com/botframework.com/oauth2/v2.0/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $appId,
                'client_secret' => $password,
                'scope' => 'https://api.botframework.com/.default',
            ]);

            if ($response->failed()) {
                Log::error("Failed to retrieve Teams bot access token: " . $response->body());
                return null;
            }

            return $response->json('access_token');
        } catch (\Exception $e) {
            Log::error("Teams bot auth exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Start a 1:1 conversation with a Teams user ID and return conversation ID.
     */
    public function createDirectConversation(
        string $serviceUrl,
        string $tenantId,
        string $userId,
        OrganizationSetting $settings,
        string $accessToken
    ): ?string {
        $appId = $settings->teams_app_id;
        $url = rtrim($serviceUrl, '/') . '/v3/conversations';

        try {
            $response = Http::withToken($accessToken)
                ->post($url, [
                    'bot' => [
                        'id' => "28:{$appId}",
                    ],
                    'members' => [
                        [
                            'id' => $userId,
                        ],
                    ],
                    'channelData' => [
                        'tenant' => [
                            'id' => $tenantId,
                        ],
                    ],
                ]);

            if ($response->failed()) {
                Log::error("Failed to create Teams direct conversation: " . $response->body());
                return null;
            }

            return $response->json('id');
        } catch (\Exception $e) {
            Log::error("Teams conversation creation exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send activity (message/card) to an existing conversation.
     */
    public function sendActivity(
        string $serviceUrl,
        string $conversationId,
        array $activity,
        string $accessToken
    ): bool {
        $url = rtrim($serviceUrl, '/') . "/v3/conversations/{$conversationId}/activities";

        try {
            $response = Http::withToken($accessToken)->post($url, $activity);

            if ($response->failed()) {
                Log::error("Failed to send Teams bot activity: " . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Teams bot sending activity exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dispatch an escalation Adaptive Card to the assigned HR representative.
     */
    public function sendEscalationAlert(Escalation $escalation, User $hrRep): bool
    {
        $settings = $escalation->organization->settings;
        if (!$settings || !$settings->teams_connected) {
            return false;
        }

        // Store routing context or find metadata
        // For testing, we require serviceUrl and tenantId. If they are not in the settings (e.g. initial setup), we retrieve them from previous bot interaction logs.
        // In a real multi-tenant scenario, we save serviceUrl and tenantId when the bot is installed or a user first messages it.
        $tenantId = $settings->teams_tenant_id;
        $serviceUrl = $settings->teams_webhook_url ?? 'https://smba.trafficmanager.net/apis'; // default Teams service URL for fallback

        if ($settings->teams_use_webhook && $settings->teams_webhook_url) {
            // Simple incoming webhook mode (one-way notify to a channel)
            return $this->sendWebhookNotification($settings->teams_webhook_url, $escalation, $hrRep);
        }

        $accessToken = $this->getAccessToken($settings);
        if (!$accessToken) {
            return false;
        }

        $hrTeamsId = $hrRep->teams_user_id;
        if (!$hrTeamsId) {
            Log::warning("Cannot escalate to HR user {$hrRep->id} - Missing teams_user_id");
            return false;
        }

        // Create direct chat with HR rep
        $convId = $this->createDirectConversation($serviceUrl, $tenantId, $hrTeamsId, $settings, $accessToken);
        if (!$convId) {
            return false;
        }

        // Prepare Adaptive Card Activity
        $card = [
            'type' => 'message',
            'attachments' => [
                [
                    'contentType' => 'application/vnd.microsoft.card.adaptive',
                    'content' => [
                        'type' => 'AdaptiveCard',
                        'version' => '1.4',
                        'body' => [
                            [
                                'type' => 'TextBlock',
                                'text' => "🔴 HR Escalation Assigned",
                                'weight' => 'bolder',
                                'size' => 'large',
                                'color' => 'attention',
                            ],
                            [
                                'type' => 'TextBlock',
                                'text' => "An employee has requested HR intervention or asked a sensitive question.",
                                'isSubtle' => true,
                            ],
                            [
                                'type' => 'FactSet',
                                'facts' => [
                                    ['title' => 'Employee:', 'value' => $escalation->user->name],
                                    ['title' => 'Email:', 'value' => $escalation->user->email],
                                    ['title' => 'Category:', 'value' => ucfirst($escalation->category)],
                                    ['title' => 'Escalation ID:', 'value' => substr($escalation->id, 0, 8)],
                                ],
                            ],
                            [
                                'type' => 'TextBlock',
                                'text' => "Original Question:",
                                'weight' => 'bolder',
                            ],
                            [
                                'type' => 'TextBlock',
                                'text' => $escalation->question->question_text,
                                'wrap' => true,
                                'italic' => true,
                                'fontType' => 'monospace',
                            ],
                            [
                                'type' => 'Input.Text',
                                'id' => 'replyText',
                                'placeholder' => 'Type your reply here to send back to the employee...',
                                'isMultiline' => true,
                            ],
                        ],
                        'actions' => [
                            [
                                'type' => 'Action.Submit',
                                'title' => 'Send Reply to Employee',
                                'data' => [
                                    'action' => 'escalation_reply',
                                    'escalation_id' => $escalation->id,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $this->sendActivity($serviceUrl, $convId, $card, $accessToken);
    }

    /**
     * Send a notification to a Teams incoming webhook URL (one-way).
     */
    private function sendWebhookNotification(string $webhookUrl, Escalation $escalation, User $hrRep): bool
    {
        try {
            $payload = [
                'type' => 'message',
                'attachments' => [
                    [
                        'contentType' => 'application/vnd.microsoft.card.adaptive',
                        'content' => [
                            'type' => 'AdaptiveCard',
                            'version' => '1.4',
                            'body' => [
                                [
                                    'type' => 'TextBlock',
                                    'text' => "🔴 HR Escalation Created",
                                    'weight' => 'bolder',
                                    'size' => 'large',
                                    'color' => 'attention',
                                ],
                                [
                                    'type' => 'FactSet',
                                    'facts' => [
                                        ['title' => 'Employee:', 'value' => $escalation->user->name],
                                        ['title' => 'Question:', 'value' => $escalation->question->question_text],
                                        ['title' => 'Assigned Representative:', 'value' => "{$hrRep->name} (Priority: {$hrRep->escalation_priority})"],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::post($webhookUrl, $payload);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Teams Webhook send exception: " . $e->getMessage());
            return false;
        }
    }
}
