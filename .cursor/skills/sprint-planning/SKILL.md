---
name: sprint-planning
description: >
  Guía la gestión ágil de proyectos de software: estimación, backlog refinement,
  definición de done, retrospectivas. Usar cuando el usuario mencione planning,
  sprint, backlog, estimación, story points, velocity, refinement, retrospectiva,
  standup, scrum, kanban, épicas, user stories, o cuando diga "cómo estimo esto",
  "cómo organizo el backlog", "cuánto tarda esto", "cómo mejoro el proceso del
  equipo", "qué entra en el sprint", "cómo escribo una user story", o cualquier
  variante relacionada con gestión ágil de proyectos de software.
---

# Sprint Planning Skill

Gestión ágil de proyectos de software: desde el backlog hasta el deploy.

**Backlog y User Stories → `references/backlog.md`**
**Estimación y Velocity → `references/estimation.md`**
**Ceremonias Ágiles → `references/ceremonies.md`**
**Métricas y Mejora Continua → `references/metrics.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — velocity reciente, sprint goal anterior, alcance P0.
2. PRD/backlog fuente si project-memory lo indica.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** sprint plan en `docs/` o herramienta del equipo; riesgos → project-memory si afectan alcance; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución — Sprint Planning Runbook

0. **Memoria** — leer project-memory; alinear backlog con alcance firmado/PRD.
1. **Preparar el backlog.** Leer `references/backlog.md`: top 20-30 items con
   criterios de aceptación, épicas divididas en historias INVEST, dependencias
   identificadas. Gate: ningún item candidato sin criterios de aceptación.
2. **Calcular la velocity disponible.** Leer `references/estimation.md`:
   promedio de los últimos 3-5 sprints. Sin histórico → aplicar el default de
   calibración (ver Defaults) y declararlo.
3. **Definir el Sprint Goal.** Una oración que resume el sprint y permite
   decidir qué cortar si surgen problemas. Sin goal claro → no continuar.
4. **Ajustar la capacidad real.** Descontar ausencias conocidas (vacaciones,
   festivos), soporte/bugs recurrentes y ceremonias. Documentar el cálculo en
   el output.
5. **Seleccionar el sprint backlog.** El equipo elige items que quepan en la
   capacidad ajustada, estimados con planning poker
   (`references/estimation.md`), con tareas técnicas identificadas por
   historia. Gate verificable: suma de SP del sprint ≤ capacidad ajustada.
6. **Registrar riesgos del sprint.** Dependencias externas, items con alta
   incertidumbre, señales de mal planning (ver checklist abajo). Producir el
   output con la plantilla de `## Entregable`. Para métricas de seguimiento
   durante el sprint, leer `references/metrics.md`; para la mecánica de las
   ceremonias, `references/ceremonies.md`.
7. **Validación y cierre** — ejecutar `## Validación`; registrar gaps en `LEARNINGS.md`.

---

## Defaults si falta contexto

Asumir y **declarar en el entregable**:

| Campo faltante | Default asumido |
|----------------|-----------------|
| Velocity (sin histórico) | 20-25 SP/sprint para 3 devs, marcado como calibración sprints 1-3 |
| Duración del sprint | 2 semanas |
| Marco de trabajo | Scrum si equipo ≥3 y roadmap definido; Kanban/Scrumban si <3 o mucho soporte |
| Capacidad productiva | 6h productivas/día por dev |
| Tamaño máximo de historia | 8 SP — más grande se divide antes de entrar al sprint |
| Definition of Done | La DoD base de esta skill (o la variante reducida si el equipo es <3) |

Pregunta bloqueante única permitida: si no existe backlog ni descripción de
trabajo alguno para planificar.

---

## Principios Ágiles para Equipos de Software

```
1. Software funcionando > documentación exhaustiva
   Demostrar progreso con código que corre, no con documentos

2. Responder al cambio > seguir un plan
   El plan es un punto de partida, no un contrato

3. Iteraciones cortas reducen riesgo
   Mejor entregar algo pequeño frecuentemente que algo grande tarde

4. El equipo estima, no el manager
   Los que hacen el trabajo son los únicos que pueden estimar

5. La velocidad promedio es más importante que la estimación perfecta
   Medir la velocidad real del equipo y usarla para planificar

6. El proceso sirve al equipo, no al revés
   Si una ceremonia no aporta valor → cambiarla o eliminarla
```

---

## Scrum vs Kanban — Cuándo Usar Cada Uno

```
Scrum (sprints de 1-2 semanas):
  ✅ Features nuevas con scope relativamente predecible
  ✅ Equipo necesita estructura y rituales
  ✅ Stakeholders quieren ver progreso en ciclos regulares
  ✅ Roadmap mediano plazo definido
  ❌ Trabajo altamente impredecible (mucho soporte/bugs urgentes)
  ❌ Equipo < 3 personas (overhead no vale)

Kanban (flujo continuo):
  ✅ Trabajo de mantenimiento y soporte
  ✅ Muchos bugs urgentes e interrupciones
  ✅ Equipos pequeños (2-3 personas)
  ✅ Trabajo con tamaño muy variable
  ❌ Features grandes que necesitan coordinación entre varios
  ❌ Stakeholders que necesitan predictibilidad de releases

Scrumban (híbrido, frecuente en la práctica):
  - Tablero Kanban con límites WIP
  - Iteraciones de 1-2 semanas para planning y retro
  - Sin sprints forzados con scope fijo
  - Reuniones cuando hay suficiente trabajo acumulado
```

