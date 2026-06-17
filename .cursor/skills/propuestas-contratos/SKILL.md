---
name: propuestas-contratos
description: >
  Guía la creación de propuestas comerciales, contratos de desarrollo, definición
  de scope y gestión de scope creep. Usar cuando el usuario necesite armar una
  propuesta para un cliente, definir el alcance de un proyecto, redactar términos
  contractuales, manejar solicitudes de cambio fuera del scope, o cuando diga
  "cómo presento esto al cliente", "cómo defino el alcance", "el cliente pide
  cosas que no estaban", "cómo cobro los cambios", "cómo protejo el proyecto",
  "necesito el contrato", "el cliente no aprueba", o cualquier variante
  relacionada con la relación comercial en proyectos de desarrollo.
---

# Propuestas y Contratos Skill

Una propuesta mal hecha gana proyectos que pierden dinero.
Un contrato mal redactado convierte un cliente difícil en un problema sin salida.

El objetivo no es protegerte del cliente — es alinear expectativas
desde el primer día para que ambas partes terminen contentas.

**Anatomía de una propuesta ganadora → `references/propuesta.md`**
**Definición de scope — criterios de aceptación → `references/scope.md`**
**Contratos — cláusulas esenciales → `references/contrato.md`**
**Scope creep — gestión de cambios → `references/scope-creep.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — punteros a PRD, alcance P0, decisiones comerciales.
2. `socialpulse-prd.md` o brief del cliente si project-memory lo indica.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** propuesta en `docs/` o archivo acordado; exclusiones/CR process → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory y PRD; no contradecir alcance firmado sin CR.
1. **Clasificar el formato** de propuesta (corta / estándar / detallada / LOI)
   según la tabla "Cuándo Usar Cada Formato de Propuesta" de este documento.
   Si el usuario no da monto ni duración, asumir formato estándar y declararlo.
2. **Leer `references/propuesta.md`** — estructura de las 8 secciones y ejemplos
   por sección. Si el scope no tiene criterios de aceptación verificables,
   leer también `references/scope.md`.
3. **Recopilar los datos del cliente y del proyecto.** Para cada campo faltante,
   aplicar los Defaults (abajo) y declararlos en el entregable; preguntar solo
   si el faltante es bloqueante (máx. 1 pregunta).
4. **Generar las 8 secciones** de la propuesta usando el esqueleto de
   `## Entregable`. Incluir siempre exclusiones explícitas y criterios de
   aceptación por módulo.
5. **Cubrir lo contractual si aplica:** términos y cláusulas →
   `references/contrato.md`; proceso de change request →
   `references/scope-creep.md`.
6. **Validar con el "Checklist antes de Enviar una Propuesta"** de este
   documento. Gate verificable: la propuesta generada no debe contener
   marcadores pendientes — `grep -nE 'TODO|PENDIENTE|\[completar\]' propuesta.md`
   debe devolver vacío.
7. **Listar las suposiciones aplicadas** al final del entregable, con la
   recomendación de validarlas con el cliente antes de firmar.
8. **Validación y cierre** — ejecutar `## Validación`; registrar gaps en `LEARNINGS.md`.

---

## Defaults si falta contexto

Si el usuario no provee estos datos, asumir y **declarar en el entregable**
(no preguntar, salvo que sea bloqueante):

| Campo faltante | Default asumido |
|----------------|-----------------|
| Formato de propuesta | Estándar (5-10 páginas) |
| Moneda | USD, precios sin impuestos |
| Validez de la propuesta | 30 días |
| Anticipo | 30-40% al firmar, no se inicia sin anticipo |
| Plan de pagos | Por hitos vinculados a fases |
| Rondas de revisión incluidas | 2 por entregable |
| SLA de feedback del cliente | 5 días hábiles para aprobar |
| Exclusiones | Migración de datos, soporte post-lanzamiento y capacitación extendida fuera de alcance salvo indicación contraria |
| Nombre/datos del cliente | Placeholders `[Cliente]` marcados para completar |

Pregunta bloqueante única permitida: si no hay ninguna descripción del
proyecto ni del problema a resolver.

---

## El Error Más Costoso de un Estudio

```
No es el bug en producción.
No es el deploy que falló.

Es el proyecto que se estimó en 3 meses y tomó 6,
porque el alcance nunca quedó claro
y el cliente fue agregando "cositas" que "no deberían demorar nada".

Causa raíz casi siempre:
→ La propuesta describió el proyecto en términos de features, no de criterios
→ El contrato no tenía un proceso de control de cambios
→ El equipo dijo "sí" a cambios sin documentarlos ni cobrarlos
→ El cliente nunca firmó un alcance detallado — solo el precio total

La propuesta y el contrato son la arquitectura del proyecto comercial.
Igual que nadie construye sin planos, nadie debería desarrollar sin scope.
```

---

## Los 3 Documentos de un Proyecto Bien Estructurado

