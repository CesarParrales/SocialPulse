# Phase 10 · Monitoring & Optimization

## Purpose
You cannot improve what you cannot measure. Post-launch is where you learn if you built the right thing, and whether it runs the way you think it does. Observability is an engineering capability, not a DevOps afterthought.

---

## 10.1 The Three Pillars of Observability

### 1. Logs
What happened and when.

```
Log levels (use correctly):
ERROR:   Something broke. Needs human attention.
WARN:    Something unexpected happened, system recovered. Monitor trend.
INFO:    Normal operation events. Deployments, user actions, key milestones.
DEBUG:   Development only. Never in production at scale.

Log rules:
□ Structured logging (JSON format) — not string concatenation
□ Include: timestamp, level, service, requestId, userId (non-PII)
□ NEVER log: passwords, tokens, credit card numbers, PII
□ Centralized log aggregation: Datadog, ELK, CloudWatch Logs, Loki

Example (structured):
{
  "timestamp": "2025-01-15T10:30:00Z",
  "level": "error",
  "service": "checkout-api",
  "requestId": "req_abc123",
  "userId": "user_xyz",
  "error": "Payment gateway timeout",
  "duration_ms": 5032
}
```

### 2. Metrics
How the system is performing over time.

**Golden Signals (Google SRE Framework)**:
```
Latency:      How long does it take to serve a request?
Traffic:      How many requests per second?
Errors:       What percentage of requests fail?
Saturation:   How full is the system? (CPU, memory, queue depth)
```

**SLI/SLO/SLA**:
```
SLI (Service Level Indicator): Actual measured metric
  Example: "95th percentile API response time"

SLO (Service Level Objective): Internal target
  Example: "p95 response time < 300ms, 99.9% of the time"

SLA (Service Level Agreement): External commitment to customers
  Example: "99.5% uptime, or service credits apply"

Rule: SLA ≤ SLO ≤ 100%
      Always set SLA worse than your SLO (you need headroom)
```

### 3. Traces
How a request flows through the system.

```
Distributed tracing tools: Jaeger, Zipkin, Datadog APM, OpenTelemetry
Use when: Microservices, async processing, debugging latency spikes

Implement OpenTelemetry from day one — it's vendor-neutral and
lets you switch backends without code changes.
```

---

## 10.2 Alerting Strategy

```
Alert on symptoms, not causes.

Good alert: "User-facing error rate > 1% for 5 minutes" → Pages someone now
Bad alert:  "CPU > 80%" → Usually not actionable, causes alert fatigue

Alert principles:
□ Every alert must be actionable
□ Every alert has a runbook
□ Alert fatigue kills incident response — prune aggressively
□ Route by severity: P1/P2 to PagerDuty, P3/P4 to Slack

Minimum alert set for production:
□ Error rate > 1% (5-minute window)
□ p95 latency > [2x baseline]
□ Service health check failing
□ Database CPU > 80% sustained
□ Disk usage > 80%
□ SSL certificate expiring < 30 days
□ Failed background jobs above threshold
□ Significant drop in key business metric (orders, signups — anomaly detection)
```

---

## 10.3 Observability Stack Options

| Budget | Stack |
|--------|-------|
| **Startup / Low Cost** | Grafana Cloud (free tier) + Loki (logs) + Prometheus (metrics) |
| **Mid-Market** | Datadog (all-in-one, powerful, expensive at scale) |
| **Mid-Market Alt** | New Relic (good full-stack observability) |
| **AWS-Native** | CloudWatch + X-Ray + AWS Managed Prometheus |
| **Self-Hosted** | Prometheus + Grafana + Loki + Jaeger (full control, ops burden) |
| **Error Tracking** | Sentry (free tier available, excellent DX) |

---

## 10.4 Performance Optimization Framework

**Measure before optimizing. Every time. Without exception.**

```
Optimization workflow:
1. Measure → What is actually slow? (Use profiler, not intuition)
2. Profile → Where is the time being spent? (CPU, DB, network, locks?)
3. Hypothesize → Root cause theory
4. Fix → Smallest change that addresses root cause
5. Measure → Did it improve? By how much?
6. Document → What changed and why (ADR if significant)
```

### Common Backend Performance Issues

```
Database:
□ N+1 queries (detect with: Bullet gem, Hibernate stats, query logging)
□ Missing indexes (use EXPLAIN ANALYZE)
□ Fetching more data than needed (SELECT * — never in production)
□ Missing pagination on large datasets
□ Synchronous DB calls that could be parallelized

Application:
□ Blocking I/O in async context
□ Uncached repeated computations
□ Memory leaks (profile with heap dumps)
□ Inefficient data serialization

Network:
□ Too many round trips (batch requests)
□ No CDN for static assets
□ Missing HTTP/2 multiplexing
□ Large payloads (compress, paginate, lazy-load)
```

### Frontend Performance Targets (Core Web Vitals)
```
LCP (Largest Contentful Paint):   < 2.5s  ← How fast does it feel loaded?
INP (Interaction to Next Paint):  < 200ms ← How responsive is it?
CLS (Cumulative Layout Shift):    < 0.1   ← Does layout jump around?

Tools: Lighthouse, WebPageTest, Chrome DevTools Performance panel
```

---

## 10.5 Post-Launch Review Cadence

```
Day 7:    First week review
  - Error rates vs baseline
  - Performance vs pre-launch benchmarks
  - User feedback themes
  - Any unexpected costs

Month 1:  First month review
  - Feature adoption vs projections
  - Infrastructure costs vs budget
  - Top 3 user pain points
  - Tech debt backlog review

Quarterly: Engineering health review
  - SLO compliance
  - Incident count and severity trends
  - Deployment frequency
  - MTTR (Mean Time to Recovery)
  - Test coverage trends
  - Dependency vulnerability status
  - Infrastructure cost optimization opportunities
```

---

## 10.6 DORA Metrics (Engineering Team Health)

The four metrics that best predict software delivery performance (DevOps Research and Assessment):

| Metric | Definition | Elite | High | Medium | Low |
|--------|------------|-------|------|--------|-----|
| **Deployment Frequency** | How often you deploy to prod | Multiple/day | Weekly | Monthly | < Monthly |
| **Lead Time for Changes** | PR merge → production | < 1 hour | < 1 day | < 1 week | > 1 month |
| **Change Failure Rate** | % of deploys causing incidents | < 5% | < 10% | 10–15% | > 15% |
| **MTTR** | Time to recover from incident | < 1 hour | < 1 day | < 1 week | > 1 week |

> Measure these quarterly. High-performing engineering teams deploy frequently AND recover quickly. The correlation is not coincidental — both require automation, testing, and operational maturity.
