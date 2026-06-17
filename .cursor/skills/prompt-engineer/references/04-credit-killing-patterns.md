# Módulo 04 · Credit-Killing Patterns — 35 Patrones que Destruyen Tokens

## Propósito
Identificar y corregir los errores más comunes en prompts antes de ejecutarlos.
Basado en los 35 patrones documentados de prompt-master v1.6.0,
adaptados y expandidos para las herramientas del ecosistema.

---

## Grupo 1 — Patrones de tarea y contexto (1-13)

```
PATRÓN 1 — Verbo de tarea vago
  Problema: "haz algo sobre...", "crea una cosa de..."
  Corrección: especificar exactamente qué produce la herramienta
  Ejemplo: "genera" / "renderiza" / "compone" / "anima" / "escribe"

PATRÓN 2 — Ausencia de sujeto central
  Problema: el prompt no especifica qué está en el centro del frame/audio/UI
  Corrección: declarar el sujeto principal explícitamente
  Malo: "algo que se vea lujoso"
  Bueno: "un neumático Continental en asfalto mojado bajo luz puntual"

PATRÓN 3 — Ausencia de restricciones de exclusión
  Problema: no decir qué NO debe aparecer
  Corrección: agregar bloque de exclusiones explícitas
  Las herramientas de imagen llenan el vacío con lo más probable —
  que raramente es lo correcto para la marca.

PATRÓN 4 — Mood decorativo sin función
  Problema: "hermoso", "increíble", "asombroso", "épico"
  Corrección: reemplazar con descriptores que la herramienta puede ejecutar
  Malo: "una foto hermosa y épica"
  Bueno: "shallow depth of field, single warm light source,
          cinematic color grading with deep blacks"

PATRÓN 5 — Longitud máxima sin efecto
  Problema: prompt de 500 palabras para una herramienta que usa 100
  Corrección: auditar y eliminar todo lo que no sea load-bearing
  Señal: si eliminar una oración no cambia el output probable, eliminarla.

PATRÓN 6 — Instrucciones contradictorias
  Problema: "minimalista pero con muchos detalles", "oscuro pero brillante"
  Corrección: resolver la contradicción antes de enviar el prompt
  Las herramientas promedian instrucciones contradictorias — resultado mediocre.

PATRÓN 7 — Ausencia de especificación técnica
  Problema: no declarar ratio, resolución, duración, o formato
  Corrección: siempre especificar el formato técnico del output esperado
  Las herramientas usan sus defaults — que raramente son los correctos.

PATRÓN 8 — Referencias de estilo sin contexto
  Problema: "estilo tipo Apple" sin especificar qué aspecto de Apple
  Corrección: especificar el elemento concreto de la referencia
  Malo: "estilo Wes Anderson"
  Bueno: "color palette pastels with centered symmetrical framing,
          top-down flat lay composition"

PATRÓN 9 — Personaje sin descripción funcional
  Problema: describir la emoción del personaje pero no su apariencia
  Corrección: si hay un personaje, describir lo que la herramienta puede procesar
  Las herramientas de imagen no pueden leer "alguien que se siente poderoso"
  pero sí pueden procesar "postura erguida, mirada directa a cámara, traje oscuro"

PATRÓN 10 — Entorno implícito
  Problema: asumir que la herramienta inferirá el contexto
  Corrección: describir el entorno explícitamente aunque sea obvio
  "Una cafetería" → "una cafetería urbana moderna con iluminación cálida
                      industrial, superficies de madera y metal oxidado"

PATRÓN 11 — Múltiples acciones en el mismo prompt
  Problema: pedir a la herramienta que haga dos cosas distintas a la vez
  Corrección: un prompt, una acción principal
  Para herramientas de texto o código: usar un objetivo por prompt

PATRÓN 12 — Ausencia de perspectiva o encuadre
  Problema (imagen/video): no especificar el punto de vista de la cámara
  Corrección: declarar: plano detalle / plano medio / plano general /
              over-the-shoulder / bird's eye / worm's eye

PATRÓN 13 — Contexto temporal ausente (video)
  Problema: no especificar qué ocurre en el tiempo en un prompt de video
  Corrección: describir el estado inicial → lo que cambia → el estado final
  "La llanta está estática → la cámara hace un slow dolly hacia adelante → fade a negro"
```

---

## Grupo 2 — Patrones de formato y alcance (14-25)

