# Build, Deploy y Flavors

## Flavors — Ambientes (dev/staging/production)

```dart
// lib/core/config/app_config.dart
enum AppFlavor { dev, staging, production }

class AppConfig {
  AppConfig._({
    required this.flavor,
    required this.apiUrl,
    required this.appName,
  });

  final AppFlavor flavor;
  final String apiUrl;
  final String appName;

  static late AppConfig instance;

  static void initialize(AppFlavor flavor) {
    instance = switch (flavor) {
      AppFlavor.dev => AppConfig._(
          flavor: flavor,
          apiUrl: 'https://dev-api.myapp.com',
          appName: 'MyApp Dev',
        ),
      AppFlavor.staging => AppConfig._(
          flavor: flavor,
          apiUrl: 'https://staging-api.myapp.com',
          appName: 'MyApp Staging',
        ),
      AppFlavor.production => AppConfig._(
          flavor: flavor,
          apiUrl: 'https://api.myapp.com',
          appName: 'MyApp',
        ),
    };
  }

  bool get isDev => flavor == AppFlavor.dev;
  bool get isProduction => flavor == AppFlavor.production;

  static String get apiUrl => instance.apiUrl;
}
```

```dart
// main_dev.dart
void main() {
  AppConfig.initialize(AppFlavor.dev);
  runApp(const ProviderScope(child: MyApp()));
}

// main_staging.dart
void main() {
  AppConfig.initialize(AppFlavor.staging);
  runApp(const ProviderScope(child: MyApp()));
}

// main_production.dart
void main() {
  AppConfig.initialize(AppFlavor.production);
  runApp(const ProviderScope(child: MyApp()));
}
```

---

## Configuración de Flavors en Android

```groovy
// android/app/build.gradle
android {
    flavorDimensions "environment"

    productFlavors {
        dev {
            dimension "environment"
            applicationIdSuffix ".dev"
            versionNameSuffix "-dev"
            resValue "string", "app_name", "MyApp Dev"
        }
        staging {
            dimension "environment"
            applicationIdSuffix ".staging"
            versionNameSuffix "-staging"
            resValue "string", "app_name", "MyApp Staging"
        }
        production {
            dimension "environment"
            resValue "string", "app_name", "MyApp"
        }
    }
}
```

---

## Configuración de Flavors en iOS

```ruby
# ios/Podfile — targets separados por flavor
# Crear targets en Xcode: MyApp Dev, MyApp Staging, MyApp

# ios/Runner/Info.plist — bundle identifier por scheme
# Dev:        com.mycompany.myapp.dev
# Staging:    com.mycompany.myapp.staging
# Production: com.mycompany.myapp
```

---

## Comandos de Build y Deploy

```bash
# Correr con flavor específico
flutter run --flavor dev -t lib/main_dev.dart
flutter run --flavor staging -t lib/main_staging.dart
flutter run --flavor production -t lib/main_production.dart

# Build APK por flavor
flutter build apk --flavor production -t lib/main_production.dart --release
flutter build appbundle --flavor production -t lib/main_production.dart --release

# Build iOS
flutter build ipa --flavor production -t lib/main_production.dart

# Build con splits (APK más pequeño)
flutter build apk --split-per-abi --flavor production -t lib/main_production.dart

# Analizar tamaño del bundle
flutter build apk --analyze-size
flutter build ipa --analyze-size
```

---

## fastlane — Automatizar Deploy a Stores

```ruby
# android/fastlane/Fastfile
default_platform(:android)

platform :android do
  desc "Deploy to Play Store internal track"
  lane :deploy_internal do
    gradle(
      task: "bundle",
      flavor: "production",
      build_type: "Release",
      project_dir: "android/"
    )
    upload_to_play_store(
      track: "internal",
      aab: "android/app/build/outputs/bundle/productionRelease/app-production-release.aab",
      json_key: ENV['GOOGLE_PLAY_JSON_KEY'],
    )
  end

  desc "Promote from internal to production"
  lane :promote_to_production do
    upload_to_play_store(
      track: "internal",
      track_promote_to: "production",
      json_key: ENV['GOOGLE_PLAY_JSON_KEY'],
    )
  end
end

# ios/fastlane/Fastfile
platform :ios do
  desc "Deploy to TestFlight"
  lane :beta do
    build_ios_app(
      scheme: "production",
      export_method: "app-store",
    )
    upload_to_testflight(
      api_key_path: ENV['APP_STORE_CONNECT_API_KEY'],
    )
  end
end
```

---

## CI/CD con GitHub Actions

