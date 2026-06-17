# GitHub como Fuente de Verdad

## Por Qué GitHub Primero

```
GitHub contiene lo que ninguna documentación tiene:
→ El historial de decisiones de diseño (por qué se hizo así)
→ Los bugs conocidos con workarounds reales de la comunidad
→ Los comportamientos "no es un bug, es una feature" explicados
→ Las soluciones que sí funcionaron (en los comentarios, no la respuesta aceptada)
→ El contexto de por qué algo fue removido o cambiado
→ Las limitaciones intencionales que nadie documenta en el README

La documentación describe lo que debería pasar.
Los issues describen lo que realmente pasa.
```

---

## Anatomía de una Búsqueda de Issues

```
URL base:
github.com/[org]/[repo]/issues

Parámetros de búsqueda:
?q=          texto de búsqueda
is:issue     solo issues (no PRs)
is:open      solo abiertos
is:closed    solo cerrados — MUY IMPORTANTE (aquí están los resueltos)
label:bug    solo bugs
label:enhancement  solo features
label:question     preguntas de la comunidad

Operadores útiles:
"mensaje de error exacto"   → búsqueda exacta
in:title                    → solo en títulos
in:comments                 → buscar en comentarios
author:username             → issues de un usuario
involves:maintainer-name    → donde participó el maintainer
created:>2023-01-01         → después de una fecha

Ejemplos reales:
github.com/laravel/framework/issues?q=is:issue+is:closed+"queue+worker+timeout"
github.com/facebook/react/issues?q=is:issue+"useEffect+cleanup+memory+leak"
github.com/nickmccurdy/next.js/issues?q=is:issue+is:closed+label:bug+"hydration+error"
```

---

## Leer un Issue con Criterio

```
Estructura típica de un issue útil:

TÍTULO:        Revela si es el mismo problema o uno parecido
DESCRIPCIÓN:   Contexto del reporter — ¿mismo stack? ¿misma versión?
REPRODUCTOR:   ¿Hay código mínimo de reproducción? Si sí → muy confiable
COMENTARIOS:   Aquí vive el valor real — leer TODOS
LABELS:        bug / wontfix / duplicate / question
ESTADO:        open / closed — y por qué se cerró

Al leer comentarios, buscar:
→ "This was fixed in vX.Y.Z" → saber qué versión soluciona
→ "Workaround:" → solución temporal si no puedes actualizar
→ "This is by design because..." → el comportamiento es intencional
→ "Duplicate of #NNNN" → el issue real está en otro lado
→ "We won't fix this because..." → la limitación es intencional
→ El último comentario del maintainer (suele resumir la conclusión)

Red flags en un issue:
❌ Nadie del equipo respondió nunca
❌ El issue tiene 3 años y sigue abierto sin actividad reciente
❌ "Works for me" sin contexto de versión o configuración
❌ El reproductor es un repositorio privado que no puedes clonar
```

---

## Leer un PR — Más Valioso que el Issue

```
Los PRs muestran:
→ El código exacto que introduce/modifica el comportamiento
→ La discusión de diseño entre contribuidores y maintainers
→ Por qué se eligió esta implementación sobre otras
→ Los casos edge que consideraron al hacer el cambio
→ Los tests que validan el comportamiento esperado

Cómo llegar al PR relevante:
1. Desde un issue cerrado: "Fixed by #NNNN" → click en el link
2. Desde el blame del archivo: git blame en GitHub muestra el commit
3. Desde el commit: "See commit abc1234" en el CHANGELOG
4. Búsqueda directa: github.com/org/repo/pulls?q=is:merged+"keyword"

Anatomía de un PR útil:

DESCRIPCIÓN:  Explica QUÉ y POR QUÉ (los mejores PRs son muy detallados)
FILES CHANGED: El código exacto que cambió
DIFF:          Línea a línea qué se agregó/quitó/modificó
COMMENTS:      Revisores pidiendo cambios, explicaciones adicionales
COMMITS:       El historial de cómo evolucionó el cambio

Tip: en el diff, los tests son la documentación ejecutable
  → Los tests muestran exactamente qué input produce qué output
  → Copiar el patrón del test para tu caso de uso = solución verificada
```

