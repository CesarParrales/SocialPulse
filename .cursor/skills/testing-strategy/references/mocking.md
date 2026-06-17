# Mocking y Fixtures

## Principios de Mocking

```
Mock = reemplazar una dependencia real con una falsa para controlar el comportamiento

Cuándo mockear:
✅ Servicios externos (Stripe, SendGrid, AWS S3)
✅ Tiempo (Date.now(), new Date())
✅ Aleatoriedad (Math.random(), crypto.randomUUID())
✅ Filesystem si el test no lo necesita realmente
✅ BD en tests unitarios (usar real en integración)

Cuándo NO mockear:
❌ La cosa que estás testeando (mockear el sujeto = test vacío)
❌ Lógica de negocio propia (testearla real)
❌ Tipos de datos simples (DTOs, Value Objects)
❌ Módulos de utilidad sin efectos secundarios

Regla de oro: si algo es lento, impredecible o externo → mockear
             si algo es tuyo, determinístico y rápido → no mockear
```

---

## Mocking en Vitest

```typescript
// vi.mock — mockear módulo completo
vi.mock('@/lib/api', () => ({
  apiClient: {
    get: vi.fn(),
    post: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  },
}));

// vi.spyOn — mockear método específico
import * as emailService from '@/services/email';
const spy = vi.spyOn(emailService, 'sendConfirmation');
spy.mockResolvedValue({ messageId: 'msg_123' });

// Verificar llamada
expect(spy).toHaveBeenCalledOnce();
expect(spy).toHaveBeenCalledWith(
  expect.objectContaining({ to: 'user@example.com' })
);

// Mock de tiempo
vi.useFakeTimers();
vi.setSystemTime(new Date('2024-01-15T10:00:00Z'));
// ... código que usa Date.now() ...
vi.useRealTimers(); // restaurar después

// Mock de módulo con factory
vi.mock('react-router-dom', async (importOriginal) => {
  const actual = await importOriginal();
  return {
    ...actual,  // mantener el resto del módulo real
    useNavigate: () => vi.fn(),  // solo mockear lo que necesitamos
    useParams: () => ({ id: 'test-id' }),
  };
});
```

---

## Factories — Datos de Test Consistentes

```typescript
// tests/factories/order.factory.ts
import { faker } from '@faker-js/faker';
import type { Order, OrderStatus, OrderItem } from '@/types';

// Factory base — valores sensatos por defecto
export function createOrder(overrides: Partial<Order> = {}): Order {
  return {
    id: faker.string.ulid(),
    reference: `ORD-${faker.string.alphanumeric(8).toUpperCase()}`,
    status: 'PENDING',
    total: {
      amount: faker.number.int({ min: 1000, max: 100000 }),
      currency: 'USD',
      formatted: '$10.00',
    },
    shippingAddress: faker.location.streetAddress(),
    createdAt: faker.date.recent().toISOString(),
    updatedAt: faker.date.recent().toISOString(),
    items: [],
    ...overrides,
  };
}

// Estado específico — más legible que overrides largos
export function createPendingOrder(overrides?: Partial<Order>): Order {
  return createOrder({ status: 'PENDING', ...overrides });
}

export function createShippedOrder(overrides?: Partial<Order>): Order {
  return createOrder({ status: 'SHIPPED', ...overrides });
}

export function createOrderWithItems(
  itemCount = 2,
  overrides?: Partial<Order>
): Order {
  return createOrder({
    items: Array.from({ length: itemCount }, createOrderItem),
    ...overrides,
  });
}

function createOrderItem(overrides: Partial<OrderItem> = {}): OrderItem {
  const quantity = faker.number.int({ min: 1, max: 5 });
  const priceCents = faker.number.int({ min: 500, max: 10000 });
  return {
    id: faker.string.ulid(),
    productId: faker.string.ulid(),
    name: faker.commerce.productName(),
    quantity,
    priceCents,
    totalCents: quantity * priceCents,
    ...overrides,
  };
}

// Lista de órdenes con estados mixtos para tests de listado
export function createOrderList(count = 5): Order[] {
  const statuses: OrderStatus[] = ['PENDING', 'PROCESSING', 'SHIPPED', 'DELIVERED'];
  return Array.from({ length: count }, (_, i) =>
    createOrder({ status: statuses[i % statuses.length] })
  );
}
```

