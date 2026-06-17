# Pull Requests y Code Review

## PR Template — Estructura Completa

```markdown
<!-- .github/pull_request_template.md -->

## Descripción

<!-- Qué hace este PR y por qué es necesario. No el cómo (eso está en el código). -->

Closes #<!-- número de issue -->

## Tipo de cambio

- [ ] 🐛 Bug fix (cambio que arregla un issue sin breaking changes)
- [ ] ✨ Nueva funcionalidad (cambio que agrega funcionalidad sin breaking changes)
- [ ] 💥 Breaking change (fix o feature que cambia comportamiento existente)
- [ ] 📚 Documentación
- [ ] ♻️ Refactoring (sin cambio de funcionalidad)
- [ ] ⚡ Performance
- [ ] 🔒 Security fix
- [ ] 🧪 Tests

## Cambios realizados

<!-- Lista concisa de los cambios más importantes -->
- 
- 
- 

## Cómo testear

<!-- Pasos para verificar que el cambio funciona correctamente -->

1. 
2. 
3. 

**Resultado esperado:**

## Screenshots / Videos (si aplica)

<!-- Para cambios de UI, incluir antes/después -->

## Checklist

- [ ] Self-review realizado
- [ ] Tests agregados para los cambios
- [ ] Tests pasando localmente (`npm test` / `php artisan test`)
- [ ] Sin console.log / dd() / dump() olvidados
- [ ] Sin archivos no relacionados con el cambio
- [ ] Documentación actualizada si aplica
- [ ] Variables de entorno nuevas añadidas a `.env.example`
- [ ] Migraciones de BD son reversibles

## Notas para el reviewer

<!-- Algo específico en lo que quieres feedback, decisiones de diseño tomadas, alternativas consideradas -->
```

---

## PR Tamaño — El Factor Más Importante

```
El tamaño del PR es el factor #1 en la calidad del code review.

PRs grandes (> 500 líneas):
❌ Reviewers los pasan rápido o los aprueban sin leer
❌ Difíciles de entender sin contexto completo
❌ Más probabilidad de bugs pasados por alto
❌ Bloquean el flujo del equipo si tardan en reviewearse

PRs pequeños (< 200 líneas de código de producción):
✅ Reviews rápidos y de calidad (< 30 min)
✅ Fácil de identificar exactamente qué hace cada cambio
✅ Fácil de revertir si hay problemas
✅ Merged rápido → feedback rápido

Límites recomendados:
- Ideal: < 200 líneas (sin contar tests)
- Aceptable: < 400 líneas
- Necesita justificación: 400-600 líneas
- Dividir obligatoriamente: > 600 líneas

Cómo dividir PRs grandes:
1. Separar refactoring de nueva funcionalidad
2. Infraestructura / tipos / interfaces en un PR → implementación en otro
3. Backend en un PR → frontend en otro (con feature flag si necesario)
4. Módulo A en PR1 → Módulo B en PR2
```

---

## Code Review — Cultura y Proceso

### Para el Autor del PR

```
Antes de pedir review:
□ Self-review: leer el propio diff como si fuera de otra persona
□ Agregar comentarios en el código sobre decisiones no obvias
□ Asegurar que los tests pasan
□ La descripción del PR explica el QUÉ y el POR QUÉ

Durante el review:
□ Responder todos los comentarios (resolve o reply)
□ No tomar los comentarios como ataques personales
□ Si no estás de acuerdo, explicar el razonamiento
□ Aplicar cambios en commits adicionales (no force push hasta que se apruebe)
□ Notificar cuando los cambios están listos para re-review
```

### Para el Reviewer

