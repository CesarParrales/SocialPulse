# SocialPulse — Product Requirements Document
**Versión:** 1.1  
**Estado:** Draft para revisión técnica  
**Autor:** Product Owner  
**Última actualización:** Junio 2026  

**Cambios v1.1:** Stack de §5.1 alineado con `composer.json`, `package.json` y `context.md` (Laravel 13, React 19, nwidart ^13). Versiones exactas viven en manifests del repo.

---

## ⚠️ Advertencias Críticas Antes de Leer

> Antes de escribir una línea de código, las siguientes limitaciones de API deben estar resueltas o el producto no existe:

1. **Meta Stories no tiene histórico.** El sistema debe estar corriendo y conectado a la cuenta del cliente para capturar Stories antes de que expiren (24hrs). Si el cliente conecta su cuenta después de que una Story expiró, esos datos se perdieron para siempre. Esto no es un bug — es una decisión de Meta. Comunicarlo al cliente en onboarding.

2. **Los permisos de Meta tardan semanas en aprobarse.** Solicitar acceso a `instagram_manage_insights` y `pages_read_engagement` avanzado desde día uno, aunque el desarrollo empiece en 3 meses.

3. **Google Ads API requiere Developer Token aprobado manualmente.** Proceso separado con Google. Iniciar en paralelo.

4. **Los benchmarks de industria de Meta NO están disponibles por API.** Los datos comparativos internos de Meta no se exponen a terceros. Ver sección de Benchmarks para la estrategia real.

---

## 1. Visión del Producto

### 1.1 Problema que Resuelve

Los equipos de marketing y agencias que gestionan múltiples cuentas en Meta (Facebook, Instagram, Messenger) y Google Ads trabajan con datos fragmentados, exportaciones manuales, y herramientas que o bien cubren solo pagado, o bien cobran como si el cliente fuera Fortune 500.

El problema central no es la falta de datos — Meta y Google los tienen todos. El problema es que los exponen de forma fragmentada, en interfaces separadas, sin unificación, sin histórico de contenido efímero (Stories), y sin comparación accionable entre canales.

### 1.2 Solución

SocialPulse es una plataforma SaaS multi-cliente que unifica en un solo dashboard:
- Performance orgánico y pagado de Meta (Facebook, Instagram, Messenger)
- Performance de Google Ads
- Histórico persistente de Stories (captura activa antes de expiración)
- Comparaciones orgánico vs pagado, período vs período, canal vs canal
- Benchmarks propios por cuenta e industria (construido con data anónima de la plataforma)
- Reportes gráficos brandeables exportables

### 1.3 Diferenciadores Reales vs. Competencia

| Competidor | Qué hacen mal | Cómo SocialPulse los supera |
|---|---|---|
| Sprout Social | Caro ($249+/mes), overkill para agencias medianas | Precio accesible, enfoque en reporting |
| Porter Metrics | Solo conectores, no guarda histórico | Almacenamiento propio + Stories capture |
| Supermetrics | Solo mueve data, no analiza | Análisis, benchmarks y reportes nativos |
| Hootsuite | Fuerte en scheduling, débil en analytics profundo | Analytics primero, scheduling como fase 2 |
| Meta Business Suite | No unifica, no exporta bien, no guarda Stories | Todo lo que Meta debería ser y no es |

### 1.4 Usuario Objetivo (ICP)

**Primario:** Agencias digitales medianas en LATAM con 5-30 cuentas de clientes activas  
**Secundario:** Freelancers y consultores de marketing digital con 3-10 clientes  
**Terciario (Fase 2):** Marcas medianas con equipo interno de marketing  

**Perfil del usuario que opera la plataforma:**
- Social Media Manager / Performance Manager
- Nivel técnico: medio. Sabe leer datos, no sabe programar
- Dispositivo: desktop 80%, mobile 20%
- Flujo típico: revisa métricas semanalmente, genera reporte mensual para el cliente

---

## 2. Alcance del Producto

### 2.1 MVP — Lo que entra (P0)

- [ ] Autenticación multi-tenant (agencia + usuarios)
- [ ] Onboarding y conexión OAuth con Meta y Google Ads
- [ ] Ingesta automática de métricas orgánicas Facebook
- [ ] Ingesta automática de métricas orgánicas Instagram (Feed + Reels)
- [ ] Captura activa de Stories cada 6 horas (mientras están vivas)
- [ ] Almacenamiento histórico desde fecha de conexión
- [ ] Ingesta de campañas pagadas Meta (Facebook + Instagram + Messenger placements)
- [ ] Ingesta de Google Ads (campañas, ad groups, ads)
- [ ] Dashboard unificado por cuenta de cliente
- [ ] Comparación orgánico vs pagado
- [ ] Comparación período vs período (MoM, custom range)
- [ ] Benchmark interno de cuenta (vs propio histórico)
- [ ] Top contenidos por métrica
- [ ] Generador de reporte PDF brandeable
- [ ] Gestión de workspaces por cliente (multi-tenant)
- [ ] Roles: Admin agencia / Operador / Read-only cliente

