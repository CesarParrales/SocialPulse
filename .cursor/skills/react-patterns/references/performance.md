# Performance — Optimización de Renders

## Regla de Oro

> Medir antes de optimizar. DevTools → Profiler primero.
> La mayoría de problemas de performance React son N+1 re-renders o bundles grandes.
> Añadir memo/useMemo/useCallback sin evidencia es ruido, no solución.

---

## Cuándo Re-renderiza un Componente

```
Un componente re-renderiza cuando:
1. Su propio estado cambia (useState, useReducer)
2. Su contexto cambia
3. Su padre re-renderiza (por defecto)
4. Sus props cambian

React.memo evita (3) — el componente solo re-renderiza si sus props cambian.
useMemo cachea un valor calculado entre renders.
useCallback cachea una función entre renders.
```

---

## React.memo — Cuándo Aplica

```typescript
// Aplicar memo cuando:
// 1. El padre re-renderiza frecuentemente
// 2. El componente es costoso de renderizar
// 3. Las props del componente raramente cambian

// ✅ BIEN: componente costoso con props estables
const HeavyChart = React.memo(function HeavyChart({
  data,
  config,
}: HeavyChartProps) {
  // Renderizado costoso — muchos elementos SVG
  return <svg>{/* ... muchos paths ... */}</svg>;
});

// ❌ NO vale la pena: componente simple que casi siempre re-renderiza igual
const SimpleText = React.memo(({ text }: { text: string }) => (
  <span>{text}</span>
));
// El overhead de memo puede superar el beneficio para componentes triviales

// ✅ Comparador personalizado para props complejas
const OrderList = React.memo(
  function OrderList({ orders, onSelect }: OrderListProps) {
    return (/* ... */);
  },
  (prevProps, nextProps) =>
    prevProps.orders.length === nextProps.orders.length &&
    prevProps.orders.every((o, i) => o.id === nextProps.orders[i].id) &&
    prevProps.onSelect === nextProps.onSelect
);
```

---

## useMemo y useCallback

```typescript
// useMemo — cachear cálculos costosos
function OrderSummary({ orders }: { orders: Order[] }) {
  // ✅ BIEN: cálculo costoso sobre array grande
  const stats = useMemo(() => ({
    total: orders.reduce((sum, o) => sum + o.totalCents, 0),
    byStatus: orders.reduce((acc, o) => {
      acc[o.status] = (acc[o.status] ?? 0) + 1;
      return acc;
    }, {} as Record<string, number>),
    avgOrderValue: orders.length
      ? orders.reduce((sum, o) => sum + o.totalCents, 0) / orders.length
      : 0,
  }), [orders]); // recalcular solo si orders cambia

  // ❌ NO necesita memo: trivial
  const count = orders.length; // no useMemo aquí

  return <div>{/* usa stats */}</div>;
}

// useCallback — estabilizar callbacks para memo y efectos
function OrdersPage() {
  const [filter, setFilter] = useState<OrderFilter>({});

  // ✅ BIEN: callback pasada a componente memoizado
  const handleFilterChange = useCallback((newFilter: Partial<OrderFilter>) => {
    setFilter(prev => ({ ...prev, ...newFilter }));
  }, []); // vacío — no depende de nada del scope

  // ✅ BIEN: callback en dependencias de useEffect o useMemo
  const fetchOrders = useCallback(async () => {
    return ordersApi.list(filter);
  }, [filter]);

  // ❌ No necesita useCallback: no se pasa a memo ni a efectos
  const handleClick = () => console.log('clicked'); // re-crear en cada render está bien

  return <FilteredOrderList onFilterChange={handleFilterChange} />;
}
```

---

## Code Splitting y Lazy Loading

```typescript
// Lazy import — cargar chunk solo cuando se necesita
const OrdersPage = lazy(() => import('./features/orders/OrdersPage'));
const AdminPanel = lazy(() => import('./features/admin/AdminPanel'));
const ReportsPage = lazy(() => import('./features/reports/ReportsPage'));

// Suspense en el router
function Router() {
  return (
    <Suspense fallback={<PageSkeleton />}>
      <Routes>
        <Route path="/orders" element={<OrdersPage />} />
        <Route path="/admin/*" element={<AdminPanel />} />
        <Route path="/reports" element={<ReportsPage />} />
      </Routes>
    </Suspense>
  );
}

// Lazy para componentes pesados dentro de una página
const HeavyDataTable = lazy(() => import('./HeavyDataTable'));
const RichTextEditor = lazy(() => import('./RichTextEditor'));

function ProductForm() {
  const [showEditor, setShowEditor] = useState(false);

  return (
    <div>
      <button onClick={() => setShowEditor(true)}>Edit Description</button>
      {showEditor && (
        <Suspense fallback={<EditorSkeleton />}>
          <RichTextEditor />
        </Suspense>
      )}
    </div>
  );
}
```

