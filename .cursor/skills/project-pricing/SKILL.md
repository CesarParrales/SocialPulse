---
name: project-pricing
description: >
  Guía el pricing de proyectos de desarrollo: cómo cobrar, cómo estimar con
  margen real, modelos de precio (hora, proyecto, retainer), cómo evitar
  proyectos que pierden dinero. Usar cuando el usuario necesite estimar un
  proyecto, definir su tarifa, estructurar un modelo de cobro, entender si un
  proyecto es rentable, o cuando diga "cuánto cobro por esto", "cómo estimo el
  precio", "cómo sé si estoy cobrando bien", "el proyecto perdió dinero",
  "cómo presento el precio al cliente", "qué modelo de cobro usar", o cualquier
  variante sobre la economía del proyecto.
---

# Project Pricing Skill

La mayoría de los estudios de desarrollo no tienen problema con la calidad técnica.
El problema es que no saben cuánto cobrar — y cuando lo saben,
no saben cómo comunicarlo.

Cobrar poco = quemar al equipo y cerrar el estudio.
Cobrar mucho sin valor claro = perder el proyecto.
Cobrar bien = un negocio sostenible que puede reinvertir en calidad.

**Modelos de cobro → `references/pricing-models.md`**
**Cómo estimar correctamente → `references/estimation.md`**
**Definir la tarifa del estudio → `references/rates.md`**
**Comunicar el precio al cliente → `references/price-communication.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — tarifa/margen del estudio si está documentado.
2. Análisis o propuesta previa en `docs/` según project-memory.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** modelo de cobro acordado → project-memory; pricing doc en repo; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory; reutilizar estimación PERT existente si hay análisis previo.
1. **Descomponer el proyecto en módulos.** Listar todos los módulos/épicas del
   proyecto (auth, CRUD principal, integraciones, panel admin, etc.). Si el
   input es vago, derivar módulos del brief y declararlos como supuestos.
2. **Estimar con PERT cada módulo** — leer `references/estimation.md`:
   tres escenarios (O/M/P), fórmula `E = (O + 4M + P) / 6`, fases no-dev
   (discovery, diseño, QA, deploy, gestión) y buffer 20-40%.
3. **Calcular el costo** — leer `references/rates.md`: tarifa break-even del
   estudio + margen 30-40%. Aplicar el default de tarifa si el estudio no la
   tiene definida (ver Defaults).
4. **Elegir el modelo de cobro** — leer `references/pricing-models.md`:
   fixed price / T&M / retainer / combinaciones, según certeza del scope y
   tipo de cliente (tabla "Cuándo Usar Cada Modelo" abajo).
5. **Preparar la comunicación del precio** — leer
   `references/price-communication.md`: presentación, manejo de objeciones,
   técnica del menú de opciones si aplica.
6. **Producir la tabla de output** con el esqueleto de `## Entregable`,
   declarando supuestos y pasando el "Checklist de Pricing". Gate verificable:
   el entregable no debe tener celdas sin completar —
   `grep -nE 'TODO|\[completar\]|XXX' pricing.md` debe devolver vacío
   (los `$X,XXX` de plantilla deben estar sustituidos por números reales).
7. **Validación y cierre** — ejecutar `## Validación`; registrar gaps en `LEARNINGS.md`.

---

## Defaults si falta contexto

Asumir y **declarar en el entregable** (no preguntar, salvo bloqueo total):

| Campo faltante | Default asumido |
|----------------|-----------------|
| Tarifa interna del estudio | Rango LATAM senior $40-70/h USD, declarado como suposición a calibrar con los costos reales del estudio |
| Composición del equipo | 1 senior + 1 mid, 6h productivas/día por persona |
| Buffer de imprevistos | 25% (subir a 35-40% si hay integraciones o legacy) |
| Modelo de cobro | Fixed price por fases si el scope está definido; T&M/discovery pagado si no |
| Moneda | USD, sin impuestos ni licencias de terceros |
| Plan de pagos | 30-40% anticipo + hitos por fase |

Pregunta bloqueante única permitida: si no hay descripción del proyecto
suficiente ni para derivar módulos.

---

## El Error que Destruye la Rentabilidad

```
El estudio recibe un proyecto.
El dev líder estima: "son unas 80 horas."
Alguien multiplica por la tarifa y ese es el precio.
El proyecto toma 160 horas.
El estudio pierde dinero.

Por qué pasó:
1. La estimación fue optimista (solo consideró el desarrollo, no todo lo demás)
2. No hubo buffer para imprevistos
3. No se contabilizaron los costos indirectos del proyecto
4. El scope creció y no se cobró el cambio

Cómo se evita:
1. Estimar lo que realmente toma (descubrimiento + diseño + dev + QA + deploy + buffer)
2. Añadir buffer del 20-40% según la incertidumbre
3. Conocer el costo real por hora del estudio (no solo la tarifa dev)
4. Tener un proceso de change requests que se respeta
```

---

## Los 3 Modelos de Cobro

