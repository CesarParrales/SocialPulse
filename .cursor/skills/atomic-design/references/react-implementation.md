# Atomic Design en React

## Estructura de Directorios

```
src/
├── components/
│   ├── atoms/              # Solo primitivos del design system
│   │   ├── Button/
│   │   │   ├── Button.tsx
│   │   │   ├── Button.stories.tsx
│   │   │   ├── Button.test.tsx
│   │   │   └── index.ts
│   │   ├── Input/
│   │   ├── Icon/
│   │   ├── Badge/
│   │   └── index.ts        # Re-export de todos los átomos
│   │
│   ├── molecules/          # Combinaciones funcionales
│   │   ├── FormField/
│   │   ├── SearchBar/
│   │   ├── PaginationNav/
│   │   └── index.ts
│   │
│   ├── organisms/          # Secciones de UI con dominio
│   │   ├── AppHeader/
│   │   ├── AppSidebar/
│   │   ├── ProductCard/
│   │   ├── DataTable/
│   │   └── index.ts
│   │
│   ├── templates/          # Layouts sin datos
│   │   ├── DashboardTemplate/
│   │   ├── AuthTemplate/
│   │   ├── MarketingTemplate/
│   │   └── index.ts
│   │
│   └── pages/              # (alternativa: en app/)
│       ├── OrdersPage/
│       └── ProductDetailPage/
│
├── features/               # Organismos específicos de dominio
│   ├── orders/
│   │   ├── OrdersTable/    ← organismo del dominio orders
│   │   ├── OrderCard/
│   │   └── OrderFilters/
│   └── products/
│
└── app/                    # Next.js App Router (las páginas reales)
    ├── (app)/
    │   ├── orders/
    │   │   └── page.tsx    ← PÁGINA que instancia template + organismos
    │   └── products/
    └── layout.tsx
```

---

## Reglas de Import por Nivel

```typescript
// ÁTOMOS — solo importan del design system o librerías externas
// ✅ Correcto:
import { cn } from '@/lib/utils';

// ❌ Nunca en un átomo:
import { FormField } from '../molecules';    // importar molécula
import { useOrdersQuery } from '@/features'; // datos de dominio

// MOLÉCULAS — solo importan átomos
// ✅ Correcto:
import { Input, Label, Icon } from '../atoms';
import { cn } from '@/lib/utils';

// ❌ Nunca en una molécula:
import { AppHeader } from '../organisms';    // importar organismo
import { useOrders } from '@/features/orders'; // datos de dominio

// ORGANISMOS — pueden importar moléculas y átomos
// ✅ Correcto:
import { Button, Badge, Avatar } from '../atoms';
import { FormField, SearchBar } from '../molecules';
import { useLocalStorage } from '@/hooks'; // hooks de utilidad
// Los organismos de features SÍ pueden importar queries
import { useOrdersQuery } from './orders.queries'; // datos de su dominio

// ❌ Nunca en un organismo:
import { DashboardTemplate } from '../templates'; // importar template

// TEMPLATES — pueden importar todo excepto páginas
// Los templates reciben todo por children/props
// Raramente necesitan importar componentes específicos

// PÁGINAS — pueden importar todo
import { DashboardTemplate } from '../templates';
import { OrdersTable } from '../organisms';
import { useOrders } from '@/features/orders';
```

---

## El Patrón Container/Presentational en Atomic Design

```typescript
// Los organismos tienen dos variantes comunes:

// VARIANTE 1 — Organismo puramente presentacional
// Recibe todo por props, sin lógica de fetching
// Más reutilizable, más fácil de testear

interface OrdersTableProps {
  orders: Order[];
  isLoading?: boolean;
  onCancelOrder: (id: string) => void;
  pagination: PaginationMeta;
  onPageChange: (page: number) => void;
}

function OrdersTable({ orders, isLoading, onCancelOrder, pagination, onPageChange }: OrdersTableProps) {
  // Solo renderizado y interacción local
  return (/* ... */);
}

// VARIANTE 2 — Organismo conectado (Container)
// Hace su propio fetching y gestiona su estado
// Menos reutilizable pero más autónomo

function ConnectedOrdersTable({ userId }: { userId: string }) {
  const { data, isLoading } = useOrders({ userId });
  const cancelOrder = useCancelOrder();

  return (
    <OrdersTable
      orders={data?.data ?? []}
      isLoading={isLoading}
      onCancelOrder={(id) => cancelOrder.mutate(id)}
      pagination={data?.meta}
      onPageChange={(page) => /* actualizar params */}
    />
  );
}

// Cuándo usar cada uno:
// Presentational → en Storybook, en testing, cuando el padre maneja el estado
// Connected → en páginas reales, cuando el organismo es autónomo
```

