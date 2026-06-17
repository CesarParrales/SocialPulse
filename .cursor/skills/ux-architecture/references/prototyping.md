# Prototipado y Validación

## Por Qué Prototipar Antes de Desarrollar

```
Un prototipo es una simulación del producto final.
Puede ser tan simple como papel o tan elaborado como código funcional.

El costo de descubrir un problema:
  En papel / wireframe:     $1
  En prototipo:             $10
  En diseño visual final:   $100
  En desarrollo:            $1,000
  En producción:            $10,000+

La ley de hierro: cuanto más tarde se descubre un problema, más cuesta arreglarlo.
Los prototipos permiten descubrir problemas baratos, no caros.

Qué se puede validar con un prototipo:
→ ¿El usuario entiende para qué sirve esta pantalla?
→ ¿Puede completar la tarea principal sin ayuda?
→ ¿Hay decisiones de navegación confusas?
→ ¿El vocabulario (labels, mensajes) tiene sentido para el usuario?
→ ¿El orden de los pasos de un flujo es lógico?

Lo que NO se puede validar con un prototipo:
→ Performance (velocidad real)
→ Viabilidad técnica de la implementación
→ Experiencia con datos reales en volumen
→ Comportamiento en edge cases técnicos
```

---

## Niveles de Fidelidad — Elegir el Correcto

```
PAPEL / BOCETO (fidelidad 1/5):
  Tiempo de creación: minutos
  Qué valida: estructura, flujo, conceptos
  Cuándo usar: exploración inicial, workshops, ideas rápidas
  Limitation: difícil de testear con usuarios remotos

WIREFRAME CLICKEABLE (fidelidad 2/5):
  Tiempo de creación: horas
  Qué valida: navegación, flujos, estructura de información
  Cuándo usar: antes de diseño visual, validación de IA
  Herramienta: Figma con links entre frames, Whimsical, Balsamiq

MOCKUP INTERACTIVO (fidelidad 3/5):
  Tiempo de creación: 1-3 días
  Qué valida: flujos completos, microcopy, jerarquía visual
  Cuándo usar: validación con usuarios antes de desarrollo
  Herramienta: Figma con prototype mode, Adobe XD

PROTOTIPO DE ALTA FIDELIDAD (fidelidad 4/5):
  Tiempo de creación: días a semanas
  Qué valida: casi todo excepto performance con datos reales
  Cuándo usar: demos ejecutivas, user testing final, handoff
  Herramienta: Figma avanzado, Framer

CÓDIGO FUNCIONAL (fidelidad 5/5):
  Tiempo de creación: semanas
  Qué valida: todo
  Cuándo usar: beta testing, validación técnica, producción
  Herramienta: el stack de desarrollo real

Regla de selección:
Usar la fidelidad más baja que responda la pregunta que se necesita responder.
No hacer un mockup de alta fidelidad para validar si un flujo tiene sentido.
```

---

## Testing con Usuarios — Cómo Validar el Prototipo

```
Cuántos usuarios para testear:
→ 5 usuarios encuentran el 80% de los problemas de usabilidad
→ No se necesitan más para encontrar los problemas principales
→ Mejor hacer 2 rondas de 5 que una ronda de 10

Tipos de test:

Moderado (con facilitador presente):
  → El facilitador da tareas y observa
  → Puede pedir al usuario que piense en voz alta
  → Puede hacer preguntas de seguimiento
  → Mejor para: problemas complejos, primera validación
  → Herramientas: Zoom con screen share, sesión presencial

No moderado (el usuario lo hace solo):
  → El usuario completa las tareas sin facilitador
  → Graba la pantalla y el audio
  → Mejor para: validar flujos específicos, mayor escala
  → Herramientas: Maze, UserTesting, Lookback

Guerrilla testing (en contexto real):
  → Mostrar el prototipo a cualquier persona disponible
  → Rápido y económico para validación rápida
  → No representativo estadísticamente pero útil para problemas obvios
  → Mejor que nada cuando no hay tiempo ni presupuesto
```

---

## Protocolo de Sesión de Testing

