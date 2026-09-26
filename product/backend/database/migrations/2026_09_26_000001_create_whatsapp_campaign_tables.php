<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 120);
            $table->string('message_type', 20);
            $table->string('template_name')->nullable();
            $table->string('template_language', 20)->nullable();
            $table->json('template_parameters')->nullable();
            $table->text('message_text')->nullable();
            $table->timestamp('consent_confirmed_at');
            $table->string('status', 20)->default('queued')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('whatsapp_campaign_recipients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained('whatsapp_campaigns')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone', 20)->index();
            $table->string('status', 20)->default('pending')->index();
            $table->string('whatsapp_message_id')->nullable();
            $table->json('response_json')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_campaign_recipients');
        Schema::dropIfExists('whatsapp_campaigns');
    }
};