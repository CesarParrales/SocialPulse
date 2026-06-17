# Modelado y Normalización

Principios de modelado de datos: normalización, claves, relaciones, tipos de datos
y auditoría. Contenido movido desde SKILL.md para mantenerlo como referencia profunda.

---

## Normalización — Cuánto es Suficiente

```
1NF — Sin grupos repetidos, cada celda un valor atómico
2NF — Sin dependencias parciales (todos los atributos dependen de toda la PK)
3NF — Sin dependencias transitivas (A→B→C: B y C a tablas separadas)

Regla práctica: normalizar hasta 3NF por defecto.
Desnormalizar SOLO cuando hay evidencia de problema de performance.
No desnormalizar "por si acaso" — el costo en consistencia es real.
```

## Claves Primarias

```sql
-- UUID v7 (ordenable temporalmente) — recomendado para sistemas distribuidos
id UUID DEFAULT gen_random_uuid() PRIMARY KEY

-- ULID — alternativa legible y ordenable
id CHAR(26) DEFAULT ulid() PRIMARY KEY

-- Auto-increment — simple, predecible, no recomendado para APIs públicas
-- (expone volumen de datos: si el último orden es #1250, el competidor lo sabe)
id BIGSERIAL PRIMARY KEY

-- Clave natural compuesta — solo cuando tiene sentido semántico real
PRIMARY KEY (country_code, tax_id)
```

## Soft Delete — Cuándo Sí, Cuándo No

```sql
-- SÍ usar soft delete cuando:
-- - Hay auditoría regulatoria requerida
-- - Los registros tienen relaciones que deben mantenerse
-- - El negocio puede necesitar "restaurar" elementos

deleted_at TIMESTAMP WITH TIME ZONE NULL  -- NULL = activo

-- NO usar soft delete cuando:
-- - Los datos son verdaderamente efímeros (logs, eventos temporales)
-- - El volumen es enorme (contamina índices, queries más lentos)
-- - La semántica es "eliminar" de verdad (GDPR, datos de prueba)

-- Si usas soft delete: índice parcial obligatorio
CREATE INDEX idx_orders_active ON orders (user_id, created_at)
WHERE deleted_at IS NULL;
-- Solo indexa registros activos — no desperdicia espacio en los eliminados
```

---

## Relaciones — Patrones Comunes

### 1:N — El Pan de Cada Día

```sql
CREATE TABLE users (
    id          BIGSERIAL PRIMARY KEY,
    email       VARCHAR(255) UNIQUE NOT NULL,
    created_at  TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE TABLE orders (
    id          BIGSERIAL PRIMARY KEY,
    user_id     BIGINT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    -- ON DELETE RESTRICT: no borrar user si tiene orders (protección de integridad)
    -- ON DELETE CASCADE: borrar orders cuando se borra user
    -- ON DELETE SET NULL: dejar user_id = NULL (si la FK es nullable)
    total       NUMERIC(10, 2) NOT NULL CHECK (total >= 0),
    status      VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at  TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Índice en FK — siempre, sin excepción
CREATE INDEX idx_orders_user_id ON orders(user_id);
```

### N:M — Tabla Pivot con Valor Propio

```sql
-- MAL: tabla pivot sin valor propio (solo IDs)
CREATE TABLE user_roles (
    user_id BIGINT REFERENCES users(id),
    role_id BIGINT REFERENCES roles(id),
    PRIMARY KEY (user_id, role_id)
);

-- BIEN: tabla pivot con metadatos propios
CREATE TABLE user_roles (
    id          BIGSERIAL PRIMARY KEY,
    user_id     BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role_id     BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    assigned_by BIGINT REFERENCES users(id),
    assigned_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    expires_at  TIMESTAMP WITH TIME ZONE NULL,
    UNIQUE (user_id, role_id)  -- evitar duplicados
);

CREATE INDEX idx_user_roles_user ON user_roles(user_id);
CREATE INDEX idx_user_roles_role ON user_roles(role_id);
```

### Polimorfismo — Con Precaución

```sql
-- Relación polimórfica: un comentario puede pertenecer a Post, Product, Video
-- Opción A: FK polimórfica (Laravel style — flexible pero sin FK real)
CREATE TABLE comments (
    id              BIGSERIAL PRIMARY KEY,
    commentable_type VARCHAR(50) NOT NULL,  -- 'App\Models\Post'
    commentable_id   BIGINT NOT NULL,       -- ID del objeto
    body            TEXT NOT NULL,
    user_id         BIGINT REFERENCES users(id)
);
CREATE INDEX idx_comments_morphable ON comments(commentable_type, commentable_id);

-- Opción B: tabla por tipo (más íntegra, más tablas)
CREATE TABLE post_comments     (id, post_id REFERENCES posts(id), body, user_id);
CREATE TABLE product_comments  (id, product_id REFERENCES products(id), body, user_id);

-- Opción C: FK nullable por cada tipo (máxima integridad, verboso)
CREATE TABLE comments (
    id         BIGSERIAL PRIMARY KEY,
    post_id    BIGINT REFERENCES posts(id) NULL,
    product_id BIGINT REFERENCES products(id) NULL,
    body       TEXT NOT NULL,
    CHECK (
        (post_id IS NOT NULL)::INT +
        (product_id IS NOT NULL)::INT = 1  -- exactamente uno
    )
);
```

---

