---
name: testing-strategy
description: >
  Guía la estrategia de testing para proyectos web y mobile: pirámide de tests,
  unitarios/integración/E2E, mocking y cobertura. Usar cuando el usuario mencione
  testing, tests, TDD, BDD, unitarios, integración, E2E, mocks, stubs, fixtures,
  cobertura de código, Vitest, Jest, Playwright, Cypress, PHPUnit, Pest, o cuando
  diga "cómo testeo esto", "cómo mockeo una dependencia", "qué debería testear",
  "cuánta cobertura necesito", "cómo estructuro mis tests", o cualquier variante.
  También usar cuando el usuario escriba código sin tests y haya lógica de
  negocio relevante.
---

# Testing Strategy Skill

Estrategia completa de testing para proyectos web y mobile.

**Pirámide y estrategia → este archivo**
**Tests unitarios — PHP/Laravel → `references/unit-php.md`**
**Tests unitarios — JS/TS → `references/unit-js.md`**
**Tests de integración y E2E → `references/integration-e2e.md`**
**Mocking y fixtures → `references/mocking.md`**

---

## Memoria

**Al iniciar** (solo si existen; no recargar lo ya en el chat):

1. `.cursor/project-memory.md` — gates y punteros (`context.md` suele listar `php artisan test`, etc.).
2. `LEARNINGS.md` de **esta skill** — solo `## Pendientes` si hay entradas.

**Al cerrar:** baseline/resultados de cobertura en el Test Plan (archivo o `docs/`); gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer `.cursor/project-memory.md` para gates locales de tests.
1. **Detectar el stack automáticamente** (no preguntar si se puede inferir):
   - `ls package.json` existe → stack JS/TS. Detectar runner: `grep -E '"(vitest|jest)"' package.json` → leer `references/unit-js.md`.
   - `ls composer.json` existe → stack PHP. `grep pestphp composer.json` → Pest; si no, PHPUnit → leer `references/unit-php.md`.
   - Ambos existen → proyecto híbrido: aplicar ambas references por capa.
2. **Inventariar lo que ya hay**: ejecutar `ls tests/ src/**/*.test.* 2>/dev/null` y el comando de tests existente (`npm test` o `php artisan test`/`vendor/bin/pest`). Gate: anotar cuántos tests pasan/fallan como baseline.
3. **Clasificar qué testear** con la tabla de decisión de abajo y la sección "Qué Testear". Si hay flujos críticos de negocio (login, pago, registro) → leer `references/integration-e2e.md`.
4. **Diseñar el Test Plan** usando la plantilla de `## Entregable`, con cobertura objetivo según tipo de proyecto (no 80% fijo).
5. **Implementar por capas**: unitarios primero (leer `references/mocking.md` si hay dependencias externas que aislar), luego integración, E2E solo para flujos críticos.
6. **Verificar**: ejecutar la suite completa y el reporte de cobertura (`npm run test -- --coverage` o `php artisan test --coverage`). Gate: todos los tests pasan (exit 0) y la cobertura cumple el criterio del Test Plan.
7. **Cerrar**: entregar el Test Plan actualizado con resultados reales, ejecutar `## Validación`, registrar hallazgos en `LEARNINGS.md`.

---

## Tabla de decisión — Qué capa usar

| Situación | Capa | Reference |
|---|---|---|
| Lógica condicional, cálculos, transformaciones | Unit | `references/unit-js.md` / `references/unit-php.md` |
| Endpoint HTTP, controller → service → repository | Integration | `references/integration-e2e.md` |
| Flujo crítico de negocio (login, checkout, onboarding) | E2E | `references/integration-e2e.md` |
| Dependencia externa (API, BD, tiempo) que aislar | Unit + mock | `references/mocking.md` |

---

## La Pirámide de Tests

```
                    ╱ E2E ╲
                   ╱ (pocos)╲
                  ╱──────────╲
                 ╱ Integración ╲
                ╱  (moderados)  ╲
               ╱────────────────╲
              ╱    Unitarios      ╲
             ╱  (la mayoría)       ╲
            ╱──────────────────────╲

Unitarios (70%):
  - Testean una unidad en aislamiento
  - Rápidos (ms), sin BD, sin red, sin filesystem
  - Para: lógica de negocio, transformaciones, validaciones
  - Feedback inmediato — corren en cada save

Integración (20%):
  - Testean varios componentes trabajando juntos
  - Velocidad media (segundos)
  - Para: endpoints HTTP, repositorios con BD real, flows de servicio
  - BD en memoria o real, no red externa

E2E (10%):
  - Testean flujos completos desde la perspectiva del usuario
  - Lentos (minutos), requieren app corriendo
  - Para: flujos críticos de negocio (login, checkout, onboarding)
  - No cubrir cada edge case — eso va en unitarios
```

