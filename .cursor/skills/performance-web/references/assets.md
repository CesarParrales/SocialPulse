# Imágenes y Assets

## Formatos Modernos — El Mayor Quick Win

```
Jerarquía de formatos (de mejor a peor):
1. AVIF  → 50% más pequeño que WebP, 80% más que JPEG. Soporte: 93%+ browsers
2. WebP  → 30% más pequeño que JPEG. Soporte: 97%+ browsers
3. JPEG  → fotos, gradientes. Compresión lossy
4. PNG   → transparencia, screenshots. Compresión lossless
5. SVG   → íconos, logos, ilustraciones. Escalable
6. GIF   → animaciones simples → reemplazar con video MP4/WebM o CSS

Regla: AVIF con fallback a WebP con fallback a JPEG
```

```html
<!-- picture element — servir el mejor formato soportado -->
<picture>
  <source srcset="/hero.avif" type="image/avif">
  <source srcset="/hero.webp" type="image/webp">
  <img src="/hero.jpg" alt="Hero" width="1200" height="630" loading="eager">
</picture>

<!-- Para imágenes menores al fold → lazy loading -->
<picture>
  <source srcset="/product.avif" type="image/avif">
  <source srcset="/product.webp" type="image/webp">
  <img src="/product.jpg" alt="Product" width="400" height="400" loading="lazy">
</picture>
```

---

## Responsive Images — Servir el Tamaño Correcto

```html
<!-- srcset — el browser elige el tamaño correcto según el dispositivo -->
<img
  srcset="
    /hero-400.webp 400w,
    /hero-800.webp 800w,
    /hero-1200.webp 1200w,
    /hero-2400.webp 2400w
  "
  sizes="
    (max-width: 640px) 100vw,
    (max-width: 1024px) 80vw,
    1200px
  "
  src="/hero-1200.webp"
  alt="Hero image"
  width="1200"
  height="630"
>

<!-- Nota: siempre incluir width y height para evitar CLS -->
<!-- Esto permite al browser reservar espacio antes de cargar la imagen -->
```

---

## Procesamiento de Imágenes

```bash
# Sharp (Node.js) — la mejor librería para procesamiento
npm install sharp

# Generar formatos modernos y tamaños responsivos
node -e "
const sharp = require('sharp');
const sizes = [400, 800, 1200, 2400];
const input = './public/hero.jpg';

for (const size of sizes) {
  // WebP
  sharp(input)
    .resize(size, null, { withoutEnlargement: true })
    .webp({ quality: 85 })
    .toFile(\`./public/hero-\${size}.webp\`);

  // AVIF
  sharp(input)
    .resize(size, null, { withoutEnlargement: true })
    .avif({ quality: 65 })
    .toFile(\`./public/hero-\${size}.avif\`);
}
"

# Squoosh CLI — batch processing
npx @squoosh/cli --webp '{"quality":85}' --avif '{"quality":65}' input/*.jpg
```

```typescript
// API de transformación de imágenes on-demand (Cloudflare Images, Imgix)
// Transforma la imagen en el edge sin pre-generar todos los tamaños

// Cloudflare Images URL
const imageUrl = `https://imagedelivery.net/${ACCOUNT_ID}/${IMAGE_ID}/w=800,f=webp,q=85`;

// Imgix
const imageUrl = `https://myapp.imgix.net/product.jpg?w=800&fm=webp&q=85&auto=format`;

// Next.js lo hace automáticamente con next/image
// Genera WebP/AVIF on-demand y los cachea en el edge
```

---

## Evitar CLS — Dimensiones Siempre Presentes

```html
<!-- ❌ MAL: sin dimensiones → layout shift cuando carga -->
<img src="/product.jpg" alt="Product">

<!-- ✅ BIEN: dimensiones explícitas → browser reserva espacio -->
<img src="/product.jpg" alt="Product" width="400" height="400">

<!-- ✅ BIEN: aspect-ratio CSS para contenedores responsivos -->
<div style="aspect-ratio: 16/9; overflow: hidden;">
  <img src="/hero.jpg" style="width: 100%; height: 100%; object-fit: cover;">
</div>

