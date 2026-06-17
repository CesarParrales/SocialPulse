# Módulo 03 · Enriquecimiento con Contexto del Ecosistema

## Propósito
Cuando hay un brief, concepto o campaña activa en la sesión,
este módulo extrae los elementos relevantes y los inyecta en el prompt.
Es la diferencia entre un prompt genérico y uno que pertenece a la campaña.

---

## 03.1 Señales de contexto activo

El módulo se activa cuando detecta cualquiera de estas señales:

```
SEÑALES EXPLÍCITAS:
  □ El usuario menciona el nombre de una campaña o cliente
  □ Hay un concepto creativo declarado en la sesión
  □ El usuario dice "para la campaña de X" o "según el brief"
  □ Hay una plataforma de marca con paleta o tono definidos

SEÑALES IMPLÍCITAS:
  □ El usuario referencia elementos visuales que corresponden
    a un KV ya definido ("la paleta oscura que usamos", "el tono del hero film")
  □ El usuario da restricciones de marca sin explicarlas
    ("sin mostrar el logo del auto", "sin connotación deportiva")
    → Estas restricciones ya existen en el brief — buscarlas

AUSENCIA DE SEÑALES:
  □ Si no hay ninguna señal: operar en Modo Aislado
  □ No inventar contexto que no existe en la sesión
```

---

## 03.2 Elementos a extraer del contexto activo

> Nota: las fuentes nombradas abajo (neurocreativity, brand-strategy,
> creative-production-inventory, media-planning) son skills complementarias
> de otro ecosistema y no existen en este repositorio. Usar cada bloque solo
> si la skill correspondiente está disponible en el entorno; si ninguna lo
> está, operar en Modo Aislado (default).

Cuando se confirma contexto activo, extraer:

```
DEL OUTPUT DE neurocreativity / advertising-insight:
  □ Concepto central (una oración)
  □ Emoción central (específica — no "positiva")
  □ Tono visual declarado
  □ Lo que la campaña NO puede ser (los anti-patrones)
  □ El gancho de memoria

DE brand-strategy / plataforma de marca:
  □ Personalidad: adjetivos activos + anti-adjetivos
  □ Paleta de colores (si está definida)
  □ Estilo fotográfico / mood visual
  □ Restricciones de identidad

DE creative-production-inventory (si existe):
  □ Especificaciones técnicas de la pieza
  □ Suite de IA recomendada para este tipo de pieza
  □ Ratio / dimensiones / duración

DE media-planning (si existe):
  □ Canal de destino (Instagram / YouTube / OOH / TV)
  → Ajusta el ratio, la duración, y el estilo del prompt
```

---

## 03.3 Cómo inyectar el contexto en el prompt

### Estructura del prompt enriquecido

```
[CONTEXTO DE MARCA / CAMPAÑA]
  Marca: [nombre y categoría]
  Concepto activo: [el concepto en una oración]
  Emoción central: [la emoción específica]
  Personalidad visual: [adjetivos activos]
  Paleta: [descripción de colores si aplica]

[RESTRICCIONES DE MARCA]
  Incluir: [elementos que deben estar presentes]
  Excluir: [elementos que nunca pueden aparecer]

[ESPECIFICACIÓN DE LA PIEZA]
  Tipo: [foto / video / motion / UI / audio]
  Formato: [dimensiones, duración, ratio]
  Canal de destino: [dónde va a vivir]
  Suite: [herramienta específica + parámetros relevantes]

[DESCRIPCIÓN VISUAL / NARRATIVA]
  [Lo que debe aparecer, cómo, con qué luz, desde qué ángulo]
  [La atmósfera que debe transmitir]

[PARÁMETROS TÉCNICOS DE LA SUITE]
  [Específicos de la herramienta — ver data/routing-suites-models.md]
```

---

## 03.4 Ejemplo aplicado — Continental "La última decisión"

### Input rough del usuario
```
"necesito un prompt para Magnific Space de una llanta en la lluvia"
```

### Contexto detectado en la sesión
```
Concepto: "La última decisión" — el conductor premium que completó
          su auto con el mismo criterio con el que lo eligió
Emoción: Orgullo silencioso del criterio completo
Personalidad: Preciso. Silencioso. Seguro de sí. No grita — sabe.
Paleta: Negros profundos, grises metálicos, punto de luz cálida
Restricciones: Sin mostrar el auto completo. Sin logos de marca de auto.
               Sin velocidad ni connotación de carreras. Sin ciudad protagonista.
Estilo: Fotografía automotriz editorial de lujo
Canal: Instagram feed (4:5) y OOH premium
```

### Prompt optimizado con contexto del ecosistema

```
[PROMPT PARA: Magnific Space — Image Refinement + Space workflow]

Ultra-close editorial shot of a Continental tire sidewall resting
on wet urban asphalt at night. The tire is the sole subject — no
car body visible, no road markings, no city skyline.

Lighting: single warm point-light source reflected in the wet
surface creating depth contrast against deep blacks and metallic
greys. Cinematic shallow depth of field, razor-sharp on the tire
tread, blurred asphalt reflection.

Mood: silent precision. The stillness of someone who has made
every decision deliberately. Not speed. Not power. Permanence.

Style reference: Pirelli Calendar editorial (textural, not erotic),
BMW M campaign photography, Car & Driver luxury automotive.

Exclude: motion blur, racing context, wide-angle distortion,
any vehicle components above the wheel arch, human presence.

Magnific settings:
  Enhancement: Ultra Quality
  Style strength: 85
  Subject focus: tire sidewall + wet reflection
  Output: 4:5 ratio for Instagram / 3:2 for OOH version

---
Modo: Ecosistema (campaña Continental activa)
Variantes disponibles: sí — versión para OOH horizontal disponible
```

---

## 03.5 Invarianza emocional en prompts del ecosistema

El principio de `creative-production-inventory`:
cada pieza debe preservar la emoción central del concepto.

Aplicado a prompts:

```
Antes de entregar cualquier prompt enriquecido, verificar:
  □ ¿La descripción visual transmite la emoción central?
  □ ¿Las palabras clave de mood corresponden al sistema
    emocional definido en neurocreativity?
  □ ¿Las restricciones eliminan los elementos que contradicen
    la personalidad de la marca?

Si una descripción es visualmente correcta pero emocionalmente neutral:
  → No es un buen prompt para el ecosistema
  → Agregar el vocabulario emocional correcto antes de entregar
```
