# User Journey Maps y Mapas de Empatía

## Mapa de Empatía — Entrar en la Cabeza del Usuario

```
El mapa de empatía responde: ¿qué experimenta el usuario?
No qué feature necesita. Qué vive internamente.

4 cuadrantes clásicos:

        PIENSA Y SIENTE
    (preocupaciones, aspiraciones,
     lo que realmente importa)
             ▲
             │
ESCUCHA ◄────┼────► VE
(qué le dicen  │  (entorno, alternativas,
sus pares,     │   lo que el mercado ofrece)
influencias)   │
             ▼
        DICE Y HACE
    (actitud en público,
     comportamiento observable)

     ──────────────────
     DOLORES │ GANANCIAS
     (miedos,│(deseos, qué considera
     frustraciones│ éxito, beneficios)
```

---

## Completar el Mapa de Empatía con Research

```
PIENSA Y SIENTE — extraer de:
- Lo que el usuario expresó emocionalmente en entrevistas
- Sus preocupaciones no dichas (tono, lenguaje corporal)
- Lo que más le importa en su vida/trabajo
- Sus ambiciones relacionadas con el problema

Ejemplo (producto de gestión de facturas):
"Le preocupa que un error en una factura dañe su reputación profesional"
"Quiere sentirse competente y en control de sus finanzas"

VE — extraer de:
- Qué alternativas usa actualmente
- Cómo lo hacen sus colegas
- Qué ofertas del mercado ve
- Qué tendencias observa en su industria

Ejemplo:
"Ve que sus colegas todavía usan Excel y lo considera aceptable"
"Ve software de facturación en anuncios pero no confía en cambiar"

ESCUCHA — extraer de:
- Qué le dicen sus pares / jefe / clientes sobre el problema
- Qué influencers o medios consume en este tema
- Qué le recomiendan cuando tiene el problema

Ejemplo:
"Su contador le dice que debería automatizar esto"
"Sus clientes le piden facturas electrónicas cada vez más"

DICE Y HACE — extraer de observación directa:
- Cómo describe el problema en público
- Qué hace realmente (vs qué dice que hace)
- Sus workarounds y hábitos

Ejemplo:
"Dice que 'no le importa el tiempo que tarda' pero en la sesión se
vio frustrado revisando 3 herramientas distintas"

DOLORES — sintetizar de todo lo anterior:
- Sus mayores miedos relacionados con el problema
- Sus obstáculos actuales
- Los riesgos que percibe

GANANCIAS — sintetizar:
- Qué considera éxito en este contexto
- Qué desearía que fuera diferente
- Los beneficios que realmente valora
```

---

## Plantilla portable — Mapa de Empatía (ASCII)

```
Sin imagen externa, generar este bloque ASCII directamente en markdown
(no depender de herramientas de visualización externas al editor):

┌─────────────────────────────────────────────────────────────┐
│                    MAPA DE EMPATÍA                          │
│                 [Nombre del Arquetipo]                      │
├────────────────────────┬────────────────────────────────────┤
│   PIENSA Y SIENTE      │         VE                        │
│   ──────────────       │         ──                        │
│   • preocupación 1     │  • alternativas actuales          │
│   • aspiración 1       │  • entorno laboral/social         │
│   • lo que le importa  │  • mercado y competidores         │
│                        │                                    │
├────────────────────────┼────────────────────────────────────┤
│       ESCUCHA          │      DICE Y HACE                  │
│       ───────          │      ───────────                  │
│   • mensajes de pares  │  • actitud pública                │
│   • influencias        │  • comportamiento real             │
│   • consejos que recibe│  • workarounds observados         │
│                        │                                    │
├────────────────────────┴────────────────────────────────────┤
│          DOLORES              │         GANANCIAS           │
│          ───────              │         ────────            │
│  😰 frustración principal     │  ⭐ qué considera éxito     │
│  😰 miedo concreto            │  ⭐ beneficio real buscado  │
│  😰 obstáculo actual          │  ⭐ deseo profundo          │
└─────────────────────────────────────────────────────────────┘

Uso: "genera el mapa de empatía para [descripción del usuario/contexto]"
→ completar los cuadrantes basándose en el contexto descrito.
También sirve una tabla markdown de 4 columnas si se prefiere formato compacto.
```

