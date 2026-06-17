# Ceremonias Ágiles

## Daily Standup — 15 Minutos, Sin Excusas

```
El standup es para el equipo, no para el manager.
No es un reporte de status. Es una coordinación entre pares.

Las 3 preguntas clásicas (reformuladas para mayor utilidad):
- ¿Qué completé ayer que avanza el sprint goal?
- ¿Qué haré hoy que avanza el sprint goal?
- ¿Hay algo que bloquea mi avance o el del equipo?

Señales de un standup disfuncional:
⚠️ "Todo bien, sin bloqueantes" en todos (no hay problemas = nadie está siendo honesto)
⚠️ Reportes detallados de 3+ minutos por persona
⚠️ El manager hace las preguntas en lugar de el equipo
⚠️ Decisiones técnicas largas durante el standup
⚠️ No se mencionan las dependencias bloqueantes
⚠️ Personas conectadas pero no presentes mentalmente

Formato alternativo eficiente (walk the board):
En lugar de ronda por persona → revisar el tablero de derecha a izquierda
1. ¿Qué está "Done" desde ayer? (mover al done)
2. ¿Qué está "In Progress"? ¿hay bloqueantes?
3. ¿Qué pasa a "In Progress" hoy?

Ventaja: se enfoca en el trabajo, no en las personas
Identifica cuellos de botella más fácilmente (qué está atascado)
```

---

## Sprint Planning — Cómo Hacerlo Bien

```
Duración: 2h para sprint de 2 semanas, 1h para sprint de 1 semana
Participantes: Scrum Master, PO, todo el equipo de desarrollo

Parte 1 — El QUÉ (primera mitad):
- PO presenta el objetivo del sprint propuesto
- PO presenta los ítems del backlog en orden de prioridad
- El equipo hace preguntas de clarificación
- El equipo confirma que los criterios de aceptación son claros
- Selección de items que el equipo puede comprometer

Parte 2 — El CÓMO (segunda mitad):
- El equipo descompone las historias en tareas técnicas
- Identifica dependencias y riesgos
- Valida que la estimación sigue siendo razonable con las tareas
- Asignación inicial de tareas (o flujo Kanban sin asignaciones fijas)
```

```
Sprint Goal — la estrella guía del sprint

Un sprint sin goal claro = dirección arbitraria cuando hay problemas.
Cuando algo se complica, el team no sabe qué priorizar.

Sprint Goal bien definido:
✅ "Esta semana los usuarios podrán completar su primer pedido end-to-end"
✅ "Al final del sprint, el admin puede gestionar el catálogo de productos"
✅ "Completar la integración con Stripe para pagos básicos"

Sprint Goal mal definido:
❌ "Implementar los tickets del sprint"
❌ "Avanzar con el proyecto"
❌ "Hacer las cosas que acordamos"

Si no se puede definir un sprint goal claro → el backlog no está bien priorizado
```

---

## Sprint Review — Demo Real, No Presentación

```
Duración: 1h para sprint de 2 semanas
Participantes: equipo + PO + stakeholders interesados (no obligatorio)

Objetivo: inspeccionar el incremento y adaptar el backlog

Agenda:
1. Sprint Goal — ¿se cumplió? (5 min)
2. Demo del software funcionando (30-40 min)
   → En staging, con datos reales o realistas
   → El equipo hace la demo, no una presentación de slides
   → Stakeholders pueden interactuar con el producto
3. Feedback de stakeholders → qué nueva información tenemos (10 min)
4. Estado del backlog → ¿cambia alguna prioridad a la luz del feedback? (10 min)

Lo que NO es el Sprint Review:
❌ Mostrar slides con lo que se hizo
❌ Explicar por qué algunas cosas no se completaron
❌ Una reunión de validación donde el PO revisa items
❌ Una demo de features que no están listas (solo lo que cumple DoD)

Tip: si nada en el sprint es "demo-able" para stakeholders → el sprint fue demasiado técnico
Las historias deben ser de usuario, no de infraestructura
```

---

## Retrospectiva — El Corazón de la Mejora

```
Duración: 1h para sprint de 2 semanas
Participantes: Scrum Master + equipo (sin PO si el equipo lo prefiere)
Frecuencia: fin de cada sprint

El objetivo NO es quejarse. Es identificar UNA acción concreta de mejora.

Formatos probados:

1. Start / Stop / Continue (el más simple):
   Start:    qué debería el equipo empezar a hacer
   Stop:     qué debería dejar de hacer
   Continue: qué está funcionando y debe continuar

2. Mad / Sad / Glad:
   Mad:    qué frustró al equipo
   Sad:    qué decepcionó
   Glad:   qué se celebra

3. 4Ls:
   Liked:    qué salió bien
   Learned:  qué aprendimos
   Lacked:   qué faltó
   Longed for: qué hubiera querido tener

4. Sailboat:
   Viento (a favor): qué nos ayudó a avanzar
   Anclas (en contra): qué nos frenó
   Arrecifes (riesgos): qué puede salir mal próximamente
   Sol (objetivo): hacia dónde vamos

Proceso estándar (1 hora):
- 5 min: set the stage (check-in del equipo)
- 10 min: generar datos (todos escriben notas individualmente)
- 5 min: insights (agrupar y votar temas más importantes)
- 30 min: decidir qué hacer (1-3 action items concretos)
- 10 min: cierre (quién es responsible de cada action item)
```

---

## Action Items de Retrospectiva — Que Funcionen

```
El problema más común: los action items se olvidan al siguiente sprint

Action item bien definido:
✅ Específico: "Configurar ESLint con las reglas acordadas"
✅ Assignado: "Responsable: Carlos"
✅ Plazo: "Para el miércoles de la semana 1 del próximo sprint"
✅ Verificable: "Done when: ESLint está en el pipeline de CI"

Action item que se olvidará:
❌ "Mejorar la comunicación"
❌ "Ser más cuidadosos con las estimaciones"
❌ "Arreglar la deuda técnica" (sin scope concreto)

Reglas:
- Máximo 3 action items por retro
- Revisarlos al inicio de la siguiente retro
- Si un action item se arrastra 3 retros → reconocer que no se va a hacer o re-priorizar
- Un action item completado = éxito concreto para celebrar

Seguimiento visible:
- Agregar los action items al backlog del sprint como tareas
- O tener una sección fija en el tablero para "Mejoras del proceso"
```

---

## Anti-patrones Comunes por Ceremonia

```
Standup:
❌ Standup de 45 minutos (solución: mover detalles a "parking lot" post-standup)
❌ El mismo bloqueante 3 días seguidos sin resolución
❌ La mitad del equipo ausente o conectada pero silenciosa

Sprint Planning:
❌ El PO agrega items durante el planning porque "son urgentes"
❌ Commitments basados en presión, no en velocity real
❌ Items estimados sin criterios de aceptación claros
❌ Sprint backlog de 60+ puntos cuando la velocity es 35

Sprint Review:
❌ Solo el tech lead hace la demo
❌ Mostrar features no completadas "para que el cliente sepa que avanzamos"
❌ Sin presencia de stakeholders → la demo no tiene impacto

Retrospectiva:
❌ "Todo estuvo bien, no hay nada para mejorar" (3 sprints seguidos)
❌ Los action items nunca se completan
❌ Los mismos temas aparecen retro tras retro sin acción
❌ El manager está presente → el equipo no habla con honestidad

Solución general: hacer menos ceremonias bien hechas que muchas ceremonias vacías
Si una ceremonia no produce valor → cambiarla, acortarla, o eliminarla
```
