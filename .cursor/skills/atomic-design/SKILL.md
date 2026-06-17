---
name: atomic-design
description: >
  Metodología Atomic Design: átomos → moléculas → organismos → templates → páginas,
  aplicada en React, Flutter y mobile. Usar cuando el usuario mencione atomic design,
  átomos, moléculas, organismos, templates, componentes atómicos, jerarquía de
  componentes, cómo dividir componentes, "cómo organizo mis componentes", "cómo
  estructura la jerarquía de UI", "hasta dónde divido un componente", "qué es un
  átomo vs una molécula", o variantes sobre descomposición de interfaces.
---

# Atomic Design Skill

Atomic Design es una metodología para crear sistemas de diseño de interfaces.
Creada por Brad Frost en 2013, propone que las interfaces se construyen
como la química: todo se compone de unidades más pequeñas.

No es una regla estricta — es un modelo mental para organizar la UI.

> **Nota**: las reglas de esta skill son estrictas a propósito, pero tienen
> excepciones documentadas en `references/patterns-pitfalls.md` (variants vs
> componentes nuevos, organismos genéricos en shared libraries, cuándo adaptar
> la metodología). Ante un caso límite, ese archivo manda.

**Los 5 niveles → `references/levels.md`**
**Aplicación en React → `references/react-implementation.md`**
**Aplicación en Flutter → `references/flutter-implementation.md`**
**Errores comunes y cuándo adaptarlo → `references/patterns-pitfalls.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — convenciones de carpetas UI del proyecto.
2. Si project-memory apunta a `context.md` / estructura frontend → leer solo esa sección.
3. `LEARNINGS.md` de esta skill — solo `## Pendientes`.

**Al cerrar:** tabla de clasificación en `docs/` o PR; decisiones de estructura → project-memory.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory; inspeccionar árbol real (`resources/js/Components/`, `components/`, etc.).
1. **Si hay debate de categorización** (¿esto es átomo o molécula? ¿organismo
   o template?) → leer `references/patterns-pitfalls.md` ANTES de decidir.
2. **Inventariar** los componentes existentes o requeridos (desde el diseño,
   el código o la descripción del producto).
3. **Clasificar cada componente** en uno de los 5 niveles usando
   `references/levels.md` y la regla de oro de la jerarquía (abajo).
4. **Validar imports**: verificar que cada componente solo importa de su nivel
   o inferiores. Documentar violaciones encontradas.
5. **Aplicar al stack**: estructura de carpetas y convenciones según
   `references/react-implementation.md` o `references/flutter-implementation.md`.
6. **Entregar la tabla de clasificación** (formato fijo del Entregable) +
   plan de refactoring si hubo violaciones.
7. **Validación** — checklist de cierre + gates de `## Validación` si hay cambios de código.

Criterios de cierre:
```
✓ Todos los componentes del alcance están en la tabla con nivel y justificación
✓ Cero dependencias circulares o imports hacia niveles superiores sin justificar
✓ Las dudas de categorización se resolvieron consultando patterns-pitfalls.md
✓ El checklist de aplicación correcta (abajo) pasa completo
```

---

## Defaults si falta contexto

El agente asume y DECLARA estos supuestos en vez de preguntar
(máx 1 pregunta si es bloqueante):

- **Ante duda átomo vs molécula** → clasificar como molécula
  (es más barato bajar de nivel después que subir).
- **Sin stack definido** → asumir React y declararlo `[HIPÓTESIS]`.
- **Sin design system existente** → asumir que átomos y moléculas genéricas
  vivirán en una carpeta `ui/` compartida y declararlo.
- **Componente con lógica de dominio ambigua** → clasificarlo como organismo
  y marcarlo `[NO VERIFICADO]` para revisión.

---

## La Metáfora de la Química

```
Química:       Átomos → Moléculas → Organismos → ...
Atomic Design: Átomos → Moléculas → Organismos → Templates → Páginas

Átomo:      La unidad más pequeña que no puede dividirse más
            sin perder su función (Button, Input, Label, Icon)

Molécula:   Átomos combinados que forman una unidad funcional coherente
            (SearchBar = Input + Button, FormField = Label + Input + Error)

Organismo:  Moléculas y átomos organizados en una sección de interfaz
            (Header = Logo + Navigation + SearchBar + Avatar)

Template:   Organismos en un layout de página sin contenido real
            (La estructura de una página de producto sin datos reales)

Página:     Un template con contenido real
            (La página del producto iPhone 15 con sus datos y fotos reales)
```

---

