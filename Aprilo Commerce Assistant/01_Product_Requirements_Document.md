# Product Requirements Document (PRD)
## Aprilo Commerce Assistant — Version 1.0

---

## 1. Document Control
* **Document Name:** Aprilo Commerce Assistant Product Requirements Document (PRD)
* **Version:** 1.0
* **Date:** July 26, 2026
* **Status:** Draft / Initial Release
* **Author:** Antigravity AI (on behalf of the Google DeepMind Advanced Agentic Coding Team)

---

## 2. Product Vision
Aprilo Commerce Assistant enables every Magento store to deploy an AI-powered shopping assistant that goes beyond traditional Q&A by executing real actions inside Magento. 

Traditional commerce chatbots are limited to static FAQ retrieval and keyword-based search. Aprilo acts as a secure, intelligent commerce employee that understands products, customers, orders, and business rules, while seamlessly handing conversations over to human agents when complex situations arise. 

By structuring the platform around three core execution levels, Aprilo establishes a new paradigm in e-commerce automation:
1. **Answer:** The AI answers product, policy, and business questions using synchronized store knowledge (RAG).
2. **Action:** The AI performs secure Magento operations (e.g., updating addresses, adding items to carts, tracking orders, creating returns) through localized API endpoints.
3. **Assist:** The AI collaborates with human agents, handling context handover seamlessly while enabling the agent to take over the conversation instantly.

---

## 3. Objectives & Key Results (OKRs)
* **Objective 1: Reduce Support Ticket Volume for Merchants.**
  * *KR 1.1:* Automate resolution of at least 60% of common tier-1 requests (order tracking, return policies, address edits).
  * *KR 1.2:* Achieve a human escalation rate of under 15% for standard customer journeys.
* **Objective 2: Increase Store Conversions.**
  * *KR 2.1:* Increase product discovery click-through rates by 25% via conversational smart search.
  * *KR 2.2:* Influence and track a 5% average increase in cart value through dynamic upsell and accessory recommendations.
* **Objective 3: Provide a Zero-Code Setup Experience.**
  * *KR 3.1:* Limit merchant onboarding to installing a standard Magento plugin, copying API credentials, and running a one-click synchronization.

---

## 4. Target Users & Personas

### 4.1. Store Owner (Merchant)
* **Profile:** E-commerce manager or store owner running a Magento 2 store.
* **Needs:** 
  * Simple, code-free installation and setup.
  * Ability to configure AI behaviors (toggle features, change colors, set default greeting).
  * Clear ROI dashboard tracking revenue influenced, tickets deflected, and chat conversions.
* **Pain Points:** Traditional chatbots require extensive training, manual flow builders, and fail to interact with Magento database entities.

### 4.2. Customer (Shopper)
* **Profile:** A guest or logged-in shopper visiting the Magento storefront.
* **Needs:**
  * Immediate, conversational answers regarding products, stock, shipping, and policies.
  * Self-service account updates (editing addresses, checking loyalty points) without submitting a form or calling support.
  * Seamless handover to a human when the AI cannot resolve their inquiry.
* **Pain Points:** Search bars that require exact keywords; support agents who lack context when a chat is transferred.

### 4.3. Support Agent
* **Profile:** Customer service representative managing queries.
* **Needs:**
  * A unified dashboard to monitor active chats.
  * Visibility of customer metadata (browsing history, cart contents, order history, and AI conversation history).
  * One-click takeover capability to stop AI responses and chat directly with the shopper.
* **Pain Points:** Switching between Magento admin and external chat systems; repetitive queries taking time away from complex customer issues.

---

## 5. Architectural Philosophy: The Secure Connector
To scale across multiple eCommerce platforms in the future (Shopify, WooCommerce, BigCommerce, Adobe Commerce), the architecture enforces strict boundary conditions:

```
  ┌────────────────────────────────────────────────────────┐
  │                    Magento Store                       │
  │  ┌───────────────────────┐   ┌──────────────────────┐  │
  │  │  Magento Storefront   │   │ Magento Plugin (PHP) │  │
  │  │ (Widget JS + CSS)     │   │ - Webhooks & APIs    │  │
  │  └───────────┬───────────┘   └──────────▲───────────┘  │
  └──────────────┼──────────────────────────┼──────────────┘
                 │ Chat Actions             │ Magento API Wrappers
                 │ & Text                   │ (JWT/HMAC)
  ┌──────────────▼──────────────────────────▼──────────────┐
  │                    Aprilo Platform                     │
  │  ┌──────────────────────────────────────────────────┐  │
  │  │                  AI Orchestrator                 │  │
  │  │  - LLM, Prompting, Memory, Vector DB             │  │
  │  └──────────────────────────────────────────────────┘  │
  │  ┌────────────────────────┐  ┌──────────────────────┐  │
  │  │    Agent Dashboard     │  │   Analytics Engine   │  │
  │  └────────────────────────┘  └──────────────────────┘  │
  └────────────────────────────────────────────────────────┘
```

