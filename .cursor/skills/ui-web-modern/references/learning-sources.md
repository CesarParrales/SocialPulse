# Learning Sources — recursos gratuitos (uso literal e inspiración)

```
last_updated: 2026-06
revisar_cada: 90 días
```

> **Caducidad:** URLs, tiers freemium y modas visuales cambian. Contrastar fecha
> de `last_updated` antes de recomendar. Las **tendencias no son verdades** —
> usar como radar o mood board, no como spec. Principios con evidencia → gates
> de validación; estética de prompt libraries → solo inspiración acotada.

---

## Cómo usar este archivo

```
USO LITERAL (copiar/adaptar con gates):
→ Componentes con código (21st.dev, shadcn)
→ Herramientas de contraste, iconos SVG, fuentes open source
→ Checklist de principios UX gratuitos (uxuiprinciples.com)
→ Plantillas docentes KIT UX UI (Figma, patrones base)

USO INSPIRACIÓN (extraer principio, no copiar pixel):
→ Prompts de heroes/landings (MotionSites free tier)
→ Artículos de tendencias (Acceseo, Awwwards, Dribbble)
→ Generadores IA con créditos free (UX Pilot — validar con ui-audit después)

REGLA: literal sin auditoría = deuda técnica. Inspiración sin criterio = moda.
```

---

## Principios UX — evidencia (gratis)

