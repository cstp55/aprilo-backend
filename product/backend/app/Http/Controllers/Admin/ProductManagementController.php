<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        $products = Product::with(['permissions', 'plans', 'organizations'])
            ->withCount(['permissions', 'plans', 'organizations'])
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $allPermissions = Permission::orderBy('category')->orderBy('name')->get()->groupBy('category');
        $totalPermissionsCount = Permission::count();
        $totalActiveOrgsCount = Organization::where('status', 'active')->count();

        return view('admin.super.products.index', compact(
            'products',
            'allPermissions',
            'totalPermissionsCount',
            'totalActiveOrgsCount'
        ));
    }

    public function create(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        $permissionsByCategory = Permission::orderBy('category')->orderBy('name')->get()->groupBy('category');

        $categories = [
            'ai_support' => 'Aprilo AI (Core Autonomous Support)',
            'chatbot' => 'Aprilo Chatbot (Omnichannel Suite)',
            'support_saas' => 'Aprilo Support (SaaS & IT Desk)',
            'sales_crm' => 'Aprilo Sales & CRM (Marketing & Outreach)',
            'hr' => 'Aprilo HR (Employee Experience & Automation)',
            'ecommerce' => 'Aprilo Commerce (Magento / Shopify / D2C)',
            'infrastructure' => 'Aprilo Infrastructure & Data Centers',
            'financial' => 'Aprilo FinTech & Banking',
            'enterprise' => 'Custom Enterprise Solution',
        ];

        return view('admin.super.products.create', compact('permissionsByCategory', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'category' => ['required', 'string', 'max:100'],
            'product_type' => ['required', 'string', Rule::in(['subscription', 'one_time_license', 'enterprise'])],
            'platform' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'icon' => ['nullable', 'string', 'max:100'],
            'features' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['uuid', 'exists:permissions,id'],
        ]);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        if (Product::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::random(4);
        }

        // Process features from newline separated text
        $featuresArray = [];
        if (! empty($validated['features'])) {
            $featuresArray = array_values(array_filter(
                array_map('trim', explode("\n", $validated['features'])),
                fn ($line) => ! empty($line)
            ));
        }

        $product = Product::create([
            'slug' => $slug,
            'name' => $validated['name'],
            'category' => $validated['category'],
            'product_type' => $validated['product_type'],
            'platform' => $validated['platform'] ?? 'web',
            'price' => $validated['price'],
            'currency' => strtoupper($validated['currency']),
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? 'Boxes',
            'features' => $featuresArray,
            'status' => 'active',
            'is_active' => (bool) ($request->input('is_active', true)),
            'featured' => (bool) ($request->input('featured', false)),
            'sort_order' => (int) ($validated['sort_order'] ?? 10),
        ]);

        if (! empty($validated['permissions'])) {
            $product->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('admin.super.products')
            ->with('status', "Product '{$product->name}' created successfully with " . count($validated['permissions'] ?? []) . ' bundled permissions!');
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorizeSuperAdmin($request);

        $product->load('permissions');
        $productPermissions = $product->permissions->pluck('id')->toArray();
        $permissionsByCategory = Permission::orderBy('category')->orderBy('name')->get()->groupBy('category');

        $categories = [
            'ai_support' => 'Aprilo AI (Core Autonomous Support)',
            'chatbot' => 'Aprilo Chatbot (Omnichannel Suite)',
            'support_saas' => 'Aprilo Support (SaaS & IT Desk)',
            'sales_crm' => 'Aprilo Sales & CRM (Marketing & Outreach)',
            'hr' => 'Aprilo HR (Employee Experience & Automation)',
            'ecommerce' => 'Aprilo Commerce (Magento / Shopify / D2C)',
            'infrastructure' => 'Aprilo Infrastructure & Data Centers',
            'financial' => 'Aprilo FinTech & Banking',
            'enterprise' => 'Custom Enterprise Solution',
        ];

        return view('admin.super.products.edit', compact(
            'product',
            'productPermissions',
            'permissionsByCategory',
            'categories'
        ));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'product_type' => ['required', 'string', Rule::in(['subscription', 'one_time_license', 'enterprise'])],
            'platform' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'icon' => ['nullable', 'string', 'max:100'],
            'features' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'draft', 'archived'])],
            'is_active' => ['nullable', 'boolean'],
            'featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['uuid', 'exists:permissions,id'],
        ]);

        $featuresArray = [];
        if (! empty($validated['features'])) {
            $featuresArray = array_values(array_filter(
                array_map('trim', explode("\n", $validated['features'])),
                fn ($line) => ! empty($line)
            ));
        }

        $product->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'product_type' => $validated['product_type'],
            'platform' => $validated['platform'] ?? $product->platform,
            'price' => $validated['price'],
            'currency' => strtoupper($validated['currency']),
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? $product->icon,
            'features' => $featuresArray,
            'status' => $validated['status'],
            'is_active' => (bool) ($request->input('is_active', false)),
            'featured' => (bool) ($request->input('featured', false)),
            'sort_order' => (int) ($validated['sort_order'] ?? 10),
        ]);

        $product->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('admin.super.products')
            ->with('status', "Product '{$product->name}' updated successfully with " . count($validated['permissions'] ?? []) . ' bundled permissions!');
    }

    public function assignOrganizationProduct(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $organization->products()->syncWithoutDetaching([
            $product->id => [
                'status' => 'active',
                'expires_at' => $validated['expires_at'] ?? now()->addYears(2),
            ],
        ]);

        return back()->with('status', "Product '{$product->name}' successfully licensed to organization '{$organization->name}'!");
    }

    public function revokeOrganizationProduct(Request $request, Organization $organization, Product $product): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $organization->products()->detach($product->id);

        return back()->with('status', "Product '{$product->name}' access revoked for organization '{$organization->name}'.");
    }

    private function authorizeSuperAdmin(Request $request): void
    {
        abort_unless(
            $request->user() && ($request->user()->isSuperAdmin() || $request->user()->hasPermission('super_admin.products.manage')),
            403,
            'Super Administrator privileges required.'
        );
    }
}
