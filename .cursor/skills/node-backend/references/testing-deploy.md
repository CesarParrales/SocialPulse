# Testing y Deploy — Node/NestJS

## Testing con Jest + Supertest

```typescript
// test/orders.e2e-spec.ts — tests end-to-end
import { Test, TestingModule } from '@nestjs/testing';
import { INestApplication, ValidationPipe } from '@nestjs/common';
import * as request from 'supertest';
import { PrismaService } from '../src/database/prisma.service';

describe('Orders (e2e)', () => {
  let app: INestApplication;
  let prisma: PrismaService;
  let authToken: string;
  let testUser: any;

  beforeAll(async () => {
    const moduleFixture: TestingModule = await Test.createTestingModule({
      imports: [AppModule],
    }).compile();

    app = moduleFixture.createNestApplication();
    app.useGlobalPipes(new ValidationPipe({ whitelist: true, transform: true }));
    await app.init();

    prisma = moduleFixture.get<PrismaService>(PrismaService);
  });

  beforeEach(async () => {
    // Limpiar BD y crear datos frescos
    await prisma.order.deleteMany();
    await prisma.user.deleteMany();

    testUser = await prisma.user.create({
      data: {
        email: 'test@example.com',
        name: 'Test User',
        passwordHash: await bcrypt.hash('password', 10),
      },
    });

    // Obtener token de auth
    const loginRes = await request(app.getHttpServer())
      .post('/api/v1/auth/login')
      .send({ email: 'test@example.com', password: 'password' });

    authToken = loginRes.body.data.accessToken;
  });

  afterAll(async () => {
    await prisma.$disconnect();
    await app.close();
  });

  describe('POST /api/v1/orders', () => {
    it('creates order successfully', async () => {
      const product = await prisma.product.create({
        data: { name: 'Test Product', priceCents: 5000, stock: 10 },
      });

      const response = await request(app.getHttpServer())
        .post('/api/v1/orders')
        .set('Authorization', `Bearer ${authToken}`)
        .send({
          items: [{ productId: product.id, quantity: 2 }],
          shippingAddress: '123 Main St',
        })
        .expect(201);

      expect(response.body.data).toMatchObject({
        status: 'PENDING',
        totalCents: 10000,
      });

      // Verificar stock decrementado
      const updatedProduct = await prisma.product.findUnique({
        where: { id: product.id },
      });
      expect(updatedProduct?.stock).toBe(8);
    });

    it('returns 422 for missing items', () =>
      request(app.getHttpServer())
        .post('/api/v1/orders')
        .set('Authorization', `Bearer ${authToken}`)
        .send({ shippingAddress: '123 Main St' })
        .expect(422)
    );

    it('returns 401 without token', () =>
      request(app.getHttpServer())
        .post('/api/v1/orders')
        .send({ items: [], shippingAddress: '123 Main St' })
        .expect(401)
    );
  });
});

// tests/unit/orders.service.spec.ts — unit test
describe('OrdersService', () => {
  let service: OrdersService;
  let repository: jest.Mocked<OrdersRepository>;
  let queue: jest.Mocked<Queue>;

  beforeEach(async () => {
    const module = await Test.createTestingModule({
      providers: [
        OrdersService,
        {
          provide: OrdersRepository,
          useValue: {
            createWithStockReservation: jest.fn(),
            findById: jest.fn(),
            updateStatus: jest.fn(),
          },
        },
        {
          provide: getQueueToken(ORDER_QUEUE),
          useValue: { add: jest.fn() },
        },
        {
          provide: getQueueToken(EMAIL_QUEUE),
          useValue: { add: jest.fn() },
        },
      ],
    }).compile();

    service = module.get<OrdersService>(OrdersService);
    repository = module.get(OrdersRepository);
    queue = module.get(getQueueToken(ORDER_QUEUE));
  });

  it('creates order and dispatches payment job', async () => {
    const mockOrder = { id: 'ord_123', totalCents: 5000, status: 'PENDING' };
    repository.createWithStockReservation.mockResolvedValue(mockOrder as any);
    queue.add.mockResolvedValue({} as any);

    const result = await service.create('user_1', {
      items: [{ productId: 'prod_1', quantity: 1 }],
      shippingAddress: '123 Main St',
    } as CreateOrderDto);

    expect(result).toEqual(mockOrder);
    expect(queue.add).toHaveBeenCalledWith(
      OrderJobName.ProcessPayment,
      expect.objectContaining({ orderId: 'ord_123' }),
      expect.any(Object),
    );
  });

  it('propagates repository errors', async () => {
    repository.createWithStockReservation.mockRejectedValue(
      new InsufficientStockException('prod_1')
    );

    await expect(service.create('user_1', {
      items: [{ productId: 'prod_1', quantity: 100 }],
      shippingAddress: '123',
    } as any)).rejects.toThrow(InsufficientStockException);
  });
});
```

