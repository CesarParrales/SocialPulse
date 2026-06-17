---
name: software-project-analysis
description: >
  Análisis y planeamiento integral de proyectos de software web y mobile. Cubre desde el primer
  contacto con el cliente hasta la arquitectura técnica lista para desarrollo. Activa SIEMPRE que
  el usuario mencione: analizar un proyecto, planear un sistema, evaluar viabilidad técnica,
  estimar un desarrollo, definir arquitectura, hacer discovery, levantar requerimientos, planear
  sprint, definir stack, crear roadmap, o cuando diga "quiero hacer una app/web/sistema",
  "necesito saber cuánto cuesta", "qué tecnología uso", "cómo arranco este proyecto",
  "evalúa mi idea", "tenemos un cliente nuevo", o cualquier variante. También activa cuando
  el usuario pegue un brief, RFP, o descripción de producto aunque no pida explícitamente análisis.
  Skill de máxima profundidad: cubre comportamiento del cliente, riesgos ocultos, arquitectura,
  estimaciones, roadmap, y entregables listos para equipo técnico y stakeholders.
---

# Software Project Analysis Skill

Skill de análisis y planeamiento de proyectos software. Cubre el ciclo completo: cliente →
discovery → arquitectura → estimación → roadmap → entregables.

**Matriz de fases y detalle de cada fase → ver `references/phases.md`**
**Plantillas de entregables y stacks → ver `references/templates.md`**
**Política de versiones del stack → `../laravel-backend/references/stack-versions.md`**
**Patrones de riesgo conocidos → ver `references/risk-patterns.md`**
**Señales de comportamiento del cliente → ver `references/client-behavior.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — PRD, stack acordado, decisiones de arquitectura.
2. Brief/RFP del cliente si project-memory lo referencia.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** análisis en `docs/`; decisiones de stack/alcance → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory y PRD antes de re-analizar desde cero.
1. **Clasificar el input** (Nivel 0-3, ver Clasificación de Input) y leer la
   matriz de activación de fases en `references/phases.md` para saber qué
   profundidad aplica a cada fase.
2. **Completar el mapa contextual mínimo** (abajo). Si faltan >4 campos,
   aplicar la Regla de Suposiciones — no bloquearse preguntando.
3. **Ejecutar las fases F1-F3** (discovery → requerimientos → arquitectura)
   siguiendo el detalle de `references/phases.md`. En F1 leer
   `references/client-behavior.md` para detectar señales del cliente; en F3
   usar los stacks de `references/templates.md` con matriz de decisión.
4. **Ejecutar F4-F5** (estimación PERT por módulo + roadmap por fases con
   gates de entrada/salida), según `references/phases.md`.
5. **Ejecutar F6** (riesgos) con el catálogo de `references/risk-patterns.md`:
   matriz probabilidad × impacto + plan de contingencia para exposición alta.
6. **Producir los entregables** según audiencia (ver Entregable y
   `references/templates.md` para plantillas completas), incluyendo el bloque
   de Suposiciones si aplicó la regla.
7. **Validar contra las Reglas de Calidad del Análisis** y ejecutar `## Validación`.
   Gate verificable: el documento final no debe tener placeholders sin resolver —
   `grep -nE 'TODO|\[completar\]|\[pendiente\]' analisis.md` debe devolver vacío.
8. **Cierre** — registrar gaps en `LEARNINGS.md`; actualizar project-memory con decisiones clave.

---

## Clasificación de Input

### Nivel 0 — Idea vaga
> "Quiero hacer algo como Instagram pero para mascotas"

Señales: sin requerimientos, sin presupuesto, sin timeline, sin equipo.
Acción: **Discovery Interview completo** antes de cualquier análisis técnico.

### Nivel 1 — Brief básico
> Descripción del producto con funcionalidades mencionadas pero sin detallar.

Señales: sabe qué quiere, no sabe cómo ni cuánto.
Acción: **Completar gaps + Análisis técnico + Estimación rough.**

### Nivel 2 — Brief detallado
> Documento con requerimientos funcionales, usuarios objetivo, restricciones.

