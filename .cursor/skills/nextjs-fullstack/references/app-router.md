# App Router — Estructura, Routing y Middleware

## Estructura de Proyecto (App Router)

```
src/
├── app/                          # Rutas y layouts
│   ├── layout.tsx                # Root layout — html, body, providers
│   ├── page.tsx                  # Home page /
│   ├── loading.tsx               # Loading UI global
│   ├── error.tsx                 # Error boundary global
│   ├── not-found.tsx             # 404 page
│   ├── globals.css
│   │
│   ├── (marketing)/              # Route group — sin segmento en URL
│   │   ├── layout.tsx            # Layout solo para marketing
│   │   ├── about/page.tsx        # /about
│   │   └── pricing/page.tsx      # /pricing
│   │
│   ├── (dashboard)/              # Route group para área autenticada
│   │   ├── layout.tsx            # Layout con sidebar, nav
│   │   ├── dashboard/page.tsx    # /dashboard
│   │   └── orders/
│   │       ├── page.tsx          # /orders
│   │       ├── loading.tsx       # Skeleton específico de orders
│   │       └── [id]/
│   │           ├── page.tsx      # /orders/[id]
│   │           └── edit/page.tsx # /orders/[id]/edit
│   │
│   └── api/                      # Route Handlers (endpoints HTTP)
│       └── webhooks/
│           └── stripe/route.ts   # POST /api/webhooks/stripe
│
├── components/                   # Componentes compartidos
│   ├── ui/                       # shadcn/ui components
│   └── layout/
│
├── lib/                          # Utilidades
│   ├── db.ts                     # Prisma client singleton
│   ├── auth.ts                   # Auth.js config
│   └── api.ts                    # API client para llamadas externas
│
└── actions/                      # Server Actions
    ├── orders.ts
    └── auth.ts
```

---

## Convenciones de Archivos

```
layout.tsx        → UI compartida entre páginas (no re-monta)
page.tsx          → UI única de la ruta (hace la ruta accesible)
loading.tsx       → UI de carga (envuelve automáticamente en Suspense)
error.tsx         → Error boundary ('use client' requerido)
not-found.tsx     → UI de 404 (llamar notFound() de next/navigation)
route.ts          → API endpoint (GET, POST, etc.)
middleware.ts     → Corre antes del request (auth, redirects) — en raíz del proyecto
template.tsx      → Como layout pero SÍ re-monta entre navegaciones
default.tsx       → Fallback para parallel routes
```

### Segmentos dinámicos

```
app/orders/[id]/page.tsx          → /orders/123          (params.id = '123')
app/docs/[...slug]/page.tsx       → /docs/a/b/c          (catch-all: params.slug = ['a','b','c'])
app/shop/[[...slug]]/page.tsx     → /shop y /shop/a/b    (optional catch-all)
```

```typescript
// Next.js 15: params es una Promise
export default async function OrderPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const order = await getOrder(id);
  if (!order) notFound();
  return <OrderView order={order} />;
}
```

---

## Root Layout — Configuración Global

```typescript
// app/layout.tsx
import type { Metadata } from 'next';
import { Inter, Playfair_Display } from 'next/font/google';
import { Providers } from '@/components/Providers';
import './globals.css';

// Fuentes optimizadas — cargadas en build, sin layout shift
const inter = Inter({
  subsets: ['latin'],
  variable: '--font-sans',
  display: 'swap',
});

const playfair = Playfair_Display({
  subsets: ['latin'],
  variable: '--font-display',
  display: 'swap',
});

// Metadata estática — mejor para SEO
export const metadata: Metadata = {
  title: {
    template: '%s | MyApp',  // "Orders | MyApp", "Home | MyApp"
    default: 'MyApp',
  },
  description: 'MyApp — manage your orders',
  metadataBase: new URL('https://myapp.com'),
  openGraph: {
    type: 'website',
    locale: 'en_US',
    url: 'https://myapp.com',
    siteName: 'MyApp',
  },
  robots: { index: true, follow: true },
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body className={`${inter.variable} ${playfair.variable} font-sans`}>
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}
```

### Layouts anidados vs templates

- `layout.tsx` persiste entre navegaciones dentro de su segmento: el estado de un sidebar no se pierde al cambiar de página.
- `template.tsx` crea una instancia nueva en cada navegación: útil para animaciones de entrada o efectos que deben re-ejecutarse por página.
- Un layout NO recibe `params` de segmentos hijos; cada layout solo conoce su propio segmento.

---

