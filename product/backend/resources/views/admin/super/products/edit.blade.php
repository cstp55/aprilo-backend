@extends('admin.layout')

@section('title', 'Super Admin - Edit Product: ' . $product->name)

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
            <h1>Edit Product: {{ $product->name }}</h1>
            <p class="help">Update commercial terms, descriptions, and bundled permissions for this offering.</p>
        </div>
        <div>
            <span style="font-size: 13px; font-family: monospace; background: #e0e7ff; color: #4338ca; padding: 6px 12px; border-radius: 6px; font-weight: 700;">
                slug: {{ $product->slug }}
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.super.products.update', $product) }}">
        @csrf
        @method('PATCH')

        <div class="card" style="margin-bottom: 24px;">
            <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">Product Specifications</h2>
            <div class="grid grid-3" style="margin-bottom: 16px;">
                <label class="field">
                    Product Name *
                    <input name="name" value="{{ old('name', $product->name) }}" required>
                </label>
                <label class="field">
                    Industry Category *
                    <select name="category" required>
                        @foreach ($categories as $catKey => $catLabel)
                            <option value="{{ $catKey }}" {{ old('category', $product->category) === $catKey ? 'selected' : '' }}>{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">
                    Catalog Status *
                    <select name="status" required>
                        <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active (Available for purchase)</option>
                        <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Draft (Under development)</option>
                        <option value="archived" {{ old('status', $product->status) === 'archived' ? 'selected' : '' }}>Archived (Legacy only)</option>
                    </select>
                </label>
            </div>

            <div class="grid grid-3" style="margin-bottom: 16px;">
                <label class="field">
                    Product Type *
                    <select name="product_type" required>
                        <option value="subscription" {{ old('product_type', $product->product_type) === 'subscription' ? 'selected' : '' }}>Recurring Subscription</option>
                        <option value="one_time_license" {{ old('product_type', $product->product_type) === 'one_time_license' ? 'selected' : '' }}>One-Time Software License</option>
                        <option value="enterprise" {{ old('product_type', $product->product_type) === 'enterprise' ? 'selected' : '' }}>Custom Enterprise SLA</option>
                    </select>
                </label>
                <label class="field">
                    Base Platform *
                    <input name="platform" value="{{ old('platform', $product->platform) }}" placeholder="e.g. web, magento, shopify, omnichannel">
                </label>
                <label class="field">
                    Base Price & Currency *
                    <div style="display: flex; gap: 8px;">
                        <input name="price" type="number" step="0.01" min="0" value="{{ old('price', $product->price) }}" style="flex: 2;" required>
                        <select name="currency" style="flex: 1;" required>
                            <option value="INR" {{ old('currency', $product->currency) === 'INR' ? 'selected' : '' }}>INR (₹)</option>
                            <option value="USD" {{ old('currency', $product->currency) === 'USD' ? 'selected' : '' }}>USD ($)</option>
                            <option value="EUR" {{ old('currency', $product->currency) === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        </select>
                    </div>
                </label>
            </div>

            <div class="grid grid-2" style="margin-bottom: 16px;">
                <label class="field">
                    Short Description
                    <textarea name="short_description" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
                </label>
                <label class="field">
                    Detailed Product Overview
                    <textarea name="description" rows="2">{{ old('description', $product->description) }}</textarea>
                </label>
            </div>

            <div class="grid grid-2" style="margin-bottom: 16px;">
                <label class="field">
                    Key Features (one per line)
                    <textarea name="features" rows="4">{{ old('features', is_array($product->features) ? implode("\n", $product->features) : '') }}</textarea>
                </label>
                <div style="display: grid; gap: 12px;">
                    <label class="field">
                        Display Sort Order
                        <input name="sort_order" type="number" value="{{ old('sort_order', $product->sort_order) }}">
                    </label>
                    <div style="display: flex; gap: 24px; align-items: center; margin-top: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            Active in Commercial Catalog
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" name="featured" value="1" {{ old('featured', $product->featured) ? 'checked' : '' }}>
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
                    <h2 style="font-size: 18px; font-weight: 800;">Bundle Granular Permissions ({{ count($productPermissions) }} Selected)</h2>
                    <p class="help" style="margin: 4px 0 0;">Check permissions to bundle with this product. Users belonging to organizations with this product active inherit these capabilities.</p>
                </div>
                <div style="font-size: 12px; font-weight: 700; color: #4f46e5;">
                    Changes apply immediately upon save
                </div>
            </div>

            @foreach ($permissionsByCategory as $category => $perms)
                <div style="margin-bottom: 20px; border: 1px solid var(--line); border-radius: 8px; padding: 14px; background: #fafafa;">
                    <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #334155; margin-bottom: 10px;">
                        {{ strtoupper($category) }} DOMAIN PERMISSIONS
                    </div>
                    <div class="grid grid-2" style="gap: 10px;">
                        @foreach ($perms as $perm)
                            @php
                                $isChecked = in_array($perm->id, old('permissions', $productPermissions));
                            @endphp
                            <label style="display: flex; align-items: flex-start; gap: 10px; background: {{ $isChecked ? '#f0fdf4' : '#ffffff' }}; border: 1px solid {{ $isChecked ? '#86efac' : '#e2e8f0' }}; border-radius: 6px; padding: 8px 12px; cursor: pointer;">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" style="width: auto; margin-top: 3px;" {{ $isChecked ? 'checked' : '' }}>
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
                Save Changes
            </button>
            <a class="button" href="{{ route('admin.super.products') }}" style="width: auto; padding: 12px 24px; background: #ffffff; color: var(--ink); border: 1px solid var(--line);">
                Cancel
            </a>
        </div>
    </form>
@endsection
