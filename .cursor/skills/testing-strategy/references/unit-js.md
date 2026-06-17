# Tests Unitarios — JavaScript / TypeScript (Vitest)

## Configuración Vitest

```typescript
// vitest.config.ts
import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
  plugins: [react()],
  test: {
    globals: true,           // describe, it, expect globales sin imports
    environment: 'jsdom',    // simular DOM del browser
    setupFiles: ['./tests/setup.ts'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'lcov', 'html'],
      exclude: [
        'node_modules/**',
        'tests/**',
        '**/*.config.*',
        '**/*.d.ts',
        '**/index.ts',       // barrel exports — sin lógica
      ],
      thresholds: {
        lines: 80,
        functions: 80,
        branches: 75,
        statements: 80,
      },
    },
  },
  resolve: {
    alias: { '@': resolve(__dirname, './src') },
  },
});

// tests/setup.ts
import '@testing-library/jest-dom';
import { cleanup } from '@testing-library/react';
import { afterEach, vi } from 'vitest';

afterEach(() => {
  cleanup();                 // limpiar DOM entre tests
  vi.clearAllMocks();        // limpiar mocks entre tests
});

// Mock global de fetch
global.fetch = vi.fn();

// Mock de módulos de terceros que usan APIs del browser
vi.mock('next/navigation', () => ({
  useRouter: () => ({ push: vi.fn(), replace: vi.fn(), back: vi.fn() }),
  usePathname: () => '/',
  useSearchParams: () => new URLSearchParams(),
  redirect: vi.fn(),
}));
```

---

## Tests de Funciones Puras

```typescript
// src/features/orders/utils/pricing.test.ts
import { describe, it, expect } from 'vitest';
import {
  calculateDiscount,
  calculateOrderTotal,
  formatMoney,
  applyVolumeDiscount,
} from './pricing';

describe('calculateDiscount', () => {
  it('applies 10% discount for orders over $100', () => {
    const items = [{ priceCents: 6000, quantity: 2 }]; // $120

    expect(calculateDiscount(items)).toBe(1200); // 10% = $12
  });

  it('applies no discount for orders under $100', () => {
    const items = [{ priceCents: 4000, quantity: 2 }]; // $80

    expect(calculateDiscount(items)).toBe(0);
  });

  it('calculates correctly with mixed quantities', () => {
    const items = [
      { priceCents: 3000, quantity: 2 },  // $60
      { priceCents: 5000, quantity: 1 },  // $50
    ]; // total $110

    expect(calculateDiscount(items)).toBe(1100); // 10% de $110
  });

  describe('with coupon', () => {
    it('stacks coupon on top of volume discount', () => {
      const items = [{ priceCents: 6000, quantity: 2 }]; // $120
      const coupon = { discountPercent: 20 };

      const discount = calculateDiscount(items, coupon);

      expect(discount).toBeGreaterThan(1200); // más que solo el 10%
    });

    it('never exceeds the order total', () => {
      const items = [{ priceCents: 1000, quantity: 1 }];
      const coupon = { discountPercent: 200 }; // descuento imposible

      const discount = calculateDiscount(items, coupon);

      expect(discount).toBeLessThanOrEqual(1000);
    });
  });
});

describe('formatMoney', () => {
  it.each([
    [0, 'USD', '$0.00'],
    [100, 'USD', '$1.00'],
    [8550, 'USD', '$85.50'],
    [1000, 'EUR', '€10.00'],
  ])('formats %i cents in %s as %s', (cents, currency, expected) => {
    expect(formatMoney(cents, currency)).toBe(expected);
  });
});
```

---

## Tests de Hooks con React Testing Library

