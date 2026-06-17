# Secrets, Variables de Entorno y Auditoría

## Secrets Management — Nunca en el Código

```bash
# ❌ MAL: secrets en código fuente
DB_PASSWORD = "mi_password_super_seguro"
STRIPE_SECRET = "sk_live_abc123..."
JWT_SECRET = "mysecretkey"

# ❌ MAL: secrets en git (aunque luego se borren)
# Una vez commiteado, está en el history para siempre

# Detectar secrets expuestos en git
git log --all --full-history -- "*.env"
git log --all --full-history -- "*.key"

# Herramienta: trufflehog o gitleaks
trufflehog git file:///path/to/repo
gitleaks detect --source . --verbose

# Si un secret fue expuesto: rotar INMEDIATAMENTE (antes de limpiarlo del historial)
# Limpiar el historial con BFG o git filter-branch es útil
# pero la rotación es lo urgente
```

---

## .gitignore Completo para Secrets

```gitignore
# Variables de entorno
.env
.env.*
!.env.example
!.env.test.example

# Keys y certificados
*.key
*.pem
*.p12
*.pfx
*.crt
*.cer
id_rsa
id_ed25519
*.pub  # public keys pueden ser menos sensibles, pero por precaución

# Credenciales de servicios
google-service-account.json
firebase-adminsdk-*.json
gcloud-key.json
aws-credentials
.aws/credentials

# Herramientas locales con credentials
.netrc
.npmrc  # si tiene tokens
.pypirc
```

---

## Variables de Entorno — Validación al Arrancar

```typescript
// lib/env.ts — validar todas las env vars al iniciar la app
import { z } from 'zod';

const envSchema = z.object({
  NODE_ENV: z.enum(['development', 'staging', 'production', 'test']),

  // BD
  DATABASE_URL: z.string().url(),

  // Auth
  JWT_SECRET: z.string().min(32, 'JWT_SECRET must be at least 32 chars'),
  JWT_REFRESH_SECRET: z.string().min(32),

  // Servicios externos
  STRIPE_SECRET_KEY: z.string().startsWith('sk_'),
  STRIPE_WEBHOOK_SECRET: z.string().startsWith('whsec_'),

  // Opcionales con defaults
  PORT: z.coerce.number().default(3000),
  LOG_LEVEL: z.enum(['error', 'warn', 'info', 'debug']).default('info'),
});

// Fallar inmediatamente si falta algo crítico
const env = (() => {
  const result = envSchema.safeParse(process.env);

  if (!result.success) {
    console.error('❌ Invalid environment variables:');
    result.error.issues.forEach(issue => {
      console.error(`  ${issue.path.join('.')}: ${issue.message}`);
    });
    process.exit(1);
  }

  return result.data;
})();

export default env;
```

```php
// Laravel — validar env vars al arrancar (con Validator en AppServiceProvider)
public function boot(): void
{
    if (!app()->isLocal()) {
        $required = ['APP_KEY', 'DB_PASSWORD', 'STRIPE_SECRET', 'JWT_SECRET'];

        foreach ($required as $key) {
            if (empty(env($key))) {
                throw new RuntimeException("Missing required environment variable: $key");
            }
        }

        // Verificar que no se use configuración de desarrollo en producción
        if (env('APP_DEBUG') === 'true') {
            throw new RuntimeException('APP_DEBUG must be false in production');
        }
    }
}
```

---

## Secret Rotation — Procedimiento

```bash
# Procedimiento cuando un secret es expuesto:

# 1. INMEDIATAMENTE: revocar el secret expuesto
#    - En el proveedor (Stripe dashboard, AWS console, etc.)
#    - No esperar a terminar el proceso de limpieza

# 2. Generar nuevo secret
openssl rand -base64 48  # para JWT_SECRET
openssl rand -hex 32     # para API keys

# 3. Actualizar en todos los ambientes
#    - Variables de entorno del servidor (o Vault)
#    - GitHub Secrets / CI/CD
#    - Servicios que usan el secret

# 4. Deploy con nuevo secret

# 5. Verificar que todo funciona

# 6. Limpiar historial de git si el secret fue commiteado
git filter-branch --force --index-filter \
  'git rm --cached --ignore-unmatch .env' \
  --prune-empty --tag-name-filter cat -- --all

# O mejor: usar BFG Repo-Cleaner (más rápido)
# java -jar bfg.jar --delete-files .env
# java -jar bfg.jar --replace-text secrets.txt

# 7. Forzar push y notificar a todos los colaboradores
git push --force --all
git push --force --tags
# Todos deben hacer fresh clone o git reset --hard

# 8. Post-mortem: cómo pasó, cómo evitarlo
```

---

## Auditoría de Seguridad

> El workflow de auditoría completo (escaneo del repo, checklist por categoría
> OWASP, clasificación de hallazgos Critical/High/Medium/Low y plantilla de
> informe con IDs) se movió a **`audit.md`** (misma carpeta).

---

## GDPR / Privacidad — Para Apps con Usuarios EU

```php
// Datos personales que requieren protección especial:
// - Nombre, email, teléfono
// - IP address (es dato personal en EU)
// - Cookies de tracking
// - Datos de salud, religión, política (especialmente sensibles)

// Derecho al olvido — borrado de datos del usuario
public function deleteUserData(User $user): void
{
    // Anonimizar en lugar de borrar (para mantener integridad de BD)
    $user->update([
        'name'       => 'Deleted User',
        'email'      => "deleted+{$user->id}@myapp.com",
        'phone'      => null,
        'avatar_url' => null,
        'deleted_at' => now(),
    ]);

    // Borrar datos que no necesitan anonimización
    $user->addresses()->delete();
    $user->paymentMethods()->delete();
    $user->sessions()->delete();

    // Loguear la acción
    Log::info('User data deleted (GDPR)', ['user_id' => $user->id]);
}

// Exportar datos del usuario (portabilidad)
public function exportUserData(User $user): array
{
    return [
        'profile' => UserResource::make($user)->toArray(request()),
        'orders'  => OrderResource::collection($user->orders)->toArray(request()),
        'addresses' => AddressResource::collection($user->addresses)->toArray(request()),
    ];
}
```
