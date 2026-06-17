# Auditoría de Dependencias

## El Principio de Menor Confianza

```
Cada dependencia que instalas es código que se ejecuta en tu sistema.
No importa cuántos stars tenga — es código de terceros.

El checklist mental antes de instalar cualquier paquete:

1. ¿Lo necesito realmente?
   → Puedo implementarlo yo mismo en < 1 hora?
   → ¿Es realmente más seguro usar el paquete que implementarlo?

2. ¿Está activamente mantenido?
   → Último commit > 12 meses atrás = riesgo de abandono
   → Paquetes abandonados son frecuentemente tomados por atacantes

3. ¿Cuál es la superficie de ataque?
   → ¿Qué permisos/accesos necesita? (filesystem, network, env)
   → ¿Tiene postinstall scripts? ¿Qué hacen?

4. ¿Puedo verificar el origen?
   → ¿Tiene SLSA provenance / Sigstore attestation?
   → ¿Los maintainers tienen MFA en sus cuentas?
   → ¿El paquete tiene alta visibilidad si hay un compromiso?
```

---

## SBOM — Software Bill of Materials

```
Un SBOM es el inventario completo de todas tus dependencias.
Es la base de la auditoría de supply chain.

GENERAR SBOM EN PHP/LARAVEL:
  # Con CycloneDX (el formato estándar)
  composer require --dev cyclonedx/cyclonedx-php-composer
  composer make-bom --output-format=JSON > sbom.json

  # Lo que incluye:
  # - Nombre y versión de cada paquete
  # - Licencia
  # - Checksum del código fuente
  # - Árbol de dependencias transitivas

GENERAR SBOM EN NODE:
  npm install -g @cyclonedx/cyclonedx-npm
  cyclonedx-npm --output-file sbom.json

  # O con npm:
  npm sbom --sbom-format cyclonedx > sbom.json

USAR EL SBOM:
  # Verificar contra base de datos de vulnerabilidades
  # GRYPE (Anchore) — analiza el SBOM
  grype sbom:sbom.json

  # OSV Scanner (Google) — gratuito y actualizado
  osv-scanner --sbom sbom.json

  # Dependency Track — plataforma completa de gestión de SBOM
  # dashboard con alertas automáticas cuando aparece nueva vulnerabilidad
```

---

## Auditoría de autoload.files (PHP — El Vector laravel-lang)

El script completo está materializado en **`../scripts/audit-autoload-files.php`**
(carpeta `scripts/` de esta skill).

```bash
# Uso: copiarlo a scripts/ del proyecto a auditar y ejecutar desde la raíz
# (donde está vendor/):
php scripts/audit-autoload-files.php

# Exit code 0 = limpio · 1 = autoload.files sospechosos (usable como gate en CI)
# Mantener la $WHITELIST del script con los paquetes del proyecto que
# justificadamente usan autoload.files (un paquete de traducciones NO lo necesita).
```

---

## Auditoría de Scripts de Instalación (npm)

```javascript
// scripts/audit-install-scripts.js
// Ejecutar: node scripts/audit-install-scripts.js

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const DANGEROUS_PATTERNS = [
  /curl\s+/,
  /wget\s+/,
  /fetch\(/,
  /http\.request\(/,
  /require\('child_process'\)/,
  /exec\(/,
  /spawn\(/,
  /\/tmp\//,
  /process\.env/,   // acceso a env vars en scripts de instalación
  /readFileSync.*(?:ssh|\.env|credentials|config)/i,
];

const SCRIPTS_TO_CHECK = ['preinstall', 'install', 'postinstall', 'prepare'];
const suspicious = [];

function checkPackage(packageJsonPath) {
  const pkg = JSON.parse(fs.readFileSync(packageJsonPath, 'utf-8'));
  const packageName = pkg.name;

  for (const scriptName of SCRIPTS_TO_CHECK) {
    if (!pkg.scripts?.[scriptName]) continue;

    const scriptContent = pkg.scripts[scriptName];
    const matches = DANGEROUS_PATTERNS.filter(p => p.test(scriptContent));

    if (matches.length > 0) {
      suspicious.push({
        package: packageName,
        script:  scriptName,
        content: scriptContent,
        patterns: matches.map(p => p.toString()),
        path: packageJsonPath,
      });
    }
  }
}

// Verificar todos los node_modules
const nodeModules = execSync('find node_modules -maxdepth 2 -name "package.json" -not -path "*/node_modules/*/node_modules/*"')
  .toString().trim().split('\n');

for (const pkgPath of nodeModules) {
  try { checkPackage(pkgPath); } catch {}
}

if (suspicious.length === 0) {
  console.log('✅ No suspicious install scripts found');
  process.exit(0);
}

console.log(`⚠️  ${suspicious.length} suspicious install scripts found:\n`);
suspicious.forEach(item => {
  console.log(`Package: ${item.package}`);
  console.log(`Script:  ${item.script}`);
  console.log(`Content: ${item.content}`);
  console.log(`Patterns: ${item.patterns.join(', ')}`);
  console.log('');
});

process.exit(1); // Fail CI
```

---

## Monitoreo Continuo de Dependencias

```yaml
# .github/dependabot.yml — actualizaciones automáticas con seguridad
version: 2
updates:
  - package-ecosystem: "composer"
    directory: "/"
    schedule:
      interval: "daily"     # diario para detectar compromisos rápido
    open-pull-requests-limit: 5
    labels:
      - "dependencies"
      - "security"
    # Solo auto-merge patches de paquetes conocidos seguros
    # Las majors y minors requieren revisión manual

  - package-ecosystem: "npm"
    directory: "/"
    schedule:
      interval: "daily"
    open-pull-requests-limit: 5

  - package-ecosystem: "github-actions"
    directory: "/"
    schedule:
      interval: "weekly"
    # CRÍTICO: las GitHub Actions también son supply chain
    # Usar commit SHA en lugar de tags (ver cicd-hardening.md)
```

---

## Herramientas de Auditoría por Ecosistema

```
PHP/COMPOSER:
  composer audit          → Advisory database oficial (gratis, incluido)
  enlightn/enlightn       → 120+ checks de seguridad específicos de Laravel
  socket.dev              → Análisis de comportamiento del paquete
  aikido.dev              → Monitoreo en tiempo real (integrado en Packagist)
  snyk composer test      → Análisis Snyk (tiene tier gratuito)
  osv-scanner             → Google Open Source Vulnerabilities (gratis)

NPM:
  npm audit               → Incluido, correr siempre
  socket npm install      → Análisis antes de instalar
  socket.dev dashboard    → Monitoreo continuo del proyecto
  snyk test               → Análisis Snyk
  retire.js               → Detecta versiones con vulnerabilidades conocidas
  osv-scanner             → Compatible con node también

CROSS-PLATFORM:
  grype (Anchore)         → Analiza SBOMs y directamente el código
  syft                    → Genera SBOMs en múltiples formatos
  trivy                   → Escaneo completo de vulnerabilidades (fs, container)
  OpenSSF Scorecard       → Evalúa la seguridad del proceso de un proyecto OSS

CUANDO USAR CADA UNO:
  Desarrollo diario:      npm audit / composer audit (ya incluidos)
  Antes de instalar algo nuevo: socket.dev (análisis instantáneo)
  Monitoreo continuo:     Dependabot + Aikido + GitHub Security Advisories
  Auditoría profunda:     Snyk o trivy en CI
  Incidente sospechado:   grype + osv-scanner para triage completo
```