---

## User Journey Map — El Camino Completo

```
El Journey Map responde: ¿qué vive el usuario DE PRINCIPIO A FIN?
No solo cuando usa el producto. Todo el contexto alrededor.

Anatomía del Journey Map:

ETAPAS      [Descubre] → [Evalúa] → [Onboarding] → [Uso Regular] → [Problema] → [Renovación]
            ────────────────────────────────────────────────────────────────────────────────

ACCIONES    Qué hace el usuario en cada etapa
            "Ve un anuncio" / "Compara precios" / "Crea cuenta" / "Genera factura" etc.

TOUCHPOINTS Dónde interactúa con el producto/marca
            Web, app, email, soporte, factura, etc.

EMOCIONES   Cómo se siente en cada etapa (curva de emoción)
            😊 → 😐 → 😤 → 😤 → 😊 → 😊
            (alta en descubrimiento, baja en onboarding, sube al lograr la tarea)

PENSAMIENTOS Qué piensa en cada etapa (citas del research)
             "¿Será confiable?" / "Esto tarda mucho" / "¡Por fin funciona!"

OPORTUNIDADES Las fricciones identificadas → dónde puede el diseño mejorar
              → Simplificar onboarding (aquí baja la emoción)
              → Confirmar visualmente que la tarea se completó

RESPONSABLE  Qué equipo es dueño de cada touchpoint
             (Marketing / Producto / Soporte / Tech)
```

---

## Construir el Journey con y sin Research

```
CON research (ideal):
- Las acciones son comportamientos observados/mencionados
- Las emociones tienen citas textuales
- Los touchpoints son los que el usuario realmente usa
- Las oportunidades emergen de los dolores identificados

SIN research (mínimo para arrancar):
- Construir el journey como hipótesis
- Marcar explícitamente qué es suposición vs qué es conocido
- Usar como guía de qué validar primero
- Actualizar cuando llegue el research

Journey como hipótesis → siempre mejor que no tener ninguno
Journey como hipótesis presentado como verdad → peligroso

Formato para marcar suposiciones:
[ASUMIDO] El usuario busca en Google antes de entrar al sitio
[VALIDADO] El usuario abandona en el paso de configuración de cuenta
           (fuente: analytics, abandono 68% en ese paso)
```

---

## Tipos de Journey Maps

```
Current State Journey:
→ Documenta CÓMO ES el proceso hoy (con todo su dolor)
→ Base para identificar oportunidades de mejora
→ Útil para stakeholders que no ven los problemas del usuario

Future State Journey:
→ Documenta CÓMO DEBERÍA SER después del diseño
→ Guía la dirección del proyecto
→ Alinea al equipo sobre qué experiencia se está creando

Service Blueprint:
→ Extiende el Journey con la capa de back-stage
→ ¿Qué hace el equipo de soporte/operaciones en cada touchpoint?
→ Útil cuando el UX depende de procesos internos de la empresa

Day in the Life:
→ Journey más amplio: todo el día del usuario
→ El producto es solo una parte de su jornada
→ Revela contexto y competidores indirectos por el tiempo del usuario
```

---

## Plantilla portable — Journey Map sin imagen externa (ASCII)

```
Cuando no hay referencia, generar este esquema ASCII en markdown
(alternativa: Mermaid `journey` o tabla markdown etapa × dimensión):

Estructura:

ETAPAS ──────────────────────────────────────────────────────►
       [E1]        [E2]        [E3]        [E4]        [E5]

EMOCIONES  😊          😐          😤          😤          😊
(curva)    ↑           →           ↓           ↓           ↑

ACCIONES   • acción    • acción    • acción    • acción    • acción
           • acción    • acción    • acción                • acción

PENSA-     "cita"      "cita"      "cita"      "cita"      "cita"
MIENTOS

OPORTU-    ─────────── ─────────── [FRICCIÓN]  [FRICCIÓN]  ───────
NIDADES                            → oportunidad → oportunidad

Inferir etapas, emociones y oportunidades desde la descripción
del producto y usuario. Marcar todo [HIPÓTESIS] hasta tener research.

Uso: "genera el journey map para [producto + contexto de usuario]"
```
