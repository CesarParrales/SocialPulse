# Eloquent Avanzado

## Scopes — Queries Reutilizables

```php
class Order extends Model
{
    // Global scope — aplica a TODAS las queries del modelo
    protected static function booted(): void
    {
        // Solo órdenes del usuario actual (si aplica multi-tenant)
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->isAdmin()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }

    // Local scopes — reutilizables con ->scope()
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Pending);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeWithRevenue(Builder $query): Builder
    {
        return $query->selectRaw('*, SUM(total_cents) OVER() as total_revenue');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function ($q, $term) {
            $q->where(function ($q) use ($term) {
                $q->where('reference', 'like', "%{$term}%")
                  ->orWhereHas('user', fn($q) => $q->where('email', 'like', "%{$term}%"));
            });
        });
    }
}

// Uso encadenado — legible y reutilizable
$orders = Order::pending()
    ->recent(30)
    ->search($request->search)
    ->with(['user', 'items'])
    ->orderByDesc('created_at')
    ->paginate(20);
```

---

## Observers — Reaccionar a Eventos del Modelo

```php
class OrderObserver
{
    public function creating(Order $order): void
    {
        // Generar UUID y reference antes de crear
        $order->uuid      = Str::ulid();
        $order->reference = 'ORD-' . strtoupper(Str::random(8));
    }

    public function created(Order $order): void
    {
        // Notificar al usuario después de crear
        $order->user->notify(new OrderCreatedNotification($order));
    }

    public function updating(Order $order): void
    {
        // Guardar historial de cambios de status
        if ($order->isDirty('status')) {
            $order->statusHistory()->create([
                'from_status' => $order->getOriginal('status'),
                'to_status'   => $order->status,
                'changed_by'  => auth()->id(),
            ]);
        }
    }

    public function deleting(Order $order): void
    {
        // Validar que se puede eliminar
        if ($order->status === OrderStatus::Shipped) {
            throw new CannotDeleteShippedOrderException();
        }
    }
}

// Registrar en AppServiceProvider o directamente en el modelo
class Order extends Model
{
    protected static function booted(): void
    {
        static::observe(OrderObserver::class);
    }
}
```

---

## Casts Personalizados

```php
// Cast para tipo Money (Value Object)
class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Money
    {
        return new Money(
            amount: (int) $value,
            currency: $attributes['currency'] ?? 'USD'
        );
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value instanceof Money) {
            return [
                $key => $value->amount,
                'currency' => $value->currency,
            ];
        }
        return [$key => (int) $value];
    }
}

// Cast para JSON con clase tipada (usando spatie/laravel-data)
class OrderMetadataCast implements CastsAttributes
{
    public function get($model, $key, $value, $attributes): OrderMetadata
    {
        return OrderMetadata::from(json_decode($value, true) ?? []);
    }

    public function set($model, $key, $value, $attributes): string
    {
        return $value instanceof OrderMetadata
            ? json_encode($value->toArray())
            : $value;
    }
}

// Usar en modelo
class Order extends Model
{
    protected $casts = [
        'status'       => OrderStatus::class,    // PHP 8.1 Enum
        'total'        => MoneyCast::class,       // Cast personalizado
        'metadata'     => OrderMetadataCast::class,
        'shipped_at'   => 'datetime',
        'is_gift'      => 'boolean',
    ];
}
```

---

## Relaciones Avanzadas

```php
class User extends Model
{
    // HasManyThrough — órdenes a través de tiendas
    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(Order::class, Store::class);
    }

    // MorphMany — notificaciones polimórficas
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    // Relación con condición
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->latest();
    }

    // BelongsToMany con campos extra en pivot
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot(['assigned_at', 'assigned_by', 'expires_at'])
            ->withTimestamps()
            ->wherePivotNull('expires_at')
            ->orWherePivot('expires_at', '>', now());
    }
}

// Eager loading con condiciones
$users = User::with([
    'orders' => fn($q) => $q->where('status', 'pending')->latest(),
    'orders.items:id,order_id,product_id,quantity',  // solo campos necesarios
    'roles:id,name',
])->paginate(20);

// Contar relaciones sin cargarlas
$users = User::withCount([
    'orders',
    'orders as pending_orders_count' => fn($q) => $q->where('status', 'pending'),
])->get();

// $user->orders_count, $user->pending_orders_count
```