---

## GitHub Discussions — Decisiones de Diseño

```
Discussions es diferente a Issues:
→ Issues = reportar problemas
→ Discussions = preguntar, explorar, debatir

En Discussions se encuentran:
→ "Why does X work this way?" con respuesta del maintainer
→ RFC (Request for Comments) de features futuras
→ Preguntas de la comunidad con respuestas del equipo core
→ Decisiones de arquitectura explicadas en detalle

Cuándo ir a Discussions:
→ Cuando el comportamiento no es un bug sino una decisión de diseño
→ Cuando necesitas entender el razonamiento detrás de una API
→ Cuando quieres saber si algo está planeado para el roadmap
→ Cuando el issue fue cerrado con "use Discussion for this"

URL: github.com/[org]/[repo]/discussions
Búsqueda: misma sintaxis que Issues
```

---

## Leer el Código Fuente — La Fuente Definitiva

```
Cuándo leer el código fuente directamente:
→ La documentación no explica un comportamiento específico
→ Quieres entender exactamente qué hace una función
→ Sospechas de un bug pero no estás seguro
→ Necesitas extender o sobreescribir un comportamiento

Cómo navegar el código fuente en GitHub:

1. Presionar '.' en cualquier repo → abre VSCode en el browser
2. Usar la búsqueda global: github.com/search?q=repo:org/name+functionName
3. Usar la búsqueda de símbolos: en el archivo, presionar 't' para búsqueda por nombre

Para encontrar la función exacta:
→ Stack trace del error → el archivo y línea exacta
→ grep -r "functionName" node_modules/[paquete]/src/
→ En el repo de GitHub: búsqueda de símbolo con Ctrl+Shift+O

Leer código fuente con contexto:
→ Los comentarios en el código son intenciones del autor
→ Los TODO y FIXME son deuda técnica conocida
→ Los comentarios de deprecación dicen cuándo y qué reemplaza qué
→ El nombre de las variables y funciones revela la intención

Los tests son la mejor documentación del código:
→ Muestran exactamente qué inputs produce qué outputs
→ Los edge cases en tests son los casos que el autor encontró difíciles
→ Un test que falla en tu versión = confirma que es un bug conocido
```

---

## Git Blame y Historia de Commits

```
Cuándo usar git blame:
→ Quieres saber por qué una línea específica está escrita así
→ Necesitas el commit que introdujo un comportamiento
→ Quieres el contexto del PR que modificó una función

En GitHub:
→ Abrir el archivo → botón "Blame" en la esquina superior derecha
→ Cada línea muestra el commit y autor que la introdujo
→ Click en el commit → ver el mensaje y el PR relacionado

Buscar cuándo cambió un comportamiento:
git log --all -p --follow -S "texto de la línea o función" -- path/archivo

En GitHub con la API:
https://api.github.com/repos/[org]/[repo]/commits?path=src/file.js

Leer un commit relevante:
→ Mensaje del commit = el resumen del cambio
→ Descripción extendida = el contexto (si existe)
→ Files changed = exactamente qué cambió
→ PR relacionado = la discusión completa

Tip: los commits de fix de bugs buenos dicen:
"Fix: [descripción]" + "Before: [comportamiento anterior]" + "After: [comportamiento nuevo]"
→ Si el commit no tiene contexto → ir al PR o issue relacionado
```

---

## Releases y Tags — Navegación por Versión

```
Leer las notas de una versión específica:
github.com/[org]/[repo]/releases/tag/v2.3.1

Ver el código en un punto específico del tiempo:
github.com/[org]/[repo]/tree/v2.3.1/src/

Comparar versiones:
github.com/[org]/[repo]/compare/v2.2.0...v2.3.1
→ Muestra todos los commits entre las dos versiones
→ Útil cuando algo se rompió entre dos versiones específicas

Ver qué cambió en UN archivo entre versiones:
github.com/[org]/[repo]/commits/main/src/specific-file.js
→ Historial completo de cambios en ese archivo

Buscar el commit que introdujo el breaking change:
1. Abrir releases entre la versión que funciona y la que no
2. Buscar "breaking" o "changed" en las release notes
3. Click en el commit → ver el PR → leer la discusión
```
