# BullMQ — Queues y Workers

> WebSockets con Socket.io (antes en este archivo) → `websockets.md`

## Setup con @nestjs/bullmq

```typescript
// app.module.ts
@Module({
  imports: [
    BullModule.forRootAsync({
      imports: [ConfigModule],
      useFactory: (configService: ConfigService) => ({
        connection: {
          host: configService.get('redis.host'),
          port: configService.get('redis.port'),
          password: configService.get('redis.password'),
        },
        defaultJobOptions: {
          removeOnComplete: 100,   // mantener últimos 100 completados
          removeOnFail: 500,       // mantener últimos 500 fallidos
          attempts: 3,
          backoff: {
            type: 'exponential',
            delay: 5000,           // 5s, 10s, 20s entre reintentos
          },
        },
      }),
      inject: [ConfigService],
    }),
  ],
})
export class AppModule {}
```

---

## Definir Queues y Jobs Tipados

```typescript
// orders/jobs/order-jobs.types.ts
export const ORDER_QUEUE = 'orders';
export const EMAIL_QUEUE = 'emails';

export enum OrderJobName {
  ProcessPayment = 'process-payment',
  SendConfirmation = 'send-confirmation',
  UpdateInventory = 'update-inventory',
  NotifyWarehouse = 'notify-warehouse',
}

export interface ProcessPaymentJobData {
  orderId: string;
  paymentToken: string;
  amount: number;
  currency: string;
}

export interface SendConfirmationJobData {
  orderId: string;
  userEmail: string;
  userName: string;
}

// Registrar queue en módulo
@Module({
  imports: [
    BullModule.registerQueue(
      { name: ORDER_QUEUE },
      { name: EMAIL_QUEUE },
    ),
  ],
})
export class OrdersModule {}
```

---

## Producer — Despachar Jobs

```typescript
// orders/orders.service.ts
@Injectable()
export class OrdersService {
  constructor(
    @InjectQueue(ORDER_QUEUE) private orderQueue: Queue,
    @InjectQueue(EMAIL_QUEUE) private emailQueue: Queue,
    private repository: OrdersRepository,
  ) {}

  async create(userId: string, dto: CreateOrderDto): Promise<Order> {
    const order = await this.repository.createWithStockReservation({
      userId,
      items: dto.items,
      shippingAddress: dto.shippingAddress,
    });

    // Despachar jobs en paralelo
    await Promise.all([
      this.orderQueue.add(
        OrderJobName.ProcessPayment,
        { orderId: order.id, paymentToken: dto.paymentToken, amount: order.totalCents } satisfies ProcessPaymentJobData,
        { priority: 1 }   // alta prioridad para pagos
      ),
      this.emailQueue.add(
        OrderJobName.SendConfirmation,
        { orderId: order.id, userEmail: order.user.email, userName: order.user.name },
        { delay: 2000 }   // esperar 2 seg antes de enviar (UX)
      ),
    ]);

    return order;
  }

  // Despachar con opciones avanzadas
  async scheduleReminder(orderId: string, delayMs: number): Promise<void> {
    await this.emailQueue.add(
      'send-reminder',
      { orderId },
      {
        delay: delayMs,
        jobId: `reminder:${orderId}`,  // ID único — evita duplicados
        removeOnComplete: true,
      }
    );
  }

  // Cancelar job programado
  async cancelReminder(orderId: string): Promise<void> {
    const job = await this.emailQueue.getJob(`reminder:${orderId}`);
    await job?.remove();
  }
}
```

---

## Processor — Consumir Jobs

```typescript
// orders/jobs/order.processor.ts
@Processor(ORDER_QUEUE, {
  concurrency: 5,    // procesar 5 jobs simultáneamente
  limiter: {
    max: 100,        // máximo 100 jobs por intervalo
    duration: 60000, // por minuto
  },
})
export class OrderProcessor extends WorkerHost {
  private readonly logger = new Logger(OrderProcessor.name);

  constructor(
    private paymentGateway: PaymentGateway,
    private repository: OrdersRepository,
    @InjectQueue(ORDER_QUEUE) private queue: Queue,
  ) {
    super();
  }

  async process(job: Job): Promise<any> {
    switch (job.name) {
      case OrderJobName.ProcessPayment:
        return this.processPayment(job as Job<ProcessPaymentJobData>);
      case OrderJobName.UpdateInventory:
        return this.updateInventory(job);
      default:
        throw new Error(`Unknown job: ${job.name}`);
    }
  }

  private async processPayment(job: Job<ProcessPaymentJobData>): Promise<void> {
    const { orderId, paymentToken, amount } = job.data;

    // Actualizar progreso — visible en Bull Dashboard
    await job.updateProgress(10);

    const order = await this.repository.findById(orderId);
    if (!order) throw new Error(`Order ${orderId} not found`);

    // Idempotencia — no procesar si ya está pagado
    if (order.status !== 'PENDING') {
      this.logger.log(`Order ${orderId} already processed, skipping`);
      return;
    }

    await job.updateProgress(30);

    const payment = await this.paymentGateway.charge({
      amount,
      token: paymentToken,
      metadata: { orderId },
    });

    await job.updateProgress(80);

    await this.repository.updateStatus(orderId, 'PROCESSING');
    await this.repository.savePayment(orderId, payment.id);

    // Encadenar siguiente job
    await this.queue.add(OrderJobName.NotifyWarehouse, { orderId });

    await job.updateProgress(100);
    this.logger.log(`Payment processed for order ${orderId}`);
  }

  // Hooks del ciclo de vida del job
  @OnWorkerEvent('completed')
  onCompleted(job: Job) {
    this.logger.log(`Job ${job.id} (${job.name}) completed`);
  }

  @OnWorkerEvent('failed')
  onFailed(job: Job, error: Error) {
    this.logger.error(`Job ${job.id} (${job.name}) failed: ${error.message}`, {
      jobData: job.data,
      attempts: job.attemptsMade,
    });
  }
}
```

---

## Bull Board — Dashboard de Monitoreo

```typescript
// app.module.ts — registrar Bull Board
import { createBullBoard } from '@bull-board/api';
import { BullMQAdapter } from '@bull-board/api/bullMQAdapter';
import { ExpressAdapter } from '@bull-board/express';

// En main.ts
const serverAdapter = new ExpressAdapter();
serverAdapter.setBasePath('/admin/queues');

createBullBoard({
  queues: [
    new BullMQAdapter(orderQueue),
    new BullMQAdapter(emailQueue),
  ],
  serverAdapter,
});

app.use('/admin/queues', basicAuth({ users: { admin: process.env.BULL_BOARD_PASSWORD } }), serverAdapter.getRouter());
```
