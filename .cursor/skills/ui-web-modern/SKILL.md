---
name: ui-web-modern
description: >
  Diseño moderno para web/SaaS/landing: tendencias actuales, tipografía, color,
  spacing, grids, micro-interacciones, referencias y fuentes de inspiración.
  Activar cuando el usuario necesite criterios de diseño visual moderno para
  interfaces web, cuando pregunte por referencias de diseño, tendencias de UI,
  tipografía para web, paletas de color modernas, sistemas de grid, animaciones
  sutiles o micro-interacciones. También cuando diga "quiero que se vea moderno",
  "cómo diseño una landing", "qué tipografías uso", "referencia de diseño SaaS",
  o cualquier variante sobre la calidad visual de interfaces web.
---

# UI Web Modern Skill

El diseño web moderno no es una tendencia — es un conjunto de principios
que hacen interfaces más claras, más rápidas de procesar y más agradables de usar.

Esta skill no reemplaza a un diseñador senior.
La complementa con criterios verificables y referencias reales.

Este SKILL.md y las references contienen solo principios atemporales
(jerarquía, contraste, ritmo, spacing). Todo lo fechado (tendencias, modas,
productos de referencia) vive en `references/trends-watch.md`.

**Tipografía para web → `references/typography.md`**
**Color y paletas modernas → `references/color.md`**
**Spacing, grid y layout → `references/layout.md`**
**Micro-interacciones y animación → `references/motion.md`**
**Referencias e inspiración → `references/inspiration.md`**
**Recursos gratuitos (literal + inspiración) → `references/learning-sources.md`**
**Tendencias y modas (con caducidad) → `references/trends-watch.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — brand/dirección visual si está documentada.
2. Design brief previo en `docs/` si existe.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** Design Brief en `docs/`; tokens visuales acordados → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory; verificar fecha de `references/trends-watch.md`.
1. **Definir contexto**: tipo de producto (SaaS/landing/dashboard), audiencia
   y sensación buscada. Si falta, aplicar Defaults.
2. **Investigar referentes**: flujo de `references/inspiration.md`; recursos
   gratuitos (principios, 21st.dev, hubs ES) en `references/learning-sources.md`;
   tendencias y Tier 1 en `references/trends-watch.md` (verificar fecha).
3. **Decidir fundaciones visuales**: tipografía (`references/typography.md`),
   paleta (`references/color.md`), spacing/grid/layout (`references/layout.md`).
4. **Definir motion**: micro-interacciones y tokens de animación
   (`references/motion.md`).
5. **Producir el Design Brief de 1 página** (plantilla en Entregable) con
   valores concretos, no adjetivos.
6. **Validación y cierre** — ejecutar `## Validación`; registrar gaps en `LEARNINGS.md`.

Criterios de cierre:
```
✓ Design Brief completo con valores concretos (hex, px, ms — no "moderno y limpio")
✓ Todos los criterios medibles de la tabla verificados ✓/✗
✓ Las decisiones de tendencia citan trends-watch.md y su fecha
```

---

## Defaults si falta contexto

El agente asume y DECLARA estos supuestos en vez de preguntar
(máx 1 pregunta si es bloqueante):

- **Sin tipo de producto definido** → asumir SaaS B2B y declararlo `[HIPÓTESIS]`.
- **Sin brand/paleta** → neutrales slate + 1 primario, marcado `[HIPÓTESIS]`
  hasta tener brand.
- **Sin tipografía definida** → Inter Variable (UI) como base única; segunda
  familia solo si el brief lo justifica.
- **Sin requisito de dark mode** → diseñar light con tokens semánticos listos
  para dark.
- **Sin referencia visual del cliente** → proponer 2 direcciones desde
  `references/inspiration.md` y pedir confirmación solo si el resultado es
  bloqueante; si no, avanzar con la más conservadora declarándolo.

---

## Los Principios del Diseño Web Moderno (atemporales)

