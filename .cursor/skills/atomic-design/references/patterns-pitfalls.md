# Errores Comunes y Cuándo Adaptar Atomic Design

## Los 5 Errores Más Frecuentes

### Error 1 — Obsesión con la categorización correcta

```
El error:
"¿Es esto un átomo o una molécula?"
→ El equipo pasa 20 minutos debatiendo la categoría
→ Se paraliza el desarrollo por purismo metodológico

La realidad:
Los límites entre niveles son difusos y contextuales.
Un Tooltip puede ser átomo en un contexto y molécula en otro.
La metodología es un modelo mental, no una ley física.

Solución:
→ Si no estás seguro del nivel, pon el componente donde tenga sentido
→ El criterio práctico: ¿es reutilizable sin conocer el dominio? → components/
→ ¿Conoce el dominio del negocio? → features/
→ Con el tiempo el equipo desarrolla intuición sin necesitar el debate
```

### Error 2 — Átomos con lógica de negocio

```
El error:
// ❌ Un "átomo" que hace fetching
function UserAvatar({ userId }) {
  const { data: user } = useUser(userId);  // fetching en un átomo!
  return <Avatar src={user?.avatar} alt={user?.name} />;
}

Por qué es un problema:
→ No es reutilizable (siempre necesita el contexto del User)
→ Es difícil de testear sin mockar la API
→ No puede usarse en Storybook sin providers complejos
→ Viola el principio de separación de responsabilidades

Solución:
→ El átomo solo recibe props: <Avatar src={} alt={} />
→ La lógica de fetching sube al organismo o a la página
→ Si necesitas el comportamiento "fetching de usuario", crea un componente
   específico en la feature: <UserAvatarConnected userId={} />
   que internamente usa el átomo Avatar
```

### Error 3 — Organismos demasiado grandes

```
El error:
// ❌ Un "organismo" que hace demasiado
function ProductPage() {
  // fetching de 5 queries diferentes
  // 400 líneas de JSX
  // lógica de carrito, reviews, imágenes, relacionados, etc.
  return (/* todo el producto en un solo componente */);
}

Señales de que un organismo es demasiado grande:
→ Tiene más de 150-200 líneas de JSX
→ Maneja más de 3-4 responsabilidades distintas
→ Los tests son complejos porque mockea muchas cosas
→ El equipo tiene miedo de tocarlo porque "rompe todo"

Solución:
→ Dividir en organismos más pequeños por sección/responsabilidad:
   ProductImages, ProductInfo, ProductPricing, RelatedProducts
→ Cada organismo maneja su propia sección de la UI
→ La página o el template los compone
```

### Error 4 — Templates que hacen fetching

```
El error:
// ❌ Un template que conecta con datos
function DashboardTemplate() {
  const { user } = useCurrentUser();   // ← mal
  const { stats } = useDashboardStats(); // ← mal
  return (/* usa los datos directamente */);
}

Por qué es un problema:
→ No es reutilizable para diferentes contextos
→ En Storybook necesita providers reales
→ Mezcla la estructura del layout con los datos

Solución:
→ El template recibe todo por props o children:
function DashboardTemplate({ header, sidebar, content }) {
  return (/* solo estructura */);
}
→ La página inyecta los organismos con sus datos:
function DashboardPage() {
  return (
    <DashboardTemplate
      header={<AppHeader />}
      sidebar={<AppSidebar />}
      content={<DashboardStats />}
    />
  );
}
```

### Error 5 — Ignorar el nivel de "Página"

```
El error:
// Tratando la pantalla principal como un organismo complejo
// en lugar de una página que compone organismos

// ❌ Organismo que es realmente una página
function OrdersSection() {
  const { data } = useOrders();
  const { user } = useUser();
  // renderiza header, sidebar, tabla, paginación, filtros...
  // todo en uno
}

Por qué importa la distinción:
→ Las páginas son las únicas que deben manejar la lógica de routing
→ Las páginas son las que conectan con el router (params, navegación)
→ Los organismos no deben depender del routing

Solución:
→ Mantener la capa de página aunque sea "solo un wrapper"
→ La página es el punto de entrada del router
→ La página se encarga de: obtener params de URL, hacer redirect, manejar auth
→ Los organismos solo reciben datos, no leen la URL
```

---

## Cuándo Adaptar la Metodología

