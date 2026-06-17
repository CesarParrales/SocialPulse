# Data Fetching, Caché y Revalidación

## Estrategias de Renderizado

```
Static (SSG) — generado en build:
  export const dynamic = 'force-static'
  Cuándo: landing pages, blog, docs, cualquier contenido que no cambia por usuario

Dynamic (SSR) — generado en cada request:
  export const dynamic = 'force-dynamic'
  Cuándo: datos personalizados por usuario, tiempo real, dashboard

Incremental Static Regeneration (ISR) — estático + re-generación periódica:
  export const revalidate = 3600  // regenerar cada 1 hora
  Cuándo: catálogos de productos, artículos, cualquier contenido con cambios infrecuentes

On-demand Revalidation — regenerar cuando los datos cambian:
  revalidatePath('/products')  o  revalidateTag('products')
  Cuándo: e-commerce, CMS — regenerar exactamente cuando cambia el contenido
```

---

## Fetch en Server Components

```typescript
// Fetch con opciones de caché (Next.js extiende fetch nativo)
async function getProducts() {
  const res = await fetch('https://api.myapp.com/products', {
    // Opciones de caché de Next.js:
    next: {
      revalidate: 3600,         // ISR — revalidar cada 1 hora
      tags: ['products'],        // tag para on-demand revalidation
    },
    // O para no cachear:
    // cache: 'no-store'          // siempre fresco (SSR)
    // O el default:
    // cache: 'force-cache'       // caché máxima (SSG)
  });

  if (!res.ok) throw new Error('Failed to fetch products');
  return res.json() as Promise<Product[]>;
}

// Con Prisma (DB directa) — caché manual con unstable_cache
import { unstable_cache } from 'next/cache';

const getCachedOrders = unstable_cache(
  async (userId: string) => {
    return prisma.order.findMany({
      where: { userId },
      include: { items: true },
    });
  },
  ['user-orders'],          // cache key parts
  {
    tags: ['orders'],       // para invalidación
    revalidate: 60,         // 1 minuto
  }
);

// Uso en página
import { auth } from '@/lib/auth';

async function OrdersPage() {
  const session = await auth();
  if (!session) redirect('/login');
  const orders = await getCachedOrders(session.user.id);
  return <OrdersList orders={orders} />;
}
```

---

## Parallel Data Fetching

```typescript
// ✅ Paralelo — ambos fetches corren al mismo tiempo
async function ProductPage({ params }: { params: { id: string } }) {
  const [product, reviews] = await Promise.all([
    getProduct(params.id),
    getReviews(params.id),
  ]);

  return <ProductView product={product} reviews={reviews} />;
}

// ❌ Secuencial — reviews espera a product (waterfall)
async function SlowProductPage({ params }: { params: { id: string } }) {
  const product = await getProduct(params.id);     // espera
  const reviews = await getReviews(params.id);     // espera que termine product
  return <ProductView product={product} reviews={reviews} />;
}

// Iniciar fetches en paralelo con Promise pero Suspense independiente
// Cada componente async puede suspender independientemente
function Page({ params }: { params: { id: string } }) {
  // Iniciar las dos promesas al mismo tiempo (sin await aún)
  const productPromise = getProduct(params.id);
  const reviewsPromise = getReviews(params.id);

  return (
    <>
      <Suspense fallback={<ProductSkeleton />}>
        <ProductInfo promise={productPromise} />
      </Suspense>
      <Suspense fallback={<ReviewsSkeleton />}>
        <ReviewsList promise={reviewsPromise} />
      </Suspense>
    </>
  );
}

// Componente que consume la promise
async function ProductInfo({ promise }: { promise: Promise<Product> }) {
  const product = await promise;  // suspende aquí, no bloquea ReviewsList
  return <div>{product.name}</div>;
}
```

---

## On-Demand Revalidation

