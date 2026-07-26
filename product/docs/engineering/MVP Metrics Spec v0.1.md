# MVP Metrics Spec v0.1

## Product

AI HR Knowledge & Workflow Assistant

## Purpose

Define the metrics that must be captured in the MVP so the first pilot can prove operational value.

## Metrics Philosophy

The product should not measure success by chat volume alone. The MVP must measure whether the assistant reduced HR work, improved employee self-service, and created trust.

## Core Metric Groups

### 1. Usage Metrics

| Metric | Definition | Why It Matters |
|---|---|---|
| Total questions | Number of employee questions submitted | Shows usage volume |
| Active users | Unique users who asked at least one question | Shows adoption |
| Repeat users | Users who return after first use | Shows usefulness |
| Top topics | Most common question categories | Shows operational pain |

### 2. Resolution Metrics

| Metric | Definition | Why It Matters |
|---|---|---|
| Resolved questions | Questions answered with sufficient source support and no escalation | Shows HR work avoided |
| Escalated questions | Questions routed to HR | Shows safety and workflow need |
| Resolution rate | Resolved questions / total questions | Core operational value metric |
| Escalation rate | Escalated questions / total questions | Shows unresolved complexity |
| Unsupported questions | Questions where approved knowledge was insufficient | Shows knowledge gaps |

### 3. Trust Metrics

| Metric | Definition | Why It Matters |
|---|---|---|
| Helpful rate | Helpful ratings / total ratings | User trust signal |
| Not helpful rate | Not helpful ratings / total ratings | Quality risk signal |
| Source citation rate | Answers with displayed source / total answers | Trust and auditability |
| Sensitive-topic escalation count | Sensitive questions escalated | Safety signal |
| High-severity incidents | Privacy or access-control issues | Must remain zero |

### 4. ROI Metrics

| Metric | Definition | Why It Matters |
|---|---|---|
| Estimated minutes saved | Resolved questions x configured minutes saved | Converts usage to time value |
| Estimated hours saved | Estimated minutes saved / 60 | Executive-friendly ROI |
| Estimated cost saved | Hours saved x HR hourly cost assumption | Optional pilot ROI |
| Ticket deflection estimate | Resolved repetitive questions that would have gone to HR | Shows support reduction |

### 5. Knowledge Quality Metrics

| Metric | Definition | Why It Matters |
|---|---|---|
| Knowledge gaps | Questions not answerable from approved sources | Helps HR improve documentation |
| Top missing topics | Most common unsupported topics | Guides source updates |
| Source usage | Which documents are cited most often | Shows valuable sources |
| Failed source indexing | Uploaded sources that failed processing | Admin reliability |

## Default ROI Assumptions

These are assumptions for first pilot and must be validated:

| Assumption | Default |
|---|---:|
| Minutes saved per resolved repetitive question | 5 minutes |
| Conservative range | 3-5 minutes |
| Base range | 5-10 minutes |
| Aggressive range | 10-15 minutes |

## Pilot Dashboard Requirements

The first dashboard should show:

1. Total questions.
2. Resolved questions.
3. Escalated questions.
4. Resolution rate.
5. Helpful rate.
6. Estimated time saved.
7. Top topics.
8. Knowledge gaps.
9. Recent escalations.

## 30-Day Pilot Success Thresholds

| Metric | Target |
|---|---:|
| Total employee questions | 50+ |
| Resolution rate | 50%+ |
| Helpful rate | 70%+ where rated |
| High-severity privacy/access incidents | 0 |
| Knowledge gaps identified | 10+ useful gaps |
| HR admin confirms time saved | Yes |

## Events To Track

| Event | Required Properties |
|---|---|
| user_invited | organization_id, role, invited_by, timestamp |
| source_uploaded | organization_id, source_id, type, uploaded_by, timestamp |
| source_indexed | organization_id, source_id, status, timestamp |
| question_asked | organization_id, user_id, question_id, timestamp |
| answer_generated | organization_id, question_id, answer_id, source_count, status, timestamp |
| answer_rated | organization_id, answer_id, rating, timestamp |
| escalation_created | organization_id, question_id, category, reason, timestamp |
| escalation_resolved | organization_id, escalation_id, resolved_by, timestamp |
| dashboard_viewed | organization_id, user_id, role, timestamp |

## Metric Guardrails

1. Do not count escalated sensitive questions as product failure automatically.
2. Do not claim exact cost savings unless customer confirms assumptions.
3. Label time-saved calculations as estimates.
4. Separate "answered" from "resolved."
5. Track unsupported questions because they create HR documentation value.

## Founder Decision

The MVP must include metrics from day one. Without ROI telemetry, the product becomes another assistant instead of an Operational AI platform.
