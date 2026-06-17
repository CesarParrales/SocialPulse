# Guía de despliegue — SocialPulse

Procedimiento para **staging** y **producción** (Ubuntu/Debian + Nginx + PHP 8.4 + PostgreSQL + Redis).

Ver también: [RUNBOOK.md](./RUNBOOK.md), [LAUNCH-CHECKLIST.md](./LAUNCH-CHECKLIST.md), [ONBOARDING.md](./ONBOARDING.md).

---

## 1. Requisitos del servidor

| Componente | Versión mínima |
|------------|----------------|
| PHP | 8.4 + extensions: pgsql, redis, mbstring, xml, curl, zip, bcmath, intl |
| Node.js | 22+ (solo en build; no necesario en runtime) |
| PostgreSQL | 16+ |
| Redis | 7+ |
| Supervisor | 4+ |
| Nginx | 1.24+ |

Usuarios del sistema: `www-data` para PHP-FPM, Horizon y cron del scheduler.

---

## 2. Estructura en el servidor

```
/var/www/socialpulse/          # código de la app
├── public/                    # document root nginx
├── storage/                   # writable www-data
└── bootstrap/cache/           # writable www-data
```

---

## 3. Primer despliegue

```bash
cd /var/www
git clone <repo-url> socialpulse
cd socialpulse

# Entorno (solo en el servidor remoto — ver docs/staging-oauth-qa.md)
cp .env.staging.example .env   # staging VPS | cp .env.example .env en prod
php artisan key:generate

# Dependencias
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Base de datos
php artisan migrate --force

# Permisos
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache

# Cache de config (prod)
php artisan view:clear   # obligatorio tras deploy: evita Blade cacheado con "@routes" sin compilar
php artisan config:cache
php artisan route:cache
php artisan view:cache   # requiere Modules/*/resources/views existentes (Content incluido)
```

---

## 4. Horizon (Supervisor)

Plantilla: [`deploy/supervisor/horizon.conf`](../deploy/supervisor/horizon.conf)

```bash
sudo cp deploy/supervisor/horizon.conf /etc/supervisor/conf.d/
# Editar rutas si difieren de /var/www/socialpulse

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start socialpulse-horizon
sudo supervisorctl status socialpulse-horizon
```

Tras cada deploy:

```bash
php artisan horizon:terminate
# Supervisor reinicia Horizon automáticamente
```

Verificar colas en `/horizon` (solo **super_admin** en staging/prod).

---

## 5. Laravel Scheduler (cron)

Plantilla: [`deploy/cron/scheduler.cron`](../deploy/cron/scheduler.cron)

```bash
sudo crontab -u www-data -e
```

Añadir:

```
* * * * * cd /var/www/socialpulse && php artisan schedule:run >> /dev/null 2>&1
```

### Jobs programados (MVP)

| Job | Frecuencia | Hora UTC |
|-----|------------|----------|
| Ingesta orgánica FB/IG | Diario | 02:00 |
| Ingesta pagada Meta/Google | Diario | 02:30 |
| Stories watcher | Cada 6 h | — |
| Pagado intraday | Cada 4 h | — |
| Token refresh Meta | Diario | 05:00 |
| Avisos token por expirar | Diario | 06:00 |
| Benchmarks industria | Semanal | (Analytics module) |
| Horizon snapshot | Cada 5 min | — |

Listar en el servidor:

```bash
php artisan schedule:list
```

---

## 6. Nginx

Plantilla: [`deploy/nginx/socialpulse.conf.example`](../deploy/nginx/socialpulse.conf.example)

```bash
sudo cp deploy/nginx/socialpulse.conf.example /etc/nginx/sites-available/socialpulse
sudo ln -s /etc/nginx/sites-available/socialpulse /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

Certificado TLS (Let's Encrypt):

```bash
sudo certbot --nginx -d app.socialpulse.app
```

---

## 7. Variables de entorno críticas

| Variable | Staging | Producción |
|----------|---------|------------|
| `APP_DEBUG` | `false` | `false` |
| `APP_URL` | URL staging | URL prod |
| `QUEUE_CONNECTION` | `redis` | `redis` |
| `SENTRY_LARAVEL_DSN` | DSN staging | DSN prod |
| `META_*` / `GOOGLE_*` | Apps de dev/staging | Apps aprobadas |
| `LEGAL_CONTACT_EMAIL` | email real | email legal |

Plantilla staging: [`.env.staging.example`](../.env.staging.example)

---

## 8. Deploy incremental (actualizaciones)

```bash
cd /var/www/socialpulse
git pull origin main

composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan horizon:terminate

php artisan socialpulse:integrations:check --require=meta,google
php artisan socialpulse:smoke --auth
php artisan socialpulse:smoke --auth --oauth
php artisan sentry:test    # si Sentry configurado
curl -sf https://app.socialpulse.app/health
```

---

## 9. Uptime monitor (externo)

Configurar en UptimeRobot, Better Stack, Pingdom, etc.:

| Check | URL | Intervalo |
|-------|-----|-----------|
| Liveness | `https://app.socialpulse.app/up` | 1 min |
| Readiness | `https://app.socialpulse.app/health` | 5 min |

Alertar si HTTP ≠ 200 o `/health` devuelve `"status":"degraded"`.

---

## 10. Meta App Review — URLs públicas

Registrar en la app de Meta:

| Recurso | URL |
|---------|-----|
| Política de privacidad | `{APP_URL}/legal/privacy` |
| Términos | `{APP_URL}/legal/terms` |
| OAuth redirect | `{APP_URL}/connections/meta/callback` |

---

## 11. Checklist post-instalación

- [ ] `php artisan socialpulse:integrations:check --require=meta,google
php artisan socialpulse:smoke --auth
php artisan socialpulse:smoke --auth --oauth`
- [ ] `php artisan horizon:status` → running
- [ ] `php artisan schedule:list` → jobs visibles
- [ ] DemoSeeder solo en staging (no en prod)
- [ ] `/horizon` accesible solo para super_admin
- [ ] Sentry recibe evento de prueba
- [ ] Uptime monitor activo

---

*Última revisión: junio 2026*