```php
// database/factories/OrderFactory.php (Laravel)
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'             => Str::ulid(),
            'reference'        => 'ORD-' . strtoupper(Str::random(8)),
            'status'           => 'pending',
            'total_cents'      => fake()->numberBetween(1000, 100000),
            'currency'         => 'USD',
            'shipping_address' => fake()->address(),
            'created_at'       => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    // Estados como métodos encadenables
    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function shipped(): static
    {
        return $this->state(['status' => 'shipped', 'shipped_at' => now()]);
    }

    public function delivered(): static
    {
        return $this->state([
            'status'       => 'delivered',
            'shipped_at'   => now()->subDays(3),
            'delivered_at' => now(),
        ]);
    }

    // Con usuario específico
    public function forUser(User $user): static
    {
        return $this->state(['user_id' => $user->id]);
    }

    // Con items
    public function withItems(int $count = 2): static
    {
        return $this->has(
            OrderItem::factory()->count($count),
            'items'
        );
    }

    // Usando configure() para after-creation hooks
    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            // Calcular total real basado en items
            if ($order->items()->exists()) {
                $total = $order->items->sum(fn($item) => $item->price_cents * $item->quantity);
                $order->update(['total_cents' => $total]);
            }
        });
    }
}

// Uso en tests
$order = Order::factory()->shipped()->withItems(3)->create();
$orders = Order::factory()->count(10)->for($user)->create();
```

---

## Snapshots — Para UI Components

```typescript
// Snapshots: útiles para detectar cambios no intencionales en UI
// Usar con moderación — se vuelven flaky con cambios de estilos normales

it('renders OrderStatusBadge correctly for each status', () => {
  const statuses: OrderStatus[] = ['PENDING', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED'];

  statuses.forEach(status => {
    const { container } = render(<OrderStatusBadge status={status} />);
    expect(container).toMatchSnapshot();
  });
});

// Snapshot inline — más legible, más controlado
it('renders correct classes for primary button', () => {
  const { container } = render(<AppButton variant="primary" label="Test" />);

  expect(container.firstChild).toMatchInlineSnapshot(`
    <button
      class="btn btn-primary w-full"
      type="button"
    >
      Test
    </button>
  `);
});

// Actualizar snapshots cuando el cambio es intencional:
// vitest --update-snapshots
```

---

## Helpers de Test Reutilizables

```typescript
// tests/helpers/render.tsx — render con todos los providers
import { render } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { MemoryRouter } from 'react-router-dom';

function createTestQueryClient() {
  return new QueryClient({
    defaultOptions: {
      queries: { retry: false, gcTime: 0 },
      mutations: { retry: false },
    },
  });
}

export function renderWithProviders(
  ui: React.ReactElement,
  options: { route?: string; queryClient?: QueryClient } = {}
) {
  const queryClient = options.queryClient ?? createTestQueryClient();
  const route = options.route ?? '/';

  return {
    ...render(
      <MemoryRouter initialEntries={[route]}>
        <QueryClientProvider client={queryClient}>
          {ui}
        </QueryClientProvider>
      </MemoryRouter>
    ),
    queryClient,
  };
}

// tests/helpers/api-mocks.ts — mocks de API reutilizables
import { http, HttpResponse } from 'msw';  // Mock Service Worker
import { setupServer } from 'msw/node';

const handlers = [
  http.get('/api/v1/orders', () => {
    return HttpResponse.json({
      data: createOrderList(5),
      meta: { total: 5, page: 1, per_page: 20, last_page: 1 },
    });
  }),

  http.post('/api/v1/orders', async ({ request }) => {
    const body = await request.json();
    return HttpResponse.json({ data: createOrder() }, { status: 201 });
  }),

  http.post('/api/v1/orders/:id/cancel', ({ params }) => {
    return HttpResponse.json({
      data: createOrder({ id: params.id as string, status: 'CANCELLED' }),
    });
  }),
];

export const server = setupServer(...handlers);

// En setup.ts
beforeAll(() => server.listen({ onUnhandledRequest: 'warn' }));
afterEach(() => server.resetHandlers());
afterAll(() => server.close());

// Override en test específico
it('handles cancel error', async () => {
  server.use(
    http.post('/api/v1/orders/:id/cancel', () =>
      HttpResponse.json({ error: 'Cannot cancel shipped order' }, { status: 409 })
    )
  );
  // ... el test ve el error de la API real ...
});
```
