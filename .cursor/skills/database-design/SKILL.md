---
name: database-design
description: >
  Guía el diseño de bases de datos relacionales y NoSQL: modelado de entidades,
  migraciones, índices, estrategias de escala y optimización de queries. Usar cuando
  el usuario mencione: diseñar una base de datos, modelar entidades, crear migraciones,
  optimizar queries, índices lentos, elegir entre SQL y NoSQL, estrategia de caché,
  normalización, relaciones entre tablas, sharding, particionamiento, backup, o cuando
  diga "la BD está lenta", "cómo modelo esto", "qué BD uso", "tengo queries lentas",
  "cómo escalo la base de datos", "cómo estructuro las tablas", o cualquier variante.
  También es útil al revisar un modelo de datos existente con señales de mal diseño,
  ofreciendo la corrección temprana como sugerencia.
---

# Database Design Skill

Guía de diseño, optimización y escalado de bases de datos para sistemas web.
Este archivo contiene el protocolo, los árboles de decisión y el checklist;
el detalle profundo vive en las references.

**Modelado, relaciones y tipos de datos → `references/modeling.md`**
**Índices y optimización de queries → `references/indexes-and-queries.md`**
**Estrategias de escala → `references/scaling.md`**
**Migraciones y Laravel → `references/migrations-laravel.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — motor BD, convenciones de migraciones, gates.
2. Fuentes que indique project-memory (`context.md`, ADRs de datos).
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** decisiones de modelo (PK, soft delete, índices clave) → project-memory; gaps → `LEARNINGS.md`; diagrama/DDL → `docs/` o migraciones en repo.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory (motor BD, comandos migrate/test del proyecto).
1. **Definir entidades y relaciones** — responder el cuestionario de diseño de abajo
   por cada entidad. Leer `references/modeling.md` para normalización, claves,
   relaciones (1:N, N:M, polimorfismo) y tipos de datos. Si falta contexto,
   aplicar `## Defaults si falta contexto` y declararlo.
2. **Elegir motor** — aplicar los árboles de decisión de este archivo
   (SQL vs NoSQL, PostgreSQL vs MySQL).
3. **Escribir el DDL** — tablas, FKs con `ON DELETE` explícito, constraints e índices
   iniciales según `references/modeling.md`. Gate: el DDL ejecuta sin errores en una
   BD limpia (`psql -f schema.sql` o equivalente termina con exit code 0).
4. **Crear las migraciones** — si el stack es Laravel, seguir
   `references/migrations-laravel.md` (incluye zero-downtime y backfill por lotes).
   Gate: ejecutar `php artisan migrate --pretend` (o `migrate` en local) y verificar
   que corre completo; `php artisan migrate:rollback` funciona o hay plan de rollback documentado.
5. **Validar performance con datos representativos** — ejecutar `EXPLAIN ANALYZE`
   sobre las **top 5 queries** esperadas del sistema (leer
   `references/indexes-and-queries.md` para interpretar el plan). Gate: ninguna query
   crítica hace `Seq Scan` sobre tablas grandes; tiempos dentro del presupuesto.
6. **Planear escala si aplica** — si el volumen esperado supera ~10M filas o hay
   requisitos de alta concurrencia, leer `references/scaling.md` (réplicas,
   particionamiento, sharding).
7. **Pasar el checklist de diseño** (al final de este archivo), ejecutar `## Validación`
   y entregar con el formato de `## Entregable`; registrar decisiones en project-memory.

### Cuestionario de diseño (por entidad)

```
1. ¿Qué entidad representa? (una sola responsabilidad)
2. ¿Cuál es su clave primaria? (natural vs. surrogate)
3. ¿Qué relaciones tiene? (1:1 / 1:N / N:M)
4. ¿Qué queries se van a ejecutar sobre ella? (define índices)
5. ¿Cuánto volumen de datos espero? (define estrategia de partición)
6. ¿Qué datos son inmutables? (define estrategia de auditoría)
7. ¿Necesita soft delete? ¿por qué?
```

---

## Defaults si falta contexto

Asumir y **declarar** estos supuestos (máximo 1 pregunta al usuario, solo si es bloqueante):

| Falta | Default asumido |
|-------|-----------------|
| Motor de BD | **Proyecto web nuevo → PostgreSQL**, salvo restricción explícita (legacy, hosting, equipo) |
| Clave primaria | `BIGSERIAL` interno + UUID/ULID expuesto en API pública |
| Dinero | `INTEGER` en centavos o `NUMERIC(10,2)` — nunca FLOAT |
| Fechas | `TIMESTAMP WITH TIME ZONE`, UTC |
| Normalización | 3NF; desnormalizar solo con evidencia de performance |
| Soft delete | No, salvo requisito de auditoría o restauración |
| Volumen | < 1M filas por tabla el primer año (sin particionamiento inicial) |

---

## Selección de Motor de BD

### Árbol de Decisión

```
¿Los datos tienen estructura fija y relaciones entre entidades?
├── SÍ → ¿Necesitas transacciones ACID entre múltiples entidades?
│         ├── SÍ → PostgreSQL (primera opción siempre)
│         └── NO → PostgreSQL igual (MySQL si legacy/equipo PHP puro)
│
└── NO → ¿Qué tipo de datos?
          ├── Documentos JSON flexibles → MongoDB / PostgreSQL JSONB
          ├── Clave-valor / caché / sesiones → Redis
          ├── Series temporales / métricas → InfluxDB / TimescaleDB
          ├── Grafos / relaciones complejas → Neo4j
          └── Búsqueda full-text → Elasticsearch / Meilisearch
```

