# Comandos Git Avanzados

## Comandos del Día a Día

```bash
# Estado y diferencias
git status -sb                          # estado compacto
git diff                                # cambios no staged
git diff --staged                       # cambios staged
git diff HEAD~1                         # vs commit anterior
git diff main...feat/my-branch         # cambios en la branch vs main

# Log y historial
git log --oneline --decorate --graph    # árbol visual
git log -p --follow src/services/OrderService.php  # historial de un archivo
git log --author="Juan"                 # commits de un autor
git log --since="2024-01-01" --until="2024-01-31"  # rango de fechas
git log -S "calculateDiscount"          # commits que tocan ese string (pickaxe)
git log --grep="BREAKING"              # commits con texto en el mensaje

# Buscar
git grep "TODO"                         # buscar en archivos tracked
git grep "calculateDiscount" HEAD       # buscar en snapshot del HEAD
git bisect start                        # iniciar búsqueda binaria de bug
git bisect bad                          # commit actual tiene el bug
git bisect good v1.0.0                  # esta versión no tenía el bug
# Git hace checkout en el medio → probar → git bisect good/bad
# Hasta encontrar el commit que introdujo el bug
git bisect reset                        # terminar bisect
```

---

## Staging Granular

```bash
# Agregar cambios por hunk (sección del diff), no archivos completos
git add -p
# Opciones: y (agregar), n (skip), s (split), e (edit), q (quit)

# Ventaja: commits más pequeños y semánticamente coherentes
# ❌ MAL: git add . → mezcla cambios de auth con de orders
# ✅ BIEN: git add -p → solo los cambios de auth en este commit

# Unstage un archivo específico
git restore --staged src/services/OrderService.php

# Descartar cambios no staged
git restore src/services/OrderService.php

# Descartar TODOS los cambios no staged (¡cuidado!)
git restore .
```

---

## Stash — Guardar Trabajo Temporal

```bash
# Guardar trabajo en progreso
git stash push -m "WIP: order cancellation flow"

# Guardar incluyendo archivos nuevos (untracked)
git stash push -u -m "WIP: new payment module"

# Ver stashes
git stash list

# Recuperar el último stash
git stash pop

# Recuperar un stash específico
git stash apply stash@{2}

# Crear branch desde stash
git stash branch feat/order-cancellation stash@{0}

# Eliminar stash
git stash drop stash@{0}
git stash clear  # eliminar todos
```

---

## Rebase Interactivo — Reescribir Historial

```bash
# Reescribir últimos N commits (antes de push)
git rebase -i HEAD~3

# Editor abre con:
# pick abc1234 feat(auth): add Google login
# pick def5678 fix typo
# pick ghi9012 add tests

# Comandos disponibles:
# pick    → mantener commit como está
# reword  → mantener pero cambiar mensaje
# edit    → pausa para modificar el commit
# squash  → combinar con el anterior (mantiene mensajes)
# fixup   → combinar con el anterior (descarta el mensaje)
# drop    → eliminar el commit

# Ejemplo: squash los últimos 3 commits en uno limpio:
# fixup abc1234
# fixup def5678
# reword ghi9012 → cambiar el mensaje final

# ⚠️ NUNCA reescribir historial de commits ya pusheados y compartidos
# Solo antes del primer push, o en tu rama personal
```

---

## Cherry-pick — Tomar Commits Específicos

```bash
# Aplicar commit específico en la rama actual
git cherry-pick abc1234

# Cherry-pick de múltiples commits
git cherry-pick abc1234 def5678

# Cherry-pick de un rango
git cherry-pick abc1234^..def5678  # desde abc1234 (inclusive) hasta def5678

# Caso de uso: hotfix aplicado a main también necesita ir a develop
git checkout develop
git cherry-pick abc1234  # el commit del hotfix

# Sin crear commit (dejar los cambios staged)
git cherry-pick --no-commit abc1234
```

---

## Revert vs Reset

```bash
# git revert — SEGURO para ramas compartidas
# Crea un nuevo commit que deshace los cambios (historial preservado)
git revert abc1234          # revertir un commit
git revert HEAD~2..HEAD     # revertir los últimos 2 commits
git revert --no-commit abc1234 def5678  # revertir sin crear commit aún

# git reset — PELIGROSO para ramas compartidas (reescribe historial)
# Solo usar en tu rama local antes de push

# Soft — mover HEAD, dejar staged
git reset --soft HEAD~1     # deshacer último commit, cambios quedan staged

# Mixed (default) — mover HEAD, dejar unstaged
git reset HEAD~1            # deshacer último commit, cambios en working directory

# Hard — mover HEAD, descartar cambios
git reset --hard HEAD~1     # ¡CUIDADO! Cambios perdidos permanentemente

# Recuperar commits "perdidos" con reflog
git reflog                  # historial de dónde estuvo HEAD
git reset --hard HEAD@{5}   # volver a ese estado
```

---

## Submodules y Subtrees

```bash
# Submodule — referencia a otro repo en un commit específico
git submodule add https://github.com/org/shared-lib.git lib/shared
git submodule update --init --recursive  # después de clonar

# Subtree — copia del repo embebida (más simple que submodule)
git subtree add --prefix=lib/shared https://github.com/org/shared-lib.git main --squash
git subtree pull --prefix=lib/shared https://github.com/org/shared-lib.git main --squash

# Cuándo usar cada uno:
# Submodule: la dependencia se actualiza frecuentemente, quieres referencia a commit específico
# Subtree: la dependencia se actualiza raramente, quieres los archivos en el repo
```

---

## Hooks — Automatizar en Git

```bash
# .husky — hooks de Git para Node.js
npm install --save-dev husky
npx husky init

# pre-commit — correr antes de cada commit
# .husky/pre-commit
npm run lint
npm run type-check

# commit-msg — verificar el mensaje del commit
# .husky/commit-msg
npx commitlint --edit $1

# pre-push — correr antes de push
# .husky/pre-push
npm run test

# PHP — con captainhook/captainhook
composer require --dev captainhook/captainhook
vendor/bin/captainhook install
```

```json
// captainhook.json
{
  "pre-commit": {
    "enabled": true,
    "actions": [
      {
        "action": "\\CaptainHook\\App\\Hook\\PHP\\Action\\Linting",
        "options": { "config": "phpstan.neon" }
      }
    ]
  },
  "commit-msg": {
    "enabled": true,
    "actions": [
      {
        "action": "\\CaptainHook\\App\\Hook\\Message\\Action\\Regex",
        "options": {
          "regex": "/^(feat|fix|docs|style|refactor|test|chore|perf|ci|build)(\\(.+\\))?(!)?: .+/"
        }
      }
    ]
  }
}
```

---

## Recuperación de Emergencia

```bash
# Recuperar archivo eliminado
git checkout HEAD -- src/services/OrderService.php

# Recuperar rama eliminada (si fue reciente)
git reflog | grep "feat/deleted-branch"
git checkout -b feat/deleted-branch abc1234

# Recuperar commit perdido después de reset --hard
git reflog                    # encontrar el SHA del commit perdido
git cherry-pick abc1234       # recuperar el commit

# Ver qué hay en staging antes de hacer commit
git diff --staged --stat

# Abortar merge en progreso
git merge --abort

# Abortar rebase en progreso
git rebase --abort

# Ver qué archivos se modificaron entre dos tags
git diff v1.0.0 v1.1.0 --name-only

# Estadísticas de contribución
git shortlog -sn --all  # commits por autor
```
