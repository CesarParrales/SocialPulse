---
name: web-architecture
description: >
  Guía la arquitectura de sistemas web y aplicaciones: patrones (MVC, Service Layer,
  hexagonal, DDD, CQRS, event-driven), decisiones estructurales, diseño de capas,
  monolito vs microservicios y escalabilidad. Usar cuando el usuario pregunte cómo
  estructurar un proyecto, qué patrón de arquitectura usar, cómo separar
  responsabilidades, o mencione: diseño de capas, arquitectura hexagonal, DDD, CQRS,
  event-driven, microservicios vs monolito, escalabilidad, o diga "cómo organizo el
  código", "cómo estructuro el proyecto", "qué arquitectura uso", "cómo escalo esto",
  "cómo separo la lógica", "el proyecto está creciendo y se está descontrolando", o
  cualquier variante. También es útil cuando el usuario esté tomando decisiones
  estructurales con impacto a largo plazo y convenga evaluar trade-offs arquitectónicos.
---

# Web Architecture Skill

Guía de patrones arquitectónicos para sistemas web: cuándo usar cada uno, trade-offs
reales, y cómo evolucionar la arquitectura conforme crece el proyecto.

**Patrones detallados (con código) → `references/patterns.md`**
**Arquitectura por tipo de proyecto → `references/by-project-type.md`**
**Anti-patrones comunes → `references/anti-patterns.md`**
**Plantilla ADR → `references/adr-template.md`**

---

## Principio Central

> La mejor arquitectura es la más simple que resuelve el problema actual
> y permite cambiar cuando el problema evolucione.

Over-engineering mata proyectos igual que under-engineering.
La pregunta correcta no es "¿qué arquitectura es la mejor?"
sino "¿qué arquitectura necesita este proyecto en este momento?"

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — ADRs existentes, patrón arquitectónico acordado.
2. `docs/adr/` o ruta que indique project-memory.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** toda decisión estructural → ADR + entrada breve en project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory y ADRs previos; no contradecir decisiones sin ADR de cambio.
1. **Levantar contexto** — responder las 6 preguntas del cuestionario de abajo
   (equipo, etapa, dominio, usuarios, deploys, experiencia). Si falta información,
   aplicar `## Defaults si falta contexto` y declararlo.
2. **Clasificar el proyecto** — leer `references/by-project-type.md` y ubicar el tipo
   de proyecto (SaaS, e-commerce, API interna, etc.) para partir de su arquitectura base.
3. **Preseleccionar patrón** — aplicar el Árbol de Decisión de este archivo.
   Para detalles de implementación del patrón elegido, leer `references/patterns.md`.
4. **Contrastar contra anti-patrones** — leer `references/anti-patterns.md` y verificar
   que la propuesta no reproduce ninguno (God Object, microservicios prematuros, etc.).
5. **Documentar la decisión como ADR** — usar `references/adr-template.md`.
   Gate: `ls docs/adr/*.md` muestra el ADR nuevo y `wc -l docs/adr/<archivo>.md`
   confirma que tiene contenido (> 20 líneas).
6. **Validar la regla de dependencias** (si ya hay código) — gate: ejecutar
   `grep -rn "Illuminate\|Eloquent\|Infrastructure" app/Domain/ src/domain/ 2>/dev/null`
   y verificar que devuelve vacío (el dominio no importa infraestructura).
7. **Entregar la recomendación** con el formato de `## Entregable`.
8. **Validación y cierre** — ejecutar `## Validación`; ADR en repo; project-memory actualizado.

### Cuestionario de contexto

```
1. ¿Cuántos devs trabajan en el proyecto? (1-3 / 4-10 / 10+)
2. ¿En qué etapa está? (MVP / crecimiento / escala / legacy)
3. ¿Cuál es el dominio? (simple CRUD / lógica compleja / múltiples dominios)
4. ¿Cuántos usuarios esperados? (< 10K / 10K-1M / > 1M)
5. ¿Hay requisitos de independencia de deploys entre módulos?
6. ¿El equipo tiene experiencia con arquitecturas complejas?
```

---

## Defaults si falta contexto

Si el usuario no aporta datos, asumir y **declarar** estos supuestos en la respuesta
(máximo 1 pregunta al usuario, solo si es bloqueante):

| Falta | Default asumido |
|-------|-----------------|
| Tamaño de equipo / etapa | Equipo 1-3 devs + MVP → **MVC + Service Layer** |
| ¿Microservicios? | **No** sin > 2 equipos independientes y límites de dominio estables |
| Motor de BD | PostgreSQL |
| Dominio | CRUD con algo de lógica → Service Layer basta, sin DDD |
| Camino de evolución | Monolito modular antes que extracción de servicios |
| Comunicación async | Events + queue del framework, no message broker dedicado |

