# User Journey Map
## Aprilo Commerce Assistant — Version 1.0

---

## 1. Document Control
* **Document Name:** Aprilo Commerce Assistant User Journey Map
* **Version:** 1.0
* **Date:** July 26, 2026
* **Status:** Draft / Initial Release
* **Author:** Antigravity AI (on behalf of the Google DeepMind Advanced Agentic Coding Team)

---

## 2. Journey 1: Anonymous Visitor Product Discovery
* **User Persona:** Guest Shopper looking for products but overwhelmed by catalogs.
* **Goal:** Find and purchase a product matching specific requirements.

```
Shopper                  Store Widget                  Aprilo Platform              Magento API
   │                          │                              │                           │
   │── Lands on home page ───>│                              │                           │
   │                          │── Retrieve session & token ─>│                           │
   │                          │<─ Token / Config returned ───│                           │
   │                          │                              │                           │
   │── "Gaming laptop" ──────>│                              │                           │
   │   under ₹70,000          │── Send message with token ──>│                           │
   │                          │                              │── Parse query filters ───>│
   │                          │                              │<─ Return search results ──│
   │                          │<─ Send Product Carousel ─────│                           │
   │<─ Carousel displayed ────│                              │                           │
   │                          │                              │                           │
   │── Clicks "Buy Now" ─────>│── Add to Cart request ──────>│                           │
   │                          │                              │── Trigger AddToCart ─────>│
   │                          │<─ Update cart layout ────────│                           │
```

### Detailed Sequence of Events
1. **Landing:** Customer lands on the storefront. The injected JavaScript (`widget.js`) checks local storage for a session. If empty, it requests a guest token from the Aprilo Platform.
2. **User Input:** Shopper opens the chat widget and types: *"I need a gaming laptop under ₹70,000."*
3. **Intent Parsing:** The widget sends the text to the Aprilo Platform. The platform's NLP engine parses the message, extracting:
   * `category`: `laptops`
   * `attributes`: `gaming`
   * `price_max`: `70000`
4. **Data Retrieval:** Aprilo executes a GraphQL query to Magento's catalog search using these filters:
   ```graphql
   query {
     products(filter: { category_id: { eq: "15" }, price: { lte: "70000" } }) {
       items { name sku price_range { minimum_price { final_price { value } } } image { url } }
     }
   }
   ```
5. **Rendering:** Aprilo formats the results into a product card carousel and returns it to `widget.js`.
6. **Action:** The user swipes through the carousel, clicks **Add to Cart** on a laptop. The widget triggers an direct add-to-cart call to Magento and updates the cart badge.

---

## 3. Journey 2: Logged-in Customer Account & Order Management
* **User Persona:** Existing Customer checking order status and modifying account details.
* **Goal:** Track shipment and update the shipping address for future orders.

### Detailed Sequence of Events
1. **Authentication Handshake:**
   * Customer logs into their account via the Magento frontend.
   * Magento fires a customer login event, and the plugin sets a session cookie containing a secure JWT.
   * `widget.js` reads the JWT and includes it in all request headers to Aprilo.
2. **Order Tracking Request:**
   * Customer asks: *"Where is my order #10000492?"*
   * Aprilo checks the customer's JWT, verifies their identity, and queries the Magento plugin endpoint:
     `GET /rest/V1/aprilo/orders/10000492`
   * The plugin verifies that the order belongs to the authenticated customer ID and returns the shipment payload:
     `{ "status": "shipped", "carrier": "FedEx", "tracking_number": "1234567890", "est_delivery": "2026-07-28" }`
   * Aprilo replies to the customer: *"Your order #10000492 was shipped via FedEx. Tracking number is 1234567890. It is estimated to arrive on July 28, 2026."* and displays a tracking link.
3. **Address Update (Action Level):**
   * Customer states: *"I want to update my default shipping address."*
   * Aprilo asks: *"Sure! Please provide your new address."*
   * Customer responds with the address.
   * Aprilo compiles the address components, calls the address validation API, and updates Magento:
     `POST /rest/V1/aprilo/customers/me/addresses`
   * Once successful, the AI confirms: *"Your default shipping address has been updated successfully!"*

---

## 4. Journey 3: Agent Handoff / Human Escalation
* **User Persona:** Frustrated Customer unable to resolve a delivery issue.
* **Goal:** Speak to a human agent to resolve a custom shipping issue.

```
Shopper                  Store Widget                  Aprilo Platform              Agent Dashboard
   │                          │                              │                           │
   │── "I need to talk" ─────>│                              │                           │
   │   to a human             │── Post message with tag ────>│                           │
   │                          │                              │── Set state: ESCALATED ──>│
   │                          │                              │── Notify Agents (WS) ────>│
   │<─ "Connecting..." ───────│                              │                           │
   │                          │                              │                           │
   │                          │                              │<─ Agent joins session ────│
   │<─ "Agent John joined" ───│                              │                           │
   │                          │                              │                           │
   │── "My package is lost" ─>│                              │                           │
   │                          │── Forward text directly ────>│                           │
   │                          │                              │── Broadcast to Agent ────>│
   │                          │                              │<──────────────────────────│
```

### Detailed Sequence of Events
1. **Trigger:** The customer says: *"My package says delivered but I don't see it. I need to talk to a human."*
2. **Escalation Trigger:** The Aprilo NLP engine detects the escalation intent. 
3. **State Change:** 
   * The conversation status in the database changes from `AI_ACTIVE` to `ESCALATED`.
   * The chat input in the storefront widget changes to a loading state: *"Connecting you to a live support representative..."*
   * The system fires a WebSocket event to the Aprilo Agent Dashboard.
4. **Agent Acceptance:**
   * Agent John sees the alert in the "Unassigned Escalations" queue.
   * John clicks **Accept Chat**. The conversation state changes to `AGENT_ACTIVE`.
   * The storefront widget displays a notification: *"Agent John has joined the conversation."* and unlocks the text input.
5. **Direct Chat:** The customer and John chat in real-time. The AI continues to monitor the conversation silently to record context but does not generate responses.
6. **Closing:** John resolves the issue, clicks **Resolve Ticket** in the agent dashboard. The conversation state changes to `CLOSED`, and the widget returns to its launcher state.
