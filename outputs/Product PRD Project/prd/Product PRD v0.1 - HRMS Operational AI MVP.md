# Product PRD v0.1 - HRMS Operational AI MVP

## 1. Document Control

| Field | Value |
|---|---|
| Product Name | AI HR Knowledge & Workflow Assistant |
| Company Category | Operational AI / AI Business Capability Platform |
| Document Type | Founder PRD |
| Version | 0.1 |
| Status | Draft for founder review and customer validation |
| Primary Market | HRMS / HR operations |
| Initial Customer Segment | SMB and mid-market companies |
| First Business Goal | Prove that Operational AI can reduce repetitive HR work and create measurable ROI |

## 2. Executive Summary

The AI HR Knowledge & Workflow Assistant is the first product wedge for the company's Operational AI platform. The product helps employees find trusted HR answers, complete simple HR-related workflows, and reduce repetitive HR support work without requiring the customer to understand prompts, agents, embeddings, or AI configuration.

The MVP should solve one expensive problem extremely well: HR teams repeatedly answer the same employee questions across policies, onboarding, benefits, leave, documents, and internal processes. Employees often search multiple documents, message HR manually, or wait for answers. HR teams lose time on repetitive support instead of strategic people operations.

The product will start with source-grounded HR knowledge assistance and controlled workflow routing. It will answer employee questions using approved company knowledge, cite the source, respect user roles, escalate uncertain or sensitive issues, and measure business value through resolved requests, time saved, ticket deflection, and user satisfaction.

This MVP is intentionally narrow. It is not the full AI Business Capability Platform. It is the first proof that the platform can connect business knowledge, permissions, workflows, and ROI measurement into a useful operational AI product.

## 3. Product Vision

Build the first trusted AI operating layer for HR operations, starting with employee knowledge and workflow assistance.

The product should feel like an HR operations assistant that understands company policy, knows what each employee is allowed to access, explains answers with sources, and helps complete routine HR work.

Long term, the same platform foundation should expand from HR into ecommerce, ERP, CRM, support, finance, manufacturing, and other operational systems.

## 4. Problem Statement

Companies have HR knowledge scattered across policy PDFs, employee handbooks, onboarding documents, shared drives, HRMS records, spreadsheets, helpdesk tickets, email threads, and chat messages.

Employees struggle to find the right answer quickly. HR teams answer the same questions repeatedly. Existing AI chatbots can answer basic questions, but many fail to handle permissioning, source attribution, workflow escalation, auditability, and measurable ROI.

The problem is not only information search. The deeper problem is operational friction:

- Employees need trusted answers.
- HR needs fewer repeated questions.
- Managers need process clarity.
- Companies need secure access controls.
- Leadership needs measurable productivity impact.

## 5. Product Thesis

If we provide a secure, source-grounded, role-aware HR assistant that resolves repeated employee questions and routes unresolved workflows, then SMB and mid-market companies will adopt the product because it reduces HR workload, improves employee experience, and creates measurable ROI within the first month.

## 6. Goals

### Business Goals

1. Validate HRMS as the first beachhead for Operational AI.
2. Convert 3-5 design partners into active pilot customers.
3. Demonstrate measurable ROI within 30 days of pilot launch.
4. Create reusable platform primitives for later ecommerce, ERP, and CRM expansion.
5. Produce at least one customer case study showing time saved or ticket reduction.

### User Goals

1. Employees can get trusted HR answers quickly.
2. HR teams can reduce repetitive manual responses.
3. Admins can control approved knowledge sources.
4. Managers can understand common employee questions and process bottlenecks.
5. Leadership can see ROI through usage and resolution metrics.

### Product Goals

1. Source-cited answers.
2. Role-aware access.
3. Escalation to human HR owner.
4. Knowledge ingestion and management.
5. Basic HR workflow routing.
6. ROI dashboard.
7. Audit log for sensitive interactions.

## 7. Non-Goals

The MVP will not:

1. Replace the customer's HRMS.
2. Automatically approve leave, payroll, benefits, or compliance-sensitive decisions.
3. Handle payroll calculations.
4. Provide legal, medical, tax, or immigration advice.
5. Support every HRMS platform on day one.
6. Build a full no-code agent builder.
7. Support fully autonomous workflow execution without human approval.
8. Support complex enterprise procurement and compliance workflows beyond basic security documentation.
9. Become a generic company chatbot without HR focus.

