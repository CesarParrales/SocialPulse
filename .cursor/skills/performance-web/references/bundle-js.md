# Bundle y JavaScript

## Analizar el Bundle Primero

```bash
# Next.js
npm install @next/bundle-analyzer --save-dev

# next.config.ts
import bundleAnalyzer from '@next/bundle-analyzer';
const withBundleAnalyzer = bundleAnalyzer({
  enabled: process.env.ANALYZE === 'true',
});
export default withBundleAnalyzer(nextConfig);

# Correr: ANALYZE=true npm run build
# Abre visualización de chunks en el browser

# Vite
npm install rollup-plugin-visualizer --save-dev

# vite.config.ts
import { visualizer } from 'rollup-plugin-visualizer';
plugins: [visualizer({ open: true, gzipSize: true })]

# Correr: npm run build — abre stats.html automáticamente
```

**Qué buscar en el análisis:**
```
🔴 Librerías duplicadas (react dos veces, lodash y lodash-es)
🔴 Dependencia enorme que solo se usa marginalmente
   (moment.js 300KB para formatear una fecha → usar date-fns o Intl.DateTimeFormat)
🔴 Chunk de vendor > 500KB gzipped
🔴 Imágenes en el bundle JS (importadas directamente)
🔴 Todo en un solo chunk (sin code splitting)
```

---

## Code Splitting — Reducir JavaScript Inicial

```typescript
// Por ruta — la forma más impactante
import { lazy, Suspense } from 'react';

// Antes: todo en el bundle inicial
import OrdersPage from './pages/OrdersPage';
import AdminPanel from './pages/AdminPanel';
import ReportsPage from './pages/ReportsPage';

// Después: cada ruta es su propio chunk
const OrdersPage  = lazy(() => import('./pages/OrdersPage'));
const AdminPanel  = lazy(() => import('./pages/AdminPanel'));
const ReportsPage = lazy(() => import('./pages/ReportsPage'));

// Con prefetch hint — carga el chunk cuando el browser está idle
const HeavyModal = lazy(() =>
  import(/* webpackPrefetch: true */ './components/HeavyModal')
);

// Componente costoso que no siempre se muestra
function ProductPage() {
  const [showEditor, setShowEditor] = useState(false);

  return (
    <>
      <ProductInfo />
      {showEditor && (
        <Suspense fallback={<EditorSkeleton />}>
          <RichTextEditor />  {/* solo carga cuando se abre */}
        </Suspense>
      )}
      <button onClick={() => setShowEditor(true)}>Edit Description</button>
    </>
  );
}
```

---

## Tree Shaking — Eliminar Código No Usado

```typescript
// ❌ MAL: importar todo el módulo (tree shaking no puede eliminar lo que no usas)
import _ from 'lodash';
const result = _.groupBy(items, 'category');

// ✅ BIEN: importar solo la función (tree shaking elimina el resto)
import groupBy from 'lodash/groupBy';
// O con lodash-es (ESM — mejor tree shaking)
import { groupBy } from 'lodash-es';

// ❌ MAL: barrel export que importa todo
// components/index.ts
export { Button } from './Button';
export { Input } from './Input';
// ... 50 más

// Importar del barrel: import { Button } from '@/components'
// → puede cargar todos los 50 componentes según el bundler

// ✅ BIEN: importar directo cuando tree shaking del barrel no funciona
import { Button } from '@/components/ui/Button';

// Verificar tree shaking con bundlephobia.com
// Comparar "bundle size" vs "tree-shakeable size"

// ❌ Peores offenders comunes:
// moment.js → usar date-fns (tree-shakeable) o Intl.DateTimeFormat nativo
// lodash    → usar lodash-es o funciones nativas
// axios     → considerar fetch nativo para bundles muy pequeños
// @mui/material → importar de la ruta específica, no del barrel

// Alternativas ligeras:
// moment.js (300KB) → date-fns (tree-shakeable, ~2KB por función)
// lodash (25KB+)    → just (funciones individuales, ~1-2KB c/u)
// chart.js (200KB)  → uPlot (40KB) para charts simples
```

---

## Optimización del Main Thread