```typescript
// app/api/webhooks/cms/route.ts
// CMS llama este webhook cuando se actualiza contenido
import { revalidatePath, revalidateTag } from 'next/cache';
import { headers } from 'next/headers';

export async function POST(request: Request) {
  // Verificar firma del webhook
  const headersList = await headers();
  const signature = headersList.get('x-webhook-signature');

  if (!verifySignature(signature, await request.text())) {
    return Response.json({ error: 'Invalid signature' }, { status: 401 });
  }

  const body = await request.json();

  switch (body.type) {
    case 'product.updated':
      // Revalidar por tag — invalida todos los fetches con tag 'products'
      revalidateTag('products');
      // Y la página específica
      revalidatePath(`/products/${body.data.slug}`);
      break;

    case 'order.status_changed':
      revalidateTag('orders');
      revalidatePath(`/orders/${body.data.id}`);
      break;

    case 'settings.updated':
      // Revalidar todo el sitio (layout)
      revalidatePath('/', 'layout');
      break;
  }

  return Response.json({ revalidated: true, timestamp: Date.now() });
}

// En Server Action — revalidar después de mutation
export async function updateProduct(id: string, data: ProductInput) {
  'use server';
  await prisma.product.update({ where: { id }, data });

  revalidateTag('products');           // invalida lista de productos
  revalidatePath(`/products/${id}`);   // invalida página específica
  revalidatePath('/admin/products');   // invalida panel admin

  redirect('/admin/products');         // redirigir después de mutation
}
```

---

## Metadata Dinámica

```typescript
// app/products/[slug]/page.tsx
import type { Metadata } from 'next';

// generateMetadata es async — puede hacer fetch
export async function generateMetadata(
  { params }: { params: { slug: string } }
): Promise<Metadata> {
  const product = await getProduct(params.slug);

  if (!product) return { title: 'Product Not Found' };

  return {
    title: product.name,
    description: product.description.slice(0, 160),
    openGraph: {
      title: product.name,
      description: product.description,
      images: [
        {
          url: product.imageUrl,
          width: 1200,
          height: 630,
          alt: product.name,
        },
      ],
    },
    alternates: {
      canonical: `https://myapp.com/products/${params.slug}`,
    },
  };
}

export default async function ProductPage({
  params,
}: {
  params: { slug: string };
}) {
  const product = await getProduct(params.slug);
  if (!product) notFound();

  return <ProductView product={product} />;
}

// Sitemap dinámico — app/sitemap.ts
export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const products = await getAllProducts();

  return [
    { url: 'https://myapp.com', lastModified: new Date(), changeFrequency: 'weekly', priority: 1 },
    { url: 'https://myapp.com/pricing', lastModified: new Date(), changeFrequency: 'monthly', priority: 0.8 },
    ...products.map(product => ({
      url: `https://myapp.com/products/${product.slug}`,
      lastModified: product.updatedAt,
      changeFrequency: 'weekly' as const,
      priority: 0.7,
    })),
  ];
}
```

---

## generateStaticParams — Pre-renderizar Rutas Dinámicas

```typescript
// app/products/[slug]/page.tsx
// Pre-renderizar las rutas más importantes en build
export async function generateStaticParams() {
  const products = await getTopProducts(100); // top 100 productos

  return products.map(product => ({
    slug: product.slug,
  }));
}

// Para rutas no pre-generadas: fallback automático
// Se exporta en el MISMO page.tsx (route segment config), NO en next.config.ts
export const dynamicParams = true; // default — generar on-demand si no está pre-generado
// dynamicParams = false → 404 si no está pre-generado
```

---

## Route Handlers (API Routes)

```typescript
// app/api/orders/route.ts
import { NextRequest, NextResponse } from 'next/server';
import { auth } from '@/lib/auth';

export async function GET(request: NextRequest) {
  const { searchParams } = request.nextUrl;
  const page = searchParams.get('page') ?? '1';
  const status = searchParams.get('status');

  const session = await auth();
  if (!session) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const orders = await prisma.order.findMany({
    where: {
      userId: session.user.id,
      ...(status && { status }),
    },
    skip: (parseInt(page) - 1) * 20,
    take: 20,
  });

  return NextResponse.json({ data: orders });
}

export async function POST(request: NextRequest) {
  const session = await auth();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });

  const body = await request.json();
  const parsed = createOrderSchema.safeParse(body);

  if (!parsed.success) {
    return NextResponse.json({
      error: 'Validation failed',
      errors: parsed.error.flatten(),
    }, { status: 422 });
  }

  const order = await createOrder(session.user.id, parsed.data);

  return NextResponse.json(
    { data: order },
    {
      status: 201,
      headers: { Location: `/api/orders/${order.id}` },
    }
  );
}

// app/api/orders/[id]/route.ts
export async function GET(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  const order = await prisma.order.findUnique({
    where: { id: params.id },
    include: { items: { include: { product: true } } },
  });

  if (!order) return NextResponse.json({ error: 'Not found' }, { status: 404 });

  return NextResponse.json({ data: order });
}
```
