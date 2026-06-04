---
name: comprobacion-produccion
description: >-
  Comprueba desarrollos antes de despliegue a producción y durante producción,
  y también inmediatamente DESPUÉS de una implementación terminada: revisión de
  calidad, riesgo y cierre de tarea. Usar cuando el usuario pida validar un
  cambio, cierre de feature, PR, go-live, pre/post despliegue, operación en
  producción, o al finalizar una implementación (post-desarrollo).
---

# Comprobación pre-producción y durante producción

## Cuándo invocar (lectura y aplicación de esta skill)

- **Tras completar una implementación** (cuando el agente o el usuario den por hecha una tarea de código): volcar una pasada de comprobación acorde a la sección **0**; no dar por cerrado solo con el último `edit` sin verificar.
- **Antes de producción** o de merge a rama de release, o cuando pidan checklists de release.
- **Durante producción:** post-despliegue, observación, operación continua.

Activa el flujo cuando haya que **validar un desarrollo o un conjunto de cambios** antes de producción, o definir/auditar **qué comprobar mientras el sistema ya está en producción**. Adapta el stack (framework, cloud, CI) a lo que exista en el repositorio o al contexto del usuario; no asumas un solo lenguaje.

## Principio

- **Pre-producción:** reducir riesgo con verificaciones reproducibles, reversibilidad y criterios de aceptación claros.
- **Producción:** asegurar visibilidad, control de cambios y reacción ante regresiones sin depender de “revisar a ojo”.
- **Post-implementación:** cerrar el bucle con verificación focalizada (no repitas todo el documento: prioriza lo tocado por el cambio).

## 0. Tras completar una implementación (invocación posterior al código)

Cuando se acaba de **implementar** una funcionalidad, corrección o refactors relevantes, aplicar de forma **acotada** (solo archivos/áreas afectados salvo señal de riesgo global):

1. **Coherencia:** el cambio cumple el objetivo del usuario; no quedan TODOs críticos ni dead code obvio; imports y rutas coherentes.
2. **Pruebas y lints:** ejecutar o proponer tests/linters aplicables; si el agente no puede ejecutar, listar comandos y qué debería pasar.
3. **Regresión:** comprobar usos/llamadas rotas, tipos, API pública, migraciones o datos si aplica.
4. **Seguridad mínima en el diff:** entradas validadas, no exponer secretos, permisos en rutas afectadas.
5. **Observabilidad mínima:** log o métrica si se introdujo un flujo de error o negocio nuevo.
6. **Formato de salida:** misma estructura que en la sección 3, pero prefijar con *“Revisión post-implementación”* y resumir en pocas frases; si todo está claro, indicar explícitamente **“Listo para revisión humana/merge”** o lo que falte.

Si el usuario pide *solo* la revisión post-implementación, puedes condensar la sección 0 y omitir el resto; si pide *release* o *producción*, cruzar 0 con las secciones 1–2.

## 1. Antes de producción (gates)

Según el tipo de cambio, ejecutar o proponer en este orden lógico (omite lo no aplicable, indícalo en el informe).

### 1.1 Código y tests

- Tests automatizados relevantes: unitarios, integración, e2e críticos, contratos.
- Cobertura mínima aceptable por el equipo; corregir tests rotos o flaky **antes** del merge a rama de release.
- Linter, tipado estricto si el proyecto lo usa; sin “warnings silenciados” nuevos de forma oportunista.
- Revisar diff: efectos colaterales, dependencias nuevas, código muerto o flags sin uso.

### 1.2 Seguridad y datos

- Secretos: ninguna credencial en repositorio; variables en gestor adecuado; rotación si se tocó algo sensible.
- Validación de entradas, autenticación/autorización en rutas afectadas, CORS/CSRF según aplique.
- Migrations/DDL: plan de migración, tiempo estimado, locks; backups antes de migraciones riesgosas.
- Cumplimiento: datos personales, retención, logs sin PII en claro si corresponde.

### 1.3 Rendimiento y resiliencia

- Consultas N+1, índices, caché; límites de rate donde proceda.
- Timeouts, reintentos con idempotencia, colas y dead-letter si hay trabajos asíncronos.
- Tamaño de assets y carga bajo conexiones reales o staging representativo.

### 1.4 Configuración y despliegue

- **Paridad** staging ↔ producción en lo esencial (variables, flags, recursos), salvo acuerdo explícito.
- **Feature flags:** estado inicial en prod, desactivación y plan de retirada.
- **Migrations/orden** de despliegue: ¿app antes o después de esquema? plan documentado.
- **Rollback:** qué commit/tag revertir, cómo deshacer migración o flag, RTO/RPO si aplica.

### 1.5 Observabilidad antes de ir

- Alertas y dashboards para **las métricas que este cambio debe mover** (latencia, errores, colas, negocio).
- Logging estructurado con `request_id`/`correlation_id` trazable.
- Sentry/Datadog/etc.: nuevas claves o spans necesarios; umbrales de error rate.

### 1.6 Documentación y operación

- Changelog, notas de release, runbook si el despliegue o el rollback no son triviales.
- “Owner” o contacto y ventana de despliegue acordada.

## 2. Durante producción (operación continua y post-despliegue)

### 2.1 Inmediatamente tras despliegue

- Smoke tests: rutas críticas, health checks, un flujo de negocio mínimo.
- Comparar **antes/después** en métricas clave (5–30 min, según tráfico): errores 5xx, latencia p95/p99, tasa de éxito.
- Revisar logs y alertas en ventana de observación; confirmar canary o porcentaje gradual si existe.

### 2.2 Operación en curso

- **SLO/alertas:** umbrales alineados con el usuario/negocio; páginas ruido vs silencio peligroso.
- **Capacidad:** autoscaling, límites, cuellos (DB, Redis, ancho de banda).
- **Deuda y dependencias:** CVE, EOL de librerías, jobs programados, certificados SSL próximos a vencer.
- **Incidentes:** post-mortem breve, acciones de seguimiento, tests de regresión para el bug clásico.

## 3. Cómo responder (formato de salida)

1. Resumen en **2–4 frases** (riesgo, alcance, fase: pre o prod).
2. **Tabla o lista de comprobaciones** con estado: `Hecho` | `Falta` | `N/A` | `Duda` y **acción** si Falta o Duda.
3. **Riesgos** ordenados: severidad × likelihood, mitigación concreta.
4. **Siguiente paso** único o lista corta (máx. 5) priorizada.

No inventes que algo pasó en CI si no viste prueba; sugiere el comando o la verificación a ejecutar.

## 4. Integración con otras skills

- Revisión UI/UX o accesibilidad: combinar con la skill de lineamientos web o del diseño del proyecto.
- Reglas de seguridad profundas: combinar con reviews de AppSec o threat model si el cambio toca fronteras de confianza.
- `DESIGN.md` / sistema de diseño: si el cambio es de interfaz, cruzar con el documento de diseño del repositorio.

## 5. Lo que no hace esta skill

- No sustituye pipelines CI ni firmas de aprobación humanas.
- No garantiza certificación formal (PCI, SOC, etc.); indica brechas y prepara el terreno.
- No fija SLOs numéricos sin contexto; propone qué medir y por qué.
