# AGENTS.md — SocialPulse

Instrucciones para **agentes IA** (Cursor, Codex, etc.) que trabajan en este repositorio.

---

## Antes de codear

0. Leer **`.cursor/project-memory.md`** — gates, punteros y decisiones recientes (capa L2).
1. Leer **`context.md`** — snapshot técnico actual.
2. Confirmar alcance en **`socialpulse-prd.md`** (P0 vs Fase 2/3).
3. Revisar **`docs/platform-roadmap.md`** si la tarea toca plataformas o fases.
4. Cargar skill(s) de **`.cursor/skills/`** según `.cursor/rules/development-protocol.mdc`.
5. Tras implementar feature relevante: aplicar **`comprobacion-produccion`**.

Al cerrar tareas con decisión de arquitectura o convención nueva: actualizar **`.cursor/project-memory.md`** (no duplicar todo en `context.md` — solo la decisión y puntero).

Responder al usuario en **español**. Código, nombres de archivos y commits en **inglés** salvo copy/i18n (`lang/es`, `lang/en`).

---

## Arquitectura en una frase

Monolito **Laravel modular** (`Modules/*`) + **Inertia/React**; dominio separado por módulo nwidart; integraciones externas solo en **capa de servicio**.

```
Connections → Ingestion → Dashboard / Analytics / Reports
                ↓
         ConnectedAsset (scope por workspace)
```

---

## Módulos — dónde tocar qué

| Tarea | Módulo / ruta |
|-------|----------------|
| OAuth, activos, tokens | `Modules/Connections/` |
| Jobs ingesta, métricas raw | `Modules/Ingestion/` |
| KPIs, dashboard, público | `Modules/Dashboard/` |
| Compare, benchmarks | `Modules/Analytics/` |
| PDF, anexos, narrativa | `Modules/Reports/` |
| Calendario, publish Meta | `Modules/Content/` |
| Credenciales agencia/plataforma | `Modules/Settings/` |
| Agencias, workspaces, roles | `Modules/Workspaces/` |
| UI páginas | `resources/js/Pages/` |
| i18n | `lang/es/app.php`, `lang/en/app.php` |
| Rutas globales auth/health | `routes/`, `app/` |

**No** llamar APIs de Meta/Google/TikTok/LinkedIn/YouTube desde controllers. Usar servicios en `Modules/Connections` o `Modules/Ingestion`.

---

## Plataformas (Junio 2026)

Todas en `PlatformCatalog` con status `available`:

- **Meta** — OAuth + System User; organic FB/IG, stories, paid; publish Content.
- **Google Ads** — OAuth + developer token; paid.
- **TikTok, LinkedIn, YouTube** — OAuth; organic; dashboard + PDF; sin publish MVP.

Nueva plataforma → ADR en `docs/` → conexión → ingesta → dashboard/reporte (patrón Fase D).

---

## Convenciones de código

- **Diff mínimo**; no refactorizar fuera de alcance (`karpathy-guidelines`).
- Controllers: auth, validación, Inertia/redirect; lógica en services.
- Módulos nuevos: seguir `laravel-modular` (estructura nwidart, ServiceProvider, routes, tests Feature).
- UI: `atomic-design` (atoms → pages); performance React: `vercel-react-best-practices`.
- Formularios/accesibilidad: `web-interface-guidelines`.
- Tokens OAuth: modelos cifrados; **nunca** en `.env` commiteado ni logs.
- Tests: PHPUnit en módulo afectado; `RefreshDatabase`; Http::fake para APIs.

---

## Entorno — errores comunes de agentes

| ❌ Incorrecto | ✅ Correcto |
|---------------|-------------|
| `cp .env.staging.example .env` en Mac local | Complementar `.env` local; staging example solo en VPS |
| Usar `APP_URL` local como redirect OAuth con túnel | Overrides `*_REDIRECT_URI` con HTTPS del túnel |
| Borrar config local (DB driver, session) al añadir OAuth | Solo añadir variables OAuth faltantes |
| Publicar benchmark industria con n<30 | Ocultar segmento hasta n≥30 |

Patrón local documentado en `context.md` y `docs/staging-oauth-qa.md`.

---

## Comandos que debes conocer

```bash
php artisan test                                    # suite completa
php artisan test Modules/Connections/tests/...    # módulo acotado
php artisan socialpulse:smoke --auth
php artisan socialpulse:integrations:check
npm run build
vendor/bin/pint --dirty
```

Nombres reales de ingesta: `ingestion:facebook-organic`, `ingestion:instagram-organic`, `ingestion:tiktok-organic`, etc. (no `ingestion:organic-facebook`).

---

## Skills obligatorias (mapa resumido)

| Contexto | Skill |
|----------|-------|
| SDLC, deploy, CI | `software-engineering-sdlc` |
| Módulos Laravel | `laravel-modular` |
| Evitar over-engineering | `karpathy-guidelines` |
| React/Inertia | `vercel-react-best-practices` |
| Componentes UI | `atomic-design` |
| UX / implementación UI | `analisis-ux-implementacion-ui` |
| Motion / polish | `emil-design-eng` |
| A11y / forms | `web-interface-guidelines` |
| Cierre feature / pre-deploy | `comprobacion-produccion` |
| Security review explícito | `security-best-practices` |

Ruta: `.cursor/skills/<nombre>/SKILL.md`.

> **Nota:** `.cursor/skills/vercel-react-best-practices/AGENTS.md` es documentación interna de esa skill (reglas React), **no** sustituye este archivo.

---

## Git y PRs

- No commitear salvo petición explícita del usuario.
- No commitear `.env`, credenciales, dumps.
- Mensajes de commit: imperativo, en inglés, foco en el *why*.

---

## Definition of done (feature P0/P1)

- [ ] Alineado con PRD o ADR documentado
- [ ] Tests Feature relevantes en verde
- [ ] `npm run build` OK si hay cambios TS/React
- [ ] i18n ES/EN si hay copy UI nuevo
- [ ] `context.md` / roadmap actualizados si cambia arquitectura o fase
- [ ] Pasada `comprobacion-produccion` para cierre de módulo

---

## Referencias rápidas

- PRD: `socialpulse-prd.md`
- Contexto vivo: `context.md`
- Roadmap: `docs/platform-roadmap.md`
- Protocolo Cursor: `.cursor/rules/development-protocol.mdc`
- QA OAuth: `docs/staging-oauth-qa.md`
