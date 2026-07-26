# Laravel API Contract v0.1

## Product

AI HR Knowledge & Workflow Assistant

## Purpose

Define the first API surface between the Next.js frontend and Laravel backend.

## API Principles

1. All authenticated endpoints require Laravel Sanctum authentication.
2. All customer-owned data must be scoped by organization.
3. Employee endpoints and admin endpoints must be separated.
4. API responses should be predictable and frontend-friendly.
5. Sensitive HR data should not be returned unless required.

## Auth Endpoints

### POST /api/auth/login

Purpose:

Authenticate user.

Request:

```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

Response:

```json
{
  "user": {
    "id": "uuid",
    "name": "HR Admin",
    "email": "admin@example.com",
    "role": "hr_admin",
    "organization_id": "uuid"
  }
}
```

### POST /api/auth/logout

Purpose:

End session.

## Current User

### GET /api/me

Purpose:

Return current user, role, and organization.

Response:

```json
{
  "user": {
    "id": "uuid",
    "name": "Employee One",
    "email": "employee@example.com",
    "role": "employee",
    "organization_id": "uuid"
  },
  "organization": {
    "id": "uuid",
    "name": "Demo Company"
  }
}
```

## Knowledge Source Endpoints

### GET /api/admin/sources

Role:

Owner or HR Admin.

Purpose:

List uploaded knowledge sources.

Response:

```json
{
  "sources": [
    {
      "id": "uuid",
      "title": "Employee Handbook",
      "source_type": "pdf",
      "status": "indexed",
      "access_scope": "all_employees",
      "uploaded_by": "uuid",
      "created_at": "2026-07-10T00:00:00Z"
    }
  ]
}
```

### POST /api/admin/sources

Role:

Owner or HR Admin.

Purpose:

Upload a new source.

Request:

Multipart form data:

- `file`
- `title`
- `access_scope`

Response:

```json
{
  "source": {
    "id": "uuid",
    "title": "Leave Policy",
    "status": "uploaded"
  }
}
```

Backend behavior:

- Store file.
- Create source record.
- Dispatch `ProcessKnowledgeSource` job.
- Create audit event.

### PATCH /api/admin/sources/{sourceId}

Role:

Owner or HR Admin.

Purpose:

Update source metadata or status.

Request:

```json
{
  "title": "Updated Leave Policy",
  "access_scope": "all_employees",
  "status": "inactive"
}
```

## Employee Question Endpoints

### POST /api/questions

Role:

Authenticated employee, manager, HR admin, or owner.

Purpose:

Ask an HR question.

Request:

```json
{
  "question_text": "What is our remote work policy?"
}
```

Response when answered:

```json
{
  "question": {
    "id": "uuid",
    "question_text": "What is our remote work policy?",
    "status": "answered"
  },
  "answer": {
    "id": "uuid",
    "answer_text": "According to the Remote Work Policy, employees may work remotely...",
    "answer_status": "answered",
    "confidence_label": "high",
    "sources": [
      {
        "source_id": "uuid",
        "source_title": "Remote Work Policy",
        "citation_label": "Remote Work Policy, Section 2"
      }
    ]
  }
}
```

Response when unsupported:

```json
{
  "question": {
    "id": "uuid",
    "status": "unsupported"
  },
  "answer": {
    "id": "uuid",
    "answer_text": "I cannot confirm this from the approved HR sources. I can escalate this to HR.",
    "answer_status": "fallback",
    "confidence_label": "low",
    "sources": []
  }
}
```

Response when escalated:

```json
{
  "question": {
    "id": "uuid",
    "status": "escalated"
  },
  "answer": {
    "id": "uuid",
    "answer_text": "This question should be reviewed by HR. I have created an escalation.",
    "answer_status": "escalated",
    "confidence_label": "low"
  },
  "escalation": {
    "id": "uuid",
    "status": "open",
    "category": "sensitive"
  }
}
```

Backend behavior:

- Verify user and organization.
- Detect sensitive topic.
- Retrieve permitted source chunks.
- Generate grounded answer or fallback.
- Save question, answer, answer sources.
- Create audit event.
- Create escalation if needed.

### GET /api/questions/{questionId}

Purpose:

Return question and answer detail for authorized user.

## Feedback Endpoints

### POST /api/answers/{answerId}/feedback

Purpose:

Capture helpful/not helpful feedback.

Request:

```json
{
  "rating": "helpful",
  "comment": "Clear answer"
}
```

Response:

```json
{
  "feedback": {
    "id": "uuid",
    "rating": "helpful"
  }
}
```

## Escalation Endpoints

### GET /api/admin/escalations

Role:

Owner or HR Admin.

Purpose:

List escalations.

Query params:

- `status`
- `category`
- `from`
- `to`

Response:

```json
{
  "escalations": [
    {
      "id": "uuid",
      "question_text": "Can HR help with a payroll dispute?",
      "category": "sensitive",
      "status": "open",
      "created_at": "2026-07-10T00:00:00Z"
    }
  ]
}
```

### PATCH /api/admin/escalations/{escalationId}

Role:

Owner or HR Admin.

Purpose:

Update escalation status or resolution note.

Request:

```json
{
  "status": "resolved",
  "resolution_note": "HR contacted the employee directly."
}
```

## Metrics Endpoints

### GET /api/admin/metrics/summary

Role:

Owner or HR Admin.

Purpose:

Return dashboard summary.

Query params:

- `from`
- `to`

Response:

```json
{
  "summary": {
    "total_questions": 120,
    "resolved_questions": 72,
    "escalated_questions": 28,
    "unsupported_questions": 20,
    "resolution_rate": 0.6,
    "helpful_rate": 0.74,
    "estimated_minutes_saved": 360,
    "estimated_hours_saved": 6
  }
}
```

### GET /api/admin/metrics/topics

Purpose:

Return top question topics and knowledge gaps.

Response:

```json
{
  "topics": [
    {
      "topic": "leave_policy",
      "count": 34
    }
  ],
  "knowledge_gaps": [
    {
      "question_text": "What is the relocation policy?",
      "count": 3
    }
  ]
}
```

## Settings Endpoints

### GET /api/admin/settings

Role:

Owner or HR Admin.

Purpose:

Return organization settings.

### PATCH /api/admin/settings

Role:

Owner or HR Admin.

Request:

```json
{
  "minutes_saved_per_resolved_question": 5,
  "assistant_status": "active",
  "default_escalation_owner": "uuid"
}
```

## API Error Format

Use consistent error responses:

```json
{
  "error": {
    "code": "forbidden",
    "message": "You do not have access to this resource."
  }
}
```

Common codes:

- `unauthenticated`
- `forbidden`
- `validation_error`
- `not_found`
- `source_not_indexed`
- `ai_unavailable`
- `rate_limited`

## Founder Decision

Use this API contract as the starting point for Laravel backend Sprint 0 and Sprint 1 planning.
