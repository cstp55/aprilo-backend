<?php

namespace App\Console\Commands;

use App\Jobs\SendWhatsAppCampaignRecipient;
use App\Models\MarketingCronLog;
use App\Models\MarketingLead;
use App\Models\Organization;
use App\Models\User;
use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignRecipient;
use App\Services\AI\CampaignAiService;
use App\Services\WhatsAppCloudApi;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WeeklyMarketingCampaignCommand extends Command
{
    protected $signature = 'aprilo:weekly-marketing-campaign 
                            {--force : Force execution immediately} 
                            {--segment=auto : Segment (auto, leads, converted, all)} 
                            {--product= : Override target product (e.g. Aprilo POS, Aprilo Chatbot)}';

    protected $description = 'Automated weekly AI marketing campaign: asks AI for current product & audience strategy, crafts tailored copy, and dispatches to leads/customers';

    public function handle(CampaignAiService $aiService, WhatsAppCloudApi $whatsApp): int
    {
        $startTime = microtime(true);
        $this->info("=================================================");
        $this->info("Starting Aprilo Automated Weekly Marketing Campaign");
        $this->info("=================================================");

        // 1. Ensure runtime database schema exists
        $this->ensureSchema();

        // 2. Ensure marketing leads exist (auto-seed from Users/Organizations if empty)
        $this->syncDefaultLeadsIfEmpty();

        $unconvertedCount = MarketingLead::unconverted()->whereNotNull('phone')->count();
        $convertedCount = MarketingLead::converted()->whereNotNull('phone')->count();

        $this->line("Active Unconverted Leads: {$unconvertedCount}");
        $this->line("Active Converted Customers: {$convertedCount}");

        // 2. Query AI for today's optimal campaign strategy & product focus
        $this->info("Consulting AI Strategist for today's product focus and copy...");
        $strategy = $aiService->determineWeeklyAutomationStrategy($unconvertedCount, $convertedCount);

        // Apply CLI overrides if provided
        if ($prodOverride = $this->option('product')) {
            $strategy['product_name'] = $prodOverride;
            $strategy['copy'] = $aiService->generateCampaignCopy([
                'product_name' => $prodOverride,
                'target_audience' => $strategy['target_audience'],
                'campaign_goal' => $strategy['campaign_goal'],
                'channel' => 'whatsapp',
            ]);
        }

        if ($segOverride = $this->option('segment')) {
            if (in_array($segOverride, ['leads', 'converted', 'all'], true)) {
                $strategy['target_audience'] = $segOverride === 'leads' ? 'unconverted_leads' : ($segOverride === 'converted' ? 'converted_customers' : 'all');
            }
        }

        $this->info("AI Strategy: " . $strategy['strategy_summary']);
        $this->line("Target Product: " . $strategy['product_name']);
        $this->line("Audience Segment: " . $strategy['target_audience']);

        // 3. Resolve Recipients based on audience segment
        $leadsQuery = MarketingLead::query()->whereNotNull('phone')->where('phone', '!=', '');

        if ($strategy['target_audience'] === 'unconverted_leads') {
            $leadsQuery->unconverted();
        } elseif ($strategy['target_audience'] === 'converted_customers') {
            $leadsQuery->converted();
        }

        $targetLeads = $leadsQuery->get();

        if ($targetLeads->isEmpty()) {
            $this->warn("No eligible phone recipients found for segment '{$strategy['target_audience']}'. Skipping dispatch.");
            
            MarketingCronLog::create([
                'command_name' => 'aprilo:weekly-marketing-campaign',
                'status' => 'skipped',
                'target_product' => $strategy['product_name'],
                'segment_targeted' => $strategy['target_audience'],
                'recipients_count' => 0,
                'ai_strategy_summary' => $strategy['strategy_summary'],
                'ai_generated_copy' => $strategy['copy']['whatsapp_message'] ?? null,
                'error_message' => 'No eligible phone recipients found for targeted segment.',
                'execution_time_seconds' => round(microtime(true) - $startTime, 2),
                'executed_at' => now(),
            ]);

            return Command::SUCCESS;
        }

        // 4. Create Campaign in Database
        $superAdminUser = User::where('role', 'super_admin')->first() ?: User::first();
        $messageCopy = $strategy['copy']['whatsapp_message'] ?? 'Check out our latest software updates at https://apriloinfotech.com';

        $campaign = WhatsAppCampaign::create([
            'created_by' => $superAdminUser?->id,
            'name' => $strategy['campaign_name'],
            'target_product' => $strategy['product_name'],
            'audience_segment' => $strategy['target_audience'],
            'message_type' => 'text',
            'message_text' => $messageCopy,
            'consent_confirmed_at' => now(),
            'status' => 'queued',
            'ai_generated' => true,
            'ai_prompt_notes' => $strategy['strategy_summary'],
            'is_automated' => true,
        ]);

        $recipientRecords = [];
        foreach ($targetLeads as $lead) {
            $recipientRecords[] = [
                'campaign_id' => $campaign->id,
                'lead_id' => $lead->id,
                'recipient_name' => $lead->name,
                'recipient_phone' => $this->normalizePhone($lead->phone),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Update lead last contacted info
            $lead->update([
                'last_contacted_at' => now(),
                'last_campaign_name' => $campaign->name,
            ]);
        }

        $campaign->recipients()->createMany($recipientRecords);

        // 5. Dispatch Recipient sends
        $this->info("Dispatching campaign to {$targetLeads->count()} recipient(s)...");

        $acceptedCount = 0;
        $failedCount = 0;

        foreach ($campaign->recipients as $recipient) {
            try {
                SendWhatsAppCampaignRecipient::dispatch($recipient);
                $acceptedCount++;
            } catch (\Throwable $e) {
                $failedCount++;
                Log::warning("Weekly Campaign Dispatch Error: " . $e->getMessage());
            }
        }

        $elapsed = round(microtime(true) - $startTime, 2);

        // 6. Log Cron Execution in Database
        MarketingCronLog::create([
            'command_name' => 'aprilo:weekly-marketing-campaign',
            'status' => 'success',
            'target_product' => $strategy['product_name'],
            'segment_targeted' => $strategy['target_audience'],
            'recipients_count' => $targetLeads->count(),
            'accepted_count' => $acceptedCount,
            'failed_count' => $failedCount,
            'campaign_id' => $campaign->id,
            'ai_strategy_summary' => $strategy['strategy_summary'],
            'ai_generated_copy' => $messageCopy,
            'execution_time_seconds' => $elapsed,
            'executed_at' => now(),
        ]);

        $this->info("Automated Weekly Campaign dispatched successfully in {$elapsed}s.");
        $this->info("Campaign ID: {$campaign->id} ({$campaign->name})");

        return Command::SUCCESS;
    }

    /**
     * Ensure runtime database schema exists for marketing tables.
     */
    protected function ensureSchema(): void
    {
        try {
            if (! Schema::hasTable('marketing_leads')) {
                Schema::create('marketing_leads', function (Blueprint $table) {
                    $table->uuid('id')->primary();
                    $table->foreignUuid('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
                    $table->string('name', 120);
                    $table->string('phone', 30)->nullable()->index();
                    $table->string('email', 150)->nullable()->index();
                    $table->string('company_name', 150)->nullable();
                    $table->string('status', 30)->default('lead')->index();
                    $table->string('target_product', 100)->nullable();
                    $table->string('preferred_channel', 20)->default('whatsapp');
                    $table->timestamp('last_contacted_at')->nullable();
                    $table->string('last_campaign_name', 150)->nullable();
                    $table->text('conversion_notes')->nullable();
                    $table->json('tags')->nullable();
                    $table->json('metadata')->nullable();
                    $table->timestamps();
                    $table->index(['status', 'target_product']);
                });
            }

            if (! Schema::hasTable('marketing_cron_logs')) {
                Schema::create('marketing_cron_logs', function (Blueprint $table) {
                    $table->uuid('id')->primary();
                    $table->string('command_name', 100)->default('aprilo:weekly-marketing-campaign');
                    $table->string('status', 30)->default('success')->index();
                    $table->string('target_product', 100)->nullable();
                    $table->string('segment_targeted', 50)->nullable();
                    $table->unsignedInteger('recipients_count')->default(0);
                    $table->unsignedInteger('accepted_count')->default(0);
                    $table->unsignedInteger('failed_count')->default(0);
                    $table->foreignUuid('campaign_id')->nullable()->constrained('whatsapp_campaigns')->nullOnDelete();
                    $table->text('ai_strategy_summary')->nullable();
                    $table->longText('ai_generated_copy')->nullable();
                    $table->text('error_message')->nullable();
                    $table->decimal('execution_time_seconds', 8, 2)->default(0.00);
                    $table->timestamp('executed_at')->nullable();
                    $table->timestamps();
                });
            }

            if (Schema::hasTable('whatsapp_campaigns')) {
                Schema::table('whatsapp_campaigns', function (Blueprint $table) {
                    if (! Schema::hasColumn('whatsapp_campaigns', 'target_product')) {
                        $table->string('target_product', 100)->nullable();
                    }
                    if (! Schema::hasColumn('whatsapp_campaigns', 'audience_segment')) {
                        $table->string('audience_segment', 50)->default('all');
                    }
                    if (! Schema::hasColumn('whatsapp_campaigns', 'resend_count')) {
                        $table->unsignedInteger('resend_count')->default(0);
                    }
                    if (! Schema::hasColumn('whatsapp_campaigns', 'last_resent_at')) {
                        $table->timestamp('last_resent_at')->nullable();
                    }
                    if (! Schema::hasColumn('whatsapp_campaigns', 'parent_campaign_id')) {
                        $table->foreignUuid('parent_campaign_id')->nullable()->constrained('whatsapp_campaigns')->nullOnDelete();
                    }
                    if (! Schema::hasColumn('whatsapp_campaigns', 'ai_generated')) {
                        $table->boolean('ai_generated')->default(false);
                    }
                    if (! Schema::hasColumn('whatsapp_campaigns', 'ai_prompt_notes')) {
                        $table->text('ai_prompt_notes')->nullable();
                    }
                    if (! Schema::hasColumn('whatsapp_campaigns', 'is_automated')) {
                        $table->boolean('is_automated')->default(false);
                    }
                });
            }

            if (Schema::hasTable('whatsapp_campaign_recipients')) {
                Schema::table('whatsapp_campaign_recipients', function (Blueprint $table) {
                    if (! Schema::hasColumn('whatsapp_campaign_recipients', 'lead_id')) {
                        $table->foreignUuid('lead_id')->nullable()->constrained('marketing_leads')->nullOnDelete();
                    }
                    if (! Schema::hasColumn('whatsapp_campaign_recipients', 'resend_attempt')) {
                        $table->unsignedInteger('resend_attempt')->default(0);
                    }
                    if (! Schema::hasColumn('whatsapp_campaign_recipients', 'last_attempted_at')) {
                        $table->timestamp('last_attempted_at')->nullable();
                    }
                });
            }
        } catch (\Throwable $e) {
            Log::info('Command schema ensure note: ' . $e->getMessage());
        }
    }

    /**
     * Normalize phone numbers to E.164 without plus.
     */
    protected function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '');
        return preg_match('/^[1-9][0-9]{7,14}$/', $digits) ? $digits : null;
    }

    /**
     * Seed initial marketing leads from existing active users & organizations if empty.
     */
    protected function syncDefaultLeadsIfEmpty(): void
    {
        if (MarketingLead::count() > 0) {
            return;
        }

        // Import from active users with phone numbers
        $users = User::whereNotNull('phone')->where('phone', '!=', '')->take(50)->get();
        foreach ($users as $u) {
            MarketingLead::firstOrCreate(
                ['phone' => $this->normalizePhone($u->phone)],
                [
                    'name' => $u->name,
                    'email' => $u->email,
                    'company_name' => $u->organization?->name ?? 'Enterprise User',
                    'organization_id' => $u->organization_id,
                    'status' => $u->organization ? 'converted' : 'lead',
                    'target_product' => 'Aprilo POS',
                    'conversion_notes' => 'Auto-synced from platform accounts',
                ]
            );
        }

        // Also add initial demo prospects if count is still zero
        if (MarketingLead::count() === 0) {
            MarketingLead::create([
                'name' => 'Amit Verma',
                'phone' => '919876543210',
                'email' => 'amit@vermatech.in',
                'company_name' => 'Verma Retail Solutions',
                'status' => 'lead',
                'target_product' => 'Aprilo POS',
                'conversion_notes' => 'Inquired about barcode billing & POS inventory sync',
            ]);
            MarketingLead::create([
                'name' => 'Pooja Sharma',
                'phone' => '919812345678',
                'email' => 'pooja@sharmaenterprises.com',
                'company_name' => 'Sharma Enterprises',
                'status' => 'converted',
                'target_product' => 'Aprilo Chatbot',
                'conversion_notes' => 'Active subscriber of Aprilo Chatbot; potential lead for TradeFlow',
            ]);
            MarketingLead::create([
                'name' => 'Suresh Patel',
                'phone' => '919898989898',
                'email' => 'suresh@pateltraders.com',
                'company_name' => 'Patel Wholesale Hub',
                'status' => 'lead',
                'target_product' => 'TradeFlow',
                'conversion_notes' => 'Requested demo for B2B inventory dispatch',
            ]);
        }
    }
}
