# Templates y Stacks de Referencia

> La matriz de activación de fases (Nivel 0-3) vive en `phases.md` (misma carpeta).
> **Versiones:** usar última estable o la del manifest del proyecto — ver
> `laravel-backend/references/stack-versions.md`. No fijar Laravel 11 / Next 14 en propuestas.

## Stacks Recomendados por Tipo de Proyecto

### Web App — Startup / MVP Rápido
```
Frontend:   Next.js (App Router) + TypeScript + Tailwind CSS — versión en package.json
Backend:    Next.js API Routes o FastAPI (Python)
Base datos: PostgreSQL (Supabase para arranque rápido)
Auth:       NextAuth.js / Clerk / Supabase Auth
Deploy:     Vercel (frontend) + Railway/Render (backend)
CI/CD:      GitHub Actions
Monitoreo:  Vercel Analytics + Sentry
```
**Cuándo:** MVP < 3 meses, equipo pequeño, no hay requisitos de escala inmediata.

### Web App — Enterprise / Escala
```
Frontend:   React + TypeScript + Vite o Next.js
Backend:    Node.js (NestJS) o Java (Spring Boot) o .NET
Base datos: PostgreSQL + Redis (caché)
Auth:       Auth0 / Okta / AWS Cognito
Infra:      AWS / GCP / Azure con Terraform
CI/CD:      GitHub Actions / GitLab CI / Azure DevOps
Monitoreo:  Datadog / New Relic + Sentry
```
**Cuándo:** Múltiples equipos, SLAs exigentes, compliance, integración con sistemas corporativos.

### E-commerce
```
Opción A (plataforma): Shopify + Next.js (Storefront API)
Opción B (custom):     Next.js + Medusa.js + PostgreSQL
Pagos:                 Stripe / MercadoPago / PayU (según región)
Busqueda:              Algolia / Elastic Search
CDN/Media:             Cloudinary + Cloudflare
```
**Cuándo:** Opción A si < 1000 SKUs y sin personalización extrema. Opción B si necesita lógica de negocio compleja.

### App Mobile — Multiplataforma
```
Framework:  React Native + Expo (managed workflow)
Lenguaje:   TypeScript
State:      Zustand o Redux Toolkit
Navigation: React Navigation v6
Backend:    Mismo backend que web si existe
Push:       Expo Notifications + FCM
Analytics:  Mixpanel / Amplitude
```
**Cuándo:** Web + mobile compartiendo codebase, equipo JS existente, tiempo de mercado prioritario.

### App Mobile — Nativa
```
iOS:        Swift + SwiftUI + Combine
Android:    Kotlin + Jetpack Compose + Coroutines
Backend:    Compartido con web (REST o GraphQL)
```
**Cuándo:** Performance crítico, acceso a hardware específico del dispositivo,
experiencia premium diferenciada, AR/VR, procesamiento intensivo.

### App Mobile — Flutter
```
Framework:  Flutter + Dart
State:      Riverpod o Bloc
Backend:    REST API + Firebase para tiempo real
```
**Cuándo:** Una codebase para iOS, Android, y Web. Equipo sin background JS/TS.
UI muy custom con animaciones complejas.

### SaaS B2B
```
Frontend:   Next.js + TypeScript + shadcn/ui
Backend:    NestJS o FastAPI
BD:         PostgreSQL con schema por tenant (multi-tenancy)
Auth:       Auth0 con organizaciones / Clerk
Billing:    Stripe Billing + webhooks
Email:      Resend / SendGrid
Queue:      BullMQ (Node) / Celery (Python)
```

### API-First / Microservicios
```
Gateway:    Kong / AWS API Gateway
Servicios:  Node.js (NestJS) o Go o Python (FastAPI)
Mensajería: RabbitMQ / Apache Kafka
BD:         Polyglot (cada servicio su BD)
Service Mesh: Istio (si K8s) o AWS App Mesh
Observabilidad: OpenTelemetry + Jaeger/Zipkin
```
**Cuándo:** Escala real (millones de usuarios), equipos independientes por dominio,
necesidad de deploys independientes por servicio. NO para MVPs — over-engineering.

