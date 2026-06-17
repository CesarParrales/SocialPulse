# Queues, Jobs y Events

## Jobs — Procesamiento Asíncrono

```php
class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Configuración del job
    public int $tries = 3;                    // reintentos máximos
    public int $maxExceptions = 2;            // excepciones antes de fallar definitivo
    public int $timeout = 60;                 // segundos antes de kill
    public int $backoff = [30, 60, 120];      // segundos entre reintentos (backoff exponencial)

    public function __construct(
        public readonly Order $order,         // serializado por ID automáticamente
        public readonly string $paymentToken,
    ) {}

    public function handle(PaymentGateway $gateway): void
    {
        // Job idempotente — seguro de re-ejecutar
        if ($this->order->payment_status === 'completed') {
            return;  // ya procesado, salir silenciosamente
        }

        $result = $gateway->charge(
            amount: $this->order->total_cents,
            token: $this->paymentToken,
            metadata: ['order_id' => $this->order->id],
        );

        $this->order->update([
            'payment_status'     => 'completed',
            'payment_id'         => $result->id,
            'payment_completed_at' => now(),
        ]);

        event(new PaymentCompleted($this->order));
    }

    // Qué hacer cuando fallan todos los reintentos
    public function failed(Throwable $exception): void
    {
        $this->order->update(['payment_status' => 'failed']);

        $this->order->user->notify(
            new PaymentFailedNotification($this->order, $exception->getMessage())
        );

        Log::error('Payment job failed permanently', [
            'order_id'  => $this->order->id,
            'exception' => $exception->getMessage(),
            'attempts'  => $this->attempts(),
        ]);
    }

    // Decidir si reintentar según tipo de excepción
    public function retryUntil(): DateTime
    {
        return now()->addHours(2);  // no reintentar después de 2 horas
    }
}

// Dispatching con control
ProcessPaymentJob::dispatch($order, $token);
ProcessPaymentJob::dispatch($order, $token)->onQueue('payments');
ProcessPaymentJob::dispatch($order, $token)->delay(now()->addMinutes(5));
ProcessPaymentJob::dispatch($order, $token)->afterCommit(); // después de que la transacción commitee

// Batch de jobs
$batch = Bus::batch([
    new ProcessImageJob($product, 'thumbnail'),
    new ProcessImageJob($product, 'medium'),
    new ProcessImageJob($product, 'large'),
])
->then(function (Batch $batch) use ($product) {
    $product->update(['images_processed' => true]);
})
->catch(function (Batch $batch, Throwable $e) {
    Log::error('Image batch failed', ['product_id' => $batch->id]);
})
->finally(function (Batch $batch) {
    // siempre ejecuta al terminar
})
->name("Process images for product {$product->id}")
->onQueue('media')
->dispatch();
```

---

## Queues — Configuración y Horizon

```php
// config/queue.php — múltiples conexiones
'connections' => [
    'redis' => [
        'driver'      => 'redis',
        'connection'  => 'default',
        'queue'       => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,    // segundos antes de considerar job colgado
        'block_for'   => null,
    ],
],

// config/horizon.php — Horizon con múltiples workers por prioridad
'environments' => [
    'production' => [
        'supervisor-default' => [
            'connection' => 'redis',
            'queue'      => ['critical', 'payments', 'default', 'emails', 'low'],
            'balance'    => 'auto',      // auto-escala workers
            'processes'  => 10,
            'tries'      => 3,
            'timeout'    => 60,
        ],
        'supervisor-media' => [
            'connection' => 'redis',
            'queue'      => ['media'],
            'balance'    => 'simple',
            'processes'  => 3,
            'timeout'    => 300,         // procesamiento de video más tiempo
        ],
    ],
],

// Despachar a queue específica según prioridad
ProcessPaymentJob::dispatch($order)->onQueue('payments');
SendWelcomeEmailJob::dispatch($user)->onQueue('emails');
GenerateReportJob::dispatch($params)->onQueue('low');
```

---

## Events y Listeners — Arquitectura Desacoplada

```php
// Evento de dominio — qué pasó
class OrderShipped
{
    public function __construct(
        public readonly Order $order,
        public readonly string $trackingNumber,
        public readonly DateTimeImmutable $shippedAt,
    ) {}
}

// Múltiples listeners independientes por evento
// EventServiceProvider o boot() en AppServiceProvider
protected $listen = [
    OrderShipped::class => [
        SendShippingConfirmationEmail::class,  // email al cliente
        UpdateInventoryRecords::class,         // actualizar inventario
        NotifyWarehouseSystem::class,          // integración externa
        CreateShippingAnalyticsEvent::class,   // analytics
    ],
];

// Listener síncrono
class SendShippingConfirmationEmail implements ShouldHandleEventsAfterCommit
{
    public function handle(OrderShipped $event): void
    {
        Mail::to($event->order->user->email)
            ->send(new ShippingConfirmationMail($event->order, $event->trackingNumber));
    }
}

// Listener en queue (async) — no bloquea al usuario
class NotifyWarehouseSystem implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'integrations';
    public int $tries = 5;

    public function handle(OrderShipped $event): void
    {
        // Llamada a sistema externo — puede fallar
        app(WarehouseClient::class)->confirmShipment(
            orderId: $event->order->reference,
            tracking: $event->trackingNumber,
        );
    }

    // No reintentar si el warehouse responde "order not found"
    public function shouldRetry(Throwable $e): bool
    {
        return !($e instanceof WarehouseOrderNotFoundException);
    }
}

// Dispatching del evento — listeners se ejecutan automáticamente
event(new OrderShipped($order, $trackingNumber, new DateTimeImmutable()));
// O en el modelo:
$order->fireEvent(new OrderShipped($order, $trackingNumber));
```

