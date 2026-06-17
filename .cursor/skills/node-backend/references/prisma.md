# Prisma ORM

## Schema Completo

```prisma
// prisma/schema.prisma
generator client {
  provider        = "prisma-client-js"
  previewFeatures = ["fullTextSearch", "fullTextIndex"]
}

datasource db {
  provider = "postgresql"
  url      = env("DATABASE_URL")
}

model User {
  id                  String    @id @default(cuid())
  email               String    @unique
  name                String
  passwordHash        String    @map("password_hash")
  role                Role      @default(CUSTOMER)
  hashedRefreshToken  String?   @map("hashed_refresh_token")
  emailVerifiedAt     DateTime? @map("email_verified_at")
  createdAt           DateTime  @default(now()) @map("created_at")
  updatedAt           DateTime  @updatedAt @map("updated_at")

  orders              Order[]
  addresses           Address[]
  notifications       Notification[]

  @@map("users")       // nombre de tabla en snake_case
}

model Order {
  id              String      @id @default(cuid())
  reference       String      @unique
  status          OrderStatus @default(PENDING)
  totalCents      Int         @map("total_cents")
  currency        String      @default("USD") @db.Char(3)
  shippingAddress String      @map("shipping_address")
  notes           String?
  metadata        Json        @default("{}")
  createdAt       DateTime    @default(now()) @map("created_at")
  updatedAt       DateTime    @updatedAt @map("updated_at")
  deletedAt       DateTime?   @map("deleted_at")

  userId          String      @map("user_id")
  user            User        @relation(fields: [userId], references: [id])

  items           OrderItem[]
  payments        Payment[]

  @@index([userId, status])
  @@index([createdAt(sort: Desc)])
  @@map("orders")
}

model OrderItem {
  id          String  @id @default(cuid())
  quantity    Int
  priceCents  Int     @map("price_cents")

  orderId     String  @map("order_id")
  order       Order   @relation(fields: [orderId], references: [id], onDelete: Cascade)

  productId   String  @map("product_id")
  product     Product @relation(fields: [productId], references: [id])

  @@map("order_items")
}

enum OrderStatus {
  PENDING
  PROCESSING
  SHIPPED
  DELIVERED
  CANCELLED
}

enum Role {
  ADMIN
  MANAGER
  CUSTOMER
}
```

---

## Prisma Service

```typescript
// database/prisma.service.ts
@Injectable()
export class PrismaService extends PrismaClient implements OnModuleInit, OnModuleDestroy {
  constructor(private configService: ConfigService) {
    super({
      datasources: {
        db: { url: configService.get('database.url') },
      },
      log: configService.get('NODE_ENV') === 'development'
        ? ['query', 'info', 'warn', 'error']
        : ['warn', 'error'],
    });
  }

  async onModuleInit() {
    await this.$connect();
  }

  async onModuleDestroy() {
    await this.$disconnect();
  }

  // Soft delete helper
  async softDelete(model: string, id: string): Promise<void> {
    await (this as any)[model].update({
      where: { id },
      data: { deletedAt: new Date() },
    });
  }

  // Transacción helper tipada
  async transaction<T>(
    fn: (prisma: Omit<PrismaClient, '$connect' | '$disconnect' | '$on' | '$transaction' | '$use' | '$extends'>) => Promise<T>
  ): Promise<T> {
    return this.$transaction(fn);
  }
}
```

---

## Repository Pattern con Prisma

