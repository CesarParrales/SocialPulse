# Backlog y User Stories

## User Stories — Estructura que Funciona

```
Formato estándar:
Como [tipo de usuario]
Quiero [acción específica]
Para [beneficio concreto de negocio]

Criterios de Aceptación (formato Gherkin):
DADO [contexto/precondición]
CUANDO [acción del usuario]
ENTONCES [resultado esperado y verificable]
```

---

## Escribir User Stories de Calidad

```
✅ Historia bien escrita:

Como comprador registrado
Quiero poder cancelar un pedido pendiente
Para corregir errores antes de que sea procesado

Criterios de Aceptación:
- DADO que tengo un pedido en estado "pendiente"
  CUANDO accedo a los detalles del pedido
  ENTONCES veo el botón "Cancelar pedido"

- DADO que hago clic en "Cancelar pedido"
  CUANDO confirmo la acción en el diálogo
  ENTONCES el pedido cambia a estado "cancelado"
  Y recibo un email de confirmación de cancelación
  Y el stock de los productos se restaura

- DADO que mi pedido está en estado "enviado"
  CUANDO intento cancelarlo
  ENTONCES veo un mensaje "Este pedido ya no puede cancelarse"
  Y el botón de cancelar no está disponible

- DADO que hago clic en "Cancelar pedido"
  CUANDO cancelo la acción en el diálogo
  ENTONCES el pedido permanece en su estado actual
  Y no se realiza ninguna acción

❌ Historia mal escrita:

"Implementar cancelación de pedidos"
→ Sin perspectiva de usuario
→ Sin criterios de aceptación verificables
→ Sin límites claros del scope
```

---

## INVEST — Criterios de una Buena Historia

```
I — Independent (independiente)
  La historia no depende de otra para completarse
  Si depende → dividir o cambiar el orden

N — Negotiable (negociable)
  Los detalles de implementación son negociables entre PO y equipo
  Solo el valor de negocio es fijo

V — Valuable (valiosa)
  Entrega valor al usuario final o al negocio
  "Crear la tabla en BD" NO es una historia de usuario

E — Estimable (estimable)
  El equipo puede estimarla con información suficiente
  Si no se puede estimar → necesita más claridad o es demasiado grande

S — Small (pequeña)
  Completable en menos de la mitad de un sprint (1 developer, < 4 días)
  Si es más grande → dividir

T — Testable (testeable)
  Los criterios de aceptación son verificables
  "Interfaz amigable" NO es testeable
  "El formulario muestra error si email ya existe" SÍ es testeable
```

---

## Dividir Épicas en Historias

```
Épica (demasiado grande para un sprint):
"Como usuario, quiero gestionar mi cuenta"

Técnicas para dividir:

1. Por flujo de usuario (más común):
   - Registrar nueva cuenta
   - Iniciar sesión con email/password
   - Recuperar contraseña olvidada
   - Actualizar información de perfil
   - Cambiar contraseña estando logueado
   - Eliminar cuenta

2. Por reglas de negocio:
   - Login básico con email/password
   - Login con Google OAuth
   - Login con 2FA opcional
   - Login con 2FA obligatorio (para admins)

3. Por tipos de datos:
   - Actualizar nombre y avatar
   - Actualizar dirección de entrega
   - Actualizar método de pago

4. Por operaciones CRUD:
   - Ver historial de órdenes
   - Buscar y filtrar órdenes
   - Ver detalle de una orden
   - Cancelar una orden

5. Happy path primero, edge cases después:
   - Sprint 1: Login básico funcional (happy path)
   - Sprint 2: Manejo de errores, bloqueo de cuenta, rate limiting
```

---

## Priorización del Backlog

```
Técnica: MoSCoW

Must have:    sin esto no funciona el producto / el sprint no vale
Should have:  importante, afecta significativamente el valor
Could have:   deseable, incluir si hay tiempo
Won't have:   no en este sprint (puede ser en el futuro)

Técnica: RICE Score
R → Reach:   ¿cuántos usuarios afecta? (usuarios/mes)
I → Impact:  ¿cuánto mejora la experiencia? (0.25 / 0.5 / 1 / 2 / 3)
C → Confidence: ¿qué tan seguro estamos? (100% / 80% / 50%)
E → Effort:  ¿cuánto trabajo requiere? (person-months)

RICE = (Reach × Impact × Confidence) / Effort

Ejemplo:
Feature A: (1000 × 2 × 0.8) / 1 = 1600
Feature B: (5000 × 0.5 × 0.5) / 3 = 417
→ Feature A tiene mayor RICE a pesar de alcance menor

Técnica simple para startups: Impacto vs Esfuerzo
Alta impacto + bajo esfuerzo  → Hacer primero (quick wins)
Alta impacto + alto esfuerzo  → Planificar cuidadosamente
Baja impacto + bajo esfuerzo  → Hacer si hay tiempo
Baja impacto + alto esfuerzo  → Eliminar del backlog
```

---

## Épicas, Historias y Tareas — Jerarquía

```
Épica (semanas a meses):
  "Sistema de pagos con Stripe"

  Historia de usuario (días):
  "Como comprador quiero guardar mi tarjeta para futuras compras"

    Tarea técnica (horas):
    - Integrar Stripe Elements en el frontend
    - Endpoint POST /payment-methods en el backend
    - Guardar stripe_customer_id en tabla users
    - Test unitario de PaymentService
    - Test de integración del endpoint

  Historia de usuario:
  "Como comprador quiero pagar con mi tarjeta guardada en un click"

    Tarea técnica:
    - ...

Regla: las tareas técnicas las crea el equipo, no el PO
El PO prioriza historias de usuario, no tareas técnicas
```

---

## Refinement / Grooming — Preparar el Backlog

```
Frecuencia: 1 vez por semana o cada 2 semanas
Duración: 1 hora máxima
Participantes: equipo completo + PO (sin stackeholders externos)

Agenda:
1. Revisar ítems nuevos del backlog (10 min)
   → El PO presenta, el equipo hace preguntas

2. Clarificar criterios de aceptación (20 min)
   → Los que se planean para el próximo sprint

3. Estimar ítems no estimados (20 min)
   → Planning poker o T-shirt sizing

4. Dividir historias grandes (10 min)
   → Las que no caben en un sprint

Output esperado:
- Top 20-30 items del backlog tienen criterios claros
- Top 10-15 items tienen estimación
- Historias épicas están divididas en stories estimables
- El equipo entiende qué viene próximamente

Señal de backlog saludable:
- El planning del lunes tarda < 2 horas porque el backlog está listo
- Raramente hay sorpresas en los criterios de aceptación durante desarrollo
```
