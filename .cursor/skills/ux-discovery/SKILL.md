---
name: ux-discovery
description: >
  Research de usuarios, arquetipos, mapas de empatía, user journeys y definición
  del problema real antes de diseñar. Activar cuando el usuario mencione: research
  de UX, entrevistas de usuario, arquetipos, personas, user journey, mapa de empatía,
  pain points, jobs to be done, definición del problema, discovery, validación con
  usuarios, o cuando diga "qué necesita el usuario", "por dónde empezamos el diseño",
  "cómo entendemos al usuario", "necesito definir el problema", "qué investigo antes
  de diseñar", o cualquier variante. También activar cuando se detecte que el equipo
  va a diseñar sin haber validado suposiciones sobre el usuario.
---

# UX Discovery Skill

La fase de discovery es la que más dinero ahorra en un proyecto.
Diseñar sin entender al usuario = construir la solución equivocada perfectamente.

**Esta skill complementa `software-project-analysis`:**
- `software-project-analysis` → qué construir técnicamente, riesgos, arquitectura
- `ux-discovery` → para quién, por qué, qué problema real resuelve

**Research y entrevistas → `references/research.md`**
**Arquetipos y personas → `references/personas.md`**
**User Journey y mapas de empatía → `references/journey-empathy.md`**
**Definición del problema → `references/problem-definition.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — PRD, usuarios objetivo, métricas de producto.
2. Discovery previo en `docs/` si project-memory lo referencia.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** artefactos en `docs/ux/`; hipótesis críticas → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory/PRD; no repetir research ya documentado.
1. **Clasificar la rama de trabajo** según el acceso disponible:
   - **COMPLETO** — hay acceso a usuarios reales (entrevistas posibles)
   - **EXPRESS** — solo hay acceso a stakeholders internos
   - **MÍNIMO** — solo existe la descripción del producto
2. **Preparación**: definir hipótesis de usuario/problema y knowledge gaps.
   Leer `references/research.md` para diseñar el protocolo (rama COMPLETO/EXPRESS).
3. **Recolección de evidencia**:
   - COMPLETO → entrevistas (5-8 usuarios) + analytics + datos de soporte
   - EXPRESS → entrevistas a stakeholders + analytics disponibles
   - MÍNIMO → analytics disponibles + suposiciones documentadas (ver Defaults)
4. **Síntesis**: construir arquetipos (`references/personas.md`), mapa de empatía
   y journey (`references/journey-empathy.md`). En ramas EXPRESS/MÍNIMO, marcar
   cada afirmación no observada como `[HIPÓTESIS]`.
5. **Definición**: Problem Statement + How Might We + criterios de éxito medibles
   (`references/problem-definition.md`).
6. **Entregar** los artefactos con la plantilla de la sección Entregable, y plan
   de validación si hubo hipótesis.
7. **Validación y cierre** — ejecutar `## Validación`; checklist de la rama; registrar gaps en `LEARNINGS.md`.

### Criterios de cierre por rama

```
COMPLETO:
✓ Mínimo 5 usuarios representativos entrevistados
✓ Arquetipos validados con citas textuales del research
✓ Problema definido con datos, no con opiniones
✓ Journey con emociones basadas en evidencia
✓ Criterios de éxito del usuario definidos y aprobados

EXPRESS:
✓ Stakeholders clave entrevistados (mínimo 2 roles distintos)
✓ Analytics/datos de soporte revisados
✓ Arquetipos construidos y marcados [HIPÓTESIS] donde no hay evidencia
✓ Problem Statement aprobado por stakeholders
✓ Plan de validación con usuarios reales agendado

MÍNIMO:
✓ Máximo 3 suposiciones críticas documentadas, todas marcadas [HIPÓTESIS]
✓ Arquetipo provisional con JTBD inferido del producto
✓ Problem Statement provisional
✓ Plan de validación post-lanzamiento escrito (qué métrica confirma/refuta cada hipótesis)
✗ NO presentar nada de esta rama como conocimiento validado
```

---

## Defaults si falta contexto

El agente asume y DECLARA estos supuestos en vez de preguntar
(máximo 1 pregunta solo si es bloqueante):

- **Sin entrevistas posibles** → usar analytics disponibles + máximo 3 suposiciones
  documentadas marcadas `[HIPÓTESIS]` + plan de validación post-lanzamiento.
- **Sin analytics** → derivar comportamiento del tipo de producto y mercado,
  marcando todo como `[NO VERIFICADO]`.
- **Sin segmento definido** → asumir el usuario más frecuente del caso de uso
  principal y declararlo: "[HIPÓTESIS] Arquetipo primario asumido: ...".
- **Sin métricas de éxito** → proponer 2-3 métricas estándar del dominio
  (activación, retención de tarea, tiempo a primera tarea completada) como punto
  de partida `[HIPÓTESIS]`.
- **Sin tiempo declarado** → asumir rama EXPRESS y declararlo.

---

## Protocolo de Discovery (preguntas núcleo)

```
Antes de diseñar cualquier pantalla, responder:

1. ¿Quién es el usuario real? (no "todos")
2. ¿Qué trabajo intenta hacer? (Jobs to Be Done)
3. ¿Cuál es su mayor frustración actual?
4. ¿Qué contexto rodea su uso del producto?
5. ¿Cómo mide el éxito?

Sin estas respuestas → el diseño es una apuesta.
Con estas respuestas → el diseño tiene dirección.
```