---

## Notifications — Multi-Canal desde Una Clase

```php
class OrderShippedNotification extends Notification implements ShouldQueue
{
    public function __construct(
        private readonly Order $order,
        private readonly string $trackingNumber,
    ) {}

    // Qué canales usar según preferencias del usuario
    public function via(User $notifiable): array
    {
        $channels = ['database']; // siempre guardar en BD

        if ($notifiable->email_notifications) {
            $channels[] = 'mail';
        }

        if ($notifiable->sms_notifications && $notifiable->phone) {
            $channels[] = 'vonage'; // SMS
        }

        if ($notifiable->push_notifications) {
            $channels[] = 'broadcast'; // WebSocket/Push
        }

        return $channels;
    }

    // Email
    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your order #{$this->order->reference} has shipped!")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Great news — your order is on its way.")
            ->line("Tracking number: **{$this->trackingNumber}**")
            ->action('Track Your Order', route('orders.track', $this->order->uuid))
            ->line('Estimated delivery: 3-5 business days.')
            ->salutation('Thanks for shopping with us!');
    }

    // Push/WebSocket
    public function toBroadcast(User $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'order_id'       => $this->order->uuid,
            'title'          => 'Order Shipped!',
            'body'           => "Order #{$this->order->reference} is on its way.",
            'tracking'       => $this->trackingNumber,
            'action_url'     => route('orders.track', $this->order->uuid),
        ]);
    }

    // Guardar en BD para centro de notificaciones
    public function toDatabase(User $notifiable): array
    {
        return [
            'type'       => 'order_shipped',
            'order_id'   => $this->order->uuid,
            'order_ref'  => $this->order->reference,
            'tracking'   => $this->trackingNumber,
            'message'    => "Order #{$this->order->reference} has been shipped.",
        ];
    }
}

// Dispatching
$user->notify(new OrderShippedNotification($order, $trackingNumber));

// Bulk notifications (para todos los admins, por ejemplo)
Notification::send(User::admins()->get(), new NewOrderNotification($order));
```

---

## Scheduled Tasks — Tareas Periódicas

```php
// bootstrap/app.php (Laravel actual) o Kernel.php (Laravel legacy)
Schedule::command('orders:process-pending')->hourly()->withoutOverlapping();
Schedule::command('sanctum:prune-expired')->daily();
Schedule::command('horizon:snapshot')->everyFiveMinutes();
Schedule::command('backup:run')->dailyAt('02:00')->onOneServer();
Schedule::command('reports:generate-weekly')->weeklyOn(1, '08:00'); // Lunes 8am

// Job en schedule (sin artisan command)
Schedule::job(new CleanupTempFilesJob())->daily();

// Closure para tareas simples
Schedule::call(function () {
    Product::where('stock', 0)
        ->where('updated_at', '<', now()->subDays(30))
        ->update(['is_active' => false]);
})->weekly();

// Con manejo de errores
Schedule::command('payments:reconcile')
    ->daily()
    ->withoutOverlapping()
    ->onSuccess(function () { Log::info('Reconciliation completed'); })
    ->onFailure(function () {
        Notification::route('mail', config('mail.admin'))
            ->notify(new ReconciliationFailedNotification());
    });
```

---

## Artisan Commands — Tareas Operacionales

Implementación de referencia de un command operacional (procesamiento por lotes,
dry-run, progress bar, exit codes) — el complemento natural del scheduling de arriba:

```php
class ProcessPendingOrdersCommand extends Command
{
    protected $signature = 'orders:process-pending
                           {--limit=100 : Máximo de órdenes a procesar}
                           {--dry-run : Simular sin ejecutar cambios}';

    protected $description = 'Process pending orders older than 24 hours';

    public function handle(OrderService $service): int
    {
        $limit  = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');

        $orders = Order::pending()
            ->where('created_at', '<', now()->subHours(24))
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No pending orders to process.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($orders->count());

        $processed = 0;
        $failed = 0;

        foreach ($orders as $order) {
            try {
                if (!$dryRun) {
                    $service->process($order);
                }
                $processed++;
            } catch (Throwable $e) {
                $this->error("Failed order {$order->id}: {$e->getMessage()}");
                Log::error('Order processing failed', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
                $failed++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->table(
            ['Status', 'Count'],
            [
                ['Processed', $processed],
                ['Failed', $failed],
                ['Dry run', $dryRun ? 'YES' : 'no'],
            ]
        );

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}

// Programar en bootstrap/app.php (Laravel actual) o Kernel (legacy)
Schedule::command('orders:process-pending --limit=500')
    ->hourly()
    ->withoutOverlapping()   // no correr si la anterior no terminó
    ->runInBackground()
    ->emailOutputOnFailure(config('mail.admin'));
```
