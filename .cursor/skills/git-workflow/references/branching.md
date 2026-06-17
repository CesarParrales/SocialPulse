# Branching Strategies

## Trunk-Based Development — Para CI/CD Real

```
Concepto central: main es la única rama de larga vida.
Todas las demás viven máximo 1-2 días.

main ───────────────────────────────────────────────────►
   │       │              │          │
   └─ A ──┘    └─── B ───┘ └── C ──┘
   (horas)     (< 1 día)    (< 2 días)

Feature flags: para código en main que no debe estar activo aún
```

```typescript
// Feature flag — código en main, activación controlada
const FEATURES = {
  newCheckout: process.env.FEATURE_NEW_CHECKOUT === 'true',
  aiSearch:    process.env.FEATURE_AI_SEARCH === 'true',
};

// En el código
function renderCheckout() {
  if (FEATURES.newCheckout) {
    return <NewCheckoutFlow />;
  }
  return <LegacyCheckout />;
}

// En .env.production — activar cuando esté listo
FEATURE_NEW_CHECKOUT=true
```

```bash
# Flujo diario con TBD

# 1. Actualizar main
git checkout main
git pull origin main

# 2. Crear rama corta
git checkout -b feat/add-coupon-validation

# 3. Trabajar en pequeños incrementos
git add -p  # agregar cambios por hunks, no archivos completos
git commit -m "feat(orders): validate coupon before applying discount"

# 4. Actualizar con main frecuentemente (evitar divergencia)
git fetch origin
git rebase origin/main

# 5. Push y abrir PR inmediatamente
git push -u origin feat/add-coupon-validation
# Abrir PR → review → merge el mismo día
```

---

## GitHub Flow — Detalles de Implementación

```bash
# Naming conventions
feat/user-auth-with-google           # feature nueva
fix/order-total-calculation          # bug fix
hotfix/payment-gateway-timeout       # urgente en producción
chore/update-dependencies            # mantenimiento
docs/api-authentication              # documentación
refactor/order-service-extract       # refactoring
test/add-cart-unit-tests             # tests

# Flujo completo
git checkout main && git pull
git checkout -b feat/order-cancellation

# ... desarrollo ...

# Antes de abrir PR: rebase para historial limpio
git fetch origin
git rebase origin/main               # mover commits encima de main actual
# Si hay conflictos: resolverlos, git rebase --continue

# Verificar que los tests pasan
npm test / php artisan test

# Push con force-with-lease (seguro) si hubo rebase
git push --force-with-lease origin feat/order-cancellation

# Abrir PR en GitHub
gh pr create --title "feat(orders): add order cancellation" \
             --body "Closes #123" \
             --reviewer teammate1,teammate2
```

---

## GitFlow — Para Apps con Ciclos de Release

```
main ────●────────────────────●────────────────────►
         │                    │
develop ─●──────────────●────●──────────────────────►
         │              │
         └─ feat/A ────┘ └─ feat/B ─────────────────►
                          (mergen a develop)

release ──────────────────────────────► (merge a main + develop)
hotfix ──────────────────────────────── (merge a main + develop)
```

```bash
# Setup con git-flow tool
brew install git-flow-avh

git flow init  # configura las ramas

# Feature
git flow feature start user-authentication
# ... desarrollo ...
git flow feature finish user-authentication  # merge a develop

# Release (cuando develop está listo)
git flow release start 1.2.0
# ... solo bug fixes, no features nuevas ...
git flow release finish 1.2.0  # merge a main y develop, tag en main

# Hotfix (bug crítico en producción)
git flow hotfix start fix-payment-bug
# ... fix ...
git flow hotfix finish fix-payment-bug  # merge a main y develop, tag
```

---

## Estrategia de Merge — Rebase vs Merge vs Squash

```bash
# Opción 1: Merge commit (conserva historial completo)
git merge feat/feature-branch
# Resultado: historial con todos los commits de la branch + commit de merge

# Opción 2: Squash merge (un commit limpio por feature)
git merge --squash feat/feature-branch
git commit -m "feat(orders): add coupon validation"
# Resultado: 1 commit en main = fácil de revertir

# Opción 3: Rebase (historial lineal, sin merge commits)
git rebase main feat/feature-branch
git checkout main && git merge feat/feature-branch --ff-only
# Resultado: historial lineal, sin commits de merge

# Recomendación por caso:
# - Squash: para features completas, historial limpio en main
# - Rebase: para mantener historial de la branch pero lineal
# - Merge commit: cuando quieres trazar exactamente cuándo se integró algo

# Configurar en GitHub:
# Settings → Merge button → Allow squash merging ✓ (recomendado)
# Settings → Merge button → Allow rebase merging ✓
# Settings → Merge button → Allow merge commits ✗ (opcional, evita commits de merge)
```

---

## Manejo de Hotfixes

```bash
# Bug crítico en producción — flujo urgente

# 1. Crear branch desde main (producción)
git checkout main
git pull origin main
git checkout -b hotfix/critical-payment-bug

# 2. Arreglar y testear
git add -p
git commit -m "fix(payments): prevent double charge on network timeout"

# 3. Abrir PR con prioridad alta
git push origin hotfix/critical-payment-bug
gh pr create --title "HOTFIX: prevent double charge" \
             --label "hotfix,priority:critical"

# 4. Después del merge y deploy → también aplicar a develop
git checkout develop
git merge main  # o cherry-pick del commit específico
```

---

## Monorepo vs Multirepo

```
Monorepo (un repo para todo):
  ✅ Cambios atómicos entre apps (backend + frontend en un PR)
  ✅ Compartir código fácilmente (packages compartidos)
  ✅ Un solo punto de CI/CD
  ✅ Visibilidad total del proyecto
  ❌ Repo puede volverse pesado con historial largo
  ❌ CI debe ser inteligente para no correr todo en cada cambio
  Herramientas: Turborepo, Nx, Lerna

Multirepo (repo por servicio/app):
  ✅ Equipos completamente independientes
  ✅ CI/CD simple y aislado
  ✅ Permisos granulares por repo
  ❌ Cambios cross-repo son complejos de coordinar
  ❌ Duplicación de configuración (CI, linting, etc.)

Cuándo elegir:
- Startup / equipo pequeño → Monorepo (simplicidad)
- Equipos independientes grandes → Multirepo
- Microservicios maduros → Multirepo o Monorepo con Nx
```
