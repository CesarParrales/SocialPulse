# Contratos — Cláusulas Esenciales

## Principio de Diseño del Contrato

```
Un contrato no es un arma para usar contra el cliente.
Es un mapa de cómo se resuelven los problemas cuando aparecen.

Un buen contrato:
→ Hace explícito lo que ambas partes asumen implícitamente
→ Define el proceso cuando hay desacuerdo (no quién tiene razón)
→ Protege a ambas partes, no solo al estudio
→ Es suficientemente simple para que el cliente lo lea de verdad

Un contrato que el cliente no lee no protege a nadie.
Si tu contrato tiene 40 páginas de letra pequeña → nadie lo lee.
Un contrato de 5-8 páginas claras es más efectivo que uno de 40.
```

---

## Las 10 Cláusulas que No Pueden Faltar

### 1. Definición del Alcance (por referencia)

```
"El alcance del proyecto es el descrito en la Propuesta Técnica y Comercial
anexa a este contrato (Anexo A), la cual forma parte integral del presente
acuerdo. Cualquier trabajo fuera de dicho alcance requiere una Solicitud de
Cambio aprobada por escrito por ambas partes."

Por qué es importante:
→ El contrato y la propuesta son un solo documento legal
→ "Aprobada por escrito" previene que los cambios verbales sean vinculantes
→ Esta cláusula ES la protección contra scope creep
```

### 2. Proceso de Control de Cambios

```
"Cualquier solicitud de cambio al alcance, funcionalidades, diseño o
especificaciones técnicas será procesada mediante el siguiente proceso:

a) El cliente solicita el cambio por escrito (email es suficiente)
b) El estudio evalúa el impacto en tiempo y costo (máx. 3 días hábiles)
c) El estudio envía una Solicitud de Cambio formal con costo y tiempo adicional
d) El cambio se implementa SOLO después de la aprobación escrita del cliente
e) El costo adicional se añade a la factura del siguiente hito de pago

Los cambios no aprobados por escrito no generan obligación de pago
pero tampoco obligación de implementación."

Sin esta cláusula → el estudio implementa cambios y luego no puede cobrarlos.
Con esta cláusula → cada cambio tiene un número de CR y un monto aprobado.
```

### 3. Responsabilidades del Cliente

```
"Para el correcto desarrollo del proyecto, el Cliente se compromete a:

a) Designar un punto de contacto con autoridad para tomar decisiones
b) Responder solicitudes de información en un plazo de [5] días hábiles
c) Revisar y aprobar (o rechazar con comentarios específicos) cada entregable
   en un plazo de [5] días hábiles desde su presentación
d) Proveer acceso a sistemas, cuentas y recursos necesarios según se soliciten
e) Proveer el contenido (textos, imágenes, datos) necesario según el calendario

El incumplimiento de estos plazos puede resultar en ajuste del calendario
del proyecto sin costo adicional para el estudio."

Por qué es crítica:
→ Define que el proyecto no depende solo del estudio
→ Protege al estudio cuando el cliente es el cuello de botella
→ Permite ajustar el timeline cuando el cliente desaparece semanas
```

### 4. Condiciones de Pago y Consecuencias

```
"El esquema de pagos es el siguiente:
  Anticipo al firmar: XX% del total
  Hito 2 [descripción]: XX%
  Hito 3 [descripción]: XX%
  Saldo final [entrega]: XX%

Condiciones:
a) El trabajo comienza solo después de recibido el anticipo
b) Las facturas tienen vencimiento de [10] días hábiles
c) Pagos con más de [10] días de atraso generan un cargo por mora de [X]% mensual
d) El estudio puede suspender el trabajo si hay pagos atrasados por más de
   [15] días, sin que esto constituya incumplimiento del contrato por parte del estudio
e) El trabajo suspendido por falta de pago puede resultar en ajuste del calendario

Por qué la suspensión es importante:
→ Sin ella, el estudio sigue trabajando y aumenta la deuda del cliente
→ Con ella, hay consecuencias concretas y proporcionales al retraso
```

### 5. Propiedad Intelectual

```
"Una vez recibido el pago total del proyecto, el estudio transfiere
al cliente todos los derechos de propiedad intelectual sobre el código
fuente y los diseños desarrollados específicamente para este proyecto.

El estudio retiene el derecho de:
a) Usar el proyecto como caso de estudio en su portafolio (sin revelar
   información confidencial del negocio del cliente)
b) Reutilizar patrones, componentes genéricos y arquitecturas desarrolladas
   (el código genérico no es IP del cliente, solo el código específico del negocio)

Mientras no se reciba el pago total, el estudio retiene todos los derechos
sobre el trabajo entregado."

Por qué el punto b) importa:
→ Un estudio que no puede reutilizar su propio código genérico
   no puede ser eficiente con ningún otro cliente
→ Lo que el cliente paga es la solución a su problema, no el framework

Por qué la transferencia post-pago importa:
→ El cliente no tiene IP si no ha pagado
→ Es un incentivo natural para pagar el saldo final
```

