# Client Behavior Patterns

Catálogo de comportamientos del cliente que impactan proyectos de software.
Detectar temprano. Documentar. Mitigar antes de firmar contrato.

---

## Patrones de Scope Creep

### SC-01: El "Y También"
**Señal:** En cada reunión aparecen funcionalidades nuevas presentadas como obvias.
> "Y también podría tener notificaciones push... y un chat... y reportes en PDF..."

**Impacto:** +30-60% tiempo no estimado. Equipo desbordado. Fechas rotas.
**Mitigación:**
- Change request formal con estimación de impacto para cada adición
- Backlog público y priorizado visible para el cliente
- Contrato con cláusula de scope change explícita

### SC-02: El "Era Obvio"
**Señal:** El cliente asume que algo está incluido aunque nunca se discutió.
> "Pero claro que necesita login con Google, eso es básico"

**Impacto:** Trabajo no estimado. Conflicto en facturación.
**Mitigación:**
- Documento de alcance con lista explícita de lo que NO está incluido
- Firma de aceptación del alcance antes de iniciar

### SC-03: El "Pequeño Cambio"
**Señal:** Solicitudes presentadas como triviales que implican refactoring profundo.
> "Solo cambia el flujo de checkout, es un pequeño ajuste"

**Impacto:** Variable — desde horas hasta semanas dependiendo de la arquitectura.
**Mitigación:**
- Análisis de impacto escrito antes de comprometerse a tiempos
- Nunca dar estimaciones verbales en reuniones sin análisis previo

### SC-04: El Scope Fantasma
**Señal:** El cliente tiene una visión del producto más grande que nunca verbalizó.
Descubierta cuando el MVP entregado "no es lo que esperaban".

**Impacto:** Retrabajos masivos. Pérdida de confianza.
**Mitigación:**
- Workshop de product vision antes de diseñar
- Prototipo navegable aprobado antes de desarrollo
- Revisiones de sprint con demos, no solo reportes

---

## Patrones de Toma de Decisiones

### TD-01: El Cliente Comité
**Señal:** Las decisiones requieren aprobación de múltiples personas sin jerarquía clara.
Cada reunión termina con "hay que consultarlo con..."

**Impacto:** Ciclos de aprobación de 2-3x lo normal. Bloqueos constantes.
**Mitigación:**
- Definir un único DRI (Directly Responsible Individual) desde el inicio
- Escalar y documentar si no hay DRI disponible
- SLA de respuesta a decisiones en contrato (ej: 48h hábiles)

### TD-02: El Decisor Ausente
**Señal:** La persona que realmente decide no asiste a las reuniones.
Las personas que sí asisten no pueden aprobar nada.

**Impacto:** Reuniones sin output. Cambios de dirección sorpresa post-aprobación.
**Mitigación:**
- Reunión de kickoff con el decisor real obligatoria
- Decisiones enviadas por escrito con deadline de aprobación
- Escalar bloqueos formalmente con impacto documentado

### TD-03: El Cambio de Opinión Crónico
**Señal:** Lo aprobado en semana 1 se cuestiona en semana 4. Patrón repetitivo.

**Impacto:** Retrabajos costosos. Equipo desmotivado. Fechas imposibles.
**Mitigación:**
- Documentación escrita de cada aprobación con fecha y nombre
- Costo explícito de cada cambio post-aprobación
- En casos extremos: pausar proyecto y redefinir proceso de trabajo

### TD-04: El Decisor por Ego
**Señal:** Decisiones técnicas tomadas por jerarquía, no por datos.
> "Usamos Oracle porque siempre hemos usado Oracle"
> "La app tiene que ser nativa porque la competencia tiene nativa"

**Impacto:** Stack subóptimo. Costos inflados. Limitaciones técnicas innecesarias.
**Mitigación:**
- Presentar opciones con matriz de decisión objetiva
- Documentar la decisión y sus consecuencias técnicas formalmente
- Si la decisión es técnicamente dañina: dejar constancia escrita de la objeción

