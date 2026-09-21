@extends('admin.layout')

@section('title', 'E-commerce Store & Catalog Dashboard')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow" style="color: #2563eb;">E-commerce Operations</div>
            <h1>Store & AI Product Catalog Dashboard</h1>
            <p class="help">Monitor connected Magento, Shopify, and WooCommerce stores, product vectorization, and order synchronization.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.ecommerce.platforms') }}" class="button" style="width: auto; padding: 10px 16px; background: #2563eb;">+ Connect Store</a>
            <a href="{{ route('admin.ecommerce.licenses') }}" class="button" style="width: auto; padding: 10px 16px; background: transparent; border: 1px solid #2563eb; color: #2563eb;">🔑 Plugin Licenses</a>
        </div>
    </div>

    <!-- Ecom Metrics Grid -->
    <div class="grid grid-4" style="margin-bottom: 24px;">
        <div class="card" style="border-left: 4px solid #2563eb;">
            <div class="metric">{{ $connectionsCount }}</div>
            <div class="label">Connected Stores</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Magento, Shopify, WooCommerce</div>
        </div>
        <div class="card" style="border-left: 4px solid #10b981;">
            <div class="metric">{{ $productsCount }}</div>
            <div class="label">Synced Products</div>
            <div style="font-size: 11px; color: #10b981; margin-top: 4px; font-weight: 700;">100% Vectorized for AI</div>
        </div>
        <div class="card" style="border-left: 4px solid #f59e0b;">
            <div class="metric">{{ $ordersCount }}</div>
            <div class="label">Synced Orders</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Live tracking active</div>
        </div>
        <div class="card" style="border-left: 4px solid #8b5cf6;">
            <div class="metric">{{ $licensesCount }}</div>
            <div class="label">Active Plugin Licenses</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Magento & Shopify extensions</div>
        </div>
    </div>

    <!-- Connected Platforms Overview -->
    <div style="margin-top: 28px;">
        <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 16px;">Active Store Integrations</h2>
        
        <div class="grid grid-2" style="margin-bottom: 28px;">
            @forelse ($connections as $conn)
                <div class="card" style="border-top: 4px solid #2563eb; position: relative;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <span class="pill" style="background: rgba(37, 99, 235, 0.15); color: #2563eb; font-weight: 800; text-transform: uppercase; font-size: 11px;">
                                {{ $conn->platform }}
                            </span>
                            <h3 style="font-size: 18px; font-weight: 700; margin: 8px 0 4px 0;">{{ $conn->store_name }}</h3>
                            <a href="{{ $conn->store_url }}" target="_blank" style="font-size: 12px; color: #2563eb; text-decoration: underline;">{{ $conn->store_url }}</a>
                        </div>
                        <span class="pill" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-weight: 700; font-size: 11px; text-transform: uppercase;">
                            {{ $conn->status }}
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 16px 0; background: #f8fafc; padding: 12px; border-radius: 6px;">
                        <div>
                            <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700;">Products</div>
                            <div style="font-size: 18px; font-weight: 800; color: var(--ink);">{{ $conn->products_count }}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700;">Orders</div>
                            <div style="font-size: 18px; font-weight: 800; color: var(--ink);">{{ $conn->orders_count }}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700;">Sync Schedule</div>
                            <div style="font-size: 14px; font-weight: 700; color: var(--ink); text-transform: capitalize;">{{ $conn->sync_interval }}</div>
                        </div>
                    </div>

                    <div style="font-size: 12px; color: var(--muted); margin-bottom: 16px;">
                        🕒 Last Synced: {{ $conn->last_synced_at ? $conn->last_synced_at->diffForHumans() : 'Never' }}
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <form method="POST" action="{{ route('admin.ecommerce.products.sync', $conn) }}" style="flex: 1;">
                            @csrf
                            <button class="button" type="submit" style="padding: 8px; font-size: 12px; background: #2563eb;">⚡ Sync Products</button>
                        </form>
                        <form method="POST" action="{{ route('admin.ecommerce.orders.sync', $conn) }}" style="flex: 1;">
                            @csrf
                            <button class="button" type="submit" style="padding: 8px; font-size: 12px; background: #10b981;">📦 Sync Orders</button>
                        </form>
                        <form method="POST" action="{{ route('admin.ecommerce.embeddings.refresh', $conn) }}" style="flex: 1;">
                            @csrf
                            <button class="button" type="submit" style="padding: 8px; font-size: 12px; background: transparent; border: 1px solid #8b5cf6; color: #8b5cf6;">✨ Embeddings</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="card" style="grid-column: span 2; text-align: center; padding: 40px 20px;">
                    <div style="font-size: 32px; margin-bottom: 10px;">🛍️</div>
                    <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 8px 0;">No E-commerce Stores Connected</h3>
                    <p style="font-size: 13px; color: var(--muted); margin: 0 0 16px 0;">Connect your Magento, Shopify, or WooCommerce store to ingest product catalogs and orders.</p>
                    <a href="{{ route('admin.ecommerce.platforms') }}" class="button" style="display: inline-block; width: auto; padding: 10px 20px; background: #2563eb;">Connect Store Now</a>
                </div>
            @endforelse
        </div>
    </div>
@endsection
