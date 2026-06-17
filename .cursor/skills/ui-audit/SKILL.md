---
name: ui-audit
description: >
  Auditoría visual de productos existentes: inconsistencias, accesibilidad WCAG,
  heurísticas de Nielsen, reporte con prioridades de mejora. Activar cuando el
  usuario mencione: auditoría de UI, auditoría de UX, revisar el diseño existente,
  inconsistencias visuales, problemas de accesibilidad, heurísticas de usabilidad,
  evaluar una interfaz, mejorar una interfaz existente, o cuando diga "qué está
  mal en mi diseño", "cómo mejoro la UI", "hay problemas de accesibilidad",
  "el diseño no es consistente", "necesito evaluar la experiencia", o cualquier
  variante donde el punto de partida es un producto o diseño existente.
---

# UI Audit Skill

Una auditoría de UI no es una opinión sobre el diseño.
Es una evaluación sistemática contra criterios establecidos y verificables.

Sin criterios → "no me gusta el azul" (inútil)
Con criterios → "el contraste de texto sobre fondo es 2.1:1, WCAG requiere 4.5:1" (accionable)

**Heurísticas de Nielsen → `references/nielsen-heuristics.md`**
**Principios UX gratuitos → `references/ux-principles-free.md`**
**Accesibilidad WCAG → `references/accessibility.md`**
**Consistencia visual → `references/visual-consistency.md`**
**Reporte de auditoría → `references/audit-report.md`**
**Catálogo recursos free → `../ui-web-modern/references/learning-sources.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — URL staging/prod, design system acordado.
2. Auditorías previas en `docs/` si existen.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** reporte en `docs/`; hallazgos sistémicos → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory; reutilizar inventario previo si existe.
1. **Pre-flight** (responder antes de auditar; si falta algo, aplicar Defaults):
   - ¿Hay URL accesible? ✓/✗
   - ¿Hay screenshots? ✓/✗
   - ¿Alcance definido (todo el producto o flujo específico)? ✓/✗
   - ¿Dispositivo/navegador objetivo definido? ✓/✗
2. **Inventario de pantallas** en scope: estado normal + estados edge
   (vacío, error, cargando).
3. **Evaluación sistemática por los 3 ejes**: usabilidad
   (`references/nielsen-heuristics.md` + tabla de scoring abajo), accesibilidad
   (`references/accessibility.md`), consistencia visual
   (`references/visual-consistency.md`). Opcional y recomendado: ≥3 principios
   del checklist gratuito en `references/ux-principles-free.md`. Documentar
   hallazgo, severidad y pantalla. NO proponer soluciones en este paso.
4. **Priorización**: clasificar por severidad y frecuencia; agrupar sistémicos
   vs puntuales; estimar impacto.
5. **Reporte**: producir el entregable ÚNICO obligatorio con el formato de
   `references/audit-report.md` (IDs, templates, resumen ejecutivo, roadmap).
6. **Validación y cierre** — ejecutar `## Validación`; registrar gaps en `LEARNINGS.md`.

Criterios de cierre:
```
✓ Todas las pantallas del alcance evaluadas en los 3 ejes
✓ Cada hallazgo tiene ID único (USA-/ACC-/VIS-/SIS-), severidad y criterio violado
✓ Tabla de scoring Nielsen completada (1-5 por heurística)
✓ Hallazgos sistémicos agrupados y diferenciados de los puntuales
✓ Reporte sigue la estructura de references/audit-report.md
✓ Si se auditó sin URL/capturas → toda severidad marcada [NO VERIFICADO]
```

---

## Defaults si falta contexto

El agente asume y DECLARA estos supuestos en vez de preguntar
(máx 1 pregunta si es bloqueante):

- **Sin URL ni capturas** → auditar por descripción, con toda severidad marcada
  `[NO VERIFICADO]` y nota de que requiere confirmación visual.
- **Sin alcance definido** → asumir el flujo principal del producto y declararlo
  `[HIPÓTESIS]`.
- **Sin dispositivo definido** → asumir desktop 1440px + mobile 390px.
- **Sin nivel WCAG definido** → evaluar contra WCAG 2.1 AA.
- **Sin estado de auth definido** → auditar la vista de usuario autenticado si
  el producto lo requiere, y declararlo.

---

## Cuándo Hacer una Auditoría de UI

```
Reactivas (algo ya está mal):
→ Los usuarios se quejan de que es confuso
→ Las métricas de conversión son bajas sin causa técnica clara
→ El equipo de soporte recibe las mismas preguntas repetidamente
→ El onboarding tiene alta tasa de abandono
→ Hay quejas de accesibilidad o problemas legales

Proactivas (antes de que algo falle):
→ Antes de un rediseño mayor (saber qué conservar y qué cambiar)
→ Antes de escalar el producto a nuevos mercados
→ Al incorporar un design system (inventariar lo existente)
→ Periódicamente como mantenimiento de calidad (anualmente)
→ Antes de auditorías de accesibilidad legales o certificaciones
```

