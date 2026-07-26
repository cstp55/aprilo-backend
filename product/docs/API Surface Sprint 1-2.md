# API Surface Sprint 1-2

## API Base

Local API base:

```text
http://127.0.0.1:8000/api
```

Health check:

```text
http://127.0.0.1:8000/up
```

Authentication uses Laravel Sanctum bearer tokens for Sprint 1.

## Completed Endpoints

| Method | Endpoint | Status | Purpose |
| --- | --- | --- | --- |
| POST | /api/auth/login | Implemented | Login with email and password, return bearer token. |
| POST | /api/auth/logout | Implemented | Revoke current token. |
| GET | /api/me | Implemented | Return current user and organization. |
| POST | /api/questions | Implemented | Ask a question and receive a source-cited answer, safe fallback, or escalation response. |
| GET | /api/questions/{question} | Implemented | Fetch one question with answers, sources, and escalation. |
| POST | /api/answers/{answer}/feedback | Implemented | Submit helpful/not helpful feedback. |
| GET | /api/admin/sources | Implemented | List organization knowledge sources. |
| POST | /api/admin/sources | Implemented | Upload a source file, parse it, chunk it, and index it in local/testing mode. |
| PATCH | /api/admin/sources/{source} | Implemented | Update source status/metadata. |
| GET | /api/admin/escalations | Implemented | List organization escalations. |
| GET | /api/admin/escalations/{escalation} | Implemented | Fetch one escalation. |
| PATCH | /api/admin/escalations/{escalation} | Implemented | Update escalation status or owner. |
| GET | /api/admin/metrics/summary | Implemented | Return question, escalation, and ROI summary metrics. |
| GET | /api/admin/metrics/topics | Implemented | Return topic and knowledge gap shell. |
| GET | /api/admin/settings | Implemented | Fetch organization assistant settings. |
| PATCH | /api/admin/settings | Implemented | Update organization assistant settings. |

## Role Access

| Role | Access |
| --- | --- |
| Owner | All Sprint 1 admin and employee APIs. |
| HR Admin | All Sprint 1 admin and employee APIs. |
| Employee | Question asking, own question viewing, and feedback. |

## Example Requests

### Login

```powershell
$base = 'http://127.0.0.1:8000/api'
$login = Invoke-RestMethod -Method Post -Uri "$base/auth/login" -ContentType 'application/json' -Body (@{
  email = 'hr@example.com'
  password = 'password'
} | ConvertTo-Json)
$token = $login.token
```

### Ask A Question

```powershell
$headers = @{ Authorization = "Bearer $token" }
Invoke-RestMethod -Method Post -Uri "$base/questions" -Headers $headers -ContentType 'application/json' -Body (@{
  question_text = 'What is the remote work policy?'
} | ConvertTo-Json)
```

### View Metrics

```powershell
Invoke-RestMethod -Method Get -Uri "$base/admin/metrics/summary" -Headers $headers
```

## Current Response Behavior

| Scenario | Sprint 1 Behavior |
| --- | --- |
| Normal HR policy question with matching indexed source | Creates question and returns source-cited answer. |
| Sensitive HR question | Creates question, answer, and escalation for HR review. |
| Unsupported or unindexed knowledge | Returns safe fallback instead of hallucinated answer. |

## Not Yet Implemented

- Provider-backed LLM answer generation.
- Semantic retrieval confidence scoring.
- Answer citation ranking.
- Embedding provider integration.
- pgvector similarity search.
- Full API rate limiting and production monitoring.
- SSO or enterprise identity integration.

## Verification

Automated coverage includes login, current user lookup, admin source listing, source upload/indexing, settings read/update, question creation, source-cited retrieval, question viewing, and metrics summary.

Latest backend result:

```text
Passed: 4 tests, 34 assertions
```
