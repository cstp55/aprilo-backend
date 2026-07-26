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
        Schema::create('organization_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('minutes_saved_per_resolved_question')->default(5);
            $table->foreignUuid('default_escalation_owner')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->string('assistant_status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_settings');
    }
};
