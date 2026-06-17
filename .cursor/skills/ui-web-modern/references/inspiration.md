# Referencias e Inspiración

## Fuentes de Inspiración Confiables

```
El criterio al buscar inspiración:
→ Buscar productos en producción, no solo conceptos de Dribbble
→ Los conceptos de Behance/Dribbble son frecuentemente inimplementables
→ Los productos reales resuelven restricciones reales (accesibilidad, datos, edge cases)
→ La inspiración debe ser del mismo contexto (SaaS→SaaS, no SaaS→app de fotos)

Nota: las listas de herramientas y recursos concretos de este archivo
(plataformas, iconos, fuentes) son datos con caducidad — contrastar con
trends-watch.md y con el estado actual del ecosistema antes de recomendar.

Catálogo curado de fuentes gratuitas (principios, componentes, hubs ES,
inspiración vs uso literal) → `learning-sources.md` (misma carpeta).
```

---

## Hubs externos (no duplicar listas completas)

```
ESPAÑOL — índice de herramientas UX/UI (mayoría free):
  formiux.com/herramientas → ver learning-sources.md § Hub herramientas

DOCENTE / ONBOARDING — plantillas copiables:
  kituxui.com → UX, UI, accesibilidad intro (team-onboarding)

PRINCIPIOS CON EVIDENCIA (subset gratuito):
  uxuiprinciples.com/es/principles → ui-audit/references/ux-principles-free.md

COMPONENTES CÓDIGO (community free):
  21st.dev/community/components → adaptar tokens; gate ui-audit
```

---

## Por Tipo de Producto

Las listas de productos de referencia (Tier 1 SaaS, landings, dashboards)
caducan con el tiempo → viven en `trends-watch.md` (misma carpeta), que tiene
fecha de actualización y ciclo de revisión. Consultar ahí antes de citar
referentes concretos.

Lo atemporal de cada categoría:

```
SaaS / productividad — qué evaluar en un referente:
  → Densidad de información sin sensación de abrumar
  → Velocidad percibida (atajos, optimistic UI)
  → Claridad al comunicar información técnica
  → Estructura de paneles y navegación lateral

PATRONES QUE FUNCIONAN EN LANDINGS B2B (estables):
  → Hero con screenshot del producto (no solo illustration)
  → Social proof temprano (logos de clientes reconocibles)
  → Features en grid o bento (no lista de bullets)
  → Pricing transparente (o CTA hacia pricing claro)
  → Testimonials específicos (nombre + empresa + foto real)
  → Demo/trial CTA prominente

Admin / dashboard — qué evaluar en un referente:
  → Visualización de datos legible a escala
  → Tablas con filtros potentes y vistas múltiples
  → Jerarquía clara entre KPIs, charts y tablas
```

---

## Plataformas de Inspiración

```
CURADAS POR CALIDAD (lo mejor para interfaces reales):
  Mobbin.com          → Capturas de apps reales en iOS/Android/Web
                        Filtrable por categoría, patrón de UI, color
  Screenlane.com      → Screenshots de landing pages y SaaS
  Landingfolio.com    → Landing pages reales con filtros por industry
  Pageflows.com       → Flujos completos de onboarding y registro
  UiGarage.net        → UI patterns con filtros precisos

DISEÑO CONCEPTUAL (inspiración visual, cuidado con implementar directamente):
  Dribbble.com        → Alta calidad visual, baja aplicabilidad real
                        Útil para: paletas, estilos, tendencias
                        No útil para: copiar como solución de UX real
  Behance.net         → Projects completos, útil para ver sistemas
  Awwwards.com        → Sitios de alto impacto visual (muchos son impracticables)

SISTEMAS DE DISEÑO PÚBLICOS:
  design-systems.gallery → Galería de design systems reales
  UntitledUI.com      → Design system Figma de referencia
  Radix Colors        → Paletas de color accesibles
  Open Source Design  → Design systems de empresas grandes

CHANGELOGS Y ACTUALIZACIONES:
  Figma changelog     → Ver qué nuevas features añaden (revelan tendencias)
  Linear changelog    → Ejemplo de changelog como branding
  Vercel changelog    → Cómo comunicar updates de producto
```

---

## Recursos de Iconos

