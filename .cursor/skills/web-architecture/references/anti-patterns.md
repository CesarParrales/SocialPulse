# Anti-Patrones — Qué Evitar y Por Qué

Cada anti-patrón tiene nombre, síntoma, impacto real, y cómo salir de él.

---

## AP-01: Fat Model / God Object

**Síntoma:**
```php
// User.php — 1,800 líneas
class User extends Model {
    public function sendWelcomeEmail() { ... }
    public function calculateDiscount() { ... }
    public function generateReport() { ... }
    public function processPayment() { ... }
    public function syncWithCRM() { ... }
    // ... 40 métodos más
}
```

**Por qué ocurre:** MVC puro sin Service Layer. "El modelo hace todo."
**Impacto:** Imposible testear en aislamiento. Cambios en pagos rompen reportes.
Merge conflicts constantes. Nadie entiende la clase completa.

**Solución:**
```php
// Distribuir responsabilidades
UserService         → lógica de negocio de usuario
UserNotificationService → emails y notificaciones
UserReportService   → generación de reportes
PaymentService      → pagos (ni siquiera es de User)
User (Model)        → solo estructura, relaciones, castings
```

---

## AP-02: Fat Controller

**Síntoma:**
```php
class OrderController {
    public function store(Request $request) {
        // 150 líneas de validación, lógica de negocio,
        // queries directas, envío de emails, y response
    }
}
```

**Por qué ocurre:** "Rápido" en el momento. Técnicamente funciona.
**Impacto:** No testeable. No reutilizable. El controller sabe demasiado.

**Solución:** Controller de máximo 20-30 líneas. Todo va en Service.
```php
class OrderController {
    public function store(CreateOrderRequest $request): JsonResponse {
        $order = $this->orderService->create($request->validated());
        return OrderResource::make($order)->response()->setStatusCode(201);
    }
}
```

---

## AP-03: Anemic Domain Model

**Síntoma:** Entidades son solo bolsas de datos. Toda la lógica está en Services.
```php
// Order — solo getters/setters, sin comportamiento
class Order {
    public int $id;
    public string $status;
    public float $total;
}

// Toda la lógica en el service — sabe demasiado sobre Order
class OrderService {
    public function cancel(Order $order): void {
        if ($order->status === 'shipped') {
            throw new Exception('Cannot cancel shipped order');
        }
        $order->status = 'cancelled';
        $order->cancelledAt = now();
        $this->orders->save($order);
    }
}
```

**Por qué es problema:** la regla "no cancelar si está enviado" vive en el Service.
Si alguien cancela desde otro Service, la regla no se aplica.

**Solución:** lógica que protege invariantes va en la entidad.
```php
class Order {
    public function cancel(): void {
        if ($this->status === OrderStatus::Shipped) {
            throw new CannotCancelShippedOrderException($this->id);
        }
        $this->status = OrderStatus::Cancelled;
        $this->cancelledAt = new DateTimeImmutable();
        $this->recordEvent(new OrderCancelled($this->id));
    }
}
```

---

## AP-04: Leaky Abstractions

**Síntoma:** El dominio importa código de infraestructura.
```php
// MALO — dominio depende de Eloquent (infraestructura)
use Illuminate\Database\Eloquent\Model;

class Order extends Model {  // Order es un Eloquent Model
    // La lógica de negocio está acoplada al ORM
}
```

**Impacto:** No puedes testear Order sin levantar la BD.
Cambiar de Eloquent a Doctrine requiere reescribir el dominio.

**Solución:** Separar entidad de dominio del modelo Eloquent.
```php
// Entidad de dominio — POPO (Plain Old PHP Object)
class Order {
    public function __construct(
        public readonly OrderId $id,
        public OrderStatus $status,
    ) {}
    // lógica de negocio aquí
}

// Modelo Eloquent — solo en infraestructura
class OrderModel extends Model {
    protected $table = 'orders';

    public function toDomain(): Order {
        return new Order(
            id: new OrderId($this->id),
            status: OrderStatus::from($this->status),
        );
    }
}
```

---

## AP-05: Service Locator (anti-patrón de DI)

**Síntoma:**
```php
class OrderService {
    public function create(array $data): Order {
        // Resolver dependencias desde el container dentro del método
        $repo = app(OrderRepositoryInterface::class);
        $mailer = app(Mailer::class);
        // ...
    }
}
```

**Impacto:** Dependencias ocultas. Imposible saber qué necesita el Service
sin leer toda su implementación. Tests requieren mockear el container.

