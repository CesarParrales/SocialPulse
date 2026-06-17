# Arquitectura — Clean Architecture Pragmática para Flutter

## Principio

Clean architecture SIN dogma: tres capas por feature, dependencias apuntando hacia adentro, y solo las abstracciones que pagan su costo. Para apps pequeñas/medianas NO crees interfaces + implementaciones duplicadas por capa: un repository concreto inyectado con Riverpod es suficiente y testeable (se overridea en tests).

```
presentation  →  domain  ←  data
(screens,        (modelos,    (repositories,
 providers)       lógica)      datasources, DTOs)

Regla: presentation nunca importa de data directamente.
       domain no importa NI de presentation NI de data.
```

---

## Estructura Feature-First Detallada

```
lib/
├── main.dart                     # Entry point (por flavor: main_dev.dart, etc.)
├── app.dart                      # MaterialApp.router + tema
│
├── core/                         # Transversal — NO lógica de negocio
│   ├── config/
│   │   ├── app_config.dart       # Variables por flavor
│   │   └── router.dart           # GoRouter config
│   ├── network/
│   │   ├── dio_client.dart       # Dio + interceptors (auth, logging)
│   │   └── api_exception.dart    # Errores tipados de red
│   ├── storage/
│   │   └── secure_storage.dart
│   └── utils/
│       ├── extensions.dart
│       └── validators.dart
│
├── features/
│   └── orders/                   # TODO lo del dominio orders vive aquí
│       ├── data/
│       │   ├── orders_repository.dart      # Orquesta datasources, expone domain models
│       │   └── orders_remote_datasource.dart # Habla con la API (Dio), devuelve DTOs/JSON
│       ├── domain/
│       │   ├── models/
│       │   │   └── order.dart              # Freezed — inmutable, fromJson
│       │   └── orders_failure.dart         # Errores tipados del dominio
│       ├── presentation/
│       │   ├── providers/
│       │   │   └── orders_provider.dart    # Riverpod — estado de la UI
│       │   ├── screens/
│       │   │   ├── orders_screen.dart
│       │   │   └── order_detail_screen.dart
│       │   └── widgets/
│       │       └── order_card.dart         # Widgets SOLO de este feature
│       └── orders.dart                     # Barrel export
│
└── shared/                       # Widgets/tema reutilizables entre features
    ├── widgets/
    └── theme/
```

Criterios:
- Un widget usado por 2+ features → `shared/widgets/`. Usado por uno → dentro del feature.
- `core/` es infraestructura (red, storage, config); `shared/` es UI.
- Barrel export (`orders.dart`) para que otros features importen `package:myapp/features/orders/orders.dart` y no rutas internas.

---

## Capa Data — Datasource y Repository

```dart
// features/orders/data/orders_remote_datasource.dart
class OrdersRemoteDatasource {
  OrdersRemoteDatasource(this._dio);
  final Dio _dio;

  Future<List<Map<String, dynamic>>> fetchOrders({int page = 1}) async {
    final response = await _dio.get('/orders', queryParameters: {'page': page});
    return List<Map<String, dynamic>>.from(response.data['data']);
  }

  Future<Map<String, dynamic>> fetchOrder(String id) async {
    final response = await _dio.get('/orders/$id');
    return response.data['data'] as Map<String, dynamic>;
  }
}

// features/orders/data/orders_repository.dart
// El repository traduce: JSON/DioException → domain models/Failures tipados
class OrdersRepository {
  OrdersRepository(this._remote);
  final OrdersRemoteDatasource _remote;

  Future<List<Order>> getOrders({int page = 1}) async {
    try {
      final json = await _remote.fetchOrders(page: page);
      return json.map(Order.fromJson).toList();
    } on DioException catch (e) {
      throw e.toOrdersFailure();  // nunca dejar escapar DioException a la UI
    }
  }
}
```

---

## Manejo de Errores Tipado

