# KPIs, Métricas y Charts

## KPI Cards — El Primer Vistazo del Dashboard

```
Las KPI cards responden la primera pregunta del operador:
"¿Cómo estamos hoy?"

ANATOMÍA DE UNA KPI CARD:
┌─────────────────────────────────┐
│  Icono    Ventas del mes        │  ← Label descriptivo
│                                 │
│           $48,295               │  ← Valor principal (grande)
│                                 │
│  ↑ 12.5% vs mes anterior        │  ← Comparación/tendencia
│  ▁▂▄▅▆▇  (sparkline opcional)  │  ← Minigráfico (opcional)
└─────────────────────────────────┘

Componentes:
  Label:      qué métrica es (pequeño, secondary text)
  Valor:      el número grande (32-40px, semibold/bold)
  Tendencia:  comparación con período anterior (verde si sube, rojo si baja)
  Sparkline:  tendencia visual de los últimos 7-30 días (opcional)
  CTA:        link a la vista detallada (opcional, solo si es útil)

CUÁNTAS KPI CARDS:
  3 cards:  layout de 3 columnas, las 3 métricas más importantes
  4 cards:  el más común, cubre los 4 aspectos clave del negocio
  5-6 cards: para dashboards complejos, en 2 filas
  Más de 6: considerar agrupar o usar una sección de métricas secundarias

LAS 4 MÉTRICAS TÍPICAS (e-commerce):
  1. Ingresos del período
  2. Número de pedidos
  3. Ticket promedio
  4. Nuevos clientes

LAS 4 MÉTRICAS TÍPICAS (SaaS):
  1. MRR (Monthly Recurring Revenue)
  2. Usuarios activos
  3. Churn rate
  4. NPS o CSAT
```

---

## Tipos de Charts y Cuándo Usar Cada Uno

```
LÍNEA (Line Chart):
  → Para tendencias en el tiempo (la métrica más usada en admin)
  → Responde: "¿Cómo ha cambiado X a lo largo del tiempo?"
  → Ideal: ingresos diarios, usuarios activos, traffic
  → Con múltiples líneas: comparar períodos o segmentos
  → NO usar para más de 4-5 líneas (ilegible)

BARRA (Bar Chart):
  → Para comparar categorías entre sí
  → Responde: "¿Cuál categoría/segmento tiene más de X?"
  → Ideal: ventas por categoría, top productos, conversión por canal
  → Horizontal: cuando los labels son largos
  → Vertical: cuando son fechas o períodos

BARRA APILADA (Stacked Bar):
  → Para composición de una métrica en el tiempo
  → Responde: "¿Qué parte del total corresponde a cada segmento?"
  → Ideal: ventas por categoría de producto por mes
  → NO usar con más de 4-5 segmentos

ÁREA (Area Chart):
  → Igual que línea pero con área rellena
  → Enfatiza el volumen más que la tendencia
  → Útil para mostrar magnitud (ingresos totales)
  → Área apilada: composición a lo largo del tiempo

DONA/PIE (Donut/Pie Chart):
  → Para mostrar composición de un total (partes del todo)
  → Responde: "¿Qué porcentaje del total es X?"
  → SOLO si hay menos de 5-6 segmentos
  → Preferir donut sobre pie (más legible en pantallas)
  → NO usar para comparar cambios en el tiempo (usar barras)

SCATTER/BUBBLE:
  → Para correlaciones entre dos variables
  → Responde: "¿Hay relación entre X e Y?"
  → Raro en admin típico, útil en analytics avanzados

TABLA CON SPARKLINES:
  → Para ranking + tendencia simultáneos
  → Responde: "¿Cuál es el top X y cómo evoluciona cada uno?"
  → Ideal: top productos, top clientes, métricas por canal
```

---

## Diseño de Charts — Principios

```
MENOS ES MÁS:
  → Remover el chart junk (gridlines innecesarias, borders decorativos)
  → Gridlines horizontales sutiles son útiles, las verticales rara vez
  → Sin 3D — los gráficos 3D distorsionan los valores
  → Sin sombras en barras — hacen los valores ambiguos

COLOR EN CHARTS:
  → Una paleta de color consistente para todos los charts del admin
  → Colores semánticos donde aplique (verde = positivo, rojo = negativo)
  → Para múltiples series: paleta distinguible (considerar daltonismo)
  → Highlight: un color prominente + el resto en gris para enfocar la atención

TIEMPO Y ESCALA:
  → El eje Y debe empezar en 0 para barras (nunca truncar)
  → Para líneas: el eje Y puede no empezar en 0 si muestra tendencia
  → Labels del eje X: no demasiados (cada 7 días, no cada día)
  → Selector de período: [7d] [30d] [90d] [12m] [Custom]

TOOLTIPS:
  → Al hover sobre un punto: mostrar el valor exacto
  → Tooltip bien diseñado: fecha + valor + comparación (% vs período anterior)
  → El cursor debe atrapar fácilmente los puntos del gráfico

RESPONSIVE DE CHARTS:
  → Los charts deben adaptarse al ancho del contenedor
  → En mobile: simplificar (menos datapoints, labels más pequeños)
  → Algunos charts son mejor reemplazados por texto en mobile:
    KPI: el número grande en mobile > el chart en mobile
```

