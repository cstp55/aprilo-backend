<?php

namespace Database\Seeders;

use App\Models\EcommerceConnection;
use App\Models\EcommerceLicense;
use App\Models\EcommerceOrder;
use App\Models\EcommerceProduct;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Permissions Catalog
        $permissions = [
            // Core permissions
            ['category' => 'core', 'slug' => 'dashboard.view', 'name' => 'View Dashboard', 'description' => 'Access the main admin dashboard.'],
            ['category' => 'core', 'slug' => 'analytics.view', 'name' => 'View Analytics', 'description' => 'Access usage, token, and estimated savings analytics.'],
            ['category' => 'core', 'slug' => 'billing.view', 'name' => 'View Billing & Invoices', 'description' => 'View subscription plans and invoice history.'],
            ['category' => 'core', 'slug' => 'billing.manage', 'name' => 'Manage Billing', 'description' => 'Upgrade plans, change billing models, and close cycles.'],
            ['category' => 'core', 'slug' => 'widget.settings', 'name' => 'Configure Widget', 'description' => 'Customize assistant branding, AI prompts, and strict restrictions.'],
            ['category' => 'core', 'slug' => 'widget.script', 'name' => 'Access Widget Embed Script', 'description' => 'View and copy client-side script tags for web deployments.'],
            ['category' => 'core', 'slug' => 'live_chat.view', 'name' => 'View Live Agent Chat', 'description' => 'Monitor customer and employee conversations in real-time.'],
            ['category' => 'core', 'slug' => 'escalations.view', 'name' => 'View Escalation Queue', 'description' => 'Inspect unresolved and sensitive ticket queues.'],
            ['category' => 'core', 'slug' => 'escalations.manage', 'name' => 'Manage Escalations', 'description' => 'Assign tickets, add resolution notes, and resolve issues.'],
            ['category' => 'core', 'slug' => 'logs.view', 'name' => 'View Interaction Logs', 'description' => 'Audit Q&A interactions, confidence scores, and ratings.'],
            ['category' => 'core', 'slug' => 'roles.manage', 'name' => 'Manage Roles & Permissions', 'description' => 'Create custom roles and assign granular permissions.'],
            ['category' => 'core', 'slug' => 'users.manage', 'name' => 'Manage Users', 'description' => 'Invite, edit, and deactivate organization users.'],
            ['category' => 'core', 'slug' => 'plugins.connect', 'name' => 'Connect Communication Channels', 'description' => 'Configure Microsoft Teams, Slack, Skype, and WhatsApp.'],

            // HR permissions
            ['category' => 'hr', 'slug' => 'hr.dashboard.view', 'name' => 'View HR Dashboard', 'description' => 'Access HR analytics, employee usage charts, and source indexing.'],
            ['category' => 'hr', 'slug' => 'knowledge.sources.view', 'name' => 'View Knowledge Base', 'description' => 'View uploaded HR documents and indexed chunk summaries.'],
            ['category' => 'hr', 'slug' => 'knowledge.sources.manage', 'name' => 'Upload & Index Sources', 'description' => 'Upload and index PDFs, DOCX, TXT, and Markdown handbooks.'],
            ['category' => 'hr', 'slug' => 'employee.leaves.manage', 'name' => 'Manage Leave Requests', 'description' => 'Approve or reject employee leave applications.'],
            ['category' => 'hr', 'slug' => 'employee.wfh.manage', 'name' => 'Manage WFH Requests', 'description' => 'Approve or reject work-from-home requests.'],
            ['category' => 'hr', 'slug' => 'employee.validate', 'name' => 'Validate Employee Identity', 'description' => 'Verify employee badge IDs and profile credentials.'],
            ['category' => 'hr', 'slug' => 'employee.idcards.manage', 'name' => 'Issue & Manage ID Cards', 'description' => 'Issue digital ID cards and manage card validity.'],

            // E-commerce permissions
            ['category' => 'ecommerce', 'slug' => 'ecommerce.dashboard.view', 'name' => 'View E-commerce Dashboard', 'description' => 'Access store sales, sync health, and catalog metrics.'],
            ['category' => 'ecommerce', 'slug' => 'ecommerce.platforms.connect', 'name' => 'Connect E-commerce Stores', 'description' => 'Connect Magento, Shopify, and WooCommerce stores.'],
            ['category' => 'ecommerce', 'slug' => 'ecommerce.products.sync', 'name' => 'Synchronize Products', 'description' => 'Run product catalog ingestion and inventory synchronization.'],
            ['category' => 'ecommerce', 'slug' => 'ecommerce.orders.sync', 'name' => 'Synchronize Orders', 'description' => 'Sync customer orders and order status tracking.'],
            ['category' => 'ecommerce', 'slug' => 'ecommerce.autosync.manage', 'name' => 'Manage Auto-Sync Settings', 'description' => 'Configure sync intervals and cron triggers.'],
            ['category' => 'ecommerce', 'slug' => 'ecommerce.embeddings.refresh', 'name' => 'Refresh Product Embeddings', 'description' => 'Re-generate vector embeddings for AI catalog search.'],
            ['category' => 'ecommerce', 'slug' => 'ecommerce.licenses.manage', 'name' => 'Manage Plugin Licenses', 'description' => 'Generate and validate Magento and Shopify plugin license keys.'],

            // Super Admin permissions
            ['category' => 'super_admin', 'slug' => 'super_admin.dashboard.view', 'name' => 'Global Super Dashboard', 'description' => 'View cross-organization platform metrics and revenue.'],
            ['category' => 'super_admin', 'slug' => 'super_admin.organizations.manage', 'name' => 'Manage All Organizations', 'description' => 'Inspect and configure any tenant organization.'],
            ['category' => 'super_admin', 'slug' => 'super_admin.revenue.view', 'name' => 'View Global Revenue', 'description' => 'Inspect cross-tenant billing and aggregated revenue.'],
            ['category' => 'super_admin', 'slug' => 'super_admin.global_logs.view', 'name' => 'View Global Audit Logs', 'description' => 'Audit cross-tenant search and live interaction logs.'],
        ];

        $permissionModels = [];
        foreach ($permissions as $perm) {
            $permissionModels[$perm['slug']] = Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                $perm
            );
        }

        // 2. Seed System Roles (Global templates)
        $superAdminRole = Role::updateOrCreate(
            ['slug' => 'super_admin', 'organization_id' => null],
            [
                'name' => 'Super Administrator',
                'description' => 'Platform owner with full unrestricted system-wide access.',
                'is_system' => true,
            ]
        );
        $superAdminRole->permissions()->sync(array_values(array_map(fn ($p) => $p->id, $permissionModels)));

        $hrAdminRole = Role::updateOrCreate(
            ['slug' => 'hr_admin', 'organization_id' => null],
            [
                'name' => 'HR Administrator',
                'description' => 'Manages HR AI knowledge, live chat, employee workflows, and billing.',
                'is_system' => true,
            ]
        );
        $hrPermissions = array_filter($permissionModels, fn ($p) => in_array($p->category, ['core', 'hr'], true));
        $hrAdminRole->permissions()->sync(array_values(array_map(fn ($p) => $p->id, $hrPermissions)));

        $ecommerceAdminRole = Role::updateOrCreate(
            ['slug' => 'ecommerce_admin', 'organization_id' => null],
            [
                'name' => 'E-commerce Administrator',
                'description' => 'Manages store connectors (Magento/Shopify), catalog sync, embeddings, and plugin licenses.',
                'is_system' => true,
            ]
        );
        $ecomPermissions = array_filter($permissionModels, fn ($p) => in_array($p->category, ['core', 'ecommerce'], true));
        $ecommerceAdminRole->permissions()->sync(array_values(array_map(fn ($p) => $p->id, $ecomPermissions)));

        $ownerRole = Role::updateOrCreate(
            ['slug' => 'owner', 'organization_id' => null],
            [
                'name' => 'Organization Owner',
                'description' => 'Full administrative access over tenant services and configurations.',
                'is_system' => true,
            ]
        );
        $ownerPermissions = array_filter($permissionModels, fn ($p) => in_array($p->category, ['core', 'hr', 'ecommerce'], true));
        $ownerRole->permissions()->sync(array_values(array_map(fn ($p) => $p->id, $ownerPermissions)));

        $employeeRole = Role::updateOrCreate(
            ['slug' => 'employee', 'organization_id' => null],
            [
                'name' => 'Employee / Staff',
                'description' => 'Standard user with employee portal chat and workflow self-service.',
                'is_system' => true,
            ]
        );

        // 3. Seed Demo Organizations & Users
        $demoOrg = Organization::firstOrCreate(
            ['name' => 'Demo Company'],
            ['status' => 'active']
        );

        // Link HR Admin User
        $hrUser = User::updateOrCreate(
            ['email' => 'hr@example.com'],
            [
                'organization_id' => $demoOrg->id,
                'role_id' => $hrAdminRole->id,
                'name' => 'Helen HR',
                'password' => Hash::make('password'),
                'role' => 'hr_admin',
                'status' => 'active',
                'employee_id' => 'EMP-HR-001',
            ]
        );

        // Link Super Admin User
        $superAdminUser = User::updateOrCreate(
            ['email' => 'superadmin@aprilo.ai'],
            [
                'organization_id' => $demoOrg->id,
                'role_id' => $superAdminRole->id,
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'status' => 'active',
                'employee_id' => 'APRILO-SUPER-01',
            ]
        );

        // Link Standard Employee
        $employeeUser = User::updateOrCreate(
            ['email' => 'employee@example.com'],
            [
                'organization_id' => $demoOrg->id,
                'role_id' => $employeeRole->id,
                'name' => 'Edward Employee',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'status' => 'active',
                'employee_id' => 'EMP-2026-987',
            ]
        );

        // Create Demo E-commerce Organization & E-commerce Admin User
        $ecomOrg = Organization::firstOrCreate(
            ['name' => 'StyleCraft Commerce'],
            ['status' => 'active']
        );

        OrganizationSetting::firstOrCreate(
            ['organization_id' => $ecomOrg->id],
            [
                'assistant_name' => 'StyleCraft Shopping Bot',
                'assistant_status' => 'active',
                'chatbot_icon' => 'star',
                'chatbot_color_palette' => '#2563eb',
            ]
        );

        $ecomOwnerUser = User::updateOrCreate(
            ['email' => 'ecommerce_owner@example.com'],
            [
                'organization_id' => $ecomOrg->id,
                'role_id' => $ownerRole->id,
                'name' => 'Olivia Ecommerce Owner',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'status' => 'active',
                'employee_id' => 'ECOM-OWNER-01',
            ]
        );

        $ecomUser = User::updateOrCreate(
            ['email' => 'ecommerce@example.com'],
            [
                'organization_id' => $ecomOrg->id,
                'role_id' => $ecommerceAdminRole->id,
                'name' => 'Evan Ecommerce',
                'password' => Hash::make('password'),
                'role' => 'ecommerce_admin',
                'status' => 'active',
                'employee_id' => 'ECOM-ADMIN-01',
            ]
        );

        // 4. Seed Demo E-commerce Connections, Products, and License Keys
        $magentoConnection = EcommerceConnection::firstOrCreate(
            [
                'organization_id' => $ecomOrg->id,
                'platform' => 'magento',
            ],
            [
                'store_name' => 'StyleCraft Magento Store',
                'store_url' => 'https://magento.stylecraft.example.com',
                'api_key' => 'magento_api_consumer_key_xyz987',
                'api_secret' => 'magento_secret_token_abc123',
                'status' => 'connected',
                'auto_sync_enabled' => true,
                'sync_interval' => 'daily',
                'last_synced_at' => now()->subHours(2),
                'metadata' => [
                    'magento_version' => '2.4.6',
                    'extension_version' => '1.2.0',
                ],
            ]
        );

        EcommerceLicense::firstOrCreate(
            [
                'organization_id' => $ecomOrg->id,
                'license_key' => 'APR-MAG-89X72-KL901-77PQA',
            ],
            [
                'connection_id' => $magentoConnection->id,
                'platform' => 'magento',
                'domain' => 'magento.stylecraft.example.com',
                'status' => 'active',
                'max_stores' => 3,
                'expires_at' => now()->addYear(),
                'created_by' => $ecomUser->id,
            ]
        );

        $sampleProducts = [
            [
                'external_product_id' => 'PRD-MAG-101',
                'title' => 'Slim Fit Denim Jacket',
                'sku' => 'SC-DNM-001',
                'description' => 'Classic vintage wash denim jacket made from 100% organic cotton.',
                'price' => 89.99,
                'inventory_quantity' => 45,
                'status' => 'active',
            ],
            [
                'external_product_id' => 'PRD-MAG-102',
                'title' => 'Waterproof Leather Hiking Boots',
                'sku' => 'SC-BOT-002',
                'description' => 'Durable all-weather leather hiking boots with Vibram grip sole.',
                'price' => 149.50,
                'inventory_quantity' => 20,
                'status' => 'active',
            ],
            [
                'external_product_id' => 'PRD-MAG-103',
                'title' => 'Merino Wool Crewneck Sweater',
                'sku' => 'SC-SWT-003',
                'description' => 'Ultra-soft thermal merino wool sweater designed for everyday warmth.',
                'price' => 65.00,
                'inventory_quantity' => 60,
                'status' => 'active',
            ],
        ];

        foreach ($sampleProducts as $prod) {
            EcommerceProduct::updateOrCreate(
                [
                    'connection_id' => $magentoConnection->id,
                    'external_product_id' => $prod['external_product_id'],
                ],
                array_merge($prod, [
                    'organization_id' => $ecomOrg->id,
                    'embedding' => array_fill(0, 32, 0.1), // lightweight mock vector
                ])
            );
        }

        EcommerceOrder::updateOrCreate(
            [
                'connection_id' => $magentoConnection->id,
                'external_order_id' => 'ORD-98421',
            ],
            [
                'organization_id' => $ecomOrg->id,
                'order_number' => '#100098421',
                'customer_name' => 'Sarah Connor',
                'customer_email' => 'sarah.c@example.com',
                'total_amount' => 239.49,
                'currency' => 'USD',
                'order_status' => 'processing',
                'financial_status' => 'paid',
            ]
        );
    }
}
