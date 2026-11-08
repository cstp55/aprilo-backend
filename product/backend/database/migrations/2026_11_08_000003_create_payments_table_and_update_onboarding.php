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
        // 1. Payments Table for tracking all gateway & platform transactions
        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->string('payment_reference', 80)->unique()->index();
                $table->string('payment_type', 50)->default('platform_fee')->index(); // platform_fee, subscription, one_time, license
                $table->string('gateway', 40)->default('razorpay');
                $table->string('gateway_order_id', 120)->nullable()->index();
                $table->string('gateway_payment_id', 120)->nullable()->index();
                $table->text('gateway_signature')->nullable();
                $table->decimal('amount', 10, 2);
                $table->string('currency', 10)->default('INR');
                $table->string('status', 30)->default('paid')->index(); // paid, pending, failed, refunded
                $table->string('customer_name', 150)->nullable();
                $table->string('customer_email', 190)->nullable();
                $table->string('customer_phone', 40)->nullable();
                $table->string('payment_method', 50)->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        // 2. Add platform fee and Razorpay order fields to onboarding_requests
        if (Schema::hasTable('onboarding_requests')) {
            Schema::table('onboarding_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('onboarding_requests', 'razorpay_order_id')) {
                    $table->string('razorpay_order_id', 120)->nullable()->after('razorpay_subscription_id');
                }
                if (! Schema::hasColumn('onboarding_requests', 'platform_fee_amount')) {
                    $table->decimal('platform_fee_amount', 10, 2)->default(19.00)->after('razorpay_order_id');
                }
                if (! Schema::hasColumn('onboarding_requests', 'platform_fee_status')) {
                    $table->string('platform_fee_status', 30)->default('pending')->after('platform_fee_amount');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('onboarding_requests')) {
            Schema::table('onboarding_requests', function (Blueprint $table) {
                if (Schema::hasColumn('onboarding_requests', 'platform_fee_status')) {
                    $table->dropColumn('platform_fee_status');
                }
                if (Schema::hasColumn('onboarding_requests', 'platform_fee_amount')) {
                    $table->dropColumn('platform_fee_amount');
                }
                if (Schema::hasColumn('onboarding_requests', 'razorpay_order_id')) {
                    $table->dropColumn('razorpay_order_id');
                }
            });
        }

        Schema::dropIfExists('payments');
    }
};
