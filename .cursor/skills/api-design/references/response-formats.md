# Estructura de respuesta — formatos estándar

Envelope `{data, meta}` por defecto. Implementación Laravel → `rest.md`.

## Respuesta exitosa (recurso único)

```json
{
  "data": {
    "id": "ord_01HX4B2C3D",
    "status": "processing",
    "total": { "amount": 8500, "currency": "USD", "formatted": "$85.00" },
    "created_at": "2024-01-15T14:30:00Z",
    "updated_at": "2024-01-15T14:35:00Z"
  }
}
```

## Listado con paginación

```json
{
  "data": [...],
  "meta": {
    "current_page": 2,
    "per_page": 20,
    "total": 347,
    "last_page": 18,
    "from": 21,
    "to": 40
  },
  "links": {
    "first": "/api/v1/orders?page=1",
    "prev": "/api/v1/orders?page=1",
    "next": "/api/v1/orders?page=3",
    "last": "/api/v1/orders?page=18"
  }
}
```

## Errores

```json
// 422
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "items.0.quantity": ["The quantity must be at least 1."]
  }
}

// 404
{ "message": "Order not found.", "error_code": "ORDER_NOT_FOUND" }

// 429
{ "message": "Too many requests.", "retry_after": 30 }

// 500 (producción — sin stack trace)
{ "message": "An unexpected error occurred.", "error_id": "err_01HX4B2C3D" }
```
