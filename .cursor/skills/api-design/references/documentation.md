# Documentación OpenAPI con Scramble

## Setup con dedoc/scramble (Laravel)

```bash
composer require dedoc/scramble
php artisan vendor:publish --provider="Dedoc\Scramble\ScrambleServiceProvider"
```

```php
// config/scramble.php
return [
    'api_path' => 'api',        // prefijo de rutas a documentar
    'api_domain' => null,
    'info' => [
        'version' => env('APP_VERSION', '1.0.0'),
        'description' => 'API documentation for MyApp',
    ],
    // Autenticación en Swagger UI
    'middleware' => ['web', 'auth'],  // proteger la docs en producción
];
```

---

## Documentar con PHPDoc — Scramble lee automáticamente

```php
class OrderController extends Controller
{
    /**
     * List user orders
     *
     * Returns a paginated list of orders for the authenticated user.
     * Supports filtering by status and date range.
     *
     * @queryParam status string Filter by status. Example: pending
     * @queryParam created_after string Filter orders after date. Example: 2024-01-01
     * @queryParam per_page integer Items per page (1-100). Default: 20. Example: 20
     * @queryParam page integer Page number. Default: 1. Example: 1
     *
     * @response OrderCollection
     */
    public function index(IndexOrderRequest $request): OrderCollection
    {
        // ...
    }

    /**
     * Create order
     *
     * Creates a new order for the authenticated user.
     * Stock is validated at creation time.
     *
     * @response 201 OrderResource
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "items": ["At least one item is required."],
     *     "items.0.product_id": ["Product does not exist."]
     *   }
     * }
     * @response 409 {
     *   "message": "Insufficient stock for product.",
     *   "error_code": "INSUFFICIENT_STOCK"
     * }
     */
    public function store(CreateOrderRequest $request): OrderResource
    {
        // ...
    }

    /**
     * Get order details
     *
     * @urlParam order string required The order UUID. Example: 01HX4B2C3D
     *
     * @response OrderResource
     * @response 403 {
     *   "message": "This action is unauthorized.",
     *   "error_code": "FORBIDDEN"
     * }
     * @response 404 {
     *   "message": "Order not found.",
     *   "error_code": "ORDER_NOT_FOUND"
     * }
     */
    public function show(Order $order): OrderResource
    {
        // ...
    }
}
```

---

## OpenAPI Manual (cuando se necesita más control)