---

## Patrones de Presupuesto

### PR-01: El Presupuesto Oculto
**Señal:** El cliente no revela el presupuesto. Pide propuesta primero.
A veces estrategia de negociación, a veces genuinamente no lo saben.

**Impacto:** Propuestas mal calibradas. Tiempo perdido en iteraciones.
**Mitigación:**
- Preguntar directamente con contexto: "Para calibrar la propuesta al nivel de producto que buscas, ¿en qué rango estamos trabajando?"
- Ofrecer 3 opciones de alcance en rangos de precio si no revelan número

### PR-02: El Presupuesto de Startup con Visión de Unicornio
**Señal:** Presupuesto de $10K para un producto que requiere $80K.
El cliente cree que "la tecnología es barata ahora" o "con IA se hace solo".

**Impacto:** Expectativas rotas. Producto incompleto o de baja calidad.
**Mitigación:**
- Presentar el costo real desglosado sin suavizarlo
- Ofrecer MVP real (no recortado artificialmente) dentro del presupuesto
- Dejar registro escrito de qué no se puede hacer con ese presupuesto

### PR-03: El Presupuesto que Desaparece
**Señal:** Aprobado en fase inicial. En fases siguientes "hay restricciones".
Común en proyectos corporativos con presupuesto anual o por ciclos.

**Impacto:** Proyecto truncado. Entregable incompleto sin valor de negocio.
**Mitigación:**
- Contrato por fases con compromiso de presupuesto por fase
- MVP en fase 1 que tenga valor standalone
- Identificar y proteger el presupuesto de la siguiente fase antes de cerrar la actual

### PR-04: El Pago por Entregable Sin Definición
**Señal:** El cliente quiere pagar contra entrega de hitos pero no define qué es "done".

**Impacto:** Disputas en cada pago. Trabajo extra para satisfacer criterios móviles.
**Mitigación:**
- Definición de Hecho por hito antes de firmar
- Criterios de aceptación concretos y verificables
- Proceso formal de sign-off por escrito

---

## Patrones de Timeline

### TL-01: La Fecha Mágica
**Señal:** Fecha límite con carga emocional o arbitraria.
> "Tiene que estar listo para el lanzamiento en la feria de noviembre"
> "El CEO lo prometió en el board"

**Impacto:** Presión constante. Decisiones técnicas comprometidas. Deuda técnica acumulada.
**Mitigación:**
- Evaluar qué es alcanzable en la fecha con scope reducido
- Documentar qué se sacrifica para cumplir la fecha
- Proponer alternativa: lanzamiento soft con funcionalidad core en fecha, expansión posterior

### TL-02: El "Para Ayer"
**Señal:** El proyecto "debería haber empezado hace 3 meses". Urgencia desde día 1.

**Impacto:** Discovery insuficiente. Arquitectura apresurada. Bugs costosos después.
**Mitigación:**
- Negarse a saltarse fases críticas aunque haya presión temporal
- Cuantificar el costo de hacerlo mal y rehacer vs. hacerlo bien desde el inicio
- Discovery mínimo de 1-2 semanas es innegociable

### TL-03: El Timeline de Precisión Falsa
**Señal:** El cliente presenta un Gantt detallado que incluye el trabajo del equipo técnico
antes de que el equipo técnico haya estimado.

**Impacto:** Compromisos imposibles. Conflicto garantizado.
**Mitigación:**
- El equipo técnico estima. El cliente no estima por el equipo.
- Presentar estimación propia basada en análisis real
- Si hay conflicto: negociar scope, no comprimir tiempo de forma arbitraria

---

## Patrones de Comunicación

### CM-01: El Cliente Silencioso
**Señal:** Tarda días/semanas en responder. Desaparece entre reuniones.
Bloquea progreso sin reconocerlo como bloqueo.

