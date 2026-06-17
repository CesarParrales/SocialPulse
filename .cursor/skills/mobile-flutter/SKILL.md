---
name: mobile-flutter
description: >
  Guía Flutter con Dart: clean architecture pragmática, estado con Riverpod,
  navegación con GoRouter, integración con APIs, notificaciones push, builds,
  flavors y deploy a stores. Usar cuando el usuario mencione Flutter, Dart,
  Riverpod, GoRouter, BLoC, Flutter widgets, pub.dev, Freezed, o cuando diga
  "cómo estructuro un proyecto Flutter", "cómo manejo estado en Flutter",
  "cómo navego en Flutter", "cómo publico en stores con Flutter", o cualquier
  variante de desarrollo móvil con Flutter.
---

# Mobile Flutter Skill

Flutter con Dart — desarrollo mobile de producción.

**Arquitectura y Estructura → `references/architecture.md`**
**Riverpod — Estado y Async → `references/riverpod.md`**
**GoRouter — Navegación → `references/navigation.md`**
**Widgets, Temas y Animaciones → `references/widgets-ui.md`**
**Build, Deploy, Push y Flavors → `references/build-deploy.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — flavors, gates `flutter analyze` / `flutter test`.
2. Estructura `lib/features/` si está documentada.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** convenciones de capas → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

### Bootstrap de proyecto (gates en orden)

0. **Memoria** — leer project-memory antes de crear estructura duplicada.
1. **Entorno**: ejecuta `flutter doctor` y verifica que no hay issues bloqueantes (✗) en las plataformas objetivo.
2. **Crear proyecto y dependencias** (sección Setup Inicial abajo); lee `references/architecture.md` y monta la estructura feature-first.
3. **Análisis estático**: ejecuta `flutter analyze` y verifica 0 errores antes de seguir.
4. **Tests base**: ejecuta `flutter test` y verifica que pasa en verde.

### Feature nuevo (5 pasos)

1. **Estructura**: crea `lib/features/X/{data,domain/models,presentation/{providers,screens,widgets}}` siguiendo `references/architecture.md`.
2. **Modelos**: define modelos Freezed + failure tipado (sealed class) en `domain/`.
3. **Estado**: datasource + repository en `data/` y providers Riverpod según `references/riverpod.md`; inyección via providers, no get_it.
4. **UI**: screen con `AsyncValue.when` (loading/error/empty/data) según `references/widgets-ui.md`; registra la ruta con `references/navigation.md`. Gate: ejecuta `dart run build_runner build --delete-conflicting-outputs` y verifica que termina sin conflictos.
5. **Verificación**: ejecuta `flutter analyze` (0 errores) y `flutter test` (verde) antes de cerrar. Para release, sigue `references/build-deploy.md`. Ejecutar `## Validación` y registrar gaps en `LEARNINGS.md`.

---

## Defaults si falta contexto

Asume y **declara** estos supuestos en lugar de preguntar (máx. 1 pregunta solo si es bloqueante):

- Estado → **Riverpod** con codegen (`@riverpod`); no BLoC salvo que el proyecto ya lo use.
- Navegación → **GoRouter** declarativo.
- Modelos → **Freezed** + `json_serializable`.
- HTTP → **Dio** con interceptors; errores tipados con sealed classes.
- Estructura → **feature-first** con capas data/domain/presentation.
- Ambientes → 3 flavors: dev / staging / production con `main_<flavor>.dart`.
- Push → **Firebase Messaging** + `flutter_local_notifications`.

---

## Flutter vs React Native — Cuándo Elegir Cada Uno

```
Flutter:
  ✅ UI 100% custom — pixel perfect en iOS y Android
  ✅ Performance superior (motor Skia/Impeller — renders propios)
  ✅ Una codebase: iOS, Android, Web, Desktop, Embedded
  ✅ Hot reload extremadamente rápido
  ✅ Dart tipado — menos errores runtime
  ✅ Mejor para animaciones complejas
  ❌ Dart — curva de aprendizaje si el equipo es JS
  ❌ Tamaño de APK/IPA mayor que nativo
  ❌ Apariencia no nativa por defecto (se puede emular)

React Native:
  ✅ Equipo JS/TS existente puede adoptarlo rápido
  ✅ Apariencia nativa por defecto
  ✅ Expo para setup rápido
  ❌ Bridge overhead (mejorado con JSI pero existe)
  ❌ Dependiente del ecosistema JS (más fragmentación)
  ❌ Animaciones complejas requieren Reanimated

Elegir Flutter cuando:
- UI muy personalizada que difiere del estilo nativo
- Equipo nuevo sin background JS
- App también para web/desktop
- Performance y animaciones son críticos

Elegir React Native cuando:
- Equipo existente de JS/TS
- Necesitas compartir código con una app web
- Integración profunda con APIs nativas específicas
```

---

## Setup Inicial

