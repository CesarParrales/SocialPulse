# REST — Patrones e Implementación Laravel

## API Resources — Transformación de Output

Nunca devolver Eloquent models directamente. Siempre API Resources.

```php
// OrderResource — control total del output
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->uuid,           // UUID público, no ID interno
            'status'     => $this->status,
            'total'      => [
                'amount'    => $this->total_cents,
                'currency'  => $this->currency,
                'formatted' => '$' . number_format($this->total_cents / 100, 2),
            ],
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relaciones cargadas condicionalmente — no N+1
            'user'  => UserResource::make($this->whenLoaded('user')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),

            // Campo condicional según contexto
            'invoice_url' => $this->when(
                $this->status === 'delivered',
                fn() => route('invoices.show', $this->uuid)
            ),

            // Solo para admins
            'internal_notes' => $this->when(
                $request->user()?->isAdmin(),
                $this->internal_notes
            ),
        ];
    }

    // Agregar metadata fuera de data
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
            ],
        ];
    }
}

// Colección con transformación de paginación
class OrderCollection extends ResourceCollection
{
    public $collects = OrderResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return [
            'meta' => [
                'current_page' => $paginated['meta']['current_page'],
                'last_page'    => $paginated['meta']['last_page'],
                'per_page'     => $paginated['meta']['per_page'],
                'total'        => $paginated['meta']['total'],
            ],
            'links' => $paginated['links'],
        ];
    }
}
```

---

## Form Requests — Validación Declarativa

```php
class CreateOrderRequest extends FormRequest
{
    // Autorización — quién puede crear órdenes
    public function authorize(): bool
    {
        return $this->user()->can('create', Order::class);
    }

    public function rules(): array
    {
        return [
            'items'                  => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id'     => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1', 'max:100'],
            'shipping_address'       => ['required', 'string', 'max:500'],
            'coupon_code'            => ['nullable', 'string', 'exists:coupons,code'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
        ];
    }

    // Mensajes personalizados
    public function messages(): array
    {
        return [
            'items.required'              => 'At least one item is required.',
            'items.*.product_id.exists'   => 'Product :input does not exist.',
            'items.*.quantity.max'        => 'Maximum 100 units per product.',
        ];
    }

    // Preprocesar datos antes de validar
    protected function prepareForValidation(): void
    {
        // Normalizar coupon_code a mayúsculas
        if ($this->coupon_code) {
            $this->merge(['coupon_code' => strtoupper($this->coupon_code)]);
        }
    }

    // Datos limpios después de validación
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        // Solo devolver campos explícitamente validados
        return $data;
    }
}
```

---

## Controller Estructura Completa

```php
class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $service,
        private readonly OrderQueryService $queryService,
    ) {}

    /**
     * GET /api/v1/orders
     * Lista órdenes con filtros, paginación y ordenamiento
     */
    public function index(IndexOrderRequest $request): JsonResponse
    {
        $orders = $this->queryService->paginate(
            filters: $request->validated(),
            perPage: $request->integer('per_page', 20),
            userId: $request->user()->id,
        );

        return OrderResource::collection($orders)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * POST /api/v1/orders
     * Crear nueva orden
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $order = $this->service->create(
            CreateOrderDTO::fromRequest($request)
        );

        return OrderResource::make($order)
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('api.v1.orders.show', $order->uuid));
    }

    /**
     * GET /api/v1/orders/{order}
     * Detalle de una orden
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['user', 'items.product', 'payments']);

        return OrderResource::make($order)->response();
    }

    /**
     * PATCH /api/v1/orders/{order}
     * Actualización parcial
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $order = $this->service->update($order, $request->validated());

        return OrderResource::make($order)->response();
    }

    /**
     * DELETE /api/v1/orders/{order}
     * Cancelar/eliminar orden
     */
    public function destroy(Order $order): JsonResponse
    {
        $this->authorize('delete', $order);

        $this->service->cancel($order);

        return response()->json(null, 204);
    }

    /**
     * POST /api/v1/orders/{order}/cancel
     * Acción específica de cancelación
     */
    public function cancel(CancelOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('cancel', $order);

        $order = $this->service->cancel($order, $request->input('reason'));

        return OrderResource::make($order)->response();
    }
}
```

---

## Route Model Binding — Por UUID Público

```php
// Buscar por UUID en lugar de ID interno
class Order extends Model
{
    // Laravel buscará por uuid en lugar de id
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

// routes/api/v1.php
Route::apiResource('orders', OrderController::class);
// Laravel automáticamente resuelve Order por uuid
// GET /orders/01HX4B2C3D → Order::where('uuid', '01HX4B2C3D')->firstOrFail()
// 404 automático si no existe
```

---

## Exception Handler — Respuestas de Error Consistentes

