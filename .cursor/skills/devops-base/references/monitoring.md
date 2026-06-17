# Monitoreo y Logs

Cobertura de esta reference: monitoreo BASE (uptime, logs centralizados, alertas de servidor). Observabilidad avanzada (tracing, OpenTelemetry, SLOs, dashboards complejos) → skill `monitoring-observability`.

## UptimeRobot (gratuito, suficiente para empezar)

```
Monitorear:
- https://api.myapp.com/health      (cada 5 min)
- https://myapp.com                  (landing page)
- Puerto 5432 (PostgreSQL)           (si está expuesto)

Alertas a:
- Email del equipo
- Canal de Slack #alerts
- PagerDuty si hay SLA

Configuración del Health Endpoint que necesita UptimeRobot:
GET /health → 200 OK (si todo bien)
GET /health → 503 Service Unavailable (si hay problema)
```

---

## Logging con Papertrail / Logtail

```bash
# En Docker — enviar logs a Papertrail
# docker-compose.yml — logging driver
services:
  api:
    logging:
      driver: syslog
      options:
        syslog-address: "udp://logs.papertrailapp.com:XXXXX"
        tag: "myapp-api"

  horizon:
    logging:
      driver: syslog
      options:
        syslog-address: "udp://logs.papertrailapp.com:XXXXX"
        tag: "myapp-horizon"
```

```php
// Laravel — structured logging con contexto
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'papertrail'],
    ],
    'papertrail' => [
        'driver' => 'monolog',
        'level' => env('LOG_LEVEL', 'info'),
        'handler' => SyslogUdpHandler::class,
        'handler_with' => [
            'host' => env('PAPERTRAIL_URL'),
            'port' => env('PAPERTRAIL_PORT'),
        ],
        'formatter' => Monolog\Formatter\LineFormatter::class,
        'formatter_with' => [
            'format' => null,
            'dateFormat' => null,
            'allowInlineLineBreaks' => true,
        ],
    ],
]

// Logging con contexto en el código
Log::info('Order created', [
    'order_id'   => $order->id,
    'user_id'    => $order->user_id,
    'total'      => $order->total_cents,
    'duration_ms' => $duration,
]);

// NO hacer esto — sin contexto searchable
Log::info("Order $orderId created for user $userId with total $total");
```

---

## Métricas del Servidor — Script de Monitoreo

Script completo, autocontenido. Instalarlo en el servidor como `/opt/scripts/health-check.sh` (`chmod +x`) y ejecutarlo cada 5 min via cron: `*/5 * * * * /opt/scripts/health-check.sh`.

```bash
#!/bin/bash
# /opt/scripts/health-check.sh — ejecutar cada 5 min via cron

SLACK_WEBHOOK="https://hooks.slack.com/services/XXX"
APP_URL="https://api.myapp.com"
THRESHOLD_DISK=85    # % de disco
THRESHOLD_CPU=90     # % de CPU
THRESHOLD_MEM=90     # % de memoria

notify_slack() {
    curl -s -X POST $SLACK_WEBHOOK \
      -H 'Content-type: application/json' \
      -d "{\"text\": \"⚠️ Alert: $1\"}"
}

# Verificar disco
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | tr -d '%')
if [ $DISK_USAGE -gt $THRESHOLD_DISK ]; then
    notify_slack "Disk usage at ${DISK_USAGE}% on $(hostname)"
fi

# Verificar CPU (promedio 5 min)
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1 | cut -d'.' -f1)
if [ $CPU_USAGE -gt $THRESHOLD_CPU ]; then
    notify_slack "CPU usage at ${CPU_USAGE}% on $(hostname)"
fi

# Verificar memoria
MEM_USAGE=$(free | awk 'NR==2 {printf "%.0f", $3/$2*100}')
if [ $MEM_USAGE -gt $THRESHOLD_MEM ]; then
    notify_slack "Memory usage at ${MEM_USAGE}% on $(hostname)"
fi

# Verificar app health
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" $APP_URL/health)
if [ $HTTP_STATUS -ne 200 ]; then
    notify_slack "Health check failed: $APP_URL/health returned $HTTP_STATUS"
fi

# Verificar containers corriendo
STOPPED=$(docker compose ps --filter "status=exited" --format "{{.Name}}")
if [ -n "$STOPPED" ]; then
    notify_slack "Stopped containers on $(hostname): $STOPPED"
fi
```

---

## Grafana + Prometheus (para escala)

Si llegas a necesitar dashboards, SLOs o tracing distribuido, pasa a la skill `monitoring-observability`. Setup mínimo de arranque:

```yaml
# docker-compose.monitoring.yml
services:
  prometheus:
    image: prom/prometheus:latest
    volumes:
      - ./monitoring/prometheus.yml:/etc/prometheus/prometheus.yml
      - prometheus_data:/prometheus
    command:
      - '--config.file=/etc/prometheus/prometheus.yml'
      - '--storage.tsdb.retention.time=15d'
    ports: ["9090:9090"]

  grafana:
    image: grafana/grafana:latest
    volumes:
      - grafana_data:/var/lib/grafana
      - ./monitoring/grafana/dashboards:/var/lib/grafana/dashboards
    environment:
      GF_SECURITY_ADMIN_PASSWORD: ${GRAFANA_PASSWORD}
      GF_INSTALL_PLUGINS: grafana-piechart-panel
    ports: ["3001:3000"]

  node-exporter:
    image: prom/node-exporter:latest
    volumes:
      - /proc:/host/proc:ro
      - /sys:/host/sys:ro
      - /:/rootfs:ro
    command:
      - '--path.procfs=/host/proc'
      - '--path.sysfs=/host/sys'

volumes:
  prometheus_data:
  grafana_data:
```

```yaml
# monitoring/prometheus.yml
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'node'
    static_configs:
      - targets: ['node-exporter:9100']

  - job_name: 'nestjs'
    static_configs:
      - targets: ['api:3000']
    metrics_path: '/metrics'   # endpoint Prometheus en la app

  - job_name: 'postgres'
    static_configs:
      - targets: ['postgres-exporter:9187']
```

---

## Alertas Críticas — Configurar desde Día 1

```
Prioridad 1 — Alertar inmediatamente (PagerDuty/SMS):
- App health check falla > 2 min consecutivos
- Error rate > 5% en últimos 5 min
- Tiempo de respuesta p95 > 5 segundos
- Disco > 90%

Prioridad 2 — Alertar a Slack (durante horario laboral):
- CPU sostenida > 80% por 15 min
- Memoria > 85%
- Queue de jobs con > 1000 pendientes
- Errores 5xx > 10 en últimos 5 min
- Deploy fallido

Prioridad 3 — Dashboard, sin alerta activa:
- Requests per minute
- Response time promedio
- Jobs procesados por hora
- Usuarios activos
```
