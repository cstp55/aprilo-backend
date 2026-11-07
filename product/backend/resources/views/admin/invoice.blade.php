<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice->invoice_number }} - Aprilo Infotech</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- html2pdf for direct 1-click PDF download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root {
            --green: #22C55E;
            --green-dark: #16A34A;
            --green-deep: #065F46;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --paper: #ffffff;
            --bg: #f8fafc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--ink);
            background: var(--bg);
            margin: 0;
            padding: 30px 20px;
            font-size: 13.5px;
            line-height: 1.6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .screen-toolbar {
            max-width: 820px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            background: var(--green);
            color: #ffffff;
            padding: 10px 18px;
            border: 0;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13.5px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.2);
        }

        .btn:hover {
            background: var(--green-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #ffffff;
            border: 1px solid var(--line);
            color: var(--ink);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .btn-secondary:hover {
            background: #f1f5f9;
            transform: translateY(-1px);
        }

        /* The printable paper canvas */
        .invoice-card {
            max-width: 820px;
            margin: 0 auto;
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 48px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
            position: relative;
        }

        .brand-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--line);
            padding-bottom: 24px;
            margin-bottom: 28px;
            gap: 20px;
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: -0.02em;
            margin: 0;
        }

        .brand-sub {
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
            margin-top: 3px;
        }

        .doc-meta {
            text-align: right;
        }

        .doc-title {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: var(--ink);
            margin: 0 0 6px 0;
            letter-spacing: 0.05em;
        }

        .stamp-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 12px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .stamp-paid {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .stamp-pending {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .meta-line {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 2px;
        }

        .meta-line strong {
            color: var(--ink);
        }

        /* Two Column Addresses */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 36px;
            margin-bottom: 32px;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px 20px;
        }

        .info-box h3 {
            margin: 0 0 10px 0;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
        }

        .info-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 4px;
        }

        .info-detail {
            font-size: 12.5px;
            color: #475569;
            line-height: 1.55;
        }

        /* Product Table */
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
        }

        table.items-table th, 
        table.items-table td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid var(--line);
        }

        table.items-table th {
            background: #f8fafc;
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
        }

        table.items-table tr:last-child td {
            border-bottom: 0;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        /* Summary / Totals block */
        .totals-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 32px;
        }

        .payment-box {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px 20px;
            font-size: 12.5px;
        }

        .payment-box h4 {
            margin: 0 0 8px 0;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
        }

        .totals-table-wrapper {
            width: 320px;
        }

        table.totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.totals-table td {
            padding: 7px 10px;
            font-size: 13.5px;
        }

        table.totals-table tr.grand-total td {
            border-top: 2px solid var(--line);
            border-bottom: 2px solid var(--line);
            padding: 12px 10px;
            font-size: 18px;
            font-weight: 800;
            font-family: 'Outfit', sans-serif;
            color: var(--ink);
        }

        /* Terms & Notes */
        .notes-section {
            border-top: 1px solid var(--line);
            padding-top: 20px;
            margin-top: 20px;
            font-size: 12px;
            color: #64748b;
            line-height: 1.6;
        }

        .notes-section ul {
            margin: 6px 0 14px 20px;
            padding: 0;
        }

        .notes-section li {
            margin-bottom: 3px;
        }

        /* Footer */
        .brand-footer {
            margin-top: 32px;
            border-top: 1px dashed var(--line);
            padding-top: 20px;
            text-align: center;
        }

        .brand-footer-name {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: 0.05em;
        }

        .brand-footer-tagline {
            font-size: 12px;
            color: var(--green-dark);
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-top: 3px;
        }

        @media (max-width: 768px) {
            .invoice-card {
                padding: 24px 16px;
            }
            .grid-2 {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .totals-section {
                flex-direction: column;
            }
            .totals-table-wrapper {
                width: 100%;
            }
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .screen-toolbar, .screen-only-btn {
                display: none !important;
            }
            .invoice-card {
                border: 0 !important;
                box-shadow: none !important;
                padding: 15mm 15mm !important;
                max-width: 100% !important;
            }
            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- On-screen Action Toolbar -->
    <div class="screen-toolbar">
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary">
                ← Back to Payments & Invoices
            </a>
            <button type="button" onclick="openEditModal()" class="btn btn-secondary" style="font-size: 13px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Invoice Details
            </button>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" onclick="window.print()" class="btn btn-secondary" title="Native browser print dialog">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/></svg>
                Print Invoice
            </button>
            <button type="button" id="downloadPdfBtn" onclick="downloadInvoicePDF()" class="btn" title="Download direct PDF file">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                Download PDF
            </button>
        </div>
    </div>

    <!-- Official Invoice Document (Rendered for Screen, Print & PDF Export) -->
    <div class="invoice-card" id="invoiceDocument">
        
        <!-- Header: Brand & Document Meta -->
        <div class="brand-header">
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h1 class="brand-title">APRILO INFOTECH</h1>
                </div>
                <div class="brand-sub">
                    A technology and software product brand of <strong>APRILO ENTERPRISES</strong>
                </div>
            </div>

            <div class="doc-meta">
                @if ($invoice->status === 'paid')
                    <span class="stamp-badge stamp-paid">PAID</span>
                @else
                    <span class="stamp-badge stamp-pending">PENDING</span>
                @endif

                <div class="doc-title">{{ $invoice->display_document_title }}</div>
                <div class="meta-line">Invoice #: <strong>{{ $invoice->invoice_number }}</strong></div>
                <div class="meta-line">Invoice date: <strong>{{ $invoice->created_at ? $invoice->created_at->format('d M Y') : now()->format('d M Y') }}</strong></div>
                <div class="meta-line">Due date: <strong>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : now()->addDays(15)->format('d M Y') }}</strong></div>
            </div>
        </div>

        <!-- 2 Column Details: Seller Details & Bill To -->
        <div class="grid-2">
            <!-- Seller Details -->
            <div class="info-box">
                <h3>Seller Details</h3>
                <div class="info-name">APRILO ENTERPRISES</div>
                <div class="info-detail">
                    <div><strong>Trading as:</strong> APRILO INFOTECH</div>
                    <div><strong>Constitution:</strong> Sole Proprietorship</div>
                    <div style="margin: 3px 0; color: var(--green-dark); font-weight: 800; font-size: 13px;">
                        <strong>GSTIN:</strong> {{ $sellerGstin ?? '09FLSPS7380R1ZH' }}
                    </div>
                    <div><strong>Registered Address:</strong> Jaunpur, Uttar Pradesh, India</div>
                    <div><strong>Website:</strong> <a href="https://apriloinfotech.com" target="_blank" style="color: var(--green-dark); text-decoration: none;">apriloinfotech.com</a></div>
                    <div><strong>Email:</strong> contact@apriloinfotech.com</div>
                </div>
            </div>

            <!-- Bill To Details -->
            <div class="info-box">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <h3 style="margin: 0; padding: 0; border: 0;">Bill To</h3>
                    <button type="button" onclick="openEditModal()" class="screen-only-btn" style="border: 0; background: #e2e8f0; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px; cursor: pointer; color: #334155;">✎ Edit</button>
                </div>
                <div class="info-name">{{ $invoice->display_customer_name }}</div>
                <div class="info-detail">
                    <div><strong>Customer ID:</strong> {{ $invoice->display_customer_id }}</div>
                    @if ($invoice->display_customer_email)
                        <div><strong>Email:</strong> {{ $invoice->display_customer_email }}</div>
                    @endif
                    <div><strong>Billing Address:</strong> {{ $invoice->display_customer_address }}</div>
                    <div style="margin-top: 4px;"><strong>Order ID:</strong> {{ $invoice->display_order_id }}</div>
                    @if ($invoice->display_billing_period)
                        <div><strong>Billing Period:</strong> {{ $invoice->display_billing_period }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Product Details Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 55%;">Product Details / Description</th>
                    <th class="text-center" style="width: 15%;">Qty</th>
                    <th class="text-right" style="width: 30%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="font-weight: 700; font-size: 14.5px; color: var(--ink);">
                            {{ $invoice->display_product_name }}
                        </div>
                        <div style="font-size: 12px; color: var(--muted); margin-top: 3px;">
                            Product Type: <strong>{{ $invoice->display_product_type }}</strong> · Plan / License: <strong>{{ $invoice->display_plan_name }}</strong>
                        </div>
                        @if ($invoice->billing_mode === 'pay_as_you_go')
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                Metered query traffic, conversational intelligence & whitelabel webhooks
                            </div>
                        @endif
                    </td>
                    <td class="text-center" style="font-weight: 600;">
                        {{ $invoice->quantity ?: 1 }}
                    </td>
                    <td class="text-right" style="font-weight: 700; font-size: 14px;">
                        ₹{{ number_format($invoice->display_unit_amount * ($invoice->quantity ?: 1), 2) }}
                    </td>
                </tr>

                @if ($invoice->display_discount_amount > 0)
                    <tr style="background: #fdfefe;">
                        <td>
                            <div style="font-weight: 600; color: #15803d;">
                                Discount Applied
                            </div>
                            <div style="font-size: 11.5px; color: var(--muted);">Promotional / License subscription discount</div>
                        </td>
                        <td class="text-center">—</td>
                        <td class="text-right" style="font-weight: 700; color: #15803d;">
                            -₹{{ number_format($invoice->display_discount_amount, 2) }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Totals & Payment Status Grid -->
        <div class="totals-section">
            <!-- Payment Status & Details Box -->
            <div class="payment-box">
                <h4>Payment Details</h4>
                <div style="margin-bottom: 4px;">
                    <strong>Payment status:</strong> 
                    <span style="font-weight: 800; color: {{ $invoice->status === 'paid' ? '#15803d' : '#b45309' }};">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
                <div style="margin-bottom: 4px;">
                    <strong>Payment method:</strong> {{ $invoice->display_payment_method }}
                </div>
                <div style="margin-bottom: 4px;">
                    <strong>Transaction reference:</strong> {{ $invoice->display_payment_reference }}
                </div>
                <div>
                    <strong>Payment date:</strong> {{ $invoice->paid_at ? $invoice->paid_at->format('d M Y') : ($invoice->created_at ? $invoice->created_at->format('d M Y') : now()->format('d M Y')) }}
                </div>
            </div>

            <!-- Financial Totals Table -->
            <div class="totals-table-wrapper">
                <table class="totals-table">
                    @if ($invoice->display_discount_amount > 0)
                        <tr>
                            <td>Gross Amount:</td>
                            <td class="text-right">₹{{ number_format($invoice->display_unit_amount * ($invoice->quantity ?: 1), 2) }}</td>
                        </tr>
                        <tr>
                            <td style="color: #15803d;">Discount:</td>
                            <td class="text-right" style="color: #15803d;">-₹{{ number_format($invoice->display_discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="font-weight: 600;">Subtotal:</td>
                        <td class="text-right" style="font-weight: 600;">₹{{ number_format($invoice->display_subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="color: var(--muted);">GST ({{ $sellerGstin ?? '09FLSPS7380R1ZH' }}):</td>
                        <td class="text-right" style="color: var(--muted); font-weight: 600;">
                            @if ($invoice->display_gst_amount > 0)
                                ₹{{ number_format($invoice->display_gst_amount, 2) }}
                            @else
                                Not charged
                            @endif
                        </td>
                    </tr>
                    <tr class="grand-total">
                        <td>Total payable:</td>
                        <td class="text-right" style="color: var(--green-dark);">₹{{ number_format($invoice->display_total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Terms, Important GST Disclosures & Notes -->
        <div class="notes-section">
            <div style="font-weight: 700; color: var(--ink); margin-bottom: 4px;">Terms and Notes</div>
            <ul>
                <li>This commercial software sales invoice relates to the software product or service specified above.</li>
                <li>Subscription validity and software license terms are governed by the applicable product agreement.</li>
                <li><strong>Seller GSTIN:</strong> <strong>{{ $sellerGstin ?? '09FLSPS7380R1ZH' }}</strong> (APRILO ENTERPRISES trading as APRILO INFOTECH).</li>
                <li>Please retain this invoice for your purchase, business, and accounting records.</li>
            </ul>
            <div style="margin-top: 8px; font-weight: 600; color: #334155;">
                {{ $invoice->notes ?: 'Thank you for choosing Aprilo Infotech. Build Smarter. Grow Faster.' }}
            </div>
        </div>

        <!-- Official Tagline & Brand Trademark Mark -->
        <div class="brand-footer">
            <div class="brand-footer-name">APRILO INFOTECH</div>
            <div class="brand-footer-tagline">Build Smarter. Grow Faster.</div>
        </div>

    </div>

    <!-- Edit Invoice Modal (For updating customer & billing details) -->
    <div id="editModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 100; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
        <div style="background: #ffffff; width: 100%; max-width: 680px; max-height: 90vh; overflow-y: auto; border-radius: 14px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border: 1px solid var(--line); padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--line); padding-bottom: 14px; margin-bottom: 18px;">
                <h3 style="margin: 0; font-size: 18px; font-family: 'Outfit', sans-serif;">Edit Invoice Billing Details ({{ $invoice->invoice_number }})</h3>
                <button type="button" onclick="closeEditModal()" style="border: 0; background: #f1f5f9; width: 30px; height: 30px; border-radius: 6px; cursor: pointer; font-size: 16px;">✕</button>
            </div>

            <form method="POST" action="{{ route('admin.invoices.update', $invoice->id) }}">
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Customer / Business Name *</label>
                        <input type="text" name="customer_name" value="{{ $invoice->display_customer_name }}" required style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Customer ID</label>
                        <input type="text" name="customer_id" value="{{ $invoice->display_customer_id }}" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Customer Email</label>
                        <input type="email" name="customer_email" value="{{ $invoice->display_customer_email }}" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Billing Address</label>
                        <input type="text" name="customer_address" value="{{ $invoice->display_customer_address }}" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Product Name</label>
                        <input type="text" name="product_name" value="{{ $invoice->display_product_name }}" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Plan / License Title</label>
                        <input type="text" name="plan_name" value="{{ $invoice->display_plan_name }}" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Order / License ID</label>
                        <input type="text" name="order_id" value="{{ $invoice->display_order_id }}" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Billing Period Text</label>
                        <input type="text" name="billing_period_text" value="{{ $invoice->display_billing_period }}" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Payment Method</label>
                        <input type="text" name="payment_method" value="{{ $invoice->display_payment_method }}" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Transaction Reference</label>
                        <input type="text" name="payment_reference" value="{{ $invoice->display_payment_reference }}" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; font-size: 13px;">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; border-top: 1px solid var(--line); padding-top: 14px;">
                    <button type="button" onclick="closeEditModal()" style="border: 1px solid #cbd5e1; background: transparent; padding: 8px 16px; border-radius: 6px; cursor: pointer;">Cancel</button>
                    <button type="submit" class="btn" style="padding: 8px 20px;">Save & Update Invoice</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script for Direct PDF Download & Edit Modal -->
    <script>
        function openEditModal() {
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function downloadInvoicePDF() {
            const element = document.getElementById('invoiceDocument');
            const btn = document.getElementById('downloadPdfBtn');
            const origHtml = btn.innerHTML;

            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Downloading PDF...';
            btn.style.opacity = '0.7';
            btn.disabled = true;

            const filename = '{{ $invoice->invoice_number }}_Aprilo_Infotech.pdf';

            const opt = {
                margin: [10, 10, 10, 10],
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { 
                    scale: 2, 
                    useCORS: true, 
                    logging: false,
                    backgroundColor: '#ffffff'
                },
                jsPDF: { 
                    unit: 'mm', 
                    format: 'a4', 
                    orientation: 'portrait' 
                }
            };

            if (typeof html2pdf !== 'undefined') {
                html2pdf().set(opt).from(element).save().then(function() {
                    btn.innerHTML = origHtml;
                    btn.style.opacity = '1';
                    btn.disabled = false;
                }).catch(function(err) {
                    console.error('PDF Generation Error:', err);
                    btn.innerHTML = origHtml;
                    btn.style.opacity = '1';
                    btn.disabled = false;
                    window.print();
                });
            } else {
                btn.innerHTML = origHtml;
                btn.style.opacity = '1';
                btn.disabled = false;
                window.print();
            }
        }
    </script>
</body>
</html>
