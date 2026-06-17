# Plantilla ADR — Architecture Decision Record

Un ADR documenta una decisión arquitectónica no obvia: qué se decidió, por qué,
qué alternativas se descartaron y qué consecuencias se aceptan. Un archivo por decisión,
numerado secuencialmente: `docs/adr/0001-titulo-corto.md`.

---

## Plantilla

```markdown
# ADR-NNNN: [Título corto de la decisión]

- **Fecha:** YYYY-MM-DD
- **Estado:** Propuesto | Aceptado | Deprecado | Reemplazado por ADR-XXXX
- **Decisores:** [quiénes participaron]

## Contexto

[Qué problema o fuerza motivó esta decisión. Restricciones técnicas, de equipo,
de negocio o de plazo que aplican. 1-3 párrafos. Debe poder leerse en 2 años
y entender por qué esto era un problema.]

## Decisión

[Qué se decidió, en afirmativo: "Usaremos X para Y". Incluir el alcance:
a qué módulos/servicios aplica y a cuáles no.]

## Alternativas consideradas

### Alternativa 1: [nombre]
- Pros: ...
- Contras: ...
- Por qué se descartó: ...

### Alternativa 2: [nombre]
- Pros: ...
- Contras: ...
- Por qué se descartó: ...

## Consecuencias

### Positivas
- [Qué mejora o se simplifica]

### Negativas (aceptadas)
- [Qué costo, riesgo o deuda se asume conscientemente]

### Neutras
- [Cambios de proceso, herramientas o convenciones que implica]
```

---

## Reglas de uso

1. **Un ADR por decisión** — no mezclar "elegimos PostgreSQL" con "usamos hexagonal".
2. **Inmutables una vez aceptados** — si la decisión cambia, crear un ADR nuevo
   que reemplace al anterior y actualizar el `Estado` del viejo.
3. **Escribir cuando la decisión está fresca** — un ADR a posteriori pierde las
   alternativas reales que se evaluaron.
4. **Decisiones que merecen ADR:** elección de patrón arquitectónico, motor de BD,
   estrategia de auth, monolito vs microservicios, framework principal, estrategia
   de versionado de API, herramienta de queues.
5. **Decisiones que NO merecen ADR:** convenciones de estilo, nombres de carpetas,
   detalles reversibles en horas.

## Ejemplo mínimo

```markdown
# ADR-0003: Monolito modular en lugar de microservicios

- **Fecha:** 2026-06-01
- **Estado:** Aceptado
- **Decisores:** Tech lead + equipo backend

## Contexto
Equipo de 4 devs, MVP validándose, presión del cliente por "microservicios".
Sin experiencia previa en distributed systems ni presupuesto de DevOps dedicado.

## Decisión
Monolito modular: un deploy, módulos con límites explícitos por dominio,
comunicación entre módulos solo por interfaces.

## Alternativas consideradas
### Microservicios desde el día 1
- Pros: escala independiente, deploys aislados.
- Contras: latencia de red, consistencia eventual, tracing distribuido, CI/CD por servicio.
- Por qué se descartó: equipo < 8 devs y dominio aún no entendido — los límites cambiarían.

## Consecuencias
### Positivas
- Deploy simple, transacciones ACID nativas, onboarding rápido.
### Negativas (aceptadas)
- Requiere disciplina para no acoplar módulos; revisión en cada PR.
### Neutras
- Si un módulo necesita escala diferenciada, se extrae como servicio (gradual).
```
