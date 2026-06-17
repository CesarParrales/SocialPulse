# WebSockets con Socket.io en NestJS

## Gateway — Servidor WebSocket

```typescript
// notifications/notifications.gateway.ts
@WebSocketGateway({
  cors: { origin: process.env.FRONTEND_URL, credentials: true },
  namespace: '/notifications',
})
export class NotificationsGateway
  implements OnGatewayInit, OnGatewayConnection, OnGatewayDisconnect {

  @WebSocketServer() server: Server;
  private readonly logger = new Logger(NotificationsGateway.name);

  constructor(
    private jwtService: JwtService,
    private usersService: UsersService,
  ) {}

  afterInit(server: Server) {
    this.logger.log('WebSocket gateway initialized');
  }

  async handleConnection(client: Socket) {
    try {
      // Autenticar en conexión
      const token = client.handshake.auth.token
        || client.handshake.headers.authorization?.replace('Bearer ', '');

      const payload = await this.jwtService.verifyAsync(token);
      const user = await this.usersService.findById(payload.sub);

      if (!user) throw new Error('User not found');

      // Guardar user en socket para uso posterior
      client.data.user = user;

      // Unir a sala personal del usuario
      await client.join(`user:${user.id}`);

      // Unir a salas por rol
      await client.join(`role:${user.role}`);

      this.logger.log(`Client connected: ${user.id}`);
    } catch (error) {
      this.logger.warn(`Connection rejected: ${error.message}`);
      client.disconnect();
    }
  }

  handleDisconnect(client: Socket) {
    if (client.data.user) {
      this.logger.log(`Client disconnected: ${client.data.user.id}`);
    }
  }

  // Escuchar mensajes del cliente
  @SubscribeMessage('mark-as-read')
  async handleMarkAsRead(
    @ConnectedSocket() client: Socket,
    @MessageBody() data: { notificationId: string },
  ): Promise<WsResponse<{ success: boolean }>> {
    await this.notificationsService.markAsRead(
      client.data.user.id,
      data.notificationId,
    );
    return { event: 'marked-as-read', data: { success: true } };
  }
}

// NotificationsService — enviar a usuario específico
@Injectable()
export class NotificationsService {
  constructor(
    @InjectRepository(Notification)
    private repository: NotificationRepository,
    private gateway: NotificationsGateway,
  ) {}

  async sendToUser(userId: string, payload: NotificationPayload): Promise<void> {
    // Guardar en BD
    const notification = await this.repository.create({
      userId,
      type: payload.type,
      data: payload.data,
    });

    // Enviar via WebSocket si está conectado (sala personal del usuario)
    this.gateway.server
      .to(`user:${userId}`)
      .emit('notification', {
        id: notification.id,
        type: notification.type,
        data: notification.data,
        createdAt: notification.createdAt,
      });
  }

  async broadcastToAdmins(payload: AdminNotificationPayload): Promise<void> {
    this.gateway.server.to('role:ADMIN').emit('admin-alert', payload);
  }
}
```

---

## Cliente JavaScript/TypeScript

```typescript
// Frontend — conectar y escuchar
import { io, Socket } from 'socket.io-client';

class NotificationClient {
  private socket: Socket;

  connect(token: string): void {
    this.socket = io(`${process.env.NEXT_PUBLIC_API_URL}/notifications`, {
      auth: { token },
      reconnection: true,
      reconnectionAttempts: 5,
      reconnectionDelay: 1000,
    });

    this.socket.on('connect', () => {
      console.log('Connected to notifications');
    });

    this.socket.on('notification', (notification: Notification) => {
      // Mostrar toast, actualizar contador, etc.
      notificationStore.add(notification);
    });

    this.socket.on('disconnect', (reason) => {
      console.log('Disconnected:', reason);
    });

    this.socket.on('connect_error', (error) => {
      console.error('Connection error:', error.message);
    });
  }

  markAsRead(notificationId: string): void {
    this.socket.emit('mark-as-read', { notificationId });
  }

  disconnect(): void {
    this.socket.disconnect();
  }
}
```
