# Chapter 11 - Risk Analysis

## Executive Summary

The company faces meaningful risks across market adoption, product scope, AI reliability, security, compliance, competition, unit economics, and execution. These risks do not invalidate the opportunity; they define the operating requirements for the company.

The highest-priority risks are trust, data access, workflow accuracy, and focus. Operational AI has more responsibility than a chatbot because it may touch employee data, internal knowledge, business systems, and workflow execution. The company must therefore build governance, permissions, auditability, human approval, and source attribution from the beginning.

Risk management should become a product advantage. If competitors move fast with weak governance, a trust-first platform can win customers that need operational reliability.

## Research Findings

### 1. AI risk frameworks are becoming standard.

NIST provides an AI Risk Management Framework for trustworthy AI. OWASP publishes LLM application risk guidance covering issues such as prompt injection, sensitive information disclosure, insecure output handling, and excessive agency. The EU AI Act establishes a risk-based regulatory framework for AI systems.

Strategic meaning: Governance should be part of product architecture, not only legal documentation.

### 2. Agentic AI increases control requirements.

Deloitte's 2026 State of AI in the Enterprise reports that autonomous agent governance remains immature for many organizations. As AI moves from answering to acting, customers will demand stronger controls.

Strategic meaning: Human approval, action limits, rollback, audit logs, and permission-aware execution should be core features.

### 3. Data breach and privacy risk are material business risks.

AI platforms can increase exposure if they ingest sensitive documents, employee data, customer records, or business-system credentials. Security failures can damage trust and create legal cost.

Strategic meaning: The company must invest early in access control, encryption, vendor risk management, security logging, and data minimization.

## Risk Register

| Risk | Severity | Probability | Impact | Mitigation |
|---|---:|---:|---|---|
| Market adoption risk | High | Medium | Customers may be interested but slow to buy | Paid pilots, ROI proof, narrow ICP |
| Focus risk | High | High | Too many industries dilute product | HRMS wedge, expansion gates |
| AI hallucination risk | High | Medium | Incorrect answers reduce trust | RAG grounding, citations, confidence thresholds, escalation |
| Excessive agency risk | High | Medium | AI performs unsafe actions | Human approval, permissions, action limits |
| Data privacy risk | High | Medium | Sensitive HR/customer data exposure | RBAC, encryption, retention policy, audit logs |
| Security breach risk | High | Medium | Loss of customer trust, legal cost | Secure SDLC, monitoring, least privilege, SOC 2 roadmap |
| Integration reliability risk | High | Medium | Broken connectors harm workflows | Connector testing, version monitoring, fallback handling |
| Incumbent competition | High | High | Platforms bundle native AI | Cross-system positioning, speed, partner channel |
| Gross margin risk | Medium-High | Medium | AI costs reduce margins | Usage controls, model routing, caching |
| Services trap | Medium-High | Medium | Company becomes custom implementation agency | Productize onboarding and capability packs |
| Regulatory risk | Medium-High | Medium | AI/data rules change by region | Compliance review, risk classification, data boundaries |
| Talent risk | Medium | Medium | Hard to hire AI/security engineers | Small senior team, focused architecture |

## AI-Specific Risk Controls

| Control | Purpose |
|---|---|
| Source attribution | Shows where answers come from |
| Confidence scoring | Identifies uncertain responses |
| Human approval | Prevents unsafe automation |
| Role-based access | Ensures users see only permitted data |
| Audit logs | Supports compliance and debugging |
| Action permissions | Controls what AI can execute |
| Retrieval boundaries | Prevents cross-customer or unauthorized data leakage |
| Prompt-injection defenses | Reduces malicious instruction risk |
| Data retention controls | Limits unnecessary data storage |
| Escalation paths | Routes uncertain cases to humans |

## Strategic Analysis

The largest strategic risk is not technical failure alone. It is building too broad too early. A company trying to serve HRMS, ecommerce, ERP, CRM, healthcare, and manufacturing at the same time will likely create shallow integrations and high services burden.

The second major risk is trust. If customers do not trust the system with employee data or workflow actions, the product cannot become operational. Trust must be designed through architecture and surfaced through UX: citations, permissions, logs, approval states, and measurable outcomes.

The third major risk is incumbent bundling. Large platforms will add native AI. The company's answer must be cross-system context, faster specialization, partner delivery, and SMB/mid-market accessibility.

## Founder Decisions

1. Build governance and auditability into MVP, even if the first workflow is simple.

2. Use human approval for sensitive write-actions until reliability is proven.

3. Start with read/search/assist workflows before high-risk autonomous execution.

4. Maintain a narrow HRMS wedge until repeatability is proven.

5. Track AI cost and reliability as product KPIs, not only engineering metrics.

6. Create a security and AI risk roadmap before selling to larger customers.

## Open Questions

1. Which HR data fields should never be ingested in the MVP?

2. What customer security standard is needed before mid-market adoption: SOC 2 roadmap, SOC 2 Type I, ISO 27001, or vendor questionnaire readiness?

3. What actions should always require human approval?

4. How should the system handle conflicting policy documents?

5. What confidence threshold should trigger escalation?

6. Which jurisdiction should be prioritized first for compliance design?

## References

1. NIST AI Risk Management Framework. https://www.nist.gov/itl/ai-risk-management-framework

2. OWASP Top 10 for Large Language Model Applications. https://owasp.org/www-project-top-10-for-large-language-model-applications/

3. EU AI Act official regulatory framework page. https://digital-strategy.ec.europa.eu/en/policies/regulatory-framework-ai

4. IBM Cost of a Data Breach Report. https://www.ibm.com/reports/data-breach

5. Deloitte, "The State of AI in the Enterprise." https://www.deloitte.com/us/en/what-we-do/capabilities/applied-artificial-intelligence/content/state-of-ai-in-the-enterprise.html

6. McKinsey, "The state of AI in 2025." https://www.mckinsey.com/capabilities/quantumblack/our-insights/the-state-of-ai
