# MVP Build Plan v0.1

## Product

AI HR Knowledge & Workflow Assistant

## Build Objective

Build the smallest trustworthy product that can prove the first Operational AI workflow:

Employee HR Policy Q&A with source citations, safe escalation, and ROI measurement.

The build should not attempt HRMS write actions, payroll, benefits automation, or broad multi-system workflow automation. The first product must prove that customers trust the assistant, employees use it, HR teams save time, and the product can measure value.

## Current Product Decision

First pilot workflow:

HR Policy Q&A + Escalation + ROI.

First integration scope:

Document upload first. HRMS API integration later.

## Build Principles

1. Build trust before automation.
2. Build source-grounded answers before workflow execution.
3. Build escalation before autonomous action.
4. Build ROI measurement before broad feature expansion.
5. Build reusable platform primitives, not a one-off HR chatbot.

## MVP Phases

### Phase 0: Product Setup

Goal: Prepare the engineering foundation.

Build:

- Repository structure.
- App shell.
- Environment configuration.
- Authentication decision.
- Database schema draft.
- AI provider abstraction decision.
- Basic deployment target.

Exit criteria:

- App can run locally.
- Basic workspace/user model is defined.
- Engineering can start building P0 product flows.

### Phase 1: Workspace and Access

Goal: Create secure customer workspace foundation.

Build:

- Organization workspace.
- User login.
- User roles.
- Admin and employee access separation.
- Basic tenant isolation.

Exit criteria:

- Admin can create/access workspace.
- Employee can access employee view.
- Admin-only pages are protected.
- Customer data is scoped by organization.

### Phase 2: Knowledge Management

Goal: Let HR admin upload approved sources.

Build:

- Document upload.
- Source metadata.
- Source status.
- Source access scope.
- Basic document parsing/indexing pipeline.

Exit criteria:

- Admin can upload HR documents.
- System shows indexing status.
- Uploaded source can be used for Q&A.
- Admin can deactivate a source.

### Phase 3: Source-Grounded Q&A

Goal: Employees get trusted answers from approved sources.

Build:

- Employee question interface.
- Retrieval from indexed sources.
- Answer generation.
- Source citation display.
- Unsupported-answer fallback.
- Sensitive-topic safety handling.

Exit criteria:

- Employee can ask HR policy questions.
- Assistant returns cited answers.
- Assistant refuses unsupported policy claims.
- Sensitive questions escalate or redirect to HR.

### Phase 4: Escalation, Feedback, and Audit

Goal: Make the assistant controllable and reviewable.

Build:

- Escalation creation.
- HR admin escalation queue.
- Helpful/not helpful feedback.
- Interaction log.
- Admin filtering by date/status.

Exit criteria:

- Unanswered or sensitive questions create escalations.
- HR admin can review and resolve escalations.
- Employee feedback is captured.
- Interaction history is available for audit and improvement.

### Phase 5: ROI Dashboard

Goal: Prove customer value.

Build:

- Usage metrics.
- Resolution metrics.
- Escalation metrics.
- Helpful rating.
- Estimated time saved.
- Top topics.
- Knowledge gaps.

Exit criteria:

- HR leader can see questions, resolved, escalated, and time saved.
- Metrics can support 30-day pilot review.
- Time saved is clearly labeled as an estimate.

### Phase 6: Design Partner Pilot Readiness

Goal: Prepare the product for controlled external use.

Build:

- Pilot setup checklist.
- Demo workspace.
- Sample HR document pack.
- Security/data-boundary notes.
- Admin onboarding guide.
- Weekly pilot reporting template.

Exit criteria:

- Product is usable by 20-100 pilot employees.
- HR admin can operate the product without engineering support for every question.
- Product captures enough data for pilot ROI review.

## Recommended Sprint Plan

### Sprint 0: Technical Foundation

Duration: 3-5 days.

Output:

- App shell.
- Auth plan.
- Database schema.
- Basic deployment target.
- AI/retrieval approach selected.

### Sprint 1: Workspace, Roles, and Admin Shell

Duration: 1 week.

Output:

- Workspace model.
- Admin/employee role separation.
- Admin navigation.
- Employee question page shell.

### Sprint 2: Knowledge Upload and Indexing

Duration: 1 week.

Output:

- Upload flow.
- Source list.
- Source metadata.
- Basic parsing and indexing.

### Sprint 3: Q&A Engine

Duration: 1-2 weeks.

Output:

- Employee asks question.
- Assistant retrieves relevant source.
- Answer includes citation.
- Unsupported answer fallback.

### Sprint 4: Escalation, Feedback, Audit

Duration: 1 week.

Output:

- Escalation queue.
- Feedback controls.
- Interaction logs.
- Sensitive-topic rules.

### Sprint 5: ROI Dashboard and Pilot Polish

Duration: 1 week.

Output:

- Dashboard.
- Pilot metrics.
- Knowledge gaps.
- Pilot setup checklist.
- Demo content.

## Definition of Done

The MVP is build-complete for first pilot when:

1. Admin can create or access workspace.
2. Admin can upload HR documents.
3. Employee can ask HR questions.
4. Assistant answers with source citations.
5. Assistant refuses unsupported answers.
6. Sensitive questions escalate.
7. HR admin can review escalations.
8. Employee can rate answers.
9. Interaction log is available.
10. ROI dashboard shows core pilot metrics.
11. Data is tenant-scoped.
12. No high-severity access-control issue is known.

## Technical Decisions Still Open

1. Web framework.
2. Database.
3. Authentication provider.
4. Document parsing approach.
5. Vector/search infrastructure.
6. AI model provider.
7. Hosting/deployment environment.
8. File storage.
9. Logging/analytics approach.
10. Slack/Teams integration timing.

## Founder Decision

Move forward with the P0 MVP build path:

Workspace -> Knowledge Upload -> Source-Grounded Q&A -> Escalation -> Feedback/Audit -> ROI Dashboard -> Pilot.
