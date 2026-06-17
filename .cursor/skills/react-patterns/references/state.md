# Estado Global — Zustand y React Query

## La Distinción Fundamental

```
Client State (Zustand):
  - Estado que PERTENECE al frontend
  - UI: sidebar abierto, tema, idioma, carrito de compras
  - Sesión: usuario autenticado, preferencias
  - No sincronizado con servidor
  - Persiste hasta que el usuario lo cambia

Server State (React Query):
  - Datos que VIVEN en el servidor
  - Orders, products, users, cualquier dato de la API
  - Tiene caché, re-fetch, stale, refetch-on-focus
  - Siempre se puede re-obtener del servidor
  - NO duplicar en Zustand — React Query ya lo cachea
```

---

## Zustand — Estado del Cliente

```typescript
// stores/ui.store.ts — estado de UI
import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface UIStore {
  // Estado
  sidebarOpen: boolean;
  theme: 'light' | 'dark' | 'system';
  notifications: Notification[];

  // Acciones
  toggleSidebar: () => void;
  setSidebarOpen: (open: boolean) => void;
  setTheme: (theme: UIStore['theme']) => void;
  addNotification: (notification: Omit<Notification, 'id'>) => void;
  removeNotification: (id: string) => void;
}

export const useUIStore = create<UIStore>()(
  persist(  // persistir en localStorage automáticamente
    (set) => ({
      sidebarOpen: true,
      theme: 'system',
      notifications: [],

      toggleSidebar: () =>
        set(state => ({ sidebarOpen: !state.sidebarOpen })),

      setSidebarOpen: (open) =>
        set({ sidebarOpen: open }),

      setTheme: (theme) =>
        set({ theme }),

      addNotification: (notification) =>
        set(state => ({
          notifications: [
            ...state.notifications,
            { ...notification, id: crypto.randomUUID() },
          ],
        })),

      removeNotification: (id) =>
        set(state => ({
          notifications: state.notifications.filter(n => n.id !== id),
        })),
    }),
    {
      name: 'ui-storage',           // key en localStorage
      partialize: (state) => ({     // solo persistir ciertos campos
        theme: state.theme,
        sidebarOpen: state.sidebarOpen,
      }),
    }
  )
);

// Uso — selector para evitar re-renders innecesarios
function Sidebar() {
  // Solo re-renderiza cuando sidebarOpen cambia
  const isOpen = useUIStore(state => state.sidebarOpen);
  const toggle = useUIStore(state => state.toggleSidebar);
  // NO: const store = useUIStore() → re-renderiza con cualquier cambio
}
```

---

## Zustand — Carrito de Compras (ejemplo complejo)

```typescript
// stores/cart.store.ts
interface CartItem {
  productId: string;
  name: string;
  priceCents: number;
  quantity: number;
  imageUrl?: string;
}

interface CartStore {
  items: CartItem[];
  isOpen: boolean;
  coupon: { code: string; discountPercent: number } | null;

  // Computed (derivados — no guardar en estado)
  get subtotal(): number;
  get discount(): number;
  get total(): number;
  get itemCount(): number;

  // Acciones
  addItem: (product: Product, quantity?: number) => void;
  removeItem: (productId: string) => void;
  updateQuantity: (productId: string, quantity: number) => void;
  applyCoupon: (code: string, discountPercent: number) => void;
  removeCoupon: () => void;
  clear: () => void;
  open: () => void;
  close: () => void;
}

export const useCartStore = create<CartStore>()(
  persist(
    (set, get) => ({
      items: [],
      isOpen: false,
      coupon: null,

      // Getters computados
      get subtotal() {
        return get().items.reduce((sum, i) => sum + i.priceCents * i.quantity, 0);
      },
      get discount() {
        const coupon = get().coupon;
        return coupon ? Math.round(get().subtotal * coupon.discountPercent / 100) : 0;
      },
      get total() {
        return get().subtotal - get().discount;
      },
      get itemCount() {
        return get().items.reduce((sum, i) => sum + i.quantity, 0);
      },

      addItem: (product, quantity = 1) =>
        set(state => {
          const existing = state.items.find(i => i.productId === product.id);
          if (existing) {
            return {
              items: state.items.map(i =>
                i.productId === product.id
                  ? { ...i, quantity: i.quantity + quantity }
                  : i
              ),
            };
          }
          return {
            items: [...state.items, {
              productId: product.id,
              name: product.name,
              priceCents: product.priceCents,
              imageUrl: product.imageUrl,
              quantity,
            }],
          };
        }),

      removeItem: (productId) =>
        set(state => ({
          items: state.items.filter(i => i.productId !== productId),
        })),

      updateQuantity: (productId, quantity) =>
        set(state => ({
          items: quantity <= 0
            ? state.items.filter(i => i.productId !== productId)
            : state.items.map(i =>
                i.productId === productId ? { ...i, quantity } : i
              ),
        })),

      clear: () => set({ items: [], coupon: null }),
      open: () => set({ isOpen: true }),
      close: () => set({ isOpen: false }),
      applyCoupon: (code, discountPercent) => set({ coupon: { code, discountPercent } }),
      removeCoupon: () => set({ coupon: null }),
    }),
    {
      name: 'cart',
      partialize: (state) => ({ items: state.items, coupon: state.coupon }),
    }
  )
);
```

---

## React Query — Estado del Servidor

