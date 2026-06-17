# Testing con Pest

## Setup y Configuración

```bash
# Pest incluido en Laravel reciente del proyecto
# Para proyectos Laravel 10:
composer require pestphp/pest --dev
composer require pestphp/pest-plugin-laravel --dev
php artisan pest:install
```

```php
// pest.php — configuración global
uses(Tests\TestCase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Unit');

// Para todos los Feature tests: BD en memoria + seed básico
uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

// TestCase base
class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Fake storage, mail, events por defecto en tests
        Storage::fake('public');
        Mail::fake();
    }
}
```

---

## Feature Tests — Comportamiento HTTP

```php
// tests/Feature/Api/OrderTest.php
describe('POST /api/v1/orders', function () {

    it('creates an order successfully', function () {
        $user = User::factory()->create();
        $products = Product::factory()->count(2)->create(['stock' => 10]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [
                    ['product_id' => $products[0]->id, 'quantity' => 2],
                    ['product_id' => $products[1]->id, 'quantity' => 1],
                ],
                'shipping_address' => '123 Main St',
            ]);

        $response->assertCreated()          // 201
            ->assertJsonStructure([
                'data' => [
                    'id', 'status', 'total',
                    'created_at', 'updated_at',
                ],
            ])
            ->assertJsonPath('data.status', 'pending');

        // Verificar BD
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status'  => 'pending',
        ]);

        // Verificar stock decrementado
        expect($products[0]->fresh()->stock)->toBe(8);
        expect($products[1]->fresh()->stock)->toBe(9);
    });

    it('returns 422 when items are missing', function () {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders', ['shipping_address' => '123 Main St'])
            ->assertUnprocessable()     // 422
            ->assertJsonValidationErrors(['items']);
    });

    it('returns 409 when product has insufficient stock', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 1]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 5]],
                'shipping_address' => '123 Main St',
            ])
            ->assertConflict();     // 409
    });

    it('requires authentication', function () {
        $this->postJson('/api/v1/orders', [])->assertUnauthorized(); // 401
    });

    it('dispatches OrderCreated event', function () {
        Event::fake([OrderCreated::class]);

        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
                'shipping_address' => '123 Main St',
            ]);

        Event::assertDispatched(OrderCreated::class, function ($event) use ($user) {
            return $event->order->user_id === $user->id;
        });
    });

    it('queues confirmation email', function () {
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
                'shipping_address' => '123 Main St',
            ]);

        Mail::assertQueued(OrderConfirmationMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    });
});
```

---

## Unit Tests — Lógica de Negocio Aislada

```php
// tests/Unit/Services/OrderServiceTest.php
describe('OrderService', function () {

    beforeEach(function () {
        // Mock de repositorio — sin BD
        $this->orderRepo = Mockery::mock(OrderRepositoryInterface::class);
        $this->inventory = Mockery::mock(InventoryService::class);
        $this->service   = new OrderService($this->orderRepo, $this->inventory);
    });

    it('calculates discount correctly for orders over $100', function () {
        $items = [
            ['product_id' => 1, 'quantity' => 2, 'price_cents' => 6000],  // $120
        ];

        $discount = $this->service->calculateDiscount($items);

        expect($discount)->toBe(1200); // 10% de $120 = $12
    });

    it('applies no discount for orders under $100', function () {
        $items = [
            ['product_id' => 1, 'quantity' => 1, 'price_cents' => 5000],  // $50
        ];

        expect($this->service->calculateDiscount($items))->toBe(0);
    });

    it('throws exception when user exceeds order limit', function () {
        $this->orderRepo
            ->expects('countPendingByUser')
            ->with(1)
            ->andReturn(5);

        expect(fn() => $this->service->validateOrderLimit(userId: 1))
            ->toThrow(OrderLimitExceededException::class);
    });
});

// Test de Value Object — sin framework, puro PHP
describe('Money', function () {

    it('adds two money values of same currency', function () {
        $a = new Money(1000, 'USD');
        $b = new Money(500, 'USD');

        expect($a->add($b)->amount)->toBe(1500);
    });

    it('throws when adding different currencies', function () {
        $usd = new Money(1000, 'USD');
        $eur = new Money(500, 'EUR');

        expect(fn() => $usd->add($eur))
            ->toThrow(CurrencyMismatchException::class);
    });

    it('rejects negative amounts', function () {
        expect(fn() => new Money(-100, 'USD'))
            ->toThrow(InvalidArgumentException::class);
    });

    it('formats correctly', function () {
        expect(new Money(8500, 'USD')->format())->toBe('85.00 USD');
    });
});
```

