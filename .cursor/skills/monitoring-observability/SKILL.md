---
name: monitoring-observability
description: >
  Guía el monitoreo y la observabilidad en producción: logs, métricas, alertas,
  error tracking, dashboards operacionales y gestión de incidentes. Usar cuando
  el usuario mencione: monitoreo, observabilidad, Sentry, logs, alertas,
  métricas, uptime, performance en producción, Datadog, Grafana, PagerDuty,
  Prometheus, o cuando diga "cómo sé si algo se rompió", "cómo monitoreo mi
  app", "necesito alertas", "qué pasó en producción", "cómo veo los errores",
  "el cliente me llama antes que yo me entero", o cualquier variante sobre
  visibilidad del sistema en producción.
---

# Monitoring & Observability Skill

Hay dos tipos de equipos de desarrollo:
los que se enteran de los problemas antes que el cliente,
y los que se enteran cuando el cliente llama.

La diferencia es instrumentación.

**Los 3 pilares — logs, métricas, trazas → `references/pillars.md`**
**Error tracking — Sentry → `references/error-tracking.md`**
**Stack por tamaño de proyecto → `references/stack-by-size.md`**
**Métricas e infraestructura — Grafana/Prometheus → `references/metrics-infra.md`**
**Alertas y on-call → `references/alerts-oncall.md`**

> Los precios y free tiers citados en las references son **datos con caducidad
> — revisar** en la web del proveedor antes de presupuestar.

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — URL prod/staging, `/health`, canales de alerta.
2. Variables Sentry/monitoring si están documentadas.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** resumen de instrumentación → `docs/` + project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución — Nivel 0 → 2 en una sesión

Las references se usan en orden de madurez: `pillars.md` (conceptos, nivel 0)
→ `error-tracking.md` (nivel 2) → `stack-by-size.md` (elegir stack) →
`metrics-infra.md` (niveles 3-4) → `alerts-oncall.md` (nivel 5).

0. **Memoria** — leer project-memory (health URL, DSN si existe, SLA).
1. **Diagnosticar el nivel actual** (escala de madurez abajo). Si es la primera
   vez con observabilidad → leer `references/pillars.md` para el marco
   conceptual. Verificar qué hay: `grep -ri sentry .env* config/ 2>/dev/null`
   y preguntar por el uptime monitor solo si no es deducible.
2. **Detectar el framework**: `ls composer.json package.json 2>/dev/null` —
   Laravel → `composer require sentry/sentry-laravel`;
   Node → `npm i @sentry/node` (Next.js: `npx @sentry/wizard@latest -i nextjs`).
   Detalle de configuración → `references/error-tracking.md`.
3. **Configurar el DSN**: crear el proyecto en Sentry (plan Free por defecto),
   poner `SENTRY_DSN` en variables de entorno (nunca hardcodeado), configurar
   `environment` y `release`.
4. **Lanzar un error de prueba**:
   - Laravel: `php artisan tinker --execute="throw new \Exception('sentry test');"`
     (o `php artisan sentry:test` si el paquete lo provee)
   - Node: endpoint temporal que ejecute `throw new Error('sentry test')` o
     `Sentry.captureException(new Error('sentry test'))`
   - **Gate: el error es visible en el dashboard de Sentry en < 5 minutos.**
     Si no aparece → revisar DSN/environment antes de continuar.
5. **Uptime check (Nivel 1)**: crear un endpoint `/health` que verifique BD y
   cache (retorna 503 si algo falla, no 200 siempre) y registrarlo en
   UptimeRobot (u otro) con intervalo de 5 min y alertas a email/Slack.
   Gate: `curl -s -o /dev/null -w "%{http_code}" https://app/health` → `200`,
   y el monitor figura "Up" en el panel.
6. **Configurar alertas mínimas**: Sentry "new issue" → email/Slack; uptime
   "down" → alguien que pueda actuar. Si el proyecto exige más (SLA, on-call)
   → `references/stack-by-size.md` para dimensionar y
   `references/metrics-infra.md` + `references/alerts-oncall.md` para
   niveles 3-5.
7. **Entregar el resumen** (ver `## Entregable`), ejecutar `## Validación` y
   registrar gaps en `LEARNINGS.md`.

---

## Por Qué la Observabilidad No es Opcional

```
Sin observabilidad en producción:
→ Te enteras de los problemas cuando el cliente llama
→ Debuggear en producción a ciegas = horas de tiempo perdido
→ No sabes si tu último deploy mejoró o empeoró el performance
→ No puedes demostrar el uptime acordado en el SLA
→ Los problemas intermitentes son imposibles de reproducir

Con observabilidad:
→ Sabes que algo falló antes de que el usuario lo note (a veces)
→ Tienes el contexto exacto de qué pasó, cuándo y por qué
→ Puedes responder "¿afectó usuarios?" con datos, no con suposiciones
→ El on-call tiene información para actuar, no para investigar a ciegas
→ Los reportes de SLA tienen datos reales
```

---

## Los 3 Pilares de la Observabilidad

