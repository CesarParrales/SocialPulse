---
name: api-design
description: >
  Guía el diseño, documentación y seguridad de APIs REST y GraphQL. Usar cuando el
  usuario mencione: diseñar una API, endpoints REST, GraphQL, contratos de API,
  versionado, autenticación de API, rate limiting, documentación OpenAPI/Swagger,
  respuestas HTTP, manejo de errores, paginación, filtros, o cuando diga "cómo
  estructuro mis endpoints", "qué status code uso", "cómo autentico la API", "cómo
  versiono la API", "cómo documento la API", "REST vs GraphQL", "cómo pagino los
  resultados", o cualquier variante. También es útil al revisar código de Controllers
  o Routes con problemas de diseño de API, ofreciendo la mejora como sugerencia.
---

# API Design Skill

Guía de diseño, implementación y documentación de APIs para sistemas web.

**REST — convenciones y patrones Laravel → `references/rest.md`**
**Autenticación y seguridad → `references/auth-security.md`**
**GraphQL — schema, resolvers, seguridad → `references/graphql.md`**
**Documentación OpenAPI → `references/documentation.md`**
**Formatos de respuesta (envelope, errores) → `references/response-formats.md`**
**Checklist pre-publicación → `references/checklist.md`**

---

## Principios Fundamentales

```
1. Consistencia ante todo — mismos patrones en todos los endpoints
2. Predecibilidad — el desarrollador NO debe adivinar cómo funciona un endpoint:
   el diseño consistente lo hace evidente antes de leer la docs
3. Contratos explícitos — request y response totalmente definidos
4. Errores informativos — nunca "Something went wrong"
5. Versionado desde el día 1 — agregar /v1/ no cuesta nada, quitarlo cuesta todo
6. Idempotencia donde aplica — PUT/DELETE seguros de llamar múltiples veces
7. Stateless — el servidor no guarda estado de la sesión del cliente
```

---

## Memoria

**Al iniciar** (solo si existen; no recargar lo ya en el chat):

