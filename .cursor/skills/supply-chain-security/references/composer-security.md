# Composer / PHP / Laravel — Ataques Activos y Defensa

## Estado Actual

> Las alertas activas del ecosistema Composer (ataque laravel-lang con su
> anatomía, verificación y remediación; CVE-2026-40261; devdojo; respuesta de
> Packagist/Composer 2.10) están centralizadas en **`incidents-current.md`**
> (misma carpeta). Consultar ese archivo primero — tiene `last_updated` y se
> revisa cada 30 días.

---

## Hardening de Composer

```bash
# ACTUALIZAR COMPOSER INMEDIATAMENTE
composer self-update --stable    # actualiza a la versión stable más reciente
composer --version               # verificar: debe ser ≥ 2.10

# INSTALAR CON COMPOSER CI EN PIPELINES (equivalente a npm ci)
composer install --no-interaction --prefer-dist --optimize-autoloader

# VERIFICAR AUTOLOAD.FILES EN TODOS LOS PAQUETES DE TERCEROS
# Esto es el vector principal del ataque laravel-lang
php -r "
\$packages = glob('vendor/*/*/composer.json');
foreach (\$packages as \$pkg) {
    \$data = json_decode(file_get_contents(\$pkg), true);
    if (!empty(\$data['autoload']['files'])) {
        echo 'AUTOLOAD FILES FOUND: ' . \$pkg . PHP_EOL;
        print_r(\$data['autoload']['files']);
    }
}
"
# Revisar CADA resultado — si no hay razón para autoload.files en ese paquete = red flag

# AUDITORÍA DE SEGURIDAD ESTÁNDAR
composer audit         # verifica contra el advisory database
composer audit --format=json | jq '.advisories | length'

# VERIFICAR INTEGRIDAD DEL LOCKFILE
# El lockfile debe estar en git y nunca ser modificado en CI
git diff --exit-code composer.lock || echo "WARNING: composer.lock was modified"
```

---

## composer.json — Configuración Segura

```json
{
  "config": {
    "preferred-install": "dist",
    "sort-packages": true,
    "allow-plugins": {
      "pestphp/pest-plugin": true,
      "php-http/discovery": false
    },
    "audit": {
      "abandoned": "report",
      "ignore": []
    }
  },
  "scripts": {
    "post-install-cmd": [
      "@php artisan package:discover --ansi"
    ],
    "post-update-cmd": [
      "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
    ],
    "security-audit": [
      "composer audit",
      "@php -r \"require 'vendor/autoload.php'; /* verificación de autoload.files */\""
    ]
  }
}
```

---

## Cómo Composer 2.10 te Protege

```
MALWARE POLICY:
  Cuando Packagist marca una versión como malware:
  → composer install: FALLA si la versión maliciosa está en el lockfile
  → composer update: BLOQUEA la instalación de la versión maliciosa
  → composer require: BLOQUEA la instalación
  
  Esto significa que incluso si tu lockfile fue generado ANTES de que se detectara
  el malware, el próximo composer install en CI/producción FALLARÁ con un error claro.
  
  Antes: el lockfile malicioso llegaba silenciosamente a producción
  Ahora: falla ruidosamente antes de que llegue a producción

VERSIONES INMUTABLES:
  → Si un atacante intenta hacer git tag rewriting en un paquete publicado
  → Packagist rechaza el update (la versión estable ya fue indexada con su hash)
  → El tag rewriting que usó laravel-lang YA NO FUNCIONA con Composer 2.10 + Packagist
  → Esto es retroactivo: aplica a versiones nuevas publicadas desde la actualización

CROSS-ECOSYSTEM (package.json en Composer packages):
  → Composer 2.10 NO audita package.json por defecto
  → Necesitas auditar npm también en proyectos híbridos (ver npm-security.md)
  → Solución: en tu pipeline CI, correr AMBOS: composer audit && npm audit
```

---

## Señales de Alerta en Paquetes Composer/PHP

```
REVISAR ANTES DE INSTALAR CUALQUIER PAQUETE NUEVO:

Señales de riesgo en composer.json:
  → autoload.files con archivos que no tienen función semántica clara
     Un paquete de traducciones, iconos o utilidades NO necesita autoload.files
     Un framework core SÍ puede necesitarlo (helpers de Laravel, por ejemplo)
  → "require": paquetes obscuros o typosquatted (symfony/processe vs symfony/process)
  → "scripts" con curl, wget, o ejecutables en events de instalación

Señales de compromiso reciente:
  → Múltiples versiones publicadas en minutos (no es el ritmo normal de un maintainer)
  → Cambio en los maintainers del paquete en Packagist recientemente
  → El repositorio GitHub tiene tags que apuntan a commits no en la rama principal
     (git ls-remote [repo] | grep refs/tags → verificar que apuntan a commits conocidos)

Verificar integridad de tags vs branches:
  # Para cualquier paquete sospechoso
  git ls-remote https://github.com/[vendor]/[package] 'refs/tags/*' | head -20
  # Comparar los SHAs de los tags con los commits en main/master

Herramientas:
  Socket.dev          → análisis de paquetes Composer en tiempo real
  Aikido Security     → monitoreo continuo de Packagist (ahora integrado)
  Dependabot          → actualizaciones automáticas con audit
  Enlightn            → auditoría de seguridad específica de Laravel
  snyk composer       → análisis con Snyk
```
