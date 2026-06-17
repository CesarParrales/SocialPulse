# Arquitectura por Tipo de Proyecto

Configuraciones probadas para los tipos de proyecto más comunes.
Cada una incluye estructura, decisiones clave, y señales de cuándo escalar.

---

## SaaS B2B — Multi-tenant

### Estrategia de Multi-tenancy

Tres modelos posibles. Elegir según volumen y aislamiento requerido:

```
Modelo A — BD compartida, tenant_id en cada tabla
  Ventaja: simple, barato, fácil de mantener
  Desventaja: un bug puede exponer datos entre tenants
  Cuándo: hasta ~1,000 tenants, datos no críticos, presupuesto limitado

Modelo B — Schema por tenant (PostgreSQL)
  Ventaja: aislamiento de datos, fácil backup por tenant
  Desventaja: migraciones complejas (correr en N schemas)
  Cuándo: datos sensibles, compliance, tenants que exigen aislamiento

Modelo C — BD por tenant
  Ventaja: aislamiento máximo, escala independiente
  Desventaja: costoso, operacionalmente complejo
  Cuándo: enterprise, datos muy sensibles, tenants grandes que pagan por aislamiento
```

### Implementación con stancl/tenancy (Laravel)

```php
// config/tenancy.php
'tenant_model' => Tenant::class,
'id_generator' => UuidGenerator::class,
'database' => [
    'based_on_username' => false,
    'template_tenant_connection' => null,
    'prefix' => 'tenant_',
    'suffix' => '',
],

// Tenant model
class Tenant extends \Stancl\Tenancy\Database\Models\Tenant
{
    use HasDatabase, HasDomains;

    protected $casts = [
        'data' => 'array',
    ];

    // Features del tenant (billing, limits)
    public function getFeature(string $feature): mixed
    {
        return $this->data['features'][$feature] ?? null;
    }
}

// Middleware automático — detecta tenant por dominio/subdominio
// tenant.myapp.com → Tenant{slug: 'tenant'}
// En rutas tenant-aware, todo corre en el contexto del tenant
```

### Billing con Laravel Cashier

```php
class BillingService
{
    public function subscribe(Tenant $tenant, string $plan): Subscription
    {
        // Tenant como customer de Stripe
        if (!$tenant->hasStripeId()) {
            $tenant->createAsStripeCustomer([
                'name' => $tenant->name,
                'email' => $tenant->admin_email,
            ]);
        }

        return $tenant->newSubscription('default', $plan)
            ->trialDays(14)
            ->create($tenant->pm_id);
    }

    public function checkFeatureLimit(Tenant $tenant, string $feature): void
    {
        $plan = $tenant->subscription('default')?->stripe_price;
        $limits = config("billing.plans.$plan.limits");

        if (isset($limits[$feature])) {
            $current = $this->getCurrentUsage($tenant, $feature);
            if ($current >= $limits[$feature]) {
                throw new FeatureLimitExceededException($feature, $limits[$feature]);
            }
        }
    }
}
```

---

## E-commerce — Alta Disponibilidad

### Arquitectura para picos de tráfico (Black Friday, lanzamientos)

```
                    ┌─── CDN (Cloudflare) ───┐
                    │  Assets, páginas static │
                    └─────────────────────────┘
                               ↓
              ┌─── Load Balancer (AWS ALB) ───┐
              │                               │
         ┌────┴────┐                    ┌────┴────┐
         │ App 1   │                    │ App 2   │  (auto-scaling)
         └────┬────┘                    └────┬────┘
              │                               │
         ┌────┴───────────────────────────────┴────┐
         │          Redis Cluster                   │
         │  Sessions / Cache / Rate limiting / Cart │
         └─────────────────────────────────────────┘
                               ↓
         ┌─────────────────────────────────────────┐
         │         PostgreSQL Primary               │
         │              + Read Replica(s)           │
         └─────────────────────────────────────────┘
                               ↓
         ┌─────────────────────────────────────────┐
         │       Queue Workers (Horizon)            │
         │  Orders / Emails / Inventory updates     │
         └─────────────────────────────────────────┘
```

### Carrito: Session vs BD vs Redis