---

## Layout del Dashboard Principal

```
ESTRUCTURA RECOMENDADA:

1. Selector de período (topbar del dashboard):
   Vista: [Hoy] [Ayer] [Últimos 7d] [Este mes] [Custom]

2. KPI Cards (primera fila):
   4 métricas clave en una fila horizontal

3. Chart principal (ancho completo o 2/3 del ancho):
   La métrica más importante como chart de línea
   Selección de métrica a mostrar (dropdown)

4. Distribución secundaria (al lado del chart principal o abajo):
   Donut de distribución por categoría / top items

5. Tablas de detalle (sección final):
   Top productos, últimos pedidos, etc.
   Con link "Ver todos" hacia el módulo completo

EJEMPLO: Dashboard de e-commerce
┌─────────────────────────────────────────────────────────────┐
│ [Período: Este mes ▼]                                       │
├──────────┬──────────┬──────────┬──────────────────────────┤
│ Ingresos │ Pedidos  │ Ticket   │ Nuevos Clientes           │
│ $48,295  │   342    │ $141.2   │     89                    │
│ ↑ 12.5%  │  ↑ 8.3%  │ ↑ 3.9%   │     ↑ 22.1%              │
├──────────┴──────────┴──────────┴──────────────────────────┤
│ Ingresos diarios                          [Métrica ▼]      │
│ [Chart de línea ancho completo]                            │
├────────────────────────────────┬───────────────────────────┤
│ Ventas por categoría           │ Top productos             │
│ [Donut chart]                  │ [Mini tabla con ranking]  │
├────────────────────────────────┴───────────────────────────┤
│ Últimos pedidos                               [Ver todos →]│
│ [Tabla simplificada con 5-10 filas]                        │
└─────────────────────────────────────────────────────────────┘
```

---

## Librerías de Charts Recomendadas

```
REACT:
  Recharts            → La más popular para React, basada en SVG
                        Fácil de customizar, documentación clara
                        Ideal para: la mayoría de dashboards
  
  Victory             → Basada en D3, más flexible pero más compleja
                        Ideal para: charts muy customizados
  
  Tremor              → Componentes de dashboard pre-estilizados
                        Incluye KPI cards, sparklines, tablas
                        Ideal para: prototipado rápido de dashboards

  shadcn/ui + Recharts → La combinación más usada en 2024
                         Recharts con el estilo de shadcn

VANILLA JS / OTROS FRAMEWORKS:
  Chart.js            → Popular, soporta múltiples frameworks
  ApexCharts          → Muchos tipos de chart, muy customizable
  ECharts (Apache)    → Muy potente para grandes volúmenes de datos

PARA PHP / FILAMENT:
  Filament Charts     → Integrado con Filament, usa Chart.js
  Livewire + Alpine   → Para charts interactivos en Laravel/Livewire

PARA DATOS EN TIEMPO REAL:
  Highcharts          → De pago, pero excelente para real-time
  uPlot               → Ultra-ligero, ideal para series temporales densas
  TradingView Charts  → Para datos financieros/stock-like
```

---

## Entregables portables — métricas y charts

```
Generar especificaciones en markdown (wireframes ASCII para layout,
tablas para datos; no depender de herramientas externas al editor):

Dashboard completo:
  → KPI cards + chart principal + distribución + tabla resumen
  → Con proporciones y espaciado correcto

KPI Cards variantes:
  → 4 cards con diferentes métricas
  → Variantes: con sparkline / sin sparkline / con color de tendencia

Chart de línea:
  → Ingresos por período con eje correctamente etiquetado
  → Con tooltip diseñado
  → Con selector de período

Donut chart:
  → Distribución por categoría
  → Con leyenda a la derecha y valores en el centro

Comparativa de tipos de chart:
  → Los mismos datos representados en: línea, barras, área
  → Para decidir cuál usar en qué contexto

Uso:
"genera el dashboard de KPIs para [e-commerce/SaaS/operaciones]"
"genera el chart de [ventas/usuarios/conversión] con el layout correcto"
"genera las KPI cards para [tipo de negocio]"
```
