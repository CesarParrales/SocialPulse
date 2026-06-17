# GitHub Actions — CI/CD

> **Versiones CI:** `php-version` y `node-version` deben coincidir con
> `composer.json` / `package.json` — no copiar ciegamente los ejemplos.
> Política: `laravel-backend/references/stack-versions.md`.

## Pipeline Completo Laravel

```yaml
# .github/workflows/ci.yml
name: CI

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]

jobs:
  # ─────────── Análisis estático ───────────
  lint:
    name: Lint & Static Analysis
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'  # ← leer de composer.json require.php
          tools: composer:v2

      - name: Cache Composer
        uses: actions/cache@v4
        with:
          path: vendor
          key: composer-${{ hashFiles('composer.lock') }}

      - name: Install dependencies
        run: composer install --prefer-dist --no-interaction

      - name: Run Pint (code style)
        run: ./vendor/bin/pint --test

      - name: Run Larastan (static analysis)
        run: ./vendor/bin/phpstan analyse --memory-limit=512M

  # ─────────── Tests ───────────
  test:
    name: Tests
    runs-on: ubuntu-latest
    needs: lint

    services:
      postgres:
        image: postgres:16-alpine
        env:
          POSTGRES_DB: myapp_test
          POSTGRES_USER: myapp
          POSTGRES_PASSWORD: secret
        options: >-
          --health-cmd pg_isready
          --health-interval 5s
          --health-timeout 5s
          --health-retries 10
        ports: ['5432:5432']

      redis:
        image: redis:7-alpine
        options: --health-cmd "redis-cli ping"
        ports: ['6379:6379']

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP with coverage
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'  # ← leer de composer.json require.php
          extensions: pdo_pgsql, redis
          coverage: pcov

      - name: Cache Composer
        uses: actions/cache@v4
        with:
          path: vendor
          key: composer-${{ hashFiles('composer.lock') }}

      - name: Install dependencies
        run: composer install --prefer-dist --no-interaction

      - name: Copy .env
        run: cp .env.testing.example .env.testing

      - name: Generate key
        run: php artisan key:generate --env=testing

      - name: Run migrations
        env:
          DB_CONNECTION: pgsql
          DB_HOST: 127.0.0.1
          DB_DATABASE: myapp_test
          DB_USERNAME: myapp
          DB_PASSWORD: secret
        run: php artisan migrate --env=testing --force

      - name: Run tests with coverage
        env:
          DB_CONNECTION: pgsql
          DB_HOST: 127.0.0.1
          DB_DATABASE: myapp_test
          DB_USERNAME: myapp
          DB_PASSWORD: secret
          REDIS_HOST: 127.0.0.1
          QUEUE_CONNECTION: sync  # sync en tests
          MAIL_MAILER: array      # no enviar emails reales
        run: php artisan test --parallel --coverage-clover coverage.xml --min=80

      - name: Upload coverage
        uses: codecov/codecov-action@v4
        with:
          file: coverage.xml
          token: ${{ secrets.CODECOV_TOKEN }}

  # ─────────── Build Docker ───────────
  build:
    name: Build & Push Image
    runs-on: ubuntu-latest
    needs: test
    if: github.ref == 'refs/heads/main' || github.ref == 'refs/heads/develop'

    permissions:
      contents: read
      packages: write

    steps:
      - uses: actions/checkout@v4

      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v3

      - name: Log in to registry
        uses: docker/login-action@v3
        with:
          registry: ghcr.io
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}

      - name: Extract metadata
        id: meta
        uses: docker/metadata-action@v5
        with:
          images: ghcr.io/${{ github.repository }}
          tags: |
            type=ref,event=branch
            type=sha,prefix=sha-
            type=raw,value=latest,enable=${{ github.ref == 'refs/heads/main' }}

      - name: Build and push
        uses: docker/build-push-action@v5
        with:
          context: .
          target: production
          push: true
          tags: ${{ steps.meta.outputs.tags }}
          labels: ${{ steps.meta.outputs.labels }}
          cache-from: type=gha
          cache-to: type=gha,mode=max
          build-args: |
            APP_VERSION=${{ github.sha }}
```

---

## Deploy a Producción (con ambientes y approval)

