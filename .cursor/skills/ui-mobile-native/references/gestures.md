# Gestos y Thumb Zones

## El Thumb Zone — Diseñar Para el Pulgar

```
El 75% de las interacciones en smartphones se hacen con un solo pulgar.
El diseño debe acomodar este hecho, no ignorarlo.

THUMB ZONE EN UN SMARTPHONE ESTÁNDAR (6"):

          ┌──────────────────────┐
          │ ████████████████████ │ ← Zona roja: muy difícil
          │ ████████████████████ │    (requiere cambiar el agarre)
          │                      │
          │ ░░░░░░░░░░░░░░░░░░░░ │ ← Zona amarilla: con esfuerzo
          │ ░░░░░░░░░░░░░░░░░░░░ │    (pulgar extendiéndose)
          │ ░░░░░░░░░░░░░░░░░░░░ │
          │                      │
          │ ▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓ │ ← Zona verde: fácil
          │ ▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓ │    (alcance natural del pulgar)
          │ ▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓ │
          │                      │
          └──────────────────────┘
          [Home indicator / bottom nav]

Para teléfonos grandes (6.5"+):
→ La zona verde se reduce más
→ Más área requiere reposicionar la mano
→ La reachability de iOS (doble tap en borde) compensa parcialmente

IMPLICACIONES PARA EL DISEÑO:

Zona VERDE (siempre aquí):
  ✅ Navegación principal (Tab Bar / Bottom Nav)
  ✅ Acciones principales (FAB, botón de confirmar)
  ✅ El input más frecuente del flujo

Zona AMARILLA (contenido principal, acciones secundarias):
  ✅ Contenido scrolleable (el usuario ya está comprometido)
  ✅ Acciones secundarias (no críticas)
  ✅ Listas de items que se desplazan

Zona ROJA (solo cuando no hay alternativa):
  ⚠️  Navigation bar del sistema (el usuario está acostumbrado)
  ⚠️  Título de pantalla (solo informativo, no interactivo)
  ❌  Botones de acción frecuente
  ❌  Acciones en la primera línea de una lista
```

---

## Los Gestos Nativos Principales

```
iOS GESTURES:

Swipe desde el borde izquierdo:
  → Navegar hacia atrás (equivalente al back button)
  → CRÍTICO: si tu UI interfiere con este gesto, rompe el comportamiento nativo
  → No colocar elementos interactivos en el borde izquierdo de la pantalla

Swipe hacia abajo en un modal/sheet:
  → Cerrar el modal (iOS 13+)
  → Debe funcionar siempre en sheets
  → En modals a pantalla completa: solo si hay un botón de dismiss

Swipe para eliminar en lista (leading/trailing):
  → Leading: acción positiva (marcar como leído, etc.)
  → Trailing: acción destructiva (eliminar, archivar)
  → Patrón establecido por Mail.app

Long press:
  → Context menu (menú flotante con acciones sobre el item)
  → Para acciones secundarias sobre un elemento específico
  → iOS 14+: context menu reemplaza 3D touch

Pinch to zoom:
  → Para imágenes, mapas, documentos
  → El sistema lo provee automáticamente en ScrollView con zoom

Double tap:
  → Zoom in/out en imágenes y mapas
  → Scroll al tope de la pantalla (doble tap en status bar)

ANDROID GESTURES:

Back gesture (sistema):
  → Swipe desde el borde izquierdo O derecho (Android 10+)
  → Maneja el Back Stack del sistema
  → Tu app debe manejar el back press correctamente
  → En Android 13+: predictive back animation

Swipe to dismiss (SnackBar, notificaciones):
  → Deslizar lateralmente para descartar
  → La SnackBar usa este patrón nativamente

Pull to refresh:
  → Deslizar hacia abajo cuando se está en el tope del scroll
  → Ambas plataformas soportan este patrón
  → Indicador de carga circular centrado

Drag to reorder (listas):
  → Long press + drag para reordenar items
  → Visual feedback: item se eleva con sombra mientras se arrastra
```

---

## Touch Targets — El Mínimo No Negociable

```
iOS:
  Mínimo: 44 × 44 pt (puntos, no pixels)
  Recomendado: 44-60 pt para acciones frecuentes

Android:
  Mínimo: 48 × 48 dp (density-independent pixels)
  Recomendado: 48-60 dp

La zona táctil puede ser mayor que el elemento visual:
  // Flutter
  GestureDetector(
    behavior: HitTestBehavior.opaque,
    child: Padding(
      padding: const EdgeInsets.all(12), // extiende el área sin visual
      child: Icon(Icons.close, size: 20),
    ),
    onTap: onClose,
  )

  // React Native
  <TouchableOpacity
    style={{ padding: 12 }}  // el área táctil incluye el padding
    hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}  // incluso más
    onPress={onClose}
  >
    <Icon name="x" size={20} />
  </TouchableOpacity>

DISTANCIA ENTRE TOUCH TARGETS:
  Mínimo entre dos targets adyacentes: 8pt/dp
  Recomendado: 12-16pt/dp
  Los targets demasiado cerca causan errores de tap frecuentes
```

---

## Feedback Táctil y Háptico

```
El feedback táctil es parte de la experiencia mobile.
Un gesto sin respuesta táctil se siente "muerto".

IOS HAPTICS:
  UIImpactFeedbackGenerator:
    .light:   para selecciones de UI menores
    .medium:  para acciones más significativas
    .heavy:   para acciones de mayor peso
    .rigid:   sensación más sólida y precisa
    .soft:    sensación suave, para contenido que se asienta

  UINotificationFeedbackGenerator:
    .success: cuando una tarea se completa correctamente
    .warning: para advertencias
    .error:   para errores

  UISelectionFeedbackGenerator:
    → Para cambios de selección en pickers, sliders, etc.

  En Flutter: HapticFeedback.mediumImpact()
  En RN: ReactNativeHapticFeedback o Vibration de React Native

ANDROID HAPTICS:
  Vibration.vibrate([0, 10]):  impacto ligero
  performHapticFeedback():     feedback de sistema
  En Flutter: HapticFeedback (mismo API que iOS)

CUÁNDO USAR CADA HAPTIC:
  ✅ Confirmación de una acción importante (success)
  ✅ Toggle de un switch (selection)
  ✅ Límite de scroll o pull to refresh (light impact)
  ✅ Error en validación (error)
  ❌ En cada tap de cualquier elemento (cansa al usuario)
  ❌ En animaciones o transiciones (distrae)

REGLA GENERAL:
El haptic debe corresponder exactamente con el momento del evento visual.
Un haptic desincronizado es peor que ningún haptic.
```

---

## Entregables portables — gestos y touch zones

```
Generar en markdown (ASCII y tablas; no depender de herramientas
de visualización externas al editor):

Thumb Zone diagram (ASCII del smartphone):
→ Zonas marcadas: ✓ fácil / ~ con esfuerzo / ✗ difícil
→ Los elementos del layout colocados en su zona correcta
→ Versión para mano derecha e izquierda

Touch target sizes (tabla comparativa):
→ 44pt vs 48dp vs tamaños incorrectos (demasiado pequeños)
→ Con el "invisible hitslop" anotado en texto

Gesture map de una pantalla (ASCII anotado):
→ La pantalla con todos los gestos disponibles anotados
→ Swipe from edge / long press / pull to refresh / swipe to dismiss

Uso:
"genera el thumb zone analysis para [tipo de app]"
"genera el gesture map para [pantalla específica]"
"muéstrame los touch targets de [componente de navegación]"
```
