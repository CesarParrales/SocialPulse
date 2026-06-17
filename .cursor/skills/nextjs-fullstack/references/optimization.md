# Optimización y Deploy

## next/image — Imágenes Optimizadas

```typescript
import Image from 'next/image';

// ✅ Imagen local — size automático
// Import relativo (o alias '@/'): NUNCA 'from "/public/..."' — ese import es inválido
import heroImage from '../public/hero.jpg';

function Hero() {
  return (
    <Image
      src={heroImage}
      alt="Hero image"
      priority           // LCP — cargar sin lazy
      quality={90}
      className="object-cover"
    />
  );
}

// ✅ Imagen remota — size explícito requerido
function ProductImage({ product }: { product: Product }) {
  return (
    <Image
      src={product.imageUrl}
      alt={product.name}
      width={400}
      height={400}
      sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 400px"
      className="rounded-lg object-cover"
    />
  );
}

// ✅ Fill — cuando el container define el tamaño
function CoverImage({ src, alt }: { src: string; alt: string }) {
  return (
    <div className="relative aspect-video">
      <Image
        src={src}
        alt={alt}
        fill
        sizes="(max-width: 768px) 100vw, 800px"
        className="object-cover"
      />
    </div>
  );
}

// next.config.ts — dominios permitidos para imágenes remotas
const config: NextConfig = {
  images: {
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'cdn.myapp.com',
        port: '',
        pathname: '/images/**',
      },
      {
        protocol: 'https',
        hostname: '*.cloudinary.com',
      },
    ],
  },
};
```

---

## next/font — Fuentes sin Layout Shift

```typescript
// app/layout.tsx
import { Inter, DM_Serif_Display } from 'next/font/google';
import localFont from 'next/font/local';

// Google Fonts — descargadas en build, self-hosted
const inter = Inter({
  subsets: ['latin'],
  variable: '--font-sans',
  display: 'swap',
  preload: true,
});

const dmSerif = DM_Serif_Display({
  weight: ['400'],
  subsets: ['latin'],
  variable: '--font-display',
  display: 'swap',
});

// Fuente local
const customFont = localFont({
  src: [
    { path: '../public/fonts/MyFont-Regular.woff2', weight: '400' },
    { path: '../public/fonts/MyFont-Bold.woff2', weight: '700' },
  ],
  variable: '--font-custom',
});

// En globals.css
// .font-sans { font-family: var(--font-sans) }
// .font-display { font-family: var(--font-display) }
```

---

## next.config.ts — Configuración de Producción

```typescript
// next.config.ts
import type { NextConfig } from 'next';

const config: NextConfig = {
  // Internacionalización
  // i18n: { locales: ['en', 'es'], defaultLocale: 'en' },

  // Headers de seguridad
  async headers() {
    return [
      {
        source: '/(.*)',
        headers: [
          { key: 'X-DNS-Prefetch-Control', value: 'on' },
          { key: 'Strict-Transport-Security', value: 'max-age=63072000' },
          { key: 'X-Frame-Options', value: 'SAMEORIGIN' },
          { key: 'X-Content-Type-Options', value: 'nosniff' },
          { key: 'Referrer-Policy', value: 'origin-when-cross-origin' },
          { key: 'Permissions-Policy', value: 'camera=(), microphone=(), geolocation=()' },
          {
            key: 'Content-Security-Policy',
            value: [
              "default-src 'self'",
              "script-src 'self' 'unsafe-eval' 'unsafe-inline'",  // unsafe-eval para Next.js dev
              "style-src 'self' 'unsafe-inline'",
              "img-src 'self' data: https:",
              "font-src 'self'",
              "connect-src 'self' https://api.myapp.com",
            ].join('; '),
          },
        ],
      },
    ];
  },

  // Redirects
  async redirects() {
    return [
      { source: '/old-path', destination: '/new-path', permanent: true },
    ];
  },

  // Rewrites — proxy transparente a API externa
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: `${process.env.API_URL}/:path*`,
      },
    ];
  },

  // Bundle analyzer en desarrollo
  ...(process.env.ANALYZE === 'true' && {
    webpack(config) {
      const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer');
      config.plugins.push(new BundleAnalyzerPlugin({ analyzerMode: 'static' }));
      return config;
    },
  }),

  // Logging de fetch en desarrollo
  logging: {
    fetches: {
      fullUrl: true,  // ver URLs completas de fetch en terminal
    },
  },
};

export default config;
```

