# Respuesta a Incidente — Supply Chain Attack

## Los Primeros 15 Minutos Importan

```
Un ataque de supply chain que llega a producción es un incidente activo.
El malware puede estar exfiltrando datos en tiempo real.
Cada minuto cuenta.

PRIORIDADES EN ORDEN:
1. CONTENER  → desconectar, revocar, aislar (antes de investigar)
2. EVALUAR   → entender el alcance del compromiso
3. REMEDIAR  → limpiar, reconstruir, restaurar
4. PREVENIR  → tapar el vector para que no vuelva a pasar
```

---

## Protocolo de Respuesta Inmediata

```bash
# ─── FASE 1: CONTENER — primeros 5 minutos ─────────────────────────────────

# 1. Si el servidor está activo con el paquete malicioso:
# Aislar el servidor de la red (o deshabilitar el tráfico saliente)
# En AWS: deshabilitar el security group que permite outbound
# En GCP: remover la regla de firewall que permite egress
# En servidor propio: iptables -I OUTPUT -j DROP

# 2. REVOCAR TODAS LAS CREDENCIALES DEL ENTORNO COMPROMETIDO
# No esperar a saber qué fue robado — asumir todo comprometido

# AWS — revocar access keys
aws iam delete-access-key --access-key-id [KEY_ID]
# Crear nuevas keys DESPUÉS de limpiar

# GitHub — revocar Personal Access Tokens
# github.com/settings/tokens → revocar todos los tokens del servidor comprometido

# npm — revocar tokens de publicación
npm token revoke [token-id]

# Secretos de CI (GitHub Actions):
# GitHub → Settings → Secrets → actualizar/rotar todos

# Docker — revocar tokens del registry

# ─── FASE 2: EVALUAR — primeros 30 minutos ──────────────────────────────────

# Los IOCs y comandos de verificación específicos de cada incidente activo
# (laravel-lang, Shai-Hulud, etc.) están en incidents-current.md — usarlos aquí.

# Verificación genérica del entorno:
# ¿Qué archivos fueron accedidos recientemente por el proceso PHP?
# (En Linux, requiere auditd activo)
ausearch -c php -ts recent 2>/dev/null | grep -E "\.env|\.ssh|credentials" | head -20
```

---

## Evaluación del Alcance del Compromiso

```
INVENTARIO DE QUÉ PUDO SER ROBADO:

Credenciales cloud (robo más común en ataques recientes):
□ AWS: IAM access keys, session tokens, instance profile creds
□ GCP: service account keys, application default credentials
□ Azure: service principal credentials
□ Verificar: ~/.aws/credentials, ~/.config/gcloud/, ~/.azure/

Secrets de aplicación:
□ .env files (incluyendo backups: .env.backup, .env.local, .env.production)
□ Variables de entorno del proceso (visibles en /proc/[pid]/environ)
□ Database credentials (conexión directa a producción)
□ Stripe/Braintree/PayPal API keys
□ SendGrid/Mailgun/SMTP credentials

Infrastructure secrets:
□ SSH keys (~/.ssh/ y las authorized_keys del servidor)
□ Kubeconfig files (~/.kube/config)
□ HashiCorp Vault tokens
□ Docker registry tokens
□ Kubernetes service account tokens

CI/CD secrets (si el ataque fue en el pipeline):
□ Todos los secrets de GitHub Actions / GitLab CI
□ Deployment keys
□ Package publishing tokens (npm, Packagist)

Para cada credencial comprometida o potencialmente comprometida:
→ REVOCAR primero, investigar si fue usada después
→ Verificar logs de uso de la credencial en cada servicio
```

---

## Remediación