```
LOGS (el quién, qué, cuándo):
  Registro de eventos que ocurrieron en el sistema.
  Responde: ¿qué pasó exactamente?
  Ejemplo: "User 123 tried to pay order #456 — Stripe returned charge_failed"

MÉTRICAS (el cuánto, tendencias):
  Valores numéricos en el tiempo.
  Responde: ¿qué tan seguido y qué tan grave?
  Ejemplo: "Error rate 0.2% → pico a 12% a las 14:30 → volvió a 0.3%"

TRAZAS / APM (el por qué, el dónde):
  El rastro completo de una request a través del sistema.
  Responde: ¿dónde exactamente tardó o falló?
  Ejemplo: "Request /checkout → 42ms app → 890ms DB (el cuello) → 234ms Stripe"

Los 3 juntos = puedes responder cualquier pregunta sobre tu sistema.
Detalle completo → references/pillars.md
```

---

## Niveles de Madurez

```
NIVEL 0 — Sin monitoreo:        Solo te enteras cuando llama el cliente.
NIVEL 1 — Uptime básico:        Sabes si el sitio está caído.
NIVEL 2 — Error tracking:       Sabes qué errores ocurren y cuándo.
NIVEL 3 — Logs centralizados:   Todos los logs en un lugar searchable.
NIVEL 4 — Métricas de app:      CPU, memoria, queries lentas, latencia.
NIVEL 5 — Alertas inteligentes: On-call con contexto para actuar.

OBJETIVO MÍNIMO PARA PRODUCCIÓN: Nivel 1 + 2
Implementable en < 4 horas por proyecto (el protocolo de arriba lo hace
en una sesión). Sin excusas.

Reference por nivel:
  Nivel 0→2: error-tracking.md  ·  Elegir stack: stack-by-size.md
  Nivel 3-4: metrics-infra.md   ·  Nivel 5: alerts-oncall.md
```

---

## Checklist Pre-Lanzamiento

```
□ Sentry instalado — errores llegan a email o Slack
□ UptimeRobot o equivalente monitoreando la URL principal
□ Logs de aplicación persistentes (no solo disco del servidor)
□ Alertas de uptime van a alguien que puede actuar
□ El equipo sabe cómo acceder a logs en producción
□ Runbook básico: "si Sentry alerta X → hacer Y"

Para proyectos con SLA formal:
□ Métricas de uptime exportables para reportes al cliente
□ Tiempo de respuesta por endpoint monitoreado
□ Alertas de anomalías configuradas
□ On-call definido con escalation clara
```

---

## Defaults si falta contexto

Si el usuario no especifica, asumir Y DECLARAR (máx. 1 pregunta solo si es
bloqueante, p. ej. no hay acceso para crear la cuenta de Sentry):

- **Stack por defecto para CUALQUIER app en producción**: Sentry Free
  (error tracking) + UptimeRobot Free (uptime). Sin preguntar.
- **Objetivo**: Nivel 1 + 2 de madurez; niveles superiores solo si hay SLA,
  alto tráfico o el usuario lo pide (dimensionar con
  `references/stack-by-size.md`).
- **Canal de alertas**: email del responsable; Slack si el equipo ya lo usa.
- **Health check**: `/health` verificando BD y cache, 503 si algo falla.
- **Precios/free tiers**: tratarlos como datos con caducidad — confirmar en la
  web del proveedor antes de comprometerlos en una propuesta.

---

## Ejemplo input → output

**Input:** "Configurar observabilidad básica en SocialPulse staging."

**Output:** Sentry Laravel con DSN en env; error de prueba visible <5 min; `/health` con BD+Redis; UptimeRobot en staging URL. Gate: `curl -s -o /dev/null -w "%{http_code}" https://staging/health` → 200.

---

## Validación

| Gate | Comando / acción | Criterio |
|------|------------------|----------|
| Sentry test | `php artisan sentry:test` o error provocado | visible en dashboard <5 min |
| Health | `curl -s -o /dev/null -w "%{http_code}" <url>/health` | 200 (503 si deps caídas) |
| Uptime | panel del monitor | estado Up |
| Alertas | configuración Sentry/uptime | canal operativo definido |
| Entregable | plantilla abajo | nivel antes→después documentado |

---

## Entregable

Resumen de instrumentación:

```markdown
# Monitoring Setup — <proyecto> — YYYY-MM-DD

## Nivel de madurez: <antes> → <después>

## Error tracking (Nivel 2)
- Sentry: proyecto <nombre>, plan <Free/...>, DSN en env var
- Error de prueba visible en dashboard: ✅ (timestamp, <5 min)
- Alertas: new issue → <email/Slack>

## Uptime (Nivel 1)
- Endpoint /health: <URL> (verifica: BD, cache) → HTTP 200
- Monitor: <UptimeRobot/...>, intervalo <5 min>, alertas a <canal>

## Pendiente (niveles 3-5, si aplica)
- [ ] Logs centralizados · [ ] Métricas de infra · [ ] On-call
```

---

## Skills relacionadas

- `devops-base` — el pipeline que despliega también instrumenta.
- `security-checklist` — logs de seguridad y accesos.
- `performance-web` — las métricas de web vitals en producción.
- `laravel-backend` / `node-backend` — instrumentación por stack.
- `propuestas-contratos` — los SLAs de uptime y respuesta se negocian aquí.

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
