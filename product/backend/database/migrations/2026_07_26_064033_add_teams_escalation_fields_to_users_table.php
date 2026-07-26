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
        Schema::table('users', function (Blueprint $table) {
            $table->string('teams_user_id')->nullable()->unique()->index();
            $table->string('teams_conversation_id')->nullable()->index();
            $table->string('teams_service_url')->nullable();
            $table->integer('escalation_priority')->default(99)->index();
            $table->boolean('escalation_routing_active')->default(true)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['teams_user_id', 'teams_conversation_id', 'teams_service_url', 'escalation_priority', 'escalation_routing_active']);
        });
    }
};
