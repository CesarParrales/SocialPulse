---
name: react-patterns
description: >
  Guía de patrones React modernos: hooks, estado global, performance, composición y
  testing con Vitest + React Testing Library. Usar cuando el usuario mencione React,
  hooks, componentes, estado, context, Zustand, Redux, React Query, formularios con
  React Hook Form, validación con Zod, optimización de renders, lazy loading, tests
  de componentes, o cuando diga "cómo organizo el estado", "cómo evito re-renders",
  "cómo estructuro mis componentes", "cómo manejo formularios en React", "cómo
  consumo una API en React", "cómo testeo un componente", o cualquier variante.
---

# React Patterns Skill

Patrones de producción para aplicaciones React modernas con TypeScript.

**Hooks — patrones y custom hooks → `references/hooks.md`**
**Estado global — Zustand y React Query → `references/state.md`**
**Formularios — React Hook Form + Zod → `references/forms.md`**
**Performance — renders y optimización → `references/performance.md`**
**Testing — Vitest + React Testing Library → `references/testing.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — libs UI (`@tanstack/react-query`, Zustand, etc.).
2. Estructura `resources/js/` o `features/` según project-memory.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** patrones de estado reutilizables → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory; inspeccionar `package.json` antes de añadir libs.
1. **Clasificar el problema** según el tipo de tarea (ver tabla de selección):

| Tarea | Reference |
|---|---|
| Estado de servidor (fetch, caché, mutations) | `references/state.md` → React Query |
| Estado de cliente (UI, carrito, preferencias) | `references/state.md` → Zustand |
| Formulario con validación | `references/forms.md` → RHF + Zod |
| Performance / re-renders | `references/performance.md` → **Profiler PRIMERO**, optimizar después |
| Lógica reutilizable / efectos | `references/hooks.md` → custom hooks |
| Tests de componentes/hooks | `references/testing.md` → Vitest + RTL |

2. **Detectar el stack actual**: revisa `package.json` (`rg '"react-query"|"@tanstack|zustand|react-hook-form|zod|vitest' package.json`) antes de introducir librerías nuevas; reutiliza lo que ya existe.
3. **Implementar** siguiendo la reference: componentes pequeños, estado lo más abajo posible, server state separado de client state.
4. **Para performance**: mide ANTES con React DevTools Profiler (renders, tiempos), aplica una optimización a la vez (`memo`/`useMemo`/virtualización/code splitting), y vuelve a medir. Sin medición previa, no optimices.
5. **Testear el comportamiento nuevo** con `references/testing.md`: happy path + estados (loading/error/empty). Gate: ejecuta `npx vitest run` y verifica que pasa en verde.
6. **Verificar tipos y lint**: ejecuta `npx tsc --noEmit` y el linter del proyecto; corrige antes de cerrar.
7. **Validación y cierre** — ejecutar `## Validación`; registrar gaps en `LEARNINGS.md`.

---

## Defaults si falta contexto

Asume y **declara** estos supuestos en lugar de preguntar (máx. 1 pregunta solo si es bloqueante):

- Estado de servidor → **React Query** (`@tanstack/react-query`); nunca `useEffect` + `useState` para fetching.
- Estado global de cliente → **Zustand**; Context solo para valores casi estáticos (tema, sesión).
- Formularios → **React Hook Form + Zod** (modo uncontrolled).
- Estructura → **feature-first** (`features/<dominio>/`), como la de abajo.
- Testing → **Vitest + React Testing Library**, queries por rol.
- Memoización → solo con evidencia de Profiler, no preventiva.

---

## Principios React Modernos

```
1. Componentes pequeños y enfocados — una responsabilidad por componente
2. Composición sobre herencia — siempre
3. Estado lo más abajo posible — no elevar innecesariamente
4. Server state ≠ Client state — manejarlos con herramientas distintas
5. Colocación — código cerca de donde se usa
6. Tipos explícitos — TypeScript estricto, sin `any`
7. Memoización solo cuando hay evidencia — no por defecto
```

---

## Estructura de Proyecto

