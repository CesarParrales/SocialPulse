---
name: problem-solving-dev
description: >
  Guía la resolución de problemas técnicos reales usando repositorios,
  documentación oficial, GitHub issues/PRs, changelogs y fuentes
  especializadas. Usar cuando el usuario enfrente un error sin solución clara,
  un comportamiento inesperado, una incompatibilidad entre versiones, una
  deprecación que rompió algo, o cuando la documentación oficial no coincide
  con lo que está pasando en el código. También usar cuando diga "no entiendo
  por qué falla", "la documentación dice X pero pasa Y", "esto funcionaba
  antes", "encontré soluciones contradictorias", "no sé si es un bug o un
  error mío", o cualquier variante donde el problema real supera lo que dice
  el manual.
---

# Problem Solving Dev Skill

La documentación oficial describe cómo funciona el sistema cuando todo va bien.
Los problemas reales ocurren en los márgenes — versiones específicas, combinaciones
de dependencias, configuraciones particulares, comportamientos no documentados.

Esta skill es el protocolo para navegar esos márgenes con criterio.

**Jerarquía de fuentes y cómo leerlas → `references/sources.md`**
**GitHub como fuente de verdad → `references/github-investigation.md`**
**Debugging sistemático → `references/debugging.md`**
**Versiones, deprecaciones y breaking changes → `references/versions.md`**
**Comunidades y Stack Overflow con criterio → `references/communities.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — versiones de stack y comandos de verificación.
2. `context.md` si project-memory apunta allí (PHP, Node, frameworks).
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** Investigation Report en `docs/` o chat; workaround recurrente → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer versiones/gates documentados antes de buscar docs genéricas.
1. **Verificar el entorno ANTES de buscar docs** (obligatorio — el 80% de las
   soluciones fallan por aplicarse a otra versión):
   - `npm list <paquete>` o `composer show <paquete>` → versión exacta instalada
   - Versión del framework: `php artisan --version`, `npx next --version`,
     `cat package.json | grep '"<framework>"'`, etc.
   - Runtime: `node --version` / `php --version`
   - Gate: tener anotadas las versiones exactas antes de continuar.
2. **Aislar el problema** (5-10 min): ¿reproducible de forma mínima?, ¿ocurre
   sin el resto del stack?, ¿qué cambió entre "funcionaba" y "no funciona"?
   (`git log --oneline --since=...` ayuda). Técnicas → `references/debugging.md`.
3. **Clasificar el error y elegir la fuente default** con el árbol de decisión
   (abajo). Si involucra upgrade/deprecación → `references/versions.md`.
4. **Buscar en orden de confianza**: GitHub issues cerrados → Discussions →
   CHANGELOG de la versión instalada → docs de ESA versión → Stack Overflow
   filtrado por versión/fecha. Cómo buscar en GitHub →
   `references/github-investigation.md`; criterios de confianza →
   `references/sources.md` y `references/communities.md`.
5. **Verificar la solución antes de aplicarla**: ¿es para la versión instalada?,
   ¿mismo contexto?, ¿hay respuestas más recientes que la contradigan?
6. **Aplicar y confirmar con un comando verificable**: el fix debe poder
   demostrarse (test que pasa, comando con exit 0, request que responde).
   Gate: el comando de verificación del Investigation Report pasa.
7. **Emitir el Investigation Report** (ver `## Entregable`), ejecutar `## Validación`
   y registrar hallazgos en `LEARNINGS.md`. Si nada funcionó → crear reproducción
   mínima (formato correcto para pedir ayuda con precisión).

---

## Árbol de Decisión — Tipo de Error → Fuente Default

```
¿Qué tipo de error es?

ERROR DE RUNTIME (excepción, crash, 500)
  → DEFAULT: GitHub issues CERRADOS del repo de la librería/framework
  → Buscar el mensaje de error exacto entre comillas
  → Logs completos primero, no el snippet

ERROR DE TIPOS / API (método no existe, firma cambió, type error)
  → DEFAULT: docs oficiales de la VERSIÓN INSTALADA (no la última)
  → Contrastar con el CHANGELOG entre la versión documentada y la instalada

COMPORTAMIENTO INESPERADO (sin error visible)
  → CHANGELOG + issues del repo ("not a bug, expected behavior")

INCOMPATIBILIDAD ENTRE PAQUETES
  → peer dependencies + lockfile (npm ls <pkg> / composer why <pkg>)
  → references/versions.md

DEPRECACIÓN / UPGRADE QUE ROMPIÓ ALGO
  → Migration guide oficial de la versión exacta
  → references/versions.md

ERROR DE COMPILACIÓN / SYNTAX
  → Docs de la versión exacta del compilador/transpiler instalado
```

---

## El Problema con Copiar Soluciones

```
El 80% del tiempo en Stack Overflow:
→ La solución tiene 5 años y la API cambió
→ Funciona para una versión diferente a la instalada
→ Resuelve un síntoma distinto que parece igual
→ Fue aceptada porque "funcionó para el que preguntó" con otro contexto
→ Tiene 200 votos porque era la única respuesta en 2019

El 80% del tiempo en la documentación oficial:
→ Muestra la versión más reciente, no la que está instalada
→ Asume configuración limpia sin el resto del stack
→ El ejemplo funciona en aislamiento pero no con las otras dependencias
→ El cambio que rompió todo está enterrado en el CHANGELOG bajo "minor"

La solución correcta casi siempre está en:
→ El issue cerrado de GitHub que nadie googleó
→ El PR que introdujo el cambio con la explicación del por qué
→ El CHANGELOG de la versión exacta instalada
→ El comentario en el issue que no fue la respuesta aceptada
→ El código fuente de la librería misma
```

