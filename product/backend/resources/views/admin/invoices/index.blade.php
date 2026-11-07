@extends('admin.layout')

@section('title', 'Payments & Invoices - Aprilo Infotech')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;">
        <div>
            <div class="eyebrow" style="color: var(--green);">Centralized Billing Engine</div>
            <h1 style="font-size: 28px; margin-top: 4px;">Payments & Software Invoices</h1>
            <p class="help" style="margin-top: 4px; max-width: 720px;">
                Official commercial billing records for <strong>APRILO INFOTECH</strong> (A brand of <strong>APRILO ENTERPRISES</strong>). Manage product subscriptions, lifetime licenses, and download professional PDF invoices.
            </p>
        </div>
        <div>
            <button type="button" class="button" onclick="openInvoiceModal()" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; padding: 12px 20px; box-shadow: 0 4px 14px rgba(34, 197, 94, 0.25);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Generate Software Invoice
            </button>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-4" style="margin-bottom: 28px;">
        <div class="card" style="border-left: 4px solid var(--green);">
            <div class="metric">₹{{ number_format($totalPaid, 2) }}</div>
            <div class="label">Total Paid Revenue</div>
        </div>
        <div class="card" style="border-left: 4px solid #f59e0b;">
            <div class="metric">₹{{ number_format($totalPending, 2) }}</div>
            <div class="label">Pending Receivables</div>
        </div>
        <div class="card" style="border-left: 4px solid #3b82f6;">
            <div class="metric">{{ $paidCount }} / {{ $totalCount }}</div>
            <div class="label">Paid / Total Invoices</div>
        </div>
        <div class="card" style="border-left: 4px solid #8b5cf6;">
            <div class="metric">{{ count($standardProducts) }} Products</div>
            <div class="label">Centralized Product Engine</div>
        </div>
    </div>

    <!-- GST Compliance & Legal Seller Notice -->
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid var(--green); border-radius: 8px; padding: 14px 18px; margin-bottom: 24px; font-size: 13px; color: #475569; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: #dcfce7; color: #15803d; font-weight: 800; font-size: 14px;">✓</span>
            <div>
                <strong>Legal Seller Details:</strong> <strong>APRILO ENTERPRISES</strong> (Sole Proprietorship) · Trading as <strong>APRILO INFOTECH</strong> · Jaunpur, Uttar Pradesh, India · Seller GSTIN: <strong>09FLSPS7380R1ZH</strong>. Central sequential numbering (<code>INV-YYYY-XXX</code>).
            </div>
        </div>
        <span class="pill" style="background: #dcfce7; color: #15803d; font-weight: 800; font-size: 12px;">GSTIN: 09FLSPS7380R1ZH</span>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card" style="padding: 16px 20px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('admin.invoices.index') }}" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 220px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Invoice #, Customer, Order ID, or Product..." style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 9px 12px; font-size: 13.5px; outline: none;">
            </div>

            <div style="width: 170px;">
                <select name="status" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 9px 12px; font-size: 13.5px; background: #fff; outline: none;">
                    <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ request('status') === 'pending' || request('status') === 'unpaid' ? 'selected' : '' }}>Pending / Unpaid</option>
                </select>
            </div>

            @if ($organizations->count() > 1)
                <div style="width: 200px;">
                    <select name="organization_id" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 9px 12px; font-size: 13.5px; background: #fff; outline: none;">
                        <option value="">All Organizations</option>
                        @foreach ($organizations as $org)
                            <option value="{{ $org->id }}" {{ request('organization_id') === $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button type="submit" class="button" style="width: auto; padding: 9px 18px; font-size: 13.5px;">Filter</button>
            @if (request()->hasAny(['search', 'status', 'organization_id']))
                <a href="{{ route('admin.invoices.index') }}" style="font-size: 13px; color: var(--muted); text-decoration: underline; padding: 6px;">Reset</a>
            @endif
        </form>
    </div>

    <!-- Invoices Table -->
    <div style="overflow-x: auto; background: var(--surface); border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <table>
            <thead>
                <tr>
                    <th>Invoice & Doc Type</th>
                    <th>Customer / Bill To</th>
                    <th>Product & Plan</th>
                    <th>Order ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment Details</th>
                    <th>Issue Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $inv)
                    <tr>
                        <td>
                            <div style="font-weight: 800; color: var(--ink); font-family: 'Outfit', sans-serif; font-size: 14.5px;">
                                <a href="{{ route('admin.invoices.show', $inv->id) }}" style="color: var(--green-dark); text-decoration: none;">
                                    {{ $inv->invoice_number }}
                                </a>
                            </div>
                            <span class="pill" style="font-size: 10.5px; margin-top: 3px; background: #f1f5f9; text-transform: uppercase;">
                                {{ str_replace('_', ' ', $inv->document_type ?? 'Sales Invoice') }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--ink);">{{ $inv->display_customer_name }}</div>
                            <div style="font-size: 12px; color: var(--muted);">{{ $inv->display_customer_email }}</div>
                            <div style="font-size: 11px; color: #94a3b8;">ID: {{ $inv->display_customer_id }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--ink);">{{ $inv->display_product_name }}</div>
                            <div style="font-size: 12px; color: var(--muted);">
                                {{ $inv->display_product_type }} · {{ $inv->display_plan_name }}
                            </div>
                        </td>
                        <td>
                            <code style="font-size: 12px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #475569; font-weight: 600;">
                                {{ $inv->display_order_id }}
                            </code>
                        </td>
                        <td>
                            <div style="font-weight: 800; font-size: 15px; color: var(--ink); font-family: 'Outfit', sans-serif;">
                                ₹{{ number_format($inv->display_total_amount, 2) }}
                            </div>
                            @if ($inv->display_discount_amount > 0)
                                <div style="font-size: 11px; color: #059669;">-₹{{ number_format($inv->display_discount_amount, 2) }} discount</div>
                            @endif
                        </td>
                        <td>
                            @if ($inv->status === 'paid')
                                <span class="pill" style="background: rgba(34, 197, 94, 0.15); color: #15803d; font-weight: 800; font-size: 11px;">
                                    PAID
                                </span>
                            @else
                                <span class="pill" style="background: rgba(245, 158, 11, 0.15); color: #b45309; font-weight: 800; font-size: 11px;">
                                    PENDING
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size: 12.5px; font-weight: 600; color: #334155;">{{ $inv->display_payment_method }}</div>
                            <div style="font-size: 11px; color: var(--muted);">Ref: {{ $inv->display_payment_reference }}</div>
                        </td>
                        <td>
                            <div style="font-size: 13px; color: var(--ink);">
                                {{ $inv->created_at ? $inv->created_at->format('d M Y') : now()->format('d M Y') }}
                            </div>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="{{ route('admin.invoices.show', $inv->id) }}" class="button" style="width: auto; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 12px; text-decoration: none;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View / PDF
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px 20px; color: var(--muted);">
                            <div style="font-size: 32px; margin-bottom: 8px;">🧾</div>
                            <div style="font-weight: 700; font-size: 16px; color: var(--ink);">No Invoices Generated Yet</div>
                            <p style="margin-top: 4px; font-size: 13.5px;">Click the <strong>"Generate Software Invoice"</strong> button above to create an official invoice marked with Aprilo Infotech.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $invoices->links() }}
    </div>

    <!-- Generate Invoice Modal -->
    <div id="invoiceModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 100; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
        <div style="background: #ffffff; width: 100%; max-width: 780px; max-height: 90vh; overflow-y: auto; border-radius: 14px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border: 1px solid var(--line);">
            
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: #fff; z-index: 10;">
                <div>
                    <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--green); letter-spacing: 0.1em;">Aprilo Central Billing Engine</div>
                    <h2 style="font-size: 20px; margin-top: 2px;">Generate Software Product Invoice</h2>
                </div>
                <button type="button" onclick="closeInvoiceModal()" style="border: 0; background: #f1f5f9; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; font-size: 18px; line-height: 1; color: var(--muted);">✕</button>
            </div>

            <form method="POST" action="{{ route('admin.invoices.store') }}" style="padding: 24px;" id="generateInvoiceForm">
                @csrf

                <!-- Brand & Legal Seller Confirmation Banner -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 12.5px; color: #475569;">
                    <div style="font-weight: 700; color: var(--ink);">Legal Seller Identity:</div>
                    <strong>APRILO ENTERPRISES</strong> (Sole Proprietorship) · Trading as <strong>APRILO INFOTECH</strong><br>
                    <strong>GSTIN:</strong> <span style="color: var(--green-dark); font-weight: 800;">09FLSPS7380R1ZH</span> · Jaunpur, Uttar Pradesh, India · <a href="https://apriloinfotech.com" target="_blank" style="color: var(--green-dark);">apriloinfotech.com</a>
                </div>

                <!-- SELECT ORGANIZATION: Auto-populates Customer Name, Email, Address, ID, Order ID & Plan -->
                <div class="field" style="margin-bottom: 20px; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 8px; padding: 14px 16px;">
                    <label style="color: #166534; font-weight: 800; font-size: 13.5px; display: flex; align-items: center; gap: 6px;">
                        <span>🏢</span> Select Organization for Billing (Auto-Fills Details):
                    </label>
                    <select name="organization_id" id="organizationBillingSelect" onchange="handleOrganizationSelect(this)" style="margin-top: 6px; font-weight: 700; border: 1px solid #86efac; background: #ffffff;">
                        <option value="">-- Choose an Organization to auto-fill customer billing info --</option>
                        @foreach ($organizations as $org)
                            <option value="{{ $org->id }}"
                                data-name="{{ $org->name }}"
                                data-email="{{ $org->email ?? $org->owner?->email ?? ($org->users->firstWhere('role', 'owner')?->email ?? ($org->users->first()?->email ?? '')) }}"
                                data-address="{{ $org->settings?->business_address ?? ($org->city ? $org->city . ', ' . ($org->state ? $org->state . ', ' : '') . $org->country : 'Jaunpur, Uttar Pradesh, India') }}"
                                data-plan="{{ ucfirst($org->plan ?? 'Professional') }}"
                                data-product="{{ 'Aprilo ' . ucfirst($org->plan ?? 'POS') . ' – Annual Subscription' }}"
                                data-custid="CUST-{{ strtoupper(substr(str_replace('-', '', $org->id), 0, 4)) }}"
                                data-orderid="APR-ORD-{{ strtoupper(substr(str_replace('-', '', $org->id), -4)) }}">
                                {{ $org->name }} (Plan: {{ ucfirst($org->plan ?? 'Standard') }})
                            </option>
                        @endforeach
                    </select>
                    <div style="font-size: 11.5px; color: #15803d; margin-top: 5px;">
                        ⚡ Selecting an organization automatically pulls & fills the Customer Name, Customer ID, Email, Billing Address, and Plan below. You can customize them anytime.
                    </div>
                </div>

                <div class="grid grid-2" style="gap: 16px; margin-bottom: 16px;">
                    <div class="field">
                        <label>Document Type</label>
                        <select name="document_type" id="docTypeSelect">
                            <option value="sales_invoice" selected>Sales Invoice (INV-YYYY-XXX)</option>
                            <option value="quotation">Quotation (QT-YYYY-XXX)</option>
                            <option value="payment_receipt">Payment Receipt (RCPT-YYYY-XXX)</option>
                            <option value="credit_note">Credit Note (CN-YYYY-XXX)</option>
                            <option value="refund_receipt">Refund Receipt (REF-YYYY-XXX)</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Select Product</label>
                        <select name="product_name" id="productSelect" onchange="handleProductChange(this)">
                            <option value="Aprilo POS" data-type="SaaS Subscription" data-plan="Annual Subscription" data-price="5000">Aprilo POS – Annual Subscription</option>
                            <option value="Aprilo Chatbot" data-type="SaaS Subscription" data-plan="Professional" data-price="4500" selected>Aprilo Chatbot – Professional</option>
                            <option value="Aprilo Agent" data-type="SaaS Subscription" data-plan="Enterprise" data-price="7500">Aprilo Agent – Enterprise</option>
                            <option value="TradeFlow" data-type="Lifetime License" data-plan="Lifetime License" data-price="12000">TradeFlow – Lifetime License</option>
                            <option value="Aprilo Commerce" data-type="SaaS Subscription" data-plan="Professional" data-price="6000">Aprilo Commerce – Professional</option>
                            <option value="Custom" data-type="One-time Purchase" data-plan="Custom License" data-price="0">Custom Product / Service</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-2" style="gap: 16px; margin-bottom: 16px;">
                    <div class="field">
                        <label>Product Type</label>
                        <input type="text" name="product_type" id="productTypeInput" value="SaaS Subscription" placeholder="e.g. SaaS Subscription, Lifetime License">
                    </div>

                    <div class="field">
                        <label>Plan / License Title</label>
                        <input type="text" name="plan_name" id="planNameInput" value="Professional" placeholder="e.g. Annual Subscription, Professional, Lifetime">
                    </div>
                </div>

                <div class="grid grid-2" style="gap: 16px; margin-bottom: 16px;">
                    <div class="field">
                        <label>Billing Period / Validity</label>
                        <input type="text" name="billing_period_text" id="billingPeriodInput" value="{{ date('d M Y') }} – {{ date('d M Y', strtotime('+1 year -1 day')) }}" placeholder="e.g. 07 Nov 2026 – 06 Nov 2027 or Lifetime License">
                    </div>

                    <div class="field">
                        <label>Order / License ID</label>
                        <input type="text" name="order_id" id="orderIdInput" value="APR-ORD-{{ rand(1001, 9999) }}" placeholder="e.g. APR-ORD-1001">
                    </div>
                </div>

                <!-- Bill To Details -->
                <div style="font-weight: 800; font-size: 13px; text-transform: uppercase; color: var(--muted); letter-spacing: 0.08em; margin: 20px 0 10px; border-top: 1px solid var(--line); padding-top: 16px;">
                    Customer Billing Details (Bill To)
                </div>

                <div class="grid grid-2" style="gap: 16px; margin-bottom: 16px;">
                    <div class="field">
                        <label>Customer / Business Name *</label>
                        <input type="text" name="customer_name" id="customerNameInput" required placeholder="e.g. Rajesh Sharma / Sharma Enterprises">
                    </div>

                    <div class="field">
                        <label>Customer ID</label>
                        <input type="text" name="customer_id" id="customerIdInput" value="CUST-{{ rand(1001, 9999) }}" placeholder="e.g. CUST-1001">
                    </div>
                </div>

                <div class="grid grid-2" style="gap: 16px; margin-bottom: 16px;">
                    <div class="field">
                        <label>Customer Email</label>
                        <input type="email" name="customer_email" id="customerEmailInput" placeholder="e.g. billing@client.com">
                    </div>

                    <div class="field">
                        <label>Customer Billing Address</label>
                        <input type="text" name="customer_address" id="customerAddressInput" placeholder="e.g. Mumbai, Maharashtra, India">
                    </div>
                </div>

                <!-- Pricing & Payment -->
                <div style="font-weight: 800; font-size: 13px; text-transform: uppercase; color: var(--muted); letter-spacing: 0.08em; margin: 20px 0 10px; border-top: 1px solid var(--line); padding-top: 16px;">
                    Pricing, Discount & Payment Details
                </div>

                <div class="grid grid-3" style="gap: 16px; margin-bottom: 16px;">
                    <div class="field">
                        <label>Unit Amount (₹) *</label>
                        <input type="number" step="0.01" name="unit_amount" id="unitAmountInput" value="5000" required oninput="calculateTotal()">
                    </div>

                    <div class="field">
                        <label>Quantity</label>
                        <input type="number" name="quantity" id="quantityInput" value="1" min="1" required oninput="calculateTotal()">
                    </div>

                    <div class="field">
                        <label>Discount (₹)</label>
                        <input type="number" step="0.01" name="discount_amount" id="discountInput" value="500" oninput="calculateTotal()">
                    </div>
                </div>

                <!-- Summary Total Display -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 12px; color: #166534; font-weight: 600;">
                            Subtotal: <span id="displaySubtotal">₹4,500.00</span> · GST: <strong>Not charged</strong>
                        </div>
                        <div style="font-size: 11px; color: #15803d; margin-top: 2px;">GSTIN: 09FLSPS7380R1ZH (Aprilo Infotech)</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 11px; text-transform: uppercase; font-weight: 800; color: #15803d; letter-spacing: 0.08em;">Total Payable</div>
                        <div id="displayTotal" style="font-size: 22px; font-weight: 800; color: #166534; font-family: 'Outfit', sans-serif;">₹4,500.00</div>
                    </div>
                </div>

                <div class="grid grid-3" style="gap: 16px; margin-bottom: 16px;">
                    <div class="field">
                        <label>Payment Status</label>
                        <select name="status">
                            <option value="paid" selected>Paid</option>
                            <option value="pending">Pending / Unpaid</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Payment Method</label>
                        <select name="payment_method">
                            <option value="UPI / Online Payment" selected>UPI / Online Payment</option>
                            <option value="Razorpay">Razorpay</option>
                            <option value="Credit / Debit Card">Credit / Debit Card</option>
                            <option value="Net Banking">Net Banking</option>
                            <option value="Bank Transfer (NEFT/IMPS)">Bank Transfer (NEFT/IMPS)</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Transaction Reference</label>
                        <input type="text" name="payment_reference" value="TXN-{{ strtoupper(substr(md5(microtime()), 0, 8)) }}" placeholder="e.g. TXN-87429103 or pay_XXXXX">
                    </div>
                </div>

                <div class="grid grid-2" style="gap: 16px; margin-bottom: 16px;">
                    <div class="field">
                        <label>Invoice Issue Date</label>
                        <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="field">
                        <label>Notes / Remarks</label>
                        <input type="text" name="notes" value="Thank you for choosing Aprilo Infotech." placeholder="e.g. Thank you for choosing Aprilo Infotech.">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; border-top: 1px solid var(--line); padding-top: 18px;">
                    <button type="button" onclick="closeInvoiceModal()" class="button" style="background: transparent; color: var(--ink); border: 1px solid var(--line); width: auto; box-shadow: none;">Cancel</button>
                    <button type="submit" class="button" style="width: auto; padding: 11px 24px; font-size: 14px; font-weight: 700;">Generate & Preview Invoice</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openInvoiceModal() {
            const modal = document.getElementById('invoiceModal');
            if (modal) {
                modal.style.display = 'flex';
                calculateTotal();

                // If organization select has options and none selected, auto-select first one
                const orgSelect = document.getElementById('organizationBillingSelect');
                if (orgSelect && orgSelect.options.length > 1 && !orgSelect.value) {
                    orgSelect.selectedIndex = 1;
                    handleOrganizationSelect(orgSelect);
                }
            }
        }

        function closeInvoiceModal() {
            const modal = document.getElementById('invoiceModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        function handleOrganizationSelect(select) {
            const opt = select.options[select.selectedIndex];
            if (!opt || !opt.value) return;

            const name = opt.getAttribute('data-name');
            const email = opt.getAttribute('data-email');
            const address = opt.getAttribute('data-address');
            const plan = opt.getAttribute('data-plan');
            const custId = opt.getAttribute('data-custid');
            const orderId = opt.getAttribute('data-orderid');

            if (name) document.getElementById('customerNameInput').value = name;
            if (email) document.getElementById('customerEmailInput').value = email;
            if (address) document.getElementById('customerAddressInput').value = address;
            if (custId) document.getElementById('customerIdInput').value = custId;
            if (orderId) document.getElementById('orderIdInput').value = orderId;
            if (plan) document.getElementById('planNameInput').value = plan;
        }

        function handleProductChange(select) {
            const opt = select.options[select.selectedIndex];
            if (!opt) return;

            const type = opt.getAttribute('data-type');
            const plan = opt.getAttribute('data-plan');
            const price = opt.getAttribute('data-price');

            if (type) document.getElementById('productTypeInput').value = type;
            if (plan) document.getElementById('planNameInput').value = plan;
            if (price && parseFloat(price) > 0) {
                document.getElementById('unitAmountInput').value = price;
            }
            calculateTotal();
        }

        function calculateTotal() {
            const unit = parseFloat(document.getElementById('unitAmountInput').value) || 0;
            const qty = parseInt(document.getElementById('quantityInput').value) || 1;
            const discount = parseFloat(document.getElementById('discountInput').value) || 0;

            const subtotal = Math.max(0, (unit * qty) - discount);
            const formattedSub = '₹' + subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            document.getElementById('displaySubtotal').textContent = formattedSub;
            document.getElementById('displayTotal').textContent = formattedSub;
        }

        // Close on clicking outside modal content
        document.getElementById('invoiceModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeInvoiceModal();
            }
        });
    </script>
@endsection
