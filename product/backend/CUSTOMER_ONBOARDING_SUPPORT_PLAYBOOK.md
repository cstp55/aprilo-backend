# Aprilo Chatbot — Customer Onboarding & Support Playbook

> **Target Product:** Aprilo Support AI Chatbot  
> **Campaign URL Tracked:** `https://apriloinfotech.com/products/aprilo-chatbot?utm_source=linkedin&utm_medium=social&utm_campaign=ph_launch_2026`  
> **Production URL:** [https://aprilo-infotech.web.app/products/aprilo-chatbot/playbook](https://aprilo-infotech.web.app/products/aprilo-chatbot/playbook)  
> **Status:** Production Ready  

---

## Table of Contents
1. [Overview & Customer Lifecycle Journey](#1-overview--customer-lifecycle-journey)
2. [Welcome Email After Registration](#2-welcome-email-after-registration)
3. [First Chatbot Setup Instructions (3-Minute Setup)](#3-first-chatbot-setup-instructions-3-minute-setup)
4. [Knowledge-Base Upload Guide (RAG Architecture)](#4-knowledge-base-upload-guide-rag-architecture)
5. [Website Integration Guide (Multi-Platform Embeds)](#5-website-integration-guide-multi-platform-embeds)
6. [WhatsApp Integration Guide (Hybrid Architecture)](#6-whatsapp-integration-guide-hybrid-architecture)
7. [Human Support Escalation Process](#7-human-support-escalation-process)
8. [Trial Expiry & Subscription Communication Sequences](#8-trial-expiry--subscription-communication-sequences)
9. [Daily Monitoring & Multi-Channel Conversion Funnel](#9-daily-monitoring--multi-channel-conversion-funnel)
10. [Implementation File Directory & Commands](#10-implementation-file-directory--commands)

---

## 1. Overview & Customer Lifecycle Journey

The customer onboarding journey connects top-of-funnel acquisition (LinkedIn, Product Hunt, Organic Search) with automated self-service configuration, continuous usage tracking, and proactive subscription renewal prompts.

```
+------------------------------------------------------------------------------------+
|                                ACQUISITION CHANNELS                                |
|  - LinkedIn Campaign (ph_launch_2026)                                              |
|  - Product Hunt Launch (Featured badge & direct embed)                             |
|  - Google Search & Organic Referrals                                               |
+------------------------------------------------------------------------------------+
                                         │
                                         ▼
+------------------------------------------------------------------------------------+
|                              STAGE 1: TRIAL REGISTRATION                            |
|  - Customer lands on /onboarding                                                   |
|  - Auto-captures UTM attribution (source, medium, campaign)                        |
|  - Provisions Organization, Owner Account, and Public Widget Key                    |
|  - Fires: trial_activated analytics event                                          |
+------------------------------------------------------------------------------------+
                                         │
                                         ▼
+------------------------------------------------------------------------------------+
|                         STAGE 2: AUTOMATED WELCOME & SETUP                          |
|  - Instant Welcome Email dispatched with 3 Quick Wins + Widget Key                 |
|  - Step 1: Assistant Name, Avatar & Brand Color Customization                      |
|  - Step 2: Knowledge Base RAG document/catalog indexing                           |
|  - Step 3: Embed 1-line script snippet into website                                |
+------------------------------------------------------------------------------------+
                                         │
                                         ▼
+------------------------------------------------------------------------------------+
|                       STAGE 3: ACTIVE USAGE & ESCALATIONS                          |
|  - AI Grounded Answering (RAG vector retrieval)                                    |
|  - Instant Free WhatsApp Mode: Immediate wa.me redirect with user query prefilled  |
|  - Live Agent Escalation: 2-step qualification handshake (Name -> Reason)          |
|  - Cookie-based session lifecycle (sliding 3-hour TTL)                             |
+------------------------------------------------------------------------------------+
                                         │
                                         ▼
+------------------------------------------------------------------------------------+
|                       STAGE 4: TRIAL NURTURING & CONVERSION                         |
|  - Day 3: Resolution stats check-in & FAQ suggestions                              |
|  - Day 10: WhatsApp Direct Connect conversion tip                                  |
|  - Day 27: 72-hour trial ending notice with deflection metrics                      |
|  - Day 30: Expiry & upgrade reminder (seamless transition to Free WhatsApp mode)  |
+------------------------------------------------------------------------------------+
```

---

## 2. Welcome Email After Registration

* **Trigger:** Customer activates 30-day free trial on `/onboarding` (`trial_activated`).  
* **Sender:** `support@apriloinfotech.com` (Display Name: *Aprilo Support Engineering*).  
* **Backend Class:** `App\Mail\ChatbotWelcomeMail.php`  
* **Blade Template:** `resources/views/emails/chatbot-welcome.blade.php`  

### Email Subject
`Welcome to Aprilo Chatbot — Your 30-Day Free Trial is Live! 🚀`

### HTML Content Template
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to Aprilo Chatbot</title>
</head>
<body style="margin: 0; padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f8fafc; color: #1e293b; line-height: 1.6;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <tr>
            <td style="padding: 32px 32px 24px; text-align: center; border-bottom: 1px solid #f1f5f9;">
                <div style="font-size: 22px; font-weight: 700; color: #0f172a;">
                    <span style="color: #2563eb;">✦</span> Aprilo Chatbot
                </div>
                <h1 style="margin: 16px 0 6px; font-size: 22px; font-weight: 700; color: #0f172a;">Welcome to Aprilo, {{ $userName }}!</h1>
                <p style="margin: 0; font-size: 14px; color: #64748b;">Your 30-day all-inclusive free trial for <strong>{{ $orgName }}</strong> is now active.</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 32px;">
                <p style="margin-top: 0; font-size: 15px; color: #334155;">
                    With our hybrid AI + human agent chatbot, you can now automate routine customer inquiries, capture qualified leads 24/7, and seamlessly redirect urgent queries directly to WhatsApp.
                </p>

                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin: 24px 0;">
                    <h2 style="margin: 0 0 14px; font-size: 15px; font-weight: 700; color: #0f172a;">3 Quick Wins to Complete Today:</h2>
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td width="28" valign="top" style="font-weight: 700; color: #2563eb;">1.</td>
                            <td style="font-size: 14px; color: #334155; padding-bottom: 10px;">
                                <strong>Name Your Assistant:</strong> Choose a persona and brand color that matches {{ $orgName }}.
                            </td>
                        </tr>
                        <tr>
                            <td width="28" valign="top" style="font-weight: 700; color: #2563eb;">2.</td>
                            <td style="font-size: 14px; color: #334155; padding-bottom: 10px;">
                                <strong>Upload Knowledge Base:</strong> Upload your FAQs, catalog documents, or crawl your website.
                            </td>
                        </tr>
                        <tr>
                            <td width="28" valign="top" style="font-weight: 700; color: #2563eb;">3.</td>
                            <td style="font-size: 14px; color: #334155;">
                                <strong>Embed the Widget:</strong> Add our 1-line script snippet right before the <code>&lt;/body&gt;</code> tag.
                            </td>
                        </tr>
                    </table>
                </div>

                <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px;">
                    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #1e40af; margin-bottom: 4px;">Your Public Widget Key</div>
                    <code style="font-family: monospace; font-size: 14px; color: #1d4ed8; font-weight: 600;">{{ $widgetKey }}</code>
                </div>

                <div style="text-align: center; margin: 28px 0;">
                    <a href="{{ $setupUrl }}" style="background-color: #2563eb; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 15px; padding: 12px 28px; border-radius: 8px; display: inline-block;">Launch Widget Setup Wizard &rarr;</a>
                </div>

                <p style="margin-bottom: 0; font-size: 13.5px; color: #64748b; line-height: 1.5;">
                    Need assistance setting up? Reply directly to this email or connect with our product engineering team directly on WhatsApp:
                    <a href="https://wa.me/message/BE3K4RAO24GXK1" style="color: #2563eb; font-weight: 600; text-decoration: none;">+91 91940 05010</a>.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
```

---

## 3. First Chatbot Setup Instructions (3-Minute Setup)

1. **Log in to Console:**  
   Open `https://app.apriloinfotech.com` &rarr; Navigate to **Settings &rarr; Chat Widget Configuration**.
2. **Configure Identity & Branding:**  
   * **Assistant Name:** Specify your brand's bot name (e.g., *"Baxtiyor Virtual Assistant"*, *"Acme Care Bot"*).
   * **Brand Color:** Set your primary brand hex code (e.g., `#2563eb` for blue or `#059669` for emerald).
   * **Widget Position:** Select `Bottom-Right` (standard) or `Bottom-Left`.
3. **Configure Welcome Message & Default Prompts:**  
   * **Welcome Greeting:** e.g., *"Hello! I am your AI assistant for Acme Corp. How can I assist you with our services today?"*
   * **Quick Action Buttons:** Add 2 to 3 pre-configured prompts (e.g., *"Our Services"*, *"Pricing Plans"*, *"Talk to Agent"*).
4. **Interactive Simulator Testing:**  
   Verify both AI text deflection and WhatsApp direct redirection in the right-hand simulator pane before publishing.

---

## 4. Knowledge-Base Upload Guide (RAG Architecture)

Aprilo Chatbot uses **Retrieval-Augmented Generation (RAG)** to provide grounded, hallucination-free answers.

| Source Format | Supported Extensions | Best Practice & Content Recommendation |
| :--- | :--- | :--- |
| **Documents** | `.pdf`, `.docx`, `.txt`, `.md` | Use clear H1/H2 markdown headers; document return policies, shipping, and FAQs. |
| **Spreadsheets** | `.csv`, `.xlsx` | Best for price lists, treatment cost matrices, and SKU directories. |
| **Website Crawl** | URL / XML Sitemap | Input root domain or sitemap; the crawler auto-indexes sub-pages and strips HTML boilerplate. |
| **Manual Q&A** | In-app form | Add exact question-and-answer pairs for high-priority inquiries (served via high-speed cache). |

### Quality Guidelines for 95%+ Deflection Accuracy
* **Avoid Scanned PDFs:** Ensure PDFs contain searchable textual layers, not image scans without OCR.
* **Phone Numbers:** Always format numbers with country codes (e.g., `+91 91940 05010`) so the chatbot can generate direct WhatsApp links.
* **Granular Chunking:** The system auto-chunks documents into 500-token blocks with metadata tags for citation referencing.

---

## 5. Website Integration Guide (Multi-Platform Embeds)

### A. Generic HTML (Any Website / Web App)
Paste immediately before the closing `</body>` tag:
```html
<!-- Aprilo Support AI Chat Widget -->
<script 
  src="https://aprilo-infotech.web.app/chat-widget/v1/widget.js" 
  data-widget-key="YOUR_ORGANIZATION_KEY" 
  data-api-url="https://app.apriloinfotech.com" 
  async>
</script>
```

### B. WordPress
1. In WordPress Admin, navigate to **Plugins &rarr; Add New** and install **WPCode** (Insert Headers and Footers).
2. Go to **Code Snippets &rarr; Header & Footer**.
3. In the **Footer** text area, paste the embed snippet and click **Save Changes**.

### C. Shopify
1. In Shopify Admin, go to **Online Store &rarr; Themes &rarr; Actions &rarr; Edit code**.
2. Open `layout/theme.liquid`.
3. Scroll to the bottom and paste the script immediately above `</body>`. Click **Save**.

### D. Magento 2
1. In Magento Admin, navigate to **Content &rarr; Design &rarr; Configuration**.
2. Select your active store view.
3. Open the **Footer** section &rarr; Paste snippet into **Miscellaneous HTML**.
4. Save configuration and flush Magento cache (`bin/magento cache:flush`).

---

## 6. WhatsApp Integration Guide (Hybrid Architecture)

Aprilo provides two distinct WhatsApp engagement models tailored to organization tier:

```
                          ┌───────────────────────────┐
                          │   Visitor Interacts via   │
                          │        Chat Widget        │
                          └─────────────┬─────────────┘
                                        │
                         Is "Only WhatsApp" Mode On?
                                        │
                      ┌─────────────────┴─────────────────┐
                     YES                                  NO
                      ▼                                   ▼
        ┌───────────────────────────┐       ┌───────────────────────────┐
        │   INSTANT FREE WA MODE    │       │     PRO / ENTERPRISE      │
        │ - AI & Agent disabled     │       │ - Autonomous AI RAG Chat  │
        │ - Immediate window.open   │       │ - Human Agent Handoff     │
        │ - Prefilled user message  │       │ - Optional WhatsApp Cloud │
        │ - WhatsApp conversion +1  │       │   API two-way messaging   │
        └───────────────────────────┘       └───────────────────────────┘
```

### Mode 1: Instant Free Plan Direct Mode (Zero API Keys)
* **Configuration:**
  1. Open **Organization Settings &rarr; Widget Settings**.
  2. Check **"Only WhatsApp Widget"**.
  3. Enter your WhatsApp number (`+919876543210`) or direct link (`https://wa.me/message/BE3K4RAO24GXK1`).
* **Behavior:** When any visitor types a question or clicks a service suggestion:
  - The question is displayed in the chat.
  - The browser executes an immediate redirect: `window.open('https://wa.me/<number>?text=<encoded_query>', '_blank')`.
  - An inline WhatsApp Action Card is rendered with an `"Open in WhatsApp →"` button if popups are intercepted.
  - Non-blocking conversion metric is recorded in dashboard.
  - Zero AI tokens consumed; zero agent queue clutter.

### Mode 2: Meta WhatsApp Cloud API (Pro & Enterprise)
1. Register a Meta Developer Account at `developers.facebook.com`.
2. Create a WhatsApp Business App and verify your phone number.
3. Obtain your **Phone Number ID**, **WhatsApp Business Account ID**, and **System User Access Token**.
4. In Aprilo Admin, open **Settings &rarr; Integrations &rarr; WhatsApp for Business** and save credentials.
5. Set webhook endpoint to `https://app.apriloinfotech.com/api/webhooks/whatsapp`.

---

## 7. Human Support Escalation Process

When a customer inquiry requires human intervention:

1. **Visitor Trigger:**  
   Visitor types `"talk to agent"`, `"human coordinator"`, or clicks the agent quick action.
2. **Qualification Handshake (2 Steps):**  
   * **Step 1 (Name):** Bot asks: *"To connect you with our live support team, could you please provide your **Name**?"*
   * **Step 2 (Reason):** Bot asks: *"Thank you, [Name]! What is the **reason** for reaching out so we can connect you with the right specialist?"*
3. **Dual Routing Screen:**  
   The chat renders two action paths:
   * **Connect on WhatsApp:** Opens WhatsApp directly with visitor name and reason prefilled.
   * **Chat with Live Agent:** Dispatches the conversation in real time to the Aprilo Agent Console via Firebase Realtime Database.
4. **Dynamic Query Param (`?mode=agent`):**  
   All escalation calls dynamically carry `?mode=agent` (or `?mode=ai_support`), ensuring zero static database lock-in.

---

## 8. Trial Expiry & Subscription Communication Sequences

Automated via Artisan command: `php artisan aprilo:send-trial-notifications`  
(Executed daily via Laravel Scheduler).

### Sequence Roadmap

| Milestone | Target Audience | Subject Line | Core Objective |
| :--- | :--- | :--- | :--- |
| **Day 0** | New trial accounts | Welcome to Aprilo Chatbot — Your 30-Day Free Trial is Live! 🚀 | Onboarding, public key delivery, 3 quick wins. |
| **Day 3** | Active trial users | How is your Aprilo Chatbot performing, {{name}}? | Review early resolution stats, suggest FAQ additions. |
| **Day 10**| Trial users | Tip: Enable WhatsApp Direct Connect on your Aprilo Widget | Showcase mobile WhatsApp redirection to boost conversions. |
| **Day 27**| Expiring in 72h | ⏳ Your Aprilo 30-Day Trial expires in 3 days | Highlight total hours saved and present 1-click Pro upgrade. |
| **Day 30**| Concluded trials | Your Aprilo Chatbot trial has concluded — Keep your assistant active | Transition to Free WhatsApp Mode; offer 1-click ₹499/mo renewal. |

---

## 9. Daily Monitoring & Multi-Channel Conversion Funnel

### Campaign Link Monitored
`https://apriloinfotech.com/products/aprilo-chatbot?utm_source=linkedin&utm_medium=social&utm_campaign=ph_launch_2026`

### Attribution Architecture
* **Capture:** `PhReferralTracker.tsx` parses all incoming traffic parameters (`utm_source`, `utm_medium`, `utm_campaign`, `utm_content`).
* **Persistence:** First-touch and last-touch attribution is stored in `sessionStorage` and a 30-day tracking cookie (`aprilo_utm_source`, `aprilo_utm_campaign`).
* **Funnel Propagation:** When users move from landing to onboarding, bot creation, and payment, their acquisition channel is automatically forwarded.

### Daily Funnel KPIs Table (Monitored in Admin Dashboard)

| Funnel KPI | Analytics Event Name | Tracked Parameters | Dashboard Location |
| :--- | :--- | :--- | :--- |
| **1. Channel Visitors** | `channel_landing_view` | `channel: "linkedin"`, `campaign: "ph_launch_2026"`, `path` | Admin Dashboard &rarr; Funnel Monitor |
| **2. Trial Registrations** | `trial_activated` | `product: "aprilo-chatbot"`, `acquisition_channel` | Admin Dashboard &rarr; Funnel Monitor |
| **3. Chatbots Created** | `bot_created` | `bot_name`, `org_id`, `acquisition_channel` | Admin Dashboard &rarr; Funnel Monitor |
| **4. Widgets Installed** | `widget_installed` | `widget_key`, `domain`, `acquisition_channel` | Admin Dashboard &rarr; Funnel Monitor |
| **5. Paying Subscriptions** | `subscription_started` | `plan`, `value: 499`, `currency: "INR"`, `acquisition_channel`| Admin Dashboard &rarr; Revenue & Funnel |

---

## 10. Implementation File Directory & Commands

### Backend (`E:\aprilo\product\backend`)
* `app/Mail/ChatbotWelcomeMail.php` — Welcome mailable.
* `resources/views/emails/chatbot-welcome.blade.php` — Responsive welcome HTML email.
* `app/Mail/TrialLifecycleReminderMail.php` — Trial lifecycle reminder mailable.
* `resources/views/emails/trial-lifecycle.blade.php` — Day 3, 10, 27, 30 email template.
* `app/Console/Commands/SendTrialLifecycleNotificationsCommand.php` — Daily scan command (`aprilo:send-trial-notifications`).
* `resources/views/admin/dashboard.blade.php` — Daily Customer Onboarding & Channel Attribution Funnel widget.

### Frontend (`E:\aprilo-infotech`)
* `src/app/products/aprilo-chatbot/playbook/page.tsx` — Interactive Customer Onboarding Playbook page.
* `src/components/analytics/PhReferralTracker.tsx` — Universal Multi-Channel UTM & Referrer Tracker.
* `src/lib/analytics.ts` — Attribution persistence and funnel event tracking helpers.
* `aprilo-chat-widget/src/widget/ApriloWidget.ts` — Widget bundle with WhatsApp redirection and cookie session expiration.

### Verification Commands
```bash
# Test trial notifications dispatch
cd /path/to/backend && php artisan aprilo:send-trial-notifications

# Rebuild frontend widget
cd /path/to/aprilo-chat-widget && npm run build

# Rebuild static site export
cd /path/to/frontend && npm run build:static

# Deploy to Firebase Hosting
firebase deploy --only hosting
```
