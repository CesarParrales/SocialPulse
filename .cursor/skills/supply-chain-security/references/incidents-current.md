# Incidentes Activos de Supply Chain

```
last_updated: 2026-05
revisar_cada: 30 días
```

> Fuente única de verdad para alertas activas. `npm-security.md`,
> `composer-security.md` e `incident-response.md` apuntan aquí en vez de
> duplicar. Si `last_updated` supera los 30 días, verificar el estado de cada
> incidente antes de actuar sobre esta información.

---

## Resumen de Alertas Activas

| Fecha | Incidente | Ecosistema | Severidad |
|---|---|---|---|
| May 22-23, 2026 | laravel-lang/* comprometidos (git tag hijacking) | Composer | CRÍTICA |
| May 2026 | devdojo/wave, devdojo/genesis + 6 paquetes (cross-ecosystem) | Composer+npm | ALTA |
| Abr 2026 | CVE-2026-40261 (CVSS 8.8) — Composer Perforce driver RCE | Composer | CRÍTICA |
| Sep-Nov 2025 | Gusano Shai-Hulud — 500+ paquetes, variante con dead man's switch | npm | CRÍTICA |

---

## laravel-lang — Supply Chain Attack (Mayo 2026)

```
PAQUETES COMPROMETIDOS (22-23 mayo 2026):
  laravel-lang/lang (7.8k stars), laravel-lang/attributes,
  laravel-lang/http-statuses, laravel-lang/actions
  700+ versiones históricas reescritas via git tag hijacking.

CUALQUIER PROYECTO LARAVEL QUE INSTALÓ laravel-lang/* ENTRE
2026-05-22 22:32 UTC Y 2026-05-23 DEBE CONSIDERARSE COMPROMETIDO
HASTA VERIFICACIÓN.

VECTOR DE ENTRADA:
  → Credenciales de organización GitHub comprometidas
  → Con acceso org-level: push access a todos los repos de laravel-lang

MECANISMO DE DISTRIBUCIÓN:
  → Git tag rewriting: cada tag existente (502 en laravel-lang/lang)
    reescrito para apuntar a un commit en un fork malicioso
  → GitHub permite que los tags de versión referencien commits de forks
  → No se modificó ningún branch oficial — solo los tags

  El commit malicioso:
  - Solo modifica DOS archivos: composer.json y src/helpers.php
  - Añade "files": ["src/helpers.php"] al autoload en composer.json
  - helpers.php contiene decoy functions + bloque de ejecución oculto

MECANISMO DE INFECCIÓN:
  1. El desarrollador corre: composer update (o composer install en CI)
  2. Composer resuelve la versión pineada (ej: ^1.0) → descarga el tag malicioso
  3. autoload.files → helpers.php se carga en CADA bootstrap de PHP
  4. helpers.php fingerprints el host (MD5 del path + hostname)
  5. Descarga payload de segunda etapa desde flipboxstudio[.]info
  6. Payload roba: cloud keys, kubeconfig, vault secrets, SSH, env files,
     browser data, password manager vaults, crypto wallets, CI tokens
```

### Verificación inmediata

```bash
# PASO 1: Verificar si el proyecto usa laravel-lang
grep -E "laravel-lang/(lang|attributes|http-statuses|actions)" composer.json composer.lock

# PASO 2: Verificar la versión instalada
composer show laravel-lang/lang 2>/dev/null
composer show laravel-lang/attributes 2>/dev/null

# PASO 3: Verificar el vector de infección
# Cualquier autoload.files en paquetes de laravel-lang es sospechoso
for pkg in vendor/laravel-lang/*/; do
  echo "=== $pkg ==="
  cat "$pkg/composer.json" | python3 -m json.tool | grep -A5 '"files"'
done
# Si aparece "src/helpers.php" → COMPROMETIDO
find vendor/laravel-lang -name "helpers.php" 2>/dev/null

# PASO 4: Verificar si el payload se ejecutó
ls -la /tmp/.laravel_locale/ 2>/dev/null && echo "PAYLOAD EJECUTADO — SERVIDOR COMPROMETIDO"

# PASO 5: Verificar el hash del commit en el lockfile vs el repo oficial
# El hash en composer.lock debería corresponder a un commit en la rama main
# del repositorio oficial, NO a un commit de un fork
```

### Remediación

```bash
# 1. Pinear a un commit SHA anterior al ataque (antes de 2026-05-22 22:32 UTC)
composer require "laravel-lang/lang:@dev" --prefer-source
# O usar Private Packagist / mirror con las versiones limpias

