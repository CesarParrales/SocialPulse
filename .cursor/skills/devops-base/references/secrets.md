# Gestión de Secrets

Monitoreo, logs y alertas → `monitoring.md`.

## GitHub Actions Secrets — Organización

```
GitHub → Settings → Secrets and Variables → Actions

Niveles:
- Repository secrets: para un repo específico
- Environment secrets: por ambiente (staging/production) con protection rules
- Organization secrets: compartidos entre repos del org

Namespacing recomendado:
PROD_APP_KEY           → secrets de producción
STAGING_APP_KEY        → secrets de staging
SHARED_STRIPE_WEBHOOK  → compartido entre ambientes

⚠️ NUNCA commitear:
- API keys
- Passwords de BD
- JWT secrets
- Certificados SSL
- Private keys SSH
```

---

## Variables de Entorno en Servidor

```bash
# Opción 1: Archivo .env en servidor (simple, funciona)
# En el servidor:
nano /opt/myapp/.env.production
# Permisos restrictivos
chmod 600 /opt/myapp/.env.production
chown deploy:deploy /opt/myapp/.env.production

# Opción 2: Systemd environment file (más seguro)
# /etc/systemd/system/myapp.service
[Service]
EnvironmentFile=/etc/myapp/env
User=deploy

# Opción 3: Docker secrets (para Swarm)
# Solo disponible en Docker Swarm mode

# Opción 4: AWS Secrets Manager (para infra en AWS)
# Recuperar en arranque de la app o via AWS SDK
```

---

## Rotación de Secrets

```bash
# Script de rotación automática de APP_KEY Laravel
#!/bin/bash
# rotate-key.sh

OLD_KEY=$(grep APP_KEY /opt/myapp/.env.production | cut -d= -f2)
NEW_KEY=$(php artisan key:generate --show)

# Actualizar .env
sed -i "s/APP_KEY=.*/APP_KEY=$NEW_KEY/" /opt/myapp/.env.production

# Regenerar caché de configuración
docker compose exec api php artisan config:cache

# Guardar key anterior en historial (para decrypt datos viejos si aplica)
echo "$(date): $OLD_KEY → $NEW_KEY" >> /var/log/key-rotation.log

echo "Key rotated successfully"
```

---

## Checklist de Secrets

- [ ] `.env.example` en git con todos los keys y sin valores
- [ ] `.env.local` / `.env.*.local` en `.gitignore`
- [ ] Secrets de CI en GitHub Environments con protection rules para production
- [ ] Archivos `.env` del servidor con `chmod 600` y owner del usuario de deploy
- [ ] Plan de rotación documentado para keys críticas (APP_KEY, JWT, BD)
- [ ] Verificar que no hay secrets en el historial: `git log -p | grep -iE 'api[_-]?key|secret|password' | head`
