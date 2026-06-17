---
data_file: tool-routing
last_updated: 2025-05-26
validity_period: 6 months
next_update_expected: 2025-11-26
notes: "Actualizar cuando aparezcan nuevas herramientas relevantes
        o cuando las existentes cambien su sintaxis de prompting."
---

# Datos: Routing de Herramientas y Templates Base

## Herramientas del ecosistema — templates específicos

### Adobe Firefly
```
Tipo de prompt:     Descriptivo + modificadores de estilo
Estructura:
  [Sujeto principal] + [acción o estado] + [entorno]
  + [iluminación] + [estilo artístico] + [mood]
  + [especificaciones técnicas]

Ventaja clave: uso comercial seguro — siempre declarar este beneficio
               cuando el output es para producción de campaña

Parámetros relevantes:
  Style: [Photography / Illustration / Vector / 3D]
  Content type: [Photo / Art / Graphic]
  Color and tone: [descripción de paleta]
  Lighting: [tipo de luz]
  Composition: [encuadre]

Negative prompts: sección "Exclude" en la interfaz
```

### Magnific AI + Space
```
Tipo de prompt:     Imagen de referencia + instrucciones de refinamiento
Estructura:
  [Descripción del resultado final esperado]
  + [qué preservar de la imagen fuente]
  + [qué mejorar o cambiar]
  + [nivel de detalle objetivo]
  + [estilo de referencia]

Space workflow: describir cada iteración como transformación:
  "A partir de [imagen X], refinando [elemento] hacia [objetivo],
   manteniendo [lo que no debe cambiar]"

Parámetros clave:
  Enhancement: Ultra Quality / Standard
  Style strength: 0-100 (85-90 para preservar sujeto con nuevo estilo)
  Creativity: 0-100 (bajo para upscale fiel / alto para reinterpretación)
```

### Runway ML
```
Tipo de prompt:     Descripción cinemática de la escena
Estructura:
  [Estado inicial de la escena]
  + [movimiento de cámara]
  + [movimiento de sujetos/elementos]
  + [transición temporal]
  + [mood visual y de iluminación]

Comandos de cámara para Runway:
  "slow dolly in" / "steady static shot" / "gentle handheld"
  "aerial descent" / "rack focus from [A] to [B]"

Para motion brush:
  Describir zona de movimiento + tipo de movimiento separadamente

Parámetros:
  Duration: [X] seconds (máximo 16s por generación)
  Aspect ratio: 16:9 / 9:16 / 1:1
  Camera motion preset: si aplica
```

### Pika Labs
```
Tipo de prompt:     Escena + movimiento específico
Estructura:
  [Descripción de la escena base]
  + [qué elemento se mueve]
  + [cómo se mueve]
  + [velocidad y suavidad del movimiento]

Para animación de imagen estática:
  "Starting from [descripción], animate [elemento] with [tipo de movimiento].
   Camera [movimiento de cámara]. [Duración]. [Mood]."
```

### Udio / Suno
```
Tipo de prompt:     Mood + género + instrumentación + uso
Estructura:
  [Mood emocional] + [género o referencia de estilo]
  + [instrumentación principal] + [tempo descriptor]
  + [vocales: sí/no, tipo si sí] + [duración]
  + [uso declarado: ad / brand / social]

Ejemplo:
  "Cinematic tension building into quiet resolution.
   Modern orchestral with sparse piano and low strings.
   No vocals. 45 seconds. Brand film underscore."

Para sound logos (2-4s):
  "Brand sonic identity. [adjetivos de la marca].
   Single distinctive musical phrase. [instrumento protagonista].
   Fade in [Xs], hold [Xs], fade out [Xs]."
```

### Figma AI + plugins
```
Para Magician / UX Pilot:
  Describir el componente en términos de función + contenido + estado
  "A [tipo de componente] that [función].
   Contains: [lista de elementos].
   State: [default/hover/active/disabled].
   Style: [sistema de diseño de referencia]."

Para v0.dev / Bolt.new:
  Describir en lenguaje natural de producto, no de código
  "Build a [tipo de componente/página] that [función principal].
   Users should be able to [acción 1], [acción 2].
   Stack: [React/Next/HTML]. Style: [Tailwind / especificación].
   Include: [elementos obligatorios]. Exclude: [lo que no debe estar]."
```

---

## Agentes de Desarrollo — System Prompts

Esta sección aplica cuando el destino es un agente de IA para desarrollo de software:
un chatbot de soporte técnico, un asistente de código, un agente de onboarding,
un bot de documentación, o cualquier LLM especializado en un dominio técnico
que se integra en un producto de cliente.

**El principio clave:** un system prompt de producción no es una instrucción —
es la arquitectura del comportamiento del agente. Cada línea define lo que hará
o no hará en cualquier situación que no se anticipó.

### Template base para agentes de desarrollo

