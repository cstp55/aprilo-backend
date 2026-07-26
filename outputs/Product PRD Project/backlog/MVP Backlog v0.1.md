# MVP Backlog v0.1

## Product

AI HR Knowledge & Workflow Assistant

## Backlog Philosophy

The first build should prove operational value, not product breadth. Every feature should support one of four outcomes:

1. Employees get trusted HR answers.
2. HR teams reduce repetitive work.
3. Admins control knowledge and permissions.
4. Leadership sees measurable ROI.

## Epic 1: Organization Workspace

### User Story 1.1: Create Organization Workspace

As a product owner, I want each customer to have a separate workspace so customer data is isolated.

Acceptance criteria:

- Workspace has organization name.
- Workspace has unique ID.
- Users, sources, logs, and settings belong to one workspace.
- No cross-workspace access is possible through normal product flows.

Priority: Must have.

### User Story 1.2: Invite Users

As an admin, I want to invite users so employees can access the assistant.

Acceptance criteria:

- Admin can invite user by email.
- Admin can assign role.
- Invited user belongs to the correct workspace.

Priority: Must have.

## Epic 2: Roles and Permissions

### User Story 2.1: Basic Roles

As an admin, I want roles so different users have appropriate access.

Roles:

- Owner
- HR Admin
- Manager
- Employee

Acceptance criteria:

- Employee can ask questions.
- HR Admin can manage sources and escalations.
- Owner can manage settings and users.
- Unauthorized users cannot access admin screens.

Priority: Must have.

### User Story 2.2: Source Access Scope

As an HR admin, I want to control who can access each knowledge source.

Acceptance criteria:

- Source can be marked all employees, managers only, HR only, or custom group.
- Assistant only uses sources available to the asking user.

Priority: Must have.

## Epic 3: Knowledge Management

### User Story 3.1: Upload HR Documents

As an HR admin, I want to upload policy documents so the assistant can answer from approved knowledge.

Acceptance criteria:

- Supports PDF, DOCX, TXT/Markdown.
- Shows upload status.
- Shows indexing status.
- Admin can deactivate a source.

Priority: Must have.

### User Story 3.2: Add FAQ Entry

As an HR admin, I want to add direct FAQ entries for common questions.

Acceptance criteria:

- Admin can add question, answer, category, and access scope.
- FAQ entry can be edited or deactivated.
- Assistant can cite FAQ entry as source.

Priority: Should have.

### User Story 3.3: Knowledge Gap List

As an HR admin, I want to see questions the assistant could not answer.

Acceptance criteria:

- Unanswered questions appear in a list.
- Admin can filter by category and date.
- Admin can use gaps to add new source material.

Priority: Should have.

## Epic 4: AI Answering

### User Story 4.1: Ask HR Question

As an employee, I want to ask an HR question and get a trusted answer.

Acceptance criteria:

- User can type a natural-language question.
- Assistant returns answer from approved sources.
- Answer includes source citation.
- If answer is missing, assistant says it cannot confirm.

Priority: Must have.

### User Story 4.2: Follow-Up Question

As an employee, I want to ask a follow-up question.

Acceptance criteria:

- Assistant preserves context for the current conversation.
- Assistant still follows source and permission rules.

Priority: Should have.

### User Story 4.3: Low Confidence Handling

As a company, I want the assistant to avoid unsafe guesses.

Acceptance criteria:

- Low-confidence answer asks for clarification or escalates.
- Assistant does not invent HR policy.
- Sensitive categories trigger safe response.

Priority: Must have.

## Epic 5: Escalation

### User Story 5.1: Escalate Unresolved Question

As an employee, I want unresolved questions to reach HR.

Acceptance criteria:

- User can request escalation.
- Assistant can recommend escalation automatically.
- Escalation includes question, user, timestamp, and category.

Priority: Must have.

### User Story 5.2: HR Escalation Queue

As an HR admin, I want to review escalations.

Acceptance criteria:

- Queue shows open, in-progress, and resolved escalations.
- Admin can mark resolved.
- Admin can add response note.

Priority: Must have.

## Epic 6: Feedback and Improvement

### User Story 6.1: Rate Answer

As an employee, I want to rate whether the answer helped.

Acceptance criteria:

- User can mark helpful or not helpful.
- Optional comment is captured.
- Admin can view feedback.

Priority: Must have.

### User Story 6.2: Admin Correction

As an HR admin, I want to correct common answer issues.

Acceptance criteria:

- Admin can review low-rated answers.
- Admin can add improved FAQ or source note.

Priority: Should have.

## Epic 7: Audit and Trust

### User Story 7.1: Interaction Log

As an HR admin, I want a log of assistant activity.

Acceptance criteria:

- Log includes user, question, answer, sources, timestamp, status, and feedback.
- Admin can filter logs.

Priority: Must have.

### User Story 7.2: Sensitive Topic Detection

As a company, I want sensitive HR questions to be treated carefully.

Acceptance criteria:

- Termination, harassment, discrimination, payroll dispute, medical, legal, immigration, and investigation topics trigger safety handling.
- Assistant recommends contacting HR for sensitive cases.

Priority: Must have.

## Epic 8: ROI Dashboard

### User Story 8.1: Usage Metrics

As an HR leader, I want to see assistant usage.

Acceptance criteria:

- Dashboard shows total questions.
- Dashboard shows active users.
- Dashboard shows top topics.

Priority: Must have.

### User Story 8.2: Resolution Metrics

As an HR leader, I want to know whether the assistant reduced work.

Acceptance criteria:

- Dashboard shows resolved questions.
- Dashboard shows escalations.
- Dashboard shows resolution rate.

Priority: Must have.

### User Story 8.3: Time Saved Estimate

As an HR leader, I want estimated time saved.

Acceptance criteria:

- Admin can configure assumed minutes saved per resolved question.
- Dashboard calculates estimated time saved.
- Estimate is labeled as estimate.

Priority: Must have.

## Epic 9: Integrations

### User Story 9.1: Slack or Teams Interface

As an employee, I want to ask questions inside my work chat.

Acceptance criteria:

- Employee can ask question from chat.
- Answer follows the same source and permission rules.
- Interaction appears in audit log.

Priority: Should have.

### User Story 9.2: Helpdesk Ticket Creation

As an HR admin, I want escalations to create tickets.

Acceptance criteria:

- Escalation can create a ticket in selected helpdesk.
- Ticket includes question and context.

Priority: Could have.

### User Story 9.3: HRMS Read Integration

As an admin, I want the assistant to know basic role/location/department where approved.

Acceptance criteria:

- Read-only integration pulls approved metadata.
- Metadata only affects permissioning and answer relevance.
- No payroll or sensitive record sync in MVP.

Priority: Could have for first pilot, should have for later beta.

## Epic 10: Admin Settings

### User Story 10.1: Escalation Settings

As an HR admin, I want to configure escalation behavior.

Acceptance criteria:

- Admin can choose default HR owner.
- Admin can mark categories as always escalate.

Priority: Should have.

### User Story 10.2: Assistant Availability

As an owner, I want to enable or disable assistant access.

Acceptance criteria:

- Owner can pause assistant for organization.
- Paused state prevents new employee questions.

Priority: Could have.

## Build Sequence Recommendation

1. Workspace and user roles.
2. Knowledge upload and source indexing.
3. Source-grounded Q&A.
4. Escalation queue.
5. Feedback and audit log.
6. ROI dashboard.
7. Slack/Teams or helpdesk integration.
8. HRMS read integration.

## MVP Cut Line

If time is limited, ship:

- Workspace.
- Roles.
- Document upload.
- Source-cited Q&A.
- Escalation queue.
- Feedback.
- Basic ROI dashboard.

Delay:

- HRMS API integration.
- Helpdesk integration.
- Slack/Teams.
- Advanced workflows.
- Multilingual support.
