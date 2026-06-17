# Tests Unitarios — PHP / Laravel (Pest)

## Configuración de Pest

```php
// tests/Pest.php
uses(Tests\TestCase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Unit');

// Feature tests — con BD
uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

// Unit tests — sin BD, sin framework si es posible
uses(Tests\TestCase::class)->in('Unit');

// Helpers globales para tests
function makeUser(array $attributes = []): User
{
    return User::factory()->create($attributes);
}

function actingAsUser(array $attributes = []): User
{
    $user = makeUser($attributes);
    test()->actingAs($user, 'sanctum');
    return $user;
}

function actingAsAdmin(): User
{
    return actingAsUser(['role' => 'admin']);
}
```

---

## Tests Unitarios — Lógica de Negocio Pura

```php
// tests/Unit/Services/PricingServiceTest.php

describe('PricingService::calculateDiscount', function () {

    it('applies 10% discount when subtotal is over $100', function () {
        $service = new PricingService();
        $items = [
            ['price_cents' => 6000, 'quantity' => 2],  // $120
        ];

        $discount = $service->calculateDiscount($items);

        expect($discount)->toBe(1200); // 10% de $120 = $12
    });

    it('applies no discount for orders under $100', function () {
        $service = new PricingService();
        $items = [['price_cents' => 4000, 'quantity' => 2]]; // $80

        expect($service->calculateDiscount($items))->toBe(0);
    });

    it('applies coupon discount on top of volume discount', function () {
        $service = new PricingService();
        $items = [['price_cents' => 6000, 'quantity' => 2]]; // $120
        $coupon = Coupon::factory()->make(['discount_percent' => 20]);

        $discount = $service->calculateDiscount($items, $coupon);

        // 10% volume + 20% coupon sobre el subtotal post-volume = $120 * 0.10 + $108 * 0.20
        expect($discount)->toBe(1200 + 2160); // $12 + $21.60
    });

    it('does not apply negative discount', function () {
        $service = new PricingService();
        $items = [['price_cents' => 1000, 'quantity' => 1]];
        $coupon = Coupon::factory()->make(['discount_percent' => 150]); // más del 100%

        $discount = $service->calculateDiscount($items, $coupon);

        expect($discount)->toBeLessThanOrEqual(1000); // no puede superar el total
    });

})->covers(PricingService::class);

// Value Objects — puro PHP, sin framework
describe('Money', function () {

    it('creates money with valid amount', function () {
        $money = new Money(1000, 'USD');
        expect($money->amount)->toBe(1000);
        expect($money->currency)->toBe('USD');
    });

    it('adds two money values of same currency', function () {
        $a = new Money(1000, 'USD');
        $b = new Money(500, 'USD');

        $result = $a->add($b);

        expect($result->amount)->toBe(1500);
    });

    it('throws when adding different currencies', function () {
        $usd = new Money(1000, 'USD');
        $eur = new Money(1000, 'EUR');

        expect(fn() => $usd->add($eur))
            ->toThrow(CurrencyMismatchException::class);
    });

    it('rejects negative amounts', function () {
        expect(fn() => new Money(-1, 'USD'))
            ->toThrow(InvalidArgumentException::class, 'Amount cannot be negative');
    });

    it('formats correctly for display', function () {
        expect(new Money(8550, 'USD')->format())->toBe('$85.50');
        expect(new Money(100, 'USD')->format())->toBe('$1.00');
    });

})->covers(Money::class);
```

---

## Tests de Feature — HTTP + BD

