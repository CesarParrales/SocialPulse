# Alertas y On-Call

> ⚠️ Los precios y free tiers citados aquí son **datos con caducidad —
> revisar** en la web del proveedor antes de presupuestar.

## El Problema de las Alertas Mal Configuradas

```
Demasiadas alertas = alert fatigue = nadie responde a las alertas.
Pocas alertas = incidentes sin detectar = el cliente llama primero.

Principio de diseño de alertas:
  Una alerta debe requerir una acción humana específica.
  Si nadie sabe qué hacer con una alerta → no debería ser una alerta.

Señales de alertas mal configuradas:
  → Alertas que se disparan y se resuelven solas sin intervención
  → Alertas que se silencian repetidamente porque "siempre suenan"
  → El equipo ya no lee las alertas porque "son ruido"
  → El mismo problema aparece múltiples veces porque nadie asignó el fix

Regla práctica:
  Si una alerta suena más de 1 vez por semana sin cambios en el sistema
  → Ajustar el umbral o eliminarla
```

---

## Jerarquía de Severidad de Alertas

```
CRÍTICO (P1) — Despertar a las 3am:
  → Sistema caído o inaccesible
  → Error rate > 50% de requests fallando
  → Datos corrompidos o pérdida de datos
  → Breach de seguridad detectado
  Acción: respuesta inmediata, < 15 minutos
  Canal: PagerDuty / llamada telefónica / SMS

ALTO (P2) — Responder en horario de trabajo extendido:
  → Error rate > 5% de requests fallando
  → Performance degradado significativamente (P95 > 5s)
  → Componente secundario caído (queue, cache)
  → Alerta de seguridad potencial
  Acción: respuesta en < 1 hora
  Canal: Slack + email

MEDIO (P3) — Responder el mismo día:
  → Error rate elevado pero tolerable (> 1%)
  → Queries lentas frecuentes
  → Disco > 80% de uso
  → Certificado SSL expira en < 30 días
  Acción: respuesta en < 4 horas
  Canal: Slack solo

BAJO (P4) — Próxima revisión planificada:
  → Dependencias desactualizadas
  → Métricas de performance ligeramente elevadas
  → Errores esperados con frecuencia ligeramente alta
  Acción: revisar en el próximo sprint o revisión semanal
  Canal: Solo en dashboard, email resumen semanal
```

---

## Configuración de Alertas por Herramienta

```yaml
# UptimeRobot — alertas de uptime
# Configuración recomendada:
# - Alert contact: email del estudio + slack webhook
# - Alert after: 2 consecutive failures (evitar falsos positivos)
# - Recovery alert: sí (saber cuándo se recuperó)
# - Custom message: incluir el URL y el proyecto afectado

# Slack Webhook para alertas de UptimeRobot:
# UptimeRobot → My Settings → Alert Contacts → Add Alert Contact → Webhook (Slack)
```

```php
// Laravel — alertas por email cuando hay muchos errores
// Usando el canal de log con threshold

// config/logging.php
'channels' => [
    'slack' => [
        'driver'   => 'slack',
        'url'      => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Production Alert',
        'emoji'    => ':warning:',
        'level'    => 'critical',  // solo critical y emergency van a Slack
    ],
    'stack' => [
        'driver'   => 'stack',
        'channels' => ['daily', 'slack'],  // log a archivo Y a Slack
    ],
],

// Uso — solo critical+ va a Slack
Log::critical('Payment system is down', [
    'error'      => $e->getMessage(),
    'order_id'   => $order->id,
]);
// → aparece en el canal #alerts de Slack inmediatamente

Log::error('Individual payment failed', [/* ... */]);
// → solo en el archivo de log, no molesta al equipo
```

---

## Alertas de Infraestructura con Grafana

```yaml
# grafana/alerts/disk.yaml — alerta de disco lleno
apiVersion: 1
groups:
  - name: Infrastructure
    folder: Alerts
    interval: 5m
    rules:
      - uid: disk-usage-high
        title: Disk Usage High
        condition: C
        data:
          - refId: A
            datasourceUid: prometheus
            model:
              expr: (node_filesystem_size_bytes - node_filesystem_free_bytes) / node_filesystem_size_bytes * 100
          - refId: C
            type: reduce
            expression: A
            reducer: last
        noDataState: NoData
        execErrState: Error
        for: 5m
        annotations:
          summary: Disk usage is above 85%
          description: 'Disk usage is {{ $value }}% on {{ $labels.instance }}'
        labels:
          severity: warning
        notifications:
          - uid: slack-channel    # configurado en Grafana
```

---

## Slack Bot para Alertas — Setup Simple

