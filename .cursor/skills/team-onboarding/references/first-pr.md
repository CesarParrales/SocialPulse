# El Primer PR — Guiado y Educativo

## Por Qué el Primer PR Importa Tanto

```
El primer PR no es solo código — es la primera interacción real del dev
con el proceso del equipo, con el estándar de calidad, y con la cultura
de code review del estudio.

Un primer PR bien guiado:
→ El dev nuevo aprende el estándar sin regañadas
→ El equipo aprende cómo el dev nuevo piensa y escribe código
→ Se establece la dinámica de code review desde el primer momento
→ El dev nuevo tiene una victoria real en los primeros días

Un primer PR mal manejado:
→ El dev nuevo siente que "no cumple con lo que esperan"
→ El code review se convierte en una lista de errores abrumadora
→ El dev nuevo aprende a tener miedo de abrir PRs
→ La dinámica del equipo comienza con el pie equivocado
```

---

## Preparar el Primer Task para el Éxito

```
El primer task debe estar preparado para guiar, no para evaluar.

CRITERIOS DEL TASK:

Tamaño:
  → Máximo 2-3 días de trabajo
  → Resultados en 1-3 archivos modificados (no 20)
  → Un solo módulo del sistema (no cross-cutting concerns)

Claridad:
  → Criterios de aceptación específicos y verificables
  → El dev puede saber cuándo está "listo" sin preguntar
  → Si hay ambigüedad → resolverla antes de asignar el task

Valor educativo:
  → Que pase por el ciclo completo: model/service/controller/test
  → Que use los patrones centrales del proyecto (no un edge case)
  → Que sea en una parte del código bien escrita (no en el legacy problemático)

Ejemplo de task bien preparado:
---
Task: Agregar campo "prioridad" a los pedidos

Descripción:
  Agregar un campo opcional "prioridad" (normal/alta/urgente) al formulario
  de creación de pedidos.

Criterios de aceptación:
  1. El campo aparece en el formulario de creación de pedidos
  2. Los valores posibles son: normal (default), alta, urgente
  3. El campo se guarda en la tabla orders
  4. El campo se muestra en la vista de detalle del pedido
  5. El listado de pedidos permite filtrar por prioridad
  6. Hay un test de feature para el nuevo campo

Archivos probablemente afectados:
  - database/migrations/ (nuevo campo)
  - app/Models/Order.php (nuevo campo en $fillable)
  - app/Http/Requests/CreateOrderRequest.php (validación)
  - app/Services/OrderService.php (si tiene lógica)
  - resources/js/components/OrderForm (frontend)
  - tests/Feature/OrderTest.php

Notas:
  El enum de prioridades está definido en app/Enums/OrderPriority.php
  (si no existe, créalo siguiendo el patrón de OrderStatus.php)
---
```

---

## Durante el Desarrollo — El Rol del Dev Buddy

```
El dev buddy no hace el trabajo por el dev nuevo.
El dev buddy elimina los bloqueos que no son parte del aprendizaje.

INTERVENCIÓN APROPIADA:
→ "El setup de tu máquina tiene un problema con X. La solución es Y."
   (Bloqueo no educativo — el dev nuevo no aprende nada de esto)

→ "¿Ya leíste cómo funcionan los Enums en Laravel? Hay un ejemplo en OrderStatus.php"
   (Orientación sin dar la respuesta — el dev nuevo aprende)

INTERVENCIÓN INAPROPIADA:
→ "Hazlo así:" + escribe el código
   (El dev nuevo no aprende y no tiene contexto de por qué)

→ "Ese enfoque está mal" sin explicar por qué
   (Desmotiva sin enseñar)

SEÑALES DE QUE EL DEV NUEVO NECESITA AYUDA:
→ Lleva más de 2 horas sin avance visible (y no está leyendo/investigando)
→ La misma pregunta aparece 3 veces (indica que no entendió la respuesta)
→ El código que está escribiendo va en dirección completamente opuesta a los patrones del proyecto

Check-in diario (15 minutos, fin del día):
  "¿Cómo estuvo el día?"
  "¿Qué avanzaste?"
  "¿Hay algo que te tenga bloqueado para mañana?"
  Resolver solo los bloqueos que impiden avanzar al día siguiente.
```

---

## Abrir el Primer PR — Antes del Code Review

