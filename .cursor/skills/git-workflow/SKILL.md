---
name: git-workflow
description: >
  Guía los flujos de trabajo con Git: branching strategy, conventional commits,
  PR templates y code review. Usar cuando el usuario mencione git, branches,
  commits, pull requests, code review, merge, rebase, gitflow, trunk-based
  development, conventional commits, semantic versioning, changelogs, o cuando
  diga "cómo organizo mis branches", "cómo escribo buenos commits", "cómo hago
  code review", "cómo manejo releases", "qué estrategia de branching uso",
  o cualquier variante relacionada con flujos de trabajo con Git.
---

# Git Workflow Skill

Flujos de trabajo con Git para equipos de desarrollo profesional.

**Branching strategies → `references/branching.md`**
**Conventional Commits y Semantic Versioning → `references/commits.md`**
**Pull Requests y Code Review → `references/pull-requests.md`**
**Comandos Git avanzados → `references/git-commands.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — rama principal, estrategia de branching acordada.
2. `.github/` o convenciones CI del repo si project-memory las referencia.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** convenciones acordadas → project-memory o `docs/`; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory; respetar rama principal y merge policy documentados.
1. **Diagnosticar el estado actual**: ejecutar `git status`, `git branch -a` y
   `git log --oneline -10`. Detectar: ¿hay convención de ramas?, ¿commits
   conventional?, ¿branch protection? Gate: el repo existe y `git status` sale
   con exit 0.
2. **Elegir la estrategia de branching** con la tabla de este archivo y el
   default declarado abajo. Detalle de cada estrategia →
   `references/branching.md`.
3. **Configurar el repo (local, no global)**: aplicar la configuración
   recomendada con `git config` SIN `--global` (ver sección Configuración).
   Gate: `git config --list --local` muestra los valores aplicados.
4. **Establecer convenciones de commits**: formato conventional commits y
   versionado → `references/commits.md`. Gate verificable:
   `git log -1 --pretty=%s | grep -E '^(feat|fix|chore|docs|refactor|test|perf)(\(.+\))?:'`.
5. **Flujo de PR end-to-end**: seguir el Checklist de PR (abajo) y
   `references/pull-requests.md` para plantilla y code review.
6. **Operaciones avanzadas** (rebase interactivo, cherry-pick, bisect,
   recuperación) solo cuando se necesiten → `references/git-commands.md`.
7. **Validación y cierre** — ejecutar `## Validación`; entregar resumen; registrar gaps en `LEARNINGS.md`.

---

## Elegir la Estrategia Correcta

```
Trunk-Based Development:
  ✅ Ramas cortas — máx 1-2 días de vida
  ✅ Feature flags para código incompleto
  ✅ Integración continua real — main siempre deployable
  ✅ Menos conflictos de merge
  ✅ Feedback rápido
  ❌ Requiere disciplina — código incompleto en main con feature flags
  Cuándo: equipos > 3 devs, CI/CD maduro, deploy frecuente

GitHub Flow (simple, efectivo — DEFAULT):
  ✅ Solo 2 tipos de rama: main + feature branches
  ✅ Simple de entender y seguir
  ✅ PR review antes de merge a main
  ✅ Deploy desde main
  Cuándo: startups, equipos pequeños, SaaS con deploy continuo

GitFlow (para releases programadas):
  ✅ Estructura clara para software con versiones
  ✅ Hotfixes sin afectar desarrollo en curso
  ❌ Complejo — muchas ramas simultáneas
  ❌ Long-lived branches = conflictos frecuentes
  Cuándo: apps móviles, librerías, software con ciclos de release

Forking Workflow:
  ✅ Para open source — colaboradores externos sin acceso directo al repo
  Cuándo: proyectos open source
```

---

## GitHub Flow — El Más Práctico

```
main ────────────────────────────────────────────────►
       │              │              │
       └─ feat/login ─┘   └─ fix/auth-bug ─┘
         (PR + review)      (PR + review)

Reglas:
1. main siempre está deployable a producción
2. Toda feature/fix en su propia rama desde main
3. Hacer PR cuando está listo para review
4. Deploy a staging desde la branch para smoke tests
5. Merge a main solo con approval
6. Deploy a producción inmediatamente después del merge

Nombres de ramas:
feat/nombre-descriptivo      → nueva funcionalidad
fix/nombre-del-bug           → corrección de bug
hotfix/nombre-critico        → fix urgente en producción
chore/nombre-tarea           → tareas técnicas (deps, config)
docs/nombre-documentacion    → solo documentación
refactor/nombre-modulo       → refactoring sin cambio de comportamiento
test/nombre-tests            → agregar o arreglar tests
```

---

## Configuración del Repo — Local Primero

**Regla: la configuración recomendada se aplica al repo (`git config` sin
`--global`). NUNCA tocar la config global del usuario sin que lo pida
explícitamente.**

```bash
# ── CONFIG LOCAL DEL REPO (recomendada — segura de aplicar) ──
# Se ejecuta dentro del repo; solo afecta a este proyecto

# Identidad (si difiere de la global, p. ej. email de trabajo)
git config user.name "Tu Nombre"
git config user.email "tu@email.com"

# Comportamiento de pull — rebase en lugar de merge
git config pull.rebase true

# Comportamiento de push
git config push.default current   # push a la rama del mismo nombre

# Verificar lo aplicado
git config --list --local
```

