<?php

namespace App\Console\Commands;

use App\Models\OrganizationSetting;
use App\Http\Controllers\Api\WidgetChatController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class FetchInboundEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Polls tenant IMAP mailboxes for inbound customer questions, runs policy queries, and auto-replies.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("=== Starting Aprilo AI Inbound Mailbox Polling ===");

        // Fetch all organizations with active email integration configured
        $activeSettings = OrganizationSetting::where('connect_mail', true)
            ->where('mail_imap_connected', true)
            ->get();

        if ($activeSettings->isEmpty()) {
            $this->warn("No active IMAP mail connections configured for polling.");
            return 0;
        }

        foreach ($activeSettings as $settings) {
            $org = $settings->organization;
            if (!$org) {
                continue;
            }

            $this->info("Scanning mailbox configurations for organization: {$org->name}");
            $this->line(" - IMAP Host: {$settings->mail_imap_host}:{$settings->mail_imap_port}");
            $this->line(" - Username: {$settings->mail_imap_username}");

            // Standard IMAP open check
            if (!function_exists('imap_open')) {
                $this->warn(" [Notice] PHP IMAP extension (imap_open) is disabled on this environment.");
                $this->info(" [Sandbox Mode] Running simulated inbound email loop for: {$settings->mail_imap_username}");

                // Simulate processing of a new inbound email query
                $mockEmail = [
                    'sender' => 'employee.test@company.com',
                    'subject' => 'Remote Work Guidelines query',
                    'body' => 'Hello HR, what is the policy regarding working from home or hybrid options?',
                ];

                $this->info("   -> Simulating incoming email from: {$mockEmail['sender']}");
                $this->info("   -> Subject: {$mockEmail['subject']}");
                $this->info("   -> Query Text: {$mockEmail['body']}");

                // Dispatch to WidgetChatController using a sub-request
                try {
                    $controller = app(WidgetChatController::class);
                    $request = Request::create('/api/widget/inbound-email', 'POST', [
                        'sender' => $mockEmail['sender'],
                        'subject' => $mockEmail['subject'],
                        'body' => $mockEmail['body'],
                        'subdomain' => strtolower(str_replace(' ', '-', $org->name)),
                    ]);

                    $response = $controller->inboundEmail($request);
                    $data = json_decode($response->getContent(), true);

                    if ($response->isSuccessful()) {
                        $this->info("   🟢 Auto-Reply Email Dispatched Successfully. Result Status: " . ($data['answer_status'] ?? 'unknown'));
                    } else {
                        $this->error("   🔴 Failed to process simulated inbound email: " . ($data['message'] ?? 'Unknown error'));
                    }
                } catch (\Exception $e) {
                    $this->error("   🔴 Error dispatching sub-request: " . $e->getMessage());
                }

                $this->line("----------------------------------------------------------------");
                continue;
            }

            // In production environments where imap_open is enabled:
            try {
                $sslString = $settings->mail_imap_encryption === 'ssl' ? '/ssl/novalidate-cert' : '/tls/novalidate-cert';
                $mboxString = "{" . $settings->mail_imap_host . ":" . $settings->mail_imap_port . "/imap" . $sslString . "}INBOX";
                
                $inbox = @imap_open($mboxString, $settings->mail_imap_username, $settings->mail_imap_password);
                
                if (!$inbox) {
                    $this->error(" [Connection Error] Failed to connect to IMAP server: " . imap_last_error());
                    continue;
                }

                $emails = imap_search($inbox, 'UNSEEN');

                if ($emails) {
                    $this->info(" Found " . count($emails) . " unread inbound emails.");
                    foreach ($emails as $mailId) {
                        $overview = imap_headerinfo($inbox, $mailId);
                        $sender = $overview->from[0]->mailbox . "@" . $overview->from[0]->host;
                        $subject = $overview->subject ?? 'No Subject';
                        $body = imap_fetchbody($inbox, $mailId, 1);

                        $this->info(" - Processing mail ID {$mailId} from {$sender}: {$subject}");

                        $controller = app(WidgetChatController::class);
                        $request = Request::create('/api/widget/inbound-email', 'POST', [
                            'sender' => $sender,
                            'subject' => $subject,
                            'body' => $body,
                            'subdomain' => strtolower(str_replace(' ', '-', $org->name)),
                        ]);

                        $controller->inboundEmail($request);
                        
                        // Mark email as read/seen
                        imap_setflag_full($inbox, $mailId, "\\Seen");
                    }
                } else {
                    $this->line(" No new unread messages found.");
                }

                imap_close($inbox);
            } catch (\Exception $e) {
                $this->error(" [Processing Failure] Exception encountered: " . $e->getMessage());
            }
        }

        $this->info("=== Finished Inbound Mailbox Polling ===");
        return 0;
    }
}