### 2.2 Fase 2 — Post-MVP

- [ ] Benchmark de industria (requiere mínimo 100 cuentas activas en plataforma)
- [ ] Alertas y notificaciones (anomalías, caída de reach, presupuesto agotado)
- [ ] Comparación entre cuentas del mismo cliente
- [ ] Dashboard público read-only para cliente final (sin login)
- [ ] Exportación a Google Slides / PowerPoint
- [ ] Programación de contenido (Meta Publishing API)

### 2.3 Fase 3 — Roadmap

- [ ] Gestión básica de anuncios Meta (crear, pausar, modificar)
- [ ] TikTok Business API
- [ ] LinkedIn Page Analytics API
- [ ] Análisis de sentimiento en comentarios (NLP)
- [ ] Recomendaciones de contenido con IA
- [ ] Seguimiento de competidores (limitado por APIs disponibles)
- [ ] App móvil (React Native)

### 2.4 Fuera de Alcance — Siempre

- Acceso a mensajes privados de usuarios (imposible por política de Meta)
- Scraping de cualquier plataforma (riesgo legal y de cuenta)
- Métricas de plataformas sin API oficial documentada
- Publicación automática sin aprobación humana (decisión de negocio, no técnica)

---

## 3. Requerimientos Funcionales

### MÓDULO 01: Autenticación y Multi-tenancy

```
ACTOR: Super Admin (dueño de la agencia)
ACCIÓN: Crea workspace de agencia, invita operadores, crea sub-workspaces por cliente
RESULTADO: Estructura jerárquica agencia → clientes → usuarios
REGLA DE NEGOCIO:
  - Un operador solo ve los clientes asignados a él
  - El cliente read-only solo ve su propio workspace
  - El super admin ve todo
PRIORIDAD: P0
```

**Roles del sistema:**
| Rol | Puede hacer |
|---|---|
| `super_admin` | Todo. Gestión de plan, billing, todos los workspaces |
| `agency_admin` | Gestión de clientes, usuarios, conexiones de cuentas |
| `operator` | Ver y operar cuentas asignadas, generar reportes |
| `client_readonly` | Solo ver dashboard de su propia marca (Fase 2) |

---

### MÓDULO 02: Conexión de Cuentas (OAuth)

```
ACTOR: Agency Admin / Operator
ACCIÓN: Conecta cuenta de Meta Business o Google Ads mediante OAuth
RESULTADO: Plataforma obtiene access token, lista activos disponibles (páginas, cuentas IG, cuentas de anuncios), usuario selecciona cuáles monitorear
REGLA DE NEGOCIO:
  - Los tokens de Meta expiran. El sistema debe manejar refresh automático
  - Si el token expira y no se puede refrescar, notificar al usuario antes de que haya gap de datos
  - Un mismo activo (página FB) no puede estar en dos workspaces distintos
  - Al desconectar una cuenta, el histórico almacenado se conserva
PRIORIDAD: P0
```

**Activos conectables en MVP:**
- Páginas de Facebook
- Cuentas de Instagram Business/Creator vinculadas
- Cuentas de Meta Ads (Business Manager)
- Cuentas de Google Ads

**⚠️ Riesgo crítico de implementación:**
El manejo de token refresh de Meta es más complejo de lo que parece. Los Page Access Tokens tienen comportamientos distintos a los User Access Tokens. Los System User Tokens de Business Manager son la solución correcta para producción pero requieren verificación de negocio en Meta.

---

### MÓDULO 03: Ingesta de Data — Orgánico

```
ACTOR: Sistema (job programado)
ACCIÓN: Jala métricas de posts, reels, stories y cuenta de cada activo conectado
RESULTADO: Data almacenada en base de datos propia, disponible para dashboard
REGLA DE NEGOCIO:
  - Posts y Reels: job diario a las 02:00 AM (hora del cliente o UTC)
  - Stories: job cada 6 horas mientras haya stories activas en las últimas 24hrs
  - Si un job falla, reintentar 3 veces con backoff exponencial
  - Si falla 3 veces, marcar como error y notificar al admin
  - No sobreescribir data histórica. Appending only para métricas
PRIORIDAD: P0
```

**Métricas a capturar por tipo de contenido:**

