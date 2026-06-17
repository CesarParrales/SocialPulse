# Information Architecture

## Qué es IA y Por Qué Importa

```
Information Architecture (IA) = la estructura que organiza el contenido
y las funcionalidades de un producto para que los usuarios las encuentren.

La IA responde a:
→ ¿Qué categorías existen en el sistema?
→ ¿Cómo se relacionan entre sí?
→ ¿Qué vocabulario usa el usuario para nombrar las cosas?
→ ¿Qué busca el usuario y dónde espera encontrarlo?

Una IA mala:
→ El usuario no sabe dónde está algo
→ El usuario no sabe cómo llamar a algo
→ El mismo concepto aparece en varios lugares con nombres distintos
→ La navegación refleja la estructura interna de la empresa, no el modelo mental del usuario

Una IA buena:
→ El usuario puede predecir dónde está algo sin haberlo visto antes
→ El vocabulario del sistema coincide con el vocabulario del usuario
→ La profundidad de navegación es razonable (3 clicks para cualquier cosa)
→ Las categorías son mutuamente excluyentes y exhaustivas (MECE)
```

---

## Los Tres Problemas Clásicos de IA

```
PROBLEMA 1 — La estructura refleja la organización interna, no el usuario

Ejemplo real:
Menú: Productos > Catálogo > Categorías > [subcategoría] > Artículo
El usuario piensa: "quiero comprar zapatillas de running"
No piensa en "catálogo" ni en "categorías" — esos son términos internos

Solución:
→ Organizar desde el modelo mental del usuario (card sorting revela esto)
→ Usar el vocabulario que usa el usuario en los labels de navegación
→ Las categorías deben ser significativas para el usuario, no para el negocio

PROBLEMA 2 — Demasiada profundidad

Estructura de 5 niveles:
Home > Sección > Categoría > Subcategoría > Item > Detalle

Problema: el usuario se pierde y no sabe volver
Solución:
→ Máximo 3-4 niveles en la mayoría de productos
→ Breadcrumbs cuando hay más profundidad
→ Búsqueda como atajo cuando la jerarquía es muy profunda

PROBLEMA 3 — Categorías que se solapan

Ejemplo:
"Noticias" / "Blog" / "Artículos" / "Novedades"
¿Dónde está el comunicado de prensa? ¿En Noticias o en Blog?

Solución:
→ Cada categoría debe ser MECE: Mutuamente Exclusiva, Colectivamente Exhaustiva
→ Un ítem de contenido solo puede vivir en UN lugar
→ Si hay duda → el usuario tiene duda → redefinir las categorías
```

---

## Card Sorting — Descubrir el Modelo Mental del Usuario

```
Card Sorting = técnica para descubrir cómo el usuario agrupa conceptos.

Proceso:
1. Crear tarjetas con los conceptos del sistema (funciones, contenidos, pantallas)
2. Pedir a 5-10 usuarios que las agrupen como les parezca lógico
3. Pedir que nombren los grupos creados
4. Analizar los patrones: ¿qué siempre va junto? ¿qué divide al grupo?

Tipos:
Open card sort:
  El usuario crea los grupos y los nombra
  → Revela cómo estructura el usuario el espacio conceptual
  → Mejor para arquitecturas nuevas sin estructura previa

Closed card sort:
  Se dan las categorías predefinidas y el usuario asigna tarjetas
  → Valida si la estructura propuesta es intuitiva
  → Mejor para evaluar una arquitectura existente

Herramientas:
  Óptima (optimalworkshop.com) → online, análisis automático
  Maze → integrado con Figma
  Physical cards → en persona, más conversacional

Analizar los resultados:
→ Grupos que aparecen en > 70% de participantes → categoría obvia
→ Tarjetas que siempre se agrupan juntas → van en la misma categoría
→ Tarjetas que siempre se separan → no pertenecen a la misma categoría
→ Nombres de grupos que se repiten → usar ese vocabulario en la UI
```

---

## Tree Testing — Validar la Navegación Sin Diseño Visual

