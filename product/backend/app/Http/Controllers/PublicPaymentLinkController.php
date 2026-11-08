<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\MarketingLead;
use App\Models\PaymentLink;
use App\Models\PaymentLinkTransaction;
use App\Services\RazorpayPaymentLinkService;
use App\Services\WhatsAppCloudApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicPaymentLinkController extends Controller
{
    /**
     * Display public checkout page.
     */
    public function show(string $slug, RazorpayPaymentLinkService $razorpayService): View
    {
        $link = PaymentLink::where('slug', $slug)->firstOrFail();

        if ($link->status === 'disabled') {
            abort(404, 'This payment link has been deactivated.');
        }

        if ($link->isExpired()) {
            abort(410, 'This payment link has expired.');
        }

        if (! $link->is_reusable && $link->payments_count > 0) {
            abort(410, 'This payment link has already been completed.');
        }

        $link->increment('views_count');

        $sellerGstin = '09FLSPS7380R1ZH';
        $razorpayKeyId = $razorpayService->getKeyId();
        $isMockMode = $razorpayService->isMockMode();
        $isSandbox = $razorpayService->isSandbox();
        $isProduction = $razorpayService->isProduction();

        return view('public.payment-link.checkout', compact(
            'link',
            'sellerGstin',
            'razorpayKeyId',
            'isMockMode',
            'isSandbox',
            'isProduction'
        ));
    }

    /**
     * Check coupon code validity and calculate discount.
     */
    public function checkCoupon(Request $request, string $slug): JsonResponse
    {
        $link = PaymentLink::where('slug', $slug)->firstOrFail();
        $code = (string) $request->input('coupon_code', '');
        $amount = (float) $request->input('amount', $link->base_amount);

        if ($link->allow_custom_amount && $amount <= 0) {
            $amount = (float) $link->base_amount;
        }

        if (! $link->isValidCoupon($code)) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired coupon code.',
                'discount' => 0.00,
                'final_amount' => $amount,
            ], 422);
        }

        $discount = $link->calculateDiscount($code, $amount);
        $finalAmount = $link->calculateFinalAmount($code, $amount);

        return response()->json([
            'valid' => true,
            'message' => "Coupon '{$link->coupon_code}' applied successfully!",
            'discount' => $discount,
            'final_amount' => $finalAmount,
        ]);
    }

    /**
     * Prepare order and initialize transaction for checkout.
     */
    public function createOrder(Request $request, string $slug, RazorpayPaymentLinkService $razorpayService): JsonResponse
    {
        $link = PaymentLink::where('slug', $slug)->firstOrFail();

        if ($link->status !== 'active' || $link->isExpired()) {
            return response()->json(['message' => 'Payment link is no longer active.'], 400);
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'customer_company' => ['nullable', 'string', 'max:150'],
            'customer_gstin' => ['nullable', 'string', 'max:30'],
            'custom_amount' => ['nullable', 'numeric', 'min:1'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        $baseAmount = $link->allow_custom_amount && ! empty($validated['custom_amount'])
            ? (float) $validated['custom_amount']
            : (float) $link->base_amount;

        $couponCode = $validated['coupon_code'] ?? null;
        $discountAmount = $link->calculateDiscount($couponCode, $baseAmount);
        $finalAmount = $link->calculateFinalAmount($couponCode, $baseAmount);

        $txnRef = 'TXN-' . strtoupper(Str::random(10));
        $phone = preg_replace('/\D+/', '', $validated['customer_phone']);

        // Generate Razorpay Order
        $order = $razorpayService->createOrder($finalAmount, 'INR', $txnRef, [
            'payment_link_id' => $link->id,
            'customer_name' => $validated['customer_name'],
            'product' => $link->product_name,
        ]);

        $transaction = PaymentLinkTransaction::create([
            'payment_link_id' => $link->id,
            'transaction_reference' => $txnRef,
            'razorpay_order_id' => $order['id'] ?? null,
            'status' => 'pending',
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $phone,
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_address' => $validated['customer_address'] ?? null,
            'customer_company' => $validated['customer_company'] ?? null,
            'customer_gstin' => $validated['customer_gstin'] ?? null,
            'base_amount' => $baseAmount,
            'coupon_code' => $link->isValidCoupon($couponCode) ? strtoupper(trim($couponCode)) : null,
            'discount_amount' => $discountAmount,
            'amount_paid' => $finalAmount,
            'currency' => 'INR',
            'payment_method' => 'Razorpay',
        ]);

        return response()->json([
            'status' => 'success',
            'transaction_id' => $transaction->id,
            'transaction_reference' => $transaction->transaction_reference,
            'order_id' => $order['id'] ?? null,
            'amount_paise' => (int) round($finalAmount * 100),
            'amount' => $finalAmount,
            'currency' => 'INR',
            'razorpay_key_id' => $razorpayService->getKeyId(),
            'is_mock_mode' => $razorpayService->isMockMode(),
            'is_sandbox' => $razorpayService->isSandbox(),
            'is_production' => $razorpayService->isProduction(),
        ]);
    }

    /**
     * Verify payment signature, generate official invoice, and send WhatsApp confirmation.
     */
    public function verifyPayment(
        Request $request,
        string $slug,
        RazorpayPaymentLinkService $razorpayService,
        WhatsAppCloudApi $whatsApp
    ): JsonResponse|RedirectResponse {
        $link = PaymentLink::where('slug', $slug)->firstOrFail();

        $transactionId = $request->input('transaction_id');
        $orderId = (string) $request->input('razorpay_order_id', '');
        $paymentId = (string) $request->input('razorpay_payment_id', '');
        $signature = (string) $request->input('razorpay_signature', '');

        $transaction = PaymentLinkTransaction::where('id', $transactionId)
            ->where('payment_link_id', $link->id)
            ->firstOrFail();

        // Prevent double processing
        if ($transaction->status === 'passed') {
            return response()->json([
                'status' => 'success',
                'redirect_url' => route('public.pay.success', [$link->slug, $transaction->id]),
            ]);
        }

        // Validate real Razorpay signature
        $isValid = $razorpayService->verifyPaymentSignature(
            $orderId ?: (string) $transaction->razorpay_order_id,
            $paymentId,
            $signature
        );

        if (! $isValid) {
            $transaction->update([
                'status' => 'failed',
                'error_message' => 'Payment signature verification failed or declined by gateway.',
                'razorpay_payment_id' => $paymentId ?: null,
            ]);

            return response()->json([
                'status' => 'failed',
                'message' => 'Payment verification failed. Please try again.',
            ], 422);
        }

        // 1. Mark transaction as PASSED
        $transaction->update([
            'status' => 'passed',
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
            'paid_at' => now(),
        ]);

        // 2. Update Link Statistics (how many users done the payment)
        $link->increment('payments_count');
        $link->increment('total_collected_amount', $transaction->amount_paid);

        if ($transaction->coupon_code && $link->isValidCoupon($transaction->coupon_code)) {
            $link->increment('coupon_uses_count');
        }

        if (! $link->is_reusable) {
            $link->update(['status' => 'completed']);
        }

        // Resolve Organization ID for the Invoice
        $orgId = null;
        if ($link->created_by) {
            $creator = \App\Models\User::find($link->created_by);
            $orgId = $creator?->organization_id;
        }
        if (! $orgId) {
            $orgId = \App\Models\Organization::first()?->id;
        }
        if (! $orgId) {
            $defaultOrg = \App\Models\Organization::firstOrCreate(
                ['slug' => 'aprilo-enterprises'],
                [
                    'name' => 'APRILO ENTERPRISES',
                    'status' => 'active',
                ]
            );
            $orgId = $defaultOrg->id;
        }

        // 3. Generate Official Aprilo Infotech Sales Invoice
        $nextInvoiceNum = Invoice::generateNumber('sales_invoice');
        $invoiceDate = now();
        $invoice = Invoice::create([
            'organization_id' => $orgId,
            'invoice_number' => $nextInvoiceNum,
            'document_type' => 'sales_invoice',
            'billing_mode' => 'commercial',
            'currency' => 'INR',
            'amount' => $transaction->amount_paid,
            'status' => 'paid',
            'billing_period_start' => $invoiceDate->copy()->startOfMonth(),
            'billing_period_end' => $invoiceDate->copy()->addYear()->endOfMonth(),
            'due_date' => $invoiceDate->copy()->addDays(15),
            'paid_at' => $invoiceDate,
            'customer_id' => 'CUST-' . strtoupper(substr(md5($transaction->customer_phone), 0, 6)),
            'customer_name' => $transaction->customer_name,
            'customer_email' => $transaction->customer_email,
            'customer_address' => $transaction->customer_address,
            'product_name' => $link->product_name ?: 'Aprilo Software Product',
            'product_type' => $link->product_type ?: 'SaaS Subscription',
            'plan_name' => $link->title,
            'order_id' => $transaction->transaction_reference,
            'billing_period_text' => date('d M Y') . ' – ' . date('d M Y', strtotime('+1 year')),
            'quantity' => 1,
            'unit_amount' => $transaction->base_amount,
            'discount_amount' => $transaction->discount_amount,
            'subtotal' => $transaction->amount_paid,
            'gst_rate' => 0.00,
            'gst_amount' => 0.00,
            'total_amount' => $transaction->amount_paid,
            'payment_method' => 'Razorpay / Online',
            'payment_reference' => $paymentId,
            'notes' => 'Thank you for choosing Aprilo Infotech. Build Smarter. Grow Faster.',
            'metadata' => [
                'organization_id' => $orgId,
                'payment_link_id' => $link->id,
                'payment_link_slug' => $link->slug,
                'coupon_code' => $transaction->coupon_code,
                'seller_legal' => 'APRILO ENTERPRISES',
                'seller_trade' => 'APRILO INFOTECH',
                'seller_gstin' => '09FLSPS7380R1ZH',
                'seller_address' => 'Jaunpur, Uttar Pradesh, India',
                'seller_website' => 'https://apriloinfotech.com',
                'seller_email' => 'contact@apriloinfotech.com',
            ],
        ]);

        $transaction->update(['invoice_id' => $invoice->id]);

        try {
            $metaFile = storage_path('invoices_meta.json');
            $all = file_exists($metaFile) ? json_decode(@file_get_contents($metaFile), true) ?: [] : [];
            $all[$invoice->invoice_number] = $invoice->toArray();
            $all[$invoice->id] = $invoice->toArray();
            @file_put_contents($metaFile, json_encode($all, JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {}

        // 4. Update or Add to Marketing Lead Pipeline (mark as converted customer!)
        try {
            MarketingLead::updateOrCreate(
                ['phone' => $transaction->customer_phone],
                [
                    'name' => $transaction->customer_name,
                    'email' => $transaction->customer_email,
                    'company_name' => $transaction->customer_company,
                    'status' => 'converted',
                    'target_product' => $link->product_name,
                    'conversion_notes' => "Converted via payment link '{$link->title}'. Amount: ₹{$transaction->amount_paid}.",
                    'last_contacted_at' => now(),
                    'last_campaign_name' => 'Payment Link Checkout',
                ]
            );
        } catch (\Throwable $e) {
            Log::info('MarketingLead pipeline sync note: ' . $e->getMessage());
        }

        // 5. Send Invoice to Customer via WhatsApp Message
        if ($link->notify_whatsapp && filled($transaction->customer_phone)) {
            $this->dispatchWhatsAppInvoice($transaction, $invoice, $link, $whatsApp);
        }

        return response()->json([
            'status' => 'success',
            'redirect_url' => route('public.pay.success', [$link->slug, $transaction->id]),
        ]);
    }

    /**
     * Display post-checkout success screen with invoice download.
     */
    public function success(string $slug, PaymentLinkTransaction $transaction): View
    {
        $link = PaymentLink::where('slug', $slug)->firstOrFail();

        if ($transaction->payment_link_id !== $link->id) {
            abort(404);
        }

        $invoice = $transaction->invoice;
        $sellerGstin = '09FLSPS7380R1ZH';

        return view('public.payment-link.success', compact('link', 'transaction', 'invoice', 'sellerGstin'));
    }

    /**
     * Public, no-login view for customers to view and download their invoice.
     */
    public function publicInvoice(Invoice $invoice): View
    {
        $sellerGstin = '09FLSPS7380R1ZH';
        $organization = $invoice->organization;

        return view('admin.invoice', compact('invoice', 'organization', 'sellerGstin'));
    }

    /**
     * Dispatch WhatsApp message with invoice and receipt details to the customer.
     */
    protected function dispatchWhatsAppInvoice(
        PaymentLinkTransaction $transaction,
        Invoice $invoice,
        PaymentLink $link,
        WhatsAppCloudApi $whatsApp
    ): void {
        try {
            $publicInvoiceUrl = route('public.pay.invoice', $invoice->id);
            $formattedAmount = number_format($transaction->amount_paid, 2);

            $messageText = "*APRILO INFOTECH*\n"
                . "A technology brand of *APRILO ENTERPRISES*\n"
                . "_Build Smarter. Grow Faster._\n\n"
                . "✅ *PAYMENT CONFIRMED*\n"
                . "Dear *{$transaction->customer_name}*, thank you for your payment!\n\n"
                . "📄 *Invoice No:* `{$invoice->invoice_number}`\n"
                . "🏷 *Product:* *{$link->product_name}*\n"
                . "💰 *Total Paid:* *₹{$formattedAmount}*\n"
                . "💳 *Payment Ref:* `{$transaction->razorpay_payment_id}`\n"
                . "📅 *Date:* " . now()->format('d M Y') . "\n\n"
                . "🔗 *View & Download Tax / Sales Invoice (PDF):*\n"
                . "{$publicInvoiceUrl}\n\n"
                . "Official Website: https://apriloinfotech.com\n"
                . "Support: contact@apriloinfotech.com\n"
                . "Thank you for partnering with Aprilo Infotech! 🚀";

            $result = $whatsApp->sendCustomText($transaction->customer_phone, $messageText);

            $msgId = data_get($result, 'messages.0.id');
            $transaction->update([
                'whatsapp_sent' => true,
                'whatsapp_message_id' => $msgId,
            ]);
        } catch (\Throwable $e) {
            Log::warning('WhatsApp Invoice notification failed: ' . $e->getMessage());
            $transaction->update([
                'whatsapp_sent' => false,
                'whatsapp_error' => mb_substr($e->getMessage(), 0, 500),
            ]);
        }
    }
}