### Laravel — Backend PHP (Full Stack o API)

Laravel es un stack de primer nivel para proyectos que requieren velocidad de desarrollo,
ecosistema maduro, y equipos PHP existentes. No es "legacy" — es producción seria.

#### Laravel Monolito Full Stack
```
Backend:    Laravel (última estable o composer.json) + PHP según require
Frontend:   Livewire 3 + Alpine.js (sin build step, reactivo)
            o Inertia.js + Vue 3 / React (SPA con routing de Laravel)
UI:         Tailwind CSS + shadcn/ui (via Inertia) o Flux (Livewire)
Base datos: MySQL 8 / PostgreSQL 15+
ORM:        Eloquent (incluido)
Auth:       Laravel Breeze (simple) / Jetstream (completo con 2FA, teams)
Deploy:     Laravel Forge + DigitalOcean/AWS/Hetzner
            o Laravel Vapor (serverless en AWS Lambda)
CI/CD:      GitHub Actions con Pest + Pint
```
**Cuándo:** Equipo PHP, proyecto con lógica de negocio compleja, CRUDs intensivos,
sistema administrativo, plataforma de gestión. Productividad máxima desde día 1.

#### Laravel API Backend (Headless)
```
Backend:    Laravel API (versión del proyecto)
Autenticación:
  - Laravel Sanctum     → SPA mismo dominio / mobile tokens
  - Laravel Passport    → OAuth2 completo (terceros, múltiples apps)
  - Laravel Fortify     → Auth headless (base para Sanctum/Passport)
Frontend:   Next.js / Nuxt / React Native / Flutter (cualquiera)
Base datos: PostgreSQL / MySQL
Deploy:     Laravel Octane (Swoole/FrankenPHP) para alta concurrencia
```
**Cuándo:** API para múltiples clientes (web + mobile), microservicios parciales,
backend compartido entre equipos frontend independientes.

#### Laravel + Arquitectura Modular
```
Estructura: nwidart/laravel-modules (DDD por módulo)
Módulos:    Auth, Users, Billing, Notifications, Reports (independientes)
Ventaja:    Monolito mantenible que puede extraerse a microservicios luego
```
**Cuándo:** Proyectos medianos-grandes donde el monolito puro se vuelve difícil
de mantener pero los microservicios son prematuros.

---

#### Paquetes Laravel por Categoría

**Autenticación y Autorización**
```
laravel/breeze          → Auth simple (blade/vue/react/api). Punto de partida limpio.
laravel/jetstream       → Auth completo: 2FA, teams, API tokens, profile photos.
laravel/fortify         → Backend headless de auth (base de Breeze y Jetstream).
laravel/sanctum         → Tokens API + SPA auth. Estándar para mobile/SPA.
laravel/passport        → OAuth2 server completo. Para apps que dan auth a terceros.
spatie/laravel-permission → RBAC/ABAC. Roles y permisos por modelo. Estándar de facto.
```

**Base de Datos y Consultas**
```
laravel/eloquent        → ORM incluido. Relaciones, scopes, observers, castings.
spatie/laravel-query-builder → Filtros, sorts, includes via query params. APIs REST limpias.
calebporzio/sushi       → Eloquent models desde arrays/CSVs. Datos estáticos sin migración.
staudenmeir/eloquent-eager-limit → Eager loading con limit por relación.
kirschbaum-development/eloquent-power-joins → JOINs complejos con Eloquent.
```

**APIs y Transformación de Datos**
```
spatie/laravel-fractal  → Transformers/serializers para APIs. Alternativa a Resources.
league/fractal          → Base de spatie/fractal. Embeddings, paginación, includes.
dedoc/scramble          → Documentación OpenAPI auto-generada desde código Laravel.
knuckleswtf/scribe      → Alternativa a Scramble para documentación API automática.
```

