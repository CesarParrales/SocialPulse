# Reporte de Auditoría — Estructura y Templates

## Principios del Buen Reporte

```
Un reporte de auditoría mal hecho:
→ Lista problemas sin prioridad → el equipo no sabe por dónde empezar
→ Critica sin contexto → parece ataque al trabajo del diseñador
→ Sin recomendaciones → el equipo sabe qué está mal pero no cómo arreglarlo
→ Sin estimación → el equipo no puede planificar

Un reporte de auditoría bien hecho:
→ Prioriza los problemas → el equipo ataca lo más impactante primero
→ Documenta el criterio violado → es objetivo, no subjetivo
→ Incluye recomendación específica → accionable
→ Agrupa problemas sistémicos → eficiente resolver juntos
→ Incluye lo que funciona bien → preservar lo bueno, no solo criticar
```

---

## Estructura del Reporte

```
SECCIÓN 1 — Resumen Ejecutivo (1 página)

Para decisores que no leerán todo el reporte.

Contenido:
→ Alcance de la auditoría (qué se evaluó y qué no)
→ Hallazgos cuantitativos: X críticos, Y serios, Z moderados
→ Top 3 problemas que requieren atención inmediata
→ Top 3 fortalezas del sistema actual (qué conservar)
→ Estimación de esfuerzo de mejora (días/semanas)
→ Recomendación: ¿rediseño puntual o design system?

SECCIÓN 2 — Metodología

Para equipos técnicos y de diseño.

Contenido:
→ Criterios usados (Nielsen, WCAG 2.1 AA, consistencia visual)
→ Scope: pantallas evaluadas, dispositivos, estados
→ Herramientas usadas (axe, contraste checker, etc.)
→ Escala de severidad usada

SECCIÓN 3 — Hallazgos por Eje

3A: Hallazgos de Usabilidad (Heurísticas de Nielsen)
3B: Hallazgos de Accesibilidad (WCAG)
3C: Hallazgos de Consistencia Visual

Cada hallazgo tiene:
→ ID único (ej: USA-001, ACC-001, VIS-001)
→ Descripción del problema
→ Criterio violado (heurística # / WCAG criterio)
→ Pantallas afectadas
→ Severidad (🔴🟠🟡🟢)
→ Evidencia (screenshot o descripción)
→ Recomendación específica

SECCIÓN 4 — Hallazgos Sistémicos

Problemas que aparecen en múltiples pantallas.
Son prioritarios porque una sola corrección impacta todo el sistema.

Contenido:
→ Lista de patrones problemáticos que se repiten
→ Cuántas pantallas afecta cada uno
→ Esfuerzo estimado para corregirlo sistémicamente

SECCIÓN 5 — Roadmap de Mejora

La priorización en el tiempo.

Corto plazo (sprint 1-2):
  → Los 🔴 CRÍTICOS
  → Los 🟠 SERIOS que afectan a más usuarios

Mediano plazo (sprint 3-6):
  → Los 🟠 SERIOS restantes
  → Los 🟡 MODERADOS de mayor impacto

Largo plazo (design system):
  → Los problemas sistémicos de consistencia
  → Los 🟡 MODERADOS restantes

SECCIÓN 6 — Fortalezas (lo que funciona bien)

Frecuentemente omitido. Siempre incluirlo:
→ Evita que el equipo descarte todo por miedo a romperse lo bueno
→ Sirve como punto de referencia para las correcciones
→ Reconoce el trabajo ya hecho
```

---

## Template de Hallazgo Individual

```
──────────────────────────────────────────────────────
ID:           USA-003
Categoría:    Usabilidad
Heurística:   H9 — Recuperación de errores
Severidad:    🟠 Serio
Pantallas:    Login, Registro, Recuperar contraseña

PROBLEMA:
El mensaje de error en el formulario de login solo dice "Credenciales incorrectas"
sin sugerir al usuario una acción de recuperación. El usuario que olvidó su
contraseña no tiene path claro hacia la solución.

IMPACTO:
→ Usuarios que olvidaron su contraseña no saben que hay recuperación de cuenta
→ Aumenta la tasa de abandono en el flujo de login
→ Aumenta tickets de soporte sobre acceso a la cuenta

RECOMENDACIÓN:
Cambiar el mensaje de error a:
"El email o contraseña son incorrectos.
¿Olvidaste tu contraseña? [link a recuperación]"

El link lleva directamente al flujo de recuperación con el email prellenado
si fue el campo de email el que falló.

ESFUERZO ESTIMADO: 2 horas de desarrollo
──────────────────────────────────────────────────────
```

