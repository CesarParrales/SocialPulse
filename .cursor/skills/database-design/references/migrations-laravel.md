# Migraciones y Laravel

## Principios de Migraciones

```
1. Cada migración = un cambio atómico y reversible
2. Nunca modificar una migración ya en producción — crear una nueva
3. Migraciones destructivas (DROP, ALTER con pérdida de datos) requieren plan de rollback
4. Datos de seed separados de migraciones de estructura
5. Migraciones largas (> 5 min) requieren estrategia de zero-downtime
```

---

## Patrones de Migración en Laravel

### Migración Completa con Todas las Opciones

```php
Schema::create('orders', function (Blueprint $table) {
    // PKs
    $table->id();                          // BIGINT UNSIGNED AUTO_INCREMENT
    $table->uuid('uuid')->unique();        // para exponer en API (no el ID interno)

    // FKs con constraint explícito
    $table->foreignId('user_id')
          ->constrained()
          ->onDelete('restrict');         // no borrar user si tiene orders

    $table->foreignId('coupon_id')
          ->nullable()
          ->constrained()
          ->onDelete('set null');         // si se borra coupon, order queda sin cupón

    // Tipos de datos
    $table->string('status', 20)->default('pending');
    $table->unsignedInteger('total_cents');  // dinero en centavos
    $table->string('currency', 3)->default('USD');
    $table->string('shipping_address');
    $table->text('notes')->nullable();
    $table->jsonb('metadata')->default('{}');  // PostgreSQL JSONB

    // Timestamps
    $table->timestamps();                  // created_at + updated_at
    $table->softDeletes();                 // deleted_at nullable

    // Índices
    $table->index(['user_id', 'status']);  // consultas por usuario + estado
    $table->index('created_at');           // ordenamiento y rangos de fecha
});

// Check constraints — Blueprint NO tiene método check(); usar SQL directo
DB::statement(
    'ALTER TABLE orders ADD CONSTRAINT orders_total_positive CHECK (total_cents >= 0)'
);
DB::statement(
    "ALTER TABLE orders ADD CONSTRAINT orders_status_valid
     CHECK (status IN ('pending','processing','shipped','delivered','cancelled'))"
);
```

### Migraciones de Alteración — Con Cuidado

```php
// Agregar columna nullable — safe, no requiere downtime
Schema::table('users', function (Blueprint $table) {
    $table->string('phone', 20)->nullable()->after('email');
});

// Agregar columna NOT NULL con default — safe
Schema::table('products', function (Blueprint $table) {
    $table->boolean('is_featured')->default(false)->after('is_active');
});

// Agregar columna NOT NULL sin default — PELIGROSO en tabla con datos
// Hacer en dos pasos:
// Paso 1: agregar nullable
Schema::table('orders', function (Blueprint $table) {
    $table->string('reference_code', 20)->nullable()->after('status');
});
// Paso 2: llenar datos existentes
Order::whereNull('reference_code')->each(function ($order) {
    $order->update(['reference_code' => 'ORD-' . str_pad($order->id, 8, '0', STR_PAD_LEFT)]);
});
// Paso 3: cambiar a NOT NULL (en migración separada, después de deploy)
Schema::table('orders', function (Blueprint $table) {
    $table->string('reference_code', 20)->nullable(false)->change();
});
```

### Migración Destructiva con Rollback

```php
public function up(): void
{
    // Antes de DROP: backup de datos en tabla temporal
    DB::statement('CREATE TABLE legacy_user_data AS SELECT * FROM user_profiles');
    Schema::dropIfExists('user_profiles');
}

public function down(): void
{
    // Restaurar desde backup
    Schema::create('user_profiles', function (Blueprint $table) {
        // estructura original
    });
    DB::statement('INSERT INTO user_profiles SELECT * FROM legacy_user_data');
    DB::statement('DROP TABLE IF EXISTS legacy_user_data');
}
```

---

## Migraciones Zero-Downtime

Para tablas grandes en producción donde un ALTER TABLE bloquea escrituras:

### Estrategia Expand-Contract (Blue-Green Schema)

```
FASE 1 — EXPAND (deploy sin downtime):
  - Agregar columna nueva nullable
  - Actualizar código para escribir en AMBAS columnas (vieja y nueva)
  - Deploy

FASE 2 — MIGRAR (background job):
  - Llenar columna nueva en registros existentes por lotes
  - Con un command de backfill propio, p. ej.:
    php artisan migrate:backfill-order-references --batch=1000
    (ver implementación en "Backfill por Lotes" abajo)

FASE 3 — CONTRACT (deploy sin downtime):
  - Actualizar código para leer SOLO de columna nueva
  - Eliminar escritura a columna vieja
  - Deploy

FASE 4 — CLEANUP (migración simple):
  - DROP COLUMN en columna vieja
  - Ya no bloquea porque el código no la usa
```

