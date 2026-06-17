# Los 5 Niveles del Atomic Design

## Nivel 1 — Átomos

```
Definición:
Los átomos son los bloques de construcción más básicos de la interfaz.
Son elementos HTML con estilo aplicado, o los componentes del design system.
No pueden dividirse más sin perder su función.

Características:
→ Sin lógica de negocio
→ Sin estado global ni fetching
→ Solo controlados por props
→ Altamente reutilizables en cualquier contexto
→ Son el vocabulario del design system

Qué son átomos:
✅ Button          — acción
✅ Input           — entrada de datos
✅ Label           — etiqueta de texto
✅ Icon            — elemento visual
✅ Badge           — indicador
✅ Avatar          — imagen de usuario
✅ Spinner         — indicador de carga
✅ Divider         — separador
✅ Tooltip         — información contextual
✅ Heading (h1-h6) — texto estructural
✅ Paragraph       — texto cuerpo
✅ Image           — imagen optimizada
✅ Checkbox        — selector binario
✅ Radio           — selector de opción
✅ Toggle          — interruptor

Qué NO son átomos:
❌ SearchBar       (Input + Button = molécula)
❌ FormField       (Label + Input + Error = molécula)
❌ NavigationItem  (Icon + Label + estado activo = molécula)
❌ UserCard        (Avatar + texto + acciones = organismo o molécula)

Regla de identificación:
Si al dividirlo en dos partes ambas siguen teniendo sentido por sí solas
→ probablemente es una molécula, no un átomo
```

```tsx
// Ejemplos de átomos bien definidos

// Átomo: Button — solo recibe props, sin lógica propia
interface ButtonProps {
  children: React.ReactNode;
  variant?: 'primary' | 'secondary' | 'ghost' | 'danger';
  size?: 'sm' | 'md' | 'lg';
  isLoading?: boolean;
  disabled?: boolean;
  onClick?: () => void;
}

// Átomo: Badge — puramente visual
interface BadgeProps {
  children: React.ReactNode;
  color?: 'blue' | 'green' | 'red' | 'yellow' | 'gray';
  size?: 'sm' | 'md';
}

// Átomo: Avatar
interface AvatarProps {
  src?: string;
  alt: string;
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';
  fallback?: string; // iniciales si no hay imagen
}
```

---

## Nivel 2 — Moléculas

```
Definición:
Las moléculas son grupos de átomos que funcionan juntos como una unidad.
Son los bloques constructivos más simples de la interfaz.

Características:
→ Combinación de 2-5 átomos con un propósito claro
→ Pueden tener estado local simple (abierto/cerrado, valor controlado)
→ Sin conocimiento del dominio de negocio
→ Reutilizables en múltiples contextos

La molécula ideal:
→ Hace UNA COSA y la hace bien
→ No importa datos del servidor
→ No sabe nada de "pedidos", "productos", "usuarios"
→ Solo sabe de su función de UI (buscar, ingresar, seleccionar)

Ejemplos de moléculas:
→ SearchBar        (Input + Button + Icon)
→ FormField        (Label + Input + HelperText + ErrorMessage)
→ NavigationItem   (Icon + Label + ActiveIndicator)
→ PaginationItem   (Button + Number)
→ FileUpload       (Button + DragZone + FilePreview)
→ PasswordInput    (Input + ToggleButton [mostrar/ocultar])
→ CounterInput     (Button[-] + Input + Button[+])
→ DateRangePicker  (DatePicker + DatePicker + Divider)
→ Breadcrumb       (Link + Separator + Link + ... + CurrentPage)
```

```tsx
// Molécula: FormField — combinación funcional de átomos
interface FormFieldProps {
  label: string;
  htmlFor: string;
  helper?: string;
  error?: string;
  required?: boolean;
  children: React.ReactNode; // el input va aquí
}

function FormField({ label, htmlFor, helper, error, required, children }: FormFieldProps) {
  return (
    <div className="form-field">
      <Label htmlFor={htmlFor} required={required}>    {/* Átomo */}
        {label}
      </Label>
      {children}                                         {/* Átomo externo */}
      {error ? (
        <ErrorMessage>{error}</ErrorMessage>             {/* Átomo */}
      ) : helper ? (
        <HelperText>{helper}</HelperText>               {/* Átomo */}
      ) : null}
    </div>
  );
}

// Uso de la molécula — sin conocimiento del dominio
<FormField label="Email" htmlFor="email" error={errors.email} required>
  <Input id="email" type="email" {...register('email')} />
</FormField>
```

