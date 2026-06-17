# SocialPulse — Contexto del proyecto

**Última actualización:** Junio 2026  
**Audiencia:** desarrolladores, agentes IA, onboarding técnico  
**Mantener sincronizado con:** `socialpulse-prd.md` (§5.1 stack), `docs/platform-roadmap.md`, este archivo tras cada fase cerrada.

---

## Qué es

SaaS **multi-tenant** para agencias digitales en LATAM. Unifica analytics **orgánico + pagado** de redes con APIs oficiales, dashboard operativo, benchmarks, informes PDF brandeables y calendario editorial con publicación Meta.

Flujo de producto:

```
CONECTAR → RECOLECTAR → ANALIZAR → INFORMAR → ACTUAR
```

---

## Jerarquía de fuentes de verdad

| Prioridad | Documento | Uso |
|-----------|-----------|-----|
| 1 | `socialpulse-prd.md` | Alcance MVP, NFRs, modelo de datos, riesgos API |
| 2 | `docs/platform-roadmap.md` | Fases A–D, mapa plataformas, sprint actual |
| 3 | `context.md` (este) | Snapshot técnico del repo **ahora** |
| 4 | ADRs en `docs/adr-*.md` | Decisiones por integración (TikTok, LinkedIn, YouTube) |
| 5 | `AGENTS.md` | Reglas operativas para agentes IA |

Ante conflicto código vs PRD → **PRD manda** hasta ADR/documento explícito de cambio.

---

## Stack

| Capa | Tecnología |
|------|------------|
| Backend | Laravel 13, PHP 8.3+ (CI/dev: 8.4) |
| Frontend | React 19, Inertia.js 2, TypeScript, Vite 8, Tailwind 3 |
| DB | PostgreSQL (prod/staging/local); SQLite en CI |
| Colas / cache | Redis + Horizon (prod/staging); database/array en dev local posible |
| Auth | Breeze + Sanctum |
| RBAC | Spatie Permission |
| Módulos | `nwidart/laravel-modules` |
| PDF | Browsershot; anexos CSV + Excel (PhpSpreadsheet) |
| Observabilidad | Sentry, `/up`, `/health` |

---

## Estado de fases (Junio 2026)

| Fase | Tema | Estado |
|------|------|--------|
| **A** | MVP analytics + informe PDF multi-canal | ✅ Cerrada |
| **B** | Deck PDF, competidores, CSV/Excel, dashboard público | ✅ Cerrada |
| **C** | Content: calendario, aprobación, publish Meta | ✅ Cerrada (base) |
| **D** | TikTok, LinkedIn, YouTube (conectar → informe) | ✅ Cerrada |
| **Launch** | QA OAuth staging, 7 días ingesta real, Sentry prod | 🟡 En curso |

**Fuera de MVP acordado:** publicación en TikTok/LinkedIn/YouTube, paid TikTok/LinkedIn, gestión de ads, scraping.

---

## Módulos Laravel

Todos bajo `Modules/`. Contrato entre módulos: servicios reciben `Collection<ConnectedAsset>` con scope explícito; no mezclar agregaciones sin filtro.

| Módulo | Responsabilidad | Piezas clave |
|--------|-----------------|--------------|
| **Workspaces** | Agencias, workspaces, miembros, invitaciones, roles | `Agency`, `Workspace`, `DemoSeeder` |
| **Connections** | OAuth, activos, tokens, catálogo plataformas | `PlatformCatalog`, `WorkspaceConnectionController`, refresh tokens |
| **Ingestion** | Jobs orgánico/paid/stories, logs, modelos métricas | `*IngestionService`, `IngestionServiceProvider` schedule |
| **Analytics** | Compare, benchmarks interno/industria, competidores | `WorkspaceComparisonService`, `IndustryBenchmark*` |
| **Dashboard** | KPIs, overview, scope activos, dashboard público | `WorkspaceDashboardService`, `PublicDashboard*` |
| **Reports** | PDF, narrativa, anexos CSV/Excel | `ReportDataAssembler`, `ReportChannelInsightsService` |
| **Content** | Calendario, drafts, workflow aprobación, publish Meta | `ContentPublishService`, `MetaContentPublishService` |
| **Notifications** | In-app + email: tokens, ingesta fallida | Jobs dispatch warnings |
| **Settings** | Credenciales cascada agencia → plataforma → `.env` | `IntegrationConfigResolver`, UI Platform/Agency |
| **Auth** | Scaffold modular; auth principal en `app/` + Breeze | — |

Frontend Inertia: `resources/js/Pages/{Module}/…`, layouts en `resources/js/Layouts/`, componentes en `resources/js/Components/`.

---

## Plataformas integradas

Implementación: `Modules/Connections/app/Support/PlatformCatalog.php`.

