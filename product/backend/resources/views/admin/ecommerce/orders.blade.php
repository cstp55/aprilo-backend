@extends('admin.layout')

@section('title', 'E-commerce Orders & Order Ingestion')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow" style="color: #2563eb;">Orders & Fulfillment</div>
            <h1>Synchronized Customer Orders</h1>
            <p class="help">Order data synced from Magento and Shopify enabling the AI chatbot to provide real-time order status updates to shoppers.</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Store Platform</th>
                <th>Customer</th>
                <th>Total Amount</th>
                <th>Order Status</th>
                <th>Financial Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $ord)
                <tr>
                    <td><strong>{{ $ord->order_number }}</strong></td>
                    <td>
                        <span class="pill" style="background: rgba(37, 99, 235, 0.12); color: #2563eb; font-weight: 700; font-size: 11px; text-transform: uppercase;">
                            {{ $ord->connection->platform ?? 'Store' }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 600;">{{ $ord->customer_name ?? 'Guest Buyer' }}</div>
                        <div style="font-size: 11px; color: var(--muted);">{{ $ord->customer_email }}</div>
                    </td>
                    <td style="font-weight: 700;">${{ number_format($ord->total_amount, 2) }} {{ $ord->currency }}</td>
                    <td>
                        <span class="pill" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-weight: 700; font-size: 11px; text-transform: uppercase;">
                            {{ $ord->order_status }}
                        </span>
                    </td>
                    <td>
                        <span class="pill" style="font-size: 11px; text-transform: uppercase;">{{ $ord->financial_status ?? 'paid' }}</span>
                    </td>
                    <td>{{ $ord->created_at ? $ord->created_at->format('M d, Y h:i A') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No customer orders synced yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 18px;">
        {{ $orders->links() }}
    </div>
@endsection
