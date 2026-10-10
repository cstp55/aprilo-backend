<?php

namespace Database\Seeders;

use App\Models\EcommerceConnection;
use App\Models\EcommerceLicense;
use App\Models\EcommerceOrder;
use App\Models\EcommerceProduct;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Permissions Catalog across all products & modules
        $permissions = [
            // Core permissions
            ['category' => 'core', 'slug' => 'dashboard.view', 'name' => 'View Dashboard', 'description' => 'Access the main admin console.'],
            ['category' => 'core', 'slug' => 'analytics.view', 'name' => 'View Analytics', 'description' => 'Access usage, token, and estimated savings analytics.'],
            ['category' => 'core', 'slug' => 'billing.view', 'name' => 'View Billing & Subscriptions', 'description' => 'View subscription plans and invoice history.'],
            ['category' => 'core', 'slug' => 'billing.manage', 'name' => 'Manage Billing', 'description' => 'Upgrade plans, change billing models, and pay invoices.'],
            ['category' => 'core', 'slug' => 'logs.view', 'name' => 'View Interaction Logs', 'description' => 'Audit Q&A interactions, confidence scores, and ratings.'],
            ['category' => 'core', 'slug' => 'roles.manage', 'name' => 'Manage Roles & RBAC', 'description' => 'Create custom roles and assign granular product permissions.'],
            ['category' => 'core', 'slug' => 'users.manage', 'name' => 'Manage Organization Users', 'description' => 'Invite, edit, and deactivate organization members.'],

            // Aprilo AI & Chatbot permissions
            ['category' => 'chatbot', 'slug' => 'widget.settings', 'name' => 'Configure Widget & AI', 'description' => 'Customize assistant branding, prompt instructions, and restrictions.'],
            ['category' => 'chatbot', 'slug' => 'widget.script', 'name' => 'Access Embed Script', 'description' => 'Copy client-side script tags for web deployments.'],
            ['category' => 'chatbot', 'slug' => 'live_chat.view', 'name' => 'Monitor Live Agent Chat', 'description' => 'Monitor customer and employee conversations in real-time.'],
            ['category' => 'chatbot', 'slug' => 'plugins.connect', 'name' => 'Connect Communication Channels', 'description' => 'Configure Microsoft Teams, Slack, Skype, and WhatsApp.'],
            ['category' => 'chatbot', 'slug' => 'chatbot.channels.manage', 'name' => 'Manage Chatbot Channels', 'description' => 'Configure bots for Skype, Teams, and WhatsApp Cloud API.'],
            ['category' => 'chatbot', 'slug' => 'chatbot.custom_automations', 'name' => 'Build Custom Automations', 'description' => 'Configure custom webhooks, triggers, and automated workflows.'],

            // Aprilo Support (SaaS & IT) permissions
            ['category' => 'support', 'slug' => 'support.dashboard.view', 'name' => 'View Support Desk Dashboard', 'description' => 'Access support metrics, resolution times, and CSAT scores.'],
            ['category' => 'support', 'slug' => 'escalations.view', 'name' => 'View Escalation Queue', 'description' => 'Inspect unresolved tickets and customer escalations.'],
            ['category' => 'support', 'slug' => 'escalations.manage', 'name' => 'Manage Escalations', 'description' => 'Assign tickets, add resolution notes, and resolve issues.'],
            ['category' => 'support', 'slug' => 'support.tickets.view', 'name' => 'View Technical Tickets', 'description' => 'Inspect SaaS bug tickets and developer escalations.'],
            ['category' => 'support', 'slug' => 'support.tickets.manage', 'name' => 'Manage Technical Tickets', 'description' => 'Route, prioritize, and close technical support tickets.'],
            ['category' => 'support', 'slug' => 'support.sla.manage', 'name' => 'Manage SLA Tracking', 'description' => 'Configure response SLAs and automatic escalation alerts.'],
            ['category' => 'support', 'slug' => 'support.settings.manage', 'name' => 'Manage Support Settings', 'description' => 'Configure public portal branding, tracking, and categories.'],
            ['category' => 'support', 'slug' => 'support.forms.manage', 'name' => 'Manage Dynamic Ticket Forms', 'description' => 'Build, edit, and publish dynamic customer intake forms.'],
            ['category' => 'support', 'slug' => 'support.diagnostics.view', 'name' => 'Inspect Diagnostic Logs', 'description' => 'View diagnostic context, API manuals, and runbooks.'],

            // Aprilo HR permissions
            ['category' => 'hr', 'slug' => 'hr.dashboard.view', 'name' => 'View HR Dashboard', 'description' => 'Access HR analytics, employee usage charts, and handbook stats.'],
            ['category' => 'hr', 'slug' => 'knowledge.sources.view', 'name' => 'View HR Knowledge Base', 'description' => 'View uploaded HR documents, handbooks, and policy summaries.'],
            ['category' => 'hr', 'slug' => 'knowledge.sources.manage', 'name' => 'Upload & Index Policy Sources', 'description' => 'Upload and index PDFs, DOCX, and handbooks for employee queries.'],
            ['category' => 'hr', 'slug' => 'employee.leaves.manage', 'name' => 'Manage Leave Requests', 'description' => 'Approve or reject employee leave applications.'],
            ['category' => 'hr', 'slug' => 'employee.wfh.manage', 'name' => 'Manage WFH Requests', 'description' => 'Approve or reject work-from-home requests.'],
            ['category' => 'hr', 'slug' => 'employee.validate', 'name' => 'Validate Employee Identity', 'description' => 'Verify employee badge IDs and credential validity.'],
            ['category' => 'hr', 'slug' => 'employee.idcards.manage', 'name' => 'Issue & Manage ID Cards', 'description' => 'Issue digital ID cards and manage card expiration.'],
            ['category' => 'hr', 'slug' => 'employee.onboarding.manage', 'name' => 'Manage Onboarding Automations', 'description' => 'Automate candidate onboarding checklist and verification.'],
            ['category' => 'hr', 'slug' => 'employee.private_queries.view', 'name' => 'Private Employee Queries', 'description' => 'Handle authenticated employee policy questions with privacy.'],

            // Aprilo Sales & CRM permissions
            ['category' => 'sales', 'slug' => 'sales.dashboard.view', 'name' => 'View Sales Dashboard', 'description' => 'Track lead conversions, campaign ROI, and response rates.'],
            ['category' => 'sales', 'slug' => 'sales.leads.view', 'name' => 'View Sales Leads', 'description' => 'Inspect inbound inquiries qualified by AI sales bot.'],
            ['category' => 'sales', 'slug' => 'sales.campaigns.manage', 'name' => 'Manage Sales Outreach', 'description' => 'Create and trigger targeted sales outreach sequences.'],
            ['category' => 'sales', 'slug' => 'sales.crm.view', 'name' => 'View CRM Pipelines', 'description' => 'Inspect customer contacts and opportunity deal stages.'],
            ['category' => 'sales', 'slug' => 'sales.crm.manage', 'name' => 'Manage CRM Records', 'description' => 'Update CRM contacts and export pipeline opportunities.'],
            ['category' => 'sales', 'slug' => 'whatsapp.campaigns.manage', 'name' => 'Manage WhatsApp Broadcasts', 'description' => 'Design templates and launch official Meta WhatsApp campaigns.'],

            // Aprilo E-commerce & D2C permissions
            ['category' => 'ecommerce', 'slug' => 'ecommerce.dashboard.view', 'name' => 'View E-commerce Dashboard', 'description' => 'Access store sales, sync health, and catalog metrics.'],
            ['category' => 'ecommerce', 'slug' => 'ecommerce.platforms.connect', 'name' => 'Connect E-commerce Stores', 'description' => 'Connect Magento 2, Shopify, and WooCommerce stores.'],
            ['category' => 'ecommerce', 'slug' => 'ecommerce.products.sync', 'name' => 'Synchronize Products', 'description' => 'Run product catalog ingestion and inventory synchronization.'],
            ['category' => 'ecommerce', 'slug' => 'ecommerce.orders.sync', 'name' => 'Synchronize Orders', 'description' => 'Sync customer orders, tracking numbers, and delivery status.'],
            ['category' => 'ecommerce', 'slug' => 'ecommerce.autosync.manage', 'name' => 'Manage Auto-Sync Settings', 'description' => 'Configure sync intervals and cron triggers.'],
            ['category' => 'ecommerce', 'slug' => 'ecommerce.embeddings.refresh', 'name' => 'Refresh Product Embeddings', 'description' => 'Re-generate vector embeddings for AI catalog search.'],
            ['category' => 'ecommerce', 'slug' => 'ecommerce.licenses.manage', 'name' => 'Manage Plugin Licenses', 'description' => 'Generate and validate Magento and Shopify plugin license keys.'],

            // Aprilo IT Infrastructure permissions
            ['category' => 'infrastructure', 'slug' => 'infra.dashboard.view', 'name' => 'View Infrastructure Ops', 'description' => 'Monitor datacenter incidents and runbook query health.'],
            ['category' => 'infrastructure', 'slug' => 'infra.runbooks.view', 'name' => 'Search Technical Runbooks', 'description' => 'Search datacenter runbooks and server maintenance guides.'],
            ['category' => 'infrastructure', 'slug' => 'infra.incidents.manage', 'name' => 'Route Infrastructure Incidents', 'description' => 'Intake severity alerts and route to network/server engineers.'],
            ['category' => 'infrastructure', 'slug' => 'infra.slas.track', 'name' => 'Track Datacenter SLAs', 'description' => 'Track uptime SLAs and incident response milestones.'],
            ['category' => 'infrastructure', 'slug' => 'infra.shift_handover.manage', 'name' => 'Manage Shift Handovers', 'description' => 'Coordinate shift handover notes and on-call rotations.'],

            // Aprilo Banking & Financial Services permissions
            ['category' => 'financial', 'slug' => 'banking.dashboard.view', 'name' => 'View FinTech Dashboard', 'description' => 'Review regulated inquiries and audit compliance reports.'],
            ['category' => 'financial', 'slug' => 'banking.compliance.audit', 'name' => 'Audit Compliance Logs', 'description' => 'Verify that responses adhere strictly to approved documentation.'],
            ['category' => 'financial', 'slug' => 'banking.intake.route', 'name' => 'Route Financial Complaints', 'description' => 'Intake customer service requests with verified identity.'],
            ['category' => 'financial', 'slug' => 'banking.verified_faq.view', 'name' => 'Search Approved Banking FAQs', 'description' => 'Search verified financial product and regulatory terms.'],

            // Super Admin Global permissions
            ['category' => 'super_admin', 'slug' => 'super_admin.dashboard.view', 'name' => 'Global Super Dashboard', 'description' => 'View cross-organization platform metrics and revenue.'],
            ['category' => 'super_admin', 'slug' => 'super_admin.organizations.manage', 'name' => 'Manage All Organizations', 'description' => 'Inspect, create, and configure any tenant organization.'],
            ['category' => 'super_admin', 'slug' => 'super_admin.products.manage', 'name' => 'Manage Products & Permissions', 'description' => 'Create products, configure pricing, and assign permissions.'],
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

        // 2. Link Permissions to Products in Database (product_permissions)
        $productMappings = [
            'aprilo-ai' => ['dashboard.view', 'analytics.view', 'widget.settings', 'widget.script', 'live_chat.view', 'chatbot.custom_automations', 'knowledge.sources.view', 'knowledge.sources.manage', 'escalations.view', 'escalations.manage', 'logs.view'],
            'aprilo-chatbot' => ['dashboard.view', 'widget.settings', 'widget.script', 'live_chat.view', 'plugins.connect', 'chatbot.channels.manage', 'chatbot.custom_automations', 'logs.view'],
            'aprilo-support' => ['dashboard.view', 'support.dashboard.view', 'support.tickets.view', 'support.tickets.manage', 'escalations.view', 'escalations.manage', 'support.sla.manage', 'support.settings.manage', 'support.forms.manage', 'support.diagnostics.view', 'knowledge.sources.view', 'logs.view'],
            'aprilo-sales' => ['dashboard.view', 'sales.dashboard.view', 'sales.leads.view', 'sales.campaigns.manage', 'sales.crm.view', 'sales.crm.manage', 'whatsapp.campaigns.manage', 'logs.view'],
            'aprilo-hr' => ['dashboard.view', 'hr.dashboard.view', 'knowledge.sources.view', 'knowledge.sources.manage', 'employee.leaves.manage', 'employee.wfh.manage', 'employee.validate', 'employee.idcards.manage', 'employee.onboarding.manage', 'employee.private_queries.view', 'logs.view'],
            'aprilo-ecommerce' => ['dashboard.view', 'ecommerce.dashboard.view', 'ecommerce.platforms.connect', 'ecommerce.products.sync', 'ecommerce.orders.sync', 'ecommerce.autosync.manage', 'ecommerce.embeddings.refresh', 'ecommerce.licenses.manage', 'logs.view'],
            'aprilo-magento-suite' => ['dashboard.view', 'ecommerce.dashboard.view', 'ecommerce.products.sync', 'ecommerce.orders.sync', 'ecommerce.embeddings.refresh', 'ecommerce.licenses.manage'],
            'aprilo-shopify-app' => ['dashboard.view', 'ecommerce.dashboard.view', 'ecommerce.products.sync', 'ecommerce.orders.sync', 'ecommerce.licenses.manage'],
            'aprilo-infrastructure' => ['dashboard.view', 'infra.dashboard.view', 'infra.runbooks.view', 'infra.incidents.manage', 'infra.slas.track', 'infra.shift_handover.manage', 'logs.view'],
            'aprilo-fintech' => ['dashboard.view', 'banking.dashboard.view', 'banking.compliance.audit', 'banking.intake.route', 'banking.verified_faq.view', 'logs.view'],
        ];

        foreach ($productMappings as $productSlug => $permSlugs) {
            $product = Product::where('slug', $productSlug)->first();
            if ($product) {
                $ids = [];
                foreach ($permSlugs as $slug) {
                    if (isset($permissionModels[$slug])) {
                        $ids[] = $permissionModels[$slug]->id;
                    }
                }
                $product->permissions()->sync($ids);
            }
        }

        // 3. System Roles & Granular Permissions
        $superAdminRole = Role::updateOrCreate(
            ['slug' => 'super_admin', 'organization_id' => null],
            [
                'name' => 'Super Administrator',
                'description' => 'Platform owner with full unrestricted system-wide access to all products and settings.',
                'is_system' => true,
            ]
        );
        $superAdminRole->permissions()->sync(array_values(array_map(fn ($p) => $p->id, $permissionModels)));

        $hrAdminRole = Role::updateOrCreate(
            ['slug' => 'hr_admin', 'organization_id' => null],
            [
                'name' => 'HR Administrator',
                'description' => 'Manages HR AI knowledge, employee onboarding, leaves, WFH, and digital ID cards.',
                'is_system' => true,
            ]
        );
        $hrPerms = array_filter($permissionModels, fn ($p) => in_array($p->category, ['core', 'hr', 'chatbot'], true));
        $hrAdminRole->permissions()->sync(array_values(array_map(fn ($p) => $p->id, $hrPerms)));

        $ecommerceAdminRole = Role::updateOrCreate(
            ['slug' => 'ecommerce_admin', 'organization_id' => null],
            [
                'name' => 'E-commerce Administrator',
                'description' => 'Manages store connectors (Magento/Shopify), catalog sync, embeddings, and plugin licenses.',
                'is_system' => true,
            ]
        );
        $ecomPerms = array_filter($permissionModels, fn ($p) => in_array($p->category, ['core', 'ecommerce'], true));
        $ecommerceAdminRole->permissions()->sync(array_values(array_map(fn ($p) => $p->id, $ecomPerms)));

        $supportAdminRole = Role::updateOrCreate(
            ['slug' => 'support_admin', 'organization_id' => null],
            [
                'name' => 'IT Support Administrator',
                'description' => 'Manages SaaS tickets, diagnostic knowledge base, SLA tracking, and engineer escalations.',
                'is_system' => true,
            ]
        );
        $supportPerms = array_filter($permissionModels, fn ($p) => in_array($p->category, ['core', 'support', 'chatbot'], true));
        $supportAdminRole->permissions()->sync(array_values(array_map(fn ($p) => $p->id, $supportPerms)));

        $salesAdminRole = Role::updateOrCreate(
            ['slug' => 'sales_admin', 'organization_id' => null],
            [
                'name' => 'Sales & CRM Manager',
                'description' => 'Manages WhatsApp broadcast campaigns, AI lead qualification, and CRM contacts.',
                'is_system' => true,
            ]
        );
        $salesPerms = array_filter($permissionModels, fn ($p) => in_array($p->category, ['core', 'sales'], true));
        $salesAdminRole->permissions()->sync(array_values(array_map(fn ($p) => $p->id, $salesPerms)));

        $ownerRole = Role::updateOrCreate(
            ['slug' => 'owner', 'organization_id' => null],
            [
                'name' => 'Organization Owner',
                'description' => 'Full administrative access over all products licensed by the organization.',
                'is_system' => true,
            ]
        );
        $ownerPerms = array_filter($permissionModels, fn ($p) => $p->category !== 'super_admin');
        $ownerRole->permissions()->sync(array_values(array_map(fn ($p) => $p->id, $ownerPerms)));

        $employeeRole = Role::updateOrCreate(
            ['slug' => 'employee', 'organization_id' => null],
            [
                'name' => 'Employee / Staff',
                'description' => 'Standard user with employee self-service and chat assistance.',
                'is_system' => true,
            ]
        );

        // 4. Seed Demo Organizations & Users
        $demoOrg = Organization::firstOrCreate(
            ['name' => 'Demo Company'],
            ['status' => 'active']
        );

        // Grant Demo Org access to Aprilo AI, Aprilo HR, and Aprilo Support
        $productsToAttach = Product::whereIn('slug', ['aprilo-ai', 'aprilo-hr', 'aprilo-support'])->get();
        foreach ($productsToAttach as $prod) {
            $demoOrg->products()->syncWithoutDetaching([
                $prod->id => ['status' => 'active', 'expires_at' => now()->addYears(2)],
            ]);
        }

        // Link HR Admin User
        User::updateOrCreate(
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
        User::updateOrCreate(
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
        User::updateOrCreate(
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

        $ecomProduct = Product::where('slug', 'aprilo-ecommerce')->first();
        if ($ecomProduct) {
            $ecomOrg->products()->syncWithoutDetaching([
                $ecomProduct->id => ['status' => 'active', 'expires_at' => now()->addYears(2)],
            ]);
        }

        OrganizationSetting::firstOrCreate(
            ['organization_id' => $ecomOrg->id],
            [
                'assistant_name' => 'StyleCraft Shopping Bot',
                'assistant_status' => 'active',
                'chatbot_icon' => 'star',
                'chatbot_color_palette' => '#2563eb',
            ]
        );

        User::updateOrCreate(
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

        // 5. Seed Demo E-commerce Connection & Sample Data
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
        ];

        foreach ($sampleProducts as $prod) {
            EcommerceProduct::updateOrCreate(
                [
                    'connection_id' => $magentoConnection->id,
                    'external_product_id' => $prod['external_product_id'],
                ],
                array_merge($prod, [
                    'organization_id' => $ecomOrg->id,
                    'embedding' => array_fill(0, 32, 0.1),
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
