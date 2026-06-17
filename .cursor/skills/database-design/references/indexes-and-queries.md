# Índices y Optimización de Queries

## EXPLAIN ANALYZE — El Arma Principal

Nunca optimizar sin medir. EXPLAIN ANALYZE ejecuta la query real y muestra el plan.

```sql
-- Analizar query problemática
EXPLAIN (ANALYZE, BUFFERS, FORMAT TEXT)
SELECT o.*, u.email, u.name
FROM orders o
JOIN users u ON u.id = o.user_id
WHERE o.status = 'pending'
  AND o.created_at > NOW() - INTERVAL '7 days'
ORDER BY o.created_at DESC
LIMIT 50;

-- Qué buscar en el output:
-- Seq Scan → full table scan, probablemente falta un índice
-- Index Scan → usa índice, bueno
-- Bitmap Heap Scan → combina múltiples índices, aceptable
-- Hash Join / Nested Loop → tipos de JOIN, el costo depende del volumen
-- rows= → filas estimadas vs. reales (diferencia grande = estadísticas desactualizadas)
-- cost= → costo relativo (no son milisegundos)
-- actual time= → tiempo real en ms
-- Buffers: hit= → páginas desde caché | read= → páginas desde disco (lento)
```

### Señales de Alerta en EXPLAIN

```
Seq Scan en tabla grande      → Falta índice o el planner eligió mal
rows estimadas muy distintas  → ANALYZE la tabla: UPDATE STATISTICS
Sort en disco (Sort Method: external merge) → aumentar work_mem para esta query
Nested Loop con muchas filas  → considerar Hash Join, revisar índices en JOIN
```

---

## Patrones de Queries Lentas y Soluciones

### Problema 1: Función en columna indexada destruye el índice

```sql
-- MAL: la función impide usar el índice en email
SELECT * FROM users WHERE LOWER(email) = 'user@example.com';

-- SOLUCIÓN A: índice funcional
CREATE INDEX idx_users_email_lower ON users(LOWER(email));

-- SOLUCIÓN B: normalizar en inserción (guardar siempre en minúsculas)
-- En Laravel: $table->string('email')->storedAs('LOWER(email)')
-- O en el modelo: $casts = ['email' => 'lowercase']

-- MAL: función en DATE impide usar índice en created_at
SELECT * FROM orders WHERE DATE(created_at) = '2024-01-15';

-- BIEN: rango que permite usar el índice B-Tree
SELECT * FROM orders
WHERE created_at >= '2024-01-15 00:00:00'
  AND created_at <  '2024-01-16 00:00:00';
```

### Problema 2: LIKE con wildcard izquierdo

```sql
-- MAL: wildcard al inicio, no puede usar B-Tree index
SELECT * FROM products WHERE name LIKE '%zapato%';

-- SOLUCIÓN: Full-text search con índice GIN
-- Crear índice
CREATE INDEX idx_products_fts ON products
USING GIN(to_tsvector('spanish', name || ' ' || COALESCE(description, '')));

-- Query
SELECT *, ts_rank(to_tsvector('spanish', name), query) as rank
FROM products, plainto_tsquery('spanish', 'zapato') query
WHERE to_tsvector('spanish', name) @@ query
ORDER BY rank DESC;

-- O con Meilisearch/Algolia para búsqueda más sofisticada
```

### Problema 3: IN con subquery no correlacionada

```sql
-- LENTO: evalúa subquery por cada fila externa
SELECT * FROM orders
WHERE user_id IN (
    SELECT id FROM users WHERE country = 'EC'
);

-- RÁPIDO: JOIN explícito
SELECT o.*
FROM orders o
JOIN users u ON u.id = o.user_id
WHERE u.country = 'EC';

-- O con EXISTS (mejor cuando la subquery puede devolver muchas filas)
SELECT * FROM orders o
WHERE EXISTS (
    SELECT 1 FROM users u
    WHERE u.id = o.user_id AND u.country = 'EC'
);
```

### Problema 4: COUNT(*) en tablas grandes

```sql
-- LENTO: COUNT(*) exact en tablas de millones de filas
SELECT COUNT(*) FROM events;  -- puede tardar segundos

-- RÁPIDO para estimado (suficiente para paginación UI)
SELECT reltuples::BIGINT AS estimated_count
FROM pg_class WHERE relname = 'events';

-- RÁPIDO para conteo real con filtro (usa índice parcial)
SELECT COUNT(*) FROM orders WHERE status = 'pending';
-- Con índice: CREATE INDEX idx_orders_pending ON orders(id) WHERE status = 'pending';
```

### Problema 5: ORDER BY + LIMIT sin índice compuesto

```sql
-- LENTO: ordena millones de filas para tomar 20
SELECT * FROM orders
WHERE user_id = 123
ORDER BY created_at DESC
LIMIT 20;

-- SOLUCIÓN: índice compuesto que incluye la columna de ordenamiento
CREATE INDEX idx_orders_user_date ON orders(user_id, created_at DESC);
-- El planner puede hacer Index Scan y tomar los 20 directamente
```

