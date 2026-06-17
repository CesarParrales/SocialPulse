# GraphQL — Diseño, Resolvers y Seguridad

## Cuándo Elegir GraphQL vs REST

```
Elegir GraphQL cuando:
✅ Múltiples clientes (web, mobile, TV) necesitan formas distintas de los mismos datos
✅ BFF (Backend for Frontend) interno — el frontend pide exactamente lo que necesita
✅ Datos con grafos profundos (usuario → órdenes → items → producto → reviews)
✅ El equipo controla cliente y servidor (contratos evolucionan juntos)

Quedarse en REST cuando:
❌ API pública para terceros (REST es más universal y fácil de consumir)
❌ Operaciones simples CRUD con pocas vistas distintas
❌ El equipo no tiene experiencia GraphQL (la curva incluye caching, N+1, seguridad)
❌ Necesitas caching HTTP estándar (CDN, ETags) — GraphQL usa POST a un solo endpoint

REGLA: el 80% de los proyectos no necesita GraphQL.
       REST bien diseñado es suficiente y más mantenible.
```

---

## Definición de Schema

El schema es el contrato. Diseñarlo desde el dominio, no desde la BD.

```graphql
# Tipos del dominio
type Order {
  id: ID!
  status: OrderStatus!
  total: Money!
  items: [OrderItem!]!
  user: User!
  createdAt: DateTime!
}

enum OrderStatus {
  PENDING
  PROCESSING
  SHIPPED
  DELIVERED
  CANCELLED
}

# Value object — evita escalares ambiguos
type Money {
  amount: Int!        # centavos
  currency: String!
  formatted: String!
}

# Queries — lecturas
type Query {
  order(id: ID!): Order
  orders(first: Int, after: String, status: OrderStatus): OrderConnection!
  me: User!
}

# Mutations — escrituras, con input types y payloads explícitos
input CreateOrderInput {
  items: [OrderItemInput!]!
  shippingAddress: String!
  couponCode: String
}

type CreateOrderPayload {
  order: Order
  errors: [UserError!]!   # errores de negocio como datos, no como excepciones
}

type UserError {
  field: String
  message: String!
  code: String!
}

type Mutation {
  createOrder(input: CreateOrderInput!): CreateOrderPayload!
  cancelOrder(id: ID!, reason: String): CancelOrderPayload!
}
```

**Convenciones:**
- Mutations con `input` único y `payload` con `errors` tipados.
- Nullabilidad consciente: `!` solo donde el dato siempre existe.
- Enums para dominios finitos, nunca strings libres.

---

## Resolvers y el Problema N+1

Cada campo se resuelve por separado: una query de 50 órdenes con `user`
dispara 1 query de órdenes + 50 queries de usuarios si no se previene.

```typescript
// MAL — N+1: un SELECT por cada order.user
const resolvers = {
  Order: {
    user: (order) => db.user.findUnique({ where: { id: order.userId } }),
  },
};

// BIEN — DataLoader: agrupa los IDs del mismo tick y hace 1 solo SELECT ... IN
import DataLoader from 'dataloader';

const userLoader = new DataLoader(async (userIds: readonly string[]) => {
  const users = await db.user.findMany({ where: { id: { in: [...userIds] } } });
  const byId = new Map(users.map(u => [u.id, u]));
  return userIds.map(id => byId.get(id) ?? null);  // mismo orden que las keys
});

const resolvers = {
  Order: {
    user: (order, _args, context) => context.loaders.user.load(order.userId),
  },
};

// IMPORTANTE: crear los loaders POR REQUEST (en el context factory),
// nunca globales — el caché del loader no debe sobrevivir entre requests.
const context = ({ req }) => ({
  user: authenticate(req),
  loaders: { user: createUserLoader(), product: createProductLoader() },
});
```

---

## Paginación con Cursors (Relay Connections)

Estándar de facto para listas en GraphQL:

```graphql
type OrderConnection {
  edges: [OrderEdge!]!
  pageInfo: PageInfo!
  totalCount: Int!
}

type OrderEdge {
  node: Order!
  cursor: String!
}

type PageInfo {
  hasNextPage: Boolean!
  hasPreviousPage: Boolean!
  startCursor: String
  endCursor: String
}
```

```graphql
# Uso del cliente
query {
  orders(first: 20, after: "Y3Vyc29yOjQy") {
    edges { node { id status } cursor }
    pageInfo { hasNextPage endCursor }
  }
}
```

- El cursor es opaco (base64 de un keyset, ej. `id` + `created_at`).
- Performance constante sin importar la página (keyset, no OFFSET).
- `totalCount` es opcional — en tablas enormes puede ser caro; usar estimados.

---

## Seguridad — Limitar lo que el Cliente Puede Pedir

GraphQL expone un lenguaje de consulta: sin límites, un cliente puede tumbar el servidor.

### Depth limiting

```typescript
// Bloquear queries anidadas más allá de N niveles
import depthLimit from 'graphql-depth-limit';

const server = new ApolloServer({
  schema,
  validationRules: [depthLimit(7)],
});
// Sin esto: { user { orders { items { product { reviews { user { orders ... }}}}}}
```

### Query complexity

```typescript
// Asignar costo por campo y rechazar queries que exceden el presupuesto
import { createComplexityRule, simpleEstimator } from 'graphql-query-complexity';

const complexityRule = createComplexityRule({
  maximumComplexity: 1000,
  estimators: [simpleEstimator({ defaultComplexity: 1 })],
  // listas multiplican: orders(first: 100) { items } = 100 × costo(items)
});
```

### Otras medidas obligatorias

```
- Deshabilitar introspección en producción (o limitarla a clientes internos)
- Persisted queries / allowlist para clientes propios (solo queries pre-registradas)
- Timeout por operación y rate limiting por usuario (no solo por IP)
- Autorización POR CAMPO/RESOLVER, no solo en el endpoint
  (el guard del endpoint no protege campos sensibles anidados)
- Validar tamaño máximo del request body
```

---

## Versionado — Deprecación de Campos, No /v2

GraphQL no versiona por URL. El schema evoluciona campo a campo:

```graphql
type Order {
  # Campo nuevo que reemplaza al viejo
  total: Money!

  # Campo viejo marcado deprecated — los clientes lo ven en tooling/introspección
  totalAmount: Float @deprecated(reason: "Usar `total.amount` (centavos). Se elimina 6 meses después de este release.")
}
```

**Proceso de deprecación:**

1. Agregar el campo nuevo sin romper el viejo (aditivo = no breaking).
2. Marcar el viejo con `@deprecated(reason: ...)` indicando reemplazo y plazo
   (fecha relativa al release, p. ej. "deploy + 6 meses").
3. Monitorear uso del campo deprecado (la mayoría de gateways/APMs lo registran).
4. Eliminar el campo solo cuando el uso llegue a cero o venza el plazo anunciado.

**Breaking changes a evitar:** eliminar campos sin deprecar, cambiar tipo de un campo,
volver nullable un campo `!`, renombrar enums en uso.

---

## Checklist GraphQL

- [ ] Schema diseñado desde el dominio (no espejo de tablas)
- [ ] Mutations con input/payload y errores de negocio tipados (`UserError`)
- [ ] DataLoader por request en toda relación N:1 / 1:N
- [ ] Paginación por cursor (connections) en toda lista
- [ ] Depth limit y query complexity configurados
- [ ] Introspección deshabilitada o restringida en producción
- [ ] Autorización a nivel de resolver/campo
- [ ] Campos viejos con `@deprecated` y plazo, nunca eliminados en caliente