Señales: hay contexto suficiente para análisis profundo.
Acción: **Análisis completo + Arquitectura + Estimación detallada + Roadmap.**

### Nivel 3 — RFP / Licitación
> Documento formal con especificaciones técnicas, criterios de evaluación, SLAs.

Señales: proceso formal, múltiples proveedores evaluando.
Acción: **Análisis completo + Propuesta técnica formal + Deck ejecutivo.**

---

## Mapa Contextual Mínimo

Antes de producir cualquier análisis, confirmar estos datos. Si faltan, preguntar
**solo los más críticos** (máx 4 preguntas por turno):

### Bloque Negocio
- [ ] ¿Qué problema real resuelve? (no la feature, el problema)
- [ ] ¿Quién es el usuario final? (perfil, comportamiento, contexto de uso)
- [ ] ¿Existe algo similar en el mercado? ¿Cómo se diferencia?
- [ ] ¿Cuál es el modelo de negocio / monetización?
- [ ] ¿Hay métricas de éxito definidas?

### Bloque Técnico
- [ ] ¿Existe sistema legado o integración requerida?
- [ ] ¿Hay stack preferido o restricciones tecnológicas?
- [ ] ¿Cuántos usuarios esperados? (día 1 vs. escala proyectada)
- [ ] ¿Requiere offline, tiempo real, geolocalización, pagos, IA?
- [ ] ¿Hay requisitos de seguridad / compliance (GDPR, PCI, HIPAA)?

### Bloque Proyecto
- [ ] ¿Cuál es el presupuesto disponible? (rango)
- [ ] ¿Cuál es la fecha límite real? (MVP vs. producto completo)
- [ ] ¿Quién es el equipo actual? (devs, diseño, PM)
- [ ] ¿Hay decisiones ya tomadas que no se pueden cambiar?
- [ ] ¿Quién toma decisiones técnicas en el cliente?

### Regla de Suposiciones

**Si faltan más de 4 campos del mapa contextual → producir igualmente el
análisis con un bloque "Suposiciones" explícito + los riesgos asociados a
cada suposición, en vez de solo preguntar.** Cada suposición no validada se
registra también en la matriz de riesgos (F6). Preguntar sigue siendo válido
para lo bloqueante, pero nunca entregar las preguntas como único output.

---

## Fases de Análisis (resumen)

**El detalle completo de cada fase vive en `references/phases.md`.**

| Fase | Objetivo | Output clave |
|------|----------|--------------|
| F1 Discovery | Entender el problema real, no el síntoma | JTBD, perfil de usuario, análisis competitivo, señales del cliente |
| F2 Requerimientos | Traducir negocio a specs verificables | Funcionales por módulo (P0-P3), no funcionales, integraciones, user stories |
| F3 Arquitectura | Definir el sistema antes de codear | Stack con matriz de decisión, diagramas C4 (Mermaid/ASCII), ADRs, modelo de datos, seguridad, APIs |
| F4 Estimación | Rangos honestos, no números mágicos | PERT por módulo + factores de ajuste + costo de no calidad |
| F5 Roadmap | Maximizar valor, minimizar riesgo | MVP real (≤12 semanas), fases 0-N con gates de entrada/salida |
| F6 Riesgos | Anticipar lo que puede salir mal | Matriz P×I, 6 categorías obligatorias, plan de contingencia |

---

## Defaults si falta contexto

Asumir y **declarar en el bloque "Suposiciones"** del entregable:

| Campo faltante | Default asumido |
|----------------|-----------------|
| Presupuesto | Rango derivado de la estimación PERT (no al revés) |
| Timeline | MVP ≤ 12 semanas como objetivo, sin fecha externa impuesta |
| Equipo | 2-3 devs + apoyo de diseño parcial |
| Escala esperada | <10k usuarios el primer año; arquitectura monolito modular |
| Stack | El stack recomendado en `references/templates.md` para el tipo de proyecto |
| Compliance | Solo buenas prácticas estándar (sin GDPR/PCI/HIPAA específicos) |
| Modelo de negocio | No validado → se registra como riesgo de negocio en F6 |