---

## Template de Hallazgo Sistémico

```
──────────────────────────────────────────────────────
ID:           SIS-001
Tipo:         Sistémico — afecta todo el sistema
Categoría:    Consistencia Visual
Severidad:    🟠 Serio (por extensión y frecuencia)
Pantallas:    14 de 18 pantallas auditadas

PROBLEMA:
El sistema tiene 6 valores de border-radius distintos (0, 2, 4, 6, 8, 12px)
sin jerarquía semántica. Los botones varían entre 4px y 8px. Los cards
entre 6px y 12px. Las modales entre 0 y 8px.

IMPACTO:
→ El sistema se percibe como inconsistente e inacabado
→ El desarrollador tiene que verificar el diseño para cada componente
→ Sin un valor definido, cada nueva pantalla introduce un valor nuevo

RECOMENDACIÓN:
Definir 3 valores semánticos de border-radius:
  sm: 4px  → elementos pequeños (badges, inputs, botones)
  md: 8px  → elementos medianos (cards, tooltips, modales)
  lg: 16px → elementos grandes (panels, drawers)

Crear un token de design system y actualizar todos los componentes.
Los componentes con valor 2px, 6px y 12px migrar al más cercano.

ESFUERZO ESTIMADO:
  Definir tokens: 1h
  Actualizar componentes en Figma: 4h
  Implementar en código (CSS variables): 2h
  QA visual: 2h
  TOTAL: ~1 día de trabajo
──────────────────────────────────────────────────────
```

---

## Resumen Ejecutivo — Template

```
AUDITORÍA DE UI — [Nombre del Producto]
Fecha: [fecha]
Evaluador: [nombre]
Alcance: [descripción breve del alcance]

──────────────────
RESUMEN DE HALLAZGOS
──────────────────
🔴 Críticos:   X problemas  (requieren acción inmediata)
🟠 Serios:     X problemas  (próximo sprint)
🟡 Moderados:  X problemas  (backlog prioritario)
🟢 Menores:    X problemas  (sesión de polish)
Total hallazgos: X

──────────────────
TOP 3 PROBLEMAS INMEDIATOS
──────────────────
1. [ACC-001] Contraste insuficiente en texto de body — 12 pantallas afectadas
2. [USA-004] Sin confirmación antes de eliminar registros — flujo crítico
3. [SIS-002] 6 variantes del botón primario sin consistencia — todo el sistema

──────────────────
TOP 3 FORTALEZAS
──────────────────
1. El flujo de onboarding es claro y bien guiado
2. Los mensajes de error de formulario son descriptivos
3. La navegación principal es consistente y predecible

──────────────────
ESTIMACIÓN DE ESFUERZO
──────────────────
Quick wins (🔴 y 🟠 puntuales):   ~5 días de desarrollo
Mejoras sistémicas (🟠 sistémicos): ~10 días de diseño + desarrollo
Design System (base para futuro):  ~20 días de diseño + implementación

RECOMENDACIÓN:
Iniciar por los quick wins en el próximo sprint.
Paralelamente, iniciar el design system para resolver los sistémicos.
```

---

## Entregables portables — reporte sin imagen

```
Sin imagen o URL, generar en markdown (tablas, Mermaid; sin herramientas
externas al editor; severidades marcadas [NO VERIFICADO] si aplica):

Resumen de estado de la auditoría (tabla):
  → Distribución de severidades (conteo por 🔴🟠🟡🟢)
  → Lista de top hallazgos con severidad
  → Indicador de salud general (🔴/🟡/🟢)

Roadmap de mejoras (Mermaid gantt o tabla por trimestre):
  → Q1 / Q2 / Q3
  → Hallazgos distribuidos por urgencia y esfuerzo

Matriz de priorización impacto × esfuerzo (tabla 2×2):
  → Alto impacto + bajo esfuerzo: quick wins
  → Alto impacto + alto esfuerzo: proyectos mayores
  → Bajo impacto + bajo esfuerzo: fill-ins
  → Bajo impacto + alto esfuerzo: evitar

Uso:
"genera el dashboard de auditoría con [X críticos, Y serios, Z moderados]"
"genera la matriz de priorización para [lista de hallazgos]"
"genera el roadmap de mejoras para [descripción de los hallazgos]"
```
