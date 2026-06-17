# Wireframes de Baja Fidelidad

## Qué Son y Para Qué Sirven

```
Wireframe = esquema estructural de una pantalla sin diseño visual.

Lo que tiene un wireframe:
→ Disposición de los bloques de contenido (layout)
→ Jerarquía visual (qué es más grande/prominente)
→ Etiquetas de función (qué hace cada bloque)
→ Anotaciones de comportamiento (qué pasa al interactuar)
→ Estados de la pantalla (vacío, con datos, error)

Lo que NO tiene un wireframe:
→ Colores específicos (solo escala de grises)
→ Tipografía final
→ Imágenes reales (solo placeholders)
→ Iconos finales (solo representaciones)
→ Microcopy definitivo (solo labels de función)

Por qué importa la baja fidelidad:
→ Facilita el feedback sobre estructura (no se distrae con el color)
→ Es rápido de iterar (minutos, no horas)
→ Comunica que "esto no es el diseño final" → el equipo sugiere cambios
→ Permite validar con usuarios sin sesgar con el diseño visual
```

---

## Los Tres Elementos de Todo Wireframe

```
1. LAYOUT — la disposición espacial

Las preguntas del layout:
→ ¿Qué es lo más importante de esta pantalla? (ocupa más espacio o está más arriba)
→ ¿Qué tiene que hacer el usuario aquí? (la acción principal es obvia)
→ ¿Qué contenido se necesita para tomar esa acción?
→ ¿Qué navegación necesita para ir a otro lado?

Patrones de layout comunes (ver ui-web-modern y ui-admin-dashboard):
F-pattern:    lectura natural occidental, sidebar izquierdo
Z-pattern:    landing pages, el ojo hace zig-zag
Single column: mobile, formularios, lectura lineal
Two columns:  contenido + sidebar, lista + detalle
Card grid:    catálogos, galerías, dashboards

2. JERARQUÍA — qué es más importante visualmente

Recursos para establecer jerarquía (sin color):
→ Tamaño: más grande = más importante
→ Posición: arriba y a la izquierda = más visible
→ Peso: texto en negrita o bloques más gruesos
→ Espacio: más espacio alrededor = más prominente
→ Contraste: escala de grises (negro vs gris claro)

Regla: si todo es importante, nada lo es.
Un wireframe debe tener un elemento claramente más prominente que los demás.

3. ANOTACIONES — el comportamiento documentado

Qué anotar:
→ "Al tocar este botón → va a [pantalla]"
→ "Este campo es obligatorio"
→ "Este bloque aparece solo si [condición]"
→ "Aquí hay un error de validación cuando [condición]"
→ "Scroll infinito / paginación"
→ "Este componente es reutilizable del design system"
```

---

## Wireframes por Tipo de Pantalla

```
PANTALLA DE LISTADO:
┌────────────────────────────────────┐
│ [Header / Navegación]              │
├────────────────────────────────────┤
│ [Título de la sección]             │
│ [Filtros / Búsqueda]               │
├────────────────────────────────────┤
│ ┌──────────────────────────────┐   │
│ │ [Item 1]          [Acción]   │   │
│ │ Label principal              │   │
│ │ Label secundario             │   │
│ └──────────────────────────────┘   │
│ ┌──────────────────────────────┐   │
│ │ [Item 2]          [Acción]   │   │
│ └──────────────────────────────┘   │
│ [Estado vacío si no hay items]     │
│ [Paginación o infinite scroll]     │
├────────────────────────────────────┤
│ [Acción flotante / FAB]            │
└────────────────────────────────────┘
Anotaciones:
→ Item toca → pantalla de detalle
→ FAB → formulario de creación
→ Estado vacío incluye CTA de creación

PANTALLA DE DETALLE:
┌────────────────────────────────────┐
│ [← Volver]    [Título]   [Acciones]│
├────────────────────────────────────┤
│ [Hero / imagen / identificador]    │
│ [Datos principales - prominentes]  │
├────────────────────────────────────┤
│ [Sección de información 1]         │
│ Label: Valor                       │
│ Label: Valor                       │
├────────────────────────────────────┤
│ [Sección de información 2]         │
│ [Contenido relacionado / lista]    │
├────────────────────────────────────┤
│ [Acciones principales]             │
│ [Botón primario]  [Botón secundario│
└────────────────────────────────────┘

FORMULARIO:
┌────────────────────────────────────┐
│ [← Cancelar]  [Título]  [Guardar] │
├────────────────────────────────────┤
│ [Sección 1]                        │
│ Label                              │
│ [Input field]                      │
│ Helper text / error                │
│                                    │
│ Label                              │
│ [Input field]                      │
│                                    │
├────────────────────────────────────┤
│ [Sección 2]                        │
│ Label                              │
│ [Selector / dropdown]              │
│                                    │
├────────────────────────────────────┤
│ [Botón primario: acción principal] │
│ [Link: acción destructiva]         │
└────────────────────────────────────┘
Anotaciones:
→ Validación inline al perder foco (onBlur)
→ Sección 2 visible solo si [condición de sección 1]
→ Botón deshabilitado hasta campos requeridos completos
```