```
Antes de que el dev nuevo pida review, el dev buddy revisa brevemente:

VERIFICACIÓN RÁPIDA (no es code review completo):
□ El código compila sin errores
□ Los tests pasan localmente (o el dev nuevo documentó por qué no)
□ No hay console.log / dd() / dump() olvidados
□ La descripción del PR tiene al menos 2 oraciones sobre qué hace

Si algo falla → el dev buddy explica qué buscar, no cómo arreglarlo.

DESCRIPCIÓN DEL PRIMER PR:
Ayudar al dev nuevo a escribir una descripción decente:

"Para el primer PR, la descripción debe tener:
 - Qué cambiaste (funcionalidad nueva / bug fix / refactor)
 - Cómo lo puedes probar tú mismo
 - Si hay algo en lo que quieres feedback específico

Ejemplo:
'Agrego el campo prioridad al formulario de pedidos.
Para probar: crear un pedido con prioridad Alta y verificar que aparece en el detalle.
Duda: ¿el enum debería estar en el modelo o en un archivo separado?'"
```

---

## El Code Review del Primer PR — Tono y Contenido

```
El code review del primer PR establece el estándar del equipo.
El tono importa tanto como el contenido.

PRINCIPIOS DEL CODE REVIEW EDUCATIVO:

1. Más preguntas, menos afirmaciones
   ❌ "Esto está mal, cámbialo así"
   ✅ "¿Consideraste manejar el caso en que el pedido ya tiene prioridad?
       ¿Qué pasaría si se llama a este método con un valor nulo?"

2. Explicar el por qué
   ❌ "El Service no debería tener lógica de presentación"
   ✅ "El Service no debería tener lógica de presentación porque si el día de mañana
       agregamos una API, el Service se reutiliza pero la presentación cambia.
       La transformación de datos para la respuesta va en el Resource."

3. Reconocer lo bueno
   Un code review sin reconocimiento positivo es un interrogatorio.
   Mencionar algo específico que está bien hecho.
   "El test está muy bien escrito — cubre los tres estados del enum."

4. Priorizar por impacto
   En el primer PR, no todos los comentarios tienen el mismo peso.
   Usar labels:
   [must] → Cambiar antes de mergear (bug, seguridad, falla el criterio de aceptación)
   [suggestion] → Buena práctica, pero no bloquea el merge
   [nit] → Preferencia de estilo, no crítica

5. Limitar los comentarios en el primer PR
   Si hay 15 comentarios en el primer PR, el dev nuevo se siente abrumado.
   Priorizar los 3-5 más importantes para el merge.
   Los demás: guardarlos para el siguiente PR o convertirlos en tasks de mejora.
```

---

## Después del Primer PR — La Retrospectiva de Onboarding

```
Al terminar la primera semana o después del primer PR mergeado,
una conversación corta (30 min) con el dev nuevo:

Preguntas para el dev nuevo:
  "¿Qué fue lo más confuso de la primera semana?"
  "¿Hubo algo que el README no explicaba y tardaste mucho en resolver?"
  "¿El task era del tamaño correcto?"
  "¿Qué le agregarías a la documentación para el próximo dev que entre?"

Por qué importa:
→ El dev nuevo tiene la perspectiva más fresca sobre lo que falta
→ Sus respuestas mejoran el onboarding del siguiente dev
→ Es una señal de que el estudio se preocupa por el proceso

Output de la retrospectiva:
→ Issues en GitHub / Linear con las mejoras al README
→ Los gotchas no documentados que encontró
→ El README actualizado con lo que faltaba

Esta conversación también le dice al dev nuevo que su perspectiva importa
y que el estudio mejora continuamente — una señal cultural importante.
```

---

## El Dev Nuevo Como Auditor del README

```
Una práctica que mejora el onboarding de forma sostenida:

Instrucción explícita al dev nuevo el día 1:
"Durante tu primera semana, cada vez que encuentres algo confuso
que no está en el README, agrega un comentario en el issue de onboarding.
Al final de la semana, convertimos esos comentarios en mejoras al README."

Esto:
→ Le da al dev nuevo un rol activo desde el día 1 (no solo "aprender")
→ Captura el conocimiento mientras aún es fresco para quien lo necesita
→ Hace que el README mejore con cada nuevo dev que entra
→ El dev nuevo entiende que documentar es parte del trabajo, no extra

Después de 3-4 onboardings con este proceso, el README se vuelve
lo suficientemente bueno para que el setup tome 2 horas en lugar de 2 días.
```
