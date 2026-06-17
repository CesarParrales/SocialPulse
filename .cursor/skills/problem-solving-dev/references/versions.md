# Versiones, Deprecaciones y Breaking Changes

## El Problema de las Versiones

```
La mayoría de los problemas "inexplicables" tienen una causa de versión:
→ El tutorial fue escrito para v2, tienes v3
→ La solución de Stack Overflow era para v1, estás en v4
→ El paquete A espera la v2 de B, pero tienes la v3
→ La feature que usas fue deprecada en v2.5 y removida en v3.0
→ El comportamiento cambió silenciosamente en un minor release

Primer comando a correr antes de buscar:
  npm list [paquete]                  # versión instalada en Node
  composer show [vendor/paquete]      # versión en PHP
  pip show [paquete]                  # versión en Python
  flutter pub deps                    # árbol de dependencias Flutter
  cat package-lock.json | grep '"[paquete]"' -A 2  # versión resuelta
```

---

## Semver — Leer el Número de Versión

```
Semantic Versioning: MAJOR.MINOR.PATCH

MAJOR (X.0.0):
→ Breaking changes — algo que usabas ya no funciona igual
→ SIEMPRE leer el migration guide antes de actualizar
→ Puede requerir cambios en tu código

MINOR (0.X.0):
→ Features nuevas backwards-compatible — no debería romper
→ A veces introduce deprecaciones (la feature vieja todavía funciona pero avisa)
→ Leer release notes por si cambió algún comportamiento por defecto

PATCH (0.0.X):
→ Bug fixes — generalmente seguro actualizar
→ A veces el "bug fix" cambia comportamiento que dependías
→ Leer las release notes si algo se rompe después de un patch

Modificadores:
→ ^1.2.3 (npm): acepta minor y patch updates → ^1.x.x
→ ~1.2.3 (npm): acepta solo patch updates → 1.2.x
→ 1.2.3 (sin modificador): versión exacta
→ * o latest: la más reciente — peligroso en producción

Regla de oro: en producción, ser explícito con las versiones
→ package-lock.json / composer.lock en git
→ npm ci en lugar de npm install (usa el lockfile exactamente)
→ composer install en lugar de composer update en deploy
```

---

## Detectar Incompatibilidades Entre Paquetes

```
Síntomas de incompatibilidad:
→ "peer dependency conflict" en npm
→ "requires X@^2.0 but found X@3.1" en composer
→ Comportamiento extraño sin error claro (la versión instalada no es la esperada)
→ Funciona en local pero no en CI (diferentes lockfiles)

Investigar el árbol de dependencias:

npm:
  npm list --depth=0              # dependencias directas con versión
  npm list [paquete]              # quién requiere ese paquete y qué versión
  npm outdated                    # qué tiene actualización disponible
  npx npm-check-updates           # ver qué se puede actualizar

Composer:
  composer depends [vendor/pkg]   # qué requiere ese paquete
  composer why [vendor/pkg]       # por qué está instalado
  composer outdated               # qué tiene versión más reciente

Resolver conflictos de dependencias:

Estrategia 1: actualizar ambos paquetes a versiones compatibles
  npm install packageA@latest packageB@latest
  Verificar que las versiones se requieren mutuamente

Estrategia 2: forzar resolución (con riesgo)
  package.json → "resolutions": { "paquete": "versión" }
  composer.json → "conflict": { ... }
  RIESGO: puede funcionar en algunos casos y fallar en otros

Estrategia 3: ir al repositorio del conflicto
  Buscar issue: "incompatible with packageB@3"
  A veces hay un fork o versión específica que resuelve la incompatibilidad

Estrategia 4: aislar en versión anterior
  Si un update reciente causó el problema:
  npm install packageA@lastWorkingVersion
  Documentar qué versión funciona hasta que se resuelva oficialmente
```

---

## Deprecaciones — Manejarlas con Tiempo

