<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_entitlements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('feature_key');
            $table->string('status')->default('active')->index();
            $table->json('limits')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'subscription_id', 'feature_key'], 'org_subscription_feature_unique');
            $table->index(['organization_id', 'feature_key', 'status'], 'org_feature_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_entitlements');
    }
};
