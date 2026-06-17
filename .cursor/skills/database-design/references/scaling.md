# Estrategias de Escala de Base de Datos

## Cuándo Escalar — Señales Reales

```
No escalar por anticipación. Escalar cuando los datos lo indican:

🟡 Investigar:
   - Queries > 100ms en promedio
   - CPU de BD > 70% sostenido
   - Conexiones activas > 80% del pool
   - Crecimiento de tabla > 10M filas con queries lentas

🔴 Actuar:
   - Queries > 500ms afectando UX
   - CPU de BD > 90%
   - Pool de conexiones saturado (errores "too many connections")
   - Replicación con lag > 10 segundos
```

---

## Nivel 1 — Optimización (antes de escalar hardware)

Orden de aplicación (de menor a mayor costo):

```
1. Índices correctos        → gratis, impacto inmediato
2. Query rewriting          → gratis, requiere análisis
3. Connection pooling       → PgBouncer, bajo costo
4. Caché de queries         → Redis, costo bajo
5. Read replicas            → costo medio, transparente para la app
6. Particionamiento         → costo medio, requiere migración
7. Sharding                 → costo alto, cambios en aplicación
```

---

## Connection Pooling con PgBouncer

PostgreSQL tiene un límite de conexiones concurrentes (~100-500 según RAM).
Cada conexión consume ~5-10MB. Sin pooling, bajo carga hay errores de conexión.

```ini
# pgbouncer.ini
[databases]
myapp = host=127.0.0.1 port=5432 dbname=myapp

[pgbouncer]
pool_mode = transaction    # una conexión por transacción (más eficiente)
max_client_conn = 1000     # clientes que se conectan a PgBouncer
default_pool_size = 25     # conexiones reales a PostgreSQL
min_pool_size = 5
reserve_pool_size = 5
reserve_pool_timeout = 3

# En .env Laravel:
# DB_HOST=pgbouncer-host (no postgres directo)
# DB_PORT=6432
```

```
Sin PgBouncer:  1,000 requests → 1,000 conexiones PostgreSQL → crash
Con PgBouncer:  1,000 requests → 25 conexiones PostgreSQL → estable
```

---

## Particionamiento de Tablas (PostgreSQL)

Para tablas que crecen indefinidamente: logs, eventos, transacciones históricas.

### Particionamiento por Rango de Fecha

```sql
-- Tabla padre (sin datos directamente)
CREATE TABLE events (
    id          BIGSERIAL,
    user_id     BIGINT NOT NULL,
    event_type  VARCHAR(50) NOT NULL,
    payload     JSONB,
    created_at  TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
) PARTITION BY RANGE (created_at);

-- Particiones por mes (crear automáticamente con pg_partman)
CREATE TABLE events_2024_01 PARTITION OF events
    FOR VALUES FROM ('2024-01-01') TO ('2024-02-01');

CREATE TABLE events_2024_02 PARTITION OF events
    FOR VALUES FROM ('2024-02-01') TO ('2024-03-01');

-- Cada partición puede tener sus propios índices
CREATE INDEX ON events_2024_01 (user_id, created_at);
CREATE INDEX ON events_2024_02 (user_id, created_at);

-- Las queries con filtro de fecha van SOLO a la partición relevante
-- Partition pruning automático:
SELECT * FROM events
WHERE created_at >= '2024-01-15' AND created_at < '2024-02-01';
-- Solo lee events_2024_01, ignora el resto → enorme mejora de performance

-- Eliminar datos antiguos: DROP PARTITION (instantáneo)
-- vs DELETE (lento, genera bloat)
DROP TABLE events_2023_01;  -- elimina millones de filas en milisegundos
```

### Automatización con pg_partman

```sql
-- pg_partman crea y elimina particiones automáticamente
SELECT partman.create_parent(
    p_parent_table => 'public.events',
    p_control => 'created_at',
    p_type => 'range',
    p_interval => 'monthly',
    p_premake => 3  -- crear 3 particiones futuras por adelantado
);

-- Configurar retención (eliminar particiones > 12 meses)
UPDATE partman.part_config
SET retention = '12 months', retention_keep_table = false
WHERE parent_table = 'public.events';

-- Job periódico (cron o pg_cron)
SELECT partman.run_maintenance();
```

---

## Replicación y Read Replicas

```
Arquitectura típica:

Client App
    │
    ├── Writes → Primary (PostgreSQL)
    │                │
    │           Streaming Replication
    │                │
    └── Reads  → Replica 1
                 Replica 2  (para reportes pesados)
```

### Configuración en Laravel (ver indexes-and-queries.md)

### Casos de Uso de Replicas

```php
// Reportes costosos → siempre en replica
class ReportService
{
    public function generateMonthlyReport(int $year, int $month): array
    {
        // Forzar replica para no afectar escrituras del primary
        return DB::connection('pgsql_read')
            ->table('orders')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_cents) as revenue')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }
}

// Búsquedas → replica
// Escrituras transaccionales → primary (automático con sticky=true)
```

---

## Redis — Patrones de Uso

### Caché con Invalidación Inteligente

