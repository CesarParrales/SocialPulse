# Anatomía de una Propuesta Ganadora

## La Estructura que Funciona

```
Una propuesta no es un catálogo de features.
Es una narrativa de confianza.

El cliente pregunta (no siempre en voz alta):
1. ¿Entienden mi problema?
2. ¿Pueden resolverlo?
3. ¿Confío en este equipo?
4. ¿El precio es justo?
5. ¿Qué pasa si algo sale mal?

La propuesta responde estas 5 preguntas en ese orden.
```

---

## Secciones de una Propuesta Estándar

### Sección 1 — Resumen Ejecutivo (1 párrafo)

```
El resumen ejecutivo es lo primero que lee el decisor.
Debe responder: ¿de qué trata esto? en 5 segundos.

NO es: "Nos complace presentar nuestra propuesta para el desarrollo..."
SÍ es: "TuEmpresa necesita un sistema de gestión de pedidos que reduzca
        el tiempo de procesamiento de 4 horas a 20 minutos y elimine
        los errores manuales que generan $X en retrabajos mensuales.
        Esta propuesta cubre el diseño, desarrollo e implementación
        de esa solución en 12 semanas."

Claves:
→ El problema del cliente en una oración
→ La solución propuesta en una oración
→ El impacto esperado si es posible cuantificarlo
→ Alcance en una oración (qué cubre)
→ Plazo total
```

### Sección 2 — Entendimiento del Problema

```
Demostrar que entiendes el negocio del cliente antes de hablar de tecnología.
Esta sección es la que diferencia al estudio que hizo su tarea del que copió una plantilla.

Incluir:
→ El contexto del negocio (de dónde viene el problema)
→ El impacto actual del problema (en tiempo, dinero, satisfacción del equipo)
→ Los stakeholders afectados
→ Las restricciones conocidas (plazo, presupuesto, sistemas existentes)
→ Los objetivos de éxito del cliente (cómo saben que fue un éxito)

Ejemplo:
"Actualmente el equipo de operaciones procesa los pedidos manualmente
en tres hojas de Excel no conectadas entre sí. Esto genera:
- 4 horas de trabajo manual diario por persona (3 personas = 12h/día)
- Errores de entrada en aproximadamente el 8% de los pedidos
- Sin visibilidad del estado del pedido para el cliente final
El objetivo del sistema es eliminar el trabajo manual repetitivo
y dar visibilidad en tiempo real a clientes y operadores."

Si esto resonó con el cliente → ya ganaste la mitad.
```

### Sección 3 — Solución Propuesta

```
Aquí va la arquitectura, el stack, el enfoque.
Pero sin abrumar con tecnología — el cliente quiere saber QUÉ hará el sistema,
no necesariamente CÓMO está construido (a menos que sea técnico).

Estructura:
→ Descripción funcional de la solución (qué hace)
→ Módulos o componentes principales (mapa visual si es posible)
→ Stack tecnológico con breve justificación (por qué este stack para este proyecto)
→ Integraciones con sistemas existentes
→ Lo que NO incluye esta propuesta (exclusiones explícitas)

Exclusiones explícitas — la parte más importante:
"Esta propuesta NO incluye:
- Migración de datos históricos del sistema actual
- Capacitación de más de 5 usuarios (adicionales a cotizar)
- Soporte y mantenimiento post-lanzamiento (plan separado disponible)
- Desarrollo de la app móvil (contemplada como fase 2)"

Sin exclusiones → el cliente asume que todo está incluido.
```

### Sección 4 — Alcance Detallado

```
La sección técnica del scope.
Cada módulo o entregable con su descripción y criterios de aceptación.

Formato por módulo:

──────────────────────────────────────────────
MÓDULO: Gestión de Pedidos
──────────────────────────────────────────────
Descripción:
  Panel web para crear, editar y dar seguimiento a pedidos.
  Incluye flujo de aprobación y notificaciones por email.

Funcionalidades incluidas:
  ✓ Crear pedido con campos: cliente, producto, cantidad, fecha de entrega
  ✓ Editar pedido mientras está en estado "borrador"
  ✓ Flujo de aprobación: Operador crea → Supervisor aprueba/rechaza
  ✓ Email de notificación al cambiar de estado
  ✓ Listado con filtros por estado, fecha, cliente
  ✓ Exportar listado a CSV

NO incluido en este módulo:
  ✗ Integración con sistema de facturación (módulo separado)
  ✗ App móvil (fase 2)

Criterio de aceptación:
  Un operador puede crear un pedido, enviarlo a aprobación,
  y el supervisor puede aprobarlo en menos de 3 clicks.
  El email de notificación llega en menos de 2 minutos.
──────────────────────────────────────────────

Por qué los criterios de aceptación son críticos:
→ Definen exactamente cuándo está "listo" un entregable
→ Sin ellos: el cliente puede decir "no estaba pensando en esto así"
   indefinidamente, sin que haya un punto de cierre
→ Con ellos: cuando el criterio se cumple, el entregable está aprobado
```

