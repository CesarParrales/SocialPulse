# Accesibilidad WCAG — Evaluar y Corregir

## Los Cuatro Principios WCAG (POUR)

```
Perceptible    → La información debe ser presentable para todos los usuarios
Operable       → Los componentes de la UI deben ser operables
Comprensible   → La información y el funcionamiento deben ser comprensibles
Robusto        → El contenido debe ser interpretado por tecnologías asistivas

Niveles de conformidad:
A   → Nivel mínimo. Sin esto, algunos usuarios no pueden usar el sistema.
AA  → Nivel recomendado. El estándar de la industria y el legal en muchos países.
AAA → Nivel óptimo. No siempre alcanzable para todo el contenido.

Target: WCAG 2.1 AA para la mayoría de productos.
WCAG 2.2 es la versión más reciente (2023) — compatible con 2.1.
```

---

## Contraste de Color — El Más Incumplido

```
Ratio mínimo de contraste:

Texto normal (< 18pt o < 14pt bold):
  AA:  4.5:1 mínimo
  AAA: 7:1 mínimo

Texto grande (≥ 18pt o ≥ 14pt bold):
  AA:  3:1 mínimo
  AAA: 4.5:1 mínimo

Componentes UI y gráficos:
  AA:  3:1 mínimo (bordes, iconos, indicadores)

Errores comunes de contraste:
❌ Texto gris claro sobre fondo blanco (#999 on #fff = 2.85:1 — falla AA)
❌ Texto blanco sobre azul claro (#fff on #4A90D9 = 2.88:1 — falla AA)
❌ Placeholder text muy claro (intencionalmente tenue pero ilegible)
❌ Texto de error en rojo claro (#ff6b6b on #fff = 2.89:1 — falla)

Herramientas para medir contraste:
→ WebAIM Contrast Checker: webaim.org/resources/contrastchecker
→ Figma Plugin: A11y - Color Contrast Checker
→ Chrome DevTools: Accessibility panel → color contrast
→ coolors.co/contrast-checker

Paleta con contraste asegurado:
Para texto sobre blanco (#FFFFFF), los colores deben ser oscuros:
  Negro:       #000000 → 21:1 ✅
  Gris oscuro: #595959 → 7.0:1 ✅ (AAA)
  Gris medio:  #767676 → 4.54:1 ✅ (AA)
  Gris claro:  #949494 → 3.03:1 ❌ (falla AA texto normal)
```

---

## Tamaño de Texto y Área Táctil

```
Texto legible:
  Mínimo recomendado: 16px para cuerpo de texto
  Mínimo absoluto: 12px (legible pero no recomendado para texto largo)
  Texto legal / metadata: 11-12px aceptable si es secundario

Área táctil (mobile):
  WCAG recomienda: 44×44px mínimo para elementos interactivos
  Apple HIG: 44pt mínimo
  Material Design: 48×48dp mínimo
  La zona táctil puede ser mayor que el elemento visual (padding invisible)

Errores comunes:
❌ Botón de 24×24px → imposible tocar con precisión en mobile
❌ Links muy juntos sin espacio entre ellos
❌ Checkbox sin área táctil extendida al label

Solución para área táctil:
  /* El elemento visual puede ser pequeño, el área táctil grande */
  .small-button {
    min-width: 44px;
    min-height: 44px;
    padding: 12px; /* extiende el área sin cambiar el visual */
  }
```

---

## Navegación por Teclado

```
Todo el sistema debe ser operable solo con teclado.
Usuarios con discapacidades motoras no usan mouse.

Tab order:
→ El foco debe seguir un orden lógico (generalmente de arriba-izquierda a abajo-derecha)
→ Los elementos interactivos deben ser alcanzables con Tab
→ El foco no debe quedar atrapado en un componente (focus trap solo en modals)

Focus visible:
→ El elemento con foco siempre debe ser visualmente distinguible
→ Nunca hacer: `outline: none` sin reemplazar con otro indicador de foco
→ El indicador de foco debe tener contraste de 3:1 contra el fondo

Atajos de teclado esenciales:
→ Tab: mover al siguiente elemento
→ Shift+Tab: mover al anterior
→ Enter / Espacio: activar botón, link, checkbox
→ Esc: cerrar modal, cancelar acción
→ Flechas: navegar dentro de componentes (tabs, radio buttons, selects)

Cómo testear:
1. Desconectar el mouse
2. Navegar toda la aplicación solo con Tab y Enter
3. Verificar que todo es alcanzable
4. Verificar que el foco es visible en todo momento
5. Verificar que los modales atrapan el foco correctamente

Errores comunes:
❌ `outline: none` en elementos interactivos
❌ Dropdown que solo funciona con hover (no con teclado)
❌ Modal que no atrapa el foco (el Tab sale del modal al fondo)
❌ Elementos custom interactivos sin tabindex o role ARIA
```

