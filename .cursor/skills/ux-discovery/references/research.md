# Research y Entrevistas de Usuario

## Tipos de Research — Cuándo Usar Cada Uno

```
CUALITATIVO (el porqué):
  Entrevistas en profundidad   → motivaciones, contexto, emociones
  Observación contextual       → comportamiento real en contexto real
  Diarios de usuario           → experiencias a lo largo del tiempo
  Focus groups                 → percepciones grupales (cuidado: groupthink)

CUANTITATIVO (el cuánto):
  Encuestas                    → validar hallazgos cualitativos a escala
  Analytics                    → qué hacen los usuarios (no por qué)
  A/B testing                  → comparar soluciones con datos reales
  Heatmaps / session recording → dónde hacen clic, dónde se pierden

REGLA:
  Empezar con cualitativo → entender el problema
  Continuar con cuantitativo → validar magnitud
  Sin cualitativo, el cuantitativo no explica el comportamiento
```

---

## Entrevistas en Profundidad — El Método Más Valioso

### Preparación

```
Reclutar correctamente:
- Usuarios reales del producto o potenciales usuarios
- Representativos del arquetipo objetivo (no los "power users" ni los amigos)
- 5-8 usuarios para hallazgos cualitativos (la ley de rendimiento decreciente)
- Incentivo si es necesario ($25-50 o equivalente en valor)
- 45-60 minutos por entrevista

Protocolo de entrevista:
1. Introducción y contexto (5 min)
   → explicar el objetivo, pedir permiso para grabar
2. Warmup (10 min)
   → preguntas sobre el usuario, su rol, rutina diaria
3. Exploración del problema (25-30 min)
   → el corazón de la entrevista
4. Proyección a futuro (5 min)
   → qué sería ideal para ellos
5. Cierre (5 min)
   → qué más quieren compartir, agradecimiento
```

### Preguntas Poderosas vs Preguntas Débiles

```
❌ PREGUNTAS DÉBILES (evitar):
"¿Te gustaría una función que...?"
→ La gente siempre dice sí a features gratis

"¿Cuántas veces usarías esto por semana?"
→ Las predicciones de comportamiento futuro son poco confiables

"¿Qué piensas de nuestro producto?"
→ Provoca respuestas condescendientes para no herir

"¿Por qué usas X?" (sin contexto)
→ Pregunta muy abierta que genera respuestas genéricas

✅ PREGUNTAS PODEROSAS:
"Cuéntame la última vez que intentaste [tarea]. ¿Cómo fue?"
→ Historia real, no hipotética

"¿Qué fue lo más frustrante de ese proceso?"
→ Emociones reales, no predicciones

"¿Qué hiciste cuando no pudiste completar [X]?"
→ Workarounds revelan el dolor real

"¿Cómo lo estabas haciendo antes? ¿Por qué cambiaste?"
→ Contexto de switching y motivaciones reales

"Muéstrame cómo lo haces normalmente."
→ Observación > descripción

"¿Qué significaría para ti si pudieras hacer esto fácilmente?"
→ El valor real del problema resuelto
```

### La Técnica de los 5 Porqués

```
Ejemplo en entrevista:

Usuario: "El proceso de facturación es complicado"
Investigador: ¿Qué es lo que lo hace complicado para ti?

Usuario: "Tengo que buscar los datos en varios sistemas"
Investigador: ¿Por qué tienes que buscarlos en varios sistemas?

Usuario: "Porque el CRM no está conectado con el sistema de inventario"
Investigador: ¿Y qué pasa cuando no están conectados?

Usuario: "Cometo errores y tengo que corregirlos después"
Investigador: ¿Qué pasa cuando hay que corregir un error?

Usuario: "El cliente se da cuenta y pierde confianza en nosotros"

INSIGHT REAL: El problema no es "facturación complicada"
Es: "los errores dañan la relación con el cliente"
La solución no es una UI más simple — es integración de datos
```

---

## Análisis de Datos Existentes (Research Secundario)

```
Antes de entrevistar usuarios, extraer insights de:

Analytics (Google Analytics, Mixpanel, Amplitude):
- ¿Dónde abandonan los usuarios el flujo?
- ¿Qué features más usan vs cuáles ignoran?
- ¿Qué dispositivos y contextos dominan?
- ¿Cuál es el tiempo de sesión por sección?

Tickets de soporte y feedback:
- Quejas más frecuentes → problemas de UX o de expectativas?
- Preguntas repetidas → gaps de onboarding o de información
- Términos que usan → vocabulario del usuario para los diseños

NPS / CSAT comentarios textuales:
- Promotores: qué los convirtió en fans
- Detractores: qué los frustró específicamente

Session recordings (Hotjar, FullStory):
- Dónde se confunden (clicks repetidos, rage clicks)
- Patrones de scroll
- Dónde abandonan formularios

REGLA: Los datos dicen QUÉ pasa. Las entrevistas dicen POR QUÉ.
Usar ambos. Nunca solo uno.
```

---

## Síntesis de Research — De Datos a Insights

```
Proceso de Affinity Mapping:

1. Capturar observaciones (no interpretaciones)
   "El usuario dijo que tiene que abrir 3 apps para completar el proceso"
   NO: "El usuario encuentra el proceso complicado" (interpretación)

2. Agrupar por tema (afinidad)
   Tema: "Fragmentación de información"
   Tema: "Falta de feedback del sistema"
   Tema: "Incertidumbre sobre el estado del proceso"

3. Identificar patrones (¿cuántos usuarios mencionaron lo mismo?)
   5/8 usuarios mencionaron fragmentación de información → INSIGHT

4. Formular insights (observación + implicación)
   "Los usuarios manejan información en múltiples herramientas desconectadas,
   lo que genera errores y pérdida de tiempo que impactan en la confianza del cliente"

5. Priorizar por frecuencia + impacto emocional
   Alta frecuencia + alto impacto emocional → resolver primero

Herramientas para síntesis:
- Post-its físicos (la mejor opción para equipos co-localizados)
- FigJam, Miro, MURAL (para equipos distribuidos)
- Notion o Airtable (para documentar formalmente)
```

---

## Entregables visuales portables — síntesis sin referencia externa

```
Cuando no hay imagen o URL de referencia, generar estos artefactos
directamente en markdown (no depender de herramientas de visualización
externas al editor):

"Genera un diagrama de síntesis de research para [producto/contexto]"
→ Diagrama Mermaid (graph/mindmap) con:
   - Clusters de insights agrupados por tema
   - Frecuencia anotada por cluster (n menciones)
   - Relaciones entre temas (aristas)
   - Top 3 insights destacados en negrita

"Genera una guía de entrevistas para [contexto]"
→ Tabla markdown con:
   - Flujo de la entrevista con timing por sección
   - Preguntas clave por sección
   - Señales de alerta a escuchar

Si el usuario describe el contexto del producto sin referencias,
generar el esquema markdown del research relevante marcando
las inferencias como [HIPÓTESIS].
```