| Tipo | Métricas |
|---|---|
| Facebook Post | reach, impressions, engagement, reactions, comments, shares, clicks, video_views (si aplica) |
| Facebook Page | fan_count, page_impressions, page_reach, page_engaged_users |
| Instagram Post | reach, impressions, likes, comments, shares, saved, profile_visits |
| Instagram Reel | plays, reach, likes, comments, shares, saved |
| Instagram Story | reach, impressions, taps_forward, taps_back, exits, replies |
| Instagram Account | followers_count, reach, impressions, profile_views |

**⚠️ Nota sobre Stories:**
El job de Stories debe verificar si hay contenido activo antes de hacer el call para no desperdiciar rate limit. Stories que ya expiraron no retornan métricas actualizadas — solo se actualiza data de Stories aún dentro de las 24hrs de vida.

---

### MÓDULO 04: Ingesta de Data — Pagado

```
ACTOR: Sistema (job programado)
ACCIÓN: Jala performance de campañas, ad sets y ads de Meta Ads y Google Ads
RESULTADO: Data de campañas almacenada, desglosada por placement y período
REGLA DE NEGOCIO:
  - Job diario para campañas del día anterior (data consolidada)
  - Job intradía cada 4 horas para campañas activas (data preliminar)
  - Distinguir claramente data "final" vs data "preliminar" en BD
  - Capturar desglose por placement: Facebook Feed, Instagram Feed, Instagram Stories, Messenger, Audience Network
PRIORIDAD: P0
```

**Métricas pagado a capturar:**

| Plataforma | Métricas |
|---|---|
| Meta Ads | spend, reach, impressions, clicks, CTR, CPM, CPC, conversions, ROAS, frequency, quality_ranking, engagement_rate_ranking |
| Google Ads | cost, impressions, clicks, CTR, CPC, conversions, conversion_value, ROAS, impression_share, quality_score |

---

### MÓDULO 05: Dashboard Principal

```
ACTOR: Operator / Agency Admin
ACCIÓN: Visualiza performance unificado de un cliente en un período
RESULTADO: Vista consolidada con métricas clave, gráficas de tendencia, top contenidos
REGLA DE NEGOCIO:
  - Selector de período: 7d, 14d, 30d, 90d, custom range
  - Comparación automática con período anterior (mismo número de días)
  - Indicadores de variación: flecha arriba/abajo + porcentaje de cambio
  - Si no hay suficiente histórico para comparar, indicarlo claramente
PRIORIDAD: P0
```

**Secciones del dashboard:**

**Overview Cards (KPIs principales)**
- Alcance total (orgánico + pagado combinado, o separado con toggle)
- Engagement rate promedio
- Inversión total (pagado)
- Crecimiento de seguidores (neto en el período)
- Impresiones totales
- Posts publicados en el período

**Gráficas de Tendencia**
- Reach diario (línea, orgánico vs pagado)
- Engagement rate diario (línea)
- Inversión diaria (barras)
- Crecimiento de comunidad (área)

**Breakdown por Canal**
- Facebook vs Instagram vs Messenger
- Feed vs Reels vs Stories

**Top Contenidos**
- Top 5 posts por reach
- Top 5 posts por engagement
- Top 5 posts por interacciones
- Preview de thumbnail, fecha, métricas clave

---

### MÓDULO 06: Comparaciones

```
ACTOR: Operator
ACCIÓN: Compara métricas entre dos períodos, entre canales, o entre orgánico y pagado
RESULTADO: Vista lado a lado con deltas y contexto
PRIORIDAD: P0
```

**Tipos de comparación disponibles en MVP:**
- Período A vs Período B (custom)
- Mes actual vs mes anterior
- Trimestre actual vs trimestre anterior
- Orgánico vs Pagado (mismo período)
- Facebook vs Instagram (mismo período)
- Feed vs Reels vs Stories (mismo período)

---

### MÓDULO 07: Benchmarks

```
ACTOR: Operator
ACCIÓN: Visualiza rendimiento de la cuenta contra benchmarks de referencia
RESULTADO: Contexto de si las métricas son buenas, normales o por debajo
PRIORIDAD: P0 (benchmark interno) / P1 (benchmark industria)
```

**MVP — Benchmark Interno (contra propio histórico):**
- Engagement rate promedio de los últimos 90 días
- Reach promedio por post (últimos 90 días)
- CPM promedio histórico
- Indicador visual: verde/amarillo/rojo vs su propio promedio

**Fase 2 — Benchmark de Industria:**
Con data anónima y agregada de todas las cuentas conectadas a SocialPulse, segmentado por:
- Categoría de industria (elegida en onboarding del cliente)
- Tamaño de comunidad (< 10k, 10k-100k, 100k-500k, 500k+)
- País / región
- Tipo de contenido