**Fuente:** [uxuiprinciples.com/es/principles](https://uxuiprinciples.com/es/principles)

Principios con acceso grativo al jun 2026 (citas + prompts IA incluidos):

| Principio | Uso literal | Uso inspiración | Skill |
|-----------|-------------|-----------------|-------|
| Carga cognitiva | Limitar ítems visibles por pantalla | Simplificar nav/forms antes de “embellecer” | ui-audit, ux-architecture |
| Consistencia y estándares | Checklist de patrones repetidos | Alinear con design system existente | design-system, ui-audit |
| Ley de Jakob | No reinventar patrones dominantes | Benchmark vs productos que el usuario ya usa | ux-discovery |
| Ley de Hick | Reducir opciones por decisión | Menús cortos, CTAs primario/secundario | ux-architecture |
| Divulgación progresiva | Ocultar avanzado bajo “Más opciones” | Onboarding por capas | ux-architecture |
| Ley de Fitts | Targets ≥44px, CTAs grandes en mobile | Hero CTA placement | ui-mobile-native |
| Flujo conversacional | Turnos claros en chat/voz | Copy de asistentes IA | prompt-engineer |
| Transparencia IA | Mostrar límites y fuente de sugerencias | UI de features con IA | ui-web-modern |
| Bienestar inclusivo | Reducir estrés, no dark patterns | Tono y densidad de alertas | ux-discovery |

Gate sugerido en auditoría: validar al menos **3 principios gratuitos** relevantes
al flujo auditado → ver `ui-audit/references/ux-principles-free.md`.

El catálogo completo (185+) tiene tier de pago; no asumir acceso salvo que el
equipo lo tenga.

---

## Hub herramientas en español (gratis)

**Fuente índice:** [formiux.com/herramientas](https://formiux.com/herramientas/)

No duplicar toda la lista aquí — FormiUX cura enlaces. Subconjunto **100% free**
más usado en la suite:

```
DISEÑO UI (free tier / open source):
  Figma               → figma.com (free starter)
  PenPot              → penpot.app (open source)

COLORES + ACCESIBILIDAD (free):
  Coolors             → coolors.co
  Color Hunt          → colorhunt.co
  Leonardo            → leonardocolor.io
  Color Tool          → material.io/resources/color
  Colour Contrast     → colourcontrast.cc
  Color Hub           → colorhub.vercel.app
  Palett              → palett.es

ICONOS (free, SVG):
  Heroicons           → heroicons.com
  Feather Icons       → feathericons.com
  Tabler Icons        → tabler.io/icons
  Lucide              → lucide.dev
  Google Material Symbols → fonts.google.com/icons
  Flowbite Icons      → flowbite.com/icons

ILUSTRACIONES (free tier):
  unDraw              → undraw.co
  Storyset            → storyset.com
  Blush               → blush.design
  DrawKit             → drawkit.com (free pack)

TIPOGRAFÍAS (free):
  Google Fonts        → fonts.google.com
  Fontshare           → fontshare.com

RESEARCH / USABILIDAD (free tier o freemium útil):
  GTmetrix            → gtmetrix.com (límites free)
  Wayback Machine     → web.archive.org

USER FLOWS (free tier):
  FigJam              → figma.com/figjam
  Miro                → miro.com (free plan)

OPTIMIZACIÓN IMÁGENES (free):
  TinyPNG             → tinypng.com
  Squosh              → squoosh.app

PLUGINS FIGMA (free):
  Autoflow, Lorem Ipsum, Unsplash, Split Shape — ver listado FormiUX
```

**Recursos docentes ES:** [kituxui.com](https://kituxui.com) — UX flows, UI base,
accesibilidad intro. Copiar/adaptar en Figma para workshops (`team-onboarding`).

---

## Componentes — uso literal en código

**21st.dev (community, gratis)** — [21st.dev/community/components](https://21st.dev/community/components)

```
CUÁNDO: acelerar moléculas/organismos React en producto web.
CATEGORÍAS ÚTILES:
  → Buttons, Cards, Forms, Tables, Sidebars (admin)
  → Heroes, Pricing, Testimonials (marketing — inspiración + adaptar tokens)

REGLAS:
  → Una familia visual por proyecto (no mezclar 5 autores)
  → Revisar focus, contraste, bundle size antes de merge
  → Adaptar a tokens del design system — no pegar estilos crudos

COMPLEMENTO ATEMPORAL (ya en trends-watch):
  shadcn/ui, Tremor, Radix — preferir si el proyecto ya los usa
```

---

## Inspiración visual — tier free

**MotionSites** — [motionsites.ai](https://motionsites.ai)

```
GRATIS: subset de prompts marcados "Copy" (heroes, landings, SaaS, agency).
PREMIUM: ignorar salvo licencia explícita del cliente.

USO: mood board + estructura de hero (headline, CTA, social proof).
NO USO: glass/3D/cinematic pesado en app producto sin medir performance-web.

Etiqueta en brief: [INSPIRACIÓN · MotionSites · revisar 90d]
```

**UX Pilot** — [uxpilot.ai/es/ai-ui-generator](https://uxpilot.ai/es/ai-ui-generator)

```
FREEMIUM: créditos limitados; prototipos rápidos wireframe/alta fidelidad.
USO: explorar 2-3 variantes de flujo en fase ux-architecture EXPRESS.
GATE OBLIGATORIO: ui-audit + web-interface-guidelines antes de implementar.
No dependencia en CI/CD.
```

**Acceseo tendencias 2026** — [acceseo.com/tendencias-ux-ui-2026.html](https://www.acceseo.com/tendencias-ux-ui-2026.html)

```
Radar editorial — desglosar cada punto en moda vs principio:
  MODA (contexto marketing): Make it big, Y2K, realismo digital exagerado
  PRINCIPIO: diseño sin barreras, sostenibilidad digital (peso/WPO)
  DIRECCIÓN LARGO PLAZO: screen-free — no default para web clásica

Detalle filtrado → trends-watch.md § Radar editorial 2026
```

---

## Mapa fuente → skill → modo de uso

| Fuente | Gratis | Skill | Modo |
|--------|--------|-------|------|
| UX/UI Principles (subset) | Sí | ui-audit, ux-* | Literal (checklist) |
| FormiUX índice | Sí | ui-web-modern | Literal (herramientas) |
| KIT UX UI | Sí | team-onboarding | Literal (plantillas) |
| 21st.dev community | Sí | atomic-design, react-patterns | Literal + adaptar tokens |
| MotionSites Copy | Parcial | ui-web-modern | Solo inspiración |
| UX Pilot | Freemium | ux-architecture | Prototipo → auditar |
| Acceseo 2026 | Sí (artículo) | trends-watch | Solo radar |

---

## Flujo recomendado (gratis de punta a punta)

```
1. Contexto     → ux-discovery + project-memory
2. Principios   → uxuiprinciples (3+ checks gratuitos)
3. Referentes   → Mobbin/Pageflows (inspiration.md) + productos Tier 1 (trends-watch)
4. Prototipo    → wireframes ASCII/Mermaid O UX Pilot free (opcional)
5. Componentes  → 21st.dev / shadcn → atomic-design
6. Validación   → ui-audit + performance-web (GTmetrix/Squosh si aplica)
7. Cierre       → LEARNINGS.md si alguna URL falló o el tier cambió
```