---

## Testing de Jobs y Queues

```php
it('dispatches ProcessPaymentJob when order is created', function () {
    Queue::fake();

    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'shipping_address' => '123 Main St',
            'payment_token' => 'tok_test_123',
        ]);

    Queue::assertPushed(ProcessPaymentJob::class, function ($job) use ($user) {
        return $job->order->user_id === $user->id;
    });

    Queue::assertPushedOn('payments', ProcessPaymentJob::class);
});

it('handles payment failure correctly', function () {
    $order = Order::factory()->create(['status' => 'pending']);
    $gateway = Mockery::mock(PaymentGateway::class);
    $gateway->expects('charge')->andThrow(new PaymentGatewayException('Card declined'));

    $job = new ProcessPaymentJob($order, 'tok_test');
    $job->handle($gateway);
    $job->failed(new PaymentGatewayException('Card declined'));

    expect($order->fresh()->payment_status)->toBe('failed');
});
```

---

## Datasets — Pruebas Parametrizadas

```php
// Probar múltiples combinaciones con dataset
it('validates order status transitions', function (string $from, string $to, bool $allowed) {
    $order = Order::factory()->create(['status' => $from]);

    if (!$allowed) {
        expect(fn() => $order->transitionTo($to))
            ->toThrow(InvalidStatusTransitionException::class);
    } else {
        $order->transitionTo($to);
        expect($order->status)->toBe($to);
    }
})->with([
    'pending → processing (allowed)'   => ['pending', 'processing', true],
    'pending → shipped (not allowed)'  => ['pending', 'shipped', false],
    'processing → shipped (allowed)'   => ['processing', 'shipped', true],
    'delivered → cancelled (not allowed)' => ['delivered', 'cancelled', false],
]);

// Dataset externo (para muchos casos)
dataset('invalid_emails', [
    'missing @'      => 'notanemail',
    'missing domain' => 'user@',
    'missing tld'    => 'user@example',
    'spaces'         => 'user @example.com',
]);

it('rejects invalid emails', function (string $email) {
    expect(fn() => new Email($email))
        ->toThrow(InvalidEmailException::class);
})->with('invalid_emails');
```

---

## Helpers de Test Personalizados

```php
// tests/Helpers/AuthHelper.php
function actingAsUser(array $overrides = []): User
{
    $user = User::factory()->create($overrides);
    test()->actingAs($user, 'sanctum');
    return $user;
}

function actingAsAdmin(): User
{
    return actingAsUser(['role' => 'admin']);
}

// Uso en tests
it('admin can see all orders', function () {
    actingAsAdmin();
    Order::factory()->count(5)->create();

    $this->getJson('/api/v1/orders')
        ->assertOk()
        ->assertJsonCount(5, 'data');
});

// Helper para crear orden completa
function createOrderWithItems(User $user, int $itemCount = 2): Order
{
    $products = Product::factory()->count($itemCount)->create(['stock' => 10]);
    return Order::factory()
        ->for($user)
        ->hasItems($itemCount, fn($i) => ['product_id' => $products[$i]->id])
        ->create();
}
```

---

## Coverage y CI

```bash
# Correr todos los tests
php artisan test

# Con coverage (requiere Xdebug o PCOV)
php artisan test --coverage --min=80

# Paralelo — más rápido en CI
php artisan test --parallel

# Filtrar
php artisan test --filter="OrderTest"
php artisan test tests/Feature/Api/

# En GitHub Actions
- name: Run Tests
  run: php artisan test --parallel --coverage-clover coverage.xml

- name: Upload Coverage
  uses: codecov/codecov-action@v3
  with:
    file: coverage.xml
```
