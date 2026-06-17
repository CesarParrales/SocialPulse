# Trends Watch — diseño web

```
last_updated: 2026-06
revisar_cada: 90 días
```

> **Datos con caducidad — revisar.** Todo lo que está en este archivo es
> sensible al tiempo: tendencias, modas, productos de referencia. El SKILL.md
> y las demás references contienen solo principios atemporales. Al usar este
> archivo, verificar la fecha de `last_updated`; si pasaron más de 90 días,
> contrastar con fuentes actuales antes de recomendar.

---

## Tendencias Sólidas vs Modas Pasajeras

```
TENDENCIAS SÓLIDAS (principios con fundamento):
→ Dark mode como opción (no como imposición)
→ Glassmorphism sutil (fondos translúcidos con blur, no la versión exagerada de 2020)
→ Tipografía variable (una fuente, múltiples pesos/anchos)
→ Espaciado generoso — "air" en el layout
→ Bordes redondeados moderados (8-12px, no 2px ni 999px en todo)
→ Micro-interacciones funcionales (comunican estado, no solo decoran)
→ Gradientes sutiles (de color a color similar, no arcoíris)
→ Ilustraciones 3D o isométricas de alta calidad (no clip art)
→ Modo claro con toques de color acento
→ Bento grid para feature sections (popularizado ~2024)

MODAS PASAJERAS (cuidado):
→ Neumorphism (fue tendencia 2020-2021, ya envejeció mal)
→ Gradientes agresivos (2021-2022)
→ Exceso de glassmorphism (2022-2023)
→ Todo en negro o todo en blanco sin matiz
→ Fuentes muy decorativas en body text (solo en headlines grandes)
→ Animaciones excesivas que ralentizan la percepción
```

---

## Referencias Tier 1 por tipo de producto

### SaaS / Herramientas de Productividad

```
REFERENCIA TIER 1 (los mejores en diseño de producto, ~2024):
  Linear.app          → El estándar de oro en SaaS design 2024
                        Velocidad percibida, atajos, densidad inteligente
  Vercel.com          → Claridad extrema, dark mode perfecto
  Figma.com           → Sidebar navigation, panel de propiedades
  Notion.so           → Estructura flexible, clean UI
  Liveblocks.io       → Documentación de producto excelente
  Resend.com          → Email SaaS con diseño impecable
  Planetscale.com     → Database UI muy bien diseñada
  Raycast.app         → Command palette, launcher UI

LO QUE APRENDER DE CADA UNO:
  Linear:    Cómo hacer interfaces de alta densidad sin sentirse abrumador
  Vercel:    Cómo comunicar información técnica de forma clara
  Notion:    Cómo hacer un producto flexible que parece simple
  Figma:     Cómo estructurar paneles de herramientas complejas
```

### Landing Pages de Producto

```
REFERENCIAS DE ALTO IMPACTO:
  Stripe.com          → El referente absoluto de landing pages B2B
                        Storytelling visual, code demos interactivos
  Tailwindcss.com     → Documentación + landing perfecta
  Framer.com          → Animaciones de producto integradas
  Vercel.com/home     → Arquitectura de información de landing
  Linear.app/homepage → Hero section con product screenshot
  Lottiefiles.com     → Animaciones en landing
```

### Admin / Dashboard

```
REFERENCIAS DE DASHBOARDS:
  Grafana             → El referente en visualización de datos
  Retool              → Admin UI flexible
  Airtable            → Tablas y vistas múltiples
  Metabase            → Analytics accesible
  Datadog             → Dashboards de monitoreo complejos
  GitHub              → Pull requests y issues (admin patterns)
  Vercel Analytics    → Dashboard de métricas limpio

PARA COMPONENTES ESPECÍFICOS:
  shadcn/ui           → Componentes con código, listos para usar
  ui.shadcn.com/charts → Recharts bien diseñados
  tremor.so           → Componentes de dashboard con React
  Mantine UI          → Componentes con excelente accesibilidad
```

---

## Notas de moda sobre técnicas (contexto temporal)

```
Gradientes: volvieron como mesh gradients y color-to-color sutiles
  (técnica en references/color.md). El degradado saturado-a-blanco
  se percibe como ~2012; los gradientes arcoíris agresivos, como 2021-2022.

Bento grid: tendencia consolidada desde ~2024 para feature sections
  (estructura en references/layout.md).

Glassmorphism: vigente solo en su versión sutil
  (receta en references/color.md).
```

---

## Radar editorial 2026 (inspiración — no spec)

```
Fuente: acceseo.com/tendencias-ux-ui-2026.html (dic 2025)
Revisar con: learning-sources.md · Las tendencias NO son verdades.

TRATAR COMO MODA (solo marketing/brand, no producto denso):
→ Make it big — tipografía maximalista en hero, no en tablas/forms
→ Estética Y2K — alta caducidad; Gen Z/contexto específico
→ Realismo digital exagerado — glass/texturas pesadas

TRATAR COMO PRINCIPIO (aplicar con gates):
→ Diseño sin barreras → WCAG + ui-audit + web-interface-guidelines
→ Sostenibilidad digital → performance-web, Squosh/TinyPNG, animaciones ≤300ms

DIRECCIÓN LARGO PLAZO (no default web clásica):
→ Screen free / voz-gestos — canal secundario; documentar en ux-architecture

Prompt libraries con estética moda (MotionSites free "Copy"):
→ Etiqueta [INSPIRACIÓN · revisar 90d] — ver learning-sources.md
```

---

## Componentes y código (free tier vigente)

```
COMMUNITY (gratis, calidad heterogénea):
  21st.dev/community/components → heroes, pricing, tables, sidebars
  Reglas: una familia visual · ui-audit antes de merge

ESTABLES EN ECOSISTEMA (preferir si el repo ya los usa):
  shadcn/ui, Tremor, Radix — ver sección Admin arriba

FREEMIUM (prototipo, no producción directa):
  uxpilot.ai → wireframes/alta fidelidad; gate ui-audit obligatorio
```
