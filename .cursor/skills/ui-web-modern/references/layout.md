# Spacing, Grid y Layout

## El Sistema de Espaciado

```
El espaciado no es decorativo — es comunicación.

Cómo el espaciado comunica:
→ Más espacio entre elementos = menos relación
→ Menos espacio entre elementos = más relación (Gestalt: proximidad)
→ Espacio consistente = sistema coherente

GRID DE 8px (el más común en web moderna):
  Todos los espacios son múltiplos de 8:
  4px   (0.5 unidad) — solo para ajustes muy finos
  8px   (1 unidad)   — espacio mínimo entre elementos relacionados
  16px  (2 unidades) — espacio estándar entre elementos del mismo grupo
  24px  (3 unidades) — espacio entre grupos o componentes diferentes
  32px  (4 unidades) — separación de secciones menores
  48px  (6 unidades) — separación de secciones principales
  64px  (8 unidades) — separación entre bloques de contenido
  96px  (12 unidades)— máximo espacio, entre secciones muy diferentes
  128px (16 unidades)— solo en landing pages de alto impacto

Por qué 8px:
→ Los monitores tienen 8px como mínima unidad visual en muchos DPIs
→ La mayoría de iconos son múltiplos de 8 (16/24/32px)
→ Los breakpoints de pantalla son múltiplos de 8
→ Math simple → menos decisiones ad hoc
```

---

## Densidad de Información — Elegir la Correcta

```
ALTA DENSIDAD (admin, dashboards, herramientas internas):
  → Padding: 8-16px en componentes
  → Gap entre elementos: 4-8px
  → Sin whitespace "decorativo"
  → Objetivo: maximizar info visible en pantalla
  → Ejemplo: tablas de datos, editores, tableros de control

MEDIA DENSIDAD (SaaS típico, apps de producto):
  → Padding: 16-24px en componentes
  → Gap entre elementos: 8-16px
  → Whitespace funcional (respira pero no exagerado)
  → Objetivo: balance entre info y usabilidad
  → Ejemplo: Linear, Notion, GitHub

BAJA DENSIDAD (landing pages, marketing, portfolios):
  → Padding: 24-48px en componentes
  → Gap entre secciones: 80-160px
  → Whitespace abundante
  → Objetivo: impacto visual, atención en el mensaje
  → Ejemplo: Apple.com, Stripe, Linear.app landing

La densidad debe ser consistente dentro del mismo contexto.
Un dashboard no puede tener secciones de alta densidad mezcladas
con secciones de landing page — crea disonancia visual.
```

---

## Sistema de Grid para Web

```
GRID DE 12 COLUMNAS (el estándar):
  → Compatible con layouts de 2, 3, 4, 6 columnas
  → Breakpoints estándar:
    xs:  < 640px   → 1 columna (full width)
    sm:  640px+    → 2 columnas (640px)
    md:  768px+    → opcional intermedio (768px)
    lg:  1024px+   → 3-4 columnas (1024px)
    xl:  1280px+   → 4-6 columnas (1280px)
    2xl: 1536px+   → max-width contenedor (1536px)

MAX-WIDTH DE CONTENEDOR:
  Texto puro (blog/artículos):     max-width: 680-720px
  UI mixta (dashboard interior):   max-width: 1200-1280px
  Landing con mucho visual:        max-width: 1400-1440px
  Full-width:                      100%

GUTTER (espacio entre columnas):
  Mobile:  16px
  Tablet:  24px
  Desktop: 32px

PADDINGS HORIZONTALES DE PÁGINA:
  Mobile:  16px o 20px (no menos — evita que el contenido toque los bordes)
  Tablet:  32px o 40px
  Desktop: 48-80px (o 0 si el max-width ya centra)
```

---

## Patrones de Layout Modernos

### Hero Section

```
FULL-SCREEN HERO (landing de alto impacto):
┌─────────────────────────────────────────────┐
│  [Navbar]                                   │
│                                             │
│         Headline principal                  │
│         (grande, alto impacto)             │
│                                             │
│         Subheadline descriptivo             │
│         (cuerpo, 1-2 líneas)               │
│                                             │
│    [CTA Primary]   [CTA Secondary]         │
│                                             │
│    [Social proof: logos o testimonial]      │
│                                             │
│    [Hero image / Product screenshot]        │
│                                             │
└─────────────────────────────────────────────┘

Variantes:
→ Text-only hero (minimalista, editorial)
→ Split hero (texto izquierda, imagen derecha — 50/50)
→ Centered hero con imagen detrás (overlay oscuro o claro)
→ Video background hero (para productos con movimiento)
→ Abstract/3D illustration hero (para tech products)

Tipografía del hero:
→ Headline: 48-72px bold (desktop), 32-48px (mobile)
→ Subheadline: 18-20px regular, max 2-3 líneas
→ No más de 8-10 palabras en el headline principal
```