---

## Virtualización — Listas Grandes

```typescript
// Para listas de más de 100 elementos — usar @tanstack/react-virtual
import { useVirtualizer } from '@tanstack/react-virtual';

function VirtualOrderList({ orders }: { orders: Order[] }) {
  const parentRef = useRef<HTMLDivElement>(null);

  const virtualizer = useVirtualizer({
    count: orders.length,
    getScrollElement: () => parentRef.current,
    estimateSize: () => 80,     // altura estimada de cada item en px
    overscan: 5,                // renderizar 5 items extra fuera del viewport
  });

  return (
    <div ref={parentRef} className="h-screen overflow-auto">
      {/* Contenedor con altura total virtual */}
      <div style={{ height: virtualizer.getTotalSize() }}>
        {virtualizer.getVirtualItems().map(vItem => (
          <div
            key={vItem.key}
            ref={virtualizer.measureElement}
            style={{
              position: 'absolute',
              top: 0,
              left: 0,
              width: '100%',
              transform: `translateY(${vItem.start}px)`,
            }}
          >
            <OrderCard order={orders[vItem.index]} />
          </div>
        ))}
      </div>
    </div>
  );
}
```

---

## Evitar Re-renders por Context

```typescript
// Context sin optimización — re-renderiza TODOS los consumidores al cambiar cualquier cosa
const AppContext = createContext({ user, settings, theme, cart });
// Si cart cambia → user, settings y theme también re-renderizan

// ✅ BIEN: separar contexts por frecuencia de cambio
const UserContext   = createContext<User | null>(null);      // cambia poco
const ThemeContext  = createContext<Theme>('light');          // cambia poco
const CartContext   = createContext<CartStore | null>(null);  // cambia frecuente

// O usar Zustand para estado que cambia frecuente — evita context del todo

// ✅ Selector en Zustand — re-render solo cuando el valor específico cambia
function CartBadge() {
  // Solo re-renderiza cuando itemCount cambia, no cuando items o total cambian
  const itemCount = useCartStore(state => state.itemCount);
  return <span>{itemCount}</span>;
}

// ✅ Colocar estado donde se usa — no elevar innecesariamente
// Si solo ProductCard usa imageZoom, el estado va en ProductCard
// No en el store global ni en el componente padre
```

---

## Skeleton Loading — UX sin Spinners

```typescript
// Skeleton que replica la estructura del contenido real
function OrderCardSkeleton() {
  return (
    <div className="rounded-lg border p-4 animate-pulse">
      <div className="flex justify-between">
        <div className="h-4 w-24 bg-gray-200 rounded" />
        <div className="h-5 w-16 bg-gray-200 rounded-full" />
      </div>
      <div className="h-3 w-16 bg-gray-200 rounded mt-2" />
      <div className="h-3 w-32 bg-gray-200 rounded mt-3" />
    </div>
  );
}

// Suspense boundary para datos asincrónicos
function OrdersSection() {
  return (
    <Suspense
      fallback={
        <div className="space-y-3">
          {Array.from({ length: 5 }).map((_, i) => (
            <OrderCardSkeleton key={i} />
          ))}
        </div>
      }
    >
      <OrdersList />  {/* Componente que usa use() o suspense-enabled query */}
    </Suspense>
  );
}
```

---

## Profiling — Detectar Problemas Reales

```
1. React DevTools → Profiler tab
   - Grabar interacción lenta
   - Identificar componentes que re-renderizan innecesariamente
   - Ver tiempo de renderizado por componente

2. Why Did You Render (desarrollo)
   npm install @welldone-software/why-did-you-render --dev

   // En index.tsx (solo dev)
   if (process.env.NODE_ENV === 'development') {
     const whyDidYouRender = require('@welldone-software/why-did-you-render');
     whyDidYouRender(React, { trackAllPureComponents: true });
   }

3. Bundle analyzer
   npx vite-bundle-analyzer  // para Vite
   npx next-bundle-analyzer  // para Next.js
   Buscar: duplicados, dependencias enormes, imports no tree-shakeable

4. Lighthouse en DevTools
   Performance score
   FCP, LCP, CLS, FID
   → actionable recommendations
```
