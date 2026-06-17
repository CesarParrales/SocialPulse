# Cómo Estimar Correctamente

## Lo que Casi Siempre Se Olvida en la Estimación

```
Los estudios estiman el desarrollo.
Olvidan todo lo demás.

Lo que realmente toma tiempo en un proyecto:

DISCOVERY Y ANÁLISIS (5-15% del total):
  → Reuniones de levantamiento con el cliente
  → Análisis de requerimientos y dudas
  → Revisión de sistemas existentes o documentación
  → Definición del scope detallado

DISEÑO UX/UI (10-20%):
  → Wireframes de flujos clave
  → Diseño visual de pantallas
  → Rondas de revisión con el cliente
  → Handoff al equipo de desarrollo

DESARROLLO BACKEND (25-40%):
  → Modelos de datos y migraciones
  → Lógica de negocio
  → APIs y endpoints
  → Integraciones con terceros

DESARROLLO FRONTEND (20-35%):
  → Implementación de pantallas
  → Integración con la API
  → Estados de carga, error, vacío
  → Responsive y cross-browser

TESTING Y QA (10-15%):
  → Tests unitarios de lógica crítica
  → Testing manual de flujos principales
  → Bug fixes del QA
  → UAT con el cliente

DEPLOYMENT Y CONFIGURACIÓN (5-10%):
  → Configuración de ambientes (staging, producción)
  → CI/CD setup
  → DNS, SSL, dominio
  → Monitoreo básico (Sentry, UptimeRobot)

GESTIÓN Y COMUNICACIÓN (5-10%):
  → Reuniones de seguimiento semanales
  → Documentación del proyecto
  → Coordinación del equipo
  → Presentaciones y demos al cliente

BUFFER DE IMPREVISTOS (20-40%):
  → Integraciones que resultan más complejas de lo esperado
  → Bugs descubiertos tarde en el proyecto
  → Cambios de requerimientos del cliente
  → Problemas técnicos no anticipados

Si solo estimaste el tiempo de desarrollo puro →
multiplicar por 2.5x a 3x para el tiempo real del proyecto.
```

---

## El Método de Estimación por Módulos

```
PASO 1: Listar todos los módulos del proyecto
  Ejemplo: Sistema de gestión de pedidos
  - Autenticación y usuarios
  - Gestión de pedidos (CRUD)
  - Flujo de aprobaciones
  - Notificaciones por email
  - Reportes y exportación
  - Panel de administración
  - API para integración con sistema ERP

PASO 2: Estimar cada módulo con 3 escenarios
  Usar PERT (Program Evaluation and Review Technique):

  O = Optimista (todo sale perfecto)
  M = Más probable (condiciones normales)
  P = Pesimista (problemas razonables)

  Fórmula PERT: E = (O + 4M + P) / 6
  Desviación:   SD = (P - O) / 6

  Ejemplo — Módulo de Autenticación:
  O = 16h (ya lo hiciste antes y tienes boilerplate)
  M = 24h (condiciones normales)
  P = 40h (cliente pide features adicionales, integraciones complejas)

  E = (16 + 4×24 + 40) / 6 = (16 + 96 + 40) / 6 = 152 / 6 = 25.3h
  SD = (40 - 16) / 6 = 4h

  Rango: 25h ± 4h → estimar 25-30 horas para este módulo

PASO 3: Sumar todos los módulos
  Autenticación:          25h
  Gestión de pedidos:     40h
  Flujo de aprobaciones:  35h
  Notificaciones:         20h
  Reportes:               30h
  Panel admin:            25h
  API ERP:                35h
  SUBTOTAL DESARROLLO:   210h

PASO 4: Añadir las fases no-dev
  Discovery/análisis:    10% = 21h
  Diseño UX/UI:          15% = 32h
  Testing/QA:            12% = 25h
  Deploy/config:          8% = 17h
  Gestión/comunicación:   8% = 17h
  SUBTOTAL ADICIONAL:    112h

PASO 5: Añadir buffer de imprevistos
  Buffer 25% sobre el total: (210 + 112) × 0.25 = 80h

TOTAL ESTIMADO: 210 + 112 + 80 = 402h → ~400 horas

PASO 6: Convertir a tiempo real de calendario
  Si el equipo trabaja 6h productivas por día:
  400h / 6h/día = 67 días hábiles
  67 días / 22 días/mes = ~3 meses de calendario

  Regla general: el tiempo en calendario es 30-50% mayor
  que el tiempo en horas puras por interrupciones, reuniones y overhead.
```

---

## Tabla de Referencia — Proyectos Típicos

