---
name: team-onboarding
description: >
  Guía el proceso para que un developer nuevo sea productivo en menos de una
  semana: setup del ambiente, documentación del proyecto, accesos y
  credenciales, primer PR guiado y transferencia de conocimiento del equipo.
  Usar cuando el usuario mencione: onboarding, dev nuevo, incorporar al equipo,
  nuevo miembro, primer día, setup del proyecto, cómo documentamos el proyecto,
  o cuando diga "entra un developer nuevo", "cómo lo pongo al tanto",
  "necesito que alguien más pueda trabajar en esto", "documentar para el
  equipo", o cualquier variante sobre incorporar personas al proyecto.
---

# Team Onboarding Skill

Un dev nuevo que tarda 2 semanas en ser productivo no es un problema del dev.
Es un problema del proceso de onboarding del estudio.

El objetivo: **primer PR real en menos de 5 días hábiles**.
No un PR de "arreglé un typo" — un PR que resuelve algo real.

**README del proyecto → `references/project-readme.md`**
**Checklist de primer día → `references/day-one.md`**
**Transferencia de conocimiento → `references/knowledge-transfer.md`**
**Primer PR guiado → `references/first-pr.md`**
**Recursos UX/UI gratuitos (onboarding diseño) → `../ui-web-modern/references/learning-sources.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — gates setup (`php artisan test`, `npm run build`).
2. `AGENTS.md`, `context.md` o README existente según project-memory.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** README/onboarding pack en repo; gaps de documentación → project-memory + `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory y docs existentes antes de generar README duplicado.
1. **Ejecutar el pre-onboarding autónomo** (sección "Tareas autónomas del
   agente", abajo): analizar el repo, detectar gaps del README y verificar el
   setup. Gate: el checklist verificable de pre-onboarding está completo con
   evidencia (comandos ejecutados y su exit code).
2. **Generar o actualizar el README** con la plantilla de
   `references/project-readme.md`, rellenando los campos desde el código real
   (stack desde manifests, scripts desde `package.json`/`composer.json`,
   estructura desde el árbol de directorios).
3. **Proponer el primer task del dev nuevo** (criterios en
   `references/first-pr.md`): acotado, real, con criterios de aceptación,
   que toque el flujo principal sin ser crítico.
4. **Preparar el día 1** con `references/day-one.md`: accesos, ambiente,
   tour de código (máx 2 horas), task asignado.
5. **Acompañar los días 2-5**: dev buddy + check-in diario de 15 min;
   estructura del primer PR y review educativo → `references/first-pr.md`.
6. **Transferir el conocimiento que no está en el código** (decisiones,
   contexto del cliente, deuda técnica) → `references/knowledge-transfer.md`.
7. **Cerrar con retrospectiva**: actualizar el README con lo que faltó,
   ejecutar `## Validación` y registrar gaps en `LEARNINGS.md`.

---

## Tareas autónomas del agente

Trabajo de pre-onboarding que el agente ejecuta solo, sin el dev nuevo:

```
1. ANALIZAR EL REPO Y GENERAR GAPS DEL README
   → ¿Setup documentado?  diff entre los pasos del README y lo que
     realmente requiere el repo (manifests, servicios en docker-compose,
     migrations, seeds)
   → ¿.env.example completo?  comparar las claves usadas en el código
     (grep de env()/process.env) contra .env.example — listar faltantes
   → ¿Scripts de instalación funcionan?  ejecutar los comandos del README
     en orden y anotar dónde fallan
   → Output: lista de gaps con severidad (bloquea setup / confunde / cosmético)

2. PROPONER EL PRIMER TASK DEL DEV NUEVO
   → Buscar candidatos: TODOs en código, issues abiertos etiquetados
     "good first issue", funciones sin tests, gaps del README detectados
   → Criterio: acotado a 2-3 días, toca el flujo principal, no es crítico,
     tiene criterios de aceptación verificables

3. GENERAR BORRADOR DE README
   → Usar la plantilla de references/project-readme.md
   → Rellenar desde el código real: stack y versiones desde composer.json /
     package.json, comandos desde scripts, estructura desde el árbol de
     directorios, servicios externos desde .env.example y config/
   → Marcar con [PENDIENTE: humano] lo que no se puede inferir del código
     (URLs de producción, responsables de accesos, contexto del cliente)
```

### Checklist verificable de pre-onboarding (ejecutable solo por el agente)

```bash
# 1. El repo clona y el manifest es válido
git status                                          # exit 0
cat package.json | jq . >/dev/null 2>&1 || composer validate --no-check-all

# 2. .env.example existe y cubre las claves usadas en el código
ls .env.example
# Node: comparar process.env.X del código contra .env.example
# Laravel: comparar env('X') de config/ contra .env.example

# 3. Las dependencias instalan limpio
npm ci 2>/dev/null || npm install      # exit 0
composer install --no-interaction     # exit 0 (si aplica)

# 4. El proyecto arranca o al menos buildea
npm run build 2>/dev/null || php artisan about     # exit 0

# 5. La suite de tests corre (aunque haya fallos, debe ejecutar)
npm test 2>/dev/null || php artisan test           # ejecuta y reporta

# 6. El README tiene las secciones mínimas
grep -E "^#+ .*(Setup|Instalación|Install)" README.md
grep -E "^#+ .*(Stack|Tecnolog)" README.md

# Cada ítem con su exit code va al informe de gaps del Entregable
```

---

## Por Qué el Onboarding Falla en los Estudios

```
Causa 1 — Sin documentación del proyecto
  "El código está en GitHub, léelo y pregunta lo que no entiendas."
  → El dev nuevo pasa días en contexto que el resto del equipo tardó meses en construir
  → Cada pregunta interrumpe a otro dev
  → El conocimiento sigue en la cabeza del dev líder

Causa 2 — Demasiada información de golpe
  El dev líder dedica medio día a explicar todo
  → El dev nuevo recibe más de lo que puede procesar
  → A las 2 horas ya no está absorbiendo
  → Al día siguiente no recuerda la mitad

Causa 3 — Sin tarea real desde el primer día
  "Los primeros días solo observa y lee el código"
  → Sin tarea = sin foco = aprendizaje difuso
  → El contexto se aprende haciendo, no leyendo

Causa 4 — El ambiente de desarrollo nunca está documentado
  "El setup es raro, hay que hacer X y luego Y, pero si tienes Z hay que..."
  → El setup tarda un día entero en funcionar
  → Cada dev nuevo repite el proceso desde cero
  → Nadie documentó los workarounds porque "ya lo sabemos todos"

La solución no es contratar mejores devs.
Es tener un proceso que funciona independientemente del dev.
```

---

## El Objetivo: Primer PR en 5 Días

```
Día 1 → Ambiente funcionando, proyecto claro, primer task asignado
Día 2 → Entendiendo el código del área del task
Día 3 → Primera implementación (puede ser parcial)
Día 4 → PR abierto con código funcional
Día 5 → PR revisado y mergeado (o en proceso de review)

Este timeline es alcanzable con:
→ README completo que responde las preguntas del día 1
→ Setup guide que funciona sin ayuda
→ Task bien definido con criterios de aceptación claros
→ Un dev del equipo disponible para preguntas (no a tiempo completo)
→ Code review como parte del proceso de aprendizaje
```

---

## Checklist de Onboarding Completo

```
ANTES de que llegue el dev nuevo:
□ Pre-onboarding autónomo del agente ejecutado (gaps + README + task)
□ README del proyecto actualizado y completo
□ Setup guide testeado en una máquina limpia
□ Accesos y credenciales preparados
□ Primer task definido con criterios de aceptación
□ Dev buddy asignado (no el dev líder — alguien más disponible)

DÍA 1 (ambiente y contexto):
□ Accesos entregados y funcionando
□ Ambiente local corriendo
□ Tour del código con el dev líder (max 2 horas)
□ Primer task explicado y asignado

DÍAS 2-3 (primer task):
□ El dev nuevo trabaja en el task
□ Dev buddy disponible para preguntas (no interrumpir proactivamente)
□ Check-in diario de 15 minutos para desbloquearlo

DÍAS 4-5 (primer PR):
□ PR abierto
□ Code review con feedback constructivo y educativo
□ Merge o plan claro para el merge

SEMANA 2:
□ El dev nuevo puede tomar tasks sin guía constante
□ Retrospectiva de onboarding: qué faltó en el proceso
□ Actualizar el README con lo que faltaba
```

---

## Defaults si falta contexto

Si el usuario no especifica, asumir Y DECLARAR (máx. 1 pregunta solo si es
bloqueante, p. ej. no hay acceso al repo a documentar):

- **Alcance**: el repo actual; si hay varios, empezar por el del producto principal.
- **Perfil del dev nuevo**: mid-level con experiencia en el stack — el README
  no asume conocimiento del proyecto pero sí del framework.
- **Timeline**: primer PR real en ≤ 5 días hábiles.
- **Primer task**: el mejor candidato del análisis autónomo (acotado, real,
  no crítico), pendiente de confirmación del lead.
- **Credenciales**: nunca en el README — solo dónde solicitarlas.

---

## Ejemplo input → output

**Input:** "Preparar onboarding para dev React/Laravel en SocialPulse."

**Output:** README actualizado desde manifests; primer task "añadir test Feature en módulo Dashboard"; checklist día 1 con accesos; gate `php artisan test` + `npm run build` exit 0 en máquina limpia documentado.

---

## Validación

| Gate | Comando | Criterio |
|------|---------|----------|
| Dependencias | `composer install` + `npm ci` | exit 0 |
| Tests | `php artisan test` | exit 0 |
| Build frontend | `npm run build` | exit 0 |
| README | secciones mínimas de `references/project-readme.md` | completas o gaps listados |
| Primer task | criterios en `references/first-pr.md` | acotado, real, no crítico |

---

## Entregable

Paquete de pre-onboarding:

```markdown
# Pre-Onboarding — <proyecto> — YYYY-MM-DD

## Informe de gaps del README
| # | Gap | Severidad | Evidencia (comando + exit) |
|---|---|---|---|
| 1 | .env.example sin SENTRY_DSN | Bloquea setup | grep en config/ vs .env.example |

## Checklist verificable de pre-onboarding
- [x/✗] Repo clona y manifest válido (exit N)
- [x/✗] .env.example completo
- [x/✗] Dependencias instalan limpio (exit N)
- [x/✗] Proyecto arranca/buildea (exit N)
- [x/✗] Tests ejecutan (exit N)
- [x/✗] README con secciones mínimas

## Borrador de README
- <ruta del borrador generado> — campos [PENDIENTE: humano]: ...

## Primer task propuesto
- Título, descripción, criterios de aceptación, estimación (2-3 días)
```

---

## Skills relacionadas

- `git-workflow` — convenciones que el dev nuevo debe conocer.
- `software-project-analysis` — el análisis técnico que alimenta el README.
- `devops-base` — la infraestructura que el dev nuevo necesita entender.
- `sprint-planning` — el proceso ágil al que el dev nuevo se integra.
- `propuestas-contratos` — contexto del proyecto y del cliente (no aplica al
  dev nuevo directamente, pero sí al contexto que se le transfiere).

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