**⚠️ Comunicación al usuario:**
El benchmark de industria debe indicar claramente cuántas cuentas están en la muestra. Con menos de 30 cuentas por segmento, el benchmark no es estadísticamente representativo y no debe mostrarse.

---

### MÓDULO 08: Reportes

```
ACTOR: Operator / Agency Admin
ACCIÓN: Genera reporte visual del período seleccionado
RESULTADO: PDF descargable, brandeable con logo del cliente y colores personalizables
PRIORIDAD: P0
```

**Configuración de reporte:**
- Selección de período
- Selección de métricas a incluir
- Logo del cliente (upload)
- Colores primarios (hex)
- Nombre del reporte / título personalizable
- Incluir/excluir secciones (overview, orgánico, pagado, top contenidos, comparaciones)

**Formato de salida MVP:**
- PDF de alta calidad (A4 landscape)

**Formato de salida Fase 2:**
- Google Slides
- PowerPoint (.pptx)

---

## 4. Requerimientos No Funcionales

| Requerimiento | Especificación |
|---|---|
| **Disponibilidad** | 99.5% uptime mensual (excluye ventanas de mantenimiento programadas) |
| **Performance** | Dashboard carga en < 3 segundos con datos de 90 días |
| **Jobs de ingesta** | Stories: cada 6hrs. Daily: completado antes de las 06:00 AM UTC |
| **Escalabilidad** | Diseño que soporte hasta 1,000 workspaces de clientes sin re-arquitectura |
| **Seguridad** | OAuth 2.0, tokens cifrados en BD, HTTPS obligatorio, rate limiting en API |
| **Retención de datos** | Histórico indefinido mientras la cuenta esté activa. 90 días post-cancelación |
| **Multi-idioma** | Español e Inglés en MVP. Arquitectura i18n desde día 1 |
| **Zonas horarias** | Cada workspace configura su timezone. Jobs y gráficas respetan la timezone del cliente |
| **GDPR / Privacidad** | Datos de usuario de Meta no se comparten. Cumplimiento con política de uso de Meta API |

---

## 5. Arquitectura Técnica

### 5.1 Stack Seleccionado

> **Fuente de versiones:** `composer.json` y `package.json` mandan sobre este documento.
> Snapshot operativo en `context.md`. Política suite: `.cursor/skills/laravel-backend/references/stack-versions.md`.

| Capa | Tecnología | Justificación |
|---|---|---|
| **Backend** | Laravel 13, PHP ^8.3 | Queue system nativo, Horizon para jobs, ecosistema maduro, velocidad de desarrollo |
| **Arquitectura modular** | `nwidart/laravel-modules` ^13 | Monolito modular por dominio (`Modules/*`); merge-plugin por módulo |
| **Frontend** | React 19 + Inertia.js 2 + TypeScript + Vite 8 + Tailwind 3 | SPA sin API separada para el producto web; componentes reutilizables |
| **Auth / RBAC** | Laravel Breeze + Sanctum + Spatie Permission | Sesiones web, tokens futuros para API móvil; roles por workspace |
| **Base de datos principal** | PostgreSQL | Queries complejas de analytics, JSON columns para raw data flexible |
| **Cache / Queue** | Redis | Jobs de ingesta, cache de dashboards, sesiones |
| **Queue monitoring** | Laravel Horizon | Visibilidad de jobs de ingesta, reintentos, alertas de fallo |
| **Jobs scheduler** | Laravel Scheduler + Supervisor | Cron jobs para ingesta diaria y Stories watcher |
| **Storage** | AWS S3 / Cloudflare R2 | Thumbnails de contenido, exports PDF |
| **Generación PDF** | Browsershot (Puppeteer) + PhpSpreadsheet | Reportes de alta calidad desde HTML/CSS; anexos CSV/Excel |
| **Observabilidad** | Sentry + `/health` | Errores en producción; health checks para deploy |
| **Servidor** | DigitalOcean / Hetzner (VPS) | Costo-efectivo para MVP. Migrar a AWS si escala lo exige |
| **CI/CD** | GitHub Actions | Deploy automatizado a staging y producción |

### 5.2 Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENTES (Browser)                        │
└──────────────────────────────┬──────────────────────────────────┘
                               │ HTTPS
┌──────────────────────────────▼──────────────────────────────────┐
│                    Nginx (Reverse Proxy + SSL)                    │
└──────────────────────────────┬──────────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────────┐
│              Laravel Application (Monolito Modular)              │
│                                                                  │
│  ┌─────────────┐  ┌──────────────┐  ┌─────────────────────┐    │
│  │  Web Routes  │  │  API Routes  │  │  Console (Scheduler) │   │
│  └──────┬──────┘  └──────┬───────┘  └──────────┬──────────┘    │
│         │                │                      │               │
│  ┌──────▼──────────────────────────────────────▼──────────┐    │
│  │                    Core Modules                          │    │
│  │  Auth | Workspaces | Connections | Ingestion | Reports  │    │
│  └──────────────────────────────┬───────────────────────── ┘   │
└─────────────────────────────────┼───────────────────────────────┘
                                  │
          ┌───────────────────────┼───────────────────────┐
          │                       │                       │
