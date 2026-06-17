# Checklist del Primer Día

## El Objetivo del Día 1

```
Al terminar el día 1, el dev nuevo debe:
✓ Tener todos los accesos funcionando
✓ Tener el ambiente local corriendo
✓ Entender qué hace el sistema a nivel de negocio
✓ Tener su primer task claro y asignado
✓ Saber a quién preguntarle qué

NO necesita al final del día 1:
✗ Entender toda la arquitectura del código
✗ Conocer todos los módulos del sistema
✗ Tener su primer PR listo
✗ Haber leído todos los tests
```

---

## Preparación Antes de que Llegue (responsabilidad del estudio)

```
Esta preparación toma 1-2 horas de un dev existente.
Se hace ANTES del primer día, no el día que llega.

ACCESOS (preparar con 1-2 días de anticipación):
□ Cuenta de email del estudio creada y con acceso
□ Invitación a GitHub/GitLab con acceso al repo del proyecto
□ Acceso al password manager del proyecto (vault específico del proyecto)
□ Invitación al Slack del equipo con los canales relevantes
□ Acceso al Linear/Jira/Notion del proyecto
□ Acceso al Sentry del proyecto (monitoreo)
□ Acceso a staging para probar (no a producción — primer día no)

DOCUMENTACIÓN (tener lista antes del día 1):
□ README actualizado y completo (ver project-readme.md)
□ .env.example con todos los campos (sin valores reales)
□ Setup guide testeado en máquina limpia en las últimas 2 semanas

PRIMER TASK (definir antes del día 1):
□ Task pequeño, bien delimitado, en un área del código manejable
□ Con criterios de aceptación claros (no "mejorar el módulo X")
□ Que requiera entender una parte del sistema, no todo
□ Que resulte en un PR real (no un cambio trivial)

DEV BUDDY asignado:
□ No el dev líder (el dev líder está muy ocupado)
□ Alguien del equipo que pueda responder preguntas en el día
□ Disponible para una llamada de 15 minutos al final del día
```

---

## Agenda del Día 1

```
MAÑANA (2-3 horas con un dev del equipo):

9:00 — Bienvenida y contexto del proyecto (30 min)
  → El dev líder o PM explica el proyecto en términos de negocio
  → No el código — el problema que resuelve y para quién
  → El contexto del cliente: qué hace su negocio, qué importa para ellos
  → El estado actual: qué está en producción, qué está en desarrollo

9:30 — Tour del código (45 min)
  → Dev líder hace el tour, no el dev nuevo
  → Estructura de directorios: qué va dónde y por qué
  → Los 2-3 patrones más importantes del proyecto (cómo están organizados Services, etc.)
  → Los flujos de negocio más críticos (ver sección en README)
  → Qué NO explicar: cada archivo, cada función, cada configuración
  → Dejar que las preguntas guíen la profundidad

10:15 — Setup del ambiente (1-2 horas, autónomo con README)
  → El dev nuevo sigue el README sin ayuda
  → El dev buddy está disponible si hay bloqueos (no sentado al lado)
  → Si el dev nuevo se bloquea → eso es un bug del README → documentar la solución

TARDE (autónomo):

12:00 — Almuerzo / break

13:00 — Exploración del proyecto y del task asignado
  → El dev nuevo lee el código relevante para su primer task
  → Hace preguntas por escrito (Slack/Linear) — no interrupciones verbales
  → El dev buddy responde cuando puede (no inmediatamente)

15:00 — Check-in de 15 minutos con el dev buddy
  → ¿El ambiente funciona?
  → ¿Entendió el task?
  → ¿Hay bloqueos?
  → NO es un status meeting — es un desbloqueador

17:00 — Fin del día
  → El dev nuevo escribe en Slack: qué hizo, qué está bloqueando, qué hará mañana
  → Esto es el standup escrito del día 1
```

---

## El Primer Task — Criterios de Selección

```
Un buen primer task:

✅ ALCANCE PEQUEÑO Y BIEN DEFINIDO
   "Agregar el campo 'notas internas' al formulario de pedido.
    El campo es opcional, se guarda en la tabla orders.
    Se muestra en la vista de detalle del pedido."
   → Toca el modelo, el formulario, el migration, el test
   → Completo pero pequeño

✅ EN UN ÁREA DEL CÓDIGO BIEN DOCUMENTADA
   Que sea en un módulo con tests existentes y código limpio.
   El primer task no es para refactorizar el legacy.

✅ QUE REQUIERA ENTENDER EL STACK COMPLETO
   Una feature pequeña end-to-end (desde el front hasta la BD)
   es mejor que arreglar un bug puntual en el backend.
   El dev nuevo aprende más del ciclo completo.

✅ CON CRITERIO DE ACEPTACIÓN VERIFICABLE
   "El campo aparece en el formulario, se guarda en la BD,
    y se muestra en la vista de detalle."
   No: "mejorar la UI del formulario de pedido"

❌ TASKS QUE NO SON BUENOS PRIMEROS TASKS:
   → Refactoring de código existente (sin contexto, es peligroso)
   → Performance optimization (requiere entender toda la arquitectura)
   → Bugfix en código legacy sin documentación
   → Integración con sistema externo (demasiadas variables)
   → Task que depende de otro task no terminado
```

---

## Errores Comunes del Día 1

```
ERROR 1 — Sobrecargar con información en la mañana
  Síntoma: el dev líder habla 3 horas seguidas del proyecto
  Resultado: el dev nuevo recuerda el 20% al día siguiente
  Solución: máximo 45 minutos de tour. El resto lo aprende haciendo.

ERROR 2 — No tener los accesos listos
  Síntoma: el día 1 se gasta en esperar aprobaciones de acceso
  Resultado: el dev nuevo no puede hacer nada productivo
  Solución: preparar todos los accesos 2 días antes

ERROR 3 — El README no funciona
  Síntoma: el setup falla con un error no documentado
  Resultado: el dev nuevo pasa horas debuggeando o interrumpiendo
  Solución: testear el README en máquina limpia antes del onboarding

ERROR 4 — El primer task es demasiado grande
  Síntoma: el dev nuevo no puede estimar cuánto le toma
  Resultado: 3 días sin PR = frustración + sensación de no avanzar
  Solución: el primer task debe ser completable en 2-3 días máximo

ERROR 5 — Sin dev buddy disponible
  Síntoma: el dev nuevo hace preguntas y espera horas para la respuesta
  Resultado: blocking continuo, ritmo lento
  Solución: asignar dev buddy con disponibilidad real ese día
```

---

## Mensaje de Bienvenida — Template

```
Enviar el día anterior o la mañana del día 1:

---
Hola [nombre],

Bienvenido al equipo. Aquí está todo lo que necesitas para el primer día:

ACCESOS:
→ GitHub: [URL del repo] (ya tienes acceso con este email)
→ Password manager: [instrucción para acceder al vault del proyecto]
→ Slack: [URL de invitación] — únete a #[proyecto] y #general
→ Linear/Jira: [URL]

PRIMER DÍA:
→ 9:00 nos juntamos por [Zoom/Meet/Slack huddle] para el tour del proyecto
→ El resto del día trabaja autónomo con el README del proyecto
→ [Nombre del dev buddy] es tu punto de contacto para preguntas hoy

ANTES DE MAÑANA:
Intenta seguir el README de setup y levantar el ambiente local.
Si encuentras algún problema, toma nota del error exacto — lo resolvemos juntos.

README del proyecto: [URL]

Cualquier duda antes del lunes, escríbeme.

[Nombre]
---
```
