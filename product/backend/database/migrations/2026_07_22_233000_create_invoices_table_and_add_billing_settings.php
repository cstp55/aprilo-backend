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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('invoice_number')->unique();
            $table->string('billing_mode');
            $table->decimal('amount', 8, 2);
            $table->string('status')->default('unpaid');
            $table->date('billing_period_start');
            $table->date('billing_period_end');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        Schema::table('organization_settings', function (Blueprint $table) {
            $table->string('billing_mode')->default('subscription');
            $table->integer('usage_queries_count')->default(0);
            $table->decimal('usage_amount_due', 8, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');

        Schema::table('organization_settings', function (Blueprint $table) {
            $table->dropColumn([
                'billing_mode',
                'usage_queries_count',
                'usage_amount_due',
            ]);
        });
    }
};
