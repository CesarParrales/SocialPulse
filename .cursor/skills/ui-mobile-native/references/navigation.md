# Navegación Mobile — Patrones y Decisiones

## Los 4 Patrones de Navegación Mobile

```
1. STACK NAVIGATION (jerarquía):
   A → B → C → D
   El usuario profundiza en contenido y regresa con back

2. TAB NAVIGATION (secciones paralelas):
   [Tab A] [Tab B] [Tab C]
   El usuario salta entre secciones sin perder el estado de cada una

3. DRAWER NAVIGATION (menú lateral):
   Hamburger menu → panel lateral con secciones
   Para apps con muchas secciones que no caben en tabs

4. MODAL NAVIGATION (temporales):
   Pantallas que flotan sobre el contenido principal
   Para flujos temporales (crear, editar, detalles)
```

---

## Stack Navigation — La Jerarquía

```
El Stack es la navegación más fundamental en mobile.
Cada pantalla que el usuario navega se apila sobre la anterior.

COMPORTAMIENTO:
  Push:   nueva pantalla entra desde la derecha (iOS) o desde abajo (Android)
  Pop:    volver a la pantalla anterior (back gesture o botón)
  Replace: reemplazar la pantalla actual sin agregar al stack
  Reset:  limpiar todo el stack y volver a la raíz

ANIMACIONES POR PLATAFORMA:
  iOS:    pantalla nueva desliza desde la derecha / volver desliza a la derecha
          La pantalla anterior se mueve ligeramente hacia la izquierda (profundidad)
  Android M3: shared element transitions / fade through / container transform

CUÁNDO USAR STACK:
  ✅ Lista → Detalle (el patrón más común)
  ✅ Formularios de múltiples pasos
  ✅ Flujos de onboarding
  ✅ Configuración con subsecciones

CUÁNDO NO USAR STACK:
  ❌ Para secciones principales de la app (usar tabs)
  ❌ Para contenido relacionado del mismo nivel (usar tabs dentro de stack)
```

---

## Tab Navigation — Las Secciones Paralelas

```
Los tabs son el patrón de navegación principal para la mayoría de apps.

CUÁNDO USAR TABS:
  ✅ La app tiene 2-5 secciones principales distintas
  ✅ El usuario necesita cambiar rápidamente entre secciones
  ✅ Cada sección tiene su propia historia de navegación
  ✅ El estado de cada sección debe preservarse al cambiar de tab

CUÁNDO NO USAR TABS:
  ❌ Solo hay 1 sección (no hay navegación)
  ❌ Las secciones tienen relación jerárquica (usar Stack)
  ❌ Más de 5 secciones (usar Drawer + tabs para las principales)

ESTADO DE LOS TABS:
  → El stack de cada tab se preserva al cambiar de tab
  → Tap en el tab actual → volver al root del stack de ese tab
  → Tap en el tab actual (ya en root) → scroll to top del contenido

PERSISTENCIA ENTRE SESIONES:
  → El tab activo puede o no persistir entre sesiones (decisión de producto)
  → iOS Mail, Twitter: recuerdan el último tab
  → Otras apps: siempre empiezan en el tab principal

TABS ANIDADOS (tabs dentro de tabs):
  → Aceptable en algunos casos (top tabs dentro de una sección)
  → Ej: [Inbox] con sub-tabs [All, Unread, Starred]
  → NO usar bottom navigation anidada — confunde la jerarquía
```

---

## Modal Navigation — Lo Temporal

```
Los modales son pantallas que "flotan" sobre el contenido.
Se usan para flujos temporales que no pertenecen al stack principal.

TIPOS DE PRESENTACIÓN EN IOS:
  Sheet (page sheet):
    → Pantalla que cubre parcialmente el contenido (iOS 13+)
    → El contenido de abajo sigue visible ligeramente
    → Se puede cerrar con swipe hacia abajo
    → Ideal para: crear, editar, filtros, detalles adicionales

  Full Screen Cover:
    → Cubre completamente la pantalla
    → No puede cerrarse con swipe (el usuario debe usar el botón dismiss)
    → Ideal para: flujos de onboarding, pantallas de pago, cámara

  Popover (iPad):
    → Cuadro flotante anclado a un elemento
    → Solo en iPad (en iPhone se convierte en sheet automáticamente)

TIPOS DE PRESENTACIÓN EN ANDROID:
  Bottom Sheet Modal:
    → El equivalente de la sheet de iOS
    → Aparece desde abajo, cierra con swipe down
    → Puede expandirse a pantalla completa

  Dialog:
    → Modal centrado con overlay oscuro
    → Para confirmaciones, alerts, inputs simples
    → Cierra con botones o tapping fuera

REGLAS DE MODALES:
  → Los modales siempre tienen un botón de dismiss explícito (X o Cancelar)
  → Los flujos de > 3 pasos dentro de un modal → considerar pantalla completa
  → El modal no debería contener navegación hacia otros modales (modal hell)
  → Al cerrar un modal, el foco regresa al elemento que lo abrió
```

