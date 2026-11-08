<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout: {{ $link->title }} — Aprilo Infotech</title>
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
            --danger: #DC2626;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f1f5f9;
            color: var(--ink);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .checkout-header {
            background: #fff;
            border-bottom: 1px solid var(--line);
            padding: 16px 24px;
        }
        .header-content {
            max-width: 1080px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .brand-block {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }
        .brand-logo {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--green), var(--green-deep));
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.2);
        }
        .brand-logo img { width: 100%; height: 100%; object-fit: cover; }
        .brand-text h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 800;
            margin: 0;
            color: var(--ink);
            letter-spacing: -0.01em;
        }
        .brand-text p {
            margin: 2px 0 0;
            font-size: 11px;
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #166534;
            background: #dcfce7;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid #bbf7d0;
        }

        .checkout-main {
            max-width: 1080px;
            margin: 32px auto;
            padding: 0 20px;
            width: 100%;
            flex-grow: 1;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 32px;
            align-items: start;
        }

        .panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .product-badge {
            display: inline-block;
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 12px;
        }

        .product-title {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 10px;
            color: var(--ink);
            line-height: 1.3;
        }

        .product-desc {
            font-size: 14px;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0 0 24px;
            display: grid;
            gap: 10px;
        }
        .feature-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13.5px;
            color: var(--ink);
            line-height: 1.4;
        }
        .feature-list svg {
            flex-shrink: 0;
            color: var(--green);
            margin-top: 2px;
        }

        .seller-card {
            border-top: 1px solid var(--line);
            padding-top: 18px;
            margin-top: 24px;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.5;
        }
        .seller-card strong { color: var(--ink); }

        /* Form elements */
        .form-group {
            margin-bottom: 16px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--ink);
        }
        .form-input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            font-size: 14px;
            color: var(--ink);
            background: #fff;
            transition: border-color 0.15s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .coupon-box {
            display: flex;
            gap: 8px;
            margin-bottom: 18px;
        }
        .btn-apply {
            background: #f1f5f9;
            border: 1px solid var(--line);
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            color: var(--ink);
            white-space: nowrap;
        }
        .btn-apply:hover { background: #e2e8f0; }

        .price-summary {
            background: #f8fafc;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 22px;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            font-size: 13.5px;
            margin-bottom: 8px;
            color: #475569;
        }
        .price-row.total {
            font-size: 18px;
            font-weight: 800;
            color: var(--ink);
            border-top: 1px solid var(--line);
            padding-top: 10px;
            margin-top: 10px;
            margin-bottom: 0;
            font-family: 'Outfit', sans-serif;
        }
        .discount-text { color: #b45309; font-weight: 700; }

        .btn-pay {
            width: 100%;
            background: var(--green);
            color: #fff;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.25);
            transition: all 0.2s ease;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 0.02em;
        }
        .btn-pay:hover:not(:disabled) {
            background: var(--green-dark);
            transform: translateY(-1px);
        }
        .btn-pay:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .wa-notice {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #166534;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 10px 12px;
            border-radius: 8px;
            margin-top: 16px;
        }

        .checkout-footer {
            text-align: center;
            padding: 24px;
            font-size: 12px;
            color: var(--muted);
            border-top: 1px solid var(--line);
            background: #fff;
            margin-top: auto;
        }

        @media (max-width: 820px) {
            .checkout-grid { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="checkout-header">
        <div class="header-content">
            <a href="https://apriloinfotech.com" target="_blank" class="brand-block">
                <div class="brand-logo">
                    <img src="{{ asset('logo.png') }}" alt="Aprilo Infotech">
                </div>
                <div class="brand-text">
                    <h1>APRILO INFOTECH</h1>
                    <p>A technology brand of APRILO ENTERPRISES</p>
                </div>
            </a>
            <div class="secure-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                Secure Razorpay Checkout
            </div>
        </div>
    </header>

    <main class="checkout-main">
        <div class="checkout-grid">
            <!-- Left: Product & Purpose Details -->
            <div class="panel">
                <span class="product-badge">{{ $link->product_name }}</span>
                <h2 class="product-title">{{ $link->title }}</h2>
                <div style="font-size: 12px; color: var(--muted); margin-bottom: 16px; font-weight: 600;">
                    Product Type: <strong>{{ $link->product_type }}</strong>
                </div>

                @if ($link->description)
                    <div class="product-desc">
                        {{ $link->description }}
                    </div>
                @else
                    <div class="product-desc">
                        Official software license and cloud services package by Aprilo Infotech. Includes verified deployment, product onboarding, and commercial sales invoice.
                    </div>
                @endif

                <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ink);">
                    What's included in this purchase:
                </h3>

                <ul class="feature-list">
                    @forelse ($link->features ?? [] as $feature)
                        <li>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>{{ $feature }}</span>
                        </li>
                    @empty
                        <li>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Full commercial software license access for {{ $link->product_name }}</span>
                        </li>
                        <li>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Official sales invoice with 1-click PDF download</span>
                        </li>
                        <li>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Automated invoice delivered directly to your WhatsApp</span>
                        </li>
                    @endforelse
                </ul>

                <div class="seller-card">
                    <div><strong>Legal Business Name:</strong> APRILO ENTERPRISES (Sole Proprietorship)</div>
                    <div><strong>Trade Name / Brand:</strong> APRILO INFOTECH</div>
                    <div><strong>Registered Address:</strong> Jaunpur, Uttar Pradesh, India</div>
                    <div><strong>Official Website:</strong> <a href="https://apriloinfotech.com" target="_blank" style="color: #2563eb;">apriloinfotech.com</a></div>
                    <div style="margin-top: 4px; color: #475569;"><em>Important Note: Unregistered Sole Proprietorship under GST (No GST charged on commercial invoice).</em></div>
                </div>
            </div>

            <!-- Right: Customer Checkout & Razorpay Payment -->
            <div class="panel">
                <h3 style="font-size: 17px; font-weight: 800; margin-top: 0; margin-bottom: 18px; color: var(--ink);">
                    Customer & Billing Details
                </h3>

                <form id="checkoutForm" onsubmit="handleCheckout(event)">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" id="customerName" class="form-input" required placeholder="e.g. Ramesh Kumar" value="{{ $link->customer_name_preset }}">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">WhatsApp Mobile Number *</label>
                            <input type="tel" id="customerPhone" class="form-input" required placeholder="e.g. 919876543210" value="{{ $link->customer_phone_preset }}">
                            <span style="font-size: 11px; color: var(--muted); display: block; margin-top: 3px;">
                                Your official invoice will be sent here.
                            </span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" id="customerEmail" class="form-input" placeholder="name@company.com" value="{{ $link->customer_email_preset }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Company / Business Name (Optional)</label>
                            <input type="text" id="customerCompany" class="form-input" placeholder="e.g. Kumar Retail & Traders">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Customer GSTIN (Optional)</label>
                            <input type="text" id="customerGstin" class="form-input" placeholder="09AAAAA0000A1Z5">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Billing Address</label>
                        <input type="text" id="customerAddress" class="form-input" placeholder="City, State, Country">
                    </div>

                    @if ($link->allow_custom_amount)
                        <div class="form-group">
                            <label class="form-label">Custom Payment Amount (₹ INR) *</label>
                            <input type="number" id="customAmount" class="form-input" step="0.01" min="1" value="{{ $link->base_amount }}" oninput="updatePricing()">
                        </div>
                    @endif

                    <!-- Coupon Input -->
                    <div class="form-group">
                        <label class="form-label">Coupon / Promo Code</label>
                        <div class="coupon-box">
                            <input type="text" id="couponCode" class="form-input" placeholder="Enter coupon code" value="{{ $link->coupon_code }}" style="text-transform: uppercase;">
                            <button type="button" class="btn-apply" onclick="applyCouponCode()">Apply</button>
                        </div>
                        <span id="couponMsg" style="font-size: 12px; display: none; font-weight: 600;"></span>
                    </div>

                    <!-- Price Breakdown Summary -->
                    <div class="price-summary">
                        <div class="price-row">
                            <span>Base Amount:</span>
                            <span id="displayBase">₹{{ number_format($link->base_amount, 2) }}</span>
                        </div>
                        <div class="price-row" id="rowDiscount" style="display: none;">
                            <span class="discount-text">Coupon Discount:</span>
                            <span class="discount-text" id="displayDiscount">-₹0.00</span>
                        </div>
                        <div class="price-row">
                            <span>GST:</span>
                            <span>Not Charged (Unregistered)</span>
                        </div>
                        <div class="price-row total">
                            <span>Total Payable:</span>
                            <span id="displayTotal">₹{{ number_format($link->base_amount, 2) }}</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-pay" id="payButton">
                        💳 Pay <span id="btnPayAmount">₹{{ number_format($link->base_amount, 2) }}</span> via Razorpay
                    </button>

                    <div class="wa-notice">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <span>Once payment is done, your official Sales Invoice will be sent directly to your WhatsApp.</span>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="checkout-footer">
        <div>© 2026 <strong>APRILO ENTERPRISES</strong> · Trading as <strong>APRILO INFOTECH</strong> · Build Smarter. Grow Faster.</div>
        <div style="margin-top: 4px; color: #94a3b8;">
            Registered in Jaunpur, Uttar Pradesh, India · Need support? Contact <a href="mailto:contact@apriloinfotech.com" style="color: inherit; text-decoration: underline;">contact@apriloinfotech.com</a>
        </div>
    </footer>

    <!-- Razorpay Checkout SDK -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <script>
        const linkSlug = "{{ $link->slug }}";
        const baseAmountDefault = {{ (float) $link->base_amount }};
        const hasCustomAmount = {{ $link->allow_custom_amount ? 'true' : 'false' }};
        const razorpayKey = "{{ $razorpayKeyId }}";
        const isMock = {{ $isMockMode ? 'true' : 'false' }};
        const isSandbox = {{ $isSandbox ? 'true' : 'false' }};
        const isProduction = {{ $isProduction ? 'true' : 'false' }};

        let currentDiscount = 0.00;
        let appliedCouponCode = "";

        function getBaseAmount() {
            if (hasCustomAmount) {
                const custom = parseFloat(document.getElementById('customAmount')?.value);
                return (!isNaN(custom) && custom > 0) ? custom : baseAmountDefault;
            }
            return baseAmountDefault;
        }

        async function applyCouponCode() {
            const code = document.getElementById('couponCode').value.trim();
            const msgEl = document.getElementById('couponMsg');
            const base = getBaseAmount();

            if (!code) {
                currentDiscount = 0.00;
                appliedCouponCode = "";
                msgEl.style.display = 'none';
                updatePricing();
                return;
            }

            try {
                const res = await fetch(`/pay/${linkSlug}/check-coupon`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ coupon_code: code, amount: base })
                });

                const data = await res.json();
                if (res.ok && data.valid) {
                    currentDiscount = parseFloat(data.discount);
                    appliedCouponCode = code;
                    msgEl.style.display = 'block';
                    msgEl.style.color = '#15803d';
                    msgEl.textContent = `✓ ${data.message} You save ₹${currentDiscount.toFixed(2)}.`;
                } else {
                    currentDiscount = 0.00;
                    appliedCouponCode = "";
                    msgEl.style.display = 'block';
                    msgEl.style.color = '#dc2626';
                    msgEl.textContent = data.message || 'Invalid coupon code.';
                }
            } catch (err) {
                currentDiscount = 0.00;
                appliedCouponCode = "";
                msgEl.style.display = 'block';
                msgEl.style.color = '#dc2626';
                msgEl.textContent = 'Could not verify coupon.';
            }

            updatePricing();
        }

        function updatePricing() {
            const base = getBaseAmount();
            const finalAmount = Math.max(0, base - currentDiscount);

            document.getElementById('displayBase').textContent = `₹${base.toFixed(2)}`;
            document.getElementById('displayTotal').textContent = `₹${finalAmount.toFixed(2)}`;
            document.getElementById('btnPayAmount').textContent = `₹${finalAmount.toFixed(2)}`;

            const rowDisc = document.getElementById('rowDiscount');
            if (currentDiscount > 0) {
                rowDisc.style.display = 'flex';
                document.getElementById('displayDiscount').textContent = `-₹${currentDiscount.toFixed(2)}`;
            } else {
                rowDisc.style.display = 'none';
            }
        }

        // Auto-check preset coupon if present
        if (document.getElementById('couponCode').value) {
            applyCouponCode();
        }

        async function handleCheckout(e) {
            if (e) e.preventDefault();
            const payBtn = document.getElementById('payButton');
            payBtn.disabled = true;
            payBtn.textContent = '⏳ Preparing Secure Order...';

            const payload = {
                customer_name: document.getElementById('customerName').value.trim(),
                customer_phone: document.getElementById('customerPhone').value.trim(),
                customer_email: document.getElementById('customerEmail').value.trim(),
                customer_company: document.getElementById('customerCompany').value.trim(),
                customer_gstin: document.getElementById('customerGstin').value.trim(),
                customer_address: document.getElementById('customerAddress').value.trim(),
                coupon_code: appliedCouponCode,
                custom_amount: hasCustomAmount ? getBaseAmount() : null
            };

            try {
                const orderRes = await fetch(`/pay/${linkSlug}/order`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const orderData = await orderRes.json();
                if (!orderRes.ok) {
                    throw new Error(orderData.message || 'Could not initiate order.');
                }

                if (!orderData.razorpay_key_id) {
                    throw new Error('Razorpay Key ID is not configured in .env. Please configure RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET.');
                }

                // Open Real Razorpay Checkout Popup
                const options = {
                    key: orderData.razorpay_key_id,
                    amount: orderData.amount_paise,
                    currency: orderData.currency,
                    name: 'APRILO INFOTECH',
                    description: "{{ addslashes($link->title) }}",
                    image: "{{ asset('logo.png') }}",
                    order_id: orderData.order_id,
                    prefill: {
                        name: payload.customer_name,
                        email: payload.customer_email,
                        contact: payload.customer_phone
                    },
                    theme: { color: '#22C55E' },
                    handler: async function (response) {
                        payBtn.textContent = '⏳ Verifying & Generating WhatsApp Invoice...';
                        await verifyAndComplete(
                            orderData.transaction_id,
                            response.razorpay_order_id,
                            response.razorpay_payment_id,
                            response.razorpay_signature
                        );
                    },
                    modal: {
                        ondismiss: function () {
                            payBtn.disabled = false;
                            updatePricing();
                        }
                    }
                };

                const rzp = new Razorpay(options);
                rzp.open();
            } catch (err) {
                alert('Checkout error: ' + err.message);
                payBtn.disabled = false;
                updatePricing();
            }
        }

        async function verifyAndComplete(transactionId, orderId, paymentId, signature) {
            try {
                const verifyRes = await fetch(`/pay/${linkSlug}/verify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        transaction_id: transactionId,
                        razorpay_order_id: orderId,
                        razorpay_payment_id: paymentId,
                        razorpay_signature: signature
                    })
                });

                const verifyData = await verifyRes.json();
                if (verifyRes.ok && verifyData.redirect_url) {
                    window.location.href = verifyData.redirect_url;
                } else {
                    alert('Payment verification error: ' + (verifyData.message || 'Verification failed.'));
                    document.getElementById('payButton').disabled = false;
                }
            } catch (err) {
                alert('Network verification error: ' + err.message);
                document.getElementById('payButton').disabled = false;
            }
        }
    </script>
</body>
</html>