* **Lightweight Magento Plugin:** Contains no AI logic, vector DBs, LLMs, prompt templates, or analytics engines. It functions as a secure connector, providing data endpoints (products, categories, customer scopes) and processing webhook payloads.
* **Centralized Aprilo Platform:** Houses the "intelligence." All prompt engineering, vector search, chat state management, and the Agent/Merchant dashboards live on the Aprilo cloud. 

---

## 6. Core Modules

### 6.1. AI Shopping Assistant
* **Context:** Visible on all frontend pages.
* **Capabilities:** 
  * Provides natural answers for product specifications, pricing, shipping, and returns.
  * Performs side-by-side product comparisons.
  * Resolves availability queries by reading Magento inventory levels.

### 6.2. Customer Account Assistant
* **Context:** Enabled after customer authentication.
* **Capabilities:**
  * Lists previous orders and extracts shipping tracking details.
  * Displays reward points, coupons, and store credit balances.
  * Displays "recently viewed" items and enables one-click "reorder".

### 6.3. Smart Product Search
* **Context:** Intercepts search intents.
* **Capabilities:**
  * Translates queries like "waterproof running shoes under ₹5,000" into Magento catalog search search filters.
  * Returns structured product card carousels in the chat widget.

### 6.4. Shopping Assistant (Discovery & Recommendation)
* **Context:** Proactive user engagement.
* **Capabilities:**
  * Recommends accessories based on cart items (e.g., suggesting a lens for a camera).
  * Evaluates coupon eligibility and informs the customer of items needed to unlock discounts.

### 6.5. Checkout Assistant
* **Context:** Triggered during cart review and checkout pages.
* **Capabilities:**
  * Explains payment and shipping methods.
  * Assists with address validation queries and solves coupon application issues.

### 6.6. Order Assistant
* **Context:** General order inquiries.
* **Capabilities:**
  * Handles cancellation requests, returns processing, and invoice downloads.
  * Obtains shipping estimates and tracks transit.

### 6.7. Customer Account Management
* **Context:** Authenticated operations.
* **Capabilities:**
  * Creates, deletes, or edits addresses in the customer profile.
  * Modifies profile details (email, phone, newsletter subscriptions).

### 6.8. Human Agent Escalation
* **Context:** Fallback scenario.
* **Capabilities:**
  * Seamlessly passes the conversation to a human support agent when the AI hits confidence thresholds or upon direct user request.
  * Retains full transcripts and user context (cart, profile) during the transfer.

### 6.9. Agent Dashboard
* **Context:** Used by store customer support teams.
* **Capabilities:**
  * Real-time monitoring of active chat sessions.
  * View current customer cart, order history, and pages visited.
  * Directly take over or transfer conversations.

---

## 7. The Three Execution Levels

| Execution Level | Description | Key Data Sources | Magento API Dependency |
|---|---|---|---|
| **Answer** | Resolves general questions using indexed store data. | Products, CMS, Store Policies, FAQs | GraphQL (Cached Catalog Read) |
| **Action** | Modifies state, updates customer info, or submits requests. | Cart, Addresses, Wishlist, Subscriptions | REST (Authenticated Write APIs) |
| **Assist** | Hands off unresolved chats to human agents. | Conversation Log, Customer Meta, Queue | WebSockets (Real-time Handover) |

---

## 8. Magento Plugin Configuration (Merchant Admin)
The plugin settings in the Magento Admin Panel contain four sections:
1. **Connection:** API Keys, Platform URL, Store ID, and Connection Status (with "Test Connection" button).
2. **Widget Customization:** Enable status, branding color, widget position, and default greeting message.
3. **Feature Toggles:** Checkboxes to enable/disable specific AI assistants (Product, Orders, Recommendations, Actions, Handoff).
4. **Data Sync Configuration:** Cron schedules, webhook toggles, and buttons for manual catalog/CMS/inventory synchronization.

---

## 9. Future Integration Scalability
While this specification focuses on Magento, the platform design is decoupled to ensure that the core conversational engine and LLM prompts remain agnostic. Adding Shopify or WooCommerce in the future will only require building a platform-specific plugin mapping to the standardized API contracts described in **05_Platform_API_Specification.md**.
