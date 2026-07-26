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
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->string('gemini_api_key')->nullable();
            $table->string('gemini_model')->default('gemini-flash-latest');
            $table->string('restriction_template')->default('strict_retrieval');
            $table->text('custom_system_prompt')->nullable();
            $table->text('eligibility_criteria')->nullable();
            $table->text('organization_details')->nullable();
            $table->boolean('strict_context_enforcement')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->dropColumn([
                'gemini_api_key',
                'gemini_model',
                'restriction_template',
                'custom_system_prompt',
                'eligibility_criteria',
                'organization_details',
                'strict_context_enforcement',
            ]);
        });
    }
};
