# Headers de Seguridad, CORS y CSP

## Headers Esenciales

```nginx
# nginx — headers de seguridad completos

server {
    # HSTS — forzar HTTPS para futuros requests
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    # preload: incluir en Chrome HSTS preload list (requiere https://hstspreload.org/)

    # Evitar clickjacking (iframes no autorizados)
    add_header X-Frame-Options "SAMEORIGIN" always;
    # DENY: nunca en iframe
    # SAMEORIGIN: solo en iframe del mismo dominio

    # Evitar MIME sniffing (el browser no intenta "adivinar" el content-type)
    add_header X-Content-Type-Options "nosniff" always;

    # XSS Filter (browsers antiguos)
    add_header X-XSS-Protection "1; mode=block" always;

    # Controlar qué información del referrer se envía
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Controlar APIs del browser disponibles
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=(), payment=(self)" always;

    # CSP — ver sección abajo
    add_header Content-Security-Policy "..." always;

    # Ocultar versión de nginx
    server_tokens off;
}

# Redirección HTTP → HTTPS
server {
    listen 80;
    server_name myapp.com www.myapp.com;
    return 301 https://$server_name$request_uri;
}
```

---

## Content Security Policy (CSP)

```
CSP previene XSS al controlar de dónde puede cargar recursos el browser.
Si un atacante inyecta <script>, CSP bloquea la ejecución.

Directivas principales:
- default-src: fallback para todo
- script-src: de dónde puede cargar JS
- style-src: de dónde puede cargar CSS
- img-src: de dónde puede cargar imágenes
- connect-src: a dónde puede hacer fetch/XHR
- font-src: de dónde puede cargar fuentes
- frame-src: qué puede cargar en iframes
```

```nginx
# CSP para SPA (React/Vue/Angular) con API propia
add_header Content-Security-Policy "
  default-src 'self';
  script-src 'self' 'nonce-{NONCE}';
  style-src 'self' 'unsafe-inline';
  img-src 'self' data: https://cdn.myapp.com;
  font-src 'self';
  connect-src 'self' https://api.myapp.com wss://api.myapp.com;
  frame-ancestors 'none';
  base-uri 'self';
  form-action 'self';
" always;

# CSP para sitio con Google Fonts y GTM
add_header Content-Security-Policy "
  default-src 'self';
  script-src 'self' https://www.googletagmanager.com 'nonce-{NONCE}';
  style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
  font-src 'self' https://fonts.gstatic.com;
  img-src 'self' data: https: blob:;
  connect-src 'self' https://api.myapp.com https://www.google-analytics.com;
  frame-ancestors 'none';
" always;
```

```php
// Laravel — CSP dinámico con nonce (para inline scripts)
// spatie/laravel-csp

// config/csp.php
'policy' => \App\Http\Policies\CSPPolicy::class,

// app/Http/Policies/CSPPolicy.php
class CSPPolicy extends Policy
{
    public function configure(): void
    {
        $this
            ->addDirective(Directive::DEFAULT, Keyword::SELF)
            ->addDirective(Directive::SCRIPT, Keyword::SELF)
            ->addDirective(Directive::SCRIPT, Keyword::NONCE)  // nonce generado automáticamente
            ->addDirective(Directive::STYLE, Keyword::SELF)
            ->addDirective(Directive::STYLE, Keyword::UNSAFE_INLINE)
            ->addDirective(Directive::IMG, Keyword::SELF)
            ->addDirective(Directive::IMG, 'data:')
            ->addDirective(Directive::CONNECT, Keyword::SELF)
            ->addDirective(Directive::FRAME_ANCESTORS, Keyword::NONE);
    }
}

// En Blade — usar nonce generado
<script nonce="{{ csp_nonce() }}">
    // este script inline es permitido por CSP
</script>
```

---

## CORS — Cross-Origin Resource Sharing