1. `.cursor/project-memory.md` — convenciones API del proyecto (versionado, auth).
2. Fuentes que indique project-memory (p. ej. `context.md`, ADRs de API).
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes` si hay entradas.

**Durante la tarea:** leer cada `references/*.md` solo cuando el protocolo lo indique.

**Al cerrar:** decisiones de contrato/versionado → project-memory; gaps → `LEARNINGS.md`; spec OpenAPI → `docs/` o repo acordado.

**Graphify:** solo si project-memory tiene `Graphify: enabled` o el usuario lo pide → `graphify-integration`.

---

## Protocolo de ejecución

0. **Memoria** — leer `.cursor/project-memory.md`; aplicar versionado y auth documentados.
1. **Elegir paradigma** — aplicar el árbol REST vs GraphQL de abajo. Si es GraphQL,
   leer `references/graphql.md` y seguir su checklist; el resto de este protocolo
   asume REST.
2. **Diseñar recursos y URLs** — sustantivos en plural, máximo 2 niveles de
   anidamiento, acciones no-CRUD como sub-recursos (secciones de este archivo).
3. **Definir el contrato por endpoint** — method, auth, request schema, responses
   2xx/4xx usando la plantilla de `## Entregable`. Para implementación Laravel
   (Resources, FormRequests, exception handler) leer `references/rest.md`.
4. **Asegurar el endpoint** — leer `references/auth-security.md` (auth, rate
   limiting, CORS). Gate: una petición sin token a un endpoint protegido devuelve
   401 — verificar con `curl -s -o /dev/null -w "%{http_code}" <url>`.
5. **Documentar en OpenAPI** — leer `references/documentation.md`. Gate: el spec
   valida (p. ej. `npx @redocly/cli lint openapi.yaml` sin errores).
6. **Probar happy path + errores** — tests de integración: 2xx, 401/403, 404, 422.
   Gate: la suite de tests del proyecto pasa (`php artisan test` / `npm test`).
7. **Pasar el checklist** — leer `references/checklist.md` antes de publicar.
8. **Validación y cierre** — ejecutar `## Validación`; actualizar project-memory si
   hubo decisión de contrato; registrar gaps en `LEARNINGS.md`.

---

## Defaults si falta contexto

Asumir y **declarar** estos supuestos (máximo 1 pregunta al usuario, solo si es bloqueante):

| Falta | Default asumido |
|-------|-----------------|
| Paradigma | REST (GraphQL solo con múltiples clientes y experiencia en el equipo) |
| Paginación | Offset/page (`?page=2&per_page=20`); cursor solo para feeds infinitos |
| Versionado | URL path: `/api/v1/` |
| Formato de respuesta | Envelope `{data, meta}` |
| Errores de validación | `422` con `errors` por campo |
| Auth | Bearer token (Sanctum/JWT según stack) |
| IDs públicos | UUID/ULID, nunca el autoincrement interno |

---

## REST vs GraphQL — Árbol de Decisión

```
¿Múltiples clientes (web, mobile, third-party) con necesidades de datos distintas?
├── SÍ → ¿El equipo tiene experiencia con GraphQL?
│         ├── SÍ → GraphQL (leer references/graphql.md)
│         └── NO → REST con recursos bien diseñados + sparse fieldsets
│
└── NO → REST (más simple, más predecible, mejor tooling)

¿API pública para terceros?
└── REST — más universal, mejor documentada, más fácil de consumir

¿Backend for Frontend (BFF) interno?
└── GraphQL — el frontend pide exactamente lo que necesita

¿Tiempo real (subscriptions)?
└── GraphQL Subscriptions o WebSockets (REST no aplica)

REGLA: el 80% de proyectos no necesita GraphQL.
       REST bien diseñado es suficiente y más mantenible.
```

---

## Estructura de URL REST

### Convenciones Obligatorias

```
Recursos en plural, sustantivos, minúsculas, guiones para separar palabras:

✅ /api/v1/orders
✅ /api/v1/orders/{id}
✅ /api/v1/orders/{id}/items
✅ /api/v1/users/{id}/orders
✅ /api/v1/product-categories

❌ /api/v1/getOrders          — verbo en URL
❌ /api/v1/order              — singular
❌ /api/v1/Orders             — mayúsculas
❌ /api/v1/order_categories   — underscore (usar guión)
❌ /api/v1/users/{id}/getOrders — verbo en URL de relación
```

### Anidamiento — Máximo 2 Niveles

```
✅ /orders/{id}/items              — items de una orden
✅ /users/{id}/addresses           — direcciones de un usuario

❌ /users/{id}/orders/{id}/items/{id}/reviews   — demasiado anidado
✅ Alternativa: /order-items/{id}/reviews       — recurso independiente

Regla: si necesitas más de 2 niveles, crear un recurso de primer nivel.
```

### Acciones No-CRUD

```
Para operaciones que no son CRUD puro, usar sub-recursos con verbo de negocio:

POST /orders/{id}/cancel          — cancelar una orden
POST /orders/{id}/refund          — iniciar reembolso
POST /invoices/{id}/send          — enviar factura
POST /users/{id}/verify-email     — verificar email
POST /auth/forgot-password        — iniciar recuperación
POST /auth/reset-password         — completar recuperación
POST /payments/{id}/capture       — capturar pago autorizado
```

---

## HTTP Methods — Semántica Correcta

```
GET     → Leer. Sin efectos secundarios. Cacheable.
          GET /orders           → listar
          GET /orders/{id}      → detalle

POST    → Crear o acción no idempotente.
          POST /orders          → crear orden
          POST /orders/{id}/cancel → acción

PUT     → Reemplazar completo. Idempotente.
          PUT /users/{id}       → reemplazar perfil completo

PATCH   → Actualización parcial. Idempotente.
          PATCH /users/{id}     → actualizar solo campos enviados

DELETE  → Eliminar. Idempotente.
          DELETE /orders/{id}   → eliminar/cancelar

HEAD    → Igual a GET pero sin body. Para verificar existencia.
OPTIONS → Listar métodos disponibles (CORS preflight).
```

---

## HTTP Status Codes — Los Que Importan

```
2xx — Éxito
200 OK              → GET, PUT, PATCH exitoso
201 Created         → POST que crea recurso (incluir Location header)
202 Accepted        → acción iniciada async (job en queue)
204 No Content      → DELETE exitoso, o PATCH sin cambios

3xx — Redirección
301 Moved Permanently → endpoint renombrado permanentemente
304 Not Modified    → con ETag/Last-Modified (caché del cliente válido)

4xx — Error del cliente
400 Bad Request     → payload malformado (JSON inválido)
401 Unauthorized    → no autenticado (falta token)
403 Forbidden       → autenticado pero sin permiso
404 Not Found       → recurso no existe
405 Method Not Allowed → método HTTP no soportado
409 Conflict        → conflicto de estado (ej: ya existe, estado inválido)
410 Gone            → recurso existió pero fue eliminado permanentemente
422 Unprocessable Entity → validación fallida (campos inválidos)
429 Too Many Requests → rate limit alcanzado

5xx — Error del servidor
500 Internal Server Error → bug no controlado
502 Bad Gateway     → upstream caído
503 Service Unavailable → mantenimiento o sobrecarga
```

---

## Estructura de Respuesta — Formato Estándar

Envelope `{data, meta}`, paginación y códigos de error → **`references/response-formats.md`**.

---

## Paginación — Tres Estrategias

```
1. Offset/Page (más común, suficiente para la mayoría)
   GET /orders?page=2&per_page=20
   Problema: OFFSET grande es lento en tablas grandes

2. Cursor/Keyset (para feeds infinitos, tiempo real)
   GET /orders?cursor=eyJpZCI6MTIzfQ&per_page=20
   Ventaja: performance constante sin importar la página
   Desventaja: no puedes saltar a página arbitraria

3. Seek (para ordenamiento complejo)
   GET /orders?after_id=123&per_page=20
   Simple, eficiente, predecible

Elegir según caso de uso:
- Panel admin con páginas numeradas → Offset/Page
- Feed infinito mobile → Cursor
- API simple → Seek
```

---

## Filtros, Búsqueda y Ordenamiento

```
Convenciones estándar:

Filtros:
GET /orders?status=pending
GET /orders?status[]=pending&status[]=processing   — múltiples valores
GET /orders?created_after=2024-01-01
GET /orders?created_before=2024-01-31
GET /orders?total_min=1000&total_max=5000

Búsqueda:
GET /products?search=laptop+stand

Ordenamiento:
GET /orders?sort=created_at                        — ascendente
GET /orders?sort=-created_at                       — descendente (prefijo -)
GET /orders?sort=-total,created_at                 — múltiples campos

Sparse fieldsets (solo campos necesarios):
GET /orders?fields=id,status,total

Includes/relaciones:
GET /orders?include=user,items.product
```

Implementación en Laravel con spatie/laravel-query-builder → `references/rest.md`.

---

## Versionado de API

```
Estrategias (de más a menos recomendada para APIs públicas):

1. URL path versioning (más claro, más común)
   /api/v1/orders
   /api/v2/orders

2. Header versioning (más limpio semánticamente)
   Accept: application/vnd.myapp.v2+json

3. Query param (menos recomendado)
   /api/orders?version=2

Cuándo crear v2:
- Breaking change: eliminar campo de respuesta
- Breaking change: cambiar tipo de dato de campo
- Breaking change: cambiar semántica de endpoint
- NO es breaking: agregar campo nuevo en respuesta
- NO es breaking: agregar endpoint nuevo

Política de deprecación:
1. Anunciar deprecación con headers: Deprecation: true, Sunset: [fecha futura
   real — mínimo "fecha del anuncio + 6 meses"]
2. Mantener versión vieja al menos 6 meses desde el anuncio
3. Documentar migración a versión nueva
```

Rutas versionadas y middleware de deprecación en Laravel → `references/rest.md`.
Versionado por deprecación de campos en GraphQL → `references/graphql.md`.

---

## Checklist de Diseño de API

Ver **`references/checklist.md`** (gate del paso 7 del protocolo).

---

## Ejemplo input → output

**Input:** "API REST para listar y crear tags de workspace con paginación."

**Output (resumen):** `GET/POST /api/v1/workspaces/{id}/tags` — spec con auth Bearer, envelope `{data, meta}`, 422 por nombre duplicado; OpenAPI en `docs/openapi/tags.yaml`; tests 200 paginado, 201, 401, 422. Gate: `npx @redocly/cli lint docs/openapi/tags.yaml` exit 0.

---

## Validación

| Gate | Comando | Criterio |
|------|---------|----------|
| OpenAPI | `npx @redocly/cli lint <spec>` | exit 0 |
| Tests integración | `php artisan test --filter=Tag` / `npm run test` | exit 0 |
| Auth protegida | `curl -s -o /dev/null -w "%{http_code}" <url-sin-token>` | 401 |
| Checklist API | `references/checklist.md` | todos los ítems aplicables ✓ |

---

## Entregable

Especificación mínima por endpoint:

```markdown
## POST /api/v1/orders

- **Auth:** Bearer token (scope: orders:write)
- **Rate limit:** 60/min por usuario

### Request
| Campo | Tipo | Reglas |
|-------|------|--------|
| items | array | required, min:1 |
| items[].product_id | string (ULID) | required, exists |
| items[].quantity | int | required, min:1 |
| shipping_address | string | required, max:500 |

### Responses
- **201** → `{data: Order}` + header `Location`
- **401** → sin token / token inválido
- **403** → sin permiso para crear órdenes
- **422** → `{message, errors: {campo: [...]}}`

### Tests mínimos
- [ ] 201 con payload válido (y Location apunta al recurso)
- [ ] 422 por cada regla de validación clave
- [ ] 401 sin token, 403 sin permiso
```

---

## Skills relacionadas

- `web-architecture` — dónde vive la API dentro de la arquitectura
- `database-design` — el modelo de datos que la API expone
- `laravel-backend` — implementación de la API en Laravel
- `node-backend` — implementación de la API en NestJS/Node
- `security-checklist` — hardening de auth, tokens y secrets
- `testing-strategy` — estrategia de tests de integración/contrato

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