```php
// tests/Feature/Api/OrdersTest.php
describe('POST /api/v1/orders', function () {

    it('creates order for authenticated user', function () {
        $user = makeUser();
        $product = Product::factory()->create(['stock' => 10, 'price_cents' => 2000]);

        actingAsUser(['id' => $user->id]);

        $response = $this->postJson('/api/v1/orders', [
            'items'            => [['product_id' => $product->id, 'quantity' => 2]],
            'shipping_address' => '123 Main St, New York',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'status', 'total', 'created_at']])
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total.amount', 4000);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status'  => 'pending',
            'total_cents' => 4000,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        expect($product->fresh()->stock)->toBe(8);
    });

    it('returns 401 when not authenticated', function () {
        $this->postJson('/api/v1/orders', [])->assertUnauthorized();
    });

    it('returns 422 with field-level errors for invalid data', function () {
        actingAsUser();

        $this->postJson('/api/v1/orders', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items', 'shipping_address']);
    });

    it('returns 409 when product has insufficient stock', function () {
        $product = Product::factory()->create(['stock' => 1]);
        actingAsUser();

        $this->postJson('/api/v1/orders', [
            'items'            => [['product_id' => $product->id, 'quantity' => 5]],
            'shipping_address' => '123 Main St',
        ])->assertConflict()
          ->assertJsonPath('error_code', 'INSUFFICIENT_STOCK');
    });

    it('dispatches OrderCreated event on success', function () {
        Event::fake([OrderCreated::class]);
        $product = Product::factory()->create(['stock' => 5]);
        $user = actingAsUser();

        $this->postJson('/api/v1/orders', [
            'items'            => [['product_id' => $product->id, 'quantity' => 1]],
            'shipping_address' => '123 Main St',
        ]);

        Event::assertDispatched(OrderCreated::class, fn($e) =>
            $e->order->user_id === $user->id
        );
    });

    it('queues confirmation email', function () {
        Mail::fake();
        $product = Product::factory()->create(['stock' => 5]);
        $user = actingAsUser();

        $this->postJson('/api/v1/orders', [
            'items'            => [['product_id' => $product->id, 'quantity' => 1]],
            'shipping_address' => '123 Main St',
        ]);

        Mail::assertQueued(OrderConfirmationMail::class,
            fn($m) => $m->hasTo($user->email)
        );
    });

})->covers(OrderController::class, OrderService::class);


describe('GET /api/v1/orders/{id}', function () {

    it('returns order for owner', function () {
        $user = actingAsUser();
        $order = Order::factory()->for($user)->create();

        $this->getJson("/api/v1/orders/{$order->uuid}")
            ->assertOk()
            ->assertJsonPath('data.id', $order->uuid);
    });

    it('returns 403 when accessing another user\'s order', function () {
        actingAsUser();
        $otherOrder = Order::factory()->create();

        $this->getJson("/api/v1/orders/{$otherOrder->uuid}")
            ->assertForbidden();
    });

    it('returns 404 for non-existent order', function () {
        actingAsUser();

        $this->getJson('/api/v1/orders/nonexistent-uuid')
            ->assertNotFound();
    });

})->covers(OrderController::class);
```

---

## Datasets — Pruebas Parametrizadas

```php
// Múltiples escenarios sin duplicar código
it('validates order status transitions', function (
    string $currentStatus,
    string $newStatus,
    bool $allowed
) {
    $order = Order::factory()->create(['status' => $currentStatus]);

    if ($allowed) {
        $order->transitionTo($newStatus);
        expect($order->fresh()->status)->toBe($newStatus);
    } else {
        expect(fn() => $order->transitionTo($newStatus))
            ->toThrow(InvalidStatusTransitionException::class);
    }
})->with([
    'pending → processing (ok)'    => ['pending', 'processing', true],
    'processing → shipped (ok)'    => ['processing', 'shipped', true],
    'pending → shipped (invalid)'  => ['pending', 'shipped', false],
    'delivered → cancelled (invalid)' => ['delivered', 'cancelled', false],
    'cancelled → any (invalid)'    => ['cancelled', 'pending', false],
]);

// Dataset externo reutilizable
dataset('invalid_payment_tokens', [
    'empty string'   => [''],
    'too short'      => ['abc'],
    'invalid chars'  => ['tok_$$$'],
    'sql injection'  => ["'; DROP TABLE orders; --"],
    'xss attempt'    => ['<script>alert(1)</script>'],
]);

it('rejects invalid payment tokens', function (string $token) {
    actingAsUser();
    $product = Product::factory()->create(['stock' => 1]);

    $this->postJson('/api/v1/orders', [
        'items'          => [['product_id' => $product->id, 'quantity' => 1]],
        'shipping_address' => '123 Main St',
        'payment_token'  => $token,
    ])->assertUnprocessable();
})->with('invalid_payment_tokens');
```

---

## Mocking en Pest/PHPUnit

```php
// Mock de servicio externo — no llama a la API real
it('processes payment via gateway', function () {
    $gateway = Mockery::mock(PaymentGateway::class);
    $gateway->expects('charge')
        ->once()
        ->with(Mockery::on(fn($args) => $args['amount'] === 5000))
        ->andReturn(new PaymentResult(id: 'pay_123', status: 'succeeded'));

    app()->instance(PaymentGateway::class, $gateway);

    $service = app(OrderService::class);
    $order = Order::factory()->create(['total_cents' => 5000]);

    $result = $service->processPayment($order, 'tok_test');

    expect($result->status)->toBe('succeeded');
    expect($order->fresh()->payment_status)->toBe('completed');
});

// Spy — verificar que se llamó algo sin definir el retorno
it('sends notification after order ships', function () {
    $notifier = Mockery::spy(NotificationService::class);
    app()->instance(NotificationService::class, $notifier);

    $order = Order::factory()->shipped()->create();
    app(OrderService::class)->markAsDelivered($order);

    $notifier->shouldHaveReceived('notifyDelivery')
        ->once()
        ->with(Mockery::type(Order::class));
});
```
