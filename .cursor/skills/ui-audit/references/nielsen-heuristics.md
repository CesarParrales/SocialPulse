# Heurísticas de Nielsen — Los 10 Principios de Usabilidad

Las 10 heurísticas de Jakob Nielsen son el estándar de evaluación de usabilidad
desde 1994 y siguen siendo la herramienta más usada en la industria.
No son reglas absolutas — son principios para guiar la evaluación.

---

## H1 — Visibilidad del Estado del Sistema

```
El sistema siempre debe mantener al usuario informado sobre lo que está ocurriendo,
con feedback apropiado y en tiempo razonable.

Qué evaluar:
→ ¿Los botones muestran estado de carga (spinner, texto "Cargando...")?
→ ¿Se confirma que una acción se completó ("Guardado", checkmark)?
→ ¿El usuario sabe en qué paso está en procesos de múltiples pasos?
→ ¿Los procesos largos tienen indicador de progreso?
→ ¿El elemento activo/seleccionado está visualmente destacado?

Ejemplos de violación:
❌ Botón "Guardar" sin feedback → el usuario hace clic varias veces pensando que falló
❌ Formulario enviado sin confirmación → el usuario no sabe si funcionó
❌ Proceso de carga sin indicador → el usuario cree que colgó

Ejemplos de cumplimiento:
✅ Toast "Cambios guardados" que aparece al guardar
✅ Barra de progreso en wizard de 4 pasos
✅ Ítem del menú activo con background destacado
✅ Botón deshabilitado + spinner durante la llamada a la API
```

---

## H2 — Correspondencia con el Mundo Real

```
El sistema debe hablar el lenguaje del usuario: palabras, frases y conceptos
familiares para el usuario, no la jerga técnica del sistema.

Qué evaluar:
→ ¿Los labels usan vocabulario del usuario (no del sistema)?
→ ¿Los iconos representan conceptos familiares?
→ ¿Los mensajes de error son en lenguaje humano?
→ ¿Las metáforas visuales son reconocibles?
→ ¿El flujo sigue el orden lógico del mundo real?

Ejemplos de violación:
❌ "Error 403 — Forbidden" (usuario no sabe qué hacer)
❌ "Nulo" o "undefined" en mensajes de error
❌ Ícono de disquete para "Guardar" (nadie sabe qué es un disquete ya)
❌ "Seleccionar entidad de usuario" en lugar de "Seleccionar cliente"

Ejemplos de cumplimiento:
✅ "No tienes permiso para ver esta página. Contacta a tu administrador."
✅ Ícono de nube con flecha hacia arriba para "Subir archivo"
✅ Proceso de compra en orden: carrito → datos → pago → confirmación
```

---

## H3 — Control y Libertad del Usuario

```
Los usuarios cometen errores y necesitan salidas de emergencia claramente marcadas
para dejar estados no deseados sin pasar por diálogos extensos.

Qué evaluar:
→ ¿Hay un botón "Cancelar" en todos los formularios y flujos?
→ ¿Las acciones destructivas se pueden deshacer?
→ ¿Los modales se pueden cerrar con Esc y tocando fuera?
→ ¿El back button funciona de forma predecible?
→ ¿El usuario puede salir de un proceso sin perder lo avanzado?

Ejemplos de violación:
❌ Formulario largo sin botón "Cancelar" o forma de salir
❌ Eliminar permanentemente sin opción de deshacer (ni siquiera 5 segundos)
❌ Modal que no se cierra con Esc
❌ Wizard que no guarda el progreso si el usuario vuelve atrás

Ejemplos de cumplimiento:
✅ "Deshacer" con timeout de 5-10 segundos para eliminaciones
✅ "Guardar borrador" en formularios largos
✅ Cierre con Esc, clic fuera del modal, botón X visible
✅ Back button que regresa al estado anterior sin perder datos
```

---

## H4 — Consistencia y Estándares

