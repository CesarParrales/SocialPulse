# NestJS — Arquitectura y Patrones

## Guards — Autenticación y Autorización

```typescript
// JWT Auth Guard
@Injectable()
export class JwtAuthGuard extends AuthGuard('jwt') {
  canActivate(context: ExecutionContext): boolean | Promise<boolean> {
    return super.canActivate(context);
  }

  handleRequest(err: any, user: any, info: any) {
    if (err || !user) {
      throw err || new UnauthorizedException('Invalid or expired token');
    }
    return user;
  }
}

// Roles Guard
@Injectable()
export class RolesGuard implements CanActivate {
  constructor(private reflector: Reflector) {}

  canActivate(context: ExecutionContext): boolean {
    const requiredRoles = this.reflector.getAllAndOverride<Role[]>(ROLES_KEY, [
      context.getHandler(),
      context.getClass(),
    ]);

    if (!requiredRoles) return true; // sin restricción de rol

    const { user } = context.switchToHttp().getRequest();
    return requiredRoles.some(role => user.roles?.includes(role));
  }
}

// Decorator para marcar roles requeridos
export const Roles = (...roles: Role[]) => SetMetadata(ROLES_KEY, roles);

// Uso en controller
@Controller('admin/orders')
@UseGuards(JwtAuthGuard, RolesGuard)
@Roles(Role.Admin, Role.Manager)
export class AdminOrdersController {
  @Get()
  findAll() { ... }

  @Delete(':id')
  @Roles(Role.Admin)  // override — solo Admin puede eliminar
  remove(@Param('id') id: string) { ... }
}

// Guard global — registrar en main.ts o AppModule
app.useGlobalGuards(new JwtAuthGuard(new Reflector()));
```

---

## Interceptors — Transformación y Logging

```typescript
// Response transform — envolver toda respuesta en {data: ...}
@Injectable()
export class TransformInterceptor<T>
  implements NestInterceptor<T, { data: T }> {

  intercept(
    context: ExecutionContext,
    next: CallHandler,
  ): Observable<{ data: T }> {
    return next.handle().pipe(
      map(data => ({ data }))
    );
  }
}

// Logging interceptor — tiempo de respuesta
@Injectable()
export class LoggingInterceptor implements NestInterceptor {
  private readonly logger = new Logger(LoggingInterceptor.name);

  intercept(context: ExecutionContext, next: CallHandler): Observable<any> {
    const req = context.switchToHttp().getRequest();
    const { method, url, user } = req;
    const start = Date.now();

    return next.handle().pipe(
      tap({
        next: () => {
          const ms = Date.now() - start;
          this.logger.log(`${method} ${url} ${ms}ms user=${user?.id}`);
        },
        error: (error) => {
          const ms = Date.now() - start;
          this.logger.error(`${method} ${url} ${ms}ms error=${error.message}`);
        },
      }),
    );
  }
}

// Cache interceptor custom con Redis
@Injectable()
export class CacheInterceptor implements NestInterceptor {
  constructor(
    @Inject(CACHE_MANAGER) private cacheManager: Cache,
  ) {}

  async intercept(ctx: ExecutionContext, next: CallHandler): Promise<Observable<any>> {
    const req = ctx.switchToHttp().getRequest();
    const key = `cache:${req.url}`;

    const cached = await this.cacheManager.get(key);
    if (cached) {
      return of(cached); // devolver desde caché
    }

    return next.handle().pipe(
      tap(data => this.cacheManager.set(key, data, 300)) // cachear 5 min
    );
  }
}
```

---

## Exception Filters — Manejo Centralizado de Errores

