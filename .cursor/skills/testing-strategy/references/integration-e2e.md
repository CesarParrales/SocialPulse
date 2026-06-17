# Tests de Integración y E2E

## Playwright — E2E Tests

```typescript
// playwright.config.ts
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  timeout: 30_000,
  retries: process.env.CI ? 2 : 0,    // reintentar en CI para reducir flakiness
  workers: process.env.CI ? 4 : 1,    // paralelo en CI
  reporter: [
    ['html', { outputFolder: 'playwright-report' }],
    ['github'],   // anotaciones en GitHub PR
  ],
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:3000',
    trace: 'on-first-retry',    // capturar trace cuando falla y se reintenta
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    // Setup — crear datos de prueba antes de los tests
    {
      name: 'setup',
      testMatch: /global\.setup\.ts/,
    },
    // Tests en Chromium (suficiente para la mayoría)
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
      dependencies: ['setup'],
    },
    // Mobile — solo flujos críticos
    {
      name: 'mobile-chrome',
      testMatch: /critical\.spec\.ts/,
      use: { ...devices['Pixel 5'] },
      dependencies: ['setup'],
    },
  ],
  webServer: {
    command: 'npm run dev',
    url: 'http://localhost:3000',
    reuseExistingServer: !process.env.CI,
    timeout: 120_000,
  },
});
```

---

## Global Setup — Datos de Test

```typescript
// tests/e2e/global.setup.ts
import { test as setup } from '@playwright/test';
import { createTestUser, seedTestData } from './helpers/setup-helpers';

// Archivo donde guardar el estado de auth (compartido entre tests)
const authFile = 'tests/e2e/.auth/user.json';
const adminAuthFile = 'tests/e2e/.auth/admin.json';

setup('Authenticate user', async ({ page }) => {
  await page.goto('/login');
  await page.getByLabel('Email').fill('test@example.com');
  await page.getByLabel('Password').fill('password');
  await page.getByRole('button', { name: 'Sign In' }).click();
  await page.waitForURL('/dashboard');

  // Guardar estado de auth — reutilizar en todos los tests (sin re-login)
  await page.context().storageState({ path: authFile });
});

setup('Authenticate admin', async ({ page }) => {
  await page.goto('/login');
  await page.getByLabel('Email').fill('admin@example.com');
  await page.getByLabel('Password').fill('admin-password');
  await page.getByRole('button', { name: 'Sign In' }).click();
  await page.waitForURL('/admin');
  await page.context().storageState({ path: adminAuthFile });
});
```

---

## Page Object Model — Mantenibilidad en E2E

```typescript
// tests/e2e/pages/orders.page.ts
import type { Page, Locator } from '@playwright/test';

export class OrdersPage {
  readonly page: Page;
  readonly ordersList: Locator;
  readonly createOrderButton: Locator;
  readonly searchInput: Locator;

  constructor(page: Page) {
    this.page = page;
    this.ordersList      = page.getByTestId('orders-list');
    this.createOrderButton = page.getByRole('button', { name: /new order/i });
    this.searchInput     = page.getByPlaceholder(/search orders/i);
  }

  async navigate() {
    await this.page.goto('/orders');
    await this.ordersList.waitFor();
  }

  async searchOrders(query: string) {
    await this.searchInput.fill(query);
    await this.page.waitForResponse(resp =>
      resp.url().includes('/api/orders') && resp.status() === 200
    );
  }

  async getOrderCount(): Promise<number> {
    return this.ordersList.getByRole('article').count();
  }

  async openOrder(reference: string) {
    await this.page.getByText(reference).click();
    await this.page.waitForURL(/\/orders\//);
  }

  async cancelOrder(reference: string) {
    await this.openOrder(reference);
    await this.page.getByRole('button', { name: /cancel order/i }).click();
    await this.page.getByRole('button', { name: /confirm/i }).click();
    await this.page.getByText(/order cancelled/i).waitFor();
  }
}

// tests/e2e/pages/checkout.page.ts
export class CheckoutPage {
  constructor(private page: Page) {}

  async fillShippingAddress(address: {
    street: string;
    city: string;
    zip: string;
    country: string;
  }) {
    await this.page.getByLabel(/street/i).fill(address.street);
    await this.page.getByLabel(/city/i).fill(address.city);
    await this.page.getByLabel(/zip/i).fill(address.zip);
    await this.page.getByLabel(/country/i).selectOption(address.country);
  }

  async fillPaymentInfo(card: {
    number: string;
    expiry: string;
    cvv: string;
  }) {
    // Stripe iframe
    const stripeFrame = this.page.frameLocator('[data-testid="stripe-card"]');
    await stripeFrame.getByLabel(/card number/i).fill(card.number);
    await stripeFrame.getByLabel(/expiry/i).fill(card.expiry);
    await stripeFrame.getByLabel(/cvv/i).fill(card.cvv);
  }

  async placeOrder() {
    await this.page.getByRole('button', { name: /place order/i }).click();
    await this.page.waitForURL(/\/orders\/.*\/confirmation/);
    return this.page.getByTestId('order-reference').textContent();
  }
}
```

