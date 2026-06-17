# Tipografía para Web Moderna

## Principios de Tipografía Web

```
Legibilidad primero:
→ La tipografía existe para ser leída
→ Una fuente "interesante" que sacrifica legibilidad = mal diseño
→ Las mejores interfaces web se leen sin que el usuario "vea" la tipografía

Jerarquía tipográfica:
→ El usuario debe poder escanear la página con los ojos cerrados
→ Los headings crean estructura visual antes de que el usuario lea el texto
→ El contraste de tamaño+peso es más poderoso que el color para la jerarquía

Variable fonts:
→ Una sola fuente, múltiples pesos, anchos e inclinaciones
→ Mejor performance (un solo archivo .woff2 para todos los pesos)
→ Animaciones tipográficas posibles sin cargar múltiples fuentes
```

---

## Fuentes Recomendadas por Contexto

```
INTERFACES SaaS / PRODUCTO (claridad y neutralidad):
  Inter Variable          → El estándar de facto para SaaS
                            Muy legible en pantalla, excelente en cualquier tamaño
                            Disponible en Google Fonts
  Geist Variable          → De Vercel, diseñada específicamente para UI
                            Moderna, geométrica, excelente legibilidad
  DM Sans                 → Más personalidad que Inter sin perder claridad
  Plus Jakarta Sans       → Alternativa a Inter con más carácter humanista

LANDING PAGES (impacto visual):
  Cal Sans                → Impacto en headlines, reconocida en el ecosistema dev
  Cabinet Grotesk         → Elegante y moderna para headlines premium
  Clash Display           → Personalidad fuerte para headlines de alto impacto
  Archivo                 → Versátil entre display y body

CONTENIDO EDITORIAL / BLOG:
  Source Serif 4 Variable → Excelente legibilidad en textos largos
  Lora                    → Clásica y legible para artículos
  Fraunces Variable       → Personalidad visual en un serif con eje óptico

CÓDIGO Y DATOS:
  JetBrains Mono          → El estándar para IDEs y code blocks
  Fira Code               → Ligaduras para código, muy legible
  Berkeley Mono           → Más carácter visual, excelente para terminals

COMBINACIONES QUE FUNCIONAN BIEN:
  Headlines               Body
  ─────────────────────────────
  Cal Sans            +   Inter          (contraste de carácter)
  Cabinet Grotesk     +   DM Sans        (coherencia geométrica)
  Fraunces Variable   +   Source Serif 4 (editorial clásico)
  Clash Display       +   Inter          (impacto + claridad)
  Geist Variable          sola            (sistema completo)
```

---

## Escala Tipográfica para Web

```
Escala modular basada en razón áurea (1.333) — "Perfect Fourth":
12 / 14 / 16 / 21 / 28 / 37 / 50 / 67px

Escala práctica para productos (más opciones intermedias):
  xs:   12px / 0.75rem    → captions, metadata, labels pequeños
  sm:   14px / 0.875rem   → body secundario, helper text, badges
  base: 16px / 1rem       → body principal (base de todo el sistema)
  lg:   18px / 1.125rem   → body destacado, intro paragraphs
  xl:   20px / 1.25rem    → h4, subtítulos menores
  2xl:  24px / 1.5rem     → h3, section headings
  3xl:  30px / 1.875rem   → h2, major section titles
  4xl:  36px / 2.25rem    → h1 en páginas interiores
  5xl:  48px / 3rem       → hero titles en landing
  6xl:  60px / 3.75rem    → display grande en hero
  7xl:  72px / 4.5rem     → headlines de máximo impacto

PESOS por rol:
  Body text:    400 (regular)
  Emphasis:     500 (medium) — usar con moderación
  Labels/UI:    500-600 (medium/semibold)
  Headings:     600-700 (semibold/bold)
  Display:      700-800 (bold/extrabold)
  Heavy impact: 800-900 (extrabold/black) — solo en muy grande

RECOMENDACIÓN PRÁCTICA:
No usar más de 3-4 tamaños por pantalla.
Constrastar por tamaño Y peso para máxima jerarquía.
```

---

## Line Height, Letter Spacing y Measure