**Colas, Jobs y Eventos**
```
laravel/horizon         → Dashboard para colas Redis. Monitoreo, métricas, reintentos.
laravel/reverb          → WebSockets nativo Laravel (reemplaza Pusher para self-hosted).
laravel/echo            → Cliente JS para WebSockets/broadcasting.
spatie/laravel-webhook-client → Recibir webhooks con firma, logs, jobs.
spatie/laravel-webhook-server → Enviar webhooks con reintentos y firma.
```

**Archivos y Media**
```
spatie/laravel-medialibrary → Gestión de archivos/imágenes asociados a modelos.
                              Conversiones automáticas, thumbnails, S3.
spatie/laravel-image-optimizer → Optimización automática de imágenes subidas.
intervention/image      → Manipulación de imágenes (resize, crop, watermark).
protonemedia/laravel-ffmpeg → Procesamiento de video/audio con FFmpeg.
```

**PDFs y Documentos**
```
barryvdh/laravel-dompdf → PDF desde vistas Blade. Sencillo, sin dependencias externas.
spatie/laravel-browsershot → PDF/screenshots con Puppeteer. Alta fidelidad CSS.
maatwebsite/laravel-excel → Excel/CSV import-export. El estándar absoluto.
```

**Notificaciones y Comunicación**
```
laravel/notifications   → Incluido. Email, SMS, Slack, DB desde un solo lugar.
spatie/laravel-mailcoach → Plataforma email marketing self-hosted sobre Laravel.
laravel-notification-channels/* → 50+ canales: Telegram, WhatsApp, Discord, etc.
```

**Multi-tenancy**
```
stancl/tenancy          → Multi-tenancy completo: BD por tenant o schema por tenant.
                          Auto-tenancy, tenant-aware jobs, eventos de ciclo de vida.
spatie/laravel-multitenancy → Alternativa más simple. Un tenant por request.
```

**Pagos y Facturación**
```
laravel/cashier-stripe  → Suscripciones Stripe: planes, trials, invoices, webhooks.
laravel/cashier-paddle  → Mismo para Paddle (sin PCI compliance propio).
spatie/laravel-stripe-checkout → Checkout de Stripe simplificado.
```

**Testing**
```
pestphp/pest            → Testing moderno sobre PHPUnit. Sintaxis limpia, plugins.
pest-plugin/laravel     → Plugin Pest específico para Laravel (artisan, requests).
spatie/pest-plugin-snapshots → Snapshot testing para APIs y vistas.
mockery/mockery         → Mocks/spies para tests. Incluido en Laravel por defecto.
```

**Monitoreo y Debugging**
```
laravel/telescope       → Debug local: queries, jobs, emails, events, logs. Esencial en dev.
laravel/pulse           → Monitoreo de producción: performance, queues, lento. Dashboard.
spatie/laravel-ray      → Debug con app Ray. Printf moderno para Laravel.
sentry/sentry-laravel   → Error tracking en producción. Integración nativa.
```

**Admin Panels**
```
filament/filament        → Admin panel de primer nivel. Rapid development, extensible.
                           Forms, tables, actions, notifications, widgets. El mejor del ecosistema.
backpack/crud            → CRUD admin alternativo. Más configurable, más verboso.
orchid/platform          → Admin orientado a aplicaciones complejas con roles.
```

**Performance y Caché**
```
laravel/octane          → Servidor persistente (Swoole/RoadRunner/FrankenPHP).
                          10x performance vs PHP-FPM tradicional.
spatie/laravel-responsecache → Caché completo de respuestas HTTP. Sin tocar lógica.
mateusjunges/laravel-kafka → Integración Kafka para eventos de alta escala.
```

**Utilidades Spatie (el ecosistema más completo)**
```
spatie/laravel-activitylog  → Log de actividad por modelo. Auditoría completa.
spatie/laravel-sluggable    → Slugs automáticos para modelos.
spatie/laravel-tags         → Sistema de tags polimórfico.
spatie/laravel-settings     → Settings tipados y cacheados en BD.
spatie/laravel-backup       → Backup automático de BD y archivos a S3/FTP.
spatie/laravel-sitemap      → Generación de sitemap.xml automática.
spatie/laravel-translatable → Modelos multiidioma sin tablas extra.
spatie/laravel-data         → DTOs tipados, validación, transformación. Muy poderoso.
spatie/laravel-typescript-transformer → Genera tipos TypeScript desde clases PHP.
```

