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
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->boolean('whatsapp_use_sandbox')->default(true);
            $table->string('whatsapp_sender_phone')->nullable();
            $table->string('whatsapp_message_template')->nullable();
            $table->string('whatsapp_brand_approval_status')->default('pending');
            
            $table->boolean('teams_use_webhook')->default(true);
            $table->string('teams_app_password')->nullable();
            
            $table->string('mail_smtp_encryption')->default('tls');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_use_sandbox',
                'whatsapp_sender_phone',
                'whatsapp_message_template',
                'whatsapp_brand_approval_status',
                
                'teams_use_webhook',
                'teams_app_password',
                
                'mail_smtp_encryption',
            ]);
        });
    }
};