## Tipos de Datos — Elegir Bien Desde el Inicio

```sql
-- Dinero: NUNCA FLOAT. Siempre NUMERIC o INTEGER (centavos)
price       NUMERIC(10, 2)  -- hasta 99,999,999.99
price_cents INTEGER         -- precio en centavos (evita decimales)

-- Fechas: siempre con timezone
created_at  TIMESTAMP WITH TIME ZONE DEFAULT NOW()
-- TIMESTAMP sin zona = problemas con DST y múltiples regiones

-- Texto corto con límite
status      VARCHAR(20)     -- con límite explícito
email       VARCHAR(255)    -- estándar para emails
slug        VARCHAR(255)    -- para URLs

-- Texto largo sin límite
description TEXT            -- sin límite, PostgreSQL lo maneja bien
body        TEXT

-- Booleanos: siempre con DEFAULT
is_active   BOOLEAN NOT NULL DEFAULT TRUE
is_verified BOOLEAN NOT NULL DEFAULT FALSE

-- Enums: CHECK constraint o tipo ENUM
status VARCHAR(20) NOT NULL CHECK (status IN ('pending', 'active', 'cancelled'))
-- O tipo nativo PostgreSQL:
CREATE TYPE order_status AS ENUM ('pending', 'processing', 'shipped', 'delivered', 'cancelled');

-- JSON: JSONB en PostgreSQL (indexable, comprimido)
metadata    JSONB           -- no JSON — JSONB es binario, más rápido, indexable
settings    JSONB DEFAULT '{}'::jsonb

-- Arrays nativos PostgreSQL
tags        TEXT[]          -- array de strings, indexable con GIN
permissions INTEGER[]
```

---

## Índices en el Diseño Inicial

> Optimización profunda, EXPLAIN ANALYZE y queries lentas → `indexes-and-queries.md`

### Reglas Básicas

```sql
-- 1. Índice automático en PRIMARY KEY y UNIQUE
-- 2. Índice OBLIGATORIO en todas las FKs
-- 3. Índice en columnas de WHERE frecuentes
-- 4. Índice compuesto cuando se filtra por múltiples columnas juntas

-- Orden importa en índice compuesto:
-- columna más selectiva (menos duplicados) PRIMERO
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
-- Sirve para: WHERE user_id = ? AND status = ?
-- Sirve para: WHERE user_id = ?
-- NO sirve para: WHERE status = ? (sin user_id)
```

### Tipos de Índices PostgreSQL

```sql
-- B-Tree (default) — para =, <, >, BETWEEN, ORDER BY
CREATE INDEX idx_orders_created ON orders(created_at DESC);

-- Partial — solo indexa filas que cumplen condición (más pequeño, más rápido)
CREATE INDEX idx_orders_pending ON orders(created_at)
WHERE status = 'pending';

-- GIN — para arrays, JSONB, full-text search
CREATE INDEX idx_products_tags ON products USING GIN(tags);
CREATE INDEX idx_users_metadata ON users USING GIN(metadata);

-- Full-text search
CREATE INDEX idx_products_search ON products
USING GIN(to_tsvector('spanish', name || ' ' || description));

-- Query con full-text
SELECT * FROM products
WHERE to_tsvector('spanish', name || ' ' || description)
      @@ plainto_tsquery('spanish', 'zapato deportivo');
```

### Cuándo NO crear un índice

```
- Tablas pequeñas (< 10,000 filas): full scan es igual o más rápido
- Columnas con muy baja selectividad (booleanos, status con pocos valores)
  sin ser parte de un índice compuesto
- Tablas con escrituras muy intensas: cada índice = overhead en INSERT/UPDATE
- Columnas que raramente aparecen en WHERE
```

---

## Auditoría y Versionado de Datos

### Tabla de Auditoría Universal

```sql
CREATE TABLE audit_logs (
    id           BIGSERIAL PRIMARY KEY,
    user_id      BIGINT REFERENCES users(id) ON DELETE SET NULL,
    action       VARCHAR(20) NOT NULL,  -- 'created', 'updated', 'deleted'
    model_type   VARCHAR(100) NOT NULL, -- 'Order', 'User', 'Product'
    model_id     BIGINT NOT NULL,
    old_values   JSONB,                 -- estado anterior
    new_values   JSONB,                 -- estado nuevo
    ip_address   INET,
    user_agent   TEXT,
    created_at   TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_audit_model ON audit_logs(model_type, model_id);
CREATE INDEX idx_audit_user  ON audit_logs(user_id, created_at DESC);
CREATE INDEX idx_audit_date  ON audit_logs(created_at DESC);
-- Nota: esta tabla solo se escribe, nunca actualiza. Particionarla por fecha
-- cuando supere 10M registros.
```

### Event Sourcing Ligero (sin framework)

```sql
-- Guardar eventos de dominio como fuente de verdad
CREATE TABLE domain_events (
    id           UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    aggregate_id UUID NOT NULL,
    aggregate_type VARCHAR(100) NOT NULL,
    event_type   VARCHAR(100) NOT NULL,
    payload      JSONB NOT NULL,
    metadata     JSONB DEFAULT '{}',
    version      INTEGER NOT NULL,
    occurred_at  TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE (aggregate_id, version)  -- sin eventos duplicados por aggregate
);

CREATE INDEX idx_events_aggregate ON domain_events(aggregate_type, aggregate_id, version);
```
