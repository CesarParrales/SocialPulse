---
name: ui-mobile-native
description: >
  Diseño para iOS y Android: guidelines HIG/Material 3, gestos nativos,
  componentes por plataforma, adaptación React Native / Flutter, patrones
  de navegación mobile. Activar cuando el usuario necesite diseñar para
  iOS o Android, cuando mencione HIG, Material Design, Material 3, gestos
  nativos, bottom navigation, tab bar, FAB, componentes de plataforma,
  o cuando diga "cómo se ve esto en iOS vs Android", "cómo diseño la
  navegación mobile", "patrones de mobile", o cualquier variante sobre
  diseño específico para plataformas móviles.
---

# UI Mobile Native Skill

El diseño para mobile no es "hacer la web más pequeña".
Es un paradigma diferente: pantalla pequeña, gestos, thumb zones, interrupciones.

iOS y Android tienen culturas de diseño distintas.
Respetarlas no es opcional — los usuarios esperan que la app se sienta "nativa".

**iOS Human Interface Guidelines → `references/ios-hig.md`**
**Material Design 3 (Android) → `references/material3.md`**
**Gestos y thumb zones → `references/gestures.md`**
**Navegación mobile → `references/navigation.md`**
**Adaptación cross-platform (RN/Flutter) → `references/cross-platform.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — stack mobile (RN/Flutter), enfoque branded vs nativo.
2. Flujos de `ux-architecture` si existen.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** matrices por pantalla en `docs/`; decisiones cross-platform → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory y flujos acordados.
1. **Definir el enfoque de plataforma**: nativo por plataforma, cross-platform
   Branded, o cross-platform con feel nativo. Si no está definido → Defaults.
2. **Diseñar la navegación**: patrón por plataforma (tab bar / bottom nav,
   stack, modales). Leer `references/navigation.md`.
3. **Aplicar guidelines por plataforma**: `references/ios-hig.md` y/o
   `references/material3.md` según el alcance.
4. **Definir gestos y thumb zones**: `references/gestures.md`.
5. **Si es cross-platform**: decidir qué se unifica y qué se adapta por
   plataforma con `references/cross-platform.md`.
6. **Entregar la matriz iOS vs Android por pantalla** (plantilla en
   Entregable) para cada pantalla clave.
7. **Validación y cierre** — ejecutar `## Validación`; pre-flight 8 ítems; registrar gaps en `LEARNINGS.md`.

### Pre-flight de 8 ítems verificables

```
✓/✗ 1. Touch targets ≥ 44×44pt (iOS) / 48×48dp (Android)
✓/✗ 2. Safe areas respetadas (notch/Dynamic Island en iOS, nav/status bar Android)
✓/✗ 3. Back gesture de Android contemplado (no entra en conflicto con swipes propios)
✓/✗ 4. Dynamic Type / escalado de fuente del sistema soportado
✓/✗ 5. Contraste verificado (WCAG AA, legible en exteriores)
✓/✗ 6. Estados de carga definidos (skeleton, no solo spinner)
✓/✗ 7. Teclado contemplado (el contenido no queda oculto; tipo de teclado por campo)
✓/✗ 8. Orientación definida (portrait-only declarado, o landscape diseñado)
```

---

## Defaults si falta contexto

El agente asume y DECLARA estos supuestos en vez de preguntar
(máx 1 pregunta si es bloqueante):

- **Cross-platform** → enfoque **Branded** (un solo diseño con adaptaciones
  mínimas por plataforma), salvo que el usuario pida feel nativo por plataforma.
- **Sin plataforma definida** → diseñar para ambas con la matriz iOS vs Android,
  marcado `[HIPÓTESIS]`.
- **Sin framework definido** → asumir el que indique el stack del equipo;
  si no hay señal, documentar la decisión como pendiente sin bloquear el diseño.
- **Sin requisito de tablet** → diseñar phone-first (~390pt de ancho) y declararlo.
- **Sin modo oscuro requerido** → diseñar light con tokens listos para dark.

---

## Las Diferencias Fundamentales entre iOS y Android

```
iOS:                              Android:
─────────────────────────────────────────────────────────
Back:    swipe desde el borde     Back: botón sistema o gesture
Nav bar: bottom tab bar           Nav:  bottom nav (M3) o drawer
Modal:   sheet desde abajo        Modal: bottom sheet o dialog
CTA:     pill buttons con border  CTA:  filled buttons con shadow
Fonts:   SF Pro (system)          Fonts: Google Sans / Roboto
Icons:   SF Symbols               Icons: Material Symbols
Alerts:  centrados, botones horiz.Alerts: Snackbars (bottom)
Scroll:  rubber band              Scroll: sin rubber band
Toggle:  right-aligned            Toggle: can be left or right
Pickers: drum rolls nativos       Pickers: dialogs con opciones
```

---

## Los Principios de Mobile UI (ambas plataformas)

```
1. THUMB ZONE — diseñar para el pulgar
   → La parte inferior de la pantalla es la zona segura de interacción
   → Las acciones principales deben estar al alcance del pulgar
   → La navegación abajo, no arriba

2. CONTENIDO FIRST
   → El contenido ocupa el máximo espacio posible
   → Los elementos de UI son minimizados pero siempre accesibles
   → Sin toolbars permanentes que quiten espacio al contenido

3. CONTEXTO DE INTERRUPCIÓN
   → Las apps móviles se usan en contextos de distracción
   → El estado se guarda automáticamente (no hay "guardar" manual)
   → El deep link permite volver al contexto correcto

4. GESTOS SON SHORTCUTS, NO SUSTITUTOS
   → Los gestos aceleran la interacción para expertos
   → Siempre debe haber un equivalente visual/táctil
   → Los gestos no visibles siempre necesitan un descubrimiento

5. DENSIDAD APROPIADA PARA DEDOS
   → Touch targets mínimo 44×44pt (iOS) / 48×48dp (Android)
   → Más espacio entre elementos que en desktop
   → Los elementos interactivos no deben estar demasiado juntos
```

