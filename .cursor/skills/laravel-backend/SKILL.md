---
name: laravel-backend
description: >
  Guía el desarrollo backend con Laravel en profundidad: arquitectura, Eloquent
  avanzado, Filament admin, Octane, queues, eventos, testing con Pest, CI/CD y
  patrones de producción. Usar cuando el usuario trabaje con Laravel o mencione:
  Eloquent, Artisan, Blade, Livewire, Inertia, Filament, Horizon, Reverb, Sanctum,
  Passport, queues, jobs, events, listeners, observers, policies, gates, service
  providers, facades, helpers, o cuando diga "cómo hago X en Laravel", "cómo
  estructuro un proyecto Laravel", "Laravel está lento", "cómo testeo en Laravel",
  "cómo hago un admin en Laravel", o cualquier variante. También aplica en proyectos
  PHP sin framework definido donde Laravel sea la elección a evaluar.
---

# Laravel Backend Skill

Guía de producción para sistemas Laravel: patrones, performance y arquitectura real.

**Eloquent avanzado → `references/eloquent.md`**
**Queues, Jobs, Events y Commands → `references/queues-events.md`**
**Filament Admin Panel → `references/filament.md`**
**Testing con Pest → `references/testing.md`**
**Performance y Producción → `references/performance.md`**
**Versiones del stack → `references/stack-versions.md`**

---

## Memoria

**Al iniciar** (solo si existen; no recargar lo ya en el chat):

