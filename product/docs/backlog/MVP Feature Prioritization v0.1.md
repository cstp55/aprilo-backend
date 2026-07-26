# MVP Feature Prioritization v0.1

## Product

AI HR Knowledge & Workflow Assistant

## Decision Summary

The MVP should prioritize a narrow, trust-first pilot:

Employee HR Policy Q&A with source citations, safe escalation, and ROI measurement.

This is the highest-confidence first workflow because it can be tested with customer documents, does not require payroll or HRMS write access, reduces repetitive HR support, and proves the core Operational AI thesis.

## Prioritization Method

Features are prioritized by five criteria:

1. Customer pain intensity.
2. Speed to build.
3. Trust and security risk.
4. ROI measurability.
5. Reuse across future platform capability packs.

Priority levels:

| Priority | Meaning |
|---|---|
| P0 | Required for first usable pilot |
| P1 | Important for private beta or paid pilot |
| P2 | Useful after first validation |
| P3 | Future / not part of MVP |

## P0: First Usable Pilot

These features must exist before a real design partner pilot.

| Feature | Why It Is P0 | Acceptance Signal |
|---|---|---|
| Organization workspace | Required for customer data separation | Each customer has isolated workspace |
| Admin login | Required for setup and knowledge control | HR admin can access admin area |
| Employee login or controlled access | Required for pilot users | Employees can ask questions securely |
| Basic roles | Required for trust | Employee and HR admin have different permissions |
| Document upload | Required to use customer HR policy sources | Admin can upload PDF/DOCX/TXT/Markdown |
| Source indexing | Required for Q&A | Uploaded sources become searchable |
| Source-cited HR answers | Core product value | Answer includes source title/reference |
| "I cannot confirm" fallback | Prevents hallucinated policy | Assistant refuses unsupported answers |
| Sensitive topic handling | Required for HR safety | Sensitive topics escalate or redirect to HR |
| Escalation queue | Required when AI cannot answer safely | HR admin sees unresolved questions |
| Feedback button | Required for quality signal | Employee marks helpful/not helpful |
| Interaction log | Required for audit and improvement | Admin can review question, answer, source, status |
| Basic ROI dashboard | Required to prove value | Shows questions, resolved, escalated, estimated time saved |

## P1: Private Beta / Paid Pilot

These should be added once the P0 workflow works.

| Feature | Why It Matters | Timing |
|---|---|---|
| Knowledge gap report | Helps HR improve missing documentation | Private beta |
| Admin FAQ entries | Lets HR quickly fix repeated missing answers | Private beta |
| Escalation categories | Improves workflow reporting | Private beta |
| Configurable time-saved assumption | Makes ROI dashboard customer-specific | Private beta |
| CSV export | Helps customer share ROI internally | Paid pilot |
| Slack or Microsoft Teams interface | Meets employees where they work | Paid pilot if customer strongly requests |
| Helpdesk ticket creation | Makes escalation operational | Paid pilot if customer uses helpdesk |

## P2: Post-Pilot Expansion

These features are valuable but should wait until the core pilot proves value.

| Feature | Reason to Wait |
|---|---|
| HRMS read integration | Useful, but document-based pilot can validate value first |
| Manager-specific answer paths | Needs better access model and customer org mapping |
| Multilingual support | Important later, but first proof should validate workflow value |
| Advanced analytics | Premature before usage volume exists |
| Browser widget / intranet embed | Useful distribution surface after core UX works |

## P3: Future / Out of MVP

These should not be built before customer validation.

| Feature | Reason Not Now |
|---|---|
| HRMS write actions | Higher risk, needs deeper permissions and auditability |
| Leave approval automation | Sensitive and platform-specific |
| Payroll automation | High-risk and out of MVP scope |
| Benefits enrollment automation | Sensitive, compliance-heavy |
| Performance review automation | Sensitive and trust-heavy |
| Full no-code agent builder | Conflicts with zero-configuration product thesis |
| Capability marketplace | Only valuable after internal packs are proven |

## Recommended MVP Cut

### Build This First

1. Workspace.
2. Admin and employee access.
3. Basic roles.
4. Document upload.
5. Source indexing.
6. HR Q&A with source citation.
7. Safe fallback and sensitive topic escalation.
8. HR escalation queue.
9. Feedback.
10. Audit log.
11. Basic ROI dashboard.

### Do Not Build Yet

1. HRMS write actions.
2. Payroll, benefits, or legal decisioning.
3. Complex integrations.
4. Marketplace.
5. Broad multi-industry capability packs.

## First Build Milestones

| Milestone | Outcome | Exit Criteria |
|---|---|---|
| M1: Internal Alpha | Founder can upload policies and ask questions | Source-cited answers work on test documents |
| M2: Trust Alpha | Assistant handles low-confidence and sensitive topics | Unsafe/sensitive questions escalate |
| M3: Pilot Admin | HR admin can review sources, logs, escalations, and feedback | Admin can manage pilot loop |
| M4: ROI Alpha | Dashboard shows basic resolution and time-saved estimate | Founder can show value story |
| M5: Design Partner Pilot | Real customer uses assistant with employees | 30-day pilot metrics collected |

## Founder Decision

The first MVP should be intentionally small:

HR Policy Q&A + Escalation + ROI.

This proves the operational AI foundation before expanding into workflow automation, HRMS integrations, ecommerce, ERP, and CRM.
