# QA OAuth en staging — SocialPulse

Guía para validar conexiones reales **antes de producción**.

> **⚠️ No copies `.env.staging.example` sobre tu `.env` local.**  
> Ese archivo es plantilla para un **servidor staging dedicado** (VPS/hosting).  
> En desarrollo local (Valet + túnel Cloudflare) **conserva tu `.env` actual** y solo **añade** variables que falten. Ver [Desarrollo local con túnel](#desarrollo-local-con-túnel-cloudflare).

---

## Servidor staging (VPS / hosting)

Usar [`.env.staging.example`](../.env.staging.example) **solo** en la máquina staging:

```bash
# En el servidor remoto (/var/www/socialpulse), NO en tu Mac:
cp .env.staging.example .env
# Completar credenciales OAuth y APP_URL=https://staging…
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=Modules\\Workspaces\\Database\\Seeders\\DemoSeeder

php artisan socialpulse:integrations:check
php artisan socialpulse:integrations:check --require=meta,google
php artisan socialpulse:smoke --auth
php artisan socialpulse:smoke --auth --oauth
```

`--require` debe fallar (exit 1) si falta alguna plataforma listada.

---

## Desarrollo local con túnel Cloudflare

Patrón típico (**no** reemplazar el `.env` entero):

```env
# Local normal (Valet)
APP_URL=http://socialtoolsdev.test

# Cloudflare Tunnel — obligatorio para HTTPS en línea
TRUSTED_PROXIES=*

# OAuth: redirects públicos del túnel (deben coincidir con consolas Meta/Google/etc.)
META_REDIRECT_URI=https://tu-dominio-tunel/connections/meta/callback
GOOGLE_REDIRECT_URI=https://tu-dominio-tunel/connections/google/callback
TIKTOK_REDIRECT_URI=https://tu-dominio-tunel/connections/tiktok/callback
LINKEDIN_REDIRECT_URI=https://tu-dominio-tunel/connections/linkedin/callback
YOUTUBE_REDIRECT_URI=https://tu-dominio-tunel/connections/youtube/callback

# Credenciales (añadir sin tocar DB/Redis/session existentes)
META_APP_ID=
META_APP_SECRET=
# … resto desde .env.example
```

**Conservar en local:** `DB_*`, `SESSION_DRIVER`, `QUEUE_CONNECTION`, `CACHE_STORE`, `APP_KEY`, comentarios y overrides del túnel.

**Tomar de `.env.example`:** bloques OAuth nuevos (TikTok, LinkedIn, YouTube) y credenciales que aún no tengas.

Comandos de verificación (misma máquina local):

```bash
php artisan socialpulse:integrations:check
php artisan socialpulse:smoke --auth --oauth
```

En local con túnel, `{APP_URL}` sigue siendo Valet; los **redirect OAuth** deben ser la URL **HTTPS del túnel**, no `http://socialtoolsdev.test`.

---

## Credenciales en la aplicación (recomendado)

Las **claves OAuth** se guardan en SocialPulse (BD cifrada), no en git:

| Rol | Dónde |
|-----|--------|
| Super admin | **Plataforma → Integraciones** (defaults globales) |
| Super admin (multi-agencia) | **Plataforma → Agencias → Integraciones** (por agencia) |
| Admin agencia | **Configuración → Integraciones** |

**Migración desde `.env` (una vez):**

```bash
# Tras completar META_*, GOOGLE_ADS_*, etc. en .env del servidor:
php artisan socialpulse:integrations:import-env --platform
php artisan socialpulse:integrations:import-env --agency=1
```

En la UI (super admin): botón **Importar desde .env** cuando hay valores pendientes.

Los **redirect URI** siguen en `.env` (infraestructura); cópialos desde la pestaña Integraciones.

---

## Checklist por plataforma

Marcar ✅ tras completar flujo end-to-end con cuenta de prueba.

| # | Plataforma | Pre-requisitos `.env` | Flujo | Activos | Ingesta |
|---|------------|----------------------|-------|---------|---------|
| 1 | **Meta OAuth** | `META_APP_ID`, `META_APP_SECRET`, redirect en app Meta | Conexiones → Conectar Meta → callback → seleccionar páginas/IG | ✅ | `ingestion:facebook-organic` / `instagram-organic` |
| 2 | **Meta System User** | `META_SYSTEM_USER_*`, `META_BUSINESS_ID` | Conectar Meta (System User) | ✅ | Igual que OAuth |
| 3 | **Google Ads** | `GOOGLE_ADS_*`, developer token | Conectar Google Ads | Cuenta ads | `ingestion:paid-google` |
| 4 | **TikTok** | `TIKTOK_CLIENT_KEY/SECRET` | Conectar TikTok → seleccionar cuenta | ✅ | `ingestion:tiktok-organic` |
| 5 | **LinkedIn** | `LINKEDIN_CLIENT_ID/SECRET` | Conectar LinkedIn → seleccionar páginas | ✅ | `ingestion:linkedin-organic` |
| 6 | **YouTube** | `YOUTUBE_CLIENT_ID/SECRET` (Google Cloud) | Conectar YouTube → seleccionar canales | ✅ | `ingestion:youtube-organic` |

### Redirect URIs (deben coincidir exactamente)

| Plataforma | URI |
|------------|-----|
| Meta | `META_REDIRECT_URI` o `{APP_URL}/connections/meta/callback` |
| Google Ads | `GOOGLE_REDIRECT_URI` o `{APP_URL}/connections/google/callback` |
| TikTok | `TIKTOK_REDIRECT_URI` o `{APP_URL}/connections/tiktok/callback` |
| LinkedIn | `LINKEDIN_REDIRECT_URI` o `{APP_URL}/connections/linkedin/callback` |
| YouTube | `YOUTUBE_REDIRECT_URI` o `{APP_URL}/connections/youtube/callback` |

## Verificación post-conexión

1. **Dashboard** — KPIs visibles para activos seleccionados (período 30 días).
2. **Comparar / Benchmarks** — sin errores con scope de activos.
3. **Informe PDF** — generar reporte; descargar PDF + anexo CSV/Excel.
4. **Horizon** — jobs `ingestion-daily` sin fallos 24 h.
5. **Notificaciones** — sin alertas de token expirado inmediato tras conectar.

## Ingesta manual (smoke)

```bash
php artisan ingestion:facebook-organic --sync
php artisan ingestion:instagram-organic --sync
php artisan ingestion:tiktok-organic --sync
php artisan ingestion:linkedin-organic --sync
php artisan ingestion:youtube-organic --sync
php artisan ingestion:paid-meta --sync
php artisan ingestion:paid-google --sync
```

Revisar `ingestion_logs` y ausencia de errores en Sentry (`SENTRY_ENVIRONMENT=staging`).

## Criterio de cierre staging

- [ ] `--require=meta,google,tiktok,linkedin,youtube` pasa (o subconjunto acordado para piloto)
- [ ] `--oauth` devuelve 302 en redirects de plataformas configuradas
- [ ] Al menos 1 workspace con datos ingeridos 7 días consecutivos
- [ ] Informe PDF generado < 30 s
- [ ] Stories watcher activo 6 h (cuenta con stories)

Actualizar [LAUNCH-CHECKLIST.md](./LAUNCH-CHECKLIST.md) al cerrar cada ítem.
