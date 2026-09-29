<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_settings', function (Blueprint $table): void {
            $table->string('public_widget_key', 64)->nullable()->unique();
        });

        Schema::create('widget_conversations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->uuid('customer_id')->nullable();
            $table->string('session_id', 120)->index();
            $table->string('domain', 255)->nullable();
            $table->text('page_url')->nullable();
            $table->string('page_title')->nullable();
            $table->string('mode')->default('ai');
            $table->string('status')->default('open')->index();
            $table->timestamps();

            $table->index(['organization_id', 'created_at']);
        });

        Schema::create('widget_conversation_messages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('widget_conversations')->cascadeOnDelete();
            $table->string('sender_type', 20);
            $table->string('message_type', 30)->default('text');
            $table->text('content');
            $table->string('sender_name')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_conversation_messages');
        Schema::dropIfExists('widget_conversations');
        Schema::table('organization_settings', function (Blueprint $table): void {
            $table->dropUnique(['public_widget_key']);
            $table->dropColumn('public_widget_key');
        });
    }
};