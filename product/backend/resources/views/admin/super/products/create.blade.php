@extends('admin.layout')

@section('title', 'Super Admin - Create New Product')

@section('content')
    <div style="margin-bottom: 16px;">
        <a class="button" style="width: auto; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: var(--ink); border: 1px solid var(--line); font-size: 13px; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 8px 14px; border-radius: 6px;" href="{{ route('admin.super.products') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Products Catalog
        </a>
    </div>

    <div class="header">
        <div>
            <div class="eyebrow" style="color: #6366f1;">Product Architect</div>
            <h1>Create New Commercial Product</h1>
            <p class="help">Define product metadata, commercial pricing, and bundle granular permissions that unlock features when licensed by organizations.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.super.products.store') }}">
        @csrf

        <div class="card" style="margin-bottom: 24px;">
            <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">Product Specifications</h2>
            <div class="grid grid-3" style="margin-bottom: 16px;">
                <label class="field">
                    Product Name *
                    <input name="name" value="{{ old('name') }}" placeholder="e.g. Aprilo Custom Agent" required>
                </label>
                <label class="field">
                    Product Slug (Optional)
                    <input name="slug" value="{{ old('slug') }}" placeholder="e.g. aprilo-custom-agent">
                </label>
                <label class="field">
                    Industry Category *
                    <select name="category" required>
                        @foreach ($categories as $catKey => $catLabel)
                            <option value="{{ $catKey }}" {{ old('category') === $catKey ? 'selected' : '' }}>{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="grid grid-3" style="margin-bottom: 16px;">
                <label class="field">
                    Product Type *
                    <select name="product_type" required>
                        <option value="subscription" {{ old('product_type') === 'subscription' ? 'selected' : '' }}>Recurring Subscription</option>
                        <option value="one_time_license" {{ old('product_type') === 'one_time_license' ? 'selected' : '' }}>One-Time Software License</option>
                        <option value="enterprise" {{ old('product_type') === 'enterprise' ? 'selected' : '' }}>Custom Enterprise SLA</option>
                    </select>
                </label>
                <label class="field">
                    Base Platform *
                    <input name="platform" value="{{ old('platform', 'web') }}" placeholder="e.g. web, magento, shopify, omnichannel">
                </label>
                <label class="field">
                    Base Price & Currency *
                    <div style="display: flex; gap: 8px;">
                        <input name="price" type="number" step="0.01" min="0" value="{{ old('price', '499.00') }}" style="flex: 2;" required>
                        <select name="currency" style="flex: 1;" required>
                            <option value="INR" {{ old('currency') === 'INR' ? 'selected' : '' }}>INR (₹)</option>
                            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD ($)</option>
                            <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        </select>
                    </div>
                </label>
            </div>

            <div class="grid grid-2" style="margin-bottom: 16px;">
                <label class="field">
                    Short Description (1-2 sentences)
                    <textarea name="short_description" rows="2" placeholder="Brief elevator pitch shown on cards">{{ old('short_description') }}</textarea>
                </label>
                <label class="field">
                    Detailed Product Overview
                    <textarea name="description" rows="2" placeholder="Comprehensive product capabilities and scope">{{ old('description') }}</textarea>
                </label>
            </div>

            <div class="grid grid-2" style="margin-bottom: 16px;">
                <label class="field">
                    Key Features (one per line)
                    <textarea name="features" rows="4" placeholder="Multi-channel AI Agent (Web, WhatsApp)&#10;Knowledge Base RAG & Semantic Chunking&#10;Real-Time Analytics & CSAT Tracking">{{ old('features') }}</textarea>
                </label>
                <div style="display: grid; gap: 12px;">
                    <label class="field">
                        Display Sort Order
                        <input name="sort_order" type="number" value="{{ old('sort_order', 10) }}">
                    </label>
                    <div style="display: flex; gap: 24px; align-items: center; margin-top: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                            Active in Commercial Catalog
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" name="featured" value="1" {{ old('featured') ? 'checked' : '' }}>
                            Mark as Featured
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions Bundling Matrix -->
        <div class="card" style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                <div>
                    <h2 style="font-size: 18px; font-weight: 800;">Bundle Granular Permissions</h2>
                    <p class="help" style="margin: 4px 0 0;">Select the permissions unlocked for organization users when they subscribe to or are licensed for this product.</p>
                </div>
                <div style="font-size: 12px; font-weight: 700; color: #4f46e5;">
                    Check all permissions that apply
                </div>
            </div>

            @foreach ($permissionsByCategory as $category => $perms)
                <div style="margin-bottom: 20px; border: 1px solid var(--line); border-radius: 8px; padding: 14px; background: #fafafa;">
                    <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #334155; margin-bottom: 10px;">
                        {{ strtoupper($category) }} DOMAIN PERMISSIONS
                    </div>
                    <div class="grid grid-2" style="gap: 10px;">
                        @foreach ($perms as $perm)
                            <label style="display: flex; align-items: flex-start; gap: 10px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; cursor: pointer;">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" style="width: auto; margin-top: 3px;" {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}>
                                <div>
                                    <div style="font-size: 13px; font-weight: 700; color: var(--ink);">{{ $perm->name }}</div>
                                    <div style="font-size: 11px; font-family: monospace; color: #64748b;">{{ $perm->slug }}</div>
                                    <div style="font-size: 11.5px; color: var(--muted); margin-top: 2px;">{{ $perm->description }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div style="display: flex; gap: 12px;">
            <button class="button" type="submit" style="width: auto; padding: 12px 28px; background: #4f46e5; border-color: #4338ca;">
                Save & Deploy Product
            </button>
            <a class="button" href="{{ route('admin.super.products') }}" style="width: auto; padding: 12px 24px; background: #ffffff; color: var(--ink); border: 1px solid var(--line);">
                Cancel
            </a>
        </div>
    </form>
@endsection