### Sección 5 — Plan de Proyecto

```
Fases, hitos y timeline. No un Gantt detallado — una vista de alto nivel.

Formato recomendado:

FASE 1 — Discovery y Diseño (semanas 1-3)
  → Levantamiento de requerimientos detallados
  → Diseño UX/UI de las pantallas principales
  → Revisión y aprobación del diseño
  Entregable: diseños aprobados por el cliente

FASE 2 — Desarrollo Core (semanas 4-9)
  → Módulo de autenticación y usuarios
  → Módulo de gestión de pedidos
  → Módulo de aprobaciones y notificaciones
  Entregable: versión funcional en ambiente de staging

FASE 3 — Integraciones y Testing (semanas 10-11)
  → Integración con facturación
  → QA y corrección de bugs
  → UAT (User Acceptance Testing) con el cliente
  Entregable: sistema aprobado por el cliente

FASE 4 — Lanzamiento (semana 12)
  → Deploy a producción
  → Capacitación del equipo (hasta 5 usuarios)
  → Documentación de usuario
  Entregable: sistema en producción

Hitos de pago vinculados a las fases:
→ Hito 1 (inicio): 30-40% al firmar
→ Hito 2 (diseño aprobado): 20-25%
→ Hito 3 (staging aprobado): 20-25%
→ Hito 4 (producción): saldo final

Por qué vincular pagos a hitos:
→ El cliente tiene incentivo para dar feedback rápido
→ El estudio tiene flujo de caja a lo largo del proyecto
→ Si el proyecto se detiene, ambas partes saben exactamente
   en qué punto está y cuánto se debe
```

### Sección 6 — Inversión

```
El precio. La gente lo llama "inversión" — úsalo porque es verdad.

Formato:

INVERSIÓN TOTAL: $XX,XXX

Desglose:
  Fase 1 — Discovery y Diseño:  $X,XXX
  Fase 2 — Desarrollo Core:     $XX,XXX
  Fase 3 — Integraciones/QA:    $X,XXX
  Fase 4 — Lanzamiento:         $X,XXX
  TOTAL:                        $XX,XXX

Plan de pagos:
  Al firmar el contrato:                       $X,XXX (30%)
  Al aprobar los diseños (Hito 2):             $X,XXX (25%)
  Al aprobar staging (Hito 3):                 $X,XXX (25%)
  Al poner en producción (Hito 4):             $X,XXX (20%)

Esta propuesta tiene validez de 30 días.

Opciones adicionales (si aplica):
  Mantenimiento mensual: $XXX/mes
  Hosting y administración: $XXX/mes

Notas importantes:
→ Los precios no incluyen IVA/impuestos aplicables
→ Cualquier desarrollo fuera de este alcance requiere un Change Request aprobado
→ Los costos de licencias de terceros (APIs, servicios cloud) son adicionales
```

### Sección 7 — Por Qué Nosotros

```
Breve. No una lista de logros — una razón específica para este proyecto.

"Hemos desarrollado 3 sistemas similares de gestión operacional
para empresas del sector [X]. Entendemos los desafíos específicos
de equipos que deben migrar de Excel a software sin interrumpir
las operaciones del día a día."

Incluir:
→ Experiencia relevante para ESTE proyecto (no el portafolio completo)
→ 1-2 casos de estudio concretos si los hay
→ El equipo que va a trabajar en el proyecto (nombres y roles)
→ Cómo trabajan (metodología, comunicación, herramientas)
```

### Sección 8 — Próximos Pasos

```
Siempre terminar con una acción clara para el cliente.

"Para proceder:
1. Revisión de esta propuesta y preguntas (antes del [fecha])
2. Llamada de alineación si hay ajustes (30 min)
3. Firma del contrato y pago del anticipo
4. Kick-off del proyecto: [fecha estimada]

¿Tienes preguntas? Escríbenos a [email] o agenda una llamada aquí: [link]"

Sin próximos pasos → la propuesta muere en la bandeja del cliente.
```

---

## Auto-Referencia — Generar Propuesta

```
Sin plantilla previa, esta skill puede generar:

Propuesta completa con todas las secciones:
"genera la propuesta para [descripción del proyecto y cliente]"

Sección específica:
"genera el alcance detallado del módulo de [nombre]"
"genera los criterios de aceptación para [funcionalidad]"
"genera el resumen ejecutivo para [descripción del proyecto]"

Revisión de una propuesta existente:
"revisa esta propuesta e identifica los gaps de scope"
"qué exclusiones le faltan a esta propuesta"
```