```
Antes de la sesión:
→ Definir 3-5 tareas específicas (no guiar al usuario)
→ Preparar el prototipo en el dispositivo correcto
→ Preparar las preguntas de seguimiento
→ No mostrar el prototipo antes de la tarea

Estructura de la sesión (45-60 min):
1. Bienvenida y contexto (5 min)
   "Vamos a probar un prototipo de [producto].
   No te estamos evaluando a ti — estamos evaluando el diseño.
   Si algo no funciona, es un problema del diseño, no tuyo.
   Por favor piensa en voz alta."

2. Preguntas de contexto (10 min)
   Entender el perfil del usuario, su contexto de uso,
   experiencia previa con productos similares

3. Tareas (30-40 min)
   Dar una tarea a la vez, sin ayudar
   "Imagina que [situación]. Haz lo que harías."
   Observar sin intervenir excepto si el usuario queda completamente bloqueado
   Tomar notas de:
   → Dónde duda el usuario (qué elementos son confusos)
   → Qué dice en voz alta
   → Dónde hace clicks que no llevan a donde esperaba

4. Debriefing (5 min)
   "¿Hay algo que quieras comentar?"
   "¿Qué fue lo más confuso?"
   "¿Qué fue lo más claro?"

Lo que NO hacer:
❌ Decir "¿entiendes para qué sirve esto?" → pregunta cerrada, sesga
❌ Ayudar cuando el usuario se confunde (observar la confusión ES el dato)
❌ Defender el diseño cuando el usuario lo critica
❌ Preguntar "¿te gusta?" → irrelevante, lo que importa es si puede usarlo
```

---

## Analizar Resultados del Testing

```
Después de 5 sesiones, buscar:

Patrones cualitativos (qué observaste):
→ Mismo punto de confusión en > 3 usuarios → problema confirmado
→ Misma acción incorrecta en > 3 usuarios → el label o la ubicación engaña
→ Misma pregunta en > 3 usuarios → falta información en ese punto

Métricas simples:
→ Task completion rate: % de usuarios que completaron la tarea
  < 60% → problema grave, rediseñar el flujo
  60-80% → hay fricciones, mejorar
  > 80% → funciona, optimizar detalles

→ Time on task: cuánto tardaron
  Más de 2x el tiempo esperado → hay problemas de usabilidad

→ Error rate: cuántas acciones incorrectas tomaron
  Más de 2-3 errores por tarea → la navegación no es intuitiva

Severidad de los hallazgos:
  Crítico:    impide completar la tarea → resolver antes de lanzar
  Serio:      complica significativamente la tarea → resolver en próxima iteración
  Menor:      molestia o confusión pasajera → considerar en backlog
  Cosmético:  preferencia estética → no priorizar
```

---

## Del Prototipo al Desarrollo — Handoff Limpio

```
El prototipo validado debe conectar con el equipo técnico:

Lo que el desarrollador necesita del prototipo:
→ Flujos completos con todos los estados (no solo el happy path)
→ Nombres consistentes de pantallas (que coinciden con el sitemap)
→ Especificación de transiciones (push, modal, replace, animación)
→ Comportamiento en estados vacío/cargando/error
→ Edge cases contemplados (no solo flujo principal)

Conexión con el stack técnico:
→ Los nombres de las pantallas del sitemap = los nombres de las rutas (routing)
→ Los estados de pantalla = los estados del store (React Query, Zustand, Riverpod)
→ Los flujos de auth = la configuración del middleware/guard
→ Las condiciones de visibilidad = la lógica de permisos

Este es el puente entre ux-architecture y las skills técnicas:
→ nextjs-fullstack: el sitemap define el App Router file structure
→ react-patterns: los estados de pantalla definen los AsyncState del componente
→ mobile-react-native: el sitemap = la estructura de Expo Router
→ mobile-flutter: el sitemap = la configuración de GoRouter
```

---

## Herramientas gratuitas opcionales (prototipo rápido)

```
Prioridad: Mermaid/ASCII (portable, sin vendor lock-in).

Si el equipo usa freemium para explorar variantes:
→ UX Pilot (créditos free) — variantes de pantalla; gate ui-audit antes de dev
→ FigJam / Miro free — flujos colaborativos (índice: formiux.com/herramientas)

Catálogo completo, tiers y reglas → ui-web-modern/references/learning-sources.md
```

---

## Entregables portables — prototipos sin herramienta

```
Cuando no hay Figma ni herramienta disponible, generar en markdown
(Mermaid flowchart, tablas o ASCII; sin herramientas externas al editor):

Flujo de prototipo conectado (Mermaid flowchart o texto):
  Pantalla A → [acción] → Pantalla B → [acción] → Pantalla C
  Con la transición indicada en cada arista

Comparación de estados (dos bloques ASCII lado a lado o tabla):
  Columna izquierda: estado vacío
  Columna derecha: estado con datos
  Útil para validar que ambos estados están contemplados

Task flow validation diagram (Mermaid con anotaciones):
  El camino del usuario en el prototipo con puntos de decisión
  Donde se esperan confusiones marcadas con [?]
  Donde se esperan éxitos marcados con [✓]

Uso:
"genera el flujo del prototipo de [proceso] con sus estados"
"genera la comparación de estados vacío vs con datos de [pantalla]"
"genera el task flow para la tarea [descripción de la tarea]"
```
