---
name: devops-base
description: >
  Guía base de DevOps para proyectos web: Docker, GitHub Actions, ambientes, secrets,
  nginx/SSL, deploy automatizado a VPS y monitoreo esencial (uptime, logs, alertas).
  Usar cuando el usuario mencione Docker, containers, CI/CD, GitHub Actions, GitLab CI,
  pipelines, deploy automatizado, ambientes (staging, producción), secrets management,
  nginx, reverse proxy, SSL, certbot, infraestructura, servidores, VPS, DigitalOcean,
  AWS, logs, o cuando diga "cómo hago deploy", "cómo configuro CI/CD", "cómo dockerizo
  mi app", "cómo manejo secrets", "necesito staging", "cómo escalo mi servidor".
  NO cubre observabilidad avanzada (tracing, SLOs, dashboards — usar
  monitoring-observability) ni Kubernetes salvo petición explícita del usuario.
---

# DevOps Base Skill

Guía de infraestructura, CI/CD y operaciones para proyectos web.

**Docker y containers → `references/docker.md`**
**GitHub Actions — CI/CD → `references/github-actions.md`**
**Nginx y SSL → `references/nginx-ssl.md`**
**Secrets y Variables de Entorno → `references/secrets.md`**
**Monitoreo y Logs → `references/monitoring.md`**
**Versiones del stack → `../laravel-backend/references/stack-versions.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — URLs staging/prod, `/health`, proveedor VPS.
2. Workflows CI existentes en `.github/workflows/`.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** comandos deploy/rollback → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución (deploy MVP)

0. **Memoria** — leer project-memory; leer `composer.json` / `package.json` para versiones CI/Docker; no duplicar monitoring avanzado (→ `monitoring-observability`).
1. **Containerizar**: Dockerfile multi-stage + `docker-compose.yml` según `references/docker.md`. Gate: ejecuta `docker compose up -d` en local y verifica que `docker compose ps` muestra todos los servicios `running` (ninguno `exited`).
2. **CI con tests**: pipeline de GitHub Actions según `references/github-actions.md` que corre lint + tests en cada PR. Gate: el workflow pasa en verde antes de habilitar cualquier deploy (`gh run list --limit 1` o pestaña Actions).
3. **Secrets y ambientes**: configura secrets de CI y `.env` del servidor según `references/secrets.md`. Gate: `grep -rn "API_KEY\|SECRET\|PASSWORD" --include="*.yml" .github/` no expone valores en claro y `.env.example` está completo.
4. **Proxy y SSL**: nginx + certbot (o Caddy) según `references/nginx-ssl.md`. Gate: `curl -sI https://dominio | head -1` devuelve 200/301 y el certificado es válido.
5. **Deploy**: SSH + `docker compose pull && up -d` para MVP, o Kamal para zero-downtime (config de abajo). Gate: `curl -f https://dominio/health` devuelve 200 tras el deploy.
6. **Plan de rollback**: documenta y prueba el camino de vuelta ANTES de necesitarlo — `kamal rollback`, o re-tag de la imagen anterior + `docker compose up -d`. Gate: rollback ejecutable en < 5 minutos.
7. **Monitoreo base**: uptime + logs + alertas de servidor según `references/monitoring.md`. Para tracing/SLOs/dashboards avanzados, deriva a la skill `monitoring-observability`.
8. **Validación y cierre** — ejecutar `## Validación`; entregar resumen; registrar gaps en `LEARNINGS.md`.

---

## Defaults si falta contexto

Asume y **declara** estos supuestos en lugar de preguntar (máx. 1 pregunta solo si es bloqueante):

- Escala MVP (1-3 devs) → VPS único + Docker Compose + GitHub Actions + nginx/certbot.
- BD → PostgreSQL managed (no self-hosted) salvo presupuesto mínimo.
- Deploy → SSH + compose para MVP; Kamal si piden zero-downtime.
- **Kubernetes solo si el usuario lo pide explícitamente** — para el resto de casos, Compose/Kamal.
- Health check → endpoint `/health` que devuelve 200/503.
- Monitoreo → UptimeRobot + logs centralizados + script de alertas de servidor (lo básico; avanzado → `monitoring-observability`).
- **PHP/Node en CI y Docker** → coincidir con manifests del repo; greenfield → última estable (`stack-versions.md`).

