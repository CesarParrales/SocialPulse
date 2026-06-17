# Definición del Problema

## Por Qué el Problem Statement es lo Más Importante

```
Un equipo sin Problem Statement claro construye funcionalidades
buscando un problema que resolver.

Un equipo con Problem Statement claro sabe:
→ Cuándo terminó el diseño (cuando resuelve el problema)
→ Cómo evaluar soluciones (¿resuelve el problema?)
→ Qué está dentro y fuera de scope
→ Cómo comunicar el valor a stakeholders
```

---

## Formatos de Problem Statement

### Formato HMW — How Might We (el más práctico)

```
"¿Cómo podríamos [verbo de acción] para [usuario específico]
 de modo que [resultado de valor]?"

❌ HMW malo (solución implícita):
"¿Cómo podríamos agregar notificaciones automáticas para los usuarios?"
→ Ya asume la solución (notificaciones)

❌ HMW malo (demasiado amplio):
"¿Cómo podríamos mejorar la experiencia del usuario?"
→ Sin dirección, caben 1000 soluciones

✅ HMW bien formulado:
"¿Cómo podríamos ayudar a los gestores administrativos
a tener visibilidad del estado de sus facturas
de modo que puedan anticipar problemas antes de que afecten al cliente?"

✅ Otro ejemplo:
"¿Cómo podríamos reducir el tiempo que toma el proceso de onboarding
para nuevos técnicos de campo
de modo que estén operativos en su primer día sin depender de su supervisor?"
```

### Formato Point of View (POV)

```
"[Usuario] necesita [necesidad real] porque [insight del research]"

[Usuario]: No un demographic. Un arquetipo con contexto.
[Necesidad]: Un verbo de acción, no una feature.
[Insight]: El por qué profundo que reveló el research.

Ejemplo:
"El gestor administrativo sobrecargado necesita
saber en tiempo real qué facturas requieren su atención
porque actualmente pierde credibilidad con sus clientes
cuando los errores llegan al cliente antes que a él."

Desglose:
Usuario: "el gestor administrativo sobrecargado" (arquetipo específico)
Necesidad: "saber en tiempo real qué facturas requieren atención" (acción)
Insight: "pierde credibilidad cuando los errores llegan al cliente primero" (el por qué real)
```

### Formato Job Story (más orientado a producto)

```
"Cuando [situación que activa la necesidad],
quiero [motivación/objetivo],
para [resultado esperado]"

Diferencia con User Story:
User Story: "Como [usuario], quiero [feature], para [beneficio]"
→ Foco en la feature

Job Story: "Cuando [situación], quiero [progreso], para [resultado]"
→ Foco en el contexto y el progreso que busca el usuario

Ejemplo:
"Cuando llega fin de mes y necesito cerrar la facturación,
quiero tener todos los datos consolidados sin buscar en múltiples sistemas,
para poder generar el informe al directorio con confianza y sin errores."

Por qué es más poderoso:
→ La situación explica cuándo ocurre el problema
→ El progreso define qué necesita lograr (no la feature)
→ El resultado revela el valor real que crea
```

---

## Criterios de un Buen Problem Statement

```
✅ Criterios:
- Está basado en research, no en suposiciones
- Describe el problema del usuario, no del negocio
- No implica una solución específica
- Es lo suficientemente específico para tener dirección
- Es lo suficientemente amplio para permitir creatividad
- El equipo puede decir "sí/no" a una solución basándose en él
- Un stakeholder puede entenderlo sin contexto técnico

Test de validación del Problem Statement:
Pregunta 1: "¿Podría alguien que no participó en el research entenderlo?"
Si no → demasiado técnico o asume contexto

Pregunta 2: "¿Elimina soluciones que no resuelven el problema?"
Si no → demasiado amplio

Pregunta 3: "¿Implica una solución específica?"
Si sí → reescribir más abstracto

Pregunta 4: "¿Está basado en algo que vimos u oímos en research?"
Si no → es una suposición, no un problem statement
```