---

## Nivel 3 — Organismos

```
Definición:
Los organismos son secciones de UI relativamente complejas formadas por
moléculas y átomos. Representan porciones distintas de una interfaz.

Características:
→ Forman secciones reconocibles (Header, Footer, Sidebar, ProductCard)
→ PRIMEROS en conocer el dominio de negocio
→ Pueden conectar con el estado global (pero idealmente reciben datos por props)
→ Son reutilizables pero en un contexto más específico

La diferencia clave con moléculas:
Una molécula no sabe qué es un "producto" — solo sabe de UI.
Un organismo sabe qué es un "producto" y lo muestra.

Ejemplos de organismos:
→ Header          (Logo + Navigation + SearchBar + UserMenu)
→ Footer          (Logo + Links + SocialIcons + Copyright)
→ Sidebar         (NavigationMenu + UserProfile + Collapse)
→ ProductCard     (Image + ProductName + Price + Rating + AddToCart)
→ OrderSummary    (Lista de OrderItem + Subtotal + Discount + Total)
→ UserProfileCard (Avatar + UserInfo + Stats + ActionButtons)
→ DataTable       (TableHeader + TableRow[] + Pagination + BulkActions)
→ LoginForm       (FormField[email] + FormField[password] + Button + Links)
→ CommentThread   (Comment[] + CommentInput + LoadMore)
```

```tsx
// Organismo: ProductCard — conoce el dominio "producto"
interface Product {
  id: string;
  name: string;
  price: number;
  rating: number;
  reviewCount: number;
  imageUrl: string;
  inStock: boolean;
}

interface ProductCardProps {
  product: Product;
  onAddToCart: (productId: string) => void;
  onWishlist?: (productId: string) => void;
}

function ProductCard({ product, onAddToCart, onWishlist }: ProductCardProps) {
  // Estado local permitido en organismo
  const [isWishlisted, setIsWishlisted] = useState(false);

  return (
    <Card className="product-card">
      {/* Átomo */}
      <Image src={product.imageUrl} alt={product.name} aspectRatio="square" />

      <Card.Body>
        {/* Átomo */}
        <Heading level={3} size="sm">{product.name}</Heading>

        {/* Molécula: combina Price + SaleIndicator */}
        <PriceDisplay price={product.price} />

        {/* Molécula: combina Stars + Count */}
        <RatingDisplay rating={product.rating} count={product.reviewCount} />
      </Card.Body>

      <Card.Footer>
        {/* Átomo */}
        <Button
          variant="primary"
          onClick={() => onAddToCart(product.id)}
          disabled={!product.inStock}
        >
          {product.inStock ? 'Add to Cart' : 'Out of Stock'}
        </Button>

        {onWishlist && (
          <Button
            variant="ghost"
            onClick={() => { setIsWishlisted(!isWishlisted); onWishlist(product.id); }}
            aria-label={isWishlisted ? 'Remove from wishlist' : 'Add to wishlist'}
          >
            <Icon name={isWishlisted ? 'heart-filled' : 'heart'} />
          </Button>
        )}
      </Card.Footer>
    </Card>
  );
}
```

---

## Nivel 4 — Templates

```
Definición:
Los templates son estructuras de página que muestran el layout
y la jerarquía de los organismos SIN contenido real.

Son el puente entre el diseño y la página final.

Características:
→ Definen la estructura y el layout de la página
→ Sin datos reales — usan slots, children o props genéricas
→ Muestran la "osamenta" de la página
→ Son equivalentes a los wireframes pero con componentes reales
→ En Storybook, los templates son donde se documenta el layout

Por qué son útiles:
→ Permiten validar el layout sin necesitar datos reales
→ Son reutilizables para páginas con la misma estructura
→ Facilitan el testing visual sin mocks complejos
→ Documentan los layouts disponibles en el sistema

Ejemplos de templates:
→ DashboardTemplate  (Header + Sidebar + MainContent + Footer)
→ ProductPageTemplate (Breadcrumb + ProductImages + ProductInfo + RelatedProducts)
→ ArticleTemplate     (Header + ArticleContent + Sidebar + Footer)
→ AuthTemplate        (CenteredCard + Logo + BackgroundPattern)
→ ErrorTemplate       (Header + ErrorMessage + BackButton)
→ ListDetailTemplate  (ListView + DetailView side-by-side)
```