### Problema 6: Pagination con OFFSET grande

```sql
-- LENTO: OFFSET 50000 descarta 50,000 filas después de leerlas
SELECT * FROM orders ORDER BY id LIMIT 20 OFFSET 50000;

-- RÁPIDO: Keyset pagination (cursor-based)
-- Primera página
SELECT * FROM orders ORDER BY id LIMIT 20;

-- Página siguiente (last_id = último id de la página anterior)
SELECT * FROM orders WHERE id > :last_id ORDER BY id LIMIT 20;

-- En Laravel:
$orders = Order::where('id', '>', $lastId)
    ->orderBy('id')
    ->limit(20)
    ->get();
```

---

## Caché de Queries

### Estrategia en Laravel con Redis

```php
// Cache-aside pattern (el más común)
class ProductRepository
{
    public function findPopular(int $limit = 10): Collection
    {
        return Cache::remember(
            key: "products:popular:{$limit}",
            ttl: now()->addMinutes(30),
            callback: fn() => Product::withCount('orders')
                ->orderByDesc('orders_count')
                ->limit($limit)
                ->get()
        );
    }

    public function findById(int $id): ?Product
    {
        return Cache::remember(
            key: "product:{$id}",
            ttl: now()->addHour(),
            callback: fn() => Product::find($id)
        );
    }

    // Invalidar caché cuando cambia el dato
    public function update(int $id, array $data): Product
    {
        $product = Product::findOrFail($id)->update($data);
        Cache::forget("product:{$id}");
        Cache::forget("products:popular:10");  // invalida listas afectadas
        return $product;
    }
}

// Tags para invalidación granular
Cache::tags(['products'])->remember('products:popular', 1800, fn() => ...);
// Invalidar todo lo de products:
Cache::tags(['products'])->flush();
```

### Qué Cachear vs Qué No

```
SÍ cachear:
✅ Resultados de queries costosas que cambian poco (productos populares, categorías)
✅ Datos de referencia (países, monedas, configuraciones)
✅ Aggregates costosas (totales, estadísticas del dashboard)
✅ Respuestas de APIs externas (con TTL corto)

NO cachear:
❌ Datos que deben ser en tiempo real (stock, saldo, estado de pago)
❌ Datos por usuario sin TTL corto (privacidad, stale data)
❌ Queries que ya son rápidas (< 5ms) — overhead de caché > beneficio
❌ Como solución a un query mal escrito — arreglar el query primero
```

---

## Read Replicas

Para sistemas con más lecturas que escrituras (típico en web):

```php
// config/database.php
'pgsql' => [
    'read' => [
        'host' => [
            env('DB_READ_HOST_1', '127.0.0.1'),
            env('DB_READ_HOST_2', '127.0.0.1'),
        ],
    ],
    'write' => [
        'host' => env('DB_WRITE_HOST', '127.0.0.1'),
    ],
    'sticky' => true,  // después de escribir, leer del primario en el mismo request
    // evita leer datos no replicados aún
    'driver' => 'pgsql',
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
],

// Laravel enruta automáticamente:
// DB::select() → read replica
// DB::insert/update/delete() → write primary

// Forzar lectura desde primary cuando necesitas consistencia
$order = Order::onWriteConnection()->find($id);
```

---

## Monitoreo de Performance en Producción

### Queries lentas automáticas

```sql
-- PostgreSQL: activar slow query log
-- en postgresql.conf:
log_min_duration_statement = 1000  -- logear queries > 1 segundo

-- Ver queries más lentas en los últimos logs
SELECT query, calls, total_exec_time, mean_exec_time, rows
FROM pg_stat_statements
ORDER BY mean_exec_time DESC
LIMIT 20;

-- Índices que no se usan (candidatos a eliminar)
SELECT schemaname, tablename, indexname, idx_scan
FROM pg_stat_user_indexes
WHERE idx_scan = 0
  AND indexname NOT LIKE '%pkey%'
ORDER BY schemaname, tablename;

-- Tablas con más seq scans (candidatas a nuevos índices)
SELECT relname, seq_scan, seq_tup_read, idx_scan
FROM pg_stat_user_tables
WHERE seq_scan > 0
ORDER BY seq_tup_read DESC
LIMIT 20;
```

### En Laravel con Telescope

```php
// Detectar N+1 en desarrollo
// Telescope muestra queries duplicadas automáticamente

// En tests: assert de conteo de queries
use Illuminate\Support\Facades\DB;

public function test_index_uses_eager_loading(): void
{
    DB::enableQueryLog();

    $response = $this->getJson('/api/orders?per_page=20');

    $queries = DB::getQueryLog();
    $this->assertCount(3, $queries, 'Expected 3 queries (orders + users + items)');
}
```
