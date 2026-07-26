# Sprint 1 Implementation Status

## Executive Summary

Sprint 1 is now implemented as a working MVP foundation for the AI HR Knowledge & Workflow Assistant.

The product has:

- A Laravel API backend.
- A Next.js frontend.
- Token-based demo login with Laravel Sanctum.
- Demo organization and users.
- First HR question workflow.
- Safe fallback answer behavior.
- Sensitive-topic escalation behavior.
- HR admin settings, source upload shell, escalation queue, and metrics views.
- Automated backend smoke coverage for the Sprint 1 API surface.

The product is ready for the next sprint: source ingestion, chunking, embeddings, retrieval, and source-cited AI answers.

## Implemented Product Areas

### Backend

- Laravel API application scaffolded.
- API routing enabled.
- Laravel Sanctum installed and configured.
- UUID-based tenant-aware data model.
- Demo organization and users seeded.
- Domain models created for:
  - Organizations.
  - Organization settings.
  - Users.
  - Knowledge sources.
  - Knowledge chunks.
  - Questions.
  - Answers.
  - Answer sources.
  - Escalations.
  - Feedback.
  - Audit events.
- First-pass service layer created for:
  - AI provider abstraction.
  - Answer orchestration.
  - Embeddings.
  - Sensitivity classification.
  - Document parsing.
  - Chunking.
  - Retrieval.
  - Source access checks.
  - Audit logging.
  - Metrics.
- Queue job created for knowledge source processing.
- Sprint 1 smoke test added.

### Frontend

- Next.js app shell implemented.
- Login screen implemented.
- Employee ask workflow implemented.
- HR admin source upload screen implemented.
- HR admin metrics dashboard implemented.
- HR admin settings screen implemented.
- HR admin escalation queue implemented.
- Shared API client implemented.
- Responsive operational SaaS layout implemented.

## Demo Accounts

| Role | Email | Password |
| --- | --- | --- |
| Owner | owner@example.com | password |
| HR Admin | hr@example.com | password |
| Employee | employee@example.com | password |

## Local Links

| Area | URL |
| --- | --- |
| Frontend app | http://127.0.0.1:3000 |
| Login | http://127.0.0.1:3000/login |
| Backend API base | http://127.0.0.1:8000/api |
| Backend health check | http://127.0.0.1:8000/up |

## Run Locally

Start the backend:

```powershell
cd C:\Users\chand\Documents\Codex\2026-07-06\n\product\backend
php artisan serve --host=127.0.0.1 --port=8000
```

Start the frontend in another terminal:

```powershell
cd C:\Users\chand\Documents\Codex\2026-07-06\n\product\frontend
C:\nodejs\npm.cmd run dev -- --hostname 127.0.0.1 --port 3000
```

## Verification Completed

| Check | Result |
| --- | --- |
| Backend route list | Passed |
| Backend automated tests | Passed: 3 tests, 23 assertions |
| Frontend source lint | Passed |
| Frontend TypeScript check | Passed |
| Frontend production build | Passed |

## Current Limitations

- Source-grounded AI answering is not implemented yet.
- Document parsing and indexing are scaffolded but not production-ready.
- Embeddings and vector search are scaffolded but not connected to a provider yet.
- The source upload flow stores source metadata and file references, but the processing job currently performs a safe status transition only.
- The local development database is SQLite. Production should use PostgreSQL with pgvector.
- Background servers were not left running by Codex because the current sandbox refused detached server launches.

## Founder Decisions

| Decision | Rationale |
| --- | --- |
| Use Laravel as the backend API and workflow layer | Strong fit for structured business logic, queues, auth, policy rules, audits, and integrations. |
| Keep AI services behind Laravel interfaces | Allows OpenAI, Azure OpenAI, AWS Bedrock, or local providers later without rewriting product logic. |
| Use Next.js for the frontend | Fast product iteration, strong UI ecosystem, and clean API-driven architecture. |
| Start with safe fallback instead of ungrounded AI answers | Protects trust and compliance before retrieval quality is validated. |
| Use HR policy Q&A as Sprint 1 beachhead | Fast workflow validation with measurable ROI and clear escalation behavior. |

## Recommended Sprint 2

1. Implement document parsing for PDF, DOCX, TXT, and Markdown.
2. Add chunking and source metadata extraction.
3. Add embedding provider integration.
4. Add PostgreSQL + pgvector local/prod setup.
5. Implement retrieval with organization-level access control.
6. Generate source-cited answers only when retrieval confidence is sufficient.
7. Add answer source citations in the frontend.
8. Add admin source processing status and error visibility.
