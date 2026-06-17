# OWASP Top 10 — Vulnerabilidades Críticas

## A01 — Broken Access Control (la más común)

```
Problema: el usuario puede acceder a recursos que no le pertenecen.

Tipos:
- IDOR (Insecure Direct Object Reference): /orders/123 siendo de otro usuario
- Escalación de privilegios: usuario normal accede a endpoint de admin
- Missing auth: endpoint sin verificar autenticación
- Forzar navegación: acceder a rutas protegidas directamente por URL
```

```php
// ❌ MAL: confiar en el ID enviado por el cliente
public function show(Request $request, int $orderId): JsonResponse
{
    $order = Order::find($orderId);  // puede ser de otro usuario
    return OrderResource::make($order)->response();
}

// ✅ BIEN: scoping por usuario autenticado
public function show(Request $request, Order $order): JsonResponse
{
    // Policy verifica que el order pertenece al usuario
    $this->authorize('view', $order);
    return OrderResource::make($order)->response();
}

// ✅ BIEN: query scoping
public function show(Request $request, string $id): JsonResponse
{
    $order = Order::where('id', $id)
        ->where('user_id', $request->user()->id)  // scope al usuario actual
        ->firstOrFail();  // 404 si no existe o no pertenece al usuario

    return OrderResource::make($order)->response();
}
```

---

## A02 — Cryptographic Failures

```
Problema: datos sensibles sin encriptar o con encriptación débil.

Qué encriptar:
- Passwords: bcrypt/Argon2 (NUNCA MD5, SHA1, SHA256 solos)
- Datos sensibles en BD: AES-256-GCM
- Comunicación: TLS 1.2+ (HTTPS)
- Datos en reposo (backups, archivos): AES-256
```

```php
// ❌ MAL: MD5 o SHA para passwords
$hashed = md5($password);           // reversible con rainbow tables
$hashed = sha256($password);        // rápido = fácil de brute-force

// ✅ BIEN: bcrypt con cost factor ≥ 12
$hashed = bcrypt($password);        // Laravel default: cost 12
$hashed = Hash::make($password);    // equivalente

// Verificar
$isValid = Hash::check($plainPassword, $hashedPassword);

// ✅ BIEN: Argon2id (más resistente que bcrypt a GPU cracking)
$hashed = Hash::make($password, ['driver' => 'argon2id']);

// Encriptar datos sensibles en BD (no solo hashear)
// Usar cuando necesitas recuperar el valor original (ej: número de tarjeta enmascarado)
$encrypted = Crypt::encryptString($sensitiveData);
$decrypted = Crypt::decryptString($encrypted);

// Almacenar datos sensibles con encriptación de columna
// (usando spatie/laravel-encrypted-attributes o manualmente)
class User extends Model
{
    protected $casts = [
        'ssn' => EncryptedCast::class,  // auto-encripta al guardar, desencripta al leer
    ];
}
```

---

## A03 — Injection (SQL, NoSQL, Command)

```php
// ❌ MAL: SQL injection — permite extraer/modificar/destruir BD
$userId = $_GET['id'];  // "1; DROP TABLE users;"
$query = "SELECT * FROM users WHERE id = $userId";
DB::statement($query);  // ¡ejecuta el DROP TABLE!

// ❌ MAL: cualquier concatenación de input en SQL
$search = $request->input('search');
DB::select("SELECT * FROM products WHERE name LIKE '%$search%'");
// Input: "%' OR '1'='1" → devuelve todos los registros

// ✅ BIEN: Eloquent (prepared statements automáticos)
User::where('id', $request->id)->first();
Product::where('name', 'like', '%' . $request->search . '%')->get();

// ✅ BIEN: query builder con bindings
DB::select('SELECT * FROM users WHERE id = ?', [$request->id]);
DB::table('products')
    ->where('name', 'like', '%' . $request->search . '%')
    ->get();

// ❌ MAL: command injection
$filename = $request->input('filename');
exec("convert $filename output.pdf");  // Input: "image.jpg; rm -rf /"

// ✅ BIEN: escapar argumentos de shell
$filename = escapeshellarg($request->input('filename'));
exec("convert $filename output.pdf");

// ✅ MEJOR: usar librerías PHP en lugar de exec cuando sea posible
// Imagick, Intervention Image → sin shell
```

