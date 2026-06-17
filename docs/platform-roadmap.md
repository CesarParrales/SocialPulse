# SocialPulse — Plan plataforma polifuncional

**Versión:** 1.0 · Junio 2026  
**Fuente de verdad funcional:** `socialpulse-prd.md`  
**Referencia de entrega:** informe mensual Chili's (Mayo 2026)

---

## 1. Visión ampliada

SocialPulse evoluciona de *dashboard de métricas* a **hub de presencia de marca**:

```
CONECTAR → RECOLECTAR → ANALIZAR → INFORMAR → ACTUAR
```

| Pilar | Qué resuelve | Estado actual |
|-------|--------------|---------------|
| **Conectar** | OAuth, activos, credenciales por agencia | ✅ MVP (Meta + Google Ads) |
| **Recolectar** | Ingesta orgánica, paid, stories, histórico | ✅ MVP en curso |
| **Analizar** | Dashboard, comparaciones, benchmarks | ✅ MVP en curso |
| **Informar** | PDF brandeable, anexos CSV/Excel, dashboard público | ✅ Fases A–B |
| **Actuar** | Publicación Meta + calendario editorial | ✅ Fase C (base); otras plataformas 📋 |

**Principio:** solo APIs oficiales; sin scraping. Cada plataforma se integra con el mismo contrato (`PlatformCatalog` + pipelines de ingesta).

---

## 2. Mapa de plataformas

| Plataforma | Canales / activos | Analytics orgánico | Analytics paid | Stories | Publicación | Fase |
|------------|-------------------|:------------------:|:--------------:|:-------:|:-----------:|------|
| **Meta** | Fanpage, Instagram, Ads | ✅ | ✅ | ✅ (IG) | ✅ (Content) | **MVP** |
| **Google** | Google Ads | — | ✅ | — | — | **MVP** |
| **TikTok** | Business account | ✅ | 📋 | — | 📋 | Fase 3 |
| **LinkedIn** | Company page | ✅ | 📋 | — | 📋 | Fase 3 |
| **YouTube** | Channel | ✅ | — | — | 📋 | Fase 3 |
| **X (Twitter)** | Profile | ⚠️ API restringida | — | — | Fase 3+ | Evaluar |
| **Competidores** | Input manual | 📋 | — | — | — | Fase 2+ |

Implementación técnica: `Modules/Connections/app/Support/PlatformCatalog.php`.

---

## 3. Arquitectura modular objetivo

```
Connections     → catálogo, OAuth, activos, tokens
Ingestion       → jobs por canal (organic_*, paid_*, stories_*)
Analytics       → agregaciones, benchmarks, comparaciones
Dashboard       → UI operativa, KPIs, contenido
Reports         → PDF/PPTX, narrativa, anexos
Content (F2)    → calendario, borradores, publishing API
Notifications   → alertas, tokens, fallos ingesta
Settings        → credenciales, prefs workspace
```

**Contrato entre módulos:** servicios reciben `Collection<ConnectedAsset>` filtrada por scope; nunca mezclar agregaciones sin filtro explícito.

---

## 4. Fases de producto

### Fase A — Cerrar MVP analytics + informe (ahora → 4 semanas)

Objetivo: paridad con informe Chili's en **datos + PDF**, no en copy manual.

- [x] Selector de activos en dashboard / compare / benchmarks
- [x] Top contenido visual + recientes + stories
- [x] KPIs configurables en `workspace.settings`
- [x] Seed demo analítico (`DemoAnalyticsSeeder`)
- [x] **PDF por canal** (Facebook orgánico, Instagram orgánico, Paid)
- [x] Top 3 posts + Top 3 reels **por canal** en PDF
- [x] Métricas con variación % y labels Meta
- [x] Anexo tabular en PDF
- [x] Narrativa automática básica (reglas: alcance ↑ / interacción ↓)

### Fase B — Informe consultivo + extensibilidad (4–8 semanas)

- [x] Template PDF estilo deck (portada, divisores, slides)
- [x] Resumen ejecutivo integrado FB + IG
- [x] Competidores manuales + brief IA externo (sin API)
- [x] Export CSV anexo
- [x] Export Excel anexo
- [x] Dashboard público read-only (cliente final)

### Fase C — Gestión de contenido (8–16 semanas, PRD Fase 2)

- [x] Módulo `Content`: calendario editorial (base)
- [x] Borradores + flujo aprobación agencia → cliente (base)
- [x] Publicación Meta (Feed + Reels) vía Publishing API
- [x] Vincular post publicado → métricas ingeridas

### Fase D — Nuevas plataformas (continuo)

Por plataforma: ADR → conexión → ingesta → dashboard → reporte.

Orden sugerido: TikTok → LinkedIn → YouTube.

- [x] **TikTok** — OAuth, activo, ingesta orgánica, dashboard e informe PDF
- [x] **LinkedIn** — OAuth, activo, ingesta orgánica, dashboard e informe PDF
- [x] **YouTube** — OAuth, activo, ingesta orgánica, dashboard e informe PDF

---

## 5. Modelo de datos (extensiones previstas)

| Entidad | Propósito | Fase |
|---------|-----------|------|
| `competitor_accounts` | Benchmark externo manual | B |
| `content_calendar_entries` | Planificación | C |
| `content_drafts` | Borradores + estado | C |
| `published_content_links` | Enlace post plataforma ↔ organic_posts | C |
| `platform_ingestion_profiles` | Config por canal (frecuencia, métricas) | A |

Sin migraciones nuevas en Fase A salvo necesidad de narrativa/competidores.

---

## 6. ADRs pendientes

| ID | Decisión |
|----|----------|
| ADR-004 | Catálogo de plataformas y capacidades (`PlatformCatalog`) |
| ADR-005 | Informe PDF multi-sección por canal |
| ADR-006 | Narrativa automática basada en reglas (no LLM en MVP) |
| ADR-007 | Módulo Content separado vs extensión Ingestion |
| ADR-008 | Competidores manuales + insight IA asistida (prompt export, sin API obligatoria) |
| ADR-009 | Integración TikTok Business API (`docs/adr-009-tiktok-integration.md`) |
| ADR-010 | Integración LinkedIn Company Page API (`docs/adr-010-linkedin-integration.md`) |
| ADR-011 | Integración YouTube Data API v3 (`docs/adr-011-youtube-integration.md`) |

---

## 7. Sprint actual (implementación inmediata)

1. **Pre-launch staging** — ejecutar [staging-oauth-qa.md](./staging-oauth-qa.md) con credenciales reales
2. **Criterios launch (7 días)** — ingesta estable, stories, perf dashboard, PDF < 30 s
3. **Observabilidad prod** — Sentry DSN, uptime monitor externo

Completado recientemente: Fase D (TikTok/LinkedIn/YouTube), Excel anexo, UI activos Conexiones, `socialpulse:integrations:check`.

---

## 8. Métricas de éxito Fase A

- Generar informe mensual Marca Alfa (demo) indistinguible en **estructura** del PDF Chili's
- Dashboard filtrable por activo con KPIs persistidos en workspace
- 100% tests módulos Dashboard, Reports, Connections en CI
- Tiempo generación PDF < 30s en staging

---

## 9. Fuera de alcance (recordatorio PRD)

- Scraping o métricas sin API oficial
- Benchmark industria Meta nativo
- Publicación automática sin aprobación humana
- Gestión de ads (crear/pausar campañas) antes de Fase 3