```yaml
# .github/workflows/deploy.yml
name: Deploy

on:
  workflow_run:
    workflows: [CI]
    types: [completed]
    branches: [main]

jobs:
  deploy-staging:
    name: Deploy to Staging
    runs-on: ubuntu-latest
    if: ${{ github.event.workflow_run.conclusion == 'success' }}
    environment:
      name: staging
      url: https://staging.myapp.com

    steps:
      - uses: actions/checkout@v4

      - name: Deploy to staging via Kamal
        uses: webfactory/ssh-agent@v0.9.0
        with:
          ssh-private-key: ${{ secrets.SSH_PRIVATE_KEY }}

      - name: Run Kamal deploy
        env:
          KAMAL_REGISTRY_PASSWORD: ${{ secrets.REGISTRY_PASSWORD }}
          APP_KEY: ${{ secrets.STAGING_APP_KEY }}
          DB_PASSWORD: ${{ secrets.STAGING_DB_PASSWORD }}
        run: |
          gem install kamal
          kamal deploy --destination staging

      - name: Run smoke tests
        run: |
          sleep 10  # esperar que la app arranque
          curl -f https://staging.myapp.com/health || exit 1

      - name: Notify on Slack
        if: always()
        uses: slackapi/slack-github-action@v1
        with:
          payload: |
            {"text": "Staging deploy ${{ job.status }}: ${{ github.sha }}"}
        env:
          SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK }}

  deploy-production:
    name: Deploy to Production
    runs-on: ubuntu-latest
    needs: deploy-staging
    environment:
      name: production
      url: https://api.myapp.com
    # Requiere approval manual en GitHub → Settings → Environments

    steps:
      - uses: actions/checkout@v4

      - name: Deploy to production via Kamal
        uses: webfactory/ssh-agent@v0.9.0
        with:
          ssh-private-key: ${{ secrets.SSH_PRIVATE_KEY }}

      - name: Run Kamal deploy
        env:
          KAMAL_REGISTRY_PASSWORD: ${{ secrets.REGISTRY_PASSWORD }}
          APP_KEY: ${{ secrets.PROD_APP_KEY }}
          DB_PASSWORD: ${{ secrets.PROD_DB_PASSWORD }}
        run: |
          gem install kamal
          kamal deploy

      - name: Verify deployment
        run: |
          sleep 15
          curl -f https://api.myapp.com/health || exit 1
          echo "Production deploy successful: ${{ github.sha }}"
```

---

## Pipeline NestJS/Node

```yaml
# .github/workflows/ci-node.yml
name: CI — Node

on:
  push:
    branches: [main, develop]
  pull_request:

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:16-alpine
        env:
          POSTGRES_DB: myapp_test
          POSTGRES_USER: myapp
          POSTGRES_PASSWORD: secret
        options: --health-cmd pg_isready
        ports: ['5432:5432']

      redis:
        image: redis:7-alpine
        options: --health-cmd "redis-cli ping"
        ports: ['6379:6379']

    steps:
      - uses: actions/checkout@v4

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'

      - name: Install dependencies
        run: npm ci

      - name: Lint
        run: npm run lint

      - name: Type check
        run: npx tsc --noEmit

      - name: Unit tests
        run: npm run test:unit -- --coverage

      - name: E2E tests
        env:
          DATABASE_URL: postgresql://myapp:secret@localhost:5432/myapp_test
          REDIS_HOST: localhost
          JWT_SECRET: test-secret-32-chars-minimum-length
        run: |
          npx prisma migrate deploy
          npm run test:e2e

      - name: Check coverage threshold
        run: npm run test:cov -- --coverageThreshold='{"global":{"lines":80}}'
```

---

## Reusable Workflows — DRY en CI/CD

```yaml
# .github/workflows/_deploy.yml — workflow reutilizable
name: Deploy (reusable)

on:
  workflow_call:
    inputs:
      environment:
        required: true
        type: string
      url:
        required: true
        type: string
    secrets:
      SSH_PRIVATE_KEY:
        required: true
      REGISTRY_PASSWORD:
        required: true
      APP_KEY:
        required: true
      DB_PASSWORD:
        required: true

jobs:
  deploy:
    runs-on: ubuntu-latest
    environment:
      name: ${{ inputs.environment }}
      url: ${{ inputs.url }}

    steps:
      - uses: actions/checkout@v4
      - uses: webfactory/ssh-agent@v0.9.0
        with:
          ssh-private-key: ${{ secrets.SSH_PRIVATE_KEY }}
      - name: Deploy
        run: kamal deploy --destination ${{ inputs.environment }}

# Uso en otro workflow
# jobs:
#   deploy-staging:
#     uses: ./.github/workflows/_deploy.yml
#     with:
#       environment: staging
#       url: https://staging.myapp.com
#     secrets: inherit
```

---

## Branch Protection y Workflow de Git

```yaml
# Configuración recomendada en GitHub → Settings → Branches → main:
# ✅ Require a pull request before merging
# ✅ Require approvals: 1 (mínimo)
# ✅ Require status checks to pass:
#     - lint
#     - test
# ✅ Require branches to be up to date
# ✅ Do not allow bypassing the above settings
# ✅ Restrict who can push to matching branches

# Conventional Commits enforced en CI
# .github/workflows/commits.yml
name: Conventional Commits
on: [pull_request]
jobs:
  check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0
      - uses: wagoid/commitlint-github-action@v5
        with:
          configFile: commitlint.config.js
```
