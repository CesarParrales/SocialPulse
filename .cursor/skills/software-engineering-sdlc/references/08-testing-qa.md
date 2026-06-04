# Phase 08 · Testing & QA

## Purpose
Testing is not a phase that happens before launch. It is a continuous engineering discipline that starts with the first line of code and ends with production monitoring. "We'll add tests later" has ended more projects than any technical debt ever will.

---

## 8.1 The Test Pyramid

```
                    /\
                   /  \
                  / E2E \       ← Few, slow, expensive. Smoke + critical paths only.
                 /────────\
                /Integration\   ← Medium. API contracts, service boundaries, DB.
               /──────────────\
              /   Unit Tests    \  ← Many, fast, cheap. Business logic, pure functions.
             /──────────────────────\
```

**Coverage targets (opinionated but evidence-based)**:
- Unit: 70–80% line coverage
- Integration: Key API endpoints + DB operations
- E2E: Top 5 critical user journeys only

> Don't worship coverage percentages. 90% coverage with bad tests is worse than 60% coverage with meaningful tests.

---

## 8.2 Test Types Reference

| Type | What It Tests | Tools | Speed |
|------|--------------|-------|-------|
| Unit | Individual functions/classes in isolation | Jest, Vitest, pytest, JUnit | < 1ms |
| Integration | Module interactions, DB, external services (mocked/real) | Supertest, pytest + SQLAlchemy | Seconds |
| Contract | API shape matches consumer expectations | Pact, OpenAPI validation | Seconds |
| E2E | Full user journey through real UI | Playwright, Cypress | Minutes |
| Performance | Load, stress, soak | k6, Locust, Artillery | Minutes–hours |
| Security | Vulnerability scanning | OWASP ZAP, Semgrep | Minutes |
| Accessibility | WCAG compliance | axe-core, Lighthouse | Seconds |

---

## 8.3 Unit Testing Standards

```
Pattern: AAA (Arrange, Act, Assert)

// Good unit test
describe('calculateDiscount', () => {
  it('applies 20% discount for premium users', () => {
    // Arrange
    const user = { tier: 'premium' }
    const price = 100

    // Act
    const result = calculateDiscount(price, user)

    // Assert
    expect(result).toBe(80)
  })

  it('applies no discount for standard users', () => {
    const user = { tier: 'standard' }
    expect(calculateDiscount(100, user)).toBe(100)
  })

  it('throws on negative price', () => {
    expect(() => calculateDiscount(-1, { tier: 'standard' })).toThrow('Price must be positive')
  })
})
```

**Rules**:
- Tests must be independent (no shared state between tests)
- Tests must be deterministic (same result every run)
- Test the behavior, not the implementation
- One logical assertion per test (not one `expect` call — one logical outcome)
- Name tests as: "it [verb] [expected outcome] [when/given condition]"

---

## 8.4 Integration Testing Strategy

```
□ Test database queries with a real test database (not mocks of SQL)
□ Test API endpoints end-to-end through HTTP (not by calling handlers directly)
□ Use factories/fixtures for test data (not hardcoded IDs)
□ Reset database state between tests (transactions or truncation)
□ Mock external services (payment, email) — don't call real APIs in tests
□ Test error paths, not just happy paths

Test database approach:
  - Dedicated test database with same schema
  - Run migrations before test suite
  - Use transactions + rollback per test for isolation
  - Or: Docker Compose with ephemeral test DB
```

---

## 8.5 E2E Testing Strategy

**Write E2E tests for**:
- User registration and login
- Core value delivery (e.g., complete a purchase, create a project)
- Payment flow
- Critical notification/email triggers

**Don't write E2E tests for**:
- Every UI state
- Things covered by unit/integration tests
- Edge cases (unit tests are faster and more reliable for these)

```javascript
// Playwright E2E example
test('user can complete purchase', async ({ page }) => {
  await page.goto('/products')
  await page.click('[data-testid="product-card-1"]')
  await page.click('[data-testid="add-to-cart"]')
  await page.goto('/checkout')
  await page.fill('[data-testid="card-number"]', '4242424242424242')
  await page.click('[data-testid="submit-order"]')
  await expect(page.locator('[data-testid="order-confirmation"]')).toBeVisible()
})
```

---

## 8.6 Performance Testing

**When to run**:
- Before every major launch
- After significant architectural changes
- Quarterly in production (chaos engineering approach)

**k6 baseline load test structure**:
```javascript
import http from 'k6/http'
import { check, sleep } from 'k6'

export const options = {
  stages: [
    { duration: '2m', target: 100 },   // Ramp up
    { duration: '5m', target: 100 },   // Sustained load
    { duration: '2m', target: 0 },     // Ramp down
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'],  // 95% of requests < 500ms
    http_req_failed: ['rate<0.01'],    // Error rate < 1%
  },
}
```

---

## 8.7 QA Sign-Off Checklist (Pre-Launch)

```
Functional:
□ All P0 and P1 acceptance criteria verified
□ Happy path tested end-to-end
□ Error handling tested (invalid inputs, network failures, empty states)
□ Edge cases documented and tested

Non-Functional:
□ Load test run at 2x expected traffic
□ Page load performance meets targets (Lighthouse > 80)
□ Mobile/responsive tested on real devices or emulator
□ Accessibility scan run (axe-core / Lighthouse)
□ Cross-browser tested (at minimum: Chrome, Safari, Firefox, Edge)

Security:
□ OWASP ZAP scan run on staging
□ Authentication flows tested (login, logout, reset, token expiry)
□ Authorization tested (attempt to access other users' data)
□ Input validation tested (XSS, SQLi attempts)

Operations:
□ Logging verified (correct level, no PII in logs)
□ Error tracking configured (Sentry/Rollbar)
□ Alerts configured and tested
□ Rollback procedure tested
```
