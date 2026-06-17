# Autenticación y Seguridad de APIs

## Estrategias de Autenticación — Cuándo Usar Cada Una

```
Laravel Sanctum — tokens de API + SPA auth con cookies
  ✅ SPA (Next.js, Vue, Nuxt) en mismo dominio o subdominio
  ✅ Apps mobile con tokens personales
  ✅ La mayoría de proyectos web modernos
  ❌ OAuth2 para terceros (usar Passport)

Laravel Passport — OAuth2 server completo
  ✅ API pública donde terceros necesitan autenticar usuarios
  ✅ "Login with MyApp" para otras aplicaciones
  ✅ Client credentials (machine-to-machine)
  ❌ SPA propio (Sanctum es más simple)

JWT con tymon/jwt-auth
  ✅ APIs stateless que no usan Laravel sessions
  ✅ Microservicios que comparten tokens
  ⚠️  Manejo de revocación más complejo que Sanctum
  ❌ No tiene ventaja sobre Sanctum para la mayoría de casos

Auth0 / Clerk / Cognito (managed auth)
  ✅ Cuando no quieres manejar auth tú mismo
  ✅ Social login complejo (múltiples proveedores)
  ✅ Empresas con SSO/SAML requerido
  ❌ Costo por usuario activo
```

---

## Sanctum — Implementación Completa

### Tokens de API (para mobile / terceros)

```php
// Crear token con habilidades (scopes)
class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        // Revocar tokens anteriores del mismo device (opcional)
        $user->tokens()->where('name', $request->device_name)->delete();

        // Crear token con habilidades específicas
        $token = $user->createToken(
            name: $request->device_name ?? 'API Token',
            abilities: $this->getTokenAbilities($user),
            expiresAt: now()->addDays(30),
        );

        return response()->json([
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at,
            'user'       => UserResource::make($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        // Revocar solo el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.'], 200);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        // Revocar todos los tokens del usuario
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'All sessions terminated.'], 200);
    }

    private function getTokenAbilities(User $user): array
    {
        return match($user->role) {
            'admin'   => ['*'],           // acceso total
            'manager' => ['read', 'write'],
            default   => ['read'],
        };
    }
}

// Proteger endpoints con habilidades específicas
Route::middleware(['auth:sanctum', 'ability:write'])->group(function () {
    Route::post('orders', [OrderController::class, 'store']);
    Route::patch('orders/{order}', [OrderController::class, 'update']);
});

// Verificar habilidad en controller
public function store(Request $request): JsonResponse
{
    if (!$request->user()->tokenCan('write')) {
        return response()->json(['message' => 'Insufficient token permissions.'], 403);
    }
    // ...
}
```

### SPA Auth con Cookies (mismo dominio)

```php
// config/sanctum.php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1,app.midominio.com')),

// El SPA primero obtiene la cookie CSRF
// GET /sanctum/csrf-cookie → Laravel setea XSRF-TOKEN cookie

// Luego hace login normal con credentials
// POST /login → seta session cookie httpOnly

// Frontend (Next.js / Vue)
await axios.get('/sanctum/csrf-cookie');
await axios.post('/login', { email, password });
// Axios incluye XSRF-TOKEN header automáticamente en requests siguientes
```

---

## Autorización con Policies

```php
// Policy por recurso — toda la lógica de autorización en un lugar
class OrderPolicy
{
    // ¿El usuario puede ver listado de órdenes?
    public function viewAny(User $user): bool
    {
        return true;  // cualquier usuario autenticado
    }

    // ¿El usuario puede ver esta orden específica?
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id
            || $user->isAdmin()
            || $user->isManager();
    }

    // ¿El usuario puede crear órdenes?
    public function create(User $user): bool
    {
        return $user->isVerified() && !$user->isSuspended();
    }

    // ¿El usuario puede actualizar esta orden?
    public function update(User $user, Order $order): bool
    {
        return $user->id === $order->user_id
            && $order->status === OrderStatus::Pending;
    }

    // ¿El usuario puede cancelar esta orden?
    public function cancel(User $user, Order $order): bool
    {
        return ($user->id === $order->user_id || $user->isAdmin())
            && in_array($order->status, [OrderStatus::Pending, OrderStatus::Processing]);
    }

    // Antes de otras verificaciones — admin bypasses all
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;  // super admin pasa todo
        }
        return null;  // continuar con verificación normal
    }
}

// Uso en Controller
$this->authorize('view', $order);       // 403 automático si falla
$this->authorize('cancel', $order);

// O manualmente
if ($request->user()->cannot('update', $order)) {
    return response()->json(['message' => 'Cannot update this order.'], 403);
}
```

