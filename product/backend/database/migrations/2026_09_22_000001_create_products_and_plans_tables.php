<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('slug')->unique()->index();
                $table->string('name');
                $table->string('category')->index(); // ai_support, ecommerce, integration, enterprise
                $table->string('product_type')->default('subscription'); // subscription, one_time_license
                $table->string('platform')->nullable(); // magento, shopify, web, standalone
                $table->text('short_description')->nullable();
                $table->longText('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('image')->nullable();
                $table->decimal('price', 10, 2)->default(0.00);
                $table->string('currency')->default('INR');
                $table->string('version')->nullable();
                $table->string('compatibility')->nullable();
                $table->json('features')->nullable();
                $table->string('documentation_url')->nullable();
                $table->string('changelog_url')->nullable();
                $table->string('download_type')->nullable();
                $table->string('license_type')->nullable();
                $table->string('status')->default('active')->index();
                $table->boolean('featured')->default(false);
                $table->boolean('best_seller')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('plans')) {
            Schema::create('plans', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('slug')->index();
                $table->string('name');
                $table->string('billing_cycle')->default('monthly'); // monthly, annual, one_time
                $table->decimal('price', 10, 2)->default(0.00);
                $table->string('currency')->default('INR');
                $table->integer('trial_period_days')->default(0); // 30 for AI Support
                $table->integer('request_limit')->default(0); // e.g. 40000 for ₹499/mo
                $table->string('razorpay_plan_id')->nullable();
                $table->json('features')->nullable();
                $table->boolean('is_popular')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['product_id', 'slug']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
        Schema::dropIfExists('products');
    }
};