---

## Tests E2E — Flujos Críticos

```typescript
// tests/e2e/checkout.spec.ts
import { test, expect } from '@playwright/test';
import { CheckoutPage } from './pages/checkout.page';

// Reutilizar auth sin re-login en cada test
test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Checkout Flow', () => {

  test('complete checkout with valid card', async ({ page }) => {
    const checkout = new CheckoutPage(page);

    // Agregar producto al carrito
    await page.goto('/products/laptop-stand');
    await page.getByRole('button', { name: /add to cart/i }).click();
    await page.getByRole('button', { name: /checkout/i }).click();

    // Shipping
    await checkout.fillShippingAddress({
      street: '123 Main St',
      city: 'New York',
      zip: '10001',
      country: 'US',
    });
    await page.getByRole('button', { name: /continue/i }).click();

    // Payment (Stripe test card)
    await checkout.fillPaymentInfo({
      number: '4242424242424242',
      expiry: '12/28',
      cvv: '123',
    });

    const orderRef = await checkout.placeOrder();

    expect(orderRef).toMatch(/ORD-[A-Z0-9]+/);
    await expect(page.getByText(/order confirmed/i)).toBeVisible();
    await expect(page.getByText(orderRef!)).toBeVisible();
  });

  test('shows error for declined card', async ({ page }) => {
    const checkout = new CheckoutPage(page);
    // ... setup ...

    await checkout.fillPaymentInfo({
      number: '4000000000000002', // Stripe test card para decline
      expiry: '12/28',
      cvv: '123',
    });

    await page.getByRole('button', { name: /place order/i }).click();

    await expect(page.getByText(/card was declined/i)).toBeVisible();
    await expect(page).not.toHaveURL(/confirmation/);
  });

  test('redirects to login if not authenticated', async ({ page }) => {
    // Sin auth state
    await page.goto('/checkout');
    await expect(page).toHaveURL(/\/login\?.*callbackUrl.*checkout/);
  });
});
```

---

## Tests de API con Supertest (Node)

```typescript
// tests/integration/orders.integration.test.ts
import { app } from '@/app';
import { prisma } from '@/lib/db';
import request from 'supertest';
import { createTestUser, createTestProduct, generateAuthToken } from '../helpers';

describe('Orders API Integration', () => {
  let authToken: string;
  let userId: string;

  beforeAll(async () => {
    const user = await createTestUser();
    userId = user.id;
    authToken = generateAuthToken(user);
  });

  afterAll(async () => {
    await prisma.order.deleteMany({ where: { userId } });
    await prisma.user.delete({ where: { id: userId } });
    await prisma.$disconnect();
  });

  describe('POST /api/v1/orders', () => {
    it('creates order and decrements stock', async () => {
      const product = await createTestProduct({ stock: 5, priceCents: 2000 });

      const response = await request(app)
        .post('/api/v1/orders')
        .set('Authorization', `Bearer ${authToken}`)
        .send({
          items: [{ productId: product.id, quantity: 2 }],
          shippingAddress: '123 Main St',
        });

      expect(response.status).toBe(201);
      expect(response.body.data).toMatchObject({
        status: 'PENDING',
        totalCents: 4000,
      });

      const updatedProduct = await prisma.product.findUnique({
        where: { id: product.id },
      });
      expect(updatedProduct?.stock).toBe(3);
    });

    it('rollbacks stock if order creation fails', async () => {
      const product = await createTestProduct({ stock: 1, priceCents: 1000 });

      // Forzar un error en el paso de notificación sin afectar la validación
      vi.spyOn(emailService, 'sendConfirmation').mockRejectedValueOnce(
        new Error('Email service down')
      );

      const response = await request(app)
        .post('/api/v1/orders')
        .set('Authorization', `Bearer ${authToken}`)
        .send({
          items: [{ productId: product.id, quantity: 1 }],
          shippingAddress: '123 Main St',
        });

      // Dependiendo de si el email es parte de la transacción
      const updatedProduct = await prisma.product.findUnique({
        where: { id: product.id },
      });
      // Stock no debe cambiar si la transacción hizo rollback
      expect(updatedProduct?.stock).toBe(1);
    });
  });
});
```
