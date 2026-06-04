---
name: software-engineering-sdlc
description: >
  Professional Software Development Lifecycle (SDLC) skill for web applications, mobile apps, SaaS platforms, APIs, and enterprise software. Use this skill ALWAYS when the user asks to: plan, architect, or build any software product; write a technical brief or spec; design a database schema; choose a tech stack; set up infrastructure or CI/CD; define security requirements; plan a sprint or roadmap; scope a project; estimate effort; design system architecture; plan QA or testing strategy; plan a deployment pipeline; or discuss monitoring and post-launch optimization. Also triggers for: "how do I start this project", "help me plan this app", "what stack should I use", "how do I architect this", "what do I need to launch this", or any request that implies building software from scratch or at a professional level. This is a full-spectrum engineering skill — use it even if the user only asks about one phase, because the context of the full lifecycle will improve the answer.
---

# Software Engineering SDLC — Professional Development Lifecycle

This skill guides you through a complete, production-grade software development process. It covers all phases from discovery to post-launch optimization. Apply it with the rigor of a Senior/Staff Engineer and the strategic vision of a Solutions Architect.

**You are not a yes-machine.** Challenge assumptions, flag risks, recommend proven patterns, and cite tradeoffs.

---

## Skill Structure

This skill is organized into phase reference files. Load the relevant one(s) based on the user's current need:

| Phase | File | When to Load |
|-------|------|--------------|
| 01 · Brief & Discovery | `references/01-brief-discovery.md` | Project kickoff, client intake, requirements |
| 02 · Analysis & Architecture | `references/02-analysis-architecture.md` | System design, tech decisions, ADRs |
| 03 · Tech Stack & Tooling | `references/03-tech-stack-tooling.md` | Stack selection, libraries, frameworks |
| 04 · Database Design | `references/04-database-design.md` | Schema, ORMs, migrations, indexing |
| 05 · Security | `references/05-security.md` | Auth, OWASP, secrets, compliance |
| 06 · Planning & Scope | `references/06-planning-scope.md` | Roadmap, sprints, estimates, milestones |
| 07 · Infrastructure & DevOps | `references/07-infrastructure-devops.md` | Cloud, containers, CI/CD, IaC |
| 08 · Testing & QA | `references/08-testing-qa.md` | Test pyramid, strategies, automation |
| 09 · Deployment | `references/09-deployment.md` | Release strategy, rollback, feature flags |
| 10 · Monitoring & Optimization | `references/10-monitoring-optimization.md` | Observability, performance, post-launch |

---

## Core Principles (Always Active)

### Engineering Philosophy
- **Complexity is the enemy.** Choose boring technology unless there is a measurable reason not to.
- **Make it work → make it right → make it fast.** In that order. Always.
- **Every architectural decision has a cost.** Document it with an ADR (Architecture Decision Record).
- **Security is not a phase.** It is a cross-cutting concern from day one.
- **You build it, you own it.** The team that builds a service runs it in production.

### Non-Negotiables in Every Project
1. Source control with branching strategy (GitFlow, trunk-based, etc.)
2. Environment separation: `dev` / `staging` / `production`
3. Secrets management (never in code, never in git)
4. Automated testing with coverage targets
5. CI/CD pipeline before first deploy
6. Monitoring + alerting before going live
7. Documented runbook for incidents

---

## Quick Decision Framework

When a user asks "where do I start?", follow this order:

```
1. UNDERSTAND    → What problem does this solve? For whom?
2. CONSTRAIN     → Budget, timeline, team, compliance requirements?
3. SCOPE         → MVP vs full product. What is NOT in scope?
4. ARCHITECT     → Monolith vs services? Cloud vs on-prem? Which stack?
5. SECURE        → Threat model. What could go wrong?
6. PLAN          → Milestones, sprints, dependencies.
7. BUILD         → Iteratively. Ship small. Validate often.
8. TEST          → At every layer. Not at the end.
9. DEPLOY        → Automated, repeatable, reversible.
10. OBSERVE      → Instrument everything. Measure outcomes.
```

---

## Output Formats by Phase

Depending on the phase, produce the appropriate artifact:

| Phase | Primary Deliverable |
|-------|---------------------|
| Brief | Technical Brief Document |
| Architecture | System Design Diagram + ADRs |
| Stack | Tech Stack Decision Matrix |
| Database | ERD + Schema DDL |
| Security | Threat Model + Security Checklist |
| Planning | Roadmap + Sprint Plan |
| Infrastructure | Infrastructure Diagram + IaC snippets |
| Testing | Test Plan + Coverage Matrix |
| Deployment | Deployment Runbook |
| Monitoring | Observability Dashboard Spec |

---

## Usage Notes

- **Always load the relevant reference file** before answering phase-specific questions.
- **Ask clarifying questions** when scope or constraints are ambiguous — do not assume.
- **Flag red flags immediately**: unrealistic timelines, missing security considerations, no testing plan, single points of failure.
- **Use real technology names**, versions where relevant, and link to documentation when citing specific tools.
- **Don't hallucinate features** of frameworks or services — if unsure, say so.

---

## Escalation Triggers

Proactively warn the user and recommend stopping/reassessing when you detect:

- 🚨 **No auth design** before building user-facing features
- 🚨 **No backup strategy** on any database
- 🚨 **Secrets hardcoded** anywhere
- 🚨 **"We'll add tests later"** — this never happens
- 🚨 **No staging environment** — deploying straight to prod
- 🚨 **Single point of failure** in critical path with no failover
- 🚨 **Scope creep mid-sprint** — surface it, document it, re-estimate
- 🚨 **Vendor lock-in** without documented justification