```php
class ProductCacheService
{
    private const TTL_PRODUCT    = 3600;    // 1 hora
    private const TTL_LISTING    = 300;     // 5 minutos (cambia más)
    private const TTL_POPULAR    = 1800;    // 30 minutos

    // Write-through: actualizar caché al mismo tiempo que BD
    public function updateProduct(int $id, array $data): Product
    {
        $product = Product::findOrFail($id)->fill($data);
        $product->save();

        // Actualizar caché inmediatamente
        Cache::put("product:{$id}", $product, self::TTL_PRODUCT);

        // Invalidar listings que pueden incluir este producto
        Cache::tags(["category:{$product->category_id}"])->flush();

        return $product;
    }

    // Stale-While-Revalidate: devolver dato viejo mientras se actualiza
    public function getPopularProducts(): Collection
    {
        $cacheKey = 'products:popular';

        if ($cached = Cache::get($cacheKey)) {
            // Si está por expirar, actualizar en background
            if (Cache::get("{$cacheKey}:ttl") < 60) {
                dispatch(new RefreshPopularProductsCache());
            }
            return $cached;
        }

        $products = Product::withCount('sales')->orderByDesc('sales_count')->limit(10)->get();
        Cache::put($cacheKey, $products, self::TTL_POPULAR);
        Cache::put("{$cacheKey}:ttl", self::TTL_POPULAR, self::TTL_POPULAR);
        return $products;
    }
}
```

### Rate Limiting con Redis

```php
// Middleware de rate limiting con ventana deslizante
class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next, int $limit = 60): Response
    {
        $key = "rate_limit:{$request->ip()}:" . floor(time() / 60);

        $current = Redis::incr($key);
        if ($current === 1) {
            Redis::expire($key, 60);  // TTL de 60 segundos
        }

        if ($current > $limit) {
            return response()->json([
                'error' => 'Too many requests',
                'retry_after' => 60 - (time() % 60),
            ], 429);
        }

        return $next($request);
    }
}
```

### Leaderboards y Contadores en Tiempo Real

```php
// Sorted Sets de Redis para rankings
class LeaderboardService
{
    public function recordSale(int $productId, int $quantity): void
    {
        Redis::zincrby('product:sales:this_month', $quantity, $productId);
    }

    public function getTopProducts(int $limit = 10): array
    {
        // ZREVRANGE devuelve de mayor a menor score
        return Redis::zrevrangebyscore(
            'product:sales:this_month',
            '+inf', '-inf',
            ['WITHSCORES' => true, 'LIMIT' => [0, $limit]]
        );
    }

    // Contadores atómicos (sin race conditions)
    public function incrementView(int $productId): void
    {
        Redis::incr("product:{$productId}:views");
        // Flush a BD periódicamente (cron cada 5 min)
    }
}
```

---

## Backup y Recuperación

### Estrategia de Backup

```bash
# Backup diario con pg_dump (logical backup)
pg_dump -Fc \
  --no-acl \
  --no-owner \
  -h $DB_HOST \
  -U $DB_USER \
  $DB_NAME > backup_$(date +%Y%m%d_%H%M%S).dump

# Comprimir y subir a S3
gzip backup_*.dump
aws s3 cp backup_*.dump.gz s3://my-backups/postgres/

# Retención: 7 días diarios, 4 semanas semanales, 12 meses mensuales

# Restaurar
pg_restore -Fc \
  -h $DB_HOST \
  -U $DB_USER \
  -d $DB_NAME_RESTORE \
  backup_20240115.dump
```

### RTO y RPO — Definir Antes de Necesitar

```
RPO (Recovery Point Objective): ¿cuántos datos podemos perder?
  - RPO = 0: replicación síncrona (costoso, lento)
  - RPO = 5min: WAL archiving a S3 (point-in-time recovery)
  - RPO = 24h: backup diario (aceptable para muchos proyectos)

RTO (Recovery Time Objective): ¿cuánto tiempo podemos estar caídos?
  - RTO = 1min: failover automático a replica
  - RTO = 30min: failover manual a replica
  - RTO = 4h: restaurar desde backup

Managed services (RDS, Supabase, Neon) resuelven esto out-of-the-box.
Para self-hosted: Patroni para HA automático.
```

### Testear el Backup (el paso que nadie hace)

```bash
# Cada mes: probar restauración en ambiente de prueba
# Un backup no testeado es solo una ilusión de seguridad

#!/bin/bash
# test-restore.sh
BACKUP_FILE=$(ls -t s3://my-backups/postgres/*.dump.gz | head -1)
aws s3 cp $BACKUP_FILE /tmp/latest-backup.dump.gz
gunzip /tmp/latest-backup.dump.gz

# Restaurar en BD de prueba
dropdb --if-exists restore_test
createdb restore_test
pg_restore -Fc -d restore_test /tmp/latest-backup.dump

# Verificar integridad básica
psql restore_test -c "SELECT COUNT(*) FROM users;"
psql restore_test -c "SELECT COUNT(*) FROM orders;"
psql restore_test -c "SELECT MAX(created_at) FROM orders;"

echo "✅ Backup restaurado y verificado: $(date)"
```
