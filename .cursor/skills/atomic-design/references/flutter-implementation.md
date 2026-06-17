# Atomic Design en Flutter

## Estructura de Directorios Flutter

```
lib/
├── core/
│   └── theme/               # Design tokens como ThemeData
│       ├── app_colors.dart
│       ├── app_typography.dart
│       ├── app_spacing.dart
│       └── app_theme.dart
│
├── shared/
│   ├── atoms/               # Widgets primitivos
│   │   ├── app_button.dart
│   │   ├── app_input.dart
│   │   ├── app_badge.dart
│   │   ├── app_avatar.dart
│   │   └── app_icon.dart
│   │
│   ├── molecules/           # Combinaciones funcionales
│   │   ├── form_field.dart
│   │   ├── search_bar.dart
│   │   ├── rating_display.dart
│   │   └── price_display.dart
│   │
│   ├── organisms/           # Secciones genéricas
│   │   ├── app_header.dart
│   │   ├── app_bottom_nav.dart
│   │   └── data_table.dart
│   │
│   └── templates/           # Layouts reutilizables
│       ├── scaffold_template.dart
│       ├── list_detail_template.dart
│       └── auth_template.dart
│
└── features/                # Organismos de dominio + páginas
    ├── orders/
    │   ├── presentation/
    │   │   ├── widgets/     # Organismos del dominio orders
    │   │   │   ├── order_card.dart
    │   │   │   └── orders_list.dart
    │   │   └── screens/    # PÁGINAS (las screens de GoRouter)
    │   │       └── orders_screen.dart
    │   └── ...
    └── products/
```

---

## Átomos en Flutter

```dart
// shared/atoms/app_button.dart
// Átomo: solo recibe parámetros, sin lógica de negocio

enum ButtonVariant { primary, secondary, ghost, danger }
enum ButtonSize { sm, md, lg }

class AppButton extends StatelessWidget {
  const AppButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.variant = ButtonVariant.primary,
    this.size = ButtonSize.md,
    this.isLoading = false,
    this.isDisabled = false,
    this.leadingIcon,
    this.trailingIcon,
  });

  final String label;
  final VoidCallback? onPressed;
  final ButtonVariant variant;
  final ButtonSize size;
  final bool isLoading;
  final bool isDisabled;
  final IconData? leadingIcon;
  final IconData? trailingIcon;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final tokens = AppTokens.of(context);  // design tokens

    // Determinar estilos por variante
    final (bgColor, fgColor, borderColor) = switch (variant) {
      ButtonVariant.primary  => (tokens.colorPrimary, Colors.white, Colors.transparent),
      ButtonVariant.secondary => (Colors.transparent, tokens.colorPrimary, tokens.colorPrimary),
      ButtonVariant.ghost    => (Colors.transparent, tokens.colorTextPrimary, Colors.transparent),
      ButtonVariant.danger   => (tokens.colorError, Colors.white, Colors.transparent),
    };

    // Determinar tamaño
    final (height, fontSize, paddingH) = switch (size) {
      ButtonSize.sm => (32.0, 13.0, 12.0),
      ButtonSize.md => (40.0, 14.0, 16.0),
      ButtonSize.lg => (48.0, 16.0, 20.0),
    };

    return AnimatedOpacity(
      opacity: isDisabled ? 0.5 : 1.0,
      duration: const Duration(milliseconds: 150),
      child: SizedBox(
        height: height,
        child: ElevatedButton(
          onPressed: (isDisabled || isLoading) ? null : onPressed,
          style: ElevatedButton.styleFrom(
            backgroundColor: bgColor,
            foregroundColor: fgColor,
            side: BorderSide(color: borderColor, width: borderColor == Colors.transparent ? 0 : 1.5),
            padding: EdgeInsets.symmetric(horizontal: paddingH),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(tokens.radiusSm)),
            elevation: 0,
          ),
          child: isLoading
              ? SizedBox(
                  width: 16,
                  height: 16,
                  child: CircularProgressIndicator(color: fgColor, strokeWidth: 2),
                )
              : Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (leadingIcon != null) ...[
                      Icon(leadingIcon, size: fontSize + 2),
                      SizedBox(width: tokens.spacing1),
                    ],
                    Text(label, style: TextStyle(fontSize: fontSize, fontWeight: FontWeight.w600)),
                    if (trailingIcon != null) ...[
                      SizedBox(width: tokens.spacing1),
                      Icon(trailingIcon, size: fontSize + 2),
                    ],
                  ],
                ),
        ),
      ),
    );
  }
}
```

---

## Moléculas en Flutter