```typescript
// src/shared/hooks/useDebounce.test.ts
import { renderHook, act } from '@testing-library/react';
import { vi, describe, it, expect, beforeEach, afterEach } from 'vitest';
import { useDebounce } from './useDebounce';

describe('useDebounce', () => {
  beforeEach(() => vi.useFakeTimers());
  afterEach(() => vi.useRealTimers());

  it('returns initial value immediately', () => {
    const { result } = renderHook(() => useDebounce('initial', 300));
    expect(result.current).toBe('initial');
  });

  it('debounces value changes', () => {
    const { result, rerender } = renderHook(
      ({ value }) => useDebounce(value, 300),
      { initialProps: { value: 'first' } }
    );

    rerender({ value: 'second' });
    expect(result.current).toBe('first'); // todavía el valor anterior

    act(() => vi.advanceTimersByTime(300));
    expect(result.current).toBe('second'); // ahora actualizado
  });

  it('only applies last change within debounce window', () => {
    const { result, rerender } = renderHook(
      ({ value }) => useDebounce(value, 300),
      { initialProps: { value: 'a' } }
    );

    rerender({ value: 'ab' });
    act(() => vi.advanceTimersByTime(100));
    rerender({ value: 'abc' });
    act(() => vi.advanceTimersByTime(100));
    rerender({ value: 'abcd' });

    expect(result.current).toBe('a'); // no ha pasado el debounce

    act(() => vi.advanceTimersByTime(300));
    expect(result.current).toBe('abcd'); // solo el último
  });
});

// Tests de useCart con Zustand
import { renderHook, act } from '@testing-library/react';
import { useCartStore } from '@/store/cart.store';

describe('CartStore', () => {
  beforeEach(() => {
    useCartStore.setState({ items: [], coupon: null }); // reset
  });

  const mockProduct = { id: '1', name: 'Product', priceCents: 1000 };

  it('adds item to cart', () => {
    const { result } = renderHook(() => useCartStore());

    act(() => result.current.addItem(mockProduct, 2));

    expect(result.current.items).toHaveLength(1);
    expect(result.current.items[0].quantity).toBe(2);
  });

  it('increments quantity for existing item', () => {
    const { result } = renderHook(() => useCartStore());

    act(() => {
      result.current.addItem(mockProduct, 1);
      result.current.addItem(mockProduct, 2);
    });

    expect(result.current.items[0].quantity).toBe(3);
  });

  it('calculates total correctly', () => {
    const { result } = renderHook(() => useCartStore());

    act(() => result.current.addItem(mockProduct, 3));

    expect(result.current.total).toBe(3000);
  });
});
```

---

## Tests de Componentes React

```typescript
// src/features/orders/components/OrderCard.test.tsx
import { render, screen, fireEvent, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';
import { OrderCard } from './OrderCard';

const mockOrder: Order = {
  id: 'ord_123',
  reference: 'ORD-001',
  status: 'PENDING',
  total: { amount: 8500, currency: 'USD', formatted: '$85.00' },
  createdAt: '2024-01-15T10:00:00Z',
};

describe('OrderCard', () => {
  it('renders order reference and total', () => {
    render(<OrderCard order={mockOrder} />);

    expect(screen.getByText('ORD-001')).toBeInTheDocument();
    expect(screen.getByText('$85.00')).toBeInTheDocument();
  });

  it('shows cancel button only for pending orders', () => {
    const onCancel = vi.fn();

    render(<OrderCard order={mockOrder} onCancel={onCancel} />);
    expect(screen.getByRole('button', { name: /cancel/i })).toBeInTheDocument();

    render(<OrderCard order={{ ...mockOrder, status: 'DELIVERED' }} onCancel={onCancel} />);
    expect(screen.queryByRole('button', { name: /cancel/i })).not.toBeInTheDocument();
  });

  it('calls onCancel with order id when cancel is clicked', async () => {
    const user = userEvent.setup();
    const onCancel = vi.fn();

    render(<OrderCard order={mockOrder} onCancel={onCancel} />);
    await user.click(screen.getByRole('button', { name: /cancel/i }));

    expect(onCancel).toHaveBeenCalledOnce();
    expect(onCancel).toHaveBeenCalledWith('ord_123');
  });

  it('shows loading state while cancelling', () => {
    render(<OrderCard order={mockOrder} onCancel={vi.fn()} isLoading />);

    const button = screen.getByRole('button', { name: /cancel/i });
    expect(button).toBeDisabled();
    expect(screen.getByText(/cancelling/i)).toBeInTheDocument();
  });

  it('applies custom className', () => {
    const { container } = render(
      <OrderCard order={mockOrder} className="custom-class" />
    );
    expect(container.firstChild).toHaveClass('custom-class');
  });
});
```

---

## Tests de Forms

```typescript
// src/features/orders/components/CreateOrderForm.test.tsx
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { CreateOrderForm } from './CreateOrderForm';

function renderWithProviders(ui: React.ReactElement) {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false } },
  });
  return render(
    <QueryClientProvider client={queryClient}>{ui}</QueryClientProvider>
  );
}

describe('CreateOrderForm', () => {
  it('shows validation error when submitted empty', async () => {
    const user = userEvent.setup();
    renderWithProviders(<CreateOrderForm onSuccess={vi.fn()} />);

    await user.click(screen.getByRole('button', { name: /place order/i }));

    await waitFor(() => {
      expect(screen.getByText(/at least one item/i)).toBeInTheDocument();
      expect(screen.getByText(/address is required/i)).toBeInTheDocument();
    });
  });

  it('calls onSuccess with created order after valid submission', async () => {
    const user = userEvent.setup();
    const onSuccess = vi.fn();
    const mockOrder = { id: 'ord_1', reference: 'ORD-001' };

    vi.mocked(createOrderMutation).mockResolvedValueOnce(mockOrder);
    renderWithProviders(<CreateOrderForm onSuccess={onSuccess} />);

    await user.type(screen.getByLabelText(/shipping address/i), '123 Main St');
    await user.click(screen.getByRole('button', { name: /place order/i }));

    await waitFor(() => {
      expect(onSuccess).toHaveBeenCalledWith(mockOrder);
    });
  });
});
```
