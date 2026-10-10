<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Inbound Channel Events & Deduplication
        if (! Schema::hasTable('support_channel_events')) {
            Schema::create('support_channel_events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->string('channel'); // website, whatsapp, email
                $table->string('event_id'); // External message or webhook ID for deduplication
                $table->string('event_type'); // message_received, delivery_status, etc.
                $table->json('payload')->nullable();
                $table->foreignUuid('ticket_id')->nullable()->constrained('support_tickets')->nullOnDelete();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->unique(['organization_id', 'channel', 'event_id'], 'support_channel_evt_unique');
                $table->index(['organization_id', 'created_at']);
            });
        }

        // 2. Outbox & Outbound Delivery Tracking
        if (! Schema::hasTable('support_outbox_events')) {
            Schema::create('support_outbox_events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignUuid('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
                $table->foreignUuid('comment_id')->constrained('support_ticket_comments')->cascadeOnDelete();
                $table->string('channel'); // website, whatsapp, email
                $table->string('recipient');
                $table->json('payload')->nullable();
                $table->string('status')->default('pending')->index(); // pending, sent, failed
                $table->unsignedSmallInteger('retry_count')->default(0);
                $table->string('external_message_id')->nullable()->index();
                $table->text('error_message')->nullable();
                $table->timestamp('dispatched_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'retry_count']);
                $table->index(['organization_id', 'ticket_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_outbox_events');
        Schema::dropIfExists('support_channel_events');
    }
};
