<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_supports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nickname', 80);
            $table->string('availability_status', 20)->default('offline');
            $table->json('availability_slots')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'nickname']);
            $table->index(['organization_id', 'is_active', 'availability_status'], 'agent_support_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_supports');
    }
};