```bash
# Instalar Flutter SDK
# https://docs.flutter.dev/get-started/install

# Verificar instalación — gate: sin issues bloqueantes
flutter doctor

# Crear proyecto con estructura limpia
flutter create --org com.mycompany --template app myapp
cd myapp

# Dependencias esenciales (pubspec.yaml)
flutter pub add \
  flutter_riverpod \
  riverpod_annotation \
  go_router \
  dio \
  flutter_secure_storage \
  shared_preferences \
  cached_network_image \
  flutter_hooks \
  hooks_riverpod \
  freezed_annotation \
  json_annotation

# Dev dependencies
flutter pub add --dev \
  build_runner \
  riverpod_generator \
  freezed \
  json_serializable \
  flutter_lints \
  mocktail
```

Estructura de carpetas completa (feature-first, capas data/domain/presentation, criterios de `core/` vs `shared/`) → `references/architecture.md`.

---

## Entry Point

```dart
// main.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'app.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Inicializaciones async antes de renderizar
  // await Firebase.initializeApp();

  runApp(
    const ProviderScope(  // Riverpod scope — wrappea toda la app
      child: MyApp(),
    ),
  );
}

// app.dart
class MyApp extends ConsumerWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);

    return MaterialApp.router(
      title: 'MyApp',
      theme: AppTheme.light,
      darkTheme: AppTheme.dark,
      themeMode: ThemeMode.system,
      routerConfig: router,
      debugShowCheckedModeBanner: false,
    );
  }
}
```

---

## Modelos con Freezed

```dart
// features/orders/domain/models/order.dart
import 'package:freezed_annotation/freezed_annotation.dart';

part 'order.freezed.dart';
part 'order.g.dart';

enum OrderStatus {
  pending,
  processing,
  shipped,
  delivered,
  cancelled;

  String get label => switch (this) {
    OrderStatus.pending    => 'Pending',
    OrderStatus.processing => 'Processing',
    OrderStatus.shipped    => 'Shipped',
    OrderStatus.delivered  => 'Delivered',
    OrderStatus.cancelled  => 'Cancelled',
  };

  Color get color => switch (this) {
    OrderStatus.pending    => Colors.orange,
    OrderStatus.processing => Colors.blue,
    OrderStatus.shipped    => Colors.purple,
    OrderStatus.delivered  => Colors.green,
    OrderStatus.cancelled  => Colors.red,
  };
}

@freezed
class Order with _$Order {
  const factory Order({
    required String id,
    required String reference,
    required OrderStatus status,
    required int totalCents,
    required String currency,
    required String shippingAddress,
    required DateTime createdAt,
    @Default([]) List<OrderItem> items,
    String? notes,
  }) = _Order;

  factory Order.fromJson(Map<String, dynamic> json) => _$OrderFromJson(json);
}

// Generar código:
// dart run build_runner build --delete-conflicting-outputs
```

---

## Checklist Flutter Producción

### Código
- [ ] Modelos con Freezed (immutabilidad + copyWith + fromJson)
- [ ] Estado con Riverpod (no setState en lógica de negocio)
- [ ] GoRouter para navegación declarativa
- [ ] Error handling en todos los async providers
- [ ] Sin print() en producción (usar logger package)

### Performance
- [ ] const constructors donde sea posible
- [ ] ListView.builder (no ListView + children para listas largas)
- [ ] RepaintBoundary para widgets que animan aislados
- [ ] CachedNetworkImage para imágenes remotas
- [ ] Lazy loading con pagination en listas

### Build
- [ ] Flavors configurados (dev/staging/production)
- [ ] Signing configurado (keystore Android, certificates iOS)
- [ ] Proguard/R8 habilitado en Android release
- [ ] app-split para reducir tamaño en Android

---

## Ejemplo input → output

**Input:** "Feature de listado de workspaces con pull-to-refresh."

**Output:** `lib/features/workspaces/` con Freezed models, Riverpod async provider, GoRouter route; UI con `AsyncValue.when`. Gates: `flutter analyze` 0 errores; `flutter test` verde.

---

## Validación

| Gate | Comando | Criterio |
|------|---------|----------|
| Doctor | `flutter doctor` | sin ✗ en plataformas objetivo |
| Codegen | `dart run build_runner build --delete-conflicting-outputs` | sin conflictos |
| Analyze | `flutter analyze` | 0 errores |
| Tests | `flutter test` | exit 0 |
| UI states | pantalla implementada | loading/error/empty/data |

---

## Entregable

Al cerrar una tarea con esta skill, entrega:

```markdown
## Implementación Flutter — <feature>

**Estructura**: carpetas creadas en features/<X>/
**Modelos**: modelos Freezed y failures tipados definidos
**Estado**: providers Riverpod creados (DI + estado de UI)
**Rutas**: rutas añadidas a GoRouter

### Verificación
- [ ] `dart run build_runner build --delete-conflicting-outputs` sin conflictos
- [ ] `flutter analyze` → 0 errores
- [ ] `flutter test` → verde
- [ ] Estados cubiertos en UI: loading / error con retry / empty / data

### Pendientes / riesgos
- ...
```

---

## Skills relacionadas

- `mobile-react-native` — alternativa con React Native + Expo (comparativa arriba)
- `ui-mobile-native` — diseño visual y patrones de UI móvil
- `api-design` — diseño de las APIs que consume la app
- `devops-base` — CI/CD base (la parte mobile vive en `references/build-deploy.md`)
- `testing-strategy` — estrategia global de tests

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