---

## Capas Fundamentales (Universal)

Todo sistema web, sin importar el patrón, tiene estas responsabilidades.
La arquitectura define cómo se organizan y comunican entre sí.

```
┌─────────────────────────────────────┐
│         PRESENTACIÓN                │  HTTP, WebSocket, CLI, Queue consumers
│   Controllers / Resolvers / Handlers│  Solo traducción: request → command/query
├─────────────────────────────────────┤
│         APLICACIÓN                  │  Orquestación de casos de uso
│   Use Cases / Services / Commands   │  Sin lógica de negocio. Sin SQL directo.
├─────────────────────────────────────┤
│            DOMINIO                  │  Lógica de negocio pura
│   Entities / Value Objects / Rules  │  Sin dependencias externas. Testeable solo.
├─────────────────────────────────────┤
│        INFRAESTRUCTURA              │  Implementaciones concretas
│   DB / APIs externas / Cache / Email│  Intercambiable. Depende del dominio, no al revés.
└─────────────────────────────────────┘
```

**Regla de dependencia:** las capas externas dependen de las internas. NUNCA al revés.
El dominio no sabe que existe Laravel, Express, MySQL, o Redis.

---

## Patrones Principales

> Implementaciones de referencia con código → `references/patterns.md`

### 1. MVC — Model View Controller

**Complejidad:** Baja | **Cuándo:** CRUD simple, equipos pequeños, MVP

```
Request → Router → Controller → Model → View/Response
```

- Controller: recibe request, llama modelo, devuelve respuesta
- Model: acceso a datos + algo de lógica de negocio (el problema)
- View: presentación

**Problema real de MVC puro:** el Model se convierte en God Object.
Con el tiempo acumula validaciones, reglas de negocio, queries complejas, y eventos.
Resultado: `UserModel` de 2,000 líneas que nadie entiende ni toca con confianza.

**Cuándo escalar fuera de MVC:** cuando los Controllers superan 200 líneas
o los Models tienen lógica que no es acceso a datos.

### 2. MVC + Service Layer

**Complejidad:** Media-Baja | **Cuándo:** La mayoría de proyectos web reales

```
Request → Controller → Service → Repository → Model
                    ↓
               Response/DTO
```

- Controller: delgado. Solo valida input y llama Service.
- Service: lógica de negocio. Orquesta operaciones.
- Repository: acceso a datos. Abstrae el ORM.
- Model: solo estructura de datos y relaciones.

**El patrón más práctico para proyectos Laravel/Node medianos.**
Separa responsabilidades sin sobre-ingeniería.
Ejemplo mínimo Controller → Service → Repository en `references/patterns.md`.

### 3. Arquitectura Hexagonal (Ports & Adapters)

**Complejidad:** Media-Alta | **Cuándo:** Lógica de negocio compleja, múltiples interfaces

```
        HTTP  |  CLI  |  Queue
              ↓
    ┌─── ADAPTADORES (entrada) ───┐
    │                             │
    │   ┌─── APLICACIÓN ──────┐  │
    │   │  Use Cases / Ports  │  │
    │   │  ┌─── DOMINIO ───┐  │  │
    │   │  │  Entities     │  │  │
    │   │  │  Value Obj    │  │  │
    │   │  │  Domain Svcs  │  │  │
    │   │  └───────────────┘  │  │
    │   └─────────────────────┘  │
    │                             │
    └─── ADAPTADORES (salida) ───┘
              ↓
    DB  |  Email  |  APIs externas
```

**Concepto clave:** el dominio define interfaces (Ports).
La infraestructura implementa esas interfaces (Adapters).
El dominio nunca importa código de infraestructura.

**Beneficio real:** cambiar MySQL por PostgreSQL, Stripe por PayPal, o agregar una CLI
sin tocar lógica de negocio. Ejemplo puerto/adaptador en `references/patterns.md`.

### 4. DDD — Domain Driven Design

**Complejidad:** Alta | **Cuándo:** Dominio complejo con múltiples bounded contexts

DDD no es una arquitectura — es una filosofía de diseño.
Se implementa generalmente con Hexagonal o Clean Architecture.

**Conceptos clave:**

```
Bounded Context:  límite explícito donde un modelo tiene significado consistente
                  Ej: "Order" en Ventas ≠ "Order" en Logística

Aggregate:        cluster de objetos tratados como unidad para cambios de datos
                  Ej: Order + OrderItems + ShippingAddress

Entity:           objeto con identidad única que persiste en el tiempo
                  Ej: User (tiene ID, cambia estado, pero sigue siendo el mismo)

Value Object:     objeto sin identidad, definido por sus atributos, inmutable
                  Ej: Money(100, 'USD'), Email('user@example.com')

Domain Event:     algo que ocurrió en el dominio con significado de negocio
                  Ej: OrderPlaced, PaymentFailed, UserRegistered

Repository:       abstracción de persistencia por aggregate root
Domain Service:   lógica que no pertenece a una entidad específica
```

