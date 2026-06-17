---
name: ui-admin-dashboard
description: >
  Diseño específico para paneles administrativos y backoffice: tablas de datos,
  formularios complejos, dashboards con métricas, navegación en sistemas internos,
  y patrones de Filament/shadcn/CoreUI. Activar cuando el usuario necesite diseñar
  o construir un panel de administración, backoffice, sistema interno, dashboard de
  analytics, o herramienta para operadores. También cuando mencione: admin panel,
  CMS, CRUD interface, data table, dashboard KPIs, filtros avanzados, bulk actions,
  o cuando el producto sea claramente de uso interno por operadores o administradores.
---

# UI Admin Dashboard Skill

El diseño para admin/backoffice es radicalmente diferente al diseño para usuarios finales.

Usuarios finales: novatos, exploración, contexto emocional
Operadores de admin: expertos, eficiencia, contexto funcional

El admin panel perfecto es invisible — el operador no "ve" el diseño,
simplemente completa sus tareas en el menor tiempo posible.

**Layout y navegación de admin → `references/admin-layout.md`**
**Data tables y listados → `references/data-tables.md`**
**Formularios complejos → `references/complex-forms.md`**
**KPIs, métricas y charts → `references/metrics-charts.md`**
**Frameworks: Filament, shadcn, CoreUI → `references/frameworks.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — stack (Laravel/Filament, React/shadcn), entidades del dominio.
2. PRD o módulos en `context.md` si project-memory apunta allí.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** specs en `docs/admin/`; framework acordado → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer stack y entidades documentadas; derivar CRUD solo si falta, marcado `[HIPÓTESIS]`.
1. **Obtener el input mínimo**: lista de entidades + acciones CRUD por entidad
   + roles. Si falta → derivarla de la descripción del producto y marcarla
   `[HIPÓTESIS]` (no bloquear).
2. **Definir layout y navegación**: sidebar/topbar, agrupación de módulos
   (máx 7-9 items). Leer `references/admin-layout.md`.
3. **Especificar tablas**: por cada entidad principal, completar la plantilla
   "Data Table Spec" del Entregable. Leer `references/data-tables.md`.
4. **Especificar formularios**: creación/edición de cada entidad, agrupación
   por secciones y validación inline. Leer `references/complex-forms.md`.
5. **Dashboard de métricas** (si aplica): KPIs, charts y su jerarquía.
   Leer `references/metrics-charts.md`.
6. **Elegir framework** según el stack (Defaults abajo) con
   `references/frameworks.md`.
7. **Entregar**: sitemap admin (Mermaid) + wireframe ASCII de tabla +
   wireframe ASCII de formulario + Data Table Spec por entidad.
8. **Validación y cierre** — ejecutar `## Validación`; registrar gaps en `LEARNINGS.md`.

Criterios de cierre:
```
✓ Toda entidad tiene: posición en navegación, Data Table Spec y spec de formulario
✓ El input mínimo (entidades/CRUD/roles) está confirmado o marcado [HIPÓTESIS]
✓ Sitemap admin + 2 wireframes ASCII (tabla y formulario) entregados
✓ Framework recomendado con justificación por stack
✓ Checklist de Admin UI (abajo) verificado
```

---

## Defaults si falta contexto

El agente asume y DECLARA estos supuestos en vez de preguntar
(máx 1 pregunta si es bloqueante):

- **Sin lista de entidades/CRUD/roles** → derivarla de la descripción del
  producto y marcarla `[HIPÓTESIS]`.
- **Framework por stack** (default si el usuario no especifica):
  - Laravel → **Filament**
  - React/Next.js → **shadcn/ui + TanStack Table**
  - Vue → **PrimeVue**
  - Otro/desconocido → recomendar según `references/frameworks.md` y declararlo
- **Sin roles definidos** → asumir admin + operador, marcado `[HIPÓTESIS]`.
- **Sin volumen de datos definido** → asumir tablas de 1k-100k filas
  (paginación servidor) `[NO VERIFICADO]`.
- **Sin requisito mobile** → desktop first con responsive básico.

---

## Prioridades del Admin UI (distintas al UI consumer)

```
CONSUMER UI:             ADMIN UI:
────────────────────────────────────────────────
Atractivo visual         Eficiencia operacional
Onboarding suave         Curva de aprendizaje aceptable
Densidad baja            Densidad media-alta
Flujos lineales          Acceso rápido a cualquier entidad
Emocional                Funcional
Mobile first             Desktop first (con responsive)
Descubrimiento           Productividad repetible
```

---

## Los 5 Principios del Admin UI

