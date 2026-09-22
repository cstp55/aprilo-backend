<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('onboarding_requests')) {
            Schema::create('onboarding_requests', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('token', 64)->unique()->index();
                $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->foreignUuid('plan_id')->nullable()->constrained('plans')->nullOnDelete();
                $table->json('account_data');
                $table->json('organization_data');
                $table->string('razorpay_subscription_id')->nullable()->index();
                $table->string('razorpay_payment_id')->nullable();
                $table->string('razorpay_signature')->nullable();
                $table->string('status')->default('pending')->index(); // pending, authorized, completed, failed
                $table->timestamp('trial_ends_at')->nullable();
                $table->boolean('autopay_authorized')->default(false);
                $table->foreignUuid('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('sso_token', 80)->nullable()->unique()->index();
                $table->timestamp('sso_token_expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignUuid('plan_id')->constrained('plans')->cascadeOnDelete();
                $table->string('razorpay_subscription_id')->unique()->index();
                $table->string('razorpay_plan_id')->nullable();
                $table->string('razorpay_customer_id')->nullable();
                $table->string('status')->default('trialing')->index(); // trialing, active, past_due, cancelled, halted
                $table->timestamp('trial_start')->nullable();
                $table->timestamp('trial_end')->nullable();
                $table->timestamp('current_cycle_start')->nullable();
                $table->timestamp('current_cycle_end')->nullable();
                $table->decimal('price', 10, 2)->default(0.00);
                $table->string('currency')->default('INR');
                $table->integer('request_limit')->default(0);
                $table->boolean('auto_renew')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('onboarding_requests');
    }
};