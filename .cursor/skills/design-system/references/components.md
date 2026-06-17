# Componentes — Anatomía, Variantes y Estados

## El Inventario Mínimo de Componentes

```
Un design system de producción necesita como mínimo:

PRIMITIVOS (construidos directamente sobre tokens):
  □ Button              (primary, secondary, ghost, danger + sizes)
  □ Input               (text, password, search, number)
  □ Textarea
  □ Select / Dropdown
  □ Checkbox
  □ Radio Button
  □ Toggle / Switch
  □ Badge / Tag
  □ Avatar
  □ Icon (wrapper del icono)
  □ Spinner / Loading
  □ Tooltip
  □ Divider

COMPUESTOS (construidos sobre primitivos):
  □ Card
  □ Modal / Dialog
  □ Alert / Banner
  □ Toast / Notification
  □ Popover
  □ Tabs
  □ Accordion
  □ Table
  □ Pagination
  □ Breadcrumb
  □ Progress Bar
  □ Form (con Label, Helper, Error)
  □ Empty State
  □ Skeleton

NAVEGACIÓN:
  □ Navbar / Header
  □ Sidebar
  □ Bottom Navigation (mobile)
  □ Menu / Dropdown Menu
```

---

## Anatomía de un Componente

```
Cada componente tiene:

1. PARTES (sub-elementos con nombre)
   Un Button puede tener: icon-left, label, icon-right, spinner
   Nombrar las partes permite comunicar cambios precisamente

2. VARIANTES (diferentes versiones del mismo componente)
   Variant: primary | secondary | ghost | danger | link
   Size:    sm | md | lg
   → Combinaciones: ButtonPrimary-MD, ButtonGhost-SM, etc.

3. ESTADOS (cambios según la interacción)
   Default:   el estado normal
   Hover:     el cursor está encima
   Focus:     seleccionado por teclado
   Active:    siendo presionado
   Disabled:  no interactivo
   Loading:   esperando respuesta
   Error:     validación fallida (inputs)
   Selected:  ítem seleccionado en listados

4. PROPS (en código)
   Los parámetros que controlan variantes y estados

5. COMPORTAMIENTO
   Qué pasa cuando se hace click, cuando recibe foco, al escribir, etc.
```

---

## Button — El Componente Más Auditado

```
Anatomía del Button:

┌─────────────────────────────────┐
│  [icon-left]  [label]  [icon-right] │
└─────────────────────────────────┘

Parts:
  icon-left:  ícono opcional antes del label
  label:      texto del botón
  icon-right: ícono opcional después del label
  spinner:    reemplaza icon-left durante loading

Variantes:
  primary:    acción principal de la pantalla (CTA)
  secondary:  acciones secundarias (1-2 por pantalla)
  ghost:      acciones terciarias, sin fondo
  danger:     acciones destructivas (eliminar, cancelar)
  link:       parece un enlace, actúa como botón

Sizes:
  sm:  padding: 8px 12px / font-size: 14px / height: 32px
  md:  padding: 10px 16px / font-size: 14px / height: 40px (default)
  lg:  padding: 12px 20px / font-size: 16px / height: 48px

Estados:
  default:   background: var(--color-primary)
  hover:     background: var(--color-primary-hover) / cursor: pointer
  focus:     outline: 2px solid var(--color-border-focus) / outline-offset: 2px
  active:    background: var(--color-primary-active) / transform: scale(0.98)
  disabled:  opacity: 0.5 / cursor: not-allowed / pointer-events: none
  loading:   spinner visible / label hidden o con texto "Cargando..."

Reglas de uso:
→ Un solo botón primary por pantalla o sección principal
→ El botón primary refleja la acción más importante
→ No usar danger para acciones no destructivas (pierden impacto)
→ Siempre con aria-label si el label no describe la acción
→ type="submit" en formularios, type="button" para lo demás
```

---

## Input — El Componente de Formulario Base

```
Anatomía del Input:

Label
┌─────────────────────────────────┐
│  [icon-left]  Placeholder  [icon-right] │
└─────────────────────────────────┘
Helper text / Error message

Parts:
  label:       texto descriptivo encima del campo
  container:   el borde y fondo del campo
  icon-left:   ícono decorativo o funcional (buscar, usuario, etc.)
  input:       el elemento <input> real
  icon-right:  acción o estado (limpiar, mostrar contraseña, etc.)
  helper:      texto de ayuda bajo el campo (color: text-secondary)
  error:       mensaje de error (color: error, ícono de advertencia)

Estados:
  default:  border: var(--color-border-default)
  hover:    border: var(--color-border-strong)
  focus:    border: var(--color-border-focus) / ring de foco visible
  error:    border: var(--color-border-error) / error message visible
  disabled: opacity: 0.5 / cursor: not-allowed / no editable
  readonly: similar a disabled pero permite selección de texto
  filled:   (cuando tiene contenido) igual que default pero con contenido

Variantes:
  default:  borde sutil, fondo blanco
  filled:   fondo gris sutil, sin borde
  unstyled: sin fondo ni borde (para uso custom)

Tipos:
  text, email, password, number, tel, search, url, date, time

Accesibilidad obligatoria:
  → <label for="id"> asociado al input
  → aria-describedby apuntando al mensaje de error cuando está presente
  → aria-invalid="true" cuando hay error
  → aria-required="true" cuando es obligatorio
```

