# Server vs Client Components

## La Decisión Central de App Router

```
SERVER COMPONENT (por defecto):
  ✅ Acceso directo a BD (Prisma, SQL)
  ✅ Variables de entorno secretas
  ✅ Dependencias pesadas solo en servidor (no van al bundle del cliente)
  ✅ Sin hydration — HTML directo al browser
  ✅ Mejor para: fetching, layouts, contenido estático, lógica de negocio
  ❌ Sin hooks (useState, useEffect)
  ❌ Sin event handlers (onClick, onChange)
  ❌ Sin APIs del browser (window, document, localStorage)

CLIENT COMPONENT ('use client'):
  ✅ Interactividad — hooks, eventos, estado
  ✅ APIs del browser
  ✅ Librerías que usan window/document
  ❌ No puede acceder a BD directamente
  ❌ Va al bundle JS del cliente — pesa más
  ❌ Requiere hydration
```

---

## Árbol de Decisión: ¿Server o Client?

```
¿Necesita estado (useState) o efectos (useEffect)?
├── SÍ → Client Component
└── NO → ¿Necesita event handlers (onClick, onChange)?
           ├── SÍ → Client Component
           └── NO → ¿Usa APIs del browser (window, localStorage)?
                     ├── SÍ → Client Component
                     └── NO → Server Component ✅
```

---

## Patrones de Composición

### Patrón 1: Server fetches, Client es interactivo

```typescript
// app/orders/page.tsx — SERVER COMPONENT
// Fetch directo a BD — sin API route intermedia
import { auth } from '@/lib/auth';

async function OrdersPage() {
  const session = await auth();
  if (!session) redirect('/login');

  // Fetch directo — en el servidor, seguro
  const orders = await prisma.order.findMany({
    where: { userId: session.user.id },
    include: { items: true },
    orderBy: { createdAt: 'desc' },
    take: 20,
  });

  return (
    <div>
      <h1>Your Orders</h1>
      {/* Pasar datos al Client Component */}
      <OrdersTable initialOrders={orders} />
    </div>
  );
}

// components/OrdersTable.tsx — CLIENT COMPONENT
// Solo la parte que necesita interactividad
'use client';

import { useState } from 'react';

function OrdersTable({ initialOrders }: { initialOrders: Order[] }) {
  const [filter, setFilter] = useState('');
  const [orders, setOrders] = useState(initialOrders);

  const filtered = orders.filter(o =>
    filter ? o.status === filter : true
  );

  return (
    <div>
      <FilterBar value={filter} onChange={setFilter} />
      {filtered.map(order => <OrderRow key={order.id} order={order} />)}
    </div>
  );
}
```

### Patrón 2: Pasar Server Component como children de Client

```typescript
// ✅ Correcto — Server Component como children
// El Client Component no sabe que sus children son Server Components

// layout.tsx (Server)
export default function Layout({ children }: { children: React.ReactNode }) {
  return (
    <ClientShell>    {/* Client Component */}
      {children}     {/* Server Components pasan como children — no se convierten en client */}
    </ClientShell>
  );
}

// ClientShell.tsx
'use client';
function ClientShell({ children }: { children: React.ReactNode }) {
  const [isOpen, setIsOpen] = useState(false);
  return (
    <div>
      <Sidebar isOpen={isOpen} onToggle={() => setIsOpen(!isOpen)} />
      <main>{children}</main>  {/* children pueden ser Server Components */}
    </div>
  );
}

// ❌ INCORRECTO — importar Server Component dentro de Client Component
'use client';
import { ServerDataComponent } from './ServerDataComponent'; // ❌ se convierte en client
```

### Patrón 3: Interleaving — mezclar Server y Client

```typescript
// Server Component fetching + Client Component interactivo en el mismo árbol
async function ProductPage({ params }: { params: { id: string } }) {
  // Fetch en servidor — paralelo
  const [product, reviews, relatedProducts] = await Promise.all([
    getProduct(params.id),
    getReviews(params.id),
    getRelatedProducts(params.id),
  ]);

  return (
    <div className="grid grid-cols-2 gap-8">
      {/* Server Component — solo HTML */}
      <ProductInfo product={product} />

      {/* Client Component — carrito interactivo */}
      <AddToCartSection
        product={product}
        variants={product.variants}
      />

      {/* Server Component */}
      <ProductReviews reviews={reviews} />

      {/* Server Component con Suspense para datos no críticos */}
      <Suspense fallback={<RelatedSkeleton />}>
        <RelatedProducts products={relatedProducts} />
      </Suspense>
    </div>
  );
}
```

