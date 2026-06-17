# Runbook de incidentes — SocialPulse

Documento operativo para staging y producción. Complementa el PRD §14 (Launch checklist).

**Instalación inicial:** [DEPLOY.md](./DEPLOY.md)

**Contacto on-call:** definir en el gestor de incidentes del equipo (PagerDuty, Slack `#incidents`, etc.).

---

## 1. Monitoreo y alertas

| Señal | Herramienta | Umbral sugerido | Acción |
|-------|-------------|-----------------|--------|
| App caída | Uptime monitor → `GET /up` | 2 fallos consecutivos (1 min) | Página on-call |
| Degradación infra | Uptime monitor → `GET /health` | HTTP 503 o `status != ok` | Investigar DB/Redis |
| Errores 5xx / excepciones | Sentry | Spike > 5× baseline 15 min | Revisar issue, rollback si P0 |
| Colas atascadas | Horizon / Redis | `pending` > 500 o lag > 30 min | Ver §4 |
| Token Meta expirado | Email + notificación in-app | Cualquier alerta | Ver §5 |
| Ingesta fallida | Email admin agencia | 3 reintentos fallidos | Ver §6 |

### Endpoints de health

```bash
# Liveness (Laravel built-in)
curl -sf https://app.socialpulse.app/up

# Readiness (DB + Redis si aplica)
curl -sf https://app.socialpulse.app/health | jq
```

Respuesta esperada de `/health`:

```json
{
  "status": "ok",
  "checks": {
    "database": { "status": "ok" },
    "redis": { "status": "ok" }
  },
  "app": "SocialPulse",
  "environment": "production",
  "version": "1.0.0"
}
```

### Sentry

- Variable: `SENTRY_LARAVEL_DSN` (vacía = desactivado).
- Release: `SENTRY_RELEASE` o `APP_VERSION` en cada deploy.
- Traces: `SENTRY_TRACES_SAMPLE_RATE=0.1` en prod (ajustar según costo).
- **No enviar PII:** `SENTRY_SEND_DEFAULT_PII=false` (default).

Verificar tras deploy:

```bash
php artisan sentry:test
```

---

## 2. Arquitectura crítica (recordatorio)

```
Web (PHP-FPM) → PostgreSQL
              → Redis (cache, sessions, queues)
              → Horizon (workers)
Scheduler (cron) → php artisan schedule:run
                 → ingesta diaria 02:00
                 → Stories watcher cada 6h
                 → token refresh 05:00 UTC
                 → avisos token 06:00 UTC
```

Colas Horizon: `ingestion-daily`, `ingestion-stories`, `ingestion-paid`, `reports`, `notifications`, `default`.

---

## 3. Severidades

| Nivel | Definición | Ejemplo | SLA respuesta |
|-------|------------|---------|---------------|
| **P0** | Plataforma inutilizable o pérdida de datos activa | App 503, DB caída, ingesta 0% 24h | 15 min |
| **P1** | Función core degradada | OAuth roto, PDFs no generan, dashboard vacío | 1 h |
| **P2** | Impacto parcial | Un workspace, un job fallido esporádico | 4 h |
| **P3** | Cosmético / mejora | Copy UI, reporte lento pero funcional | Backlog |

---

## 4. Colas y Horizon atascadas

**Síntomas:** jobs en `pending`, dashboard sin datos nuevos, Horizon muestra backlog.

```bash
# Estado
php artisan horizon:status

# Reiniciar workers (graceful)
php artisan horizon:terminate
# Supervisor debe relanzar horizon

# Inspeccionar failed jobs
php artisan queue:failed
php artisan queue:retry all   # solo si la causa raíz está resuelta
```

**Causas frecuentes:**

- Redis caído o sin memoria → revisar `GET /health`, logs Redis.
- Rate limit Meta/Google → esperar ventana; jobs reintentan con backoff.
- Worker muerto → verificar Supervisor/systemd en el servidor.

---

## 5. Tokens OAuth / conexiones Meta

**Síntomas:** notificación "Token expirado", conexión en estado `expired` o `error`, activos vacíos.

**Diagnóstico:**

1. Workspace → Conexiones → estado de la plataforma.
2. Logs: buscar `RefreshPlatformTokenJob`, `Meta Graph API`.
3. Sentry: filtrar por `connection_id` en contexto si está instrumentado.

**Resolución:**

| Modo | Acción |
|------|--------|
| OAuth usuario | Admin reconecta Meta desde el workspace (`Conectar Meta OAuth`). |
| System User | Verificar `META_SYSTEM_USER_ACCESS_TOKEN` y permisos BM; reconectar System User. |

**Prevención:** refresh automático diario 05:00 UTC; aviso proactivo 7 días antes.

---

## 6. Fallos de ingesta

**Síntomas:** email "Fallo de ingesta", activo sin datos nuevos, job en `failed_jobs`.

**Flujo:**

1. Identificar job en `failed_jobs` (payload → `OrganicFacebookJob`, etc.).
2. Leer mensaje de error (rate limit, permiso revocado, token inválido).
3. Si permiso revocado → P1, notificar agencia para reconectar.
4. Si rate limit → reintentar manualmente tras 1h: `php artisan queue:retry {id}`.
5. Tras 3 fallos el sistema ya notificó al admin; documentar en post-mortem si es recurrente.

**Stories:** recuerda que Stories **no tienen histórico en Meta**. Si la cuenta se conectó tarde, los datos previos no existen (limitación de API, no bug).

---

## 7. Deploy y rollback

### Deploy (checklist rápido)

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm ci && npm run build
php artisan horizon:terminate
# reload PHP-FPM
```

### Rollback

1. Revertir al tag/commit anterior en el servidor.
2. `composer install`, `npm run build` si hubo cambios de assets.
3. **Migraciones:** solo revertir si la migración nueva es incompatible; preferir migración forward-fix.
4. `php artisan horizon:terminate`
5. Smoke test: `/up`, `/health`, login, dashboard de un workspace demo.

---

## 8. Smoke tests post-deploy

- [ ] `GET /up` → 200
- [ ] `GET /health` → 200, `database: ok`
- [ ] Login con usuario demo
- [ ] Dashboard carga (`/workspaces/{id}/dashboard`)
- [ ] Horizon activo (`php artisan horizon:status`)
- [ ] `php artisan socialpulse:smoke --auth`
- [ ] Sentry recibe evento de prueba (staging)

---

## 9. Escalado y contactos externos

| Proveedor | Escenario | Acción |
|-----------|-----------|--------|
| Meta | Permisos rechazados, app review | developers.facebook.com → App Dashboard |
| Google Ads | Developer token suspendido | Google Ads API Center |
| Hosting | CPU/mem/disco | Panel cloud + alertas |
| PostgreSQL | Conexiones agotadas | PgBouncer / aumentar pool |

---

## 10. Post-mortem (plantilla breve)

1. **Qué pasó** (timeline UTC)
2. **Impacto** (usuarios, workspaces, duración)
3. **Causa raíz**
4. **Detección** (¿alerta funcionó?)
5. **Acciones correctivas** (código, runbook, alerta nueva)
6. **Acciones de seguimiento** (owner + fecha)

---

*Última revisión: junio 2026 — mantener alineado con `socialpulse-prd.md` y `.env.example`.*
