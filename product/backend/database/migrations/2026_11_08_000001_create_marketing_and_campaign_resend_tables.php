<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Marketing Leads & Customer Conversion Tracking
        if (! Schema::hasTable('marketing_leads')) {
            Schema::create('marketing_leads', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
                $table->string('name', 120);
                $table->string('phone', 30)->nullable()->index();
                $table->string('email', 150)->nullable()->index();
                $table->string('company_name', 150)->nullable();
                $table->string('status', 30)->default('lead')->index(); // lead (unconverted), converted (paying customer), lost
                $table->string('target_product', 100)->nullable(); // e.g. Aprilo POS, Aprilo Chatbot, TradeFlow
                $table->string('preferred_channel', 20)->default('whatsapp'); // whatsapp, email, both
                $table->timestamp('last_contacted_at')->nullable();
                $table->string('last_campaign_name', 150)->nullable();
                $table->text('conversion_notes')->nullable();
                $table->json('tags')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['status', 'target_product']);
            });
        }

        // 2. Marketing Cron Execution Logs (Monitoring automated weekly jobs)
        if (! Schema::hasTable('marketing_cron_logs')) {
            Schema::create('marketing_cron_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('command_name', 100)->default('aprilo:weekly-marketing-campaign');
                $table->string('status', 30)->default('success')->index(); // success, failed, skipped, running
                $table->string('target_product', 100)->nullable();
                $table->string('segment_targeted', 50)->nullable(); // unconverted_leads, converted_customers, all
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

        // 3. Extend whatsapp_campaigns with resend and AI product metadata
        if (Schema::hasTable('whatsapp_campaigns')) {
            Schema::table('whatsapp_campaigns', function (Blueprint $table) {
                if (! Schema::hasColumn('whatsapp_campaigns', 'target_product')) {
                    $table->string('target_product', 100)->nullable()->after('name');
                }
                if (! Schema::hasColumn('whatsapp_campaigns', 'audience_segment')) {
                    $table->string('audience_segment', 50)->default('all')->after('target_product'); // all, leads, converted, manual
                }
                if (! Schema::hasColumn('whatsapp_campaigns', 'resend_count')) {
                    $table->unsignedInteger('resend_count')->default(0)->after('status');
                }
                if (! Schema::hasColumn('whatsapp_campaigns', 'last_resent_at')) {
                    $table->timestamp('last_resent_at')->nullable()->after('resend_count');
                }
                if (! Schema::hasColumn('whatsapp_campaigns', 'parent_campaign_id')) {
                    $table->foreignUuid('parent_campaign_id')->nullable()->after('last_resent_at')->constrained('whatsapp_campaigns')->nullOnDelete();
                }
                if (! Schema::hasColumn('whatsapp_campaigns', 'ai_generated')) {
                    $table->boolean('ai_generated')->default(false)->after('parent_campaign_id');
                }
                if (! Schema::hasColumn('whatsapp_campaigns', 'ai_prompt_notes')) {
                    $table->text('ai_prompt_notes')->nullable()->after('ai_generated');
                }
                if (! Schema::hasColumn('whatsapp_campaigns', 'is_automated')) {
                    $table->boolean('is_automated')->default(false)->after('ai_prompt_notes');
                }
            });
        }

        // 4. Extend whatsapp_campaign_recipients with lead reference & resend attempts
        if (Schema::hasTable('whatsapp_campaign_recipients')) {
            Schema::table('whatsapp_campaign_recipients', function (Blueprint $table) {
                if (! Schema::hasColumn('whatsapp_campaign_recipients', 'lead_id')) {
                    $table->foreignUuid('lead_id')->nullable()->after('user_id')->constrained('marketing_leads')->nullOnDelete();
                }
                if (! Schema::hasColumn('whatsapp_campaign_recipients', 'resend_attempt')) {
                    $table->unsignedInteger('resend_attempt')->default(0)->after('status');
                }
                if (! Schema::hasColumn('whatsapp_campaign_recipients', 'last_attempted_at')) {
                    $table->timestamp('last_attempted_at')->nullable()->after('resend_attempt');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('whatsapp_campaign_recipients')) {
            Schema::table('whatsapp_campaign_recipients', function (Blueprint $table) {
                $columns = ['lead_id', 'resend_attempt', 'last_attempted_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('whatsapp_campaign_recipients', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('whatsapp_campaigns')) {
            Schema::table('whatsapp_campaigns', function (Blueprint $table) {
                $columns = ['target_product', 'audience_segment', 'resend_count', 'last_resent_at', 'parent_campaign_id', 'ai_generated', 'ai_prompt_notes', 'is_automated'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('whatsapp_campaigns', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('marketing_cron_logs');
        Schema::dropIfExists('marketing_leads');
    }
};