```typescript
// orders/orders.repository.ts
@Injectable()
export class OrdersRepository {
  constructor(private prisma: PrismaService) {}

  async create(data: CreateOrderInput): Promise<Order> {
    return this.prisma.order.create({
      data: {
        reference: generateReference(),
        userId: data.userId,
        shippingAddress: data.shippingAddress,
        totalCents: data.totalCents,
        items: {
          create: data.items.map(item => ({
            productId: item.productId,
            quantity: item.quantity,
            priceCents: item.priceCents,
          })),
        },
      },
      include: { items: { include: { product: true } }, user: true },
    });
  }

  async findById(id: string): Promise<Order | null> {
    return this.prisma.order.findFirst({
      where: {
        id,
        deletedAt: null,  // soft delete filter
      },
      include: {
        items: { include: { product: true } },
        user: { select: { id: true, name: true, email: true } },
      },
    });
  }

  async findPaginated(
    userId: string,
    filters: OrderFilters,
    pagination: PaginationInput,
  ): Promise<{ data: Order[]; total: number }> {
    const where: Prisma.OrderWhereInput = {
      userId,
      deletedAt: null,
      ...(filters.status && { status: filters.status }),
      ...(filters.createdAfter && { createdAt: { gte: filters.createdAfter } }),
      ...(filters.search && {
        OR: [
          { reference: { contains: filters.search, mode: 'insensitive' } },
        ],
      }),
    };

    const [data, total] = await this.prisma.$transaction([
      this.prisma.order.findMany({
        where,
        include: { items: true },
        orderBy: { createdAt: 'desc' },
        skip: (pagination.page - 1) * pagination.perPage,
        take: pagination.perPage,
      }),
      this.prisma.order.count({ where }),
    ]);

    return { data, total };
  }

  async updateStatus(id: string, status: OrderStatus): Promise<Order> {
    return this.prisma.order.update({
      where: { id },
      data: { status, updatedAt: new Date() },
    });
  }

  // Transacción: crear orden y decrementar stock
  async createWithStockReservation(data: CreateOrderInput): Promise<Order> {
    return this.prisma.$transaction(async (tx) => {
      // Verificar y decrementar stock
      for (const item of data.items) {
        const updated = await tx.product.updateMany({
          where: {
            id: item.productId,
            stock: { gte: item.quantity }, // solo si hay suficiente stock
          },
          data: { stock: { decrement: item.quantity } },
        });

        if (updated.count === 0) {
          throw new InsufficientStockException(item.productId);
        }
      }

      // Crear la orden
      return tx.order.create({
        data: {
          userId: data.userId,
          reference: generateReference(),
          shippingAddress: data.shippingAddress,
          totalCents: data.totalCents,
          items: { create: data.items },
        },
        include: { items: true },
      });
    });
  }
}
```

---

## Migraciones Prisma

```bash
# Crear migración
npx prisma migrate dev --name add_order_reference_column

# Aplicar en producción (no interactivo)
npx prisma migrate deploy

# Reset en desarrollo (cuidado — borra datos)
npx prisma migrate reset

# Generar cliente después de cambios en schema
npx prisma generate

# Inspeccionar BD existente → generar schema
npx prisma db pull

# Abrir Prisma Studio (GUI)
npx prisma studio
```

---

## Prisma con Read Replicas

```typescript
// Para proyectos con alta carga de lectura
const prisma = new PrismaClient().$extends(
  readReplicas({
    url: process.env.DATABASE_REPLICA_URL,
  })
);

// Prisma automáticamente enruta:
// findMany, findFirst, findUnique → replica
// create, update, delete, $transaction → primary

// Forzar primary para lectura crítica (post-write)
const order = await prisma.$primary().order.findUnique({
  where: { id: orderId },
});
```

---

## Seeding

```typescript
// prisma/seed.ts
const prisma = new PrismaClient();

async function main() {
  // Idempotente — upsert por campo único
  const admin = await prisma.user.upsert({
    where: { email: 'admin@example.com' },
    update: {},
    create: {
      email: 'admin@example.com',
      name: 'Admin User',
      passwordHash: await bcrypt.hash('password', 10),
      role: 'ADMIN',
    },
  });

  // Datos de catálogo
  const categories = [
    { name: 'Electronics', slug: 'electronics' },
    { name: 'Clothing', slug: 'clothing' },
  ];

  await prisma.category.createMany({
    data: categories,
    skipDuplicates: true,
  });

  console.log(`Seeded: admin=${admin.id}`);
}

main()
  .catch(console.error)
  .finally(() => prisma.$disconnect());

// package.json
// "prisma": { "seed": "ts-node prisma/seed.ts" }
// Ejecutar: npx prisma db seed
```
