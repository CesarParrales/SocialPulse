---
name: design-system
description: >
  Creación y mantenimiento de Design Systems: tokens, componentes, documentación,
  versioning y governance. Activar cuando el usuario mencione: design system,
  sistema de diseño, tokens de diseño, componentes reutilizables, biblioteca de
  componentes, Storybook, style guide, variables de diseño, atomic design en código,
  o cuando diga "cómo creo un design system", "cómo documento los componentes",
  "cómo mantengo la consistencia entre diseño y código", "cómo comparto componentes
  entre proyectos", "qué son los design tokens", o cualquier variante.
---

# Design System Skill

Un design system no es una biblioteca de componentes bonitos.
Es la infraestructura compartida que permite que diseño y desarrollo
hablen el mismo idioma y construyan con coherencia y velocidad.

**Tokens de diseño → `references/tokens.md`**
**Componentes — anatomía y estados → `references/components.md`**
**Documentación y Storybook → `references/documentation.md`**
**Governance y versioning → `references/governance.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — tokens/componentes ya adoptados en el repo.
2. Resultados de `ui-audit` o `atomic-design` si project-memory los referencia.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** JSON tokens en repo; owner/governance → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución — DS bootstrap en 3 fases

0. **Memoria** — leer convenciones UI del proyecto; no duplicar tokens existentes.
1. **Evaluar pertinencia**: confirmar que es el momento de construir un DS
   (ver criterios abajo). Si no lo es, recomendarlo y parar.
2. **FASE 1 — Inventario / audit**: si hay producto existente, inventariar
   componentes y valores actuales (idealmente con `ui-audit`). Si NO hay
   producto existente → partir de paleta neutral + los 20 componentes core
   (lista en `references/components.md`).
3. **FASE 2 — Tokens mínimos**: definir foundations (color, tipografía,
   spacing, radius, shadow, motion) como tokens semánticos.
   Leer `references/tokens.md`. Producir el JSON exportable del Entregable.
4. **FASE 3 — Esqueleto de documentación/Storybook**: estructura de docs,
   usage guidelines por componente, setup de Storybook.
   Leer `references/documentation.md`.
5. **Definir governance**: owner, proceso de contribución y deprecación,
   versionado semántico. Leer `references/governance.md`.
6. **Validación y cierre** — ejecutar `## Validación`; checklist 10 ítems; registrar gaps en `LEARNINGS.md`.

---

## Defaults si falta contexto

El agente asume y DECLARA estos supuestos (marcados `[HIPÓTESIS]` o
`[NO VERIFICADO]`) en vez de preguntar (máx 1 pregunta si es bloqueante):

- **Sin producto existente** → paleta neutral (grises + 1 primario azul) +
  20 componentes core, todo marcado `[HIPÓTESIS]` hasta tener brand.
- **Sin brand definido** → tokens semánticos con valores placeholder
  neutrales y nota de que el brand los sobreescribirá.
- **Sin stack definido** → exportar tokens como JSON estándar + variables CSS
  (formato más portable) y declararlo.
- **Sin modo oscuro requerido** → definir solo light, dejando la estructura
  semántica lista para dark.
- **Sin equipo de diseño** → priorizar tokens + componentes en código;
  Figma queda como recomendado, no bloqueante.

---

## Qué es (y Qué No es) un Design System

```
ES un design system:
→ El conjunto de decisiones de diseño codificadas y reutilizables
→ La única fuente de verdad para cómo se ve y se comporta el producto
→ Un producto vivo con versiones, owners y proceso de contribución
→ La capa que conecta Figma con el código

NO es un design system:
→ Una colección de screenshots bonitos
→ Una guía de estilo estática en PDF
→ Una biblioteca de componentes sin tokens ni reglas
→ Un proyecto de "algún día" que se termina "cuando haya tiempo"
→ Solo responsabilidad del equipo de diseño (necesita diseño + desarrollo)
```

---

## Cuándo Construir un Design System

```
Síntomas de su ausencia:
→ El mismo componente implementado de 5 formas distintas
→ Cada nueva pantalla empieza desde cero
→ Diseño y código nunca coinciden exactamente
→ Onboarding de nuevos devs/diseñadores tarda semanas
→ Un cambio de brand requiere tocar 200 archivos

Condiciones para construirlo:
→ El producto tiene más de 3-5 pantallas en producción
→ Hay más de 2 personas trabajando en diseño o desarrollo
→ El producto va a escalar (más features, más plataformas)
→ Hay auditoría previa que documenta inconsistencias existentes

Cuándo NO es el momento:
→ MVP con 2 pantallas → overkill
→ El equipo tiene 1 designer y 1 developer → coordinar directamente
→ El producto va a cambiar radicalmente en 3 meses → esperar
```

---

## Los 3 Niveles de un Design System

