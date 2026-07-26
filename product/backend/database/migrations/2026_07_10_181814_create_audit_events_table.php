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
        Schema::create('audit_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('actor_user_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->string('event_type')->index();
            $table->string('entity_type')->nullable()->index();
            $table->uuid('entity_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['organization_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_events');
    }
};