┌─────────▼────────┐   ┌──────────▼──────────┐  ┌────────▼────────┐
│   PostgreSQL      │   │       Redis          │  │    AWS S3 / R2  │
│   (datos y        │   │  (cache + queues +   │  │  (thumbnails +  │
│    histórico)     │   │   sessions)          │  │   PDF exports)  │
└──────────────────┘   └─────────────────────┘  └─────────────────┘

WORKERS (separados del proceso web):
┌──────────────────────────────────────────────────────────────────┐
│                    Laravel Horizon (Queue Workers)                │
│                                                                  │
│  Queue: ingestion-daily   → DailyMetricsJob                     │
│  Queue: ingestion-stories → StoriesWatcherJob (cada 6hrs)        │
│  Queue: ingestion-paid    → PaidMetricsJob                       │
│  Queue: reports           → GenerateReportJob                    │
│  Queue: notifications     → NotificationJob                      │
└──────────────────────────────────────────────────────────────────┘

APIS EXTERNAS:
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│  Meta Graph API  │  │ Meta Marketing   │  │  Google Ads API  │
│  (orgánico FB)   │  │ API (pagado)      │  │                  │
└──────────────────┘  └──────────────────┘  └──────────────────┘
```

### 5.3 Estructura Modular del Monolito

```
app/
├── Modules/
│   ├── Auth/               # Autenticación, roles, permisos
│   ├── Workspaces/         # Multi-tenancy, clientes, usuarios
│   ├── Connections/        # OAuth, token management, activos
│   ├── Ingestion/          # Jobs de ingesta por plataforma
│   │   ├── Meta/
│   │   │   ├── OrganicFacebookJob.php
│   │   │   ├── OrganicInstagramJob.php
│   │   │   ├── StoriesWatcherJob.php
│   │   │   └── PaidMetaJob.php
│   │   └── Google/
│   │       └── GoogleAdsJob.php
│   ├── Analytics/          # Cálculos, aggregations, benchmarks
│   ├── Dashboard/          # Controllers y data para UI
│   ├── Reports/            # Generación de PDF, templates
│   └── Notifications/      # Alertas, emails, webhooks internos
```

### 5.4 Decisiones de Arquitectura (ADRs)

**ADR-001: Monolito Modular vs Microservicios**
- **Decisión:** Monolito modular con Laravel
- **Razón:** Equipo pequeño en MVP. Microservicios requieren madurez DevOps que no hay en fase inicial. Modular permite extraer servicios cuando el volumen lo justifique.
- **Trade-off negativo:** Scaling debe ser de toda la app, no por módulo. Aceptable hasta ~1,000 workspaces.
- **Revisión:** Cuando workers de ingesta consuman > 60% de recursos del servidor.

**ADR-002: Almacenamiento de tokens Meta**
- **Decisión:** Tokens cifrados con AES-256 en base de datos PostgreSQL, no en variables de entorno
- **Razón:** Tokens son por cuenta de cliente, no globales. Variables de entorno no escalan a multi-tenant.
- **Trade-off negativo:** Mayor complejidad en rotación de claves de cifrado.

**ADR-003: Inertia.js vs API + React standalone**
- **Decisión:** Inertia.js con React
- **Razón:** Elimina la necesidad de una API REST separada para el frontend propio. Reduce complejidad. Auth y autorización manejados por Laravel directamente.
- **Trade-off negativo:** Si en Fase 3 se agrega app móvil, necesitará API REST de todas formas. Se puede agregar después sin romper nada.

---

## 6. Modelo de Datos (ERD Simplificado)

```sql
-- Multi-tenancy
agencies (id, name, plan, billing_email, settings, created_at)
workspaces (id, agency_id, name, industry_category, timezone, settings)
users (id, agency_id, name, email, role, created_at)
workspace_users (workspace_id, user_id, role)

-- Conexiones de cuentas
platform_connections (
  id, workspace_id, platform [meta|google],
  access_token (encrypted), refresh_token (encrypted),
  token_expires_at, status [active|expired|error],
  meta_business_id, created_at
)

connected_assets (
  id, connection_id, asset_type [fb_page|ig_account|meta_ads|google_ads],
  platform_asset_id, name, is_active, metadata (jsonb)
)