Máximo 1 pregunta si el faltante es bloqueante (ej.: no se puede inferir
ni el tipo de producto).

---

## Ejemplo input → output

**Input:** "Brief: app de reservas para restaurantes, presupuesto desconocido."

**Output:** análisis Nivel 2 — módulos P0 (reservas, admin, notificaciones), stack monolito modular, PERT con rangos, roadmap 3 fases, riesgos top 3 con mitigación, suposiciones explícitas. Gate: grep placeholders vacío.

---

## Validación

| Gate | Acción | Criterio |
|------|--------|----------|
| Placeholders | `grep -nE 'TODO|\[completar\]|\[pendiente\]' <archivo>` | vacío |
| Estimación | tabla PERT | rangos, no puntos únicos |
| MVP | roadmap | ≤12 semanas o justificado |
| Riesgos | matriz F6 | prob × impacto + mitigación |
| Reglas calidad | 7 reglas de este SKILL.md | cumplidas |

---

## Entregable

Esqueleto mínimo del documento de análisis (plantillas completas por sección
en `references/templates.md`):

```markdown
# Análisis — [Proyecto]

## Resumen Ejecutivo
[Problema, solución, usuarios, inversión estimada con rangos, riesgos top 3]

## Suposiciones
[Solo si faltó contexto: cada suposición + riesgo asociado]

## Requerimientos
[Módulos P0-P3 con criterios verificables + no funcionales + integraciones]

## Arquitectura
[Stack justificado + diagrama C4 (Mermaid) + ADRs clave + modelo de datos]

## Estimación (PERT)
| Módulo | O | M | P | PERT | Rango |
|--------|---|---|---|------|-------|

## Roadmap
[Fases con gates de entrada/salida y métricas de éxito]

## Matriz de Riesgos
| Riesgo | Probabilidad | Impacto | Exposición | Mitigación |
|--------|-------------|---------|------------|------------|

## Fuera de Alcance
[Qué NO se hace — igual de importante que lo que sí]
```

### Entregables por audiencia

**Para cliente / stakeholders no técnicos:**
- [ ] Resumen ejecutivo (1 página)
- [ ] Propuesta de valor técnica en lenguaje de negocio
- [ ] Roadmap visual con hitos y fechas
- [ ] Estimación de costo con rangos honestos
- [ ] Riesgos principales en lenguaje de negocio

**Para equipo técnico:**
- [ ] Documento de arquitectura (ADRs incluidos)
- [ ] User stories con criterios de aceptación
- [ ] Diagrama de entidades / modelo de datos
- [ ] Diagramas C4
- [ ] Configuración de infra propuesta
- [ ] Estándares de código y proceso de trabajo

**Para gestión del proyecto:**
- [ ] Roadmap por fases con gates de entrada/salida
- [ ] Estimación PERT por módulo
- [ ] Matriz de riesgos
- [ ] Plan de comunicación con cliente
- [ ] Definition of Done por tipo de tarea

---

## Reglas de Calidad del Análisis

1. **Sin estimaciones puntuales** — siempre rangos con justificación
2. **Sin stack por moda** — cada decisión técnica tiene justificación documentada
3. **Sin MVP inflado** — si supera 12 semanas, reducir scope o justificar explícitamente
4. **Sin riesgos genéricos** — cada riesgo tiene probabilidad, impacto y mitigación concreta
5. **Sin requerimientos ambiguos** — si no tiene criterio de aceptación verificable, no es un requerimiento
6. **Documentar lo que NO se hace** — scope fuera igual de importante que scope dentro
7. **Señalar suposiciones explícitamente** — toda suposición no validada es un riesgo latente

---

## Skills relacionadas

| Skill | Relación |
|-------|----------|
| `propuestas-contratos` | El análisis alimenta la propuesta comercial y el scope |
| `project-pricing` | La estimación PERT es la base del precio |
| `sprint-planning` | El roadmap y las user stories se ejecutan en sprints |
| `web-architecture` | Profundiza las decisiones de arquitectura de F3 |

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