### Feature Grid

```
FEATURE GRID (beneficios del producto):
Opciones de layout:

3 columnas (el más común):
┌──────────┬──────────┬──────────┐
│ [Icon]   │ [Icon]   │ [Icon]   │
│ Title    │ Title    │ Title    │
│ Desc     │ Desc     │ Desc     │
└──────────┴──────────┴──────────┘

Bento Grid (vigencia como tendencia → trends-watch.md):
┌──────────────┬──────┬──────┐
│              │      │      │
│  Feature A   │ Feat │ Feat │
│  (grande)    │  B   │  C   │
│              │      │      │
├──────┬───────┴──────┴──────┤
│ Feat │                     │
│  D   │  Feature E (ancha)  │
└──────┴─────────────────────┘
→ Tamaños distintos comunican jerarquía
→ Las features más importantes ocupan más espacio
```

### Dashboard Layout

```
DASHBOARD LAYOUT (el más común en SaaS):

Patrón 1 — Sidebar + Content (más común):
┌────────┬────────────────────────────────┐
│        │  [Topbar: search + user]       │
│        ├────────────────────────────────┤
│Sidebar │  [Breadcrumb / Page Title]     │
│  Nav   │                                │
│        │  [KPI Cards row]               │
│        │                                │
│        │  [Chart largo]  [Tabla]       │
│        │                                │
│        │  [Lista / tabla principal]     │
└────────┴────────────────────────────────┘

Patrón 2 — Top nav (para apps con pocas secciones):
┌────────────────────────────────────────┐
│  Logo  [Nav items]          [User]     │
├────────────────────────────────────────┤
│  [Page title + actions]               │
├────────────────────────────────────────┤
│  [Content grid]                        │
└────────────────────────────────────────┘

KPI Cards:
→ Siempre en fila de 3-4 tarjetas
→ Número grande, label pequeño, tendencia (flecha + %)
→ Máximo 1 acción por card (ver detalle)
```

---

## Responsive Design Patterns

```
MOBILE-FIRST APPROACH:
  Diseñar primero para mobile → escalar hacia desktop
  No: diseñar desktop → comprimir para mobile (pierde calidad)

BREAKPOINTS PRÁCTICOS:
  @media (max-width: 639px)   { /* mobile */ }
  @media (min-width: 640px)   { /* tablet portrait */ }
  @media (min-width: 1024px)  { /* tablet landscape / desktop */ }
  @media (min-width: 1280px)  { /* desktop wide */ }

PATRONES DE ADAPTACIÓN POR COMPONENTE:

Navbar:
  Mobile:  hamburger menu, logo centered
  Desktop: horizontal nav, logo left, actions right

Grid de features:
  Mobile:  1 columna, stacked
  Tablet:  2 columnas
  Desktop: 3 columnas o bento

Tabla de datos:
  Mobile:  cards en lugar de tabla, o scroll horizontal
  Desktop: tabla completa con todas las columnas

Sidebar navigation:
  Mobile:  drawer (panel lateral que aparece sobre el contenido)
  Tablet:  sidebar colapsable (icons only)
  Desktop: sidebar expandida con labels
```

---

## Wireframes portables — layouts sin imagen

```
Sin imagen, generar wireframes ASCII en markdown
(no depender de herramientas de visualización externas al editor):

Hero section wireframe con variantes:
  → Full-screen / Split / Centered
  → Con elementos de diseño modernos (espacio, tipografía, jerarquía)

Feature grid con variantes:
  → 3 columnas / Bento / List con imagen
  → Mostrando el patrón de spacing y jerarquía

Dashboard layout:
  → Sidebar + content / Top nav
  → KPI cards + chart + tabla

Responsive comparison:
  → Mobile vs Desktop del mismo layout
  → Mostrando cómo se adaptan los componentes

Uso:
"genera el layout de hero section moderno para [tipo de producto]"
"genera un bento grid de features para [producto]"
"genera el layout del dashboard de [SaaS/analytics/e-commerce]"
"muéstrame cómo se adapta [componente] de desktop a mobile"
```
