---
name: performance-web
description: >
  Guía la optimización de performance web: Core Web Vitals, caché, lazy loading,
  bundle size y profiling, con flujo medir → optimizar → re-medir. Usar cuando
  el usuario mencione performance web, velocidad de carga, Core Web Vitals, LCP,
  FID, CLS, FCP, TTFB, caché de browser, CDN, lazy loading, code splitting,
  bundle analysis, tree shaking, minificación, compresión, optimización de
  imágenes, o cuando diga "mi sitio está lento", "cómo mejoro el score de
  Lighthouse", "cómo reduzco el bundle", "cómo optimizo el tiempo de carga",
  o cualquier variante relacionada con performance frontend y web.
---

# Web Performance Skill

Optimización de performance para aplicaciones web modernas.

**Core Web Vitals y métricas → `references/metrics.md`**
**Bundle y JavaScript → `references/bundle-js.md`**
**Caché, CDN y red → `references/cache-network.md`**
**Imágenes y assets → `references/assets.md`**
**Backend performance → `references/backend-perf.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — URL de staging/prod, gates `npm run build`.
2. Presupuestos de performance si están documentados.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** baseline/delta en Performance Audit Report; umbrales acordados → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory (URL a medir, entorno prod vs local).
1. **Medir el baseline**: ejecutar
   `npx lighthouse URL --only-categories=performance --output=json --output-path=./lighthouse.json --quiet`.
   Gate: el JSON existe y tiene `categories.performance.score`.
2. **Extraer LCP/TTFB/CLS/INP/FCP** del JSON (comandos `jq` y umbrales en
   `references/metrics.md`). Si hay tráfico real, contrastar con CrUX/PSI.
3. **Seguir el árbol de decisión** (abajo) para identificar el cuello de
   botella real — no asumir.
4. **Leer la reference correspondiente** al bottleneck y aplicar la
   optimización de mayor impacto primero (ver Quick Wins):
   - TTFB alto → `references/backend-perf.md`
   - LCP por imágenes/fonts → `references/assets.md`
   - LCP/INP por JS → `references/bundle-js.md`
   - Sin caché/CDN → `references/cache-network.md`
5. **Re-medir con el mismo comando** del paso 1 (idealmente 3 corridas y
   promediar) y comparar contra el baseline. Gate: la métrica objetivo mejoró;
   si empeoró o no cambió → revertir y volver al paso 3.
6. **Emitir el Performance Audit Report** (ver `## Entregable`) con baseline,
   bottleneck, acción y delta medido.
7. **Validación y cierre** — ejecutar `## Validación`; registrar gaps en `LEARNINGS.md`.

---

## El Proceso Correcto

```
1. MEDIR antes de optimizar — sin datos, sin dirección
2. IDENTIFICAR el cuello de botella real (no asumir)
3. OPTIMIZAR lo que los datos indican
4. MEDIR de nuevo para confirmar mejora
5. Repetir

Herramientas de medición (detalle y comandos en references/metrics.md):
- Lighthouse (DevTools o CLI) → score general + recomendaciones
- WebPageTest.org → medición real desde distintas ubicaciones
- Chrome DevTools Performance tab → flamegraph, main thread blocking
- Chrome DevTools Network tab → waterfall de recursos
- Core Web Vitals en producción → CrUX + web-vitals lib (RUM)
- PageSpeed Insights → datos reales de usuarios + laboratorio
```

Las métricas (LCP, INP, CLS, FCP, TTFB), sus umbrales y cómo medirlas con
Lighthouse CLI, CrUX y la librería web-vitals están en `references/metrics.md`.

---

## Árbol de Decisión — Dónde Está el Problema

```
Lighthouse score bajo:

Performance < 50?
├── TTFB > 800ms → problema de servidor/BD/caché
│   └── Ver references/backend-perf.md
├── LCP > 2.5s + TTFB ok → problema de frontend
│   ├── Imagen hero → references/assets.md
│   ├── Render-blocking JS → references/bundle-js.md
│   └── Sin CDN → references/cache-network.md
├── CLS > 0.1 → imágenes/fonts sin dimensiones
│   └── Ver references/assets.md#cls
└── INP > 200ms → JS pesado en main thread
    └── Ver references/bundle-js.md#main-thread

Puntuaciones:
90-100 = Rápido      → mantener, no optimizar compulsivamente
50-89  = Mejorar     → optimizaciones de mayor impacto primero
0-49   = Lento       → problema serio, usuarios lo notan
```

---

## Quick Wins — Impacto Alto, Esfuerzo Bajo

