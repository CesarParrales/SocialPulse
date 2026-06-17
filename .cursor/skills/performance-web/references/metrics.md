# Core Web Vitals y Métricas — Qué Medir y Cómo

## Core Web Vitals — Las Métricas que Importan

```
LCP — Largest Contentful Paint (velocidad de carga)
  Bueno:    ≤ 2.5 seg
  Mejorar:  2.5 - 4.0 seg
  Malo:     > 4.0 seg
  Causa: imagen hero sin optimizar, render-blocking JS/CSS, TTFB alto

FID/INP — First Input Delay / Interaction to Next Paint (interactividad)
  INP Bueno:    ≤ 200 ms
  INP Mejorar:  200 - 500 ms
  INP Malo:     > 500 ms
  Causa: JS pesado en main thread, hydration lenta, event handlers costosos
  Nota: INP reemplazó a FID como Core Web Vital oficial

CLS — Cumulative Layout Shift (estabilidad visual)
  Bueno:    ≤ 0.1
  Mejorar:  0.1 - 0.25
  Malo:     > 0.25
  Causa: imágenes sin dimensiones, fonts que cambian el layout, ads dinámicos

FCP — First Contentful Paint (percepción de velocidad)
  Bueno:    ≤ 1.8 seg
  Mejorar:  1.8 - 3.0 seg
  Causa: render-blocking resources, server response lento

TTFB — Time to First Byte (velocidad del servidor)
  Bueno:    ≤ 800 ms
  Causa: servidor lento, BD lenta, sin caché, CDN mal configurado
```

---

## Cómo Medir — Laboratorio (Lighthouse CLI)

```bash
# Instalar y correr Lighthouse desde CLI
npx lighthouse https://ejemplo.com --output=json --output-path=./lighthouse.json --quiet

# Solo la categoría de performance (más rápido)
npx lighthouse https://ejemplo.com --only-categories=performance --output=json --output-path=./lighthouse.json --quiet

# Extraer las métricas clave del JSON
cat lighthouse.json | jq '{
  score: .categories.performance.score,
  LCP:  .audits["largest-contentful-paint"].numericValue,
  TTFB: .audits["server-response-time"].numericValue,
  CLS:  .audits["cumulative-layout-shift"].numericValue,
  INP:  .audits["interaction-to-next-paint"].numericValue,
  FCP:  .audits["first-contentful-paint"].numericValue
}'

# Emular condiciones móviles (default) vs desktop
npx lighthouse https://ejemplo.com --preset=desktop --output=json --output-path=./lighthouse-desktop.json

# Promediar varias corridas — una sola medición es ruidosa
for i in 1 2 3; do
  npx lighthouse https://ejemplo.com --only-categories=performance \
    --output=json --output-path=./run-$i.json --quiet
done
```

Limitaciones del laboratorio: condiciones simuladas, una sola ubicación,
sin variabilidad de dispositivos reales. Útil para comparar antes/después
de un cambio, no para conocer la experiencia real de los usuarios.

---

## Cómo Medir — Datos Reales de Usuarios (CrUX)

```bash
# Chrome User Experience Report — datos reales de usuarios de Chrome
# Via PageSpeed Insights API (incluye CrUX + Lighthouse):
curl -s "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=https://ejemplo.com&strategy=mobile" \
  | jq '.loadingExperience.metrics'

# Via CrUX API directamente (requiere API key de Google):
curl -s "https://chromeuxreport.googleapis.com/v1/records:queryRecord?key=API_KEY" \
  -d '{"url": "https://ejemplo.com"}' | jq '.record.metrics'

# Qué da CrUX que Lighthouse no:
# → Percentil 75 de usuarios reales (el estándar de Google para "pasa CWV")
# → Distribución good/needs-improvement/poor por métrica
# → Datos de dispositivos y conexiones reales
# Limitación: requiere tráfico suficiente; sitios pequeños no aparecen
```

---

## Cómo Medir — En Producción (web-vitals lib / RUM)

```bash
npm install web-vitals
```

```javascript
// Instrumentación RUM (Real User Monitoring) con la lib oficial
import { onLCP, onINP, onCLS, onTTFB, onFCP } from 'web-vitals';

function sendToAnalytics(metric) {
  // Enviar al endpoint propio o a la herramienta de analytics
  navigator.sendBeacon('/analytics/vitals', JSON.stringify({
    name: metric.name,        // 'LCP' | 'INP' | 'CLS' | 'TTFB' | 'FCP'
    value: metric.value,
    rating: metric.rating,    // 'good' | 'needs-improvement' | 'poor'
    page: location.pathname,
  }));
}

onLCP(sendToAnalytics);
onINP(sendToAnalytics);
onCLS(sendToAnalytics);
onTTFB(sendToAnalytics);
onFCP(sendToAnalytics);
```

```
Cuándo usar cada fuente:
  Lighthouse CLI  → durante el desarrollo, comparar antes/después de cada cambio
  CrUX/PSI        → diagnóstico inicial y validación con usuarios reales
  web-vitals RUM  → monitoreo continuo en producción, por página y segmento

Otras herramientas de medición:
  WebPageTest.org                    → waterfall desde distintas ubicaciones
  Chrome DevTools Performance tab    → flamegraph, main thread blocking
  Chrome DevTools Network tab        → waterfall de recursos
```