---

## Navegación por Contexto — Cuándo Usar Qué

```
ESCENARIO: App de mensajería (como WhatsApp/Telegram)
  Bottom tabs:  [Chats] [Calls] [Status] [Settings]
  Stack en Chats: Lista de chats → Conversación → Perfil de contacto
  Modal: Nuevo mensaje / Adjuntar archivo / Cámara

ESCENARIO: E-commerce
  Bottom tabs:  [Home] [Explore] [Cart] [Profile]
  Stack en Home: Home → Categoría → Lista → Producto → Checkout
  Modal: Filtros / Variantes del producto / Confirmación de compra

ESCENARIO: App de noticias/contenido
  Bottom tabs:  [Feed] [Explore] [Saved] [Profile]
  Stack en Feed: Feed → Artículo → Comentarios
  Modal: Share / Report / Filter

ESCENARIO: App de salud/fitness
  Bottom tabs:  [Dashboard] [Workout] [Log] [Profile]
  Stack en Workout: Lista de workouts → Detalle → En progreso
  Modal: Quick log / Settings del workout

ESCENARIO: Dashboard/herramienta B2B
  Considera usar Drawer si hay muchas secciones:
  Hamburger → Drawer con navegación completa
  O Tab Bar con máx 5 secciones principales + overflow en "More"
```

---

## Deep Links — El Contexto Correcto al Reabrir

```
Deep linking permite que una URL abra directamente la pantalla correcta.

POR QUÉ IMPORTA EN EL DISEÑO:
  → El usuario recibe una notificación → tap → debe ir directamente a la pantalla relevante
  → El usuario comparte un link → el destinatario ve exactamente el mismo contenido
  → El sistema operativo puede reabrir la app en el estado correcto

TIPOS DE DEEP LINKS:
  App URL schemes: myapp://orders/123 (funciona solo si la app está instalada)
  Universal Links (iOS) / App Links (Android): https://myapp.com/orders/123
  → Si la app está instalada: abre la app en la pantalla correcta
  → Si no: abre el browser en la URL

IMPLICACIONES PARA LA NAVEGACIÓN:
  → Cada pantalla importante debe tener una URL/ruta única
  → El stack se construye desde la ruta (no solo se abre la pantalla final)
  → Ejemplo: si el link va a un pedido → el stack debe ser: Home → Mis pedidos → Pedido #123
    (para que el back button funcione correctamente)

ESTADOS A CONSIDERAR CON DEEP LINKS:
  → El usuario no está autenticado → redirigir a login → después del login → pantalla correcta
  → El contenido fue eliminado → mostrar 404/empty state apropiado
  → El usuario no tiene permiso → mostrar error apropiado, no crash
```

---

## Entregables portables — patrones de navegación

```
Generar en markdown (Mermaid flowchart, ASCII y tablas; no depender
de herramientas de visualización externas al editor):

Stack navigation diagram (Mermaid o ASCII):
→ Las pantallas como cajas apiladas
→ Push/pop animations indicados
→ El breadcrumb implícito del stack

Tab navigation con estado:
→ Los 4-5 tabs con sus stacks independientes
→ Estado preservado al cambiar de tab

Modal flow:
→ El sheet de iOS vs el bottom sheet de Android
→ Los estados: collapsed, mid, expanded, full screen

Comparativa iOS vs Android navigation:
→ Las mismas 4 pantallas en ambas plataformas
→ Las diferencias de navegación anotadas

Uso:
"genera el diagrama de navegación para [tipo de app]"
"genera la comparativa de navegación iOS vs Android para [app]"
"genera el deep link flow para [app con notificaciones]"
```
