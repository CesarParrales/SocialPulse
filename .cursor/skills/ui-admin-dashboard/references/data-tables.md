# Data Tables y Listados

## Anatomía de una Data Table Completa

```
┌─────────────────────────────────────────────────────────────────┐
│  [Bulk action bar — aparece al seleccionar]                     │ ← Bulk bar
├─────────────────────────────────────────────────────────────────┤
│  Búsqueda: [___________]  Filtros activos: [Estado ×] [Fecha ×] │ ← Filter bar
│  248 registros                              [Columnas] [Export]  │
├──┬──────────────┬────────┬──────────┬────────┬──────────────────┤
│☐ │ Nombre ↑    │ Email  │ Estado   │ Fecha  │ Acciones         │ ← Header
├──┼──────────────┼────────┼──────────┼────────┼──────────────────┤
│☑ │ Juan Pérez  │ j@...  │ ● Activo │ 15 ene │ [Ver][Edit][...] │ ← Row selected
│☐ │ María López │ m@...  │ ○ Inact. │ 14 ene │ [Ver][Edit][...] │
│☐ │ Carlos...   │ c@...  │ ● Activo │ 13 ene │ [Ver][Edit][...] │
├──┴──────────────┴────────┴──────────┴────────┴──────────────────┤
│  ← 1  2  3  4  5 ... 24 →              Mostrando 1-20 de 248   │ ← Pagination
└─────────────────────────────────────────────────────────────────┘

Partes:
  Filter bar:  búsqueda + filtros activos como tags removibles + config de columnas
  Table header: labels de columna + ordenamiento + checkbox de selección total
  Table body:  filas de datos + checkbox individual + acciones inline
  Bulk bar:    aparece flotante cuando hay items seleccionados
  Pagination:  navegación + contador de resultados
```

---

## Columnas — Qué Mostrar y En Qué Orden

```
ORDEN RECOMENDADO DE COLUMNAS:
  1. Checkbox (si hay bulk actions)
  2. Identificador principal (nombre, número de referencia)
  3. Información descriptiva clave (2-3 columnas max)
  4. Estado (badge visual)
  5. Fechas relevantes
  6. Acciones

TIPOS DE COLUMNAS Y CÓMO FORMATEARLAS:

Texto (nombre, email, descripción):
  → Truncar con ellipsis si es largo
  → Tooltip al hover para ver el texto completo
  → Nunca wrap largo — rompe la scanabilidad

Número (precio, cantidad, ID):
  → Alineado a la derecha (alineación monetaria estándar)
  → Formatear con separadores (1,234.56)
  → Para IDs: fuente monospace

Fecha:
  → Formato relativo para recientes ("hace 2 horas")
  → Formato absoluto para antiguas ("15 ene 2024")
  → Tooltip con fecha+hora completa
  → Nunca formato ambiguo (01/02/24 → ¿enero o febrero?)

Estado:
  → Badge con color semántico
  → Siempre con icono o dot de color (no solo color)
  → Texto del estado en el badge (no solo el color)
  ● Activo (verde) / ○ Inactivo (gris) / ⚠ Pendiente (amarillo) / ✕ Error (rojo)

Boolean:
  → Checkmark / X o Toggle (no "true/false")
  → Con color semántico (verde checkmark, gris X)

Acciones (última columna):
  → Las 1-2 acciones más frecuentes como botones ghost
  → Las demás en un menú (...) de overflow
  → No más de 2 botones visibles para no saturar

Avatar/Imagen:
  → Imagen pequeña (32px) + texto a la derecha
  → Fallback con iniciales si no hay imagen
```

---

## Filtros — El Corazón del Admin

```
Sin buenos filtros, el admin es inutilizable con grandes volúmenes de datos.

TIPOS DE FILTROS:

Quick filters (tabs o botones):
  → Para estados frecuentes
  → [Todos] [Activos] [Pendientes] [Cancelados]
  → Siempre visible, no requiere abrir un panel
  → Con contador de items por estado

Filtros de columna (inline):
  → Click en el header de la columna → dropdown de filtro
  → Ideal para texto (contains/equals) y rangos de fecha

Panel de filtros avanzados:
  → Botón "Filtros" que abre un panel lateral o modal
  → Para combinaciones complejas de múltiples criterios
  → Permite guardar filtros como "vistas" nombradas

Búsqueda:
  → Busca en los campos más importantes (nombre, email, referencia)
  → Resultados en tiempo real (debounced, 300ms)
  → Indica qué campos busca ("Búsqueda en nombre y email")

FILTROS ACTIVOS COMO TAGS:
  Mostrar siempre los filtros aplicados como badges removibles:
  
  Filtros: [Estado: Activo ×] [Fecha: Ene 2024 ×] [País: MX ×]  [Limpiar todo]
  
  → El usuario sabe exactamente qué está viendo
  → Puede remover filtros individuales con ×
  → "Limpiar todo" para resetear rápido

GUARDAR FILTROS COMO VISTAS:
  Para operadores que usan los mismos filtros repetidamente:
  → "Guardar esta vista" con un nombre
  → Accesible como tabs o en un menú de vistas guardadas
  → Aumenta enormemente la productividad de usuarios avanzados
```

