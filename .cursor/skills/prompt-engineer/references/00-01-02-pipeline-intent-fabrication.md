# Módulo 00 · Pipeline Completo — Referencia de Lógica

## El pipeline en detalle

Este módulo documenta la lógica de cada paso para referencia.
La ejecución es silenciosa — el usuario solo ve el prompt resultante.

---

### Paso 1 — Detección de herramienta

```
HERRAMIENTAS DEL ECOSISTEMA (prioritarias):
  Adobe Firefly     → imagen, uso comercial seguro, integrado en CC
  Magnific Space    → upscaling, refinamiento, storyboards
  Canva AI          → imagen + diseño, equipos sin diseñador dedicado
  Runway ML         → video, motion, efectos visuales
  Pika Labs         → video corto, social media, animación de imagen
  Kling AI          → video narrativo, duraciones largas
  Udio              → música, audio branding, jingles
  Suno              → música con letra, jingles rápidos
  Adobe Podcast AI  → mejora de voz, post-producción de audio
  Figma AI          → UI/UX, system design, prototipos
  v0.dev            → web components, landing pages en código
  Bolt.new          → web apps completas desde descripción
  Claude Code       → código, agentic tasks, automatización
  Cursor/Windsurf   → IDE con IA, refactoring, completions

HERRAMIENTAS EXTERNAS AL ECOSISTEMA (también soportadas):
  Midjourney, DALL-E, Stable Diffusion → imagen
  ChatGPT, Gemini, Llama → LLM general
  GitHub Copilot → código
  Cualquier herramienta desconocida → Universal Fingerprint
```

### Universal Fingerprint — para herramientas desconocidas

```
Si la herramienta no está en la lista:
1. Inferir de la descripción del usuario si es: imagen / video / audio /
   texto / código / UI / datos / agente
2. Aplicar el template de la categoría más cercana
3. Declarar en el output: "Optimizado para [categoría inferida].
   Si la herramienta tiene restricciones específicas, ajustar [X] y [Y]."
```

---

### Paso 2 — Extracción de intención

Ver Módulo 01 más abajo en este mismo archivo (9 dimensiones).

### Paso 3 — Clarificación

```
Preguntar SOLO si falta información que el modelo no puede inferir
y que cambia sustancialmente el prompt.

Ejemplos de cuando SÍ preguntar:
  "¿El video es para TV (16:9) o para Instagram (9:16)?"
  "¿La imagen es para uso comercial o personal?"
  "¿El personaje tiene rasgos físicos específicos?"

Ejemplos de cuando NO preguntar:
  "¿Qué estilo prefieres?" — inferir del contexto o usar el del ecosistema
  "¿Qué colores?" — si hay contexto de marca, usarlos; si no, inferir del mood
  "¿Para qué es el prompt?" — si el objetivo está claro, no re-preguntar
```

### Pasos 4-6

Ver `04-credit-killing-patterns.md` (misma carpeta) y `data/routing-suites-models.md`.

---

---

# Módulo 01 · Extracción de Intención — 9 Dimensiones

## Las 9 dimensiones (análisis silencioso)

Ejecutar internamente antes de construir el prompt.
No mostrar este análisis al usuario.

```
DIMENSIÓN 1 — TAREA PRINCIPAL
  ¿Qué debe producir exactamente la herramienta?
  (Una imagen, un video de X segundos, un componente React, un jingle)

DIMENSIÓN 2 — SUJETO / CONTENIDO CENTRAL
  ¿Qué está en el centro del output?
  (Una llanta, una persona, una interfaz, un paisaje sonoro)

DIMENSIÓN 3 — ESTILO / MOOD
  ¿Qué atmósfera, tono visual, o sentimiento debe tener?
  ¿Hay referencias implícitas o explícitas de estilo?

DIMENSIÓN 4 — RESTRICCIONES TÉCNICAS
  ¿Hay formato, ratio, duración, resolución, o formato de archivo requerido?
  ¿Hay restricciones de la plataforma de destino?

DIMENSIÓN 5 — RESTRICCIONES DE CONTENIDO
  ¿Qué NO debe aparecer?
  ¿Hay elementos que el usuario evitó mencionar que probablemente quiere excluir?

DIMENSIÓN 6 — AUDIENCIA / CONTEXTO DE USO
  ¿Para quién es este output y dónde va a vivir?
  ¿Hay implicaciones culturales o de mercado?

DIMENSIÓN 7 — CRITERIO DE ÉXITO
  ¿Qué hace que este prompt sea exitoso?
  ¿Qué resultado exacto busca el usuario?

DIMENSIÓN 8 — CONTEXTO DEL ECOSISTEMA (si aplica)
  ¿Hay concepto creativo, marca, o campaña activa en la sesión?
  Si sí: ver 03-ecosystem-enrichment.md (misma carpeta)

DIMENSIÓN 9 — GAPS CRÍTICOS
  ¿Qué información falta que cambiaría sustancialmente el prompt?
  ¿Vale la pena preguntar o se puede inferir razonablemente?
```

---

---

# Módulo 02 · Reglas de No-Fabricación

## Por qué estas técnicas fallan en single-prompt

Este módulo documenta el razonamiento detrás de las prohibiciones.
Para consulta; la aplicación es automática en el pipeline.

---

### Mixture of Experts — por qué falla

```
Qué promete: múltiples "expertos" con distintos puntos de vista
             evalúan y enrutan la respuesta

Por qué falla en single-prompt:
  Un solo modelo, un solo forward pass, no puede hacer routing real.
  Lo que ocurre: el modelo finge tener múltiples personalidades
  pero todas provienen del mismo estado de activación.
  Resultado: respuestas infladas, inconsistentes, con alta probabilidad
  de alucinación en cada "experto" simulado.

Cuándo sí funciona: con múltiples llamadas a la API reales,
cada una en su propio contexto. No en un solo prompt.
```

### Tree of Thought — por qué falla

```
Qué promete: el modelo explora múltiples ramas de razonamiento
             en paralelo y selecciona la mejor

Por qué falla:
  Los LLMs generan texto secuencial. No hay paralelismo real.
  El modelo simula "ramas" pero cada una contamina las siguientes
  porque comparten el mismo contexto de activación.
  Resultado: razonamiento circular que parece profundo pero es lineal.
```

### Chain of Thought en modelos razonadores — por qué degrada

```
Modelos nativamente razonadores (lista actualizada en data/routing-suites-models.md):
  o3, o4-mini, DeepSeek-R1, Qwen3 thinking, Gemini 2.5 Thinking

Estos modelos tienen razonamiento interno (thinking tokens).
Cuando se les agrega CoT explícito en el prompt:
  1. El modelo tiene que reconciliar su razonamiento interno
     con el que el prompt le pide mostrar
  2. Esto produce outputs más largos y menos precisos
  3. El "thinking" visible puede contradecir el interno

Regla simple: si el modelo piensa solo, no pedirle que piense en voz alta.
```
