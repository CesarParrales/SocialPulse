# Métricas e Infraestructura

> ⚠️ Los precios y free tiers citados aquí son **datos con caducidad —
> revisar** en la web del proveedor antes de presupuestar.

## Uptime Monitoring — El Mínimo Absoluto

```
Si no haces nada más → haz esto.
Un monitor de uptime te llama antes que el cliente.

UPTIMEROBOT (gratis hasta 50 monitores, cada 5 minutos):
  → El más fácil de configurar
  → Gratis para la mayoría de proyectos

FRESHPING (gratis hasta 50 monitores, cada 1 minuto):
  → Chequeos cada 1 minuto (vs 5 de UptimeRobot en free)
  → Múltiples ubicaciones geográficas

BETTERSTACK UPTIME (ex Logtail):
  → Chequeos cada 30 segundos en plan de pago
  → Integración con logs (el mismo stack)
  → Status pages incluidas

QUÉ MONITOREAR:
  1. El health check endpoint de la app (/health)
  2. El dominio principal (homepage)
  3. El endpoint de login / auth (crítico para el negocio)
  4. La API principal (si es una API)
  5. El panel de admin (si es crítico para el cliente)

CONFIGURACIÓN CORRECTA DEL HEALTH CHECK MONITOR:
  → URL: https://app.cliente.com/health
  → Interval: 1-5 minutos
  → Timeout: 10-15 segundos
  → Expected status: 200
  → Alert channels: email del estudio + email del cliente (opcional)
  → Alert after: 2 consecutive failures (evitar falsos positivos de 1 segundo)
```

---

## Laravel Telescope — Observabilidad Local y Staging

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

```php
// config/telescope.php — habilitar solo en no-producción
'enabled' => env('TELESCOPE_ENABLED', false),

// .env.local / .env.staging
TELESCOPE_ENABLED=true

// Qué muestra Telescope:
// → Requests HTTP con todos los parámetros
// → Queries SQL con tiempo de ejecución
// → Jobs y sus resultados
// → Emails enviados
// → Notificaciones
// → Logs de la aplicación
// → Cache hits y misses
// → Eventos disparados
// → Excepciones
// → Redis commands

// Telescope en staging es invaluable para debuggear
// antes de llegar a producción.

// Para producción con acceso restringido:
// AppServiceProvider.php
use Laravel\Telescope\Telescope;
Telescope::filter(function (IncomingEntry $entry) {
    if ($this->app->environment('production')) {
        return $entry->isReportableException()
            || $entry->type === EntryType::SLOW_QUERY;
    }
    return true;
});
```

---

## Prometheus + Grafana — Para Proyectos que Escalan

```
Cuándo necesitas Prometheus + Grafana:
→ Múltiples servidores o servicios
→ Necesitas métricas de negocio (no solo infraestructura)
→ El cliente quiere dashboards de performance
→ Tienes un plan de SLA formal

Cuándo NO lo necesitas:
→ Un solo servidor con un proyecto small/medium
→ No tienes tiempo para mantenerlo
→ Datadog o New Relic son más cost-effective para tu escala
```

```yaml
# docker-compose.yml — stack de monitoreo completo

services:
  prometheus:
    image: prom/prometheus:latest
    volumes:
      - ./prometheus.yml:/etc/prometheus/prometheus.yml
      - prometheus_data:/prometheus
    ports:
      - "9090:9090"
    command:
      - '--config.file=/etc/prometheus/prometheus.yml'
      - '--storage.tsdb.retention.time=30d'

  grafana:
    image: grafana/grafana:latest
    volumes:
      - grafana_data:/var/lib/grafana
      - ./grafana/dashboards:/etc/grafana/provisioning/dashboards
    ports:
      - "3001:3000"
    environment:
      GF_SECURITY_ADMIN_PASSWORD: ${GRAFANA_PASSWORD}
      GF_USERS_ALLOW_SIGN_UP: 'false'
    depends_on:
      - prometheus

  node_exporter:
    image: prom/node-exporter:latest
    # Exporta métricas del servidor: CPU, RAM, disco, red
    volumes:
      - /proc:/host/proc:ro
      - /sys:/host/sys:ro
    command:
      - '--path.procfs=/host/proc'
      - '--path.sysfs=/host/sys'

volumes:
  prometheus_data:
  grafana_data:
```