---

## Logging Estructurado con Pino

```typescript
// logger/pino-logger.service.ts
import pino from 'pino';

@Injectable()
export class LoggerService implements LoggerService {
  private logger = pino({
    level: process.env.LOG_LEVEL ?? 'info',
    transport: process.env.NODE_ENV === 'development'
      ? { target: 'pino-pretty' }
      : undefined,
    serializers: {
      req: (req) => ({
        id: req.id,
        method: req.method,
        url: req.url,
        userId: req.user?.id,
      }),
      err: pino.stdSerializers.err,
    },
  });

  log(message: string, context?: string) {
    this.logger.info({ context }, message);
  }

  error(message: string, trace?: string, context?: string) {
    this.logger.error({ context, trace }, message);
  }

  warn(message: string, context?: string) {
    this.logger.warn({ context }, message);
  }

  // Crear logger con contexto para trazabilidad
  forContext(context: string) {
    return this.logger.child({ context });
  }
}
```

---

## Deploy con PM2

```javascript
// ecosystem.config.js
module.exports = {
  apps: [{
    name: 'api',
    script: 'dist/main.js',
    instances: 'max',          // un proceso por CPU core
    exec_mode: 'cluster',      // cluster mode para balancear
    max_memory_restart: '500M',
    env_production: {
      NODE_ENV: 'production',
      PORT: 3000,
    },
    // Zero-downtime reload
    wait_ready: true,
    listen_timeout: 10000,
    kill_timeout: 5000,
  }, {
    name: 'worker',
    script: 'dist/worker.js',   // proceso separado para queue workers
    instances: 2,
    exec_mode: 'fork',
    env_production: {
      NODE_ENV: 'production',
    },
  }],
};
```

```bash
# Deploy commands
npm run build
pm2 reload ecosystem.config.js --env production  # zero-downtime reload
pm2 save   # persistir configuración
```

---

## Dockerfile Optimizado

```dockerfile
# Multi-stage build para imagen mínima
FROM node:20-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production && npm ci  # deps de prod + dev para build
COPY . .
RUN npm run build

# Imagen de producción — mínima
FROM node:20-alpine AS production
WORKDIR /app

# Usuario no-root para seguridad
RUN addgroup -S app && adduser -S app -G app

COPY --from=builder --chown=app:app /app/dist ./dist
COPY --from=builder --chown=app:app /app/node_modules ./node_modules
COPY --from=builder --chown=app:app /app/package.json ./

USER app
EXPOSE 3000

# Health check para orquestadores
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
  CMD wget -qO- http://localhost:3000/health || exit 1

CMD ["node", "dist/main.js"]
```

```yaml
# docker-compose.yml para desarrollo
services:
  api:
    build: .
    ports: ['3000:3000']
    environment:
      - DATABASE_URL=postgresql://myapp:secret@postgres:5432/myapp
      - REDIS_HOST=redis
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_started
    volumes:
      - ./src:/app/src   # hot reload en dev

  postgres:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: myapp
      POSTGRES_USER: myapp
      POSTGRES_PASSWORD: secret
    volumes:
      - postgres_data:/var/lib/postgresql/data
    healthcheck:
      test: ['CMD-SHELL', 'pg_isready -U myapp']
      interval: 5s
      timeout: 5s
      retries: 5

  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data

  bull-board:
    image: deadly0/bull-board
    ports: ['3001:3000']
    environment:
      - REDIS_HOST=redis
    depends_on: [redis]

volumes:
  postgres_data:
  redis_data:
```