---

## Chunking y Lazy Collections — Grandes Volúmenes

```php
// chunk — procesar por lotes (carga lote en memoria)
Order::pending()->chunk(500, function (Collection $orders) {
    foreach ($orders as $order) {
        ProcessOrderJob::dispatch($order);
    }
});

// chunkById — más eficiente en tablas grandes (usa índice de PK)
Order::pending()
    ->orderBy('id')
    ->chunkById(1000, function (Collection $orders) {
        // procesar lote
    });

// LazyCollection — un registro a la vez (mínima memoria)
Order::pending()
    ->lazy()
    ->each(function (Order $order) {
        // procesar sin cargar todos en memoria
    });

// cursor — lazy con un solo query
foreach (Order::pending()->cursor() as $order) {
    // procesar
}

// Para exportar millones de filas sin OOM
public function export(): StreamedResponse
{
    return response()->streamDownload(function () {
        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['ID', 'Reference', 'Total', 'Status', 'Date']);

        Order::select(['id', 'reference', 'total_cents', 'status', 'created_at'])
            ->lazy(1000)
            ->each(function (Order $order) use ($handle) {
                fputcsv($handle, [
                    $order->id,
                    $order->reference,
                    $order->total_cents / 100,
                    $order->status,
                    $order->created_at->format('Y-m-d'),
                ]);
            });

        fclose($handle);
    }, 'orders-export.csv');
}
```

---

## Subqueries y Queries Avanzadas

```php
// Subquery en select — último pedido del usuario
$users = User::addSelect([
    'last_order_at' => Order::select('created_at')
        ->whereColumn('user_id', 'users.id')
        ->latest()
        ->limit(1),
    'total_spent' => Order::selectRaw('SUM(total_cents)')
        ->whereColumn('user_id', 'users.id')
        ->where('status', '!=', 'cancelled'),
])->get();

// $user->last_order_at, $user->total_spent

// whereExists — más eficiente que whereIn para subqueries grandes
$usersWithOrders = User::whereExists(function ($query) {
    $query->select(DB::raw(1))
        ->from('orders')
        ->whereColumn('user_id', 'users.id')
        ->where('status', 'delivered');
})->get();

// Raw queries para agregaciones complejas
$stats = Order::selectRaw('
    DATE_TRUNC(\'month\', created_at) AS month,
    COUNT(*) AS total_orders,
    COUNT(*) FILTER (WHERE status = \'delivered\') AS completed_orders,
    SUM(total_cents) AS revenue_cents,
    AVG(total_cents)::INTEGER AS avg_order_cents
')
->where('created_at', '>=', now()->subMonths(12))
->groupByRaw('DATE_TRUNC(\'month\', created_at)')
->orderByRaw('month DESC')
->get();

// Upsert — crear o actualizar en batch
Product::upsert(
    $products,                          // array de datos
    ['sku'],                            // columnas únicas para match
    ['name', 'price_cents', 'stock']    // columnas a actualizar si existe
);
```

---

## spatie/laravel-data — DTOs Tipados

```php
// Alternativa moderna a DTOs manuales
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Min;

class CreateOrderData extends Data
{
    public function __construct(
        #[Required]
        public readonly array $items,

        #[Required, Min(1)]
        public readonly string $shipping_address,

        public readonly ?string $coupon_code = null,
        public readonly ?string $notes = null,
    ) {}

    // Crear desde request con validación automática
    public static function fromRequest(Request $request): self
    {
        return self::from($request->validated());
    }

    // Rules para validación integrada
    public static function rules(): array
    {
        return [
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
            'shipping_address'     => ['required', 'string', 'max:500'],
        ];
    }
}

// En controller — con validación automática
public function store(Request $request): JsonResponse
{
    $data = CreateOrderData::validateAndCreate($request->all());
    // Lanza ValidationException automáticamente si falla
    $order = $this->service->create($data);
    return OrderResource::make($order)->response()->setStatusCode(201);
}
```
