# Patrones Detallados — Implementaciones de Referencia

## MVC + Service Layer — Versión Mínima

El flujo básico Controller → Service → Repository, sin abstracciones extra:

```php
// Controller — delgado
class OrderController {
    public function store(CreateOrderRequest $request, OrderService $service) {
        $order = $service->createOrder($request->validated());
        return OrderResource::make($order);
    }
}

// Service — lógica de negocio
class OrderService {
    public function createOrder(array $data): Order {
        // validaciones de negocio, cálculos, eventos
        $order = $this->orderRepository->create($data);
        event(new OrderCreated($order));
        return $order;
    }
}

// Repository — acceso a datos
class OrderRepository {
    public function create(array $data): Order {
        return Order::create($data);
    }
}
```

---

## Hexagonal — Puerto y Adaptador Mínimos

```php
// Puerto (interfaz en dominio)
interface OrderRepositoryInterface {
    public function findById(OrderId $id): Order;
    public function save(Order $order): void;
}

// Adaptador (implementación en infraestructura)
class EloquentOrderRepository implements OrderRepositoryInterface {
    public function findById(OrderId $id): Order { /* ... */ }
    public function save(Order $order): void { /* ... */ }
}

// Use Case (en aplicación) — no sabe qué BD existe
class CreateOrderUseCase {
    public function __construct(
        private OrderRepositoryInterface $orders,  // inyectado
        private PaymentGatewayInterface $payments  // inyectado
    ) {}
}
```

**Beneficio real:** cambiar MySQL por PostgreSQL, Stripe por PayPal,
o agregar una CLI sin tocar una línea de lógica de negocio.

---

## Event-Driven — Listeners Desacoplados en Laravel

```php
// Evento de dominio
class OrderPlaced {
    public function __construct(public readonly Order $order) {}
}

// Listeners desacoplados
class SendOrderConfirmationEmail implements ShouldQueue {
    public function handle(OrderPlaced $event): void { /* ... */ }
}

class UpdateInventory implements ShouldQueue {
    public function handle(OrderPlaced $event): void { /* ... */ }
}

// Dispatching
event(new OrderPlaced($order));
// Los listeners corren en queue — el usuario no espera
```

---

## Repository Pattern

Abstrae el acceso a datos. El dominio habla con una interfaz, no con Eloquent/Doctrine/Mongoose.

### Implementación Laravel

```php
// 1. Interfaz en dominio o aplicación
interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): User;
    public function delete(int $id): void;
    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator;
}

// 2. Implementación Eloquent en infraestructura
class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return UserModel::find($id)?->toDomain();
    }

    public function findByEmail(string $email): ?User
    {
        return UserModel::where('email', $email)->first()?->toDomain();
    }

    public function save(User $user): User
    {
        $model = UserModel::updateOrCreate(
            ['id' => $user->id],
            $user->toArray()
        );
        return $model->toDomain();
    }

    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator
    {
        return UserModel::query()
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where('name', 'like', "%$s%"))
            ->when($filters['role'] ?? null, fn($q, $r) => $q->where('role', $r))
            ->paginate($perPage);
    }
}

// 3. Binding en Service Provider
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
    }
}

// 4. Uso en Service/Use Case — no sabe qué BD existe
class UserService
{
    public function __construct(
        private UserRepositoryInterface $users
    ) {}

    public function getUserProfile(int $id): UserProfileDTO
    {
        $user = $this->users->findById($id)
            ?? throw new UserNotFoundException($id);

        return UserProfileDTO::from($user);
    }
}
```

---

## Service Layer Pattern

### Estructura de un Service bien diseñado

```php
class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private ProductRepositoryInterface $products,
        private PaymentService $payments,
        private EventDispatcher $events,
    ) {}

    /**
     * Reglas de negocio:
     * - Stock debe ser suficiente
     * - Usuario no puede tener más de 5 órdenes pendientes
     * - Descuento aplicado si el total > $100
     */
    public function placeOrder(PlaceOrderDTO $dto): Order
    {
        // 1. Validaciones de negocio (no de formato — eso va en FormRequest)
        $this->ensureStockAvailable($dto->items);
        $this->ensureUserOrderLimit($dto->userId);

        // 2. Construir aggregate
        $order = Order::create(
            userId: $dto->userId,
            items: $this->resolveItems($dto->items),
            discount: $this->calculateDiscount($dto->items),
        );

        // 3. Persistir
        $order = $this->orders->save($order);

        // 4. Efectos secundarios via eventos (async, desacoplado)
        $this->events->dispatch(new OrderPlaced($order));

        return $order;
    }

    private function ensureStockAvailable(array $items): void
    {
        foreach ($items as $item) {
            $product = $this->products->findById($item['product_id']);
            if ($product->stock < $item['quantity']) {
                throw new InsufficientStockException($product->id, $item['quantity']);
            }
        }
    }

    private function ensureUserOrderLimit(int $userId): void
    {
        $pendingCount = $this->orders->countPendingByUser($userId);
        if ($pendingCount >= 5) {
            throw new OrderLimitExceededException($userId);
        }
    }
}
```

---

## Value Objects

Objetos inmutables definidos por sus atributos. Sin identidad propia.

