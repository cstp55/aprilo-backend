<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Successful — Aprilo Infotech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">

    <style>
        :root {
            --green: #22C55E;
            --green-dark: #16A34A;
            --green-deep: #065F46;
            --ink: #1F2937;
            --muted: #6B7280;
            --surface: #FFFFFF;
            --paper: #F8FAFC;
            --line: #E2E8F0;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f1f5f9;
            color: var(--ink);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .success-card {
            background: #fff;
            max-width: 580px;
            width: 100%;
            border-radius: 16px;
            border: 1px solid var(--line);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            padding: 40px;
            text-align: center;
        }

        .success-icon {
            width: 64px;
            height: 64px;
            background: #dcfce7;
            color: #16a34a;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 0 0 8px #f0fdf4;
        }

        .brand-sub {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
            margin-bottom: 4px;
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 800;
            margin: 0 0 8px;
            color: var(--ink);
        }

        .subtitle {
            font-size: 14px;
            color: #64748b;
            margin: 0 0 24px;
            line-height: 1.5;
        }

        .details-box {
            background: #f8fafc;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            text-align: left;
            display: grid;
            gap: 12px;
            font-size: 13.5px;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #475569;
        }
        .details-row strong {
            color: var(--ink);
        }

        .btn-invoice {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: var(--green);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            padding: 13px 20px;
            border-radius: 8px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.25);
            transition: all 0.2s ease;
            margin-bottom: 14px;
        }
        .btn-invoice:hover {
            background: var(--green-dark);
            transform: translateY(-1px);
        }

        .wa-banner {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            color: #166534;
            text-align: left;
            line-height: 1.4;
            margin-bottom: 20px;
        }

        .footer-note {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>

        <div class="brand-sub">APRILO INFOTECH · APRILO ENTERPRISES</div>
        <h1>Payment Successful!</h1>
        <p class="subtitle">Thank you for your purchase, <strong>{{ $transaction->customer_name }}</strong>. Your transaction has been verified and processed.</p>

        <!-- Transaction Details Box -->
        <div class="details-box">
            <div class="details-row">
                <span>Product / Plan:</span>
                <strong>{{ $link->product_name }} — {{ $link->title }}</strong>
            </div>
            <div class="details-row">
                <span>Total Amount Paid:</span>
                <strong style="color: #166534; font-size: 16px;">₹{{ number_format($transaction->amount_paid, 2) }}</strong>
            </div>
            @if ($transaction->coupon_code)
                <div class="details-row">
                    <span>Coupon Applied:</span>
                    <strong style="color: #b45309;">{{ $transaction->coupon_code }} (-₹{{ number_format($transaction->discount_amount, 2) }})</strong>
                </div>
            @endif
            @if ($invoice)
                <div class="details-row">
                    <span>Sales Invoice No:</span>
                    <strong><code>{{ $invoice->invoice_number }}</code></strong>
                </div>
            @endif
            <div class="details-row">
                <span>Payment Reference:</span>
                <strong style="font-family: monospace;">{{ $transaction->razorpay_payment_id ?: $transaction->transaction_reference }}</strong>
            </div>
            <div class="details-row">
                <span>Payment Date:</span>
                <strong>{{ $transaction->paid_at ? $transaction->paid_at->format('d M Y, h:i A') : now()->format('d M Y') }}</strong>
            </div>
        </div>

        <!-- WhatsApp Delivery Confirmation -->
        <div class="wa-banner">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
            <div>
                <strong>WhatsApp Invoice Delivered:</strong>
                <div>A confirmation copy and invoice download link have been sent to <strong>+{{ $transaction->customer_phone }}</strong>.</div>
            </div>
        </div>

        @if ($invoice)
            <a href="{{ route('public.pay.invoice', $invoice->id) }}" target="_blank" class="btn-invoice">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                View & Download Sales Invoice (PDF)
            </a>
        @endif

        <div class="footer-note">
            Need assistance with your license or onboarding?<br>
            Reach out to <a href="mailto:contact@apriloinfotech.com" style="color: #2563eb; font-weight: 600;">contact@apriloinfotech.com</a> or visit <a href="https://apriloinfotech.com" target="_blank" style="color: #2563eb; font-weight: 600;">apriloinfotech.com</a>
        </div>
    </div>
</body>
</html>