```
1. Imágenes modernas (WebP/AVIF) + lazy loading
   Impacto: -30-60% peso de página
   Tiempo: 1 hora

2. Compresión gzip/brotli en servidor
   Impacto: -70% tamaño de assets de texto
   Tiempo: 30 minutos

3. Cache-Control headers correctos
   Impacto: 0ms para recursos cacheados
   Tiempo: 30 minutos

4. Eliminar render-blocking scripts
   Impacto: -0.5-2s en FCP/LCP
   Tiempo: 1-2 horas

5. Preconnect a dominios externos críticos
   Impacto: -200-500ms de latencia DNS
   Tiempo: 15 minutos

6. Font display: swap
   Impacto: elimina FOIT (flash of invisible text)
   Tiempo: 15 minutos

7. Critical CSS inline
   Impacto: -0.3-1s FCP
   Tiempo: 2-4 horas

8. Prefetch de rutas probables
   Impacto: navegación "instantánea"
   Tiempo: 1 hora
```

---

## Checklist de Performance por Tipo de Proyecto

### Next.js / SSR
- [ ] `next/image` para todas las imágenes (no `<img>`)
- [ ] `next/font` para fuentes (no Google Fonts CDN)
- [ ] Server Components para contenido estático
- [ ] `loading.tsx` con skeletons en rutas pesadas
- [ ] ISR/SSG donde los datos lo permitan
- [ ] Bundle analyzer corrido y chunks grandes identificados

### SPA (React/Vue)
- [ ] Code splitting por ruta (`lazy()` + `Suspense`)
- [ ] Bundle analyzer — identificar dependencias pesadas
- [ ] Tree shaking verificado (no imports de barrel con todo)
- [ ] Virtualización en listas > 100 elementos
- [ ] `React.memo` solo donde hay evidencia de re-renders costosos

### Backend/API
- [ ] TTFB < 800ms en Lighthouse
- [ ] Redis para queries frecuentes y lentas
- [ ] Índices en columnas de WHERE y JOIN
- [ ] `SELECT` explícito (no `SELECT *`)
- [ ] Paginación en todos los endpoints de listado
- [ ] HTTP/2 habilitado en servidor

---

## Defaults si falta contexto

Si el usuario no especifica, asumir Y DECLARAR (máx. 1 pregunta solo si es
bloqueante, p. ej. no hay URL accesible para medir):

- **URL a medir**: la home en producción; si no hay deploy, levantar el build
  de producción local (`npm run build && npm run start`) — nunca el dev server.
- **Dispositivo**: mobile (default de Lighthouse) — es el caso peor.
- **Métrica objetivo**: la peor de los Core Web Vitals según umbrales de
  `references/metrics.md`.
- **Score aceptable**: ≥ 90 no se optimiza más; el esfuerzo va a lo que está
  en rojo primero.
- **Presupuesto de cambios**: empezar por Quick Wins antes de refactors grandes.

---

## Ejemplo input → output

**Input:** "Dashboard Inertia tarda 4s en LCP en mobile."

**Output:** Lighthouse baseline LCP 4.1s; bottleneck JS bundle + imagen hero; lazy-load + WebP; re-medición LCP 2.3s. Gate: `npx lighthouse URL --only-categories=performance` score sube ≥10 pts.

---

## Validación

| Gate | Comando | Criterio |
|------|---------|----------|
| Baseline | `npx lighthouse URL --only-categories=performance --output=json` | JSON con score |
| Re-medición | mismo comando post-fix | métrica objetivo mejoró |
| Build prod | `npm run build` (si hubo cambios JS) | exit 0 |
| Report | plantilla `## Entregable` | antes/después documentado |

---

## Entregable

Plantilla del **Performance Audit Report**:

```markdown
# Performance Audit Report — <URL> — YYYY-MM-DD

## Baseline (Lighthouse, mobile, mediana de 3 corridas)
| Métrica | Valor | Umbral | Estado |
|---|---|---|---|
| Score | n | ≥90 | 🔴/🟡/🟢 |
| LCP | n s | ≤2.5s | ... |
| TTFB | n ms | ≤800ms | ... |
| CLS | n | ≤0.1 | ... |
| INP | n ms | ≤200ms | ... |

## Bottleneck identificado
- <diagnóstico según el árbol de decisión + evidencia>

## Acciones aplicadas
1. <acción> (reference usada: ...)

## Delta medido (re-medición con el mismo comando)
| Métrica | Antes | Después | Delta |
|---|---|---|---|
| ... | ... | ... | ... |

## Pendientes / siguiente iteración
- ...
```

---

## Skills relacionadas

- `monitoring-observability` — web vitals y performance en producción continua.
- `nextjs-fullstack` / `react-patterns` — patrones de rendering y bundle del frontend.
- `laravel-backend` / `node-backend` — optimización del TTFB y queries.

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