```php
// Money — evita errores de precisión y mezcla de monedas
final class Money
{
    public function __construct(
        private readonly int $amount,      // en centavos, nunca float
        private readonly string $currency
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self((int) round($this->amount * $factor), $this->currency);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }

    public function format(): string
    {
        return number_format($this->amount / 100, 2) . ' ' . $this->currency;
    }

    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatchException($this->currency, $other->currency);
        }
    }
}

// Email — garantiza formato válido en construcción
final class Email
{
    private readonly string $value;

    public function __construct(string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException($value);
        }
        $this->value = strtolower($value);
    }

    public function value(): string { return $this->value; }
    public function domain(): string { return explode('@', $this->value)[1]; }
    public function equals(Email $other): bool { return $this->value === $other->value; }
}

// Uso — el tipo garantiza la validez, no hay que validar en cada método
class User
{
    public function __construct(
        public readonly UserId $id,
        public readonly Email $email,      // ya está validado
        public readonly Money $balance,    // ya tiene moneda correcta
    ) {}
}
```

---

## DTOs — Data Transfer Objects

Transportan datos entre capas sin lógica de negocio.

```php
// DTO de entrada (request → application)
final class CreateUserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $role = 'user',
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password'),
            role: $request->input('role', 'user'),
        );
    }
}

// DTO de salida (application → presentation)
final class UserProfileDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $role,
        public readonly string $memberSince,
    ) {}

    public static function from(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email->value(),
            role: $user->role->value,
            memberSince: $user->createdAt->format('Y-m-d'),
        );
    }
}
```

---

## CQRS Práctico (sin Event Sourcing)

Separar modelos de lectura y escritura sin la complejidad de Event Sourcing.

```php
// COMMAND — escritura con validación de negocio
class PlaceOrderCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly array $items,
        public readonly string $shippingAddress,
    ) {}
}

class PlaceOrderHandler
{
    public function handle(PlaceOrderCommand $command): OrderId
    {
        // lógica de negocio completa, validaciones, eventos
        $order = $this->orderService->placeOrder($command);
        return $order->id;
    }
}

// QUERY — lectura optimizada, sin pasar por dominio
class GetUserOrdersQuery
{
    public function __construct(
        public readonly int $userId,
        public readonly int $page = 1,
        public readonly string $status = 'all',
    ) {}
}

class GetUserOrdersHandler
{
    public function handle(GetUserOrdersQuery $query): array
    {
        // Query directo a BD, sin Eloquent models, sin lógica de negocio
        // Solo proyecciones optimizadas para la vista
        return DB::select("
            SELECT o.id, o.total, o.status, o.created_at,
                   COUNT(oi.id) as item_count
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            WHERE o.user_id = ?
              AND (? = 'all' OR o.status = ?)
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT 20 OFFSET ?
        ", [$query->userId, $query->status, $query->status, ($query->page - 1) * 20]);
    }
}

// Command Bus / Query Bus (con laravel-command-bus o implementación propia)
class OrderController
{
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $orderId = $this->commandBus->dispatch(
            new PlaceOrderCommand(...$request->validated())
        );
        return response()->json(['id' => $orderId], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $this->queryBus->ask(
            new GetUserOrdersQuery(
                userId: $request->user()->id,
                page: $request->integer('page', 1),
                status: $request->string('status', 'all'),
            )
        );
        return response()->json($orders);
    }
}
```

---

## Estructura de Directorios por Patrón

### MVC + Service Layer (Laravel)
```
app/
├── Http/
│   ├── Controllers/          # Delgados. Solo request/response.
│   ├── Requests/             # Validación de formato
│   └── Resources/            # Transformación de output
├── Services/                 # Lógica de negocio
├── Repositories/             # Acceso a datos
│   └── Interfaces/
├── Models/                   # Eloquent models (solo estructura)
├── DTOs/                     # Objetos de transferencia
└── Events/ + Listeners/      # Efectos secundarios async
```

### Hexagonal / DDD Modular (Laravel)
```
app/
├── Modules/
│   ├── Orders/
│   │   ├── Application/
│   │   │   ├── UseCases/     # PlaceOrder, CancelOrder
│   │   │   ├── Commands/     # PlaceOrderCommand
│   │   │   ├── Queries/      # GetOrdersQuery
│   │   │   └── DTOs/
│   │   ├── Domain/
│   │   │   ├── Entities/     # Order, OrderItem
│   │   │   ├── ValueObjects/ # OrderStatus, Money
│   │   │   ├── Events/       # OrderPlaced, OrderCancelled
│   │   │   ├── Exceptions/   # InsufficientStockException
│   │   │   └── Repositories/ # Interfaces solamente
│   │   └── Infrastructure/
│   │       ├── Persistence/  # EloquentOrderRepository
│   │       ├── Http/         # Controllers, Requests, Resources
│   │       └── Providers/    # Bindings, Service Providers
│   │
│   ├── Users/                # Mismo patrón
│   └── Billing/              # Mismo patrón
│
└── Shared/
    ├── Domain/               # ValueObjects compartidos (Money, Email)
    └── Infrastructure/       # Utils compartidos
```

### React / Next.js Frontend
```
src/
├── app/                      # Routes (Next.js App Router)
├── features/                 # Módulos por dominio
│   ├── orders/
│   │   ├── components/       # UI específica de orders
│   │   ├── hooks/            # useOrders, useOrderDetail
│   │   ├── services/         # API calls
│   │   ├── store/            # Estado de orders
│   │   └── types/            # TypeScript types
│   └── users/
├── shared/
│   ├── components/           # UI reutilizable (Button, Modal, Table)
│   ├── hooks/                # Hooks genéricos
│   └── utils/                # Helpers
└── lib/
    ├── api/                  # API client base
    └── auth/                 # Auth helpers
```
