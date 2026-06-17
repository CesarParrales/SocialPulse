# Debugging Sistemático

## El Error Más Común al Debuggear

```
Copiar el mensaje de error en Google → tomar la primera solución → aplicar → no funciona.

Por qué falla:
→ Se saltó el paso de entender QUÉ está fallando y POR QUÉ
→ La solución puede ser para el síntoma, no la causa
→ El contexto (versión, config, stack) puede ser diferente

El debugging es un proceso de eliminación sistemática.
Cada paso reduce el espacio de posibles causas.
```

---

## Proceso de Debugging Sistemático

```
PASO 1 — Leer el error completo, no el snippet

❌ MAL:
"Error: Cannot read properties of undefined (reading 'map')"
→ buscar ese mensaje en Google

✅ BIEN:
Leer el stack trace completo:
  - Línea exacta donde ocurrió el error
  - El call stack: qué función llamó a qué función
  - El contexto: qué variables estaban en scope
  - ¿Es un error síncrono o asíncrono?

El stack trace dice:
→ Dónde: archivo y línea exacta
→ Qué: tipo de error
→ Cómo llegamos ahí: el call stack
→ Cuándo: si hay timestamp, el orden de eventos

PASO 2 — Reproducir de forma mínima

Eliminar código hasta tener el caso más pequeño que reproduce el error.

Por qué:
→ A veces la causa aparece al reducir (error en algo que no sospechaban)
→ Si no se puede reproducir mínimamente → el bug es de estado/timing
→ La reproducción mínima es lo que se necesita para pedir ayuda con precisión

Si no puedes reproducir de forma mínima:
→ El bug es dependiente de estado o datos específicos
→ Agregar logging para capturar el estado exacto cuando falla
→ Puede ser un race condition (buscar "flaky" en los issues del repo)

PASO 3 — Identificar qué cambió

"Funcionaba antes" implica que algo cambió. Buscar:
→ ¿Actualizaste algún paquete? (revisar lockfile con git diff)
→ ¿Cambiaste configuración del entorno?
→ ¿Hay datos diferentes que disparan el error?
→ ¿Cambió el código en un archivo relacionado?
→ ¿Cambió la versión de Node/PHP/Ruby/Python?

git diff HEAD~1 -- package-lock.json    # ver qué paquetes cambiaron
git log --oneline -20                   # ver commits recientes
git bisect                              # encontrar el commit que introdujo el bug

PASO 4 — Aislar la variable

Solo cambiar UNA COSA a la vez.
Si cambias múltiples cosas y funciona → no sabes qué lo arregló.
Si cambias múltiples cosas y no funciona → tienes múltiples variables contaminando.

Técnica: comentar código hasta encontrar qué lo causa
→ Binario: comentar la mitad del código → ¿sigue fallando?
→ Si sí: el problema está en la mitad que quedó
→ Si no: el problema está en la mitad comentada
→ Repetir hasta aislar

PASO 5 — Verificar la hipótesis antes de "arreglar"

Antes de aplicar cualquier solución:
→ ¿Entiendes por qué el error ocurre?
→ ¿La solución ataca la causa o el síntoma?
→ ¿La solución podría causar un problema diferente?

Si no puedes explicar por qué el fix funciona → probablemente no entiende la causa.
```

---

## Logging Estratégico — Ver el Estado Real

```
El logging es más poderoso que el debugging con breakpoints
para problemas de producción, timing, o estado distribuido.

Qué loguear en cada capa:

En el punto de entrada (controller/handler):
  → Input exacto recibido (sanitizado si hay datos sensibles)
  → ID del request/trace para correlacionar logs

En la lógica de negocio:
  → Valores de las variables en puntos de decisión
  → Qué rama tomó el código (if/switch)
  → Resultado de operaciones críticas

En el punto de salida:
  → Output exacto devuelto
  → Tiempo de ejecución si hay problema de performance

Logging para timing problems:
  console.time('operation-name');
  // ... código ...
  console.timeEnd('operation-name');

  // PHP
  $start = microtime(true);
  // ... código ...
  Log::info('Duration', ['ms' => (microtime(true) - $start) * 1000]);

Logging para problemas de estado:
  → Loguear el estado completo del objeto antes y después de la operación
  → No loguear "entré a la función" — loguear con qué datos

Remover logging antes de hacer push:
  → Usar grep -r "console.log\|dd(\|dump(" src/ antes de PR
  → Configurar el linter para detectar prints de debug
```

---

## Herramientas de Debugging por Ecosistema

