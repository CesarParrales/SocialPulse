# Filament — Admin Panel en Laravel

Filament construye paneles de administración sobre Livewire: Resources (CRUD),
widgets, forms y tablas declarativas. Es la opción por defecto para "necesito
un admin en Laravel" sin escribir un frontend a medida.

## Setup del Panel

```bash
composer require filament/filament:"^3.0"
php artisan filament:install --panels
# Crea app/Providers/Filament/AdminPanelProvider.php y la ruta /admin

php artisan make:filament-user   # primer usuario admin
```

```php
// app/Providers/Filament/AdminPanelProvider.php
public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()
        ->colors(['primary' => Color::Indigo])
        ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
        ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
        ->middleware([/* web middleware stack por defecto */])
        ->authMiddleware([Authenticate::class]);
}
```

Múltiples paneles (admin + portal de clientes): un provider por panel con
`php artisan make:filament-panel app`.

---

## Resources — CRUD Declarativo

```bash
php artisan make:filament-resource Order --generate
# --generate infiere form y table desde la BD
# --view agrega página de solo lectura
# --soft-deletes agrega filtros de papelera
```

```php
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Ventas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->searchable()          // busca en BD, no carga todo
                ->preload()
                ->required(),

            Forms\Components\Select::make('status')
                ->options(OrderStatus::class)   // enum nativo PHP
                ->required(),

            Forms\Components\TextInput::make('total_cents')
                ->numeric()
                ->required()
                ->minValue(0),

            Forms\Components\Textarea::make('notes')
                ->maxLength(1000)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('Orden')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')   // relación
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (OrderStatus $state) => match ($state) {
                        OrderStatus::Pending   => 'warning',
                        OrderStatus::Shipped   => 'success',
                        OrderStatus::Cancelled => 'danger',
                        default                => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_cents')
                    ->money('usd', divideBy: 100)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(OrderStatus::class),
                Tables\Filters\Filter::make('created_at')
                    ->form([Forms\Components\DatePicker::make('desde')])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['desde'], fn ($q, $d) => $q->whereDate('created_at', '>=', $d))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Acción de negocio custom
                Tables\Actions\Action::make('cancelar')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === OrderStatus::Pending)
                    ->action(fn (Order $record, CancelOrderAction $action) => $action->execute($record)),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,  // tabla de order_items embebida
        ];
    }
}
```

### Relation Managers

```bash
php artisan make:filament-relation-manager OrderResource items name
```

Muestran y editan relaciones (HasMany, BelongsToMany) dentro de la página
del registro padre, con su propio form/table.

---

## Widgets — Dashboard

```bash
php artisan make:filament-widget OrdersStats --stats-overview
php artisan make:filament-widget OrdersChart --chart
```

```php
class OrdersStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Órdenes hoy', Order::whereDate('created_at', today())->count())
                ->description('vs. ayer')
                ->color('success'),
            Stat::make('Ingresos del mes',
                '$' . number_format(Order::whereMonth('created_at', now()->month)
                    ->sum('total_cents') / 100, 2)),
            Stat::make('Pendientes', Order::where('status', 'pending')->count())
                ->color('warning'),
        ];
    }

    // Cachear stats costosas — el dashboard se refresca con polling
    protected static ?string $pollingInterval = '30s';
}
```

---

## Auth y Policies en el Panel

Filament respeta las Policies de Laravel automáticamente:

```php
// OrderPolicy — Filament la usa para mostrar/ocultar acciones
class OrderPolicy
{
    public function viewAny(User $user): bool  { return $user->can('orders.view'); }
    public function create(User $user): bool   { return $user->can('orders.create'); }
    public function update(User $user, Order $order): bool { return $user->can('orders.update'); }
    public function delete(User $user, Order $order): bool { return $user->isAdmin(); }
}
```

Controlar quién entra al panel:

```php
// User model — requerido en producción
class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(['admin', 'manager'])
            && $this->hasVerifiedEmail();
    }
}
```

Roles/permisos granulares: integrar `spatie/laravel-permission`
(+ plugin `filament-shield` para generar permisos por Resource).

---

## Optimización de Queries en Tablas

Las tablas de Filament generan N+1 fácilmente con columnas de relación:

```php
public static function table(Table $table): Table
{
    return $table
        // Eager loading explícito — evita N+1 en user.name, items.count
        ->modifyQueryUsing(fn (Builder $query) => $query
            ->with('user')
            ->withCount('items')
            ->select(['id', 'uuid', 'user_id', 'status', 'total_cents', 'created_at']))
        ->columns([
            Tables\Columns\TextColumn::make('items_count')->label('Items'),
            // ...
        ]);
}
```

Reglas:
- `modifyQueryUsing` con `with()`/`withCount()` para toda columna de relación.
- `searchable()` en columnas de relación genera JOIN — verificar índices.
- `Select::relationship()` siempre con `searchable()` en tablas grandes
  (sin eso carga todos los registros en el HTML).
- Activar `Model::preventLazyLoading()` en dev detecta los N+1 del panel.

---

## Notas de Deploy

```bash
# En el pipeline de deploy, después de composer install:
php artisan filament:upgrade        # publica assets de Filament
php artisan icons:cache             # cachea iconos Blade (gran impacto en TTFB)
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

- `filament:upgrade` debe correr en cada deploy (agregarlo a
  `post-autoload-dump` en composer.json lo automatiza).
- Con Octane: verificar que los widgets no guarden estado en propiedades estáticas.
- El panel usa sesiones — con múltiples servidores, `SESSION_DRIVER=redis`.
- Proteger `/admin` adicionalmente (IP allowlist o VPN) si el panel es interno.

---

## Checklist Filament

- [ ] `canAccessPanel()` implementado (sin esto, todo usuario entra en producción)
- [ ] Policies definidas por cada Resource
- [ ] `modifyQueryUsing` con eager loading en tablas con relaciones
- [ ] Selects de relación con `searchable()` (no cargar tablas completas)
- [ ] Widgets costosos con caché o polling espaciado
- [ ] `filament:upgrade` + `icons:cache` en el deploy