```
Tipo de prompt:     Identidad + Scope + Comportamiento + Límites + Herramientas

Estructura:
  [IDENTIDAD]
    Quién es el agente y para qué contexto específico existe.
    Nunca genérico: "asistente de IA". Siempre específico:
    "asistente técnico del sistema de gestión de pedidos de Acme Corp."

  [SCOPE — qué SÍ hace]
    Lista exhaustiva de lo que el agente puede y debe hacer.
    En positivo. Verbos de acción: "responde", "analiza", "genera", "explica".

  [LÍMITES — qué NO hace]
    Igual de importante que el scope.
    Los límites previenen alucinaciones y usos no previstos.
    Sin límites explícitos: el agente intentará hacer todo.

  [COMPORTAMIENTO]
    Cómo responde ante situaciones comunes:
    → preguntas fuera de scope
    → información que no tiene
    → contradicciones en el input
    → solicitudes ambiguas

  [HERRAMIENTAS] (si el agente tiene acceso a funciones/APIs)
    Cuándo llamar cada herramienta.
    Qué hacer si la herramienta falla.
    Qué NO hacer con el output de la herramienta.

Longitud óptima: 300-800 tokens. Menos = comportamiento impredecible.
                 Más = la instrucción se pierde en ventana de contexto larga.
```

### Patrones de system prompt que funcionan en producción

```
PATRÓN 1 — Scope explícito con ejemplos:
  "Cuando el usuario pregunte sobre [X], responde con [Y].
   Cuando pregunte sobre [Z], di que no tienes esa información
   y sugiere contactar a [persona/canal]."
  → Los ejemplos son más efectivos que las reglas abstractas.

PATRÓN 2 — Persona con contexto de negocio:
  "Eres el asistente de [empresa]. Los usuarios son [descripción del usuario].
   Su objetivo principal es [objetivo]. El contexto es [contexto del negocio]."
  → El modelo genera respuestas más relevantes con contexto real.

PATRÓN 3 — Trigger patterns para herramientas:
  "Usa la herramienta [X] cuando el usuario mencione [keywords específicos].
   No uses [X] si el usuario solo está explorando — espera intención clara."
  → Reduce llamadas innecesarias a herramientas (costo y latencia).

PATRÓN 4 — Escalation path:
  "Si no puedes responder con certeza, di exactamente:
   'No tengo información suficiente sobre esto. Te recomiendo [acción].'
   Nunca inventes información sobre [dominio crítico]."
  → Previene alucinaciones en el dominio donde más importa.

PATRÓN 5 — Formato de respuesta consistente:
  "Todas tus respuestas sobre [tipo de consulta] siguen este formato:
   Situación: [1-2 oraciones]
   Pasos: [lista numerada]
   Nota importante: [si aplica]"
  → La consistencia del formato mejora la UX del agente.
```

### Errores comunes en system prompts de agentes

```
❌ "Eres un asistente muy útil y amigable que siempre ayuda."
   → Sin scope → intenta hacer todo → alucinaciones frecuentes

❌ "Responde solo preguntas sobre nuestros productos."
   → Regla sin ejemplos ni comportamiento para edge cases
   → El agente no sabe qué hacer cuando la pregunta es mixta

❌ Instrucciones contradictorias:
   "Sé conciso." + "Da respuestas detalladas y completas."
   → El modelo promedia o ignora una de las dos

❌ Scope creep en el system prompt:
   Agregar instrucciones sin quitar otras → el prompt crece infinitamente
   → A más de 1500 tokens: las instrucciones del inicio se debilitan

❌ No probar edge cases:
   Un prompt que funciona en el happy path no está probado.
   Los edge cases revelan dónde el modelo improvisa.
```

---

## Referencias de Prompts de Producción

Para proyectos que incluyen integración con LLMs y agentes de IA,
los system prompts reales de herramientas en producción son la mejor
referencia de cómo estructurar comportamiento en escenarios reales.

**Repositorio de referencia:**
github.com/asgeirtj/system_prompts_leaks
(40k+ stars, actualizado regularmente, incluye Claude, GPT-5.5, Gemini, Grok,
Copilot, Cursor, Zed y más)

**Prompts más relevantes para proyectos de desarrollo:**

```
Agentes de coding:
  Anthropic/claude-code.md          → agente de coding, manejo de contexto de codebase
  Misc/vscode-copilot-agent.md       → agente de IDE con herramientas de archivos
  Misc/cursor.md                     → agente con acceso a contexto del proyecto
  Misc/amp-code.md                   → agente de Sourcegraph, indexación de código

Agentes especializados en dominio técnico:
  Misc/docker-gordon-ai.md           → agente vertical en infraestructura
  Misc/zed.md                        → agente de editor con restricciones claras
  Misc/warp-2.0-agent.md             → agente de terminal

Agentes de búsqueda / research:
  Perplexity/perplexity-computer.md  → agente con web search y tool use
  OpenAI/tool-web-search.md          → cómo estructurar herramienta de búsqueda
  OpenAI/tool-deep-research.md       → research con razonamiento extendido

Agentes de productividad:
  Misc/notion-ai.md                  → agente especializado en dominio de datos del usuario
  Misc/raycast-ai.md                 → launcher agent con contexto del sistema
```

