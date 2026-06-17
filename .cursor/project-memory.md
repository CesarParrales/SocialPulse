# Memoria del proyecto — SocialPulse

Capa L2: decisiones recientes, gates y punteros. **No duplica** `context.md` ni el PRD.

`last_updated: 2026-06-12`

## Fuentes de verdad (leer según la tarea)

| Prioridad | Archivo | Cuándo |
|-----------|---------|--------|
| 1 | `socialpulse-prd.md` | Alcance MVP, P0 vs Fase 2/3, ADRs de producto |
| 2 | `context.md` | Stack, módulos, plataformas, env, comandos |
| 3 | `AGENTS.md` | Reglas operativas para agentes |
| 4 | `.cursor/rules/development-protocol.mdc` | Mapa de skills y flujo mínimo |
| 5 | `docs/platform-roadmap.md` | Plataformas y fases |
| 6 | ADRs en `docs/` | Decisiones de arquitectura por integración |

Ante conflicto código vs PRD → **PRD manda** hasta ADR o documento de cambio explícito.

## Stack (snapshot breve)

Detalle completo en `context.md`:

- Backend: Laravel 13, PHP 8.3+, módulos nwidart (`Modules/*`)
- Frontend: Inertia + React 19 (`resources/js/`)
- BD / colas: PostgreSQL, Redis, Horizon
- Tests: PHPUnit/Pest por módulo; CI en push a `main`/`master`

## Gates locales

Comandos antes de cerrar tareas técnicas (ver `context.md` § comandos):

```bash
php artisan test
npm run build
vendor/bin/pint --test   # si toca PHP estilo
```

Última verificación registrada: *(actualizar al cerrar features P0)*

## Decisiones recientes

### 2026-06-11 · Suite Dev Studio Fase A sincronizada
- Contexto: 31 skills de dominio con `## Memoria` + `## Validación`; memoria L2 activa.
- Decisión: skills suite en `.cursor/skills/` complementan las propias; leer `project-memory` paso 0 antes de tareas.
- Afecta: todos los agentes; mapa en `development-protocol.mdc`.
- Verificación: 0 diffs suite ↔ proyecto en las 33 skills instaladas.

### 2026-06-12 · PRD stack alineado con repo (v1.1)
- Contexto: `socialpulse-prd.md` §5.1 decía Laravel 11; el repo usa Laravel 13 (`composer.json`).
- Decisión: PRD v1.1 — stack explícito (Laravel 13, React 19, Inertia 2, nwidart ^13, Sentry, etc.); manifests mandan sobre texto estático.
- Afecta: PRD, skill `laravel-modular`; coherente con `context.md`, `README.md`, `development-protocol.mdc`.
- Verificación: grep sin "Laravel 11" en docs de producto del repo.

### 2026-06-12 · Recursos UX/UI gratuitos (suite)
- Contexto: catálogo `learning-sources.md` + checklist `ux-principles-free.md` sincronizados en triple install (origen, global, repo).
- Decisión: recursos **free** en dos modos — **literal** (principios, 21st.dev, FormiUX, iconos) con gates; **inspiración** (MotionSites, Acceseo, UX Pilot) sin spec directo. Tendencias ≠ verdades; revisar `trends-watch.md` cada 90d.
- Afecta: `ui-web-modern`, `ui-audit`, `ux-architecture`, `atomic-design`, `team-onboarding`; canvas auditoría pestaña Recursos.
- Verificación: `./install-local.sh --no-global --project` ejecutado; diffs 0 en refs nuevas.
- Punteros skills:
  - `.cursor/skills/ui-web-modern/references/learning-sources.md`
  - `.cursor/skills/ui-audit/references/ux-principles-free.md`
  - `.cursor/skills/ui-web-modern/references/trends-watch.md`
- Reporte suite: `suite-dev-studio/docs/evolution-report-2026-06-12.md`

<!--
### YYYY-MM-DD · título breve
- Contexto: ...
- Decisión: ...
- Afecta: módulo/skill ...
-->

## Integraciones opcionales

| Herramienta | Estado | Notas |
|-------------|--------|-------|
| Graphify | `disabled` | Activar con skill `graphify-integration` tras `uv tool install graphifyy`; no `alwaysApply` |

## Skills del workspace

**Propias del repo** (prioridad en `.cursor/skills/`): `laravel-modular`, `software-engineering-sdlc`, `comprobacion-produccion`, `karpathy-guidelines`, `vercel-react-best-practices`, `atomic-design`, `analisis-ux-implementacion-ui`, `emil-design-eng`, `web-interface-guidelines`, `security-best-practices`.

**Suite Dev Studio** (complementarias — usar junto con las propias, no sustituir reglas):

| Tarea | Skill suite | Skill propia (si aplica) |
|-------|-------------|--------------------------|
| Backend Laravel (API, actions, resources) | `laravel-backend` | `laravel-modular` |
| Tests y cobertura | `testing-strategy` | tests del módulo en `Modules/*` |
| Componentes UI (atoms → pages) | `atomic-design` | `vercel-react-best-practices` |
| Recursos UX free (literal / inspiración) | `ui-web-modern` → `learning-sources.md` | `web-interface-guidelines` |
| Auditoría UI + principios UX free | `ui-audit` → `ux-principles-free.md` | `analisis-ux-implementacion-ui` |
| Exploración repo grande | `graphify-integration` | — |

Mapa completo: `.cursor/rules/development-protocol.mdc`.
