---
name: supply-chain-security
description: >
  Guía la seguridad de supply chain para proyectos que usan npm, Composer/PHP
  y Laravel: auditoría de dependencias, detección de paquetes maliciosos,
  hardening de lockfiles y pipelines CI/CD. Alertas activas (revisar
  references/incidents-current.md): laravel-lang/* comprometidos mayo 2026,
  gusano npm Shai-Hulud, CVE-2026-40261 en Composer < 2.9.6. Usar cuando el
  usuario mencione: supply chain, dependencias maliciosas, npm audit, composer
  audit, paquetes comprometidos, lockfile, autoload.files sospechoso,
  postinstall scripts, o cuando diga "cómo protejo mis dependencias", "cómo sé
  si un paquete es seguro", "cómo verifico mi composer.lock", "qué es un ataque
  de supply chain", "cómo audito mis paquetes", o cuando se detecte código con
  dependencias sin pinear o scripts de instalación sin auditar.
---

# Supply Chain Security Skill

Un ataque de supply chain no explota tu código — explota el código que instalas.
El vector es la confianza implícita que depositas en las dependencias.

Esta skill cubre la amenaza desde todos los ángulos:
el paquete que instalas, el proceso que lo instala, y el pipeline que lo despliega.

**Incidentes activos (leer PRIMERO) → `references/incidents-current.md`**
**npm — Defensa y hardening → `references/npm-security.md`**
**Composer/PHP/Laravel — Defensa y hardening → `references/composer-security.md`**
**Auditoría de dependencias → `references/dependency-audit.md`**
**CI/CD hardening → `references/cicd-hardening.md`**
**Respuesta a incidente → `references/incident-response.md`**
**Script de auditoría autoload.files → `scripts/audit-autoload-files.php`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — gates CI, política de lockfiles.
2. `references/incidents-current.md` — **obligatorio** (incidentes activos).
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** informe PASS/FAIL en `docs/`; incidentes nuevos → proponer update de `incidents-current.md`; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory + `references/incidents-current.md` primero.
1. **Revisar incidentes activos**: leer `references/incidents-current.md`
   (cabecera `last_updated` — si supera 30 días, verificar vigencia). Si el
   proyecto usa un paquete listado → saltar directo a su sección de
   verificación y, si hay compromiso, a `references/incident-response.md`.
2. **Detectar lockfiles y ecosistemas**: `ls package-lock.json composer.lock
   pnpm-lock.yaml yarn.lock 2>/dev/null`. Gate: si un manifest existe sin su
   lockfile en git → hallazgo FAIL inmediato.
3. **Ejecutar el checklist de comandos** según ecosistema:
   - npm: `npm audit --audit-level=high` y `npm ls` de paquetes alertados
     (detalle en `references/npm-security.md`).
   - Composer: `composer --version` (≥ 2.9.6, ideal ≥ 2.10), `composer audit`,
     y `php scripts/audit-autoload-files.php` (copiar el script de `scripts/`
     de esta skill al proyecto; detalle en `references/composer-security.md`).
   - Gate por comando: exit != 0 → FAIL con la evidencia (CVEs, paquetes).
4. **Auditar el pipeline**: leer `references/cicd-hardening.md` y verificar:
   `npm ci`/`composer install` (no update) en CI, actions pineadas por SHA,
   audit como gate de PR.
5. **Profundizar si hay paquetes nuevos o sospechosos**: leer
   `references/dependency-audit.md` (criterios antes de instalar, SBOM,
   herramientas por ecosistema).
6. **Emitir el informe PASS/FAIL** con la plantilla de `## Entregable`,
   adjuntando el output real de cada comando como evidencia.
7. **Validación y cierre** — ejecutar `## Validación`; si incidente nuevo →
   proponer update de `incidents-current.md`; registrar gaps en `LEARNINGS.md`.

---

## Los 5 Vectores de Ataque Principales

```
VECTOR 1 — Credenciales de maintainer comprometidas (phishing)
  Resultado: nueva versión maliciosa de paquete legítimo popular
  Ecosistema: npm y Packagist
  Señal: versión publicada fuera del ciclo normal / sprint corto

VECTOR 2 — Git tag rewriting
  Resultado: tags existentes apuntan a commits maliciosos en forks
  Ecosistema: Packagist (GitHub como origen)
  Señal: composer.lock hash no coincide con commit en rama principal

VECTOR 3 — Cross-ecosystem hooks (PHP malware en package.json)
  Resultado: postinstall de npm ejecuta binario malicioso
  Ecosistema: proyectos que combinan Composer + npm
  Señal: package.json con postinstall inesperado en paquete PHP

VECTOR 4 — autoload.files como ejecución implícita
  Resultado: código PHP malicioso ejecuta en cada bootstrap
  Ecosistema: Composer/Laravel
  Señal: paquete de terceros con archivos en autoload.files
         sin razón funcional (un paquete de traducción no necesita helpers.php)

VECTOR 5 — Gusanos autopropagantes
  Resultado: un package comprometido infecta otros del mismo maintainer
  Ecosistema: npm principalmente
  Señal: múltiples paquetes del mismo autor con cambios simultáneos
```

---

## Checklist de Protección de Supply Chain

```
INMEDIATO (hacer hoy):
□ Actualizar Composer a ≥ 2.9.6 (CVE-2026-40261 y CVE-2026-40176)
□ Actualizar a Composer 2.10 (malware policy activa por defecto)
□ Verificar paquetes con alertas activas → references/incidents-current.md
□ npm audit --audit-level=high en todos los proyectos Node
□ MFA habilitado en GitHub, npm, Packagist para TODOS los maintainers

LOCKFILE Y PINNING:
□ composer.lock en git (nunca en .gitignore)
□ package-lock.json en git (nunca en .gitignore)
□ composer install (no update) en CI y producción
□ npm ci (no npm install) en CI
□ Dependencias críticas pineadas por commit SHA (no por tag)

AUDITORÍA DE AUTOLOAD:
□ Revisar autoload.files en TODOS los paquetes de terceros:
  php scripts/audit-autoload-files.php   (script en scripts/ de esta skill)
  o manualmente: grep -r '"files"' vendor/*/composer.json
