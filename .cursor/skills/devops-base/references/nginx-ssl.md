# Nginx y SSL

## Configuración Nginx para Laravel/PHP-FPM

```nginx
# /etc/nginx/conf.d/api.conf
server {
    listen 80;
    server_name api.myapp.com;

    # Redirigir todo a HTTPS
    location / {
        return 301 https://$server_name$request_uri;
    }

    # Certbot challenge — necesario para renovación SSL
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }
}

server {
    listen 443 ssl http2;
    server_name api.myapp.com;

    # SSL — gestionado por Certbot
    ssl_certificate     /etc/letsencrypt/live/api.myapp.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.myapp.com/privkey.pem;

    # Configuración SSL segura
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers off;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_session_timeout 1d;
    ssl_session_cache shared:SSL:50m;
    ssl_stapling on;
    ssl_stapling_verify on;

    # Headers de seguridad
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options DENY always;
    add_header X-Content-Type-Options nosniff always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    root /var/www/public;
    index index.php;

    # Tamaño máximo de upload
    client_max_body_size 50M;

    # Timeouts
    fastcgi_read_timeout 300;
    proxy_read_timeout 300;

    # Logs
    access_log /var/log/nginx/api.access.log;
    error_log  /var/log/nginx/api.error.log warn;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass   api:9000;    # nombre del servicio Docker
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include        fastcgi_params;

        # Timeouts para requests largos (reportes, exports)
        fastcgi_read_timeout 300;
    }

    # Bloquear acceso a archivos sensibles
    location ~ /\.(ht|env|git) {
        deny all;
    }

    location ~ \.(log|lock|md)$ {
        deny all;
    }
}
```

---

## Configuración Nginx para Node/NestJS (Reverse Proxy)

```nginx
# /etc/nginx/conf.d/node-api.conf
upstream nestjs_app {
    server api:3000;
    server api:3001;   # segundo container si hay varios
    keepalive 64;
}

server {
    listen 80;
    server_name api.myapp.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.myapp.com;

    ssl_certificate     /etc/letsencrypt/live/api.myapp.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.myapp.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    add_header Strict-Transport-Security "max-age=31536000" always;
    add_header X-Content-Type-Options nosniff always;

    client_max_body_size 20M;

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;
    limit_req zone=api burst=20 nodelay;
    limit_req_status 429;

    location / {
        proxy_pass         http://nestjs_app;
        proxy_http_version 1.1;
        proxy_set_header   Upgrade $http_upgrade;
        proxy_set_header   Connection 'upgrade';
        proxy_set_header   Host $host;
        proxy_set_header   X-Real-IP $remote_addr;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;

        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout    60s;
        proxy_read_timeout    60s;
    }

    # WebSockets — ruta específica para Socket.io
    location /socket.io/ {
        proxy_pass         http://nestjs_app;
        proxy_http_version 1.1;
        proxy_set_header   Upgrade $http_upgrade;
        proxy_set_header   Connection "upgrade";
        proxy_set_header   Host $host;
        proxy_read_timeout 3600s;    # mantener conexión larga
    }
}
```

---

## Certbot SSL Automático

```bash
# Instalar Certbot en servidor Ubuntu
sudo apt update
sudo apt install certbot python3-certbot-nginx

# Obtener certificado (Nginx ya corriendo)
sudo certbot --nginx -d api.myapp.com -d www.myapp.com \
  --non-interactive --agree-tos -m admin@myapp.com

# Renovación automática (cron ya configurado por certbot)
# Verificar que existe:
sudo systemctl status certbot.timer

# Test de renovación
sudo certbot renew --dry-run
```

```bash
# Con Docker — Certbot como container
# Ver docker-compose.prod.yml en docker.md
# Script de renovación automática:

#!/bin/bash
# /opt/myapp/renew-ssl.sh
docker compose -f docker-compose.yml -f docker-compose.prod.yml run --rm certbot renew
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec nginx nginx -s reload

# Agregar a crontab: 0 12 * * * /opt/myapp/renew-ssl.sh >> /var/log/certbot-renew.log 2>&1
```

---

## Caddy — Alternativa a Nginx con SSL Automático

```caddyfile
# Caddyfile — SSL automático sin configuración extra
api.myapp.com {
    # SSL automático con Let's Encrypt
    # Sin configuración extra necesaria

    # Reverse proxy a la app
    reverse_proxy api:3000 {
        header_up X-Real-IP {remote_host}
        header_up X-Forwarded-Proto {scheme}
    }

    # Headers de seguridad
    header {
        Strict-Transport-Security "max-age=31536000"
        X-Content-Type-Options nosniff
        X-Frame-Options DENY
    }

    # Rate limiting
    rate_limit {
        zone dynamic {
            key {remote_host}
            events 60
            window 1m
        }
    }

    # Logs estructurados
    log {
        output file /var/log/caddy/api.log {
            roll_size 10mb
            roll_keep 5
        }
        format json
    }
}
```

```yaml
# docker-compose con Caddy
services:
  caddy:
    image: caddy:2-alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./Caddyfile:/etc/caddy/Caddyfile:ro
      - caddy_data:/data        # certificados persisten
      - caddy_config:/config
    depends_on: [api]

volumes:
  caddy_data:
  caddy_config:
```

**Cuándo Caddy vs Nginx:**
- Caddy: proyectos nuevos, SSL sin configuración, config simple
- Nginx: máximo control, sistemas existentes, configuraciones muy específicas

---

## Optimización Nginx para Performance

```nginx
# nginx.conf — configuración global optimizada
worker_processes auto;              # un worker por CPU core
worker_rlimit_nofile 65535;

events {
    worker_connections 4096;
    use epoll;
    multi_accept on;
}

http {
    # Compresión
    gzip on;
    gzip_comp_level 5;
    gzip_types text/plain text/css application/json application/javascript
               text/xml application/xml application/xml+rss text/javascript;
    gzip_vary on;
    gzip_min_length 1024;

    # Cache de archivos estáticos
    open_file_cache max=200000 inactive=20s;
    open_file_cache_valid 30s;
    open_file_cache_min_uses 2;
    open_file_cache_errors on;

    # Buffers
    client_body_buffer_size 128k;
    client_max_body_size 50M;
    client_header_buffer_size 1k;
    large_client_header_buffers 4 16k;

    # Timeouts
    keepalive_timeout 65;
    keepalive_requests 1000;
    send_timeout 30;

    # Security
    server_tokens off;   # no revelar versión de Nginx

    include /etc/nginx/conf.d/*.conf;
}
```