---

## Fases de Discovery

```
FASE 1 — Preparación (antes de hablar con usuarios)
  - Definir hipótesis de usuario y problema
  - Identificar qué NO sabemos (knowledge gaps)
  - Diseñar protocolo de investigación
  - Reclutar participantes representativos

FASE 2 — Research (hablar con usuarios reales)
  - Entrevistas en profundidad (5-8 usuarios)
  - Observación contextual si es posible
  - Encuestas para validar hallazgos cualitativos
  - Análisis de datos existentes (analytics, soporte)

FASE 3 — Síntesis (dar forma a los hallazgos)
  - Arquetipos / Personas
  - Mapas de empatía
  - User Journey Maps
  - Insights accionables

FASE 4 — Definición (problema + oportunidad)
  - Problem Statement
  - How Might We questions
  - Criterios de éxito medibles
  - Alcance del diseño
```

---

## Cuándo Usar Cada Rama (profundidad y tiempo)

```
COMPLETO (4-6 semanas):
  ✓ Producto nuevo sin usuarios previos
  ✓ Rediseño mayor de flujo crítico
  ✓ Nuevo mercado o segmento de usuario
  ✓ Problema de negocio grave sin causa clara

EXPRESS (1-2 semanas):
  ✓ Feature nueva en producto existente
  ✓ Mejora de flujo conocido
  ✓ Hay datos y stakeholders disponibles rápidamente

MÍNIMO (2-3 días):
  ✓ Mejora puntual con hipótesis clara
  ✓ Validar una sola asunción crítica
  ✓ Ya existe research previo relevante
  ✓ Solo se cuenta con la descripción del producto

Sin Discovery (con riesgo documentado):
  Solo cuando hay presión extrema de tiempo
  → Documentar las suposiciones que se hacen
  → Planificar validación post-lanzamiento
```

---

## Ejemplo input → output

**Input:** "Discovery EXPRESS para dashboard analytics B2B."

**Output:** 2 arquetipos con `[HIPÓTESIS]` donde falte evidencia; journey del flujo principal; Problem Statement + 3 métricas de éxito; plan de validación post-launch. Gate: checklist EXPRESS cumplido.

---

## Validación

| Gate | Acción | Criterio |
|------|--------|----------|
| Rama | documento | COMPLETO / EXPRESS / MÍNIMO declarada |
| Hipótesis | artefactos | marcadas `[HIPÓTESIS]` si aplica |
| Problem Statement | entregable | medible + fuera de scope |
| Checklist rama | criterios de cierre arriba | todos los ✓ aplicables |
| Entregable | plantilla abajo | completa |

---

## Entregables visuales (portables)

- Diagramas, sitemaps y flujos → bloques Mermaid (flowchart/graph)
- Auditorías y comparativas → tablas markdown
- Wireframes y layouts → bloques ASCII
- Jerarquías de componentes → árbol con indentación o Mermaid
No depender de herramientas de visualización externas al editor.

Plantillas ASCII copiables para los artefactos de esta skill:
- Mapa de Empatía y Journey Map → `references/journey-empathy.md`
- Persona / arquetipo → `references/personas.md`
- Problem Statement Canvas → `references/problem-definition.md`
- Síntesis de research → `references/research.md`

---

## Entregable

Plantilla mínima del output estándar (rellenar y entregar en markdown):

```markdown
# Discovery — [Producto] · Rama: [COMPLETO|EXPRESS|MÍNIMO] · [fecha]

## Persona(s) (máx 2-3)
- Nombre del arquetipo: ...
- Contexto (cuándo/dónde/con qué): ...
- JTBD: Cuando [situación], quiero [motivación], para [resultado]
- Frustraciones (con cita o [HIPÓTESIS]): ...

## Empathy Map
| Piensa y siente | Ve | Escucha | Dice y hace |
|---|---|---|---|
| ... | ... | ... | ... |
Dolores: ... · Ganancias: ...

## Journey (flujo principal)
Etapas → Acciones → Touchpoints → Emociones → Oportunidades
(usar plantilla ASCII de references/journey-empathy.md)

## Problem Statement
"¿Cómo podríamos [verbo] para [usuario] de modo que [resultado de valor]?"
- Criterios de éxito: ...
- Fuera de scope: ...

## Hipótesis y plan de validación (si aplica)
| # | Hipótesis | Cómo se valida | Métrica |
|---|---|---|---|
```

Entregables complementarios por audiencia:

```
Para diseño:    arquetipos (2-3 máx), journey del flujo principal,
                mapa de empatía por arquetipo, top 10 insights priorizados
Para producto:  problem statement, oportunidades, criterios de éxito,
                riesgos de adopción
Para técnico:   flujos críticos, casos edge del research, restricciones de
                contexto (móvil, conectividad, accesibilidad), integraciones
                esperadas (conecta con software-project-analysis)
```

---

## Skills relacionadas

- `software-project-analysis` — qué construir técnicamente; esta skill define para quién y por qué
- `ux-architecture` — recibe el problema definido y los arquetipos para estructurar pantallas y flujos
- `ui-audit` — la auditoría es research del producto existente; alimenta el discovery de rediseños

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
