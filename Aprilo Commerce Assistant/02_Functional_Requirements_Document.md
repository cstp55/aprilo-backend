# Functional Requirements Document (FRD)
## Aprilo Commerce Assistant — Version 1.0

---

## 1. Document Control
* **Document Name:** Aprilo Commerce Assistant Functional Requirements Document (FRD)
* **Version:** 1.0
* **Date:** July 26, 2026
* **Status:** Draft / Initial Release
* **Author:** Antigravity AI (on behalf of the Google DeepMind Advanced Agentic Coding Team)

---

## 2. System Scope & Boundaries
The Aprilo Commerce Assistant system comprises three main functional components:
1. **Frontend Widget:** Script injected into the Magento storefront rendering the chat interface.
2. **Magento Plugin:** Local PHP module handling configuration, webhooks, data synchronization, and API translations.
3. **Aprilo Platform:** Core cloud service handling session memory, AI orchestration (RAG/NLP), agent dashboards, and analytics.

---

## 3. Core Modules Functional Requirements

### 3.1. AI Shopping Assistant
* **FR-1.1: Product Q&A:** The system shall answer natural language queries regarding catalog products (e.g., color, size, specs, inventory status, custom options).
* **FR-1.2: Product Comparison:** The system shall generate markdown tables comparing up to 3 products side-by-side on dimensions like price, rating, features, and stock availability.
* **FR-1.3: RAG Citation:** All product answers must reference specific catalog entities. The chat widget must display clickable links to product detail pages (PDP) for any recommended items.

### 3.2. Customer Account Assistant (Authenticated)
* **FR-2.1: Order History:** The system shall display the 5 most recent orders with status (processing, shipped, completed, canceled) and grand total.
* **FR-2.2: Order Reordering:** The system shall provide a "Reorder" button next to past orders, which triggers an API call to add all items to the current cart.
* **FR-2.3: Reward Points & Store Credit:** The system shall retrieve and display the customer's current balance of reward points and store credit when requested.

### 3.3. Smart Product Search
* **FR-3.1: Conversational Search Parsing:** The system shall parse unstructured user prompts to extract search filters (e.g., category, color, material, price bounds).
* **FR-3.2: Magento Search Translation:** The platform shall query the Magento REST API or GraphQL with filter groups matching the extracted entities.
* **FR-3.3: Product Carousel UI:** Results must be rendered as an interactive, horizontal swipeable carousel in the chat widget containing the product image, title, price, and an "Add to Cart" button.

### 3.4. Shopping Assistant (Recommendations & Bundles)
* **FR-4.1: Accessory Recommendations:** When a customer adds an item to the cart, the AI shall query Magento's related, cross-sell, or up-sell products to suggest accessories.
* **FR-4.2: Coupon Eligibility:** The system shall analyze the cart items and value, informing the user if they qualify for active coupon rules (e.g., "Add ₹500 more to get free shipping").

### 3.5. Checkout Assistant
* **FR-5.1: Address Validation:** The system shall assist the customer in formatting their billing/shipping address if the Magento checkout system rejects it.
* **FR-5.2: Shipping & Payment FAQs:** The system shall answer queries on shipping rates, transit times, and accepted payment methods using indexed store policies.

### 3.6. Order Assistant (Post-Purchase)
* **FR-6.1: Real-time Order Tracking:** The system shall retrieve tracking numbers and carrier URLs (UPS, FedEx, DHL, etc.) from Magento shipments and display them to the user.
* **FR-6.2: Order Cancellation:** For orders with status `pending`, the system shall enable the customer to request cancellation. The AI will call the Magento API to cancel the order and update the state.
* **FR-6.3: Returns & Refund Status:** The system shall guide the user through creating a RMA (Return Merchandise Authorization) and fetch existing return request states.

### 3.7. Customer Account Management
* **FR-7.1: Address Book Management:** The system shall allow users to add, update, or delete addresses in their address book via chat.
* **FR-7.2: Newsletter Subscription:** The system shall process commands to toggle the newsletter subscription flag (`subscribed` / `unsubscribed`) for the customer profile.

### 3.8. Human Agent Escalation
* **FR-8.1: Confidence-Based Fallback:** If the AI confidence score drops below 0.70 for two consecutive turns, the system must present a "Talk to a Human" button.
* **FR-8.2: Explicit Escalation:** The system shall trigger the escalation workflow immediately if the user types phrases like "talk to agent", "representative", "human", or "customer service".
* **FR-8.3: State Handover:** Upon escalation, the AI must lock its input field, mark the session state as `escalated`, and push the transcript to the active agent queue.

### 3.9. Agent Dashboard
* **FR-9.1: Live Conversations Feed:** The dashboard shall display a list of all active chat sessions sorted by state (Unassigned, AI Active, Escalated, Closed).
* **FR-9.2: Customer Details Panel:** Clicking a chat must display:
  * Customer Name, Email, Group, and Lifetime Value.
  * Active Cart items, totals, and coupon codes applied.
  * Recent browsing history (last 5 URLs visited).
* **FR-9.3: Manual Intervention:** The agent must be able to send real-time text/image messages, apply predefined macro responses, and transfer the chat to another agent.

---

## 4. Magento Admin Configuration
The Magento plugin config screen must support:
* **Connection Validation:** A "Test Connection" button that sends a ping to the Aprilo Platform with the configured API credentials and displays a success or failure notification.
* **Widget Theme Manager:** A visual color picker to select the widget's primary and secondary brand colors, which updates the injected JS configuration in real-time.
* **Synchronization Manager:** A table showing the sync status of:
  * Catalog (Products & Categories)
  * Store CMS pages (Refund/Shipping policy)
  * Inventory levels
* The merchant can trigger a manual sync or define cron intervals (e.g., hourly, daily) for automated sync.

---

## 5. Non-Functional & Performance Requirements
* **Response Latency:** AI chat responses must have a time-to-first-token of under 1.5 seconds.
* **API Overhead:** Plugin API callbacks must not add more than 200ms to Magento server response times.
* **Scalability:** The platform must support up to 5,000 concurrent storefront chat connections per tenant.
* **Data Security:** All customer sessions must expire after 30 minutes of inactivity.