---

## Ejemplo input → output

**Input:** "Pantalla login en app RN Expo — feel branded."

**Output:** matriz iOS vs Android (nav, CTA, teclado); touch targets ≥44/48; safe areas; estados loading skeleton. Gate: pre-flight 8/8 ✓.

---

## Validación

| Gate | Acción | Criterio |
|------|--------|----------|
| Matriz | por pantalla clave | plantilla Entregable completa |
| Pre-flight | 8 ítems | todos ✓/✗ documentados |
| Touch targets | dimensiones | ≥44pt / ≥48dp |
| Plataforma | guidelines | HIG y/o M3 según alcance |
| Cross-platform | decisión | unificada vs adaptada documentada |

---

## Entregables visuales (portables)

- Diagramas, sitemaps y flujos → bloques Mermaid (flowchart/graph)
- Auditorías y comparativas → tablas markdown
- Wireframes y layouts → bloques ASCII
- Jerarquías de componentes → árbol con indentación o Mermaid
No depender de herramientas de visualización externas al editor.

---

## Entregable — Matriz iOS vs Android por pantalla

Plantilla copiable (una por pantalla clave):

```markdown
# Matriz iOS vs Android — Pantalla: [nombre]

| Dimensión | iOS | Android | Cross-platform (Branded) |
|---|---|---|---|
| Navegación | [tab bar / push / sheet] | [bottom nav / back gesture] | [decisión unificada] |
| CTA principal | [pill button, posición] | [filled button / FAB] | [decisión] |
| Modales | [sheet desde abajo] | [bottom sheet / dialog] | [decisión] |
| Tipografía | [SF Pro, tamaño pt] | [Roboto/Google Sans, sp] | [familia única + ajustes] |
| Gestos | [swipe back, pull-to-refresh] | [back gesture, ripple] | [decisión + conflictos] |
| Feedback | [haptics, alerts centrados] | [snackbar, ripple] | [decisión] |

Notas de adaptación: ...
Pre-flight (8 ítems): [tabla ✓/✗]
```

---

## Checklist de Mobile UI Completo

```
Touch targets:
✓ Todos los elementos interactivos: mín 44pt iOS / 48dp Android
✓ Espacio entre targets adyacentes: mín 8pt/dp
✓ No hay acciones que requieren precisión táctil excesiva

Navegación:
✓ La navegación principal está en la parte inferior (iOS/Android moderno)
✓ El back gesture funciona en iOS (swipe desde el borde izquierdo)
✓ Los modales/sheets se pueden cerrar con swipe hacia abajo
✓ El deep linking está contemplado para las pantallas principales

Safe Areas:
✓ El contenido respeta el safe area del notch/Dynamic Island en iOS
✓ El contenido respeta la barra de navegación del sistema en Android
✓ Nada importante está oculto por el sistema UI

Texto y legibilidad:
✓ Texto mínimo de 17pt para body en iOS / 16sp en Android
✓ Contraste suficiente para leer en exteriores
✓ No se usa fuente muy delgada (weight < 400) en texto pequeño

Performance percibida:
✓ Las transiciones entre pantallas son fluidas (60fps mínimo)
✓ Los estados de carga son inmediatos (skeleton, no spinner)
✓ Las animaciones respetan prefers-reduced-motion
```

---

## Datos con caducidad — revisar (sección fechada: 2026-06)

> Lo siguiente es time-sensitive: versiones de Material 3, hardware de iPhone
> (Dynamic Island) y comparativas de performance RN/Flutter evolucionan.
> Verificar contra docs oficiales antes de decidir.

```
APPS NATIVAS (Swift/Kotlin):
  ✓ Performance crítica (juegos, AR, procesamiento de video)
  ✓ Integración profunda con hardware (sensores, NFC, health)
  ✓ El look-and-feel nativo es un diferencial competitivo
  ✓ Presupuesto para dos equipos de desarrollo
  ✗ Costoso mantener dos codebases

CROSS-PLATFORM FLUTTER:
  ✓ Un codebase para iOS + Android (+ web + desktop)
  ✓ Performance cercana a native (renderiza sus propios widgets)
  ✓ Design system propio (Cupertino o Material o custom)
  ✓ Hot reload acelera desarrollo
  ✗ No usa componentes nativos reales — puede sentirse "extraño" en iOS

CROSS-PLATFORM REACT NATIVE:
  ✓ Un codebase con puentes a componentes nativos reales
  ✓ Equipo web puede contribuir al mobile
  ✓ Librería de componentes nativos accesible
  ✗ Performance inferior a Flutter en UIs complejas con animaciones
  ✗ Los puentes nativo→JS pueden crear latencia

CUÁNDO USAR CADA UNO:
  B2B / empresa interna:   Flutter o RN (time to market importa más que native feel)
  Consumer app masiva:     Nativo o Flutter
  Startup early stage:     RN (si el equipo ya sabe React) o Flutter
  Equipo web puro:         RN (reutiliza conocimiento)
```

---

## Skills relacionadas

- `mobile-react-native` — implementación técnica en React Native
- `mobile-flutter` — implementación técnica en Flutter
- `ui-web-modern` — algunos principios aplican pero con ajustes para mobile
- `ux-architecture` — los flujos y sitemaps se adaptan para mobile

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