```
Los usuarios no deberían preguntarse si diferentes palabras, situaciones o acciones
significan lo mismo. Seguir las convenciones de la plataforma.

Qué evaluar:
→ ¿El mismo botón tiene el mismo label en todo el sistema?
→ ¿Las acciones similares están en los mismos lugares?
→ ¿Los patrones de navegación son consistentes en todas las secciones?
→ ¿Se siguen las convenciones de la plataforma (iOS/Android/Web)?
→ ¿Los iconos siempre representan lo mismo?

Ejemplos de violación:
❌ "Guardar" en una pantalla y "Actualizar" en otra para la misma acción
❌ El botón primario está a la derecha en algunos dialogs y a la izquierda en otros
❌ Diferente patrones de navegación en la misma app
❌ El ícono de campana significa notificaciones en un lugar y alertas en otro

Ejemplos de cumplimiento:
✅ Todos los botones de acción destructiva son rojo con texto blanco
✅ La navegación principal está siempre en el mismo lugar
✅ Los modales de confirmación siempre tienen el mismo estructura
```

---

## H5 — Prevención de Errores

```
Mejor que buenos mensajes de error es un diseño cuidadoso que evita que
los problemas ocurran en primer lugar.

Qué evaluar:
→ ¿Los formularios validan en tiempo real (no solo al enviar)?
→ ¿Las acciones destructivas piden confirmación?
→ ¿Los campos tienen ejemplos o formato de ayuda?
→ ¿Los estados inválidos son visualmente claros antes de intentar?
→ ¿Se deshabilitan opciones que no están disponibles (vs mostrar error después)?

Ejemplos de violación:
❌ Validar solo al enviar el formulario → usuario pierde lo que escribió
❌ "Eliminar cuenta" sin confirmación con texto a escribir
❌ Campo de fecha sin hint del formato esperado → siempre hay errores
❌ Botón activo cuando la acción no es posible → error al hacer clic

Ejemplos de cumplimiento:
✅ Validación inline al perder el foco (onBlur)
✅ "Para confirmar, escribe DELETE" en eliminaciones permanentes
✅ Hint text: "Ej: 15/01/2024" bajo el campo de fecha
✅ Botón deshabilitado con tooltip explicando por qué no está disponible
```

---

## H6 — Reconocer en Lugar de Recordar

```
Minimizar la carga de memoria del usuario haciendo visibles objetos, acciones
y opciones. El usuario no debería tener que recordar información entre pantallas.

Qué evaluar:
→ ¿Las opciones son visibles en lugar de requerir que el usuario las recuerde?
→ ¿Hay breadcrumbs o indicadores de dónde está el usuario?
→ ¿Los formularios muestran lo que se eligió en pasos anteriores?
→ ¿Los filtros activos son visibles y removibles?
→ ¿Hay historial o recientes para elementos frecuentes?

Ejemplos de violación:
❌ Wizard de 5 pasos que no muestra un resumen de los pasos anteriores
❌ Búsqueda que no muestra el término buscado en los resultados
❌ Filtros aplicados sin indicación visual de cuáles están activos
❌ Autocompletado que requiere escribir el término exacto (sin variantes)

Ejemplos de cumplimiento:
✅ Resumen del carrito visible durante todo el checkout
✅ "Buscando: 'zapatillas running'" en la página de resultados
✅ Tags de filtros activos con botón X para remover cada uno
✅ "Visto recientemente" en la búsqueda de productos
```

---

## H7 — Flexibilidad y Eficiencia

```
Los aceleradores — invisibles para el usuario novato — permiten al usuario experto
interactuar más rápido. El sistema debe ser útil para ambos perfiles.

Qué evaluar:
→ ¿Hay atajos de teclado para acciones frecuentes?
→ ¿Las acciones comunes son accesibles en pocos pasos para usuarios avanzados?
→ ¿Hay shortcuts contextuales (clic derecho, gestos en mobile)?
→ ¿Los campos frecuentes tienen autocompletado?
→ ¿El usuario puede personalizar su experiencia?

Ejemplos de violación:
❌ App sin atajos de teclado para crear nuevo item (Cmd+N)
❌ Acción frecuente enterrada a 4 clicks de profundidad
❌ Sin gestos de swipe en mobile para acciones comunes

Ejemplos de cumplimiento:
✅ Cmd+K para búsqueda global
✅ Swipe para eliminar en listas de mobile
✅ "Últimas búsquedas" en campo de búsqueda
✅ Template predefinido para crear items frecuentes
```

---

## H8 — Diseño Estético y Minimalista