```
PHP / Laravel:
  Telescope:    dashboard local de requests, queries, jobs, logs
  Ray:          debug con la app Ray (alternativa visual a dd())
  Clockwork:    profiler en el browser DevTools
  Xdebug:       breakpoints reales en el IDE
  dd($var):     dump and die — útil en desarrollo, nunca en producción
  Log::debug(): logging persistente — mejor que dd() para problemas de timing

  Tip Laravel: \Illuminate\Support\Facades\DB::enableQueryLog();
               dd(DB::getQueryLog()); // ver todas las queries ejecutadas

Node.js / NestJS:
  node --inspect: debugger nativo con Chrome DevTools
  console.trace(): stack trace desde cualquier punto
  DEBUG=*:       activar logs de debug de todos los módulos
  clinic.js:     profiler para event loop, memory, I/O

  Tip Node: process._getActiveHandles() // encontrar handles que previenen exit

React:
  React DevTools: inspeccionar state, props, renders
  Why Did You Render: detectar re-renders innecesarios
  Profiler tab:  flamegraph de tiempo de render por componente

  Tip React: console.count('ComponentName render') // contar renders

Flutter:
  flutter analyze: análisis estático
  flutter inspector: widget tree en tiempo real
  debugPrint():  logging que no satura la consola
  Flutter DevTools: profiler, memory, network inspector

  Tip Flutter: assert(() { print(state); return true; }()); // log solo en debug
```

---

## Errores Clásicos y Dónde Buscar

```
"Cannot read properties of undefined"
  Causa común: datos asíncronos que no llegaron todavía
  Buscar en: issues del framework con "undefined" + el contexto
  Fix pattern: optional chaining (?.) o validación antes de acceder

"Module not found" / "Class not found"
  Causa común: autoload no actualizado, path incorrecto, typo
  Verificar: npm install / composer dump-autoload / paths case-sensitive
  Trampa: diferencia entre / y \ en Windows

"CORS error"
  Causa común: configuración de CORS en el servidor, no en el cliente
  Buscar en: docs del framework de servidor
  Trampa: el error aparece en el browser pero la causa está en el servidor

"419 Page Expired" (Laravel)
  Causa: token CSRF faltante o expirado
  Buscar en: docs de CSRF de Laravel para el tipo de request
  Fix: verificar que el form tiene @csrf o el header X-CSRF-TOKEN

"hydration error" (Next.js / SSR)
  Causa: HTML del servidor no coincide con el del cliente
  Buscar en: github.com/vercel/next.js/issues?q=hydration
  Trampa: Date.now(), Math.random(), IDs dinámicos generan diferencias

"deadlock" (DB)
  Causa: dos transacciones esperándose mutuamente
  Buscar en: show processlist en MySQL / pg_locks en PostgreSQL
  Fix pattern: orden consistente de bloqueo de tablas, timeouts explícitos

"413 Request Too Large"
  Causa: límite de tamaño en Nginx/PHP/Node no configurado
  Buscar en: configuración de client_max_body_size (nginx) o upload_max_filesize (PHP)
  Trampa: puede estar limitado en múltiples capas (nginx + php-fpm + framework)
```

---

## Reproducción Mínima — El Arte del MRE

```
Minimum Reproducible Example (MRE) — por qué importa:
→ Obliga a entender el problema (no solo los síntomas)
→ A veces encontrar el MRE resuelve el problema
→ Es lo que permite pedir ayuda con precisión
→ Maintainers solo atienden issues con MRE

Proceso para crear un MRE:
1. Partir del código que falla
2. Eliminar todo lo que no sea necesario para reproducir el error
3. Reemplazar datos reales con datos ficticios mínimos
4. Verificar que el error todavía ocurre
5. Repetir hasta no poder eliminar más

Para proyectos web:
→ CodeSandbox, StackBlitz, CodePen (para frontend)
→ PHPSandbox, 3v4l.org (para PHP)
→ Replit (para Node)
→ DartPad (para Flutter/Dart)
→ GitHub repo mínimo público (para problemas complejos)

Qué incluir en el MRE:
→ Versiones exactas: "React 18.2.0, Next.js 14.1.0, Node 20.x"
→ El código mínimo que reproduce el problema
→ Los pasos exactos para reproducirlo
→ El resultado esperado vs el resultado actual
→ Lo que ya intentaste (para no recibir sugerencias ya probadas)

MRE que no funciona (común):
→ "Aquí está mi repositorio de 50 archivos" → no es mínimo
→ "El error pasa cuando hago X" sin código → no es reproducible
→ "Aquí está la función" sin el contexto de cómo se llama → incompleto
```
