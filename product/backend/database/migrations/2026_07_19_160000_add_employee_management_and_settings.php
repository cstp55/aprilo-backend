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
        // Add employee_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->unique()->index();
        });

        // Add chatbot settings to organization_settings
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->string('chatbot_color_palette')->default('#174f3f');
            $table->string('chatbot_icon')->default('default');
            $table->string('chatbot_data_source')->default('documents');
            $table->string('chatbot_intelligence_level')->default('standard');
            $table->string('chatbot_ticket_creation')->default('feedback-based');
            $table->boolean('chatbot_escalation_enabled')->default(true);
            $table->boolean('connect_teams')->default(false);
            $table->boolean('connect_skype')->default(false);
            $table->boolean('connect_whatsapp')->default(false);
            $table->boolean('connect_mail')->default(false);
            $table->string('hr_desk_email')->nullable();
        });

        // Create leave_requests
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending')->index(); // pending, approved, rejected
            $table->foreignUuid('approved_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        // Create wfh_requests
        Schema::create('wfh_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending')->index(); // pending, approved, rejected
            $table->foreignUuid('approved_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        // Create id_cards
        Schema::create('id_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('card_number')->unique();
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->string('status')->default('active')->index(); // active, suspended, expired
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('id_cards');
        Schema::dropIfExists('wfh_requests');
        Schema::dropIfExists('leave_requests');

        Schema::table('organization_settings', function (Blueprint $table) {
            $table->dropColumn([
                'chatbot_color_palette',
                'chatbot_icon',
                'chatbot_data_source',
                'chatbot_intelligence_level',
                'chatbot_ticket_creation',
                'chatbot_escalation_enabled',
                'connect_teams',
                'connect_skype',
                'connect_whatsapp',
                'connect_mail',
                'hr_desk_email',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('employee_id');
        });
    }
};