## 8. Target Customer

### Initial ICP

| Attribute | Definition |
|---|---|
| Company size | 50-1,000 employees |
| Company type | SMB or mid-market company with recurring HR support needs |
| Existing systems | HRMS, policy documents, employee handbook, shared drive, chat, helpdesk, email |
| Pain intensity | HR team repeatedly answers the same questions |
| Buying trigger | Growth, remote/hybrid workforce, onboarding burden, HR team overload, policy complexity |
| Buyer | HR leader, operations leader, founder/CEO, or IT-supported HR team |
| Success metric | Reduced HR tickets, faster answers, fewer repeated questions, time saved |

### Early Exclusions

- Highly regulated enterprise HR with heavy compliance review.
- Companies without organized HR documentation.
- Companies expecting payroll automation in MVP.
- Companies requiring deep HRMS write-back before proving knowledge workflows.

## 9. User Personas

### Employee

Needs fast, clear answers about policies, leave, onboarding, benefits, documents, and HR processes.

Primary jobs:

- Ask HR questions.
- Find the right policy.
- Understand next steps.
- Know when to contact HR.

Success looks like:

- Gets a trusted answer in under one minute.
- Sees the source behind the answer.
- Knows whether the answer applies to their role/location.

### HR Admin

Owns HR content, employee support quality, and internal process accuracy.

Primary jobs:

- Upload and manage approved HR knowledge.
- Review unanswered or escalated questions.
- Correct inaccurate answers.
- Track repeated employee issues.
- Measure HR support reduction.

Success looks like:

- Repetitive employee questions decrease.
- HR can control what the assistant knows.
- Escalations are visible and manageable.

### HR Leader

Owns HR operations, productivity, employee experience, and policy consistency.

Primary jobs:

- Reduce HR workload.
- Improve employee self-service.
- Identify knowledge gaps.
- Prove ROI.

Success looks like:

- Fewer repetitive tickets.
- Faster onboarding.
- Better employee satisfaction.
- Clear monthly ROI report.

### IT / Security Owner

Owns access, privacy, compliance, and system safety.

Primary jobs:

- Review data boundaries.
- Approve integrations.
- Confirm user access control.
- Audit system activity.

Success looks like:

- Clear role-based permissions.
- Audit logs available.
- Sensitive actions require approval.
- Data is not exposed across users or customers.

### Founder / CEO

Owns business productivity, cost control, and company operating discipline.

Primary jobs:

- Reduce manual work.
- Improve employee experience.
- Validate business value.
- Decide whether to expand usage.

Success looks like:

- Clear ROI within 30 days.
- Product is simple to deploy.
- HR team saves time.

## 10. Core Use Cases

### Use Case 1: HR Policy Question Answering

Employee asks: "How many paid leaves do I have?" or "What is the remote work policy?"

The assistant:

1. Understands the question.
2. Searches approved HR knowledge.
3. Applies role/location constraints where available.
4. Gives a concise answer.
5. Shows source citation.
6. Escalates if confidence is low or the answer is sensitive.

### Use Case 2: Employee Handbook Search

Employee asks a general question and receives an answer grounded in the handbook or policy documents.

The assistant should not invent policy. If the answer is missing, it should say so and route the question to HR.

### Use Case 3: Onboarding Support

New employee asks about first-week tasks, required documents, tool access, reporting manager, or company policies.

The assistant provides relevant onboarding guidance and points to source documents.

### Use Case 4: HR Ticket Deflection

Employee asks a repeated question that would normally become an HR ticket.

The assistant resolves the question and logs the resolution as a deflected request.

### Use Case 5: Escalation to HR

If the assistant cannot answer safely, it creates an escalation record with:

- Employee question.
- Suggested category.
- Relevant sources found.
- Confidence reason.
- Recommended HR owner.

### Use Case 6: Admin Knowledge Management

HR admin uploads, reviews, removes, or replaces approved knowledge sources.

The system tracks:

- Source title.
- Upload date.
- Owner.
- Access scope.
- Version.
- Status.

### Use Case 7: ROI Reporting

HR leader sees dashboard:

- Questions asked.
- Questions resolved.
- Escalations.
- Estimated time saved.
- Top repeated topics.
- Unanswered questions.
- User satisfaction.

## 11. MVP Scope

### Must Have

| Capability | Description |
|---|---|
| Organization workspace | Each customer has isolated workspace and knowledge base |
| User roles | Employee, HR admin, manager, owner/admin |
| Knowledge upload | Upload approved HR documents and FAQs |
| Knowledge indexing | System prepares sources for AI search |
| Source-grounded Q&A | Answers must cite approved source material |
| Confidence handling | Low-confidence answers should escalate or ask for clarification |
| Basic access control | Users only access knowledge permitted to their role/group |
| Escalation queue | HR admin can see unanswered or sensitive requests |
| Feedback controls | Users can mark answer helpful/not helpful |
| Audit log | Track user question, answer, source, confidence, and escalation |
| ROI dashboard | Show resolution, escalation, time saved, and top topics |

### Should Have

| Capability | Description |
|---|---|
| Slack or Microsoft Teams interface | Employees can ask questions where they already work |
| HRMS read integration | Pull basic employee role/location/department where approved |
| Helpdesk integration | Create ticket when escalation is needed |
| Suggested knowledge gaps | Identify missing or weak HR content |
| Admin answer review | HR admin can approve/correct common answers |
| Multilingual answers | Support English first, then selected regional languages |

### Could Have

| Capability | Description |
|---|---|
| Workflow templates | Onboarding, leave request guidance, document request routing |
| Manager-specific answers | Manager policy guidance where access allows |
| Analytics export | CSV export for HR reporting |
| Browser widget | Embedded assistant for intranet or HR portal |

### Future

| Capability | Description |
|---|---|
| HRMS write actions | Submit leave request, update records, trigger HR workflows |
| Advanced workflow automation | Multi-step HR process orchestration |
| Policy change detection | Detect contradictions or changes in policy documents |
| Capability marketplace | Third-party HR workflow packs |
| Cross-system employee operations | HRMS + IT + finance + helpdesk workflows |

## 12. Functional Requirements

### FR1: Organization Workspace

The product must support isolated customer workspaces.

Acceptance criteria:

- Each organization has separate users, knowledge, settings, and logs.
- A user from one organization cannot access another organization's data.
- Admin can invite users to the organization.

### FR2: User Authentication and Roles

The product must support role-based access.

Initial roles:

- Owner
- HR Admin
- Manager
- Employee

Acceptance criteria:

- Admin can assign user role.
- Role affects knowledge access and admin features.
- Unauthorized users cannot access admin pages.

### FR3: Knowledge Source Management

HR admins must be able to add approved HR knowledge.

Supported initial sources:

- PDF
- DOCX
- TXT/Markdown
- FAQ entries
- Web page or intranet URL, if simple and accessible

Acceptance criteria:

- Admin can upload a document.
- Admin can name the source and assign owner/status.
- Admin can remove or deactivate a source.
- System shows indexing status.

### FR4: Source-Grounded Answering

The assistant must answer based on approved sources.

Acceptance criteria:

- Answer includes source title or citation.
- If the answer is not found, assistant says it cannot confirm.
- Assistant does not invent policy.
- Answer includes "contact HR" path when needed.

### FR5: Clarification and Confidence Handling

The assistant must ask clarifying questions or escalate when it is uncertain.

Acceptance criteria:

- Low-confidence response does not pretend certainty.
- Sensitive categories trigger escalation.
- Admin can see unresolved questions.

### FR6: Escalation Queue

HR admins need a queue for unresolved or sensitive questions.

Acceptance criteria:

- Escalation includes original question, user, timestamp, category, and relevant sources.
- Admin can mark escalation as resolved.
- Admin can add a final answer or instruction.

### FR7: Feedback

Users must be able to rate answer helpfulness.

Acceptance criteria:

- User can mark helpful or not helpful.
- Optional comment can be captured.
- Feedback appears in admin analytics.

### FR8: Audit Log

The product must preserve a basic activity trail.

