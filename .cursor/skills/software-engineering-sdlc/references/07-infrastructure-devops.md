# Phase 07 · Infrastructure & DevOps

## Purpose
Design and automate the infrastructure that runs the application reliably, at cost, with the ability to recover from failure and ship changes safely. "It works on my machine" is not an architecture.

---

## 7.1 Environment Strategy

**Minimum viable environment set**:

```
local       → Developer machines (Docker Compose for services)
development → Shared dev environment (optional, useful for large teams)
staging     → Production mirror. Full data anonymization. All integrations.
production  → The real thing. Treat it with respect.
```

**Parity principle**: Staging must mirror production as closely as possible. Database version, OS version, environment variables, third-party services. Divergence between staging and production causes late-night incidents.

---

## 7.2 Infrastructure as Code (IaC)

All infrastructure defined as code. No manual clicks in cloud consoles that aren't also in version control.

### Terraform (Primary Recommendation)
```hcl
# Minimal production structure
terraform/
├── modules/
│   ├── vpc/
│   ├── rds/
│   ├── ecs/  (or eks/)
│   └── redis/
├── environments/
│   ├── staging/
│   │   ├── main.tf
│   │   └── terraform.tfvars
│   └── production/
│       ├── main.tf
│       └── terraform.tfvars
└── README.md
```

**Rules**:
- Remote state: S3 + DynamoDB lock (AWS), GCS (GCP), Terraform Cloud
- Never commit `terraform.tfstate`
- Plan before apply, always
- Separate service accounts per environment

---

## 7.3 Container Strategy

### Docker Standards
```dockerfile
# Production Dockerfile checklist:
□ Multi-stage build (build stage separate from runtime)
□ Non-root user
□ Minimal base image (alpine or distroless)
□ No secrets in Dockerfile
□ .dockerignore configured
□ HEALTHCHECK defined
□ Specific version tags, not :latest in production

# Example multi-stage Node.js
FROM node:20-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production

FROM node:20-alpine AS runtime
RUN addgroup -S appgroup && adduser -S appuser -G appgroup
WORKDIR /app
COPY --from=builder /app/node_modules ./node_modules
COPY --chown=appuser:appgroup . .
USER appuser
EXPOSE 3000
HEALTHCHECK --interval=30s CMD wget -qO- http://localhost:3000/health || exit 1
CMD ["node", "server.js"]
```

### Kubernetes (When Needed)
Use Kubernetes when you need:
- Independent scaling of multiple services
- Zero-downtime rolling deployments
- Sophisticated traffic management
- Self-healing infrastructure at scale

Don't use Kubernetes for:
- A single-service application
- A team without DevOps experience
- "We might need it someday"

**Alternatives to consider first**: Fly.io, Railway, Render, AWS ECS (simpler than K8s, still container-native)

---

## 7.4 CI/CD Pipeline Architecture

### Pipeline Stages (Ordered)

```
TRIGGER: Push to PR / Merge to main

Stage 1: FAST FEEDBACK (< 3 minutes)
  - Code formatting check
  - Lint
  - Type check
  - Unit tests
  - Secret scanning (gitleaks)

Stage 2: BUILD (< 5 minutes)
  - Build artifacts
  - Docker image build
  - Container vulnerability scan (Trivy)
  - Dependency audit

Stage 3: TEST (< 15 minutes)
  - Integration tests
  - API contract tests
  - SAST scan (Semgrep)

Stage 4: STAGING DEPLOY (on merge to main)
  - Deploy to staging
  - Run smoke tests
  - Run E2E tests (subset)
  - Performance regression check

Stage 5: PRODUCTION DEPLOY (manual trigger or auto on tag)
  - Deploy strategy: blue-green or rolling
  - Smoke tests
  - Automated rollback trigger on error spike
```

### GitHub Actions Template Structure
```yaml
# .github/workflows/ci.yml
name: CI Pipeline
on: [push, pull_request]

jobs:
  fast-check:
    runs-on: ubuntu-latest
    steps:
      - lint-and-typecheck
      - unit-tests
      - secret-scan

  build:
    needs: fast-check
    steps:
      - docker-build
      - image-scan

  integration-test:
    needs: build
    steps:
      - spin-up-test-db
      - run-integration-tests

  deploy-staging:
    needs: integration-test
    if: github.ref == 'refs/heads/main'
    steps:
      - deploy-to-staging
      - run-smoke-tests
```

---

## 7.5 Reliability Patterns

### Health Checks
Every service must expose:
```
GET /health         → Basic liveness check. Returns 200 if process is alive.
GET /health/ready   → Readiness check. Returns 200 only if all dependencies (DB, cache) are reachable.
```

### Zero-Downtime Deployment
```
Strategy options:
1. Rolling update:     Replace instances one at a time. Simple, some risk of mixed versions.
2. Blue-Green:         Run two identical environments. Switch traffic. Rollback = switch back.
3. Canary:             Route X% of traffic to new version. Monitor. Gradually increase.

Default recommendation: Blue-Green for most web applications.
```

### Autoscaling Rules
```
Scale OUT when: CPU > 70% for 5 minutes, OR memory > 80%, OR request queue depth > threshold
Scale IN when:  CPU < 30% for 15 minutes
Min instances:  ≥ 2 in production (single instance = single point of failure)
```

---

## 7.6 Infrastructure Cost Management

```
□ Tag all resources with: environment, service, team, cost-center
□ Set up billing alerts at 80% and 100% of budget
□ Right-size instances — start small, scale with data
□ Use reserved/committed instances for baseline load (can save 40–60%)
□ Delete unused resources (test environments, orphaned volumes)
□ Review S3/storage costs quarterly — they accumulate silently
□ Logs retention policy: 90 days hot, archive or delete after
```
