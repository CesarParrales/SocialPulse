# Jerarquía de Fuentes y Cómo Leerlas

## Mapa de Fuentes por Tipo de Problema

```
PROBLEMA DE SINTAXIS / API CAMBIADA
  Prioridad 1: Documentación de la versión exacta instalada
  Prioridad 2: CHANGELOG de la versión instalada
  Prioridad 3: Migration Guide (si existe)
  Trampa: googlear sin versión → llega la doc de la versión más reciente

PROBLEMA DE COMPORTAMIENTO INESPERADO (no es error)
  Prioridad 1: GitHub Issues cerrados del repo (buscar el comportamiento)
  Prioridad 2: GitHub Discussions
  Prioridad 3: Tests del proyecto que cubren ese comportamiento
  Trampa: asumir que es un bug cuando es comportamiento intencional

INCOMPATIBILIDAD ENTRE PAQUETES
  Prioridad 1: peer dependencies en package.json/composer.json de cada paquete
  Prioridad 2: Issues del repo con el nombre del otro paquete en el título
  Prioridad 3: Lockfile para ver qué versión se resolvió
  Trampa: actualizar solo un paquete sin revisar sus dependencias

ERROR DE RUNTIME INTERMITENTE
  Prioridad 1: Logs completos con stack trace (no el snippet del error)
  Prioridad 2: Issues con "flaky" o "intermittent" en el título
  Prioridad 3: Condiciones de race condition en el código fuente
  Trampa: reproducir sin el contexto de producción (carga, concurrencia)

DEPRECACIÓN QUE ROMPIÓ ALGO
  Prioridad 1: CHANGELOG de la versión que introdujo el breaking change
  Prioridad 2: Migration Guide oficial
  Prioridad 3: Issues con "deprecated" o "removed" en el título
  Trampa: saltar a la solución sin entender qué reemplaza qué y por qué

PERFORMANCE INESPERADAMENTE BAJA
  Prioridad 1: Profiler (antes de buscar soluciones)
  Prioridad 2: Issues con "performance" o "slow" + tu versión
  Prioridad 3: PRs que optimizaron el área problemática
  Trampa: optimizar sin datos del profiler → optimizar lo incorrecto
```

---

## Cómo Leer la Documentación Oficial con Criterio

```
Verificar siempre ANTES de leer:
  ¿La versión de la doc coincide con la versión instalada?
  → npm list [paquete] | head -3
  → composer show [vendor/paquete]
  → pip show [paquete]

Señales de que la doc no aplica a tu versión:
  - La API que muestra no existe en tu código
  - El import path es diferente al que tienes
  - Menciona configuración que no reconoces
  - Los ejemplos usan sintaxis que tu versión no soporta

Navegar la documentación de versión específica:
  GitHub: github.com/repo/blob/v2.3.1/docs/  (tag de versión exacta)
  npm: npmjs.com/package/react/v/18.2.0
  Packagist: packagist.org/packages/vendor/pkg#2.3.1
  PyPI: pypi.org/project/package/1.2.3/

Cuando la documentación dice "see the examples":
  → Los ejemplos oficiales son la fuente más confiable de uso correcto
  → Clonar el repo de ejemplos y comparar con tu implementación
  → Los ejemplos a veces tienen bugs — verificar que tengan tests pasando

Secciones que se leen menos pero son más útiles:
  → "Known issues" o "Limitations"
  → "Troubleshooting" o "FAQ"
  → "Upgrading" o "Migration"
  → "Configuration reference" completo (no solo el happy path)
```

---

## Leer un CHANGELOG con Eficiencia

```
El CHANGELOG es la documentación de qué cambió entre versiones.
Es la primera fuente cuando "funcionaba antes y ahora no".

Tipos de entradas y qué significan:

BREAKING CHANGE / MAJOR:
  Algo que se usaba ya no funciona igual
  → Buscar aquí cuando actualizaste una versión mayor
  → Siempre viene con el por qué del cambio
  Ejemplo: "BREAKING: config.database.host must now be an array"

DEPRECATED:
  Todavía funciona pero dejará de funcionar
  → El aviso de cuándo dejará de funcionar
  → La alternativa recomendada
  Ejemplo: "Deprecated: use env() instead of getenv() in config files"

FIXED:
  Un bug que fue corregido
  → Buscar aquí cuando tu error parece un bug del framework
  → Incluye el número de issue/PR que lo resolvió
  Ejemplo: "Fixed: session not persisting when using Redis driver (#3421)"

CHANGED:
  Comportamiento que cambió sin ser breaking (según el autor)
  → A veces es breaking para casos de uso específicos
  → Leer con cuidado si el comportamiento que cambió es el que usas

Estrategia de lectura cuando "algo se rompió al actualizar":
  1. Identificar la versión donde funcionaba y la actual
  2. Abrir CHANGELOG en la versión actual
  3. Leer de más reciente a más antiguo SOLO las entradas BREAKING y CHANGED
  4. Si no está ahí, buscar en CHANGELOG de versiones intermedias
  5. Si tampoco, buscar en los Issues con "regression" en el título
```