Acceptance criteria:

- Log records question, answer, source, user, timestamp, confidence status, and escalation status.
- Admin can filter logs by date and status.
- Sensitive logs are visible only to authorized admin users.

### FR9: ROI Dashboard

HR leader needs basic value reporting.

Acceptance criteria:

- Dashboard shows total questions.
- Dashboard shows resolved questions.
- Dashboard shows escalations.
- Dashboard shows estimated time saved.
- Dashboard shows top categories.
- Dashboard shows answer helpfulness.

### FR10: Admin Settings

Admins must control assistant behavior.

Acceptance criteria:

- Admin can set escalation categories.
- Admin can configure estimated minutes saved per resolved question.
- Admin can set allowed knowledge sources.
- Admin can disable assistant access if needed.

## 13. AI Behavior Requirements

The assistant must:

1. Answer only from approved customer knowledge or clearly state when it is using general non-policy reasoning.
2. Cite source material for HR policy answers.
3. Avoid legal, medical, payroll, immigration, or tax advice.
4. Escalate sensitive or uncertain questions.
5. Respect role-based access.
6. Avoid exposing hidden source content to unauthorized users.
7. Use clear, concise, employee-friendly language.
8. Ask clarifying questions when the user's question is ambiguous.
9. Explain when HR confirmation is required.
10. Log enough information for audit and improvement.

## 14. Sensitive Topics

The assistant should escalate or provide safe guidance for:

- Termination
- Harassment
- Discrimination
- Medical leave
- Payroll disputes
- Legal claims
- Immigration
- Employee investigations
- Sensitive performance matters
- Personal data access requests

## 15. Data and Security Requirements

### MVP Security Baseline

| Requirement | MVP Expectation |
|---|---|
| Tenant isolation | Required |
| Role-based access | Required |
| Audit logs | Required |
| Encryption in transit | Required |
| Encryption at rest | Required where platform supports it |
| Source access controls | Required |
| Admin-only knowledge management | Required |
| Human escalation | Required |
| Data deletion | Admin can request/delete workspace data |
| Model provider abstraction | Preferred, to avoid lock-in |

### Data Boundary

The MVP should ingest only approved HR knowledge and minimal user metadata needed for access control.

Do not ingest payroll, medical, performance-review, or highly sensitive employee records in the first MVP unless a customer explicitly approves and security controls are reviewed.

## 16. Integrations

### MVP Integration Priority

1. Document upload and internal knowledge sources.
2. Slack or Microsoft Teams interface.
3. Helpdesk ticket creation for escalation.
4. HRMS read-only integration for department, role, location, and employment type.
5. HRMS write actions later.

### First HRMS Candidates

Final platform choice remains open, but early candidates include:

- OrangeHRM
- Zoho People
- BambooHR
- Keka
- greytHR
- Darwinbox

Selection criteria:

- API availability.
- Customer access.
- SMB/mid-market usage.
- Integration simplicity.
- Partner access.
- Data permission model.

## 17. Analytics and ROI

### Core Metrics

| Metric | Definition |
|---|---|
| Questions asked | Total employee questions |
| Resolution rate | Percentage answered without HR escalation |
| Escalation rate | Percentage requiring HR follow-up |
| Helpful rate | Percentage rated helpful |
| Estimated time saved | Resolved questions x assumed minutes saved |
| Top topics | Most common employee question categories |
| Knowledge gaps | Questions not answerable from approved sources |
| Active users | Employees using assistant in period |
| Repeat users | Users returning after first answer |

### ROI Assumption

Initial default:

One resolved repetitive HR question saves 5-10 minutes of HR/admin time.

This is an assumption and must be validated in pilots.

## 18. Success Metrics

### MVP Success Criteria

The MVP is successful if, within 30-60 days of pilot launch:

1. At least 50% of employee questions are resolved without HR escalation.
2. At least 70% of rated answers are marked helpful.
3. HR admin confirms measurable time saved.
4. No high-severity privacy or access-control issue occurs.
5. At least one pilot customer agrees to continue as paid subscription.
6. The product identifies clear knowledge gaps that HR wants to fix.

### Business Success Criteria