**Solución:** Inyección de dependencias explícita en constructor.
```php
class OrderService {
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly Mailer $mailer,
    ) {}
    // Las dependencias son visibles y testeables
}
```

---

## AP-06: N+1 Queries

**Síntoma:**
```php
$orders = Order::all();  // 1 query

foreach ($orders as $order) {
    echo $order->user->name;  // 1 query por cada order = N queries
    foreach ($order->items as $item) {
        echo $item->product->name;  // N*M queries
    }
}
// 100 órdenes = 1 + 100 + 500 queries. La BD en llamas.
```

**Impacto:** Degradación exponencial de performance con volumen de datos.
Invisible en dev con pocos datos. Catastrófico en producción.

**Solución:** Eager loading explícito.
```php
$orders = Order::with(['user', 'items.product'])->get();
// 3 queries siempre, sin importar el volumen

// Detectar N+1 en desarrollo
// Laravel Telescope o Debugbar muestran queries duplicadas
// En tests: assertQueryCount(3) con spatie/laravel-query-count
```

---

## AP-07: Magic Numbers y Strings

**Síntoma:**
```php
if ($order->status === 'pending') { ... }
if ($user->role === 3) { ... }
$tax = $subtotal * 0.18;
```

**Impacto:** ¿Qué es el rol 3? ¿El 0.18 es el mismo en todos lados?
Un cambio requiere buscar en todo el codebase.

**Solución:** Enums y constantes tipadas.
```php
enum OrderStatus: string {
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}

enum UserRole: int {
    case Admin = 1;
    case Manager = 2;
    case Customer = 3;
}

class TaxConfig {
    const VAT_RATE = 0.18;  // o mejor: en config/tax.php
}

// Uso
if ($order->status === OrderStatus::Pending) { ... }
```

---

## AP-08: Exceptions como Control de Flujo

**Síntoma:**
```php
try {
    $user = $this->users->findByEmail($email);
    return $user;
} catch (UserNotFoundException $e) {
    return null;  // La excepción se usa como "not found"
}
```

**Impacto:** Exceptions son costosas. Ocultan el flujo lógico.
Stack traces innecesarios en logs.

**Solución:** Distinguir errores excepcionales de resultados esperados.
```php
// Para "puede o no existir" — return nullable
public function findByEmail(string $email): ?User {
    return UserModel::where('email', $email)->first()?->toDomain();
}

// Para "debe existir, si no hay un bug" — exception apropiada
public function findByIdOrFail(int $id): User {
    return UserModel::findOrFail($id)->toDomain();
    // Lanza ModelNotFoundException si no existe
}
```

---

## AP-09: Shared Mutable State

**Síntoma en frontend (React):**
```javascript
// Estado global mutable compartido entre componentes no relacionados
let globalUser = null;
let globalCart = [];

// Cualquier componente puede mutar estos sin control
function Header() {
    globalUser = fetchUser();  // sin notificar a nadie
}
```

**Síntoma en backend:**
```php
class OrderService {
    private static array $processedOrders = [];  // static mutable
    // En concurrencia: dos requests modifican este estado simultáneamente
}
```

**Solución:** Estado inmutable o con mutación controlada.
```javascript
// React: estado centralizado con acciones explícitas
const useOrderStore = create((set) => ({
    orders: [],
    addOrder: (order) => set((state) => ({
        orders: [...state.orders, order]  // inmutable update
    })),
}));
```

---

## AP-10: Premature Optimization

**Síntoma:**
- Cache en todo desde el día 1 sin medir
- Microservicios para un proyecto con 10 usuarios
- Índices en cada columna "por si acaso"
- Redis para datos que caben en una variable

**Impacto:** Complejidad innecesaria. Tiempo perdido optimizando lo que no es cuello de botella.
"Premature optimization is the root of all evil" — Knuth.

**Solución:**
1. Hacer funcionar correctamente
2. Medir con datos reales (profiler, slow query log, APM)
3. Optimizar solo lo que los datos indican como cuello de botella
4. Volver a medir

---

## AP-11: Cargo Cult Architecture

**Síntoma:**
> "Netflix usa microservicios, nosotros también deberíamos"
> "Airbnb usa Event Sourcing, lo implementamos"
> "Vi en un blog que Hexagonal es mejor"

**Impacto:** Arquitectura inadecuada para el contexto real.
Complejidad que el equipo no puede mantener. Frustración. Abandono.

**Regla:** Adoptar una arquitectura porque resuelve un problema que TÚ TIENES,
no porque alguien más con problemas diferentes la usa.

Netflix tiene 1,000+ ingenieros gestionando microservicios.
Tu startup de 5 devs no tiene ese problema todavía.
