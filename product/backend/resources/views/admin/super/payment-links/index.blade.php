@extends('admin.layout')

@section('title', 'Payment Link Generator - Super Admin')

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
        .pl-badge.active { background: #dcfce7; color: #15803d; }
        .pl-badge.disabled { background: #f1f5f9; color: #64748b; }
        .pl-badge.expired { background: #fee2e2; color: #991b1b; }
        .pl-badge.product { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
        .pl-badge.coupon { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-card {
            background: #fff;
            width: 100%;
            max-width: 680px;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--line);
            max-height: 90vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .modal-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-header h3 { margin: 0; font-size: 17px; font-weight: 700; color: var(--ink); }
        .modal-body { padding: 20px 24px; }
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--line);
            background: #f8fafc;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            border-radius: 0 0 12px 12px;
        }

        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .form-label { display: grid; gap: 6px; color: var(--ink); font-size: 13px; font-weight: 700; }
        .form-label input, .form-label select, .form-label textarea {
            width: 100%; min-width: 0; padding: 10px 12px; border: 1px solid var(--line); border-radius: 6px; background: #fff; color: var(--ink); font-size: 13.5px;
        }
        .form-label textarea { min-height: 80px; resize: vertical; }

        @media (max-width: 860px) {
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>

    <div style="margin-bottom: 16px;">
        <a class="button" style="width: auto; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); font-size: 13px; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 8px 14px; border-radius: 6px;" href="{{ route('admin.super.dashboard') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Dashboard
        </a>
    </div>

    <div class="header">
        <div>
            <div class="eyebrow">Financial Operations & Billing</div>
            <h1>Razorpay Payment Link Generator</h1>
            <p class="help">Generate public, branded checkout links for Aprilo products with custom amounts, coupons, user conversion tracking & WhatsApp invoice delivery.</p>
        </div>
        <button type="button" class="button" style="width: auto; background: var(--green); display: inline-flex; align-items: center; gap: 8px;" onclick="openCreateModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            + Generate Payment Link
        </button>
    </div>

    @if (session('status'))
        <div class="card" style="margin-bottom: 18px; border-left: 4px solid var(--green);">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="card" style="margin-bottom: 18px; border-left: 4px solid var(--danger); color: var(--danger);">{{ session('error') }}</div>
    @endif

    <!-- Metrics Grid -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="lbl">Total Links Created</div>
            <div class="val">{{ $totalLinks }}</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Public checkout pages</div>
        </div>
        <div class="stat-card" style="border-left: 3px solid #16a34a;">
            <div class="lbl" style="color: #15803d;">Successful Payments (Passed)</div>
            <div class="val" style="color: #15803d;">{{ $totalPaidTransactions }}</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Total customers paid</div>
        </div>
        <div class="stat-card" style="border-left: 3px solid #dc2626;">
            <div class="lbl" style="color: #dc2626;">Failed / Abandoned</div>
            <div class="val" style="color: #dc2626;">{{ $totalFailedTransactions }}</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Payment verification issues</div>
        </div>
        <div class="stat-card" style="border-left: 3px solid #2563eb;">
            <div class="lbl" style="color: #1d4ed8;">Total Collected Revenue</div>
            <div class="val" style="color: #1d4ed8;">₹{{ number_format($totalRevenue, 2) }}</div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Net Razorpay collections</div>
        </div>
    </div>

    <!-- Payment Links Table -->
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h2 style="font-size: 19px; margin: 0;">Active & Historic Payment Links</h2>
            <span class="help" style="margin: 0;">Copy public link to share directly with clients via WhatsApp or email.</span>
        </div>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Title & Product</th>
                        <th>Amount & Coupon</th>
                        <th>Public Checkout Link</th>
                        <th>Payments Done (Passed / Failed)</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($links as $link)
                        <tr>
                            <td>
                                <strong>{{ $link->title }}</strong>
                                <div style="margin-top: 3px;">
                                    <span class="pl-badge product">{{ $link->product_name }}</span>
                                    <span style="font-size: 11px; color: var(--muted); margin-left: 4px;">{{ $link->product_type }}</span>
                                </div>
                            </td>
                            <td>
                                <strong style="font-size: 15px;">₹{{ number_format($link->base_amount, 2) }}</strong>
                                @if ($link->coupon_code)
                                    <div style="margin-top: 3px;">
                                        <span class="pl-badge coupon">
                                            Code: {{ $link->coupon_code }} ({{ $link->coupon_discount_type === 'percentage' ? $link->coupon_discount_value . '%' : '₹' . number_format($link->coupon_discount_value) }} OFF)
                                        </span>
                                    </div>
                                @endif
                                @if ($link->allow_custom_amount)
                                    <div style="font-size: 10px; color: #2563eb; margin-top: 2px;">Custom amount allowed</div>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <code style="font-size: 12px; background: #f8fafc; padding: 4px 8px; border-radius: 4px; border: 1px solid var(--line);">/pay/{{ $link->slug }}</code>
                                    <button type="button" class="button" style="width: auto; padding: 4px 8px; font-size: 11px; background: #fff; color: var(--ink); border: 1px solid var(--line);" onclick="copyToClipboard('{{ $link->getCheckoutUrl() }}')">
                                        Copy
                                    </button>
                                </div>
                                <div style="margin-top: 4px;">
                                    <a href="{{ $link->getCheckoutUrl() }}" target="_blank" style="font-size: 11px; color: #2563eb; text-decoration: underline;">
                                        Open Checkout Page ↗
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 14px; font-weight: 800; color: #166534;">
                                    {{ $link->passed_count }} Paid (₹{{ number_format($link->total_collected_amount, 2) }})
                                </div>
                                <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">
                                    {{ $link->failed_count }} Failed · {{ $link->views_count }} Views
                                </div>
                            </td>
                            <td>
                                @if ($link->isExpired())
                                    <span class="pl-badge expired">Expired</span>
                                @else
                                    <span class="pl-badge {{ $link->status }}">{{ ucfirst($link->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 12px;">{{ $link->created_at->format('Y-m-d') }}</div>
                                <div style="font-size: 11px; color: var(--muted);">{{ $link->creator?->name ?? 'Admin' }}</div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ route('admin.super.payment-links.show', $link->id) }}" class="button" style="width: auto; padding: 6px 10px; font-size: 12px; background: #fff; color: var(--ink); border: 1px solid var(--line);">
                                        Track ➔
                                    </a>
                                    <form method="POST" action="{{ route('admin.super.payment-links.status', $link->id) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="button" style="width: auto; padding: 6px 10px; font-size: 12px; background: #fff; color: {{ $link->status === 'active' ? '#dc2626' : '#166534' }}; border: 1px solid var(--line);">
                                            {{ $link->status === 'active' ? 'Disable' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--muted); padding: 32px;">
                                No payment links generated yet. Click "+ Generate Payment Link" above to create your first public checkout link.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 14px;">{{ $links->links() }}</div>
    </div>

    <!-- Create Payment Link Modal -->
    <div id="createModal" class="modal-overlay" style="display: none;">
        <div class="modal-card">
            <div class="modal-header">
                <h3>+ Generate Public Razorpay Payment Link</h3>
                <button type="button" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);" onclick="closeCreateModal()">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.super.payment-links.store') }}" id="createLinkForm">
                @csrf
                <div class="modal-body" style="display: grid; gap: 16px;">
                    <div class="form-grid">
                        <label class="form-label">Link Title / Purpose
                            <input name="title" id="linkTitleInput" required placeholder="e.g. Aprilo POS — Annual License" value="{{ old('title') }}">
                        </label>
                        <label class="form-label">Software Product
                            <select name="product_name" id="productSelect" onchange="autoFillProductDetails()">
                                <option value="">-- Custom Software Service --</option>
                                @foreach ($standardProducts as $name => $info)
                                    <option value="{{ $name }}" data-price="{{ $info['default_price'] }}" data-type="{{ $info['type'] }}" data-features="{{ implode("\n", $info['features']) }}">
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="form-grid">
                        <label class="form-label">Product Type / License
                            <input name="product_type" id="productTypeInput" placeholder="e.g. SaaS Subscription or Lifetime License" value="SaaS Subscription">
                        </label>
                        <label class="form-label">Base Amount (₹ INR)
                            <input name="base_amount" id="baseAmountInput" type="number" step="0.01" min="1" required placeholder="5000.00" value="{{ old('base_amount', '5000.00') }}">
                        </label>
                    </div>

                    <div class="form-grid">
                        <label class="form-label">Coupon Code (Optional)
                            <input name="coupon_code" id="couponCodeInput" placeholder="e.g. APRILO500" value="{{ old('coupon_code') }}">
                        </label>
                        <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 8px;">
                            <label class="form-label">Discount Type
                                <select name="coupon_discount_type">
                                    <option value="fixed">Fixed ₹ Off</option>
                                    <option value="percentage">Percentage % Off</option>
                                </select>
                            </label>
                            <label class="form-label">Discount Value
                                <input name="coupon_discount_value" type="number" step="0.01" placeholder="500.00" value="{{ old('coupon_discount_value', '0.00') }}">
                            </label>
                        </div>
                    </div>

                    <label class="form-label">Why they are paying / Description
                        <textarea name="description" id="descriptionInput" placeholder="Brief explanation of the purchase, license validity, or deliverables..."></textarea>
                    </label>

                    <label class="form-label">Features Included on Checkout Page (One per line)
                        <textarea name="features_text" id="featuresInput" placeholder="Lightning-fast barcode billing&#10;Real-time inventory sync & alerts&#10;Commercial invoice with 1-click PDF download"></textarea>
                    </label>

                    <div class="form-grid">
                        <label class="form-label">Expiry Date (Optional)
                            <input name="expires_at" type="date">
                        </label>
                        <label class="form-label">Max Coupon Uses (Optional)
                            <input name="max_coupon_uses" type="number" min="1" placeholder="Unlimited">
                        </label>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid var(--line);">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" name="allow_custom_amount" value="1" style="width: 16px;">
                            Allow customer to enter a custom amount at checkout (e.g. donations or flexible invoice)
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" name="is_reusable" value="1" checked style="width: 16px;">
                            Reusable link (multiple customers can complete payment using this link)
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" name="notify_whatsapp" value="1" checked style="width: 16px;">
                            Automatically dispatch invoice to customer's WhatsApp mobile number once payment is confirmed
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button" style="width: auto; background: #fff; color: var(--ink); border: 1px solid var(--line);" onclick="closeCreateModal()">Cancel</button>
                    <button type="submit" class="button" style="width: auto; background: var(--green);">Generate & Publish Link</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('createModal').style.display = 'flex';
        }

        function closeCreateModal() {
            document.getElementById('createModal').style.display = 'none';
        }

        function autoFillProductDetails() {
            const select = document.getElementById('productSelect');
            const selected = select.selectedOptions[0];
            if (!selected || !selected.value) return;

            const price = selected.dataset.price;
            const type = selected.dataset.type;
            const features = selected.dataset.features;
            const titleInput = document.getElementById('linkTitleInput');

            if (price) document.getElementById('baseAmountInput').value = price;
            if (type) document.getElementById('productTypeInput').value = type;
            if (features) document.getElementById('featuresInput').value = features;
            if (titleInput && (!titleInput.value || titleInput.value.includes('Aprilo') || titleInput.value.includes('TradeFlow'))) {
                titleInput.value = `${selected.value} — ${type}`;
            }
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Public payment link copied to clipboard!\n' + text);
            }).catch(() => {
                prompt('Copy this link:', text);
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeCreateModal();
        });
    </script>
@endsection