---

## Bulk Actions — Operar en Múltiples Registros

```
Las bulk actions permiten operar en múltiples registros a la vez.
Son esenciales en admin — sin ellas, las operaciones masivas son imposibles.

DISEÑO DE BULK ACTIONS:

Selección:
  → Checkbox en cada fila
  → Checkbox en el header selecciona todos los de la página
  → "Seleccionar todos los X resultados" para ir más allá de la página actual
  → El contador de seleccionados es visible

Barra de bulk actions:
  → Aparece al seleccionar el primer item (reemplaza o se superpone al toolbar)
  → Muestra el conteo: "3 seleccionados"
  → Acciones disponibles como botones
  → "Cancelar selección" para desmarcar todo

┌─────────────────────────────────────────────────────────────┐
│  3 seleccionados                                            │
│  [Activar] [Desactivar] [Exportar] [Eliminar]  [Cancelar]  │
└─────────────────────────────────────────────────────────────┘

Acciones destructivas en bulk:
  → SIEMPRE confirmar antes de ejecutar
  → Mostrar el número exacto de registros afectados
  → Si la operación puede ser reversible, ofrecer opción
  
  "¿Eliminar 3 usuarios? Esta acción no se puede deshacer."
  [Cancelar]                                    [Eliminar 3]
```

---

## Ordenamiento y Paginación

```
ORDENAMIENTO:
  → Click en el header de la columna → ordena asc
  → Click de nuevo → ordena desc
  → Tercer click → quita el ordenamiento
  
  Indicadores visuales:
  → Columna ordenada tiene flecha ↑ (asc) o ↓ (desc)
  → Las columnas orderables tienen un hint visual sutil al hover
  → El ordenamiento activo se persiste (o al menos durante la sesión)

PAGINACIÓN:
  Opciones por tipo de uso:

  Paginación numérica (la más común):
  ← Anterior  1  2  3  ...  24  Siguiente →
  Mostrando 21-40 de 482 registros
  Por página: [20 ▼]

  Cursor-based pagination (para grandes volúmenes con cambio frecuente):
  ← Anterior                      Siguiente →
  (No muestra número de páginas — usa cursores del API)

  Infinite scroll (para feeds, no para tablas de admin):
  → NO recomendado para admin — hace difícil "llegar al registro X"
  → El admin necesita predictibilidad en la navegación

  Items por página:
  → Opciones: 10 / 20 / 50 / 100
  → Default: 20 (balance entre info visible y performance)
  → Para operadores avanzados: 100 o más

VIRTUALIZACIÓN para grandes volúmenes:
  → Con > 500 filas en pantalla → usar virtualización (TanStack Virtual)
  → El scroll parece normal pero solo renderiza lo visible
  → Crítico para tablas con miles de filas
```

---

## Wireframes portables — data tables (ASCII + tablas markdown)

```
Generar wireframes ASCII y tablas markdown directamente
(no depender de herramientas de visualización externas al editor):

Tabla completa con todos sus componentes:
  → Filter bar + sort headers + rows + bulk bar + pagination
  → Columnas con sus tipos: texto, badge de estado, fecha, acciones

Estados de la tabla:
  → Normal / Con items seleccionados / Con filtros activos / Vacía

Comparativa de densidades:
  → Compact (32px por row) / Default (48px) / Comfortable (64px)

Filtros activos como tags:
  → Ejemplos de diferentes combinaciones de filtros
  → Con y sin la barra de bulk actions

Uso:
"genera la tabla de datos para [entidad: usuarios/pedidos/productos]"
"genera la tabla con bulk actions activadas"
"genera la comparativa de densidad de tabla"
"genera el panel de filtros avanzados para [entidad]"
```
