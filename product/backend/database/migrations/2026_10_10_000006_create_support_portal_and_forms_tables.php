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
        // 1. Support Portal Settings
        if (! Schema::hasTable('support_portal_settings')) {
            Schema::create('support_portal_settings', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->string('portal_title')->default('Customer Support Portal');
                $table->string('portal_subheading')->nullable()->default('Submit a ticket or check existing request status');
                $table->string('portal_slug')->nullable();
                $table->string('support_email')->nullable();
                $table->string('primary_color', 32)->default('#22C55E');
                $table->string('secondary_color', 32)->default('#0F172A');
                $table->string('logo_url')->nullable();
                $table->string('favicon_url')->nullable();
                $table->uuid('default_form_id')->nullable();
                $table->boolean('allow_anonymous_tickets')->default(true);
                $table->boolean('require_email_verification')->default(false);
                $table->boolean('enable_customer_portal_auth')->default(true);
                $table->boolean('is_portal_active')->default(true);
                $table->json('branding_metadata')->nullable();
                $table->timestamps();

                $table->unique('organization_id');
                $table->unique('portal_slug');
            });
        }

        // 2. Add department to support_ticket_categories if needed
        if (Schema::hasTable('support_ticket_categories') && ! Schema::hasColumn('support_ticket_categories', 'department')) {
            Schema::table('support_ticket_categories', function (Blueprint $table) {
                $table->string('department')->nullable()->after('description');
                $table->uuid('default_form_id')->nullable()->after('department');
            });
        }

        // 3. Dynamic Support Ticket Forms
        if (! Schema::hasTable('support_ticket_forms')) {
            Schema::create('support_ticket_forms', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->uuid('category_id')->nullable();
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->unsignedInteger('version')->default(1);
                $table->boolean('is_default')->default(false);
                $table->json('schema'); // Array of form field definitions
                $table->json('settings')->nullable(); // Button text, success message, confirmation
                $table->timestamp('published_at')->nullable();
                $table->timestamp('archived_at')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['organization_id', 'status']);
                $table->unique(['organization_id', 'slug']);
            });
        }

        // 4. Form Version History
        if (! Schema::hasTable('support_ticket_form_versions')) {
            Schema::create('support_ticket_form_versions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('form_id')->constrained('support_ticket_forms')->cascadeOnDelete();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->unsignedInteger('version');
                $table->json('schema');
                $table->string('change_summary')->nullable();
                $table->foreignUuid('published_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['form_id', 'version']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_form_versions');
        Schema::dropIfExists('support_ticket_forms');
        
        if (Schema::hasTable('support_ticket_categories') && Schema::hasColumn('support_ticket_categories', 'department')) {
            Schema::table('support_ticket_categories', function (Blueprint $table) {
                $table->dropColumn(['department', 'default_form_id']);
            });
        }

        Schema::dropIfExists('support_portal_settings');
    }
};
