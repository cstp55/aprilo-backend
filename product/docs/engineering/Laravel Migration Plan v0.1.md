# Laravel Migration Plan v0.1

## Product

AI HR Knowledge & Workflow Assistant

## Purpose

Define the first Laravel migration sequence for the MVP data model.

## Migration Order

Recommended order:

1. organizations
2. users role/organization updates
3. organization_settings
4. knowledge_sources
5. knowledge_chunks
6. questions
7. answers
8. answer_sources
9. escalations
10. feedback
11. audit_events

## Tables

### organizations

Fields:

- id UUID primary key
- name string
- status string default active
- timestamps

Indexes:

- status

### users

Laravel includes a users table by default. Extend it with:

- id UUID or keep Laravel default ID strategy
- organization_id foreign key nullable during invite/setup
- name
- email
- password
- role string
- status string default active
- timestamps

Indexes:

- organization_id
- email unique
- role
- status

### organization_settings

Fields:

- id UUID primary key
- organization_id foreign key unique
- minutes_saved_per_resolved_question integer default 5
- default_escalation_owner foreign key nullable
- assistant_status string default active
- timestamps

### knowledge_sources

Fields:

- id UUID primary key
- organization_id foreign key
- title string
- source_type string
- file_path string nullable
- status string
- access_scope string default all_employees
- uploaded_by foreign key
- metadata JSON nullable
- timestamps

Indexes:

- organization_id
- status
- access_scope
- uploaded_by

### knowledge_chunks

Fields:

- id UUID primary key
- organization_id foreign key
- source_id foreign key
- chunk_text text
- chunk_index integer
- section_title string nullable
- embedding vector
- metadata JSON nullable
- created_at timestamp

Indexes:

- organization_id
- source_id
- vector index after pgvector setup

### questions

Fields:

- id UUID primary key
- organization_id foreign key
- user_id foreign key
- question_text text
- topic string nullable
- sensitivity_status string default normal
- status string
- created_at timestamp

Indexes:

- organization_id
- user_id
- status
- topic
- created_at

### answers

Fields:

- id UUID primary key
- organization_id foreign key
- question_id foreign key
- answer_text text
- answer_status string
- confidence_label string nullable
- model_provider string nullable
- model_name string nullable
- metadata JSON nullable
- created_at timestamp

Indexes:

- organization_id
- question_id
- answer_status

### answer_sources

Fields:

- id UUID primary key
- organization_id foreign key
- answer_id foreign key
- source_id foreign key
- chunk_id foreign key
- citation_label string nullable
- created_at timestamp

Indexes:

- organization_id
- answer_id
- source_id
- chunk_id

### escalations

Fields:

- id UUID primary key
- organization_id foreign key
- question_id foreign key
- user_id foreign key
- category string
- status string default open
- assigned_to foreign key nullable
- resolution_note text nullable
- created_at timestamp
- resolved_at timestamp nullable

Indexes:

- organization_id
- status
- category
- assigned_to
- created_at

### feedback

Fields:

- id UUID primary key
- organization_id foreign key
- answer_id foreign key
- user_id foreign key
- rating string
- comment text nullable
- created_at timestamp

Indexes:

- organization_id
- answer_id
- rating

### audit_events

Fields:

- id UUID primary key
- organization_id foreign key
- actor_user_id foreign key nullable
- event_type string
- entity_type string nullable
- entity_id UUID nullable
- metadata JSON nullable
- created_at timestamp

Indexes:

- organization_id
- actor_user_id
- event_type
- entity_type
- entity_id
- created_at

## pgvector Setup

The backend requires pgvector for MVP semantic retrieval.

Migration requirement:

- Enable vector extension.
- Add vector column to knowledge_chunks.
- Add index after embedding dimension is selected.

Open decision:

- Embedding dimension depends on selected embedding model.

## UUID Decision

Recommendation:

Use UUIDs for customer-facing and API-facing records.

Reason:

- Safer for public APIs.
- Better for multi-tenant SaaS.
- Avoids exposing sequential IDs.

## Soft Deletes

Recommended:

- Use soft deletes for knowledge_sources.
- Consider soft deletes for organizations and users.
- Do not soft delete audit_events.

## Tenant Isolation Rule

Every query touching customer data must filter by organization_id.

This should be enforced in:

- Controllers.
- Policies.
- Query scopes.
- Tests.

## Founder Decision

Use this migration plan as the starting point for Laravel Sprint 0 schema work.