```
Una deprecación es un aviso antes de la eliminación.
El código sigue funcionando pero el reloj corre.

Detectar deprecaciones activas:
→ Warnings en la consola/logs durante ejecución
→ "deprecated" en el CHANGELOG de la versión actual
→ Comentarios @deprecated en el código fuente
→ Documentación que dice "use X instead"
→ npm audit o herramientas equivalentes

Proceso para manejar una deprecación:

PASO 1 — Entender qué fue deprecado y por qué
  → Leer el warning completo
  → Buscar el CHANGELOG o PR donde se deprecó
  → Entender qué reemplaza la API deprecada y por qué cambió

PASO 2 — Encontrar la alternativa correcta
  → La documentación oficial normalmente dice qué usar en su lugar
  → Si no: buscar en los issues "migration from [old] to [new]"
  → No siempre es un reemplazo 1:1 — puede ser diferente conceptualmente

PASO 3 — Estimar el impacto
  → grep -r "funcionDeprecada\|APIDeprecada" src/ | wc -l
  → ¿Es una función usada en 1 lugar o en 50?
  → ¿El cambio requiere refactoring o es un rename simple?

PASO 4 — Planificar la migración
  → Si la eliminación es en la próxima versión mayor: URGENTE
  → Si es en 2 versiones: crear ticket, agregar al backlog
  → NUNCA ignorar deprecaciones porque "todavía funciona"

Señal de cuándo va a ser eliminada:
  → "This will be removed in v4.0" → urgente si estás en v3.x
  → "This was removed in v4.0" → ya es un breaking change si actualizaste
  → Sin fecha anunciada → buscar en roadmap del proyecto
```

---

## Migration Guides — Leerlas Completas

```
Cuando hay una guía de migración oficial, leerla COMPLETA antes de migrar.

Por qué se omite y por qué es un error:
→ La sección relevante puede estar en la mitad de la guía
→ Los pasos tienen orden — saltarse uno rompe los siguientes
→ Los "known issues" al final de la guía evitan horas de debugging

Estructura típica de una migration guide:
1. Prerequisites (versión mínima requerida antes de migrar)
2. Automated migration tools (si existen — usarlos primero)
3. Breaking changes con código before/after
4. Optional changes (mejoras que no son obligatorias)
5. Known issues durante la migración
6. Help resources

Herramientas de codemods — automatizar la migración:
→ React: npx react-codemod [transform] [path]
→ Next.js: npx @next/codemod [transform] [path]
→ Laravel: Rector para refactoring automático
→ Angular: ng update (migra automáticamente)

Cuándo confiar en el codemod:
✅ Cambios de nombre de funciones (rename trivial)
✅ Cambios de imports
✅ Cambios de sintaxis equivalentes
❌ Cambios de comportamiento (el codemod cambia la forma pero no la lógica)
❌ Cuando el código que migra es complejo o personalizado
```

---

## Lockfiles — La Fuente de Verdad de las Versiones Instaladas

```
Los lockfiles garantizan que todos instalan las mismas versiones.
Son la fuente de verdad de qué está realmente instalado.

package-lock.json / yarn.lock (Node):
  → No editar manualmente
  → Siempre commitear en git
  → npm ci en CI usa el lockfile exactamente (no resuelve de nuevo)
  → Si hay conflictos de merge: resolver con npm install (no merge manual)

composer.lock (PHP):
  → No editar manualmente
  → Siempre commitear en git
  → composer install (no composer update) en producción y CI
  → composer update = regenerar el lockfile (puede cambiar versiones)

Comparar lockfiles para encontrar qué cambió:
  git diff HEAD~1 package-lock.json | grep '"version"' | head -30
  git diff HEAD~1 composer.lock | grep '"version"' | head -30

Cuando el lockfile causa problemas:
→ "funciona en mi máquina" = lockfiles diferentes
→ Solución: eliminar lockfile + node_modules/vendor → instalar de nuevo
→ En CI: asegurar que usa npm ci / composer install (no install/update)
```

---

## Environments — El Bug que Solo Pasa en Producción

```
Causas más comunes de "funciona en local, falla en producción":

Variables de entorno:
→ ¿Están todas las variables de producción configuradas?
→ ¿Los valores son correctos? (URL de API, credenciales)
→ ¿Hay variables que en local tienen valor por defecto y en prod no?

Versiones de runtime:
→ ¿Node/PHP/Python es la misma versión?
→ node --version en local vs lo que tiene el servidor
→ Solución: .nvmrc, .tool-versions o especificar en Dockerfile

Dependencias de sistema:
→ ¿Las extensiones PHP instaladas son las mismas?
→ ¿Las librerías del sistema (imagemagick, etc.) están en prod?
→ Solución: documentar en Dockerfile o requirements del sistema

Cache y estado persistente:
→ En local el caché se limpia entre pruebas
→ En producción puede haber caché stale de deploys anteriores
→ Solución: php artisan cache:clear / redis-cli FLUSHDB tras deploy

Permisos de filesystem:
→ En local el usuario tiene permisos para todo
→ En producción el proceso corre como un usuario sin privilegios
→ Solución: verificar permisos de storage/, logs/, tmp/

Límites de recursos:
→ Memory limit (PHP: memory_limit, Node: --max-old-space-size)
→ Timeouts de conexión (más estrictos en prod)
→ Límites de uploads (nginx: client_max_body_size)
→ Número de procesos concurrentes (pool de conexiones DB)
```
