# Sitemaps y Flujos de Pantallas

## Sitemap — El Mapa Completo del Sistema

```
El sitemap es el inventario completo de todas las pantallas del sistema
organizado jerárquicamente según la estructura de navegación.

NO es:
→ Un mapa del sitio web para SEO (eso es otro concepto)
→ Un diagrama de flujo (eso es el flow)
→ Un wireframe (eso viene después)

SÍ es:
→ La lista de todas las pantallas que existen
→ Cómo están organizadas jerárquicamente
→ El vocabulario acordado para referirse a cada pantalla
→ La base para el routing de la aplicación
```

---

## Anatomía de un Sitemap

```
Niveles del sitemap:

Nivel 0 — Entry points:
  Landing page, login, onboarding

Nivel 1 — Secciones principales:
  Lo que vive en la navegación principal (navbar, bottom tabs, sidebar)

Nivel 2 — Subsecciones:
  Las pantallas dentro de cada sección principal

Nivel 3 — Pantallas de detalle:
  Item individual, formulario, modal expandido

Nivel 4+ — Rara vez necesario:
  Si hay un nivel 4, probablemente hay un problema de IA

Elementos adicionales en el sitemap:
  [M] → Modal (pantalla que se superpone, no reemplaza)
  [O] → Overlay / Drawer
  [S] → State variant (misma pantalla con estado diferente)
  [E] → External (sale de la aplicación)
  [A] → Auth required (solo accesible logueado)
  [R] → Role-based (solo ciertos usuarios)
```

---

## Sitemap por Tipo de Producto

```
SaaS / Dashboard Web:
  Auth:
    Login
    Register
    Forgot Password
    Reset Password
  App (auth required):
    Dashboard / Home
    [Módulo Principal A]
      Lista de A
      Detalle de A
      Crear/Editar A [M]
    [Módulo Principal B]
      ...
    Configuración
      Perfil
      Cuenta
      Notificaciones
      Seguridad
      [Billing — si aplica]
    Admin [R: admin]
      Gestión de usuarios
      ...

E-commerce:
  Marketing:
    Home
    Categorías
      Subcategorías
        Lista de productos
          Detalle de producto
    Búsqueda
    Ofertas
  Compra:
    Carrito [M o pantalla]
    Checkout
      Datos de envío
      Método de pago
      Confirmación
    Confirmación de pedido
  Cuenta:
    Mis pedidos
      Detalle de pedido
      Tracking
    Mis datos
    Mis direcciones
    Mis favoritos

App Mobile:
  Onboarding (primera vez):
    Splash
    Intro screens (1-3)
    Login / Registro
  App principal (tabs):
    Tab 1: [función principal]
    Tab 2: [función secundaria]
    Tab 3: Perfil / Cuenta
  Flujos modales:
    Crear/editar item [modal]
    Notificaciones [overlay]
    Configuración [push screen]
```

---

## Screen Flows — Diagrama de Flujo entre Pantallas

```
El flow mapea el MOVIMIENTO del usuario entre pantallas.
El sitemap es estático. El flow es dinámico.

Elementos de un flow diagram:

Rectángulo:           Pantalla
Rectángulo redondeado: Modal / Overlay
Rombo:                Decisión (if/else del usuario)
Círculo lleno:        Entry point (inicio del flujo)
Círculo con borde:    End point (fin del flujo)
Flechas:              Dirección de navegación
Label en flecha:      Acción que dispara la transición

Tipos de transición a documentar:
→ [tap/click]       acción primaria del usuario
→ [submit]          envío de formulario
→ [swipe]           gesto en mobile
→ [timer]           transición automática
→ [success]         resultado exitoso
→ [error]           resultado con error
→ [back]            botón de volver
→ [cancel]          cancelar la acción
```

---

## Flujos que Siempre Hay que Mapear

```
1. HAPPY PATH — el flujo ideal sin fricción
   El camino que toma el usuario cuando todo funciona
   Como punto de partida, siempre el primero

2. AUTH FLOW — registro y login completo
   Nuevo usuario → registro → verificación → onboarding → app
   Usuario existente → login → app (con y sin sesión guardada)
   Olvidé contraseña → email → reset → nueva contraseña → login → app
   Cierre de sesión → ¿dónde va el usuario?

3. ERROR PATHS — qué pasa cuando algo falla
   Form con errores de validación → feedback → corrección → reintento
   Error de red → feedback → retry → éxito o mensaje de soporte
   Sin permisos → mensaje explicativo → redirigir a dónde?
   Item no encontrado (404) → mensaje + acción alternativa

4. EMPTY STATES — primera vez que se usa una sección
   Lista vacía (sin datos aún) → qué ve el usuario → cómo crea su primer item
   Sin resultados de búsqueda → qué ve → qué puede hacer

5. ONBOARDING — primera vez en el sistema
   Cómo llega el usuario al estado "útil" lo más rápido posible
   Qué hay que configurar para que la app funcione
   Qué se puede posponer para después

6. EDGE CASES CRÍTICOS
   Session expirada a mitad de un flujo → dónde va el usuario al re-loguearse?
   Conexión perdida a mitad de un proceso importante
   Usuario intenta hacer una acción sin permisos suficientes
   Item eliminado mientras otro usuario lo está editando
```

---

## Notación de Flujos en Texto (cuando no hay herramienta)

```
Formato de texto para documentar flujos rápidamente:

FLUJO: Crear nueva orden
Entry point: Dashboard > botón "Nueva Orden"

[Lista de productos]
  ↓ usuario selecciona producto
  → toca "Agregar al carrito"
[Carrito actualizado] (indicador visual)
  ↓ usuario toca "Ver carrito"
[Carrito]
  ├── SI carrito vacío
  │     → mostrar "Tu carrito está vacío" + CTA "Explorar productos"
  │     → vuelve a [Lista de productos]
  └── SI carrito con items
        ↓ usuario toca "Proceder al pago"
[Checkout - Datos de envío]
  ↓ usuario completa y toca "Continuar"
  ├── SI validación falla
  │     → mostrar errores inline en el formulario
  │     → usuario corrige y reintenta
  └── SI validación ok
        ↓
[Checkout - Método de pago]
  ↓ usuario selecciona y toca "Confirmar pedido"
  ├── SI pago rechazado
  │     → "Pago no procesado" + opciones: reintentar, cambiar método
  └── SI pago exitoso
        ↓
[Confirmación de pedido]
  → email de confirmación (background)
  ↓ usuario toca "Ver mis pedidos"
[Mis pedidos - nuevo pedido visible]
Exit point: ✓
```

---

## Entregables portables — flujos y sitemaps sin imagen externa

```
Cuando no hay referencia, generar en markdown con Mermaid
(no depender de herramientas de visualización externas al editor):

SITEMAP en Mermaid graph TD:
  → Un nodo por pantalla, jerarquía con aristas
  → Anotaciones en el nodo: [M] mobile-first, [A] requiere auth,
    [R] rol restringido, [PENDIENTE VALIDACIÓN] si no está validado

FLOW DIAGRAM en Mermaid flowchart:
  → Rectángulos para pantallas
  → Rombos para decisiones
  → Aristas con labels de acción
  → Sufijo ✓ para flujos exitosos, ✗ para flujos de error,
    (alt) para estados alternativos

FLOW COMPARATIVO (Happy Path vs Error Path):
  Tabla markdown de dos columnas mostrando el contraste paso a paso

Uso:
"genera el sitemap de [descripción del producto]"
"genera el flow del proceso de [checkout / registro / etc.]"
"genera el happy path y error path para [flujo específico]"
```