```typescript
// lib/queryClient.ts — configuración global
export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 1000 * 60 * 5,      // 5 min — no re-fetch si dato es reciente
      gcTime: 1000 * 60 * 10,        // 10 min — mantener en caché inactivo
      retry: 2,                       // reintentar 2 veces en error
      refetchOnWindowFocus: true,     // re-fetch al volver a la pestaña
      refetchOnReconnect: true,       // re-fetch al recuperar conexión
    },
    mutations: {
      retry: 0,                       // no reintentar mutations por defecto
    },
  },
});

// features/orders/api/orders.api.ts
const ordersApi = {
  list: (params: OrderFilters) =>
    apiClient.get<PaginatedResponse<Order>>('/orders', { params }),

  getById: (id: string) =>
    apiClient.get<ApiResponse<Order>>(`/orders/${id}`),

  create: (data: CreateOrderDto) =>
    apiClient.post<ApiResponse<Order>>('/orders', data),

  cancel: (id: string, reason?: string) =>
    apiClient.post<ApiResponse<Order>>(`/orders/${id}/cancel`, { reason }),
};

// features/orders/hooks/useOrders.ts
const ORDERS_KEY = 'orders';

// Query keys tipadas — factory pattern
const orderKeys = {
  all:    [ORDERS_KEY] as const,
  lists:  () => [...orderKeys.all, 'list'] as const,
  list:   (filters: OrderFilters) => [...orderKeys.lists(), filters] as const,
  detail: (id: string) => [...orderKeys.all, 'detail', id] as const,
};

// Hook para listar
export function useOrders(filters: OrderFilters = {}) {
  return useQuery({
    queryKey: orderKeys.list(filters),
    queryFn:  () => ordersApi.list(filters).then(r => r.data),
    select:   (data) => data,  // transformar si necesario
  });
}

// Hook para detalle
export function useOrder(id: string) {
  return useQuery({
    queryKey: orderKeys.detail(id),
    queryFn:  () => ordersApi.getById(id).then(r => r.data.data),
    enabled:  !!id,  // no ejecutar si no hay id
    staleTime: 30_000,  // override — detalles pueden cachear más
  });
}

// Hook para crear orden
export function useCreateOrder() {
  return useMutation({
    mutationFn: ordersApi.create,
    onSuccess: (newOrder) => {
      // Invalidar lista para que se re-fetch
      queryClient.invalidateQueries({ queryKey: orderKeys.lists() });

      // Agregar el nuevo orden al caché sin re-fetch
      queryClient.setQueryData(
        orderKeys.detail(newOrder.data.id),
        newOrder.data
      );
    },
    onError: (error: ApiError) => {
      toast.error(error.message);
    },
  });
}

// Hook para cancelar
export function useCancelOrder() {
  return useMutation({
    mutationFn: ({ id, reason }: { id: string; reason?: string }) =>
      ordersApi.cancel(id, reason),

    // Optimistic update — actualizar UI antes de respuesta del servidor
    onMutate: async ({ id }) => {
      await queryClient.cancelQueries({ queryKey: orderKeys.detail(id) });

      const previous = queryClient.getQueryData(orderKeys.detail(id));

      // Actualizar optimistamente
      queryClient.setQueryData(orderKeys.detail(id), (old: Order) => ({
        ...old,
        status: 'CANCELLED',
      }));

      return { previous, id };  // contexto para rollback
    },

    onError: (_err, _vars, context) => {
      // Revertir si hay error
      if (context?.previous) {
        queryClient.setQueryData(orderKeys.detail(context.id), context.previous);
      }
    },

    onSettled: (_data, _err, { id }) => {
      // Siempre re-fetch para tener el estado real
      queryClient.invalidateQueries({ queryKey: orderKeys.detail(id) });
    },
  });
}

// Uso en componente
function OrderDetailPage({ orderId }: { orderId: string }) {
  const { data: order, isLoading, error } = useOrder(orderId);
  const cancelOrder = useCancelOrder();

  if (isLoading) return <OrderSkeleton />;
  if (error) return <ErrorMessage error={error} />;

  return (
    <div>
      <OrderInfo order={order} />
      <button
        onClick={() => cancelOrder.mutate({ id: order.id })}
        disabled={cancelOrder.isPending}
      >
        {cancelOrder.isPending ? 'Cancelling...' : 'Cancel Order'}
      </button>
    </div>
  );
}
```

---

## Prefetching — Datos Antes de Navegar

```typescript
// Prefetch en hover de un link — dato listo cuando el usuario llega
function OrderRow({ order }: { order: Order }) {
  const queryClient = useQueryClient();

  const prefetchOrder = () => {
    queryClient.prefetchQuery({
      queryKey: orderKeys.detail(order.id),
      queryFn: () => ordersApi.getById(order.id).then(r => r.data.data),
      staleTime: 60_000,  // no prefetch si fue fetch hace < 1 min
    });
  };

  return (
    <Link
      to={`/orders/${order.id}`}
      onMouseEnter={prefetchOrder}
      onFocus={prefetchOrder}
    >
      {order.reference}
    </Link>
  );
}

// Infinite Query para feeds
export function useInfiniteOrders() {
  return useInfiniteQuery({
    queryKey: orderKeys.lists(),
    queryFn: ({ pageParam = 1 }) =>
      ordersApi.list({ page: pageParam, per_page: 20 }).then(r => r.data),
    initialPageParam: 1,
    getNextPageParam: (lastPage) =>
      lastPage.meta.current_page < lastPage.meta.last_page
        ? lastPage.meta.current_page + 1
        : undefined,
  });
}
```
