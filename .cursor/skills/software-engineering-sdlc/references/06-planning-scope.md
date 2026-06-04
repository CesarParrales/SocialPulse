# Phase 06 · Planning & Scope

## Purpose
Translate requirements and architecture into a realistic, trackable execution plan. The goal is not a perfect Gantt chart — it's shared understanding of what gets built, in what order, by whom, and when.

---

## 6.1 Project Sizing (T-Shirt Sizing)

Before committing to dates, estimate relative complexity:

| Size | Story Points | Duration (1 dev) | Examples |
|------|--------------|------------------|----------|
| XS | 1 | < 2 hours | Bug fix, copy change, config tweak |
| S | 2–3 | 1/2 day | Simple CRUD endpoint, minor UI change |
| M | 5 | 1–2 days | Feature with DB + API + UI |
| L | 8 | 3–5 days | Complex feature, multiple integrations |
| XL | 13 | 1–2 weeks | Sub-system, major refactor |
| Epic | 20+ | Break it down | This is not a story, it's a project |

> **If you can't size it, you don't understand it yet.** Spike first (timebox: 1–2 days), then size.

---

## 6.2 Milestone Planning Template

```markdown
# [Project Name] — Release Plan

## Milestone 0: Foundation (Week 1–2)
Goal: Working skeleton. Nothing impressive, but all layers connected.
- [ ] Repo setup, CI/CD pipeline
- [ ] Auth working end-to-end
- [ ] Database migrations running
- [ ] Staging environment live
- [ ] Hello World deployed

## Milestone 1: MVP Core (Week 3–N)
Goal: Core value loop working for first user persona.
- [ ] [Core Feature A]
- [ ] [Core Feature B]
- [ ] Basic error handling
- [ ] Basic logging

## Milestone 2: MVP Complete (Week N+1 to N+M)
Goal: Shippable to beta users.
- [ ] [Remaining P0 features]
- [ ] Onboarding flow
- [ ] Basic monitoring live
- [ ] Security review done

## Milestone 3: Launch
Goal: Public availability.
- [ ] All P1 features
- [ ] Performance tested
- [ ] Backup/restore verified
- [ ] Runbook written
- [ ] Support workflow defined
```

---

## 6.3 Sprint Structure (Agile / Scrum Reference)

**Sprint length**: 1–2 weeks. 2 weeks is standard. 1 week for high-uncertainty projects.

**Ceremonies (minimum viable)**:
```
Sprint Planning:    Define sprint goal, select stories, task-break to < 1 day items
Daily Standup:      15 min max. Three questions: done, doing, blocked.
Sprint Review:      Demo working software. Not slides. Not screenshots. Working software.
Retrospective:      What worked? What didn't? One concrete action item.
```

**Capacity planning**:
```
Available hours per dev per sprint = Sprint days × 6 hours (not 8 — meetings, reviews, context switching)
Team capacity = Sum of individual capacities
Never plan to 100% capacity. 70–80% to allow for the unexpected.
```

---

## 6.4 Backlog Prioritization Framework

Use **WSJF (Weighted Shortest Job First)** for engineering precision, or **ICE (Impact × Confidence × Ease)** for simplicity:

### ICE Score
```
Impact:     1–10 (How much does this move the needle on our goal?)
Confidence: 1–10 (How sure are we Impact is right?)
Ease:       1–10 (How easy is it to implement? 10 = trivial)

ICE = Impact × Confidence × Ease
```

### WSJF (SAFe)
```
Cost of Delay = User Value + Time Criticality + Risk Reduction
WSJF = Cost of Delay / Job Size
```

---

## 6.5 Definition of Done (DoD)

Every team must define "done" explicitly. Suggested baseline:

```
A story is DONE when:
□ Code written and reviewed (PR approved by ≥1 peer)
□ Unit tests written (coverage meets threshold)
□ Integration tests updated if applicable
□ No new lint warnings or type errors
□ Deployed to staging and smoke-tested
□ Acceptance criteria verified by author
□ Documentation updated (if public-facing)
□ PM/stakeholder sign-off if required
```

---

## 6.6 Scope Creep Defense

The most expensive thing in software development is building the wrong thing. Second most expensive: building undiscussed features during a sprint.

```
Scope creep indicators:
- "While you're in there, can you also..."
- Mid-sprint requirement changes without estimation
- "It's a small thing" (it never is)
- "The client really wants it by tomorrow"

Response playbook:
1. Log it in the backlog (never lose the request)
2. Estimate it
3. Trade it for something of equal size in the sprint, OR
4. Defer it to the next sprint
5. Document the decision

The sprint goal is a contract. Changing it mid-sprint has a cost.
```

---

## 6.7 Risk Register Template

```markdown
| # | Risk | Probability | Impact | Score | Mitigation | Owner |
|---|------|-------------|--------|-------|------------|-------|
| 1 | Third-party API instability | Medium | High | 6 | Mock + fallback | BE Lead |
| 2 | Key engineer unavailability | Low | High | 4 | Docs + pair programming | PM |
| 3 | Scope expansion | High | Medium | 6 | Strict DoD + backlog process | PM |
```

Score = Probability (1–3) × Impact (1–3)