---

## Los Tres Ejes de Evaluación

```
1. USABILIDAD (Heurísticas de Nielsen)
   ¿El sistema es fácil de usar y entender?
   Evalúa: navegación, feedback, errores, consistencia de interacción

2. ACCESIBILIDAD (WCAG 2.1 / 2.2)
   ¿El sistema puede ser usado por personas con discapacidades?
   Evalúa: contraste, tamaño de texto, navegación por teclado, lectores de pantalla

3. CONSISTENCIA VISUAL
   ¿El sistema se ve y se comporta de forma coherente?
   Evalúa: colores, tipografía, espaciado, componentes, patrones de interacción
```

---

## Tabla de scoring Nielsen (copiable)

Puntuar 1-5 por heurística (1 = falla grave y frecuente · 3 = cumple con
fricciones · 5 = cumple de forma ejemplar). Detalle de cada heurística en
`references/nielsen-heuristics.md`.

```markdown
| # | Heurística | Score (1-5) | Hallazgos (IDs) |
|---|---|---|---|
| H1 | Visibilidad del estado del sistema | | |
| H2 | Relación sistema-mundo real | | |
| H3 | Control y libertad del usuario | | |
| H4 | Consistencia y estándares | | |
| H5 | Prevención de errores | | |
| H6 | Reconocer mejor que recordar | | |
| H7 | Flexibilidad y eficiencia de uso | | |
| H8 | Diseño estético y minimalista | | |
| H9 | Recuperación de errores | | |
| H10 | Ayuda y documentación | | |
| | **Promedio** | | |
```

---

## Escala de Severidad

```
🔴 CRÍTICO — bloquea el uso o viola ley/norma
  Ejemplos: formulario que no se puede enviar, texto ilegible, falla WCAG AA
  Acción: resolver antes del próximo release

🟠 SERIO — causa confusión significativa o excluye usuarios
  Ejemplos: navegación inconsistente, error sin mensaje claro
  Acción: resolver en el próximo sprint o iteración

🟡 MODERADO — fricciones que impactan la experiencia
  Ejemplos: label ambiguo, jerarquía visual débil
  Acción: resolver en backlog próximo

🟢 MENOR — preferencia o mejora incremental
  Ejemplos: espaciado inconsistente, color no del todo correcto
  Acción: acumular y resolver en sesión de polish

⚪ COSMÉTICO — no impacta la usabilidad
  Ejemplos: pixel desalineado, sombra ligeramente diferente
  Acción: solo si hay tiempo disponible
```

---

## Ejemplo input → output

**Input:** "Auditar dashboard Inertia en staging mobile."

**Output:** reporte Nielsen scoring + hallazgos ACC-/VIS- con severidad; top 3 sistémicos; roadmap corto/medio. Gate: cada hallazgo con ID y criterio violado.

---

## Validación

| Gate | Acción | Criterio |
|------|--------|----------|
| Alcance | pre-flight | pantallas + estados edge |
| 3 ejes | reporte | usabilidad + a11y + consistencia |
| IDs | hallazgos | USA-/ACC-/VIS-/SIS- únicos |
| Nielsen | tabla 1-5 | completa |
| Formato | `references/audit-report.md` | estructura mínima cumplida |

---

## Entregables visuales (portables)

- Diagramas, sitemaps y flujos → bloques Mermaid (flowchart/graph)
- Auditorías y comparativas → tablas markdown
- Wireframes y layouts → bloques ASCII
- Jerarquías de componentes → árbol con indentación o Mermaid
No depender de herramientas de visualización externas al editor.

---

## Entregable

**Único entregable obligatorio**: el reporte con el formato de
`references/audit-report.md`. Estructura mínima:

```markdown
# Auditoría de UI — [Producto] · [fecha]
Alcance: ... · Dispositivos: ... · Criterios: Nielsen + WCAG 2.1 AA + consistencia

## Resumen ejecutivo
🔴 X · 🟠 X · 🟡 X · 🟢 X — Top 3 problemas / Top 3 fortalezas / esfuerzo estimado

## Scoring Nielsen (tabla 1-5 por heurística)

## Hallazgos por eje (template de references/audit-report.md)
ID (USA-/ACC-/VIS-) | problema | criterio violado | pantallas | severidad |
evidencia | recomendación | esfuerzo

## Hallazgos sistémicos (IDs SIS-)

## Roadmap de mejora (corto / mediano / largo plazo)
```

---

## Skills relacionadas

- `ux-discovery` — la auditoría es research del producto existente
- `design-system` — los hallazgos de auditoría alimentan el design system
- `ui-web-modern`, `ui-admin-dashboard`, `ui-mobile-native` — definen qué debería ser

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