```php
// bootstrap/app.php (Laravel actual del proyecto)
->withExceptions(function (Exceptions $exceptions) {

    // Validación — 422
    $exceptions->render(function (ValidationException $e, Request $request) {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $e->errors(),
            ], 422);
        }
    });

    // No encontrado — 404
    $exceptions->render(function (ModelNotFoundException $e, Request $request) {
        if ($request->expectsJson()) {
            $model = class_basename($e->getModel());
            return response()->json([
                'message'    => "{$model} not found.",
                'error_code' => strtoupper($model) . '_NOT_FOUND',
            ], 404);
        }
    });

    // No autorizado — 403
    $exceptions->render(function (AuthorizationException $e, Request $request) {
        if ($request->expectsJson()) {
            return response()->json([
                'message'    => 'This action is unauthorized.',
                'error_code' => 'FORBIDDEN',
            ], 403);
        }
    });

    // No autenticado — 401
    $exceptions->render(function (AuthenticationException $e, Request $request) {
        if ($request->expectsJson()) {
            return response()->json([
                'message'    => 'Unauthenticated.',
                'error_code' => 'UNAUTHENTICATED',
            ], 401);
        }
    });

    // Excepciones de dominio con código de error específico
    $exceptions->render(function (DomainException $e, Request $request) {
        if ($request->expectsJson()) {
            return response()->json([
                'message'    => $e->getMessage(),
                'error_code' => $e->getErrorCode(),  // ej: 'INSUFFICIENT_STOCK'
            ], $e->getHttpStatus());
        }
    });

    // Cualquier otro error — 500
    $exceptions->render(function (Throwable $e, Request $request) {
        if ($request->expectsJson() && app()->isProduction()) {
            $errorId = Str::uuid();
            Log::error('Unhandled exception', [
                'error_id'  => $errorId,
                'exception' => $e,
                'request'   => $request->all(),
            ]);

            return response()->json([
                'message'  => 'An unexpected error occurred.',
                'error_id' => $errorId,  // para correlacionar con logs
            ], 500);
        }
    });
})
```

---

## Rate Limiting

```php
// bootstrap/app.php o RouteServiceProvider
RateLimiter::for('api', function (Request $request) {
    return $request->user()
        ? Limit::perMinute(60)->by($request->user()->id)
        : Limit::perMinute(10)->by($request->ip());
});

// Rate limiting diferenciado por tipo de operación
RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip())
        ->response(function (Request $request, array $headers) {
            return response()->json([
                'message'     => 'Too many login attempts.',
                'retry_after' => $headers['Retry-After'],
            ], 429, $headers);
        });
});

RateLimiter::for('uploads', function (Request $request) {
    return [
        Limit::perMinute(10)->by($request->user()->id),
        Limit::perDay(100)->by($request->user()->id),
    ];
});

// En rutas
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::apiResource('orders', OrderController::class);
});

Route::middleware('throttle:auth')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);
});
```

---

## Filtros, Ordenamiento e Includes con spatie/laravel-query-builder

```php
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = QueryBuilder::for(Order::class)
            ->allowedFilters([
                'status',
                AllowedFilter::exact('user_id'),
                AllowedFilter::scope('created_after'),  // scope en el modelo
                AllowedFilter::callback('total_min', function ($query, $value) {
                    $query->where('total_cents', '>=', $value * 100);
                }),
            ])
            ->allowedSorts(['created_at', 'total_cents', 'status'])
            ->allowedIncludes(['user', 'items', 'items.product'])
            ->defaultSort('-created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }
}
```

---

## Versionado en Laravel — Rutas y Middleware de Deprecación

```php
// routes/api.php
Route::prefix('v1')->name('api.v1.')->group(base_path('routes/api/v1.php'));
Route::prefix('v2')->name('api.v2.')->group(base_path('routes/api/v2.php'));

// Middleware de deprecación
class DeprecatedVersionMiddleware
{
    public function handle(Request $request, Closure $next, string $sunsetDate): Response
    {
        $response = $next($request);
        $response->headers->set('Deprecation', 'true');
        $response->headers->set('Sunset', $sunsetDate);
        $response->headers->set('Link', '</api/v2/docs>; rel="successor-version"');
        return $response;
    }
}

// routes/api/v1.php
// Sunset = fecha futura real al momento de anunciar (mínimo deploy + 6 meses)
Route::middleware('deprecated:' . now()->addMonths(6)->toDateString())->group(function () {
    Route::apiResource('orders', V1\OrderController::class);
});
```

---

## CORS

```php
// config/cors.php
return [
    'paths'                => ['api/*'],
    'allowed_methods'      => ['*'],
    'allowed_origins'      => [
        env('FRONTEND_URL', 'http://localhost:3000'),
        'https://app.midominio.com',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers'      => ['*'],
    'exposed_headers'      => ['X-RateLimit-Limit', 'X-RateLimit-Remaining', 'Retry-After'],
    'max_age'              => 86400,  // 24h preflight cache
    'supports_credentials' => true,   // para cookies (Sanctum SPA)
];
```

---

## Caching de Respuestas HTTP

```php
// Cache headers para endpoints que cambian poco
class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Cache::remember('products:active', 300, fn() =>
            Product::active()->with('category')->get()
        );

        return response()
            ->json(['data' => ProductResource::collection($products)])
            ->header('Cache-Control', 'public, max-age=300')
            ->header('ETag', md5($products->toJson()));
    }

    public function show(Product $product): JsonResponse
    {
        $etag = md5($product->updated_at . $product->id);

        // Responder 304 si el cliente tiene la versión actual
        if (request()->header('If-None-Match') === $etag) {
            return response()->json(null, 304);
        }

        return response()
            ->json(['data' => ProductResource::make($product)])
            ->header('ETag', $etag)
            ->header('Cache-Control', 'private, max-age=60');
    }
}
```
