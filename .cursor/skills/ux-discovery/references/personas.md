# Arquetipos y Personas

## Personas vs Arquetipos — La Distinción Importante

```
Persona (tradicional):
  Nombre ficticio, foto de stock, demografía detallada
  Riesgo: el equipo se enfoca en los datos ficticios, no en los patrones reales
  Ventaja: humaniza al usuario, crea empatía

Arquetipo (más robusto):
  Basado en patrones de comportamiento reales del research
  Sin nombre ficticio inventado — el patrón es el protagonista
  Ventaja: más honesto sobre lo que se sabe vs lo que se asume

Recomendación:
  Usar arquetipos como base sólida
  Agregar una persona concreta solo si el equipo necesita humanización adicional
  NUNCA inventar datos demográficos — solo incluir lo que el research reveló
```

---

## Estructura de un Arquetipo Sólido

```
ARQUETIPO: [Nombre que describe el patrón de comportamiento]
Ejemplo: "El Gestor Sobrecargado" / "El Explorador Técnico" / "El Decisor Cauteloso"

CONTEXTO DE USO
- ¿Cuándo y dónde usa el producto?
- ¿Con qué dispositivos y en qué condiciones?
- ¿Solo o en colaboración con otros?

OBJETIVO PRINCIPAL (Jobs to Be Done)
"Cuando [situación], quiero [motivación], para [resultado esperado]"
Ejemplo: "Cuando cierro el mes, quiero tener todos los reportes consolidados,
para presentarlos al directorio sin errores ni retrabajos de último minuto."

FRUSTRACIONES PRINCIPALES (documentadas en research)
- [frustración 1 — cita textual si es posible]
- [frustración 2]
- [frustración 3]

COMPORTAMIENTOS CLAVE
- Cómo hace la tarea actualmente (workarounds)
- Qué herramientas usa hoy
- Cómo mide su propio éxito

NECESIDADES NO SATISFECHAS
- Lo que necesita pero no tiene
- Lo que intenta pero no logra

SEÑALES DE ÉXITO PARA ESTE ARQUETIPO
- ¿Cómo sabe que el diseño funcionó para él/ella?
- ¿Qué cambio de comportamiento esperamos ver?

REPRESENTATIVIDAD
- ¿Qué % de los usuarios reales representa?
- ¿Cuántos usuarios del research corresponden a este patrón?
```

---

## Cuántos Arquetipos Crear

```
1 arquetipo → producto muy específico con usuario homogéneo
              (herramienta interna, SaaS muy vertical)

2-3 arquetipos → mayoría de productos digitales
               → diseñar primero para el arquetipo principal
               → los otros informan edge cases y flujos secundarios

4+ arquetipos → señal de que el product-market fit no está claro
               → el producto intenta servir a demasiados usuarios diferentes
               → o el research no se sintetizó bien

REGLA: El arquetipo principal es el que guía las decisiones difíciles.
Cuando hay un trade-off de diseño, preguntar:
"¿Qué haría el Arquetipo Principal?"
```

---

## Jobs to Be Done — La Herramienta Más Poderosa

```
JTBD revela la motivación real, no la feature que piden.

Formato:
"Cuando [situación que dispara la necesidad],
quiero [el progreso que buscan],
para [el resultado que valoran]"

Nivel funcional:
"Cuando necesito generar una factura, quiero hacerlo en 2 minutos,
para no perder tiempo en tareas administrativas."

Nivel emocional:
"Cuando envío una factura a un cliente importante,
quiero sentirme seguro de que es profesional y correcta,
para proyectar credibilidad."

Nivel social:
"Cuando mi equipo revisa el trabajo,
quiero que el proceso sea transparente,
para que confíen en mis decisiones."

Los 3 niveles importan. Los diseñadores solo trabajan el funcional.
El emocional y social son donde vive el valor real del producto.

JTBD revela competidores reales:
Si el JTBD es "organizar mis finanzas del mes" →
el competidor es una hoja de Excel, no otro software de finanzas.
Diseñar contra Excel es diferente que diseñar contra un SaaS.
```

---

## Plantilla portable — Persona sin imagen externa (ASCII)

```
Cuando el usuario describe un contexto sin proporcionar imagen/URL,
generar esta ficha ASCII en markdown (no depender de herramientas
de visualización externas al editor):

┌─────────────────────────────────────────────────┐
│  NOMBRE DEL ARQUETIPO (sin foto de stock)       │
│  "Descripción del patrón en una línea"          │
├────────────────┬────────────────────────────────┤
│  CONTEXTO      │  JOB TO BE DONE                │
│  ──────────    │  ──────────────                │
│  • cuándo      │  Cuando [situación]             │
│  • dónde       │  Quiero [motivación]            │
│  • con qué     │  Para [resultado]               │
├────────────────┼────────────────────────────────┤
│  FRUSTRACIONES │  COMPORTAMIENTOS               │
│  😤 [cita real]│  → workaround 1                │
│  😤 [cita real]│  → herramientas que usa         │
├────────────────┴────────────────────────────────┤
│  SEÑALES DE ÉXITO                               │
│  ✓ Comportamiento esperado post-diseño          │
└─────────────────────────────────────────────────┘

Uso: "genera el arquetipo para [descripción del usuario]"
```

---

## Validar vs Inventar Arquetipos

```
Señales de arquetipo VÁLIDO (basado en research):
✅ Los comportamientos son observados, no asumidos
✅ Las frustraciones tienen citas textuales del research
✅ El JTBD es específico al contexto del producto
✅ El equipo reconoce a usuarios reales en la descripción
✅ Los workarounds son concretos ("usa WhatsApp para coordinar")

Señales de arquetipo INVÁLIDO (inventado):
❌ Datos demográficos muy específicos sin research ("37 años, casada, 2 hijos")
❌ Frustraciones genéricas ("quiere que sea más fácil de usar")
❌ JTBD que parece una feature request ("quiere un dashboard de métricas")
❌ El equipo no reconoce ningún usuario real en la descripción
❌ Los workarounds son hipotéticos ("probablemente usa Excel")

Test de validación:
Mostrar el arquetipo a 3 usuarios reales del research.
Si dicen "me reconozco en esto" → válido.
Si dicen "no sé quién es esta persona" → volver al research.
```
