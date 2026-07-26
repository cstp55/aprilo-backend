# Development Roadmap
## Aprilo Commerce Assistant — Version 1.0

---

## 1. Document Control
* **Document Name:** Aprilo Commerce Assistant Development Roadmap
* **Version:** 1.0
* **Date:** July 26, 2026
* **Status:** Draft / Initial Release
* **Author:** Antigravity AI (on behalf of the Google DeepMind Advanced Agentic Coding Team)

---

## 2. Project Execution Phases

```
┌────────────────────────────────────────────────────────────────────────┐
│                          Development Roadmap                           │
├─────────┬───────────────────────────────────┬───────────┬──────────────┤
│ Phase   │ Description                       │ Duration  │ Target Milest│
├─────────┼───────────────────────────────────┼───────────┼──────────────┤
│ Phase 1 │ Plugin Scaffolding & Setup        │ Sprints1-2│ Local API    │
│ Phase 2 │ Sync Infrastructure & Webhooks    │ Sprints3-4│ Catalog Sync │
│ Phase 3 │ Core AI Engine (Answer/Action)    │ Sprints5-6│ RAG & Tools  │
│ Phase 4 │ Handoff & Agent Dashboard         │ Sprints7-8│ WebSocket Live│
│ Phase 5 │ Hardening, UAT & Launch           │ Sprint 9  │ Prod Ready   │
└─────────┴───────────────────────────────────┴───────────┴──────────────┘
```

---

## 3. Sprint-by-Sprint Breakdown

### Phase 1: Plugin Scaffolding & Configuration (Sprints 1 - 2)
* **Sprint 1: Extension Foundation**
  * Scaffold `Aprilo_CommerceAssistant` Magento extension structure.
  * Define module configurations, system.xml for merchant options, and XML injection rules.
  * *Milestone:* Injected widget script compiles and loads on storefront.
* **Sprint 2: REST & GraphQL API Endpoints**
  * Implement endpoint wrappers for Customer Addresses, Orders, and Profiles.
  * Scaffold database tables for settings and token storage.
  * *Milestone:* Successfully call Magento APIs using JWT/HMAC headers from a REST client.

### Phase 2: Sync Infrastructure & Real-Time Sync (Sprints 3 - 4)
* **Sprint 3: Catalog & CMS Data Sync**
  * Build sync queue model (`aprilo_sync_queue`) in Magento.
  * Implement cron job executing every 15 minutes to push changes to Aprilo Platform.
  * *Milestone:* Products and Category catalog data successfully indexed in Aprilo DB.
* **Sprint 4: Real-time Webhook Events**
  * Implement event observers for `customer_login` and `sales_order_save_after`.
  * Build HMAC validation service inside the Magento extension.
  * *Milestone:* Order status updates instantly push webhook notifications to Aprilo.

### Phase 3: Platform AI Orchestrator: Answer & Action (Sprints 5 - 6)
* **Sprint 5: Semantic Search & RAG (Answer)**
  * Set up PostgreSQL + pgvector databases on the Aprilo platform.
  * Build RAG pipeline using catalog index and store policies.
  * *Milestone:* Shopping Assistant successfully answers product specifications and returns PDP catalog cards.
* **Sprint 6: Dynamic Action Executions (Action)**
  * Define LLM tool-calling registry.
  * Integrate OpenAI/Gemini agent models to trigger API calls (cart changes, address updates).
  * *Milestone:* AI correctly updates user address books and adds items to cart based on conversational requests.

### Phase 4: Live Handoff & Support Agent Interface (Sprints 7 - 8)
* **Sprint 7: Agent Workspace Dashboard**
  * Build the React/Next.js dashboard for support agents.
  * Set up session queue sorting, filtering, and customer metadata views.
  * *Milestone:* Dashboard displays active chats, customer cart contents, and history logs.
* **Sprint 8: WebSocket Real-Time Handover (Assist)**
  * Implement WebSocket server (Socket.io or Laravel Reverb) to manage real-time chats.
  * Build agent-to-customer direct routing logic.
  * *Milestone:* Agents can seamlessly take over active AI chats and send messages without page reloads.

### Phase 5: Hardening, UAT & Pilot Deployment (Sprint 9)
* **Sprint 9: System Hardening**
  * Run performance testing (verifying API latency under high traffic).
  * Conduct security audit (vulnerability checking on API inputs, JWT verification).
  * Onboard first pilot Magento merchant for validation.
  * *Milestone:* Production deployment.

---

## 4. Definition of Done (DoD)
To mark any task or sprint as complete, it must meet the following criteria:
1. **Code Quality:** Passing static analysis (PHPStan Level 6 for Magento, ESLint for Next.js).
2. **Testing:** Unit test coverage above 80%. Integration tests verified for all API contracts.
3. **Security:** No hardcoded keys. Webhooks validated via HMAC, APIs authenticated via JWT.
4. **Documentation:** Code functions annotated. Corresponding specifications updated.
