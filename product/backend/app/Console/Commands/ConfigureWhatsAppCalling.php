<?php

namespace App\Console\Commands;

use App\Services\WhatsAppCloudApi;
use Illuminate\Console\Command;
use Throwable;

class ConfigureWhatsAppCalling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:configure-calling
                            {--enable : Enable calling on the configured WhatsApp phone number}
                            {--icon=DEFAULT : Call icon visibility (DEFAULT, HIDDEN)}
                            {--callback=ENABLED : Callback permission status (ENABLED, DISABLED)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View or configure WhatsApp Cloud API calling settings for the phone number';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppCloudApi $api): int
    {
        $phoneNumberId = config('services.whatsapp.phone_number_id');

        if (! $phoneNumberId) {
            $this->error('WHATSAPP_PHONE_NUMBER_ID is not configured.');

            return self::FAILURE;
        }

        $this->info("Target Phone Number ID: {$phoneNumberId}");

        if ($this->option('enable')) {
            $this->info('Enabling calling configuration via Meta Settings API...');

            try {
                $icon = (string) $this->option('icon');
                $callback = (string) $this->option('callback');

                $result = $api->enableCalling($icon, $callback);
                $this->info('Meta response: ' . json_encode($result));
                $this->info('Calling has been successfully enabled.');
            } catch (Throwable $e) {
                $this->error('Failed to enable calling: ' . $e->getMessage());

                return self::FAILURE;
            }
        }

        try {
            $settings = $api->getSettings();
            $calling = $settings['calling'] ?? [];

            $this->table(
                ['Setting', 'Value'],
                [
                    ['Calling Status', $calling['status'] ?? 'UNKNOWN'],
                    ['Call Icon Visibility', $calling['call_icon_visibility'] ?? 'UNKNOWN'],
                    ['Callback Permission', $calling['callback_permission_status'] ?? 'UNKNOWN'],
                ]
            );
        } catch (Throwable $e) {
            $this->warn('Could not fetch current settings: ' . $e->getMessage());
        }

        return self::SUCCESS;
    }
}