1. 3-5 design partners agree to test the MVP.
2. 5-10 paid pilots are launched.
3. 50% or more of pilots convert to paid subscription.
4. First case study shows time saved or ticket reduction.
5. First integration pattern is reusable.

## 19. User Experience Principles

1. Employee experience should feel simple and safe.
2. HR admin experience should feel controlled and auditable.
3. Answers should be concise, sourced, and actionable.
4. The assistant should never pretend certainty.
5. Escalation should feel like a feature, not a failure.
6. Admin analytics should focus on decisions, not vanity charts.
7. The product should fit existing workflows before asking users to change behavior.

## 20. MVP User Journeys

### Employee Journey

1. Employee opens assistant.
2. Employee asks HR question.
3. Assistant checks approved knowledge and permissions.
4. Assistant answers with source citation.
5. Employee marks helpful or asks follow-up.
6. If unresolved, assistant escalates to HR.

### HR Admin Journey

1. HR admin creates workspace.
2. HR admin uploads policy documents.
3. System indexes sources.
4. HR admin invites users.
5. HR admin reviews escalations and feedback.
6. HR admin checks ROI dashboard.
7. HR admin updates knowledge gaps.

### HR Leader Journey

1. HR leader reviews dashboard.
2. HR leader identifies top repeated topics.
3. HR leader sees estimated time saved.
4. HR leader decides whether to expand usage.

## 21. Release Plan

### Alpha

Goal: Internal demo and founder validation.

Scope:

- Workspace
- Document upload
- Basic Q&A
- Source citation
- Admin view
- Manual test users

### Private Beta

Goal: 3-5 design partners.

Scope:

- Roles
- Escalation queue
- Feedback
- Audit log
- Basic ROI dashboard
- Security baseline

### Paid Pilot

Goal: Convert pilot customers into subscriptions.

Scope:

- Improved analytics
- Knowledge gap reporting
- Helpdesk or Slack/Teams integration
- Customer-specific onboarding checklist
- ROI report export

## 22. Dependencies

1. Customer access to HR documents.
2. Approved pilot company and HR owner.
3. Clear data boundary.
4. AI model provider.
5. Vector/search infrastructure.
6. Authentication and tenant isolation.
7. Admin dashboard.
8. Security review checklist.

## 23. Risks

| Risk | Impact | Mitigation |
|---|---|---|
| Wrong first use case | Low adoption | Validate with HR interviews before build completion |
| Low answer accuracy | Loss of trust | Source citation, confidence thresholds, admin feedback |
| Privacy concern | Sales blocker | RBAC, audit logs, data boundaries |
| Too much custom setup | Services trap | Standard onboarding and source templates |
| Incumbent HRMS AI | Competitive pressure | Cross-system positioning and faster implementation |
| AI cost growth | Margin risk | Usage controls and model routing |
| Weak documentation quality | Poor answers | Knowledge gap reports and admin source cleanup |

## 24. Open Questions

1. Which HR workflow should be the primary MVP wedge: policy Q&A, onboarding, or ticket deflection?
2. Which first interface should we prioritize: web app, Slack, Microsoft Teams, or HRMS widget?
3. Should pilots start with document upload only, or include HRMS read integration?
4. Which HRMS platform should be first: OrangeHRM, Zoho People, BambooHR, Keka, greytHR, or Darwinbox?
5. What is the correct pilot price?
6. What security promises are required for first customers?
7. What answer accuracy threshold is acceptable for HR pilot launch?
8. What data should never enter the MVP?

## 25. Out-of-Scope Until Later

1. Payroll automation.
2. Benefits enrollment automation.
3. Legal advice.
4. Medical leave decisioning.
5. Performance review automation.
6. Fully autonomous HR actions.
7. Deep enterprise SSO and compliance certification.
8. Multi-country labor law interpretation.
9. Marketplace for third-party capabilities.
10. General AI agent builder.

## 26. Final Founder Decision

Proceed with PRD v0.1 as the first product definition for the HRMS Operational AI MVP.

The MVP should be positioned as:

AI HR Knowledge & Workflow Assistant: a secure, source-grounded, role-aware assistant that reduces repetitive HR work and proves operational ROI.

This product should become the first practical proof of the broader AI Business Capability Platform.
