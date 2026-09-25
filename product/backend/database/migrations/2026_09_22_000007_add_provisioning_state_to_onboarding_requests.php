<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onboarding_requests', function (Blueprint $table) {
            $table->string('razorpay_customer_id')->nullable()->after('razorpay_subscription_id');
            $table->string('provisioning_status')->default('pending')->index()->after('status');
            $table->unsignedTinyInteger('provisioning_attempts')->default(0)->after('provisioning_status');
            $table->text('provisioning_error')->nullable()->after('provisioning_attempts');
            $table->timestamp('provisioned_at')->nullable()->after('provisioning_error');
        });
    }

    public function down(): void
    {
        Schema::table('onboarding_requests', function (Blueprint $table) {
            $table->dropColumn([
                'razorpay_customer_id',
                'provisioning_status',
                'provisioning_attempts',
                'provisioning_error',
                'provisioned_at',
            ]);
        });
    }
};
