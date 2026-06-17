---
name: graphify-integration
description: >
  Integración opcional con Graphify para consultar un grafo de conocimiento del
  codebase en lugar de leer archivos masivos. Usar cuando el usuario pida graphify,
  explorar un repo grande o legacy, mapear dependencias, encontrar nodos centrales,
  o cuando project-memory tenga Graphify enabled. No usar en tareas triviales,
  greenfield pequeño, ni como sustituto de tests o project-memory.
---

# Graphify — Integración opcional

Graphify es **herramienta externa** (Python 3.10+, `uv tool install graphifyy`). Genera un grafo consultable del código y docs. **No forma parte del núcleo de Suite Dev Studio.**

## Cuándo usar / cuándo no

| Usar | No usar |
|------|---------|
| Repo grande, legacy, monorepo | Proyecto nuevo &lt; ~20 archivos relevantes |
| Exploración antes de refactor | Tarea acotada con archivos ya conocidos |
| Usuario pidió Graphify o `graphify: enabled` en project-memory | Cada conversación (fricción + tokens en reglas) |
| Entender dependencias transversales | Sustituir `php artisan test`, gates o PRD |

**No instalar** `graphify cursor install` con reglas `alwaysApply: true` si el proyecto ya usa `.cursor/rules/` densas — compite por atención. Preferir activación explícita.

## Protocolo de ejecución

1. **Comprobar** si Graphify está disponible: `command -v graphify` o `uv tool run graphify --help`.
2. **Comprobar** project-memory: si `Graphify: disabled` (default) y el usuario no pidió Graphify → **detener** y usar `problem-solving-dev` + grep/semantic search normal.
3. **Si el grafo no existe** en el proyecto: sugerir al usuario (una vez):
   ```bash
   uv tool install graphifyy
   graphify /ruta/al/proyecto
   ```
   Opcional: `graphify cursor install` solo si el usuario acepta la regla en `.cursor/rules/graphify.mdc`.
4. **Consultar** en lugar de leer docenas de archivos:
   ```bash
   graphify query "¿qué servicios llaman a X?"
   graphify query "dependencias del módulo Y"
   ```
5. **Cruzar** con fuentes de verdad del proyecto (`context.md`, ADRs) — el grafo puede inferir relaciones (`INFERRED`); marcar `[GRAFO-INFERIDO]` vs `[CÓDIGO-VERIFICADO]` si hay duda.
6. **Cerrar:** resumen en chat + si hubo decisión arquitectónica → `.cursor/project-memory.md`; no editar el grafo manualmente.

## Defaults si falta contexto

- Graphify no instalado → seguir `problem-solving-dev` sin mencionar Graphify de nuevo salvo que el usuario insista.
- Grafo desactualizado tras cambios grandes → sugerir re-ejecutar `graphify .` en el root del repo.
- Conflicto grafo vs PRD/context → **PRD/context gana**.

## Entregable

```markdown
## Exploración Graphify — [tema]

### Consultas ejecutadas
- `graphify query "..."` → hallazgo breve

### Nodos / relaciones relevantes
- ...

### Verificación cruzada
- [ ] Confirmado en código: `path/to/file.php`
- [ ] Alineado con context.md / ADR

### Siguiente paso
- ...
```

## Skills relacionadas

- `problem-solving-dev` — fallback sin Graphify
- `software-project-analysis` — análisis de alcance humano
- `web-architecture` — decisiones estructurales (no las infiere solo el grafo)
- `skill-evolution` — no aplica al grafo; sí a mejoras de esta skill

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
