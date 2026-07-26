# AI HR Knowledge & Workflow Assistant

Operational AI MVP for HR policy Q&A, safe escalation, and ROI measurement.

## Structure

```text
product/
  backend/   Laravel API backend
  frontend/  Next.js frontend
  docs/      PRD, HLD, LLD, engineering specs
```

## MVP Stack

- Frontend: Next.js + TypeScript
- Backend: Laravel API
- Auth: Laravel Sanctum
- Local Database: SQLite for Sprint 1 development
- Production Database: PostgreSQL
- Vector Search: pgvector for Sprint 2+
- Queue: Laravel queues, Redis for production
- Storage: S3-compatible object storage
- AI: Provider abstraction inside Laravel

## First Workflow

Employee HR Policy Q&A with:

- Source-cited answers
- Safe fallback
- Sensitive-topic escalation
- HR admin escalation queue
- Feedback
- Audit logs
- ROI dashboard

## Local Links

After starting the local servers:

- Frontend: http://127.0.0.1:3000
- Login: http://127.0.0.1:3000/login
- API Base: http://127.0.0.1:8000/api
- Health: http://127.0.0.1:8000/up

## Demo Accounts

| Role | Email | Password |
| --- | --- | --- |
| Owner | owner@example.com | password |
| HR Admin | hr@example.com | password |
| Employee | employee@example.com | password |

## Run Locally

Backend:

```powershell
cd C:\Users\chand\Documents\Codex\2026-07-06\n\product\backend
php artisan serve --host=127.0.0.1 --port=8000
```

Frontend:

```powershell
cd C:\Users\chand\Documents\Codex\2026-07-06\n\product\frontend
C:\nodejs\npm.cmd run dev -- --hostname 127.0.0.1 --port 3000
```

## Sprint 1 Status

Completed:

- Laravel backend scaffolded
- Next.js frontend scaffolded
- Product docs copied into `docs/`
- API routes enabled in Laravel
- Initial domain models, migrations, controllers, jobs, policies, enums, and services scaffolded
- Auth, demo users, and organization seed data implemented
- First question workflow implemented with safe fallback
- Sensitive-question escalation implemented
- Source upload shell implemented
- Admin metrics, settings, and escalation APIs implemented
- Frontend app shell, login, ask, sources, metrics, settings, and escalations screens implemented
- Backend Sprint 1 smoke test added

Next:

- Sprint 2 document parsing
- Chunking and indexing
- Embedding provider integration
- pgvector retrieval
- Source-cited answer generation
- Frontend citation display

## Documentation

- `docs/Sprint 1 Implementation Status.md`
- `docs/Sprint 2 Implementation Status.md`
- `docs/API Surface Sprint 1-2.md`
- `docs/Server and Nginx Setup.md`

## Sprint 2 Status

Completed:

- TXT, Markdown, and DOCX source parsing
- Best-effort PDF text extraction
- Paragraph-aware chunking
- Local source indexing
- Local lexical retrieval
- Source-cited answers
- Citation display in the Ask HR screen
- Indexed chunk counts in the source admin table
- Retrieval integration test

Next:

- Embedding provider integration
- PostgreSQL + pgvector retrieval
- Hybrid search
- Provider-backed answer generation
- Production PDF parser