-- Datos orgánicos
organic_posts (
  id, asset_id, platform_post_id, post_type [feed|reel|story],
  published_at, content_preview, thumbnail_url,
  raw_metrics (jsonb), captured_at
)

organic_metrics_daily (
  id, asset_id, date, metric_type, value, platform
)

stories_snapshots (
  id, asset_id, story_id, captured_at,
  reach, impressions, taps_forward, taps_back, exits, replies,
  expires_at, is_expired
)

account_metrics_daily (
  id, asset_id, date,
  followers, reach, impressions, profile_views,
  posts_published, engagement_rate
)

-- Datos pagados
ad_campaigns (
  id, asset_id, platform_campaign_id, name, status,
  objective, daily_budget, lifetime_budget, start_date, end_date
)

ad_metrics_daily (
  id, campaign_id, ad_set_id, ad_id, date, placement,
  spend, reach, impressions, clicks, ctr, cpm, cpc,
  conversions, conversion_value, roas, is_preliminary
)

-- Benchmarks
benchmark_snapshots (
  id, workspace_id, asset_id, period_start, period_end,
  engagement_rate_avg, reach_avg, cpm_avg, calculated_at
)

-- Reportes
reports (
  id, workspace_id, name, period_start, period_end,
  config (jsonb), status [pending|generating|ready|error],
  file_url, generated_at
)

-- Jobs tracking
ingestion_logs (
  id, asset_id, job_type, status [success|error|partial],
  records_ingested, error_message, executed_at, duration_ms
)
```

---

## 7. Integraciones Externas

### 7.1 Meta Graph API

| Endpoint | Para qué | Rate Limit |
|---|---|---|
| `/{page-id}/insights` | Métricas de página Facebook | 200 calls/hora por token |
| `/{ig-user-id}/media` | Posts y Reels de Instagram | 200 calls/hora |
| `/{ig-user-id}/stories` | Stories activas | 200 calls/hora |
| `/{media-id}/insights` | Métricas de post/reel específico | 200 calls/hora |
| `/{ig-user-id}/insights` | Métricas de cuenta IG | 200 calls/hora |

**Estrategia de rate limit:**
- Distribuir jobs en tiempo para no golpear el límite
- Si se acerca al límite, pausar job y reanudar en la siguiente ventana
- Priorizar Stories sobre métricas históricas cuando hay restricción

### 7.2 Meta Marketing API

| Endpoint | Para qué |
|---|---|
| `/act_{ad-account-id}/campaigns` | Lista de campañas |
| `/act_{ad-account-id}/insights` | Métricas de performance |
| Parámetro `breakdowns` | Desglose por placement, edad, género |

### 7.3 Google Ads API

- Requiere OAuth 2.0 + Developer Token aprobado
- Biblioteca oficial: `google-ads-php` o calls directas via GAQL (Google Ads Query Language)
- Queries en GAQL son similares a SQL — más mantenibles que los endpoints legacy

### 7.4 Manejo de Fallos de API

```
Para cada call externo:
1. Timeout máximo: 30 segundos
2. Retry automático: 3 intentos con backoff (1s, 5s, 15s)
3. Si falla 3 veces:
   a. Marcar job como error en ingestion_logs
   b. No bloquear otros jobs
   c. Notificar al admin si el fallo es persistente (>2 días)
