# Adaptación Cross-Platform: RN y Flutter

> **Datos con caducidad — revisar.** Las comparativas de performance entre
> React Native y Flutter, los nombres de componentes y el hardware citado
> (Dynamic Island, etc.) cambian con cada versión. Verificar contra docs
> oficiales y benchmarks recientes antes de decidir.

## El Dilema del Cross-Platform Design

```
Las apps cross-platform tienen que tomar una decisión de diseño fundamental:

OPCIÓN A — PLATAFORMA NATIVA:
  La app se ve y se comporta como iOS en iOS y como Android en Android.
  El diseño se bifurca en dos versiones.
  El código de UI se bifurca parcialmente.

OPCIÓN B — DISEÑO PROPIO (Branded):
  La app tiene su propio look and feel consistente en ambas plataformas.
  Una sola versión del diseño.
  El código de UI es casi idéntico en ambas plataformas.

CUÁNDO ELEGIR NATIVA:
  → La app se compite con apps nativas del sistema (ej: cliente de email)
  → El feel nativo es parte del valor del producto
  → El equipo de diseño puede mantener dos versiones

CUÁNDO ELEGIR BRANDED:
  → B2B / apps de empresa donde la consistencia importa más que el feel
  → Apps donde el branding propio es el diferenciador
  → El equipo no tiene recursos para dos versiones de diseño
  → La mayoría de apps cross-platform van por este camino

REALIDAD EN EL MERCADO:
  → La mayoría de apps cross-platform exitosas usan Branded design
  → Ejemplos: Discord, Shopify, Linear → branded pero respetando las convenciones
  → Los usuarios toleran el branded design si la UX es sólida
```

---

## Adaptar Específicamente por Plataforma

```
Aunque el diseño sea branded, algunas cosas deben adaptarse:

ADAPTAR SIEMPRE (aunque sea branded):

Gestos de navegación:
  → iOS: back gesture desde el borde izquierdo → SIEMPRE respetar
  → Android: back button del sistema → SIEMPRE manejar correctamente
  → Si RN/Flutter no maneja esto, el sistema lo hace por defecto

Safe Areas:
  → iOS: Dynamic Island, home indicator
  → Android: status bar, navigation bar (gesture nav o buttons)
  → Usar SafeAreaView (RN) o SafeArea (Flutter) → adaptación automática

Alerts y Confirmaciones:
  → iOS: UIAlertController (centrado, dos botones horizontales)
  → Android: AlertDialog (material, un botón por línea)
  → En branded: usar el mismo modal custom en ambas plataformas está bien

Teclado:
  → iOS: teclado sube y puede cubrir inputs → KeyboardAvoidingView
  → Android: la pantalla se comprime automáticamente
  → Flutter: Scaffold(resizeToAvoidBottomInset: true)

Haptics:
  → iOS y Android tienen APIs diferentes para haptic feedback
  → Siempre abstraer en una función propia que llame al API correcto

ADAPTAR OPCIONALMENTE (decisión de diseño):

Tipografía:
  → iOS: SF Pro (System font) vs Android: Roboto/Google Sans
  → Branded: una sola fuente para ambas plataformas
  → Semi-nativo: SF Pro en iOS, Google Sans en Android

Iconos:
  → iOS: SF Symbols (requieren iOS 13+)
  → Android: Material Symbols
  → Branded: una sola librería (Lucide, Phosphor, etc.) para ambas

Navegación:
  → iOS espera Tab Bar + Stack
  → Android espera Bottom Nav + Stack
  → En la práctica son casi idénticos — usar el mismo diseño
```

---

## Diseño para React Native

```
COMPONENTES NATIVOS VS CUSTOM:

React Native renderiza componentes nativos REALES:
  <Text>       → UILabel (iOS) / TextView (Android)
  <TextInput>  → UITextField / EditText
  <ScrollView> → UIScrollView / ScrollView
  <Image>      → UIImageView / ImageView
  <Switch>     → UISwitch / Switch
  <Modal>      → UIModalPresentationController / Dialog

Esto significa: el componente <Switch> se ve diferente en iOS vs Android.
Si quieres consistencia visual → reemplazar con un componente custom.

NAVEGACIÓN EN REACT NATIVE:
  React Navigation (el estándar):
  → Stack Navigator: para stack navigation
  → Tab Navigator: para bottom tabs
  → Drawer Navigator: para sidebar
  → Modal stack: para modales

  Animaciones: usa las nativas de cada plataforma por defecto:
  → iOS: slide from right para stack
  → Android: fade through (Material) para stack
  → Se pueden customizar o unificar

LIBRERÍAS QUE AYUDAN AL CROSS-PLATFORM:
  react-native-reanimated  → Animaciones en el UI thread (60fps)
  @shopify/flash-list       → Lista virtualizada de alto performance
  react-native-safe-area-context → Safe areas automáticas
  @gorhom/bottom-sheet     → Bottom sheet nativo de alto rendimiento
  expo-haptics              → Haptic feedback cross-platform
  react-native-svg          → SVG nativo para ilustraciones y iconos
```

