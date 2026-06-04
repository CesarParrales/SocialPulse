---
name: web-interface-guidelines
description: Revisa código UI contra los Vercel Web Interface Guidelines (accesibilidad, focus, formularios, animación, tipografía, rendimiento, estado en URL, touch, dark mode, i18n, hidratación). Usar al revisar componentes web, PRs de frontend, o cuando el usuario pida lineamientos de interfaz web, estándares Vercel o compliance de UI.
---

# Web Interface Guidelines

Revisión de archivos de UI contra las [Vercel Web Interface Guidelines](https://github.com/vercel-labs/web-interface-guidelines). Salida concisa y útil; priorizar señal sobre ruido.

## Cuándo usar

- El usuario pide revisar código por "Web Interface Guidelines", "lineamientos Vercel" o "estándares de interfaz web".
- Revisión de componentes, páginas o PRs de frontend.
- Implementación de nuevas pantallas o componentes que deben cumplir buenas prácticas de UI web.

## Flujo de revisión

1. **Leer** los archivos indicados por el usuario (o el patrón/archivo que mencione).
2. **Comprobar** cada regla aplicable según [reference.md](reference.md).
3. **Reportar** por archivo, formato `file:line - hallazgo`. Sin preámbulos; solo explicación cuando el arreglo no sea obvio.

## Formato de salida

Agrupar por archivo. Usar `archivo:línea` (clickeable en VS Code/Cursor). Hallazgos breves.

```text
## src/Button.tsx

src/Button.tsx:42 - botón solo icono sin aria-label
src/Button.tsx:18 - input sin label
src/Button.tsx:55 - animación sin prefers-reduced-motion
src/Button.tsx:67 - transition: all → listar propiedades

## src/Modal.tsx

src/Modal.tsx:12 - falta overscroll-behavior: contain
src/Modal.tsx:34 - "..." → "…"

## src/Card.tsx

✓ pass
```

## Checklist rápido (antes de reportar)

- [ ] Accesibilidad: `aria-label` en botones solo icono, labels en controles, semántica HTML, `alt` en imágenes.
- [ ] Focus: anillo visible (`focus-visible:ring-*`), nunca `outline: none` sin reemplazo.
- [ ] Formularios: `autocomplete`, `name`, no bloquear paste, labels clickeables, errores inline.
- [ ] Animación: `prefers-reduced-motion`, solo `transform`/`opacity`, no `transition: all`.
- [ ] Tipografía: `…` no `...`, comillas curvas, espacios de no separación donde aplique.
- [ ] Contenido: truncado/line-clamp en contenedores de texto, estados vacíos, flex con `min-w-0`.
- [ ] Imágenes: `width` y `height` explícitos, `loading="lazy"` bajo el pliegue.
- [ ] Anti‑patrones: sin `user-scalable=no`, sin `onPaste`+preventDefault, sin `<div onClick>` para acciones.

## Referencia y ejemplos

- **Reglas por categoría:** [reference.md](reference.md)
- **Antes/después (accesibilidad, focus, forms, animación, tipografía, imágenes, copy):** [examples.md](examples.md)
