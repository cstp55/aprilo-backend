<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('knowledge_chunks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('source_id')->constrained('knowledge_sources')->cascadeOnDelete();
            $table->longText('chunk_text');
            $table->unsignedInteger('chunk_index');
            $table->string('section_title')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['organization_id', 'source_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
            DB::statement('ALTER TABLE knowledge_chunks ADD COLUMN embedding vector(1536)');
        } else {
            Schema::table('knowledge_chunks', function (Blueprint $table) {
                $table->json('embedding')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_chunks');
    }
};