**Impacto:** Dependencias no resueltas. Equipo esperando. Fechas que se extienden.
**Mitigación:**
- SLA de respuesta en contrato
- Comunicar impacto formal por escrito de cada bloqueo con fecha
- Escalar si el silencio supera el SLA

### CM-02: El Micromanager Técnico
**Señal:** El cliente sin background técnico opina sobre implementación específica.
> "¿Por qué usaron un array ahí? Yo hubiera usado un objeto"

**Impacto:** Decisiones técnicas comprometidas. Equipo desmotivado. Fricción constante.
**Mitigación:**
- Definir roles y responsabilidades técnicas en el contrato
- El cliente aprueba comportamiento y UX. No arquitectura interna.
- Demos de funcionalidad, no code reviews con el cliente

### CM-03: El Teléfono Roto
**Señal:** El interlocutor del proyecto no es el usuario final ni el decisor.
La información pasa por 2-3 personas antes de llegar al equipo técnico.

**Impacto:** Requerimientos distorsionados. Feedback que no refleja necesidad real.
**Mitigación:**
- Acceso directo a usuarios finales para testing y validación
- Workshops con el decisor real al menos en discovery
- Documentación escrita que viaja con el requerimiento, no solo verbal

### CM-04: El Cambio Verbal
**Señal:** El cliente da instrucciones de cambio en conversaciones informales
(WhatsApp, pasillo, llamada no grabada) sin seguimiento escrito.

**Impacto:** Cambios implementados sin control de versiones. Conflictos después.
**Mitigación:**
- Todo cambio debe confirmarse por escrito antes de implementar
- Canal oficial de comunicación definido desde el inicio
- Confirmar verbales por email: "Como acordamos en la llamada de hoy..."

---

## Patrones de Validación de Producto

### VP-01: El Cliente Como Usuario Único
**Señal:** El cliente asume que sus preferencias representan las del usuario final.
> "A mí no me gusta ese color, cámbialo" (sin data de usuarios)

**Impacto:** Decisiones de UX basadas en opinión, no en comportamiento real.
**Mitigación:**
- Separar explícitamente preferencia personal del cliente de datos de usuario
- Proponer user testing antes de decisiones cosméticas grandes
- Presentar decisiones de diseño con justificación de UX, no solo estética

### VP-02: El Rechazo del MVP
**Señal:** El cliente acepta conceptualmente el MVP pero al verlo pide el producto completo.
> "Esto se ve muy básico para mostrarle a los usuarios"

**Impacto:** MVP nunca lanza. No hay datos reales. Proyecto se extiende indefinidamente.
**Mitigación:**
- Alinear expectativas del MVP antes de construirlo (prototipo visual aprobado)
- Explicar el valor del MVP no está en el polish sino en el aprendizaje
- Usuarios beta seleccionados que toleren y valoren el producto en progreso

### VP-03: El Pivot Súbito
**Señal:** Cambio radical de dirección del producto mid-project.
Usualmente gatillado por: competidor lanzó algo, CEO tuvo una idea, board lo pidió.

**Impacto:** Trabajo previo potencialmente descartado. Reestimación completa necesaria.
**Mitigación:**
- Evaluar impacto técnico completo antes de comprometerse con el pivot
- Documentar qué es reutilizable y qué se descarta
- Tratar el pivot como un proyecto nuevo: nuevo discovery, nueva estimación, nuevo contrato si aplica

---

## Señales de Alerta Máxima

Estas señales combinadas indican proyecto de alto riesgo. Considerar renegociar términos
o declinar si no se pueden mitigar:

🔴 Presupuesto irreal + timeline imposible + decisor ausente
🔴 Scope no definido + "empecemos ya" + cambios verbales frecuentes
🔴 Múltiples stakeholders con agendas distintas sin DRI claro
🔴 "Ya tuvimos otro proveedor que no funcionó" sin análisis de por qué
🔴 Resistencia a firmar documento de alcance antes de iniciar
