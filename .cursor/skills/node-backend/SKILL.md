---
name: node-backend
description: >
  Guía el desarrollo backend en Node.js con NestJS y Express: arquitectura, módulos,
  guards, interceptors, WebSockets, queues con BullMQ, testing y deploy. Usar cuando
  el usuario mencione: Node.js, NestJS, Express, Fastify, TypeScript backend, APIs en
  Node, BullMQ, Socket.io, Prisma, TypeORM, o cuando diga "cómo estructuro un proyecto
  Node", "cómo hago auth en NestJS", "cómo proceso jobs en Node", "necesito WebSockets
  en Node", "cómo escalo Node.js", o cualquier variante. También aplica cuando el
  stack de backend definido sea JavaScript/TypeScript aunque no se mencione el
  framework específico.
---

# Node Backend Skill

Backend Node.js de producción con NestJS como framework principal.

**NestJS — arquitectura y módulos → `references/nestjs.md`**
**Prisma ORM → `references/prisma.md`**
**BullMQ — queues y workers → `references/bullmq.md`**
**WebSockets con Socket.io → `references/websockets.md`**
**Testing y Deploy → `references/testing-deploy.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — estructura de módulos, gates (`npm run test:e2e`, health).
2. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** decisiones de módulo/queue → project-memory; entregable en repo; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución — Módulo NestJS

8 pasos para implementar cualquier módulo/feature de backend Node:

0. **Memoria** — leer `.cursor/project-memory.md`, `package.json` y convenciones Nest del proyecto.
1. **Module** — crear el módulo con CLI (`nest g module orders`) declarando
   imports/providers/exports (patrón de abajo). Para guards, interceptors y
   estructura avanzada leer `references/nestjs.md`.
2. **DTO + validación** — DTOs con class-validator (`whitelist: true` global) y
   decoradores Swagger (sección DTOs de abajo).
3. **Service** — lógica de negocio inyectable; acceso a datos con Prisma
   (leer `references/prisma.md` si hay esquema/queries nuevos).
4. **Controller** — endpoints versionados (`/api/v1/`), códigos HTTP correctos,
   side-effects async via BullMQ (leer `references/bullmq.md`); tiempo real via
   gateway (leer `references/websockets.md`).
5. **Test e2e** — happy path + validación + auth (leer
   `references/testing-deploy.md`). Gate: `npm run test:e2e` pasa.
6. **Swagger** — verificar que el módulo aparece documentado. Gate: `/docs`
   responde y lista los endpoints nuevos en entorno no productivo.
7. **Checklist de deploy** — repasar el Checklist Node.js Producción de este
   archivo. Gate: health check responde —
   `curl -s -o /dev/null -w "%{http_code}" http://localhost:3000/health` devuelve 200.
8. **Validación y cierre** — ejecutar `## Validación`; actualizar project-memory;
   registrar gaps en `LEARNINGS.md`.

---

## Defaults si falta contexto

Asumir y **declarar** estos supuestos (máximo 1 pregunta al usuario, solo si es bloqueante):

| Falta | Default asumido |
|-------|-----------------|
| Framework | **NestJS por defecto**; API < 5 endpoints sin necesidad de DI → Express minimal |
| Lenguaje | TypeScript estricto (última estable) |
| Runtime | Node.js LTS activa o versión en `.nvmrc` / `package.json` engines |
| ORM | Prisma + PostgreSQL |
| Validación | class-validator con `whitelist: true` y `forbidNonWhitelisted: true` |
| Auth | JWT con refresh tokens (Passport) |
| Jobs async | BullMQ + Redis |
| Docs | Swagger en `/docs` (solo no-producción) |
| Tests | Jest unit + supertest e2e |

---

## NestJS vs Express vs Fastify

```
NestJS (recomendado para proyectos serios):
  ✅ Arquitectura opinada: módulos, DI, decoradores
  ✅ TypeScript nativo
  ✅ Ecosistema completo: guards, interceptors, pipes, filters
  ✅ CLI para scaffolding rápido
  ✅ Escala bien con equipos grandes
  ❌ Curva de aprendizaje por decoradores y DI
  ❌ Over-engineered para microservicios muy simples

Express (para APIs simples o equipos con preferencia):
  ✅ Mínimo, flexible, conocido universalmente
  ✅ Ecosistema enorme de middlewares
  ❌ Sin estructura — cada equipo inventa la suya
  ❌ TypeScript requiere configuración extra
  ❌ No escala bien en equipos sin disciplina

Fastify (para performance máximo):
  ✅ 2x más rápido que Express en benchmarks
  ✅ TypeScript nativo con schemas
  ✅ Plugin system bien diseñado
  ❌ Ecosistema menor que Express/NestJS
  ❌ Menos desarrolladores familiarizados
```

---

## Estructura de Proyecto NestJS

