<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_settings', function (Blueprint $table): void {
            if (!Schema::hasColumn('organization_settings', 'enable_lead_capture')) {
                $table->boolean('enable_lead_capture')->default(false)->after('welcome_message');
            }
            if (!Schema::hasColumn('organization_settings', 'lead_capture_prompt')) {
                $table->text('lead_capture_prompt')->nullable()->after('enable_lead_capture');
            }
            if (!Schema::hasColumn('organization_settings', 'lead_capture_fields')) {
                $table->string('lead_capture_fields', 255)->default('name,email,phone')->after('lead_capture_prompt');
            }
            if (!Schema::hasColumn('organization_settings', 'lead_notification_email')) {
                $table->string('lead_notification_email', 255)->nullable()->after('lead_capture_fields');
            }
        });

        Schema::table('widget_conversations', function (Blueprint $table): void {
            if (!Schema::hasColumn('widget_conversations', 'customer_name')) {
                $table->string('customer_name', 120)->nullable()->after('session_id');
            }
            if (!Schema::hasColumn('widget_conversations', 'customer_email')) {
                $table->string('customer_email', 120)->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('widget_conversations', 'customer_phone')) {
                $table->string('customer_phone', 60)->nullable()->after('customer_email');
            }
            if (!Schema::hasColumn('widget_conversations', 'lead_captured')) {
                $table->boolean('lead_captured')->default(false)->after('customer_phone');
            }
            if (!Schema::hasColumn('widget_conversations', 'lead_captured_at')) {
                $table->timestamp('lead_captured_at')->nullable()->after('lead_captured');
            }
        });
    }

    public function down(): void
    {
        Schema::table('widget_conversations', function (Blueprint $table): void {
            $table->dropColumn(['customer_name', 'customer_email', 'customer_phone', 'lead_captured', 'lead_captured_at']);
        });

        Schema::table('organization_settings', function (Blueprint $table): void {
            $table->dropColumn(['enable_lead_capture', 'lead_capture_prompt', 'lead_capture_fields', 'lead_notification_email']);
        });
    }
};
