# Test Cases
## Aprilo Commerce Assistant — Version 1.0

---

## 1. Document Control
* **Document Name:** Aprilo Commerce Assistant Test Cases
* **Version:** 1.0
* **Date:** July 26, 2026
* **Status:** Draft / Initial Release
* **Author:** Antigravity AI (on behalf of the Google DeepMind Advanced Agentic Coding Team)

---

## 2. System Test Cases

### TC-SYS-01: Conversational Product Recommendation (RAG)
* **Pre-conditions:** The Magento catalog has been synced to Aprilo.
* **Input Data:** User query: *"Show me gaming laptops under ₹70,000."*
* **Steps:**
  1. Open storefront widget.
  2. Enter the input query and press send.
  3. Wait for response.
* **Expected Result:** The system returns a markdown response answering the request, followed by a product carousel showing laptops matching the criteria with images, prices, and direct links to their PDPs.

### TC-SYS-02: Customer Address Update via Chat (Action Level)
* **Pre-conditions:** Customer is logged in to the Magento storefront.
* **Input Data:** User query: *"Update my shipping address to 12 Ring Road, Delhi."*
* **Steps:**
  1. Login to the customer account.
  2. Open the chat widget and type address change request.
  3. Confirm the address details when prompted by the AI.
  4. Navigate to Magento account dashboard -> Address Book.
* **Expected Result:** The AI updates the database, confirms success in the chat, and the Magento Admin panel/customer dashboard displays the newly added address.

---

## 3. Integration Test Cases

### TC-INT-01: Catalog Synchronisation Cron
* **Pre-conditions:** Cron is active on Magento; new products are added in the Magento catalog admin.
* **Input Data:** Add product "Aero Running Shoes" in Magento.
* **Steps:**
  1. Create the product in Magento Admin catalog.
  2. Run `bin/magento cron:run --group=default` manually or wait for scheduling.
  3. Query the product via Aprilo's search endpoint.
* **Expected Result:** The product is recorded in the `aprilo_sync_queue` table with status `1` (Success) and is searchable on Aprilo via embeddings.

### TC-INT-02: Webhook Trigger on Order Status Change
* **Pre-conditions:** An order exists in `processing` state.
* **Input Data:** Shift order status to `shipped` in Magento Admin and add carrier details.
* **Steps:**
  1. In Magento Admin, go to Sales -> Orders.
  2. Ship the order, typing carrier `FedEx` and tracking number `1234567890`.
  3. Check the webhook request logs on Aprilo Platform.
* **Expected Result:** Magento fires the `sales_order_save_after` event, and the plugin sends a POST request containing order totals, carrier tracking number, and customer email to Aprilo.

---

## 4. Security Test Cases

### TC-SEC-01: Reject Webhook with Invalid HMAC Signature
* **Pre-conditions:** Platform webhook listener is running.
* **Input Data:** Send POST request with modified body or incorrect header `X-Aprilo-Signature`.
* **Steps:**
  1. Intercept a legitimate Platform -> Magento address update payload.
  2. Modify the `customer_id` from `12` to `1` (Admin).
  3. Send request to Magento REST API without updating the signature hash.
* **Expected Result:** Magento plugin detects that the computed SHA256 signature does not match the header signature and returns `HTTP 401 Unauthorized`. The address update is rejected.

### TC-SEC-02: Unauthorized Data Access (Customer Boundary)
* **Pre-conditions:** Customer A is logged in. Customer B's order exists.
* **Input Data:** Customer A queries Customer B's order ID `10000495` via storefront chat.
* **Steps:**
  1. Authenticate customer A.
  2. Ask AI to: *"Show my order #10000495."*
* **Expected Result:** The Magento API wrapper filters orders using the authenticated session customer ID. Since the requested order does not belong to Customer A, the system returns: *"We couldn't find an order with that ID associated with your account."*

---

## 5. User Acceptance Testing (UAT) Criteria
* **Merchant Acceptance:**
  * Installation of extension takes less than 15 minutes.
  * API validation passes automatically upon credential configuration.
  * Live agent takeover switches chat control instantly without browser refreshes.
* **Customer Acceptance:**
  * Widget opens on mobile devices without obstructing checkout page flows.
  * Response times are consistently under 2 seconds.
  * Product carousel details match current store inventory.