---

## OG Images con ImageResponse

```typescript
// app/opengraph-image.tsx — OG image para la home
import { ImageResponse } from 'next/og';

export const runtime = 'edge';
export const alt = 'MyApp';
export const size = { width: 1200, height: 630 };
export const contentType = 'image/png';

export default async function Image() {
  return new ImageResponse(
    <div
      style={{
        background: 'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)',
        width: '100%',
        height: '100%',
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        color: 'white',
      }}
    >
      <h1 style={{ fontSize: 72, fontWeight: 700 }}>MyApp</h1>
      <p style={{ fontSize: 32, opacity: 0.8 }}>Manage your orders effortlessly</p>
    </div>
  );
}

// app/products/[slug]/opengraph-image.tsx — OG dinámica por producto
export default async function Image({ params }: { params: { slug: string } }) {
  const product = await getProduct(params.slug);

  return new ImageResponse(
    <div style={{ display: 'flex', background: '#fff', width: '100%', height: '100%' }}>
      <img src={product.imageUrl} style={{ width: 630, height: 630, objectFit: 'cover' }} />
      <div style={{ flex: 1, padding: 40, display: 'flex', flexDirection: 'column' }}>
        <h1 style={{ fontSize: 48 }}>{product.name}</h1>
        <p style={{ fontSize: 32, color: '#666' }}>{product.description.slice(0, 100)}</p>
        <p style={{ fontSize: 40, fontWeight: 700, marginTop: 'auto' }}>
          ${(product.priceCents / 100).toFixed(2)}
        </p>
      </div>
    </div>
  );
}
```

---

## Deploy en Vercel

```bash
# Vercel CLI
npm i -g vercel
vercel  # primera vez — guía interactiva
vercel --prod  # deploy a producción
```

```json
// vercel.json — configuración (opcional — Vercel auto-detecta Next.js)
{
  "buildCommand": "npm run build",
  "outputDirectory": ".next",
  "framework": "nextjs",
  "regions": ["iad1", "gru1"],  // regiones para Edge Functions
  "env": {
    "NEXTAUTH_URL": "@nextauth-url-production"  // @ref a secret de Vercel
  }
}
```

```yaml
# .github/workflows/deploy-vercel.yml
name: Deploy to Vercel
on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Deploy to Vercel
        uses: amondnet/vercel-action@v25
        with:
          vercel-token: ${{ secrets.VERCEL_TOKEN }}
          vercel-org-id: ${{ secrets.VERCEL_ORG_ID }}
          vercel-project-id: ${{ secrets.VERCEL_PROJECT_ID }}
          vercel-args: '--prod'
```

---

## Deploy Self-Hosted (Docker + Nginx)

```dockerfile
# Dockerfile para Next.js standalone
FROM node:20-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM node:20-alpine AS production
WORKDIR /app

ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1
ENV PORT=3000

RUN addgroup -S nextjs && adduser -S nextjs -G nextjs

# Standalone output de Next.js — imagen mínima
COPY --from=builder --chown=nextjs:nextjs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nextjs /app/.next/static ./.next/static
COPY --from=builder --chown=nextjs:nextjs /app/public ./public

USER nextjs
EXPOSE 3000

HEALTHCHECK --interval=30s CMD wget -qO- http://localhost:3000/api/health || exit 1

CMD ["node", "server.js"]
```

```typescript
// next.config.ts — habilitar standalone output
const config: NextConfig = {
  output: 'standalone',  // imagen Docker mínima — solo lo necesario
};
```
