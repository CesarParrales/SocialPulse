# SocialPulse

Plataforma SaaS multi-tenant que unifica analytics orgánico y pagado de **Meta**, **Google Ads**, **TikTok**, **LinkedIn** y **YouTube** para agencias digitales.

Documento de producto: [`socialpulse-prd.md`](socialpulse-prd.md) · Contexto técnico: [`context.md`](context.md) · Agentes IA: [`AGENTS.md`](AGENTS.md)

## Stack

| Capa | Tecnología |
|------|------------|
| Backend | Laravel 13, PHP 8.4 |
| Frontend | React 19, Inertia.js 2, TypeScript, Tailwind CSS 3 |
| Base de datos | PostgreSQL |
| Cache / colas | Redis, Laravel Horizon |
| Auth | Laravel Breeze + Sanctum |
| RBAC | Spatie Laravel Permission |
| Observabilidad | Sentry, health checks |
| Arquitectura | Monolito modular (`nwidart/laravel-modules`) |

## Módulos

```
Modules/
├── Auth/
├── Workspaces/
├── Connections/
├── Ingestion/
├── Analytics/
├── Dashboard/
├── Reports/
├── Notifications/
├── Settings/
└── Content/
```

## Requisitos locales

- PHP 8.4+
- Composer 2.x
- Node.js 22+
- PostgreSQL 16+ (o `DB_CONNECTION=sqlite` para pruebas rápidas)
- Redis 7+ (recomendado; en CI se usa sqlite/array/sync)

## Instalación

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### Datos demo

```bash
php artisan db:seed --class=Modules\\Workspaces\\Database\\Seeders\\RolesSeeder
php artisan db:seed --class=Modules\\Workspaces\\Database\\Seeders\\DemoSeeder
```

| Rol | Email | Password |
|-----|-------|----------|
| Super admin | `super@socialpulse.test` | `password` |
| Admin agencia | `admin@agenciademo.test` | `password` |
| Operador | `operador@agenciademo.test` | `password` |
| Cliente readonly | `cliente@agenciademo.test` | `password` |

### Desarrollo

```bash
composer dev
```

Levanta servidor, colas, logs y Vite en paralelo.

### Horizon (colas de ingesta)

```bash
php artisan horizon
```

Colas: `ingestion-daily`, `ingestion-stories`, `ingestion-paid`, `reports`, `notifications`, `default`.

En producción usar **Supervisor** + **cron** del scheduler — ver [docs/DEPLOY.md](docs/DEPLOY.md).

### Tests

```bash
php artisan test
vendor/bin/pint --test
npm run build
```

## Operaciones y launch

| Documento | Contenido |
|-----------|-----------|
| [docs/DEPLOY.md](docs/DEPLOY.md) | Supervisor, cron, Nginx, staging |
| [docs/ONBOARDING.md](docs/ONBOARDING.md) | Flujo E2E agencia → workspace → conexiones → dashboard |
| [docs/RUNBOOK.md](docs/RUNBOOK.md) | Incidentes, colas, tokens, rollback |
| [docs/LAUNCH-CHECKLIST.md](docs/LAUNCH-CHECKLIST.md) | Criterios PRD §14 con estado |

### Health checks

```bash
curl -sf http://localhost/up          # liveness
curl -sf http://localhost/health | jq # readiness (DB, Redis)
```

### Sentry (staging / producción)

```env
SENTRY_LARAVEL_DSN=https://…@sentry.io/…
SENTRY_ENVIRONMENT=production
APP_VERSION=1.0.0
```

Tras configurar el DSN:

```bash
php artisan sentry:test
```

### Smoke test (post-deploy)

```bash
php artisan socialpulse:smoke
php artisan socialpulse:integrations:check
php artisan socialpulse:smoke --auth   # requiere DemoSeeder
php artisan socialpulse:smoke --auth --oauth   # redirects OAuth si hay credenciales en .env
```

### Páginas legales

| URL | Uso |
|-----|-----|
| `/legal/privacy` | Meta App Review, footer público |
| `/legal/terms` | Términos de servicio |

Configurar `LEGAL_CONTACT_EMAIL` en producción.

## Protocolo de desarrollo

Ver [`.cursor/rules/development-protocol.mdc`](.cursor/rules/development-protocol.mdc) y skills en [`.cursor/skills/`](.cursor/skills/).

Plantillas de despliegue en [`deploy/`](deploy/) (Supervisor, cron, Nginx). Guía completa: [docs/DEPLOY.md](docs/DEPLOY.md).

## Licencia

MIT