---

## Qué Testear — Decisión Práctica

```
SÍ testear:
✅ Lógica de negocio con condiciones (if, switch, cálculos)
✅ Transformaciones de datos
✅ Validaciones de input
✅ Estados imposibles o edge cases que pueden crashear
✅ Integración entre capas (controller → service → repository)
✅ Flujos críticos de usuario (login, pago, registro)
✅ Regresiones — si se rompió una vez, test para que no vuelva

NO testear (o priorizar último):
❌ Getters/setters sin lógica
❌ Wrappers triviales de librerías externas
❌ Código generado automáticamente (migrations, factories)
❌ Configuración (env vars, constantes estáticas)
❌ UI pixel-perfect (screenshot testing tiene muy alto costo de mantenimiento)
```

---

## Definición de Done para Tests

```
Feature completada ≠ código escrito
Feature completada = código escrito + tests pasando + cobertura según el Test Plan

Mínimos por tipo de test:
- Unit:        happy path + edge cases + error path
- Integration: happy path + auth/permisos + validación
- E2E:         solo flujos críticos de negocio

Cobertura objetivo por capa (orientación, ajustar al tipo de proyecto):
- Lógica de negocio: 90%+
- Controllers/endpoints: 80%+
- Utilidades/helpers: 70%+
- UI components: 60%+ (opcional en muchos proyectos)

Criterio por tipo de proyecto (en vez de un 80% global fijo):
- Librería/SDK publicado:        90%+ global, mutation testing recomendado
- SaaS/app con lógica de negocio: 80%+ en services/dominio, 70%+ global
- MVP/prototipo con deadline:     unit en lógica core + integration en endpoints
                                  críticos; cobertura global no es gate
- Sitio de contenido/landing:     E2E mínimo del flujo principal; unit solo
                                  si hay lógica (formularios, cálculos)
```

---

## Anti-patrones de Testing

```
🔴 Tests que testean la implementación, no el comportamiento
   Problema: se rompen con cada refactor aunque el código funcione
   Solución: testear inputs/outputs, no cómo se implementó internamente

🔴 Tests que dependen del orden de ejecución
   Problema: pasan en aislamiento, fallan en suite completa
   Solución: cada test es independiente — setup/teardown explícito

🔴 Mock de todo, incluyendo la cosa que se está testeando
   Problema: el test no prueba nada real
   Solución: mockear solo dependencias externas, testear la lógica real

🔴 Tests frágiles que fallan por razones irrelevantes
   Problema: fechas hardcodeadas, IDs específicos, timeouts variables
   Solución: usar factories, dates relativas, timeouts generosos

🔴 Un test que verifica demasiado
   Problema: cuando falla, no se sabe qué falló
   Solución: un concepto por test — it should do X, not it should do X and Y and Z

🔴 Tests duplicados que testean lo mismo
   Problema: costo de mantenimiento alto sin beneficio
   Solución: DRY con shared fixtures y factories
```

---

## Velocidad — Hacer Tests Rápidos

```
Unitarios — deben ser instantáneos:
- Sin I/O (BD, red, filesystem)
- Sin sleep() o timeouts arbitrarios
- Sin side effects globales
- Paralelos por defecto

Integración — optimizar:
- BD en memoria (SQLite) o Docker con postgres prelevantado
- Transacciones con rollback en lugar de truncate
- Fixtures cargados una sola vez por suite (no por test)
- Paralelizar con workers separados por BD

E2E — contener el daño:
- Correr solo en CI, no en cada save
- Paralelizar por navegador o worker
- Reutilizar sesión autenticada entre tests del mismo grupo
- Fixtures en BD en lugar de crear todo por UI
```

---

## Setup de CI para Tests

