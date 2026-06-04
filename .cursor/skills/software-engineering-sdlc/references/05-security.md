# Phase 05 · Security

## Purpose
Security is a first-class engineering concern, not a checklist item at launch. Every system that handles user data, money, or access will be attacked. Design as if it's when, not if.

---

## 5.1 Threat Model Framework (STRIDE)

For each significant system component, analyze:

| Threat | Description | Example |
|--------|-------------|---------|
| **S**poofing | Impersonating another user/system | Forged JWTs, IDOR |
| **T**ampering | Modifying data in transit or at rest | SQL injection, CSRF |
| **R**epudiation | Denying an action occurred | Missing audit logs |
| **I**nformation Disclosure | Leaking sensitive data | Verbose error messages, over-fetching |
| **D**enial of Service | Making system unavailable | No rate limiting, unbounded queries |
| **E**levation of Privilege | Gaining unauthorized permissions | Broken access control |

---

## 5.2 OWASP Top 10 — Engineering Mitigations

| Vulnerability | Mitigation |
|---------------|------------|
| **A01: Broken Access Control** | Deny by default. Authorization check on every route. Tests for it. |
| **A02: Cryptographic Failures** | TLS everywhere. No MD5/SHA1 for passwords. Use bcrypt/argon2. Encrypt PII at rest. |
| **A03: Injection** | Parameterized queries only. Never string-concat SQL. |
| **A04: Insecure Design** | Threat model during design, not after. |
| **A05: Security Misconfiguration** | Hardened defaults. No debug in production. Security headers. |
| **A06: Vulnerable Components** | Automated dependency scanning in CI (Snyk, Dependabot). |
| **A07: Auth Failures** | MFA where possible. Secure password reset flows. Rate limit auth endpoints. |
| **A08: Data Integrity Failures** | Verify integrity of data from third parties. Signed artifacts in CI/CD. |
| **A09: Logging Failures** | Log security events. Never log sensitive data. Centralized log storage. |
| **A10: SSRF** | Allowlist outbound requests. Don't expose internal network. |

---

## 5.3 Authentication Design

### Passwords
```
□ Hash with bcrypt (cost 12+) or Argon2id — never MD5, SHA1, or SHA256 alone
□ Enforce minimum length (12+ chars), not complexity requirements (NIST guidance)
□ Breach password check via HaveIBeenPwned API
□ Rate limit: 5 failed attempts → lockout or CAPTCHA
□ Secure password reset: time-limited tokens (15 min), single-use, via email only
```

### Sessions / JWTs
```
JWT:
□ Short expiry on access tokens (15 min)
□ Refresh tokens stored in httpOnly, secure cookie (not localStorage)
□ Refresh token rotation on use
□ Token revocation strategy (Redis blocklist for logout)
□ Validate alg field — never accept "none"

Sessions:
□ Session IDs: cryptographically random, 128-bit minimum
□ Regenerate session ID on privilege change (login, role change)
□ Secure + HttpOnly + SameSite=Strict cookies
□ Session expiry configured
```

### Third-Party Auth (Recommended)
Use Auth0, Clerk, Supabase Auth, Cognito, or Firebase Auth unless:
- You have a compelling compliance reason not to
- You have a security team with auth expertise

> Rolling your own auth is the engineering equivalent of rolling your own crypto. Don't.

---

## 5.4 Authorization Patterns

```
RBAC (Role-Based Access Control):
  Simple, effective for most apps
  Roles: admin, editor, viewer (or domain-specific)

ABAC (Attribute-Based Access Control):
  Fine-grained, complex
  "User can edit post IF user.org_id == post.org_id AND post.status == 'draft'"

ReBAC (Relationship-Based):
  Google Zanzibar model (used by Google, Airbnb)
  Libraries: SpiceDB, OpenFGA
  Use for: complex permission graphs (e.g., nested folder permissions)
```

**Pattern**: Authorization logic lives in ONE place. Never scattered across controllers.

---

## 5.5 API Security Checklist

```
□ Authentication required on all non-public endpoints
□ Authorization checked on every resource access (not just endpoint)
□ Rate limiting: per-IP and per-user
□ Input validation: type, length, format on ALL inputs
□ Output encoding: prevent XSS in any HTML context
□ CORS policy: explicit allowlist, not wildcard in production
□ Security headers configured:
    Content-Security-Policy
    X-Content-Type-Options: nosniff
    X-Frame-Options: DENY
    Strict-Transport-Security (HSTS)
    Referrer-Policy
□ No sensitive data in URLs (use body/headers)
□ Pagination enforced (no unbounded list endpoints)
□ Verbose error messages disabled in production
□ Stack traces never returned to clients
```

---

## 5.6 Secrets Management

```
NEVER:
□ Hardcode secrets in source code
□ Commit .env files with real values (use .env.example)
□ Put secrets in URL parameters
□ Log secrets (even "for debugging")
□ Store secrets in browser localStorage

DO:
□ Use a secrets manager: AWS Secrets Manager, HashiCorp Vault, Doppler, GCP Secret Manager
□ Rotate secrets regularly, automate rotation where possible
□ Principle of least privilege: each service gets only the secrets it needs
□ Audit secret access
□ Separate secrets per environment (dev/staging/prod)
```

---

## 5.7 Compliance Considerations

| Standard | Applies To | Key Requirements |
|----------|------------|-----------------|
| **GDPR** | EU user data | Data minimization, consent, right to deletion, DPA |
| **HIPAA** | US health data | Encryption, audit logs, BAA with vendors |
| **PCI-DSS** | Payment card data | Never store raw card data. Use Stripe/Braintree. |
| **SOC 2** | B2B SaaS | Security, availability, confidentiality controls |
| **CCPA** | California users | Similar to GDPR, opt-out of sale |

> For PCI-DSS: The simplest path is never handling card data yourself. Tokenize via Stripe and your scope drops to near-zero.

---

## 5.8 Security in SDLC

```
Design:    Threat modeling session, ADR for security decisions
Dev:       SAST in IDE (SonarLint), pre-commit secret scanning
CI:        SAST scan (Semgrep, SonarCloud), dependency audit (Snyk/Dependabot)
CD:        Container image scanning (Trivy, Snyk), signed artifacts
Staging:   DAST scan (OWASP ZAP), manual pen test before major launches
Prod:      WAF, anomaly alerting, quarterly access review
```
