# Fases de Análisis — Matriz de Activación y Detalle

## Cuándo Ejecutar Cada Fase

| Nivel de Input | F1 Discovery | F2 Reqs | F3 Arq | F4 Estimación | F5 Roadmap | F6 Riesgos |
|----------------|-------------|---------|--------|--------------|------------|------------|
| Nivel 0 — Idea vaga | Completo | Básico | Sketch | Rough | Alto nivel | Top 5 |
| Nivel 1 — Brief básico | Parcial | Completo | Propuesta | PERT básico | Por fases | Completo |
| Nivel 2 — Brief detallado | Validación | Completo | Completo | PERT detallado | Completo | Completo |
| Nivel 3 — RFP | Mínimo | Completo | Completo | PERT detallado | Completo | Completo |

---

## FASE 1 — Discovery y Comprensión del Cliente

**Objetivo:** entender el problema real, no el síntoma que describe el cliente.

### 1.1 Análisis del Problema Real
- Separar: lo que el cliente *dice* que quiere vs. lo que *necesita*
- Identificar el trabajo-a-hacer (Jobs To Be Done)
- Detectar suposiciones no validadas del cliente
- Evaluar viabilidad del modelo de negocio (no solo del software)

### 1.2 Perfil del Usuario Final
- Segmentos de usuario (primario, secundario, admin)
- Contexto de uso: dispositivo, conectividad, nivel técnico, idioma
- Pain points actuales con la solución existente
- Comportamientos esperados vs. comportamientos reales probables

### 1.3 Análisis Competitivo Rápido
- 3-5 competidores directos: qué hacen bien, qué hacen mal
- Oportunidad de diferenciación técnica real
- Riesgos de replicabilidad del producto

### 1.4 Señales de Comportamiento del Cliente
**Leer `client-behavior.md` (misma carpeta) para patrones completos.**

Señales críticas a detectar en esta fase:
- Scope creep latente (mencionan "y también podría tener...")
- Decisiones hechas por ego, no por datos
- Conflictos internos disfrazados de requerimientos técnicos
- Presupuesto irreal vs. expectativas de producto
- Timelines imposibles con justificaciones emocionales

---

## FASE 2 — Levantamiento de Requerimientos

**Objetivo:** traducir necesidades de negocio a especificaciones técnicas verificables.

### 2.1 Requerimientos Funcionales
Formato estándar por módulo:

```
MÓDULO: [nombre]
ACTOR: [quién ejecuta]
ACCIÓN: [qué hace]
RESULTADO: [qué obtiene]
REGLA DE NEGOCIO: [condiciones, validaciones, excepciones]
PRIORIDAD: [P0-crítico / P1-importante / P2-deseable / P3-futuro]
```

### 2.2 Requerimientos No Funcionales
- **Performance:** tiempo de respuesta esperado, carga concurrente
- **Disponibilidad:** uptime requerido (99% vs 99.9% vs 99.99% — diferencia de costo enorme)
- **Seguridad:** autenticación, autorización, cifrado, auditoría
- **Escalabilidad:** horizontal vs. vertical, cuándo y cuánto
- **Mantenibilidad:** documentación, cobertura de tests, onboarding de devs
- **Accesibilidad:** WCAG 2.1 AA si aplica
- **Internacionalización:** idiomas, monedas, zonas horarias

### 2.3 Requerimientos de Integración
Para cada integración externa documentar:
- Nombre del sistema / API
- Tipo: REST / SOAP / SDK / Webhook / File transfer
- Flujo de datos: dirección, frecuencia, volumen
- Disponibilidad del proveedor para soporte
- Costo de integración (licencias, llamadas API, dev time)
- Plan B si la integración falla o cambia

### 2.4 User Stories (formato para desarrollo)
```
Como [tipo de usuario]
Quiero [acción específica]
Para [beneficio concreto]

Criterios de Aceptación:
- DADO [contexto]
- CUANDO [acción]
- ENTONCES [resultado esperado]

Definición de Hecho:
- [ ] Tests unitarios escritos y pasando
- [ ] Code review aprobado
- [ ] Desplegado en staging
- [ ] QA sign-off
```

---

## FASE 3 — Arquitectura Técnica

**Objetivo:** definir el sistema completo antes de escribir una línea de código.

### 3.1 Selección de Stack

Evaluar cada opción con matriz:

| Criterio | Peso | Opción A | Opción B |
|----------|------|----------|----------|
| Fit con problema | 25% | | |
| Velocidad de desarrollo | 20% | | |
| Talento disponible | 20% | | |
| Escalabilidad | 15% | | |
| Costo operativo | 10% | | |
| Comunidad/soporte | 10% | | |

**Stacks comunes por tipo de proyecto → ver `templates.md` (misma carpeta).**

### 3.2 Arquitectura del Sistema

Producir obligatoriamente (diagramas en Mermaid o ASCII):
- **Diagrama C4 Nivel 1:** contexto del sistema (actores externos)
- **Diagrama C4 Nivel 2:** containers (frontend, backend, DB, servicios)
- **Diagrama C4 Nivel 3:** componentes críticos (auth, pagos, notificaciones)
- **Diagrama de flujo de datos:** cómo viaja la información sensible
- **Diagrama de deployment:** infra cloud, regiones, CDN, CI/CD

### 3.3 Decisiones de Arquitectura (ADR)

Para cada decisión técnica importante documentar:
```
ADR-001: [Título de la decisión]
Estado: [Propuesta / Aceptada / Reemplazada]
Contexto: [Por qué hay que decidir esto]
Opciones consideradas: [A, B, C]
Decisión: [Opción elegida]
Consecuencias positivas: [...]
Consecuencias negativas / trade-offs: [...]
```