```
1. CLARIDAD sobre decoración
   El diseño sirve al contenido. Cada elemento tiene un propósito.
   Si se puede quitar sin perder información → quitar.

2. ESPACIO como elemento de diseño
   El whitespace no es espacio vacío — es lo que hace que el contenido respire.
   Las interfaces modernas son generosas con el espacio.

3. TIPOGRAFÍA como jerarquía visual principal
   Los contrastes tipográficos (tamaño, peso, color) crean jerarquía.
   El color es secundario a la tipografía para crear estructura.

4. VELOCIDAD percibida
   Loading states, skeleton screens, optimistic UI.
   El usuario siente que la app es rápida aunque no lo sea.

5. FEEDBACK inmediato
   Cada acción tiene respuesta visual inmediata.
   Hover states, focus states, loading states.

6. CONSISTENCIA sistémica
   Todo se ve como parte del mismo sistema.
   Los componentes comparten el mismo lenguaje visual.

REGLA DE ORO (filtro anti-moda):
¿Esto mejora la claridad o solo se ve "diferente"?
Si es solo diferente sin mejorar claridad → es moda, no diseño.
(Catálogo de tendencias y modas vigentes: references/trends-watch.md)
```

---

## Criterios medibles de "moderno bien hecho"

```
✓/✗ Contraste WCAG AA verificado (texto normal ≥ 4.5:1, grande ≥ 3:1)
✓/✗ Máximo 2 familias tipográficas
✓/✗ Escala tipográfica definida y consistente (sin tamaños ad hoc)
✓/✗ Line-height 1.5-1.7 en body; medida de línea ≤ 75 caracteres
✓/✗ Sistema de espaciado en grid de 4 u 8px, sin valores sueltos
✓/✗ Paleta con roles semánticos; primario con 5-9 tonos; máx 1-2 acentos
✓/✗ Jerarquía visual clara: 1 elemento dominante por sección
✓/✗ Todos los interactivos con hover y focus visible
✓/✗ Loading states y empty states definidos (no solo "No hay datos")
✓/✗ Animaciones ≤ 300ms en UI y con prefers-reduced-motion respetado
✓/✗ Responsive sin broken states (verificado en 390px y 1440px)
```

---

## Ejemplo input → output

**Input:** "Design brief para landing SaaS analytics."

**Output:** tipografía Inter + escala; paleta slate+blue con hex; spacing 8px grid; motion tokens ≤300ms; tabla criterios medibles ✓/✗. Gate: sin adjetivos vacíos ("moderno") — solo valores concretos.

---

## Validación

| Gate | Acción | Criterio |
|------|--------|----------|
| Design Brief | plantilla Entregable | hex, px, ms concretos |
| Criterios medibles | tabla abajo | todos evaluados ✓/✗ |
| Tendencias | `trends-watch.md` | fecha citada si aplica |
| WCAG | contraste | AA en texto normal |

---

## Entregables visuales (portables)

- Diagramas, sitemaps y flujos → bloques Mermaid (flowchart/graph)
- Auditorías y comparativas → tablas markdown
- Wireframes y layouts → bloques ASCII
- Jerarquías de componentes → árbol con indentación o Mermaid
No depender de herramientas de visualización externas al editor.

---

## Entregable — Design Brief de 1 página

```markdown
# Design Brief — [Producto] · [fecha]

## Dirección
Sensación buscada: [3 adjetivos] · Referentes: [2-3, con fecha de trends-watch]

## Tipografía
| Rol | Familia | Pesos | Tamaños |
|---|---|---|---|
| Headings | [nombre] | [600/700] | [48/36/24px] |
| Body | [nombre] | [400/500] | [16/14px] |

## Paleta (hex completa)
| Rol | Light | Dark (si aplica) |
|---|---|---|
| Primario | #... | #... |
| Primario hover | #... | #... |
| Fondo / superficie | #... / #... | #... / #... |
| Texto primario / secundario | #... / #... | #... / #... |
| Success / Error / Warning | #... / #... / #... | ... |

## Escala de spacing
4 / 8 / 12 / 16 / 24 / 32 / 48 / 64 px (grid base: [4|8]px)

## Radios
sm: [4]px · md: [8]px · lg: [16]px

## Motion tokens
| Token | Duración | Easing |
|---|---|---|
| fast (hover, toggles) | 150ms | ease-out |
| base (transiciones) | 250ms | cubic-bezier(0.2, 0, 0, 1) |
| slow (modales, páginas) | 300ms | ease-in-out |

## Verificación
[Tabla de criterios medibles ✓/✗]
```

---

## Skills relacionadas

- `design-system` — implementa las decisiones de esta skill como tokens y componentes
- `atomic-design` — los átomos y moléculas tienen la estética definida aquí
- `ui-audit` — esta skill define qué es "buen diseño" para la auditoría
- `nextjs-fullstack`, `react-patterns` — implementación técnica de lo que define esta skill

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