---

## Principios Fundamentales

```
1. Infraestructura como código — todo reproducible desde un repo
2. Ambientes idénticos — dev, staging, prod con misma configuración base
3. Deploy automatizado — ningún deploy manual que no sea un click
4. Fallar rápido — tests antes de deploy, no después
5. Rollback siempre disponible — revertir en < 5 minutos
6. Secrets fuera del código — nunca en git, nunca hardcodeados
7. Observabilidad desde el día 1 — logs, métricas, alertas
```

---

## Stack DevOps Recomendado por Tamaño

> ⚠️ Datos con caducidad — revisar: los precios y planes gratuitos cambian; verifica en la web del proveedor antes de presupuestar.

### Startup / MVP (1-3 devs)
```
Servidor:    DigitalOcean Droplet o Hetzner VPS (€5-20/mes — dato con caducidad, revisar)
Containers:  Docker + Docker Compose
CI/CD:       GitHub Actions
Proxy:       Nginx + Certbot (SSL gratuito)
BD:          Managed PostgreSQL (DO/Supabase/Neon)
Redis:       Managed Redis (DO/Upstash)
Logs:        Papertrail o Logtail (gratuito para pequeño volumen — dato con caducidad, revisar)
Deploy:      SSH + docker compose pull && up
```

### Producto en Crecimiento (4-15 devs)
```
Servidor:    2-3 VPS + Load Balancer (DO/Hetzner)
o:           AWS EC2 + ALB + Auto Scaling Group
Containers:  Docker + Docker Compose o Kamal
CI/CD:       GitHub Actions con ambientes y approvals
Proxy:       Nginx o Caddy
BD:          RDS PostgreSQL Multi-AZ
Redis:       ElastiCache o managed
Logs:        Grafana Loki o Datadog
Monitoreo:   Grafana + Prometheus o Datadog
Deploy:      Kamal (zero-downtime) o ECS
```

### Escala (15+ devs) — solo si el usuario lo pide explícitamente
```
Orquestación: Kubernetes (EKS/GKE/AKS) o Nomad
CI/CD:        GitHub Actions + ArgoCD (GitOps)
Registry:     ECR o Docker Hub
Service mesh: Istio o Linkerd (si microservicios)
Observabilidad: OpenTelemetry + Grafana Stack → skill monitoring-observability
Secrets:      AWS Secrets Manager o HashiCorp Vault
```

---

## Kamal — Deploy Zero-Downtime para VPS (Recomendado para Laravel/Rails)

Kamal es la herramienta de deploy de Basecamp/37signals. Deploy de containers
a VPS con zero-downtime sin necesitar Kubernetes.

```yaml
# config/deploy.yml
service: myapp
image: registry.digitalocean.com/myapp/api

servers:
  web:
    hosts:
      - 1.2.3.4
      - 1.2.3.5
    labels:
      traefik.http.routers.myapp.rule: Host(`api.myapp.com`)
  worker:
    hosts:
      - 1.2.3.6
    cmd: php artisan queue:work --sleep=3 --tries=3

registry:
  server: registry.digitalocean.com
  username: myuser
  password:
    - KAMAL_REGISTRY_PASSWORD  # de .env

env:
  clear:
    APP_ENV: production
    APP_URL: https://api.myapp.com
  secret:
    - APP_KEY
    - DB_PASSWORD
    - REDIS_PASSWORD
    - STRIPE_SECRET

accessories:
  db:
    image: postgres:16-alpine
    host: 1.2.3.4
    env:
      secret:
        - POSTGRES_PASSWORD
    volumes:
      - /var/lib/postgresql/data:/var/lib/postgresql/data
  redis:
    image: redis:7-alpine
    host: 1.2.3.4
    volumes:
      - /var/lib/redis:/data

healthcheck:
  path: /health
  port: 3000
  interval: 5s

# Comandos
# kamal setup          → primera vez: instala Docker, configura servidor
# kamal deploy         → deploy con zero-downtime
# kamal rollback       → revertir al deploy anterior
# kamal app logs       → ver logs en tiempo real
# kamal app exec       → ejecutar comando en container
```