```
Los diálogos no deben contener información irrelevante o raramente necesaria.
Cada unidad extra de información compite con la información relevante.

Qué evaluar:
→ ¿Cada elemento en pantalla tiene un propósito claro?
→ ¿Hay texto, botones o elementos que nadie usa?
→ ¿La jerarquía visual guía al usuario a la acción principal?
→ ¿Los formularios solo piden información necesaria?
→ ¿Hay ruido visual que compite con el contenido?

Ejemplos de violación:
❌ Formulario con 20 campos cuando solo 5 son necesarios
❌ 4 botones de acción del mismo peso visual → el usuario no sabe cuál
❌ Texto legal en la pantalla principal que podría estar en un modal
❌ Dashboard con 15 widgets todos del mismo tamaño

Ejemplos de cumplimiento:
✅ Un botón primario claro por pantalla o sección
✅ Progressive disclosure: mostrar opciones avanzadas solo cuando se necesitan
✅ "Registrarse" con solo email y contraseña → datos adicionales después
```

---

## H9 — Ayuda al Usuario a Reconocer, Diagnosticar y Recuperarse de Errores

```
Los mensajes de error deben expresarse en lenguaje simple, indicar con precisión
el problema y sugerir una solución constructivamente.

Qué evaluar:
→ ¿Los errores tienen mensajes claros en lenguaje humano?
→ ¿Los errores indican específicamente qué falló?
→ ¿Los errores sugieren cómo resolver el problema?
→ ¿Los errores de formulario están junto al campo que falló?
→ ¿Los errores de sistema tienen una acción alternativa?

Ejemplos de violación:
❌ "Error al procesar la solicitud" sin más información
❌ Error al final del formulario sin indicar cuál campo
❌ "Inténtelo de nuevo" sin saber cuándo o cómo
❌ Error 500 sin opción de volver o contactar soporte

Ejemplos de cumplimiento:
✅ "El email ya está registrado. ¿Olvidaste tu contraseña?" con link
✅ Error inline bajo el campo con el label específico en rojo
✅ "No pudimos procesar el pago. Verifica los datos o usa otra tarjeta."
✅ Página de error con "Volver al inicio" y "Contactar soporte"
```

---

## H10 — Ayuda y Documentación

```
Aunque es mejor si el sistema no necesita explicación, puede ser necesario
proveer ayuda que sea fácil de encontrar y orientada a la tarea del usuario.

Qué evaluar:
→ ¿La ayuda está disponible contextualmente (donde se necesita)?
→ ¿Los tooltips explican funcionalidades no obvias?
→ ¿Hay onboarding para funcionalidades complejas?
→ ¿Los mensajes de ayuda están orientados a la tarea (no al sistema)?
→ ¿El vacío de conocimiento del usuario es real o el diseño es el problema?

Ejemplos de violación:
❌ Manual de usuario de 50 páginas porque el sistema es confuso
❌ Tooltip que dice "Haga clic aquí" sin explicar para qué
❌ Sin onboarding para una funcionalidad compleja

Ejemplos de cumplimiento:
✅ Tooltip contextual: "Activa esto para recibir alertas cuando un pedido cambie de estado"
✅ Tour de onboarding al usar una función por primera vez
✅ Empty state que explica para qué sirve la sección y cómo empezar

Regla: si se necesita mucha ayuda → primero verificar si el diseño puede mejorar
La documentación no reemplaza un buen diseño, lo complementa.
```

---

## Tabla de Evaluación Heurística — Template

```
Formato para documentar hallazgos heurísticos:

| # | Heurística | Pantalla | Hallazgo | Severidad |
|---|-----------|---------|---------|-----------|
| 1 | H1 Visibilidad | Checkout - Paso 2 | El botón "Continuar" no muestra estado de carga al hacer clic | 🟠 Serio |
| 2 | H4 Consistencia | Todo el sistema | "Guardar" en perfil, "Actualizar" en configuración, "Aplicar" en filtros | 🟡 Moderado |
| 3 | H9 Errores | Login | Mensaje "Credenciales inválidas" sin sugerir recuperar contraseña | 🟠 Serio |
| 4 | H5 Prevención | Crear pedido | Sin confirmación antes de eliminar pedido en progreso | 🔴 Crítico |

Generación sin captura:
"genera la tabla de evaluación heurística para [pantalla/flujo específico]"
→ Generar la tabla markdown con hallazgos inferidos del contexto,
  marcando la severidad como [NO VERIFICADO] si no hubo evidencia visual
→ Cada hallazgo tiene severidad, pantalla, heurística y recomendación
```
