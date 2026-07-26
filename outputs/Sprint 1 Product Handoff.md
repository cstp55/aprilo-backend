# Sprint 1-2 Product Handoff

## Status

Sprint 1 and Sprint 2 implementation are complete for the MVP foundation and first source-grounded answer workflow.

Built project:

```text
C:\Users\chand\Documents\Codex\2026-07-06\n\product
```

## Links

After starting the local servers:

| Area | URL |
| --- | --- |
| Frontend app | http://127.0.0.1:3000 |
| Login | http://127.0.0.1:3000/login |
| Backend API base | http://127.0.0.1:8000/api |
| Health check | http://127.0.0.1:8000/up |

## Start Commands

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

## Demo Login

| Role | Email | Password |
| --- | --- | --- |
| Owner | owner@example.com | password |
| HR Admin | hr@example.com | password |
| Employee | employee@example.com | password |

## Completed API Surface

- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/me`
- `POST /api/questions`
- `GET /api/questions/{question}`
- `POST /api/answers/{answer}/feedback`
- `GET /api/admin/sources`
- `POST /api/admin/sources`
- `PATCH /api/admin/sources/{source}`
- `GET /api/admin/escalations`
- `GET /api/admin/escalations/{escalation}`
- `PATCH /api/admin/escalations/{escalation}`
- `GET /api/admin/metrics/summary`
- `GET /api/admin/metrics/topics`
- `GET /api/admin/settings`
- `PATCH /api/admin/settings`

## Sprint 2 Additions

- Source upload now indexes TXT, Markdown, and DOCX files locally.
- PDF parsing is best-effort and should be upgraded before production.
- Indexed sources create searchable chunks.
- Questions retrieve relevant chunks.
- Answers include citation labels and source records.
- Employee UI shows citations under grounded answers.
- Admin source table shows indexed chunk count.

## Verification

- Backend route list: passed.
- Backend tests: passed, 4 tests, 34 assertions.
- Frontend source lint: passed.
- Frontend TypeScript check: passed.
- Frontend production build: passed.

## Server Recommendation

Local development does not need Nginx.

Production should use:

- Nginx.
- PHP-FPM for Laravel.
- Supervisor for queue workers.
- PostgreSQL with pgvector.
- Redis.
- S3-compatible object storage.
- Managed HTTPS.

Recommended domains:

- `app.yourdomain.com` for the Next.js frontend.
- `api.yourdomain.com` for the Laravel backend.

## Next Step

Sprint 3 should add embeddings, PostgreSQL + pgvector retrieval, hybrid search, and provider-backed source-only answer generation.