---

## Diseño para Flutter

```
FILOSOFÍA DE FLUTTER:
Flutter renderiza sus PROPIOS widgets (no usa componentes nativos).
Usa Skia/Impeller para renderizar directamente en el canvas.
Esto da control total sobre el look and feel pero requiere más trabajo para "sentirse" nativo.

THEMES EN FLUTTER — ADAPTACIÓN POR PLATAFORMA:
  // Detectar la plataforma y aplicar el theme correcto
  ThemeData get _theme => Platform.isIOS
      ? ThemeData(
          platform: TargetPlatform.iOS,
          cupertinoOverrideTheme: const CupertinoThemeData(
            textTheme: CupertinoTextThemeData(/* SF Pro styles */),
          ),
        )
      : ThemeData(
          useMaterial3: true,
          colorSchemeSeed: const Color(0xFF2563EB),
        );

CUPERTINO WIDGETS (look iOS):
  CupertinoNavigationBar      → Navigation bar de iOS
  CupertinoTabBar             → Tab Bar de iOS
  CupertinoButton             → Botón de iOS
  CupertinoTextField          → Input de iOS
  CupertinoAlertDialog        → Alert centrado de iOS
  CupertinoActionSheet        → Action Sheet de iOS
  CupertinoDatePicker         → Drum roll picker de iOS
  CupertinoSwitch             → Switch de iOS

MATERIAL WIDGETS (look Android/branded):
  NavigationBar               → Bottom navigation M3
  FloatingActionButton        → FAB de Material
  FilledButton, OutlinedButton, TextButton → Botones M3
  TextField                   → Input de Material
  AlertDialog                 → Dialog de Material
  BottomSheet                 → Sheet de Android

APPROACH HÍBRIDO (el más usado en producción):
  → Usar Material 3 en ambas plataformas (branded)
  → Adaptar solo los componentes donde el feel nativo importa mucho:
    - Teclado behavior: diferente en iOS vs Android (Scaffold handles it)
    - Scrolling physics: BouncingScrollPhysics en iOS, ClampingScrollPhysics en Android
    - Back navigation: ya manejado por Flutter/Navigator automáticamente

  // Scrolling nativo por plataforma
  ScrollConfiguration(
    behavior: Platform.isIOS
        ? const BouncingScrollBehavior()
        : const ClampingScrollBehavior(),
    child: ListView(...),
  )

  // O automático:
  ListView(
    physics: const ScrollPhysics(), // Flutter auto-adapta
  )
```

---

## Checklist de Cross-Platform Design

```
AMBAS PLATAFORMAS:
□ Safe areas respetadas (Dynamic Island/notch en iOS, nav bar en Android)
□ Touch targets mínimo 44pt/48dp
□ Back gesture funciona en iOS (swipe desde borde izquierdo)
□ Back button del sistema funciona en Android
□ El teclado no cubre los inputs
□ El scroll tiene la física correcta por plataforma
□ Los haptics están implementados donde corresponde
□ Dark mode funciona correctamente en ambas

iOS ESPECÍFICO:
□ Tab Bar en la parte inferior (no en la parte superior)
□ Los modales/sheets se pueden cerrar con swipe down
□ Large Title colapsa al scrollear donde corresponde
□ SF Pro o fuente equivalente se usa para el sistema

ANDROID ESPECÍFICO:
□ El back button no causa un comportamiento inesperado
□ Los bottom sheets funcionan con swipe down
□ El FAB (si se usa) está en la posición correcta (bottom right)
□ Las transiciones de Material Motion están implementadas

CROSS-PLATFORM:
□ El diseño se ve branded (consistente en ambas) o nativo (adaptado a cada una)
□ La decisión fue documentada (no es accidental)
□ Los componentes que difieren por plataforma están bien identificados
□ Las librerías elegidas tienen soporte activo en ambas plataformas
```

---

## Entregables portables — cross-platform

```
Generar en markdown (tablas, Mermaid, ASCII; no depender de
herramientas de visualización externas al editor):

Side-by-side platform comparison (tabla de 2 columnas):
→ La misma pantalla en iOS y Android (branded vs nativo)
→ Las diferencias anotadas claramente

Decision tree para branded vs nativo (Mermaid flowchart):
→ Árbol de decisiones según el tipo de app y el equipo
→ Con ejemplos de apps reales en cada categoría

Component adaptation table (tabla markdown):
→ Qué componentes adaptar vs cuáles unificar
→ Columnas: Componente / iOS nativo / Android nativo / Branded option

Safe area diagram (ASCII de ambos dispositivos):
→ Los dos dispositivos con sus zonas de safe area
→ Cómo SafeAreaView/SafeArea resuelve automáticamente

Uso:
"genera la comparativa iOS vs Android de [mi app tipo X]"
"genera la tabla de adaptación de componentes para [Flutter/RN]"
"genera el diagrama de safe areas para iOS y Android"
```
