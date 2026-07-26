# PRD Source Alignment

## Purpose

This file explains how Product PRD v0.1 maps to the existing startup strategy documents.

## Source Documents Used

| Source | PRD Impact |
|---|---|
| Founder Discovery Framework | Validation-first approach, customer interview gates, design partner requirement |
| Market Gap Discovery | Business-first positioning, workflow execution, role-aware AI, integration gap |
| Company Blueprint | Product principles, moat, MVP principles, product maturity roadmap, success metrics |
| Business Strategy Book Chapter 1 | HRMS beachhead, target customers, operational AI positioning |
| BSB Chapter 2 and chapter drafts | Operational AI category, HRMS as first wedge, platform expansion logic |

## Key Decisions Carried Into PRD

### From Company Blueprint

Decision: We are not competing to build the smartest AI. We are building the most useful AI platform for business operations.

PRD translation:

- Product is not a generic chatbot.
- MVP focuses on HR operational outcomes.
- Metrics include resolution, time saved, escalation, and ROI.

### From Product Principles

Blueprint principle: Business-first, AI-second.

PRD translation:

- Customers buy HR Knowledge Assistant and workflow support, not prompts or embeddings.

Blueprint principle: Security by design.

PRD translation:

- Role-based access, audit logs, tenant isolation, sensitive topic escalation.

Blueprint principle: Every capability must produce measurable ROI.

PRD translation:

- ROI dashboard is must-have in MVP.

Blueprint principle: Human approval for sensitive actions.

PRD translation:

- Sensitive HR topics escalate to human HR owner.
- No autonomous HRMS write actions in MVP.

Blueprint principle: Integrate before replacing.

PRD translation:

- Product supports existing HR documents, HRMS, helpdesk, Slack/Teams.
- It does not replace HRMS.

### From Chapter 1

Decision: First production implementation focuses on HRMS.

PRD translation:

- MVP is HRMS Operational AI.
- Initial use cases are employee policy Q&A, HR knowledge search, onboarding support, and ticket deflection.

### From Market Gap Discovery

Finding: Businesses do not want another chatbot. They want AI that understands business context, integrates with business software, executes workflows, and measures ROI.

PRD translation:

- Assistant must cite sources.
- Assistant must route workflows.
- Assistant must measure resolved questions and time saved.
- Assistant must be role-aware.

## MVP Scope Rationale

### Why HR Knowledge First

HR knowledge assistance is the fastest path because:

- Repetitive pain is easy to understand.
- Required data can start with documents and FAQs.
- Risk is lower than payroll or ERP actions.
- ROI can be estimated quickly.
- It creates reusable platform primitives.

### Why Workflow Routing Second

Workflow routing proves Operational AI without jumping to high-risk autonomous execution.

The assistant can:

- Identify request type.
- Gather context.
- Route to HR.
- Track status.
- Learn knowledge gaps.

This is safer than immediate HRMS write actions.

### Why ROI Dashboard Is MVP

Without ROI measurement, the product becomes another AI assistant. ROI dashboard is needed because the company's strategy is based on measurable operational value.

## Validation Dependencies

PRD v0.1 assumes:

1. HR teams have repeated employee questions.
2. HR leaders will pay to reduce repetitive work.
3. Source citations increase trust.
4. Escalation makes AI acceptable for sensitive HR topics.
5. Customers can provide enough approved HR content to start.
6. A 30-day pilot can show measurable value.

These assumptions must be tested before PRD v0.2.

## Future PRD Versions

### PRD v0.2

Should incorporate:

- Customer interview findings.
- First design partner requirements.
- Final MVP wedge selection.
- First integration decision.
- Pilot pricing recommendation.

### PRD v0.3

Should incorporate:

- Engineering feasibility review.
- UX flows.
- Data model.
- Security review.
- Pilot launch checklist.

### PRD v1.0

Should be engineering-locked and pilot-ready.
