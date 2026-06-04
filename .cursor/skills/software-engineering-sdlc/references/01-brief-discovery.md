# Phase 01 · Brief & Discovery

## Purpose
Transform a vague idea or client request into a structured, actionable technical brief. This is where the most expensive bugs in software get created — not in code, but in assumptions.

---

## 1.1 Business Context Questions (Always Ask)

Before any technical discussion:

```
1. What specific problem does this solve? (Not "we need an app" — the actual pain)
2. Who are the users? (Personas, volumes, technical literacy)
3. What does success look like in 6 months? (KPIs, metrics)
4. What is the budget range? (Order of magnitude)
5. What is the hard deadline, if any?
6. Is there an existing system to replace or integrate with?
7. What compliance/regulatory requirements apply? (GDPR, HIPAA, PCI-DSS, SOC2...)
8. Who will maintain this after launch?
```

**Red flag**: A client/stakeholder who cannot answer questions 1, 2, and 3 is not ready to build software. Surface this immediately.

---

## 1.2 Technical Brief Template

```markdown
# Technical Brief: [Project Name]
**Version**: 1.0 | **Date**: YYYY-MM-DD | **Author**: [Name]

## Problem Statement
[One paragraph. What breaks today without this product?]

## Target Users
| Persona | Description | Volume (est.) | Technical Level |
|---------|-------------|---------------|-----------------|
| ...     | ...         | ...           | ...             |

## Business Objectives
- Primary: [Measurable outcome]
- Secondary: [Supporting goals]

## Functional Requirements (MVP)
### Must Have (P0)
- [ ] Feature A
- [ ] Feature B

### Should Have (P1)
- [ ] Feature C

### Nice to Have (P2 — Out of MVP scope)
- [ ] Feature D

## Non-Functional Requirements
| Category       | Requirement                          |
|----------------|--------------------------------------|
| Performance    | Page load < 2s / API response < 200ms |
| Availability   | 99.9% uptime (8.7h downtime/year)    |
| Scalability    | Support X concurrent users           |
| Security       | [Compliance framework if applicable] |
| Accessibility  | WCAG 2.1 AA minimum                  |

## Out of Scope (Explicit)
- [Item] — rationale
- [Item] — rationale

## Constraints
- Budget: [Range]
- Timeline: [Milestones]
- Team: [Composition]
- Technology: [Any mandated choices + why]

## Assumptions & Risks
| Assumption | Risk if Wrong | Mitigation |
|------------|---------------|------------|
| ...        | ...           | ...        |

## Success Metrics (Post-Launch)
- [ ] Metric 1: [Baseline → Target]
- [ ] Metric 2: [Baseline → Target]
```

---

## 1.3 MVP Definition Framework

Use this to push back on scope creep at the brief stage:

**MoSCoW Method Applied to Software**:
- **Must Have**: The product literally doesn't function without this
- **Should Have**: Important, but launch can happen without it
- **Could Have**: Nice UX, not blocking value delivery
- **Won't Have (this time)**: Explicitly deferred. Document it.

> Rule of thumb: If you're debating whether something is P0, it's P1.

---

## 1.4 Stakeholder Alignment Checklist

Before leaving discovery phase:

- [ ] Business owner has signed off on functional requirements
- [ ] MVP scope is documented and agreed upon
- [ ] "Out of scope" list is explicit and signed off
- [ ] Success metrics are defined and measurable
- [ ] Compliance requirements identified (or explicitly declared N/A)
- [ ] Budget and timeline expectations aligned
- [ ] Technical risks flagged to non-technical stakeholders in plain language

---

## 1.5 Common Discovery Failures (Learn From These)

| Failure Pattern | Consequence | Prevention |
|-----------------|-------------|------------|
| Building without knowing the user | Product nobody uses | User interviews before code |
| Undefined "done" criteria | Infinite scope | Written acceptance criteria |
| Skipping non-functional requirements | Performance disasters in prod | NFRs in brief, not afterthought |
| No explicit "out of scope" | Scope creep kills timelines | MoSCoW with sign-off |
| Compliance discovered late | Rearchitecting under pressure | Legal/compliance review in week 1 |
