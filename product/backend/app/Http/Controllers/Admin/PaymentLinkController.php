<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentLink;
use App\Models\PaymentLinkTransaction;
use App\Services\AI\CampaignAiService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentLinkController extends Controller
{
    /**
     * Self-heal runtime database schema for payment links and transactions.
     */
    protected function ensureSchema(): void
    {
        try {
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
                    $table->string('coupon_discount_type', 20)->default('fixed');
                    $table->decimal('coupon_discount_value', 10, 2)->default(0.00);
                    $table->unsignedInteger('max_coupon_uses')->nullable();
                    $table->unsignedInteger('coupon_uses_count')->default(0);
                    $table->boolean('is_reusable')->default(true);
                    $table->string('status', 30)->default('active')->index();
                    $table->unsignedInteger('views_count')->default(0);
                    $table->unsignedInteger('payments_count')->default(0);
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

            if (! Schema::hasTable('payment_link_transactions')) {
                Schema::create('payment_link_transactions', function (Blueprint $table) {
                    $table->uuid('id')->primary();
                    $table->foreignUuid('payment_link_id')->constrained('payment_links')->cascadeOnDelete();
                    $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                    $table->string('transaction_reference', 100)->unique()->index();
                    $table->string('razorpay_order_id', 100)->nullable()->index();
                    $table->string('razorpay_payment_id', 100)->nullable()->index();
                    $table->string('razorpay_signature', 255)->nullable();
                    $table->string('status', 30)->default('pending')->index();
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
        } catch (\Throwable $e) {
            Log::info('PaymentLink schema ensure notice: ' . $e->getMessage());
        }
    }

    public function index(Request $request, CampaignAiService $aiService): View
    {
        $this->authorizeSuperAdmin($request);
        $this->ensureSchema();

        $links = PaymentLink::query()
            ->with('creator:id,name')
            ->withCount([
                'transactions',
                'transactions as passed_count' => fn ($q) => $q->where('status', 'passed'),
                'transactions as failed_count' => fn ($q) => $q->where('status', 'failed'),
            ])
            ->latest()
            ->paginate(15);

        $totalLinks = PaymentLink::count();
        $totalPaidTransactions = PaymentLinkTransaction::where('status', 'passed')->count();
        $totalFailedTransactions = PaymentLinkTransaction::where('status', 'failed')->count();
        $totalRevenue = (float) PaymentLinkTransaction::where('status', 'passed')->sum('amount_paid');

        $standardProducts = [
            'Aprilo POS' => ['type' => 'SaaS Subscription', 'default_price' => 5000, 'features' => ['Fast barcode checkout & offline sync', 'Real-time inventory & low-stock alerts', '1-Click GST & commercial invoice download']],
            'Aprilo Chatbot' => ['type' => 'SaaS Subscription', 'default_price' => 4500, 'features' => ['24/7 AI WhatsApp customer support', 'Product catalog & knowledge base answers', 'Instant lead qualification & WhatsApp CRM sync']],
            'Aprilo Agent' => ['type' => 'SaaS Subscription', 'default_price' => 7500, 'features' => ['Autonomous multi-channel ticket operator', 'Custom webhook integration & SLA tracking', 'Cross-platform task automation']],
            'Aprilo Commerce' => ['type' => 'SaaS Subscription', 'default_price' => 6000, 'features' => ['Two-way catalog & order sync', 'Shopify & Magento connector integration', 'AI semantic search embeddings']],
            'TradeFlow' => ['type' => 'Lifetime License', 'default_price' => 12000, 'features' => ['B2B wholesale distributor margin manager', 'Bulk dispatch & automated vendor price lists', 'Batch & expiry tracking with lifetime license']],
            'Custom Software Service' => ['type' => 'Professional Services', 'default_price' => 10000, 'features' => ['Tailored software development & deployment', 'Dedicated priority onboarding & support']],
        ];

        return view('admin.super.payment-links.index', compact(
            'links',
            'totalLinks',
            'totalPaidTransactions',
            'totalFailedTransactions',
            'totalRevenue',
            'standardProducts'
        ));
    }

    public function show(Request $request, PaymentLink $paymentLink): View
    {
        $this->authorizeSuperAdmin($request);
        $this->ensureSchema();

        $transactions = $paymentLink->transactions()
            ->with('invoice:id,invoice_number,status,total_amount')
            ->latest()
            ->paginate(20);

        $passedCount = $paymentLink->transactions()->where('status', 'passed')->count();
        $failedCount = $paymentLink->transactions()->where('status', 'failed')->count();
        $totalCollected = (float) $paymentLink->transactions()->where('status', 'passed')->sum('amount_paid');

        return view('admin.super.payment-links.show', compact(
            'paymentLink',
            'transactions',
            'passedCount',
            'failedCount',
            'totalCollected'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);
        $this->ensureSchema();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'product_name' => ['nullable', 'string', 'max:120'],
            'product_type' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:2000'],
            'features_text' => ['nullable', 'string', 'max:2000'],
            'base_amount' => ['required', 'numeric', 'min:1'],
            'allow_custom_amount' => ['nullable', 'boolean'],
            'min_amount' => ['nullable', 'numeric', 'min:1'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'coupon_discount_type' => ['required_with:coupon_code', 'in:fixed,percentage'],
            'coupon_discount_value' => ['nullable', 'numeric', 'min:0'],
            'max_coupon_uses' => ['nullable', 'integer', 'min:1'],
            'is_reusable' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date'],
            'notify_whatsapp' => ['nullable', 'boolean'],
        ]);

        $features = [];
        if (! empty($validated['features_text'])) {
            $features = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validated['features_text']))));
        }

        $slug = PaymentLink::generateUniqueSlug($validated['title']);

        $link = PaymentLink::create([
            'created_by' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => $slug,
            'product_name' => $validated['product_name'] ?? 'Aprilo Software',
            'product_type' => $validated['product_type'] ?? 'SaaS Subscription',
            'description' => $validated['description'] ?? null,
            'features' => $features,
            'base_amount' => $validated['base_amount'],
            'allow_custom_amount' => (bool) ($request->boolean('allow_custom_amount')),
            'min_amount' => $validated['min_amount'] ?? null,
            'coupon_code' => filled($validated['coupon_code'] ?? null) ? strtoupper(trim($validated['coupon_code'])) : null,
            'coupon_discount_type' => $validated['coupon_discount_type'] ?? 'fixed',
            'coupon_discount_value' => $validated['coupon_discount_value'] ?? 0.00,
            'max_coupon_uses' => $validated['max_coupon_uses'] ?? null,
            'is_reusable' => $request->has('is_reusable') ? $request->boolean('is_reusable') : true,
            'status' => 'active',
            'expires_at' => $validated['expires_at'] ?? null,
            'notify_whatsapp' => $request->has('notify_whatsapp') ? $request->boolean('notify_whatsapp') : true,
        ]);

        return redirect()->route('admin.super.payment-links.show', $link->id)
            ->with('status', "Payment link '{$link->title}' generated successfully! Public checkout URL is ready.");
    }

    public function updateStatus(Request $request, PaymentLink $paymentLink): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);
        $this->ensureSchema();

        $newStatus = $paymentLink->status === 'active' ? 'disabled' : 'active';
        $paymentLink->update(['status' => $newStatus]);

        return back()->with('status', "Payment link status updated to {$newStatus}.");
    }

    public function destroy(Request $request, PaymentLink $paymentLink): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);
        $title = $paymentLink->title;
        $paymentLink->delete();

        return redirect()->route('admin.super.payment-links')
            ->with('status', "Payment link '{$title}' and its transactions deleted.");
    }

    private function authorizeSuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Super Administrator access is required.');
    }
}