### 3.4 Modelo de Datos

- Entidades principales y sus relaciones
- Estrategia de base de datos (relacional vs. NoSQL vs. híbrido)
- Manejo de migraciones
- Estrategia de backup y recuperación
- Volumen esperado de datos (impacto en costo y performance)

### 3.5 Seguridad por Diseño

- Modelo de autenticación (JWT / OAuth2 / SAML / Passkeys)
- Modelo de autorización (RBAC / ABAC / ACL)
- Superficies de ataque identificadas
- Datos sensibles: dónde viven, cómo se cifran, quién accede
- Cumplimiento regulatorio requerido

### 3.6 Estrategia de APIs

- REST vs. GraphQL vs. gRPC — decisión justificada
- Versionado de APIs
- Rate limiting y throttling
- Documentación (OpenAPI/Swagger)
- Estrategia de breaking changes

---

## FASE 4 — Estimación de Esfuerzo

**Objetivo:** estimación honesta con rangos, no números mágicos.

### 4.1 Metodología de Estimación

Usar **tres puntos** siempre (no estimación puntual):
```
Optimista (O): todo sale bien, sin bloqueos
Más probable (M): condiciones normales de desarrollo
Pesimista (P): problemas técnicos, cambios de requerimientos

PERT = (O + 4M + P) / 6
Desviación estándar = (P - O) / 6
```

### 4.2 Estructura de Estimación por Módulo

Para cada módulo/épica:

```
MÓDULO: [nombre]
├── Backend (lógica + APIs): X días
├── Frontend/UI: X días
├── Integración + pruebas: X días
├── QA: X días (mínimo 20% del total de dev)
└── Buffer técnico: X días (15-20% del total)

TOTAL MÓDULO: X días / X semanas
```

### 4.3 Factores de Ajuste

Aplicar multiplicadores según contexto:

| Factor | Multiplicador |
|--------|--------------|
| Equipo nuevo en stack | x1.3 |
| Requerimientos cambiantes (cliente inestable) | x1.4 |
| Integraciones con sistemas legado | x1.5 |
| Sin diseño UX previo | x1.2 |
| Deuda técnica existente | x1.3-2.0 |
| Primer proyecto del equipo juntos | x1.25 |

### 4.4 Costo de No Calidad

Documentar costo explícito de omitir:
- Tests: +40% tiempo de debugging post-producción
- Documentación: +30% tiempo de onboarding nuevos devs
- Code review: +25% bugs en producción
- Staging environment: imposible detectar bugs de integración

---

## FASE 5 — Roadmap y Fases de Entrega

**Objetivo:** secuencia de entrega que maximiza valor y minimiza riesgo.

### 5.1 Definición de MVP

El MVP real responde a: **¿cuál es el mínimo que valida la hipótesis de negocio más crítica?**

No es: "todo lo que pedimos pero más rápido"
No es: "la versión barata del producto completo"
Sí es: "el experimento más pequeño que prueba si alguien paga/usa esto"

Criterios del MVP:
- Resuelve UN problema central completamente
- Es usable por usuarios reales sin asistencia
- Genera datos medibles de comportamiento
- Puede entregarse en ≤ 12 semanas (si tarda más, reducir scope)

### 5.2 Estructura de Fases

```
FASE 0 — Fundaciones (semanas 1-2)
├── Setup de infra y CI/CD
├── Arquitectura base y auth
├── Design system / componentes base
└── Definición de estándares de código

FASE 1 — MVP Core (semanas 3-N)
├── Módulos P0 únicamente
├── Happy path completo y funcional
├── Sin optimizaciones de performance aún
└── QA básico + deploy a staging

FASE 2 — MVP Completo (semanas N+1-M)
├── Módulos P1
├── Edge cases y manejo de errores
├── Performance básico
└── Beta con usuarios reales

FASE 3 — Producto (semanas M+1-...)
├── Módulos P2
├── Optimizaciones
├── Analytics y monitoreo
└── Automatizaciones

FASE N — Evolución continua
└── Backlog priorizado por datos reales
```

### 5.3 Criterios de Entrada/Salida por Fase

Cada fase tiene:
- **Gate de entrada:** qué debe estar listo para empezar
- **Definición de hecho:** qué debe estar completo para cerrar
- **Métricas de éxito:** números concretos, no "funciona bien"

---

## FASE 6 — Análisis de Riesgos

**Leer `risk-patterns.md` (misma carpeta) para catálogo completo.**

### 6.1 Matriz de Riesgos

| Riesgo | Probabilidad | Impacto | Exposición | Mitigación |
|--------|-------------|---------|------------|------------|
| [riesgo] | Alta/Media/Baja | Alto/Medio/Bajo | P×I | [acción] |

### 6.2 Categorías de Riesgo Obligatorias

- **Riesgos técnicos:** deuda técnica, integraciones, escalabilidad
- **Riesgos de requerimientos:** scope creep, cambios, ambigüedad
- **Riesgos de equipo:** disponibilidad, rotación, curva de aprendizaje
- **Riesgos de negocio:** cambio de prioridades, presupuesto, mercado
- **Riesgos del cliente:** indecisión, aprobaciones lentas, conflictos internos
- **Riesgos externos:** APIs de terceros, regulación, dependencias

### 6.3 Plan de Contingencia

Para cada riesgo de exposición Alta:
- Señal de alerta temprana (¿cómo saber que está pasando?)
- Umbral de activación del plan B
- Plan B concreto y ejecutable
- Responsable de activarlo