```
PRECIO FIJO (fixed price):
  → El cliente paga un monto acordado por el scope completo
  → El riesgo de tiempo adicional lo asume el estudio
  → Ventaja: el cliente tiene certeza de inversión
  → Cuándo usar: scope muy bien definido, proyecto repetible

TIEMPO Y MATERIALES (T&M):
  → El cliente paga por horas trabajadas
  → El riesgo de duración lo asume el cliente
  → Ventaja: máxima flexibilidad para cambios
  → Cuándo usar: scope incierto, proyectos ágiles, discovery

RETAINER MENSUAL:
  → El cliente paga una tarifa mensual fija por capacidad reservada
  → Trabajo continuo: mantenimiento, features incrementales, soporte
  → Ventaja: previsibilidad para ambas partes
  → Cuándo usar: relaciones de largo plazo, productos en evolución

Combinaciones comunes:
  → Fixed price para MVP + retainer mensual para evolución post-launch
  → T&M para discovery + fixed price para el proyecto principal
  → Fixed price por fases (reduce riesgo para ambas partes)
```

---

## Cuándo Usar Cada Modelo

```
Proyecto nuevo, scope definido, cliente directo:
  → Fixed price. El cliente quiere certeza. El estudio hizo el análisis.

Proyecto nuevo, scope incierto, cliente con recursos:
  → T&M o Fixed price por fases con discovery pagado primero.

Proyecto de empresa, proceso de licitación:
  → Fixed price (casi siempre exigido en licitaciones).
  → Añadir cláusulas de change request al contrato.

Producto en evolución continua (startup, SaaS):
  → Retainer mensual + sprint planning mensual.
  → El cliente decide cada mes qué se construye.

Mantenimiento y soporte post-lanzamiento:
  → Retainer mensual con banco de horas.
  → SLA de tiempo de respuesta según el nivel del retainer.

Cliente que no sabe qué quiere:
  → Discovery pagado primero. Siempre.
  → El precio del discovery se descuenta del proyecto si continúan.
```

---

## Ejemplo input → output

**Input:** "Precio fixed para MVP Laravel+React, 4 módulos, cliente PYME."

**Output:** tabla PERT por módulo, buffer 25%, fixed price con rango ±15%, plan 40/30/30, tarifa declarada como supuesto LATAM senior. Gate: sin `XXX` ni celdas vacías en el doc.

---

## Validación

| Gate | Acción | Criterio |
|------|--------|----------|
| Placeholders | `grep -nE 'TODO|\[completar\]|XXX' <archivo>` | vacío |
| Buffer | fases no-dev + buffer | ≥20% total |
| Margen | precio vs costo | ≥30% margen declarado |
| Modelo | elección documentada | alineado con certeza del scope |
| Checklist | Checklist de Pricing | ítems aplicables ✓ |

---

## Entregable

```markdown
# Pricing — [Proyecto]

## Estimación por módulo (PERT)
| Módulo | O | M | P | PERT (h) | Rango |
|--------|---|---|---|----------|-------|
| [módulo 1] | | | | | |
| **Subtotal desarrollo** | | | | **Xh** | |

## Fases no-dev y buffer
| Concepto | % | Horas |
|----------|---|-------|
| Discovery/análisis | 10% | |
| Diseño UX/UI | 15% | |
| Testing/QA | 12% | |
| Deploy/config | 8% | |
| Gestión/comunicación | 8% | |
| Buffer imprevistos | 25% | |
| **TOTAL** | | **Xh** |

## Precio
- Tarifa aplicada: $X/h ([fuente: tarifa del estudio / default LATAM senior])
- Modelo de cobro: [fixed price / T&M / retainer] — [justificación 1 línea]
- Precio total: $X,XXX — Rango: $X,XXX-$X,XXX
- Plan de pagos: [anticipo % + hitos]

## Supuestos declarados
- [defaults aplicados por falta de información]

## Riesgos de la estimación
- [señales de alerta detectadas — ver references/estimation.md]
```

---

## Checklist de Pricing

```
Antes de enviar el precio:
□ Estimé las horas reales de CADA fase (no solo desarrollo)
□ Añadí buffer del 20-40% según la incertidumbre del proyecto
□ Calculé el costo de las dependencias externas (licencias, APIs)
□ El precio cubre el costo real del estudio (no solo los dev)
□ El modelo de cobro es el correcto para este tipo de proyecto
□ Definí el plan de pagos vinculado a hitos

Antes de aceptar un proyecto:
□ El precio incluye margen real (no solo cubrir costos)
□ El cliente tiene presupuesto para el proyecto completo
□ El alcance está suficientemente definido para el modelo elegido
□ Si hay incertidumbre alta: precio por fases o T&M, no fixed price total
```

---

## Skills relacionadas

| Skill | Relación |
|-------|----------|
| `software-project-analysis` | El análisis técnico es la base de la estimación |
| `propuestas-contratos` | El precio va en la propuesta y el contrato |
| `sprint-planning` | La velocity del equipo informa la estimación |

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
