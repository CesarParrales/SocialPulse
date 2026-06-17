# GoRouter — Navegación Declarativa

## Setup Completo

```dart
// core/config/router.dart
@riverpod
GoRouter router(RouterRef ref) {
  // Escuchar cambios de auth para redirigir
  final authState = ref.watch(authNotifierProvider);

  return GoRouter(
    initialLocation: '/splash',
    debugLogDiagnostics: kDebugMode,

    // Redirect global — corre en cada navegación
    redirect: (context, state) {
      final isAuthenticated = authState.valueOrNull != null;
      final isLoading = authState.isLoading;
      final isOnAuth = state.matchedLocation.startsWith('/auth');
      final isOnSplash = state.matchedLocation == '/splash';

      if (isLoading || isOnSplash) return null;  // no redirigir durante carga

      if (!isAuthenticated && !isOnAuth) return '/auth/login';
      if (isAuthenticated && isOnAuth) return '/home';

      return null;  // sin redirección
    },

    routes: [
      GoRoute(
        path: '/splash',
        builder: (context, state) => const SplashScreen(),
      ),

      // Shell route — bottom navigation persistente
      StatefulShellRoute.indexedStack(
        builder: (context, state, navigationShell) =>
            MainScaffold(navigationShell: navigationShell),
        branches: [
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: '/home',
                builder: (context, state) => const HomeScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: '/orders',
                builder: (context, state) => const OrdersScreen(),
                routes: [
                  GoRoute(
                    path: ':id',
                    builder: (context, state) => OrderDetailScreen(
                      orderId: state.pathParameters['id']!,
                    ),
                    routes: [
                      GoRoute(
                        path: 'edit',
                        builder: (context, state) => EditOrderScreen(
                          orderId: state.pathParameters['id']!,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: '/profile',
                builder: (context, state) => const ProfileScreen(),
              ),
            ],
          ),
        ],
      ),

      // Auth routes — fuera del shell
      GoRoute(
        path: '/auth',
        redirect: (_, __) => '/auth/login',
        routes: [
          GoRoute(
            path: 'login',
            builder: (context, state) {
              final from = state.uri.queryParameters['from'];
              return LoginScreen(redirectTo: from);
            },
          ),
          GoRoute(
            path: 'register',
            builder: (context, state) => const RegisterScreen(),
          ),
          GoRoute(
            path: 'forgot-password',
            builder: (context, state) => const ForgotPasswordScreen(),
          ),
        ],
      ),

      // Modal routes
      GoRoute(
        path: '/image-viewer',
        pageBuilder: (context, state) => CustomTransitionPage(
          child: ImageViewerScreen(
            imageUrl: state.extra as String,
          ),
          transitionsBuilder: (context, animation, _, child) =>
              FadeTransition(opacity: animation, child: child),
        ),
      ),
    ],

    // Error page personalizada
    errorBuilder: (context, state) => ErrorScreen(error: state.error),
  );
}
```

---

## Navegación en Código

```dart
// Navegación básica
context.go('/orders');                    // reemplazar stack
context.push('/orders/123');             // agregar al stack
context.pop();                           // volver
context.pushReplacement('/home');        // reemplazar current
context.goNamed('order-detail', pathParameters: {'id': orderId});

// Con parámetros extra (no en URL)
context.push('/image-viewer', extra: product.imageUrl);

// Con query parameters
context.go('/orders?status=pending');

// Desde fuera del context (en providers)
ref.read(routerProvider).go('/login?from=/orders/123');

// Named routes para evitar strings duplicados
GoRoute(
  path: '/orders/:id',
  name: 'order-detail',  // nombre único
  builder: (context, state) => OrderDetailScreen(
    orderId: state.pathParameters['id']!,
  ),
),
// Uso: context.goNamed('order-detail', pathParameters: {'id': id})
```

---

## Bottom Navigation con StatefulShellRoute

```dart
// shared/widgets/main_scaffold.dart
class MainScaffold extends StatelessWidget {
  const MainScaffold({
    super.key,
    required this.navigationShell,
  });

  final StatefulNavigationShell navigationShell;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: navigationShell,
      bottomNavigationBar: NavigationBar(
        selectedIndex: navigationShell.currentIndex,
        onDestinationSelected: (index) => navigationShell.goBranch(
          index,
          // Si ya está en el tab, ir al root de ese tab
          initialLocation: index == navigationShell.currentIndex,
        ),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home),
            label: 'Home',
          ),
          NavigationDestination(
            icon: Icon(Icons.receipt_outlined),
            selectedIcon: Icon(Icons.receipt),
            label: 'Orders',
          ),
          NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person),
            label: 'Profile',
          ),
        ],
      ),
    );
  }
}
```

---

## Deep Links

```yaml
# android/app/src/main/AndroidManifest.xml
<activity ...>
  <intent-filter android:autoVerify="true">
    <action android:name="android.intent.action.VIEW" />
    <category android:name="android.intent.category.DEFAULT" />
    <category android:name="android.intent.category.BROWSABLE" />
    <data
      android:scheme="https"
      android:host="myapp.com" />
  </intent-filter>
</activity>
```

```dart
// ios/Runner/Info.plist
// <key>FlutterDeepLinkingEnabled</key><true/>
// <key>CFBundleURLTypes</key>...

// GoRouter maneja deep links automáticamente
// https://myapp.com/orders/123 → GoRoute path: '/orders/:id'

// Manejar link cuando la app está en background
GoRouter(
  // ...
  observers: [GoRouterObserver()],  // para analytics
);
```
