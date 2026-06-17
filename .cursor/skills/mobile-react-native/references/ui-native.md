# Componentes y UI Nativos

## FlashList — Listas de Alto Performance

```typescript
// @shopify/flash-list — más rápido que FlatList para listas largas
import { FlashList } from '@shopify/flash-list';

function OrdersList({ orders }: { orders: Order[] }) {
  return (
    <FlashList
      data={orders}
      renderItem={({ item }) => <OrderCard order={item} />}
      keyExtractor={item => item.id}
      estimatedItemSize={80}    // REQUERIDO — altura estimada del item
      estimatedListSize={{ height: 600, width: 390 }}

      // Separador entre items
      ItemSeparatorComponent={() => (
        <View style={{ height: 1, backgroundColor: '#e5e5e5' }} />
      )}

      // Header y Footer
      ListHeaderComponent={<ListHeader title="Your Orders" count={orders.length} />}
      ListEmptyComponent={<EmptyOrders />}
    />
  );
}

// Cuándo FlatList vs FlashList:
// < 50 items + items de tamaño uniforme → FlatList está bien
// > 50 items o items de tamaño variable → FlashList
```

---

## expo-image — Imágenes Optimizadas

```typescript
import { Image } from 'expo-image';

// expo-image > React Native Image:
// - Caché avanzado (disco + memoria)
// - Formatos modernos (WebP, AVIF)
// - Blur hash placeholder
// - Transiciones suaves

function ProductImage({ product }: { product: Product }) {
  return (
    <Image
      source={{ uri: product.imageUrl }}
      style={{ width: 200, height: 200, borderRadius: 12 }}
      contentFit="cover"           // equivalente a object-fit CSS
      transition={300}             // fade in de 300ms
      placeholder={{ blurhash: product.blurhash }}  // placeholder blur
      cachePolicy="memory-disk"    // caché en memoria y disco
    />
  );
}

// Avatar con fallback
function UserAvatar({ user }: { user: User }) {
  return (
    <Image
      source={user.avatarUrl ? { uri: user.avatarUrl } : require('@/assets/default-avatar.png')}
      style={{ width: 40, height: 40, borderRadius: 20 }}
      contentFit="cover"
    />
  );
}
```

---

## Formularios Nativos

```typescript
import { TextInput, KeyboardAvoidingView, Platform, ScrollView } from 'react-native';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';

function LoginForm() {
  const { control, handleSubmit, formState: { errors, isSubmitting } } = useForm({
    resolver: zodResolver(loginSchema),
  });

  return (
    // KeyboardAvoidingView evita que el teclado tape los inputs
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      style={{ flex: 1 }}
    >
      <ScrollView
        keyboardShouldPersistTaps="handled"  // tocar fuera del teclado no cierra el form
        contentContainerStyle={{ flexGrow: 1, padding: 20 }}
      >
        <Controller
          control={control}
          name="email"
          render={({ field: { onChange, onBlur, value, ref } }) => (
            <View>
              <TextInput
                ref={ref}
                onChangeText={onChange}
                onBlur={onBlur}
                value={value}
                placeholder="Email"
                keyboardType="email-address"
                autoCapitalize="none"
                autoCorrect={false}
                returnKeyType="next"
                onSubmitEditing={() => passwordRef.current?.focus()}
                style={[styles.input, errors.email && styles.inputError]}
              />
              {errors.email && (
                <Text style={styles.errorText}>{errors.email.message}</Text>
              )}
            </View>
          )}
        />

        <Controller
          control={control}
          name="password"
          render={({ field: { onChange, onBlur, value, ref } }) => (
            <View>
              <TextInput
                ref={passwordRef}
                onChangeText={onChange}
                onBlur={onBlur}
                value={value}
                placeholder="Password"
                secureTextEntry
                returnKeyType="done"
                onSubmitEditing={handleSubmit(onSubmit)}
                style={[styles.input, errors.password && styles.inputError]}
              />
            </View>
          )}
        />

        <TouchableOpacity
          onPress={handleSubmit(onSubmit)}
          disabled={isSubmitting}
          style={[styles.button, isSubmitting && styles.buttonDisabled]}
        >
          {isSubmitting
            ? <ActivityIndicator color="white" />
            : <Text style={styles.buttonText}>Sign In</Text>
          }
        </TouchableOpacity>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
```

---

## Animaciones con Reanimated