### 6. Confidencialidad

```
"Ambas partes se comprometen a mantener confidencial toda información
marcada como confidencial o que por su naturaleza deba considerarse como tal.

El estudio tratará como confidencial:
→ Los datos del negocio del cliente
→ Sus procesos, precios, clientes y estrategias que conozca en el proyecto
→ Información técnica sobre sistemas existentes del cliente

El cliente tratará como confidencial:
→ Metodologías, herramientas y procesos del estudio
→ Plantillas, frameworks y arquitecturas propias del estudio
→ El precio de los servicios (salvo que el cliente requiera divulgarlo
   para procesos de licitación)

Esta obligación de confidencialidad dura [2] años después del término del contrato."
```

### 7. Garantía Post-Lanzamiento

```
"El estudio garantiza que el software entregado funciona según los criterios
de aceptación acordados por un período de [60] días después del lanzamiento.

Durante este período, el estudio corregirá sin costo adicional:
→ Bugs: comportamientos que difieren de los criterios de aceptación
→ Errores de implementación respecto al diseño aprobado

NO está cubierto por la garantía:
→ Nuevas funcionalidades o cambios de comportamiento
→ Problemas causados por cambios en sistemas de terceros
→ Problemas causados por modificaciones realizadas por el cliente
→ Problemas de performance causados por volúmenes de datos no contemplados

Después de los [60] días, el trabajo de corrección se cotiza a tarifa de
mantenimiento o se incluye en un plan de soporte mensual."

Por qué 60 días y no "para siempre":
→ La garantía sin límite de tiempo convierte cada feature en un bug potencial
→ 60 días es suficiente para descubrir bugs reales de producción
→ Si hay más problemas después de 60 días → plan de mantenimiento
```

### 8. Terminación del Contrato

```
"Cualquiera de las partes puede terminar este contrato con [15] días de
aviso escrito si la otra parte incumple de forma material sus obligaciones
y no lo subsana en [10] días después de recibir notificación del incumplimiento.

En caso de terminación:
a) El estudio entregará todo el trabajo completado hasta la fecha de terminación
b) El cliente pagará por el trabajo completado hasta la fecha de terminación,
   calculado proporcionalmente
c) Si la terminación es por incumplimiento del cliente: el cliente paga también
   los costos directos incurridos por la terminación anticipada
d) Si la terminación es por incumplimiento del estudio: el estudio devuelve
   la parte proporcional del anticipo correspondiente al trabajo no entregado

[Cláusula opcional pero recomendada:]
En caso de que el cliente pause el proyecto por más de [60] días por razones
internas, el estudio puede aplicar un cargo de reactivación del [X]% para
cubrir los costos de retomar el contexto del proyecto."

La cláusula de pausa es crítica:
→ Los proyectos que pausan indefinidamente son un pasivo para el estudio
→ El equipo se dispersa, el contexto se pierde, retomar cuesta dinero real
```

### 9. Limitación de Responsabilidad

```
"La responsabilidad máxima del estudio por cualquier causa relacionada con
este contrato se limita al monto total pagado por el cliente para este proyecto.

El estudio no será responsable por:
→ Pérdidas de negocio o lucro cesante del cliente
→ Daños indirectos, consecuentes o punitivos
→ Problemas causados por servicios de terceros (cloud, APIs, etc.)
→ Problemas causados por modificaciones realizadas por el cliente
   al código entregado

[NOTA: Esta cláusula debe ser revisada por un abogado local —
 la aplicabilidad varía por jurisdicción]"
```

### 10. Resolución de Disputas

```
"Ante cualquier disputa relacionada con este contrato, las partes se
comprometen a:

a) Primero: intentar resolución directa entre los representantes de
   ambas partes en un plazo de [15] días hábiles
b) Si no hay acuerdo: mediación por un mediador neutral acordado por ambas partes
c) Si la mediación falla: las leyes y tribunales de [jurisdicción] son aplicables

La mediación es menos costosa y más rápida que el litigio.
Ambas partes prefieren resolver los problemas antes de llegar a esa instancia."
```

---

## Sobre los Contratos y los Abogados

```
Este documento es una guía práctica, no asesoría legal.

Lo que puedes hacer con estas cláusulas:
→ Redactar un contrato básico funcional que la mayoría de clientes firmará
→ Entender qué puntos son críticos y cuáles son secundarios
→ Tener conversaciones informadas con un abogado

Lo que deberías hacer con un abogado:
→ Revisar el contrato final antes de usarlo con clientes grandes
→ Verificar la aplicabilidad de las cláusulas en tu jurisdicción
→ Adaptar el lenguaje para cumplir con la legislación local de contratos y
   protección al consumidor

Inversión estimada en abogado para revisar un contrato base: $300-800
Costo de un proyecto que se fue a litigio sin contrato claro: 10-100x más

La inversión en asesoría legal es una de las mejores que hace un estudio.
```
