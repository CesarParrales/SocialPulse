# SocialPulse

Plataforma SaaS multi-tenant que unifica analytics orgánico y pagado de **Meta** (Facebook, Instagram, Messenger) y **Google Ads** para agencias digitales.

Documento de producto: [`socialpulse-prd.md`](socialpulse-prd.md)

## Stack (última versión estable al scaffold)

| Capa | Tecnología |
|------|------------|
| Backend | Laravel 13, PHP 8.4 |
| Frontend | React 19, Inertia.js 2, TypeScript, Tailwind CSS 3 |
| Base de datos | PostgreSQL |
| Cache / colas | Redis, Laravel Horizon |
| Auth | Laravel Breeze + Sanctum |
| RBAC | Spatie Laravel Permission |
| Arquitectura | Monolito modular (`nwidart/laravel-modules`) |

## Módulos

```
Modules/
├── Auth/
├── Workspaces/
├── Connections/
├── Ingestion/
├── Analytics/
├── Dashboard/
├── Reports/
└── Notifications/
```

## Requisitos locales

- PHP 8.4+
- Composer 2.x
- Node.js 22+
- PostgreSQL 16+ (o `DB_CONNECTION=sqlite` para pruebas rápidas)
- Redis 7+

## Instalación

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### Desarrollo

```bash
composer dev
```

Levanta servidor, colas, logs y Vite en paralelo.

### Horizon (colas de ingesta)

```bash
php artisan horizon
```

Colas configuradas: `ingestion-daily`, `ingestion-stories`, `ingestion-paid`, `reports`, `notifications`.

## Protocolo de desarrollo

Ver [`.cursor/rules/development-protocol.mdc`](.cursor/rules/development-protocol.mdc) y skills en [`.cursor/skills/`](.cursor/skills/).

## Próximos pasos (Mes 1 — Fundaciones)

1. Solicitar permisos Meta y Google Ads Developer Token
2. ~~Módulo `Workspaces`: multi-tenancy y roles~~ (en progreso)
3. Módulo `Connections`: OAuth Meta + Google
4. Primer job de ingesta Facebook orgánico en staging

## Cuándo aplicamos estilo al UI

| Fase | Qué | Estado |
|------|-----|--------|
| **Ahora** | Breeze por defecto (gris/índigo) — pantallas funcionales, sin identidad SocialPulse | Activo |
| **Tras flujos core** | Workspaces, Equipo, Connections y onboarding navegables | Siguiente hito |
| **Mes 4–5 (PRD)** | Dashboard con datos reales — pasada de diseño con skills `emil-design-eng`, `analisis-ux-implementacion-ui`, `atomic-design` | Planificado |
| **Pre-launch beta** | Accesibilidad (`web-interface-guidelines`) y pulido final | Planificado |

La regla: **primero flujo correcto, luego estética de producto**. Cuando cerremos Connections + onboarding, hacemos un sprint dedicado de UI/branding.

## Licencia

MIT
