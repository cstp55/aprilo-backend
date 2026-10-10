<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Support AI Drafts
        if (! Schema::hasTable('support_ai_drafts')) {
            Schema::create('support_ai_drafts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignUuid('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
                $table->foreignUuid('trigger_comment_id')->nullable()->constrained('support_ticket_comments')->nullOnDelete();
                $table->text('trigger_message_preview')->nullable();
                $table->longText('proposed_reply');
                $table->longText('edited_reply')->nullable();
                $table->string('status')->default('pending_review')->index(); // pending_review, approved, rejected, superseded, send_failed
                $table->boolean('is_stale')->default(false)->index();
                $table->string('model')->nullable();
                $table->decimal('confidence_score', 4, 2)->nullable();
                $table->json('generation_metadata')->nullable();
                $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamp('dispatched_at')->nullable();
                $table->foreignUuid('dispatched_comment_id')->nullable()->constrained('support_ticket_comments')->nullOnDelete();
                $table->string('dispatch_channel')->nullable();
                $table->string('dispatch_status')->nullable(); // delivered, failed
                $table->text('dispatch_error')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->index(['ticket_id', 'status']);
                $table->index(['organization_id', 'status']);
            });
        }

        // 2. Draft Sources & Citations
        if (! Schema::hasTable('support_ai_draft_sources')) {
            Schema::create('support_ai_draft_sources', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('draft_id')->constrained('support_ai_drafts')->cascadeOnDelete();
                $table->foreignUuid('source_id')->nullable()->constrained('knowledge_sources')->nullOnDelete();
                $table->foreignUuid('chunk_id')->nullable()->constrained('knowledge_chunks')->nullOnDelete();
                $table->string('title');
                $table->text('citation_excerpt');
                $table->decimal('relevance_score', 5, 4)->nullable();
                $table->timestamps();

                $table->index(['draft_id']);
            });
        }

        // 3. Draft Feedback
        if (! Schema::hasTable('support_ai_feedback')) {
            Schema::create('support_ai_feedback', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignUuid('draft_id')->constrained('support_ai_drafts')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->tinyInteger('rating')->default(1); // 1 = helpful, -1 = unhelpful
                $table->text('feedback_text')->nullable();
                $table->boolean('was_edited')->default(false);
                $table->timestamps();

                $table->index(['organization_id', 'draft_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ai_feedback');
        Schema::dropIfExists('support_ai_draft_sources');
        Schema::dropIfExists('support_ai_drafts');
    }
};
