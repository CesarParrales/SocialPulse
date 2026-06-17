# Auditoría de Seguridad — Workflow Completo

Workflow para auditar una aplicación existente de punta a punta y emitir un
informe accionable. Para secrets y variables de entorno ver `secrets-env.md`;
para detalle de cada vulnerabilidad ver `owasp.md`.

---

## Workflow de Auditoría

```
PASO 1 — Escanear el repo (automático)
  → npm audit --audit-level=high  /  composer audit
  → SAST (Semgrep) + escáner de secrets (gitleaks/trufflehog)
  → Gate: si el audit de dependencias sale con exit != 0 → hallazgo
    Critical/High automático, listar CVEs en el informe

PASO 2 — Checklist por categoría OWASP (manual, ver abajo)
  → Recorrer cada categoría con el checklist manual
  → Apoyarse en owasp.md para el detalle de cada vulnerabilidad

PASO 3 — Clasificar hallazgos
  → Cada hallazgo recibe ID, severidad y evidencia (ver criterios abajo)

PASO 4 — Emitir el informe
  → Usar la plantilla con IDs del final de este archivo
  → Critical/High bloquean deploy; Medium/Low van al backlog con fecha
```

---

## Paso 1 — Herramientas de Escaneo

### Auditoría de dependencias (obligatoria, primera siempre)

```bash
# Node
npm audit --audit-level=high
# Gate: exit code != 0 → BLOQUEAR y listar CVEs en el informe

# PHP
composer audit
composer audit --format=json | jq '.advisories'
# Gate: exit code != 0 → BLOQUEAR y listar advisories en el informe
```

### Herramientas de Análisis Estático

```bash
# PHP
composer require --dev enlightn/enlightn
php artisan enlightn  # 100+ checks de seguridad para Laravel

# PHP Stan + security rules
composer require --dev phpstan/phpstan psalm/plugin-security
vendor/bin/phpstan analyse

# JavaScript/TypeScript
npm install --save-dev eslint-plugin-security
# .eslintrc
{
  "plugins": ["security"],
  "extends": ["plugin:security/recommended"]
}

# Semgrep — análisis estático multi-lenguaje
pip install semgrep
semgrep --config=p/php          # reglas para PHP
semgrep --config=p/javascript   # reglas para JS
semgrep --config=p/typescript

# SAST en CI
- name: Run SAST
  run: semgrep --config=p/php --error --json > semgrep-results.json
```

### Escaneo de secrets en el historial

```bash
trufflehog git file:///path/to/repo
gitleaks detect --source . --verbose
# Cualquier secret detectado = hallazgo Critical (rotar antes de limpiar historial)
```

### Dependency Scanning en CI

```yaml
# .github/workflows/security.yml
name: Security Scan

on:
  push:
    branches: [main]
  schedule:
    - cron: '0 8 * * 1'  # Lunes 8am — escaneo semanal

jobs:
  audit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: npm audit
        run: npm audit --audit-level=high

      - name: OWASP Dependency Check
        uses: dependency-check/Dependency-Check_Action@main
        with:
          project: 'myapp'
          path: '.'
          format: 'HTML'

      - name: Semgrep SAST
        uses: semgrep/semgrep-action@v1
        with:
          config: >-
            p/typescript
            p/react
            p/owasp-top-ten

      - name: Scan for leaked secrets
        uses: trufflesecurity/trufflehog@main
        with:
          path: ./
          base: ${{ github.event.repository.default_branch }}
          head: HEAD
```

---

## Paso 2 — Checklist de Auditoría Manual (por categoría OWASP)

```
Revisión de código centrada en seguridad:

Autenticación y Autorización (A01/A07):
□ ¿Cada endpoint verifica autenticación?
□ ¿Cada endpoint verifica que el usuario tiene acceso al recurso?
□ ¿Se verifica ownership de recursos (IDOR)?
□ ¿Los admin endpoints están separados y protegidos?

Manejo de Input (A03 — Injection):
□ ¿Se valida en el servidor (no solo en frontend)?
□ ¿Todos los parámetros pasan por validación tipada?
□ ¿Los uploads verifican tipo MIME y tamaño?
□ ¿Los campos de texto largo tienen límite?

Queries de BD (A03):
□ ¿No hay SQL concatenado?
□ ¿El ORM o prepared statements en todo?
□ ¿Las queries están limitadas (no devuelven millones de rows)?

Output y Logs (A03 XSS / A09 Logging):
□ ¿Los templates escapan output (XSS)?
□ ¿Los logs no incluyen passwords/tokens/PII?
□ ¿Los errores de producción son genéricos para el usuario?

Secretos (A02/A05):
□ ¿No hay hardcoded secrets en el código?
□ ¿El .gitignore es correcto?
□ ¿Las API keys tienen los mínimos permisos necesarios?

Dependencias (A06):
□ ¿`npm audit` o `composer audit` pasa sin problemas críticos?
□ ¿Las dependencias están actualizadas?
□ ¿No hay paquetes abandonados con CVEs conocidos?

Configuración (A05):
□ ¿Headers de seguridad configurados? (ver headers-cors.md)
□ ¿CORS restrictivo?
□ ¿Modo debug desactivado en producción?
```

---

## Paso 3 — Clasificación de Hallazgos

```
CRITICAL — Explotable ahora, impacto total
  Ejemplos: SQLi confirmada, secret de producción en el repo, auth bypass,
            CVE crítico en dependencia con exploit público
  Acción: bloquear deploy, arreglar HOY, rotar credenciales si aplica

HIGH — Explotable con esfuerzo moderado, impacto alto
  Ejemplos: IDOR en recursos sensibles, XSS almacenado, CVE high en deps,
            falta de rate limiting en login
  Acción: bloquear deploy a producción, arreglar esta semana

MEDIUM — Requiere condiciones específicas o impacto parcial
  Ejemplos: CORS demasiado abierto, headers de seguridad ausentes,
            mensajes de error que filtran información
  Acción: backlog con fecha comprometida (≤ 1 sprint)

LOW — Defensa en profundidad, sin vector directo
  Ejemplos: versión de framework antigua sin CVE conocido aplicable,
            cookies sin atributos óptimos en rutas no sensibles
  Acción: backlog, agrupar en tarea de hardening
```

---

## Paso 4 — Plantilla de Informe de Auditoría

```markdown
# Informe de Auditoría de Seguridad — <proyecto>

- Fecha: YYYY-MM-DD
- Alcance: <repos/módulos auditados>
- Comandos ejecutados: npm audit --audit-level=high (exit N),
  composer audit (exit N), semgrep, gitleaks

## Resumen
| Severidad | Cantidad |
|---|---|
| Critical | n |
| High | n |
| Medium | n |
| Low | n |

Veredicto: <APTO PARA DEPLOY | BLOQUEADO (Critical/High abiertos)>

## Hallazgos

### [SEC-001] <título> — CRITICAL
- Categoría OWASP: A0X
- Ubicación: <archivo:línea o endpoint>
- Evidencia: <output del comando, snippet, CVE>
- Impacto: ...
- Remediación: ...

### [SEC-002] ...

## CVEs de dependencias (de npm audit / composer audit)
| CVE/Advisory | Paquete | Severidad | Fix disponible |
|---|---|---|---|
| ... | ... | ... | ... |
```
