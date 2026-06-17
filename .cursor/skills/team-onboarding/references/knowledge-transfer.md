# Transferencia de Conocimiento

## El Conocimiento que Vive en la Cabeza del Lead

```
Todo estudio tiene un dev con conocimiento crítico no documentado:
→ Por qué se tomó cierta decisión técnica
→ Qué intentaron antes que no funcionó
→ La integración que parece simple pero tiene 3 edge cases
→ El cliente que siempre pide X de una manera no obvia
→ El workaround que existe porque el sistema externo hace algo raro

Este conocimiento es invisible hasta que:
a) El lead no está disponible y el proyecto se bloquea
b) Entra alguien nuevo y no puede avanzar sin preguntar constantemente
c) El lead se va del estudio y se lleva el conocimiento con él

La transferencia de conocimiento no es documentar todo.
Es documentar lo que no se puede inferir del código.
```

---

## Los 3 Tipos de Conocimiento a Transferir

### Tipo 1 — Decisiones Técnicas (ADRs)

```
ADR = Architecture Decision Record
Documenta por qué se tomó una decisión técnica importante.

Por qué importa:
→ El dev nuevo lee el código y ve que usaron Redis para las sesiones
→ Sin ADR: "¿por qué no la BD? ¿hay un problema con el enfoque actual?"
→ Con ADR: "Se eligió Redis porque con 1000 usuarios concurrentes la BD
   mostraba latencia de 200ms en las sesiones. Redis lo bajó a 3ms."

Formato simple de un ADR:

# ADR-001: Sesiones en Redis, no en base de datos

**Fecha:** 2024-03-15
**Estado:** Aprobado

**Contexto:**
El sistema tiene ~1000 usuarios concurrentes en hora pico.

**Decisión:**
Mover las sesiones de la BD a Redis.

**Razones:**
- Latencia de sesiones en BD: 200ms promedio en hora pico
- Latencia en Redis: 3ms
- Las sesiones no necesitan persistencia durable

**Alternativas consideradas:**
- Memcached: descartado por falta de soporte en el equipo
- Sesiones en cookie: descartado por tamaño del payload

**Consecuencias:**
- Requiere Redis corriendo localmente para desarrollo
- Las sesiones se pierden si Redis se reinicia sin persistencia

Dónde guardar los ADRs:
  docs/adr/ en el repositorio (junto al código)
  Formato: adr-001-descripcion.md, adr-002-descripcion.md
```

### Tipo 2 — Gotchas y Comportamientos No Obvios

```
Las trampas que no están en la documentación oficial.

Formato simple:

# Gotchas del Proyecto

## Integración con Stripe
- Los webhooks de Stripe llegan con un delay de hasta 30 segundos en staging.
  En producción son casi inmediatos. No asumir que el delay en staging es un bug.
- El campo `metadata` de Stripe tiene un límite de 500 caracteres por valor.
  Si el cliente envía un order_id largo, puede truncarse.

## Comportamiento del Queue
- El job SendOrderConfirmation retarda intencionalmente 5 segundos antes
  de enviar el email. Esto es para permitir que otros jobs del mismo pedido
  se completen primero. No "optimizar" este delay sin discutirlo.

## Base de datos
- La tabla `orders` tiene triggers en PostgreSQL para el campo `updated_at`.
  No actualizar ese campo manualmente — el trigger lo maneja.
  Si ves que Eloquent hace una query de update extra, es el trigger.

## Cliente Acme Corp específicamente
- El cliente importa pedidos desde un CSV cada lunes a las 6am.
  Durante esa importación hay picos de ~500 pedidos simultáneos.
  No hacer deploys los lunes antes de las 8am.

Estos gotchas se documentan cuando se descubren.
Cuando alguien pierde 2 horas en un problema → documentar la solución
en este archivo como parte de la solución al problema.
```

### Tipo 3 — Contexto del Cliente y el Negocio