---

## Estados de Pantalla — No Solo el Happy Path

```
Todo wireframe debe incluir los 4 estados:

1. ESTADO VACÍO (first use / sin datos)
   ┌────────────────────────┐
   │                        │
   │    [Ilustración]       │
   │                        │
   │   "Título del vacío"   │
   │   Descripción breve    │
   │                        │
   │   [CTA: crear primero] │
   └────────────────────────┘
   → No mostrar listas vacías sin orientar al usuario
   → El CTA lleva directo a crear el primer item
   → El texto explica qué se puede hacer aquí

2. ESTADO DE CARGA (loading)
   → Skeleton screens (placeholders de la forma del contenido)
   → NO spinner genérico si hay estructura conocida
   → La skeleton debe tener la misma estructura que el contenido real
   → Anotación: "aparece durante la carga, desaparece en X ms"

3. ESTADO CON DATOS (happy path)
   → El estado principal documentado en el wireframe

4. ESTADO DE ERROR
   → Error de carga de datos: mensaje + botón de retry
   → Error de formulario: errores inline en campos
   → Error de sistema: mensaje genérico + acción alternativa
   → Error de permisos: explicación + redirección apropiada

Regla: si no están los 4 estados → el wireframe está incompleto
El desarrollador necesita saber qué mostrar en cada estado.
```

---

## Herramientas para Wireframes

```
Papel y lápiz / pizarrón:
  ✅ El más rápido para explorar
  ✅ El que más invita a cambios (se ve "boceto")
  ✅ Perfecto para workshops con el equipo
  ❌ No escalable, difícil de compartir

Figma (modo wireframe):
  ✅ Standard de la industria
  ✅ Componentes de wireframe reutilizables (Wireframe Kit)
  ✅ Colaborativo en tiempo real
  ✅ Se puede enlazar a flows
  Tip: usar una library de wireframe (Figma Community tiene muchas gratuitas)

Whimsical:
  ✅ Muy rápido para flows y wireframes simples
  ✅ Integra flows + wireframes en el mismo lugar
  ✅ Más accesible que Figma para no diseñadores

Balsamiq:
  ✅ Específicamente para wireframes de baja fidelidad
  ✅ Estética de "sketch" que comunica "esto no es final"
  ✅ Muy fácil para no diseñadores

Miro / FigJam:
  ✅ Para workshops colaborativos
  ✅ Combinar sticky notes + wireframes en un canvas
  ❌ Menos preciso que Figma para wireframes detallados

Sin herramienta (esta skill):
  Cuando no hay herramienta disponible → generar wireframes ASCII
  anotados (fidelidad 2/5) directamente en markdown
```

---

## Wireframes portables — sin herramienta (ASCII)

```
Generar wireframes ASCII anotados directamente en markdown
(no depender de herramientas de visualización externas al editor):

Pantalla de listado estándar:
  "genera el wireframe de una lista de pedidos con filtros y estados"

Formulario con validación:
  "genera el wireframe del formulario de checkout con estados de error"

Dashboard con métricas:
  "genera el wireframe de un dashboard de ventas con KPIs y tabla"

Pantalla de detalle:
  "genera el wireframe de la pantalla de detalle de producto"

El wireframe ASCII generado incluye:
→ Layout con cajas y separadores de caracteres
→ Bloques etiquetados por función
→ Anotaciones de comportamiento
→ Indicación de estados (hover, error, disabled)
→ Flujo de navegación desde/hacia la pantalla

Nivel de fidelidad: deliberadamente bajo (boceto estructural)
→ Usar como punto de partida para iterar con Figma/Whimsical
→ O usar directamente para validar estructura con usuarios
```
