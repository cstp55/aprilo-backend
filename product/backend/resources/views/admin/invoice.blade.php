<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }} - Aprilo AI</title>
    <style>
        :root {
            --green: #d22630; /* Align with corporate palette */
            --ink: #1c1f23;
            --muted: #58625f;
            --line: #ded7ca;
            --bg: #faf8f5;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--ink);
            background: #ffffff;
            margin: 0;
            padding: 40px;
            font-size: 14px;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--line);
            padding-bottom: 24px;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 22px;
            font-weight: 800;
            color: var(--green);
            letter-spacing: -0.02em;
        }

        .invoice-details {
            text-align: right;
        }

        .invoice-details h1 {
            font-size: 24px;
            margin: 0 0 8px;
            color: var(--ink);
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .address-box strong {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--line);
        }

        th {
            font-weight: 700;
            color: var(--muted);
            background: #fafafa;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            margin-left: auto;
            width: 300px;
        }

        .totals table td {
            border-bottom: 0;
            padding: 8px 12px;
        }

        .totals table tr.grand-total td {
            border-top: 2px solid var(--line);
            font-size: 16px;
            font-weight: 700;
        }

        .footer {
            margin-top: 60px;
            border-top: 1px solid var(--line);
            padding-top: 20px;
            text-align: center;
            font-size: 12px;
            color: var(--muted);
        }

        .actions {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            background: var(--green);
            color: white;
            padding: 10px 20px;
            border: 0;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--line);
            color: var(--ink);
        }

        @media print {
            .actions {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Actions (Print/Back) -->
        <div class="actions">
            <button onclick="window.print()" class="btn">Print Invoice / Save PDF</button>
            <a href="{{ route('admin.settings', ['tab' => 'pricing']) }}" class="btn btn-secondary">← Back to Settings</a>
        </div>

        <div class="header">
            <div>
                <div class="logo">Aprilo AI</div>
                <div style="margin-top: 8px; color: var(--muted); font-size: 12px;">
                    Enterprise AI Operating Layer<br>
                    support@aprilo.ai
                </div>
            </div>
            <div class="invoice-details">
                <h1>INVOICE</h1>
                <div style="font-size: 13px; color: var(--muted);">
                    Invoice #: <strong>{{ $invoice->invoice_number }}</strong><br>
                    Date: {{ $invoice->created_at->format('M d, Y') }}<br>
                    Due Date: {{ $invoice->due_date->format('M d, Y') }}
                </div>
            </div>
        </div>

        <div class="grid">
            <div class="address-box">
                <strong>Billed To:</strong>
                <strong>{{ $organization->name }}</strong>
                <div style="color: var(--muted); font-size: 13px; margin-top: 4px;">
                    Plan: <span style="text-transform: capitalize;">{{ $organization->plan }}</span><br>
                    Subdomain: {{ $organization->subdomain ?: 'N/A' }}
                </div>
            </div>
            <div class="address-box">
                <strong>Billed By:</strong>
                <strong>Aprilo Technologies Inc.</strong>
                <div style="color: var(--muted); font-size: 13px; margin-top: 4px;">
                    100 Pine Street, Suite 1200<br>
                    San Francisco, CA 94111
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>Billing Mode</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        @if ($invoice->billing_mode === 'pay_as_you_go')
                            <strong>Pay-As-You-Go Monthly Metered Usage</strong>
                            <div style="font-size: 12px; color: var(--muted); margin-top: 4px;">Includes all processed employee queries and whitelabel webhook notifications.</div>
                        @else
                            <strong>Monthly {{ ucfirst($organization->plan) }} Plan Subscription Fee</strong>
                            <div style="font-size: 12px; color: var(--muted); margin-top: 4px;">Access fee for advanced integrations, whitelabeling, and high reasoning features.</div>
                        @endif
                    </td>
                    <td>
                        <span style="text-transform: capitalize;">{{ str_replace('_', ' ', $invoice->billing_mode) }}</span>
                    </td>
                    <td class="text-right">${{ number_format($invoice->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">${{ number_format($invoice->amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Tax (0%):</td>
                    <td class="text-right">$0.00</td>
                </tr>
                <tr class="grand-total">
                    <td>Total Due:</td>
                    <td class="text-right">${{ number_format($invoice->amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            Thank you for partnering with Aprilo AI. If you have questions regarding this invoice, please contact support@aprilo.ai.<br>
            <span style="display: block; margin-top: 10px; font-weight: bold; color: var(--green);">Paid via Credit Card Card ending in **** (Auto-debit)</span>
        </div>
    </div>
</body>
</html>
