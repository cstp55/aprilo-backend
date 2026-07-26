# Implementation Blueprint v0.1

## Product

AI HR Knowledge & Workflow Assistant

## Purpose

This document translates the PRD and Sprint 0 plan into an implementation-ready blueprint for a Laravel API backend and Next.js frontend.

## Final Stack Decision

| Layer | Decision |
|---|---|
| Frontend | Next.js + TypeScript |
| Backend | Laravel API |
| Database | PostgreSQL |
| Vector Search | pgvector |
| Queue | Redis + Laravel Queues/Horizon |
| Auth | Laravel Sanctum |
| Authorization | Laravel Policies/Gates |
| Storage | S3-compatible object storage |
| AI | Laravel AI provider abstraction |
| Deployment | Vercel frontend + managed Laravel backend |

## Repository Strategy

Recommended structure:

```text
product/
  backend/      Laravel API
  frontend/     Next.js app
  docs/         PRD, API contract, architecture docs
```

Reason:

- Backend and frontend can evolve independently.
- Laravel owns business rules, data, auth, queues, and integrations.
- Next.js owns product UI and user experience.
- A single product repo keeps MVP coordination simple.

## Backend Responsibilities

Laravel owns:

1. Authentication and sessions.
2. Organization workspaces.
3. Users, roles, and permissions.
4. Knowledge source metadata.
5. File upload handling.
6. Document processing jobs.
7. Embedding generation.
8. Retrieval and source filtering.
9. AI answer orchestration.
10. Sensitive-topic detection.
11. Escalations.
12. Feedback.
13. Audit logs.
14. ROI metrics.
15. Future integrations.

## Frontend Responsibilities

Next.js owns:

1. Login and authenticated app shell.
2. Employee ask interface.
3. HR admin dashboard.
4. Knowledge source management UI.
5. Escalation queue UI.
6. ROI dashboard UI.
7. Settings screens.
8. Pilot-friendly onboarding experience.

## Laravel Module Structure

Recommended backend modules:

```text
app/
  Models/
    Organization.php
    OrganizationSetting.php
    User.php
    KnowledgeSource.php
    KnowledgeChunk.php
    Question.php
    Answer.php
    AnswerSource.php
    Escalation.php
    Feedback.php
    AuditEvent.php

  Http/
    Controllers/
      AuthController.php
      OrganizationController.php
      UserController.php
      KnowledgeSourceController.php
      QuestionController.php
      EscalationController.php
      FeedbackController.php
      MetricsController.php

  Services/
    AI/
      AiProviderInterface.php
      AiAnswerService.php
      EmbeddingService.php
      SensitivityClassifier.php
    Knowledge/
      DocumentParser.php
      ChunkingService.php
      RetrievalService.php
      SourceAccessService.php
    Audit/
      AuditLogger.php
    Metrics/
      MetricsService.php

  Jobs/
    ProcessKnowledgeSource.php
    GenerateKnowledgeChunkEmbeddings.php

  Policies/
    KnowledgeSourcePolicy.php
    EscalationPolicy.php
    MetricsPolicy.php

  Events/
    QuestionAsked.php
    AnswerGenerated.php
    EscalationCreated.php
    SourceUploaded.php
```

## Frontend Route Structure

Recommended Next.js routes:

```text
/
/login
/app
/app/ask
/app/admin
/app/admin/sources
/app/admin/escalations
/app/admin/dashboard
/app/admin/settings
```

## MVP Build Sequence

### Step 1: Backend Foundation

- Laravel project.
- Postgres connection.
- Sanctum auth.
- Organization and user models.
- Role checks.
- API route structure.

### Step 2: Frontend Foundation

- Next.js project.
- Auth-aware layout.
- Employee app shell.
- Admin app shell.
- API client wrapper.

### Step 3: Knowledge Sources

- Upload endpoint.
- Source metadata table.
- File storage.
- Indexing status.
- Source list UI.

### Step 4: Document Processing

- Parse document text.
- Chunk text.
- Create embeddings.
- Store chunks with source metadata.
- Queue-based processing.

### Step 5: Employee Q&A

- Ask endpoint.
- Retrieval service.
- Source-grounded answer service.
- Citation response format.
- Fallback handling.

### Step 6: Escalation, Feedback, Audit

- Escalation endpoint and queue.
- Feedback endpoint.
- Audit logger.
- Admin review screens.

### Step 7: Metrics Dashboard

- Metrics API.
- Dashboard UI.
- Resolution, escalation, helpfulness, time saved.

## First Engineering Milestone

The first milestone is not a complete AI product. It is:

Admin can upload a policy document, employee can ask a question, and the system returns a source-cited answer or safely escalates.

## Engineering Guardrails

1. Every table with customer data must include `organization_id`.
2. Every API request must be scoped to the authenticated organization.
3. Employee users must not access admin endpoints.
4. AI answers must cite source chunks.
5. Unsupported answers must fall back instead of guessing.
6. Sensitive HR topics must escalate.
7. Every question must create an audit trail.
8. AI provider calls must go through backend service abstractions.

## Founder Decision

Proceed to Sprint 0 using this implementation blueprint.
