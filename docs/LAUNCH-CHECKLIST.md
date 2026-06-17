# Launch checklist — SocialPulse

Estado de los criterios de **PRD §14** y operación pre-launch. Actualizar en cada release candidate.

**Leyenda:** ✅ Implementado / documentado · 🟡 Parcial · ⬜ Pendiente (requiere entorno real o legal)

---

## Infraestructura y observabilidad

| Item | Estado | Notas |
|------|--------|-------|
| CI (tests + build + Pint) | ✅ | `.github/workflows/ci.yml` |
| Health liveness `/up` | ✅ | Laravel default |
| Health readiness `/health` | ✅ | DB + Redis (si aplica) |
| Sentry SDK integrado | ✅ | `sentry/sentry-laravel`, `config/sentry.php` |
| Sentry DSN en staging/prod | ⬜ | Configurar `SENTRY_LARAVEL_DSN` |
| Uptime monitor externo | ⬜ | [DEPLOY.md](./DEPLOY.md) §9 — `/up` y `/health` |
| Runbook de incidentes | ✅ | [RUNBOOK.md](./RUNBOOK.md) |
| Onboarding E2E documentado | ✅ | [ONBOARDING.md](./ONBOARDING.md) |
| Guía de despliegue | ✅ | [DEPLOY.md](./DEPLOY.md) |
| Horizon + scheduler en prod | ✅ | [DEPLOY.md](./DEPLOY.md) + `deploy/supervisor/` |

---

## Producto MVP (P0)

| Item | Estado | Notas |
|------|--------|-------|
| Multi-tenancy + roles | ✅ | Workspaces, Spatie Permission |
| OAuth Meta + Google + Fase D (TikTok, LinkedIn, YouTube) | ✅ | Connections module |
| Verificación credenciales staging | ✅ | `php artisan socialpulse:integrations:check` |
| Guía QA OAuth manual staging | ✅ | [staging-oauth-qa.md](./staging-oauth-qa.md) |
| Meta System User (prod) | ✅ | Ver `.env.example` |
| Ingesta orgánica programada | ✅ | Cola `ingestion-daily` |
| Stories watcher 6h | 🟡 | Verificar 7 días en staging con cuenta real |
| Dashboard + analytics | ✅ | Dashboard module |
| Benchmarks interno + industria | ✅ | n≥30 industria |
| Reportes PDF | ✅ | Cola `reports` |
| Notificaciones email + in-app | ✅ | Token, ingesta |
| i18n ES/EN | ✅ | |
| Cliente readonly | ✅ | Dashboard solo lectura |
| Política de privacidad publicada | ✅ | `/legal/privacy` (revisar con legal antes de prod) |
| Términos de servicio | ✅ | `/legal/terms` |
| Smoke test E2E automatizado | ✅ | `php artisan socialpulse:smoke --auth` |

---

## Criterios de launch (7 días reales)

| Item | Estado | Notas |
|------|--------|-------|
| Ingesta sin errores 7 días con cuentas reales | ⬜ | Validar en staging → prod |
| Stories capturando correctamente | ⬜ | Cuenta con stories activas |
| Dashboard < 3 s con 90 días de data | ⬜ | Perf test en staging |
| PDF con logo/colores custom | 🟡 | Probar con workspace configurado |
| Onboarding sin soporte técnico | 🟡 | Seguir [ONBOARDING.md](./ONBOARDING.md) con usuario piloto |

---

## Configuración producción (referencia)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.socialpulse.app
APP_VERSION=1.0.0

SENTRY_LARAVEL_DSN=https://…@sentry.io/…
SENTRY_ENVIRONMENT=production
SENTRY_RELEASE=1.0.0
SENTRY_TRACES_SAMPLE_RATE=0.1

QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
```

### Smoke test E2E

```bash
# Rutas públicas (health, legal, login)
php artisan socialpulse:smoke

# Credenciales OAuth/API (staging)
php artisan socialpulse:integrations:check
php artisan socialpulse:integrations:check --require=meta,google,tiktok

# Incluye dashboard, conexiones, reportes, contenido
php artisan db:seed --class=Modules\\Workspaces\\Database\\Seeders\\DemoSeeder
php artisan socialpulse:smoke --auth

# Redirects OAuth (solo plataformas configuradas en .env)
php artisan socialpulse:smoke --auth --oauth
```

### Comandos post-deploy

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan horizon:terminate
php artisan sentry:test
php artisan socialpulse:smoke --auth
curl -sf https://app.socialpulse.app/health
```

### URLs legales (Meta App Review)

| Página | URL |
|--------|-----|
| Privacidad | `{APP_URL}/legal/privacy` |
| Términos | `{APP_URL}/legal/terms` |

Contacto legal: `LEGAL_CONTACT_EMAIL` en `.env`.

---

## Smoke test E2E (manual)

Usar [ONBOARDING.md](./ONBOARDING.md) §10 como guión. Tiempo objetivo: < 30 min sin soporte.

---

*Actualizar este archivo al cerrar cada ítem ⬜ en staging o producción.*
