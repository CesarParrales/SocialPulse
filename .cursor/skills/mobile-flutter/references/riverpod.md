# Riverpod — Estado y Async

## Por Qué Riverpod sobre BLoC o Provider

```
Riverpod:
  ✅ Type-safe — errores en compilación, no runtime
  ✅ Testeable sin BuildContext
  ✅ Async nativo (AsyncValue)
  ✅ Sin boilerplate de BLoC
  ✅ Generador de código (riverpod_generator)
  ✅ DevTools integrados

BLoC: más verboso, mejor para equipos con experiencia previa
Provider: deprecated en favor de Riverpod
setState: solo para estado UI local simple (animaciones, toggles)
```

---

## Providers Fundamentales

```dart
// Con generador de código (recomendado)

// 1. Simple — valor sincrónico
@riverpod
String appVersion(AppVersionRef ref) => '1.0.0';

// 2. Async — fetch de API
@riverpod
Future<List<Order>> orders(OrdersRef ref) async {
  final repo = ref.watch(orderRepositoryProvider);
  return repo.getOrders();
}

// 3. Con parámetro (family)
@riverpod
Future<Order> order(OrderRef ref, String id) async {
  final repo = ref.watch(orderRepositoryProvider);
  return repo.getOrderById(id);
}

// 4. Stateful — para estado mutable
@riverpod
class AuthNotifier extends _$AuthNotifier {
  @override
  Future<User?> build() async {
    // Estado inicial — cargar usuario guardado
    return ref.read(authRepositoryProvider).getCurrentUser();
  }

  Future<void> login(String email, String password) async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(
      () => ref.read(authRepositoryProvider).login(email, password),
    );
  }

  Future<void> logout() async {
    await ref.read(authRepositoryProvider).logout();
    state = const AsyncData(null);
  }
}

// 5. KeepAlive — no destruir cuando no hay listeners
@Riverpod(keepAlive: true)
class CartNotifier extends _$CartNotifier {
  @override
  List<CartItem> build() => [];

  void addItem(Product product, int quantity) {
    final existing = state.indexWhere((i) => i.productId == product.id);
    if (existing >= 0) {
      state = [
        for (var i = 0; i < state.length; i++)
          if (i == existing)
            state[i].copyWith(quantity: state[i].quantity + quantity)
          else
            state[i],
      ];
    } else {
      state = [...state, CartItem(productId: product.id, quantity: quantity, product: product)];
    }
  }

  void removeItem(String productId) {
    state = state.where((i) => i.productId != productId).toList();
  }

  void clear() => state = [];

  int get totalItems => state.fold(0, (sum, i) => sum + i.quantity);
  int get totalCents => state.fold(0, (sum, i) => sum + i.product.priceCents * i.quantity);
}
```

---

## Repository Pattern

```dart
// features/orders/data/order_repository.dart

// Interface (abstract class en Dart)
abstract class OrderRepository {
  Future<List<Order>> getOrders({OrderStatus? status, int page = 1});
  Future<Order> getOrderById(String id);
  Future<Order> createOrder(CreateOrderInput input);
  Future<Order> cancelOrder(String id, {String? reason});
}

// Implementación
class OrderRepositoryImpl implements OrderRepository {
  OrderRepositoryImpl(this._client);
  final DioClient _client;

  @override
  Future<List<Order>> getOrders({OrderStatus? status, int page = 1}) async {
    final response = await _client.get(
      '/orders',
      queryParameters: {
        if (status != null) 'status': status.name,
        'page': page,
        'per_page': 20,
      },
    );
    return (response.data['data'] as List)
        .map((json) => Order.fromJson(json))
        .toList();
  }

  @override
  Future<Order> getOrderById(String id) async {
    final response = await _client.get('/orders/$id');
    return Order.fromJson(response.data['data']);
  }

  @override
  Future<Order> createOrder(CreateOrderInput input) async {
    final response = await _client.post('/orders', data: input.toJson());
    return Order.fromJson(response.data['data']);
  }

  @override
  Future<Order> cancelOrder(String id, {String? reason}) async {
    final response = await _client.post(
      '/orders/$id/cancel',
      data: {'reason': reason},
    );
    return Order.fromJson(response.data['data']);
  }
}

// Provider del repositorio
@riverpod
OrderRepository orderRepository(OrderRepositoryRef ref) {
  return OrderRepositoryImpl(ref.watch(dioClientProvider));
}
```

---

## AsyncValue — Manejo de Estado Async

```dart
// Consumir en widget
class OrdersScreen extends ConsumerWidget {
  const OrdersScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final ordersAsync = ref.watch(ordersProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('My Orders')),
      body: ordersAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => ErrorView(
          message: error.toString(),
          onRetry: () => ref.invalidate(ordersProvider),
        ),
        data: (orders) => orders.isEmpty
            ? const EmptyOrdersView()
            : OrdersList(orders: orders),
      ),
    );
  }
}

// Con refresh manual
class OrdersScreen extends ConsumerWidget {
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final ordersAsync = ref.watch(ordersProvider);

    return RefreshIndicator(
      onRefresh: () => ref.refresh(ordersProvider.future),
      child: ordersAsync.when(
        // Mostrar datos viejos mientras recarga (mejor UX)
        skipLoadingOnReload: true,
        loading: () => const OrdersSkeletonList(),
        error: (e, _) => ErrorView(message: e.toString()),
        data: (orders) => OrdersList(orders: orders),
      ),
    );
  }
}
```

