<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create organization_chat_usages table for daily queries & token metrics
        if (!Schema::hasTable('organization_chat_usages')) {
            Schema::create('organization_chat_usages', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
                $table->date('usage_date')->index();
                $table->unsignedInteger('queries_count')->default(0);
                $table->unsignedBigInteger('prompt_tokens')->default(0);
                $table->unsignedBigInteger('completion_tokens')->default(0);
                $table->unsignedBigInteger('total_tokens')->default(0);
                $table->decimal('cost_estimate', 10, 4)->default(0.0000);
                $table->timestamps();

                $table->unique(['organization_id', 'usage_date'], 'org_usage_date_unique');
            });
        }

        // 2. Add is_widget_enabled to organization_settings
        Schema::table('organization_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('organization_settings', 'is_widget_enabled')) {
                $table->boolean('is_widget_enabled')->default(true)->after('assistant_status');
            }
            if (!Schema::hasColumn('organization_settings', 'total_tokens_used')) {
                $table->unsignedBigInteger('total_tokens_used')->default(0)->after('usage_queries_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_chat_usages');

        Schema::table('organization_settings', function (Blueprint $table) {
            if (Schema::hasColumn('organization_settings', 'is_widget_enabled')) {
                $table->dropColumn('is_widget_enabled');
            }
            if (Schema::hasColumn('organization_settings', 'total_tokens_used')) {
                $table->dropColumn('total_tokens_used');
            }
        });
    }
};