### Backfill por Lotes en Laravel

```php
// Artisan command para backfill seguro
class BackfillOrderReferenceCodes extends Command
{
    protected $signature = 'migrate:backfill-order-references {--batch=1000}';

    public function handle(): void
    {
        $batch = (int) $this->option('batch');
        $total = Order::whereNull('reference_code')->count();
        $bar = $this->output->createProgressBar($total);

        Order::whereNull('reference_code')
            ->orderBy('id')
            ->chunk($batch, function ($orders) use ($bar) {
                foreach ($orders as $order) {
                    $order->updateQuietly([  // sin disparar eventos
                        'reference_code' => 'ORD-' . str_pad($order->id, 8, '0', STR_PAD_LEFT)
                    ]);
                    $bar->advance();
                }
                // Pausa para no saturar la BD en producción
                usleep(100000);  // 100ms entre chunks
            });

        $bar->finish();
        $this->info("Backfill completado: {$total} registros");
    }
}
```

---

## Seeders — Datos de Referencia y Testing

```php
// Seeder de datos de referencia (países, monedas, roles)
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotente — puede correr múltiples veces sin duplicar
        $roles = ['admin', 'manager', 'customer'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }
}

// Factory para testing con datos realistas
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'       => fake()->uuid(),
            'user_id'    => User::factory(),
            'status'     => fake()->randomElement(['pending', 'processing', 'shipped']),
            'total_cents' => fake()->numberBetween(1000, 100000),
            'currency'   => 'USD',
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    // Estados específicos para tests
    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function shipped(): static
    {
        return $this->state(['status' => 'shipped']);
    }

    public function withItems(int $count = 3): static
    {
        return $this->has(OrderItem::factory()->count($count));
    }
}

// Uso en tests
$order = Order::factory()->pending()->withItems(3)->create();
$orders = Order::factory()->count(50)->shipped()->create(['user_id' => $user->id]);
```

---

## Modelos Eloquent — Buenas Prácticas

```php
class Order extends Model
{
    use SoftDeletes;

    // Columns que se pueden asignar masivamente (explícito, no $guarded = [])
    protected $fillable = [
        'user_id', 'status', 'total_cents', 'currency',
        'shipping_address', 'notes', 'metadata',
    ];

    // Castings automáticos
    protected $casts = [
        'metadata'   => 'array',          // JSONB → array PHP
        'total_cents' => 'integer',
        'status'     => OrderStatus::class, // cast a Enum nativo PHP 8.1+
        'created_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Columnas ocultas en serialización (nunca en API responses)
    protected $hidden = ['deleted_at'];

    // Appends — atributos computados en serialización
    protected $appends = ['total_formatted'];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Attribute — valor computado
    public function getTotalFormattedAttribute(): string
    {
        return number_format($this->total_cents / 100, 2) . ' ' . $this->currency;
    }

    // Scopes — queries reutilizables
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Pending);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInDateRange(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}

// Uso de scopes — legible y reutilizable
$orders = Order::pending()
    ->forUser(auth()->id())
    ->inDateRange(now()->subDays(7), now())
    ->with(['items.product', 'user'])
    ->orderByDesc('created_at')
    ->paginate(20);
```

---

## Transacciones — Cuándo y Cómo

```php
// Siempre que múltiples operaciones de BD deben ser atómicas
public function placeOrder(PlaceOrderDTO $dto): Order
{
    return DB::transaction(function () use ($dto) {
        // Si cualquier paso falla, todo hace rollback automático
        $order = Order::create([...]);

        foreach ($dto->items as $item) {
            // Lock pesimista — evita vender más stock del disponible
            $product = Product::lockForUpdate()->findOrFail($item['product_id']);

            if ($product->stock < $item['quantity']) {
                throw new InsufficientStockException($product->id);
                // La excepción aborta la transacción completa
            }

            $product->decrement('stock', $item['quantity']);

            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $product->id,
                'quantity'   => $item['quantity'],
                'price_cents' => $product->price_cents,
            ]);
        }

        // Eventos DESPUÉS de que la transacción commitea
        // (si el evento dispara un job que lee la BD, el dato ya debe estar)
        $order->load('items');
        return $order;
    });

    // Despachar eventos FUERA de la transacción
    event(new OrderPlaced($order));
}

// Nivel de aislamiento para operaciones críticas
DB::transaction(function () {
    // ...
}, attempts: 3);  // reintentar hasta 3 veces si hay deadlock

// Serializable isolation para operaciones financieras críticas
DB::statement('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');
DB::transaction(function () { ... });
```
