---
name: analisis-ux-implementacion-ui
description: Performs UX analysis (user goals, flows, usability criteria, accessibility) and translates findings into UI implementation with fidelity to design. Use when designing interfaces, reviewing UX, implementing Figma/mockups, or when the user mentions UX analysis, UI implementation, usability, design systems, or accessibility.
---

# Análisis UX e implementación UI

Guía el análisis de experiencia de usuario y la implementación fiel de interfaces en proyectos web, apps y productos digitales. Se integra con metodologías como Atomic Design cuando la estructura de componentes lo requiera.

## Flujo de trabajo

1. **Análisis UX**: Entender objetivos de usuario, contexto de uso y restricciones antes de proponer o implementar UI.
2. **Criterios de usabilidad**: Aplicar principios claros (visibilidad del estado, feedback, consistencia, prevención de errores, accesibilidad).
3. **Implementación UI**: Traducir diseño a código manteniendo especificaciones (espaciado, tipografía, estados, responsive).
4. **Validación**: Comprobar que la UI implementada cumple los criterios acordados y el diseño de referencia.

## Criterios de usabilidad a verificar

| Criterio | Pregunta clave |
|----------|----------------|
| **Visibilidad del estado** | ¿El usuario sabe en qué pantalla/estado está y qué puede hacer? |
| **Feedback** | ¿Las acciones tienen respuesta inmediata (loading, éxito, error)? |
| **Consistencia** | ¿Terminología, patrones y componentes se repiten de forma predecible? |
| **Prevención de errores** | ¿Se evitan errores con validación, confirmaciones o deshabilitado cuando aplica? |
| **Accesibilidad** | ¿Contraste, foco, semántica y teclado están cubiertos (WCAG 2.x)? |
| **Jerarquía y escaneo** | ¿La información importante destaca y el contenido es escaneable? |

## De análisis a implementación UI

- **Especificaciones**: Respetar espaciado, tipografía, colores y radios del diseño (tokens/variables cuando existan).
- **Estados**: Implementar todos los estados relevantes (default, hover, focus, active, disabled, error, loading).
- **Responsive**: Definir breakpoints y comportamiento en móvil/tablet/desktop según el diseño.
- **Componentes**: Reutilizar y componer (atoms → molecules → organisms) en lugar de duplicar UI.
- **Accesibilidad en código**: Etiquetas, roles ARIA cuando haga falta, orden de tabulación y contraste suficiente.

## Checklist antes de dar por cerrada la UI

- [ ] ¿Los objetivos de usuario y el flujo principal están respetados?
- [ ] ¿Hay feedback claro para acciones asíncronas (envío, guardado, error)?
- [ ] ¿Los estados interactivos (hover, focus, disabled, error) están implementados?
- [ ] ¿La implementación coincide con el diseño en espaciado, tipografía y colores?
- [ ] ¿La interfaz es usable con teclado y tiene contraste/textos legibles?
- [ ] ¿Los componentes reutilizables están bien nombrados y desacoplados?

## Formato de análisis UX (salida sugerida)

Al entregar un análisis UX, estructurar así:

```markdown
## Objetivos y contexto
- Usuario objetivo y tarea principal
- Restricciones (plataforma, permisos, datos)

## Flujo y puntos de fricción
- Pasos del flujo actual o propuesto
- Riesgos de abandono o confusión

## Criterios aplicados
- Qué criterios de usabilidad se priorizan y cómo se cumplen (o gaps)

## Recomendaciones
- Cambios concretos de UI o flujo
```

## Anti-patrones

- **Implementar sin contexto de uso**: Siempre aclarar quién usa la pantalla y para qué.
- **Saltarse estados**: No dejar solo el estado “ideal”; incluir loading, error y vacío.
- **Ignorar especificaciones del diseño**: No inventar márgenes, tamaños o colores; usar los del diseño o design system.
- **Componentes acoplados al flujo**: Evitar que un atom o molecule conozca rutas o lógica de negocio específica de una pantalla.
- **Accesibilidad al final**: Considerar contraste, foco y semántica desde el primer componente.