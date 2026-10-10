<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('support_ticket_sla_policies')) {
            Schema::create('support_ticket_sla_policies', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->string('name');
                $table->string('priority')->default('normal'); // low, normal, high, urgent
                $table->unsignedInteger('response_time_minutes')->default(240); // 4 hours
                $table->unsignedInteger('resolution_time_minutes')->default(1440); // 24 hours
                $table->boolean('is_active')->default(true);
                $table->json('business_hours')->nullable();
                $table->timestamps();

                $table->unique(['organization_id', 'priority']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_sla_policies');
    }
};
