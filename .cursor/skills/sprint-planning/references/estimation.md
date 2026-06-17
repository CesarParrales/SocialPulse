# Estimación y Velocity

## Story Points vs Horas — La Discusión Real

```
Story Points:
  ✅ Capturan complejidad + incertidumbre + esfuerzo como unidad
  ✅ Permiten comparar items relativamente ("esto es el doble de complejo que aquello")
  ✅ Se normalizan solos — el equipo calibra su escala interna
  ✅ Separan la estimación del tiempo (evita presión "¿por qué tardas tanto?")
  ❌ No intuitivos para stakeholders ("¿cuándo estará listo?")

Horas/Días:
  ✅ Fáciles de entender para todos
  ✅ Se pueden convertir directamente a fechas
  ❌ Las estimaciones en horas son históricamente optimistas (subestiman)
  ❌ No capturan incertidumbre
  ❌ Crean presión y micromanagement ("¿por qué tardaste 6h en vez de 3h?")

Recomendación:
- Para el equipo y planning interno: Story Points
- Para comunicar con stakeholders: "Este sprint entregamos X features" o rangos de fecha
- Para proyectos nuevos: empezar con horas, migrar a SP cuando el equipo se calibra
```

---

## Planning Poker — El Proceso

```
Escala Fibonacci modificada (la más común):
0, 1, 2, 3, 5, 8, 13, 21, 40, 100, ?

Por qué Fibonacci:
- La incertidumbre aumenta con el tamaño
- No tiene sentido distinguir entre 14 y 15 si son grandes
- Las diferencias grandes (13 vs 21) son más importantes que las pequeñas (4 vs 5)

Tarjetas especiales:
? → No tengo suficiente información para estimar
☕ → Necesitamos un descanso
∞ → Demasiado grande, necesita dividirse antes de estimar
```

```
Proceso de Planning Poker:

1. El PO lee la historia y los criterios de aceptación (2 min)
2. El equipo hace preguntas de clarificación (3-5 min)
3. Cada dev elige una carta en secreto (1 min)
4. Revelar simultáneamente
5. Si hay consenso (o diferencia de 1) → acordar ese valor
6. Si hay divergencia significativa:
   - El más alto y el más bajo explican su razonamiento (2 min)
   - Votar de nuevo
   - Máximo 2 rondas — si no hay consenso, tomar el promedio o el más alto

Duración: máximo 5-7 min por historia
Si tarda más → la historia necesita más claridad antes de estimarla
```

---

## Escala de Referencia — Calibración Inicial

```
Para calibrar el equipo, definir una historia de referencia:

Ejemplo de escala para un proyecto web típico:

1 punto   → Cambio trivial bien comprendido
            "Cambiar texto del botón de Submit a Enviar"
            "Agregar campo opcional en formulario existente"

2 puntos  → Pequeño, bien comprendido, sin incertidumbre
            "Endpoint CRUD simple con validaciones básicas"
            "Agregar filtro por estado a listado existente"

3 puntos  → Mediano, bien comprendido
            "Nuevo módulo CRUD completo (Create + Read + Update + Delete)"
            "Integración simple con API externa documentada"

5 puntos  → Complejo o con incertidumbre media
            "Flujo de autenticación con OAuth"
            "Módulo de notificaciones multi-canal"
            "Primera vez que el equipo usa una tecnología"

8 puntos  → Alta complejidad o incertidumbre significativa
            "Sistema de pagos con Stripe (primera integración)"
            "Migración de schema con transformación de datos"
            "Módulo con múltiples reglas de negocio interrelacionadas"

13 puntos → Muy complejo, debería dividirse si es posible
            "Refactoring de módulo crítico con tests insuficientes"
            "Integración con sistema legado sin documentación"

20/21+    → Épica disfrazada de historia — DIVIDIR antes de estimar
```

---

## Velocity — Medir y Usar Correctamente

```
Velocity = suma de story points completados en un sprint

"Completados" significa Definition of Done cumplida totalmente.
Historia al 90% = 0 puntos (para calcular velocity)
Historia al 100% = sus puntos completos

Período de calibración: 3-5 sprints para tener una lectura confiable

Cómo usar la velocity:
1. Calcular promedio de los últimos 3-5 sprints
2. Tomar el rango: velocity mínima y máxima
3. Planificar entre esos valores (no el máximo)

Ejemplo:
Sprint 1: 34 puntos
Sprint 2: 28 puntos
Sprint 3: 41 puntos
Sprint 4: 36 puntos
Sprint 5: 31 puntos

Promedio: 34 puntos
Rango: 28-41 puntos
Planificar: 30-34 puntos (conservador)
```

---

## Factores que Afectan la Velocity

```
Factores que REDUCEN la velocity (ajustar en planning):

Por ausentismo:
- Vacaciones, enfermedad, festivos
- Capacidad = (días laborables - días de ausencia) × velocity diaria
- Si el equipo tiene 25% de ausencia → reducir el sprint backlog 25%

Por actividades no de desarrollo:
- Reuniones de empresa / all-hands
- Onboarding de nuevos miembros (reduce velocity del senior también)
- Soporte L2/L3 si el equipo es responsible
- Entrevistas técnicas

Por factores técnicos:
- Deuda técnica alta → multiplicador x1.3-1.5 en estimaciones
- Tecnología nueva para el equipo → x1.3-1.5 en primeras 2-4 semanas
- Dependencias externas bloqueantes
- Ambiente de desarrollo inestable

Factores que se MANTIENEN constantes:
- Ceremonias ágiles (ya contabilizadas en la velocity histórica)
- Code reviews (parte del Definition of Done)
- Testing (parte de las estimaciones)
```

---

## Técnica PERT para Estimaciones de Proyecto

```
Cuando el cliente pregunta "¿cuándo estará listo?":

PERT = (Optimista + 4 × Más probable + Pesimista) / 6
Desviación estándar = (Pesimista - Optimista) / 6

Ejemplo para un módulo:
Optimista:     3 semanas (todo sale perfecto, sin blockers)
Más probable:  5 semanas (condiciones normales)
Pesimista:     9 semanas (problemas técnicos, cambios de reqs)

PERT = (3 + 4×5 + 9) / 6 = (3 + 20 + 9) / 6 = 32/6 = 5.3 semanas
Desv. estándar = (9 - 3) / 6 = 1 semana

Dar como fecha: 5-7 semanas (PERT ± 1.5 desv. estándar)
Nunca dar como fecha: "3 semanas" (el optimista)

Por qué es importante:
- Las estimaciones puntuales SIEMPRE son incorrectas
- Los rangos son honestos y gestionan expectativas
- El cliente puede tomar decisiones informadas
```

---

## T-Shirt Sizing — Para Estimación Rápida de Backlog Largo

```
Cuando tienes 30+ items y necesitas estimarlos rápido:
XS, S, M, L, XL (no story points todavía)

Proceso (máx 1h para 30-40 items):
1. El PO describe cada item brevemente (30 seg cada uno)
2. El equipo vota simultáneamente con gestos o tarjetas:
   XS = pulgar arriba, S = mano abierta, M = signo V, L = puño, XL = cruz
3. Sin discusión para consenso rápido
4. Anotar los outliers para discutir

Conversión a story points después:
XS = 1
S = 2-3
M = 5
L = 8
XL = 13-20 (probablemente dividir)

Cuándo usar T-Shirt sizing:
- Grooming de backlog largo (50+ items)
- Estimación rápida para roadmap trimestral
- Cuando el cliente necesita un forecast aproximado rápido
```