---

## Seguridad — Checklist y Patrones

### Headers de Seguridad

```php
// Middleware para headers de seguridad en APIs
class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Solo en producción con HTTPS
        if (app()->isProduction()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
```

### Sanitización y Validación

```php
// NUNCA confiar en input del cliente
class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Whitelist de campos permitidos para ordenamiento
        $allowedSorts = ['name', 'price', 'created_at'];
        $sort = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';

        // Limitar per_page a rango razonable
        $perPage = min(max($request->integer('per_page', 20), 1), 100);

        // Sanitizar búsqueda (el ORM previene SQL injection, pero limpiar igual)
        $search = substr(strip_tags($request->string('search')), 0, 100);

        $products = Product::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy($sort)
            ->paginate($perPage);

        return ProductResource::collection($products)->response();
    }
}
```

### Mass Assignment — Siempre Explícito

```php
// NUNCA hacer esto en un controller de API
$user->update($request->all());      // ❌ cualquier campo puede ser modificado
$user->fill($request->all())->save(); // ❌ igual de peligroso

// SIEMPRE validar y extraer campos específicos
$user->update($request->validated()); // ✅ solo campos validados en FormRequest
$user->update($request->only(['name', 'phone', 'avatar'])); // ✅ whitelist explícita
```

### Prevenir Exposición de Datos

```php
// En modelos — campos nunca visibles en JSON
class User extends Model
{
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];
}

// En Resources — control granular por contexto
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->uuid,
            'name'  => $this->name,
            'email' => $this->email,

            // Solo admin ve datos sensibles
            'phone'      => $this->when($request->user()?->isAdmin(), $this->phone),
            'ip_address' => $this->when($request->user()?->isAdmin(), $this->last_ip),

            // Nunca exponer — ni con admin
            // 'password' — jamás
            // 'stripe_id' — usar métodos específicos
        ];
    }
}
```

### Protección contra Enumeración de Recursos

```php
// MAL: responder diferente si existe o no → permite enumerar usuarios
// POST /auth/forgot-password con email inexistente → 404
// POST /auth/forgot-password con email existente → 200
// Un atacante puede enumerar qué emails están registrados

// BIEN: siempre la misma respuesta
public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
{
    // Procesar en background — no revelar si el email existe
    ForgotPasswordJob::dispatch($request->email);

    // Siempre 200, siempre el mismo mensaje
    return response()->json([
        'message' => 'If that email address is in our database, you will receive a password reset email.',
    ]);
}
```

### Tokens — Almacenamiento y Rotación

```php
// Tokens de refresh — rotación automática
class TokenController extends Controller
{
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        // Revocar token actual
        $currentToken->delete();

        // Crear nuevo token con mismas habilidades
        $newToken = $user->createToken(
            name: $currentToken->name,
            abilities: $currentToken->abilities,
            expiresAt: now()->addDays(30),
        );

        return response()->json([
            'token'      => $newToken->plainTextToken,
            'expires_at' => $newToken->accessToken->expires_at,
        ]);
    }
}

// Limpiar tokens expirados — comando periódico (cron diario)
// php artisan sanctum:prune-expired --hours=24
```

---

## Webhook Security

```php
// Verificar firma de webhooks entrantes (Stripe, GitHub, etc.)
class WebhookController extends Controller
{
    public function stripe(Request $request): JsonResponse
    {
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $signature,
                $secret
            );
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Idempotencia — no procesar el mismo evento dos veces
        if (ProcessedWebhook::where('event_id', $event->id)->exists()) {
            return response()->json(['status' => 'already_processed']);
        }

        ProcessedWebhook::create(['event_id' => $event->id]);

        // Procesar en queue — responder a Stripe rápido
        StripeWebhookJob::dispatch($event);

        return response()->json(['status' => 'accepted'], 202);
    }
}
```