□ Cualquier paquete con archivos en autoload.files necesita justificación
□ Un paquete de utilidades/traducciones/iconos NO necesita autoload.files

PIPELINE CI/CD:
□ Correr composer audit y npm audit en cada PR
□ Bloquear el merge si hay vulnerabilidades críticas
□ Usar hash verification en los steps de instalación
□ Secrets de CI separados de secrets de producción
□ Rotar secrets de CI periódicamente

MONITOREO CONTINUO:
□ Dependabot o Renovate configurado
□ Socket.dev o Aikido para monitoreo de paquetes en tiempo real
□ GitHub Security Advisories habilitado en el repo
```

---

## Defaults si falta contexto

Si el usuario no especifica, asumir Y DECLARAR (máx. 1 pregunta solo si es
bloqueante, p. ej. no hay acceso al lockfile):

- **Alcance**: todos los lockfiles del repo actual (raíz + subcarpetas obvias).
- **Nivel de audit**: `--audit-level=high` en npm; cualquier advisory en
  `composer audit` cuenta como hallazgo.
- **Proyecto híbrido** (Composer + npm): auditar AMBOS ecosistemas siempre.
- **Sin acceso al servidor de producción**: auditar el repo y declarar que la
  verificación de IOCs en servidor queda pendiente.
- **Criterio PASS**: todos los gates del protocolo con exit 0 y lockfiles en git.

---

## Ejemplo input → output

**Input:** "Auditar supply chain del monorepo Laravel + Inertia."

**Output:** PASS/FAIL con `composer audit`, `npm audit --audit-level=high`, lockfiles en git, CI usa `composer install`/`npm ci`; hallazgo FAIL si manifest sin lockfile. Gate: ambos audit exit 0 o CVEs listados con plan.

---

## Validación

| Gate | Comando | Criterio |
|------|---------|----------|
| Composer | `composer --version` | ≥ 2.9.6 |
| Composer audit | `composer audit` | exit 0 o CVEs documentados |
| npm audit | `npm audit --audit-level=high` | exit 0 o CVEs documentados |
| autoload.files | `php scripts/audit-autoload-files.php` | sin hallazgos sospechosos |
| Lockfiles | `git ls-files '*lock*'` | lockfile por manifest |
| Resultado | informe | PASS solo si gates críticos OK |

---

## Entregable

Informe de auditoría de supply chain:

```markdown
# Supply Chain Audit — <proyecto> — YYYY-MM-DD

## Resultado: PASS | FAIL

## Incidentes activos revisados
- incidents-current.md (last_updated: <fecha>) — afectado: sí/no

## Evidencia por comando
| Comando | Exit | Evidencia/CVEs |
|---|---|---|
| npm audit --audit-level=high | 0/1 | ... |
| composer audit | 0/1 | ... |
| composer --version | — | 2.x.x (≥2.9.6: sí/no) |
| php scripts/audit-autoload-files.php | 0/1 | paquetes sospechosos |

## Lockfiles
- composer.lock en git: sí/no · package-lock.json en git: sí/no
- CI usa npm ci / composer install: sí/no

## Acciones requeridas (si FAIL)
1. ...
```

---

## Skills relacionadas

- `security-checklist` — seguridad de la app en sí (esta skill = seguridad del toolchain).
- `git-workflow` — lockfiles en git, branch protection.
- `devops-base` — CI/CD hardening.
- `laravel-backend` / `node-backend` — el stack que se protege.

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