## Por Qué Atomic Design Importa al Ingeniero

```
Sin modelo mental de jerarquía:
→ Cada componente tiene un tamaño y responsabilidad aleatoria
→ El mismo patrón se implementa de 5 formas distintas
→ Los componentes tienen dependencias circulares o implícitas
→ Un cambio en el diseño requiere tocar 15 archivos

Con Atomic Design:
→ La jerarquía es predecible — cualquiera del equipo sabe dónde buscar
→ Los componentes de bajo nivel (átomos) son altamente reutilizables
→ Los cambios se contienen en el nivel correcto
→ El onboarding es más rápido porque la organización tiene lógica

Aplicado al código:
→ Átomos = los componentes del design system (Button, Input, Icon)
→ Moléculas = combinaciones que resuelven un problema específico
→ Organismos = secciones completas de UI (Header, Sidebar, ProductCard)
→ Templates = layouts sin datos (usado en Storybook y diseño)
→ Páginas = lo que el router renderiza (conecta datos con templates)
```

---

## La Regla de Oro de la Jerarquía

```
Una unidad de un nivel SOLO puede usar unidades del mismo nivel
o de niveles INFERIORES.

✓ Una molécula puede usar átomos
✓ Un organismo puede usar moléculas y átomos
✓ Un template puede usar organismos, moléculas y átomos
✗ Un átomo NO puede usar una molécula
✗ Una molécula NO puede importar un organismo
✗ Un organismo NO puede usar un template

Señal de que algo está mal:
Si necesitas importar un "organismo" dentro de un "átomo"
→ El átomo en realidad es una molécula o más
→ O el organismo necesita dividirse

(Excepciones y casos límite: references/patterns-pitfalls.md)
```

---

## Entregables visuales (portables)

- Diagramas, sitemaps y flujos → bloques Mermaid (flowchart/graph)
- Auditorías y comparativas → tablas markdown
- Wireframes y layouts → bloques ASCII
- Jerarquías de componentes → árbol con indentación o Mermaid
No depender de herramientas de visualización externas al editor.

---

## Ejemplo input → output

**Input:** "Clasificar `Button`, `SearchBar` y `Header` en nuestro proyecto Inertia."

**Output:** tabla con `Button`→Átomo, `SearchBar`→Molécula (Input+Icon+Button), `Header`→Organismo; violación detectada si `Button` importa `Header`; plan de refactor en 2 PRs si aplica.

---

## Validación

| Gate | Acción | Criterio |
|------|--------|----------|
| Jerarquía | revisar imports en archivos tocados | sin import ascendente (átomo→organismo) |
| Build frontend | `npm run build` o `npm run dev` (según project-memory) | exit 0 |
| Lint (si existe) | `npm run lint` | exit 0 |
| Tabla entregada | formato fijo de `## Entregable` | todas las filas con justificación |

---

## Entregable

Tabla fija de clasificación (formato obligatorio):

```markdown
| Componente | Nivel | Justificación | Imports permitidos |
|---|---|---|---|
| Button | Átomo | Indivisible sin perder función; sin lógica de negocio | tokens, Icon |
| FormField | Molécula | Label + Input + Error como unidad funcional | átomos |
| LoginForm | Organismo | Sección completa; conoce el dominio auth | moléculas, átomos |
| AuthTemplate | Template | Layout sin datos reales (props/slots) | organismos ↓ |
| LoginPage | Página | Conecta datos/store con el template | template ↓ |
```

Complementos opcionales: árbol de composición (indentación o Mermaid) y plan
de refactoring para las violaciones detectadas.

---

## Checklist de Aplicación Correcta

```
✓ Los átomos no tienen lógica de negocio
✓ Los átomos no hacen fetching de datos
✓ Las moléculas son funcionales pero no conocen el dominio del negocio
✓ Los organismos son los primeros en conocer el dominio (Order, Product)
✓ Los templates no tienen datos reales (solo props/slots)
✓ Las páginas son las únicas que conectan con el store/API directamente
✓ La jerarquía no tiene dependencias circulares
✓ Un cambio en un átomo no rompe nada que no sea predecible
```

---

## Skills relacionadas

- `design-system` — atomic design es la filosofía; el design system es la implementación
- `react-patterns` — aplicación técnica de atomic design en React
- `mobile-flutter` — cómo aplicar atomic design en Flutter/Dart
- `ui-web-modern`, `ui-admin-dashboard`, `ui-mobile-native` — contextos de aplicación
- Componentes community free (21st.dev) → `ui-web-modern/references/learning-sources.md`

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