---

## Dio Client con Interceptors

```dart
// core/network/dio_client.dart
@Riverpod(keepAlive: true)
DioClient dioClient(DioClientRef ref) {
  return DioClient(ref);
}

class DioClient {
  DioClient(this._ref) {
    _dio = Dio(BaseOptions(
      baseUrl: AppConfig.apiUrl,
      connectTimeout: const Duration(seconds: 10),
      receiveTimeout: const Duration(seconds: 30),
      headers: {'Content-Type': 'application/json'},
    ));

    _dio.interceptors.addAll([
      _AuthInterceptor(_ref),
      _LogInterceptor(),
      if (kDebugMode) PrettyDioLogger(),
    ]);
  }

  late final Dio _dio;
  final Ref _ref;

  Future<Response> get(String path, {Map<String, dynamic>? queryParameters}) =>
      _dio.get(path, queryParameters: queryParameters);

  Future<Response> post(String path, {dynamic data}) =>
      _dio.post(path, data: data);

  Future<Response> patch(String path, {dynamic data}) =>
      _dio.patch(path, data: data);

  Future<Response> delete(String path) => _dio.delete(path);
}

class _AuthInterceptor extends Interceptor {
  _AuthInterceptor(this._ref);
  final Ref _ref;

  @override
  Future<void> onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final storage = _ref.read(secureStorageProvider);
    final token = await storage.read(key: 'access_token');
    if (token != null) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }

  @override
  Future<void> onError(
    DioException err,
    ErrorInterceptorHandler handler,
  ) async {
    if (err.response?.statusCode == 401) {
      // Intentar refresh token
      try {
        final storage = _ref.read(secureStorageProvider);
        final refreshToken = await storage.read(key: 'refresh_token');
        if (refreshToken == null) {
          _ref.read(authNotifierProvider.notifier).logout();
          return handler.reject(err);
        }

        final dio = Dio();
        final response = await dio.post(
          '${AppConfig.apiUrl}/auth/refresh',
          data: {'refreshToken': refreshToken},
        );

        final newToken = response.data['accessToken'] as String;
        await storage.write(key: 'access_token', value: newToken);

        // Reintentar request original
        err.requestOptions.headers['Authorization'] = 'Bearer $newToken';
        final retry = await dio.fetch(err.requestOptions);
        return handler.resolve(retry);

      } catch (_) {
        _ref.read(authNotifierProvider.notifier).logout();
      }
    }
    handler.next(err);
  }
}
```

---

## Testing con Mocktail

```dart
// test/features/orders/orders_test.dart
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mocktail/mocktail.dart';

class MockOrderRepository extends Mock implements OrderRepository {}

void main() {
  late MockOrderRepository mockRepo;
  late ProviderContainer container;

  setUp(() {
    mockRepo = MockOrderRepository();
    container = ProviderContainer(
      overrides: [
        // Override del provider real con el mock
        orderRepositoryProvider.overrideWithValue(mockRepo),
      ],
    );
    addTearDown(container.dispose);
  });

  group('ordersProvider', () {
    test('returns orders list on success', () async {
      final mockOrders = [
        Order(
          id: '1',
          reference: 'ORD-001',
          status: OrderStatus.pending,
          totalCents: 5000,
          currency: 'USD',
          shippingAddress: '123 Main St',
          createdAt: DateTime.now(),
        ),
      ];

      when(() => mockRepo.getOrders()).thenAnswer((_) async => mockOrders);

      final orders = await container.read(ordersProvider.future);
      expect(orders, equals(mockOrders));
    });

    test('throws on network error', () async {
      when(() => mockRepo.getOrders())
          .thenThrow(DioException(requestOptions: RequestOptions()));

      expect(
        () => container.read(ordersProvider.future),
        throwsA(isA<DioException>()),
      );
    });
  });

  group('CartNotifier', () {
    test('adds item to cart', () {
      final product = Product(id: '1', name: 'Test', priceCents: 1000);

      container.read(cartNotifierProvider.notifier).addItem(product, 2);

      final cart = container.read(cartNotifierProvider);
      expect(cart.length, 1);
      expect(cart.first.quantity, 2);
      expect(container.read(cartNotifierProvider.notifier).totalCents, 2000);
    });

    test('increments quantity for existing item', () {
      final product = Product(id: '1', name: 'Test', priceCents: 1000);
      final notifier = container.read(cartNotifierProvider.notifier);

      notifier.addItem(product, 1);
      notifier.addItem(product, 2);

      final cart = container.read(cartNotifierProvider);
      expect(cart.first.quantity, 3);
    });
  });
}
```