---

## De Problem Statement a Criterios de Éxito

```
El Problem Statement define el problema.
Los criterios de éxito definen cómo saber que lo resolvimos.

Proceso:

Problem Statement:
"¿Cómo podríamos ayudar a los gestores a anticipar problemas
de facturación antes de que lleguen al cliente?"

Criterios de éxito del usuario (cualitativos):
→ El gestor se siente en control del proceso
→ No necesita revisar manualmente cada factura
→ Confía en que los errores se detectan automáticamente

Criterios de éxito medibles (cuantitativos):
→ Reducir el tiempo de revisión mensual de 3h a < 30 min
→ Reducir errores que llegan al cliente de 8% a < 1%
→ Aumentar NPS del proceso de facturación de 25 a > 50

Vinculados con métricas de producto:
→ Tasa de adopción del dashboard de facturación > 80% en 60 días
→ Reducción de tickets de soporte relacionados con facturas en 60%

CONECTAR con sprint-planning:
→ Los criterios de éxito se convierten en Key Results de los OKRs
→ Los KRs guían qué medir en cada sprint
→ Definition of Done técnica valida que el criterio de éxito es alcanzable
```

---

## Priorizar Problemas — Cuál Resolver Primero

```
Matriz de Impacto vs Frecuencia:

Alta Frecuencia + Alto Impacto    → PRIORIDAD 1 (resolver ahora)
Alta Frecuencia + Bajo Impacto    → PRIORIDAD 2 (optimizar)
Baja Frecuencia + Alto Impacto    → PRIORIDAD 3 (edge case crítico)
Baja Frecuencia + Bajo Impacto    → BACKLOG (si hay tiempo)

Aplicado al discovery:
→ Los problemas de PRIORIDAD 1 definen el Problem Statement principal
→ Los de PRIORIDAD 2 informan mejoras en el flujo
→ Los de PRIORIDAD 3 se convierten en edge cases del diseño
→ Los de PRIORIDAD 4 se documentan pero no bloquean

Criterio adicional: Factibilidad técnica
Un problema de alta prioridad que es técnicamente imposible hoy
→ documentar como deuda futura
→ diseñar con degradación elegante en el presente
```

---

## Plantilla portable — Problem Statement Canvas (ASCII)

```
Sin referencia externa, generar este canvas ASCII en markdown
(no depender de herramientas de visualización externas al editor):

┌─────────────────────────────────────────────────────────────┐
│              PROBLEM STATEMENT CANVAS                       │
│                                                             │
├─────────────────────────┬───────────────────────────────────┤
│  QUIÉN                  │  QUÉ INTENTA LOGRAR               │
│  ─────                  │  ───────────────────              │
│  [arquetipo específico] │  [job to be done]                 │
│                         │                                   │
├─────────────────────────┼───────────────────────────────────┤
│  FRICCIÓN ACTUAL        │  INSIGHT DEL RESEARCH             │
│  ────────────────       │  ─────────────────────            │
│  [qué impide el         │  [el por qué profundo             │
│   progreso hoy]         │   del problema]                   │
├─────────────────────────┴───────────────────────────────────┤
│                  PROBLEM STATEMENT                          │
│  ─────────────────────────────────────────────────────────  │
│  "¿Cómo podríamos [verbo] para [usuario]                   │
│   de modo que [resultado de valor]?"                        │
├─────────────────────────────────────────────────────────────┤
│  CRITERIOS DE ÉXITO         │  FUERA DE SCOPE               │
│  ───────────────────        │  ────────────────             │
│  ✓ [métrica 1]              │  ✗ [qué NO resuelve esto]     │
│  ✓ [métrica 2]              │  ✗ [qué queda para después]   │
└─────────────────────────────────────────────────────────────┘

Uso: "genera el problem statement canvas para [contexto del producto]"
→ inferir los campos desde la descripción del producto y usuario,
marcando [HIPÓTESIS] lo no validado.
```
