# Patrones avanzados — Laravel Modular

## Patrón Repositorio por módulo

Separar la lógica de consultas del Service para mantener el módulo testeable.

```php
// Modules/Orders/app/Contracts/OrderRepository.php
namespace Modules\Orders\app\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Orders\app\Models\Order;

interface OrderRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?Order;
    public function create(array $data): Order;
    public function update(Order $order, array $data): Order;
    public function delete(Order $order): bool;
}
```

```php
// Modules/Orders/app/Repositories/EloquentOrderRepository.php
namespace Modules\Orders\app\Repositories;

use Modules\Orders\app\Contracts\OrderRepository;
use Modules\Orders\app\Models\Order;

class EloquentOrderRepository implements OrderRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Order::latest()->paginate($perPage);
    }

    public function findById(int $id): ?Order
    {
        return Order::find($id);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);
        return $order->fresh();
    }

    public function delete(Order $order): bool
    {
        return $order->delete();
    }
}
```

Registrar en el ServiceProvider:
```php
public function register(): void
{
    $this->app->bind(
        \Modules\Orders\app\Contracts\OrderRepository::class,
        \Modules\Orders\app\Repositories\EloquentOrderRepository::class,
    );
}
```

---

## Módulo Shared / Core

Para código verdaderamente transversal (helpers, contratos base, traits):

```
Modules/
└── Shared/
    └── app/
        ├── Contracts/
        │   ├── HasUuid.php
        │   └── Auditable.php
        ├── Traits/
        │   ├── HasUuidTrait.php
        │   └── AuditableTrait.php
        ├── Helpers/
        │   └── MoneyFormatter.php
        └── Exceptions/
            └── DomainException.php
```

```php
// Modules/Shared/app/Traits/HasUuidTrait.php
namespace Modules\Shared\app\Traits;

use Illuminate\Support\Str;

trait HasUuidTrait
{
    protected static function bootHasUuidTrait(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
```

---

## CQRS simplificado en módulos

Útil cuando la lógica de lectura y escritura difieren mucho:

```
Modules/Products/app/
├── Commands/
│   ├── CreateProduct.php       ← DTO del comando
│   └── Handlers/
│       └── CreateProductHandler.php
├── Queries/
│   ├── GetProductList.php
│   └── Handlers/
│       └── GetProductListHandler.php
```

```php
// Commands/CreateProduct.php
readonly class CreateProduct
{
    public function __construct(
        public string $name,
        public float  $price,
        public int    $stock,
    ) {}
}

// Commands/Handlers/CreateProductHandler.php
class CreateProductHandler
{
    public function __construct(
        private ProductRepository $products
    ) {}

    public function handle(CreateProduct $command): Product
    {
        return $this->products->create([
            'name'  => $command->name,
            'price' => $command->price,
            'stock' => $command->stock,
        ]);
    }
}
```

Registrar handlers como singletons o usar un Command Bus (ej. `Tactician`):
```php
// En ServiceProvider
$this->app->singleton(CreateProductHandler::class);
```

---

## Módulo con API Resources + Transformers

```php
// Modules/Products/app/Http/Resources/ProductResource.php
namespace Modules\Products\app\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'price'      => [
                'amount'   => $this->price,
                'currency' => 'USD',
                'formatted'=> '$' . number_format($this->price, 2),
            ],
            'stock'      => $this->stock,
            'status'     => $this->status->label(), // enum nativo PHP
            'created_at' => $this->created_at->toISOString(),
            'links'      => [
                'self' => route('products.show', $this->id),
            ],
        ];
    }
}
```

---

## Módulo con Enums (PHP nativo)

```php
// Modules/Orders/app/Enums/OrderStatus.php
namespace Modules\Orders\app\Enums;

enum OrderStatus: string
{
    case Pending   = 'pending';
    case Confirmed = 'confirmed';
    case Shipped   = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Pendiente',
            self::Confirmed => 'Confirmado',
            self::Shipped   => 'Enviado',
            self::Delivered => 'Entregado',
            self::Cancelled => 'Cancelado',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled]);
    }
}
```

---

## Configuración de stubs personalizados

Publicar stubs del paquete para adaptarlos al proyecto:
```bash
php artisan vendor:publish --tag=stubs
```

Editar `stubs/nwidart-stubs/` para agregar namespace de empresa, headers PHP, etc.

---

## Módulo con autenticación separada (Sanctum/Passport)

```php
// Modules/Auth/app/Http/Controllers/LoginController.php
namespace Modules\Auth\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $token = $request->user()->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token]);
    }
}
```

---

## Eventos entre módulos — ejemplo completo

```php
// Modules/Orders/app/Events/OrderPlaced.php
namespace Modules\Orders\app\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Orders\app\Models\Order;

class OrderPlaced
{
    use Dispatchable;

    public function __construct(
        public readonly Order $order
    ) {}
}
```

```php
// Modules/Inventory/app/Listeners/ReserveStock.php
namespace Modules\Inventory\app\Listeners;

use Modules\Orders\app\Events\OrderPlaced;

class ReserveStock
{
    public function handle(OrderPlaced $event): void
    {
        foreach ($event->order->items as $item) {
            // Lógica de reserva de stock propia del módulo Inventory
        }
    }
}
```

Registrar en `Modules/Inventory/Providers/EventServiceProvider.php`:
```php
protected $listen = [
    \Modules\Orders\app\Events\OrderPlaced::class => [
        ReserveStock::class,
    ],
];
```

> Nota: El módulo Inventory depende del evento de Orders (aceptable), pero no de los modelos internos de Orders.