```
LINE HEIGHT por contexto:
  Display/headlines muy grandes (>40px): 1.1 - 1.2
  Headings normales (24-40px):           1.2 - 1.3
  Subheadings (18-24px):                 1.3 - 1.4
  Body text (14-18px):                   1.5 - 1.7
  UI labels y botones:                   1.2 - 1.4

LETTER SPACING por tamaño:
  Muy grande (>40px):    -0.02em a -0.04em (tight)
  Headings (24-40px):    -0.01em a -0.02em (slightly tight)
  Body (16-24px):        0em (neutral)
  Small (12-14px):       0.01em a 0.05em (wider para legibilidad)
  Uppercase/caps:        0.05em a 0.1em (siempre más espacio)

MEASURE (longitud de línea):
  Cuerpo de texto largo:  55-75 caracteres (ideal ~65)
  UI labels:              sin límite (son cortos por naturaleza)
  Cards y sidebars:       40-55 caracteres

Por qué importa la measure:
→ Líneas muy largas (>80 chars): el ojo pierde el hilo al saltar de línea
→ Líneas muy cortas (<40 chars): demasiados saltos de línea, ritmo cortado
→ El max-width del contenedor de texto controla esto:
  max-width: 65ch; /* la unidad ch = ancho del carácter '0' */
```

---

## Tipografía Responsive

```css
/* Tipografía fluida — sin media queries para el tamaño */
/* clamp(mínimo, preferido, máximo) */

:root {
  /* Escala fluida: crece de 14px en móvil a 16px en desktop */
  --text-sm:   clamp(0.8rem, 0.17vw + 0.76rem, 0.875rem);
  --text-base: clamp(0.875rem, 0.34vw + 0.76rem, 1rem);
  --text-lg:   clamp(1rem, 0.61vw + 0.85rem, 1.125rem);

  /* Headlines fluidas: crecen significativamente */
  --text-2xl: clamp(1.25rem, 1.5vw + 0.85rem, 1.5rem);
  --text-3xl: clamp(1.5rem, 2.5vw + 0.85rem, 2rem);
  --text-4xl: clamp(1.75rem, 3.5vw + 0.6rem, 2.5rem);
  --text-5xl: clamp(2rem, 5vw + 0.5rem, 3.5rem);
  --text-6xl: clamp(2.5rem, 7vw + 0.5rem, 4.5rem);
}

/* Alternativa con media queries — más control explícito */
h1 { font-size: 2rem; }

@media (min-width: 768px) {
  h1 { font-size: 2.75rem; }
}

@media (min-width: 1200px) {
  h1 { font-size: 3.5rem; }
}
```

---

## Tipografía para Dark Mode

```css
/* El texto en dark mode necesita ajustes sutiles */

/* Light mode: negro casi puro sobre blanco */
--color-text-primary: #111827;   /* gray-900 */
--color-text-secondary: #4B5563; /* gray-600 */

/* Dark mode: no usar blanco puro — demasiado contraste genera fatiga */
@media (prefers-color-scheme: dark) {
  --color-text-primary: #F3F4F6;   /* gray-100 — no #FFFFFF */
  --color-text-secondary: #9CA3AF; /* gray-400 */
}

/* Por qué no #FFFFFF sobre #000000 en dark mode:
   El contraste 21:1 es técnicamente perfecto pero visualmente duro.
   El ojo humano ve "halos" alrededor del texto blanco sobre negro puro.
   Usar gray-100 sobre gray-900: contraste ~15:1 — excelente y sin fatiga visual. */

/* Font weight en dark mode — el texto fino se percibe más tenue */
/* A veces es necesario aumentar el font-weight ligeramente */
@media (prefers-color-scheme: dark) {
  body {
    font-weight: 400; /* o 450 con variable font */
    -webkit-font-smoothing: antialiased; /* crítico en dark mode */
    -moz-osx-font-smoothing: grayscale;
  }
}
```

---

## Plantilla portable — ejemplos tipográficos (texto)

```
Sin imagen, generar esta representación en markdown (texto/ASCII;
no depender de herramientas de visualización externas al editor):

Comparativa de combinaciones:
  [Cal Sans headline] + [Inter body]
  Large heading bold | Subheading medium | Body regular text sample
  ─────────────────────────────────────────────
  [Geist Variable solo]
  Large heading bold | Subheading medium | Body regular text sample

Escala tipográfica visual:
  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  72px Bold  Headline Impact
  ━━━━━━━━━━━━━━━━━━━━━━━━  48px Bold  Hero Title
  ━━━━━━━━━━━━━━━━  36px Semibold  Section Title
  ━━━━━━━━━━━  24px Semibold  Subsection
  ━━━━━━━━  20px Medium  Card Title
  ━━━━━  16px Regular  Body Text
  ━━━  14px Regular  Secondary Text
  ━━  12px Medium  Caption/Label

Uso:
"genera ejemplos de combinaciones tipográficas para [SaaS/landing/blog]"
"genera la escala tipográfica para [producto]"
"muéstrame tipografía para dark mode"
```