```
1. DENSIDAD INTELIGENTE
   Mostrar más información sin sacrificar la scanabilidad.
   La densidad debe ser elegida, no accidental.
   No es "meter todo" — es "mostrar lo relevante compactamente".

2. ACCIONES EN CONTEXTO
   Las acciones deben estar donde el usuario está mirando.
   No requerir navegar a otra pantalla para una acción frecuente.
   Bulk actions, inline editing, context menus.

3. FILTROS Y BÚSQUEDA POTENTES
   El admin gestiona grandes volúmenes de datos.
   Los filtros son la navegación real del admin.
   Sin buenos filtros, el admin es inutilizable a escala.

4. FEEDBACK INMEDIATO
   El operador ejecuta acciones frecuentemente y necesita saber el resultado.
   Toasts de éxito/error, contadores actualizados en tiempo real.
   Optimistic updates cuando es apropiado.

5. CONSISTENCIA SOBRE CREATIVIDAD
   Cada pantalla del admin debe comportarse predeciblemente.
   El operador no debe aprender nuevos patrones en cada módulo.
   La sorpresa es el enemigo de la productividad.
```

---

## Ejemplo input → output

**Input:** "Admin para entidad Workspace en Laravel Filament."

**Output:** sitemap admin Mermaid; Data Table Spec (columnas, filtros, bulk); form spec por secciones; wireframes ASCII tabla+form. Gate: cada entidad con nav + table + form.

---

## Validación

| Gate | Acción | Criterio |
|------|--------|----------|
| Entidades | input mínimo | confirmado o `[HIPÓTESIS]` |
| Data Table Spec | por entidad | columnas + filtros + acciones |
| Wireframes | ASCII | tabla + formulario |
| Framework | recomendación | alineado al stack |
| Checklist Admin UI | sección abajo | ítems aplicables ✓ |

---

## Entregables visuales (portables)

- Diagramas, sitemaps y flujos → bloques Mermaid (flowchart/graph)
- Auditorías y comparativas → tablas markdown
- Wireframes y layouts → bloques ASCII
- Jerarquías de componentes → árbol con indentación o Mermaid
No depender de herramientas de visualización externas al editor.

---

## Entregable

Output estándar: **sitemap admin (Mermaid) + wireframe ASCII de tabla +
wireframe ASCII de formulario + Data Table Spec por entidad**.

### Plantilla "Data Table Spec" (una por entidad)

```markdown
# Data Table Spec — [Entidad]

| Columna | Tipo | Sortable | Visible default |
|---|---|---|---|
| ID | número | ✓ | ✗ |
| Nombre | texto + link a detalle | ✓ | ✓ |
| Estado | badge | ✓ | ✓ |
| Creado | fecha relativa | ✓ (default desc) | ✓ |

Filtros: [estado (select), rango de fechas, búsqueda por nombre/email]
Orden default: [created_at desc]
Bulk actions: [eliminar (con confirmación), exportar, cambiar estado]
Acciones por fila: [ver, editar, eliminar]
Estado vacío: [mensaje + CTA "Crear primer(a) [entidad]"]
Estado vacío con filtros: [mensaje "Sin resultados para estos filtros" + limpiar filtros]
Paginación: [servidor, 25/50/100 por página]
Permisos: [qué roles ven/editan/eliminan]
```

### Wireframes ASCII mínimos (adaptar)

```
TABLA                                      FORMULARIO
┌─Sidebar─┬─[Búsqueda] [Filtros] [+ Crear]─┐  ┌─[← Volver] Editar [Entidad]──────┐
│ Módulo  │ □ | Nombre ▾ | Estado | Fecha │  │ Sección: Información general      │
│ Módulo* │ □ | Item A   | ●Activo| 2d    │  │  [Nombre*___________] [Email*___] │
│ Módulo  │ □ | Item B   | ○Borr. | 5d    │  │ Sección: Configuración            │
│         │ (n seleccionados) [Bulk ▾]    │  │  [Rol ▾] [☑ Activo]              │
│         │ ← 1 2 3 … →   25/pág ▾        │  │            [Cancelar] [Guardar]   │
└─────────┴───────────────────────────────┘  └───────────────────────────────────┘
```

---

## Checklist de Admin UI Completo

```
Layout y navegación:
✓ La navegación principal tiene máx 7-9 items (más = abrumador)
✓ El módulo activo está claramente destacado
✓ Hay breadcrumb en todas las pantallas internas
✓ Las acciones de página están en la parte superior (no al final)
✓ La búsqueda global está accesible desde cualquier pantalla

Tablas:
✓ Las columnas más importantes están a la izquierda
✓ Las acciones están a la derecha de cada fila
✓ El ordenamiento es visible y funcional
✓ Los filtros aplicados son visibles y removibles
✓ El estado vacío tiene instrucción clara

Formularios:
✓ Los campos están agrupados por sección lógica
✓ Los campos requeridos están marcados consistentemente
✓ La validación es inline (no solo al enviar)
✓ Los campos de búsqueda/autocomplete funcionan con gran volumen

Performance:
✓ Las tablas con > 100 rows usan paginación o virtualización
✓ Los filtros no bloquean la UI mientras cargan
✓ Los bulk actions tienen confirmación para acciones destructivas
```

---

## Skills relacionadas

- `ui-web-modern` — los principios visuales aplican, pero con distintas prioridades
- `design-system` — el admin tiene su propio set de componentes especializados
- `laravel-backend`, `node-backend` — la UI del admin sirve a estas APIs
- `react-patterns`, `nextjs-fullstack` — implementación técnica del admin frontend

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
