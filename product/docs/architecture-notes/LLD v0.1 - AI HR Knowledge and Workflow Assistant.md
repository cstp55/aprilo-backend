# Low-Level Design v0.1 - AI HR Knowledge and Workflow Assistant

## 1. Document Control

| Field | Value |
|---|---|
| Product | AI HR Knowledge & Workflow Assistant |
| Document Type | Low-Level Design |
| Version | 0.1 |
| Status | Draft for engineering review |
| Backend | Laravel API |
| Frontend | Next.js + TypeScript |
| Database | PostgreSQL + pgvector |

## 2. Purpose

This Low-Level Design defines the first implementation details for the MVP. It expands the HLD into Laravel modules, models, controllers, services, jobs, policies, database tables, API behavior, frontend routes, and core workflows.

This is not final code. It is the engineering design baseline for Sprint 0 and Sprint 1.

## 3. Backend Package Structure

```text
backend/
  app/
    Models/
    Http/
      Controllers/
      Requests/
      Resources/
      Middleware/
    Services/
      AI/
      Knowledge/
      Audit/
      Metrics/
    Jobs/
    Policies/
    Events/
    Listeners/
    Enums/
  database/
    migrations/
    seeders/
  routes/
    api.php
  tests/
    Feature/
    Unit/
```

## 4. Laravel Models

### Organization

Fields:

- id
- name
- status
- timestamps

Relationships:

- hasMany users
- hasOne organizationSetting
- hasMany knowledgeSources
- hasMany questions
- hasMany escalations
- hasMany auditEvents

### User

Fields:

- id
- organization_id
- name
- email
- password
- role
- status
- timestamps

Relationships:

- belongsTo organization
- hasMany questions
- hasMany feedback

### OrganizationSetting

Fields:

- id
- organization_id
- minutes_saved_per_resolved_question
- default_escalation_owner
- assistant_status
- timestamps

### KnowledgeSource

Fields:

- id
- organization_id
- title
- source_type
- file_path
- status
- access_scope
- uploaded_by
- metadata
- timestamps

Relationships:

- belongsTo organization
- belongsTo uploader
- hasMany chunks

### KnowledgeChunk

Fields:

- id
- organization_id
- source_id
- chunk_text
- chunk_index
- section_title
- embedding
- metadata
- created_at

Relationships:

- belongsTo organization
- belongsTo source

### Question

Fields:

- id
- organization_id
- user_id
- question_text
- topic
- sensitivity_status
- status
- created_at

Relationships:

- belongsTo user
- hasMany answers
- hasOne escalation

### Answer

Fields:

- id
- organization_id
- question_id
- answer_text
- answer_status
- confidence_label
- model_provider
- model_name
- metadata
- created_at

Relationships:

- belongsTo question
- hasMany answerSources
- hasMany feedback

### AnswerSource

Fields:

- id
- organization_id
- answer_id
- source_id
- chunk_id
- citation_label
- created_at

### Escalation

Fields:

- id
- organization_id
- question_id
- user_id
- category
- status
- assigned_to
- resolution_note
- created_at
- resolved_at

### Feedback

Fields:

- id
- organization_id
- answer_id
- user_id
- rating
- comment
- created_at

### AuditEvent

Fields:

- id
- organization_id
- actor_user_id
- event_type
- entity_type
- entity_id
- metadata
- created_at

## 5. Enums

Recommended enums:

```text
UserRole: owner, hr_admin, employee
OrganizationStatus: active, paused, archived
SourceStatus: uploaded, indexing, indexed, failed, inactive
SourceType: pdf, docx, txt, markdown, faq
AccessScope: all_employees, managers, hr_only, custom
QuestionStatus: answered, escalated, unsupported
SensitivityStatus: normal, sensitive, unknown
AnswerStatus: answered, fallback, escalated
ConfidenceLabel: high, medium, low
EscalationStatus: open, in_progress, resolved
FeedbackRating: helpful, not_helpful
```

## 6. Controllers

### AuthController

Methods:

- login
- logout
- me

### KnowledgeSourceController

Methods:

- index
- store
- update
- show

Responsibilities:

- Validate upload.
- Store file.
- Create source record.
- Dispatch processing job.
- Return source status.

### QuestionController

Methods:

- store
- show

Responsibilities:

- Accept question.
- Call QuestionAnsweringService.
- Return answer, citations, or escalation.

### EscalationController

Methods:

- index
- show
- update

Responsibilities:

- List escalations.
- Update status.
- Save resolution note.

### FeedbackController

Methods:

- store

Responsibilities:

- Store helpful/not helpful feedback.
- Write audit event.

### MetricsController

Methods:

- summary
- topics

Responsibilities:

- Return dashboard metrics.
- Return knowledge gaps and top topics.

### SettingsController

Methods:

- show
- update

Responsibilities:

- Manage minutes-saved assumption.
- Manage assistant status.
- Manage default escalation owner.

## 7. Services

### AiProviderInterface

Purpose:

Decouple product logic from specific AI providers.

Methods:

```text
generateAnswer(question, context, policy): AiAnswerResult
createEmbedding(text): array
classifySensitivity(question): SensitivityResult
```

### AiAnswerService

Responsibilities:

- Build answer prompt.
- Enforce answer rules.
- Call provider.
- Validate source support.
- Return answer result.

### EmbeddingService

Responsibilities:

- Generate embeddings for chunks.
- Store embedding metadata.
- Track provider/model used.

### SensitivityClassifier

Responsibilities:

- Detect sensitive HR topics.
- Return category and reason.

### DocumentParser

Responsibilities:

- Extract text from PDF, DOCX, TXT/Markdown.
- Return parse result or failure.

