# Frontend Route and Screen Plan v0.1

## Product

AI HR Knowledge & Workflow Assistant

## Purpose

Define the first Next.js route and screen structure for the MVP.

## Frontend Principle

The product should open directly into the working experience. No marketing-style landing page is needed for the MVP.

The first screen after login should help the user do their job:

- Employees ask HR questions.
- HR admins manage sources, escalations, and metrics.

## Routes

| Route | User | Purpose | Priority |
|---|---|---|---|
| `/login` | All | Sign in | P0 |
| `/app` | All | Role-aware redirect | P0 |
| `/app/ask` | Employee/Admin | Ask HR question | P0 |
| `/app/admin` | HR Admin/Owner | Admin overview | P0 |
| `/app/admin/sources` | HR Admin/Owner | Upload and manage sources | P0 |
| `/app/admin/escalations` | HR Admin/Owner | Review unresolved/sensitive questions | P0 |
| `/app/admin/dashboard` | HR Admin/Owner | ROI and usage metrics | P0 |
| `/app/admin/settings` | HR Admin/Owner | Assistant settings and assumptions | P1 |

## Screen Requirements

### Login Screen

Purpose:

Let user sign in.

Required elements:

- Email field.
- Password field.
- Sign in button.
- Error state.

### Employee Ask Screen

Purpose:

Let employees ask HR questions and receive source-cited answers.

Required elements:

- Question input.
- Submit button.
- Answer area.
- Source citation display.
- Fallback state.
- Escalation state.
- Helpful/not helpful buttons.
- Recent questions, optional.

Design notes:

- Keep the interface calm and work-focused.
- Do not make the product feel like a playful chatbot.
- Source citation must be visible.
- Escalation must feel normal, not like a failure.

### Admin Overview

Purpose:

Give HR admin quick status.

Required elements:

- Sources indexed.
- Open escalations.
- Questions this week.
- Resolution rate.
- Link to upload sources.
- Link to review escalations.

### Sources Screen

Purpose:

Manage approved HR knowledge.

Required elements:

- Upload source button.
- Source list table.
- Source title.
- Type.
- Status.
- Access scope.
- Uploaded date.
- Actions: deactivate/edit.

### Escalations Screen

Purpose:

Review unresolved or sensitive employee questions.

Required elements:

- Escalation list.
- Status filter.
- Category filter.
- Question text.
- Created date.
- Assigned owner.
- Resolve action.
- Resolution note field.

### Dashboard Screen

Purpose:

Show pilot ROI and product value.

Required elements:

- Total questions.
- Resolved questions.
- Escalated questions.
- Resolution rate.
- Helpful rate.
- Estimated time saved.
- Top topics.
- Knowledge gaps.

Important:

Estimated time saved must be labeled as an estimate.

### Settings Screen

Purpose:

Configure basic assistant behavior.

Required elements:

- Minutes saved per resolved question.
- Default escalation owner.
- Assistant active/paused.
- Sensitive topic categories, later.

## Role-Based Navigation

Employee navigation:

- Ask HR
- My recent questions, later

HR Admin navigation:

- Ask HR
- Admin Overview
- Sources
- Escalations
- Dashboard
- Settings

Owner navigation:

- Same as HR Admin.
- User management later.

## Frontend API Client

Create one API client wrapper for Laravel endpoints.

Responsibilities:

- Attach credentials/session.
- Handle JSON errors.
- Normalize error messages.
- Redirect unauthenticated users.

## First UI Milestone

The first usable UI milestone:

1. Admin logs in.
2. Admin uploads HR policy source.
3. Employee logs in.
4. Employee asks question.
5. Employee receives answer with citation or escalation.
6. Admin sees escalation/log.
7. Dashboard shows first metrics.

## Founder Decision

Build a utilitarian SaaS interface first. The product should feel trustworthy, fast, and operational.