---

## A04 — Insecure Design

```
Problema: la arquitectura no considera seguridad desde el principio.

Patrones seguros por diseño:
- Principio de menor privilegio: cada componente con mínimos permisos
- Defense in depth: múltiples capas de seguridad
- Fail secure: en caso de error, denegar por defecto
- Separación de privilegios: admin vs usuario regular
```

```php
// ❌ MAL: fail open — en caso de error, permitir acceso
public function canAccess(User $user, Resource $resource): bool
{
    try {
        return $this->checkPermission($user, $resource);
    } catch (Exception $e) {
        return true;  // ❌ error → acceso permitido
    }
}

// ✅ BIEN: fail secure — en caso de error, denegar
public function canAccess(User $user, Resource $resource): bool
{
    try {
        return $this->checkPermission($user, $resource);
    } catch (Exception $e) {
        Log::error('Permission check failed', ['error' => $e->getMessage()]);
        return false;  // ✅ error → acceso denegado
    }
}

// ❌ MAL: lógica de seguridad mezclada con lógica de negocio
public function getOrder(int $id): Order
{
    $order = Order::find($id);
    // ¿Dónde está la verificación de que el usuario puede ver esto?
    return $order;
}

// ✅ BIEN: seguridad en capa dedicada (Policy)
public function getOrder(int $id): Order
{
    $order = Order::findOrFail($id);
    $this->authorize('view', $order);  // siempre explícito
    return $order;
}
```

---

## A05 — Security Misconfiguration

```bash
# Configuraciones peligrosas comunes:

# ❌ Debug mode en producción → stack traces al usuario
APP_DEBUG=true   # EN PRODUCCIÓN: APP_DEBUG=false

# ❌ Credenciales por defecto
# MySQL root sin password, Redis sin auth, MongoDB expuesto

# ❌ Directorios listables en nginx
# autoindex on;  → usuarios pueden ver todos los archivos

# ❌ Error messages que revelan tecnología
# "Uncaught Exception in PDO::__construct(): SQLSTATE[HY000] [2002]..."
# Revela: PHP + PDO + MySQL

# ✅ BIEN: errores genéricos en producción
# app/Exceptions/Handler.php
public function register(): void
{
    $this->renderable(function (\Throwable $e) {
        if (app()->isProduction() && request()->expectsJson()) {
            $id = Str::uuid();
            Log::error('Unhandled exception', ['id' => $id, 'error' => $e]);
            return response()->json(['message' => 'Server error', 'id' => $id], 500);
        }
    });
}
```

---

## A06 — Vulnerable and Outdated Components

```bash
# Auditar dependencias
npm audit                           # Node.js
npm audit fix                       # auto-fix si hay fix disponible
npm audit --audit-level=high        # solo errores graves

composer audit                      # PHP/Laravel
pip-audit                           # Python

# Automatizar con Dependabot
# .github/dependabot.yml
version: 2
updates:
  - package-ecosystem: "npm"
    directory: "/"
    schedule:
      interval: "weekly"
    open-pull-requests-limit: 5

  - package-ecosystem: "composer"
    directory: "/"
    schedule:
      interval: "weekly"

# Snyk — análisis más profundo
npm install -g snyk
snyk test
snyk monitor  # monitoreo continuo en CI
```

---

## A07 — Identification and Authentication Failures

