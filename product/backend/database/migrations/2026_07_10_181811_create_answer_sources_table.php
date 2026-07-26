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
        Schema::create('answer_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('answer_id')->constrained('answers')->cascadeOnDelete();
            $table->foreignUuid('source_id')->constrained('knowledge_sources')->cascadeOnDelete();
            $table->foreignUuid('chunk_id')->constrained('knowledge_chunks')->cascadeOnDelete();
            $table->string('citation_label')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['organization_id', 'answer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_sources');
    }
};
