# Troubleshooting — Laravel Modular

## Errores comunes y soluciones

### 1. Class not found / Autoload
**Síntoma:** `Class "Modules\X\app\Y" not found`

**Causas y soluciones:**
```bash
# Siempre correr tras crear/mover archivos de módulo
composer dump-autoload

# Verificar que el módulo está activo
php artisan module:list

# Verificar status en modules_statuses.json (raíz del proyecto)
cat modules_statuses.json
```

También verificar `composer.json`:
```json
"extra": {
    "merge-plugin": {
        "include": ["Modules/*/composer.json"]
    }
}
```

> Nota de consistencia: en esta guía, para Laravel 11+ se prioriza el uso del merge-plugin del paquete modular.

---

### 2. Views not found
**Síntoma:** `View [nombremodulo::vista] not found`

**Causas:**
- La carpeta es `Resources/views/` (mayúscula R) — verificar capitalización exacta
- El nombre en el ServiceProvider no coincide con el prefijo usado

```php
// En el ServiceProvider
$this->loadViewsFrom(module_path($this->moduleName, 'resources/views'), 'nombremodulo');
//                                                     ^ minúsculas        ^ clave

// En el controlador / blade
return view('nombremodulo::index');  // debe coincidir exactamente
```

```bash
php artisan optimize:clear   # limpiar cache de vistas
php artisan view:clear
```

---

### 3. Rutas no registradas
**Síntoma:** `Route [x.index] not defined` o 404 en rutas de módulo

```bash
php artisan route:list | grep modulo   # verificar si existe
php artisan optimize:clear             # limpiar cache de rutas
```

Verificar el `RouteServiceProvider` del módulo:
```php
public function boot(): void
{
    $this->routes(function () {
        Route::middleware('api')
            ->prefix('api')
            ->group(module_path($this->name, '/routes/api.php'));

        Route::middleware('web')
            ->group(module_path($this->name, '/routes/web.php'));
    });
}
```

---

### 4. Migraciones no ejecutadas
```bash
# Migrar solo un módulo
php artisan module:migrate NombreModulo

# Migrar todos los módulos
php artisan module:migrate

# Ver estado
php artisan module:migrate-status NombreModulo

# Rollback
php artisan module:migrate-rollback NombreModulo --step=1
```

---

### 5. Config no cargada
**Síntoma:** `config('nombremodulo.clave')` retorna null

```bash
php artisan config:clear
php artisan optimize:clear
```

Verificar en ServiceProvider:
```php
public function register(): void
{
    $this->mergeConfigFrom(
        module_path($this->moduleName, 'config/config.php'),
        $this->moduleNameLower  // esta clave se usa en config('clave.x')
    );
}
```

---

### 6. Módulo deshabilitado sin querer
```bash
# Habilitar módulo
php artisan module:enable NombreModulo

# El archivo modules_statuses.json debe mostrar true
# {"NombreModulo": true}
```

---

### 7. Tests del módulo no encontrados
```bash
# Ejecutar solo tests de un módulo
php artisan test --filter="Modules\\\\NombreModulo"

# O usando phpunit directamente
./vendor/bin/phpunit Modules/NombreModulo/tests/ --testdox
```

Verificar que el `phpunit.xml` incluye la carpeta de módulos:
```xml
<testsuites>
    <testsuite name="Modules">
        <directory suffix="Test.php">./Modules</directory>
    </testsuite>
</testsuites>
```

---

### 8. Dependencias circulares entre módulos

**Síntoma:** Módulo A usa modelos de B, B usa modelos de A.

**Solución:** Extraer la dependencia a un módulo `Shared` o usar eventos:
```
Modules/
├── Orders/      ← escucha evento de Users
├── Users/       ← dispara evento UserRegistered
└── Shared/      ← contratos compartidos (no depende de nadie)
```

---

### 9. Error al publicar assets del módulo
```bash
php artisan module:publish NombreModulo
# Publica en public/modules/nombremodulo/

# Si hay conflictos:
php artisan module:publish NombreModulo --force
```

---

### 10. Merge plugin no activo

**Síntoma:** Los `composer.json` de módulos no son leídos.

```bash
composer config allow-plugins.wikimedia/composer-merge-plugin true
composer update
```

Verificar en `composer.json` raíz:
```json
"extra": {
    "merge-plugin": {
        "include": ["Modules/*/composer.json"]
    }
}
```

---

## Comandos de diagnóstico rápido

```bash
# Estado general
php artisan module:list

# Limpiar todo el cache
php artisan optimize:clear

# Verificar rutas del módulo
php artisan route:list --path=api/v1/nombre-modulo

# Ver todas las migraciones pendientes
php artisan migrate:status

# Verificar autoload PSR-4
composer dump-autoload -v
```

---

## Checklist al crear un módulo nuevo

- [ ] `php artisan module:make NombreModulo`
- [ ] Revisar y ajustar el `module.json` (description, keywords)
- [ ] Configurar rutas en `routes/api.php` o `routes/web.php`
- [ ] Registrar eventos en `Providers/EventServiceProvider.php` (si aplica)
- [ ] Agregar binding de contratos en el `ServiceProvider`
- [ ] Crear migration: `php artisan module:make-migration create_tabla NombreModulo`
- [ ] Correr `php artisan module:migrate NombreModulo`
- [ ] Verificar con `php artisan module:list`
- [ ] Correr `composer dump-autoload`
- [ ] Escribir al menos un Feature test
