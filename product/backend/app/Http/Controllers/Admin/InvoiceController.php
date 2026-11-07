<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    protected function authorizeAccess(Request $request): void
    {
        $user = $request->user();
        if ($user->isSuperAdmin() || $user->role_slug === 'owner' || $user->hasPermission('billing.view')) {
            return;
        }
        abort(403, 'Unauthorized access to invoices and billing.');
    }

    /**
     * Self-heal runtime database schema if migration has not been manually executed.
     */
    protected function ensureSchema(): void
    {
        try {
            if (Schema::hasTable('invoices') && ! Schema::hasColumn('invoices', 'customer_name')) {
                Schema::table('invoices', function ($table) {
                    if (! Schema::hasColumn('invoices', 'document_type')) {
                        $table->string('document_type')->default('sales_invoice')->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'currency')) {
                        $table->string('currency')->default('INR')->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'customer_id')) {
                        $table->string('customer_id')->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'customer_name')) {
                        $table->string('customer_name')->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'customer_email')) {
                        $table->string('customer_email')->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'customer_address')) {
                        $table->text('customer_address')->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'product_name')) {
                        $table->string('product_name')->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'product_type')) {
                        $table->string('product_type')->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'plan_name')) {
                        $table->string('plan_name')->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'order_id')) {
                        $table->string('order_id')->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'billing_period_text')) {
                        $table->string('billing_period_text')->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'quantity')) {
                        $table->integer('quantity')->default(1)->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'unit_amount')) {
                        $table->decimal('unit_amount', 10, 2)->default(0.00)->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'discount_amount')) {
                        $table->decimal('discount_amount', 10, 2)->default(0.00)->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'subtotal')) {
                        $table->decimal('subtotal', 10, 2)->default(0.00)->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'gst_rate')) {
                        $table->decimal('gst_rate', 5, 2)->default(0.00)->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'gst_amount')) {
                        $table->decimal('gst_amount', 10, 2)->default(0.00)->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'total_amount')) {
                        $table->decimal('total_amount', 10, 2)->default(0.00)->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'payment_method')) {
                        $table->string('payment_method')->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'payment_reference')) {
                        $table->string('payment_reference')->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'notes')) {
                        $table->text('notes')->nullable();
                    }
                    if (! Schema::hasColumn('invoices', 'metadata')) {
                        $table->json('metadata')->nullable();
                    }
                });
            }
        } catch (\Throwable $e) {
            // Silently proceed with fallback persistence
        }
    }

    public function index(Request $request): View
    {
        $this->authorizeAccess($request);
        $this->ensureSchema();
        $user = $request->user();

        $query = Invoice::with('organization')->latest();

        if (! $user->isSuperAdmin()) {
            $query->where('organization_id', $user->organization_id);
        } elseif ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'LIKE', "%{$term}%");
                if (Schema::hasColumn('invoices', 'customer_name')) {
                    $q->orWhere('customer_name', 'LIKE', "%{$term}%")
                      ->orWhere('customer_email', 'LIKE', "%{$term}%")
                      ->orWhere('order_id', 'LIKE', "%{$term}%")
                      ->orWhere('product_name', 'LIKE', "%{$term}%");
                }
            });
        }

        $invoices = $query->paginate(15)->withQueryString();

        // Metrics calculations
        $baseMetricsQuery = Invoice::query();
        if (! $user->isSuperAdmin()) {
            $baseMetricsQuery->where('organization_id', $user->organization_id);
        }

        $totalPaid = (float) (clone $baseMetricsQuery)->where('status', 'paid')->sum('amount');
        $totalPending = (float) (clone $baseMetricsQuery)->whereIn('status', ['unpaid', 'pending', 'open'])->sum('amount');
        $paidCount = (clone $baseMetricsQuery)->where('status', 'paid')->count();
        $pendingCount = (clone $baseMetricsQuery)->whereIn('status', ['unpaid', 'pending', 'open'])->count();
        $totalCount = (clone $baseMetricsQuery)->count();

        // Preset products across Aprilo Infotech
        $standardProducts = [
            'Aprilo POS' => ['type' => 'SaaS Subscription', 'default_plan' => 'Annual Subscription', 'price' => 5000],
            'Aprilo Chatbot' => ['type' => 'SaaS Subscription', 'default_plan' => 'Professional', 'price' => 4500],
            'Aprilo Agent' => ['type' => 'SaaS Subscription', 'default_plan' => 'Enterprise', 'price' => 7500],
            'TradeFlow' => ['type' => 'Lifetime License', 'default_plan' => 'Lifetime License', 'price' => 12000],
            'Aprilo Commerce' => ['type' => 'SaaS Subscription', 'default_plan' => 'Professional', 'price' => 6000],
            'Custom Software / Support' => ['type' => 'One-time Purchase', 'default_plan' => 'Custom License', 'price' => 10000],
        ];

        // Load all organizations with settings for dropdown selection
        $organizations = Organization::with(['owner', 'settings', 'users'])->orderBy('name')->get();
        $nextInvoiceNumber = Invoice::generateNumber('sales_invoice');

        return view('admin.invoices.index', compact(
            'invoices',
            'totalPaid',
            'totalPending',
            'paidCount',
            'pendingCount',
            'totalCount',
            'standardProducts',
            'organizations',
            'nextInvoiceNumber'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess($request);
        $this->ensureSchema();
        $user = $request->user();

        $validated = $request->validate([
            'document_type' => ['nullable', 'string', 'in:sales_invoice,quotation,payment_receipt,credit_note,refund_receipt'],
            'organization_id' => ['nullable', 'string'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'customer_id' => ['nullable', 'string', 'max:100'],
            'product_name' => ['required', 'string', 'max:255'],
            'product_type' => ['nullable', 'string', 'max:100'],
            'plan_name' => ['nullable', 'string', 'max:100'],
            'billing_period_text' => ['nullable', 'string', 'max:255'],
            'order_id' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_amount' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:paid,pending,unpaid'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'invoice_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $orgId = ! empty($validated['organization_id'])
            ? $validated['organization_id']
            : ($user->organization_id ?? Organization::first()?->id);

        if (! $orgId) {
            return back()->withErrors(['organization_id' => 'Valid organization required to generate invoice.']);
        }

        $documentType = $validated['document_type'] ?? 'sales_invoice';
        $invoiceNumber = Invoice::generateNumber($documentType);

        $quantity = (int) $validated['quantity'];
        $unitAmount = (float) $validated['unit_amount'];
        $discountAmount = (float) ($validated['discount_amount'] ?? 0.00);
        $subtotal = max(0.00, ($unitAmount * $quantity) - $discountAmount);
        
        $gstAmount = 0.00;
        $totalAmount = $subtotal;

        $customerId = $validated['customer_id'] ?: ('CUST-' . rand(1000, 9999));
        $orderId = $validated['order_id'] ?: ('APR-ORD-' . rand(1000, 9999));
        $invoiceDate = ! empty($validated['invoice_date']) ? \Carbon\Carbon::parse($validated['invoice_date']) : now();
        $isPaid = in_array($validated['status'], ['paid'], true);

        $payload = [
            'organization_id' => $orgId,
            'invoice_number' => $invoiceNumber,
            'billing_mode' => 'subscription',
            'amount' => $totalAmount,
            'status' => $isPaid ? 'paid' : 'unpaid',
            'billing_period_start' => $invoiceDate->copy()->startOfMonth(),
            'billing_period_end' => $invoiceDate->copy()->endOfMonth(),
            'due_date' => $invoiceDate->copy()->addDays(15),
            'paid_at' => $isPaid ? $invoiceDate : null,
            'created_at' => $invoiceDate,
        ];

        $richDetails = [
            'document_type' => $documentType,
            'currency' => 'INR',
            'customer_id' => $customerId,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_address' => $validated['customer_address'] ?? null,
            'product_name' => $validated['product_name'],
            'product_type' => $validated['product_type'] ?? 'SaaS Subscription',
            'plan_name' => $validated['plan_name'] ?? 'Professional',
            'order_id' => $orderId,
            'billing_period_text' => $validated['billing_period_text'] ?? null,
            'quantity' => $quantity,
            'unit_amount' => $unitAmount,
            'discount_amount' => $discountAmount,
            'subtotal' => $subtotal,
            'gst_rate' => 0.00,
            'gst_amount' => $gstAmount,
            'total_amount' => $totalAmount,
            'payment_method' => $validated['payment_method'] ?? 'UPI / Online Payment',
            'payment_reference' => $validated['payment_reference'] ?: ('TXN-' . strtoupper(substr(md5(uniqid()), 0, 8))),
            'notes' => $validated['notes'] ?? 'Thank you for choosing Aprilo Infotech. Build Smarter. Grow Faster.',
            'seller_gstin' => '09FLSPS7380R1ZH',
            'metadata' => [
                'seller_legal_name' => 'APRILO ENTERPRISES',
                'seller_trade_name' => 'APRILO INFOTECH',
                'seller_type' => 'Sole Proprietorship',
                'seller_gstin' => '09FLSPS7380R1ZH',
                'seller_address' => 'Jaunpur, Uttar Pradesh, India',
                'seller_website' => 'https://apriloinfotech.com',
                'seller_email' => 'contact@apriloinfotech.com',
                'tagline' => 'Build Smarter. Grow Faster.',
            ],
        ];

        // Save real DB columns if available
        foreach ($richDetails as $col => $val) {
            if (Schema::hasColumn('invoices', $col)) {
                $payload[$col] = $val;
            }
        }

        $invoice = Invoice::create($payload);

        // Resilient persistence cache in storage
        $this->persistInvoiceMeta($invoice->invoice_number, $invoice->id, $richDetails);

        return redirect()->route('admin.invoices.show', $invoice->id)
            ->with('status', "Invoice {$invoice->invoice_number} generated successfully for Aprilo Infotech!");
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeAccess($request);
        $this->ensureSchema();

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'customer_id' => ['nullable', 'string', 'max:100'],
            'order_id' => ['nullable', 'string', 'max:100'],
            'product_name' => ['nullable', 'string', 'max:255'],
            'product_type' => ['nullable', 'string', 'max:100'],
            'plan_name' => ['nullable', 'string', 'max:100'],
            'billing_period_text' => ['nullable', 'string', 'max:255'],
            'unit_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:paid,pending,unpaid'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($validated as $col => $val) {
            if (Schema::hasColumn('invoices', $col)) {
                $invoice->{$col} = $val;
            }
        }

        if (! empty($validated['total_amount'])) {
            $invoice->amount = $validated['total_amount'];
        }
        if (! empty($validated['status'])) {
            $invoice->status = $validated['status'];
            if ($validated['status'] === 'paid' && ! $invoice->paid_at) {
                $invoice->paid_at = now();
            }
        }

        $invoice->save();

        // Also persist in metadata cache
        $this->persistInvoiceMeta($invoice->invoice_number, $invoice->id, $validated);

        return back()->with('status', "Invoice {$invoice->invoice_number} updated successfully!");
    }

    public function show(Request $request, Invoice $invoice): View
    {
        $this->authorizeAccess($request);
        $this->ensureSchema();
        $user = $request->user();

        if (! $user->isSuperAdmin() && $invoice->organization_id !== $user->organization_id) {
            abort(404, 'Invoice not found.');
        }

        $organization = $invoice->organization ?? Organization::find($invoice->organization_id);
        $sellerGstin = '09FLSPS7380R1ZH';

        return view('admin.invoice', compact('invoice', 'organization', 'sellerGstin'));
    }

    protected function persistInvoiceMeta(string $invoiceNumber, string $invoiceId, array $data): void
    {
        try {
            $metaFile = storage_path('invoices_meta.json');
            $all = file_exists($metaFile) ? json_decode(@file_get_contents($metaFile), true) ?: [] : [];
            
            $existing = $all[$invoiceNumber] ?? ($all[$invoiceId] ?? []);
            $merged = array_merge($existing, $data);

            $all[$invoiceNumber] = $merged;
            $all[$invoiceId] = $merged;
            @file_put_contents($metaFile, json_encode($all, JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {
            // Ignore file write errors
        }
    }
}
