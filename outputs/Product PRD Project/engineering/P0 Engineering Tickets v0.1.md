# P0 Engineering Tickets v0.1

## Product

AI HR Knowledge & Workflow Assistant

## Ticket Status

Draft execution tickets. These should be converted into the chosen project management system after technical stack decisions are made.

## Epic A: Workspace and Access

### P0-A1: Create Organization Workspace Model

Goal:

Support isolated customer workspaces.

Requirements:

- Organization has ID, name, created date, status.
- Users belong to an organization.
- Sources, questions, answers, logs, and escalations belong to an organization.

Acceptance criteria:

- Organization record can be created.
- Data queries are scoped by organization.
- No user can access another organization's records through normal app flow.

Dependencies:

- Database selected.

### P0-A2: Implement User Authentication

Goal:

Allow admin and employee users to securely access the product.

Requirements:

- User can log in.
- User belongs to organization.
- User has role.

Acceptance criteria:

- Logged-out user cannot access app pages.
- Logged-in user sees correct workspace.
- User identity appears in logs.

Dependencies:

- Auth provider selected.

### P0-A3: Implement Basic Roles

Goal:

Separate employee and HR admin capabilities.

Roles:

- Owner
- HR Admin
- Employee

Acceptance criteria:

- Employee can ask questions.
- HR Admin can upload sources and review escalations.
- Owner can manage users/settings.
- Admin pages block employee access.

Dependencies:

- Authentication.
- Organization model.

## Epic B: Knowledge Management

### P0-B1: Build Source Upload Flow

Goal:

Allow HR admin to upload approved HR documents.

Supported formats:

- PDF.
- DOCX.
- TXT/Markdown.

Acceptance criteria:

- Admin can upload a file.
- Uploaded file appears in source list.
- Source has status: uploaded, indexing, indexed, failed, inactive.
- Admin can deactivate source.

Dependencies:

- File storage selected.
- Admin role.

### P0-B2: Store Source Metadata

Goal:

Track source ownership and visibility.

Fields:

- Source ID.
- Organization ID.
- Title.
- File type.
- Uploaded by.
- Upload date.
- Status.
- Access scope.

Acceptance criteria:

- Source metadata is stored.
- Source list shows title, type, status, upload date.
- Inactive sources are not used for answers.

Dependencies:

- Source upload.

### P0-B3: Parse and Index Documents

Goal:

Prepare uploaded documents for retrieval.

Requirements:

- Extract text.
- Split into retrievable chunks.
- Store chunk text and source reference.
- Mark indexing status.

Acceptance criteria:

- Uploaded document becomes searchable.
- Failed parsing shows clear status.
- Chunks preserve source reference for citation.

Dependencies:

- Document parsing approach.
- Search/vector infrastructure.

## Epic C: AI Q&A

### P0-C1: Employee Question Interface

Goal:

Let employees ask HR questions.

Acceptance criteria:

- Employee can submit a question.
- Question is associated with user and organization.
- Loading and response states exist.

Dependencies:

- Authentication.
- Employee role.

### P0-C2: Retrieve Relevant Source Chunks

Goal:

Find relevant approved knowledge for the employee's question.

Requirements:

- Retrieval scoped to organization.
- Retrieval excludes inactive sources.
- Retrieval respects source access scope.

Acceptance criteria:

- Search returns relevant chunks from approved documents.
- Employee cannot retrieve HR-admin-only sources.
- Retrieval result includes source reference.

Dependencies:

- Indexed sources.
- Role/access logic.

### P0-C3: Generate Source-Cited Answer

Goal:

Answer employee question from approved sources.

Requirements:

- Use retrieved source chunks.
- Display concise answer.
- Display source citation.
- Avoid unsupported claims.

Acceptance criteria:

- Answer includes source title or reference.
- Unsupported answer returns safe fallback.
- Answer is logged.

Dependencies:

- Retrieval.
- AI provider.

### P0-C4: Implement Unsupported Answer Fallback

Goal:

Prevent hallucinated HR policy answers.

Acceptance criteria:

- If source support is weak, assistant says it cannot confirm from approved sources.
- Assistant suggests contacting HR or creates escalation.
- Unsupported answer is tracked as knowledge gap.

