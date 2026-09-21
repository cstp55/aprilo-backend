@extends('admin.layout')

@section('title', 'E-commerce Platforms & Store Connectors')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow" style="color: #2563eb;">Platform Connectors</div>
            <h1>Connect E-commerce Stores</h1>
            <p class="help">Configure API credentials for Magento 2, Shopify, WooCommerce, and custom stores.</p>
        </div>
    </div>

    <!-- Connection Form -->
    <form class="card" method="POST" action="{{ route('admin.ecommerce.platforms.store') }}" style="margin-bottom: 24px;">
        @csrf
        <h3 style="font-size: 16px; font-weight: 700; margin: 0 0 16px 0;">New Platform Connection</h3>
        
        <div class="grid grid-2" style="margin-bottom: 16px;">
            <label class="field">
                E-commerce Platform
                <select name="platform" required style="background: #fff;">
                    <option value="magento">Magento 2 (Adobe Commerce)</option>
                    <option value="shopify">Shopify</option>
                    <option value="woocommerce">WooCommerce (WordPress)</option>
                    <option value="custom">Custom E-commerce API</option>
                </select>
            </label>

            <label class="field">
                Store Name
                <input name="store_name" placeholder="e.g. My Style Boutique" required>
            </label>

            <label class="field">
                Store URL
                <input name="store_url" type="url" placeholder="https://store.example.com" required>
            </label>

            <label class="field">
                Auto-Sync Interval
                <select name="sync_interval" style="background: #fff;">
                    <option value="hourly">Hourly Sync</option>
                    <option value="daily" selected>Daily Sync</option>
                    <option value="realtime">Real-time Webhook</option>
                </select>
            </label>

            <label class="field">
                API Key / Consumer Key
                <input name="api_key" placeholder="Enter API Key">
            </label>

            <label class="field">
                API Secret / Consumer Secret
                <input name="api_secret" type="password" placeholder="Enter API Secret">
            </label>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--line); padding-top: 16px;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                <input type="checkbox" name="auto_sync_enabled" value="1" checked style="width: auto;">
                Enable Automated Background Sync (Products & Inventory)
            </label>
            <button class="button" type="submit" style="width: auto; padding: 10px 20px; background: #2563eb;">Connect Store</button>
        </div>
    </form>

    <!-- Connected Stores Table -->
    <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 16px;">Existing Store Integrations</h2>
    <table>
        <thead>
            <tr>
                <th>Platform</th>
                <th>Store Name</th>
                <th>Store URL</th>
                <th>Products Count</th>
                <th>Orders Count</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($connections as $conn)
                <tr>
                    <td>
                        <span class="pill" style="background: rgba(37, 99, 235, 0.15); color: #2563eb; font-weight: 800; text-transform: uppercase;">
                            {{ $conn->platform }}
                        </span>
                    </td>
                    <td><strong>{{ $conn->store_name }}</strong></td>
                    <td><a href="{{ $conn->store_url }}" target="_blank" style="color: #2563eb; text-decoration: underline;">{{ $conn->store_url }}</a></td>
                    <td style="font-weight: 600;">{{ $conn->products_count }} products</td>
                    <td style="font-weight: 600;">{{ $conn->orders_count }} orders</td>
                    <td>
                        <span class="pill" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-weight: 700; text-transform: uppercase;">
                            {{ $conn->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No store platforms connected yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
