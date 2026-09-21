@extends('admin.layout')

@section('title', 'E-commerce Plugin Licenses')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow" style="color: #2563eb;">Plugin Extensions</div>
            <h1>Plugin & Module License Keys</h1>
            <p class="help">Generate license keys for authenticating our official Magento 2 and Shopify AI Chatbot extensions.</p>
        </div>
    </div>

    <!-- Generate License Key Form -->
    <form class="card" method="POST" action="{{ route('admin.ecommerce.licenses.store') }}" style="margin-bottom: 24px;">
        @csrf
        <h3 style="font-size: 16px; font-weight: 700; margin: 0 0 16px 0;">Issue New Plugin License</h3>
        
        <div class="grid grid-2" style="margin-bottom: 16px;">
            <label class="field">
                Target Platform
                <select name="platform" required style="background: #fff;">
                    <option value="magento">Magento 2 Module</option>
                    <option value="shopify">Shopify App</option>
                    <option value="woocommerce">WooCommerce Plugin</option>
                </select>
            </label>

            <label class="field">
                Licensed Domain (Optional)
                <input name="domain" placeholder="e.g. store.mydomain.com">
            </label>

            <label class="field">
                Associated Connected Store
                <select name="connection_id" style="background: #fff;">
                    <option value="">-- Standalone License --</option>
                    @foreach ($connections as $conn)
                        <option value="{{ $conn->id }}">{{ $conn->store_name }} ({{ strtoupper($conn->platform) }})</option>
                    @endforeach
                </select>
            </label>

            <label class="field">
                Max Allowed Stores / Domains
                <input name="max_stores" type="number" min="1" max="50" value="1" required>
            </label>
        </div>

        <div style="display: flex; justify-content: flex-end;">
            <button class="button" type="submit" style="width: auto; padding: 10px 20px; background: #2563eb;">🔑 Generate License Key</button>
        </div>
    </form>

    <!-- Issued Licenses Table -->
    <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 16px;">Active License Keys</h2>
    <table>
        <thead>
            <tr>
                <th>License Key</th>
                <th>Platform</th>
                <th>Domain Restriction</th>
                <th>Max Stores</th>
                <th>Expires</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($licenses as $lic)
                <tr>
                    <td>
                        <strong style="font-family: monospace; font-size: 13px; background: #eff6ff; color: #1e40af; padding: 4px 8px; border-radius: 4px; border: 1px solid #bfdbfe;">
                            {{ $lic->license_key }}
                        </strong>
                    </td>
                    <td>
                        <span class="pill" style="background: rgba(37, 99, 235, 0.12); color: #2563eb; font-weight: 700; text-transform: uppercase;">
                            {{ $lic->platform }}
                        </span>
                    </td>
                    <td>{{ $lic->domain ?? 'Any authorized domain' }}</td>
                    <td style="font-weight: 600;">{{ $lic->max_stores }} store(s)</td>
                    <td>{{ $lic->expires_at ? $lic->expires_at->format('M d, Y') : 'Lifetime' }}</td>
                    <td>
                        <span class="pill" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-weight: 700; text-transform: uppercase;">
                            {{ $lic->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No plugin licenses generated yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
