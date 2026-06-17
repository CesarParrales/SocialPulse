# Layout y Navegación de Admin

## El Layout Estándar de Admin

```
┌─────────────────────────────────────────────────────────────┐
│  [Logo]     [Search]            [Notifications] [User]      │ ← Topbar
├──────────┬──────────────────────────────────────────────────┤
│          │  [Breadcrumb]         [Page Title]   [Actions]  │
│ Sidebar  ├──────────────────────────────────────────────────┤
│  Nav     │                                                   │
│          │                                                   │
│ [Item]   │              Content Area                        │
│ [Item ▼] │                                                   │
│  [Sub]   │                                                   │
│  [Sub]   │                                                   │
│ [Item]   │                                                   │
│          │                                                   │
│ ─────    │                                                   │
│ [User]   │                                                   │
│ [Help]   │                                                   │
└──────────┴──────────────────────────────────────────────────┘

Dimensiones estándar:
  Sidebar:      240-280px (expandida) / 64-72px (colapsada a icons)
  Topbar:       56-64px de altura
  Content:      flex-1 con padding 24-32px
  Max-width:    1440-1600px (para pantallas muy anchas)
```

---

## Sidebar — El Corazón del Admin

```
ESTRUCTURA DE LA SIDEBAR:

Header:
  → Logo de la empresa o nombre del sistema
  → Toggle para colapsar/expandir (en desktop)

Navigation principal:
  → Máximo 7-9 items de primer nivel
  → Íconos SIEMPRE (incluso cuando hay labels — permite colapsar)
  → Separadores para agrupar secciones relacionadas
  → Subnav como acordeón (no flyout — más predecible)

Footer de sidebar:
  → Avatar + nombre del usuario logueado
  → Link a perfil/configuración
  → Botón de logout

ESTADOS DE LOS NAV ITEMS:
  Default:  fondo transparente, texto/icon en color secundario
  Hover:    fondo sutil (bg-muted), text en color primario
  Active:   fondo con color primary (saturación baja) o filled
            text en color primary o en contraste con el fondo
  Disabled: opacity 0.5, cursor not-allowed

CUÁNDO USAR SUBNAV vs MÓDULOS SEPARADOS:
  Subnav (acordeón dentro del mismo sidebar):
  → Categorías de la misma entidad (Órdenes > Pendientes, Completadas, Canceladas)
  → Configuración por sección

  Módulos separados (item propio en sidebar):
  → Entidades distintas del sistema (Usuarios, Productos, Órdenes)
  → Secciones con acciones muy diferentes entre sí
```

---

## Topbar — Utilidad Sobre Decoración

```
EL TOPBAR TIENE TRES ZONAS:

Zona izquierda:
  → Botón de hamburger/toggle del sidebar (en mobile)
  → Breadcrumb de la sección actual (opcional, pero útil)

Zona central:
  → Búsqueda global (el elemento más importante del topbar)
  → Siempre accesible con Cmd+K o Ctrl+K como atajo

Zona derecha:
  → Notificaciones (icono con badge de count)
  → Selector de organización/workspace (si hay multi-tenant)
  → Avatar del usuario (dropdown con perfil, settings, logout)

BÚSQUEDA GLOBAL EN ADMIN:
  → El admin maneja decenas de entidades y miles de registros
  → La búsqueda global es la "navegación experta" del admin
  → Busca en múltiples entidades simultáneamente
  → Muestra resultados agrupados por entidad
  → Soporta atajos (Cmd+K = abrir, Esc = cerrar, ↑↓ = navegar)

  Resultado de búsqueda bien diseñado:
  ┌─────────────────────────────────┐
  │ 🔍 Buscar...              ⌘K  │
  ├─────────────────────────────────┤
  │ USUARIOS                        │
  │ 👤 Juan Pérez    juan@...       │
  │ 👤 María López   maria@...      │
  ├─────────────────────────────────┤
  │ ÓRDENES                         │
  │ 📦 #ORD-1234    $150.00        │
  │ 📦 #ORD-1235    $89.50         │
  └─────────────────────────────────┘
```

---

## Page Header — La Zona de Control

```
Cada pantalla del admin tiene un page header estándar:

┌─────────────────────────────────────────────────────────────┐
│ ← (breadcrumb)  Pedidos / Pendientes                        │
│                                                             │
│ Pedidos Pendientes                    [Filtros] [+ Crear]  │
│ 248 pedidos                                                 │
└─────────────────────────────────────────────────────────────┘

Componentes del page header:
  Breadcrumb:     navegación jerárquica (Home > Módulo > Sub)
  Título:         nombre de la entidad/sección en plural
  Contador:       total de registros visible (actualizado en tiempo real)
  Acciones:       botones de las acciones principales de la página
                  Orden: destructivo más alejado, CTA principal más a la derecha

BOTONES EN PAGE HEADER:
  [Exportar]  [Importar]  [Filtros]  [+ Crear nuevo]
  ← menos frecuente          más frecuente →

  Primary action:    el botón "+" o "Crear nuevo"
  Secondary actions: exportar, importar, bulk actions
  Tertiary:         configuración avanzada, generalmente en un menú (...)
```

---

## Responsive en Admin

```
Admin es DESKTOP FIRST — pero necesita funcionar en tablet y móvil.

DESKTOP (1024px+):
  → Sidebar expandida con labels
  → Layout de 2+ columnas para formularios
  → Tablas completas con todas las columnas
  → Acciones inline en tablas

TABLET (768px-1023px):
  → Sidebar colapsada a solo íconos
  → Toggle para mostrar/esconder sidebar
  → Formularios en 1 columna o columnas más estrechas
  → Tablas con scroll horizontal para columnas no esenciales

MOBILE (<768px):
  → Sidebar como drawer (oculta, toggle con hamburger)
  → Tablas como cards (1 card por registro)
  → Formularios en 1 columna
  → Page header simplificado (solo título + acción principal)
  → Las acciones secundarias en un menú (...)

Tablas en mobile:
  En lugar de mostrar una tabla con scroll horizontal difícil de usar:
  
  Antes (tabla):                Después (card):
  ┌──┬──────┬──┬──┐             ┌─────────────────────┐
  │  │Juan  │..│..│             │ Juan Pérez           │
  └──┴──────┴──┴──┘             │ juan@example.com     │
  ← scroll horizontal →         │ 15 ene 2024  Activo  │
                                 │ [Ver] [Editar]       │
                                 └─────────────────────┘
```

---

## Wireframes portables — layouts de admin (ASCII)

```
Generar wireframes ASCII directamente en markdown
(no depender de herramientas de visualización externas al editor):

Layout completo con proporciones anotadas:
→ Sidebar (240px) + topbar (60px) + content área
→ Anchos/altos indicados como anotaciones de texto

Sidebar en dos estados:
→ Expandida (con labels) vs colapsada (solo icons)
→ Navigation items con sus estados active/hover/default

Page header estándar:
→ Breadcrumb + título + contador + botones de acción
→ Con un ejemplo de página real (Usuarios, Pedidos, Productos)

Responsive comparison:
→ El mismo admin en desktop, tablet, mobile
→ Cómo cambia el sidebar y la tabla en cada breakpoint

Uso:
"genera el layout de admin para [tipo de sistema: e-commerce/CRM/operaciones]"
"genera la sidebar de navegación con los módulos de [descripción del sistema]"
"genera el responsive del admin de [sistema] en desktop, tablet y mobile"
```
