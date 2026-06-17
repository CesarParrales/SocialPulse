# Scope Creep — Gestión de Cambios

## Entender el Scope Creep Antes de Combatirlo

```
El scope creep no siempre es de mala fe.
La mayoría de las veces el cliente genuinamente creyó que algo estaba incluido,
o genuinamente no sabía que necesitaba algo hasta que vio el producto.

Tipos de scope creep:

1. SCOPE CREEP DE DESCUBRIMIENTO (el más común)
   El cliente ve el sistema en desarrollo y descubre necesidades que no sabía
   que tenía. "Ah, y necesitamos también poder exportar esto a PDF."
   → No es mala fe — es descubrimiento natural del producto
   → Tratarlo como un aprendizaje valioso, no como un ataque
   → Documentarlo y cotizarlo como CR

2. SCOPE CREEP DE ASUNCIÓN
   El cliente asumió que algo estaba incluido porque "parecía obvio".
   "Pero si tiene login, ¿no debería tener también log de actividad?"
   → Resultado de un scope ambiguo en la propuesta
   → Revisar si el scope realmente era ambiguo — a veces el estudio tiene responsabilidad
   → Negociar caso por caso: si era razonablemente implícito, incluirlo; si no, CR

3. SCOPE CREEP DE OPORTUNIDAD
   El cliente aprovecha el momentum del proyecto para agregar cosas nuevas.
   "Ya que están trabajando en el módulo X, ¿podrían también agregar Y?"
   → Aquí la respuesta es siempre "con gusto, cotizamos el cambio"
   → No es descortés — es profesional

4. SCOPE CREEP DE PRESIÓN
   El cliente insiste en que algo estaba incluido aunque claramente no estaba.
   → Este es el caso para el que existe el contrato y el scope firmado
   → Se resuelve con documentación, no con autoridad
```

---

## El Proceso de Change Request

```
Un CR bien ejecutado no daña la relación con el cliente.
Un CR mal comunicado sí puede hacerlo.

La diferencia está en CÓMO se comunica, no en SI se cobra.

FLUJO DE UN CR:

1. El cliente solicita algo (puede ser en una reunión, por email, en Slack)
   → Documentar la solicitud inmediatamente
   → No decir "sí" ni "no" todavía — decir "lo evalúo y te doy una respuesta"

2. Evaluar internamente
   → ¿Está claramente fuera del scope? (documentado en la propuesta)
   → ¿Cuánto tiempo toma? (estimación honesta)
   → ¿Afecta el timeline del proyecto?

3. Responder con el CR formal
   → Nunca verbalmente — siempre por escrito
   → Ver el template de CR abajo

4. El cliente aprueba (o negocia)
   → Si aprueba → empieza el trabajo
   → Si negocia → ajustar hasta llegar a un acuerdo
   → Si rechaza → seguir con el scope original

5. Ejecutar y facturar
   → El costo del CR se acumula para el próximo hito o se factura por separado
```

---

## Template de Solicitud de Cambio (CR)

```
SOLICITUD DE CAMBIO #[número]
Proyecto: [nombre del proyecto]
Fecha: [fecha]
Solicitado por: [nombre del cliente]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
DESCRIPCIÓN DEL CAMBIO SOLICITADO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[Descripción clara y específica de lo que el cliente solicita]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
POR QUÉ ESTÁ FUERA DEL SCOPE ACTUAL
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
El scope actual (según la propuesta aprobada) define:
"[cita exacta de la propuesta que muestra que esto no estaba incluido]"

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
IMPACTO DEL CAMBIO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Tiempo adicional estimado: [X] horas / [X] días
Costo adicional: $[monto] + IVA/impuestos aplicables
Impacto en el timeline: [describe si retrasa la entrega o no]
Impacto en otras funcionalidades: [si lo hay]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
APROBACIÓN
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Al aprobar este Change Request, el cliente autoriza:
✓ El trabajo adicional descrito
✓ El costo adicional de $[monto]
✓ El ajuste de timeline si aplica

□ APROBADO
□ RECHAZADO
□ REQUIERE DISCUSIÓN — Por favor agendar llamada

Nombre: _______________________
Firma: ________________________
Fecha: ________________________

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
NOTA: Este CR solo es válido con aprobación escrita del cliente.
      La aprobación por email a este documento es suficiente.
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## Cómo Comunicar un CR Sin Dañar la Relación

```
El tono correcto: colaborativo, no defensivo ni agresivo.
No eres el guardia del scope. Eres el socio que ayuda a construir
exactamente lo que el cliente necesita — y a entender el costo de cada decisión.

SCRIPT PARA COMUNICAR UN CR EN LLAMADA:

"Entendemos la necesidad, tiene mucho sentido para el negocio.
Esta funcionalidad no estaba contemplada en el scope original —
[explicar brevemente por qué, sin sonar acusatorio].

Lo que hacemos es evaluarlo como un cambio y te enviamos una cotización
en [X días]. Así puedes decidir si quieres incluirlo ahora, en una
segunda fase, o si prefieres ajustar el scope quitando algo de menor
prioridad para compensar."

Por qué funciona:
→ Valida la necesidad del cliente (no la descarta)
→ No culpa al cliente por pedir algo nuevo
→ Ofrece opciones (aprobar CR / posponer / swap de scope)
→ Es un proceso, no un rechazo

SCRIPT PARA EMAIL DE CR:

"Hola [nombre],

Gracias por compartir esta necesidad — tiene sentido desde la perspectiva
del negocio.

Revisamos el scope acordado y esta funcionalidad no está incluida en
el contrato actual [referencia a la sección específica de la propuesta].

Preparamos una Solicitud de Cambio con el detalle técnico, el costo
adicional y el impacto en el timeline. La encontrarás adjunta.

Tienes preguntas, con gusto las conversamos."

Lo que NO hacer:
❌ "Eso no estaba en el contrato y no lo vamos a hacer sin pagar"
   → Agresivo, innecesario, daña la relación
❌ "Bueno, lo hacemos de todas formas porque somos un buen equipo"
   → Regalar trabajo, resentimiento silencioso, no sostenible
❌ Ignorar la solicitud y seguir
   → El cliente asume que se va a hacer
```

---

## El Swap de Scope — Herramienta de Negociación

```
Cuando el cliente quiere agregar algo pero el presupuesto es fijo:

"¿Qué podemos quitar o simplificar para hacer espacio a esto?"

El swap de scope es la alternativa al CR cuando el cliente no puede
o no quiere aumentar el presupuesto, pero la nueva necesidad es genuina.

Cómo proponerlo:

"Entiendo que el presupuesto no tiene espacio para el costo del CR.
Una opción es revisar el scope y ver si hay algo que podemos simplificar
o posponer a una segunda fase para hacer espacio a esto que necesitas.

Por ejemplo, el módulo de reportes avanzados podría simplificarse
al reporte básico de exportación CSV, lo que nos libera [X] horas
que podríamos usar para lo que me estás pidiendo."

Por qué el swap funciona:
→ El cliente tiene el control de la decisión
→ El estudio mantiene el presupuesto intacto
→ El scope total permanece igual — solo se redistribuye
→ Nadie pierde: el cliente obtiene lo nuevo, el estudio cobra lo mismo
```

---

## El Scope Creep que SÍ hay que Absorber

```
Hay cambios que técnicamente están fuera del scope pero que el estudio
debería absorber sin un CR. La regla: si el estudio tiene responsabilidad.

Cuándo absorber sin CR:

1. El bug no es un CR (aunque el cliente lo describa como cambio)
   Un bug es cuando el sistema no funciona según el criterio de aceptación.
   Si el criterio decía "el email llega en menos de 5 minutos"
   y el email llega en 30 → eso es un bug, no un CR.

2. La ambigüedad en el scope era del estudio
   Si la propuesta era ambigua y el cliente razonablemente entendió
   que algo estaba incluido, el estudio tiene responsabilidad parcial.
   Absorber o negociar un split del costo.

3. La solución técnica elegida generó una limitación no anticipada
   Si el estudio eligió una arquitectura que luego resultó en una
   limitación funcional → ajustar sin CR.

CÓMO DISTINGUIR:
Pregunta: "¿Un desarrollador razonable, leyendo la propuesta,
           habría entendido que esto estaba incluido?"

Si la respuesta es SÍ → absorber o negociar
Si la respuesta es NO → CR formal
```

---

## Registro de CRs — Llevar el Control

```
Un registro simple de todos los CRs del proyecto:

CR# | Fecha | Descripción | Estado | Costo | Aprobado por
─────────────────────────────────────────────────────────
001 | 15/01 | Exportar PDF facturas | Aprobado | $800 | Ana García
002 | 22/01 | Logo en color azul    | Rechazado | -    | -
003 | 05/02 | Dashboard en tiempo real | Pendiente | $2,200 | -

Por qué llevar el registro:
→ Al final del proyecto, tener un resumen de todos los cambios
→ Si hay disputa sobre el scope, hay documentación de todo
→ Permite entender patrones: ¿este tipo de proyectos siempre tienen CRs de X?
   → Incorporar en futuras propuestas como parte del scope o del buffer

Herramienta: una hoja de cálculo simple es suficiente.
No necesitas software especial para esto.
```
