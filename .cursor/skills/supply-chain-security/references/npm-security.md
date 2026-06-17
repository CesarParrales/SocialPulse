# npm — Ataques Activos y Defensa

## Estado Actual

> Las alertas activas del ecosistema npm (gusano Shai-Hulud, paquetes
> afectados, anatomía del ataque, IOCs y comandos de verificación) están
> centralizadas en **`incidents-current.md`** (misma carpeta). Consultar ese
> archivo primero — tiene `last_updated` y se revisa cada 30 días.

---

## Protección en npm

```bash
# USAR npm ci EN LUGAR DE npm install
# npm ci usa el lockfile exactamente — no resuelve de nuevo
npm ci

# AUDITORÍA ESTÁNDAR
npm audit                           # todos los problemas
npm audit --audit-level=high        # solo high y critical
npm audit fix                       # fix automático cuando hay solución

# VERIFICAR PAQUETES ESPECÍFICOS
# Comprobar si @ctrl/tinycolor (vector inicial de Shai-Hulud) está instalado
npm ls @ctrl/tinycolor

# VERIFICAR POSTINSTALL SCRIPTS SOSPECHOSOS
# Antes de instalar, revisar si el paquete tiene postinstall
npm view [paquete] scripts

# INSTALAR SIN EJECUTAR SCRIPTS (para auditar primero)
npm install --ignore-scripts [paquete]
# Luego revisar manualmente el código antes de ejecutar scripts

# SOCKET.DEV — verificar paquetes antes de instalar
npx socket npm install [paquete]
# Socket analiza el paquete en tiempo real antes de instalarlo

# BLOQUEAR OUTBOUND DE POSTINSTALL (para entornos CI sensibles)
# En package.json:
{
  "scripts": {
    "postinstall": "echo 'WARNING: postinstall scripts are disabled in CI'"
  }
}
```

---

## Configuración de .npmrc para Hardening

```ini
# .npmrc — hardening de npm

# Prevenir ejecución de scripts de ciclo de vida de paquetes
# (Más agresivo — puede romper paquetes que los necesitan)
# ignore-scripts=true

# Verificar integridad de paquetes
audit=true
audit-level=high

# Usar solo el lockfile (no resolver de nuevo)
# Equivalente a npm ci pero para npm install
prefer-frozen-lockfile=true   # (pnpm) / npm ci ya hace esto

# Timeout para evitar paquetes que hacen requests lentos al instalar
fetch-timeout=60000

# Registry explícito (no confiar en redirecciones)
registry=https://registry.npmjs.org/

# Para proyectos con paquetes privados — usar scoped registry
@mycompany:registry=https://npm.pkg.github.com
```

---

## Señales de Alerta en un Paquete npm

```
REVISAR ANTES DE INSTALAR CUALQUIER PAQUETE NUEVO:

Señales de riesgo en package.json:
  → postinstall, preinstall, install con scripts que hacen fetch/curl/wget
  → scripts que escriben a /tmp o ejecutan binarios
  → dependencies con paquetes oscuros o con nombres similares a populares (typosquatting)

Señales de riesgo en el código:
  → require('child_process') en código de inicialización
  → eval() o Function() con strings dinámicos
  → fetch()/http.request() a URLs hardcodeadas en el código de instalación
  → Acceso a process.env en el nivel de módulo (fuera de funciones)
  → fs.readFileSync('~/.ssh/id_rsa') o acceso a archivos sensibles

Señales de compromiso reciente:
  → Versión nueva publicada de forma inesperada (fuera del ciclo del proyecto)
  → Cambios de maintainer recientes (transfer de ownership)
  → Muchas versiones publicadas en corto tiempo (sign de ataque en progreso)
  → El repositorio GitHub tiene commits recientes que no coinciden con el changelog

Herramientas de inspección:
  npx npm-audit-resolver                    → gestión de excepciones de audit
  npx socket npm install [paquete]          → análisis antes de instalar
  npx package-inspector [paquete]           → inspección del código del paquete
  node -e "require('[paquete]')" --dry-run  → ver qué se ejecuta al importar
```

---

## GitHub Actions — Hardening del Pipeline

```yaml
# .github/workflows/security.yml
name: Security Audit

on:
  push:
    branches: [main, develop]
  pull_request:
  schedule:
    - cron: '0 8 * * *'  # Diariamente — detectar nuevas vulnerabilidades

jobs:
  npm-audit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      # CRÍTICO: usar commit SHA, no tag (previene tag-rewriting en actions)
      - uses: actions/setup-node@v4
        with:
          node-version: '20'

      # npm ci — usa lockfile exactamente, sin resolver de nuevo
      - run: npm ci --ignore-scripts
        # --ignore-scripts previene ejecución de postinstall durante CI audit

      # Auditoría estándar
      - run: npm audit --audit-level=high

      # Verificación adicional con Socket.dev (detecta Shai-Hulud y similares)
      - name: Socket Security Check
        uses: nicholasgasior/socket-action@v1  # o el equivalente actual
        with:
          api-key: ${{ secrets.SOCKET_API_KEY }}

      # Verificar que el lockfile no fue modificado por npm install
      - name: Verify lockfile integrity
        run: |
          if ! git diff --exit-code package-lock.json; then
            echo "ERROR: package-lock.json was modified during CI — possible tampering"
            exit 1
          fi

  # Separar la instalación "real" de la auditoría
  build:
    runs-on: ubuntu-latest
    needs: npm-audit    # Solo buildear si la auditoría pasó
    steps:
      - uses: actions/checkout@v4
      - run: npm ci    # Aquí sí corren los scripts — auditoría ya pasó
      - run: npm run build
```

---

## npm Provenance — La Protección de 2023+ Activada

```bash
# Verificar que un paquete tiene provenance (SLSA attestation)
npm audit signatures [paquete]@[version]

# Publicar con provenance (para maintainers de paquetes)
# Solo funciona desde GitHub Actions con el OIDC token correcto
npm publish --provenance

# package.json — configurar para requerir provenance
{
  "publishConfig": {
    "provenance": true
  }
}

# Qué protege:
# → Vincula el paquete publicado al commit exacto de GitHub que lo generó
# → Un atacante con el token de publicación NO puede publicar con provenance falsa
# → El atacante necesitaría también comprometer el GitHub Actions workflow

# Limitación actual:
# → No todos los paquetes tienen provenance
# → Shai-Hulud ocurrió antes de que fuera obligatorio
# → La adopción crece pero no es universal aún
```