```
src/
├── app/                        # Configuración top-level
│   ├── App.tsx                 # Root component
│   ├── Router.tsx              # Definición de rutas
│   └── Providers.tsx           # QueryClient, Theme, Auth, etc.
│
├── features/                   # Módulos por dominio (feature-first)
│   ├── orders/
│   │   ├── components/         # UI específica de orders
│   │   │   ├── OrderCard.tsx
│   │   │   ├── OrderList.tsx
│   │   │   └── OrderStatus.tsx
│   │   ├── hooks/              # Hooks del dominio
│   │   │   ├── useOrders.ts
│   │   │   └── useOrderDetail.ts
│   │   ├── api/                # Llamadas a API
│   │   │   └── orders.api.ts
│   │   ├── store/              # Estado local del módulo
│   │   │   └── orders.store.ts
│   │   ├── types/              # TypeScript types
│   │   │   └── order.types.ts
│   │   └── index.ts            # Barrel export
│   │
│   └── auth/
│
├── shared/                     # Compartido entre features
│   ├── components/             # UI genérica reutilizable
│   │   ├── ui/                 # Primitivos (Button, Input, Modal)
│   │   └── layout/             # Layout components (Header, Sidebar)
│   ├── hooks/                  # Hooks genéricos
│   │   ├── useDebounce.ts
│   │   ├── useLocalStorage.ts
│   │   └── useIntersectionObserver.ts
│   └── utils/                  # Helpers
│
└── lib/                        # Configuración de librerías
    ├── api/                    # Axios instance, interceptors
    ├── auth/                   # Auth helpers
    └── queryClient.ts          # React Query config
```

---

## Componentes — Patrones Fundamentales

### Componente bien tipado

```typescript
// features/orders/components/OrderCard.tsx
interface OrderCardProps {
  order: Order;
  onCancel?: (orderId: string) => void;
  isLoading?: boolean;
  className?: string;
}

export function OrderCard({
  order,
  onCancel,
  isLoading = false,
  className,
}: OrderCardProps) {
  const handleCancel = () => {
    onCancel?.(order.id); // optional chaining — no llama si no se pasó
  };

  return (
    <div className={cn('rounded-lg border p-4', className)}>
      <div className="flex items-center justify-between">
        <h3 className="font-medium">{order.reference}</h3>
        <OrderStatusBadge status={order.status} />
      </div>
      <p className="text-sm text-gray-500 mt-1">
        {formatMoney(order.total)}
      </p>
      {onCancel && order.status === 'PENDING' && (
        <button
          onClick={handleCancel}
          disabled={isLoading}
          className="mt-3 text-sm text-red-600 hover:text-red-800"
        >
          {isLoading ? 'Cancelling...' : 'Cancel Order'}
        </button>
      )}
    </div>
  );
}
```

### Compound Components — para UI compleja relacionada

```typescript
// Compound pattern: Table con sub-componentes
interface TableContextValue {
  selectedRows: Set<string>;
  toggleRow: (id: string) => void;
  selectAll: () => void;
}

const TableContext = createContext<TableContextValue | null>(null);

function useTableContext() {
  const ctx = useContext(TableContext);
  if (!ctx) throw new Error('Must be used within Table');
  return ctx;
}

// Root component
function Table({ children, data }: TableProps) {
  const [selectedRows, setSelectedRows] = useState<Set<string>>(new Set());

  const toggleRow = (id: string) =>
    setSelectedRows(prev => {
      const next = new Set(prev);
      next.has(id) ? next.delete(id) : next.add(id);
      return next;
    });

  const selectAll = () =>
    setSelectedRows(new Set(data.map(row => row.id)));

  return (
    <TableContext.Provider value={{ selectedRows, toggleRow, selectAll }}>
      <table className="w-full">{children}</table>
    </TableContext.Provider>
  );
}

// Sub-componentes
function TableRow({ row }: { row: DataRow }) {
  const { selectedRows, toggleRow } = useTableContext();
  return (
    <tr
      className={cn('border-b', selectedRows.has(row.id) && 'bg-blue-50')}
      onClick={() => toggleRow(row.id)}
    >
      {/* ... */}
    </tr>
  );
}

// Namespace — API limpia
Table.Row = TableRow;
Table.Header = TableHeader;
Table.Footer = TableFooter;

// Uso
<Table data={orders}>
  <Table.Header />
  {orders.map(order => <Table.Row key={order.id} row={order} />)}
  <Table.Footer />
</Table>
```

### Render Props — para lógica compartida con UI variable

