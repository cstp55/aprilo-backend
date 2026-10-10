<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_permissions')) {
            Schema::create('product_permissions', function (Blueprint $table) {
                $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignUuid('permission_id')->constrained('permissions')->cascadeOnDelete();

                $table->primary(['product_id', 'permission_id']);
            });
        }

        if (! Schema::hasTable('organization_products')) {
            Schema::create('organization_products', function (Blueprint $table) {
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('status')->default('active')->index();
                $table->timestamp('expires_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->primary(['organization_id', 'product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_products');
        Schema::dropIfExists('product_permissions');
    }
};