```
Atomic Design es una guía, no un dogma.
Hay contextos donde la estructura estricta no aplica:

PROYECTOS MUY PEQUEÑOS (< 20 componentes):
  La jerarquía de 5 niveles es overhead innecesario.
  → Usar solo: ui/ (primitivos) + components/ (compuestos) + pages/

APLICACIONES MÓVILES CON MUCHOS GESTOS:
  Los organismos interactúan entre sí de formas complejas.
  → La separación estricta puede dificultar la coordinación de animaciones.
  → Adaptar: organismos pueden conocer a sus organismos hermanos.

DASHBOARDS ALTAMENTE DINÁMICOS:
  Los layouts cambian según el rol y las preferencias del usuario.
  → Los templates se vuelven demasiado rígidos.
  → Adaptar: usar un sistema de layout dinámico en vez de templates fijos.

DESIGN SYSTEM COMPARTIDO ENTRE MÚLTIPLES APPS:
  Los átomos y moléculas van en un paquete separado.
  Los organismos y templates quedan en cada app.
  → La jerarquía se distribuye entre repositorios.

LA REGLA FINAL:
Si la metodología te ayuda a organizar → úsala.
Si te genera más debates que componentes → simplifícala.
El objetivo es la consistencia y la velocidad del equipo,
no la pureza de la metodología.
```

---

## Refactoring hacia Atomic Design

```
Para proyectos existentes sin estructura atómica:

FASE 1 — Inventario (no tocar código)
→ Listar todos los componentes existentes
→ Categorizarlos tentativamente por nivel
→ Identificar duplicados y variantes del mismo componente

FASE 2 — Extraer átomos
→ Identificar los elementos más básicos (Button, Input, etc.)
→ Crear versiones genéricas y reemplazar los específicos
→ Los componentes existentes siguen funcionando — no romper nada

FASE 3 — Identificar moléculas
→ Buscar combinaciones que se repiten en múltiples organismos
→ Extraerlas y documentarlas
→ No crear moléculas que solo se usan en un lugar (YAGNI)

FASE 4 — Reorganizar organismos
→ Mover los organismos de dominio a sus features correspondientes
→ Los organismos genéricos a shared/organisms/

FASE 5 — Crear templates (si aplica)
→ Identificar layouts que se repiten en múltiples páginas
→ Extraer el layout a un template
→ Las páginas pasan el contenido como children/props

REGLA DE ORO DEL REFACTORING:
Nunca refactorizar todo a la vez.
Aplicar atomic design en los componentes nuevos.
Refactorizar los existentes de forma incremental cuando se tocan.
```

---

## Atomic Design y el Design System — La Conexión

```
Atomic Design es la filosofía.
El Design System es la implementación.

Cómo se relacionan:

Design System → provee los ÁTOMOS
  Los tokens de color, tipografía, spacing
  Los componentes primitivos (Button, Input, etc.)
  → Son los "átomos" del sistema

Tu aplicación → ensambla MOLÉCULAS y ORGANISMOS
  Usando los átomos del design system como bloques
  Las moléculas y organismos son específicos de tu aplicación
  → No van en el design system (a menos que sean compartidos entre apps)

Shared Component Library → puede contener hasta ORGANISMOS genéricos
  Si tienes múltiples aplicaciones con los mismos patrones de UI
  → El design system puede incluir organismos genéricos (DataTable, Header pattern)

Regla práctica:
→ Design System = átomos + moléculas muy genéricas
→ Tu app = moléculas específicas + organismos + templates + páginas
```

---

## Entregables portables — árbol de errores comunes

```
Generar directamente en markdown (árbol con indentación, tabla o Mermaid;
sin herramientas de visualización externas al editor):

Diagrama de señales de alerta:
  Átomo    → ¿tiene fetching? → ⚠️ Promover a organismo
  Molécula → ¿conoce el dominio? → ⚠️ Mover a feature
  Organismo → ¿tiene 300+ líneas? → ⚠️ Dividir en sub-organismos
  Template → ¿hace fetching? → ⚠️ Mover lógica a la página
  Página → ¿mezcla layout con lógica? → ⚠️ Extraer template

Árbol de refactoring:
  Componente monolítico (antes)
    → Átomo: Button extraído
    → Molécula: FormField extraído
    → Organismo: LoginForm resultante
    → Página: LoginPage que lo ensambla

Uso:
"analiza este árbol de componentes y detecta violaciones de atomic design"
"genera el plan de refactoring de [componente monolítico] hacia atomic design"
```