```tsx
// Template: DashboardTemplate — estructura sin datos
interface DashboardTemplateProps {
  header?: React.ReactNode;
  sidebar?: React.ReactNode;
  breadcrumb?: React.ReactNode;
  content: React.ReactNode;         // requerido — el contenido principal
  aside?: React.ReactNode;          // panel lateral derecho (opcional)
}

function DashboardTemplate({
  header,
  sidebar,
  breadcrumb,
  content,
  aside,
}: DashboardTemplateProps) {
  return (
    <div className="dashboard-layout">
      {header && (
        <header className="dashboard-header">{header}</header>
      )}
      <div className="dashboard-body">
        {sidebar && (
          <nav className="dashboard-sidebar">{sidebar}</nav>
        )}
        <main className="dashboard-main">
          {breadcrumb && (
            <div className="dashboard-breadcrumb">{breadcrumb}</div>
          )}
          <div className={cn('dashboard-content', aside && 'with-aside')}>
            <div className="dashboard-primary">{content}</div>
            {aside && (
              <aside className="dashboard-aside">{aside}</aside>
            )}
          </div>
        </main>
      </div>
    </div>
  );
}

// En Storybook — template sin datos reales
export const Default: Story = {
  render: () => (
    <DashboardTemplate
      header={<Header />}
      sidebar={<Sidebar />}
      content={<div style={{ height: '400px', background: '#f5f5f5' }}>Main content area</div>}
    />
  ),
};
```

---

## Nivel 5 — Páginas

```
Definición:
Las páginas son instancias específicas de templates con contenido real.
Son lo que el usuario ve. Son lo que el router renderiza.

Características:
→ Instancian templates con datos reales
→ Conectan con el store, la API o el contexto de la aplicación
→ Son específicas — no reutilizables (son páginas concretas)
→ Revelan si el diseño del template funciona con contenido real

Por qué son el último nivel:
→ Los cambios de diseño se propagan hacia abajo (template, organismos, etc.)
→ El contenido real a veces revela problemas del diseño
   (texto muy largo que rompe el layout, imágenes con aspect ratios inesperados)
→ Son la validación final del sistema

En aplicaciones con routing:
→ Cada ruta = una Página
→ La Página conecta datos con el Template correspondiente
→ La Página pasa los datos a los Organismos
```

```tsx
// Página: OrdersPage — conecta datos con template
// Esta es una PÁGINA → conecta con React Query + Route params

function OrdersPage() {
  // La página es la que hace fetching
  const { data: orders, isLoading, error } = useOrders();
  const [filter, setFilter] = useState<OrderFilter>({});

  // Manejo de estados de la página
  if (isLoading) return <DashboardTemplate content={<OrdersTableSkeleton />} />;
  if (error)     return <DashboardTemplate content={<ErrorState error={error} />} />;

  return (
    // Usa el template para la estructura
    <DashboardTemplate
      header={<AppHeader />}           // Organismo
      sidebar={<AppSidebar />}          // Organismo
      breadcrumb={
        <Breadcrumb items={[
          { label: 'Home', href: '/' },
          { label: 'Orders' },
        ]} />
      }
      content={
        // Organismo: conoce el dominio "orders"
        <OrdersTable
          orders={orders}
          filter={filter}
          onFilterChange={setFilter}
        />
      }
    />
  );
}

// En Next.js App Router
// app/(app)/orders/page.tsx → ESTA ES LA PÁGINA
export default function OrdersPageRoute() {
  return <OrdersPage />;
}
```

---

## Diagrama portable — los 5 niveles (ASCII/Mermaid)

```
Generar el diagrama completo directamente en markdown
(ASCII como abajo, o Mermaid graph; sin herramientas externas al editor):

ÁTOMO          MOLÉCULA           ORGANISMO
[Button]   +   [Label]+[Input]  → [LoginForm]
[Icon]         = [FormField]       [Button]+[FormField]×2
[Label]
    ↓               ↓                    ↓
                TEMPLATE           → PÁGINA
                [AuthTemplate]       /login con datos reales
                Header+Form+Footer   y auth context conectado

Uso:
"genera el diagrama atomic design para [producto/contexto]"
"descompón [componente] en su jerarquía de átomos y moléculas"
```