---

## Variables de Entorno por Ambiente

```bash
# Estructura de archivos .env
.env                  # valores por defecto (sin secrets, en git)
.env.local            # overrides locales (no en git)
.env.development      # solo desarrollo
.env.staging          # staging (sin secrets, en git)
.env.production       # producción (sin secrets, en git)
.env.test             # testing

# En .gitignore — SIEMPRE
.env.local
.env.*.local
.env.production.local

# .env.example — SIEMPRE en git, con todos los keys pero sin valores
APP_KEY=
DB_PASSWORD=
STRIPE_SECRET=
JWT_SECRET=
```

---

## Checklist DevOps por Fase

### Antes del primer deploy
- [ ] Docker Compose funcionando en dev (misma versión que prod)
- [ ] Variables de entorno documentadas en .env.example
- [ ] Pipeline de CI corriendo tests automáticamente en cada PR
- [ ] Dominio configurado con DNS
- [ ] SSL configurado (Certbot o Caddy automático)
- [ ] Health check endpoint funcionando (`/health`)
- [ ] Backup de BD configurado y testeado

### En cada deploy
- [ ] Tests pasan en CI antes de deploy
- [ ] Migraciones de BD antes de swap de containers
- [ ] Zero-downtime (sin 502 durante deploy)
- [ ] Verificar health check post-deploy (`curl -f https://dominio/health`)
- [ ] Rollback disponible y documentado

### Monitoreo en producción
- [ ] Uptime monitoring (UptimeRobot gratuito o Better Uptime)
- [ ] Error tracking (Sentry)
- [ ] Slow query log configurado
- [ ] Alertas de disco lleno, CPU, memoria
- [ ] Logs centralizados (no solo en el servidor)

---

## Ejemplo input → output

**Input:** "Deploy MVP de Laravel en VPS con Docker Compose + GitHub Actions."

**Output:** Dockerfile multi-stage + compose; workflow test en PR; nginx + certbot; deploy SSH `docker compose pull && up -d`; rollback documentado. Gate: `curl -f https://dominio/health` → 200 post-deploy.

---

## Validación

| Gate | Comando | Criterio |
|------|---------|----------|
| Compose local | `docker compose ps` | servicios running |
| CI | último workflow / `gh run list` | verde |
| Health | `curl -f https://<dominio>/health` | 200 |
| SSL | `curl -sI https://<dominio>` | cert válido |
| Secrets | grep en workflows | sin valores en claro |
| Rollback | comando documentado | ejecutable <5 min |

---

## Entregable

Al cerrar una tarea con esta skill, entrega:

```markdown
## Deploy — <proyecto/servicio>

**Infra**: VPS/proveedor, Docker Compose | Kamal
**CI/CD**: workflow(s) creados y qué gates tienen
**Ambientes**: dev / staging / production y sus URLs
**Secrets**: dónde viven (GitHub Environments / .env servidor) — sin valores

### Verificación
- [ ] CI en verde en el último run
- [ ] `curl -f https://<dominio>/health` → 200
- [ ] SSL válido
- [ ] Rollback probado: <comando exacto>

### Plan de rollback
1. <pasos concretos, < 5 min>
```

---

## Skills relacionadas

- `monitoring-observability` — observabilidad avanzada: tracing, SLOs, dashboards
- `security-checklist` — hardening y revisión de seguridad pre-producción
- `supply-chain-security` — seguridad de dependencias y pipeline
- `git-workflow` — estrategia de branches que alimenta el CI/CD
- `laravel-backend` / `node-backend` — las apps que se despliegan con esta skill

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
