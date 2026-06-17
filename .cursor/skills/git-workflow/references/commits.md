# Conventional Commits y Semantic Versioning

## Conventional Commits — El Estándar

```
Formato:
<type>(<scope>): <description>

[optional body]

[optional footer(s)]

type:    feat, fix, docs, style, refactor, test, chore, perf, ci, build
scope:   módulo afectado (orders, auth, payments, ui, api, db...)
subject: descripción corta en imperativo, presente, minúsculas
body:    explicación del qué y por qué (no el cómo)
footer:  BREAKING CHANGE, Closes #issue, Reviewed-by: ...
```

---

## Tipos y Cuándo Usar Cada Uno

```bash
# feat — nueva funcionalidad visible para el usuario
git commit -m "feat(orders): add coupon code validation at checkout"
git commit -m "feat(auth): implement Google OAuth login"
git commit -m "feat(api): add bulk order creation endpoint"

# fix — corrección de bug
git commit -m "fix(orders): prevent negative total when coupon exceeds subtotal"
git commit -m "fix(auth): refresh token not rotating on concurrent requests"
git commit -m "fix(api): order list not respecting per_page limit"

# perf — mejora de performance sin cambio de funcionalidad
git commit -m "perf(db): add composite index on orders(user_id, status)"
git commit -m "perf(api): cache featured products for 5 minutes"

# refactor — cambio interno sin cambio de funcionalidad ni fix
git commit -m "refactor(orders): extract discount calculation to PricingService"
git commit -m "refactor(auth): replace manual JWT with Sanctum"

# test — agregar o arreglar tests
git commit -m "test(orders): add unit tests for discount calculation edge cases"
git commit -m "test(auth): fix flaky login test due to timing issue"

# docs — solo documentación
git commit -m "docs(api): add OpenAPI spec for orders endpoint"
git commit -m "docs: update deployment guide for Kamal"

# chore — tareas de mantenimiento, dependencias, configuración
git commit -m "chore: update dependencies to latest versions"
git commit -m "chore(ci): add code coverage reporting to GitHub Actions"
git commit -m "chore(env): add STRIPE_WEBHOOK_SECRET to .env.example"

# style — formato, whitespace, punto y coma (no cambia lógica)
git commit -m "style(orders): apply Pint formatting rules"

# ci — cambios en CI/CD
git commit -m "ci: add E2E tests to production deploy pipeline"
git commit -m "ci: cache npm dependencies between runs"

# build — cambios en sistema de build, dependencias externas
git commit -m "build: upgrade to Vite 5"
git commit -m "build(docker): use multi-stage build to reduce image size"
```

---

## Breaking Changes

```bash
# Breaking change en el footer
git commit -m "feat(api): change order status from string to enum

BREAKING CHANGE: order.status field is now an enum value (PENDING, PROCESSING, etc.)
instead of lowercase strings (pending, processing, etc.).
Clients must update their status comparisons."

# Breaking change con ! en el tipo
git commit -m "feat(api)!: rename 'price' to 'total' in order response

Clients consuming order.price must update to order.total."

# Body cuando el cambio necesita contexto
git commit -m "fix(auth): invalidate all sessions on password change

Previously, changing a password didn't invalidate active sessions,
allowing compromised sessions to remain valid indefinitely.

This fix revokes all existing tokens when a password is updated.
Users will need to log in again on all devices.

Closes #247"
```

---

## Commitlint — Enforcar el Estándar

```bash
npm install --save-dev @commitlint/cli @commitlint/config-conventional
```

```javascript
// commitlint.config.js
module.exports = {
  extends: ['@commitlint/config-conventional'],
  rules: {
    // Tipos permitidos
    'type-enum': [2, 'always', [
      'feat', 'fix', 'docs', 'style', 'refactor',
      'test', 'chore', 'perf', 'ci', 'build', 'revert'
    ]],

    // Scopes obligatorios para este proyecto
    // 'scope-enum': [2, 'always', ['orders', 'auth', 'payments', 'api', 'ui']],

    // Subject en minúsculas
    'subject-case': [2, 'always', 'lower-case'],

    // Subject no termina en punto
    'subject-full-stop': [2, 'never', '.'],

    // Longitud máxima del header
    'header-max-length': [2, 'always', 100],

    // Body con 72 chars por línea
    'body-max-line-length': [1, 'always', 72],
  },
};
```