| Plataforma | Activo | Conectar | Ingesta | Dashboard/PDF | Publish |
|------------|--------|:--------:|:-------:|:-------------:|:-------:|
| **Meta** | FB Page, IG, Meta Ads | ✅ OAuth + System User | ✅ organic + stories + paid | ✅ | ✅ FB/IG (Content) |
| **Google** | Google Ads | ✅ OAuth | ✅ paid | ✅ paid | — |
| **TikTok** | Business account | ✅ | ✅ organic | ✅ | 📋 |
| **LinkedIn** | Company page | ✅ | ✅ organic | ✅ | 📋 |
| **YouTube** | Channel | ✅ | ✅ organic | ✅ | 📋 |

ADRs: `docs/adr-009-tiktok-integration.md`, `adr-010-linkedin-integration.md`, `adr-011-youtube-integration.md`.

---

## Pipeline de ingesta (scheduler UTC)

| Hora | Job |
|------|-----|
| 02:00 | Organic Facebook, Instagram |
| 02:15 | Organic TikTok |
| 02:20 | Organic LinkedIn |
| 02:25 | Organic YouTube |
| 02:30 | Paid Meta, Paid Google (daily) |
| cada 4h | Paid Meta/Google intraday |
| cada 6h | Stories watcher |
| 05:00 | Token refresh dispatch |
| 06:00 | Token expiry warnings |

Colas Horizon: `ingestion-daily`, `ingestion-stories`, `ingestion-paid`, `reports`, `notifications`, `default`.

Comandos manuales (`--sync` opcional):

```bash
ingestion:facebook-organic
ingestion:instagram-organic
ingestion:stories-watcher
ingestion:paid-meta
ingestion:paid-google
ingestion:tiktok-organic
ingestion:linkedin-organic
ingestion:youtube-organic
```

---

## Credenciales y entorno

### Tres archivos — no confundir

| Archivo | Uso |
|---------|-----|
| **`.env`** | Tu máquina local. **Conservar y complementar.** |
| **`.env.example`** | Plantilla repo: OAuth, túnel, Redis, Sentry |
| **`.env.staging.example`** | **Solo servidor staging remoto.** Nunca `cp` sobre `.env` local |

### Desarrollo local típico (Valet + Cloudflare Tunnel)

```env
APP_URL=http://socialtoolsdev.test
TRUSTED_PROXIES=*

# Redirects OAuth = URL HTTPS pública del túnel (no APP_URL local)
META_REDIRECT_URI=https://tu-dominio-tunel/connections/meta/callback
GOOGLE_REDIRECT_URI=https://tu-dominio-tunel/connections/google/callback
# Idem TikTok, LinkedIn, YouTube
```

`APP_URL` puede ser Valet; los `*_REDIRECT_URI` deben coincidir con las consolas OAuth públicas.

Credenciales OAuth: cascada **agencia → plataforma (Settings) → `.env`**. Tokens de usuario: **cifrados en BD**, nunca en git.

Ver: `docs/staging-oauth-qa.md`, `Modules/Settings/app/Services/IntegrationConfigResolver.php`.

---

## Comandos operativos

```bash
# Verificación
php artisan socialpulse:smoke
php artisan socialpulse:smoke --auth
php artisan socialpulse:smoke --auth --oauth
php artisan socialpulse:integrations:check
php artisan socialpulse:integrations:check --require=meta,google,tiktok

# Calidad
php artisan test
vendor/bin/pint --test
npm run build

# Demo
php artisan db:seed --class=Modules\\Workspaces\\Database\\Seeders\\DemoSeeder
```

Usuarios demo: ver `README.md`.

---

## CI (`.github/workflows/ci.yml`)

Push/PR a `main`/`master`: composer, npm build, migrate SQLite, `php artisan test`, smoke, integrations check, Pint.

---

## Restricciones no negociables

- Meta Stories: captura cada 6h; **sin histórico** previo a conexión.
- APIs Meta/Google/TikTok/LinkedIn/YouTube: **solo vía servicios**, nunca desde controllers.
- Benchmark industria: **no mostrar** hasta n≥30 por segmento.
- Sin scraping ni plataformas sin API oficial.
- Tokens OAuth cifrados en BD (AES-256).

---

## Documentación operativa

| Doc | Contenido |
|-----|-----------|
| `README.md` | Instalación, demo, smoke |
| `docs/ONBOARDING.md` | Flujo agencia → dashboard |
| `docs/DEPLOY.md` | Nginx, Supervisor, cron |
| `docs/RUNBOOK.md` | Incidentes |
| `docs/LAUNCH-CHECKLIST.md` | Criterios launch PRD §14 |
| `docs/staging-oauth-qa.md` | QA OAuth (staging vs local túnel) |
| `docs/platform-roadmap.md` | Roadmap fases |

---

## Sprint actual

1. QA OAuth en staging con credenciales reales (`docs/staging-oauth-qa.md`)
2. Criterios launch: 7 días ingesta estable, perf dashboard, PDF < 30s
3. Sentry DSN + uptime monitor en prod

---

## Skills del repo (agentes)

Mapa completo en `.cursor/rules/development-protocol.mdc` y `AGENTS.md`. Skills en `.cursor/skills/`.
