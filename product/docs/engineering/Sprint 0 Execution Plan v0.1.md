# Sprint 0 Execution Plan v0.1

## Product

AI HR Knowledge & Workflow Assistant

## Sprint Goal

Prepare the product for MVP development by making foundational technical decisions and creating the first app skeleton.

Sprint 0 should not build the full product. It should create the foundation that lets Sprint 1 start cleanly.

## Duration

3-5 working days.

## Sprint 0 Outcomes

By the end of Sprint 0, we should have:

1. Technical stack confirmed.
2. Repository/app scaffold created.
3. App runs locally.
4. Database connection planned or configured.
5. Authentication approach selected.
6. Initial data model converted into schema draft.
7. Environment variables documented.
8. First route/page structure created.
9. Basic deployment path selected.
10. P0 tickets ready for Sprint 1.

## Sprint 0 Tasks

### Task 1: Confirm Stack

Decision:

- Next.js + TypeScript.
- Laravel API backend.
- Laravel Sanctum.
- PostgreSQL.
- pgvector for retrieval.
- Redis queues.
- S3-compatible storage.
- AI provider abstraction.
- Vercel deployment.

Output:

- Stack accepted or revised.

### Task 2: Create App Skeleton

Output:

- Web app scaffold.
- TypeScript enabled.
- Basic layout.
- Basic routes.
- Styling system installed.

Suggested routes:

- `/`
- `/login`
- `/app`
- `/app/ask`
- `/app/admin`
- `/app/admin/sources`
- `/app/admin/escalations`
- `/app/admin/dashboard`

### Task 3: Define Environment Variables

Initial variables:

- DATABASE_URL.
- AUTH_SECRET or auth provider keys.
- STORAGE_BUCKET.
- AI_PROVIDER.
- AI_API_KEY.
- APP_URL.

Output:

- `.env.example` with placeholders.

### Task 4: Convert Data Model Into Schema Draft

Tables:

- organizations.
- users/profiles.
- organization_settings.
- knowledge_sources.
- knowledge_chunks.
- questions.
- answers.
- answer_sources.
- escalations.
- feedback.
- audit_events.

Output:

- Schema draft or migration plan.

### Task 5: Define Access Rules

Rules:

- All customer data is scoped by organization_id.
- Employee can ask questions.
- HR admin can upload sources and review escalations.
- Owner can manage workspace settings.

Output:

- Access-control notes or middleware plan.

### Task 6: Define AI Service Interface

The app should not call an AI provider directly from UI code.

Define internal interface:

- generateAnswer(question, context, policy).
- createEmbedding(text).
- classifySensitivity(question).

Output:

- AI adapter interface plan.

### Task 7: Define Document Processing Pipeline

Pipeline:

- Upload file.
- Extract text.
- Chunk text.
- Embed chunks.
- Store chunks.
- Mark source indexed.

Output:

- Document processing plan and first implementation target.

### Task 8: Create Demo Data Plan

Demo workspace needs:

- Sample organization.
- Admin user.
- Employee user.
- Sample HR policy documents.
- Sample questions.

Output:

- Demo data checklist.

## Sprint 0 Acceptance Criteria

Sprint 0 is complete when:

1. Stack is confirmed.
2. App skeleton exists and runs locally.
3. Route structure exists.
4. Data model is ready for implementation.
5. Auth approach is chosen.
6. AI provider abstraction is defined.
7. Document processing plan is clear.
8. Sprint 1 tickets are ready to build.

## Sprint 1 Preview

Sprint 1 should build:

- Organization workspace.
- Authentication.
- Basic roles.
- Admin shell.
- Employee ask page shell.

## Founder Decision

Start Sprint 0 only after confirming the recommended stack:

Next.js + TypeScript frontend + Laravel API backend + PostgreSQL/pgvector + Redis queues + S3-compatible storage + AI provider abstraction.
