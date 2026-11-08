<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Payment Links Table
        if (! Schema::hasTable('payment_links')) {
            Schema::create('payment_links', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title', 190);
                $table->string('slug', 190)->unique()->index();
                $table->string('product_name', 120)->nullable();
                $table->string('product_type', 80)->default('SaaS Subscription');
                $table->text('description')->nullable();
                $table->json('features')->nullable();
                $table->decimal('base_amount', 10, 2);
                $table->boolean('allow_custom_amount')->default(false);
                $table->decimal('min_amount', 10, 2)->nullable();
                $table->string('coupon_code', 50)->nullable()->index();
                $table->string('coupon_discount_type', 20)->default('fixed'); // fixed, percentage
                $table->decimal('coupon_discount_value', 10, 2)->default(0.00);
                $table->unsignedInteger('max_coupon_uses')->nullable();
                $table->unsignedInteger('coupon_uses_count')->default(0);
                $table->boolean('is_reusable')->default(true);
                $table->string('status', 30)->default('active')->index(); // active, expired, disabled, completed
                $table->unsignedInteger('views_count')->default(0);
                $table->unsignedInteger('payments_count')->default(0); // number of users who completed payment
                $table->decimal('total_collected_amount', 10, 2)->default(0.00);
                $table->timestamp('expires_at')->nullable();
                $table->string('customer_name_preset', 120)->nullable();
                $table->string('customer_email_preset', 150)->nullable();
                $table->string('customer_phone_preset', 30)->nullable();
                $table->boolean('notify_whatsapp')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        // 2. Payment Link Transactions (Tracking individual user payments: passed / failed)
        if (! Schema::hasTable('payment_link_transactions')) {
            Schema::create('payment_link_transactions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('payment_link_id')->constrained('payment_links')->cascadeOnDelete();
                $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->string('transaction_reference', 100)->unique()->index();
                $table->string('razorpay_order_id', 100)->nullable()->index();
                $table->string('razorpay_payment_id', 100)->nullable()->index();
                $table->string('razorpay_signature', 255)->nullable();
                $table->string('status', 30)->default('pending')->index(); // passed, failed, pending
                $table->text('error_message')->nullable();
                $table->string('customer_name', 120);
                $table->string('customer_phone', 30)->index();
                $table->string('customer_email', 150)->nullable();
                $table->text('customer_address')->nullable();
                $table->string('customer_company', 150)->nullable();
                $table->string('customer_gstin', 30)->nullable();
                $table->decimal('base_amount', 10, 2);
                $table->string('coupon_code', 50)->nullable();
                $table->decimal('discount_amount', 10, 2)->default(0.00);
                $table->decimal('amount_paid', 10, 2);
                $table->string('currency', 10)->default('INR');
                $table->string('payment_method', 50)->default('Razorpay');
                $table->boolean('whatsapp_sent')->default(false);
                $table->string('whatsapp_message_id', 100)->nullable();
                $table->text('whatsapp_error')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->index(['payment_link_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_link_transactions');
        Schema::dropIfExists('payment_links');
    }
};

