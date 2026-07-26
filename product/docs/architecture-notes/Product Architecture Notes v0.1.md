# Product Architecture Notes v0.1

## Purpose

This is not a technical architecture specification. It is a product-level architecture note that defines the major modules the MVP must support so engineering decisions stay aligned with the product thesis.

## Architecture Principle

The platform should not be built as a one-off HR chatbot. It should be built as the first capability pack on top of reusable Operational AI platform primitives.

## Core Platform Primitives

| Primitive | Purpose | Reuse Beyond HR |
|---|---|---|
| Tenant workspace | Isolate each customer | Required for all verticals |
| User and role model | Control access | Required for ecommerce, ERP, CRM |
| Knowledge ingestion | Load approved business knowledge | Required for all capability packs |
| Retrieval and source grounding | Answer from trusted sources | Required for trustworthy AI |
| Permission filter | Prevent unauthorized answers | Required for enterprise trust |
| AI orchestration | Route prompts, tools, sources, and safety rules | Required for future workflows |
| Escalation engine | Route uncertain/sensitive requests to humans | Required for controlled automation |
| Audit log | Track what happened | Required for compliance and debugging |
| ROI analytics | Measure business impact | Required for pricing and retention |
| Connector layer | Integrate business systems | Required for platform expansion |

## MVP Module Map

### 1. Workspace Module

Responsibilities:

- Organization account.
- User membership.
- Workspace settings.
- Data isolation.

### 2. Identity and Access Module

Responsibilities:

- Login.
- Roles.
- Access groups.
- Permission checks.

Initial roles:

- Owner.
- HR Admin.
- Manager.
- Employee.

### 3. Knowledge Module

Responsibilities:

- Source upload.
- Source metadata.
- Source access scope.
- Indexing status.
- Source deactivation.

Supported source types:

- PDF.
- DOCX.
- TXT/Markdown.
- FAQ.
- Later: URLs, HRMS knowledge, helpdesk articles.

### 4. AI Answer Module

Responsibilities:

- Question intake.
- Retrieval from approved sources.
- Permission filtering.
- Answer generation.
- Citation generation.
- Confidence assessment.
- Safe fallback.

Behavior:

- Answer from sources when possible.
- Ask clarification when needed.
- Escalate sensitive or low-confidence cases.
- Never invent policy.

### 5. Escalation Module

Responsibilities:

- Create escalation.
- Assign HR owner.
- Track status.
- Store final response.
- Feed knowledge-gap analysis.

### 6. Feedback Module

Responsibilities:

- Helpful/not helpful rating.
- Optional feedback comment.
- Admin review.
- Quality metrics.

### 7. Audit Module

Responsibilities:

- Log question.
- Log answer.
- Log sources.
- Log confidence status.
- Log user and timestamp.
- Log escalation and feedback.

### 8. Analytics Module

Responsibilities:

- Usage metrics.
- Resolution rate.
- Escalation rate.
- Helpful rate.
- Estimated time saved.
- Top topics.
- Knowledge gaps.

### 9. Integration Module

Initial:

- None required beyond document upload for Alpha.

Beta options:

- Slack or Microsoft Teams.
- Helpdesk ticket creation.
- HRMS read-only metadata.

Future:

- HRMS write actions.
- ERP, CRM, ecommerce connectors.

## Data Boundary

The MVP should start with minimal data:

- User email/name.
- User role.
- Optional department/location if needed for access.
- Approved HR documents.
- Questions and answers.
- Feedback.
- Escalation records.

Avoid in MVP:

- Payroll records.
- Medical data.
- Performance reviews.
- Sensitive employee investigations.
- Legal case records.
- Benefits claims.

## AI Safety Rules

The AI layer must include:

1. Approved-source grounding.
2. Role-aware retrieval.
3. Sensitive topic detection.
4. Low-confidence fallback.
5. Human escalation.
6. Source citation.
7. Audit logging.

## Reuse Requirements

Engineering should design these modules so future capability packs can reuse them:

- Ecommerce order/product assistant.
- ERP procurement assistant.
- CRM account assistant.
- Customer support assistant.
- Internal knowledge assistant.

## Early Technical Decisions To Make

1. Authentication approach.
2. Tenant isolation strategy.
3. Document parsing and indexing approach.
4. Retrieval strategy.
5. AI model provider abstraction.
6. Logging and analytics schema.
7. Admin dashboard scope.
8. Escalation object design.
9. Data retention policy.
10. Integration framework.

## Architecture Risks

| Risk | Product Impact | Mitigation |
|---|---|---|
| One-off HR chatbot architecture | Hard to expand into ecommerce/ERP | Build reusable primitives |
| Weak permission model | Customer trust failure | Permission filter before AI answer |
| Poor source quality | Incorrect answers | Admin source management and knowledge gaps |
| No audit trail | Cannot sell to serious customers | Log every important interaction |
| AI cost untracked | Margin risk | Track cost per question and per resolved request |
| No provider abstraction | Model lock-in | Build model routing layer early |

## Product Architecture Decision

Build the MVP as the first capability pack on a reusable Operational AI platform foundation.

The first capability pack is HR Knowledge & Workflow Assistance.

The reusable foundation is tenant, roles, sources, retrieval, permissioning, escalation, audit, analytics, and connectors.
