# Technical Stack Decision v0.1

## Product

AI HR Knowledge & Workflow Assistant

## Decision Status

Recommended for MVP. This should be reviewed before engineering starts Sprint 0.

## Stack Recommendation

| Layer | Recommendation | Reason |
|---|---|---|
| Web app | Next.js + TypeScript | Fast MVP development, strong full-stack support, good hiring market, reusable for future SaaS |
| UI | Tailwind CSS + component library | Fast polished dashboards without building every component from scratch |
| Backend/API | Laravel | Strong API layer for tenant isolation, RBAC, queues, audit logs, AI orchestration, and integrations |
| Database | Postgres | Reliable relational model for organizations, users, sources, questions, escalations, logs, and metrics |
| Authentication | Laravel Sanctum | Clean first-party SPA/API authentication when Laravel owns the backend |
| Authorization | Laravel Policies/Gates | Strong role and permission enforcement |
| Queues | Redis + Laravel Queues/Horizon | Needed for document parsing, embeddings, retries, notifications, and future integrations |
| File storage | S3-compatible storage | Store uploaded HR policy documents safely |
| Vector search | Postgres pgvector for MVP | Keeps retrieval close to source data and avoids extra infrastructure early |
| AI provider | Laravel provider abstraction layer | Allows OpenAI, Anthropic, Gemini, or other providers later without rewriting product logic |
| Document parsing | Laravel queue-driven parsing pipeline | Extract text from PDF, DOCX, TXT/Markdown and prepare chunks for retrieval |
| Event/audit logs | Postgres tables via Laravel events | Keeps HR audit data inside the product database |
| Hosting | Vercel frontend + managed Laravel backend + managed Postgres | Separates frontend speed from backend service reliability |
| Error monitoring | Sentry or equivalent | Needed before pilot to diagnose production issues |
| Product analytics | Internal event tables first | Avoid sending sensitive HR usage data to third parties before policy is clear |

## Preferred MVP Stack

The cleanest MVP stack is:

- Next.js
- TypeScript
- Tailwind CSS
- Laravel API backend
- Laravel Sanctum
- Laravel Policies/Gates
- PostgreSQL
- pgvector
- Redis queues
- S3-compatible storage
- AI provider abstraction in Laravel
- Internal analytics and audit tables
- Vercel frontend deployment
- Managed Laravel backend deployment

This stack is strong for the first HRMS MVP because it keeps the product serious without becoming over-engineered:

- Laravel owns backend logic, auth, roles, queues, audit logs, and integrations.
- Postgres stores SaaS data and retrieval vectors.
- Object storage stores HR documents.
- Next.js focuses on frontend UX.
- Enough flexibility to expand later.

## Why Not Build More Complex Infrastructure Now

Do not start with:

- Separate microservices.
- A dedicated vector database unless Postgres retrieval fails.
- Heavy workflow orchestration.
- Full enterprise identity stack.
- Full data warehouse.
- HRMS write integration.

Reason:

The first product must prove customer value, not infrastructure complexity.

## Technical Decision Details

### Web Framework

Decision:

Use Next.js with TypeScript for the frontend.

Why:

- Good for SaaS dashboards.
- Can support admin pages, employee assistant, and API routes in one product.
- Easy to deploy.
- Strong ecosystem.

### Database

Decision:

Use Postgres.

Why:

- Multi-tenant SaaS data is relational.
- Audit logs, sources, users, organizations, escalations, and metrics all fit well.
- pgvector can support MVP retrieval without a separate vector database.

### Authentication

Decision:

Use Laravel Sanctum.

Why:

If Laravel owns the backend, authentication and authorization should live there too. This avoids splitting business permissions between Supabase/Clerk and Laravel.

### File Storage

Decision:

Use managed object storage.

Why:

HR documents should not be stored as loose files inside the app. Store original files in storage, extracted text/chunks in database.

### Retrieval

Decision:

Use pgvector for MVP.

Why:

It is enough for early document retrieval and keeps infrastructure simple.

Upgrade later if:

- Retrieval quality is poor.
- Data volume grows.
- Multi-source search becomes complex.
- We need advanced hybrid search at scale.

### AI Provider

Decision:

Build an AI provider abstraction from day one.

Why:

The company should not hard-code the product to one model provider. The Laravel backend should expose an internal AI service interface, which can route to the selected model provider.

### Analytics

Decision:

Use internal event tables first.

Why:

Pilot metrics include HR-related usage. Keeping early product metrics in our own database reduces privacy and compliance complexity.

## Stack Risks

| Risk | Mitigation |
|---|---|
| More moving parts than Next.js-only | Keep Laravel as a modular monolith, avoid microservices |
| pgvector retrieval limits | Design retrieval layer so a vector DB can replace it later |
| Laravel document parsing quality may vary | Add Python worker later only if extraction quality becomes a real issue |
| AI provider cost risk | Track cost per question and per resolved answer |
| HR data privacy risk | Store minimal employee metadata; audit all access |

## Decisions Closed

| Decision | Answer |
|---|---|
| Web framework | Next.js + TypeScript |
| Backend/API | Laravel |
| Database | Postgres |
| Auth | Laravel Sanctum |
| Authorization | Laravel Policies/Gates |
| Queues | Redis + Laravel Queues/Horizon |
| Document parsing | Laravel queue-driven parsing pipeline; optional Python worker later |
| Vector search | pgvector |
| AI provider | Abstracted provider layer inside Laravel |
| Hosting | Vercel frontend + managed Laravel backend |
| File storage | S3-compatible |
| Logging/analytics | Internal Postgres audit and event tables via Laravel |
| Slack/Teams timing | Later, after document-first pilot works |

## Founder Decision

Proceed with a Laravel-backed SaaS architecture:

Next.js + TypeScript frontend + Laravel API backend + PostgreSQL/pgvector + Redis queues + S3-compatible storage + AI provider abstraction.

This gives us the fastest path to a trustworthy pilot while keeping the platform reusable for later ecommerce, ERP, and CRM capability packs.