```yaml
# prometheus.yml
global:
  scrape_interval: 15s
  evaluation_interval: 15s

scrape_configs:
  - job_name: 'node'
    static_configs:
      - targets: ['node_exporter:9100']

  - job_name: 'laravel_app'
    static_configs:
      - targets: ['app:9191']  # con spatie/laravel-prometheus

  - job_name: 'nginx'
    static_configs:
      - targets: ['nginx_exporter:9113']
```

---

## Métricas de Negocio vs Métricas de Infraestructura

```
Infraestructura (lo que los sysadmins monitorean):
  CPU usage, RAM, disco, network I/O, connections

Aplicación (lo que los developers monitorean):
  Error rate, latencia de endpoints, queue depth, cache hit rate

Negocio (lo que el cliente entiende):
  Pedidos creados por hora, usuarios activos, conversión, revenue

Las métricas de negocio son las más valiosas para el cliente.
Un dashboard que muestra "CPU 45%" no le dice nada.
Un dashboard que muestra "127 pedidos procesados hoy, 99.2% exitosos" sí.
```

```php
// Laravel — métricas de negocio con spatie/laravel-prometheus
// composer require spatie/laravel-prometheus

// Definir métricas propias
use Spatie\LaravelPrometheus\MetricTypes\Counter;

class OrderMetrics
{
    public function register(): void
    {
        Prometheus::addCounter('orders_created_total')
            ->helpText('Total number of orders created')
            ->labels(['status', 'payment_method']);

        Prometheus::addGauge('orders_pending')
            ->helpText('Number of orders currently pending')
            ->value(fn () => Order::pending()->count());

        Prometheus::addHistogram('order_processing_duration_seconds')
            ->helpText('Time to process an order')
            ->buckets([0.1, 0.5, 1, 2, 5, 10]);
    }
}

// Registrar un evento
Prometheus::counter('orders_created_total')
    ->labels(['completed', 'credit_card'])
    ->increment();

// Medir duración
$timer = Prometheus::histogram('order_processing_duration_seconds')->startTimer();
// ... procesar orden ...
$timer->stop();
```

---

## Database Monitoring — Las Queries Lentas

```php
// Laravel — log de queries lentas automático
// AppServiceProvider.php

public function boot(): void
{
    if (config('app.debug')) {
        // En desarrollo: log todas las queries
        DB::listen(function (QueryExecuted $query) {
            if ($query->time > 100) { // > 100ms
                Log::warning('Slow query detected', [
                    'sql'      => $query->sql,
                    'bindings' => $query->bindings,
                    'time_ms'  => $query->time,
                ]);
            }
        });
    }

    // En producción: solo las muy lentas (> 1 segundo)
    DB::whenQueryingForLongerThan(1000, function (Connection $connection, QueryExecuted $event) {
        Log::error('Very slow query in production', [
            'sql'     => $event->sql,
            'time_ms' => $event->time,
        ]);

        // También enviar a Sentry con nivel warning
        \Sentry\captureMessage('Slow query: ' . $event->sql, \Sentry\Severity::warning());
    });
}

// Nueva Relic / Datadog APM capturan esto automáticamente
// sin necesidad de configuración manual
```

```bash
# MySQL — habilitar slow query log en el servidor
# /etc/mysql/mysql.conf.d/mysqld.cnf
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 1    # queries > 1 segundo
log_queries_not_using_indexes = 1  # también las que no usan índice

# Analizar con pt-query-digest (Percona Toolkit)
pt-query-digest /var/log/mysql/slow.log | head -100
# Muestra las queries más lentas y frecuentes
```