```yaml
# .github/workflows/flutter-ci.yml
name: Flutter CI

on:
  push:
    branches: [main, develop]
  pull_request:

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - uses: subosito/flutter-action@v2
        with:
          flutter-version: '3.24.0'
          channel: stable
          cache: true

      - name: Install dependencies
        run: flutter pub get

      - name: Generate code
        run: dart run build_runner build --delete-conflicting-outputs

      - name: Analyze
        run: flutter analyze

      - name: Run tests
        run: flutter test --coverage

      - name: Check coverage
        run: |
          COVERAGE=$(lcov --summary coverage/lcov.info 2>&1 | grep "lines" | awk '{print $2}' | tr -d '%')
          if (( $(echo "$COVERAGE < 80" | bc -l) )); then
            echo "Coverage ${COVERAGE}% is below 80%"
            exit 1
          fi

  build-android:
    runs-on: ubuntu-latest
    needs: test
    if: github.ref == 'refs/heads/main'
    steps:
      - uses: actions/checkout@v4
      - uses: subosito/flutter-action@v2
        with:
          flutter-version: '3.24.0'
          cache: true

      - name: Setup Java
        uses: actions/setup-java@v4
        with:
          distribution: temurin
          java-version: 17

      - name: Decode keystore
        run: |
          echo "${{ secrets.ANDROID_KEYSTORE_BASE64 }}" | base64 -d > android/app/keystore.jks

      - name: Build release bundle
        env:
          KEYSTORE_PASSWORD: ${{ secrets.KEYSTORE_PASSWORD }}
          KEY_ALIAS: ${{ secrets.KEY_ALIAS }}
          KEY_PASSWORD: ${{ secrets.KEY_PASSWORD }}
        run: |
          flutter pub get
          flutter build appbundle \
            --flavor production \
            -t lib/main_production.dart \
            --release

      - name: Upload to Play Store
        uses: r0adkll/upload-google-play@v1
        with:
          serviceAccountJsonPlainText: ${{ secrets.GOOGLE_PLAY_SERVICE_ACCOUNT }}
          packageName: com.mycompany.myapp
          releaseFiles: build/app/outputs/bundle/productionRelease/*.aab
          track: internal

  build-ios:
    runs-on: macos-latest
    needs: test
    if: github.ref == 'refs/heads/main'
    steps:
      - uses: actions/checkout@v4
      - uses: subosito/flutter-action@v2
        with:
          flutter-version: '3.24.0'
          cache: true

      - name: Install certificates
        uses: apple-actions/import-codesign-certs@v2
        with:
          p12-file-base64: ${{ secrets.IOS_DIST_SIGNING_CERT }}
          p12-password: ${{ secrets.IOS_DIST_SIGNING_CERT_PASSWORD }}

      - name: Build iOS
        run: |
          flutter pub get
          flutter build ipa \
            --flavor production \
            -t lib/main_production.dart

      - name: Upload to TestFlight
        uses: apple-actions/upload-testflight-build@v1
        with:
          app-path: build/ios/ipa/*.ipa
          issuer-id: ${{ secrets.APPSTORE_ISSUER_ID }}
          api-key-id: ${{ secrets.APPSTORE_KEY_ID }}
          api-private-key: ${{ secrets.APPSTORE_PRIVATE_KEY }}
```

---

## Push Notifications — Firebase Messaging (resumen)

```bash
# Setup con FlutterFire CLI
dart pub global activate flutterfire_cli
flutterfire configure   # genera firebase_options.dart por flavor/proyecto
flutter pub add firebase_core firebase_messaging flutter_local_notifications
```

```dart
// main.dart — inicializar y pedir permisos
await Firebase.initializeApp(options: DefaultFirebaseOptions.currentPlatform);

final messaging = FirebaseMessaging.instance;
await messaging.requestPermission(alert: true, badge: true, sound: true);

// Token del dispositivo — enviarlo al backend asociado al usuario
final token = await messaging.getToken();
messaging.onTokenRefresh.listen(sendTokenToBackend);

// Foreground: FCM no muestra notificación — usar flutter_local_notifications
FirebaseMessaging.onMessage.listen((message) {
  showLocalNotification(message.notification?.title, message.notification?.body);
});

// Tap en notificación (app en background) → deep link con GoRouter
FirebaseMessaging.onMessageOpenedApp.listen((message) {
  final route = message.data['route'];           // ej. '/orders/123'
  if (route != null) router.go(route);
});

// App cerrada (terminated) — mensaje que abrió la app
final initial = await messaging.getInitialMessage();
if (initial?.data['route'] != null) router.go(initial!.data['route']);

// Handler de background — función top-level obligatoria
@pragma('vm:entry-point')
Future<void> _onBackgroundMessage(RemoteMessage message) async {
  // procesar data message (no UI aquí)
}
// En main(): FirebaseMessaging.onBackgroundMessage(_onBackgroundMessage);
```

Checklist por plataforma:
- **iOS**: capability "Push Notifications" + "Background Modes → Remote notifications" en Xcode; subir la APNs key (.p8) a Firebase Console.
- **Android 13+**: permiso `POST_NOTIFICATIONS` se pide en runtime (lo gestiona `requestPermission`); icono de notificación en `android/app/src/main/res/`.
- Probar en dispositivo físico — los simuladores iOS no reciben push remotos (los recientes con cuenta de desarrollador sí, pero valida en físico).

---

## pubspec.yaml — Dependencias de Referencia

```yaml
name: myapp
description: MyApp Flutter

environment:
  sdk: ">=3.4.0 <4.0.0"

dependencies:
  flutter:
    sdk: flutter

  # Estado
  flutter_riverpod: ^2.5.1
  riverpod_annotation: ^2.3.5
  hooks_riverpod: ^2.5.1
  flutter_hooks: ^0.20.5

  # Navegación
  go_router: ^14.2.0

  # Red
  dio: ^5.7.0
  pretty_dio_logger: ^1.4.0

  # Storage
  flutter_secure_storage: ^9.2.2
  shared_preferences: ^2.3.1

  # Serialización
  freezed_annotation: ^2.4.4
  json_annotation: ^4.9.0

  # Imágenes
  cached_network_image: ^3.4.1

  # UI
  shimmer: ^3.0.0
  gap: ^3.0.1               # espaciado semántico
  flutter_svg: ^2.0.10

  # Notificaciones
  firebase_messaging: ^15.1.0
  flutter_local_notifications: ^17.2.2

  # Utils
  intl: ^0.19.0
  logger: ^2.4.0
  package_info_plus: ^8.1.0

dev_dependencies:
  flutter_test:
    sdk: flutter
  flutter_lints: ^4.0.0
  build_runner: ^2.4.12
  riverpod_generator: ^2.4.3
  freezed: ^2.5.7
  json_serializable: ^6.8.0
  mocktail: ^1.0.4
```
