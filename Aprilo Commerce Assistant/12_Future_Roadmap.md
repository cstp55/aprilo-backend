# Future Roadmap
## Aprilo Commerce Assistant — Version 1.0

---

## 1. Document Control
* **Document Name:** Aprilo Commerce Assistant Future Roadmap
* **Version:** 1.0
* **Date:** July 26, 2026
* **Status:** Draft / Initial Release
* **Author:** Antigravity AI (on behalf of the Google DeepMind Advanced Agentic Coding Team)

---

## 2. Multi-Platform Extension Strategy
To scale Aprilo beyond Magento, the core Platform logic must remain entirely decoupled from any specific eCommerce system. Platforms are integrated using a translation layer or adapter pattern.

```
                  ┌─────────────────────────────────────┐
                  │           Aprilo Platform           │
                  │   ┌─────────────────────────────┐   │
                  │   │    Platform Adapter Core    │   │
                  │   └──────────────┬──────────────┘   │
                  └──────────────────┼──────────────────┘
                                     │
         ┌───────────────────────────┼───────────────────────────┐
         ▼                           ▼                           ▼
 ┌───────────────┐           ┌───────────────┐           ┌───────────────┐
 │ Magento Adapter│          │Shopify Adapter│           │ WooComm Adapter│
 └───────┬───────┘           └───────┬───────┘           └───────┬───────┘
         │                           │                           │
         ▼                           ▼                           ▼
 ┌───────────────┐           ┌───────────────┐           ┌───────────────┐
 │ Magento 2 API │           │ Shopify Admin │           │ WooCommerce   │
 │ (REST/GraphQL)│           │ (GraphQL App) │           │ REST API      │
 └───────────────┘           └───────────────┘           └───────────────┘
```

### 2.1. Translation Adapter Interface
The platform declares a generic interface for commerce adapters:
```typescript
interface CommerceAdapter {
  getCustomerDetails(customerId: string): Promise<GenericCustomer>;
  updateCustomerAddress(customerId: string, address: GenericAddress): Promise<boolean>;
  getOrders(customerId: string): Promise<GenericOrder[]>;
  searchCatalog(filters: CatalogFilters): Promise<GenericProduct[]>;
  applyCoupon(cartId: string, couponCode: string): Promise<CouponStatus>;
}
```
* **Step 1:** Shopify / WooCommerce plugin is developed to expose the same API contracts defined in `05_Platform_API_Specification.md`.
* **Step 2:** The platform-specific adapter is loaded into the platform workspace depending on the tenant's registered `platform_type` metadata. No alterations are needed for LLM agent prompt configurations or memory modules.

---

## 3. Advanced AI Capabilities

### 3.1. Multimodal Product Discovery
* **Feature:** Customers can upload images of items (e.g., matching a shirt they saw) directly into the chat widget.
* **Mechanism:** The Aprilo platform processes the image using vision language models (e.g. Gemini Pro Vision), generates search embedding representations, and runs cosine similarity matching against the tenant's vector catalog to return visually similar products.

### 3.2. Proactive Cart Abandonment Recovery
* **Feature:** Re-engaging users who left items in their carts.
* **Mechanism:** The platform registers an event after 30 minutes of cart inactivity, evaluates shopper preferences, and sends a push notification or email with a tailored coupon. When clicked, it resumes the chat session with: *"I noticed you left items in your cart. Would you like a 10% coupon to complete your order?"*

---

## 4. Analytical Scale Architecture
As message volumes increase across thousands of storefront tenants, the platform will migrate telemetry and chat audit logs to a columnar database.

* **Database Choice:** **ClickHouse** or **Amazon Redshift** for raw message telemetry, response times, and event tracking.
* **Attribution Engine:** Machine learning models will run over the session logs to calculate exact conversions, establishing direct causal links between AI suggestions and completed checkout orders.