4. Errores de token expirado → trigger de refresh automático
5. Errores de permisos revocados → notificar urgente al usuario
```

---

## 8. Seguridad

### 8.1 Autenticación y Autorización

- Autenticación: Laravel Sanctum (sesiones web) + tokens para futuras APIs
- Autorización: Spatie Permission (RBAC)
- 2FA opcional en MVP, recomendado para `super_admin` y `agency_admin`
- Sesiones con timeout configurable (default: 8 horas)

### 8.2 Datos Sensibles

| Dato | Cómo se protege |
|---|---|
| Tokens OAuth de Meta/Google | AES-256 cifrado en BD |
| Passwords de usuarios | bcrypt (Laravel default) |
| Datos de métricas | No son PII — sin cifrado especial necesario |
| Thumbnails de contenido | URLs presignadas con expiración, no públicas permanentes |

### 8.3 Cumplimiento Meta API

- La app de Meta debe tener Política de Privacidad publicada
- Los datos jalados por API solo pueden usarse para mostrar al propietario de la cuenta — no para entrenamiento de modelos ni venta
- Si un usuario desconecta su cuenta, los datos jalados vía API deben poder eliminarse (implementar función de "borrar datos")
- Almacenar historial de cuándo se accedió a datos y para qué (audit log básico)

### 8.4 Infraestructura

- HTTPS obligatorio. Certificados via Let's Encrypt / Cloudflare
- Variables de entorno en servidor, nunca en código ni git
- Firewall: solo puertos 80, 443, y SSH desde IPs específicas
- Backups diarios de PostgreSQL a S3 con retención de 30 días
- No exponer panel de Horizon públicamente — protegido por auth

---

## 9. Proceso de Aprobación de APIs (Timeline Crítico)

**Iniciar ANTES de escribir código:**

| Tarea | Tiempo estimado | Responsable |
|---|---|---|
| Crear Meta Developer App | 1-2 días | Dev Lead |
| Publicar Política de Privacidad | 1 día | Product Owner |
| Solicitar `instagram_manage_insights` | 1-3 semanas | Dev Lead |
| Solicitar `pages_read_engagement` avanzado | 1-3 semanas | Dev Lead |
| Verificar negocio en Meta Business Manager | 1-4 semanas | Product Owner |
| Solicitar Google Ads Developer Token (Basic Access) | 2-4 semanas | Dev Lead |
| Grabar video demo de uso de permisos Meta | 2-3 días | Product Owner |

**⚠️ Sin estos aprobados no hay producto. Empezar el día 1 del proyecto.**

---

## 10. Estimación de Esfuerzo (MVP)

### Equipo mínimo recomendado
- 1 Backend Developer (Laravel, experiencia con APIs externas)
- 1 Frontend Developer (React / Inertia)
- 1 Product Owner (Cesar) — definición, QA, feedback

*Un Full-Stack senior puede cubrir Backend + integraciones. Frontend puede ir como segundo recurso.*

### Estimación por módulo (días de trabajo)

| Módulo | Backend | Frontend | QA | Total |
|---|---|---|---|---|
| Setup infra, CI/CD, auth base | 8 | 5 | 3 | 16 |
| Multi-tenancy y workspaces | 6 | 8 | 3 | 17 |
| OAuth connections (Meta + Google) | 10 | 6 | 4 | 20 |
| Ingesta orgánico Facebook | 8 | 0 | 2 | 10 |
| Ingesta orgánico Instagram + Reels | 8 | 0 | 2 | 10 |
| Stories watcher + almacenamiento | 10 | 0 | 3 | 13 |
| Ingesta pagado Meta | 8 | 0 | 2 | 10 |
| Ingesta Google Ads | 8 | 0 | 2 | 10 |
| Dashboard principal | 5 | 15 | 4 | 24 |
| Comparaciones | 4 | 8 | 3 | 15 |
| Benchmarks internos | 5 | 6 | 2 | 13 |
| Generador de reportes PDF | 6 | 8 | 4 | 18 |
| Gestión de workspace UI | 3 | 8 | 2 | 13 |
| Notificaciones básicas (email) | 4 | 2 | 2 | 8 |
| **Buffer técnico (15%)** | | | | **27** |
| **TOTAL** | | | | **~194 días** |

**En semanas con equipo de 2 devs:** ~14-16 semanas  
**Estimación pesimista (problemas de API, cambios de scope):** 20 semanas

### Costo estimado LATAM

| Escenario | Costo |
|---|---|
| 2 devs mid-level ($2,500/mes c/u) | $14,000 - $20,000 |
| 1 dev senior full-stack ($4,000/mes) | $14,000 - $20,000 |
| Freelancers por módulo | $10,000 - $16,000 (mayor riesgo de coordinación) |

---

## 11. Matriz de Riesgos

| Riesgo | Prob | Impacto | Mitigación |
|---|---|---|---|
| Meta rechaza o demora aprobación de permisos | Alta | Crítico | Iniciar solicitudes día 1. Tener plan de demo funcional para la revisión |
| Token refresh falla silenciosamente y hay gap de datos | Media | Alto | Monitoreo activo de estado de tokens. Alert antes de expiración |
| Rate limit de Meta detiene ingesta en cuentas grandes | Media | Medio | Distribuir jobs. Priorización de Stories. Queue con throttle |
| Google Ads Developer Token bloqueado en "Basic Access" | Media | Alto | Aplicar a Standard Access apenas haya producto funcional |
| Stories expiradas antes de conectar cuenta | Alta | Medio | Comunicar en onboarding. No es un bug, es una limitación conocida |
| Scope creep en MVP (scheduling, ads manager, etc.) | Alta | Alto | PRD firmado. Fase 2 documentada. "No" como respuesta válida |
| Meta cambia su API (deprecaciones) | Media | Alto | Abstraer todos los calls a la API en una capa de servicio propia. Nunca llamar la API directamente desde los controllers |
| Datos de benchmark insuficientes para ser útiles | Alta | Medio | No mostrar benchmark de industria hasta tener n>30 por segmento. Comunicarlo |
| Competidor clona el producto antes de escalar | Media | Medio | Velocidad de ejecución y conocimiento del mercado LATAM como ventaja |

---

## 12. Modelo de Negocio (Referencial)

### Pricing sugerido

| Plan | Precio/mes | Límite de workspaces | Target |
|---|---|---|---|
| Starter | $49 | 3 workspaces | Freelancer / consultor |
| Agency | $129 | 10 workspaces | Agencia pequeña |
| Agency Pro | $299 | 30 workspaces | Agencia mediana |
| Enterprise | Custom | Ilimitado | Agencias grandes / in-house |

**Add-ons posibles:**
- Workspaces adicionales: $12/workspace/mes
- Reporte PDF adicionales (si se limita por plan): $5/reporte
- Acceso cliente read-only: $8/cliente/mes (Fase 2)

### Proyección conservadora

| Mes | Workspaces activos | MRR estimado |
|---|---|---|
| 3 | 15 | $735 |
| 6 | 40 | $3,200 |
| 12 | 120 | $10,800 |
| 18 | 300 | $28,000 |

---

## 13. Roadmap Visual

```
2026
│
├── MES 1-2: FUNDACIONES
│   ├── Solicitar aprobaciones API (Meta + Google) — DÍA 1
│   ├── Setup infra, CI/CD, environments
│   ├── Auth + multi-tenancy base
│   └── OAuth connections Meta y Google
│
├── MES 3-4: INGESTA CORE
│   ├── Jobs orgánico Facebook
│   ├── Jobs orgánico Instagram + Reels
│   ├── Stories Watcher (ingesta cada 6hrs)
│   └── Jobs pagado Meta + Google Ads
│
├── MES 4-5: DASHBOARD Y REPORTES
│   ├── Dashboard principal
│   ├── Comparaciones período vs período
│   ├── Benchmark interno
│   └── Generador de reportes PDF
│
├── MES 5-6: QA, BETA Y LAUNCH
│   ├── QA exhaustivo de ingesta con cuentas reales
│   ├── Beta privada con 3-5 clientes reales
│   ├── Correcciones post-beta
│   └── Launch público
│
2027
│
├── Q1: FASE 2
│   ├── Benchmark de industria (con base de 50+ cuentas)
│   ├── Alertas y notificaciones
│   ├── Dashboard público read-only para cliente
│   └── Export Google Slides / PPT
│
└── Q2-Q3: FASE 3
    ├── Programación de contenido
    ├── TikTok API
    ├── Gestión básica de anuncios
    └── IA: recomendaciones de contenido
