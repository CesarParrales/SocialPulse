# Error Tracking — Sentry y Alternativas

> ⚠️ Los precios y free tiers citados aquí son **datos con caducidad —
> revisar** en la web del proveedor antes de presupuestar.

## Por Qué Sentry Primero

```
Si solo instalas UNA herramienta de observabilidad → que sea Sentry.

Qué te da Sentry que no tienes sin él:
→ Captura automática de excepciones no manejadas
→ Stack trace completo con contexto (usuario, request, variables)
→ Agrupación inteligente de errores (1000 instancias del mismo bug = 1 issue)
→ Asignación de errores a deploys específicos ("este error empezó con el deploy de hoy")
→ Performance monitoring básico incluido en el plan free
→ Alertas cuando un error nuevo aparece o cuando la frecuencia sube

ROI del plan gratuito de Sentry:
→ Free: 5,000 errores/mes — suficiente para la mayoría de proyectos small
→ Developer (gratis): proyectos personales y small projects
→ Si el proyecto genera más de 5k errores/mes → Team plan $26/mes
→ Costo de 1 hora de debugging sin Sentry: más que un mes de Sentry
```

---

## Sentry en Laravel — Setup Completo

```bash
composer require sentry/sentry-laravel
```

```php
// .env
SENTRY_LARAVEL_DSN=https://[KEY]@sentry.io/[PROJECT_ID]
SENTRY_TRACES_SAMPLE_RATE=0.1  # 10% de requests para performance monitoring
SENTRY_PROFILES_SAMPLE_RATE=0.1 # 10% para profiling (plan de pago)

// config/sentry.php
return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'release' => env('SENTRY_RELEASE', 'local@' . trim(exec('git log --pretty="%H" -n1 HEAD'))),
    'environment' => app()->environment(),
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),

    // Ignorar errores que no necesitamos rastrear
    'ignore_exceptions' => [
        \Illuminate\Auth\AuthenticationException::class,    // 401s son esperados
        \Illuminate\Auth\Access\AuthorizationException::class, // 403s son esperados
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class, // 404s
        \Illuminate\Validation\ValidationException::class,  // validaciones son expected
    ],

    // Scrubbing de datos sensibles — CRÍTICO
    'send_default_pii' => false,  // no enviar emails/IPs por defecto
];

// bootstrap/app.php — capturar excepciones no manejadas
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->report(function (\Throwable $e) {
        if (app()->bound(\Sentry\State\HubInterface::class)) {
            \Sentry\captureException($e);
        }
    });
})
```

---

## Enriquecer el Contexto de los Errores

```php
// Agregar contexto del usuario a Sentry (para saber qué usuario tuvo el error)
// app/Http/Middleware/SetSentryUserContext.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sentry\State\Scope;
use function Sentry\configureScope;

class SetSentryUserContext
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            configureScope(function (Scope $scope): void {
                $scope->setUser([
                    'id'    => auth()->id(),
                    'email' => auth()->user()->email,
                    // NO incluir datos sensibles como passwords, tokens, etc.
                ]);
            });
        }

        return $next($request);
    }
}

// Capturar errores esperados con contexto adicional
try {
    $result = $paymentGateway->charge($order);
} catch (PaymentDeclinedException $e) {
    // Error esperado — capturar con contexto de negocio
    \Sentry\captureException($e, [
        'tags' => [
            'payment.gateway' => 'stripe',
            'payment.type'    => $order->payment_type,
        ],
        'extra' => [
            'order_id'    => $order->id,
            'amount_cents' => $order->total_cents,
            // NO: número de tarjeta, CVV, datos del cliente
        ],
        'level' => \Sentry\Severity::warning(), // no es un error crítico
    ]);

    throw $e; // re-throw para que lo maneje el controller
}
```

---

## Sentry en Node.js/NestJS

