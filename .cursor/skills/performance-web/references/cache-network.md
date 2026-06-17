# Caché, CDN y Red

## Cache-Control Headers — La Base

```nginx
# nginx.conf — políticas de caché por tipo de recurso

# Assets con hash en el nombre — caché máxima (immutable)
# JS/CSS generados por Webpack/Vite tienen hash: app.a1b2c3d4.js
location ~* \.(js|css)$ {
    add_header Cache-Control "public, max-age=31536000, immutable";
    # 1 año — el hash cambia cuando cambia el contenido
}

# Imágenes — caché larga
location ~* \.(webp|avif|jpg|jpeg|png|gif|svg|ico)$ {
    add_header Cache-Control "public, max-age=2592000"; # 30 días
    add_header Vary "Accept"; # variar según Accept header (WebP vs JPG)
}

# Fuentes — caché máxima (raramente cambian)
location ~* \.(woff2|woff|ttf|eot)$ {
    add_header Cache-Control "public, max-age=31536000, immutable";
    add_header Access-Control-Allow-Origin "*"; # CORS para fuentes
}

# HTML — sin caché agresivo (el usuario debe ver actualizaciones)
location ~* \.html$ {
    add_header Cache-Control "public, max-age=0, must-revalidate";
}

# API responses — sin caché por defecto, controlar desde el backend
location /api/ {
    add_header Cache-Control "private, no-store";
}
```

---

## Service Worker — Caché Offline

```typescript
// public/sw.js — Service Worker básico
const CACHE_VERSION = 'v1.0.3';
const STATIC_CACHE = `static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `dynamic-${CACHE_VERSION}`;

const PRECACHE_ASSETS = [
  '/',
  '/offline.html',
  '/manifest.json',
];

// Instalar — precachear assets críticos
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then(cache => cache.addAll(PRECACHE_ASSETS))
  );
  self.skipWaiting();
});

// Activar — limpiar caches viejas
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key !== STATIC_CACHE && key !== DYNAMIC_CACHE)
          .map(key => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

// Fetch — estrategia según tipo de recurso
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // API requests — Network first, fallback a caché
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(networkFirstStrategy(request));
    return;
  }

  // Static assets — Cache first
  if (request.destination === 'image' ||
      request.destination === 'script' ||
      request.destination === 'style') {
    event.respondWith(cacheFirstStrategy(request));
    return;
  }

  // Páginas HTML — Stale while revalidate
  event.respondWith(staleWhileRevalidate(request));
});

async function networkFirstStrategy(request: Request): Promise<Response> {
  try {
    const networkResponse = await fetch(request);
    const cache = await caches.open(DYNAMIC_CACHE);
    cache.put(request, networkResponse.clone());
    return networkResponse;
  } catch {
    const cached = await caches.match(request);
    return cached ?? caches.match('/offline.html') ?? new Response('Offline', { status: 503 });
  }
}

async function cacheFirstStrategy(request: Request): Promise<Response> {
  const cached = await caches.match(request);
  if (cached) return cached;

  const networkResponse = await fetch(request);
  const cache = await caches.open(STATIC_CACHE);
  cache.put(request, networkResponse.clone());
  return networkResponse;
}

async function staleWhileRevalidate(request: Request): Promise<Response> {
  const cached = await caches.match(request);
  const networkPromise = fetch(request).then(response => {
    caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, response.clone()));
    return response;
  });

  return cached ?? networkPromise;
}
```

---

## HTTP/2 y HTTP/3

```nginx
# nginx.conf — habilitar HTTP/2
server {
    listen 443 ssl http2;  # HTTP/2 sobre SSL
    # ...
}

# HTTP/2 Server Push — enviar recursos antes de que se pidan
# (útil para CSS crítico y fuentes)
location / {
    http2_push /static/css/critical.css;
    http2_push /static/fonts/inter.woff2;
    try_files $uri $uri/ /index.html;
}
```

```
HTTP/2 beneficios:
- Multiplexing: múltiples requests en una sola conexión TCP
- Header compression: menos overhead en cada request
- Server Push: enviar assets antes de que se pidan

