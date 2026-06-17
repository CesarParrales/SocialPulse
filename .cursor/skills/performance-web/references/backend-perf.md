# Backend Performance

## TTFB — Time to First Byte

```
TTFB alto (> 800ms) es un multiplicador de lentitud:
Todo lo que pase después (descargar HTML, CSS, JS, imágenes)
empieza después del TTFB.

Diagnóstico:
curl -w "\nTTFB: %{time_starttransfer}s\n" -o /dev/null -s https://myapp.com/

Causas comunes:
1. Query de BD lenta en el request inicial → Redis cache
2. Sin server-side cache del HTML → página generada en cada request
3. Servidor en región lejana al usuario → CDN/edge
4. PHP sin OPcache → recompila cada request
5. Sin HTTP keep-alive → nueva conexión TCP por request
```

---

## Query Performance — El Mayor Impacto

```php
// Laravel Debugbar / Telescope — identificar N+1 en desarrollo
// Ver references/database-design.md para patrones completos

// Caché de queries con Redis — el cambio con mayor impacto
class ProductService
{
    // Sin caché: 80ms query por cada request
    // Con caché: 0ms para requests dentro del TTL
    public function getFeaturedProducts(): Collection
    {
        return Cache::remember('products:featured', 300, fn() =>
            Product::featured()
                ->with('category', 'primaryImage')
                ->select(['id', 'name', 'slug', 'price_cents', 'category_id'])
                ->limit(12)
                ->get()
        );
    }

    // Caché de aggregates — muy costosas sin caché
    public function getDashboardStats(int $userId): array
    {
        return Cache::remember("user:{$userId}:stats", 600, function () use ($userId) {
            return [
                'total_orders'   => Order::forUser($userId)->count(),
                'pending_orders' => Order::forUser($userId)->pending()->count(),
                'total_spent'    => Order::forUser($userId)->delivered()->sum('total_cents'),
                'last_order_at'  => Order::forUser($userId)->latest()->value('created_at'),
            ];
        });
    }
}

// Respuestas HTTP cacheadas (nginx o Varnish para páginas completas)
class PublicController extends Controller
{
    public function index(): Response
    {
        $products = Cache::remember('public:products', 300, fn() =>
            Product::active()->with('category')->limit(50)->get()
        );

        return response()
            ->view('products.index', compact('products'))
            ->header('Cache-Control', 'public, max-age=300, s-maxage=300')
            ->header('Vary', 'Accept-Encoding');
    }
}
```

---

## PHP/Laravel Performance

```php
// OPcache — compilar PHP una sola vez
// php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=64
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0  // NO en producción
opcache.fast_shutdown=1

// Laravel — cachear configuración, rutas, vistas
php artisan config:cache   // config/*.php → bootstrap/cache/config.php
php artisan route:cache    // todas las rutas → un archivo PHP
php artisan view:cache     // precompilar vistas Blade
php artisan event:cache    // listeners → un archivo

// Laravel Octane — servidor persistente (sin bootstrap por request)
// Ver laravel-backend.md references/performance.md

// Eager loading — nunca N+1 en producción
// Detectar en desarrollo:
Model::preventLazyLoading(!app()->isProduction());

// Seleccionar solo columnas necesarias
User::select(['id', 'name', 'email'])->get();  // no SELECT *
Order::with(['items:id,order_id,quantity,price_cents'])->get(); // columnas en relaciones

// Chunk para procesar grandes volúmenes sin OOM
Order::where('status', 'pending')
    ->chunkById(500, function (Collection $orders) {
        $orders->each(fn($order) => $order->process());
    });
```

---

## Node.js / NestJS Performance

```typescript
// Compresión
import compression from 'compression';
app.use(compression({ level: 6 })); // level 6 = balance speed/size

// Caché con Redis
@Injectable()
export class CacheService {
  constructor(@Inject(CACHE_MANAGER) private cache: Cache) {}

  async getOrSet<T>(
    key: string,
    factory: () => Promise<T>,
    ttl = 300
  ): Promise<T> {
    const cached = await this.cache.get<T>(key);
    if (cached !== null && cached !== undefined) return cached;

    const value = await factory();
    await this.cache.set(key, value, ttl * 1000);
    return value;
  }
}

// Connection pooling — Prisma ya lo hace por defecto
// Configurar pool size según carga esperada
// prisma/schema.prisma
// datasource db {
//   url = env("DATABASE_URL?connection_limit=10&pool_timeout=20")
// }

// Cluster mode — usar todos los cores
// ecosystem.config.js
{
  instances: 'max',    // un proceso por CPU
  exec_mode: 'cluster',
}

// Evitar blocking the event loop
// ❌ MAL: sync file read en request handler
const data = fs.readFileSync('large-file.json');

// ✅ BIEN: async file read
const data = await fs.promises.readFile('large-file.json', 'utf-8');

// ✅ MEJOR: para archivos grandes, usar streams
const stream = fs.createReadStream('large-file.csv');
// procesar en chunks sin cargar todo en memoria
```

---

## Profiling en Producción

```bash
# PHP — identificar funciones lentas
# Instalar Xdebug o Blackfire

# Blackfire CLI
blackfire curl https://myapp.com/api/products
# Muestra flamegraph de cada función y su tiempo

# Node.js — CPU profile
node --prof app.js  # genera isolate-*.log
node --prof-process isolate-*.log > processed.txt

# O con clinic.js
npx clinic doctor -- node app.js
# Identifica: event loop blocking, memory leaks, I/O bottlenecks

# PostgreSQL — queries lentas
SELECT query, calls, total_exec_time, mean_exec_time, rows
FROM pg_stat_statements
ORDER BY mean_exec_time DESC
LIMIT 20;

# Identificar queries sin índice
EXPLAIN (ANALYZE, BUFFERS)
SELECT * FROM orders WHERE user_id = 123 ORDER BY created_at DESC LIMIT 20;
# Buscar: "Seq Scan" en tablas grandes → necesita índice
```

---

## Lazy Loading de Módulos Pesados

```typescript
// Node.js — importar módulos pesados solo cuando se necesitan
// No importar al inicio del proceso — ralentiza el cold start

// ❌ MAL: importar siempre aunque raramente se use
import PDFKit from 'pdfkit';  // 1MB+ en bundle

// ✅ BIEN: importar dinámicamente cuando se necesita
async function generateInvoice(order: Order): Promise<Buffer> {
  const { default: PDFKit } = await import('pdfkit');  // solo cuando se llama
  const doc = new PDFKit();
  // ...
  return buffer;
}

// PHP — no cargar clases costosas en bootstrap
// ❌ MAL: en AppServiceProvider::boot()
$this->app->singleton(HeavyPDFService::class);  // siempre en memoria

// ✅ BIEN: lazy binding — solo cuando se inyecta
$this->app->bind(HeavyPDFService::class);  // crea instancia al primer uso
```
