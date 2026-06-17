# Stack de Monitoreo por Tamaño de Proyecto

> ⚠️ Los precios, límites y free tiers citados aquí son **datos con caducidad
> — revisar** en la web de cada proveedor antes de presupuestar o recomendar.

## Stack Mínimo (Todo Proyecto, Siempre)

```
Costo: $0/mes
Tiempo de setup: 2 horas
Cubre: error tracking + uptime

─────────────────────────────────────────────────────
HERRAMIENTAS:
  Error tracking:  Sentry Free (5k errores/mes)
  Uptime:          UptimeRobot Free (50 monitores, 5min interval)
  Logs:            Los logs del servidor (acceso SSH cuando hay problema)

QUÉ DETECTA:
  ✅ La app lanzó una excepción
  ✅ Un endpoint retorna 500
  ✅ La app está caída
  ❌ Performance degradado (sin alerta, solo si el cliente reporta)
  ❌ Queries lentas (solo con logs manuales)
  ❌ Métricas de infraestructura

CUÁNDO ES SUFICIENTE:
  → Proyectos < $5,000 de desarrollo
  → Sitios de presencia o portfolios
  → MVPs sin SLA
  → Proyectos de bajo tráfico (< 100 usuarios/día)
─────────────────────────────────────────────────────
```

---

## Stack Básico (Proyectos con Usuarios Reales)

```
Costo: ~$26-50/mes
Tiempo de setup: 4-6 horas
Cubre: error tracking + uptime + logs centralizados + alertas básicas

─────────────────────────────────────────────────────
HERRAMIENTAS:
  Error tracking:  Sentry Team ($26/mes, 50k errores/mes)
  Uptime:          Freshping Free (1min interval) o BetterStack
  Logs:            BetterStack Logs (gratis hasta 1GB/mes)
  Alertas:         Slack Webhook (gratis)

CONFIGURACIÓN:
  1. Sentry con DSN en la app
     - Alert: new issue → Slack
     - Alert: error spike → Slack
  2. Freshping apuntando al health check
     - Alert: downtime → email + Slack
  3. BetterStack recibiendo logs estructurados (JSON)
  4. Health check endpoint en la app (/health)

QUÉ DETECTA:
  ✅ Todas las excepciones con contexto
  ✅ La app está caída
  ✅ Errores de seguridad (si están logueados)
  ✅ Logs searchables para debugging
  ⚠️  Performance: solo via logs, no métricas automáticas
  ❌ Métricas de infraestructura (CPU, RAM)

CUÁNDO ES SUFICIENTE:
  → SaaS pequeño o mediano sin SLA formal
  → E-commerce de bajo/medio volumen
  → Apps B2B internas
  → Proyectos $5,000-$30,000
─────────────────────────────────────────────────────
```

---

## Stack Profesional (Proyectos con SLA o Alto Tráfico)

```
Costo: ~$100-300/mes
Tiempo de setup: 1-2 días
Cubre: observabilidad completa + alertas inteligentes + dashboards

─────────────────────────────────────────────────────
HERRAMIENTAS:
  Error tracking:  Sentry Business ($89/mes, 500k errores + performance)
  Uptime:          BetterStack ($20/mes, 30s interval, múltiples ubicaciones)
  Logs:            BetterStack Logs ($25/mes, 5GB) o Datadog
  APM:             Sentry Performance (incluido en Business)
  Infraestructura: Grafana Cloud Free (10k métricas/mes)
                   + Prometheus en el servidor
  Alertas:         PagerDuty Free (5 usuarios) + Slack
  Status page:     Instatus Free

CONFIGURACIÓN ADICIONAL:
  → Sentry Performance: traces en 10% de requests
  → Grafana dashboards: CPU, RAM, disco, DB connections
  → PagerDuty para alertas críticas (P1/P2)
  → Runbooks documentados en Notion/Confluence

QUÉ DETECTA:
  ✅ Todo lo del stack básico
  ✅ Performance degradado por endpoint
  ✅ Queries lentas con contexto completo
  ✅ Saturación de infraestructura (CPU, RAM, disco)
  ✅ Anomalías de tráfico
  ✅ Tendencias de performance a lo largo del tiempo

CUÁNDO ES SUFICIENTE:
  → Proyectos con SLA firmado
  → E-commerce de alto volumen
  → SaaS con múltiples clientes
  → Proyectos > $50,000
  → Apps con miles de usuarios diarios
─────────────────────────────────────────────────────
```