Dependencies:

- Confidence/threshold approach.
- Escalation module.

### P0-C5: Sensitive Topic Detection

Goal:

Route sensitive HR questions safely.

Sensitive categories:

- Termination.
- Harassment.
- Discrimination.
- Payroll dispute.
- Medical leave.
- Legal claim.
- Immigration.
- Employee investigation.
- Personal data request.

Acceptance criteria:

- Sensitive questions do not receive risky autonomous advice.
- Assistant gives safe response.
- Escalation is created or recommended.

Dependencies:

- Escalation module.

## Epic D: Escalation

### P0-D1: Create Escalation Record

Goal:

Capture questions that require HR review.

Fields:

- Escalation ID.
- Organization ID.
- User ID.
- Question.
- Category.
- Status.
- Relevant source references.
- Created date.
- Assigned HR owner.

Acceptance criteria:

- Escalation can be created from unsupported or sensitive question.
- Escalation appears in admin queue.

Dependencies:

- Question flow.

### P0-D2: HR Admin Escalation Queue

Goal:

Allow HR admin to review unresolved questions.

Acceptance criteria:

- Admin can view open escalations.
- Admin can filter by status.
- Admin can mark resolved.
- Admin can add response note.

Dependencies:

- Admin role.
- Escalation record.

## Epic E: Feedback and Audit

### P0-E1: Helpful / Not Helpful Feedback

Goal:

Capture employee answer quality signal.

Acceptance criteria:

- Employee can mark answer helpful or not helpful.
- Optional comment can be stored.
- Feedback appears in admin analytics.

Dependencies:

- Answer record.

### P0-E2: Interaction Log

Goal:

Create an audit trail for trust and debugging.

Log fields:

- User.
- Organization.
- Question.
- Answer.
- Sources used.
- Confidence/fallback status.
- Escalation status.
- Feedback.
- Timestamp.

Acceptance criteria:

- Every question creates log entry.
- Admin can view logs.
- Logs are scoped to organization.

Dependencies:

- Q&A flow.
- Workspace model.

## Epic F: ROI Dashboard

### P0-F1: Usage Metrics

Goal:

Show pilot activity.

Metrics:

- Total questions.
- Active users.
- Top topics.

Acceptance criteria:

- Dashboard shows metrics for selected date range.
- Metrics are scoped to organization.

Dependencies:

- Interaction logs.

### P0-F2: Resolution Metrics

Goal:

Show whether assistant reduces HR work.

Metrics:

- Resolved questions.
- Escalated questions.
- Resolution rate.
- Helpful rate.

Acceptance criteria:

- Dashboard shows resolved vs escalated.
- Dashboard shows helpful percentage where ratings exist.

Dependencies:

- Escalations.
- Feedback.

### P0-F3: Estimated Time Saved

Goal:

Translate usage into business value.

Requirement:

Default estimate: each resolved repetitive HR question saves 5-10 minutes.

Acceptance criteria:

- Admin can configure minutes saved assumption.
- Dashboard shows estimated time saved.
- Metric is labeled as estimate.

Dependencies:

- Resolution metrics.

## Epic G: Pilot Readiness

### P0-G1: Demo Workspace

Goal:

Create sample workspace for demo and testing.

Acceptance criteria:

- Demo workspace contains sample HR documents.
- Demo questions show source-cited answers.
- Demo includes sensitive-topic escalation.

Dependencies:

- Core flow.

### P0-G2: Pilot Setup Checklist

Goal:

Standardize design partner onboarding.

Acceptance criteria:

- Checklist covers documents, users, admin owner, sensitive topics, and success metrics.
- Checklist is usable before customer pilot.

Dependencies:

- Pilot plan.

## Recommended Build Order

1. P0-A1, P0-A2, P0-A3.
2. P0-B1, P0-B2, P0-B3.
3. P0-C1, P0-C2, P0-C3.
4. P0-C4, P0-C5.
5. P0-D1, P0-D2.
6. P0-E1, P0-E2.
7. P0-F1, P0-F2, P0-F3.
8. P0-G1, P0-G2.