1. `.cursor/project-memory.md` — decisiones, gates, punteros al repo.
2. Fuentes que indique project-memory (p. ej. `context.md`, `AGENTS.md`).
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes` si hay entradas.

**Durante la tarea:** leer cada `references/*.md` solo cuando el protocolo lo indique.

**Al cerrar:** decisiones del proyecto → project-memory; gaps de skill → `LEARNINGS.md`; entregable → archivo en repo (`docs/…`).

**Graphify:** solo si project-memory tiene `Graphify: enabled` o el usuario lo pide → `graphify-integration`.

---

## Protocolo de ejecución — Feature Laravel

8 pasos para implementar cualquier feature de backend:

0. **Memoria** — leer `.cursor/project-memory.md`, `composer.json` / `package.json` y aplicar gates locales documentados allí.
1. **Ruta** — definir la ruta versionada en `routes/api/v1.php` (o web). Gate:
   `php artisan route:list --path=<recurso>` muestra la ruta nueva.
2. **FormRequest** — validación de formato y autorización
   (`php artisan make:request`). Las reglas de negocio NO van aquí.
3. **Action/Service** — lógica de negocio en una clase de un solo propósito
   (patrón Actions de abajo). Transacciones DB donde haya múltiples escrituras.
4. **Model + migración** — si hay datos nuevos: modelo con `$fillable`/`$casts`
   y migración reversible. Leer `references/eloquent.md` para relaciones, scopes
   y prevención de N+1. Gate: `php artisan migrate` corre sin errores.
5. **Resource/respuesta** — API Resource para el output (nunca el modelo crudo);
   side-effects async via events/jobs (leer `references/queues-events.md`).
6. **Feature test con Pest** — happy path + 422 + 401/403 (leer
   `references/testing.md`). Gate: `php artisan test --filter=<FeatureTest>` pasa.
7. **Checklist de deploy** — repasar el Checklist Laravel Producción de este
   archivo; si toca performance u Octane, leer `references/performance.md`.
8. **Validación y cierre** — ejecutar `## Validación`; actualizar project-memory si
   hubo decisión de arquitectura; registrar gaps en `LEARNINGS.md`.

## Cuándo leer qué reference

| Síntoma / tarea | Reference |
|-----------------|-----------|
| N+1, queries lentas de Eloquent, relaciones complejas | `references/eloquent.md` |
| Job fallido, reintentos, eventos, listeners, scheduling | `references/queues-events.md` |
| Lentitud general, Octane, caché, deploy | `references/performance.md` |
| Escribir o arreglar tests | `references/testing.md` |
| Panel de administración, CRUD interno, dashboard | `references/filament.md` |

---

## Defaults si falta contexto

Asumir y **declarar** estos supuestos (máximo 1 pregunta al usuario, solo si es bloqueante):

| Falta | Default asumido |
|-------|-----------------|
| Versión | **Última Laravel estable** + PHP requerido por `composer.json`; si no hay repo, ver `references/stack-versions.md` y declarar versión |
| BD | PostgreSQL (`DB_CONNECTION=pgsql`) |
| Auth API | Sanctum |
| Queues/cache/sessions | Redis |
| Testing | Pest (no PHPUnit puro) |
| Admin | Filament (última compatible con Laravel del proyecto) |
| Lógica de negocio | Actions de un solo propósito; Services solo si orquestan varias |
| Estructura | La de abajo (por defecto Laravel + Services/Actions/DTOs) |

---

## Estructura de Proyecto Recomendada

Laravel por defecto mezcla todo en `app/`. Para proyectos medianos-grandes,
organizar por dominio de negocio, no por tipo técnico.

```
app/
├── Console/
│   └── Commands/           # Artisan commands
│
├── Exceptions/
│   └── Domain/             # Excepciones de negocio tipadas
│       ├── InsufficientStockException.php
│       └── OrderLimitExceededException.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/         # Controladores API separados por versión
│   │   └── Web/            # Controladores web (Inertia/Blade)
│   ├── Middleware/
│   ├── Requests/           # Form Requests por módulo
│   └── Resources/          # API Resources
│
├── Models/                 # Eloquent models
│
├── Services/               # Lógica de negocio
│   ├── OrderService.php
│   └── PaymentService.php
│
├── Repositories/           # Acceso a datos
│   ├── Contracts/          # Interfaces
│   └── Eloquent/           # Implementaciones
│
├── DTOs/                   # Data Transfer Objects
│
├── Actions/                # Acciones de un solo propósito (alternativa a Services)
│   └── Orders/
│       ├── CreateOrderAction.php
│       ├── CancelOrderAction.php
│       └── RefundOrderAction.php
│
├── Events/                 # Domain events
├── Listeners/              # Event handlers
├── Jobs/                   # Queue jobs
├── Notifications/          # Notificaciones multi-canal
├── Mail/                   # Mailables
├── Policies/               # Autorización por modelo
└── Providers/              # Service Providers
    ├── AppServiceProvider.php
    └── RepositoryServiceProvider.php

database/
├── factories/
├── migrations/
└── seeders/

tests/
├── Feature/                # Tests de integración (HTTP + BD)
│   ├── Api/
│   └── Web/
└── Unit/                   # Tests unitarios (sin BD, sin HTTP)
    ├── Services/
    └── Models/
```

---

## Service Providers — El Bootstrap del Sistema

```php
// AppServiceProvider — configuración global
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings de interfaces a implementaciones
        $this->app->bind(
            OrderRepositoryInterface::class,
            EloquentOrderRepository::class
        );

        // Singleton para servicios que deben ser una sola instancia
        $this->app->singleton(CurrencyConverter::class, function () {
            return new CurrencyConverter(config('services.exchange_rate.api_key'));
        });
    }

    public function boot(): void
    {
        // Configuración de modelos
        Model::preventLazyLoading(!app()->isProduction());
        // Lanza excepción en dev si hay lazy loading (N+1 prevención)
        // En producción solo loguea el warning

        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());
        // Lanza excepción si se intenta asignar campo no en $fillable

        // Macros globales
        Response::macro('success', fn($data, $status = 200) =>
            response()->json(['data' => $data], $status)
        );

        // Paginación personalizada
        Paginator::useBootstrapFive();

        // Strict mode en desarrollo
        if (app()->isLocal()) {
            DB::listen(function ($query) {
                if ($query->time > 1000) { // queries > 1 segundo
                    Log::warning('Slow query detected', [
                        'sql'  => $query->sql,
                        'time' => $query->time,
                    ]);
                }
            });
        }
    }
}
```

---

## Actions Pattern — Alternativa a Services para Operaciones Únicas

Para casos de uso simples, Actions son más limpias que Services grandes.

```php
// Una clase, una responsabilidad, una acción
class CreateOrderAction
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly InventoryService $inventory,
    ) {}

    public function execute(CreateOrderDTO $dto): Order
    {
        $this->inventory->validateAndReserve($dto->items);

        $order = DB::transaction(function () use ($dto) {
            $order = $this->orders->create($dto);
            $this->inventory->commitReservation($order);
            return $order;
        });

        event(new OrderCreated($order));
        return $order;
    }
}

// En el Controller — inyección directa
class OrderController extends Controller
{
    public function store(
        CreateOrderRequest $request,
        CreateOrderAction $action   // Laravel inyecta automáticamente
    ): JsonResponse {
        $order = $action->execute(
            CreateOrderDTO::fromRequest($request)
        );
        return OrderResource::make($order)->response()->setStatusCode(201);
    }
}
```

---

## Facades vs Dependency Injection

```php
// Facades — convenientes pero ocultan dependencias
Cache::remember('key', 300, fn() => ...);
DB::transaction(fn() => ...);
Log::info('message');

// DI explícita — testeable, visible, recomendada en Services
class OrderService
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
    ) {}
}

// Regla práctica:
// Controllers → Facades está bien (son thin de todas formas)
// Services, Actions, Jobs → DI explícita
// Tests → siempre mockear via DI, no facades (aunque Laravel lo permite)
```

---

## Configuración de Entornos

```php
// config/app.php — no hardcodear nunca
'debug' => (bool) env('APP_DEBUG', false),
'timezone' => env('APP_TIMEZONE', 'UTC'),

// Acceso tipado a configuración (mejor que env() directo en código)
// config('services.stripe.secret')  ✅
// env('STRIPE_SECRET')              ❌ (no cacheable)

// .env.example — siempre actualizado, en git, sin valores reales
APP_NAME=MyApp
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=myapp
DB_USERNAME=myapp
DB_PASSWORD=

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit          # Mailpit en desarrollo
MAIL_PORT=1025

STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
```

Artisan commands operacionales (batch, dry-run, scheduling) → `references/queues-events.md`.

---

## Helpers y Macros Globales

```php
// app/helpers.php — funciones globales del proyecto
if (!function_exists('money')) {
    function money(int $cents, string $currency = 'USD'): string
    {
        return '$' . number_format($cents / 100, 2) . ' ' . $currency;
    }
}

if (!function_exists('ulid')) {
    function ulid(): string
    {
        return (string) \Illuminate\Support\Str::ulid();
    }
}

// composer.json — autoload del archivo
"autoload": {
    "files": ["app/helpers.php"],
    "psr-4": { "App\\": "app/" }
}

// Collection Macros — extender colecciones de Laravel
Collection::macro('groupByDate', function (string $field): Collection {
    return $this->groupBy(fn($item) =>
        Carbon::parse(data_get($item, $field))->format('Y-m-d')
    );
});

// Uso
$ordersByDay = $orders->groupByDate('created_at');
```

---

## Checklist Laravel Producción

### Configuración
- [ ] `APP_DEBUG=false` en producción
- [ ] `APP_KEY` generado y secreto
- [ ] `config:cache` y `route:cache` en deploy
- [ ] Variables de entorno sensibles en vault/secrets manager, no en .env del servidor
- [ ] `QUEUE_CONNECTION=redis` (no sync en producción)
- [ ] `SESSION_DRIVER=redis` (no file en producción con múltiples servidores)

### Código
- [ ] `Model::preventLazyLoading()` activo en staging para detectar N+1
- [ ] Todos los jobs implementan `ShouldQueue` donde corresponde
- [ ] Retry logic en jobs que pueden fallar por servicios externos
- [ ] Logs con niveles correctos (no todo en `info`)
- [ ] Sin `dd()`, `dump()`, `var_dump()` en código

### Seguridad
- [ ] `php artisan key:generate` en setup inicial
- [ ] HTTPS forzado (`FORCE_HTTPS=true` o en middleware)
- [ ] Rate limiting en todos los endpoints de auth
- [ ] Tokens de API con expiración configurada
- [ ] `php artisan sanctum:prune-expired` en cron

### Monitoreo
- [ ] Horizon para monitoreo de queues (`/horizon`)
- [ ] Telescope deshabilitado en producción (o protegido)
- [ ] Pulse para métricas de producción (`/pulse`)
- [ ] Sentry o similar para error tracking
- [ ] Slow query log configurado

---

## Ejemplo input → output

**Input:** "Endpoint POST para crear invitaciones a workspace con email y rol."

**Output (resumen):** ruta `POST /api/v1/workspaces/{id}/invitations` → `StoreInvitationRequest` → `CreateInvitationAction` → `InvitationResource` → `tests/Feature/Workspace/StoreInvitationTest.php` (201, 422 email inválido, 403 sin permiso). Gates: `php artisan test --filter=StoreInvitationTest` exit 0.

---

## Validación

Ejecutar antes de marcar completa. Si falla, corregir y re-ejecutar:

| Gate | Comando | Criterio |
|------|---------|----------|
| Tests feature | `php artisan test --filter=<FeatureTest>` | exit 0 |
| Rutas | `php artisan route:list --path=<recurso>` | ruta registrada |
| Estilo PHP | `vendor/bin/pint --test` | exit 0 |
| Migraciones | `php artisan migrate --pretend` | sin error |

Si el proyecto define `docs/validation/`, guardar log breve `docs/validation/YYYY-MM-DD-<feature>.log`; si no, incluir salida en el Entregable.

---

## Entregable

Toda feature implementada con esta skill cierra con:

```markdown
## Feature: [nombre]

### Archivos
- Ruta: routes/api/v1.php (+ método y path)
- Request: app/Http/Requests/...
- Action/Service: app/Actions/...
- Modelo/migración: ... (o "sin cambios de BD")
- Resource: app/Http/Resources/...
- Test: tests/Feature/...

### Verificación
- [ ] `php artisan test --filter=<FeatureTest>` pasa
- [ ] `php artisan route:list --path=<recurso>` muestra la ruta
- [ ] Migración reversible (`migrate:rollback` probado o plan documentado)

### Pendientes / deuda asumida
[...]
```

---

## Skills relacionadas

- `web-architecture` — patrón arquitectónico del proyecto (Services, hexagonal, módulos)
- `database-design` — modelado y migraciones que Eloquent consume
- `api-design` — contratos REST que estos controllers implementan
- `testing-strategy` — estrategia de tests más allá de Pest
- `security-checklist` — hardening del deploy y secrets
- `performance-web` — performance del stack completo (no solo PHP)
- `devops-base` — CI/CD y servidores del deploy

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