---

## Card — Composición de Contenido

```
Anatomía de Card:

┌─────────────────────────────────┐
│  [Header: título + acciones]    │
│  ─────────────────────────────  │
│  [Media: imagen / video]        │
│  [Body: contenido principal]    │
│  ─────────────────────────────  │
│  [Footer: acciones / metadata]  │
└─────────────────────────────────┘

Todos los slots son opcionales.
Una Card puede tener solo Body.

Variantes:
  default:    white bg, border, shadow-sm
  raised:     white bg, no border, shadow-md
  flat:       white bg, border, no shadow
  ghost:      transparent bg, no border, no shadow
  interactive: cursor pointer, hover state, focus ring

Comportamiento interactivo:
→ Si toda la card es clickeable → role="article" o role="button"
→ Si tiene acciones dentro → no hacer la card entera clickeable
→ Hover state: shadow más grande o slight scale (1.02)
→ Focus: ring visible para navegación por teclado
```

---

## Modal / Dialog

```
Anatomía:

┌─────────────────────────────────────┐
│  [Título]                    [✕]   │
│  ─────────────────────────────────  │
│                                     │
│  [Contenido del modal]              │
│                                     │
│  ─────────────────────────────────  │
│  [Botón cancelar]  [Botón primario] │
└─────────────────────────────────────┘
  [Overlay oscuro detrás]

Comportamiento obligatorio:
→ Focus trap: Tab no puede salir del modal
→ Primer elemento focuseable recibe foco al abrir
→ Esc cierra el modal
→ Click en overlay cierra el modal (si no es destructivo)
→ Al cerrar, el foco regresa al trigger que lo abrió
→ role="dialog" / aria-modal="true" / aria-labelledby al título

Tamaños:
  sm:  max-width: 400px  → confirmaciones simples
  md:  max-width: 560px  → formularios básicos (default)
  lg:  max-width: 720px  → formularios complejos
  xl:  max-width: 960px  → contenido enriquecido
  full: 100% viewport    → mobile-first flows

Animación:
  Enter: opacity 0→1 + scale 0.95→1 en 200ms ease-out
  Exit:  opacity 1→0 + scale 1→0.95 en 150ms ease-in
```

---

## Tabla de Estados Requeridos por Componente

```
Componente      | Default | Hover | Focus | Active | Disabled | Error | Loading | Selected
─────────────────────────────────────────────────────────────────────────────────────────
Button          |   ✓    |   ✓   |   ✓   |   ✓    |    ✓     |   -   |    ✓    |    -
Input           |   ✓    |   ✓   |   ✓   |   -    |    ✓     |   ✓   |    -    |    -
Select          |   ✓    |   ✓   |   ✓   |   -    |    ✓     |   ✓   |    -    |    ✓
Checkbox        |   ✓    |   ✓   |   ✓   |   -    |    ✓     |   ✓   |    -    |    ✓
Radio           |   ✓    |   ✓   |   ✓   |   -    |    ✓     |   ✓   |    -    |    ✓
Toggle          |   ✓    |   ✓   |   ✓   |   -    |    ✓     |   -   |    ✓    |    ✓
Card            |   ✓    |  opt  |  opt  |   -    |    -     |   -   |   opt   |   opt
Tab item        |   ✓    |   ✓   |   ✓   |   -    |    ✓     |   -   |    -    |    ✓
Menu item       |   ✓    |   ✓   |   ✓   |   -    |    ✓     |   -   |    -    |   opt
─────────────────────────────────────────────────────────────────────────────────────────
✓ = requerido  opt = opcional según uso  - = no aplica
```

---

## Entregables portables — anatomía de componentes

```
Sin imagen, generar en markdown (tablas, ASCII o Mermaid;
no depender de herramientas de visualización externas al editor):

Grid de variantes de un componente (tabla variant × size × state):
  Mostrando todas las combinaciones
  Útil para revisar completitud antes de entregar al equipo

Anatomy diagram (ASCII con partes etiquetadas):
  El componente con sus partes nombradas
  Nombres de parts + los tokens que aplican a cada parte

Tabla de estados (markdown):
  Filas: componentes / Columnas: estados
  Marcas: ✓ implementado / ○ pendiente / - no aplica

Uso:
"genera el grid de variantes del componente Button"
"genera el anatomy diagram del componente Input"
"genera la tabla de estados para el inventario del design system"
```
