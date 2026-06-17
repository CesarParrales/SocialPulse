# Métricas y Mejora Continua

## Métricas de Equipo que Importan

```
Las métricas son para que el equipo mejore, no para juzgar al equipo.
Nunca usar métricas de velocidad o velocity para comparar equipos.

DORA Metrics (DevOps Research and Assessment) — las más relevantes:

1. Deployment Frequency
   ¿Con qué frecuencia se despliega a producción?
   Elite:    múltiples veces por día
   High:     una vez al día a una vez por semana
   Medium:   una vez por semana a una vez por mes
   Low:      menos de una vez por mes

2. Lead Time for Changes
   ¿Cuánto tarda un commit en llegar a producción?
   Elite:    < 1 hora
   High:     1 día - 1 semana
   Medium:   1 semana - 1 mes
   Low:      más de 1 mes

3. Change Failure Rate
   ¿Qué porcentaje de deployments causa problemas?
   Elite:    0-5%
   High:     5-10%
   Medium:   10-15%
   Low:      > 15%

4. Mean Time to Recovery (MTTR)
   ¿Cuánto tarda en recuperarse de un incidente?
   Elite:    < 1 hora
   High:     < 1 día
   Medium:   1 día - 1 semana
   Low:      > 1 semana
```

---

## Métricas de Sprint

```
Velocity:
- Story points completados por sprint (con DoD cumplida)
- Usar para planificar, no para presionar
- Trend (tendencia) más importante que valor absoluto
- Comparar el equipo consigo mismo, nunca con otros equipos

Sprint Goal Achievement Rate:
- % de sprints donde se cumplió el sprint goal
- Meta: > 80%
- Si es < 60% → hay problemas de estimación, planning o interrupciones

Commitment Reliability:
- % de story points comprometidos vs completados
- Meta: 80-90% (el 10-20% de margen es saludable)
- Si es < 70% → estimaciones muy optimistas o scope creep
- Si es > 95% → estimaciones muy conservadoras

Bug Rate:
- Número de bugs reportados en producción por sprint
- Bugs encontrados en QA (mejor) vs bugs en producción (peor)
- Tendencia decreciente = mejora en calidad

Cycle Time (Kanban):
- Tiempo desde que una tarea entra en "In Progress" hasta "Done"
- Monitorear outliers (tareas que tardan 3x el promedio)
- Indica bottlenecks en el proceso
```

---

## Health Metrics del Equipo

```
Estos no se miden con números, se evalúan en retros:

Satisfacción del equipo (1-5, anonymized en retro):
- ¿Estás aprendiendo y creciendo?
- ¿Puedes trabajar con autonomía?
- ¿El trabajo tiene sentido y propósito?
- ¿El equipo es psicológicamente seguro para decir lo que piensas?

Señales de equipo saludable:
✅ Los miembros se ayudan mutuamente sin que se les pida
✅ Los problemas se mencionan temprano, no cuando ya son crisis
✅ El equipo dice "no" cuando es necesario
✅ Los errores se tratan como aprendizaje, no como fallas personales
✅ Hay debates técnicos saludables

Señales de equipo en problemas:
⚠️ Nadie menciona problemas hasta que explotan
⚠️ Las retros siempre tienen la misma lista de problemas sin resolución
⚠️ Alta rotación de miembros
⚠️ Personas trabajando en silos sin conocimiento cruzado
⚠️ "Héroe único" que bloquea el conocimiento
```

---

## Gestión de Deuda Técnica

```
La deuda técnica NO es un problema que se resuelve "en algún momento".
Se gestiona activamente o crece hasta paralizar al equipo.

Tipos:
- Deliberada: "Sabemos que esto no es ideal, pero necesitamos entregarlo rápido"
  → Crear ticket inmediatamente, priorizar en próximo sprint
- Inadvertida: problemas descubiertos después de implementar
  → Documentar cuando se descubren
- Bit rot: código que funcionaba y se volvió problemático por cambios en el contexto
  → Requiere refactoring periódico

Estrategias de gestión:

1. Budget de deuda técnica (20% de la velocity):
   - Reservar 20% del sprint para deuda técnica y mantenimiento
   - No negociable con el PO — es costo de "mantener las luces encendidas"
   - Si la deuda es alta → aumentar al 30-40% temporalmente

2. Boy Scout Rule:
   - "Dejar el campamento más limpio de como lo encontraste"
   - Si tocas un archivo → deja una mejora pequeña
   - No refactoring masivo, pero mejoras incrementales
   - Se suma a la Definition of Done: "Si modifiqué código existente, lo dejé mejor"

3. Deuda técnica en el backlog:
   - Crear items de deuda técnica como user stories técnicas
   - Priorizar con el PO como cualquier otro item
   - "Como desarrollador, quiero extraer la lógica de descuentos a un servicio
      para que sea testeable y mantenible"
   - El PO entiende el valor cuando está explicado en términos de negocio

4. Refactoring sessions:
   - Periódicamente (cada 3-4 sprints): un sprint con 40-50% de deuda técnica
   - Solo cuando la deuda está afectando la velocity mediblemente
```

---

## Retrospectiva de Proceso — Mejorar el Sistema

```
Cada 3-4 meses: retro de nivel más alto sobre el proceso completo

Preguntas:
- ¿Las ceremonias actuales agregan valor? ¿Cuáles deben cambiar?
- ¿La duración de los sprints es la correcta?
- ¿El tamaño del equipo es el adecuado?
- ¿Las herramientas que usamos nos ayudan o nos estorban?
- ¿El backlog refleja las prioridades reales del negocio?
- ¿Hay patrones recurrentes en las retros que no hemos resuelto?

Señales de que el proceso necesita cambiar:
⚠️ Los sprints siempre terminan con items sin completar (por las mismas razones)
⚠️ La velocity es inconsistente sin razón clara
⚠️ El equipo considera las ceremonias una pérdida de tiempo
⚠️ Los stakeholders están descontentos con el ritmo de entrega
⚠️ El producto backlog tiene > 100 items activos (imposible de gestionar)

Cambios comunes que mejoran el proceso:
→ Acortar los sprints (de 2 semanas a 1 semana) si hay mucho cambio de prioridades
→ Ampliar los sprints (de 1 a 2 semanas) si el planning overhead es alto
→ Eliminar el standup diario si el equipo está en sync de otra forma
→ Combinar Review + Retro en 1.5h si los sprints son cortos
→ Hacer refinement más frecuente si hay muchas sorpresas en el planning
```

---

## OKRs — Alinear el Equipo con la Estrategia

```
OKR = Objective + Key Results
Conecta el trabajo del sprint con los objetivos del negocio

Estructura:
Objective: qué queremos lograr (cualitativo, inspirador)
Key Result: cómo medimos que lo logramos (cuantitativo, verificable)

Ejemplo:
Objective: "Mejorar la experiencia de checkout para aumentar conversión"

Key Results:
- KR1: Reducir el abandono del carrito de 68% a 55%
- KR2: Reducir el tiempo promedio de checkout de 4.2 min a 2.5 min
- KR3: Aumentar el NPS del proceso de pago de 32 a 50

Los sprints sirven a los OKRs:
- Sprint 1: mejorar formulario de pago (impacta KR2)
- Sprint 2: guardar dirección para compras futuras (impacta KR1, KR2)
- Sprint 3: one-click checkout para usuarios recurrentes (impacta KR1, KR2)
- Sprint 4: optimizar performance del checkout (impacta KR2, KR3)

Revisión: OKRs trimestrales, revisados mensualmente
Meta de logro: 60-70% de los KRs (100% = objetivos muy fáciles)
```
