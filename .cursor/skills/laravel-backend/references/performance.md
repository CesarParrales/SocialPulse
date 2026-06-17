# Performance y Producción

## Laravel Octane — 10x Performance

Octane mantiene la aplicación en memoria entre requests (sin bootstrap por request).

```bash
composer require laravel/octane
php artisan octane:install  # elegir: FrankenPHP (recomendado) o Swoole
```

```php
// config/octane.php
return [
    'server' => env('OCTANE_SERVER', 'frankenphp'),
    'warm'   => [
        // Servicios a precalentar en memoria al arrancar
        \App\Services\CurrencyConverter::class,
        \App\Services\ConfigService::class,
    ],
    'listeners' => [
        // Limpiar estado entre requests — CRÍTICO
        RequestReceived::class => [
            ...Octane::prepareApplicationForNextOperation(),
            ...Octane::prepareApplicationForNextRequest(),
        ],
        RequestHandled::class => [],
        RequestTerminated::class => [
            FlushTemporaryContainerInstances::class,
        ],
    ],
    'garbage' => 50,  // forzar GC cada 50 requests
    'max_execution_time' => 30,
    'tick_interval' => -1,
    'workers' => env('OCTANE_WORKERS', 'auto'),
    'task_workers' => env('OCTANE_TASK_WORKERS', 'auto'),
];

// ⚠️ PRECAUCIONES con Octane:
// 1. Sin estado estático entre requests — usar DI, no static properties
// 2. DB connections se reusan — verificar si el driver es compatible
// 3. Limpiar bindings del container que tienen estado
// 4. Testear con Octane antes de producción — algunos paquetes no son compatibles
```

---

## Caché — Estrategias en Laravel

```php
// Configuración Redis (preferida sobre file/array)
// .env
CACHE_STORE=redis
REDIS_CACHE_DB=1  // base de datos Redis separada para caché

// Tags para invalidación granular
class ProductService
{
    public function getWithCategory(int $id): Product
    {
        return Cache::tags(["products", "product:{$id}"])
            ->remember("product:{$id}:full", 3600, fn() =>
                Product::with('category', 'variants')->findOrFail($id)
            );
    }

    public function update(int $id, array $data): Product
    {
        $product = Product::findOrFail($id)->fill($data);
        $product->save();

        // Invalidar todo lo relacionado con este producto
        Cache::tags(["product:{$id}"])->flush();
        // Invalidar listings de su categoría
        Cache::tags(["category:{$product->category_id}"])->flush();

        return $product;
    }
}

// Cache forever para datos que casi nunca cambian
$countries = Cache::rememberForever('countries', fn() => Country::all());

// Cache con lock para evitar thundering herd
$data = Cache::lock('generate-report', 60)->get(function () {
    // Solo un proceso puede generar el reporte a la vez
    return Report::generate();
});

// Atomic lock para operaciones críticas
$lock = Cache::lock('process-payment:' . $orderId, 30);
if ($lock->get()) {
    try {
        // procesar pago
    } finally {
        $lock->release();
    }
} else {
    throw new PaymentAlreadyProcessingException();
}
```

---

## Response Optimization

```php
// Comprimir responses con middleware
// En Kernel.php
\Illuminate\Http\Middleware\GzipResponse::class

// Paginar SIEMPRE — nunca devolver colecciones sin límite
// ❌ MAL
Product::all()  // sin límite — puede devolver millones de registros

// ✅ BIEN
Product::paginate(50)
Product::cursorPaginate(50)  // más eficiente para tablas grandes

// Seleccionar solo columnas necesarias
User::select(['id', 'name', 'email'])->get()  // no SELECT *

// Defer loading de relaciones pesadas
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->uuid,
            'status' => $this->status,
            // whenLoaded evita error si no se hizo eager loading
            // Y evita N+1 si se olvidó el with()
            'items'  => OrderItemResource::collection($this->whenLoaded('items')),
            'user'   => UserResource::make($this->whenLoaded('user')),
        ];
    }
}
```

---

## Deploy Pipeline

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_DB: myapp_test
          POSTGRES_USER: myapp
          POSTGRES_PASSWORD: secret
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'  # leer de composer.json require.php
          extensions: pdo_pgsql, redis
          coverage: pcov

      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader

      - name: Run tests
        env:
          DB_CONNECTION: pgsql
          DB_HOST: 127.0.0.1
          DB_DATABASE: myapp_test
          DB_USERNAME: myapp
          DB_PASSWORD: secret
        run: php artisan test --parallel --coverage --min=80

  deploy:
    needs: test
    runs-on: ubuntu-latest
    steps:
      - name: Deploy via SSH
        uses: appleboy/ssh-action@v1
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/myapp

            # Zero-downtime deploy
            git pull origin main
            composer install --no-dev --optimize-autoloader
            php artisan migrate --force    # migraciones en producción
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            php artisan event:cache

            # Reiniciar workers sin downtime
            php artisan queue:restart
            php artisan octane:reload      # si usa Octane
```

---

## Monitoreo con Laravel Pulse

```php
// config/pulse.php — métricas a monitorear
'recorders' => [
    Recorders\CacheInteractions::class => [
        'enabled' => env('PULSE_CACHE_INTERACTIONS_ENABLED', true),
        'sample_rate' => 1,
    ],
    Recorders\Exceptions::class => [
        'enabled' => env('PULSE_EXCEPTIONS_ENABLED', true),
        'sample_rate' => 1,
    ],
    Recorders\Queues::class => [
        'enabled' => env('PULSE_QUEUES_ENABLED', true),
        'sample_rate' => 1,
    ],
    Recorders\Requests::class => [
        'enabled' => env('PULSE_REQUESTS_ENABLED', true),
        'sample_rate' => 0.1,  // 10% de requests — no registrar todos
        'ignore' => [
            '#^/pulse#',      // no trackear el propio Pulse
            '#^/horizon#',
            '#^/_debugbar#',
        ],
    ],
    Recorders\SlowQueries::class => [
        'enabled' => env('PULSE_SLOW_QUERIES_ENABLED', true),
        'threshold' => 1000,   // queries > 1 segundo
    ],
    Recorders\SlowRequests::class => [
        'threshold' => 2000,   // requests > 2 segundos
    ],
],

// Proteger dashboard en producción
// routes/web.php
Route::middleware(['auth', 'can:viewPulse'])
    ->get('/pulse', fn() => view('vendor.pulse.dashboard'));
```

---

## Sentry — Error Tracking en Producción

```bash
composer require sentry/sentry-laravel
php artisan sentry:publish
```

```php
// config/sentry.php
return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'environment' => env('APP_ENV', 'production'),
    'release' => env('APP_VERSION', 'unknown'),
    'traces_sample_rate' => 0.1,  // 10% de requests para performance
    'breadcrumbs' => [
        'logs' => true,
        'sql_queries' => true,
        'sql_bindings' => false,  // no loguear bindings (datos sensibles)
        'queue_info' => true,
    ],
];

// Capturar excepciones de dominio con contexto
try {
    $this->paymentService->charge($order);
} catch (PaymentException $e) {
    \Sentry\captureException($e, [
        'extra' => [
            'order_id'  => $order->id,
            'amount'    => $order->total_cents,
            'user_id'   => $order->user_id,
        ],
    ]);
    throw $e;
}

// Identificar usuario en Sentry
public function handle(Request $request, Closure $next): Response
{
    if (auth()->check()) {
        \Sentry\configureScope(function (\Sentry\State\Scope $scope) {
            $scope->setUser([
                'id'    => auth()->id(),
                'email' => auth()->user()->email,
            ]);
        });
    }
    return $next($request);
}
```