```
src/
├── main.ts                    # Bootstrap de la app
├── app.module.ts              # Módulo raíz
│
├── config/                    # Configuración tipada
│   ├── configuration.ts
│   └── validation.ts
│
├── common/                    # Compartido entre módulos
│   ├── decorators/            # Decoradores custom
│   ├── filters/               # Exception filters globales
│   ├── guards/                # Guards de auth
│   ├── interceptors/          # Logging, transform, caché
│   ├── pipes/                 # Validación y transformación
│   └── dto/                   # DTOs compartidos (pagination, etc.)
│
├── modules/                   # Módulos de dominio
│   ├── auth/
│   │   ├── auth.module.ts
│   │   ├── auth.controller.ts
│   │   ├── auth.service.ts
│   │   ├── strategies/        # Passport strategies (JWT, Local)
│   │   └── dto/
│   │
│   ├── orders/
│   │   ├── orders.module.ts
│   │   ├── orders.controller.ts
│   │   ├── orders.service.ts
│   │   ├── orders.repository.ts
│   │   ├── entities/          # Prisma/TypeORM entities
│   │   ├── dto/               # CreateOrderDto, UpdateOrderDto
│   │   └── events/            # OrderCreated, OrderShipped
│   │
│   └── users/
│
├── database/
│   ├── prisma/
│   │   └── schema.prisma
│   └── migrations/
│
└── test/
    ├── app.e2e-spec.ts
    └── jest-e2e.json
```

---

## Bootstrap de la Aplicación

```typescript
// main.ts
import { NestFactory } from '@nestjs/core';
import { ValidationPipe, VersioningType } from '@nestjs/common';
import { SwaggerModule, DocumentBuilder } from '@nestjs/swagger';
import { AppModule } from './app.module';

async function bootstrap() {
  const app = await NestFactory.create(AppModule, {
    logger: ['error', 'warn', 'log'],  // no verbose en prod
  });

  // Prefijo global y versionado
  app.setGlobalPrefix('api');
  app.enableVersioning({
    type: VersioningType.URI,
    defaultVersion: '1',
  });

  // Validación global con class-validator
  app.useGlobalPipes(new ValidationPipe({
    whitelist: true,          // eliminar campos no declarados en DTO
    forbidNonWhitelisted: true, // error si llegan campos no permitidos
    transform: true,          // auto-transformar tipos (string → number)
    transformOptions: {
      enableImplicitConversion: true,
    },
  }));

  // CORS
  app.enableCors({
    origin: process.env.FRONTEND_URL,
    credentials: true,
  });

  // Swagger (solo en no-producción)
  if (process.env.NODE_ENV !== 'production') {
    const config = new DocumentBuilder()
      .setTitle('MyApp API')
      .setVersion('1.0')
      .addBearerAuth()
      .build();
    const document = SwaggerModule.createDocument(app, config);
    SwaggerModule.setup('docs', app, document);
  }

  // Graceful shutdown
  app.enableShutdownHooks();

  const port = process.env.PORT ?? 3000;
  await app.listen(port);
  console.log(`Application running on port ${port}`);
}

bootstrap();
```

---

## Módulo Completo — Patrón Estándar

```typescript
// orders/orders.module.ts
@Module({
  imports: [
    TypeOrmModule.forFeature([Order, OrderItem]),
    BullModule.registerQueue({ name: 'orders' }),
    UsersModule,          // módulos que necesita
    ProductsModule,
  ],
  controllers: [OrdersController],
  providers: [
    OrdersService,
    OrdersRepository,
    OrdersProcessor,      // BullMQ processor
  ],
  exports: [OrdersService],  // exponer para otros módulos
})
export class OrdersModule {}
```

---

## Configuración Tipada con @nestjs/config

```typescript
// config/configuration.ts
export default () => ({
  port: parseInt(process.env.PORT ?? '3000', 10),
  database: {
    host: process.env.DB_HOST,
    port: parseInt(process.env.DB_PORT ?? '5432', 10),
    name: process.env.DB_DATABASE,
    username: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
  },
  jwt: {
    secret: process.env.JWT_SECRET,
    expiresIn: process.env.JWT_EXPIRES_IN ?? '7d',
    refreshSecret: process.env.JWT_REFRESH_SECRET,
    refreshExpiresIn: process.env.JWT_REFRESH_EXPIRES_IN ?? '30d',
  },
  redis: {
    host: process.env.REDIS_HOST ?? 'localhost',
    port: parseInt(process.env.REDIS_PORT ?? '6379', 10),
  },
  stripe: {
    secretKey: process.env.STRIPE_SECRET_KEY,
    webhookSecret: process.env.STRIPE_WEBHOOK_SECRET,
  },
});

// config/validation.ts — validar ENV al arrancar
import * as Joi from 'joi';

export const validationSchema = Joi.object({
  NODE_ENV: Joi.string().valid('development', 'production', 'test').required(),
  PORT: Joi.number().default(3000),
  DB_HOST: Joi.string().required(),
  DB_DATABASE: Joi.string().required(),
  DB_USERNAME: Joi.string().required(),
  DB_PASSWORD: Joi.string().required(),
  JWT_SECRET: Joi.string().min(32).required(),
  REDIS_HOST: Joi.string().required(),
  STRIPE_SECRET_KEY: Joi.string().required(),
});

// app.module.ts — registrar configuración
@Module({
  imports: [
    ConfigModule.forRoot({
      isGlobal: true,           // disponible en todos los módulos
      load: [configuration],
      validationSchema,
      validationOptions: {
        abortEarly: true,       // falla rápido si falta variable crítica
      },
    }),
    // resto de módulos...
  ],
})
export class AppModule {}
```

