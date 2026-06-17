# Los 3 Pilares — Logs, Métricas y Traces

## Pilar 1 — Logs: El Registro de Qué Pasó

```
Los logs son el "qué exactamente ocurrió" del sistema.
Sin logs estructurados, debuggear en producción es leer ruido.

La diferencia entre log útil y log inútil:

❌ LOG INÚTIL:
[2024-01-15 14:32:11] ERROR: Something went wrong
[2024-01-15 14:32:11] INFO: Processing request
[2024-01-15 14:32:11] DEBUG: Done

→ Sin contexto: ¿qué request? ¿qué usuario? ¿qué datos?
→ Sin estructura: no se puede buscar ni filtrar
→ Sin severidad consistente: ERROR para todo

✅ LOG ÚTIL:
{
  "level": "error",
  "message": "Payment processing failed",
  "timestamp": "2024-01-15T14:32:11.000Z",
  "request_id": "req_abc123",
  "user_id": 4521,
  "order_id": "ORD-98765",
  "error": "Stripe API timeout after 30s",
  "stripe_error_code": "api_connection_error",
  "environment": "production",
  "version": "2.4.1"
}

→ Searchable: puedo buscar todos los errores del user 4521
→ Correlacionable: puedo seguir todo el flujo del request req_abc123
→ Accionable: sé exactamente qué falló y por qué
```

---

## Logging Estructurado en Laravel

```php
// config/logging.php — configurar logging JSON para producción
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
        'ignore_exceptions' => false,
    ],

    'production' => [
        'driver'    => 'monolog',
        'handler'   => \Monolog\Handler\StreamHandler::class,
        'formatter' => \Monolog\Formatter\JsonFormatter::class,
        'with' => [
            'stream' => 'php://stdout',  // para Docker/cloud
        ],
    ],
],

// Niveles de log — usar el correcto:
// emergency: sistema inutilizable
// alert:     acción inmediata requerida
// critical:  condición crítica (componente caído)
// error:     errores que no rompen el flujo pero son un problema
// warning:   situación inusual pero manejada
// notice:    eventos normales pero significativos
// info:      eventos normales de operación
// debug:     información detallada para desarrollo

// Logging con contexto — SIEMPRE incluir contexto
Log::info('User logged in', [
    'user_id'    => $user->id,
    'ip'         => request()->ip(),
    'user_agent' => request()->userAgent(),
]);

Log::error('Order payment failed', [
    'order_id'   => $order->id,
    'user_id'    => $order->user_id,
    'amount'     => $order->total_cents,
    'gateway'    => 'stripe',
    'error_code' => $e->getCode(),
    'error_msg'  => $e->getMessage(),
]);

// Middleware para agregar request_id a todos los logs
// app/Http/Middleware/RequestIdMiddleware.php
public function handle(Request $request, Closure $next): Response
{
    $requestId = Str::uuid()->toString();
    $request->headers->set('X-Request-ID', $requestId);

    // Agregar a todos los logs de este request
    Log::withContext(['request_id' => $requestId]);

    $response = $next($request);
    $response->headers->set('X-Request-ID', $requestId);

    return $response;
}
```

---

## Logging Estructurado en Node.js/NestJS

```typescript
// Pino — el logger más rápido para Node.js
// npm install pino pino-http

// logger.ts
import pino from 'pino';

export const logger = pino({
  level: process.env.LOG_LEVEL || 'info',
  // En producción: JSON; en desarrollo: pretty print
  transport: process.env.NODE_ENV === 'development'
    ? { target: 'pino-pretty', options: { colorize: true } }
    : undefined,
  base: {
    env:     process.env.NODE_ENV,
    version: process.env.APP_VERSION,
  },
  // Redactar campos sensibles automáticamente
  redact: {
    paths: ['req.headers.authorization', 'body.password', 'body.credit_card'],
    censor: '[REDACTED]',
  },
});

// En NestJS — custom logger con Pino
// main.ts
const app = await NestFactory.create(AppModule, {
  bufferLogs: true,
});
app.useLogger(new PinoLogger());

// Uso en servicios
@Injectable()
export class OrderService {
  private readonly logger = new Logger(OrderService.name);

  async processPayment(orderId: string, userId: string) {
    this.logger.log('Processing payment', { orderId, userId });

    try {
      const result = await this.stripe.charge(/* ... */);
      this.logger.log('Payment successful', { orderId, chargeId: result.id });
    } catch (error) {
      this.logger.error('Payment failed', {
        orderId,
        userId,
        error: error.message,
        code: error.code,
      });
      throw error;
    }
  }
}
```