```yaml
# .github/workflows/test.yml (genérico)
name: Tests

on: [push, pull_request]

jobs:
  unit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Run unit tests
        run: npm run test:unit  # o php artisan test --filter Unit

  integration:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:16-alpine
        env:
          POSTGRES_DB: test_db
          POSTGRES_USER: test
          POSTGRES_PASSWORD: test
        options: --health-cmd pg_isready
    steps:
      - uses: actions/checkout@v4
      - name: Run integration tests
        run: npm run test:integration  # o php artisan test --filter Feature

  e2e:
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'  # solo en main
    steps:
      - uses: actions/checkout@v4
      - name: Start app
        run: docker compose up -d
      - name: Run E2E tests
        run: npm run test:e2e

# Regla de oro:
# Si los tests de unit/integration pasan, no bloquear el PR por E2E
# E2E es gate solo para deploy a producción
```

---

## Métricas de Calidad de Tests

```
Mutation Score (con StrykerJS o infection PHP):
- Mide si los tests detectan cambios en el código
- > 70% = buena cobertura real
- Cobertura de líneas puede ser 100% con mutation score de 30% (tests vacíos)

Flakiness Rate:
- Tests que a veces pasan y a veces fallan sin cambios en código
- Meta: 0% flakiness
- Monitorear: un test que falla 3 veces seguidas → priorizar arreglo

Build Time:
- Unitarios: < 30 segundos en total
- Integración: < 3 minutos en total
- E2E: < 15 minutos en total
- Si supera estos límites → optimizar paralelismo o dividir suite
```

---

## Defaults si falta contexto

Si el usuario no especifica, asumir Y DECLARAR estos supuestos (máx. 1 pregunta
solo si es bloqueante, p. ej. no hay manifest de dependencias):

- **Stack**: el que indiquen `package.json`/`composer.json`. Híbrido → ambas capas.
- **Runner JS**: Vitest para proyectos con Vite/nuevos; Jest si ya está configurado.
- **Runner PHP**: Pest si está en `composer.json`; PHPUnit en caso contrario.
- **E2E**: Playwright por defecto (Cypress solo si ya existe en el proyecto).
- **Cobertura**: criterio por tipo de proyecto (ver Definición de Done), no 80% fijo.
- **Alcance**: solo el código tocado en la tarea actual + flujos críticos que dependan de él.

---

## Ejemplo input → output

**Input:** "Añadir tests al servicio que calcula KPIs de workspace."

**Output:** Test Plan con capa Unit (Pest) para `WorkspaceDashboardService` — casos happy, workspace vacío, scope sin activos; gate `php artisan test --filter=WorkspaceDashboardServiceTest` exit 0; cobertura del servicio ≥80% declarada en el plan.

---

## Validación

| Gate | Comando | Criterio |
|------|---------|----------|
| Suite PHP | `php artisan test` o `vendor/bin/pest` | exit 0 |
| Suite JS (si aplica) | `npm run test` / `npx vitest run` | exit 0 |
| Cobertura (si acordada) | `php artisan test --coverage` / `npm test -- --coverage` | ≥ objetivo del Test Plan |
| CI local (opcional) | mismos pasos que `.github/workflows/` | exit 0 |

---

## Entregable

Plantilla mínima del **Test Plan**:

```markdown
# Test Plan — <feature/módulo>

## Alcance
- Qué se testea: ...
- Qué queda fuera y por qué: ...

## Capas
| Capa | Qué cubre | Herramienta |
|---|---|---|
| Unit | ... | Vitest / Pest |
| Integration | ... | Supertest / Pest Feature |
| E2E | ... | Playwright |

## Casos
- [UNIT-01] <comportamiento> — happy path / edge / error
- [INT-01]  <endpoint> — happy / auth / validación
- [E2E-01]  <flujo crítico>

## Comandos de ejecución
- Unit: `npm run test:unit` / `php artisan test --filter Unit`
- Integration: `npm run test:integration` / `php artisan test --filter Feature`
- E2E: `npm run test:e2e`
- Cobertura: `npm test -- --coverage` / `php artisan test --coverage`

## Criterio de cobertura (según tipo de proyecto)
- Tipo de proyecto: <librería | SaaS | MVP | sitio de contenido>
- Objetivo: <ej. 80%+ en services, 70%+ global>
- Resultado real: <rellenar tras ejecutar>
```

---

## Skills relacionadas

- `git-workflow` — tests como gate de PR y convenciones de CI.
- `devops-base` — pipeline que ejecuta la suite.
- `laravel-backend` / `node-backend` — el stack cuyos patrones se testean.
- `react-patterns` — testing de componentes y hooks.

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
