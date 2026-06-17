# Governance y Versioning

## Por Qué el Diseño y el Código se Desincronean

```
El problema más común en design systems maduros:
→ El diseñador actualiza el componente en Figma
→ El desarrollador no se entera
→ El código y el diseño divergen silenciosamente
→ Tres meses después, nadie sabe cuál es la versión "correcta"

O el inverso:
→ El desarrollador necesita una variante que no existe en Figma
→ La implementa en código sin actualizar el diseño
→ Figma dice una cosa, el browser muestra otra

La solución no es herramientas — es proceso.
```

---

## Modelo de Governance: Federado vs Centralizado

```
CENTRALIZADO (un equipo dueño de todo):
  ✅ Alta consistencia
  ✅ Control de calidad claro
  ❌ Cuello de botella — el equipo de DS es lento para los feature teams
  ❌ Desconexión de las necesidades reales del producto
  Cuándo: empresas grandes con design system como producto (Figma, Atlassian)

FEDERADO (múltiples contribuidores con un core team):
  ✅ Escala con el producto
  ✅ Los feature teams contribuyen lo que necesitan
  ✅ El core team mantiene la calidad y coherencia
  ❌ Requiere proceso robusto de contribución
  Cuándo: la mayoría de productos con equipos de 5-30 personas

DESCENTRALIZADO (cada equipo hace lo suyo):
  ❌ Inevitablemente diverge
  ❌ No es un design system, es una colección de componentes
  Cuándo: nunca (si el objetivo es consistencia)

Recomendación: FEDERADO con un owner claro del sistema
```

---

## Proceso de Contribución

```
Para agregar o modificar un componente:

1. PROPOSAL (design + dev)
   → Describir qué se necesita y por qué
   → Verificar que no existe algo similar ya
   → Propuesta inicial en texto o boceto

2. DESIGN REVIEW
   → El designer del DS revisa la propuesta
   → Verifica que se alinea con los tokens y patrones existentes
   → Da feedback o aprueba para desarrollo

3. IMPLEMENTACIÓN
   → El developer implementa el componente
   → Usa los tokens del sistema (no hardcoded)
   → Implementa todos los estados requeridos
   → Incluye tests y stories de Storybook

4. ACCESIBILITY CHECK
   → Axe DevTools en Storybook sin errores
   → Navegación por teclado manual
   → Contraste verificado

5. DESIGN QA
   → El designer verifica que el código coincide con el diseño
   → Pixel parity en los casos principales
   → Sign-off del designer del DS

6. DOCUMENTATION
   → Story completa con todos los estados
   → Usage guidelines escritos
   → Props documentados
   → Ejemplos de código

7. MERGE + RELEASE
   → PR aprobado
   → CHANGELOG actualizado
   → Version bump
   → Publicar en npm / actualizar en Figma
```

---

## Versionado Semántico del Design System

```
Seguir Semver: MAJOR.MINOR.PATCH

PATCH (1.0.X): correcciones sin cambio de API
  → Fix de bug visual en un componente
  → Corrección de typo en documentación
  → Mejora de accesibilidad sin cambio de props

MINOR (1.X.0): nuevas funcionalidades sin breaking changes
  → Nuevo componente agregado al sistema
  → Nueva variante de componente existente
  → Nuevo token agregado
  → Nueva prop opcional en componente existente

MAJOR (X.0.0): breaking changes
  → Cambio en el nombre de un token (--color-blue → --color-primary)
  → Cambio en props de un componente (variant="contained" → variant="primary")
  → Eliminación de un componente o token
  → Cambio en el comportamiento que rompe implementaciones existentes

Deprecation process:
  Versión N:   Marcar como @deprecated con JSDoc + warning en consola
  Versión N+1: Mantener con advertencia más visible
  Versión N+2: Eliminar (MAJOR bump obligatorio)

Siempre comunicar con tiempo:
  → Anunciar deprecaciones en el CHANGELOG con al menos 2 minor versions de aviso
  → Documentar el migration path (cómo migrar del old al new)
  → Ofrecer codemod si hay muchos usos en el codebase
```

---

## CHANGELOG — Comunicar los Cambios

```markdown
# Changelog

## [2.4.0] — 2024-03-15

### Added
- `Select` component con soporte para multi-select y opciones agrupadas
- Token `--color-warning-strong` para texto sobre fondo de warning
- Propiedad `fullWidth` en `Button` para ocupar todo el ancho del contenedor

### Changed
- `Card` ahora acepta `as` prop para renderizar como `<article>`, `<section>` o `<div>`
- Tamaño del spinner en `Button` reducido de 20px a 16px para mejor proporcionalidad

### Fixed
- `Input` en estado `disabled` ya no aplica `:hover` styles en browsers modernos
- `Modal` ya no pierde el focus trap al hacer resize de la ventana
- Contraste del placeholder text aumentado de 3.1:1 a 4.6:1 (cumple WCAG AA)

### Deprecated
- `--color-blue-primary` será eliminado en v3.0.0 → usar `--color-primary`
- Prop `isFullWidth` en `Button` → usar `fullWidth` (sin el prefijo `is`)

---

## [2.3.1] — 2024-03-08

### Fixed
- `Tooltip` ya no causa jank visual al aparecer en Safari 17
- `Badge` ahora aplica correctamente el color `--color-text-inverse` en variant dark
```

---

## Figma + Código — Mantenerlos Sincronizados

```
El problema: Figma y el código son dos fuentes de verdad distintas.
La solución: hacer los tokens la única fuente de verdad.

Flujo recomendado:

Tokens Studio Plugin (Figma):
→ Los tokens se definen en JSON
→ El plugin los sincroniza con las variables de Figma
→ El mismo JSON alimenta Style Dictionary para generar CSS/SCSS/JS
→ Un cambio en el token JSON → se actualiza Figma Y el código

Flujo sin Tokens Studio:
→ Definir tokens en CSS custom properties
→ Figma Tokens Plugin lee el CSS y crea variables de Figma
→ Al cambiar el CSS → ejecutar el plugin para sincronizar Figma

Automated design QA:
→ Chromatic (pago): compara screenshots de Storybook entre commits
→ Detecta cambios visuales no intencionales automáticamente
→ El diseñador revisa y aprueba o rechaza los diffs visuales

Checklist de sincronización:
□ Los nombres de tokens en Figma = los nombres en CSS (--color-primary en ambos)
□ Los valores numéricos son idénticos (spacing de 16px en Figma = 1rem en CSS)
□ Los componentes de Figma tienen los mismos estados que el código
□ Las props de código tienen control equivalente en Figma (variant, size, state)
```

---

## Métricas de Salud del Design System

```
Adoption rate:
  % de pantallas del producto que usan componentes del DS
  Objetivo: > 80%
  Medir con: grep en el código / auditoría visual

Coverage:
  % de componentes necesarios que están en el DS
  Objetivo: los componentes core están cubiertos antes de escalar

Drift rate:
  % de componentes en el código que divergen del diseño en Figma
  Objetivo: < 10% de divergencia en componentes core
  Medir con: Chromatic o visual audit manual

Contribution velocity:
  Tiempo promedio desde propuesta hasta merge de un nuevo componente
  Objetivo: < 2 semanas para componentes medianos
  Señal de proceso demasiado burocrático si es > 4 semanas

Satisfaction:
  NPS interno: ¿el equipo considera que el DS les ahorra tiempo?
  Objetivo: score positivo (más personas que lo recomiendan que los que no)
  Medir con: encuesta semestral al equipo
```