```typescript
// Filter global — formato consistente de errores
@Catch()
export class GlobalExceptionFilter implements ExceptionFilter {
  private readonly logger = new Logger(GlobalExceptionFilter.name);

  catch(exception: unknown, host: ArgumentsHost): void {
    const ctx = host.switchToHttp();
    const response = ctx.getResponse<Response>();
    const request = ctx.getRequest<Request>();

    let status = HttpStatus.INTERNAL_SERVER_ERROR;
    let message = 'Internal server error';
    let errors: Record<string, string[]> | undefined;
    let errorCode: string | undefined;

    if (exception instanceof HttpException) {
      status = exception.getStatus();
      const res = exception.getResponse();

      if (typeof res === 'object' && 'message' in res) {
        if (Array.isArray((res as any).message)) {
          // Errores de validación (class-validator)
          message = 'The given data was invalid';
          errors = this.formatValidationErrors((res as any).message);
        } else {
          message = (res as any).message;
        }
      }
    } else if (exception instanceof DomainException) {
      status = exception.httpStatus;
      message = exception.message;
      errorCode = exception.errorCode;
    } else {
      // Error inesperado — loguear completo, responder genérico
      const errorId = crypto.randomUUID();
      this.logger.error('Unhandled exception', {
        errorId,
        error: exception,
        path: request.url,
        method: request.method,
      });

      response.status(500).json({
        message: 'An unexpected error occurred',
        errorId, // para correlacionar con logs
      });
      return;
    }

    response.status(status).json({
      message,
      ...(errors && { errors }),
      ...(errorCode && { errorCode }),
    });
  }

  private formatValidationErrors(messages: string[]): Record<string, string[]> {
    return messages.reduce((acc, msg) => {
      const field = msg.split(' ')[0];
      if (!acc[field]) acc[field] = [];
      acc[field].push(msg);
      return acc;
    }, {} as Record<string, string[]>);
  }
}
```

---

## Auth con JWT + Refresh Tokens

```typescript
// auth/auth.service.ts
@Injectable()
export class AuthService {
  constructor(
    private usersService: UsersService,
    private jwtService: JwtService,
    private configService: ConfigService,
  ) {}

  async login(user: User): Promise<TokenPair> {
    const payload = { sub: user.id, email: user.email, roles: user.roles };

    const [accessToken, refreshToken] = await Promise.all([
      this.jwtService.signAsync(payload, {
        secret: this.configService.get('jwt.secret'),
        expiresIn: '15m',  // access token corto
      }),
      this.jwtService.signAsync(payload, {
        secret: this.configService.get('jwt.refreshSecret'),
        expiresIn: '30d',  // refresh token largo
      }),
    ]);

    // Guardar hash del refresh token (no el token en plano)
    const hashedRefresh = await bcrypt.hash(refreshToken, 10);
    await this.usersService.updateRefreshToken(user.id, hashedRefresh);

    return { accessToken, refreshToken };
  }

  async refreshTokens(userId: number, refreshToken: string): Promise<TokenPair> {
    const user = await this.usersService.findById(userId);

    if (!user?.hashedRefreshToken) {
      throw new ForbiddenException('Access denied');
    }

    const isValid = await bcrypt.compare(refreshToken, user.hashedRefreshToken);
    if (!isValid) throw new ForbiddenException('Access denied');

    return this.login(user);
  }

  async logout(userId: number): Promise<void> {
    await this.usersService.updateRefreshToken(userId, null);
  }
}

// auth/strategies/jwt.strategy.ts
@Injectable()
export class JwtStrategy extends PassportStrategy(Strategy) {
  constructor(configService: ConfigService) {
    super({
      jwtFromRequest: ExtractJwt.fromAuthHeaderAsBearerToken(),
      ignoreExpiration: false,
      secretOrKey: configService.get('jwt.secret'),
    });
  }

  async validate(payload: JwtPayload): Promise<AuthUser> {
    return {
      id: payload.sub,
      email: payload.email,
      roles: payload.roles,
    };
  }
}
```

---

## Pipes — Validación y Transformación

