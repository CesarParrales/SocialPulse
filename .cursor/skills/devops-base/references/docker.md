# Docker — Contenedores para Web

> **Versiones:** leer `composer.json` / `package.json` del proyecto. Los ejemplos usan
> tags ilustrativos — sustituir por la **última estable** compatible (ver
> `laravel-backend/references/stack-versions.md`).

## Dockerfile Laravel (PHP-FPM + Nginx)

```dockerfile
# Dockerfile — ajustar tag PHP a composer.json require.php (ej. 8.4)
FROM php:8.4-fpm-alpine AS base

# Extensiones PHP necesarias
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    oniguruma-dev \
    && docker-php-ext-install \
    pdo pdo_pgsql \
    zip \
    gd \
    opcache \
    pcntl \           # para Horizon/Octane
    bcmath

# Redis extension via PECL
RUN pecl install redis && docker-php-ext-enable redis

# OPcache optimizado para producción
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# --- Build stage ---
FROM base AS builder
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction
COPY . .
RUN composer run-script post-autoload-dump

# --- Production ---
FROM base AS production
RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www
WORKDIR /var/www

COPY --from=builder --chown=www:www /var/www .

# Permisos de Laravel
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www:www storage bootstrap/cache

USER www
EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=10s \
  CMD php-fpm-healthcheck || exit 1

CMD ["php-fpm"]
```

```ini
# docker/php/opcache.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=64
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0   ; NO en producción — requiere restart para ver cambios
opcache.save_comments=1
opcache.fast_shutdown=1
```

---

## Dockerfile Node/NestJS

```dockerfile
FROM node:20-alpine AS base
RUN apk add --no-cache dumb-init  # manejo correcto de signals

# --- Dependencies ---
FROM base AS deps
WORKDIR /app
COPY package*.json ./
RUN npm ci

# --- Builder ---
FROM base AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .
RUN npm run build
# Solo deps de producción
RUN npm ci --only=production && npm cache clean --force

# --- Production ---
FROM base AS production
ENV NODE_ENV=production
WORKDIR /app

RUN addgroup -S app && adduser -S app -G app

COPY --from=builder --chown=app:app /app/dist ./dist
COPY --from=builder --chown=app:app /app/node_modules ./node_modules
COPY --chown=app:app package.json .

USER app
EXPOSE 3000

HEALTHCHECK --interval=30s --timeout=10s --start-period=5s \
  CMD wget -qO- http://localhost:3000/health || exit 1

# dumb-init como PID 1 para manejo correcto de signals
ENTRYPOINT ["dumb-init", "--"]
CMD ["node", "dist/main.js"]
```

---

## Docker Compose — Desarrollo Completo

```yaml
# docker-compose.yml
services:
  # Laravel API
  api:
    build:
      context: .
      target: production
    restart: unless-stopped
    environment:
      APP_ENV: local
      DB_HOST: postgres
      REDIS_HOST: redis
      QUEUE_CONNECTION: redis
    volumes:
      - ./storage:/var/www/storage   # persistir uploads en dev
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_started
    networks: [app]

  # Nginx como reverse proxy
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
      - ./public:/var/www/public:ro
      - certs:/etc/ssl/certs
    depends_on: [api]
    networks: [app]

  # Queue worker (Laravel Horizon)
  horizon:
    build:
      context: .
      target: production
    command: php artisan horizon
    restart: unless-stopped
    environment:
      APP_ENV: local
      DB_HOST: postgres
      REDIS_HOST: redis
    depends_on: [api]
    networks: [app]

  # Scheduler (cron jobs)
  scheduler:
    build:
      context: .
      target: production
    command: sh -c "while true; do php artisan schedule:run --verbose --no-interaction & sleep 60; done"
    restart: unless-stopped
    depends_on: [api]
    networks: [app]

  # PostgreSQL
  postgres:
    image: postgres:16-alpine
    restart: unless-stopped
    environment:
      POSTGRES_DB: ${DB_DATABASE:-myapp}
      POSTGRES_USER: ${DB_USERNAME:-myapp}
      POSTGRES_PASSWORD: ${DB_PASSWORD:-secret}
    volumes:
      - postgres_data:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U ${DB_USERNAME:-myapp}"]
      interval: 5s
      timeout: 5s
      retries: 10
    networks: [app]

  # Redis
  redis:
    image: redis:7-alpine
    restart: unless-stopped
    command: redis-server --appendonly yes --requirepass ${REDIS_PASSWORD:-}
    volumes:
      - redis_data:/data
    networks: [app]

  # Mailpit — capturar emails en desarrollo
  mailpit:
    image: axllent/mailpit
    ports:
      - "8025:8025"   # UI web
      - "1025:1025"   # SMTP
    networks: [app]

  # Adminer — UI para BD
  adminer:
    image: adminer:4-standalone
    ports: ["8080:8080"]
    depends_on: [postgres]
    networks: [app]

networks:
  app:
    driver: bridge

volumes:
  postgres_data:
  redis_data:
  certs:
```

---

## Docker Compose Override para Producción

```yaml
# docker-compose.prod.yml — sobreescribe valores para producción
services:
  api:
    build:
      target: production
    restart: always
    environment:
      APP_ENV: production
      APP_DEBUG: "false"
    volumes: []  # sin bind mounts en prod

  nginx:
    volumes:
      - ./docker/nginx/prod.conf:/etc/nginx/conf.d/default.conf:ro
      - certbot_www:/var/www/certbot
      - certbot_conf:/etc/letsencrypt

  certbot:
    image: certbot/certbot
    volumes:
      - certbot_www:/var/www/certbot
      - certbot_conf:/etc/letsencrypt
    command: certonly --webroot --webroot-path=/var/www/certbot
             -d api.myapp.com --email admin@myapp.com --agree-tos

  # Sin mailpit, sin adminer en producción
  mailpit: !reset
  adminer: !reset

volumes:
  certbot_www:
  certbot_conf:

# Usar con: docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

---

## Comandos Docker Útiles

```bash
# Build y levantar
docker compose up -d --build

# Ver logs en tiempo real
docker compose logs -f api
docker compose logs -f --tail=100 horizon

# Ejecutar comando en container corriendo
docker compose exec api php artisan migrate
docker compose exec api php artisan tinker
docker compose exec api bash

# Ver estado de containers
docker compose ps
docker stats  # CPU y memoria en tiempo real

# Limpiar recursos no usados
docker system prune -a --volumes  # ¡cuidado en producción!
docker image prune -a
docker volume prune

# Rebuild de un servicio específico
docker compose up -d --build api --no-deps

# Escalar workers horizontalmente
docker compose up -d --scale horizon=3

# Inspeccionar container
docker inspect myapp-api-1
docker exec -it myapp-api-1 sh
```

---

## .dockerignore — Siempre Presente

```
.git
.github
.gitignore
.env
.env.*
!.env.example
node_modules
vendor
/storage/*.key
storage/app/public
storage/framework/cache
storage/framework/sessions
storage/framework/views
storage/logs
*.log
tests
docker
docker-compose*.yml
Dockerfile
README.md
.phpunit.cache
coverage
```
