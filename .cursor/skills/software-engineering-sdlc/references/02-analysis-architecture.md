# Phase 02 · Analysis & Architecture

## Purpose
Transform requirements into a system design that is buildable, scalable, maintainable, and secure. The output of this phase dictates 80% of your future problems.

---

## 2.1 Architecture Patterns — When to Use What

| Pattern | Use When | Avoid When |
|---------|----------|------------|
| **Monolith** | Team < 8, startup, first version, tight coupling OK | Scaling requirements demand independent deploys |
| **Modular Monolith** | Growing team, wants modularity without ops overhead | You need polyglot services |
| **Microservices** | Large teams, independent scaling, complex domains | You don't have DevOps maturity to support it |
| **Serverless** | Event-driven, variable load, low ops budget | Long-running processes, latency-sensitive |
| **BFF (Backend for Frontend)** | Multiple clients (web/mobile/TV) with different data needs | Single client type |
| **Event-Driven / CQRS** | High write-read asymmetry, audit trails, eventual consistency OK | Simple CRUD apps |

> **Hot take backed by data**: 83% of teams that start with microservices wish they'd started with a monolith. (Source: multiple engineering retrospectives at Shopify, Stack Overflow, Amazon's own teams.) Start simple. Decompose when you have evidence to do so.

---

## 2.2 System Design Checklist

For every component, answer:

```
□ What is its single responsibility?
□ What does it depend on? (upstream)
□ What depends on it? (downstream)
□ What happens if it fails?
□ How does it scale?
□ How is it monitored?
□ What does a deploy look like?
```

---

## 2.3 Architecture Decision Record (ADR) Template

Every significant architectural decision gets an ADR. No exceptions.

```markdown
# ADR-[NNN]: [Decision Title]

**Date**: YYYY-MM-DD
**Status**: Proposed | Accepted | Deprecated | Superseded by ADR-XXX

## Context
[What situation forced this decision? What constraints exist?]

## Decision
[What was decided, in one clear sentence.]

## Rationale
[Why this option over alternatives? Include data or benchmarks if available.]

## Alternatives Considered
| Option | Pros | Cons | Why Rejected |
|--------|------|------|--------------|
| ...    | ...  | ...  | ...          |

## Consequences
**Positive**: [What gets easier/better]
**Negative**: [What gets harder/worse — be honest]
**Risks**: [What could go wrong, likelihood, mitigation]

## Review Date
[When should this decision be revisited?]
```

---

## 2.4 System Design Diagram Components

Always produce a diagram that includes:

1. **Client Layer**: Browser, mobile app, third-party consumers
2. **Edge Layer**: CDN, WAF, Load Balancer, API Gateway
3. **Application Layer**: Services, workers, schedulers
4. **Data Layer**: Primary DB, read replicas, cache, object storage, search
5. **Async Layer**: Message queues, event buses
6. **External Services**: Auth providers, payment processors, email, analytics
7. **Observability**: Log aggregation, metrics, tracing

---

## 2.5 Scalability Design Checklist

- [ ] Stateless application layer (sessions in Redis/DB, not server memory)
- [ ] Database connection pooling configured
- [ ] Cache strategy defined (L1 in-process, L2 Redis, L3 CDN)
- [ ] Async processing for operations > 500ms
- [ ] Rate limiting on public endpoints
- [ ] Pagination on all list endpoints (no `SELECT *` in prod)
- [ ] CDN for static assets
- [ ] Horizontal scaling possible without re-architecture

---

## 2.6 API Design Standards

### REST
- Use nouns, not verbs in URLs: `/users/123/orders` not `/getUserOrders`
- HTTP verbs mean what they say: GET (read), POST (create), PUT (replace), PATCH (update), DELETE (remove)
- Versioning strategy decided upfront: URI (`/v1/`) or header (`API-Version: 1`)
- Consistent error envelope:
```json
{
  "error": {
    "code": "RESOURCE_NOT_FOUND",
    "message": "User 123 not found",
    "requestId": "req_abc123",
    "timestamp": "2025-01-15T10:30:00Z"
  }
}
```

### GraphQL (when appropriate)
- Use when clients have highly variable data requirements
- Add query depth limiting and complexity analysis
- Never in a public API without authentication + rate limiting

### gRPC (when appropriate)
- Internal service-to-service communication
- High-throughput binary protocol
- Requires schema discipline (protobuf)

---

## 2.7 Integration Patterns

| Pattern | Use Case |
|---------|----------|
| **Synchronous REST/gRPC** | Real-time response required |
| **Async Queue (SQS, RabbitMQ)** | Decoupled processing, retryable |
| **Event Bus (Kafka, SNS)** | Fan-out to multiple consumers |
| **Webhooks** | Third-party notifications, partner integrations |
| **Polling** | Last resort — when push is impossible |
