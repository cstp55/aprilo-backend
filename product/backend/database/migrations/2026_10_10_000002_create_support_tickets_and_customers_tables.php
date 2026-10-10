<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Support Customers
        if (! Schema::hasTable('support_customers')) {
            Schema::create('support_customers', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('company')->nullable();
                $table->string('external_id')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['organization_id', 'email']);
                $table->index(['organization_id', 'phone']);
            });
        }

        // 2. Channel Identities
        if (! Schema::hasTable('support_channel_identities')) {
            Schema::create('support_channel_identities', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignUuid('customer_id')->constrained('support_customers')->cascadeOnDelete();
                $table->string('channel'); // website, whatsapp, email, etc.
                $table->string('channel_identifier'); // phone number, email address, web session/visitor ID
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['organization_id', 'channel', 'channel_identifier'], 'support_channel_ident_unique');
            });
        }

        // 3. Ticket Categories
        if (! Schema::hasTable('support_ticket_categories')) {
            Schema::create('support_ticket_categories', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['organization_id', 'slug']);
            });
        }

        // 4. Support Tickets
        if (! Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->string('ticket_number');
                $table->unsignedBigInteger('ticket_sequence')->default(1);
                $table->foreignUuid('customer_id')->nullable()->constrained('support_customers')->nullOnDelete();
                $table->string('conversation_id')->nullable()->index(); // Firebase conversation link
                $table->string('subject');
                $table->longText('description');
                $table->string('channel')->default('portal')->index(); // website, whatsapp, email, portal
                $table->foreignUuid('category_id')->nullable()->constrained('support_ticket_categories')->nullOnDelete();
                $table->string('department')->nullable()->index();
                $table->string('priority')->default('normal')->index(); // low, normal, high, urgent
                $table->string('status')->default('new')->index(); // new, open, in_progress, pending_customer, pending_internal, resolved, closed
                $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('first_response_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamp('reopened_at')->nullable();
                $table->timestamp('sla_due_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['organization_id', 'ticket_number']);
                $table->index(['organization_id', 'status', 'priority']);
                $table->index(['organization_id', 'assigned_to']);
            });
        }

        // 5. Support Ticket Comments
        if (! Schema::hasTable('support_ticket_comments')) {
            Schema::create('support_ticket_comments', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignUuid('customer_id')->nullable()->constrained('support_customers')->nullOnDelete();
                $table->string('author_type')->default('agent'); // agent, customer, system, ai
                $table->string('author_name')->nullable();
                $table->longText('body');
                $table->boolean('is_internal')->default(false)->index(); // true = internal note, false = customer visible
                $table->string('channel')->nullable();
                $table->string('external_message_id')->nullable()->index();
                $table->json('attachments')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['ticket_id', 'is_internal', 'created_at']);
                $table->index(['organization_id', 'created_at']);
            });
        }

        // 6. Support Ticket Lifecycle Events (Immutable audit log)
        if (! Schema::hasTable('support_ticket_events')) {
            Schema::create('support_ticket_events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignUuid('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('actor_type')->default('user'); // user, customer, system
                $table->string('event_type'); // created, status_changed, assigned, priority_changed, comment_added, note_added, resolved, closed, reopened
                $table->json('previous_value')->nullable();
                $table->json('new_value')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();

                $table->index(['ticket_id', 'created_at']);
                $table->index(['organization_id', 'event_type']);
            });
        }

        // 7. Support Ticket Tags & Pivot
        if (! Schema::hasTable('support_ticket_tags')) {
            Schema::create('support_ticket_tags', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->timestamps();

                $table->unique(['organization_id', 'slug']);
            });
        }

        if (! Schema::hasTable('support_ticket_tag')) {
            Schema::create('support_ticket_tag', function (Blueprint $table) {
                $table->foreignUuid('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
                $table->foreignUuid('tag_id')->constrained('support_ticket_tags')->cascadeOnDelete();
                $table->primary(['ticket_id', 'tag_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_tag');
        Schema::dropIfExists('support_ticket_tags');
        Schema::dropIfExists('support_ticket_events');
        Schema::dropIfExists('support_ticket_comments');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('support_ticket_categories');
        Schema::dropIfExists('support_channel_identities');
        Schema::dropIfExists('support_customers');
    }
};