```javascript
// Una función de webhook que el equipo puede reutilizar en cualquier proyecto
// Funciona con Slack Incoming Webhooks (gratis)

// lib/slack-alert.js
async function sendSlackAlert({ severity, title, message, details = {}, url = null }) {
  const WEBHOOK_URL = process.env.SLACK_WEBHOOK_URL;
  if (!WEBHOOK_URL) return; // silent fail si no está configurado

  const colors = {
    critical: '#FF0000',
    high:     '#FF6B00',
    medium:   '#FFA500',
    low:      '#36A64F',
    info:     '#0066CC',
  };

  const blocks = [
    {
      type: 'header',
      text: {
        type: 'plain_text',
        text: `${severity.toUpperCase()}: ${title}`,
      },
    },
    {
      type: 'section',
      text: { type: 'mrkdwn', text: message },
    },
  ];

  if (Object.keys(details).length > 0) {
    blocks.push({
      type: 'section',
      fields: Object.entries(details).map(([key, value]) => ({
        type: 'mrkdwn',
        text: `*${key}:*\n${value}`,
      })),
    });
  }

  if (url) {
    blocks.push({
      type: 'actions',
      elements: [{
        type: 'button',
        text: { type: 'plain_text', text: 'View in Sentry' },
        url,
        style: 'danger',
      }],
    });
  }

  await fetch(WEBHOOK_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      attachments: [{
        color: colors[severity] || colors.info,
        blocks,
      }],
    }),
  });
}

// Uso
await sendSlackAlert({
  severity: 'critical',
  title: 'Payment System Down',
  message: 'Stripe API is returning 503 errors. All payments are failing.',
  details: {
    'Error Rate': '100%',
    'Duration':   '5 minutes',
    'Project':    'MyApp Production',
  },
  url: 'https://sentry.io/organizations/my-org/issues/12345/',
});
```

---

## On-Call — Para Proyectos con SLA

```
¿Necesitas on-call?

SÍ si:
→ El cliente tiene un SLA firmado con tiempo de respuesta
→ El sistema procesa transacciones financieras
→ La caída del sistema significa pérdida de ingresos para el cliente (ecommerce, SaaS)
→ El sistema es usado 24/7 globalmente

NO necesitas on-call formal si:
→ Es un sitio de presencia corporativa o blog
→ El sistema solo se usa en horario de negocio
→ No hay SLA firmado
→ El cliente entiende que el soporte es en horario de trabajo

HERRAMIENTAS DE ON-CALL:
  PagerDuty:  el estándar. Tiene free tier para equipos pequeños.
  Opsgenie:   alternativa a PagerDuty, buena integración con Atlassian.
  BetterStack: monitoreo + alertas + on-call en un solo stack.
  Grafana OnCall: open source, self-hosted si tienes Grafana.

ROTACIÓN MÍNIMA DE ON-CALL:
  Semana A: Developer 1 → primary
  Semana B: Developer 2 → primary
  Siempre hay un secondary (el otro) para escalar si el primary no responde.

COMPENSACIÓN DE ON-CALL:
  Si hay on-call → hay compensación económica o días libres.
  El on-call sin compensación genera burnout y rotación.
  Esto va en el contrato del developer o en el plan de mantenimiento del cliente.
```

---

## Status Page — Comunicación Proactiva con el Cliente

```
Una status page pública muestra el estado del sistema al cliente
y reduce las llamadas de "¿está caído el sistema?".

HERRAMIENTAS:
  Instatus (gratis hasta 1 proyecto):     instatus.com
  StatusPage.io (Atlassian):              statuspage.io (de pago)
  BetterStack Status Pages:               gratis en plan básico
  Upptime (GitHub Pages, open source):    gratis, self-hosted

QUÉ MOSTRAR EN LA STATUS PAGE:
  → Estado de cada componente crítico (App, API, Base de datos, Pagos)
  → Historial de incidentes de los últimos 90 días
  → Tiempos de resolución de incidentes anteriores
  → Actualizaciones durante un incidente activo

POR QUÉ VALE LA PENA PARA EL ESTUDIO:
  → El cliente ve proactividad: "nos comunicamos antes de que pregunten"
  → Reduce el volumen de llamadas/mensajes durante incidentes
  → Demuestra que hay procesos maduros de gestión de incidentes
  → Se puede incluir como parte del plan de mantenimiento

TEMPLATE DE ACTUALIZACIÓN DURANTE INCIDENTE:
  14:35 - Investigando: Hemos detectado problemas con [componente].
          Nuestro equipo está investigando activamente.
  14:52 - Identificado: El problema se debe a [causa genérica].
          Estamos implementando la solución.
  15:18 - Resuelto: El sistema está operando normalmente.
          Causa raíz y acciones preventivas publicadas en 24h.
```
