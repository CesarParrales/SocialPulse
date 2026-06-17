# Material Design 3 — Android

> **Datos con caducidad — revisar.** Las versiones de Material (M2/M3 y
> posteriores), sus componentes y APIs evolucionan. Verificar contra
> m3.material.io antes de implementar.

## La Filosofía de Material You (M3)

```
Material Design 3 (Material You) es el sistema de diseño de Google para 2021+.
La diferencia fundamental con M2: PERSONALIZACIÓN DINÁMICA.

Material You:
→ El color del sistema se adapta al wallpaper del usuario (Dynamic Color)
→ Formas más redondeadas y expresivas (menos cuadrado que M2)
→ Mayor jerarquía tipográfica
→ Componentes más "expresivos" (menos corporativos)
→ Tonos de color (color roles) en lugar de colores fijos

Key principle: "Expressive personalization for everyone"
El diseño se adapta al usuario, no al revés.
```

---

## Dynamic Color — El Corazón de M3

```
Dynamic Color extrae los colores del wallpaper del usuario
y los aplica en toda la interfaz del sistema y las apps compatibles.

COLOR ROLES EN M3 (en lugar de colores fijos):
  Primary:         el color más frecuente en la UI
  On Primary:      texto/iconos sobre Primary
  Primary Container: contenedor de elementos destacados
  On Primary Container: contenido dentro del Primary Container

  Secondary:       color de apoyo, menos prominente
  Tertiary:        color de acento para elementos especiales
  Error:           para errores y estados destructivos

  Background:      fondo de la pantalla principal
  Surface:         fondo de componentes (cards, sheets)
  Surface Variant: variante de surface para contraste sutil

IMPLEMENTAR EN APPS CROSS-PLATFORM:
  Flutter: ThemeData.colorSchemeSeed() para implementar Dynamic Color
  RN: react-native-material-you o implementar manualmente
  
  Con soporte en Android 12+ (API 31+) el sistema provee la paleta.
  Para versiones anteriores y iOS: usar un color seed fijo de la marca.
```

---

## Tipografía Material 3

```
M3 usa Google Sans / Roboto con un sistema tipográfico expresivo.

TYPE SCALE M3:
  Display Large:   57sp / 64sp line-height / -0.25 tracking
  Display Medium:  45sp / 52sp / 0
  Display Small:   36sp / 44sp / 0

  Headline Large:  32sp / 40sp / 0
  Headline Medium: 28sp / 36sp / 0
  Headline Small:  24sp / 32sp / 0

  Title Large:     22sp / 28sp / 0
  Title Medium:    16sp / 24sp / +0.15
  Title Small:     14sp / 20sp / +0.1

  Body Large:      16sp / 24sp / +0.5
  Body Medium:     14sp / 20sp / +0.25
  Body Small:      12sp / 16sp / +0.4

  Label Large:     14sp / 20sp / +0.1
  Label Medium:    12sp / 16sp / +0.5
  Label Small:     11sp / 16sp / +0.5

sp = scale-independent pixels (respetan el tamaño de fuente del sistema)
```

---

## Componentes M3 y Sus Variantes

### Botones M3

```
M3 tiene 5 variantes de botón (de más a menos énfasis):

Filled Button (mayor énfasis):
  → Fondo: color Primary
  → Para la acción principal del flujo
  → Equivalente al "primary" en otros sistemas

Filled Tonal Button:
  → Fondo: Secondary Container (más sutil que Filled)
  → Para acciones importantes pero no la principal
  → El más usado en Android moderno para acciones secundarias

Elevated Button:
  → Fondo: Surface con sombra
  → Para acciones en superficies de color (como cards)
  → Proporciona separación visual

Outlined Button:
  → Fondo: transparente, con borde
  → Para acciones alternativas con visibilidad media
  → Similar al secondary en otros sistemas

Text Button (menor énfasis):
  → Solo texto, sin fondo ni borde
  → Para acciones opcionales, en dialogs, o en listas

FAB (Floating Action Button):
  → Para la acción principal de la pantalla (crear, componer, etc.)
  → Siempre flotando sobre el contenido
  → 3 tamaños: small (40dp) / regular (56dp) / large (96dp)
  → Extended FAB: incluye un label de texto
```