```dart
// shared/molecules/form_field_widget.dart
// Molécula: combina átomos sin conocer el dominio

class FormFieldWidget extends StatelessWidget {
  const FormFieldWidget({
    super.key,
    required this.label,
    required this.child,
    this.helper,
    this.error,
    this.isRequired = false,
  });

  final String label;
  final Widget child;   // El input va aquí — composición
  final String? helper;
  final String? error;
  final bool isRequired;

  @override
  Widget build(BuildContext context) {
    final tokens = AppTokens.of(context);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        // Átomo: label
        Row(
          children: [
            Text(
              label,
              style: TextStyle(
                fontSize: tokens.textSm,
                fontWeight: FontWeight.w500,
                color: tokens.colorTextPrimary,
              ),
            ),
            if (isRequired)
              Text(' *', style: TextStyle(color: tokens.colorError)),
          ],
        ),
        SizedBox(height: tokens.spacing1),

        // El input externo (child)
        child,

        // Error o helper text
        if (error != null) ...[
          SizedBox(height: tokens.spacing1),
          Row(
            children: [
              Icon(Icons.error_outline, size: 14, color: tokens.colorError),
              SizedBox(width: 4),
              Text(
                error!,
                style: TextStyle(fontSize: tokens.textXs, color: tokens.colorError),
              ),
            ],
          ),
        ] else if (helper != null) ...[
          SizedBox(height: tokens.spacing1),
          Text(
            helper!,
            style: TextStyle(fontSize: tokens.textXs, color: tokens.colorTextSecondary),
          ),
        ],
      ],
    );
  }
}
```

---

## Organismos en Flutter

```dart
// features/products/presentation/widgets/product_card.dart
// Organismo: conoce el dominio "producto"

class ProductCard extends StatelessWidget {
  const ProductCard({
    super.key,
    required this.product,
    required this.onAddToCart,
    this.onWishlist,
  });

  final Product product;           // CONOCE el dominio Product
  final VoidCallback onAddToCart;
  final VoidCallback? onWishlist;

  @override
  Widget build(BuildContext context) {
    final tokens = AppTokens.of(context);

    return Container(
      decoration: BoxDecoration(
        color: tokens.colorBgDefault,
        borderRadius: BorderRadius.circular(tokens.radiusMd),
        border: Border.all(color: tokens.colorBorderDefault),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 4, offset: const Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Átomo: imagen
          ClipRRect(
            borderRadius: BorderRadius.vertical(top: Radius.circular(tokens.radiusMd)),
            child: AspectRatio(
              aspectRatio: 1,
              child: Image.network(product.imageUrl, fit: BoxFit.cover),
            ),
          ),

          Padding(
            padding: EdgeInsets.all(tokens.spacing3),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Átomo: texto
                Text(
                  product.name,
                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: tokens.textBase),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                SizedBox(height: tokens.spacing1),

                // Molécula: price + discount
                PriceDisplay(
                  price: product.price,
                  originalPrice: product.originalPrice,
                ),
                SizedBox(height: tokens.spacing1),

                // Molécula: rating
                RatingDisplay(rating: product.rating, count: product.reviewCount),
                SizedBox(height: tokens.spacing3),

                // Átomo: button
                AppButton(
                  label: product.inStock ? 'Add to Cart' : 'Out of Stock',
                  onPressed: product.inStock ? onAddToCart : null,
                  isDisabled: !product.inStock,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
```

---

## Templates en Flutter

```dart
// shared/templates/scaffold_template.dart
// Template: estructura sin datos

class ScaffoldTemplate extends StatelessWidget {
  const ScaffoldTemplate({
    super.key,
    this.appBar,
    required this.body,
    this.bottomNavigationBar,
    this.floatingActionButton,
    this.drawer,
    this.backgroundColor,
  });

  final PreferredSizeWidget? appBar;
  final Widget body;
  final Widget? bottomNavigationBar;
  final Widget? floatingActionButton;
  final Widget? drawer;
  final Color? backgroundColor;

  @override
  Widget build(BuildContext context) {
    final tokens = AppTokens.of(context);

    return Scaffold(
      backgroundColor: backgroundColor ?? tokens.colorBgSubtle,
      appBar: appBar,
      drawer: drawer,
      body: SafeArea(child: body),
      bottomNavigationBar: bottomNavigationBar,
      floatingActionButton: floatingActionButton,
    );
  }
}
```

---

## Páginas en Flutter (Screens)

```dart
// features/orders/presentation/screens/orders_screen.dart
// PÁGINA: conecta datos con template

class OrdersScreen extends ConsumerWidget {
  const OrdersScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    // La página es la que hace fetching
    final ordersAsync = ref.watch(ordersProvider);

    return ScaffoldTemplate(
      // Template para la estructura
      appBar: AppBar(title: const Text('My Orders')),
      bottomNavigationBar: const AppBottomNav(currentIndex: 1),

      // La página conecta el estado con los organismos
      body: ordersAsync.when(
        loading: () => const OrdersListSkeleton(),
        error: (e, _) => ErrorState(message: e.toString()),
        data: (orders) => orders.isEmpty
            ? const EmptyOrdersState()
            : OrdersList(               // Organismo
                orders: orders,
                onCancelOrder: (id) =>
                    ref.read(ordersProvider.notifier).cancel(id),
              ),
      ),

      // Floating action button
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/orders/new'),
        icon: const Icon(Icons.add),
        label: const Text('New Order'),
      ),
    );
  }
}
```
