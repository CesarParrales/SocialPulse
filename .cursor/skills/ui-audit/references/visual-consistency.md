# Consistencia Visual — Auditar la Coherencia del Sistema

## Por Qué la Consistencia Importa

```
Un sistema inconsistente:
→ El usuario aprende patrones que luego se rompen → confusión
→ El desarrollador implementa el mismo componente de 5 formas distintas
→ El diseñador reinventa la rueda en cada pantalla nueva
→ La marca no se percibe como profesional o confiable

Un sistema consistente:
→ El usuario predice cómo funcionan las cosas nuevas
→ El desarrollador reutiliza componentes → velocidad y coherencia
→ El diseñador trabaja con decisiones ya tomadas → foco en problemas reales
→ La marca transmite profesionalismo y confianza
```

---

## Los 5 Ejes de Consistencia Visual

### 1. Tipografía

```
Qué auditar:

FAMILIAS:
→ ¿Cuántas familias tipográficas hay en uso? (ideal: máximo 2)
→ ¿Hay una familia para display y otra para body?
→ ¿Se usan web fonts que no están en el design system?

ESCALA:
→ ¿Hay una escala tipográfica definida? (11/12/14/16/20/24/32/48px)
→ ¿O hay tamaños ad-hoc (17px, 22px, 38px) sin sistema?

PESOS:
→ ¿Qué pesos se usan? (400, 500, 600, 700)
→ ¿Son consistentes con la intención semántica (bold = importante)?

LINE HEIGHT:
→ ¿Hay valores consistentes? (1.4 para body, 1.2 para headings)
→ ¿O varía entre pantallas?

Hallazgos comunes en auditoría:
❌ 8 tamaños de texto distintos en 5 pantallas
❌ 3 familias diferentes sin jerarquía clara
❌ Misma información con diferentes tamaños en pantallas diferentes
❌ Headings con font-weight inconsistente (600 en uno, 700 en otro)

Documenta:
→ Lista de todos los tamaños/pesos/familias encontrados
→ Cuáles son parte del sistema y cuáles son outliers
→ Propuesta de escala tipográfica consolidada
```

### 2. Color

```
Qué auditar:

PALETA:
→ ¿Cuántos colores distintos hay en uso? (capturar todos con eyedropper)
→ ¿Hay colores similares pero no idénticos? (#1A56DB vs #1B57DC)
→ ¿Los colores tienen roles semánticos definidos?

ROLES SEMÁNTICOS:
Primary:      acción principal, CTA, elementos interactivos clave
Secondary:    acciones secundarias, highlights
Success:      confirmaciones, estados completados (verde)
Warning:      alertas, estados que requieren atención (amarillo/naranja)
Error:        errores, estados destructivos (rojo)
Neutral:      textos, bordes, fondos

DARK MODE:
→ ¿Existe una versión dark? ¿Es coherente con el light?
→ ¿Los colores tienen suficiente contraste en ambos modos?

Hallazgos comunes:
❌ 4 tonos de azul diferentes sin jerarquía semántica
❌ El rojo se usa tanto para errores como para branding (confuso)
❌ Color primario que cambia entre pantallas (#0066CC en una, #0073E6 en otra)
❌ Sin estado hover definido para la mayoría de elementos interactivos

Documenta:
→ Tabla con cada color encontrado + su hex + dónde se usa
→ Colores que deben consolidarse
→ Colores que faltan (ej: no hay color de warning definido)
```

### 3. Espaciado

```
Qué auditar:

SISTEMA DE ESPACIADO:
→ ¿Hay una escala de spacing? (4/8/12/16/24/32/48px o 4pt grid)
→ ¿O hay padding/margin de valores arbitrarios?

CONSISTENCIA INTERNA DE COMPONENTES:
→ ¿Los botones del mismo tipo tienen el mismo padding?
→ ¿Las cards tienen el mismo padding interno?
→ ¿Las secciones tienen los mismos márgenes verticales?

RITMO VERTICAL:
→ ¿El espacio entre secciones es consistente?
→ ¿Los formularios tienen el mismo espacio entre campos?

Hallazgos comunes:
❌ Botones con padding-x: 12px en uno, 16px en otro, 14px en otro
❌ Espaciado entre campos de formulario varía sin razón
❌ Márgenes de página inconsistentes (32px en desktop, 20px en otra pantalla)

Documenta:
→ Lista de todos los valores de spacing encontrados
→ Propuesta de grid de 8px o 4px que consolida
```

### 4. Componentes

```
Qué auditar:

INVENTARIO DE COMPONENTES:
→ ¿Cuántas variantes del mismo componente existen?
→ ¿Los botones primarios siempre son iguales?
→ ¿Los inputs tienen el mismo estilo en todos los formularios?
→ ¿Los modales tienen la misma estructura?

ESTADOS DE COMPONENTES:
→ ¿Todos los botones tienen estado hover, focus, active, disabled?
→ ¿Los inputs tienen estado normal, focus, error, disabled?
→ ¿Los estados son consistentes entre componentes similares?

INCONSISTENCIAS COMUNES:
❌ Botón primario: azul en el dashboard, verde en checkout
❌ Input con border-radius: 4px en registro, 8px en perfil
❌ Card sin estado hover en una sección, con hover en otra
❌ Checkbox custom que se ve diferente en 3 formularios

Documenta:
→ Screenshots de cada variante del mismo componente
→ Qué variante mantener y cuáles eliminar
→ Lista de componentes que necesitan estados faltantes
```

### 5. Iconografía

```
Qué auditar:

CONSISTENCIA DE LIBRERÍA:
→ ¿Se usa una sola librería de iconos o hay mezcla?
→ ¿Los estilos de los iconos son coherentes? (outline vs solid)

TAMAÑOS:
→ ¿Los iconos tienen tamaños consistentes por contexto?
→ ¿Hay un grid de tamaños definido? (16/20/24/32px)

SIGNIFICADO SEMÁNTICO:
→ ¿El mismo icono significa lo mismo en todo el sistema?
→ ¿Hay iconos ambiguos que pueden confundir?

Hallazgos comunes:
❌ Mezcla de Font Awesome outline con Material Icons filled
❌ El icono de engranaje significa "configuración" en un lugar y "acciones" en otro
❌ Iconos de 18px, 20px y 22px mezclados sin sistema
```

---

## Entregables portables — inconsistencias sin imagen

```
Sin captura de pantalla, generar tablas markdown
(no depender de herramientas de visualización externas al editor;
marcar hallazgos como [NO VERIFICADO] si no hubo evidencia visual):

Comparativa de inconsistencias tipográficas (tabla):
→ Escala de texto actual encontrada vs escala propuesta consolidada

Paleta de colores auditada (tabla hex | uso | rol propuesto):
→ Los colores existentes agrupados por similitud
→ Los duplicados/redundantes marcados
→ Los roles semánticos asignados

Inventario de variantes del mismo componente (tabla):
→ Botones/inputs/cards encontrados en la auditoría
→ Los que deben consolidarse destacados

Antes/después de la corrección (tabla de 2 columnas):
→ Izquierda: el problema (spacing inconsistente, colores distintos)
→ Derecha: la corrección propuesta

Uso:
"genera el análisis de consistencia para un sistema con [descripción]"
"compara estos dos botones y encuentra inconsistencias"
"genera la propuesta de consolidación de colores para [paleta descrita]"
```