---

## Pilar 2 — Métricas: El Estado del Sistema Ahora

```
Las métricas son datos numéricos a lo largo del tiempo.
Responden: "¿Cómo está el sistema ahora? ¿Está sano?"

Tipos de métricas:

COUNTER (contador acumulativo):
  → Solo sube. Total de requests, total de errores, total de ventas.
  → Útil para tasas: errores por minuto = delta del counter / tiempo

GAUGE (valor puntual):
  → Puede subir y bajar. CPU actual, RAM actual, conexiones activas.
  → Lo que "es" el sistema ahora mismo.

HISTOGRAM (distribución):
  → Latencia de requests distribuida en buckets.
  → P50, P95, P99 de tiempo de respuesta.
  → El promedio miente — el P95 y P99 son los que el usuario siente.

Las 4 señales doradas (Google SRE):
  1. Latency:    tiempo que tardan los requests
  2. Traffic:    cuántos requests/segundo está manejando el sistema
  3. Errors:     tasa de requests que fallan
  4. Saturation: qué tan "lleno" está el sistema (CPU, RAM, disco)

Si monitoreás estas 4 en tiempo real → sabés el estado del sistema.
```

---

## Pilar 3 — Traces: Por Qué Tardó un Request

```
Un trace sigue el camino de un request a través de todos los servicios.

Para un monolito Laravel/Node típico:
→ Un trace muestra: tiempo en el controller + tiempo en DB + tiempo en cache + tiempo de respuesta
→ Identifica exactamente qué query o qué función está tardando

Para microservicios:
→ Un trace atraviesa múltiples servicios
→ Muestra exactamente en qué servicio se está perdiendo el tiempo
→ Sin traces, es casi imposible debuggear performance en microservicios

Terminología:
  Trace:  el viaje completo del request (de entrada a salida)
  Span:   un paso individual dentro del trace (una query, una llamada a API)
  Context: los datos que se pasan entre spans (trace_id, span_id)

Cuándo necesitas distributed tracing:
  ✅ Tienes 3+ servicios que se comunican entre sí
  ✅ Los requests cruzan múltiples bases de datos o sistemas
  ✅ Hay colas (jobs) entre el request del usuario y la respuesta
  ✅ Los tiempos de respuesta son lentos y no sabes por qué

Cuándo NO necesitas distributed tracing:
  → Monolito de una sola app → los logs de queries son suficientes
  → Laravel Telescope en local → muestra todo sin tracing
  → Proyectos pequeños → el overhead no vale
```

---

## Correlación entre los 3 Pilares

```
El poder real no está en cada pilar por separado — está en la correlación:

ESCENARIO: El cliente reporta que el sistema "estuvo lento" ayer a las 3pm.

Con los 3 pilares:
1. MÉTRICAS: Veo que a las 15:03 el P95 de latencia subió de 200ms a 4s
             y la CPU subió al 95%
2. LOGS: Busco los logs entre 15:00-15:10 → encuentro 847 slow queries
         todos apuntando a la tabla orders con un LIKE %searchterm%
3. TRACES: Confirmo que el request lento tenía un span de 3.8s en la DB
           para una query específica sin índice

DIAGNÓSTICO en 5 minutos: falta un índice en la columna de búsqueda.
SIN los 3 pilares: horas de debugging a ciegas + reproducir manualmente.

La clave para la correlación:
→ Todos los sistemas deben usar el mismo request_id / trace_id
→ El mismo ID aparece en logs, métricas y traces
→ Un solo ID te lleva de la alerta → al log → al trace
```
