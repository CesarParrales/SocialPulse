---
name: laravel-modular
description: >
  Guía experta para crear y estructurar módulos en Laravel 11+ con arquitectura modular usando nwidart/laravel-modules.
  Usa esta skill SIEMPRE que el usuario mencione: desarrollo modular en Laravel, crear módulos Laravel, arquitectura de módulos,
  nwidart/laravel-modules, laravel-modules, DDD en Laravel, módulos Laravel 11, separar funcionalidades en Laravel,
  modularizar aplicación Laravel, o cualquier tarea de scaffold/generación de código dentro de módulos Laravel.
  También activa esta skill cuando pregunten cómo organizar un proyecto Laravel grande, separar dominios, o estructurar
  un monolito modular en PHP/Laravel.
---

# Laravel Modular Development Skill

Skill para guiar el diseño, creación y mantenimiento de módulos en **Laravel 11+** con `nwidart/laravel-modules`.

## Cuándo aplica esta skill

- Scaffolding de nuevos módulos
- Diseño de arquitectura modular (estructura de carpetas, namespaces, dependencias)
- Comunicación entre módulos (eventos, contratos, facades)
- Migración de código monolítico a módulos
- Dudas sobre convenciones, testing modular, Service Providers

---

## 1. Instalación y configuración (Laravel 11+)

```bash
composer require nwidart/laravel-modules wikimedia/composer-merge-plugin
php artisan vendor:publish --provider="Nwidart\Modules\LaravelModulesServiceProvider"
```

`composer.json` — autoloading y merge-plugin:
```json
"extra": {
    "merge-plugin": {
        "include": ["Modules/*/composer.json"]
    }
},
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Modules\\": "Modules/"
    }
}
```
> **Laravel 11+**: La línea `"Modules\\": "modules/"` ya NO es necesaria en `composer.json` si usas la versión ≥11 del paquete. Usa solo el merge-plugin.

```bash
composer dump-autoload
```

---

## 2. Estructura canónica de un módulo

```
Modules/
└── NombreModulo/
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   ├── Middleware/
    │   │   └── Requests/
    │   ├── Models/
    │   ├── Services/
    │   ├── Repositories/
    │   ├── Events/
    │   ├── Listeners/
    │   └── Contracts/          ← interfaces públicas del módulo
    ├── config/
    ├── database/
    │   ├── factories/
    │   ├── migrations/
    │   └── seeders/
    ├── resources/
    │   ├── views/
    │   ├── lang/
    │   └── assets/
    ├── routes/
    │   ├── web.php
    │   └── api.php
    ├── tests/
    │   ├── Feature/
    │   └── Unit/
    ├── Providers/
    │   ├── NombreModuloServiceProvider.php
    │   └── RouteServiceProvider.php
    ├── composer.json
    └── module.json
```

---

## 3. Comandos Artisan esenciales

| Acción | Comando |
|--------|---------|
| Crear módulo completo | `php artisan module:make NombreModulo` |
| Crear módulo sin recursos extra | `php artisan module:make NombreModulo --plain` |
| Listar módulos | `php artisan module:list` |
| Habilitar/deshabilitar | `php artisan module:enable NombreModulo` / `module:disable` |
| Ejecutar migraciones | `php artisan module:migrate NombreModulo` |
| Hacer rollback | `php artisan module:migrate-rollback NombreModulo` |
| Crear controlador | `php artisan module:make-controller NombreController NombreModulo` |
| Crear modelo | `php artisan module:make-model NombreModel NombreModulo` |
| Crear migration | `php artisan module:make-migration create_tabla NombreModulo` |
| Crear request | `php artisan module:make-request NombreRequest NombreModulo` |
| Crear seeder | `php artisan module:make-seed NombreSeeder NombreModulo` |
| Crear evento | `php artisan module:make-event NombreEvent NombreModulo` |
| Crear listener | `php artisan module:make-listener NombreListener NombreModulo` |
| Crear job | `php artisan module:make-job NombreJob NombreModulo` |
| Crear middleware | `php artisan module:make-middleware NombreMiddleware NombreModulo` |
| Crear resource | `php artisan module:make-resource NombreResource NombreModulo` |
| Crear policy | `php artisan module:make-policy NombrePolicy NombreModulo` |
| Crear test | `php artisan module:make-test NombreTest NombreModulo` |
| Crear provider | `php artisan module:make-provider NombreProvider NombreModulo` |
| Crear factory | `php artisan module:make-factory NombreFactory NombreModulo` |
| Publicar assets | `php artisan module:publish NombreModulo` |

---

## 4. Service Provider base de un módulo