---

## DTOs con class-validator

```typescript
// orders/dto/create-order.dto.ts
import { IsArray, IsString, IsInt, Min, MaxLength,
         IsOptional, ValidateNested, ArrayMinSize } from 'class-validator';
import { Type } from 'class-transformer';
import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';

class OrderItemDto {
  @ApiProperty({ example: 42 })
  @IsInt()
  @Min(1)
  productId: number;

  @ApiProperty({ example: 2 })
  @IsInt()
  @Min(1)
  quantity: number;
}

export class CreateOrderDto {
  @ApiProperty({ type: [OrderItemDto] })
  @IsArray()
  @ArrayMinSize(1)
  @ValidateNested({ each: true })
  @Type(() => OrderItemDto)
  items: OrderItemDto[];

  @ApiProperty({ example: '123 Main St, New York' })
  @IsString()
  @MaxLength(500)
  shippingAddress: string;

  @ApiPropertyOptional({ example: 'SAVE20' })
  @IsOptional()
  @IsString()
  couponCode?: string;
}
```

---

## Checklist Node.js Producción

### Configuración
- [ ] Variables de entorno validadas con Joi al arrancar
- [ ] `NODE_ENV=production` en producción
- [ ] Graceful shutdown configurado (`enableShutdownHooks`)
- [ ] Cluster mode o PM2 para usar todos los cores
- [ ] Límite de memoria configurado (`--max-old-space-size`)

### Seguridad
- [ ] Helmet para headers de seguridad
- [ ] Rate limiting (throttler de NestJS)
- [ ] CORS restrictivo
- [ ] `whitelist: true` en ValidationPipe (sin esto, campos extras pasan)
- [ ] JWT con refresh tokens
- [ ] Secrets en variables de entorno, nunca en código

### Performance
- [ ] Compresión activada (`compression` middleware)
- [ ] Connection pooling en BD (Prisma lo maneja)
- [ ] Redis para caché y sessions
- [ ] BullMQ para tareas async (no bloquear el event loop)

### Monitoreo
- [ ] Structured logging (Winston/Pino)
- [ ] Health checks expuestos (`@nestjs/terminus`)
- [ ] Métricas Prometheus (`@willsoto/nestjs-prometheus`)
- [ ] Sentry para error tracking

---

## Ejemplo input → output

**Input:** "Módulo NestJS para webhooks de Stripe con cola BullMQ."

**Output:** `WebhooksModule` + DTO verificación firma + `StripeWebhookService` + job `ProcessStripeEvent` en BullMQ; e2e 200 payload válido, 400 firma inválida. Gates: `npm run test:e2e -- webhooks` exit 0; `/health` → 200.

---

## Validación

| Gate | Comando | Criterio |
|------|---------|----------|
| E2E | `npm run test:e2e` | exit 0 |
| Unit (si aplica) | `npm run test` | exit 0 |
| Health | `curl -s -o /dev/null -w "%{http_code}" <base>/health` | 200 |
| Swagger | GET `/docs` en dev | lista endpoints nuevos |
| Lint/build | `npm run lint` / `npm run build` (según project-memory) | exit 0 |

---

## Entregable

Todo módulo implementado con esta skill cierra con:

```markdown
## Módulo: [nombre]

### Archivos
- Module: src/modules/<x>/<x>.module.ts
- DTOs: src/modules/<x>/dto/...
- Service: src/modules/<x>/<x>.service.ts
- Controller: src/modules/<x>/<x>.controller.ts (+ endpoints y métodos)
- Tests: test/<x>.e2e-spec.ts

### Verificación
- [ ] `npm run test:e2e` pasa
- [ ] `/health` responde 200
- [ ] `/docs` (Swagger) lista los endpoints nuevos
- [ ] Variables de entorno nuevas agregadas al schema Joi y a .env.example

### Pendientes / deuda asumida
[...]
```

---

## Skills relacionadas

- `web-architecture` — patrón arquitectónico del proyecto (módulos, capas, eventos)
- `database-design` — el modelo de datos que Prisma consume
- `api-design` — contratos REST/GraphQL que estos controllers implementan
- `nextjs-fullstack` — cuando el backend Node convive con Next.js
- `testing-strategy` — estrategia de tests más allá de Jest/supertest
- `security-checklist` — hardening de auth, tokens y secrets
- `devops-base` — CI/CD, Docker y deploy del servicio

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
