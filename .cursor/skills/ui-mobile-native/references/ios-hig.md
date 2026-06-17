# iOS Human Interface Guidelines

> **Datos con caducidad — revisar.** El hardware citado (notch, Dynamic Island,
> home indicator) y las medidas de safe area cambian por generación de iPhone.
> Verificar contra el HIG oficial de Apple para el hardware vigente.

## Los Principios HIG de Apple

```
Apple define tres atributos de una gran app iOS:
1. Claridad: texto legible, iconos claros, funcionalidad obvia
2. Deferencia: la UI ayuda al usuario a entender y interactuar
               con el contenido sin competir con él
3. Profundidad: capas visuales y movimiento transmiten jerarquía

Filosofía de diseño de Apple:
→ La app desaparece — el usuario ve su contenido, no la UI
→ La consistencia con el sistema reduce la curva de aprendizaje
→ Los usuarios ya conocen los patrones de iOS — aprovecharlos
```

---

## Safe Areas y Display Cutouts

```
Los iPhones modernos tienen Dynamic Island/notch y home indicator.
El contenido interactivo NUNCA debe solaparse con estas zonas.

Zonas de safe area:
  Status bar:      ~44-54pt en la parte superior (donde está el reloj/Dynamic Island)
  Home indicator:  ~34pt en la parte inferior (el indicador de swipe a home)
  Sides:           ~0pt (sin cutouts en los lados — iPhone tiene pantalla completa)

SafeAreaInsets en código:
  SwiftUI:    .ignoresSafeArea(edges: .top) para imágenes que se extienden
  Flutter:    SafeArea(child: ...) o MediaQuery.of(context).padding
  RN:         SafeAreaView o useSafeAreaInsets()

Qué va FUERA del safe area (intencional):
  → Imágenes de fondo o hero images (se extienden al borde)
  → Tab bar (el sistema lo gestiona automáticamente)
  → Background colors que llenan toda la pantalla

Qué va DENTRO del safe area (siempre):
  → Texto e información crítica
  → Botones y elementos interactivos
  → Indicadores de estado importantes
```

---

## Tipografía iOS — SF Pro y Type Styles

```
SF Pro (San Francisco) es la fuente del sistema iOS.
En apps nativas se usa automáticamente como la fuente del sistema.
En apps cross-platform (RN/Flutter) se puede configurar explícitamente.

TEXT STYLES OFICIALES DE IOS (en puntos):
  Large Title:    34pt Regular/Bold  → Páginas principales con scroll
  Title 1:        28pt Regular       → Títulos importantes
  Title 2:        22pt Regular       → Subsecciones, títulos de pantalla
  Title 3:        20pt Regular       → Títulos de tarjeta
  Headline:       17pt Semibold      → Texto de énfasis
  Body:           17pt Regular       → Cuerpo de texto principal
  Callout:        16pt Regular       → Texto de apoyo secundario
  Subheadline:    15pt Regular       → Labels secundarios
  Footnote:       13pt Regular       → Notas al pie, timestamps
  Caption 1:      12pt Regular       → Metadatos muy pequeños
  Caption 2:      11pt Regular       → El mínimo legible

DYNAMIC TYPE (obligatorio para accesibilidad):
  → Las apps deben respetar el tamaño de fuente del sistema del usuario
  → UIFont.preferredFont(forTextStyle:) en Swift
  → En Flutter: usar TextTheme del ThemeData que ya incluye scaling
  → En RN: useWindowDimensions() + PixelRatio para adaptación

SF Pro Display vs SF Pro Text:
  → Display: para textos grandes (> 20pt) — más espaciado entre letras
  → Text: para textos normales (< 20pt) — optimizado para legibilidad pequeña
  → iOS cambia automáticamente entre los dos según el tamaño

FUENTES ALTERNATIVAS (cuando no se usa SF Pro):
  → Inter o DM Sans son los sustitutos más cercanos en espíritu
  → Nunca usar fuentes decorativas para body text en iOS
```

---

## Componentes iOS y Sus Reglas

### Tab Bar (Bottom Navigation iOS)