```php
<?php

namespace Modules\NombreModulo\Providers;

use Illuminate\Support\ServiceProvider;

class NombreModuloServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'NombreModulo';
    protected string $moduleNameLower = 'nombremodulo';

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/config.php'),
            $this->moduleNameLower
        );
    }

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(module_path($this->moduleName, 'resources/views'), $this->moduleNameLower);
    }

    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(module_path($this->moduleName, 'resources/lang'), $this->moduleNameLower);
    }

    protected function registerCommands(): void {}
    protected function registerCommandSchedules(): void {}
}
```

---

## 5. Convenciones clave

### Namespaces
```php
namespace Modules\NombreModulo\app\Http\Controllers;
namespace Modules\NombreModulo\app\Models;
namespace Modules\NombreModulo\app\Services;
namespace Modules\NombreModulo\app\Contracts;
```

### Rutas (api.php)
```php
use Modules\NombreModulo\app\Http\Controllers\NombreController;

Route::prefix('v1/nombre-modulo')
    ->name('nombre-modulo.')
    ->middleware(['api', 'auth:sanctum'])
    ->group(function () {
        Route::apiResource('recursos', NombreController::class);
    });
```

### Vistas
```php
// Llamar vista del módulo
return view('nombremodulo::nombre-vista');
// Retornar con datos
return view('nombremodulo::index', compact('items'));
```

---

## 6. Comunicación entre módulos

### ✅ Correcto: Eventos (desacoplado)
```php
// Módulo A dispara
event(new OrderPlaced($order));

// Módulo B escucha (en su EventServiceProvider)
protected $listen = [
    \Modules\Orders\app\Events\OrderPlaced::class => [
        \Modules\Inventory\app\Listeners\ReserveStock::class,
    ],
];
```

### ✅ Correcto: Contratos (interfaces compartidas)
```php
// En Modules/Shared/app/Contracts/PaymentGateway.php
interface PaymentGateway {
    public function charge(float $amount, string $currency): Receipt;
}

// Módulo Payments implementa
// Módulo Orders usa el contrato (no la implementación)
```

### ❌ Incorrecto: Acceso directo al modelo de otro módulo
```php
// EVITAR: acopla módulos directamente
$user = \Modules\Users\app\Models\User::find($id);
```

---

## 7. Principios de diseño modular

1. **Un módulo = un dominio de negocio** — No crear un módulo por cada modelo Eloquent
2. **Límites claros** — Cada módulo expone solo lo necesario a través de su `Contracts/`
3. **Sin dependencias circulares** — Si A→B y B→A, usar eventos o extraer en módulo `Shared`
4. **Módulo `Core` o `Shared`** — Para utilidades comunes (helpers, traits, contratos base)
5. **Migraciones independientes** — Cada módulo gestiona sus propias migraciones
6. **Tests por módulo** — Cada módulo tiene su carpeta `tests/` con Feature y Unit tests

---

## 8. Testing modular

```php
// tests/Feature/OrderTest.php (dentro del módulo Orders)
namespace Modules\Orders\Tests\Feature;

use Tests\TestCase; // usa el TestCase base de Laravel
use Modules\Orders\app\Models\Order;

class OrderTest extends TestCase
{
    public function test_order_can_be_placed(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'product_id' => 1,
            'quantity'   => 2,
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'status', 'total']);
    }
}
```

Ejecutar tests de un módulo específico:
```bash
php artisan test --filter=Modules\\Orders
# o con phpunit directamente:
./vendor/bin/phpunit Modules/Orders/tests/
```

---

## 9. Detección de violaciones de arquitectura (Deptrac)

Instalar y configurar `qossmic/deptrac` para detectar que los módulos no accedan directamente entre sí:

```yaml
# deptrac.yaml
parameters:
  paths: ['Modules']
  layers:
    - name: Orders
      collectors:
        - type: className
          regex: ^Modules\\Orders\\
    - name: Inventory
      collectors:
        - type: className
          regex: ^Modules\\Inventory\\
  ruleset:
    Orders:
      - Inventory  # Orders puede depender de Inventory (solo a través de contratos)
    Inventory: ~   # Inventory no depende de nadie
```

```bash
./vendor/bin/deptrac analyse
```

---

## 10. Guía rápida para respuestas

Cuando el usuario pide ayuda con módulos Laravel, sigue este orden:

1. **Identificar el dominio** — ¿Cuál es la responsabilidad del módulo?
2. **Scaffold** — Proporcionar el comando `module:make` y los sub-comandos necesarios
3. **Service Provider** — Mostrar configuración relevante para el caso
4. **Estructura de archivos** — Adaptar la estructura canónica al dominio pedido
5. **Comunicación** — Si hay interacción con otros módulos, mostrar patrón correcto (eventos/contratos)
6. **Rutas y controladores** — Con prefijos y nombres apropiados al módulo
7. **Tests** — Incluir al menos un ejemplo de test

### Más detalles

Para patrones avanzados, consulta:
- `references/patterns.md` — Repositorios, CQRS, módulos compartidos
- `references/troubleshooting.md` — Errores comunes y soluciones