```typescript
// npm install @sentry/node @sentry/profiling-node

// main.ts — inicializar ANTES de cualquier import
import * as Sentry from '@sentry/node';
import { nodeProfilingIntegration } from '@sentry/profiling-node';

Sentry.init({
  dsn: process.env.SENTRY_DSN,
  environment: process.env.NODE_ENV,
  release: process.env.APP_VERSION,
  tracesSampleRate: parseFloat(process.env.SENTRY_TRACES_SAMPLE_RATE || '0.1'),

  integrations: [
    nodeProfilingIntegration(),
    Sentry.httpIntegration(),       // captura requests HTTP
    Sentry.expressIntegration(),     // captura errores de Express
    Sentry.prismaIntegration(),      // tracing de queries Prisma
  ],

  // No enviar errores esperados
  beforeSend(event) {
    if (event.exception?.values?.[0]?.type === 'UnauthorizedException') {
      return null; // no reportar 401s
    }
    return event;
  },
});

// Global exception filter para NestJS
@Catch()
export class SentryExceptionFilter implements ExceptionFilter {
  catch(exception: unknown, host: ArgumentsHost) {
    Sentry.captureException(exception);

    const ctx = host.switchToHttp();
    const response = ctx.getResponse<Response>();
    const status = exception instanceof HttpException
      ? exception.getStatus()
      : HttpStatus.INTERNAL_SERVER_ERROR;

    response.status(status).json({
      statusCode: status,
      message: status >= 500 ? 'Internal server error' : (exception as HttpException).message,
    });
  }
}

// Contexto de usuario
Sentry.setUser({ id: userId, email: userEmail });
// Limpiar al logout:
Sentry.setUser(null);
```

---

## Alertas en Sentry — Configuración Útil

```
Alertas que valen la pena configurar:

1. NUEVO ISSUE (la más importante)
   Trigger: cuando aparece un error que nunca habíamos visto
   → Te enteras de problemas nuevos en el mismo momento que ocurren
   → Configurar: Alerts → Issue Alerts → "A new issue is created"

2. ERROR RATE SPIKE
   Trigger: cuando la frecuencia de errores sube > X% en 1 hora
   → Detecta deploys problemáticos antes de que el cliente lo note
   → Configurar: umbral basado en el baseline (primeras 2 semanas en prod)

3. ERROR REGRESSION
   Trigger: cuando un error que estaba resuelto vuelve a aparecer
   → Previene regressions silenciosas
   → Configurar: "An issue that has been resolved is seen again"

4. HIGH VOLUME SINGLE ISSUE
   Trigger: cuando un solo error ocurre > 100 veces en 1 hora
   → Indica un problema sistémico, no un error puntual
   → Configurar: Volume thresholds por issue

Canales de alerta por severidad:
  Crítico:   PagerDuty / llamada telefónica (si hay SLA)
  Alto:      Slack #alerts-críticos + email
  Medio:     Slack #alerts-info
  Bajo:      Solo en el dashboard de Sentry (no interrumpir)
```

---

## Alternativas a Sentry

```
BUGSNAG:
  Pros: interface más simple, buena integración con Jira
  Contras: más caro que Sentry para volúmenes similares
  Cuándo: equipo que ya usa Jira y prefiere todo integrado

ROLLBAR:
  Pros: muy buena API, fácil integración con muchos frameworks
  Contras: menos features que Sentry en el tier gratuito
  Cuándo: legacy projects con integraciones específicas

TRACKJS:
  Pros: excelente para frontend JavaScript específicamente
  Contras: solo frontend
  Cuándo: SPA-heavy donde los errores de JS son el mayor problema

GLITCHTIP (open source, self-hosted):
  Pros: compatible con el SDK de Sentry, gratuito, datos en tu infra
  Contras: mantenimiento propio, menos features que Sentry cloud
  Cuándo: cliente con restricciones de datos que no puede usar SaaS externo
  Instalación: docker run -p 8000:8000 ghcr.io/glitchtip/glitchtip

HONEYBADGER:
  Pros: muy simple, buen tier gratuito para proyectos pequeños
  Contras: menos capacidad de análisis que Sentry
  Cuándo: proyectos muy pequeños donde la simplicidad importa

Recomendación para un estudio:
  → Sentry para todos los proyectos nuevos
  → Una sola cuenta de organización, múltiples proyectos
  → Mejor negociar un plan Team/Business por volumen que pagar por proyecto
```
