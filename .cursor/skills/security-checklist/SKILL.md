---
name: security-checklist
description: >
  Guía la seguridad por capas de aplicaciones web: OWASP Top 10, auth segura,
  secrets, CORS, CSP y auditoría con informe clasificado. Usar cuando el usuario
  mencione seguridad, vulnerabilidades, OWASP, SQL injection, XSS, CSRF, auth,
  tokens, secrets, variables de entorno, CORS, CSP, encriptación, hashing,
  rate limiting, o cuando diga "es esto seguro", "cómo protejo mi app",
  "cómo manejo los secrets", "cómo evito ataques", "cómo aseguro mi API",
  o cualquier variante. También usar cuando se detecte código con posibles
  vulnerabilidades aunque el usuario no haya pedido revisión de seguridad
  explícitamente.
---

# Security Checklist Skill

Seguridad por capas para aplicaciones web modernas.

**OWASP Top 10 — vulnerabilidades críticas → `references/owasp.md`**
**Auth segura — tokens, passwords, sesiones → `references/auth-secure.md`**
**Secrets y variables de entorno → `references/secrets-env.md`**
**Headers y CORS/CSP → `references/headers-cors.md`**
**Auditoría completa — workflow e informe → `references/audit.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — políticas de secrets, gates CI, OAuth del proyecto.
2. ADRs o reglas de seguridad que indique project-memory.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** hallazgos Critical/High → informe en `docs/`; decisiones de política → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory (tokens OAuth, gates `composer audit` / CI).
1. **Determinar el modo**: ¿auditoría de app existente o implementación segura
   de algo nuevo? Auditoría → leer `references/audit.md` y seguir su workflow.
   Implementación → identificar la capa (auth, input, secrets, headers) y leer
   la reference correspondiente.
2. **Auditar dependencias (obligatorio en ambos modos)**: ejecutar
   `npm audit --audit-level=high` y/o `composer audit` según los manifests
   presentes. **Gate: exit code != 0 → BLOQUEAR el cierre y listar los CVEs
   en el informe** (tabla de CVEs de `references/audit.md`).
3. **Modelar amenazas** con la sección "Modelo de Amenazas" de este archivo:
   activos, atacantes, vectores, impacto. 5 minutos, no un documento formal.
4. **Recorrer el checklist por categoría** (abajo) sobre el código afectado.
   Para el detalle de cada vulnerabilidad → `references/owasp.md`; para
   passwords/tokens → `references/auth-secure.md`; para secrets →
   `references/secrets-env.md`; para CORS/CSP → `references/headers-cors.md`.
5. **Clasificar hallazgos** como Critical/High/Medium/Low con los criterios de
   `references/audit.md`. Gate: Critical/High abiertos = no apto para deploy.
6. **Emitir el informe** con la plantilla de `## Entregable` y verificar que
   los fixes aplicados pasan re-ejecutando los comandos del paso 2.
7. **Validación y cierre** — ejecutar `## Validación`; registrar gaps en `LEARNINGS.md`.

---

## Modelo de Amenazas — Pensar Antes de Codear

```
Antes de implementar seguridad, definir:

1. ¿Qué activos necesitan protección?
   - Datos personales de usuarios (PII)
   - Datos financieros (números de tarjeta, saldos)
   - Credenciales (passwords, tokens, API keys)
   - Propiedad intelectual (código fuente, algoritmos)

2. ¿Quiénes son los atacantes probables?
   - Usuarios malintencionados de la propia app
   - Bots automáticos (credential stuffing, scraping)
   - Atacantes externos (SQLi, XSS, CSRF)
   - Empleados deshonestos (insider threat)

3. ¿Cuáles son los vectores de ataque?
   - Input de usuario (formularios, URLs, headers)
   - Dependencias de terceros (supply chain)
   - Infraestructura expuesta (puertos abiertos, configs default)
   - Ingeniería social (phishing, credenciales filtradas)

4. ¿Cuál es el impacto de cada breach?
   - Pérdida de datos → multa GDPR, daño reputacional
   - Acceso no autorizado → fraude, abuso de recursos
   - Downtime → pérdida de ingresos
```

---

## Capas de Defensa

```
Capa 1 — Red y Servidor
  ✓ HTTPS en todo (HTTP → redirect a HTTPS)
  ✓ HSTS header
  ✓ Firewall (solo puertos necesarios abiertos)
  ✓ DDoS protection (Cloudflare)
  ✓ SSH key-only (no passwords)

Capa 2 — Aplicación — Input
  ✓ Validar y sanitizar TODA entrada del usuario
  ✓ Prepared statements / ORM (nunca SQL concatenado)
  ✓ Límites en tamaño de inputs
  ✓ Content-Type validation en uploads

Capa 3 — Aplicación — Auth
  ✓ Passwords hasheados con bcrypt/argon2 (nunca MD5/SHA1)
  ✓ Tokens opacos (no predecibles)
  ✓ Expiración de tokens
  ✓ Rate limiting en login
  ✓ 2FA disponible para cuentas sensibles

Capa 4 — Aplicación — Output
  ✓ Escapar output en templates (XSS)
  ✓ CSP header
  ✓ Sin datos sensibles en logs
  ✓ Errores genéricos al usuario (stack traces internamente)

Capa 5 — Datos
  ✓ Datos sensibles encriptados en reposo (AES-256)
  ✓ Conexiones BD con mínimos privilegios
  ✓ Backups encriptados
  ✓ Auditoría de accesos
```