```
Tree Testing = verificar si los usuarios pueden encontrar cosas
en la estructura de navegación propuesta, sin diseño visual.

Por qué es poderoso:
→ Se valida la estructura antes de invertir en diseño
→ Elimina el ruido visual (el usuario reacciona a la estructura, no al color)
→ Identifica exactamente dónde se pierden los usuarios

Proceso:
1. Crear el árbol de navegación en texto plano:
   Home
   ├── Mis pedidos
   │   ├── Pedidos activos
   │   ├── Historial
   │   └── Cancelar pedido
   ├── Mi cuenta
   │   ├── Datos personales
   │   └── Direcciones
   └── Soporte

2. Definir tareas: "Encuentra dónde ver el estado de un pedido que hiciste ayer"

3. El usuario navega el árbol de texto buscando la respuesta

4. Medir: ¿encontraron el item correcto? ¿cuántos clicks tomó? ¿dónde se desviaron?

Métricas:
→ Directness: % de usuarios que fueron directo al destino correcto
→ Success: % de usuarios que llegaron al destino correcto (aunque no directo)
→ First click: dónde hace clic el usuario primero (revela el modelo mental)

Herramientas:
→ Óptima TreeJack (mejor para tree testing)
→ Maze
→ UserZoom

Umbral de calidad:
→ Success rate > 80% → la arquitectura funciona para esa tarea
→ Success rate < 60% → reestructurar esa área antes de diseñar
```

---

## Principios de IA para Productos Digitales

```
1. Organización orientada al usuario (no al negocio)
   Labels que usa el usuario, no jerga interna
   Categorías que coinciden con el modelo mental del usuario

2. Consistencia
   Si "Pedidos" está en el menú principal, no llamarlo "Órdenes" en otro lugar
   Mismo patrón de navegación en todo el sistema

3. Jerarquía visible
   El usuario siempre sabe dónde está (breadcrumbs, highlights en nav)
   Puede volver al nivel anterior con un click

4. Múltiples formas de encontrar algo
   Navegación + búsqueda + filtros + accesos directos
   El usuario con modelos mentales diferentes llega al mismo lugar

5. Mínima carga cognitiva
   No mostrar todo al mismo tiempo
   Progresividad: mostrar más detalles cuando el usuario los necesita

6. Robustez (para diferentes contextos)
   ¿Funciona la IA con 5 items? ¿Con 500 items?
   ¿Funciona en mobile con el espacio reducido?
   ¿Funciona cuando el usuario no está logueado?
```

---

## Plantilla portable — IA sin imagen externa

```
Sin referencia externa, generar estos artefactos en markdown
(ASCII, tablas, o Mermaid graph; no depender de herramientas
de visualización externas al editor):

Diagrama de IA (ASCII):
┌─────────────────────────────────────────────────────────┐
│                    [Nombre del producto]                 │
│                         HOME                            │
├──────────┬──────────┬──────────┬────────────────────────┤
│ Sección A│ Sección B│ Sección C│       Sección D        │
├──────────┼──────────┼──────────┼────────────────────────┤
│ • Item 1 │ • Item 1 │ • Item 1 │ • Item 1               │
│ • Item 2 │ • Item 2 │ • Item 2 │ • Item 2               │
│ • Item 3 │          │ • Item 3 │ • Item 3               │
│          │          │   • Sub 1│                        │
│          │          │   • Sub 2│                        │
└──────────┴──────────┴──────────┴────────────────────────┘

Tabla de análisis MECE:
┌────────────────┬──────────────┬──────────────────────────┐
│   Categoría    │  Qué incluye │  Posibles solapamientos  │
├────────────────┼──────────────┼──────────────────────────┤
│ [Categoría A]  │ [contenido]  │ [con qué categoría]      │
│ [Categoría B]  │ [contenido]  │ [con qué categoría]      │
└────────────────┴──────────────┴──────────────────────────┘

Uso: "genera la IA para [descripción del producto con sus secciones principales]"
```
