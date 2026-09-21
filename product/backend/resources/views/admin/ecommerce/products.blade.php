@extends('admin.layout')

@section('title', 'E-commerce Product Catalog')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow" style="color: #2563eb;">Product Intelligence</div>
            <h1>Synced Product Catalog</h1>
            <p class="help">Products ingested from Magento, Shopify, and WooCommerce with vector embeddings for AI recommendations.</p>
        </div>
    </div>

    <!-- Filter Form -->
    <form class="card" method="GET" action="{{ route('admin.ecommerce.products') }}" style="margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 16px; align-items: end;">
            <label class="field">
                Store Connection
                <select name="connection_id" style="background: #fff;">
                    <option value="">All Connected Stores</option>
                    @foreach ($connections as $conn)
                        <option value="{{ $conn->id }}" @selected(request('connection_id') === $conn->id)>
                            {{ $conn->store_name }} ({{ strtoupper($conn->platform) }})
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                Search SKU or Title
                <input name="search" value="{{ request('search') }}" placeholder="e.g. Denim Jacket, SC-DNM-001">
            </label>
            <div style="display: flex; gap: 8px;">
                <button class="button" type="submit" style="width: auto; padding: 10px 18px; background: #2563eb;">Filter</button>
                <a href="{{ route('admin.ecommerce.products') }}" class="button" style="width: auto; padding: 10px 18px; background: transparent; border: 1px solid var(--line); color: var(--ink);">Reset</a>
            </div>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Title</th>
                <th>Store Platform</th>
                <th>Price</th>
                <th>Stock</th>
                <th>AI Embedding</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $prod)
                <tr>
                    <td><strong>{{ $prod->sku ?? 'N/A' }}</strong></td>
                    <td style="max-width: 320px;">
                        <div style="font-weight: 600;">{{ $prod->title }}</div>
                        <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">{{ Str::limit($prod->description, 80) }}</div>
                    </td>
                    <td>
                        <span class="pill" style="background: rgba(37, 99, 235, 0.12); color: #2563eb; font-weight: 700; font-size: 11px; text-transform: uppercase;">
                            {{ $prod->connection->platform ?? 'Store' }}
                        </span>
                    </td>
                    <td style="font-weight: 700; color: var(--ink);">${{ number_format($prod->price, 2) }}</td>
                    <td style="font-weight: 600;">{{ $prod->inventory_quantity }} units</td>
                    <td>
                        @if (! empty($prod->embedding))
                            <span class="pill" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-weight: 700; font-size: 11px;">✓ Vectorized</span>
                        @else
                            <span class="pill" style="background: rgba(245, 158, 11, 0.15); color: #d97706; font-size: 11px;">Pending Vector</span>
                        @endif
                    </td>
                    <td>
                        <span class="pill" style="font-size: 11px; text-transform: uppercase;">{{ $prod->status }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No products found. Please sync products from your connected store platform.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 18px;">
        {{ $products->links() }}
    </div>
@endsection