---

## Checklist de Seguridad por Categoría

### Autenticación
- [ ] Passwords hasheados con bcrypt (cost ≥ 12) o Argon2id
- [ ] Sin almacenar passwords en texto plano ni reversibles (MD5/SHA)
- [ ] Tokens JWT con expiración corta (15-60 min access token)
- [ ] Refresh tokens rotativos con revocación
- [ ] Rate limiting en `/login` (máx 5 intentos / 15 min)
- [ ] Bloqueo temporal o CAPTCHA después de N intentos fallidos
- [ ] Mensaje de error genérico ("Invalid credentials") — no revelar si el email existe
- [ ] 2FA disponible para cuentas de admin/manager
- [ ] Tokens almacenados en httpOnly cookies o SecureStore (mobile) — no localStorage

### Autorización
- [ ] Verificar permisos en el servidor — nunca confiar en el cliente
- [ ] IDOR prevenido: verificar ownership antes de devolver recursos
- [ ] Principio de menor privilegio en roles
- [ ] Separar endpoints de admin del resto
- [ ] Auditoría de accesos a datos sensibles

### Inputs y Datos
- [ ] Validación en servidor (no solo en frontend)
- [ ] ORM/prepared statements — sin SQL concatenado
- [ ] Sanitización de HTML si se permite contenido rico
- [ ] Límites de tamaño en uploads (tipo y tamaño de archivo)
- [ ] No exponer IDs internos en URLs de API pública (usar UUIDs)

### Comunicación
- [ ] HTTPS en todo — sin excepciones en producción
- [ ] HSTS header con `includeSubDomains`
- [ ] Certificados SSL válidos y renovación automática
- [ ] CORS restrictivo — solo dominios de confianza

### Secrets
- [ ] Sin credenciales en código fuente o git history
- [ ] Secrets en variables de entorno o vault
- [ ] `.gitignore` actualizado (`.env`, `*.key`, `*.pem`)
- [ ] Rotación de secrets al detectar exposición
- [ ] API keys con permisos mínimos necesarios

### Dependencias
- [ ] `npm audit --audit-level=high` o `composer audit` con exit 0
- [ ] Dependabot o Renovate para actualizaciones automáticas
- [ ] Sin paquetes abandonados o con CVEs conocidos
- [ ] Lockfile en git (package-lock.json, composer.lock)

---

## Defaults si falta contexto

Si el usuario no especifica, asumir Y DECLARAR (máx. 1 pregunta solo si es
bloqueante, p. ej. no hay acceso al código de producción):

- **Modo**: si hay código existente y la petición es genérica ("¿es seguro?")
  → modo auditoría con `references/audit.md`.
- **Alcance**: el código del repo actual; infraestructura solo si hay archivos
  de config (nginx, Docker, CI) en el repo.
- **Severidad mínima que bloquea**: High (además de Critical).
- **Hashing**: Argon2id o bcrypt cost ≥ 12. **Tokens**: JWT corto + refresh rotativo.
- **Apps con usuarios EU**: aplicar la sección GDPR de `references/secrets-env.md`.

---

## Ejemplo input → output

**Input:** "¿Es seguro el flujo OAuth de conexiones Meta?"

**Output:** informe con `composer audit` + revisión tokens cifrados en BD, rate limit en callback, CORS; hallazgo Medium si falta `state` CSRF; veredicto APTO si no hay High/Critical abiertos. Gate: `composer audit` exit 0.

---

## Validación

| Gate | Comando | Criterio |
|------|---------|----------|
| Dependencias npm | `npm audit --audit-level=high` | exit 0 (o CVEs documentados + plan) |
| Dependencias PHP | `composer audit` | exit 0 (o CVEs documentados + plan) |
| Severidad | informe | 0 Critical/High abiertos para deploy |
| Checklist | categorías de este SKILL.md | ítems aplicables revisados |

---

## Entregable

Informe de auditoría (plantilla completa con IDs en `references/audit.md`):

```markdown
# Informe de Seguridad — <proyecto> — YYYY-MM-DD

## Comandos ejecutados
- npm audit --audit-level=high → exit <N>
- composer audit → exit <N>

## Resumen: Critical <n> · High <n> · Medium <n> · Low <n>
Veredicto: <APTO | BLOQUEADO>

## Hallazgos
### [SEC-001] <título> — <severidad>
- Categoría OWASP / Ubicación / Evidencia / Remediación

## CVEs de dependencias
| CVE | Paquete | Severidad | Fix |
```

---

## Skills relacionadas

- `supply-chain-security` — seguridad del toolchain y dependencias (esta skill = seguridad de la app en sí).
- `devops-base` — gates de seguridad en CI/CD.
- `monitoring-observability` — logs de seguridad y detección en producción.
- `laravel-backend` / `node-backend` — implementación segura por stack.

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
