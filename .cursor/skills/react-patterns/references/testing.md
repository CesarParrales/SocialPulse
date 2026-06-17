# Testing — Vitest + React Testing Library

## Filosofía

```
Testea lo que el USUARIO ve y hace, no la implementación:
  ✅ "al hacer click en Cancel, aparece el mensaje de confirmación"
  ❌ "el state interno isOpen cambia a true"

Si un refactor sin cambio de comportamiento rompe el test → el test estaba mal.
```

## Setup

```bash
npm i -D vitest @testing-library/react @testing-library/user-event @testing-library/jest-dom jsdom
```

```typescript
// vitest.config.ts
import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: './src/test/setup.ts',
  },
});

// src/test/setup.ts
import '@testing-library/jest-dom/vitest';
```

```bash
# Ejecutar
npx vitest run          # una pasada (CI)
npx vitest              # watch mode
npx vitest run --coverage
```

---

## render / screen / userEvent — Patrón Base

```typescript
// OrderCard.test.tsx
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';
import { OrderCard } from './OrderCard';

const mockOrder = {
  id: 'ord_1',
  reference: 'ORD-001',
  status: 'PENDING' as const,
  total: 4999,
};

describe('OrderCard', () => {
  it('muestra la referencia y el total', () => {
    render(<OrderCard order={mockOrder} />);

    expect(screen.getByText('ORD-001')).toBeInTheDocument();
    expect(screen.getByText('$49.99')).toBeInTheDocument();
  });

  it('llama onCancel con el id al hacer click', async () => {
    const user = userEvent.setup();
    const onCancel = vi.fn();

    render(<OrderCard order={mockOrder} onCancel={onCancel} />);

    await user.click(screen.getByRole('button', { name: /cancel order/i }));

    expect(onCancel).toHaveBeenCalledWith('ord_1');
  });

  it('no muestra el botón cancelar si el pedido no es PENDING', () => {
    render(<OrderCard order={{ ...mockOrder, status: 'SHIPPED' }} onCancel={vi.fn()} />);

    expect(screen.queryByRole('button', { name: /cancel/i })).not.toBeInTheDocument();
  });
});
```

Reglas:
- `userEvent.setup()` siempre, nunca `fireEvent` (userEvent simula interacción real: focus, keydown, etc.).
- `getBy*` cuando DEBE existir, `queryBy*` para afirmar ausencia, `findBy*` para elementos async.

---

## Queries por Rol — Prioridad

```
1. getByRole('button', { name: /submit/i })   ← preferida, valida accesibilidad
2. getByLabelText(/email/i)                    ← formularios
3. getByPlaceholderText / getByText            ← contenido
4. getByTestId('order-row')                    ← último recurso
```

```typescript
// Roles comunes
screen.getByRole('button', { name: /guardar/i });
screen.getByRole('textbox', { name: /email/i });       // input text con label
screen.getByRole('checkbox', { name: /acepto/i });
screen.getByRole('heading', { level: 2, name: /pedidos/i });
screen.getByRole('link', { name: /ver detalle/i });
screen.getByRole('option', { name: /españa/i });
screen.getByRole('alert');                              // mensajes de error

// Debug: ver los roles disponibles del render actual
screen.logTestingPlaygroundURL(); // o screen.debug()
```

---

## Tests de Hooks con renderHook

```typescript
import { renderHook, act, waitFor } from '@testing-library/react';
import { useDebounce } from './useDebounce';

describe('useDebounce', () => {
  it('retrasa la actualización del valor', () => {
    vi.useFakeTimers();

    const { result, rerender } = renderHook(
      ({ value }) => useDebounce(value, 300),
      { initialProps: { value: 'a' } }
    );

    rerender({ value: 'ab' });
    expect(result.current).toBe('a');        // aún no pasó el delay

    act(() => vi.advanceTimersByTime(300));
    expect(result.current).toBe('ab');

    vi.useRealTimers();
  });
});

// Hook con estado — envolver updates en act()
it('incrementa el contador', () => {
  const { result } = renderHook(() => useCounter());

  act(() => result.current.increment());

  expect(result.current.count).toBe(1);
});
```