## loading / error / not-found

```typescript
// app/orders/loading.tsx — Suspense automático para toda la ruta
export default function Loading() {
  return <OrdersSkeleton />;
}

// app/orders/error.tsx — error boundary del segmento
'use client';

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  return (
    <div>
      <h2>Algo salió mal cargando tus pedidos</h2>
      <button onClick={() => reset()}>Reintentar</button>
    </div>
  );
}

// app/orders/[id]/page.tsx — disparar el 404
import { notFound } from 'next/navigation';

export default async function OrderPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const order = await getOrder(id);
  if (!order) notFound();  // renderiza el not-found.tsx más cercano
  return <OrderView order={order} />;
}
```

Jerarquía de render por segmento: `layout` → `template` → `error` (boundary) → `loading` (Suspense) → `not-found` → `page`.

---

## Route Groups

```
app/
├── (marketing)/          # paréntesis = NO aparece en la URL
│   ├── layout.tsx        # layout público (header marketing, footer)
│   └── pricing/page.tsx  # → /pricing
└── (dashboard)/
    ├── layout.tsx        # layout autenticado (sidebar)
    └── orders/page.tsx   # → /orders
```

Usos: layouts distintos para áreas del sitio sin afectar URLs, y organizar rutas por dominio. Dos route groups no pueden resolver a la misma URL (`(a)/page.tsx` y `(b)/page.tsx` colisionan).

---

## Parallel Routes e Intercepting Routes

```typescript
// Parallel routes — slots con @nombre, renderizados por el layout padre
// app/@modal/(.)orders/[id]/page.tsx — intercepting route
// Navegando a /orders/123 desde el listado → abre modal
// Accediendo directamente a /orders/123 → página completa

// app/layout.tsx — el layout recibe cada slot como prop
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

// app/@modal/default.tsx — fallback cuando el slot no tiene match
export default function Default() {
  return null;
}
```

Convenciones de interceptación: `(.)` mismo nivel, `(..)` un nivel arriba, `(..)(..)` dos niveles, `(...)` desde la raíz.

---

## Route Handlers (API endpoints)

```typescript
// app/api/orders/route.ts
import { NextRequest, NextResponse } from 'next/server';
import { auth } from '@/lib/auth';

export async function GET(request: NextRequest) {
  const session = await auth();
  if (!session) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const { searchParams } = request.nextUrl;
  const page = searchParams.get('page') ?? '1';

  const orders = await prisma.order.findMany({
    where: { userId: session.user.id },
    skip: (parseInt(page) - 1) * 20,
    take: 20,
  });

  return NextResponse.json({ data: orders });
}

// app/api/orders/[id]/route.ts — params dinámicos
export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  const order = await prisma.order.findUnique({ where: { id } });
  if (!order) return NextResponse.json({ error: 'Not found' }, { status: 404 });
  return NextResponse.json({ data: order });
}
```

Cuándo usar Route Handler vs Server Action: webhooks externos, endpoints consumidos por terceros o apps móviles → Route Handler; mutations desde la propia UI → Server Action (ver `server-client.md`).

---

## Middleware

```typescript
// middleware.ts (raíz del proyecto, junto a package.json — no dentro de app/)
import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function middleware(request: NextRequest) {
  // Redirects, rewrites, headers, A/B testing, geo...
  if (request.nextUrl.pathname === '/old-dashboard') {
    return NextResponse.redirect(new URL('/dashboard', request.url));
  }
  return NextResponse.next();
}

export const config = {
  // Excluir estáticos y assets — el middleware corre en TODOS los requests que matcheen
  matcher: ['/((?!_next/static|_next/image|favicon.ico).*)'],
};
```

- Corre en runtime Edge: sin Node APIs (fs, net), sin Prisma directo.
- Para protección de rutas con sesión, usa el middleware de Auth.js (ver `auth.md`).
- Mantenlo ligero: cada request del matcher lo atraviesa.

---

## Navegación

```typescript
// Declarativa — prefetch automático en viewport
import Link from 'next/link';
<Link href="/orders/123">Ver pedido</Link>

// Programática — Client Components
'use client';
import { useRouter, usePathname, useSearchParams } from 'next/navigation';

function Nav() {
  const router = useRouter();
  const pathname = usePathname();

  return (
    <button onClick={() => router.push('/orders')}>
      Ir a pedidos
    </button>
  );
}

// Server Components / Server Actions
import { redirect } from 'next/navigation';
redirect('/login');  // lanza internamente — no poner código después
```