```
Lo que el dev necesita saber del cliente para tomar buenas decisiones técnicas.

No es un manual del cliente — es el contexto de negocio que afecta las decisiones.

# Contexto de Negocio — [Nombre del proyecto]

## El cliente

**Acme Corp** gestiona un almacén con 15 operadores y 3 supervisores.
Los operadores son el usuario principal del sistema — no son técnicos.
Los supervisores aprueban pedidos grandes (> $5,000).

## Lo que más importa para el cliente

1. **Velocidad de carga** — los operadores usan tablets lentas en el almacén.
   Una página que tarda > 3 segundos genera quejas.

2. **Sin pérdida de datos** — si un operador crea un pedido y se corta la red,
   el pedido no puede perderse. Usar drafts autosave.

3. **Notificaciones en tiempo real** — los supervisores quieren saber
   inmediatamente cuando hay un pedido para aprobar, no al refrescar la página.

## Decisiones técnicas que surgieron del contexto

- Se eligió lazy loading agresivo por las tablets lentas
- Se implementó autosave en formularios de pedido (cada 30 segundos)
- Se usaron WebSockets para las notificaciones de aprobación

## Qué NO cambiar sin consultar

- El flujo de aprobación fue diseñado con el cliente y tiene lógica específica de su negocio.
  Cualquier cambio debe ser validado con [nombre del PM o del cliente].
```

---

## Sesión de Knowledge Transfer con el Lead

```
Para proyectos complejos o cuando hay riesgo de pérdida de conocimiento,
una sesión estructurada con el dev lead es más eficiente que documentar solo.

Duración: 2-3 horas en total, en sesiones de 45-60 minutos
Grabación: sí (con permiso) — la grabación puede consultarse después

AGENDA DE LA SESIÓN:

Bloque 1 — Historia y contexto (30 min)
  → ¿Cuál era el estado del proyecto cuando entraste?
  → ¿Cuáles fueron las 3 decisiones más difíciles que tomaste?
  → ¿Qué cambiarías si empezaras de cero?

Bloque 2 — El código problemático (45 min)
  → ¿Qué partes del código te generan más ansiedad cuando hay que cambiarlas?
  → ¿Qué hay en el código que nadie más entiende completamente?
  → ¿Hay deuda técnica que hay que conocer antes de tocar X?

Bloque 3 — Integraciones y servicios externos (30 min)
  → ¿Cuáles son los servicios externos más frágiles?
  → ¿Qué pasa cuando falla cada servicio externo?
  → ¿Hay comportamientos no documentados de las APIs externas?

Bloque 4 — El cliente (30 min)
  → ¿Qué cosas del cliente son importantes para las decisiones técnicas?
  → ¿Hay preferencias del cliente que no están documentadas?
  → ¿Qué cambios históricos pedidos por el cliente explican por qué el código está así?

El output de la sesión:
→ Notas del dev buddy sobre lo aprendido
→ Lista de ADRs a escribir
→ Actualización del GOTCHAS.md
→ Actualización del contexto de negocio en el README
```

---

## Documentación Continua — El Proceso Sostenible

```
El problema de documentar todo de golpe: nadie lo hace.
La documentación que se crea durante el trabajo es la que se mantiene.

REGLA DEL ESTUDIO: "Si pierdes más de 1 hora en algo → documentarlo"

Flujo:
1. Dev encuentra algo confuso o se traba
2. Dev resuelve el problema
3. Dev documenta la solución en el lugar correcto:
   → Gotcha: docs/gotchas.md
   → Decisión técnica: docs/adr/
   → Setup issue: README → sección "Problemas comunes"

Tiempo de documentación: 15-20 minutos después de resolver el problema.
Es el momento en que el conocimiento está más fresco.

Dónde va cada cosa:
  README.md               → Setup, arquitectura general, convenciones
  docs/adr/               → Decisiones técnicas con contexto
  docs/gotchas.md         → Comportamientos inesperados y soluciones
  docs/runbooks/          → Procesos operativos (deploy, rollback, etc.)
  Comentarios en el código → Explicar el POR QUÉ de decisiones no obvias
                              (el QUÉ lo dice el código, el POR QUÉ lo dice el comentario)

Lo que no documentar:
  → Cómo funciona el framework (para eso está la documentación oficial)
  → Qué hace cada función (eso lo dice el código)
  → El historial de decisiones exploradas y descartadas
    (solo la decisión final y por qué se tomó)
```