---

## Framework de Sprint (2 semanas)

```
Semana 1:
  Lunes       → Sprint Planning (2h máx para sprint de 2 semanas)
  Martes-Vie  → Desarrollo
  Diario      → Standup (15 min máx)

Semana 2:
  Lunes-Jue   → Desarrollo
  Jueves      → Backlog Refinement / Grooming (1h)
  Viernes     → Sprint Review (demo, 1h) + Retrospectiva (1h)
               → Fin de sprint / inicio del siguiente lunes

Artifacts:
  Product Backlog   → lista priorizada de todo lo que se puede hacer
  Sprint Backlog    → lo que el equipo se comprometió en este sprint
  Increment         → software funcionando al final del sprint
```

---

## Definition of Done — No Negociable

```
Una tarea está "Done" cuando cumple TODOS estos criterios.
Sin excepto. Sin "está casi lista".

Criterios base para todo el equipo:

Código:
□ Funcionalidad implementada según criterios de aceptación
□ Code review aprobado por al menos 1 par
□ Sin errores de linter o type-checker
□ Sin console.log/dd()/dump() olvidados

Tests:
□ Tests unitarios escritos para lógica de negocio nueva
□ Tests de integración para endpoints nuevos o modificados
□ Todos los tests pasando en CI (no solo local)
□ Cobertura no ha bajado del umbral definido

Deploy:
□ Desplegado en ambiente de staging
□ QA/smoke test en staging completado
□ Migraciones de BD incluidas y reversibles
□ Variables de entorno nuevas documentadas

Documentación:
□ Documentación de API actualizada (si aplica)
□ README actualizado (si el setup cambió)
□ CHANGELOG o PR description actualizado

Ready para producción:
□ Feature flag disponible si el cambio es riesgoso
□ Monitoreo/alertas configurados para funcionalidades críticas nuevas
□ Product Owner ha aceptado el criterio de aceptación
```

### DoD reducida — equipos < 3 personas

```
Para equipos de 1-2 personas el overhead de la DoD completa no se sostiene.
Mínimo no negociable:

□ Funcionalidad cumple los criterios de aceptación
□ Sin errores de linter/type-checker ni debug statements olvidados
□ Tests para la lógica de negocio crítica pasando en CI
□ Migraciones reversibles y variables de entorno documentadas
□ Probado en staging (o entorno equivalente) antes de producción
□ Self-review con diff completo si no hay par disponible para code review

Lo que se recorta (y se asume como riesgo declarado):
→ Review por par obligatorio → self-review disciplinado
→ Cobertura con umbral → tests solo en lógica crítica
→ Feature flags y monitoreo → solo en cambios de alto riesgo
```

---

## Ejemplo input → output

**Input:** "Planning sprint 12 — equipo 3 devs, velocity ~22 SP."

**Output:** Sprint Goal una frase; capacidad 20 SP tras 1 día festivo; backlog 5 items P0 con AC; total 19 SP; riesgo: dependencia API Meta. Gate: suma SP ≤ capacidad.

---

## Validación

| Gate | Acción | Criterio |
|------|--------|----------|
| Sprint goal | output | una oración clara |
| Capacidad | cálculo documentado | SP ajustados ≤ velocity ref |
| Backlog items | cada fila | criterios de aceptación |
| Suma SP | sprint backlog | ≤ capacidad final |
| Checklist | Checklist de Sprint Planning | ítems aplicables ✓ |

---

## Entregable

Output estándar del sprint planning:

```markdown
# Sprint [N] — [fechas]

## Sprint Goal
[Una oración que resume el sprint]

## Capacidad
- Velocity de referencia: X SP ([histórico 3-5 sprints / default calibración])
- Ajustes: [ausencias, soporte, festivos] → Capacidad final: X SP

## Sprint Backlog
| # | Item | Prioridad | SP | Responsable | Dependencias |
|---|------|-----------|----|-------------|--------------|
| 1 | | P0 | | | |
| **Total** | | | **X SP** | | |

## Riesgos del sprint
- [riesgo / dependencia externa / item con incertidumbre alta + plan]

## Supuestos declarados
- [defaults aplicados por falta de contexto]
```

---

## Checklist de Sprint Planning

```
Antes del planning (responsabilidad del PO):
□ Backlog priorizado y actualizado
□ Top 20-30 items con criterios de aceptación escritos
□ Épicas grandes divididas en historias estimables
□ Dependencias entre items identificadas

Durante el planning:
□ Revisar velocity promedio de los últimos 3-5 sprints
□ Ajustar por ausentismo conocido (vacaciones, festivos)
□ El equipo selecciona items que quepan en su velocity
□ Cada item tiene criterios de aceptación claros
□ Tareas técnicas identificadas para cada historia
□ Sprint goal definido (una oración que resume el sprint)

Señales de mal planning:
⚠️ El PO mete más stories de las que caben "porque son urgentes"
⚠️ Se estimaron items sin criterios de aceptación claros
⚠️ El equipo no participó activamente en la estimación
⚠️ No hay sprint goal claro → sin dirección en caso de problemas
```

---

## Skills relacionadas

| Skill | Relación |
|-------|----------|
| `software-project-analysis` | El roadmap y las user stories del análisis alimentan el backlog |
| `propuestas-contratos` | El alcance firmado define qué entra al backlog; los CRs lo modifican |
| `project-pricing` | La velocity real calibra futuras estimaciones de precio |
| `team-onboarding` | El proceso ágil del equipo es parte del onboarding |

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