```bash
# ── CONFIG GLOBAL (SOLO si el usuario la pide explícitamente) ──
# Afecta a TODOS los repos de la máquina del usuario

git config --global core.editor "code --wait"   # VS Code
git config --global core.editor "vim"            # Vim

# Alias útiles
git config --global alias.st "status -sb"
git config --global alias.lg "log --oneline --decorate --graph --all"
git config --global alias.last "log -1 HEAD --stat"
git config --global alias.undo "reset HEAD~1 --mixed"
git config --global alias.aliases "config --get-regexp alias"

# Diff tool
git config --global diff.tool vscode
git config --global difftool.vscode.cmd "code --wait --diff \$LOCAL \$REMOTE"

# Line endings: Windows
git config --global core.autocrlf true
# Line endings: Mac/Linux
git config --global core.autocrlf input
```

---

## .gitignore Global — Para tu Máquina

(También es config global del usuario — aplicar solo si lo pide.)

```bash
# ~/.gitignore_global — ignorar en todos los repos
git config --global core.excludesfile ~/.gitignore_global
```

```gitignore
# macOS
.DS_Store
.AppleDouble
.LSOverride
._*

# Windows
Thumbs.db
ehthumbs.db
Desktop.ini

# Linux
*~

# Editores
.vscode/
.idea/
*.swp
*.swo
*.sublime-workspace

# Herramientas de desarrollo
.env.local
*.log
```

---

## Checklist de PR — End-to-End

```bash
# 1. Branch desde main actualizada, con convención de nombre
git checkout main && git pull
git checkout -b feat/nombre-descriptivo

# 2. Commits conventional durante el desarrollo
git add <archivos>
git commit -m "feat(modulo): descripción en imperativo"
# Verificar formato:
git log -1 --pretty=%s | grep -E '^(feat|fix|chore|docs|refactor|test|perf)(\(.+\))?:'

# 3. Antes de abrir el PR
#    - Tests pasando localmente
#    - Self-review: git diff main...HEAD
#    - Sin archivos de debug, logs, binarios o cambios no relacionados

# 4. Abrir el PR con plantilla de body
git push -u origin HEAD
gh pr create --title "feat(modulo): descripción" --body "$(cat <<'EOF'
## Qué
<qué cambia este PR>

## Por qué
<contexto / issue que resuelve>

## Cómo probarlo
1. ...

## Checklist
- [ ] Tests pasando
- [ ] Self-review hecho
- [ ] Sin cambios no relacionados
EOF
)"

# 5. Tras el approval → merge (squash si los commits intermedios no aportan)
gh pr merge --squash
```

---

## Checklist de Git para Equipos

### Setup del Proyecto
- [ ] Branch protection en `main` configurada
- [ ] CI obligatorio antes de merge
- [ ] Al menos 1 approval requerido para merge
- [ ] PR template configurado
- [ ] `.gitignore` completo en el repo
- [ ] Conventional commits enforced con commitlint

### Por Pull Request
- [ ] Branch desde `main` actualizada
- [ ] Commits con mensajes descriptivos (conventional)
- [ ] Tests pasando localmente
- [ ] Self-review antes de pedir review
- [ ] Descripción en el PR explicando el qué y el por qué
- [ ] Sin archivos de debug, logs, o cambios no relacionados
- [ ] No incluir binarios o archivos generados

### En Code Review
- [ ] Revisar la lógica, no solo el estilo
- [ ] Preguntar en lugar de dictar
- [ ] Aprobar cuando está listo (no esperar perfección)
- [ ] Responder feedback antes de 24h

---

## Defaults si falta contexto

Si el usuario no especifica, asumir Y DECLARAR (máx. 1 pregunta solo si es
bloqueante):

- **Estrategia de branching**: GitHub Flow para equipos ≤ 5 con CI;
  GitFlow SOLO con releases versionadas o apps móviles;
  Trunk-Based solo si el equipo ya tiene feature flags y CI/CD maduro.
- **Configuración**: local del repo (`git config` sin `--global`);
  NUNCA tocar la config global del usuario sin pedirlo.
- **Commits**: conventional commits en inglés, imperativo.
- **Merge**: squash merge para feature branches (historia de main limpia).
- **Rama principal**: `main`.

---

## Ejemplo input → output

**Input:** "Configurar flujo de PRs para equipo de 4 con CI en GitHub."

**Output:** GitHub Flow documentado; convención `feat/*`/`fix/*`; squash merge; `git config --local pull.rebase true`; checklist branch protection pendiente en remoto. Gate: último commit cumple conventional commits.

---

## Validación

| Gate | Comando | Criterio |
|------|---------|----------|
| Repo accesible | `git status` | exit 0 |
| Config local | `git config --list --local` | valores acordados presentes |
| Commits | `git log -1 --pretty=%s \| grep -E '^(feat\|fix\|chore\|docs\|refactor\|test\|perf)(\\(.+\\))?:'` | match |
| PR checklist | sección Checklist de PR en este SKILL.md | ítems aplicables ✓ |

---

## Entregable

Resumen del flujo configurado para el repo:

```markdown
# Git Workflow — <repo> — YYYY-MM-DD

## Estrategia: <GitHub Flow | GitFlow | Trunk-Based> (motivo: ...)

## Convenciones
- Ramas: feat/* fix/* hotfix/* chore/* docs/* refactor/* test/*
- Commits: conventional commits (verificado con grep en git log)
- Merge: <squash | merge commit | rebase>

## Configuración aplicada (local del repo)
| Clave | Valor |
|---|---|
| pull.rebase | true |
| push.default | current |

## Pendiente de configurar en el remoto
- [ ] Branch protection en main
- [ ] CI obligatorio + 1 approval
- [ ] PR template en .github/
```

---

## Skills relacionadas

- `team-onboarding` — las convenciones que el dev nuevo debe conocer.
- `supply-chain-security` — lockfiles en git y branch protection.
- `devops-base` — CI que corre en cada PR.
- `sprint-planning` — el flujo de trabajo donde viven los PRs.

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