```yaml
# openapi.yaml — spec completa
openapi: 3.1.0
info:
  title: MyApp API
  version: 1.0.0
  description: |
    REST API for MyApp platform.

    ## Authentication
    All endpoints (except auth) require Bearer token authentication.

    ```
    Authorization: Bearer {token}
    ```

    ## Rate Limiting
    - Authenticated: 60 requests/minute
    - Unauthenticated: 10 requests/minute

    Rate limit headers are included in every response:
    - `X-RateLimit-Limit`
    - `X-RateLimit-Remaining`
    - `Retry-After` (only when limit exceeded)

servers:
  - url: https://api.myapp.com/v1
    description: Production
  - url: https://staging-api.myapp.com/v1
    description: Staging
  - url: http://localhost:8000/api/v1
    description: Local development

security:
  - BearerAuth: []

components:
  securitySchemes:
    BearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT

  schemas:
    Order:
      type: object
      required: [id, status, total, created_at]
      properties:
        id:
          type: string
          format: ulid
          example: "01HX4B2C3D4E5F6G7H8J9K0L1M"
          description: Public order identifier (ULID)
        status:
          type: string
          enum: [pending, processing, shipped, delivered, cancelled]
          example: processing
        total:
          $ref: '#/components/schemas/Money'
        user:
          $ref: '#/components/schemas/UserSummary'
          nullable: true
          description: Included when ?include=user
        items:
          type: array
          items:
            $ref: '#/components/schemas/OrderItem'
          description: Included when ?include=items
        created_at:
          type: string
          format: date-time
          example: "2024-01-15T14:30:00Z"
        updated_at:
          type: string
          format: date-time

    Money:
      type: object
      required: [amount, currency, formatted]
      properties:
        amount:
          type: integer
          description: Amount in cents
          example: 8500
        currency:
          type: string
          maxLength: 3
          example: USD
        formatted:
          type: string
          example: "$85.00"

    PaginationMeta:
      type: object
      properties:
        current_page: { type: integer, example: 2 }
        last_page:    { type: integer, example: 18 }
        per_page:     { type: integer, example: 20 }
        total:        { type: integer, example: 347 }

    Error:
      type: object
      required: [message]
      properties:
        message:
          type: string
          example: "The given data was invalid."
        error_code:
          type: string
          example: "ORDER_NOT_FOUND"
        errors:
          type: object
          additionalProperties:
            type: array
            items: { type: string }
          description: Field-level validation errors (422 only)

paths:
  /orders:
    get:
      summary: List orders
      operationId: listOrders
      tags: [Orders]
      parameters:
        - name: status
          in: query
          schema:
            type: string
            enum: [pending, processing, shipped, delivered, cancelled]
        - name: page
          in: query
          schema: { type: integer, default: 1 }
        - name: per_page
          in: query
          schema: { type: integer, default: 20, minimum: 1, maximum: 100 }
        - name: sort
          in: query
          description: "Field to sort by. Prefix with - for descending. Example: -created_at"
          schema:
            type: string
            enum: [created_at, -created_at, total_cents, -total_cents]
        - name: include
          in: query
          description: "Comma-separated relationships to include"
          schema:
            type: string
            example: "user,items"
      responses:
        '200':
          description: Paginated list of orders
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    type: array
                    items: { $ref: '#/components/schemas/Order' }
                  meta:
                    $ref: '#/components/schemas/PaginationMeta'
        '401':
          description: Unauthenticated
          content:
            application/json:
              schema: { $ref: '#/components/schemas/Error' }

    post:
      summary: Create order
      operationId: createOrder
      tags: [Orders]
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [items, shipping_address]
              properties:
                items:
                  type: array
                  minItems: 1
                  maxItems: 50
                  items:
                    type: object
                    required: [product_id, quantity]
                    properties:
                      product_id: { type: integer }
                      quantity:   { type: integer, minimum: 1, maximum: 100 }
                shipping_address:
                  type: string
                  maxLength: 500
                coupon_code:
                  type: string
                  nullable: true
            example:
              items:
                - product_id: 42
                  quantity: 2
                - product_id: 17
                  quantity: 1
              shipping_address: "123 Main St, New York, NY 10001"
              coupon_code: "SAVE20"
      responses:
        '201':
          description: Order created
          headers:
            Location:
              description: URL of the created order
              schema: { type: string, format: uri }
          content:
            application/json:
              schema:
                type: object
                properties:
                  data: { $ref: '#/components/schemas/Order' }
        '422':
          description: Validation failed
          content:
            application/json:
              schema: { $ref: '#/components/schemas/Error' }
        '409':
          description: Business rule violation (insufficient stock, order limit exceeded)
          content:
            application/json:
              schema: { $ref: '#/components/schemas/Error' }
```

---

## Postman Collection — Generar desde OpenAPI

```bash
# Con Newman CLI
npm install -g newman

# Importar spec y correr tests
newman run openapi.yaml \
  --env-var "base_url=https://staging-api.myapp.com/v1" \
  --env-var "token=your-test-token" \
  --reporters cli,json \
  --reporter-json-export results.json

# En CI/CD — verificar que la API cumple el contrato
# Si falla: el deploy no procede
```

---

## Changelog de API — Comunicar Cambios

```markdown
# API Changelog

## v2.0.0 — 2024-06-01 (Breaking Changes)
### ⚠️ Breaking Changes
- `GET /orders`: campo `price` renombrado a `total.amount` (en centavos)
- `POST /orders`: campo `address` renombrado a `shipping_address`
- Status `"in-transit"` renombrado a `"shipped"`

### Migration Guide
Ver: https://docs.myapp.com/api/migration/v1-to-v2

### Deprecation Notice
v1 soportada hasta 2024-12-01. Sunset header incluido en todas las respuestas v1.

---

## v1.3.0 — 2024-03-15 (Non-breaking)
### ✅ New
- `GET /orders`: nuevo filtro `created_after`, `created_before`
- `GET /orders/{id}`: nuevo campo `estimated_delivery` en respuesta
- Nuevo endpoint: `POST /orders/{id}/track` para iniciar tracking

### 🔧 Fixed
- `GET /products`: ordenamiento por `price` ahora correcto para múltiples monedas
```