```
PARA INTERFACES MODERNAS:
  Lucide Icons        → El estándar para SaaS (consistente, limpio, outline)
                        Disponible en React, Vue, Flutter, SVG
  Heroicons           → Del equipo de Tailwind, mismo estilo
  Phosphor Icons      → Mayor variedad de estilos (thin/regular/bold/fill)
  Radix Icons         → Minimalistas, perfectos para UI densa
  Tabler Icons        → 4000+ iconos, outline y filled

PARA LANDING / MARKETING:
  Feather Icons       → Ultra limpios para secciones de features
  Solar Icons (Figma) → Premium, muy bien diseñados
  Material Symbols    → Variable fonts de iconos de Google

PARA MOBILE NATIVO:
  SF Symbols          → iOS/macOS (usar el app SF Symbols en Mac)
  Material Symbols    → Android/Material Design

REGLAS DE USO:
→ Una sola librería por proyecto (consistencia de estilo)
→ Mismo tamaño en el mismo contexto (16px para inline, 24px para actions)
→ Mismo estilo (no mezclar outline con filled en la misma sección)
→ Siempre con texto o aria-label (nunca icono solo sin contexto)
```

---

## Recursos de Fuentes Gratuitas

```
GOOGLE FONTS (gratuitas, las mejores para web):
  Inter Variable      → fonts.google.com/specimen/Inter
  Geist Variable      → vercel.com/font (no en Google Fonts)
  DM Sans             → fonts.google.com/specimen/DM+Sans
  Plus Jakarta Sans   → fonts.google.com/specimen/Plus+Jakarta+Sans
  Source Serif 4      → fonts.google.com/specimen/Source+Serif+4

FONTSOURCE (npm, auto-alojadas, mejor performance):
  npm install @fontsource-variable/inter
  import '@fontsource-variable/inter';
  → Evita el CDN de Google Fonts (privacy + performance)

FUENTES GRATUITAS DE ALTA CALIDAD:
  Cal Sans            → cal.com/fonts (gratuita, impacto en headlines)
  Cascadia Code       → Microsoft, para code blocks
  Commit Mono         → Para terminales y editores

SERVICIOS DE FUENTES DE PAGO (cuando vale la inversión):
  Klim Type           → Calibre, Tiempos, Söhne
  Pangram Pangram     → Cabinet Grotesk, General Sans
  Fontshare           → Curación de fuentes de calidad, algunas gratuitas
```

---

## Recursos de Imágenes y Ilustraciones

```
FOTOS (gratuitas, alta calidad):
  Unsplash.com        → El estándar para fotos gratuitas
  Pexels.com          → Similar a Unsplash
  Picsum.photos       → Placeholders para desarrollo
  Pravatar.cc         → Avatares de usuario para prototipos

ILUSTRACIONES:
  unDraw.co           → Ilustraciones con color personalizable
  Storyset by Freepik → Ilustraciones animables
  Humaaans.com        → Ilustraciones de personas modulares
  Blush.design        → Kits de ilustración curados
  Spline.design       → 3D interactivo para web (sin 3D skills)

VIDEOS / LOOPS:
  Pexels.com/videos   → Videos gratuitos para fondos
  Mixkit.co           → Clips cortos gratuitos

GRADIENTES:
  Grainy Gradient (css.glass) → Gradientes con textura
  ui.glass/generator  → Glassmorphism CSS generator
  coolhue.web.app     → Gradientes curados

PLACEHOLDERS PARA DESARROLLO:
  placehold.co        → Imágenes placeholder con texto
  placeholder.com     → Similar
```

---

## Flujo de Investigación de Referentes

```
Proceso recomendado antes de diseñar:

1. DEFINIR EL CONTEXTO (5 min)
   ¿Qué tipo de producto? (SaaS/landing/dashboard/mobile)
   ¿Qué audiencia? (técnica/empresarial/consumidor)
   ¿Qué sensación debe transmitir? (rápido/confiable/creativo)

2. BUSCAR REFERENTES REALES (20-30 min)
   → Mobbin para patrones de UI específicos
   → Screenlane para el tipo de producto
   → Visitar los productos Tier 1 relevantes directamente
   → Capturar 10-15 referencias de alta calidad
   → Principios gratuitos: learning-sources.md § Principios UX
   → Componentes literales (si hay stack React): 21st.dev o shadcn del proyecto

3. IDENTIFICAR PATRONES (15 min)
   → Qué tienen en común los mejores referentes
   → Qué patrones de layout se repiten
   → Qué paletas de color se usan
   → Qué tipografías

4. HACER MOOD BOARD (15 min)
   → Collage de los mejores elementos de cada referente
   → No copiar — extraer los principios
   → Definir: "el producto se siente como X pero con Y"

5. VALIDAR CON EL EQUIPO
   → El mood board evita sorpresas en el diseño final
   → Todos tienen el mismo referente visual antes de empezar
   → Es más barato cambiar el mood board que cambiar el diseño
```