---

#### Cuándo Elegir Laravel vs Node/Python

| Criterio | Laravel gana | Node/Python gana |
|----------|-------------|-----------------|
| Velocidad de desarrollo CRUD | ✅ Claro ganador | — |
| Ecosistema de paquetes maduros | ✅ Muy maduro | Python similar |
| Equipo existente PHP | ✅ Obvio | — |
| Tiempo real / WebSockets intensivos | Reverb (bueno) | Node (mejor) |
| ML / IA en backend | — | ✅ Python claro ganador |
| Microservicios pequeños | — | ✅ Node/Go más ligero |
| Admin panel built-in (Filament) | ✅ Sin rival | — |
| Multi-tenancy complejo | ✅ Tenancy for Laravel | — |
| API pública con OAuth2 | ✅ Passport | Similar NestJS |
| Serverless / Edge | Vapor (costoso) | ✅ Node más natural |

---

## Plantilla: Resumen Ejecutivo

```markdown
# [Nombre del Proyecto] — Resumen Ejecutivo

## El Problema
[1-2 oraciones describiendo el problema real del negocio]

## La Solución Propuesta
[Descripción del producto en lenguaje de negocio, sin jerga técnica]

## Usuarios Objetivo
- Usuario primario: [descripción]
- Usuario secundario: [descripción]
- Volumen estimado: [número de usuarios proyectados]

## Alcance del Proyecto

### Incluido en este alcance:
- [funcionalidad 1]
- [funcionalidad 2]

### Fuera de alcance (versiones futuras):
- [funcionalidad A]
- [funcionalidad B]

## Inversión Estimada
| Fase | Duración | Costo Estimado |
|------|----------|----------------|
| MVP  | X semanas | $X,XXX - $X,XXX |
| V1.0 | X semanas | $X,XXX - $X,XXX |
| **Total** | **X semanas** | **$XX,XXX - $XX,XXX** |

*Estimaciones con metodología PERT. Rangos reflejan variabilidad normal de proyectos de software.*

## Riesgos Principales
1. [Riesgo 1] — Mitigación: [acción]
2. [Riesgo 2] — Mitigación: [acción]
3. [Riesgo 3] — Mitigación: [acción]

## Próximos Pasos
1. [acción] — Responsable: [quién] — Fecha: [cuándo]
2. [acción] — Responsable: [quién] — Fecha: [cuándo]
```

---

## Plantilla: Architecture Decision Record (ADR)

```markdown
# ADR-[NNN]: [Título de la Decisión]

**Fecha:** YYYY-MM-DD
**Estado:** Propuesta | Aceptada | Reemplazada por ADR-NNN | Deprecada
**Decidido por:** [nombre/rol]

## Contexto
[Por qué esta decisión existe. Qué problema resuelve. Qué restricciones aplican.]

## Opciones Evaluadas

### Opción A: [nombre]
- **Pros:** [lista]
- **Contras:** [lista]
- **Costo estimado de implementación:** [tiempo/dinero]

### Opción B: [nombre]
- **Pros:** [lista]
- **Contras:** [lista]
- **Costo estimado de implementación:** [tiempo/dinero]

## Decisión
Elegimos **[Opción X]** porque [justificación basada en contexto y criterios].

## Consecuencias Positivas
- [beneficio 1]
- [beneficio 2]

## Trade-offs Aceptados
- [limitación 1]
- [limitación 2]

## Criterios para Revisar esta Decisión
[Qué condición o evento haría que revisáramos esta decisión]
```

---

## Plantilla: User Story con Criterios de Aceptación