---

## Mock de fetch y React Query

```typescript
// Wrapper con QueryClient fresco por test (sin retries ni caché compartida)
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

function createWrapper() {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: { retry: false, gcTime: 0 },
      mutations: { retry: false },
    },
  });
  return ({ children }: { children: React.ReactNode }) => (
    <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
  );
}

// Mock de fetch global
describe('useOrders', () => {
  beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn());
  });

  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('devuelve los pedidos de la API', async () => {
    vi.mocked(fetch).mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: [mockOrder] }),
    } as Response);

    const { result } = renderHook(() => useOrders(), { wrapper: createWrapper() });

    await waitFor(() => expect(result.current.isSuccess).toBe(true));
    expect(result.current.data).toHaveLength(1);
  });

  it('expone el error cuando la API falla', async () => {
    vi.mocked(fetch).mockResolvedValueOnce({
      ok: false,
      status: 500,
      json: async () => ({ message: 'Server error' }),
    } as Response);

    const { result } = renderHook(() => useOrders(), { wrapper: createWrapper() });

    await waitFor(() => expect(result.current.isError).toBe(true));
  });
});
```

Para suites grandes, considera MSW (Mock Service Worker): intercepta a nivel de red y los mocks se comparten entre tests y Storybook. Para pocos tests, `vi.stubGlobal('fetch', ...)` es suficiente.

```typescript
// Mock de módulo (API client propio en vez de fetch)
vi.mock('@/features/orders/api/orders.api', () => ({
  fetchOrders: vi.fn().mockResolvedValue([mockOrder]),
}));
```

---

## Componente con Formulario (RHF + Zod)

```typescript
it('muestra error de validación y no envía', async () => {
  const user = userEvent.setup();
  const onSubmit = vi.fn();

  render(<LoginForm onSubmit={onSubmit} />);

  await user.type(screen.getByLabelText(/email/i), 'no-es-email');
  await user.click(screen.getByRole('button', { name: /sign in/i }));

  expect(await screen.findByText(/email inválido/i)).toBeInTheDocument();
  expect(onSubmit).not.toHaveBeenCalled();
});

it('envía con datos válidos', async () => {
  const user = userEvent.setup();
  const onSubmit = vi.fn();

  render(<LoginForm onSubmit={onSubmit} />);

  await user.type(screen.getByLabelText(/email/i), 'ana@test.com');
  await user.type(screen.getByLabelText(/password/i), 'secret123');
  await user.click(screen.getByRole('button', { name: /sign in/i }));

  await waitFor(() =>
    expect(onSubmit).toHaveBeenCalledWith(
      expect.objectContaining({ email: 'ana@test.com' })
    )
  );
});
```

---

## Qué NO Testear

```
❌ Implementación interna: nombres de state, llamadas a setState, orden de hooks
❌ Librerías de terceros: que React Query cachea, que Zod valida un email
❌ Estilos/CSS: clases de Tailwind, colores (eso es visual regression, otra herramienta)
❌ Snapshots gigantes de árboles completos — frágiles y nadie los revisa
❌ Detalles de markup sin valor semántico: cantidad de divs, estructura interna
❌ Mocks de todo: si todo está mockeado, el test no prueba nada

✅ Comportamiento observable, contratos de props, estados (loading/error/empty/datos),
   validación de formularios, navegación condicional, accesibilidad por roles
```

---

## Checklist de Test de Componente

- [ ] Happy path: render con datos y la interacción principal
- [ ] Estados: loading, error, vacío
- [ ] Callbacks llamados con los argumentos correctos
- [ ] Queries por rol/label (no por testId salvo necesidad)
- [ ] Sin `await` faltantes en `userEvent` / `findBy*` (warnings de act = test mal escrito)
- [ ] `npx vitest run` pasa en verde