> **Datos con caducidad — last_updated: 2026-05, revisar trimestralmente.**
> Si la fecha actual supera el trimestre, advertir al usuario que las horas y
> precios de referencia pueden estar desactualizados antes de usarlos.

```
Estas son estimaciones de referencia basadas en proyectos reales.
Ajustar según la complejidad y el equipo específico.

PROYECTO             HORAS        TIEMPO        RANGO PRECIO
─────────────────────────────────────────────────────────────
Landing page simple    20-40h      1-2 semanas   $1,500-4,000
Landing page premium   60-100h     3-5 semanas   $4,000-10,000
Blog/CMS básico        80-120h     4-6 semanas   $5,000-12,000
E-commerce básico     200-300h     2-3 meses     $15,000-30,000
E-commerce completo   400-600h     4-6 meses     $30,000-60,000
SaaS MVP             300-500h      3-5 meses     $25,000-50,000
SaaS completo        600-1200h     6-12 meses    $50,000-120,000
App móvil (1 plat.)  300-500h      3-5 meses     $25,000-50,000
App móvil (2 plat.)  450-700h      4-7 meses     $35,000-70,000
Sistema interno      200-400h      2-4 meses     $15,000-40,000
API/microservicio    100-200h      1-2 meses     $8,000-20,000

Notas:
→ Los precios son en USD y asumen equipo senior en Latinoamérica
→ Ajustar x1.5-2 para equipos en España/Europa
→ El rango bajo = scope simple, sin integraciones complejas
→ El rango alto = scope complejo, integraciones, diseño custom
→ Proyectos en industrias reguladas (salud, finanzas) +30-50%
```

---

## Las Señales de una Estimación que Perderá Dinero

```
Señales de alerta durante la estimación:

❌ "Integración con sistema X" sin documentación del sistema X
   → El tiempo de integración puede ser 3x lo estimado
   → Solicitar documentación técnica antes de estimar

❌ El cliente tiene "datos históricos" para migrar
   → La migración de datos casi siempre es más compleja de lo esperado
   → Estimar la migración por separado como su propio módulo

❌ "El diseño ya está en Figma" sin revisarlo
   → Los diseños de Figma suelen tener gaps, inconsistencias y estados faltantes
   → Revisar el Figma y estimar el costo de design QA + gaps

❌ "Solo necesitamos un cambio al sistema actual"
   → El código existente puede ser un desastre técnico
   → Revisar el código antes de estimar ("discovery técnico")

❌ Primera vez que el equipo usa una tecnología clave
   → Multiplicar la estimación de ese módulo por 1.5-2
   → La curva de aprendizaje no la paga el cliente (la absorbe el estudio)

❌ El cliente quiere empezar "esta semana"
   → La presión de tiempo comprime el análisis → errores de estimación
   → Un proyecto mal estimado por presión de tiempo es rentable para nadie

❌ El precio ya lo decidió alguien antes de la estimación
   → "El cliente tiene $15,000 de presupuesto, fíjate cómo entras"
   → Si el proyecto vale $25,000, no entra en $15,000 de calidad
   → Opciones: reducir scope para que entre en $15,000, o declinar
```

---

## El Sanity Check Final

```
Antes de enviar la propuesta con el precio:

PREGUNTA 1: ¿Cuánto perdería si el proyecto toma el doble?
  Si la respuesta es "el proyecto ya sería a pérdida" → el margen es insuficiente.
  Un proyecto rentable debe aguantar 40-50% de sobrecosto antes de ir a pérdida.

PREGUNTA 2: ¿El precio incluye el costo del dinero bloqueado?
  Si el proyecto dura 4 meses y el pago final es al entregar:
  → El estudio financia al cliente por 4 meses
  → Los pagos por hitos deben cubrir los costos corrientes del estudio

PREGUNTA 3: ¿El equipo puede hacer esto con calidad en el tiempo estimado?
  Si la respuesta es "sí, pero muy ajustado" → el buffer es insuficiente.
  Si la respuesta es "honestamente no sé" → necesitas más información antes de cotizar.

PREGUNTA 4: ¿Rechazarías otro proyecto mejor para hacer este?
  Todo proyecto tiene un costo de oportunidad.
  Si el precio no compensa lo que rechazas → el precio es demasiado bajo.

PREGUNTA 5: ¿Le dirías al cliente el precio con confianza?
  Si sientes que "es muy caro" al comunicarlo → probablemente no entiendiste el valor.
  Si sientes que "es muy barato" al comunicarlo → probablemente lo es.
  El precio correcto se comunica con confianza, no con disculpas.
```