```bash
# ─── FASE 3: REMEDIAR ────────────────────────────────────────────────────────

# Opción A: Reconstruir el servidor desde imagen limpia (RECOMENDADO)
# La reconstrucción garantiza que no haya persistencia no detectada
# El malware puede haber dejado backdoors fuera del directorio del paquete

# Opción B: Limpiar el servidor existente (si reconstruir no es posible)

# 1. Remover el paquete comprometido
composer remove laravel-lang/lang laravel-lang/attributes laravel-lang/http-statuses laravel-lang/actions

# 2. Limpiar artifacts del payload
rm -rf /tmp/.laravel_locale/ 2>/dev/null

# 3. Verificar que no haya persistencia en cron
crontab -l 2>/dev/null | grep -v "^#"
ls -la /etc/cron.d/ /etc/cron.daily/ /etc/cron.hourly/ 2>/dev/null

# 4. Verificar procesos sospechosos
ps aux | grep -E "\.sshd|\.tmp|/tmp/" 
# Los ataques de npm colocaron binarios en /tmp/.sshd

# 5. Verificar nuevos usuarios o SSH keys
cat /etc/passwd | grep -v "nologin\|false" | grep -v "^#"
cat ~/.ssh/authorized_keys 2>/dev/null

# 6. Reinstalar dependencias limpias
# Primero actualizar Composer:
composer self-update --stable

# Luego instalar con versiones verificadas (pineando por commit SHA si es posible)
composer install --no-interaction --prefer-dist

# 7. Verificar que autoload.files está limpio ANTES de arrancar
php scripts/audit-autoload-files.php  # ver dependency-audit.md

# 8. Regenerar todos los secrets de la aplicación
php artisan key:generate
# Forzar logout de todas las sesiones activas
php artisan session:flush 2>/dev/null || php artisan cache:clear

# ─── FASE 4: RESTAURAR CREDENCIALES ─────────────────────────────────────────

# Solo restaurar credenciales DESPUÉS de limpiar el sistema
# Generar nuevas credenciales — no reusar las anteriores
# Actualizar en todos los lugares donde se usaban:
# - .env de producción
# - GitHub Secrets / CI secrets
# - Secrets managers (AWS SSM, Vault, etc.)
# - Documentación interna
```

---

## Comunicación Durante el Incidente

```
Dependiendo del alcance, puede ser necesario notificar:

Internamente (siempre):
→ El equipo técnico: qué pasó, qué se está haciendo
→ El CTO/CEO: si hay impacto potencial en clientes o datos
→ Legal/Compliance: si hay datos personales potencialmente afectados

A clientes (si aplica):
→ Si hay datos de clientes potencialmente comprometidos:
  - GDPR: notificar en 72h a la autoridad supervisora
  - Notificar a los usuarios afectados con información clara
→ Template básico:
  "Hemos detectado un incidente de seguridad relacionado con una dependencia
   de terceros. El [fecha] detectamos [descripción genérica del vector].
   Hemos tomado las siguientes medidas: [acciones].
   Como precaución, recomendamos [acciones para el usuario si aplica]."

A la comunidad (si el paquete es open source):
→ Crear un Security Advisory en GitHub
→ Notificar al maintainer del paquete comprometido
→ Si descubriste el ataque: reportar a Packagist/npm y CVE

Documentar todo:
→ Timeline del incidente (cuándo ocurrió, cuándo se detectó, cuándo se contuvo)
→ Qué sistemas estuvieron expuestos y por cuánto tiempo
→ Qué credenciales fueron revocadas y cuándo
→ Este documento es el post-mortem
```

---

## Post-Mortem — Prevenir la Recurrencia

```
Después del incidente, antes de declararlo cerrado:

ANÁLISIS DE CAUSA RAÍZ:
□ ¿Cómo entró el paquete malicioso? (update automático, PR de deps, nuevo feature)
□ ¿Por qué no se detectó antes? (falta de auditoría en CI, no hay audit en el paso correcto)
□ ¿Cuánto tiempo estuvo activo el compromiso? (diferencia entre instalación y detección)
□ ¿Qué datos o secrets estuvieron expuestos?

MEJORAS DE PROCESO:
□ ¿Los lockfiles estaban en git? → Si no, añadirlos
□ ¿La auditoría corría en CI? → Si no, añadirla como gate
□ ¿Se usaba composer ci / npm ci? → Si no, migrar
□ ¿Los secrets de CI tienen el scope correcto? → Revisar todos
□ ¿Hay un proceso de revisión antes de actualizar deps? → Crear uno

MONITOREO AÑADIDO:
□ Alertas en Aikido/Socket para los paquetes del proyecto
□ GitHub Security Advisories habilitado
□ Proceso de revisión mensual de deps nuevas
□ Script de auditoría de autoload.files en CI

Regla de oro del post-mortem:
El objetivo no es encontrar culpables — es encontrar qué falló en el sistema
y cómo mejorarlo para que no pueda volver a pasar.
```
