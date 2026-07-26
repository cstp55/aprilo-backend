# System Architecture
## Aprilo Commerce Assistant — Version 1.0

---

## 1. Document Control
* **Document Name:** Aprilo Commerce Assistant System Architecture
* **Version:** 1.0
* **Date:** July 26, 2026
* **Status:** Draft / Initial Release
* **Author:** Antigravity AI (on behalf of the Google DeepMind Advanced Agentic Coding Team)

---

## 2. High-Level Component Architecture

```
                 ┌───────────────────────────────────────┐
                 │          Storefront Browser          │
                 │   ┌───────────────┐   ┌───────────┐   │
                 │   │  Store Page   │   │ widget.js │   │
                 │   └───────────────┘   └─────▲─────┘   │
                 └─────────────────────────────┼─────────┘
                                               │ WebSockets / HTTP
                                               ▼
  ┌─────────────────────────────────────────────────────────────────────────────┐
  │                              Aprilo Platform                                │
  │   ┌────────────────────┐   ┌──────────────────────┐   ┌─────────────────┐   │
  │   │  API Gateway &     │   │   AI Orchestrator    │   │  Vector DB      │   │
  │   │  Security Router   │   │   (State, Memory)    │   │  (pgvector)     │   │
  │   └─────────┬──────────┘   └───────────▲──────────┘   └────────▲────────┘   │
  │             │                          │                       │            │
  │             │ REST Calls               └───── RAG Lookup ──────┘            │
  │             ▼                                                               │
  │   ┌────────────────────┐   ┌──────────────────────┐                         │
  │   │  Agent Dashboard   │   │   Analytics Engine   │                         │
  │   │  (Live Escalation) │   │                      │                         │
  │   └────────────────────┘   └──────────────────────┘                         │
  └─────────────┬───────────────────────────────────────────────────────────────┘
                │
                │ Encrypted API Calls (HMAC)
                ▼
  ┌─────────────────────────────────────────────────────────────┐
  │                       Magento Server                        │
  │   ┌──────────────────────────┐   ┌──────────────────────┐   │
  │   │   CommerceAssistant      │   │  Magento Core APIs   │   │
  │   │   Plugin (Controllers)   │   │  (Catalog, Orders)   │   │
  │   └─────────┬────────────────┘   └──────────▲───────────┘   │
  │             │                               │               │
  │             └──────── Internal Query ───────┘               │
  └─────────────────────────────────────────────────────────────┘
```

---

## 3. Core Sequence Flows

### 3.1. Dynamic Tool Calling & Magento Query
This diagram illustrates the sequence when a customer asks a question requiring database retrieval (e.g., *"Where is my order?"*).

```mermaid
sequenceDiagram
    autonumber
    actor Customer as Storefront Customer
    participant Widget as widget.js
    participant Platform as Aprilo Platform
    participant Plugin as Magento Plugin
    participant Magento as Magento Core

    Customer->>Widget: "Check my last order"
    Widget->>Platform: POST /api/v1/chat/message (include JWT)
    Note over Platform: AI parses intent:<br/>wants order details.<br/>Triggers GET_ORDERS tool.
    Platform->>Plugin: GET /rest/V1/aprilo/customer/details (HMAC Signed)
    Note over Plugin: Validate signature &<br/>retrieve customer ID from JWT
    Plugin->>Magento: Fetch recent orders
    Magento-->>Plugin: Return Order #10000492 details
    Plugin-->>Platform: Return Order JSON
    Note over Platform: AI formats response<br/>with order status
    Platform-->>Widget: Message payload (markdown format)
    Widget-->>Customer: "Your order #10000492 is in transit..."
```

### 3.2. Platform Inbound Security validation
Flow detailing signature validation of platform-triggered requests.

```mermaid
sequenceDiagram
    autonumber
    participant Platform as Aprilo Platform
    participant Plugin as Magento Plugin
    participant Core as Magento Core

    Platform->>Plugin: HTTP POST /V1/aprilo/customer/address
    Note over Platform: Generates signature using<br/>HMAC-SHA256(Secret, payload)
    Note over Plugin: 1. Verify X-Aprilo-Timestamp<br/>2. Re-compute HMAC signature<br/>3. Match with Header Signature
    alt Signatures Match
        Plugin->>Core: Process Address Update
        Core-->>Plugin: Success
        Plugin-->>Platform: HTTP 200 OK (address_id: 46)
    else Invalid Signature / Expired Timestamp
        Plugin-->>Platform: HTTP 401 Unauthorized
    end
```

### 3.3. Human Handoff Workflow
Flow detailing how a customer is seamlessly transitioned to a human agent when the AI cannot resolve the request.

```mermaid
sequenceDiagram
    autonumber
    actor Customer as Storefront Customer
    participant Widget as widget.js
    participant Platform as Aprilo Platform
    participant Agent as Agent Dashboard (WS)

    Customer->>Widget: "I want to talk to a human"
    Widget->>Platform: POST /api/v1/chat/message (Escalation Intent)
    Note over Platform: 1. Set session state: escalated<br/>2. Put session in unassigned queue
    Platform->>Agent: WebSocket Event: "new_escalation_alert" (transcripts, user meta)
    Platform-->>Widget: "Connecting to agent..." (Disable input box)
    Note over Agent: Agent John accepts chat
    Agent->>Platform: Join Session Event
    Platform->>Widget: WebSocket Event: "agent_joined" (Enable input box)
    Customer->>Widget: "My package is lost..."
    Widget->>Platform: Forward Message (Direct routing)
    Platform->>Agent: WebSocket Broadcast: Display message
```