```typescript
// ParseUlidPipe — convertir string a ULID validado
@Injectable()
export class ParseUlidPipe implements PipeTransform {
  transform(value: string): string {
    if (!isValidUlid(value)) {
      throw new BadRequestException(`Invalid ID format: ${value}`);
    }
    return value;
  }
}

// SanitizePipe — limpiar inputs de texto
@Injectable()
export class SanitizePipe implements PipeTransform {
  transform(value: any): any {
    if (typeof value === 'string') {
      return value.trim().replace(/<[^>]*>/g, ''); // strip HTML tags
    }
    if (typeof value === 'object' && value !== null) {
      return Object.fromEntries(
        Object.entries(value).map(([k, v]) => [k, this.transform(v)])
      );
    }
    return value;
  }
}

// Uso en controller
@Get(':id')
findOne(
  @Param('id', ParseUlidPipe) id: string,
  @Body(SanitizePipe, new ValidationPipe()) dto: UpdateOrderDto,
) { ... }
```

---

## Controller Completo

```typescript
@ApiTags('orders')
@ApiBearerAuth()
@Controller({ path: 'orders', version: '1' })
@UseGuards(JwtAuthGuard)
@UseInterceptors(LoggingInterceptor)
export class OrdersController {
  constructor(private readonly ordersService: OrdersService) {}

  @Get()
  @ApiOperation({ summary: 'List orders with pagination and filters' })
  @ApiQuery({ name: 'status', required: false, enum: OrderStatus })
  @ApiQuery({ name: 'page', required: false, type: Number })
  async findAll(
    @CurrentUser() user: AuthUser,
    @Query() query: FindOrdersQueryDto,
  ): Promise<PaginatedResponse<OrderDto>> {
    return this.ordersService.findAll(user.id, query);
  }

  @Post()
  @HttpCode(HttpStatus.CREATED)
  @ApiOperation({ summary: 'Create a new order' })
  @ApiResponse({ status: 201, type: OrderDto })
  @ApiResponse({ status: 422, description: 'Validation failed' })
  @ApiResponse({ status: 409, description: 'Insufficient stock' })
  async create(
    @CurrentUser() user: AuthUser,
    @Body() dto: CreateOrderDto,
  ): Promise<OrderDto> {
    return this.ordersService.create(user.id, dto);
  }

  @Get(':id')
  @ApiParam({ name: 'id', description: 'Order ULID' })
  async findOne(
    @CurrentUser() user: AuthUser,
    @Param('id', ParseUlidPipe) id: string,
  ): Promise<OrderDto> {
    const order = await this.ordersService.findByIdOrFail(id);
    if (order.userId !== user.id && !user.roles.includes(Role.Admin)) {
      throw new ForbiddenException();
    }
    return order;
  }

  @Post(':id/cancel')
  @HttpCode(HttpStatus.OK)
  async cancel(
    @CurrentUser() user: AuthUser,
    @Param('id', ParseUlidPipe) id: string,
    @Body() dto: CancelOrderDto,
  ): Promise<OrderDto> {
    return this.ordersService.cancel(id, user.id, dto.reason);
  }
}

// Decorator para obtener usuario actual
export const CurrentUser = createParamDecorator(
  (data: unknown, ctx: ExecutionContext): AuthUser => {
    const request = ctx.switchToHttp().getRequest();
    return request.user;
  },
);
```

---

## Health Checks

```typescript
// health/health.controller.ts
@Controller('health')
export class HealthController {
  constructor(
    private health: HealthCheckService,
    private db: PrismaHealthIndicator,
    private redis: RedisHealthIndicator,
    private http: HttpHealthIndicator,
  ) {}

  @Get()
  @HealthCheck()
  check() {
    return this.health.check([
      () => this.db.isHealthy('database'),
      () => this.redis.isHealthy('redis'),
      () => this.http.pingCheck('stripe', 'https://api.stripe.com/v1'),
    ]);
  }
}

// Respuesta:
// GET /health → 200 { status: 'ok', info: { database: { status: 'up' }, ... } }
// GET /health → 503 { status: 'error', error: { redis: { status: 'down' } } }
```