```dart
// features/orders/domain/orders_failure.dart
// sealed class → el switch en la UI es EXHAUSTIVO (el compilador obliga a cubrir casos)
sealed class OrdersFailure implements Exception {
  const OrdersFailure();
}

class OrdersNetworkFailure extends OrdersFailure {
  const OrdersNetworkFailure();
}

class OrdersNotFoundFailure extends OrdersFailure {
  const OrdersNotFoundFailure(this.orderId);
  final String orderId;
}

class OrdersUnauthorizedFailure extends OrdersFailure {
  const OrdersUnauthorizedFailure();
}

class OrdersUnknownFailure extends OrdersFailure {
  const OrdersUnknownFailure([this.message]);
  final String? message;
}

// Traducción desde Dio — extension en la capa data
extension on DioException {
  OrdersFailure toOrdersFailure() => switch (response?.statusCode) {
        401 || 403 => const OrdersUnauthorizedFailure(),
        404        => OrdersNotFoundFailure(requestOptions.path),
        _ when type == DioExceptionType.connectionTimeout ||
               type == DioExceptionType.connectionError =>
          const OrdersNetworkFailure(),
        _ => OrdersUnknownFailure(message),
      };
}

// En la UI — mensaje por caso, exhaustivo
String failureMessage(OrdersFailure f) => switch (f) {
      OrdersNetworkFailure()      => 'Sin conexión. Revisa tu red.',
      OrdersNotFoundFailure()     => 'Pedido no encontrado.',
      OrdersUnauthorizedFailure() => 'Sesión expirada. Vuelve a entrar.',
      OrdersUnknownFailure()      => 'Algo salió mal. Inténtalo de nuevo.',
    };
```

Alternativa funcional: devolver `Result<T, Failure>` (p. ej. con `fpdart` o un sealed `Result` propio) en vez de lanzar. Elige UNA convención por proyecto; con Riverpod, lanzar y dejar que `AsyncValue.error` capture es lo más simple.

---

## Inyección de Dependencias con Riverpod

Riverpod ES el contenedor de DI — no añadas get_it/injectable salvo necesidad real.

```dart
// core/network/dio_client.dart
@riverpod
Dio dio(Ref ref) {
  final dio = Dio(BaseOptions(
    baseUrl: AppConfig.apiUrl,
    connectTimeout: const Duration(seconds: 10),
  ));
  dio.interceptors.add(AuthInterceptor(ref));
  return dio;
}

// features/orders/data — cadena de providers
@riverpod
OrdersRemoteDatasource ordersRemoteDatasource(Ref ref) =>
    OrdersRemoteDatasource(ref.watch(dioProvider));

@riverpod
OrdersRepository ordersRepository(Ref ref) =>
    OrdersRepository(ref.watch(ordersRemoteDatasourceProvider));

// features/orders/presentation/providers/orders_provider.dart
@riverpod
Future<List<Order>> orders(Ref ref) =>
    ref.watch(ordersRepositoryProvider).getOrders();
```

```dart
// En tests — override del repository, sin mocks de red
testWidgets('muestra pedidos', (tester) async {
  await tester.pumpWidget(
    ProviderScope(
      overrides: [
        ordersRepositoryProvider.overrideWithValue(FakeOrdersRepository()),
      ],
      child: const MyApp(),
    ),
  );
  // ...
});
```

---

## Capa Presentation — Consumo del Estado

```dart
// features/orders/presentation/screens/orders_screen.dart
class OrdersScreen extends ConsumerWidget {
  const OrdersScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final ordersAsync = ref.watch(ordersProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Pedidos')),
      body: ordersAsync.when(
        loading: () => const OrdersListSkeleton(),
        error: (error, _) => ErrorView(
          message: error is OrdersFailure
              ? failureMessage(error)
              : 'Error inesperado',
          onRetry: () => ref.invalidate(ordersProvider),
        ),
        data: (orders) => orders.isEmpty
            ? const EmptyOrdersView()
            : ListView.builder(
                itemCount: orders.length,
                itemBuilder: (_, i) => OrderCard(order: orders[i]),
              ),
      ),
    );
  }
}
```

Reglas de la capa presentation:
- Screens son `ConsumerWidget`/`HookConsumerWidget` delgados: sin lógica de negocio, sin Dio, sin parseo de JSON.
- Estados siempre cubiertos: loading, error (con retry), empty, data.
- Mutations via Notifier (ver `riverpod.md`), nunca llamando al repository directo desde un onPressed.

---

## Flujo para un Feature Nuevo

```
1. mkdir -p lib/features/X/{data,domain/models,presentation/{providers,screens,widgets}}
2. Modelo Freezed en domain/models/ + failure tipado
3. Datasource + repository en data/, providers de DI
4. Provider de estado en presentation/providers/
5. Screen + widgets, registrar ruta en core/config/router.dart
6. dart run build_runner build --delete-conflicting-outputs  → sin conflictos
7. flutter test  → verde (tests con override de repository)
```
