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
        Schema::create('ecommerce_connections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('platform')->index(); // shopify, magento, woocommerce, custom
            $table->string('store_name');
            $table->string('store_url')->nullable();
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->text('access_token')->nullable();
            $table->string('status')->default('connected')->index(); // connected, disconnected, error
            $table->boolean('auto_sync_enabled')->default(true);
            $table->string('sync_interval')->default('daily'); // hourly, daily, realtime
            $table->timestamp('last_synced_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('ecommerce_licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('connection_id')->nullable()->constrained('ecommerce_connections')->nullOnDelete();
            $table->string('license_key')->unique()->index();
            $table->string('platform')->index(); // magento, shopify, woocommerce
            $table->string('domain')->nullable();
            $table->string('status')->default('active')->index(); // active, suspended, expired
            $table->integer('max_stores')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ecommerce_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('connection_id')->constrained('ecommerce_connections')->cascadeOnDelete();
            $table->string('external_product_id')->index();
            $table->string('title');
            $table->string('sku')->nullable()->index();
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('inventory_quantity')->default(0);
            $table->string('status')->default('active')->index(); // active, draft, archived
            $table->json('raw_data')->nullable();
            $table->json('embedding')->nullable();
            $table->timestamps();

            $table->unique(['connection_id', 'external_product_id']);
        });

        Schema::create('ecommerce_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('connection_id')->constrained('ecommerce_connections')->cascadeOnDelete();
            $table->string('external_order_id')->index();
            $table->string('order_number')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable()->index();
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->string('currency')->default('USD');
            $table->string('order_status')->default('open')->index();
            $table->string('financial_status')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['connection_id', 'external_order_id']);
        });

        Schema::create('ecommerce_sync_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('connection_id')->nullable()->constrained('ecommerce_connections')->cascadeOnDelete();
            $table->string('sync_type')->index(); // products, orders, embeddings
            $table->string('status')->default('success')->index(); // success, failed, in_progress
            $table->integer('items_processed')->default(0);
            $table->integer('items_failed')->default(0);
            $table->text('error_details')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_sync_logs');
        Schema::dropIfExists('ecommerce_orders');
        Schema::dropIfExists('ecommerce_products');
        Schema::dropIfExists('ecommerce_licenses');
        Schema::dropIfExists('ecommerce_connections');
    }
};