---

## Streaming con Suspense

```typescript
// app/dashboard/page.tsx
// Los datos lentos no bloquean los datos rápidos

export default function DashboardPage() {
  return (
    <div className="space-y-6">
      {/* Crítico — carga inmediata sin suspense */}
      <DashboardHeader />

      {/* Stats — puede tardar, mostrar skeleton mientras */}
      <Suspense fallback={<StatsSkeleton />}>
        <DashboardStats />  {/* fetch async dentro */}
      </Suspense>

      <div className="grid grid-cols-2 gap-6">
        {/* Los dos streams en paralelo — no uno espera al otro */}
        <Suspense fallback={<OrdersSkeleton />}>
          <RecentOrders />
        </Suspense>

        <Suspense fallback={<ChartSkeleton />}>
          <RevenueChart />
        </Suspense>
      </div>
    </div>
  );
}

// DashboardStats.tsx — Server Component async
async function DashboardStats() {
  // Esta función puede tardar — no bloquea el resto de la página
  const stats = await getStats(); // DB query costosa

  return (
    <div className="grid grid-cols-4 gap-4">
      <StatCard title="Orders" value={stats.totalOrders} />
      <StatCard title="Revenue" value={formatMoney(stats.revenue)} />
      {/* ... */}
    </div>
  );
}
```

---

## Server Actions — Mutations sin API Route

```typescript
// actions/orders.ts
'use server';

import { z } from 'zod';
import { auth } from '@/lib/auth';
import { revalidatePath, revalidateTag } from 'next/cache';

const cancelOrderSchema = z.object({
  orderId: z.string(),
  reason: z.string().optional(),
});

export async function cancelOrder(formData: FormData) {
  // 1. Auth check — SIEMPRE en Server Actions
  const session = await auth();
  if (!session) throw new Error('Unauthorized');

  // 2. Validar input
  const parsed = cancelOrderSchema.safeParse({
    orderId: formData.get('orderId'),
    reason: formData.get('reason'),
  });

  if (!parsed.success) {
    return { error: 'Invalid input', errors: parsed.error.flatten() };
  }

  // 3. Verificar ownership
  const order = await prisma.order.findUnique({
    where: { id: parsed.data.orderId },
  });

  if (!order || order.userId !== session.user.id) {
    return { error: 'Order not found' };
  }

  if (order.status !== 'PENDING') {
    return { error: 'Only pending orders can be cancelled' };
  }

  // 4. Ejecutar
  await prisma.order.update({
    where: { id: order.id },
    data: { status: 'CANCELLED' },
  });

  // 5. Revalidar caché
  revalidatePath('/orders');
  revalidatePath(`/orders/${order.id}`);

  return { success: true };
}

// Uso en Client Component
'use client';

function CancelOrderButton({ orderId }: { orderId: string }) {
  const [state, formAction, isPending] = useActionState(cancelOrder, null);

  return (
    <form action={formAction}>
      <input type="hidden" name="orderId" value={orderId} />
      {state?.error && <p className="text-red-500">{state.error}</p>}
      <button type="submit" disabled={isPending}>
        {isPending ? 'Cancelling...' : 'Cancel Order'}
      </button>
    </form>
  );
}

// O sin form (programáticamente)
function CancelButton({ orderId }: { orderId: string }) {
  const cancelWithId = cancelOrder.bind(null, orderId);
  return (
    <button
      onClick={async () => {
        const result = await cancelOrder(new FormData());
        if (result?.error) toast.error(result.error);
      }}
    >
      Cancel
    </button>
  );
}
```

---

## Parallel Routes y Intercepting Routes

```typescript
// app/@modal/(.)orders/[id]/page.tsx — intercepting route
// Cuando se navega a /orders/123 desde el listado → abre modal
// Cuando se accede directamente a /orders/123 → página completa

// app/layout.tsx — parallel route recibe el modal
export default function Layout({
  children,
  modal,   // @modal slot
}: {
  children: React.ReactNode;
  modal: React.ReactNode;
}) {
  return (
    <>
      {children}
      {modal}  {/* Se muestra superpuesto cuando hay intercepting route */}
    </>
  );
}
```