**Cuándo aplica DDD real:** cuando el negocio tiene reglas complejas que cambian,
múltiples equipos trabajan en dominios distintos, y el lenguaje del código
debe coincidir con el lenguaje que usa el cliente (Ubiquitous Language).

**Cuándo NO usar DDD:** CRUDs simples, MVP, proyectos < 6 meses, equipo sin experiencia previa.
El costo de entrada es alto. Aplicarlo en proyectos simples = over-engineering puro.

### 5. CQRS — Command Query Responsibility Segregation

**Complejidad:** Media-Alta | **Cuándo:** Lecturas y escrituras con necesidades muy distintas

```
         Writes                    Reads
           ↓                         ↓
    ┌─── Commands ───┐      ┌─── Queries ───┐
    │  CreateOrder   │      │  GetOrders    │
    │  UpdateUser    │      │  OrderSummary │
    └────────────────┘      └───────────────┘
           ↓                         ↓
    ┌─── Write Model ┐      ┌─── Read Model ─┐
    │  Validaciones  │      │  Proyecciones  │
    │  Eventos       │      │  Denormalized  │
    │  Consistencia  │      │  Fast queries  │
    └────────────────┘      └────────────────┘
           ↓                         ↓
    ┌─── Write DB ───┐      ┌─── Read DB ────┐
    │  Normalizado   │      │  Optimizado    │
    │  PostgreSQL    │      │  PostgreSQL    │
    └────────────────┘      │  views/Redis   │
                            └────────────────┘
```

**Beneficio real:** las escrituras garantizan consistencia y disparan eventos.
Las lecturas son queries optimizadas sin pasar por lógica de negocio.
Reportes y dashboards nunca bloquean las escrituras transaccionales.

**CQRS simple (sin Event Sourcing):** misma BD, diferentes modelos de lectura/escritura.
**CQRS + Event Sourcing:** estado derivado de eventos. Complejidad máxima. Raramente necesario.

### 6. Event-Driven Architecture

**Complejidad:** Media-Alta | **Cuándo:** Sistemas desacoplados, procesos async, integraciones

```
Producer → Event Bus → Consumer A
                    → Consumer B
                    → Consumer C
```

**Tipos de eventos:**

```
Domain Events:      algo pasó en el dominio
                    Ej: OrderPlaced → dispara: send email, update inventory, notify logistics

Integration Events: comunicación entre servicios/bounded contexts
                    Ej: UserRegistered (en Auth) → (en Marketing) send welcome campaign

Commands:           intención de hacer algo (puede rechazarse)
                    Ej: PlaceOrder → puede fallar si no hay stock
```

Ejemplo de eventos + listeners en queue (Laravel) en `references/patterns.md`.

### 7. Monolito vs Microservicios

**La decisión más malentendida en arquitectura de software.**

#### Monolito Bien Estructurado

```
✅ Cuándo usar:
   - Equipo < 15 devs
   - Dominio no completamente entendido aún
   - MVP / producto en validación
   - Sin necesidad de deploys independientes por módulo
   - Presupuesto de infra limitado

✅ Ventajas:
   - Deploy simple
   - Sin latencia de red entre módulos
   - Transacciones ACID nativas
   - Debugging directo
   - Onboarding rápido

⚠️ Riesgos si no se estructura:
   - Big Ball of Mud (código spaghetti a escala)
   - Módulos que se acoplan sin control
   - Tests lentos por dependencias cruzadas
```

#### Monolito Modular (el sweet spot)

```
Un solo deploy, múltiples módulos con límites explícitos.
Cada módulo: su carpeta, sus interfaces públicas, sus tests.
Comunicación entre módulos: solo por interfaces, nunca acceso directo a BD ajena.

Beneficio: disciplina de microservicios sin su complejidad operacional.
Puede extraerse a microservicio real cuando sea necesario — y solo cuando sea necesario.
```

#### Microservicios

```
✅ Cuándo REALMENTE aplica:
   - Equipos > 15 devs trabajando en dominios independientes
   - Necesidad probada de escala diferenciada por servicio
   - Dominio completamente entendido (los límites son estables)
   - Equipo con experiencia en distributed systems
   - Presupuesto de infra y DevOps para soportarlo

❌ Cuándo NO aplica (aunque el cliente lo pida):
   - MVP o producto sin validar
   - Equipo < 8 devs
   - Primer proyecto del equipo
   - "Porque Netflix lo usa"

Costo real de microservicios:
   - Latencia de red entre servicios
   - Consistencia eventual (sin transacciones ACID entre servicios)
   - Distributed tracing obligatorio
   - Service discovery, load balancing, circuit breakers
   - CI/CD por servicio
   - Contratos de API entre equipos
   - Debugging distribuido (exponencialmente más difícil)
```