```
1. PROPUESTA COMERCIAL
   → Para el cliente antes de firmar
   → Define: qué se hace, cómo se hace, cuánto cuesta, cuándo entrega
   → Tono: vendedor + técnico. Confianza + claridad.
   → Firmado = aceptación del alcance y el precio

2. CONTRATO / ACUERDO DE SERVICIOS
   → El documento legal
   → Define: derechos, obligaciones, pagos, IP, garantías, terminación
   → Puede ser el mismo documento que la propuesta o separado
   → Firmado por ambas partes antes de iniciar

3. CHANGE REQUEST / SOLICITUD DE CAMBIO
   → Para cada solicitud fuera del scope original
   → Define: qué cambia, cuánto cuesta, cuánto demora
   → Aprobado = autorización para ejecutar y cobrar
   → El proceso de CR es la diferencia entre un estudio rentable y uno que regala trabajo

Sin estos 3 documentos → el proyecto depende de la buena voluntad de ambas partes.
Con los 3 → hay un proceso claro cuando hay desacuerdo.
```

---

## Cuándo Usar Cada Formato de Propuesta

```
PROPUESTA CORTA (1-3 páginas):
  → Proyectos < $5,000 o < 4 semanas
  → Cliente recurrente con historial
  → Scope muy bien definido de entrada
  → Riesgo bajo

PROPUESTA ESTÁNDAR (5-10 páginas):
  → Proyectos medianos, primer proyecto con el cliente
  → Scope con algunas partes por definir en discovery
  → Múltiples módulos o fases

PROPUESTA DETALLADA (10-20+ páginas):
  → Proyectos > $20,000 o > 3 meses
  → Cliente corporativo con proceso de aprobación formal
  → Múltiples stakeholders que evalúan la propuesta
  → Competencia con otras agencias/estudios
  → Alto riesgo técnico o de integración

LETTER OF INTENT (LOI) + CONTRATO DESPUÉS:
  → Proyectos muy grandes donde el scope requiere discovery primero
  → El LOI cubre la fase de discovery con un costo fijo
  → El contrato principal se firma al terminar el discovery
  → Protege al estudio de hacer discovery gratis
```

---

## Ejemplo input → output

**Input:** "Propuesta estándar para dashboard analytics SaaS, 3 meses."

**Output:** 8 secciones con módulos P0, exclusiones (mobile app, BI custom), criterios de aceptación por módulo, hitos de pago 30/40/30, suposiciones declaradas. Gate: `grep -nE 'TODO|PENDIENTE|\[completar\]' propuesta.md` vacío.

---

## Validación

| Gate | Acción | Criterio |
|------|--------|----------|
| Placeholders | `grep -nE 'TODO|PENDIENTE|\[completar\]' <archivo>` | vacío |
| Exclusiones | sección 3-4 | lista explícita |
| Criterios | por módulo | verificables |
| Checklist | sección Checklist antes de Enviar | ítems aplicables ✓ |
| Suposiciones | bloque final | listadas |

---

## Entregable

Esqueleto mínimo de la propuesta estándar (detalle por sección en
`references/propuesta.md`):

```markdown
# Propuesta — [Proyecto] para [Cliente]

## 1. Resumen Ejecutivo
[Problema + solución + impacto + alcance + plazo, en un párrafo]

## 2. Entendimiento del Problema
[Contexto del negocio, impacto actual, stakeholders, restricciones, objetivos de éxito]

## 3. Solución Propuesta
[Descripción funcional, módulos, stack justificado, integraciones]
**Esta propuesta NO incluye:** [exclusiones explícitas]

## 4. Alcance Detallado
[Por módulo: descripción, funcionalidades incluidas, NO incluido, criterio de aceptación]

## 5. Plan de Proyecto
[Fases con entregable por fase + hitos de pago vinculados]

## 6. Inversión
[Total, desglose por fase, plan de pagos, validez, notas de impuestos/licencias/CRs]

## 7. Por Qué Nosotros
[Experiencia relevante para ESTE proyecto, equipo asignado, forma de trabajo]

## 8. Próximos Pasos
[Acción concreta con fechas: revisión → llamada → firma + anticipo → kick-off]

---
## Suposiciones aplicadas
[Lista de defaults asumidos por falta de información — validar antes de firmar]
```

---

## Checklist antes de Enviar una Propuesta

```
El proyecto:
□ Tengo claro el problema que el cliente quiere resolver
□ Entiendo quién va a usar el sistema y cómo
□ Identifiqué las integraciones con sistemas externos
□ Estimé con buffer (no el escenario optimista)
□ Definí qué NO está incluido (exclusiones explícitas)

La propuesta:
□ Tiene criterios de aceptación por entregable (no solo descripción)
□ Tiene un proceso de control de cambios descrito
□ Especifica cuántas rondas de revisión están incluidas
□ Especifica el SLA de feedback del cliente (ej: 5 días hábiles para aprobar)
□ Tiene cláusula de pausas del proyecto (qué pasa si el cliente desaparece)
□ El precio incluye el costo real del proyecto (no el precio para ganar)

Antes de firmar:
□ El cliente leyó y tiene preguntas → buen signo
□ El cliente firmó (no solo dijo "ok por mail")
□ Recibí el anticipo (no se inicia sin anticipo)
```

---

## Skills relacionadas

| Skill | Relación |
|-------|----------|
| `software-project-analysis` | El análisis técnico alimenta la propuesta |
| `project-pricing` | El precio es parte de la propuesta |
| `sprint-planning` | El alcance definido aquí se ejecuta en sprints |

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