```php
// Redis para carrito — rápido, no bloquea BD, TTL automático
class CartService
{
    private string $cartKey;

    public function __construct(private Redis $redis, int $userId)
    {
        $this->cartKey = "cart:user:{$userId}";
    }

    public function addItem(int $productId, int $quantity): void
    {
        $cart = $this->getCart();
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
        $this->redis->setex($this->cartKey, 86400 * 7, json_encode($cart));
        // TTL: 7 días. El carrito no se pierde si el usuario cierra el browser.
    }

    public function checkout(): Order
    {
        $cart = $this->getCart();
        // Verificar stock en este momento (no cuando agregó al carrito)
        $this->inventoryService->lockStock($cart);
        // Proceder con orden
        $order = $this->orderService->create($cart);
        $this->redis->del($this->cartKey);
        return $order;
    }
}
```

### Manejo de Stock Concurrente

```php
// Problema: dos usuarios compran el último item simultáneamente
// Solución: Pessimistic Locking o Optimistic Locking

// Pessimistic Lock (bloquea la fila durante la transacción)
DB::transaction(function () use ($productId, $quantity) {
    $product = Product::lockForUpdate()->find($productId);
    // Solo un proceso puede tener este lock a la vez

    if ($product->stock < $quantity) {
        throw new InsufficientStockException();
    }

    $product->decrement('stock', $quantity);
    // Lock se libera al cerrar la transacción
});

// Optimistic Lock (sin bloqueo, detecta conflictos al guardar)
DB::transaction(function () use ($productId, $quantity, $version) {
    $affected = Product::where('id', $productId)
        ->where('version', $version)          // versión esperada
        ->where('stock', '>=', $quantity)
        ->update([
            'stock' => DB::raw("stock - {$quantity}"),
            'version' => DB::raw('version + 1'),
        ]);

    if ($affected === 0) {
        throw new StockConflictException(); // Otro proceso fue primero
    }
});
```

---

## API REST — Backend Headless

### Estructura de Respuestas Consistente

```php
// Trait para respuestas estandarizadas
trait ApiResponse
{
    protected function success(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
        ], $status);
    }

    protected function error(string $message, int $status, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    protected function paginated(LengthAwarePaginator $paginator): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}

// Handler global de excepciones
class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->renderable(function (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        });

        $this->renderable(function (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
            ], 404);
        });

        $this->renderable(function (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        });
    }
}
```

### Versionado de API

```php
// routes/api.php
Route::prefix('v1')->group(base_path('routes/api/v1.php'));
Route::prefix('v2')->group(base_path('routes/api/v2.php'));

// Estructura por versión
app/Http/Controllers/
├── V1/
│   ├── UserController.php
│   └── OrderController.php
└── V2/
    ├── UserController.php   // Breaking changes aquí, sin tocar V1
    └── OrderController.php

// Header alternativo para versionado
// Accept: application/vnd.myapp.v2+json
```

---

## Real-time — WebSockets con Laravel Reverb

```php
// Evento broadcast
class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    // Canal privado por usuario — solo el dueño de la orden recibe
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("orders.{$this->order->user_id}"),
        ];
    }

    // Solo enviar los datos necesarios
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'updated_at' => $this->order->updated_at->toISOString(),
        ];
    }

    // Nombre del evento en el cliente
    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }
}

// Cliente JavaScript (Laravel Echo)
Echo.private(`orders.${userId}`)
    .listen('.order.status.updated', (data) => {
        updateOrderStatus(data.order_id, data.status);
    });
```

---

## Señales de que la Arquitectura Actual se Quedó Pequeña

```
🟡 Advertencia temprana:
   - Controllers > 200 líneas consistentemente
   - Services que importan otros Services formando cadenas largas
   - Tests de un módulo rompen al cambiar otro módulo
   - BD con queries de más de 5 JOINs frecuentemente
   - Un dev es el único que entiende cierto módulo

🔴 Acción requerida:
   - Deploy de un cambio pequeño requiere QA de todo el sistema
   - Bugs en producción frecuentes por efectos secundarios inesperados
   - Onboarding de nuevo dev tarda más de 2 semanas en ser productivo
   - Miedo a cambiar código "que funciona"
   - Dos equipos bloqueados esperando que el otro termine
```