```php
// ❌ MAL: sin rate limiting en login → brute force posible
Route::post('/login', [AuthController::class, 'login']);

// ✅ BIEN: rate limiting estricto en auth
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,15');  // 5 intentos cada 15 minutos

// ❌ MAL: mensaje de error que revela si el email existe
if (!$user) {
    return response()->json(['error' => 'Email not found'], 404);  // revela info
}
if (!Hash::check($password, $user->password)) {
    return response()->json(['error' => 'Wrong password'], 401);   // revela info
}

// ✅ BIEN: mensaje genérico
if (!$user || !Hash::check($password, $user->password)) {
    return response()->json(['error' => 'Invalid credentials'], 401);
}

// Timing attack prevention — usar hash_equals o Hash::check
// Hash::check usa timing-safe comparison internamente
// No usar === para comparar hashes

// Invalidar sesiones en logout
public function logout(Request $request): JsonResponse
{
    $request->user()->currentAccessToken()->delete();  // token actual
    // O todos los tokens:
    // $request->user()->tokens()->delete();
    return response()->json(['message' => 'Logged out']);
}

// Detección de credenciales comprometidas
// Integrar con HaveIBeenPwned API durante registro/cambio de password
public function checkPasswordBreached(string $password): bool
{
    $sha1 = strtoupper(sha1($password));
    $prefix = substr($sha1, 0, 5);
    $suffix = substr($sha1, 5);

    $response = Http::get("https://api.pwnedpasswords.com/range/$prefix");
    return str_contains($response->body(), $suffix);
}
```

---

## A08 — Software and Data Integrity Failures

```php
// ❌ MAL: deserializar datos del usuario sin validar
$data = unserialize($request->input('data'));  // PHP object injection

// ✅ BIEN: usar JSON en lugar de serialize
$data = json_decode($request->input('data'), true);

// Verificar integridad de webhooks (ya cubierto en api-design)
// Siempre verificar firma HMAC de webhooks entrantes

// Supply chain — verificar checksums de paquetes
# composer verify (verifica hashes del lockfile)
composer install --verify  # verifica que los paquetes descargados coinciden con lockfile

# npm — usar package-lock.json (no ignorar en .gitignore)
npm ci  # instala EXACTAMENTE lo que hay en lockfile (en lugar de npm install)
```

---

## A09 — Security Logging and Monitoring Failures

```php
// Qué loguear (sin datos sensibles):
Log::info('User logged in', ['user_id' => $user->id, 'ip' => $request->ip()]);
Log::warning('Failed login attempt', ['email' => $request->email, 'ip' => $request->ip()]);
Log::error('Payment failed', ['order_id' => $order->id, 'error_code' => $e->getCode()]);
Log::critical('Admin access from unusual location', ['admin_id' => $user->id, 'ip' => $ip]);

// ❌ MAL: loguear datos sensibles
Log::info('Login', ['password' => $password]);           // password en logs!
Log::info('Payment', ['card_number' => $cardNumber]);    // PCI violation!
Log::debug('Request', ['headers' => $request->headers]); // puede incluir tokens

// Alertas automáticas para eventos críticos:
// - N intentos fallidos de login desde misma IP
// - Login desde país inusual para un usuario
// - Acceso a endpoints de admin fuera de horario
// - Export masivo de datos
// - Cambio de password/email de admin
```

---

## A10 — Server-Side Request Forgery (SSRF)

```php
// ❌ MAL: hacer request a URL proporcionada por el usuario
public function fetchPreview(Request $request): JsonResponse
{
    $url = $request->input('url');
    $response = Http::get($url);  // puede ser http://169.254.169.254 (AWS metadata!)
    return response()->json(['content' => $response->body()]);
}

// ✅ BIEN: whitelist de dominios permitidos
public function fetchPreview(Request $request): JsonResponse
{
    $url = $request->input('url');
    $parsed = parse_url($url);

    $allowedHosts = ['myapp.com', 'cdn.myapp.com', 'api.partner.com'];

    if (!in_array($parsed['host'], $allowedHosts)) {
        return response()->json(['error' => 'URL not allowed'], 403);
    }

    // Bloquear IPs privadas y localhost
    $ip = gethostbyname($parsed['host']);
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return response()->json(['error' => 'Private addresses not allowed'], 403);
    }

    $response = Http::get($url);
    return response()->json(['title' => $this->extractTitle($response->body())]);
}
```
