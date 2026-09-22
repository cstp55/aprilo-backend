<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductsAndPlansSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Aprilo Support AI (Core Subscription with 1-Month Free Trial)
        $supportAi = Product::updateOrCreate(
            ['slug' => 'aprilo-support'],
            [
                'name' => 'Aprilo Support AI',
                'category' => 'ai_support',
                'product_type' => 'subscription',
                'platform' => 'web',
                'short_description' => 'Autonomous customer support agent with 1-month free trial, 40,000 queries quota, and multi-channel escalations.',
                'description' => 'Aprilo Support AI is an intelligent customer and product support platform powered by advanced LLMs and RAG. It resolves customer inquiries 24/7 across website chat, WhatsApp, and Microsoft Teams.',
                'icon' => 'Headphones',
                'price' => 499.00,
                'currency' => 'INR',
                'status' => 'active',
                'featured' => true,
                'best_seller' => true,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    'Multi-channel AI Agent (Web, WhatsApp, Teams)',
                    'Knowledge Base RAG & Semantic Chunking',
                    'Smart Escalations to Human Support',
                    'Real-Time Analytics & CSAT Tracking',
                    'Customizable Brand Personality & System Prompts',
                ],
                'metadata' => [
                    'trial_available' => true,
                    'trial_description' => '1 Month (30 Days) Free Trial with AutoPay Mandate',
                ],
            ]
        );

        // Plans for Aprilo Support AI
        Plan::updateOrCreate(
            ['product_id' => $supportAi->id, 'slug' => 'starter'],
            [
                'name' => 'Starter AI Support',
                'billing_cycle' => 'monthly',
                'price' => 499.00,
                'currency' => 'INR',
                'trial_period_days' => 30, // 1 MONTH FREE TRIAL
                'request_limit' => 40000,   // up to 40,000 requests
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    'Up to 40,000 AI Queries / month',
                    '1 Month (30 Days) 100% Free Trial',
                    'Web Chatbot Widget',
                    'Official WhatsApp Cloud API Integration',
                    'Standard Knowledge Base (up to 50 documents)',
                    'Email & In-app Ticket Escalation',
                    'Standard SLA & Community Support',
                ],
                'metadata' => [
                    'tagline' => 'Best for Growing Businesses & Startups',
                    'overage_rate' => '₹0.02 per additional query',
                ],
            ]
        );

        Plan::updateOrCreate(
            ['product_id' => $supportAi->id, 'slug' => 'growth'],
            [
                'name' => 'Growth AI Support',
                'billing_cycle' => 'monthly',
                'price' => 1499.00,
                'currency' => 'INR',
                'trial_period_days' => 30, // 1 MONTH FREE TRIAL
                'request_limit' => 150000,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 2,
                'features' => [
                    'Up to 150,000 AI Queries / month',
                    '1 Month (30 Days) 100% Free Trial',
                    'All Starter Features',
                    'Microsoft Teams & Skype Bot Integration',
                    'Advanced Vector Knowledge Base (Unlimited documents)',
                    'Automated HR & Leave Query Workflows',
                    'Priority Support & Dedicated Onboarding',
                ],
                'metadata' => [
                    'tagline' => 'For scaling teams with high support volume',
                ],
            ]
        );

        Plan::updateOrCreate(
            ['product_id' => $supportAi->id, 'slug' => 'enterprise'],
            [
                'name' => 'Enterprise AI Support',
                'billing_cycle' => 'monthly',
                'price' => 4999.00,
                'currency' => 'INR',
                'trial_period_days' => 0, // Custom trial on request
                'request_limit' => 600000,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 3,
                'features' => [
                    'Up to 600,000 AI Queries / month',
                    'Custom LLM Bring-Your-Own-Key Support',
                    'Custom ERP / CRM System Connectors',
                    'Multi-tenant SLA with 99.9% Uptime Guarantee',
                    'Dedicated Account Executive & 24/7 Phone Support',
                ],
                'metadata' => [
                    'tagline' => 'For large scale enterprises & high throughput',
                ],
            ]
        );

        // 2. Aprilo Commerce - Magento AI Suite (Software Plugin)
        $magentoSuite = Product::updateOrCreate(
            ['slug' => 'aprilo-magento-suite'],
            [
                'name' => 'Aprilo Commerce for Magento 2',
                'category' => 'ecommerce',
                'product_type' => 'subscription',
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
                'sort_order' => 2,
                'features' => [
                    'Magento 2.4.x Native PHP Module',
                    'Bi-directional Product & Order Catalog Sync',
                    'AI Semantic Search & Product Recommendations',
                    'Domain-locked License Key Generator',
                ],
            ]
        );

        Plan::updateOrCreate(
            ['product_id' => $magentoSuite->id, 'slug' => 'single-store'],
            [
                'name' => 'Single Store License',
                'billing_cycle' => 'monthly',
                'price' => 1999.00,
                'currency' => 'INR',
                'trial_period_days' => 0, // No trial for e-commerce plugin
                'request_limit' => 50000,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    '1 Production Magento Domain',
                    'Unlimited Staging / Local Domains',
                    '50,000 Product Search AI Queries / mo',
                    'Automatic Catalog Vector Synchronization',
                    'Standard Email Support & Module Updates',
                ],
            ]
        );

        // 3. Aprilo Commerce - Shopify AI Agent (Software App)
        $shopifyApp = Product::updateOrCreate(
            ['slug' => 'aprilo-shopify-app'],
            [
                'name' => 'Aprilo Commerce for Shopify',
                'category' => 'ecommerce',
                'product_type' => 'subscription',
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
                'sort_order' => 3,
                'features' => [
                    '1-Click Shopify App Embed Installation',
                    'Live Inventory & Order Status Lookup',
                    'AI Abandoned Cart Recovery Widget',
                ],
            ]
        );

        Plan::updateOrCreate(
            ['product_id' => $shopifyApp->id, 'slug' => 'standard'],
            [
                'name' => 'Standard Shopify Store',
                'billing_cycle' => 'monthly',
                'price' => 999.00,
                'currency' => 'INR',
                'trial_period_days' => 14,
                'request_limit' => 30000,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    '1 Shopify Store Connection',
                    'Up to 30,000 AI Sessions / month',
                    'Live Order Status & Tracking',
                    'Theme App Extension Support',
                ],
            ]
        );
    }
}