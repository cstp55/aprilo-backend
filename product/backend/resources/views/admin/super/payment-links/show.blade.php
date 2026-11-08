@extends('admin.layout')

@section('title', "Track Payment Link: {$paymentLink->title} - Super Admin")

@section('content')
    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
        }
        .stat-card .val {
            font-size: 26px;
            font-weight: 800;
            color: var(--ink);
            font-family: 'Outfit', sans-serif;
            margin-top: 4px;
        }
        .stat-card .lbl {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
        }

        .pl-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }
        .pl-badge.passed { background: #dcfce7; color: #15803d; }
        .pl-badge.failed { background: #fee2e2; color: #991b1b; }
        .pl-badge.pending { background: #fef3c7; color: #b45309; }
        .pl-badge.product { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
        .pl-badge.coupon { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }

        @media (max-width: 860px) {
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>

    <div style="margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
        <a class="button" style="width: auto; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); font-size: 13px; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 8px 14px; border-radius: 6px;" href="{{ route('admin.super.payment-links') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to All Payment Links
        </a>

        <div style="display: flex; gap: 8px;">
            <a href="{{ $paymentLink->getCheckoutUrl() }}" target="_blank" class="button" style="width: auto; background: #fff; color: var(--ink); border: 1px solid var(--line); font-size: 13px; padding: 8px 14px;">
                Open Public Checkout ↗
            </a>
            <button type="button" class="button" style="width: auto; background: var(--green); font-size: 13px; padding: 8px 14px;" onclick="copyToClipboard('{{ $paymentLink->getCheckoutUrl() }}')">
                📋 Copy Link URL
            </button>
        </div>
    </div>

    <div class="header">
        <div>
            <div class="eyebrow">Payment Link Analytics & Tracking</div>
            <h1>{{ $paymentLink->title }}</h1>
            <p class="help">Tracking user checkout interactions, passed vs failed payment status, and automated WhatsApp invoice dispatches.</p>
        </div>
        <div>
            <span class="pl-badge product" style="font-size: 13px; padding: 6px 12px;">{{ $paymentLink->product_name }}</span>
            <span class="pl-badge {{ $paymentLink->status }}" style="font-size: 13px; padding: 6px 12px; margin-left: 6px;">
                {{ ucfirst($paymentLink->status) }}
            </span>
        </div>
    </div>

    @if (session('status'))
        <div class="card" style="margin-bottom: 18px; border-left: 4px solid var(--green);">{{ session('status') }}</div>
    @endif

    <!-- Metrics Grid -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="lbl">Total Link Visits</div>
            <div class="val">{{ $paymentLink->views_count }}</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Public checkout impressions</div>
        </div>
        <div class="stat-card" style="border-left: 3px solid #16a34a;">
            <div class="lbl" style="color: #15803d;">Users Completed (Passed)</div>
            <div class="val" style="color: #15803d;">{{ $passedCount }}</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Verified Razorpay orders</div>
        </div>
        <div class="stat-card" style="border-left: 3px solid #dc2626;">
            <div class="lbl" style="color: #dc2626;">Failed Payment Attempts</div>
            <div class="val" style="color: #dc2626;">{{ $failedCount }}</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Declined or unverified</div>
        </div>
        <div class="stat-card" style="border-left: 3px solid #2563eb;">
            <div class="lbl" style="color: #1d4ed8;">Total Collected Amount</div>
            <div class="val" style="color: #1d4ed8;">₹{{ number_format($totalCollected, 2) }}</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Net via this link</div>
        </div>
    </div>

    <!-- Link Details Card -->
    <div class="card" style="margin-bottom: 24px;">
        <h3 style="margin-top: 0; font-size: 16px;">Link Configuration & Checkout Setup</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 14px;">
            <div>
                <span class="lbl" style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase;">Public URL</span>
                <div style="margin-top: 4px;"><code style="font-size: 13px;">{{ $paymentLink->getCheckoutUrl() }}</code></div>
            </div>
            <div>
                <span class="lbl" style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase;">Base Amount</span>
                <div style="margin-top: 4px; font-size: 16px; font-weight: 800;">₹{{ number_format($paymentLink->base_amount, 2) }}</div>
            </div>
            <div>
                <span class="lbl" style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase;">Coupon Discount</span>
                <div style="margin-top: 4px;">
                    @if ($paymentLink->coupon_code)
                        <span class="pl-badge coupon">
                            {{ $paymentLink->coupon_code }} ({{ $paymentLink->coupon_discount_type === 'percentage' ? $paymentLink->coupon_discount_value . '%' : '₹' . number_format($paymentLink->coupon_discount_value) }} OFF)
                        </span>
                        <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">
                            Used {{ $paymentLink->coupon_uses_count }} @if($paymentLink->max_coupon_uses)/ {{ $paymentLink->max_coupon_uses }}@endif times
                        </div>
                    @else
                        <span style="color: var(--muted); font-size: 13px;">No coupon configured</span>
                    @endif
                </div>
            </div>
            <div>
                <span class="lbl" style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase;">WhatsApp Delivery</span>
                <div style="margin-top: 4px; font-size: 13px; font-weight: 600; color: {{ $paymentLink->notify_whatsapp ? '#15803d' : 'var(--muted)' }};">
                    {{ $paymentLink->notify_whatsapp ? '✓ Auto-dispatch invoice to WhatsApp' : 'Disabled' }}
                </div>
            </div>
        </div>

        @if ($paymentLink->description)
            <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--line); font-size: 13.5px; color: var(--ink);">
                <strong>Why they are paying:</strong> {{ $paymentLink->description }}
            </div>
        @endif

        @if (!empty($paymentLink->features))
            <div style="margin-top: 10px; font-size: 13px; color: var(--muted);">
                <strong>Features shown to user:</strong>
                <ul style="margin: 4px 0 0 18px; padding: 0;">
                    @foreach ($paymentLink->features as $feat)
                        <li>{{ $feat }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Customer Payment Transactions Table (Tracking Status: Passed / Failed) -->
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h2 style="font-size: 19px; margin: 0;">Customer Payment Transactions (Passed / Failed Tracking)</h2>
            <span class="help" style="margin: 0;">Shows every user who initiated or completed checkout on this link.</span>
        </div>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Customer / Contact</th>
                        <th>Amount Paid</th>
                        <th>Coupon Used</th>
                        <th>Payment Status</th>
                        <th>Razorpay Ref</th>
                        <th>Invoice Generated</th>
                        <th>WhatsApp Delivery</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $txn)
                        <tr>
                            <td>
                                <div>{{ $txn->paid_at?->format('Y-m-d H:i') ?? $txn->created_at->format('Y-m-d H:i') }}</div>
                                <div style="font-size: 11px; color: var(--muted);">Ref: {{ $txn->transaction_reference }}</div>
                            </td>
                            <td>
                                <strong>{{ $txn->customer_name }}</strong>
                                <div style="font-size: 12px; color: var(--ink);">WhatsApp: <code>{{ $txn->customer_phone }}</code></div>
                                @if ($txn->customer_email)
                                    <div style="font-size: 11px; color: var(--muted);">{{ $txn->customer_email }}</div>
                                @endif
                                @if ($txn->customer_company)
                                    <div style="font-size: 11px; color: var(--muted);">Company: {{ $txn->customer_company }}</div>
                                @endif
                            </td>
                            <td>
                                <strong style="font-size: 15px; color: {{ $txn->status === 'passed' ? '#166534' : 'var(--ink)' }};">
                                    ₹{{ number_format($txn->amount_paid, 2) }}
                                </strong>
                                @if ($txn->discount_amount > 0)
                                    <div style="font-size: 10px; color: #b45309;">Disc: -₹{{ number_format($txn->discount_amount, 2) }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($txn->coupon_code)
                                    <span class="pl-badge coupon">{{ $txn->coupon_code }}</span>
                                @else
                                    <span style="color: var(--muted); font-size: 12px;">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($txn->status === 'passed')
                                    <span class="pl-badge passed">✓ PASSED</span>
                                @elseif ($txn->status === 'failed')
                                    <span class="pl-badge failed">✕ FAILED</span>
                                    @if ($txn->error_message)
                                        <div style="font-size: 10px; color: #dc2626; max-width: 180px; margin-top: 2px;">
                                            {{ $txn->error_message }}
                                        </div>
                                    @endif
                                @else
                                    <span class="pl-badge pending">⏳ PENDING</span>
                                @endif
                            </td>
                            <td>
                                @if ($txn->razorpay_payment_id)
                                    <code style="font-size: 11px;">{{ $txn->razorpay_payment_id }}</code>
                                @else
                                    <span style="color: var(--muted); font-size: 12px;">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($txn->invoice)
                                    <a href="{{ route('admin.invoices.show', $txn->invoice->id) }}" target="_blank" style="font-size: 12px; font-weight: 700; color: #2563eb; text-decoration: underline;">
                                        {{ $txn->invoice->invoice_number }} ↗
                                    </a>
                                @else
                                    <span style="color: var(--muted); font-size: 12px;">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($txn->whatsapp_sent)
                                    <span class="pl-badge passed" title="Message ID: {{ $txn->whatsapp_message_id }}">
                                        ✓ Sent to WhatsApp
                                    </span>
                                @elseif ($txn->status === 'passed')
                                    <span class="pl-badge pending" title="{{ $txn->whatsapp_error }}">
                                        Pending / Simulated
                                    </span>
                                @else
                                    <span style="color: var(--muted); font-size: 12px;">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--muted); padding: 32px;">
                                No checkout attempts or payments recorded on this link yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 14px;">{{ $transactions->links() }}</div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Public payment link copied to clipboard!\n' + text);
            }).catch(() => {
                prompt('Copy this link:', text);
            });
        }
    </script>
@endsection