```typescript
// Compartir lógica de paginación con UI diferente en cada lugar
function Paginated<T>({
  query,
  render,
  renderEmpty,
  renderLoading,
}: {
  query: UseQueryResult<PaginatedResponse<T>>;
  render: (items: T[]) => React.ReactNode;
  renderEmpty?: () => React.ReactNode;
  renderLoading?: () => React.ReactNode;
}) {
  if (query.isLoading) {
    return renderLoading?.() ?? <DefaultSkeleton />;
  }

  if (query.isError) {
    return <ErrorMessage error={query.error} onRetry={query.refetch} />;
  }

  const items = query.data?.data ?? [];

  if (items.length === 0) {
    return renderEmpty?.() ?? <EmptyState />;
  }

  return (
    <>
      {render(items)}
      <Pagination meta={query.data?.meta} />
    </>
  );
}

// Uso — UI diferente, lógica idéntica
<Paginated
  query={useOrders()}
  render={(orders) => orders.map(o => <OrderCard key={o.id} order={o} />)}
  renderEmpty={() => <p>No orders yet. Place your first order!</p>}
/>
```

---

## TypeScript Strict — Tipos Útiles

```typescript
// Tipos de utilidad para APIs
type ApiResponse<T> = {
  data: T;
  meta?: PaginationMeta;
};

type ApiError = {
  message: string;
  errorCode?: string;
  errors?: Record<string, string[]>;
};

// Discriminated unions para estado
type AsyncState<T> =
  | { status: 'idle' }
  | { status: 'loading' }
  | { status: 'success'; data: T }
  | { status: 'error'; error: string };

// Uso con type narrowing
function renderState<T>(state: AsyncState<T>, render: (data: T) => JSX.Element) {
  switch (state.status) {
    case 'idle':    return null;
    case 'loading': return <Spinner />;
    case 'error':   return <Error message={state.error} />;
    case 'success': return render(state.data);
  }
}

// Tipos para props opcionales con valores por defecto
type ButtonVariant = 'primary' | 'secondary' | 'ghost' | 'danger';
type ButtonSize = 'sm' | 'md' | 'lg';

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant;
  size?: ButtonSize;
  isLoading?: boolean;
  leftIcon?: React.ReactNode;
}
```

---

## Checklist de Componente Listo para Producción

- [ ] Props tipadas con interface explícita
- [ ] Valores por defecto en parámetros destructurados
- [ ] Manejo de estados: loading, error, empty, populated
- [ ] Accesibilidad: aria-label, role, keyboard navigation
- [ ] className prop para extensión desde el padre
- [ ] sin lógica de negocio en el componente (va en hooks/services)
- [ ] Test cubriendo happy path + edge cases principales
- [ ] Export nombrado (no default export en componentes)

---

## Ejemplo input → output

**Input:** "Formulario de invitación con email y rol, validación Zod."

**Output:** feature `features/invitations/` con RHF + Zod; React Query mutation; tests RTL happy + 422. Gates: `npx vitest run --filter=InvitationForm` exit 0; `npx tsc --noEmit` exit 0.

---

## Validación

| Gate | Comando | Criterio |
|------|---------|----------|
| Tests | `npx vitest run` / `npm test` | exit 0 |
| Types | `npx tsc --noEmit` | exit 0 |
| Lint | `npm run lint` (si existe) | exit 0 |
| Performance | React Profiler (si optimizaste) | tabla antes/después en entregable |

---

## Entregable

Para implementaciones (componentes, hooks, formularios):

```markdown
## Implementación React — <feature>

**Patrón aplicado**: <compound / render props / custom hook / ...> y por qué
**Estado**: qué vive en React Query / Zustand / local
**Tests**: archivos creados y casos cubiertos

### Verificación
- [ ] `npx vitest run` en verde
- [ ] `npx tsc --noEmit` sin errores
```

Para optimizaciones de performance, SIEMPRE tabla antes/después con métrica medida:

```markdown
## Optimización — <componente/ruta>

| Métrica | Antes | Después | Cómo se midió |
|---|---|---|---|
| Re-renders de <X> por interacción | 14 | 2 | React DevTools Profiler |
| Tiempo de render commit | 120ms | 35ms | Profiler (flamegraph) |
| Bundle del chunk | 480KB | 210KB | `npx vite-bundle-visualizer` o build output |

**Cambios**: <memo en X / virtualización con Y / lazy() en Z>
**Comando de verificación**: `npm run build` + Profiler sobre <interacción concreta>
```

---

## Skills relacionadas

- `nextjs-fullstack` — Server Components, App Router y data fetching en Next.js
- `testing-strategy` — estrategia global de tests (pirámide, e2e, cobertura)
- `performance-web` — Core Web Vitals y performance más allá de React
- `design-system` — tokens y primitivos UI que consumen estos patrones
- `atomic-design` — organización de componentes por niveles
- `ui-web-modern` — implementación visual de las interfaces

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
