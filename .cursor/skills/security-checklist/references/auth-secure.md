# Auth Segura — Tokens, Passwords y Sesiones

## Password Hashing — Lo Básico No Negociable

```php
// Algoritmos recomendados (2024):
// Argon2id: el más moderno, resistente a GPU/ASIC cracking
// bcrypt:   ampliamente soportado, cost factor ajustable
// scrypt:   memoria-intensivo, buena alternativa

// ❌ NUNCA para passwords:
// MD5, SHA1, SHA256, SHA512 solos (rápidos = fáciles de brute-force)
// Encriptación reversible (necesitas verificar, no recuperar)

// Laravel — bcrypt (default) con cost 12
$hash = Hash::make($password);                        // bcrypt, cost 12
$hash = Hash::make($password, ['rounds' => 14]);      // cost más alto = más lento = más seguro

// Argon2id (más resistente)
$hash = Hash::make($password, [
    'driver' => 'argon2id',
    'memory' => 65536,    // 64MB de memoria
    'threads' => 2,
    'time'    => 4,       // 4 iteraciones
]);

// Verificar y re-hashear si el cost factor cambió
if (Hash::check($password, $user->password_hash)) {
    if (Hash::needsRehash($user->password_hash)) {
        $user->update(['password_hash' => Hash::make($password)]);
    }
    // autenticar...
}

// Node.js — bcrypt
import bcrypt from 'bcryptjs';

const saltRounds = 12;
const hash = await bcrypt.hash(password, saltRounds);
const isValid = await bcrypt.compare(plainPassword, hash);
```

---

## JWT — Patrones Seguros

```typescript
// ❌ MAL: algoritmo none (sin firma)
const token = jwt.sign(payload, '', { algorithm: 'none' });

// ❌ MAL: secret débil o en código
const token = jwt.sign(payload, 'secret');
const token = jwt.sign(payload, 'password123');

// ✅ BIEN: secret fuerte de variable de entorno
const token = jwt.sign(payload, process.env.JWT_SECRET!, {
  algorithm: 'HS256',
  expiresIn: '15m',      // access token corto
  issuer: 'myapp',
  audience: 'myapp-users',
});

// Generar secret fuerte:
// node -e "console.log(require('crypto').randomBytes(64).toString('hex'))"

// ✅ MEJOR: RS256 con par de llaves (asimétrico)
// Verificar tokens sin exponer el private key
const token = jwt.sign(payload, privateKey, {
  algorithm: 'RS256',
  expiresIn: '15m',
});

// Verificar (en otros servicios, solo necesitan la public key)
const decoded = jwt.verify(token, publicKey, {
  algorithms: ['RS256'],   // whitelist de algoritmos
  issuer: 'myapp',
  audience: 'myapp-users',
});

// Claims que siempre incluir
const payload = {
  sub: user.id,          // subject — quién es el usuario
  iat: Math.floor(Date.now() / 1000),  // issued at
  exp: ...,              // expiry (manejado por expiresIn)
  jti: crypto.randomUUID(), // JWT ID único — para revocación
  // NO incluir datos sensibles (email visible en base64)
};
```

---

## Refresh Token Pattern — Seguro

```typescript
// Patrón: access token corto + refresh token largo

interface TokenPair {
  accessToken: string;   // 15 min, en memoria
  refreshToken: string;  // 30 días, en httpOnly cookie o SecureStore
}

// Rotation: cada refresh genera un nuevo par y revoca el anterior
async function refreshTokens(refreshToken: string): Promise<TokenPair> {
  // 1. Verificar que el refresh token es válido
  const payload = jwt.verify(refreshToken, process.env.JWT_REFRESH_SECRET!);

  // 2. Verificar que no fue revocado (guardarlo en Redis)
  const isRevoked = await redis.exists(`revoked:${payload.jti}`);
  if (isRevoked) throw new UnauthorizedException('Token revoked');

  // 3. Revocar el token usado
  const ttl = payload.exp - Math.floor(Date.now() / 1000);
  await redis.setex(`revoked:${payload.jti}`, ttl, '1');

  // 4. Generar nuevo par
  const user = await usersRepository.findById(payload.sub);
  return generateTokenPair(user);
}

// httpOnly cookies para web — no accesibles por JavaScript (XSS protection)
res.cookie('refreshToken', refreshToken, {
  httpOnly: true,       // no accesible por document.cookie
  secure: true,         // solo HTTPS
  sameSite: 'strict',   // CSRF protection
  maxAge: 30 * 24 * 60 * 60 * 1000,  // 30 días en ms
  path: '/api/auth',    // solo enviado en rutas de auth
});

// Access token en memoria (no localStorage, no sessionStorage)
// localStorage es vulnerable a XSS — cualquier script puede leerlo
let accessToken: string | null = null;  // en memoria, se pierde al refrescar
```

---

## 2FA — Two Factor Authentication

```php
// Laravel con pragmarx/google2fa
// O con spatie/laravel-google2fa

// Setup para un usuario
$google2fa = app(Google2FA::class);
$secretKey = $google2fa->generateSecretKey();

// Guardar en BD (encriptado)
$user->update([
    'two_factor_secret' => encrypt($secretKey),
    'two_factor_enabled' => false,  // requiere verificación para activar
]);

// URL para QR code
$qrUrl = $google2fa->getQRCodeUrl(
    config('app.name'),
    $user->email,
    $secretKey
);

// Verificar código durante login
public function verify2FA(Request $request): JsonResponse
{
    $user = $request->user();
    $secret = decrypt($user->two_factor_secret);
    $code = $request->input('code');

    $google2fa = app(Google2FA::class);
    $isValid = $google2fa->verifyKey($secret, $code);

    if (!$isValid) {
        return response()->json(['error' => 'Invalid 2FA code'], 422);
    }

    // Generar tokens reales después de 2FA exitoso
    $token = $user->createToken('api')->plainTextToken;
    return response()->json(['token' => $token]);
}

// Códigos de backup — para cuando se pierde el dispositivo
public function generateBackupCodes(User $user): array
{
    $codes = collect(range(1, 8))->map(fn() =>
        strtoupper(Str::random(4) . '-' . Str::random(4))
    )->toArray();

    // Guardar hashes (no los codes en plano)
    $user->update([
        'two_factor_backup_codes' => json_encode(
            array_map(fn($code) => Hash::make($code), $codes)
        ),
    ]);

    return $codes;  // mostrar solo una vez al usuario
}
```

---

## Session Security

```php
// config/session.php — Laravel
'secure'    => env('SESSION_SECURE_COOKIE', true),   // solo HTTPS
'http_only' => true,                                  // no accesible por JS
'same_site' => 'lax',                                 // CSRF protection
'driver'    => 'redis',                               // no file en producción multi-server
'lifetime'  => 120,                                   // 2 horas
'expire_on_close' => false,

// Regenerar session ID en login (session fixation prevention)
Auth::login($user);
$request->session()->regenerate();  // nuevo session ID post-login

// Invalidar session en logout
Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();

// Node.js — express-session seguro
app.use(session({
  secret: process.env.SESSION_SECRET!,
  resave: false,
  saveUninitialized: false,
  cookie: {
    secure: process.env.NODE_ENV === 'production',
    httpOnly: true,
    sameSite: 'strict',
    maxAge: 1000 * 60 * 60 * 2,  // 2 horas
  },
  store: new RedisStore({ client: redisClient }),  // no memoria (no escala)
  name: '__sess',  // no revelar que usas express-session
}));
```