---

## Documentación Oficial vs Realidad — Casos Comunes

```
CASO 1: El ejemplo funciona, tu código no

Diferencias típicas a revisar:
→ ¿Instalaste la misma versión del ejemplo?
→ ¿Tienes todas las dependencias peers?
→ ¿Tu entorno tiene variables de entorno necesarias?
→ ¿El orden de los middlewares/providers importa?
→ ¿Hay configuración global implícita en el ejemplo que no ves?

Técnica: diff visual entre el ejemplo mínimo y tu código
  Eliminar tu código hasta que sea igual al ejemplo → agregar de vuelta parte por parte

CASO 2: La documentación dice X, el código hace Y

Causa probable:
→ La doc describe el comportamiento de una versión futura (roadmap)
→ La doc tiene un bug (sí, pasa)
→ El comportamiento cambió sin actualizarse la doc
→ Estás leyendo la doc de una versión diferente

Verificar:
→ Leer el código fuente de la función directamente
→ Los tests del proyecto revelan el comportamiento esperado real
→ Buscar issue en el repo con "documentation" + el comportamiento

CASO 3: Dos fuentes dicen cosas contradictorias

Resolución:
→ La más reciente gana si ambas son del mismo proyecto
→ El código fuente gana sobre cualquier documentación
→ El maintainer del proyecto gana sobre contribuidores externos
→ La solución con más contexto/explicación suele ser más confiable

CASO 4: Nadie más tiene este problema (o eso parece)

Antes de asumir que es único:
→ Buscar en inglés aunque el proyecto sea en otro idioma
→ Buscar el mensaje de error exacto entre comillas
→ Buscar variaciones: el error en diferentes versiones puede tener mensajes distintos
→ Buscar el nombre del archivo o función que falla
→ El problema puede estar reportado con diferente terminología
```

---

## Fuentes por Ecosistema

```
PHP / Laravel:
  Oficial:    laravel.com/docs/{version}
  Issues:     github.com/laravel/framework/issues
  Changelog:  github.com/laravel/framework/blob/master/CHANGELOG.md
  Comunidad:  laracasts.com/discuss, laravel.io/forum
  Paquetes:   packagist.org → github del paquete
  Tip:        buscar en issues con ?q=is:issue+[error message]

JavaScript / Node / React:
  Oficial:    versión en npmjs.com o docs del framework
  Issues:     github.com/[org]/[repo]/issues
  Changelog:  github.com/[org]/[repo]/releases
  Comunidad:  github discussions, Discord oficial del framework
  Bundler:    comparar package-lock.json o yarn.lock con lo esperado
  Tip:        node_modules/[paquete]/CHANGELOG.md tiene el changelog local

Python:
  Oficial:    docs.python.org/{version} o docs del paquete en readthedocs
  Issues:     github o PyPI project links
  Changelog:  pypi.org/project/{pkg}/#history
  Comunidad:  Python Discord, project's GitHub Discussions

Flutter / Dart:
  Oficial:    docs.flutter.dev (incluye versión selector)
  Issues:     github.com/flutter/flutter/issues
  Changelog:  CHANGELOG en pub.dev/{package}
  Comunidad:  flutter.dev/community, GitHub Discussions
  Tip:        flutter doctor -v antes de cualquier otra investigación

iOS / Swift:
  Oficial:    developer.apple.com/documentation
  Issues:     developer.apple.com/forums (Apple Developer Forums)
  Changelog:  release notes en developer.apple.com
  Comunidad:  Swift Forums (forums.swift.org)
  Tip:        release notes de Xcode siempre incluyen breaking changes

Android / Kotlin:
  Oficial:    developer.android.com/reference
  Issues:     issuetracker.google.com
  Changelog:  developer.android.com/jetpack/androidx/releases
  Comunidad:  stackoverflow con tag [android] o [kotlin]
  Tip:        migrationguide en developer.android.com para versiones mayor
```
