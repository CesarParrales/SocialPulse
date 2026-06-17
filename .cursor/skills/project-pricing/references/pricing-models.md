# Modelos de Cobro — Cómo Estructurar el Precio

## Fixed Price — El Más Común, el Más Riesgoso

```
El cliente paga un monto fijo por el scope definido.

Por qué los clientes prefieren fixed price:
→ Certeza de inversión — saben exactamente cuánto van a gastar
→ El riesgo de tiempo adicional lo asume el estudio
→ Más fácil de aprobar internamente (presupuesto cerrado)

Por qué los estudios aceptan fixed price:
→ Es lo que el cliente pide
→ Permite cobrar por valor, no solo por horas
→ Si el scope está bien definido y estimado con buffer, es rentable

El riesgo real del fixed price:
→ El scope ambiguo se convierte en trabajo no cobrado
→ Los imprevistos técnicos van a cargo del estudio
→ El scope creep erosiona el margen si no hay proceso de CRs

Cuándo fixed price funciona:
✅ Scope muy bien definido con criterios de aceptación claros
✅ Proyecto con tecnología conocida por el equipo (sin sorpresas)
✅ El estudio tiene historial de proyectos similares para estimar bien
✅ El contrato tiene un proceso de change requests claro

Cuándo fixed price es una trampa:
❌ El scope tiene partes "por definir" o "según vayan los requerimientos"
❌ Hay integraciones con sistemas legacy sin documentación
❌ El cliente "necesita ver cómo queda" para decidir el resto
❌ Primera vez que el estudio hace este tipo de proyecto

VARIANTE: Fixed price por fases
→ Cada fase es un contrato separado con scope fijo
→ Reduce el riesgo para ambas partes
→ Permite ajustar el scope de fases futuras según lo aprendido
→ El cliente puede parar en cualquier fase sin perder todo
```

---

## Tiempo y Materiales — El Más Justo, el Más Difícil de Vender

```
El cliente paga por las horas reales trabajadas.

Por qué T&M es objetivamente más justo:
→ El cliente solo paga por trabajo real
→ El estudio no asume el riesgo de imprevistos
→ El scope puede cambiar libremente sin renegociar
→ Es la forma más honesta de trabajar en proyectos inciertos

Por qué los clientes lo resisten:
→ Sin certeza de cuánto van a gastar total
→ Sensación de "cheque en blanco"
→ Requieren confiar en que el estudio es eficiente

Cómo vender T&M correctamente:
→ Dar un rango realista de costo total (no solo "son $X/hora")
  "Estimamos entre 150 y 200 horas para el proyecto,
   lo que representa una inversión de $15,000 a $20,000."
→ Dar reportes semanales de horas consumidas vs presupuesto
→ Alertar cuando se llega al 75% del estimado
→ El cliente puede detener o ajustar el scope en cualquier momento

Variantes de T&M:
  T&M con cap (techo):
  → "T&M hasta un máximo de $20,000"
  → El riesgo superior al cap lo asume el estudio
  → Da certeza al cliente sin perder flexibilidad
  → Recomendado: los clientes lo aceptan mucho más fácil

  T&M con estimado por fase:
  → Cada sprint o fase tiene un estimado aprobado
  → El cliente aprueba antes de cada fase
  → Mayor control para ambas partes
```

---

## Retainer Mensual — El Modelo Más Predecible

```
El cliente paga una tarifa mensual fija por capacidad reservada.

Por qué el retainer es el mejor modelo para el estudio:
→ Ingresos predecibles → mejor planning de recursos
→ Relación de largo plazo → el equipo conoce el producto profundamente
→ El cliente puede cambiar prioridades cada mes sin renegociar
→ Menor costo de ventas (no hay que conseguir clientes nuevos constantemente)

Por qué el retainer funciona para el cliente:
→ Tiene capacidad de desarrollo disponible sin contratar en planilla
→ El equipo conoce el producto → menor tiempo en contexto
→ Puede ajustar el volumen de trabajo mes a mes (dentro de límites)

Estructura de un retainer:

RETAINER DE CAPACIDAD FIJA:
  → "20 horas mensuales de desarrollo"
  → Las horas no usadas se pierden (no se acumulan)
  → El estudio puede planificar su equipo con certeza
  → Precio: tarifa × horas con descuento por volumen comprometido

RETAINER CON BANCO DE HORAS:
  → "Mínimo 20 horas/mes, máximo 40 horas/mes"
  → Las horas extra al mínimo se cobran al mismo precio
  → Más flexible pero menos predecible para el estudio

RETAINER DE SOPORTE + EVOLUCIÓN:
  → Define un SLA de tiempo de respuesta para bugs/incidentes
  → Define capacidad disponible para features nuevas
  → Precio: SLA básico + X horas de desarrollo mensual

Cómo estructurar el precio del retainer:
  → La tarifa mensual del retainer tiene un descuento vs el proyecto puntual
    (normalmente 10-15% menor, por la previsibilidad que le da al estudio)
  → El contrato inicial: mínimo 3 meses (para que sea rentable para el estudio)
  → Renovación: mensual o trimestral con preaviso de 30 días para terminar

Señal de retainer saludable:
  El cliente usa entre el 70% y 100% de las horas comprometidas.
  Si usa menos del 50% consistentemente → el cliente probablemente va a cancelar.
  Si usa 100% y pide más → es momento de hablar de aumentar el retainer.
```

---

## Combinaciones de Modelos Reales

```
STARTUP EN ETAPA TEMPRANA:
  Fase 1 (Discovery):   Fixed price pequeño ($1,000-3,000)
  Fase 2 (MVP):         Fixed price con scope bien definido
  Post-lanzamiento:     Retainer mensual para evolución

EMPRESA CON PROYECTO ESPECÍFICO:
  Si el scope está claro:    Fixed price total
  Si hay incertidumbre:      Fixed price por fases
  Post-lanzamiento:          Retainer o proyecto adicional

EMPRESA CON NECESIDADES CONTINUAS:
  Inicio:              Proyecto fixed price para lo urgente
  Continuo:            Retainer mensual para evolución y soporte

PROYECTO DE LICITACIÓN CORPORATIVA:
  Siempre:             Fixed price (exigido)
  Protección:          Cláusulas de CR muy claras en el contrato
  Extra:               Presupuesto de contingencia explícito en la propuesta

PRODUCTO PROPIO DEL ESTUDIO (si aplica):
  No aplica modelo de cobro, aplica modelo de negocio (SaaS, licencias, etc.)
```