```markdown
## US-[NNN]: [Título corto]

**Épica:** [nombre de la épica]
**Prioridad:** P0 | P1 | P2 | P3
**Estimación:** [puntos de historia o días]
**Dependencias:** [US-XXX, US-YYY]

### Historia
Como **[tipo de usuario]**
Quiero **[acción específica y concreta]**
Para **[beneficio de negocio medible]**

### Criterios de Aceptación

**Escenario 1: [nombre del happy path]**
- DADO que [contexto inicial]
- CUANDO [el usuario realiza la acción]
- ENTONCES [resultado esperado verificable]

**Escenario 2: [nombre del edge case]**
- DADO que [contexto con condición especial]
- CUANDO [el usuario realiza la acción]
- ENTONCES [comportamiento correcto del sistema]

**Escenario 3: [nombre del error path]**
- DADO que [condición de error]
- CUANDO [el usuario intenta la acción]
- ENTONCES [el sistema muestra/hace X]

### Definición de Hecho
- [ ] Criterios de aceptación verificados en staging
- [ ] Tests unitarios escritos (cobertura ≥ 80% en lógica de negocio)
- [ ] Tests de integración si hay interacción con otros módulos
- [ ] Code review aprobado por al menos 1 par
- [ ] Sin errores en linter/formatter
- [ ] Documentación de API actualizada si aplica
- [ ] QA sign-off
- [ ] Desplegado en staging
```

---

## Plantilla: Change Request

```markdown
# CR-[NNN]: [Título del Cambio]

**Fecha de solicitud:** YYYY-MM-DD
**Solicitado por:** [nombre/rol del cliente]
**Prioridad solicitada:** Urgente | Alta | Normal | Baja

## Descripción del Cambio
[Qué se está pidiendo cambiar o agregar]

## Justificación del Cliente
[Por qué el cliente necesita este cambio]

## Análisis de Impacto Técnico

### Módulos Afectados
- [módulo 1]: [descripción del impacto]
- [módulo 2]: [descripción del impacto]

### Estimación de Esfuerzo
| Actividad | Optimista | Probable | Pesimista | PERT |
|-----------|-----------|----------|-----------|------|
| Backend   | Xd | Xd | Xd | Xd |
| Frontend  | Xd | Xd | Xd | Xd |
| QA        | Xd | Xd | Xd | Xd |
| **Total** | | | | **Xd** |

### Impacto en Timeline
[Cómo afecta las fechas comprometidas actualmente]

### Impacto en Costo
[Costo adicional estimado]

### Riesgos del Cambio
[Qué puede salir mal si se hace este cambio]

## Opciones

### Opción 1: Implementar ahora
Costo: $X — Impacto en entrega: +X días

### Opción 2: Diferir a siguiente fase
Sin impacto en fase actual. Estimación en fase siguiente.

### Opción 3: No implementar
[Justificación de por qué no se recomienda]

## Decisión
[ ] Aprobado — Opción: ___ — Firma cliente: _____________ Fecha: ______
[ ] Rechazado — Motivo: ___________________________________
[ ] Diferido — A fase: ___________________________________
```

---

## Checklist de Kick-off de Proyecto

### Antes de empezar a desarrollar:
- [ ] Documento de alcance firmado por cliente
- [ ] Mapa contextual completo (todos los campos del SKILL.md)
- [ ] Prototipo navegable aprobado por cliente (si aplica)
- [ ] Stack técnico definido y validado con spike si es nueva tecnología
- [ ] Repositorio creado con estructura base y README
- [ ] Ambientes configurados: dev, staging, producción
- [ ] CI/CD pipeline básico funcionando (al menos build + deploy automático)
- [ ] Canales de comunicación definidos y acordados con cliente
- [ ] Herramienta de gestión de proyectos configurada (Jira, Linear, GitHub Projects)
- [ ] Accesos necesarios entregados al equipo
- [ ] Definición de Done acordada con cliente
- [ ] Primera retrospectiva y demo agendadas
- [ ] SLA de respuesta del cliente acordado y firmado
- [ ] Contacto de emergencia del cliente fuera de horas hábiles