### PostgreSQL vs MySQL — La Decisión Real

| Criterio | PostgreSQL | MySQL |
|----------|-----------|-------|
| Transacciones complejas | ✅ Superior | Bueno |
| JSON nativo (JSONB) | ✅ Excelente | Limitado |
| Full-text search | ✅ Bueno | Limitado |
| Extensiones (PostGIS, etc.) | ✅ Ecosistema enorme | Limitado |
| Replicación | Bueno | ✅ Más simple |
| Hosting managed | AWS RDS, Supabase, Neon | AWS RDS, PlanetScale |
| Soporte Laravel/Eloquent | ✅ Completo | ✅ Completo |
| **Recomendación** | **Proyectos nuevos** | Solo si hay razón específica |

### Árbol de Decisión — ¿SQL o NoSQL?

```
¿Los datos tienen esquema predecible y relaciones entre entidades?
├── SÍ → PostgreSQL
│
└── NO o PARCIAL →
      ¿Qué patrón de acceso domina?
      ├── Leer/escribir por clave única (millones de ops/seg) → Redis
      ├── Documentos semi-estructurados con queries flexibles → MongoDB / PostgreSQL JSONB
      ├── Datos de series temporales (métricas, IoT, logs) → TimescaleDB / InfluxDB
      ├── Búsqueda full-text compleja → Elasticsearch / Meilisearch
      └── Relaciones tipo grafo (red social, recomendaciones) → Neo4j

REGLA: PostgreSQL con JSONB resuelve el 80% de los casos "NoSQL".
       Usar un motor especializado solo cuando hay evidencia de necesidad real.
```

---

## Mapa de references

| Necesitas | Leer |
|-----------|------|
| Normalización, claves primarias, soft delete | `references/modeling.md` |
| Relaciones 1:N, N:M, polimorfismo | `references/modeling.md` |
| Tipos de datos y constraints | `references/modeling.md` |
| Auditoría y event sourcing ligero | `references/modeling.md` |
| EXPLAIN ANALYZE, queries lentas, caché, read replicas | `references/indexes-and-queries.md` |
| Particionamiento, sharding, alta concurrencia | `references/scaling.md` |
| Migraciones Laravel, zero-downtime, seeders, factories | `references/migrations-laravel.md` |

---

## Checklist de Diseño

Antes de poner una tabla en producción:

- [ ] Cada tabla tiene exactamente una responsabilidad
- [ ] Tipos de datos correctos (sin floats para dinero, sin VARCHAR sin límite innecesario)
- [ ] FK con ON DELETE definido explícitamente (no asumir default)
- [ ] Índice en cada FK
- [ ] Índices en columnas de WHERE frecuentes (validado con EXPLAIN ANALYZE)
- [ ] Constraints de CHECK para valores con dominio finito
- [ ] `created_at` y `updated_at` en toda tabla que cambia de estado
- [ ] Soft delete solo donde tiene justificación real
- [ ] Campos sensibles (passwords, tokens) nunca en texto plano
- [ ] Migración reversible (o con plan de rollback documentado)
- [ ] Datos de prueba representativos para validar performance antes de producción

---

## Ejemplo input → output

**Input:** "Modelar invitaciones a workspace: email, rol, expiración, estado."

**Output:** tabla `workspace_invitations` con ULID PK, FK a `workspaces`, índice único `(workspace_id, email)` pendiente, `expires_at`, enum status; migración Laravel reversible; EXPLAIN de listado por workspace sin Seq Scan. Gate: `php artisan migrate --pretend` exit 0.

---

## Validación

| Gate | Comando | Criterio |
|------|---------|----------|
| Migraciones | `php artisan migrate --pretend` / `migrate` local | exit 0 |
| Rollback | `php artisan migrate:rollback --step=1` (o plan documentado) | sin error |
| DDL directo | `psql -f schema.sql` (si aplica) | exit 0 |
| Performance | `EXPLAIN ANALYZE` top 5 queries | sin Seq Scan en tablas grandes |
| Checklist diseño | sección al final de este SKILL.md | ítems aplicables ✓ |

---

## Entregable

Todo diseño de BD debe cerrar con este formato:

```markdown
## Modelo de datos
[Diagrama entidad-relación en Mermaid o lista de tablas con sus relaciones]

## DDL / Migraciones
[Archivos creados y orden de ejecución]

## Decisiones clave
- Motor: [PostgreSQL/...] porque ...
- PK: [estrategia] porque ...
- [Otras decisiones no obvias con su justificación]

## Validación de performance
| Query (top 5) | Plan (EXPLAIN ANALYZE) | Tiempo | OK |
|---------------|------------------------|--------|----|

## Pendientes / riesgos
[Particionamiento futuro, queries a vigilar, deuda asumida]
```

---

## Skills relacionadas

- `web-architecture` — el modelo de datos sigue a la arquitectura, no al revés
- `laravel-backend` — Eloquent, modelos y queries sobre este diseño
- `node-backend` — Prisma y acceso a datos desde Node
- `api-design` — cómo exponer estas entidades en la API (paginación, IDs públicos)
- `performance-web` — cuando la lentitud no es solo de BD
- `security-checklist` — datos sensibles, cifrado y acceso

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