---

## Mentalidad: El Manual Describe el Camino Normal

```
La documentación oficial:
→ Describe happy path con configuración estándar
→ Está escrita para la última versión publicada
→ Asume que usas solo ese paquete, no el ecosistema completo
→ No documenta los bugs conocidos (eso está en los issues)
→ No documenta el comportamiento en edge cases

El código fuente:
→ Es la documentación definitiva — describe exactamente qué hace
→ Los tests del proyecto revelan el comportamiento esperado
→ Los commits recientes revelan qué cambió y por qué

Los issues cerrados:
→ Son la memoria colectiva de todos los que tuvieron el mismo problema
→ Las respuestas "no es un bug, es comportamiento esperado" explican el diseño
→ Los "won't fix" explican las limitaciones intencionales
→ Los issues con muchos comentarios = problema común con soluciones reales

El ingenio humano:
→ Está en los workarounds documentados en los comentarios de los issues
→ En las discusiones de por qué se tomó una decisión de diseño
→ En los PRs rechazados que explican qué no se puede cambiar
→ En los forks que resolvieron lo que el repo principal no resolvió
```

---

## Cuándo Confiar en Cada Fuente

```
ALTA CONFIANZA:
✅ Documentación oficial de la versión exacta instalada
✅ GitHub Issues cerrados con "fixed" + número de versión del fix
✅ CHANGELOG con entrada específica para el comportamiento
✅ Tests del proyecto que cubren el caso
✅ Código fuente de la función que falla
✅ Respuesta del maintainer del proyecto

CONFIANZA MEDIA:
⚠️ Stack Overflow — requiere verificar versión y fecha
⚠️ Blog posts técnicos — requiere verificar fecha y versión
⚠️ Documentación oficial de versión diferente a la instalada
⚠️ Respuestas con muchos votos pero antiguas

BAJA CONFIANZA:
❌ Tutoriales sin fecha visible
❌ Soluciones de ChatGPT/Claude sin verificar (incluida esta skill)
❌ "Funcionó para mí" sin contexto de versión
❌ README de forks sin mantenimiento activo
❌ Respuestas de Stack Overflow sin votos en posts sin actividad reciente

TRAMPA COMÚN:
La respuesta aceptada en Stack Overflow no es siempre la correcta.
Los votos son del momento en que se publicó, no del estado actual.
Leer TODOS los comentarios y respuestas, no solo la aceptada.
```

---

## Defaults si falta contexto

Si el usuario no especifica, asumir Y DECLARAR (máx. 1 pregunta solo si es
bloqueante, p. ej. no comparte el mensaje de error completo):

- **Versiones**: las que reporten `npm list` / `composer show` del repo actual
  — nunca asumir "la última".
- **Fuente inicial**: según el árbol de decisión (runtime → issues cerrados;
  tipos/API → docs de la versión instalada).
- **Reproducibilidad**: intentar reproducción mínima local antes de atribuir
  el error a la librería.
- **Solución preferida**: la documentada para la versión instalada; actualizar
  la dependencia solo si el fix oficial lo requiere y se declara el cambio.

---

## Ejemplo input → output

**Input:** "Horizon jobs fallan con `Redis connection refused` tras deploy."

**Output:** Investigation Report — versiones Redis/Horizon anotadas; issue #N confirma `REDIS_URL` vs `REDIS_HOST`; fix en `.env` alineado con `context.md`; gate `php artisan horizon:status` exit 0.

---

## Validación

| Gate | Acción | Criterio |
|------|--------|----------|
| Versiones | `npm list` / `composer show` / `php artisan --version` | anotadas en el reporte |
| Fix verificable | comando/test del reporte | exit 0 / test pasa |
| Confianza | Investigation Report | ≥ Medio o riesgo declarado |
| Entregable | plantilla `## Entregable` | síntoma + evidencia + verificación |

---

## Entregable

Plantilla del **Investigation Report**:

```markdown
# Investigation Report — <error/problema> — YYYY-MM-DD

## Síntoma
- <mensaje de error exacto / comportamiento observado>
- Entorno: <paquete>@<versión instalada>, <framework>@<versión>, <runtime>@<versión>

## Hipótesis evaluadas
1. <hipótesis> → descartada/confirmada porque ...

## Evidencia por fuente
| Fuente | Hallazgo | Confianza |
|---|---|---|
| GitHub issue #N (cerrado) | ... | Alta |
| CHANGELOG vX.Y.Z | ... | Alta |
| Stack Overflow (fecha, versión) | ... | Media |

## Solución aplicada
- <cambio exacto + por qué corresponde a esta versión>

## Nivel de confianza: <Alto | Medio | Bajo> (motivo)

## Comando de verificación que confirma el fix
`<comando con exit 0 / test que pasa / request que responde>`
```

---

## Skills relacionadas

- `web-architecture` — contexto arquitectónico del sistema donde ocurre el problema.
- `laravel-backend` / `node-backend` — conocimiento del stack backend afectado.
- `react-patterns` — comportamiento esperado en el frontend React.

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