```
PATRÓN 14 — Chain of Thought en modelo razonador
  Ver 00-01-02-pipeline-intent-fabrication.md, Módulo 02 (misma carpeta)
  Corrección: eliminar "piensa paso a paso" para o3/o4/DeepSeek-R1/Gemini Thinking

PATRÓN 15 — Formato de output no declarado
  Problema: el LLM no sabe si quieres párrafo, lista, tabla, JSON, código
  Corrección: especificar el formato al final del prompt

PATRÓN 16 — Longitud de output no acotada
  Problema: respuestas infladas de LLMs que no saben cuándo parar
  Corrección: declarar "en [N] palabras" o "máximo [N] líneas"

PATRÓN 17 — Idioma no declarado (para LLMs multilingüe)
  Corrección: declarar el idioma del output si puede ser ambiguo

PATRÓN 18 — Rol asignado sin contexto de tarea
  Problema: "eres un experto en marketing" sin decirle qué hacer con eso
  Corrección: el rol tiene que conectar directamente con la tarea
  Bueno: "eres un director de arte especializado en publicidad automotriz.
          Tu tarea es describir la composición visual de..."

PATRÓN 19 — Grounding anchor ausente (para Gemini y herramientas web)
  Problema: no anclar el contexto a una fuente de verdad cuando la hay
  Corrección: para herramientas con acceso web, especificar la fuente

PATRÓN 20 — XML tagging ausente en prompts largos para Claude
  Problema: prompts largos sin estructura de tags confunden el contexto
  Corrección: usar <context>, <task>, <constraints>, <output_format>
  Solo para prompts de LLM con múltiples secciones.

PATRÓN 21 — Few-shot inconsistente
  Problema: los ejemplos dados no corresponden al output que se busca
  Corrección: los ejemplos deben ser el tipo exacto de output esperado

PATRÓN 22 — Instrucción de negación sin alternativa
  Problema: "no uses colores brillantes" sin decir qué sí usar
  Corrección: toda restricción acompañada de la alternativa correcta
  Malo: "sin colores brillantes"
  Bueno: "sin colores brillantes — usar paleta desaturada con
          único acento en tonos cobre/ámbar"

PATRÓN 23 — Scope creep en prompt de código
  Problema: pedir múltiples features en un solo prompt a Cursor/Claude Code
  Corrección: un feature por prompt; usar memoria de sesión para contexto

PATRÓN 24 — Prompt de imagen sin especificar tipo de render
  Para herramientas de imagen: declarar
  fotografía / ilustración / render 3D / pintura digital / mixed media

PATRÓN 25 — Ausencia de negative prompts donde la herramienta los soporta
  Midjourney (--no), Stable Diffusion (negative_prompt), Firefly (exclusions):
  Usar los mecanismos nativos de exclusión de cada herramienta.
```

---

## Grupo 3 — Patrones de razonamiento y agentes (26-35)

```
PATRÓN 26 — Agentic prompt sin definición de done
  Problema: el agente no sabe cuándo parar
  Corrección: siempre incluir criterio de finalización explícito

PATRÓN 27 — Prompt de agente sin manejo de error
  Corrección: especificar qué hace el agente si encuentra un obstáculo

PATRÓN 28 — Contexto de memoria asumido en sesión nueva
  Problema: el prompt asume que el modelo recuerda sesiones anteriores
  Corrección: incluir el contexto relevante en el prompt, no asumirlo

PATRÓN 29 — Tool calling sin output format declarado
  Para Claude con herramientas: especificar el formato del resultado esperado

PATRÓN 30 — Prompt de refactoring sin scope
  Para Cursor/Claude Code: especificar exactamente qué archivos y funciones
  "Refactoriza el módulo de autenticación" es ambiguo.
  "Refactoriza auth.js: extrae la función validateToken en un helper separado"

PATRÓN 31 — Prompt de datos sin declarar el formato de entrada
  Problema: el modelo no sabe cómo están estructurados los datos que va a procesar
  Corrección: declarar: CSV / JSON / texto plano / tabla y las columnas relevantes

PATRÓN 32 — Solicitud de análisis sin criterio de evaluación
  Problema: "analiza esto" sin decir con qué criterio
  Corrección: "analiza esto considerando [criterio 1], [criterio 2], [criterio 3]"

PATRÓN 33 — Prompt de traducción sin registro objetivo
  Problema: no especificar el registro del texto traducido
  Corrección: "traduce al español en registro [formal/informal/técnico/creativo]"

PATRÓN 34 — Prompt de imagen para video sin motion direction
  Problema: Runway y Pika necesitan saber qué se mueve y cómo
  Corrección: especificar qué elementos tienen movimiento y de qué tipo
  "La cámara hace un slow push-in hacia la llanta mientras el reflejo
   del asfalto tiembla levemente por la lluvia"

PATRÓN 35 — Vague first turns en modelos como Opus/Sonnet (sesiones largas)
  Problema: en conversaciones largas, el primer turno demasiado vago
             hace que el modelo pierda el hilo estratégico
  Corrección: en sesiones largas, el primer mensaje debe reestablecer
              el contexto clave explícitamente
```