```
Qué revisar (de mayor a menor importancia):
1. Lógica y correctitud — ¿hace lo que dice hacer?
2. Seguridad — ¿hay vulnerabilidades introducidas?
3. Performance — ¿hay N+1 queries o loops costosos?
4. Tests — ¿los casos edge están cubiertos?
5. Diseño — ¿es la abstracción correcta?
6. Legibilidad — ¿se puede entender fácilmente?
7. Estilo — solo si la guía de estilo lo requiere (dejar para el linter)

Cómo escribir buen feedback:
✅ Preguntas: "¿Consideraste usar X aquí?"
✅ Sugerencias: "Sugiero extraer esto en un método separado porque..."
✅ Reconocimiento: "Buen enfoque para manejar el error aquí 👍"
✅ Niveles claros: [nit], [suggestion], [must], [question]

❌ Imperativo sin explicación: "Cambia esto"
❌ Ambigüedad: "Esto no me parece bien"
❌ Perfeccionismo: bloquear un PR por cosas menores estilísticas
❌ Aprobar sin leer: "LGTM" en un PR de 800 líneas en 2 minutos
```

---

## Labels y Tipos de Comentarios

```markdown
<!-- Tipos de comentarios para claridad -->

[nit] Variables con nombres más descriptivos serían útiles aquí.
<!-- No bloquea el PR — mejora menor de estilo -->

[suggestion] Podrías extraer esta lógica a un método `calculateDiscount()`.
<!-- No bloquea pero sería una mejora -->

[question] ¿Por qué se eligió este approach en lugar de usar el ORM directamente?
<!-- Solicitud de explicación, no bloquea -->

[must] Este loop puede resultar en N+1 queries. Usar eager loading.
<!-- Bloquea el PR — debe resolverse -->

[blocking] Esta query sin índice causará degradación de performance en producción.
<!-- Bloquea el PR — crítico -->

[praise] Excelente manejo del edge case de concurrencia aquí 🎯
<!-- Reconocimiento positivo -->
```

---

## Branch Protection Rules — GitHub

```yaml
# Settings → Branches → Add rule → Branch name pattern: main

Reglas recomendadas:
✓ Require a pull request before merging
  ✓ Required approvals: 1 (equipos pequeños) / 2 (equipos medianos)
  ✓ Dismiss stale pull request approvals when new commits are pushed
  ✓ Require review from Code Owners (si hay CODEOWNERS)

✓ Require status checks to pass before merging
  ✓ Require branches to be up to date before merging
  Status checks: [lint, test, build] — los del CI

✓ Require conversation resolution before merging
  (todos los comentarios resueltos antes de mergear)

✓ Require signed commits (para repos de alta seguridad)

✓ Do not allow bypassing the above settings
  (ni los admins pueden saltarse las reglas)

# CODEOWNERS — revisores automáticos por área
# .github/CODEOWNERS
/src/features/payments/    @payments-team
/src/features/auth/        @security-team
/infrastructure/           @devops-team
*.md                       @docs-team
```

---

## Automatización de Review con GitHub Actions

```yaml
# .github/workflows/pr-checks.yml
name: PR Checks

on:
  pull_request:
    types: [opened, synchronize, reopened]

jobs:
  # Verificar tamaño del PR
  size-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0

      - name: Check PR size
        run: |
          ADDITIONS=$(git diff --stat origin/main...HEAD | tail -1 | awk '{print $4}')
          if [ "$ADDITIONS" -gt 600 ]; then
            echo "❌ PR too large: $ADDITIONS additions. Consider breaking it down."
            exit 1
          elif [ "$ADDITIONS" -gt 400 ]; then
            echo "⚠️ PR is large: $ADDITIONS additions. Reviewers may need extra time."
          fi

  # Verificar que el PR tiene descripción
  description-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/github-script@v7
        with:
          script: |
            const { body } = context.payload.pull_request;
            if (!body || body.trim().length < 50) {
              core.setFailed('PR description is too short. Please describe the changes.');
            }

  # Auto-assign reviewers basado en archivos cambiados
  auto-assign:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: kentaro-m/auto-assign-action@v1.2.6
        with:
          configuration-path: .github/auto-assign.yml

# .github/auto-assign.yml
reviewers:
  - teammate1
  - teammate2
numberOfReviewers: 1

addReviewers: true
addAssignees: author
```