```typescript
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withSpring,
  withTiming,
  withRepeat,
  withSequence,
  interpolate,
  Extrapolation,
} from 'react-native-reanimated';

// Botón con animación de press
function AnimatedButton({ onPress, children }: Props) {
  const scale = useSharedValue(1);

  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ scale: scale.value }],
  }));

  return (
    <Pressable
      onPressIn={() => { scale.value = withSpring(0.95); }}
      onPressOut={() => { scale.value = withSpring(1); }}
      onPress={onPress}
    >
      <Animated.View style={animatedStyle}>
        {children}
      </Animated.View>
    </Pressable>
  );
}

// Skeleton shimmer animation
function SkeletonCard() {
  const opacity = useSharedValue(0.3);

  useEffect(() => {
    opacity.value = withRepeat(
      withSequence(
        withTiming(1, { duration: 700 }),
        withTiming(0.3, { duration: 700 }),
      ),
      -1,  // infinito
      false
    );
  }, []);

  const animatedStyle = useAnimatedStyle(() => ({
    opacity: opacity.value,
  }));

  return (
    <Animated.View style={[styles.card, animatedStyle]}>
      <View style={styles.skeletonTitle} />
      <View style={styles.skeletonSubtitle} />
    </Animated.View>
  );
}

// Scroll animation — header que se reduce al scrollear
function AnimatedHeader() {
  const scrollY = useSharedValue(0);

  const headerHeight = useAnimatedStyle(() => ({
    height: interpolate(
      scrollY.value,
      [0, 100],         // input range
      [200, 80],        // output range
      Extrapolation.CLAMP
    ),
  }));

  const titleOpacity = useAnimatedStyle(() => ({
    opacity: interpolate(scrollY.value, [0, 80], [1, 0], Extrapolation.CLAMP),
  }));

  return (
    <>
      <Animated.View style={[styles.header, headerHeight]}>
        <Animated.Text style={[styles.title, titleOpacity]}>
          My Orders
        </Animated.Text>
      </Animated.View>
      <Animated.FlatList
        onScroll={({ nativeEvent }) => {
          scrollY.value = nativeEvent.contentOffset.y;
        }}
        scrollEventThrottle={16}
        // ...
      />
    </>
  );
}
```

---

## Haptic Feedback

```typescript
import * as Haptics from 'expo-haptics';

// Tipos de feedback
function handleSuccessAction() {
  Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
}

function handleErrorAction() {
  Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
}

function handleButtonPress() {
  Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
}

function handleHeavyAction() {
  Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Heavy);
}

// Integrado en botón
function HapticButton({ onPress, children, variant = 'light' }: Props) {
  const handlePress = () => {
    Haptics.impactAsync(
      variant === 'heavy'
        ? Haptics.ImpactFeedbackStyle.Heavy
        : Haptics.ImpactFeedbackStyle.Light
    );
    onPress();
  };

  return <TouchableOpacity onPress={handlePress}>{children}</TouchableOpacity>;
}
```

---

## Bottom Sheet con @gorhom/bottom-sheet

```typescript
import BottomSheet, { BottomSheetView, BottomSheetBackdrop } from '@gorhom/bottom-sheet';
import { useCallback, useRef, useMemo } from 'react';

function OrderActionsSheet({ order }: { order: Order }) {
  const bottomSheetRef = useRef<BottomSheet>(null);

  // Snap points — donde puede detenerse el sheet
  const snapPoints = useMemo(() => ['40%', '80%'], []);

  const handleOpen  = () => bottomSheetRef.current?.expand();
  const handleClose = () => bottomSheetRef.current?.close();

  // Backdrop con dismiss al tocar
  const renderBackdrop = useCallback(
    (props: any) => (
      <BottomSheetBackdrop
        {...props}
        disappearsOnIndex={-1}
        appearsOnIndex={0}
        onPress={handleClose}
      />
    ),
    []
  );

  return (
    <>
      <TouchableOpacity onPress={handleOpen}>
        <Text>Order Actions</Text>
      </TouchableOpacity>

      <BottomSheet
        ref={bottomSheetRef}
        index={-1}              // -1 = cerrado inicialmente
        snapPoints={snapPoints}
        enablePanDownToClose
        backdropComponent={renderBackdrop}
        backgroundStyle={{ backgroundColor: '#fff' }}
      >
        <BottomSheetView style={{ padding: 20 }}>
          <Text style={styles.title}>Order #{order.reference}</Text>

          <TouchableOpacity style={styles.action} onPress={() => router.push(`/orders/${order.id}`)}>
            <Ionicons name="eye-outline" size={20} />
            <Text>View Details</Text>
          </TouchableOpacity>

          {order.status === 'PENDING' && (
            <TouchableOpacity style={[styles.action, styles.danger]}>
              <Ionicons name="close-circle-outline" size={20} color="red" />
              <Text style={{ color: 'red' }}>Cancel Order</Text>
            </TouchableOpacity>
          )}
        </BottomSheetView>
      </BottomSheet>
    </>
  );
}
```