```yaml
# .github/workflows/commits.yml — verificar en PR
name: Conventional Commits

on:
  pull_request:
    types: [opened, synchronize, reopened, edited]

jobs:
  commitlint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0   # historial completo para verificar todos los commits

      - uses: wagoid/commitlint-github-action@v5
        with:
          configFile: commitlint.config.js
          helpURL: https://github.com/myorg/myapp/blob/main/docs/contributing.md
```

---

## Semantic Versioning — MAJOR.MINOR.PATCH

```
MAJOR.MINOR.PATCH

PATCH (1.0.x): fix — bug fix compatible con versión anterior
MINOR (1.x.0): feat — nueva funcionalidad compatible con versión anterior
MAJOR (x.0.0): BREAKING CHANGE — cambio incompatible con versión anterior

Ejemplos:
1.0.0 → 1.0.1  cuando: fix(auth): prevent session fixation
1.0.1 → 1.1.0  cuando: feat(orders): add coupon support
1.1.0 → 2.0.0  cuando: feat(api)!: change response format (breaking)
```

---

## Changelog Automático con standard-version o release-please

```bash
# Instalar
npm install --save-dev standard-version

# package.json
{
  "scripts": {
    "release": "standard-version",
    "release:minor": "standard-version --release-as minor",
    "release:major": "standard-version --release-as major",
    "release:patch": "standard-version --release-as patch",
    "release:dry": "standard-version --dry-run"
  }
}

# Correr release
npm run release:dry   # ver qué haría sin ejecutar
npm run release       # crea tag + actualiza CHANGELOG.md + bump version

# .versionrc.json — personalizar el changelog
{
  "types": [
    {"type": "feat",     "section": "✨ Features"},
    {"type": "fix",      "section": "🐛 Bug Fixes"},
    {"type": "perf",     "section": "⚡ Performance"},
    {"type": "docs",     "section": "📚 Documentation", "hidden": false},
    {"type": "refactor", "section": "♻️ Code Refactoring", "hidden": false},
    {"type": "test",     "hidden": true},
    {"type": "chore",    "hidden": true},
    {"type": "style",    "hidden": true},
    {"type": "ci",       "hidden": true}
  ]
}
```

---

## Release Please — GitHub Action Automatizado

```yaml
# .github/workflows/release.yml
name: Release Please

on:
  push:
    branches: [main]

jobs:
  release-please:
    runs-on: ubuntu-latest
    steps:
      - uses: google-github-actions/release-please-action@v4
        id: release
        with:
          release-type: node         # o php, python, etc.
          package-name: myapp

      # Pasos que solo corren cuando hay un release nuevo
      - uses: actions/checkout@v4
        if: ${{ steps.release.outputs.release_created }}

      - name: Build Docker image
        if: ${{ steps.release.outputs.release_created }}
        run: |
          docker build -t myapp:${{ steps.release.outputs.tag_name }} .
          docker push myapp:${{ steps.release.outputs.tag_name }}
          docker push myapp:latest

# Release Please:
# 1. Monitorea commits con conventional commits
# 2. Crea automáticamente un PR de release
# 3. El PR actualiza version + CHANGELOG.md
# 4. Al mergear el PR → crea tag y GitHub Release
```

---

## Tags y Releases Manuales

```bash
# Crear tag anotado (preferido sobre tags ligeros)
git tag -a v1.2.0 -m "Release v1.2.0: add coupon support and order cancellation"

# Push del tag
git push origin v1.2.0

# O todos los tags
git push origin --tags

# Ver tags
git tag -l "v*"
git show v1.2.0

# Checkout a un tag específico
git checkout v1.2.0  # detached HEAD
git checkout -b hotfix/v1.2.1 v1.2.0  # nueva branch desde tag

# Eliminar tag (local y remoto)
git tag -d v1.2.0
git push origin --delete v1.2.0
```