# 2. Limpiar el artifact del payload
rm -rf /tmp/.laravel_locale/ 2>/dev/null

# 3. ROTACIÓN OBLIGATORIA DE CREDENCIALES (asumir todo robado):
# - AWS keys / IAM credentials, GCP service accounts, Azure credentials
# - Vault tokens, Docker registry tokens, GitHub PATs
# - CI/CD secrets (GitHub Actions, GitLab CI, etc.)
# - SSH keys del servidor y del desarrollador
# - .env files de todos los ambientes
# Protocolo completo de contención → incident-response.md
```

### IOCs (Indicadores de Compromiso)

```bash
# Marker del payload en el servidor
ls -la /tmp/.laravel_locale/ 2>/dev/null

# Requests a dominios del atacante en logs del servidor
grep -E "flipboxstudio|webhook\.site|ngrok" /var/log/nginx/access.log 2>/dev/null | tail -20
grep -E "flipboxstudio|webhook\.site|ngrok" /var/log/apache2/access.log 2>/dev/null | tail -20
```

---

## Shai-Hulud — Gusano npm (Sep 2025, activo)

```
Sep 2025:  500+ paquetes npm comprometidos (gusano autoreplicante)
           Paquetes afectados notables: @ctrl/tinycolor, angulartics2, ngx-toastr
Nov 2025:  Variante evolucionada con "dead man's switch" (GitLab)
2026:      Ataques sistemáticos — npm es el ecosistema más atacado

El cambio fundamental de 2025:
Antes: ataques aislados (typosquatting, un paquete a la vez)
Ahora: gusanos autopropagantes que infectan el portfolio completo de un
       maintainer y luego saltan a proyectos downstream en horas

CÓMO FUNCIONÓ:
  Fase 1 — Compromiso de credenciales del maintainer:
    → Phishing dirigido a maintainers de paquetes populares
    → Credential stuffing de tokens de publicación npm
  Fase 2 — Publicación del paquete infectado:
    → Versión nueva del paquete legítimo con postinstall malicioso
    → El script telemetry.js o equivalente se ejecuta al instalar
  Fase 3 — Recolección en la máquina del desarrollador:
    → Escanea: SSH keys, .env files, credenciales cloud (AWS/GCP/Azure)
    → Exfiltra a repositorios GitHub controlados por el atacante
  Fase 4 — Autopropagación (la novedad de 2025):
    → Si obtiene GitHub PAT → accede a otros repos del desarrollador
    → Infecta otros paquetes que el mismo maintainer publica
    → Un maintainer comprometido = todo su portfolio comprometido

  Herramientas usadas:
    → TruffleHog (escáner de secretos legítimo) para detectar credenciales
    → Webhooks externos para exfiltración (webhook.site y similares)
```

### Verificación inmediata

```bash
npm audit --audit-level=critical

# Buscar específicamente paquetes Shai-Hulud conocidos:
npm ls @ctrl/tinycolor angulartics2 ngx-toastr 2>/dev/null

# IOCs: repos de exfiltración creados por el atacante
grep -r "s1ngularity\|shai.hulud" /var/log/ 2>/dev/null | head -10
# Binarios colocados por los ataques npm
ps aux | grep -E "/tmp/\.sshd"
```

---

## CVE-2026-40261 — Composer Perforce Driver RCE (Abr 2026)

```
CVE-2026-40261 (CVSS 8.8) — y relacionado CVE-2026-40176
  Permite ejecutar comandos OS al instalar dependencias
  Afecta: Composer < 2.9.6

ACCIÓN: composer self-update --stable
  Verificar: composer --version → debe ser ≥ 2.9.6
  Recomendado: ≥ 2.10 (malware policy activa por defecto)
```

---

## devdojo — Cross-Ecosystem Malware (May 2026)

```
devdojo/wave, devdojo/genesis + 6 paquetes más
  Malware en package.json (cross-ecosystem, oculto para scanners PHP)
  Lección: en proyectos Composer + npm hay que auditar AMBOS ecosistemas
  → composer audit && npm audit en el mismo pipeline
```

---

## Respuesta de los Registries (contexto, no alerta)

```
PACKAGIST / COMPOSER 2.10:
  → Malware policy activa: versiones maliciosas bloqueadas incluso si
    están en composer.lock (composer install FALLA ruidosamente)
  → Versiones estables inmutables: git tag rewriting = rechazado
    (el vector de laravel-lang ya no funciona con Composer 2.10 + Packagist)
  → Aikido Security integrado para detección de malware en tiempo real

NPM PROVENANCE:
  → Adopción creciente pero no universal — no asumir que protege todo
```
