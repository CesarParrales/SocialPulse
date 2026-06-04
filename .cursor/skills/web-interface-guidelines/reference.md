# Web Interface Guidelines — Referencia completa

Reglas por categoría. Revisar archivos contra estas reglas y reportar `archivo:línea - hallazgo`.

---

## Accessibility

- Botones solo icono: `aria-label`
- Controles de formulario: `<label>` o `aria-label`
- Elementos interactivos: manejadores de teclado (`onKeyDown`/`onKeyUp`)
- `<button>` para acciones; `<a>`/`<Link>` para navegación (no `<div onClick>`)
- Imágenes: `alt` (o `alt=""` si son decorativas)
- Iconos decorativos: `aria-hidden="true"`
- Actualizaciones async (toasts, validación): `aria-live="polite"`
- Usar HTML semántico (`<button>`, `<a>`, `<label>`, `<table>`) antes de ARIA
- Encabezados jerárquicos `<h1>`–`<h6>`; incluir skip link al contenido principal
- Anclas de encabezado: `scroll-margin-top`

---

## Focus States

- Elementos interactivos con foco visible: `focus-visible:ring-*` o equivalente
- Nunca `outline-none` / `outline: none` sin reemplazo de foco
- Preferir `:focus-visible` sobre `:focus` (evitar anillo en click)
- Controles compuestos: agrupar foco con `:focus-within`

---

## Forms

- Inputs con `autocomplete` y `name` significativo
- `type` correcto (`email`, `tel`, `url`, `number`) y `inputmode`
- Nunca bloquear paste (`onPaste` + `preventDefault`)
- Labels clickeables (`htmlFor` o envolviendo el control)
- `spellCheck={false}` en emails, códigos, usernames
- Checkboxes/radios: label + control en un solo hit target (sin zonas muertas)
- Botón submit habilitado hasta que empiece la petición; spinner durante la petición
- Errores inline junto a los campos; foco en el primer error al enviar
- Placeholders terminan en `…` y muestran patrón de ejemplo
- `autocomplete="off"` en campos no-auth para no disparar el gestor de contraseñas
- Avisar antes de salir con cambios sin guardar (`beforeunload` o guard del router)

---

## Animation

- Respetar `prefers-reduced-motion` (variante reducida o desactivar)
- Animar solo `transform`/`opacity` (compositor-friendly)
- Nunca `transition: all`—listar propiedades explícitamente
- `transform-origin` correcto
- SVG: transformaciones en wrapper `<g>` con `transform-box: fill-box; transform-origin: center`
- Animaciones interrumpibles—responder a input del usuario a mitad de animación

---

## Typography

- `…` no `...`
- Comillas curvas `"` `"` no rectas `"`
- Espacios de no separación: `10&nbsp;MB`, `⌘&nbsp;K`, nombres de marca
- Estados de carga terminan en `…`: `"Loading…"`, `"Saving…"`
- `font-variant-numeric: tabular-nums` para columnas/comparaciones numéricas
- `text-wrap: balance` o `text-pretty` en encabezados (evita viudas)

---

## Content Handling

- Contenedores de texto manejan contenido largo: `truncate`, `line-clamp-*`, o `break-words`
- Hijos en flex necesitan `min-w-0` para permitir truncado
- Manejar estados vacíos—no renderizar UI rota para strings/arrays vacíos
- Contenido generado por usuario: anticipar inputs cortos, medios y muy largos

---

## Images

- `<img>` con `width` y `height` explícitos (evita CLS)
- Imágenes bajo el pliegue: `loading="lazy"`
- Imágenes críticas above-the-fold: `priority` o `fetchpriority="high"`

---

## Performance

- Listas grandes (>50 items): virtualizar (ej. `virtua`, `content-visibility: auto`)
- No lecturas de layout en render (`getBoundingClientRect`, `offsetHeight`, `offsetWidth`, `scrollTop`)
- Agrupar lecturas/escrituras DOM; evitar intercalar
- Preferir inputs no controlados; controlados deben ser baratos por tecla
- `<link rel="preconnect">` para dominios CDN/asset
- Fuentes críticas: `<link rel="preload" as="font">` con `font-display: swap`

---

## Navigation & State

- La URL refleja estado—filtros, tabs, paginación, paneles expandidos en query params
- Links con `<a>`/`<Link>` (soporte Cmd/Ctrl+click, middle-click)
- Deep-link en UI con estado (si usa `useState`, considerar sincronizar con URL, ej. nuqs)
- Acciones destructivas: modal de confirmación o ventana de undo—nunca inmediatas

---

## Touch & Interaction

- `touch-action: manipulation` (evita retraso de zoom por doble tap)
- `-webkit-tap-highlight-color` definido de forma intencional
- `overscroll-behavior: contain` en modales/drawers/sheets
- Durante drag: desactivar selección de texto, `inert` en elementos arrastrados
- `autoFocus` con moderación—solo desktop, un solo input principal; evitar en móvil

---

## Safe Areas & Layout

- Layouts full-bleed: `env(safe-area-inset-*)` para notches
- Evitar scrollbars no deseados: `overflow-x-hidden` en contenedores, corregir overflow
- Flex/grid en lugar de medición con JS para layout

---

## Dark Mode & Theming

- `color-scheme: dark` en `<html>` para temas oscuros (scrollbar, inputs)
- `<meta name="theme-color">` acorde al fondo de la página
- `<select>` nativo: `background-color` y `color` explícitos (dark mode en Windows)

---

## Locale & i18n

- Fechas/horas: `Intl.DateTimeFormat`, no formatos hardcodeados
- Números/monedas: `Intl.NumberFormat`, no formatos hardcodeados
- Detección de idioma: `Accept-Language` / `navigator.languages`, no por IP

---

## Hydration Safety

- Inputs con `value` necesitan `onChange` (o `defaultValue` si no controlados)
- Render de fecha/hora: evitar mismatch de hidratación (servidor vs cliente)
- `suppressHydrationWarning` solo donde sea realmente necesario

---

## Hover & Interactive States

- Botones/links con estado `hover:` (feedback visual)
- Estados interactivos aumentan contraste: hover/active/focus más prominentes que el resto

---

## Content & Copy

- Voz activa: "Install the CLI" no "The CLI will be installed"
- Title Case en encabezados/botones (estilo Chicago)
- Números para conteos: "8 deployments" no "eight"
- Labels de botón específicos: "Save API Key" no "Continue"
- Mensajes de error incluyen solución/siguiente paso, no solo el problema
- Segunda persona; evitar primera persona
- `&` en lugar de "and" cuando falte espacio

---

## Anti-patterns (marcar siempre)

- `user-scalable=no` o `maximum-scale=1` que desactiven zoom
- `onPaste` con `preventDefault`
- `transition: all`
- `outline-none` sin reemplazo focus-visible
- Navegación con `onClick` inline sin `<a>`
- `<div>` o `<span>` con manejadores de click (deben ser `<button>`)
- Imágenes sin dimensiones
- Arrays grandes con `.map()` sin virtualización
- Inputs de formulario sin labels
- Botones icono sin `aria-label`
- Formatos de fecha/número hardcodeados (usar `Intl.*`)
- `autoFocus` sin justificación clara
