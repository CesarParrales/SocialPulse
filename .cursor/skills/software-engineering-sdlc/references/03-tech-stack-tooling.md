# Phase 03 · Tech Stack & Tooling

## Purpose
Select the right tools for the job — not the trendiest. Every technology choice is a long-term commitment to a maintenance burden, hiring pool, and community ecosystem.

---

## 3.1 Stack Selection Decision Matrix

Score each option 1–5 on:

| Dimension | Description |
|-----------|-------------|
| **Team Familiarity** | Does the team know this today? |
| **Hiring Pool** | Can we hire for this in our market? |
| **Community / Ecosystem** | Active OSS, good libraries, Stack Overflow presence |
| **Performance** | Meets our NFRs? |
| **Operational Maturity** | Managed services available? Ops burden? |
| **Long-term Viability** | Still relevant in 3–5 years? (avoid hype cycles) |
| **Cost** | Licensing, cloud costs, developer productivity |

---

## 3.2 Production-Proven Stack Reference

### Web Frontend
| Use Case | Recommended | Notes |
|----------|-------------|-------|
| SPA / Complex UI | React + TypeScript | Largest ecosystem, safe long-term bet |
| Full-stack with SSR | Next.js | SEO requirements, performance |
| Content-heavy sites | Astro | Ship less JS, excellent performance |
| Internal tools | React or Vue | Vue is simpler for small teams |
| Mobile (cross-platform) | React Native or Flutter | Flutter growing fast; RN has more JS talent |

### Backend
| Use Case | Recommended | Notes |
|----------|-------------|-------|
| REST API (JS team) | Node.js + Fastify or Express | Fastify measurably faster than Express |
| REST API (type safety) | Node.js + TypeScript + Hono | Modern, lightweight |
| High performance API | Go (Gin/Echo) | Excellent for high-concurrency |
| Data-heavy / ML | Python + FastAPI | Fast, modern, async |
| Enterprise / complex domain | Java (Spring Boot) or Kotlin | Mature ecosystem, strong typing |
| Rapid prototyping | Ruby on Rails / Django | Still valid for MVPs; don't dismiss |

### Database
| Use Case | Recommended | Notes |
|----------|-------------|-------|
| General relational | PostgreSQL | Default answer. Nearly always correct. |
| High-write workloads | MySQL 8+ / PostgreSQL with tuning | Know your workload first |
| Document store | MongoDB | Only if document model genuinely fits |
| Key-value cache | Redis | Industry standard |
| Search | Elasticsearch / Typesense (lighter) | Typesense for simpler search needs |
| Analytics / OLAP | ClickHouse or BigQuery | Never use OLTP DB for analytics |
| Time-series | TimescaleDB (Postgres ext.) | IoT, metrics, events |

### Infrastructure
| Use Case | Recommended | Notes |
|----------|-------------|-------|
| Cloud (default) | AWS | Largest ecosystem, most managed services |
| Cloud (simpler DX) | GCP | Better ML services, Kubernetes native |
| Cloud (developer-friendly) | DigitalOcean / Fly.io | Smaller budget, simpler ops |
| Containers | Docker + Kubernetes | K8s only when you need it |
| Serverless | AWS Lambda / Vercel / Cloudflare Workers | Match to use case |
| IaC | Terraform or Pulumi | Terraform more mature; Pulumi for code-first |

---

## 3.3 Development Tooling Standards

### Always Include
```
□ Version control:       Git (mandatory, non-negotiable)
□ Branching strategy:    Documented (GitFlow, trunk-based, GitHub Flow)
□ Code formatting:       Prettier / Black / gofmt (language-appropriate)
□ Linting:               ESLint / Pylint / Golint
□ Pre-commit hooks:      Husky (JS) / pre-commit (Python)
□ Dependency management: Lock files committed (package-lock.json, poetry.lock)
□ Secret scanning:       git-secrets or gitleaks in CI
□ Dependency auditing:   npm audit / pip-audit / snyk in CI
```

### Recommended
```
□ API documentation:     OpenAPI/Swagger (auto-generated where possible)
□ Code review:           GitHub PRs or GitLab MRs with required approvals
□ Issue tracking:        Linear, Jira, or GitHub Issues (pick one, commit)
□ Documentation:         Notion, Confluence, or repo /docs
□ Internal comms:        Slack or Teams (not email for engineering)
```

---

## 3.4 Stack Anti-Patterns to Avoid

| Anti-Pattern | Why It Hurts |
|--------------|--------------|
| Polyglot microservices on a 3-person team | Ops overhead kills velocity |
| Choosing NoSQL to "scale later" | PostgreSQL scales to billions of rows with proper indexing |
| ORM everywhere including reporting queries | ORMs are for CRUD; raw SQL for complex analytics |
| Latest framework version on day 1 | Wait for patch releases; breaking changes are real |
| Reinventing auth | Use Clerk, Auth0, Supabase Auth, or Cognito. Auth is hard. |
| Self-hosting everything to save money | Engineer time costs more than managed services |
| JavaScript on the backend "because the team knows it" | Valid reason — but document the tradeoff |

---

## 3.5 The "Boring Technology" Principle

> "Choose boring technology." — Dan McKinley, ex-Etsy

Each "innovation token" spent on a non-standard technology is a real cost: training, debugging unknown issues, reduced hiring pool. Start with:

- **Postgres** before anything fancier
- **Monolith** before microservices  
- **REST** before GraphQL before gRPC
- **Managed cloud service** before self-hosted
- **Existing framework** before custom

Spend innovation tokens deliberately, on the things that actually differentiate your product.