```typescript
// El main thread hace todo: render, eventos, scripts
// Si está bloqueado > 50ms → el usuario percibe lag

// 1. Web Workers para cálculos costosos
const worker = new Worker(new URL('./heavy-computation.worker.ts', import.meta.url));

worker.postMessage({ data: largeDataSet });
worker.onmessage = (event) => {
  setResult(event.data.result);
};

// heavy-computation.worker.ts
self.onmessage = (event) => {
  const result = performHeavyCalculation(event.data.data);
  self.postMessage({ result });
};

// 2. Diferir trabajo no urgente con scheduler API
// Hacer trabajo cuando el browser esté idle
if ('scheduler' in window) {
  scheduler.postTask(() => {
    loadAnalytics();
    prefetchNextRoute();
  }, { priority: 'background' });
} else {
  requestIdleCallback(() => {
    loadAnalytics();
    prefetchNextRoute();
  });
}

// 3. Debounce en event handlers costosos
function SearchInput() {
  const [query, setQuery] = useState('');
  const debouncedQuery = useDebounce(query, 300); // no buscar en cada keystroke

  // Usar debouncedQuery en la query, no query
  const { data } = useSearch(debouncedQuery);

  return <input value={query} onChange={e => setQuery(e.target.value)} />;
}

// 4. Evitar layouts síncronos forzados (Layout Thrashing)
// ❌ MAL: leer y escribir DOM alternadamente
elements.forEach(el => {
  const height = el.offsetHeight;  // fuerza layout
  el.style.height = height + 10 + 'px';  // invalida layout
});

// ✅ BIEN: agrupar lecturas y escrituras
const heights = elements.map(el => el.offsetHeight);  // leer todo
elements.forEach((el, i) => {
  el.style.height = heights[i] + 10 + 'px';  // escribir todo
});

// 5. Intersection Observer para ejecutar solo cuando es visible
function ExpensiveComponent() {
  const [isVisible, setIsVisible] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setIsVisible(true);
          observer.disconnect(); // solo necesitamos saberlo una vez
        }
      },
      { threshold: 0.1 }
    );

    if (ref.current) observer.observe(ref.current);
    return () => observer.disconnect();
  }, []);

  return (
    <div ref={ref}>
      {isVisible ? <HeavyChart /> : <ChartSkeleton />}
    </div>
  );
}
```

---

## Script Loading — Eliminar Render-Blocking

```html
<!-- ❌ MAL: bloquea el render mientras descarga y ejecuta -->
<script src="analytics.js"></script>

<!-- ✅ BIEN: defer — ejecuta después del HTML parseado, antes de DOMContentLoaded -->
<script src="app.js" defer></script>

<!-- ✅ BIEN: async — ejecuta tan pronto como descarga (sin orden garantizado) -->
<!-- Solo para scripts completamente independientes (analytics, chat widgets) -->
<script src="analytics.js" async></script>

<!-- ✅ MEJOR: type=module — defer por defecto + ESM -->
<script type="module" src="app.js"></script>

<!-- Preload para recursos críticos — descarga con alta prioridad -->
<link rel="preload" href="/fonts/inter.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/hero-image.webp" as="image">

<!-- Prefetch para recursos probables en navegación futura -->
<link rel="prefetch" href="/pages/about.js">

<!-- Preconnect para dominios externos críticos -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://api.myapp.com">
<link rel="dns-prefetch" href="https://cdn.myapp.com"> <!-- menor beneficio, sin costo -->
```

---

## Lighthouse CI — Performance Gate en CI/CD

```yaml
# .github/workflows/performance.yml
- name: Run Lighthouse CI
  uses: treosh/lighthouse-ci-action@v11
  with:
    urls: |
      https://staging.myapp.com/
      https://staging.myapp.com/products
      https://staging.myapp.com/checkout
    budgetPath: ./.lighthouserc.json
    uploadArtifacts: true

# .lighthouserc.json — umbrales mínimos
{
  "ci": {
    "assert": {
      "assertions": {
        "categories:performance":    ["error", {"minScore": 0.8}],
        "categories:accessibility":  ["error", {"minScore": 0.9}],
        "categories:seo":            ["warn",  {"minScore": 0.9}],
        "first-contentful-paint":    ["error", {"maxNumericValue": 2000}],
        "largest-contentful-paint":  ["error", {"maxNumericValue": 3000}],
        "cumulative-layout-shift":   ["error", {"maxNumericValue": 0.1}],
        "total-blocking-time":       ["error", {"maxNumericValue": 300}]
      }
    }
  }
}
```