```
Nivel 1 — FOUNDATIONS (base de todo)
  Design Tokens: colores, tipografía, espaciado, bordes, sombras, motion
  → Implementados como variables CSS, tokens JSON, Figma variables

Nivel 2 — COMPONENTS (bloques constructivos)
  Primitivos: Button, Input, Select, Checkbox, Badge...
  Compuestos: Card, Modal, Form, Table, Navigation...
  → Construidos sobre los tokens, con estados documentados y accesibles

Nivel 3 — PATTERNS (soluciones de UX recurrentes)
  Patterns: formularios de creación, flujos de confirmación, empty states...
  Templates: layouts de página, estructuras de dashboard...
  → Construidos sobre los componentes, con guías de cuándo y cómo usar
```

---

## Ejemplo input → output

**Input:** "Bootstrap DS para SaaS React sin brand definido."

**Output:** tokens JSON semánticos (color/spacing/type); inventario 20 componentes core; esqueleto Storybook; owner propuesto; v0.1.0 semver. Gate: checklist 10 ítems bloqueantes.

---

## Validación

| Gate | Acción | Criterio |
|------|--------|----------|
| Tokens JSON | entregable | exportable, no solo prose |
| Semánticos | color/spacing | roles, no solo raw |
| Componentes | inventario | ≥20 core documentados |
| WCAG | componentes core | AA |
| Checklist | 10 ítems bloqueantes | todos ✓ |

---

## Entregables visuales (portables)

- Diagramas, sitemaps y flujos → bloques Mermaid (flowchart/graph)
- Auditorías y comparativas → tablas markdown
- Wireframes y layouts → bloques ASCII
- Jerarquías de componentes → árbol con indentación o Mermaid
No depender de herramientas de visualización externas al editor.

---

## Entregable

**Obligatorio**: JSON de design tokens exportable (no solo descripción),
con esta estructura mínima (detalle y variantes en `references/tokens.md`):

```json
{
  "color": {
    "primary": { "value": "#2563EB", "type": "color" },
    "primary-hover": { "value": "#1D4ED8", "type": "color" },
    "text-primary": { "value": "#111827", "type": "color" },
    "text-secondary": { "value": "#4B5563", "type": "color" },
    "success": { "value": "#16A34A", "type": "color" },
    "error": { "value": "#DC2626", "type": "color" },
    "warning": { "value": "#CA8A04", "type": "color" }
  },
  "spacing": {
    "1": { "value": "4px" }, "2": { "value": "8px" },
    "4": { "value": "16px" }, "8": { "value": "32px" }
  },
  "radius": {
    "sm": { "value": "4px" }, "md": { "value": "8px" }, "lg": { "value": "16px" }
  },
  "typography": {
    "font-family-base": { "value": "Inter, sans-serif" },
    "text-sm": { "value": "14px" }, "text-base": { "value": "16px" },
    "text-lg": { "value": "18px" }
  },
  "shadow": { "sm": { "value": "0 1px 2px rgb(0 0 0 / 0.05)" } },
  "motion": {
    "duration-fast": { "value": "150ms" },
    "easing-standard": { "value": "cubic-bezier(0.2, 0, 0, 1)" }
  }
}
```

Acompañado de: inventario de componentes (tabla), esqueleto de documentación
y reglas de governance.

---

## Checklist de cierre — 10 ítems bloqueantes

```
✓/✗ 1.  Tokens definidos para color, tipografía, spacing, radius y shadow
✓/✗ 2.  Tokens exportados como JSON (entregable obligatorio) y variables CSS
✓/✗ 3.  Tokens semánticos (no solo paleta raw)
✓/✗ 4.  Inventario de componentes core definido (≥20)
✓/✗ 5.  Cada componente core tiene sus estados documentados
✓/✗ 6.  Cada componente core cumple WCAG AA
✓/✗ 7.  Cada componente tiene usage guidelines (cuándo usar / cuándo no)
✓/✗ 8.  Esqueleto de documentación/Storybook creado y accesible al equipo
✓/✗ 9.  Hay un owner del design system (persona o equipo)
✓/✗ 10. El sistema tiene versionado semántico definido
```

### Recomendados (no bloqueantes)

```
□ Modo light y dark definidos
□ Tokens sincronizados con Figma variables
□ Cada componente tiene tests en Storybook
□ Cada componente tiene ejemplos de código
□ El changelog está actualizado
□ Proceso de contribución documentado
□ Proceso de deprecación documentado
```

---

## Skills relacionadas

- `ui-audit` — la auditoría revela qué tokens y componentes necesita el sistema
- `atomic-design` — atomic design es la filosofía; el design system es la implementación
- `ui-web-modern`, `ui-admin-dashboard`, `ui-mobile-native` — contextos a los que sirve el sistema
- `react-patterns`, `nextjs-fullstack`, `mobile-flutter` — implementación técnica del sistema

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