HTTP/3 (QUIC):
- UDP en lugar de TCP → menos latencia en redes con pérdida de paquetes
- Mejor en mobile (cambios de red)
- Cloudflare y Nginx 1.25+ lo soportan

Domain Sharding (anti-patrón moderno):
- HTTP/1.1: hacer requests a múltiples dominios era útil (paralelo)
- HTTP/2: NO hacer esto — multiplexing hace un solo dominio más eficiente
```

---

## CDN — Contenido Cerca del Usuario

```
Cuándo usar CDN:
✅ Audiencia global (usuarios en múltiples continentes)
✅ Imágenes y assets estáticos pesados
✅ APIs con respuestas cacheables (productos, catálogos)
✅ Reducir carga en servidor de origen

Opciones:
- Cloudflare: gratuito para uso básico, DDoS protection incluida
- AWS CloudFront: integración nativa con S3 y EC2
- Fastly: para APIs con caché edge (VCL customizable)
- Vercel Edge Network: automático si se usa Vercel

Configuración básica con Cloudflare:
1. Cambiar NS a Cloudflare
2. Assets estáticos: cachear en edge (CSS, JS, imágenes)
3. HTML: no cachear o usar cache muy corto (5 min)
4. API: cache selectivo por endpoint
5. Purge automático en deploy via Cloudflare API
```

```bash
# Purgar caché de Cloudflare en deploy
curl -X POST "https://api.cloudflare.com/client/v4/zones/${ZONE_ID}/purge_cache" \
  -H "Authorization: Bearer ${CF_API_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"purge_everything": true}'

# O purgar URLs específicas
curl -X POST "https://api.cloudflare.com/client/v4/zones/${ZONE_ID}/purge_cache" \
  -H "Authorization: Bearer ${CF_API_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"files": ["https://myapp.com/api/products", "https://myapp.com/products"]}'
```

---

## ETag y Conditional Requests

```php
// Laravel — respuestas con ETag
public function show(Product $product): JsonResponse
{
    $data = ProductResource::make($product)->toArray(request());
    $etag = md5(json_encode($data) . $product->updated_at->timestamp);

    // Si el cliente tiene la versión actual → 304 Not Modified (sin body)
    if (request()->header('If-None-Match') === $etag) {
        return response()->json(null, 304);
    }

    return response()
        ->json(['data' => $data])
        ->header('ETag', $etag)
        ->header('Cache-Control', 'private, max-age=60');
}

// Last-Modified alternativa
public function index(): JsonResponse
{
    $products = Product::active()->get();
    $lastModified = $products->max('updated_at');

    if ($modifiedSince = request()->header('If-Modified-Since')) {
        if (Carbon::parse($modifiedSince)->gte($lastModified)) {
            return response(null, 304);
        }
    }

    return response()
        ->json(['data' => ProductResource::collection($products)])
        ->header('Last-Modified', $lastModified->toRfc7231String())
        ->header('Cache-Control', 'public, max-age=300');
}
```

---

## Resource Hints — Precarga Inteligente

```typescript
// next.config.ts — headers de preload para recursos críticos
async headers() {
  return [
    {
      source: '/(.*)',
      headers: [
        // Preconectar a dominios externos antes de que se necesiten
        {
          key: 'Link',
          value: [
            '<https://api.myapp.com>; rel=preconnect',
            '<https://fonts.gstatic.com>; rel=preconnect; crossorigin',
            '<https://cdn.myapp.com>; rel=dns-prefetch',
          ].join(', '),
        },
      ],
    },
  ];
},

// En Next.js — prefetch automático en hover con Link
import Link from 'next/link';

// Next.js prefetch automáticamente los links en viewport
// Se puede deshabilitar para links poco probables
<Link href="/orders" prefetch={false}>Orders</Link>

// Prefetch manual en React Router
import { useFetcher } from 'react-router-dom';

function NavLink({ to, children }: Props) {
  const fetcher = useFetcher();
  return (
    <Link
      to={to}
      onMouseEnter={() => fetcher.load(to)}  // prefetch al hover
    >
      {children}
    </Link>
  );
}
```