---

## Stack Enterprise (Múltiples Servicios o Microservicios)

```
Costo: $500+/mes (varía mucho)
Tiempo de setup: 1-2 semanas
Cubre: observabilidad completa para sistemas distribuidos

─────────────────────────────────────────────────────
HERRAMIENTAS:
  Error tracking:  Sentry Business o Datadog Error Tracking
  APM + Tracing:   Datadog APM ($31/host/mes) o New Relic
  Logs:            Datadog Logs o Elastic Stack
  Métricas:        Datadog o Prometheus + Grafana (self-hosted)
  On-call:         PagerDuty Team ($19/usuario/mes)
  Status page:     StatusPage.io o custom

O STACK OPEN SOURCE (self-hosted):
  Logs:            Loki (Grafana)
  Métricas:        Prometheus
  Tracing:         Jaeger o Tempo (Grafana)
  Dashboards:      Grafana
  Alerting:        Grafana Alerting → PagerDuty
  Todo junto:      Grafana Cloud Free tier cubre bastante

CUANDO USAR DATADOG:
  → Quieres un solo pane of glass (todo en un lugar)
  → El cliente ya paga por Datadog
  → Tienes presupuesto para la herramienta más completa
  → No quieres mantener infraestructura de monitoreo propia

CUANDO USAR STACK OPEN SOURCE:
  → Control total de los datos (GDPR, compliance)
  → Equipo técnico con capacidad para mantenerlo
  → Muchos hosts (Datadog cobra por host, puede ser caro)
  → El cliente tiene restricciones de datos
─────────────────────────────────────────────────────
```

---

## Monitoreo Como Servicio al Cliente

```
El monitoreo puede ser parte del plan de mantenimiento mensual.

INCLUIR EN EL PLAN DE MANTENIMIENTO:
  → Setup y mantenimiento de la stack de monitoreo
  → Revisión semanal de errores y anomalías
  → Informe mensual de uptime y performance
  → Respuesta a alertas en horario de trabajo
  → Actualización de umbrales de alerta según el crecimiento

CÓMO PRESENTARLO AL CLIENTE:

"El plan de mantenimiento incluye monitoreo 24/7 del sistema.
Esto significa que nuestro equipo se entera de los problemas
antes de que tú o tus usuarios los reporten.

Cada mes recibirás un informe con:
- Uptime del período (meta: > 99.5%)
- Los 5 errores más frecuentes y su estado de resolución
- Performance del sistema (tiempo de respuesta promedio)
- Cualquier incidente ocurrido y cómo se resolvió"

Por qué el cliente lo valora:
→ Tranquilidad de que alguien está mirando el sistema
→ Datos concretos sobre la salud del sistema
→ Historial de problemas resueltos (demuestra el valor del mantenimiento)

Por qué el estudio lo valora:
→ Ingreso recurrente predecible
→ Control sobre la calidad del sistema post-lanzamiento
→ Contexto para estimar el trabajo de mantenimiento
→ Detectas los problemas antes de que sean crisis
```

---

## Checklist de Monitoreo Pre-Launch

```
La semana antes del lanzamiento a producción, verificar:

Sentry:
□ DSN configurado en producción (variable de entorno, no hardcodeado)
□ Release configurado (para saber qué deploy introdujo un error)
□ Ambiente configurado ("production", no "local")
□ Datos sensibles redactados (passwords, tokens no aparecen en Sentry)
□ Alert configurado: nuevo issue → Slack/email

Health Check:
□ /health endpoint respondiendo correctamente
□ Verifica: DB, cache, queue (si aplica)
□ Retorna 503 si algo falla (no 200 siempre)

Uptime Monitor:
□ Monitor apuntando al health check
□ Interval: 1-5 minutos
□ Alert channels configurados (email + Slack)
□ Recovery alerts habilitados

Logs:
□ Logs estructurados (JSON) en producción
□ Nivel de log apropiado (no DEBUG en producción)
□ Sin datos sensibles en los logs
□ Centralización configurada (si aplica)

Alertas:
□ Al menos 2 personas reciben alertas (no solo una persona)
□ Canal de Slack creado (#alerts-[nombre-proyecto])
□ Contacto del cliente para alertas críticas (si corresponde)

Post-launch (primera semana):
□ Revisar Sentry diariamente — entender el baseline
□ Verificar que las alertas de Sentry son útiles (ajustar si hay ruido)
□ Confirmar que el uptime monitor está funcionando (hacer un test)
```