```
La Tab Bar es la navegación principal en iOS.

ANATOMÍA:
  → 3-5 tabs (Apple recomienda máx 5)
  → Cada tab: ícono + label opcional (con label = más claro)
  → El tab activo: ícono en color tint (azul por defecto)
  → Fondo: blur transparente o color sólido

REGLAS:
  → Los tabs representan secciones distintas de la app (no acciones)
  → Cada tab lleva a una pantalla raíz independiente
  → El badge muestra notificaciones no leídas (número o punto rojo)
  → La Tab Bar siempre es visible (no desaparece al scrollear)

ICONOS:
  → SF Symbols preferiblemente (consistencia con el sistema)
  → Pesos: regular para inactivo, fill para activo
  → Tamaño: 25x25pt en la Tab Bar

CUÁNDO NO USAR TAB BAR:
  → Flujos de configuración o onboarding (no tiene tabs)
  → Apps de una sola función (no necesita tabs)
  → Cuando las secciones tienen relación jerárquica (usar Navigation Stack)
```

### Navigation Bar (Header iOS)

```
La Navigation Bar está en la parte superior.

VARIANTES:
  Large Title:  título grande en la parte superior + nav bar compacta al scrollear
                Uso: pantallas de nivel 1 (raíz de cada tab)
  Standard:     título en la barra de navegación (tamaño normal)
                Uso: pantallas de nivel 2 y más profundas
  Inline:       título centrado, más compacta
                Uso: modales y hojas

ELEMENTOS PERMITIDOS:
  → Izquierda: back button (automático) o botón de dismiss (modales)
  → Centro: título o custom view (buscador)
  → Derecha: 1-2 acciones (icono o texto corto)

REGLAS:
  → El título debe ser el nombre de la pantalla o sección
  → Máximo 2 botones de acción a la derecha
  → El back button siempre va a la izquierda (nunca reemplazar)
  → Large Title colapsa a Standard al scrollear (UIScrollView)
```

### Action Sheet y Alert iOS

```
iOS usa dos tipos de diálogo:

ALERT (modal centrado):
  → Para información crítica que requiere decisión del usuario
  → 1-2 botones (horizontal) o 3+ (vertical)
  → El botón destructivo va a la izquierda (convención)
  → Nunca para confirmaciones rutinarias — pierde impacto

  [Mensaje de alerta]
  [Cancelar]  [Confirmar]

ACTION SHEET (sheet desde abajo):
  → Para ofrecer múltiples opciones de acción
  → El botón Cancel está separado al fondo
  → La acción destructiva es en rojo
  → Aparece desde la parte inferior (nativo en iPad es un popover)

  ──────────────────────
  [Opción 1]
  [Opción 2]
  [Eliminar]  ← en rojo
  ──────────────────────
  [Cancelar]
```

---

## Colores iOS

```
SISTEMA DE COLORES DE IOS:
  iOS define colores del sistema que se adaptan automáticamente a light/dark.
  En apps nativas se usan: UIColor.systemBlue, .systemRed, etc.
  En cross-platform: implementar el mismo comportamiento manualmente.

Colores del sistema iOS:
  systemBlue:   #007AFF (light) / #0A84FF (dark)
  systemGreen:  #34C759 / #30D158
  systemRed:    #FF3B30 / #FF453A
  systemOrange: #FF9500 / #FF9F0A
  systemYellow: #FFCC00 / #FFD60A
  systemPink:   #FF2D55 / #FF375F
  systemPurple: #AF52DE / #BF5AF2
  systemTeal:   #5AC8FA / #5AC8FA
  systemIndigo: #5856D6 / #5E5CE6
  systemGray:   escala de 6 niveles adaptativa

COLORES SEMÁNTICOS (se adaptan automáticamente):
  label:           Negro en light, Blanco en dark
  secondaryLabel:  Gris 55% opacidad / Gris 60%
  background:      Blanco / Negro muy oscuro
  secondaryBackground: Blanco roto / Gris muy oscuro
  separator:       Línea sutil que respeta el tema
  tint:            El color de acento de la app (azul por defecto)
```

---

## Entregables portables — patrones iOS

```
Generar en markdown (wireframes ASCII y tablas; no depender de
herramientas de visualización externas al editor):

Pantalla iOS típica con todos sus componentes (wireframe ASCII):
→ Status bar + Navigation bar + contenido + Tab bar
→ Con safe areas correctamente respetadas

Comparativa Large Title vs Standard:
→ La misma pantalla en el estado normal y al scrollear

Action Sheet y Alert iOS:
→ Anatomía de ambos con sus botones y estilos
→ Cuándo usar cada uno

iOS Color System:
→ Los colores del sistema en light y dark mode
→ Cómo cambian automáticamente

Uso:
"genera la pantalla [nombre] con patrón iOS"
"genera la tab bar para [app con 4 tabs]"
"genera el action sheet para [acción de usuario]"
```