---

## Textos Alternativos y ARIA

```
Imágenes:
→ Imágenes decorativas: alt="" (vacío, no "imagen" ni "foto")
→ Imágenes informativas: alt con descripción concisa del contenido
→ Imágenes de texto: alt con el texto exacto de la imagen
→ Imágenes de acción (botón/link): alt describe la acción, no la imagen

Errores comunes de alt:
❌ alt="imagen" o alt="foto" → inútil para lectores de pantalla
❌ alt="DSC_0042.jpg" → nombre de archivo, no descripción
❌ Alt repetitivo del caption que ya está en el DOM
❌ Sin alt en imagen que es el único contenido de un link

ARIA esencial para componentes custom:

Roles:
→ role="button" para elementos que actúan como botón pero no son <button>
→ role="navigation" para bloques de navegación
→ role="dialog" para modals
→ role="alert" para mensajes de error/success dinámicos
→ role="tab" / "tabpanel" para componentes de tabs

Estados:
→ aria-expanded="true/false" para accordions, dropdowns
→ aria-checked="true/false" para checkboxes custom
→ aria-selected="true/false" para tabs, items de lista seleccionables
→ aria-disabled="true" para elementos deshabilitados
→ aria-required="true" para campos obligatorios
→ aria-invalid="true" para campos con error

Relaciones:
→ aria-labelledby para asociar label con elemento
→ aria-describedby para asociar descripción/error con elemento
→ aria-controls para relación entre trigger y el panel que controla
→ aria-live="polite" para anuncios dinámicos (success, error, loading)

Ejemplo correcto de campo con error:
<label for="email">Email</label>
<input
  id="email"
  type="email"
  aria-invalid="true"
  aria-describedby="email-error"
  aria-required="true"
>
<p id="email-error" role="alert">
  El email no tiene un formato válido.
</p>
```

---

## Herramientas de Auditoría de Accesibilidad

```
Automáticas (encuentran ~30-40% de los problemas):
→ axe DevTools (extensión Chrome/Firefox) → la más completa
→ WAVE (wave.webaim.org) → visual y fácil de interpretar
→ Lighthouse → tab Accessibility en Chrome DevTools
→ Axe-core en CI → automatizar en el pipeline de testing

Semiautomáticas:
→ Color Oracle → simula daltonismo en toda la pantalla
→ NoCoffee Vision Simulator → simula distintas deficiencias visuales
→ Accessibility Insights → guía paso a paso

Manuales (esenciales — lo automático no puede capturar todo):
→ Navegar con solo teclado (Tab, Enter, Esc, flechas)
→ Testear con VoiceOver (Mac/iOS) o NVDA (Windows) o TalkBack (Android)
→ Zoom al 200% y verificar que nada se rompe
→ Testear con modo de alto contraste del sistema operativo

Proceso recomendado:
1. Correr axe DevTools → resolver todos los errores automáticos
2. Navegar con teclado → resolver focus y tab order
3. Activar lector de pantalla → resolver semántica y ARIA
4. Testear con usuarios reales con discapacidad (idealmente)
```

---

## Checklist portable de accesibilidad (copiable)

```
Sin imagen, generar este checklist estructurado en markdown
(no depender de herramientas de visualización externas al editor):

┌─────────────────────────────────────────────────────────┐
│              CHECKLIST WCAG 2.1 AA                      │
│                                                         │
│  CONTRASTE                                              │
│  □ Texto normal: ratio ≥ 4.5:1                         │
│  □ Texto grande: ratio ≥ 3:1                            │
│  □ Componentes UI: ratio ≥ 3:1                         │
│                                                         │
│  TECLADO                                                │
│  □ Todo interactivo alcanzable con Tab                  │
│  □ Foco visible en todo momento                         │
│  □ Sin focus trap fuera de modales                      │
│  □ Esc cierra modales y menús                           │
│                                                         │
│  TEXTOS ALTERNATIVOS                                    │
│  □ Imágenes informativas tienen alt descriptivo         │
│  □ Imágenes decorativas tienen alt=""                  │
│  □ Iconos de acción tienen aria-label                   │
│                                                         │
│  SEMÁNTICA                                              │
│  □ Jerarquía de headings (h1→h2→h3) lógica             │
│  □ Formularios con labels asociados                     │
│  □ Errores con aria-describedby                         │
│  □ Listas con <ul>/<ol> correcto                        │
└─────────────────────────────────────────────────────────┘

Uso: "genera el checklist de accesibilidad para [tipo de pantalla]"
→ marcar cada ítem ✓ cumple / ✗ falla / — no aplica
```