### Navigation Bar (Bottom Nav M3)

```
La Bottom Navigation Bar es la navegación principal en Android.

ANATOMÍA M3:
  → 2-5 destinos (como iOS, pero se llaman "destinations")
  → Icon + label en todos (M3 siempre muestra el label)
  → El item activo: indicator pill sobre el ícono (novedad M3)
  → Fondo: Surface color

DIFERENCIAS CON IOS:
  → iOS: el tab activo cambia el color del ícono
  → Android M3: un "pill" de color aparece detrás del ícono activo
  → El efecto M3 es más "material" y expresivo

NAVIGATION RAIL (para tablets Android):
  → La versión vertical de la Bottom Nav para pantallas anchas
  → Íconos a la izquierda, labels opcionales
  → Automáticamente usar cuando el ancho > 600dp

NAVIGATION DRAWER:
  → Para apps con muchos destinos (> 5)
  → O cuando necesitas más texto descriptivo por item
  → Modal (sobre el contenido) o Permanent (al lado del contenido)
```

### Sheets M3

```
BOTTOM SHEET (el modal estándar de Android):
  Modal Bottom Sheet:
  → Se superpone sobre el contenido con overlay oscuro
  → Se puede cerrar con swipe hacia abajo o tapping el overlay
  → Para acciones o contenido que no requiere pantalla completa

  Standard Bottom Sheet:
  → Co-existe con el contenido principal (sin overlay)
  → Se puede expandir a pantalla completa con drag
  → Para contenido que se usa mientras se ve el contenido principal

  En Flutter: showModalBottomSheet() y BottomSheet
  En RN: @gorhom/bottom-sheet (la librería estándar)

SIDE SHEET (novedad en M3):
  → Aparece desde el lado derecho
  → Para filtros, detalles adicionales, configuración
  → Más común en tablets (> 600dp)
```

---

## Elevación y Superficies en M3

```
M3 usa color tinting en lugar de sombras para la elevación.
Las superficies más "elevadas" tienen más tinting del color primario.

TONAL SURFACE LEVELS:
  Level 0 (base):    Surface puro (sin tinting)
  Level 1 (+5%):     Cards, Navigation Drawers
  Level 2 (+8%):     Floating Action Buttons
  Level 3 (+11%):    Navigation Bar, Bottom Sheet header
  Level 4 (+12%):    solo en estados específicos
  Level 5 (+14%):    Navigation Rail, Tooltips

Por qué es mejor que sombras:
→ Funciona en dark mode (las sombras se pierden en backgrounds oscuros)
→ Refleja el Dynamic Color del usuario
→ Más sutil y moderno

Para implementar en Flutter:
  Material(
    elevation: 3,
    color: Theme.of(context).colorScheme.surface,
    // Flutter aplica el tinting automáticamente
  )
```

---

## Entregables portables — patrones Android M3

```
Generar en markdown (wireframes ASCII y tablas; no depender de
herramientas de visualización externas al editor):

Bottom Navigation M3 con pill indicator:
→ El indicador de selección de M3 vs M2 vs iOS

Botones M3 en sus 5 variantes:
→ Filled / Tonal / Elevated / Outlined / Text / FAB
→ Con colores del sistema M3

Bottom Sheet con estados:
→ Colapsado, medio expandido, expandido a pantalla completa

Dynamic Color comparison:
→ La misma app con 3 wallpapers diferentes
→ Cómo cambia la paleta automáticamente

Elevación con color tinting:
→ Los 5 niveles de surface con su tinting visual

Uso:
"genera la pantalla [nombre] con patrón Android M3"
"genera la bottom navigation M3 para [app de 4 tabs]"
"genera los botones M3 con Dynamic Color"
```