```
CORS controla qué dominios pueden hacer requests a tu API.
Sin CORS restrictivo → cualquier sitio puede hacer requests usando las cookies del usuario.

Regla:
- API pública: CORS amplio pero con métodos limitados
- API interna (solo frontend propio): CORS restrictivo a dominios conocidos
- API mixta: whitelist de dominios de confianza
```

```php
// config/cors.php — Laravel
return [
    'paths'            => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods'  => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // ❌ MAL: '*' permite cualquier origen
    // 'allowed_origins' => ['*'],

    // ✅ BIEN: whitelist explícita
    'allowed_origins'  => [
        env('FRONTEND_URL', 'http://localhost:3000'),
        'https://app.myapp.com',
        'https://admin.myapp.com',
    ],

    // Para APIs públicas donde no hay credenciales
    // 'allowed_origins' => ['*'],
    // 'supports_credentials' => false,  // sin cookies

    'allowed_headers'  => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-TOKEN'],

    'exposed_headers'  => ['X-RateLimit-Limit', 'X-RateLimit-Remaining', 'Retry-After'],

    'max_age'          => 7200,   // caché de preflight (OPTIONS) por 2 horas

    'supports_credentials' => true,  // necesario para cookies (Sanctum SPA)
];

// Node.js — cors package
import cors from 'cors';

const allowedOrigins = [
  process.env.FRONTEND_URL!,
  'https://app.myapp.com',
];

app.use(cors({
  origin: (origin, callback) => {
    if (!origin || allowedOrigins.includes(origin)) {
      callback(null, true);
    } else {
      callback(new Error(`CORS: origin ${origin} not allowed`));
    }
  },
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
  allowedHeaders: ['Content-Type', 'Authorization'],
  maxAge: 7200,
}));
```

---

## CSRF Protection

```
CSRF (Cross-Site Request Forgery): un sitio malicioso hace un request
a tu app usando las cookies del usuario logueado.

Protecciones:
1. SameSite cookies (moderno, suficiente para la mayoría)
2. CSRF token (para formularios tradicionales)
3. Custom header (para SPAs)
```

```php
// Laravel — CSRF token automático en formularios Blade
// El middleware VerifyCsrfToken verifica el token en cada POST/PUT/DELETE

// En formularios:
<form method="POST" action="/orders">
    @csrf  {{-- genera <input type="hidden" name="_token" value="..."> --}}
    <!-- ... -->
</form>

// En SPA con Sanctum (custom header approach):
// El browser envía el XSRF-TOKEN cookie automáticamente como header
// Laravel verifica el header X-XSRF-TOKEN

// Excluir rutas de CSRF (webhooks de terceros)
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'api/webhooks/*',  // webhooks externos tienen su propio sistema de auth (HMAC)
];
```

```typescript
// Axios — enviar CSRF token automáticamente
import axios from 'axios';

// Sanctum: Axios incluye X-XSRF-TOKEN header automáticamente
// si el servidor envía la cookie XSRF-TOKEN

// Custom header para APIs sin cookies
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
```

---

## Rate Limiting — Protección Contra Abuso

```php
// Laravel — rate limiting por ruta
// bootstrap/app.php o RouteServiceProvider

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinutes(15, 5)  // 5 intentos por 15 min
        ->by($request->ip())
        ->response(function () {
            return response()->json([
                'error'       => 'Too many attempts. Please wait 15 minutes.',
                'retry_after' => 900,
            ], 429);
        });
});

RateLimiter::for('api', function (Request $request) {
    return [
        Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()),
        // Burst limit para usuarios autenticados
        $request->user()
            ? Limit::perSecond(5)->by($request->user()->id)
            : Limit::perSecond(2)->by($request->ip()),
    ];
});

// Límites más estrictos para operaciones sensibles
RateLimiter::for('password-reset', function (Request $request) {
    return Limit::perHour(3)->by($request->input('email'));
});

RateLimiter::for('exports', function (Request $request) {
    return Limit::perDay(10)->by($request->user()->id);
});

// En rutas
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

Route::middleware('throttle:api')->group(function () {
    Route::apiResource('orders', OrderController::class);
});
```