```

---

## 14. Definition of Done

### Por feature/módulo:
- [ ] Código en rama `feature/*` con PR aprobado
- [ ] Tests unitarios escritos (mínimo happy path + error principal)
- [ ] Funciona en ambiente `staging` con datos reales de una cuenta de prueba
- [ ] No rompe ningún test existente
- [ ] Documentación de API o módulo actualizada si aplica
- [ ] Code review por al menos 1 persona además del autor

### Por sprint:
- [ ] Todos los P0 del sprint están en `Done`
- [ ] Ningún bug crítico abierto sin asignar
- [ ] Demo al Product Owner
- [ ] Staging actualizado y estable

### Para Launch:
- [ ] Ingesta de todas las fuentes funcionando sin errores por 7 días consecutivos con cuentas reales
- [ ] Stories watcher capturando y almacenando correctamente
- [ ] Dashboard carga en < 3 segundos con 90 días de data
- [ ] PDF generado correctamente con logo y colores custom
- [ ] Flujo de onboarding completo sin asistencia técnica
- [ ] Monitoreo y alertas activos (Sentry + uptime monitor)
- [ ] Runbook de incidentes documentado
- [ ] Política de privacidad publicada y aprobada por Meta

---

## 15. Próximos Pasos Inmediatos

1. **HOY:** Crear cuenta en `developers.facebook.com` y solicitar permisos básicos
2. **Esta semana:** Publicar dominio con Política de Privacidad y Términos de Servicio (puede ser simple, pero tiene que existir)
3. **Esta semana:** Solicitar Google Ads Developer Token
4. **Semana 1-2:** Contratar o confirmar equipo de desarrollo
5. **Semana 2:** Kickoff técnico con el equipo. Revisión de este PRD. ADRs firmados
6. **Semana 2:** Setup de repositorio, environments, CI/CD base
7. **Semana 3:** Primer job de ingesta funcionando en staging (Facebook orgánico como prueba de concepto)

---

*Este documento es un PRD vivo. Debe actualizarse con cada decisión técnica relevante, cambio de scope, o aprendizaje de beta. Versionar con fecha en el nombre del archivo.*

**socialpulse-prd-v1.0-jun2026.md**