---

## Composición vs Herencia en React

```typescript
// Atomic Design favorece la COMPOSICIÓN sobre la herencia

// ❌ MAL: herencia de componente
class EnhancedButton extends Button {
  render() {
    return <div className="enhanced-wrapper"><super.render()</div>;
  }
}

// ✅ BIEN: composición con children y props
function IconButton({ icon, children, ...buttonProps }: IconButtonProps) {
  return (
    <Button {...buttonProps}>
      <Icon name={icon} />
      {children}
    </Button>
  );
}

// ✅ MEJOR: render props para composición flexible
function Button({ leftSlot, rightSlot, children, ...props }: ButtonProps) {
  return (
    <button {...props}>
      {leftSlot}
      {children}
      {rightSlot}
    </button>
  );
}

// Uso:
<Button leftSlot={<Icon name="plus" />}>Create Order</Button>
<Button rightSlot={<Spinner />} isLoading>Saving...</Button>
```

---

## Feature-First vs Atomic-First

```
La tensión común: ¿uso atomic design puro o feature-based?

ATOMIC PURO:
  src/components/atoms/
  src/components/molecules/
  src/components/organisms/
  src/features/  (no hay aquí, todo en components/)

Problema: los organismos de dominio están mezclados con los genéricos
  → ¿Dónde va OrdersTable? ¿Es un organismo o una feature?

HÍBRIDO (el más práctico en proyectos reales):

  src/
  ├── components/          # Componentes genéricos reutilizables
  │   ├── ui/              # Átomos + moléculas (design system)
  │   └── layout/          # Templates + layout organisms
  │
  └── features/            # Organismos específicos de dominio
      ├── orders/
      │   ├── components/  # Organismos del dominio orders
      │   ├── hooks/
      │   └── api/
      └── products/
          ├── components/  # Organismos del dominio products
          └── ...

Por qué funciona mejor:
→ Los componentes genéricos (átomos/moléculas) en components/ui/
→ Los componentes de dominio (organismos) co-localizados con su feature
→ Más fácil de encontrar y mantener
→ Menos "¿esto es un átomo o una molécula?" como categoría estricta
→ Más "¿esto es genérico o específico de esta feature?"
```

---

## Testing por Nivel Atómico

```typescript
// La estrategia de testing cambia según el nivel

// ÁTOMOS → Unit tests puros
// Sin rendering context, sin providers
describe('Button', () => {
  it('renders with correct variant class', () => {
    render(<Button variant="primary">Save</Button>);
    expect(screen.getByRole('button')).toHaveClass('btn-primary');
  });

  it('shows spinner when loading', () => {
    render(<Button isLoading>Save</Button>);
    expect(screen.getByRole('img', { name: /loading/i })).toBeInTheDocument();
  });
});

// MOLÉCULAS → Unit tests con interacción
describe('FormField', () => {
  it('shows error message when error prop provided', () => {
    render(
      <FormField label="Email" htmlFor="email" error="Invalid email">
        <Input id="email" />
      </FormField>
    );
    expect(screen.getByText('Invalid email')).toBeInTheDocument();
  });
});

// ORGANISMOS → Integration tests (con providers si necesitan)
describe('LoginForm', () => {
  it('submits with valid credentials', async () => {
    const onSubmit = vi.fn();
    render(<LoginForm onSubmit={onSubmit} />);

    await userEvent.type(screen.getByLabelText('Email'), 'user@test.com');
    await userEvent.type(screen.getByLabelText('Password'), 'password123');
    await userEvent.click(screen.getByRole('button', { name: /sign in/i }));

    expect(onSubmit).toHaveBeenCalledWith({ email: 'user@test.com', password: 'password123' });
  });
});

// PÁGINAS → E2E tests o integration tests con mocks de API
// Ver testing-strategy skill para el protocolo completo
```