<!-- Skeleton placeholder mientras carga la imagen -->
<div class="relative aspect-square bg-gray-200 animate-pulse">
  <img
    src="/product.jpg"
    alt="Product"
    class="absolute inset-0 w-full h-full object-cover"
    onload="this.parentElement.classList.remove('animate-pulse', 'bg-gray-200')"
  >
</div>
```

```css
/* Reservar espacio para imágenes con aspect-ratio conocido */
.hero-image {
  aspect-ratio: 16 / 9;
  width: 100%;
  background-color: #f3f4f6; /* placeholder color */
}

/* Evitar CLS en fonts */
@font-face {
  font-family: 'Inter';
  src: url('/fonts/inter.woff2') format('woff2');
  font-display: swap;  /* mostrar fallback font mientras carga */
  /* optional: no usar si el shift es muy visible */
  /* block: invisible hasta cargar (FOIT) */
  /* fallback: 100ms invisible, luego fallback */
}
```

---

## Imágenes en Next.js

```typescript
import Image from 'next/image';

// LCP image — priority para cargar sin lazy
function HeroSection() {
  return (
    <div className="relative h-[600px]">
      <Image
        src="/hero.jpg"
        alt="Hero"
        fill                          // ocupa el container
        priority                      // no lazy — crítico para LCP
        quality={90}
        sizes="100vw"
        className="object-cover"
        placeholder="blur"            // muestra blur mientras carga
        blurDataURL="data:image/jpeg;base64,..." // tiny base64 placeholder
      />
    </div>
  );
}

// Imagen de producto — lazy + responsive
function ProductImage({ product }: { product: Product }) {
  return (
    <Image
      src={product.imageUrl}
      alt={product.name}
      width={400}
      height={400}
      sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 400px"
      className="rounded-lg object-cover"
      // Next.js automáticamente genera WebP/AVIF y múltiples tamaños
    />
  );
}

// Generar blur placeholder con Sharp
import sharp from 'sharp';

async function getBlurDataUrl(src: string): Promise<string> {
  const buffer = await sharp(src)
    .resize(10, 10, { fit: 'inside' })
    .webp({ quality: 20 })
    .toBuffer();

  return `data:image/webp;base64,${buffer.toString('base64')}`;
}
```

---

## Fonts — Sin FOIT ni CLS

```typescript
// next/font — fuentes en build, sin requests externas, sin CLS
import { Inter, Playfair_Display } from 'next/font/google';

const inter = Inter({
  subsets: ['latin'],
  display: 'swap',       // swap: mostrar fallback hasta cargar (evita FOIT)
  variable: '--font-inter',
  preload: true,
  fallback: ['system-ui', '-apple-system', 'sans-serif'],
});

// Font local — máximo control, sin requests externas en absoluto
import localFont from 'next/font/local';

const customFont = localFont({
  src: [
    {
      path: '../../public/fonts/CustomFont-Regular.woff2',
      weight: '400',
      style: 'normal',
    },
    {
      path: '../../public/fonts/CustomFont-Bold.woff2',
      weight: '700',
      style: 'normal',
    },
  ],
  variable: '--font-custom',
  display: 'swap',
  // next/font ajusta automáticamente el fallback font para minimizar CLS
  adjustFontFallback: true,
});
```

---

## Videos — Reemplazar GIFs Animados

```html
<!-- GIF animado: pesado, sin control, sin compresión moderna -->
<!-- ❌ animation.gif → 5MB -->

<!-- ✅ Video MP4/WebM → mismo contenido, 80% más ligero -->
<video
  autoplay
  loop
  muted
  playsinline     <!-- necesario en iOS para autoplay -->
  width="600"
  height="400"
>
  <source src="/animation.webm" type="video/webm">
  <source src="/animation.mp4" type="video/mp4">
</video>
```

```bash
# Convertir GIF a video con ffmpeg
ffmpeg -i animation.gif \
  -c:v libvpx-vp9 -b:v 0 -crf 37 -pass 1 -an -f webm /dev/null && \
ffmpeg -i animation.gif \
  -c:v libvpx-vp9 -b:v 0 -crf 37 -pass 2 -an animation.webm

ffmpeg -i animation.gif \
  -c:v libx264 -pix_fmt yuv420p -movflags faststart animation.mp4
```
