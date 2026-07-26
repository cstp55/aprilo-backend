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
        // Add plan to organizations
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('plan')->default('basic')->index();
        });

        // Add assistant_name and connection fields to organization_settings
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->string('assistant_name')->default('Aprilo Bot');

            // Teams config
            $table->text('teams_webhook_url')->nullable();
            $table->string('teams_tenant_id')->nullable();
            $table->string('teams_app_id')->nullable();
            $table->boolean('teams_connected')->default(false);

            // Skype config
            $table->string('skype_bot_id')->nullable();
            $table->text('skype_client_secret')->nullable();
            $table->boolean('skype_connected')->default(false);

            // WhatsApp config
            $table->string('whatsapp_phone_number_id')->nullable();
            $table->string('whatsapp_business_account_id')->nullable();
            $table->text('whatsapp_access_token')->nullable();
            $table->boolean('whatsapp_connected')->default(false);

            // Mail config
            $table->string('mail_smtp_host')->nullable();
            $table->integer('mail_smtp_port')->nullable();
            $table->string('mail_smtp_username')->nullable();
            $table->text('mail_smtp_password')->nullable();
            $table->boolean('mail_connected')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->dropColumn([
                'assistant_name',
                'teams_webhook_url',
                'teams_tenant_id',
                'teams_app_id',
                'teams_connected',
                'skype_bot_id',
                'skype_client_secret',
                'skype_connected',
                'whatsapp_phone_number_id',
                'whatsapp_business_account_id',
                'whatsapp_access_token',
                'whatsapp_connected',
                'mail_smtp_host',
                'mail_smtp_port',
                'mail_smtp_username',
                'mail_smtp_password',
                'mail_connected',
            ]);
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('plan');
        });
    }
};