**Cómo usar esta referencia:**

```
1. Identificar qué tipo de agente necesita el proyecto del cliente
   (coding / búsqueda / dominio específico / productividad)

2. Leer el prompt de la categoría equivalente
   Preguntas a responder al leerlo:
   → ¿Cómo define el scope sin ser restrictivo en exceso?
   → ¿Cómo maneja las solicitudes fuera de scope?
   → ¿Cómo estructura el acceso a herramientas?
   → ¿Qué ejemplos incluye para comportamiento específico?

3. Extraer los PRINCIPIOS, no el texto
   Copiar el texto de un system prompt de otro producto → comportamiento
   del otro producto en el tuyo. Los principios son transferibles.
   El texto no.

4. Verificar la fecha del prompt antes de aplicar conclusiones
   Los modelos se actualizan. Un prompt de 6 meses puede no reflejar
   el comportamiento actual del modelo.
```

**Limitación importante:**
No todo el contenido del repositorio es verificado como auténtico.
Los prompts de Claude tienen mayor credibilidad (el mantenedor tiene
screenshots de verificación). Los de terceros varían en confiabilidad.
Usar como referencia de diseño, no como especificación técnica oficial.

---
data_file: reasoning-models
last_updated: 2026-05-31
validity_period: 2 months
next_update_expected: 2026-07-31
notes: "ACTUALIZAR CON FRECUENCIA. Los modelos nativamente razonadores
        se actualizan y lanzan constantemente. Esta lista puede quedar
        desactualizada en semanas. Verificar antes de usar."
agent_instruction: >
  AL LEER ESTE ARCHIVO: comparar next_update_expected contra la fecha actual.
  Si la fecha actual supera next_update_expected, alertar al usuario que la
  lista de modelos razonadores puede estar desactualizada antes de aplicar
  la regla de no-CoT.
---

# Datos: Modelos Nativamente Razonadores (mayo 2026)

## Modelos que NO deben recibir Chain of Thought explícito

```
OpenAI:
  GPT-5.5 Thinking                   → razonador nativo (lanzado may 2026)
  GPT-5.4 Thinking                   → razonador nativo
  o3, o3-mini, o4-mini               → razonadores clásicos de OpenAI
  o1, o1-mini, o1-preview            → generación anterior, mismo principio
  → CoT explícito degrada el output en todos.

Anthropic:
  Claude Opus 4.7 (extended thinking) → cuando el usuario activa explícitamente
  Claude Opus 4.6 (extended thinking) → extended thinking
  → Claude en modo normal SÍ se beneficia de CoT.
  → Verificar si el usuario está en modo thinking antes de aplicar regla.

Google:
  Gemini 3.5 Flash (thinking mode)   → cuando thinking está activo
  Gemini 3.1 Pro (thinking mode)     → cuando thinking está activo
  Gemini 2.5 Pro / Flash (thinking)  → versiones anteriores
  → El modo thinking se activa explícitamente — verificar.

xAI:
  Grok 4.3 (modo thinking)           → thinking disponible en Grok 4.x
  → Verificar si el usuario está usando el modo thinking.

DeepSeek:
  DeepSeek-R1 y variantes (R1-Zero, R1-Distill)
  → Siempre. Son modelos razonadores nativos.

Qwen:
  Qwen3 (modo thinking)
  QwQ-32B
  → Cuando el modo thinking está activo.
```

## Modelos estándar que SÍ se benefician de CoT

```
Anthropic:   Claude Sonnet 4.6 (modo normal, sin extended thinking)
             Claude Opus 4.6 / 4.7 (modo normal)
             Claude Haiku 4.5
OpenAI:      GPT-5.5 Instant, GPT-5.3 Chat API (no-thinking)
             GPT-4.1, GPT-4o y versiones anteriores
Google:      Gemini 3.5 Flash / 3.1 Pro (sin thinking activo)
             Gemini CLI (sin thinking)
xAI:         Grok 4.2 (sin thinking), Grok Expert
Meta:        Llama 3.x, Llama 4.x (todos)
Mistral:     Mistral Large, Le Chat, Mixtral (todos)
Perplexity:  Modelos de Perplexity (no son razonadores nativos)
```

## Regla de aplicación

```
Si la herramienta destino es un modelo razonador nativo:
  → NUNCA agregar "piensa paso a paso" / "think step by step" /
    "reason through this" / "show your reasoning"
  → Dar el prompt directo, conciso, sin scaffolding de razonamiento

Si hay duda sobre si un modelo es razonador:
  → Preguntar al usuario o tratar como modelo estándar (CoT sí)
  → Es más seguro agregar CoT de más que quitarlo de menos

Señal de que el usuario está en modo razonador:
  → Menciona "thinking", "extended thinking", "o3", "o4"
  → La respuesta tarda más de lo normal (el modelo está razonando)
  → La interfaz muestra un indicador de "thinking"
```