---

## Árbol de Decisión de Arquitectura

```
¿Equipo > 10 devs con dominios independientes?
├── SÍ → ¿Dominio bien entendido y límites estables?
│         ├── SÍ → Microservicios o Monolito Modular + extracción gradual
│         └── NO → Monolito Modular (extraer después)
│
└── NO → ¿Lógica de negocio compleja con muchas reglas?
          ├── SÍ → ¿Equipo con experiencia en DDD?
          │         ├── SÍ → Hexagonal + DDD
          │         └── NO → MVC + Service Layer (escalar a Hexagonal gradual)
          │
          └── NO → ¿Es principalmente CRUD?
                    ├── SÍ → MVC + Service Layer (no over-engineer)
                    └── NO → MVC + Service Layer + Event-Driven para async
```

---

## Evolución de Arquitectura

La arquitectura debe crecer con el proyecto. No diseñar para el año 5 desde el día 1.

```
Día 1 — MVP:
  MVC + Service Layer básico
  Un repo, un deploy, PostgreSQL

Mes 3-6 — Crecimiento:
  Service Layer más definido
  Repositories para abstraer ORM
  Events para procesos async
  Cache layer

Mes 6-12 — Escala:
  Separar módulos con límites explícitos
  CQRS para reads pesadas
  Queue workers dedicados
  Read replicas en BD

Año 1+ — Madurez:
  Evaluar extracción de servicios por dominio
  Solo si hay razón técnica, no filosófica
  Monolito modular bien mantenido > microservicios prematuros
```

---

## Reglas de Oro

1. **Simple primero** — agregar complejidad solo cuando el problema lo requiere, no antes
2. **Dependencias hacia adentro** — dominio no depende de infraestructura, nunca
3. **Módulos por dominio, no por tipo** — `/orders/` no `/controllers/` `/models/` `/services/`
4. **Interfaces en las fronteras** — todo lo que puede cambiar (BD, email, pagos) detrás de interfaz
5. **Lógica de negocio testeable sola** — si necesitas levantar el framework para testear negocio, algo está mal
6. **Eventos para desacoplar** — no llamadas directas entre módulos no relacionados
7. **Un aggregate, una transacción** — no modificar múltiples aggregates en una sola transacción
8. **Documentar las decisiones** — ADR por cada decisión arquitectónica no obvia (ver `references/adr-template.md`)

---

## Ejemplo input → output

**Input:** "SocialPulse crece: ¿separamos ingesta en microservicio?"

**Output:** ADR recomiando monolito modular (módulo `Ingestion` + colas Horizon) — trade-offs documentados; riesgo operacional de microservicios prematuros; siguiente paso: `docs/adr/0007-ingestion-stays-monolith.md` + verificar `Modules/Ingestion/` aislado por interfaces.

---

## Validación

| Gate | Acción | Criterio |
|------|--------|----------|
| ADR | `ls docs/adr/*.md` + revisión contenido | ADR nuevo >20 líneas, status claro |
| Dependencias dominio | `grep -rn "Illuminate\|Infrastructure" app/Domain/ src/domain/` | vacío (si hay capa dominio) |
| Anti-patrones | contrastar con `references/anti-patterns.md` | ninguno aplicable sin mitigación |
| Entregable | formato `## Entregable` | trade-offs + siguiente paso verificable |

---

## Entregable

Toda recomendación de arquitectura debe cerrar con este formato:

```markdown
## Decisión recomendada
[Patrón/arquitectura elegida y a qué alcance aplica]

## Trade-offs
- Ganamos: ...
- Perdemos / aceptamos: ...

## Riesgos (top 3)
1. [Riesgo] → mitigación: ...
2. [Riesgo] → mitigación: ...
3. [Riesgo] → mitigación: ...

## Siguiente paso verificable
[Acción concreta + cómo verificar que se cumplió, p. ej. "crear ADR-0001 y
estructura de carpetas del módulo orders; verificar con `ls docs/adr/ app/Modules/Orders/`"]
```

---

## Skills relacionadas

- `database-design` — modelado de datos y elección de motor de BD
- `api-design` — diseño de contratos REST/GraphQL entre capas o servicios
- `laravel-backend` — implementación de estos patrones en Laravel
- `node-backend` — implementación de estos patrones en NestJS/Node
- `devops-base` — infraestructura, CI/CD y deploys de la arquitectura elegida
- `testing-strategy` — estrategia de tests por capa
- `performance-web` — optimización cuando la arquitectura ya está en producción

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