### ChunkingService

Responsibilities:

- Split text into retrievable chunks.
- Preserve section titles where possible.
- Add chunk indexes.

### RetrievalService

Responsibilities:

- Embed question.
- Query pgvector.
- Filter by organization.
- Filter by source access scope.
- Return top chunks.

### SourceAccessService

Responsibilities:

- Determine whether a user can access a source.
- Apply role and scope rules.

### AuditLogger

Responsibilities:

- Create audit events.
- Attach actor, entity, event type, metadata.

### MetricsService

Responsibilities:

- Calculate dashboard summary.
- Calculate resolution rate.
- Calculate helpful rate.
- Calculate estimated time saved.
- Identify top topics and knowledge gaps.

## 8. Jobs

### ProcessKnowledgeSource

Triggered by:

- Source upload.

Steps:

1. Mark source indexing.
2. Read file from storage.
3. Parse document.
4. Chunk text.
5. Dispatch embedding job or generate embeddings inline for MVP.
6. Store chunks.
7. Mark source indexed.
8. On failure, mark source failed and log error.

### GenerateKnowledgeChunkEmbeddings

Triggered by:

- ProcessKnowledgeSource, if separated.

Steps:

1. Load chunks without embeddings.
2. Call EmbeddingService.
3. Store vectors.
4. Log completion.

## 9. Policies

### KnowledgeSourcePolicy

Rules:

- Owner/HR admin can view all organization sources.
- Employee cannot view source management.
- Retrieval can use only accessible sources.

### EscalationPolicy

Rules:

- Owner/HR admin can view organization escalations.
- Employee can view own escalation status later if needed.

### MetricsPolicy

Rules:

- Owner/HR admin can view dashboard.
- Employee cannot view organization metrics.

## 10. API Routes

```text
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/me

GET    /api/admin/sources
POST   /api/admin/sources
PATCH  /api/admin/sources/{source}

POST   /api/questions
GET    /api/questions/{question}
POST   /api/answers/{answer}/feedback

GET    /api/admin/escalations
GET    /api/admin/escalations/{escalation}
PATCH  /api/admin/escalations/{escalation}

GET    /api/admin/metrics/summary
GET    /api/admin/metrics/topics

GET    /api/admin/settings
PATCH  /api/admin/settings
```

## 11. Question Answering Algorithm

```text
1. Authenticate user.
2. Load organization and role.
3. Store question.
4. Classify sensitivity.
5. If sensitive:
   a. Create safe answer.
   b. Create escalation.
   c. Write audit event.
   d. Return escalated response.
6. Retrieve permitted chunks using question embedding.
7. If no strong source context:
   a. Create fallback answer.
   b. Optionally create escalation.
   c. Mark question unsupported.
   d. Write audit event.
   e. Return fallback.
8. Generate answer using source context.
9. Store answer and answer_sources.
10. Mark question answered.
11. Write audit event.
12. Return answer with citations.
```

## 12. Source Access Algorithm

```text
1. Start with organization_id filter.
2. Exclude inactive or failed sources.
3. Apply access_scope:
   - all_employees: allowed.
   - hr_only: owner/hr_admin only.
   - managers: manager/owner/hr_admin, later.
   - custom: later.
4. Retrieve only chunks from allowed sources.
```

## 13. Frontend Components

### Shared Components

- AppLayout
- Sidebar
- Topbar
- Button
- Input
- Textarea
- DataTable
- StatusBadge
- EmptyState
- LoadingState
- ErrorState

### Employee Components

- AskQuestionForm
- AnswerCard
- SourceCitationList
- FeedbackControls
- EscalationNotice

### Admin Components

- SourceUploadPanel
- SourceTable
- EscalationTable
- EscalationDetailPanel
- MetricsCards
- TopTopicsTable
- KnowledgeGapsTable
- SettingsForm

## 14. Frontend State and API Use

Use a single API client wrapper.

Responsibilities:

- Send credentials/session.
- Normalize errors.
- Handle unauthenticated state.
- Return typed responses.

MVP can use simple client state. Add a data-fetching library later if needed.

## 15. Error Handling

### Backend Error Format

```json
{
  "error": {
    "code": "validation_error",
    "message": "The uploaded file type is not supported."
  }
}
```

### Important Error Codes

- unauthenticated
- forbidden
- validation_error
- not_found
- source_not_indexed
- source_processing_failed
- ai_unavailable
- rate_limited

## 16. Testing Plan

### Backend Feature Tests

- User can login.
- Employee cannot access admin sources.
- HR admin can upload source.
- Source processing creates chunks.
- Employee question retrieves only organization sources.
- Employee cannot access HR-only source.
- Sensitive question creates escalation.
- Unsupported question returns fallback.
- Feedback is stored.
- Metrics summary returns correct counts.

### Unit Tests

- SourceAccessService.
- ChunkingService.
- SensitivityClassifier.
- MetricsService.
- AuditLogger.

### Frontend Tests Later

- Login flow.
- Ask question flow.
- Upload source flow.
- Escalation review flow.
- Dashboard rendering.

## 17. Observability

MVP logging:

- Request errors.
- Job failures.
- AI provider failures.
- Source parsing failures.
- Escalation creation.
- Audit events.

Recommended:

- Sentry or equivalent error monitoring.
- Laravel logs.
- Queue failure table.

## 18. LLD Conclusion

This LLD gives engineering a concrete first implementation path. The system should be built as a Laravel modular monolith with a Next.js frontend. The design prioritizes trusted answers, tenant isolation, source grounding, safe escalation, audit logs, and ROI metrics.

This is ready to support Sprint 0 scaffolding.
