# Phase 04 · Database Design

## Purpose
Design a data model that is correct, performant, and maintainable. Most production performance issues trace back to poor database design. Fix it here, not in production at 2 AM.

---

## 4.1 Database Design Principles

1. **Normalize first, denormalize with evidence** — Start at 3NF. Denormalize only when you have profiler data showing a bottleneck.
2. **Name things explicitly** — `user_id`, not `id` everywhere. `created_at`, `updated_at` on every table.
3. **Soft deletes with caution** — `deleted_at` columns add complexity to every query. Use them only when audit trails are required.
4. **Timestamps in UTC always** — Store in UTC, convert at display layer.
5. **Never store computed values** — Unless performance demands it, and document when you do.
6. **Foreign keys are not optional** — They exist. Use them. They prevent orphan data.
7. **Design for queries you know, not queries you imagine** — Query-driven schema design beats theoretical normalization.

---

## 4.2 Standard Table Conventions

```sql
-- Every table should have at minimum:
CREATE TABLE entities (
  id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),  -- UUID preferred over serial in distributed systems
  -- ... domain columns ...
  created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  deleted_at  TIMESTAMPTZ  -- Only if soft-delete is a requirement
);

-- Auto-update updated_at (PostgreSQL)
CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN NEW.updated_at = NOW(); RETURN NEW; END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_set_updated_at
  BEFORE UPDATE ON entities
  FOR EACH ROW EXECUTE FUNCTION set_updated_at();
```

---

## 4.3 Indexing Strategy

```
Rule 1: Index every foreign key column.
Rule 2: Index columns used in WHERE clauses (especially high-cardinality).
Rule 3: Composite indexes — column order matters (most selective first).
Rule 4: Index columns used in ORDER BY and GROUP BY on large tables.
Rule 5: Don't over-index writes. Each index slows down INSERT/UPDATE/DELETE.
Rule 6: Use partial indexes for common filtered queries.
Rule 7: EXPLAIN ANALYZE before assuming you need an index.
```

```sql
-- Example: partial index for active users only
CREATE INDEX idx_users_active_email ON users(email) WHERE deleted_at IS NULL;

-- Example: composite index for common query pattern
CREATE INDEX idx_orders_user_status ON orders(user_id, status, created_at DESC);
```

---

## 4.4 ERD Documentation Template

For every entity, document:

```markdown
## [Entity Name]

**Purpose**: [One line description]

| Column | Type | Nullable | Default | Notes |
|--------|------|----------|---------|-------|
| id | UUID | NO | gen_random_uuid() | PK |
| ... | ... | ... | ... | ... |
| created_at | TIMESTAMPTZ | NO | NOW() | |
| updated_at | TIMESTAMPTZ | NO | NOW() | Auto-updated |

**Indexes**: [List non-PK indexes]
**Relationships**: [FK references]
**Business Rules**: [Constraints enforced at DB level]
```

---

## 4.5 Migration Strategy

```
□ Use migration framework: Flyway, Liquibase, Prisma Migrate, Alembic, Rails Migrations
□ Migrations are version-controlled in the repo
□ Never edit a migration that has been applied to staging/prod
□ All schema changes go through migrations — no manual DDL in production
□ Test rollback scripts before deploying
□ Zero-downtime migrations for production:
   - Add columns as nullable first
   - Backfill data
   - Add constraints / NOT NULL in subsequent migration
   - Never DROP a column in the same migration as removing code that uses it
```

### Zero-Downtime Column Removal (3-Deploy Pattern)
```
Deploy 1: Code stops writing to old_column, starts writing to new_column
Deploy 2: Backfill data. Remove code reading old_column.
Deploy 3: DROP COLUMN old_column (safe — no code references it)
```

---

## 4.6 Multi-Tenancy Patterns

| Pattern | How | Pros | Cons |
|---------|-----|------|------|
| **Row-level** | `tenant_id` column everywhere | Simple, one DB | Risk of data leaks if query misses filter |
| **Schema-per-tenant** | Separate Postgres schema | Better isolation | Harder migrations |
| **DB-per-tenant** | Separate database | Full isolation | High ops overhead, expensive |

> Row-level is valid for most SaaS. Use Postgres RLS (Row Level Security) to enforce tenant isolation at DB level — don't rely solely on application logic.

---

## 4.7 Caching Strategy

```
L1: In-process cache (memory) — microseconds — use for static config
L2: Redis / Memcached — milliseconds — use for session, hot data, rate limits
L3: CDN — tens of milliseconds — use for public, static content

Cache invalidation strategy must be decided before caching is implemented.
Options:
  - TTL-based: Simple, eventual consistency, stale reads possible
  - Write-through: Update cache on every write, no stale reads, higher write latency
  - Cache-aside: App manages cache, most flexible, most code
  - Event-driven invalidation: On data change, publish event to invalidate
```

---

## 4.8 Database Security Checklist

```
□ Application user has LEAST PRIVILEGE (SELECT/INSERT/UPDATE/DELETE only, no DDL)
□ Migrations run with a separate privileged user, not the app user
□ Database not exposed to public internet (private subnet only)
□ Connections encrypted with TLS
□ Connection string in secrets manager, not in env files committed to git
□ Connection pooling configured (PgBouncer for PostgreSQL at scale)
□ Query timeouts configured to prevent runaway queries
□ Automated backups with tested restore procedure
□ Point-in-time recovery (PITR) enabled for production
```
