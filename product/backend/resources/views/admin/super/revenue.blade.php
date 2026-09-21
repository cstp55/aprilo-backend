@extends('admin.layout')

@section('title', 'Super Admin - Global Revenue')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow" style="color: #10b981;">Financial Intelligence</div>
            <h1>Global Platform Revenue</h1>
            <p class="help">Aggregate subscription payments and invoice transaction history across all tenant organizations.</p>
        </div>
    </div>

    <div class="grid grid-2" style="margin-bottom: 24px;">
        <div class="card" style="border-left: 4px solid #10b981;">
            <div class="metric">${{ number_format($totalPaid, 2) }}</div>
            <div class="label">Total Paid Invoices</div>
        </div>
        <div class="card" style="border-left: 4px solid #f59e0b;">
            <div class="metric">${{ number_format($totalPending, 2) }}</div>
            <div class="label">Pending Receivables</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Organization</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Issued Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoices as $inv)
                <tr>
                    <td><strong>{{ $inv->invoice_number }}</strong></td>
                    <td>{{ $inv->organization->name ?? 'Unknown' }}</td>
                    <td style="font-weight: 700;">${{ number_format($inv->amount_cents / 100, 2) }}</td>
                    <td>
                        <span class="pill" style="{{ $inv->status === 'paid' ? 'background: rgba(16, 185, 129, 0.15); color: #059669;' : 'background: rgba(245, 158, 11, 0.15); color: #d97706;' }} font-size: 11px; text-transform: uppercase;">
                            {{ $inv->status }}
                        </span>
                    </td>
                    <td>{{ $inv->created_at ? $inv->created_at->format('M d, Y') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No invoices generated yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 18px;">
        {{ $invoices->links() }}
    </div>
@endsection
