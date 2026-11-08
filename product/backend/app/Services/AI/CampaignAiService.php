<?php

namespace App\Services\AI;

use App\Models\MarketingLead;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CampaignAiService
{
    /**
     * Product knowledge base for Aprilo software suite.
     */
    protected array $productKnowledge = [
        'Aprilo POS' => [
            'name' => 'Aprilo POS',
            'tagline' => 'Fast, intelligent retail billing & inventory POS system',
            'lead_hook' => 'Stop losing revenue to slow manual billing and inventory mismatch.',
            'awareness_hook' => 'Scale your store operations with Aprilo POS — now integrated with automated inventory sync & GST invoicing.',
            'benefits' => [
                'Lightning-fast checkout with barcode scanning',
                'Real-time inventory sync & low-stock alerts',
                'GST & commercial invoices with 1-click PDF download',
                'Works online & offline with multi-terminal support',
            ],
            'url' => 'https://apriloinfotech.com/pos',
        ],
        'Aprilo Chatbot' => [
            'name' => 'Aprilo Chatbot',
            'tagline' => '24/7 AI Customer Support & WhatsApp Engagement Engine',
            'lead_hook' => 'Tired of missing customer leads after business hours? Let AI answer and convert them instantly.',
            'awareness_hook' => 'Give your store 24/7 AI-powered support and WhatsApp auto-replies with Aprilo Chatbot.',
            'benefits' => [
                'Automated WhatsApp & website instant customer resolution',
                'Answers from your product catalog & custom knowledge base',
                'Collects verified leads and escalates complex queries',
                'Reduces support workload by over 70%',
            ],
            'url' => 'https://apriloinfotech.com/chatbot',
        ],
        'Aprilo Agent' => [
            'name' => 'Aprilo Agent',
            'tagline' => 'Autonomous Multi-Channel AI Business Operator',
            'lead_hook' => 'Automate your repetitive business operations with an autonomous AI agent working around the clock.',
            'awareness_hook' => 'Elevate your enterprise with Aprilo Agent — handles ticketing, CRM sync, and automated outbox campaigns.',
            'benefits' => [
                'Autonomous ticket assignment & SLA resolution',
                'Cross-platform webhook sync with Shopify, Magento & custom APIs',
                'Intelligent lead qualification & follow-up routing',
                'Multi-channel outreach via WhatsApp, Teams & Email',
            ],
            'url' => 'https://apriloinfotech.com/agent',
        ],
        'Aprilo Commerce' => [
            'name' => 'Aprilo Commerce',
            'tagline' => 'Unified E-Commerce Connectors & Catalog Synchronization',
            'lead_hook' => 'Managing products across multiple stores is tedious. Centralize your catalog in one dashboard.',
            'awareness_hook' => 'Sync your catalog and orders effortlessly across Magento, Shopify, and custom stores.',
            'benefits' => [
                'Real-time two-way order & inventory synchronization',
                'Unified plugin licensing engine',
                'AI product embedding & semantic search refresh',
                'Zero duplicate orders or stock discrepancies',
            ],
            'url' => 'https://apriloinfotech.com/commerce',
        ],
        'TradeFlow' => [
            'name' => 'TradeFlow',
            'tagline' => 'Enterprise B2B Wholesale & Supply Chain Management',
            'lead_hook' => 'Eliminate supply chain bottlenecks, stockouts, and manual purchase order delays.',
            'awareness_hook' => 'Manage bulk wholesale distribution and distributor network margins seamlessly with TradeFlow.',
            'benefits' => [
                'Wholesale order tracking & automated dealer dispatch',
                'Vendor price lists & margin calculator',
                'Batch & expiry tracking with lifetime software license',
                'Comprehensive GST, ledger & credit note reconciliation',
            ],
            'url' => 'https://apriloinfotech.com/tradeflow',
        ],
    ];

    /**
     * Generate marketing campaign copy tailored by AI for product, audience, and channel.
     */
    public function generateCampaignCopy(array $params): array
    {
        $productName = $params['product_name'] ?? 'Aprilo Chatbot';
        $targetAudience = $params['target_audience'] ?? 'unconverted_leads'; // unconverted_leads, converted_customers
        $campaignGoal = $params['campaign_goal'] ?? ($targetAudience === 'unconverted_leads' ? 'lead_followup' : 'product_awareness');
        $channel = $params['channel'] ?? 'whatsapp'; // whatsapp, email, both
        $tone = $params['tone'] ?? 'persuasive';
        $discountCode = $params['discount_code'] ?? null;
        $customNotes = $params['custom_notes'] ?? null;

        $productInfo = $this->productKnowledge[$productName] ?? [
            'name' => $productName,
            'tagline' => 'High-performance software product by Aprilo Infotech',
            'lead_hook' => 'Transform your operational efficiency with ' . $productName,
            'awareness_hook' => 'Discover the latest software features in ' . $productName,
            'benefits' => ['Fast deployment', 'Dedicated support', 'Designed for scaling Indian enterprises'],
            'url' => 'https://apriloinfotech.com',
        ];

        // Attempt Gemini AI Generation
        $aiResult = $this->callGeminiForCopy($productInfo, $targetAudience, $campaignGoal, $channel, $tone, $discountCode, $customNotes);

        if ($aiResult) {
            return $aiResult;
        }

        // Fallback to high-converting rule-based copy engine
        return $this->generateRuleBasedCopy($productInfo, $targetAudience, $campaignGoal, $channel, $tone, $discountCode, $customNotes);
    }

    /**
     * Determine optimal strategy before initiating automated weekly cron campaign.
     */
    public function determineWeeklyAutomationStrategy(int $unconvertedCount = 0, int $convertedCount = 0): array
    {
        $products = array_keys($this->productKnowledge);
        // Week of year determines rotating product focus
        $weekNumber = (int) date('W');
        $productIndex = $weekNumber % count($products);
        $selectedProduct = $products[$productIndex];

        // Strategy logic:
        // If there are more unconverted leads, emphasize lead followup & conversion offer.
        // If leads are few, emphasize new product awareness & upsell to converted customers.
        $targetAudience = ($unconvertedCount > 0 && ($unconvertedCount >= $convertedCount || $weekNumber % 2 === 1))
            ? 'unconverted_leads'
            : 'converted_customers';

        $campaignGoal = $targetAudience === 'unconverted_leads' ? 'lead_followup' : 'product_awareness';
        $discountCode = $targetAudience === 'unconverted_leads' ? 'GROWTH' . date('y') : null;

        $copy = $this->generateCampaignCopy([
            'product_name' => $selectedProduct,
            'target_audience' => $targetAudience,
            'campaign_goal' => $campaignGoal,
            'channel' => 'whatsapp',
            'tone' => 'persuasive',
            'discountCode' => $discountCode,
            'custom_notes' => 'Weekly scheduled automated dispatch for active business growth.',
        ]);

        $summary = sprintf(
            "Weekly AI Strategy (Week %d): Targeted '%s' (%s) featuring '%s'. Goal: %s.",
            $weekNumber,
            $targetAudience === 'unconverted_leads' ? 'Unconverted Leads' : 'Converted Customers',
            $targetAudience === 'unconverted_leads' ? "{$unconvertedCount} leads" : "{$convertedCount} customers",
            $selectedProduct,
            $targetAudience === 'unconverted_leads' ? 'Nurturing & Trial Conversion' : 'Cross-sell & Product Expansion'
        );

        return [
            'product_name' => $selectedProduct,
            'target_audience' => $targetAudience,
            'campaign_goal' => $campaignGoal,
            'strategy_summary' => $summary,
            'campaign_name' => sprintf('[Automated Weekly] %s — %s (W%d)', $selectedProduct, ucfirst(str_replace('_', ' ', $campaignGoal)), $weekNumber),
            'copy' => $copy,
        ];
    }

    /**
     * Call Gemini API to generate structured campaign messaging.
     */
    protected function callGeminiForCopy(
        array $productInfo,
        string $targetAudience,
        string $campaignGoal,
        string $channel,
        string $tone,
        ?string $discountCode,
        ?string $customNotes
    ): ?array {
        $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        if (! $apiKey) {
            return null;
        }

        $audienceDesc = $targetAudience === 'unconverted_leads'
            ? 'UNCONVERTED LEADS (prospects who showed interest but have not purchased yet. Needs friction removal, urgent benefit, clear ROI proof, and a compelling followup call to action)'
            : 'CONVERTED CUSTOMERS (existing paying users of Aprilo. Needs new product awareness, how it integrates with their current stack, appreciation, and exclusive early-adopter access)';

        $benefitsList = implode("\n- ", $productInfo['benefits']);

        $prompt = <<<EOT
You are an expert marketing copywriter for Aprilo Infotech (a technology brand of APRILO ENTERPRISES), crafting a high-conversion marketing campaign.

Target Product: {$productInfo['name']} ({$productInfo['tagline']})
Product Benefits:
- {$benefitsList}
Product URL: {$productInfo['url']}

Target Audience: {$audienceDesc}
Campaign Goal: {$campaignGoal}
Channel: {$channel}
Tone: {$tone}
Discount Code: {$discountCode}
Special Instructions: {$customNotes}

Guidelines for WhatsApp:
- Use emojis naturally.
- Use WhatsApp formatting: *bold* for emphasis, _italics_ sparingly.
- Maximum 150-250 words. Punchy, scannable, conversational.
- Personalization tags: use {customer_name} and {company_name}.
- Clear Call to Action at the end (e.g. Reply 'YES' or tap link).

Guidelines for Email:
- Catchy subject line (under 60 chars) with high open-rate potential.
- Compelling body with hook, value points, and strong CTA.

Respond ONLY with a valid, clean JSON object with this exact structure:
{
    "campaign_name": "Suggested short campaign name",
    "whatsapp_headline": "Short punchy hook",
    "whatsapp_message": "Full WhatsApp text with formatting and emojis",
    "email_subject": "Email subject line",
    "email_preview": "Email preview text",
    "email_body": "Full email message body",
    "call_to_action": "Exact CTA wording",
    "strategy_notes": "One-line rationale of why this copy works for this audience"
}
EOT;

        try {
            $model = config('services.gemini.model', 'gemini-1.5-flash');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

            $response = Http::timeout(10)
                ->withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-goog-api-key' => $apiKey,
                ])
                ->post($url, [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                    ],
                ]);

            if ($response->successful()) {
                $rawText = $response->json('candidates.0.content.parts.0.text');
                if ($rawText) {
                    $json = json_decode($rawText, true);
                    if (is_array($json) && ! empty($json['whatsapp_message'])) {
                        return [
                            'campaign_name' => $json['campaign_name'] ?? ($productInfo['name'] . ' Outreach'),
                            'whatsapp_headline' => $json['whatsapp_headline'] ?? $productInfo['lead_hook'],
                            'whatsapp_message' => trim($json['whatsapp_message']),
                            'email_subject' => $json['email_subject'] ?? ("Boost your business with " . $productInfo['name']),
                            'email_preview' => $json['email_preview'] ?? $productInfo['tagline'],
                            'email_body' => trim($json['email_body'] ?? $json['whatsapp_message']),
                            'call_to_action' => $json['call_to_action'] ?? 'Reply YES to get started',
                            'strategy_notes' => $json['strategy_notes'] ?? 'Generated by Gemini AI',
                            'ai_provider' => 'gemini',
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Gemini AI Campaign Generator error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Rule-based high-converting campaign copy generator (robust offline fallback).
     */
    protected function generateRuleBasedCopy(
        array $productInfo,
        string $targetAudience,
        string $campaignGoal,
        string $channel,
        string $tone,
        ?string $discountCode,
        ?string $customNotes
    ): array {
        $pName = $productInfo['name'];
        $benefitsBullet = implode("\n👉 ", $productInfo['benefits']);
        $discountSnippet = $discountCode ? "\n🎁 *Exclusive Offer:* Use code *{$discountCode}* for an instant discount this week only!" : "";

        if ($targetAudience === 'unconverted_leads') {
            // High-converting followup copy for unconverted prospects
            $waMessage = "Hello *{customer_name}* 👋\n\n"
                . "We noticed you recently explored *{$pName}* to streamline your operations.\n\n"
                . "Are you still dealing with {$productInfo['lead_hook']}?\n\n"
                . "With *{$pName}*, businesses like yours achieve:\n"
                . "👉 {$benefitsBullet}\n"
                . "{$discountSnippet}\n\n"
                . "💡 *Zero Risk:* Take a guided 5-minute demo and see how it fits your business.\n\n"
                . "📲 *Reply 'DEMO'* to this message or visit:\n{$productInfo['url']}\n\n"
                . "Best regards,\n*Aprilo Infotech Team*\n_Build Smarter. Grow Faster._";

            $emailSubject = "Quick follow-up regarding your {$pName} inquiry";
            $emailBody = "Hi {customer_name},\n\n"
                . "I'm following up on your recent interest in {$pName}.\n\n"
                . "Most business owners we speak with want to eliminate manual operational friction. That's why we built {$pName} ({$productInfo['tagline']}).\n\n"
                . "Key benefits:\n- " . implode("\n- ", $productInfo['benefits']) . "\n\n"
                . ($discountCode ? "As a special incentive, use code {$discountCode} to claim your onboarding discount.\n\n" : "")
                . "Would you be open to a quick 5-minute walkthrough this week?\n\n"
                . "Explore details: {$productInfo['url']}\n\n"
                . "Warm regards,\nAprilo Infotech\nhttps://apriloinfotech.com";

            $cta = "Reply 'DEMO' to schedule walkthrough";
            $strategy = "Lead Follow-up: Addresses core friction, highlights tangible benefits, and lowers conversion resistance with a quick demo call.";
        } else {
            // High-converting product awareness / upsell copy for existing customers
            $waMessage = "Dear *{customer_name}* 🌟\n\n"
                . "Thank you for being a valued partner with *Aprilo*!\n\n"
                . "We're excited to introduce our latest product expansion: *{$pName}* 🚀\n\n"
                . "{$productInfo['awareness_hook']}\n\n"
                . "✨ *How it complements your setup:*\n"
                . "👉 {$benefitsBullet}\n"
                . "{$discountSnippet}\n\n"
                . "As an existing customer, you get priority onboarding & seamless integration with your current account.\n\n"
                . "📲 *Reply 'INFO'* to activate your trial, or view details here:\n{$productInfo['url']}\n\n"
                . "Cheers,\n*Aprilo Infotech Team*\n_Build Smarter. Grow Faster._";

            $emailSubject = "Exclusive new product announcement: Introducing {$pName}";
            $emailBody = "Hi {customer_name},\n\n"
                . "As a valued Aprilo user, you know how much we care about making enterprise software intuitive and powerful.\n\n"
                . "Today, we're proud to unveil {$pName} — {$productInfo['tagline']}.\n\n"
                . "Why add {$pName} to your stack?\n- " . implode("\n- ", $productInfo['benefits']) . "\n\n"
                . ($discountCode ? "Enjoy VIP customer pricing with code {$discountCode}.\n\n" : "")
                . "Learn more or request activation: {$productInfo['url']}\n\n"
                . "Thank you for growing with us,\nAprilo Infotech\nhttps://apriloinfotech.com";

            $cta = "Reply 'INFO' for VIP Customer Onboarding";
            $strategy = "Customer Expansion: Acknowledges existing partnership and presents complementary product as a natural upgrade.";
        }

        return [
            'campaign_name' => "{$pName} — " . ($targetAudience === 'unconverted_leads' ? 'Lead Followup Nudge' : 'Product Awareness Announcement'),
            'whatsapp_headline' => $targetAudience === 'unconverted_leads' ? $productInfo['lead_hook'] : $productInfo['awareness_hook'],
            'whatsapp_message' => $waMessage,
            'email_subject' => $emailSubject,
            'email_preview' => $productInfo['tagline'],
            'email_body' => $emailBody,
            'call_to_action' => $cta,
            'strategy_notes' => $strategy,
            'ai_provider' => 'smart_template_engine',
        ];
    }

    /**
     * Get available products list.
     */
    public function getAvailableProducts(): array
    {
        return array_keys($this->productKnowledge);
    }
}

