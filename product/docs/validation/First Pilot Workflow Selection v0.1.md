# First Pilot Workflow Selection v0.1

## Decision

The first pilot workflow should be:

Employee HR Policy Q&A with source citations, safe escalation, and ticket-deflection measurement.

## Why This Workflow Comes First

This workflow is the best first pilot because it has the strongest balance of customer pain, low implementation risk, measurable ROI, and platform reuse.

| Criteria | HR Policy Q&A | Onboarding Support | HR Ticket Deflection | HRMS Write Actions |
|---|---:|---:|---:|---:|
| Customer pain | High | High | High | Medium-High |
| Build speed | High | Medium | Medium | Low |
| Trust risk | Low-Medium | Medium | Medium | High |
| Integration complexity | Low | Medium | Medium | High |
| ROI measurability | Medium-High | Medium | High | High |
| Source availability | High | Medium | Medium | Low-Medium |
| Pilot readiness | Very High | Medium | Medium-High | Low |
| Recommendation | Start here | Add later | Measure through Q&A | Not MVP |

## Pilot Workflow Definition

### Workflow Name

HR Policy Q&A and Escalation.

### Primary User

Employee.

### Primary Buyer

HR leader, operations leader, founder/CEO, or IT-supported HR team.

### Admin Owner

HR admin or HR generalist.

### Trigger

Employee has an HR question that would normally be sent to HR by chat, email, portal, or helpdesk.

### Example Questions

- What is our leave policy?
- How many paid leaves do employees get?
- What is the remote work policy?
- How do I request work-from-home?
- Where can I find the reimbursement policy?
- What documents do I need during onboarding?
- Who should I contact for benefits questions?

### Assistant Flow

1. Employee asks HR question.
2. Assistant checks user role and approved sources.
3. Assistant retrieves relevant policy content.
4. Assistant answers with source citation.
5. If confidence is low, assistant asks clarification or escalates.
6. If topic is sensitive, assistant gives safe response and routes to HR.
7. Employee marks helpful/not helpful.
8. Interaction is logged.
9. Dashboard records resolved question, escalation, and estimated time saved.

### HR Admin Flow

1. HR admin uploads approved HR documents.
2. HR admin reviews unanswered questions.
3. HR admin resolves escalations.
4. HR admin identifies repeated topics and missing documentation.
5. HR admin reviews weekly ROI dashboard.

## Pilot Scope

### Included

- Approved HR policy documents.
- Employee handbook.
- FAQ entries.
- HR policy Q&A.
- Source citations.
- Safe fallback.
- Sensitive topic escalation.
- Helpful/not helpful feedback.
- Admin escalation queue.
- Basic ROI dashboard.

### Excluded

- Payroll records.
- Medical records.
- Performance reviews.
- Employee investigations.
- HRMS write actions.
- Leave approval automation.
- Benefits enrollment automation.
- Legal or immigration advice.

## Data Required From Pilot Customer

Minimum:

1. Employee handbook.
2. Leave policy.
3. Remote work policy.
4. Onboarding checklist or onboarding guide.
5. Benefits overview, if non-sensitive.
6. Expense/reimbursement policy, if available.
7. HR contact/escalation owner.

Optional:

1. Top 20 repeated HR questions.
2. Anonymized sample HR tickets.
3. Current weekly HR question volume.
4. Average HR time spent per repeated question.

## Pilot Success Metrics

| Metric | Target | Why It Matters |
|---|---:|---|
| Employee questions asked | 50+ in first 30 days | Shows usage |
| Resolution rate | 50%+ without HR escalation | Shows operational value |
| Helpful rating | 70%+ on rated answers | Shows trust and usefulness |
| Escalation rate | Tracked, not minimized at all costs | Escalation is safer than guessing |
| Estimated time saved | 5-10 minutes per resolved repetitive question | Converts usage into ROI |
| Knowledge gaps identified | 10+ meaningful gaps | Shows admin value |
| Trust incidents | 0 high-severity incidents | Required for HR adoption |

## Pilot Pricing Hypothesis

For first design partners, use one of three options:

| Option | Price | When To Use |
|---|---:|---|
| Free design partner | $0 for 30 days | Only if customer gives deep access, weekly feedback, and testimonial option |
| Paid pilot light | $500-$1,500 for 30 days | Best default for SMB validation |
| Paid pilot serious | $2,000-$5,000 for 60 days | Use for larger company or heavier setup |

Recommendation:

Default to paid pilot light unless the customer is strategically valuable.

## First Pilot Customer Criteria

Ideal first pilot customer:

- 50-300 employees.
- Has HR documents ready.
- Receives repeated HR questions weekly.
- Uses Slack, Teams, email, or helpdesk for HR questions.
- Has an HR owner who will review answers.
- Accepts document-only MVP without HRMS write access.
- Can meet weekly during the pilot.

Avoid first pilot customer:

- Demands payroll or leave approval automation.
- Has no organized HR knowledge.
- Requires long procurement.
- Requires SOC 2 before any pilot.
- Wants broad custom workflows outside HR.

## 30-Day Pilot Plan

### Week 0: Setup

- Collect HR documents.
- Define pilot users.
- Configure workspace.
- Upload and index sources.
- Agree on sensitive topics.
- Agree on baseline metrics.

### Week 1: Controlled Launch

- HR admin and small employee group test questions.
- Fix source gaps.
- Review unsafe or low-confidence responses.
- Confirm escalation flow.

### Week 2: Employee Pilot

- Expand to selected employee group.
- Track questions, resolutions, escalations, and helpfulness.
- HR admin reviews top repeated topics.

### Week 3: Optimization

- Add missing FAQ entries.
- Improve source quality.
- Review knowledge gaps.
- Tune escalation categories.

### Week 4: ROI Review

- Calculate questions resolved.
- Estimate HR time saved.
- Review trust incidents.
- Decide conversion or next pilot stage.

## Founder Decision

The first pilot should not wait for HRMS API integration.

Start with approved HR documents, employee questions, source-grounded answers, safe escalation, and ROI reporting. HRMS integration becomes a later unlock after the workflow value is proven.
