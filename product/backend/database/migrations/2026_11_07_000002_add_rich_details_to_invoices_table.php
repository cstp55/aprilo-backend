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
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (! Schema::hasColumn('invoices', 'document_type')) {
                    $table->string('document_type')->default('sales_invoice')->after('invoice_number');
                }
                if (! Schema::hasColumn('invoices', 'currency')) {
                    $table->string('currency')->default('INR')->after('amount');
                }
                if (! Schema::hasColumn('invoices', 'customer_id')) {
                    $table->string('customer_id')->nullable()->after('currency');
                }
                if (! Schema::hasColumn('invoices', 'customer_name')) {
                    $table->string('customer_name')->nullable()->after('customer_id');
                }
                if (! Schema::hasColumn('invoices', 'customer_email')) {
                    $table->string('customer_email')->nullable()->after('customer_name');
                }
                if (! Schema::hasColumn('invoices', 'customer_address')) {
                    $table->text('customer_address')->nullable()->after('customer_email');
                }
                if (! Schema::hasColumn('invoices', 'product_name')) {
                    $table->string('product_name')->nullable()->after('customer_address');
                }
                if (! Schema::hasColumn('invoices', 'product_type')) {
                    $table->string('product_type')->nullable()->after('product_name');
                }
                if (! Schema::hasColumn('invoices', 'plan_name')) {
                    $table->string('plan_name')->nullable()->after('product_type');
                }
                if (! Schema::hasColumn('invoices', 'order_id')) {
                    $table->string('order_id')->nullable()->after('plan_name');
                }
                if (! Schema::hasColumn('invoices', 'billing_period_text')) {
                    $table->string('billing_period_text')->nullable()->after('order_id');
                }
                if (! Schema::hasColumn('invoices', 'quantity')) {
                    $table->integer('quantity')->default(1)->after('billing_period_text');
                }
                if (! Schema::hasColumn('invoices', 'unit_amount')) {
                    $table->decimal('unit_amount', 10, 2)->default(0.00)->after('quantity');
                }
                if (! Schema::hasColumn('invoices', 'discount_amount')) {
                    $table->decimal('discount_amount', 10, 2)->default(0.00)->after('unit_amount');
                }
                if (! Schema::hasColumn('invoices', 'subtotal')) {
                    $table->decimal('subtotal', 10, 2)->default(0.00)->after('discount_amount');
                }
                if (! Schema::hasColumn('invoices', 'gst_rate')) {
                    $table->decimal('gst_rate', 5, 2)->default(0.00)->after('subtotal');
                }
                if (! Schema::hasColumn('invoices', 'gst_amount')) {
                    $table->decimal('gst_amount', 10, 2)->default(0.00)->after('gst_rate');
                }
                if (! Schema::hasColumn('invoices', 'total_amount')) {
                    $table->decimal('total_amount', 10, 2)->default(0.00)->after('gst_amount');
                }
                if (! Schema::hasColumn('invoices', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('status');
                }
                if (! Schema::hasColumn('invoices', 'payment_reference')) {
                    $table->string('payment_reference')->nullable()->after('payment_method');
                }
                if (! Schema::hasColumn('invoices', 'notes')) {
                    $table->text('notes')->nullable()->after('payment_reference');
                }
                if (! Schema::hasColumn('invoices', 'metadata')) {
                    $table->json('metadata')->nullable()->after('notes');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $columns = [
                    'document_type',
                    'currency',
                    'customer_id',
                    'customer_name',
                    'customer_email',
                    'customer_address',
                    'product_name',
                    'product_type',
                    'plan_name',
                    'order_id',
                    'billing_period_text',
                    'quantity',
                    'unit_amount',
                    'discount_amount',
                    'subtotal',
                    'gst_rate',
                    'gst_amount',
                    'total_amount',
                    'payment_method',
                    'payment_reference',
                    'notes',
                    'metadata',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('invoices', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};

