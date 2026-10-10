<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsAndPlansSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Aprilo AI (Website Chatbot, Support, AI + Human Agent, Custom Automations)
        $apriloAi = Product::updateOrCreate(
            ['slug' => 'aprilo-ai'],
            [
                'name' => 'Aprilo AI',
                'category' => 'ai_support',
                'product_type' => 'subscription',
                'platform' => 'web',
                'short_description' => 'Autonomous website AI assistant, intelligent support, human-agent escalation, and custom workflow automations.',
                'description' => 'Aprilo AI delivers autonomous website customer assistance, dynamic support routing, hybrid AI + human agent collaboration, and tailored business workflow automations powered by high-speed LLMs and vector RAG.',
                'icon' => 'Bot',
                'price' => 799.00,
                'currency' => 'INR',
                'status' => 'active',
                'featured' => true,
                'best_seller' => true,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    'Website Chatbot & Floating Widget',
                    'Autonomous AI Resolution + Human Escalation',
                    'Custom Workflow Automations & Webhooks',
                    'Vector Knowledge Base RAG & Semantic Search',
                    'Brand Personality & System Prompt Tuning',
                ],
                'metadata' => [
                    'trial_available' => true,
                    'trial_description' => '30 Days Free Trial with AutoPay Mandate',
                ],
            ]
        );

        Plan::updateOrCreate(
            ['product_id' => $apriloAi->id, 'slug' => 'starter'],
            [
                'name' => 'Aprilo AI Starter',
                'billing_cycle' => 'monthly',
                'price' => 799.00,
                'currency' => 'INR',
                'trial_period_days' => 30,
                'request_limit' => 50000,
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    '50,000 AI Sessions / month',
                    '30 Days Free Trial',
                    'Web Chatbot Widget',
                    'Custom Workflow Automations',
                    'Email Escalation Dispatch',
                ],
            ]
        );

        // 2. Aprilo Chatbot (Website Chatbot, Support, AI + Human, Channel Integrations: Teams, Skype, WhatsApp)
        $apriloChatbot = Product::updateOrCreate(
            ['slug' => 'aprilo-chatbot'],
            [
                'name' => 'Aprilo Chatbot',
                'category' => 'chatbot',
                'product_type' => 'subscription',
                'platform' => 'omnichannel',
                'short_description' => 'Omnichannel chatbot integrating website widget, WhatsApp, Microsoft Teams, Skype, and Slack with human-in-the-loop.',
                'description' => 'Engage customers and internal teams across any channel. Seamlessly connects web widgets with official WhatsApp Cloud API, Microsoft Teams bots, Skype, and Slack with real-time human agent handoff.',
                'icon' => 'MessageSquare',
                'price' => 999.00,
                'currency' => 'INR',
                'status' => 'active',
                'featured' => true,
                'best_seller' => false,
                'is_active' => true,
                'sort_order' => 2,
                'features' => [
                    'Omnichannel Integrations (WhatsApp, Teams, Skype, Slack)',
                    'Website Chatbot Floating Widget',
                    'Hybrid AI + Live Human Agent Routing',
                    'Rich Interactive Media & Quick Reply Buttons',
                    'Centralized Unified Conversation Inbox',
                ],
            ]
        );

        Plan::updateOrCreate(
            ['product_id' => $apriloChatbot->id, 'slug' => 'omnichannel-growth'],
            [
                'name' => 'Omnichannel Growth',
                'billing_cycle' => 'monthly',
                'price' => 999.00,
                'currency' => 'INR',
                'trial_period_days' => 14,
                'request_limit' => 100000,
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    '100,000 Messages / month',
                    'WhatsApp Cloud API + Microsoft Teams + Skype',
                    'Live Agent Takeover & Assignment',
                    'Auto-Translating Multi-language Support',
                ],
            ]
        );

        // 3. Aprilo Support (SaaS-based, IT Support, Ticket System, AI + Agent, Diagnostic RAG)
        $apriloSupport = Product::updateOrCreate(
            ['slug' => 'aprilo-support'],
            [
                'name' => 'Aprilo Support',
                'category' => 'support_saas',
                'product_type' => 'subscription',
                'platform' => 'saas',
                'short_description' => 'SaaS IT service desk, ticket triage, AI diagnostic runbook retrieval, and automated engineer escalations.',
                'description' => 'Designed for SaaS companies and IT service providers. Automatically classifies bugs, troubleshoots software configs from API docs, searches technical manuals, and generates engineering escalation tickets with diagnostic context.',
                'icon' => 'Headphones',
                'price' => 1499.00,
                'currency' => 'INR',
                'status' => 'active',
                'featured' => false,
                'best_seller' => true,
                'is_active' => true,
                'sort_order' => 3,
                'features' => [
                    'SaaS IT Ticket & Incident Desk',
                    'Technical Manual & API Doc Vector Search',
                    'Automatic Bug Classification & Diagnostic Intake',
                    'SLA Tracking & Severity Escalations',
                    'AI Co-Pilot for Human Support Engineers',
                ],
            ]
        );

        Plan::updateOrCreate(
            ['product_id' => $apriloSupport->id, 'slug' => 'it-desk-pro'],
            [
                'name' => 'IT Desk Pro',
                'billing_cycle' => 'monthly',
                'price' => 1499.00,
                'currency' => 'INR',
                'trial_period_days' => 30,
                'request_limit' => 150000,
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    '150,000 Technical Queries / month',
                    'Full Incident Ticket Lifecycle',
                    'API & Knowledge Base Ingestion',
                    'Engineering Escalation Connectors',
                    '99.9% Support SLA Guarantee',
                ],
            ]
        );

        // 4. Aprilo Sales & CRM (Marketing, Lead Generation, WhatsApp Broadcasts, CRM)
        $apriloSales = Product::updateOrCreate(
            ['slug' => 'aprilo-sales'],
            [
                'name' => 'Aprilo Sales & CRM',
                'category' => 'sales_crm',
                'product_type' => 'subscription',
                'platform' => 'web',
                'short_description' => 'AI sales outreach, WhatsApp marketing campaigns, lead qualification bots, and automated CRM pipelines.',
                'description' => 'Accelerate revenue with AI sales agents. Qualify inbound web visitors into pipeline leads, run official WhatsApp promotional broadcast campaigns, and automatically sync CRM contact records.',
                'icon' => 'TrendingUp',
                'price' => 1299.00,
                'currency' => 'INR',
                'status' => 'active',
                'featured' => false,
                'best_seller' => false,
                'is_active' => true,
                'sort_order' => 4,
                'features' => [
                    'WhatsApp Cloud Broadcast Campaigns',
                    'Lead Scoring & Inbound Qualification AI',
                    'CRM Contact Management & Deal Pipelines',
                    'Interactive Broadcast Templates with Meta Approvals',
                    'Campaign Analytics & Conversion Tracking',
                ],
            ]
        );

        Plan::updateOrCreate(
            ['product_id' => $apriloSales->id, 'slug' => 'sales-growth'],
            [
                'name' => 'Sales Growth Suite',
                'billing_cycle' => 'monthly',
                'price' => 1299.00,
                'currency' => 'INR',
                'trial_period_days' => 14,
                'request_limit' => 75000,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    '75,000 Campaign Messages / month',
                    'Broadcast Campaign Manager',
                    'CRM Lead Exporter & Webhooks',
                    'Automated Lead Qualification Agent',
                ],
            ]
        );

        // 5. Aprilo HR (Employee Support, Hiring Automation, Onboarding, Verification, Leaves, WFH, ID Cards)
        $apriloHr = Product::updateOrCreate(
            ['slug' => 'aprilo-hr'],
            [
                'name' => 'Aprilo HR',
                'category' => 'hr',
                'product_type' => 'subscription',
                'platform' => 'web',
                'short_description' => 'Complete internal HR AI portal: employee policy handbook RAG, hiring workflows, leave & WFH approvals, badge validation, and ID cards.',
                'description' => 'Automate human resources and employee self-service. Answers handbook policies, automates candidate onboarding and badge verification, manages leave & work-from-home requests, and issues digital employee ID cards.',
                'icon' => 'Users',
                'price' => 1199.00,
                'currency' => 'INR',
                'status' => 'active',
                'featured' => true,
                'best_seller' => false,
                'is_active' => true,
                'sort_order' => 5,
                'features' => [
                    'Employee Handbook & Policy RAG Search',
                    'Leave & Work-From-Home Approval Workflows',
                    'Employee Badge Identity Verification',
                    'Digital Employee ID Card Generation & PDF Export',
                    'Private Authenticated Queries with Access Control',
                    'Hiring & Onboarding Automations',
                ],
            ]
        );

        Plan::updateOrCreate(
            ['product_id' => $apriloHr->id, 'slug' => 'hr-enterprise'],
            [
                'name' => 'HR Operations Suite',
                'billing_cycle' => 'monthly',
                'price' => 1199.00,
                'currency' => 'INR',
                'trial_period_days' => 30,
                'request_limit' => 100000,
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    'Unlimited Employees Portal Access',
                    'Leave & WFH Management Workflows',
                    'Automated Handbook Document Ingestion',
                    'Digital ID Card Generator',
                    'Dedicated Role-based Access Control',
                ],
            ]
        );

        // 6. Aprilo Commerce - E-commerce & D2C (Magento 2, Shopify, Order Status, Delivery/Return FAQs)
        $apriloEcom = Product::updateOrCreate(
            ['slug' => 'aprilo-ecommerce'],
            [
                'name' => 'Aprilo Commerce (E-commerce & D2C)',
                'category' => 'ecommerce',
                'product_type' => 'subscription',
                'platform' => 'multichannel',
                'short_description' => 'Conversational commerce for Magento 2 & Shopify: real-time order tracking, delivery/returns FAQs, product catalog search, and cart recovery.',
                'description' => 'Transform your online store into an intelligent shopping assistant. Syncs Magento 2 and Shopify catalogs, answers customer order status and delivery inquiries, resolves return-policy questions, and recovers abandoned carts.',
                'icon' => 'ShoppingCart',
                'price' => 1999.00,
                'currency' => 'INR',
                'status' => 'active',
                'featured' => true,
                'best_seller' => true,
                'is_active' => true,
                'sort_order' => 6,
                'features' => [
                    'Real-Time Order Status & Shipping Tracking',
                    'Product Specifications & Compatibility Recommendations',
                    'Automated Return Policy & Refund FAQ Handling',
                    'Catalog Semantic Vector Search Embeddings',
                    'Magento 2 & Shopify 1-Click Store Connectors',
                    'WhatsApp Order Notifications & Agent Escalations',
                ],
            ]
        );

        Plan::updateOrCreate(
            ['product_id' => $apriloEcom->id, 'slug' => 'store-growth'],
            [
                'name' => 'Commerce Store Growth',
                'billing_cycle' => 'monthly',
                'price' => 1999.00,
                'currency' => 'INR',
                'trial_period_days' => 14,
                'request_limit' => 120000,
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    'Up to 3 Online Stores (Magento / Shopify)',
                    '120,000 Shopping Inquiries / month',
                    'Live Order Status Lookup',
                    'Catalog Vector Embeddings Sync',
                    'WhatsApp Order Updates',
                ],
            ]
        );

        // 7. Magento 2 Native Extension License
        Product::updateOrCreate(
            ['slug' => 'aprilo-magento-suite'],
            [
                'name' => 'Aprilo Commerce for Magento 2 (Module)',
                'category' => 'ecommerce',
                'product_type' => 'one_time_license',
                'platform' => 'magento',
                'short_description' => 'Full AI catalog search, vector embeddings, and real-time order tracking module for Adobe Commerce / Magento 2.',
                'description' => 'Turn your Magento 2 store into an intelligent conversational shopping experience. Generates semantic embeddings for your entire catalog and syncs orders automatically.',
                'icon' => 'Boxes',
                'price' => 1999.00,
                'currency' => 'INR',
                'status' => 'active',
                'featured' => false,
                'best_seller' => false,
                'is_active' => true,
                'sort_order' => 7,
                'features' => [
                    'Magento 2.4.x Native PHP Module',
                    'Bi-directional Product & Order Catalog Sync',
                    'AI Semantic Search & Product Recommendations',
                    'Domain-locked License Key Generator',
                ],
            ]
        );

        // 8. Shopify App License
        Product::updateOrCreate(
            ['slug' => 'aprilo-shopify-app'],
            [
                'name' => 'Aprilo Commerce for Shopify (App)',
                'category' => 'ecommerce',
                'product_type' => 'one_time_license',
                'platform' => 'shopify',
                'short_description' => 'Automated Shopify AI Sales & Support bot that answers customer queries and tracks order status.',
                'description' => 'Empower your Shopify store with an intelligent assistant that recommends products and pulls real-time tracking numbers directly from Shopify Admin API.',
                'icon' => 'ShoppingBag',
                'price' => 999.00,
                'currency' => 'INR',
                'status' => 'active',
                'featured' => false,
                'best_seller' => false,
                'is_active' => true,
                'sort_order' => 8,
                'features' => [
                    '1-Click Shopify App Embed Installation',
                    'Live Inventory & Order Status Lookup',
                    'AI Abandoned Cart Recovery Widget',
                ],
            ]
        );

        // 9. Data Centers & Managed IT Infrastructure (Runbooks, Technical Manuals, Incident Intake, SLA Tracking)
        $apriloInfra = Product::updateOrCreate(
            ['slug' => 'aprilo-infrastructure'],
            [
                'name' => 'Aprilo IT Infrastructure & Data Centers',
                'category' => 'infrastructure',
                'product_type' => 'enterprise',
                'platform' => 'hybrid',
                'short_description' => 'Specialist AI for datacenter operators & MSPs: runbook troubleshooting, severity triage, SLA tracking, and shift handovers.',
                'description' => 'Tailored for data-center operators, managed service providers, and infrastructure support teams. Troublshoots incidents from technical runbooks, handles incident intake, SLA tracking, escalation alerts, and shift handovers.',
                'icon' => 'Server',
                'price' => 4999.00,
                'currency' => 'INR',
                'status' => 'active',
                'featured' => false,
                'best_seller' => false,
                'is_active' => true,
                'sort_order' => 9,
                'features' => [
                    'Troubleshooting from Runbooks and Technical Manuals',
                    'Incident Intake, Severity Classification & Routing',
                    'SLA Tracking, Escalation Notifications & Shift Handovers',
                    'Internal Knowledge Search for Network & Server Procedures',
                    'Strict Role-Based Telemetry & Integration Boundaries',
                ],
            ]
        );

        // 10. Banking & Financial Services (Regulated FAQ RAG, Audited Intake, KYC Verification, Human Escalation)
        $apriloFin = Product::updateOrCreate(
            ['slug' => 'aprilo-fintech'],
            [
                'name' => 'Aprilo FinTech & Banking',
                'category' => 'financial',
                'product_type' => 'enterprise',
                'platform' => 'secure_cloud',
                'short_description' => 'Enterprise compliance AI for banks & fintechs: approved public documentation guidance, audited service-request intake, and human escalation.',
                'description' => 'Built for banks, payment providers, and financial institutions requiring rigorous security and compliance. Answers general product, fee, and process FAQs from approved public documentation, with verified audit logs and human escalation.',
                'icon' => 'ShieldCheck',
                'price' => 7999.00,
                'currency' => 'INR',
                'status' => 'active',
                'featured' => false,
                'best_seller' => false,
                'is_active' => true,
                'sort_order' => 10,
                'features' => [
                    'Regulated Banking & Financial Product FAQs',
                    'Guidance Strictly Restricted to Approved Documentation',
                    'Complaint Intake & Audited Service-Request Routing',
                    'Instant Human Escalation for Sensitive Financial Cases',
                    'Complete Cryptographic Audit Trails & Regulatory Compliance',
                ],
            ]
        );
    }
}