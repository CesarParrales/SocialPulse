# Phase 09 · Deployment

## Purpose
Deployment is not the finish line — it is the beginning of the feedback loop. Every deployment must be safe, automated, auditable, and reversible.

---

## 9.1 Deployment Readiness Checklist

Before any production deployment:

```
Code Quality:
□ All CI checks passing
□ PR reviewed and approved
□ No TODO/FIXME in critical paths
□ No known unresolved issues in scope of release

Infrastructure:
□ Staging deployment successful
□ Smoke tests passing on staging
□ DB migrations tested on staging data copy
□ Config/environment variables verified for production

Operations:
□ Monitoring and alerts active
□ On-call engineer aware of deployment
□ Rollback procedure documented
□ Deployment window communicated (avoid Friday afternoons)
□ Feature flags configured if using canary/gradual rollout
```

---

## 9.2 Release Strategies

### Rolling Update
```
How: Replace instances gradually, one at a time.
Pros: Simple, no extra infrastructure cost.
Cons: Temporary mixed-version state, rollback requires another deploy.
Use when: Low-risk changes, quick deployments.
```

### Blue-Green Deployment
```
How:
  Blue  = current production (live)
  Green = new version (warm, idle)
  → Switch load balancer from Blue → Green
  → Blue becomes standby for rollback

Pros: Instant rollback (switch back), zero downtime.
Cons: 2x infrastructure cost during transition.
Use when: High-risk changes, need clean rollback option.
```

### Canary Release
```
How: Route X% of traffic to new version. Monitor. Increase gradually.
  5% → monitor for 30 min → 25% → monitor → 50% → 100%

Pros: Real production validation with limited blast radius.
Cons: Requires feature flag infrastructure or traffic splitting.
Use when: Major changes with uncertainty, data migrations, performance-sensitive features.
```

### Feature Flags (Recommended for all teams)
```
Libraries: LaunchDarkly, Unleash (self-hosted), GrowthBook (OSS), Flagsmith

Benefits:
- Deploy code without activating features
- Rollback a feature without a code deploy
- A/B test features
- Gradual rollout by user segment

Example:
const showNewCheckout = await flags.isEnabled('new-checkout-flow', { userId: user.id })
```

---

## 9.3 Database Migration Safety

This is where most deployment incidents originate. Follow this strictly:

```
Rule 1: Migrations must be backward-compatible.
        The old code version must still work during a deploy.

Rule 2: Never combine code changes and destructive migrations in one deploy.

Safe migration sequence:
Deploy N:   Add new_column (nullable, no default)
Deploy N:   Code writes to BOTH old_column and new_column
Deploy N+1: Backfill new_column for existing rows
Deploy N+1: Code reads from new_column only
Deploy N+2: DROP old_column (safe, no code references it)

Large table migrations:
□ Add index CONCURRENTLY (PostgreSQL) — non-blocking
□ Use pg_repack or online schema change tools for large table alterations
□ Batch large data backfills (don't UPDATE 10M rows in one transaction)
□ Test migration duration on staging with production-sized data
```

---

## 9.4 Deployment Runbook Template

Every service must have a runbook. No exceptions.

```markdown
# Deployment Runbook: [Service Name]

## Pre-Deployment
1. Verify CI is green: [CI URL]
2. Check staging health: [Staging URL]/health
3. Notify #deployments channel: "Deploying [service] [version] to prod"
4. Check current error rate baseline: [Dashboard URL]

## Deployment Steps
1. `git tag v[X.Y.Z] -m "Release [X.Y.Z]: [brief description]"`
2. `git push origin v[X.Y.Z]`
3. Monitor GitHub Actions: [CI URL]
4. Verify staging auto-deploy succeeded
5. Trigger production deploy: [Command or UI step]
6. Monitor rollout: [Deployment dashboard URL]

## Post-Deployment Validation (10 minutes)
1. Check health endpoint: `curl https://api.example.com/health`
2. Verify error rate hasn't spiked: [Dashboard URL]
3. Run smoke test suite: `npm run smoke:prod`
4. Verify key user flows manually
5. Check database connection pool: [DB dashboard]

## Rollback Procedure
Option A (Feature Flag): Toggle off [flag name] in [LaunchDarkly/Unleash URL]
Option B (Code Rollback):
1. `kubectl rollout undo deployment/[service-name]`  OR
2. Revert in CI/CD: [Platform UI step]
3. Verify rollback complete: check `/health` endpoint
4. Notify team: "Rolled back [service] to [previous version] — investigating"

## Escalation
- Primary on-call: [Name, Slack handle, phone]
- Backup: [Name, Slack handle]
- Incident channel: #incidents
```

---

## 9.5 Post-Deployment Monitoring Window

```
First 5 minutes:
□ Error rate nominal (baseline ± 10%)
□ Response time nominal (p95 within SLA)
□ Health checks all green
□ No spike in 5xx responses

First 30 minutes:
□ CPU/memory stable
□ Database connection pool not exhausted
□ No anomalies in business metrics (orders, signups, etc.)
□ No alerts firing

First 24 hours:
□ No degradation in user-reported metrics
□ No unexpected batch job failures
□ Log volume within normal range
□ Support tickets not spiking
```

---

## 9.6 Incident Response Basics

```
Severity levels:
P1 - Critical: Production down, data loss, security breach → 15 min response
P2 - High:     Major feature broken, significant user impact → 1 hour response
P3 - Medium:   Feature degraded, workaround exists → Next business day
P4 - Low:      Minor issue, minimal impact → Next sprint

Incident process:
1. Detect (monitoring alerts, user report)
2. Acknowledge (claim it, post in #incidents)
3. Assess (what's broken? how many users? is it getting worse?)
4. Mitigate (fastest path to stopping the bleeding — rollback, feature flag, scale)
5. Resolve (fix root cause)
6. Post-mortem (within 48 hours, blameless, document learnings)
```
