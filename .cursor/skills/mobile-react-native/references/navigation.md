# Expo Router — Navegación

## Conceptos Fundamentales

```
Expo Router = file-based routing para React Native
Mismo concepto que Next.js App Router pero para mobile.

Carpetas especiales:
(group)/      → route group sin segmento en URL
[param]/      → ruta dinámica
+not-found    → pantalla 404
_layout.tsx   → layout que envuelve las rutas hijas
```

---

## Layouts y Navegadores

```typescript
// app/(app)/(tabs)/_layout.tsx — Bottom Tab Navigator
import { Tabs } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useColorScheme } from 'react-native';

export default function TabLayout() {
  const colorScheme = useColorScheme();

  return (
    <Tabs
      screenOptions={{
        tabBarActiveTintColor: colorScheme === 'dark' ? '#fff' : '#000',
        tabBarStyle: {
          backgroundColor: colorScheme === 'dark' ? '#1a1a1a' : '#ffffff',
          borderTopColor: colorScheme === 'dark' ? '#333' : '#e5e5e5',
        },
        headerShown: false,
      }}
    >
      <Tabs.Screen
        name="index"
        options={{
          title: 'Home',
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="home" size={size} color={color} />
          ),
        }}
      />
      <Tabs.Screen
        name="orders"
        options={{
          title: 'Orders',
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="receipt-outline" size={size} color={color} />
          ),
          tabBarBadge: 3,  // notificación numérica
        }}
      />
      <Tabs.Screen
        name="profile"
        options={{
          title: 'Profile',
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="person-outline" size={size} color={color} />
          ),
        }}
      />
    </Tabs>
  );
}
```

---

## Stack Navigator dentro de Tab

```typescript
// app/(app)/(tabs)/orders/_layout.tsx
// Stack navigator dentro de la tab de Orders
import { Stack } from 'expo-router';

export default function OrdersStack() {
  return (
    <Stack
      screenOptions={{
        headerStyle: { backgroundColor: '#fff' },
        headerTintColor: '#000',
        headerBackTitle: '',  // sin texto en botón back (iOS)
        animation: 'slide_from_right',
      }}
    >
      <Stack.Screen
        name="index"
        options={{ title: 'My Orders' }}
      />
      <Stack.Screen
        name="[id]"
        options={({ route }) => ({
          title: `Order #${route.params?.id}`,
          headerRight: () => <OrderActionsButton />,
        })}
      />
    </Stack>
  );
}
```

---

## Navegación Programática

```typescript
import { router, useRouter, useLocalSearchParams, Link } from 'expo-router';

// Navegación imperativa desde cualquier lugar
router.push('/orders/123');
router.push({ pathname: '/orders/[id]', params: { id: order.id } });
router.replace('/login');   // sin historial (logout)
router.back();              // volver
router.dismiss();           // cerrar modal

// En componente
function OrderCard({ order }: { order: Order }) {
  const router = useRouter();

  return (
    <Pressable onPress={() => router.push(`/orders/${order.id}`)}>
      <Text>{order.reference}</Text>
    </Pressable>
  );
}

// Link declarativo — preferido para navegación simple
function OrderItem({ order }: { order: Order }) {
  return (
    <Link href={`/orders/${order.id}`} asChild>
      <Pressable>
        <Text>{order.reference}</Text>
      </Pressable>
    </Link>
  );
}

// Recibir params en pantalla de destino
function OrderDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const { data: order, isLoading } = useOrder(id);

  return (/* ... */);
}
```

---

## Modal Screens

```typescript
// app/(app)/_layout.tsx — definir modales a nivel de stack raíz
import { Stack } from 'expo-router';

export default function AppLayout() {
  return (
    <Stack>
      <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
      <Stack.Screen
        name="order-details-modal"
        options={{
          presentation: 'modal',        // modal iOS
          headerTitle: 'Order Details',
          headerLeft: () => (
            <Pressable onPress={() => router.back()}>
              <Text>Cancel</Text>
            </Pressable>
          ),
        }}
      />
      <Stack.Screen
        name="image-viewer"
        options={{
          presentation: 'fullScreenModal',
          headerShown: false,
          animation: 'fade',
        }}
      />
    </Stack>
  );
}

// Abrir modal
router.push('/order-details-modal?orderId=123');
```

---

## Protección de Rutas con Auth

```typescript
// app/(app)/_layout.tsx — verificar auth antes de renderizar
import { Redirect, Stack } from 'expo-router';
import { useAuthStore } from '@/store/auth.store';

export default function ProtectedLayout() {
  const { isAuthenticated, isInitialized } = useAuthStore();

  if (!isInitialized) return null;  // esperando inicialización

  if (!isAuthenticated) {
    return <Redirect href="/login" />;
  }

  return (
    <Stack screenOptions={{ headerShown: false }}>
      <Stack.Screen name="(tabs)" />
    </Stack>
  );
}

// app/(auth)/_layout.tsx — redirigir si ya está autenticado
export default function AuthLayout() {
  const { isAuthenticated } = useAuthStore();

  if (isAuthenticated) {
    return <Redirect href="/(app)/(tabs)" />;
  }

  return (
    <Stack screenOptions={{ headerShown: false }}>
      <Stack.Screen name="login" />
      <Stack.Screen name="register" />
    </Stack>
  );
}
```

---

## Deep Links y Universal Links

```typescript
// app.json — configurar scheme para deep links
{
  "expo": {
    "scheme": "myapp",
    "ios": {
      "associatedDomains": ["applinks:myapp.com"]  // Universal Links
    },
    "android": {
      "intentFilters": [
        {
          "action": "VIEW",
          "autoVerify": true,
          "data": [{ "scheme": "https", "host": "myapp.com" }],
          "category": ["BROWSABLE", "DEFAULT"]
        }
      ]
    }
  }
}

// Expo Router maneja deep links automáticamente
// myapp://orders/123         → app/(app)/orders/[id].tsx
// https://myapp.com/orders/123 → mismo

// Manejo de URL al abrir la app desde background
import * as Linking from 'expo-linking';
import { useEffect } from 'react';

function useDeepLinkHandler() {
  useEffect(() => {
    const sub = Linking.addEventListener('url', ({ url }) => {
      const parsed = Linking.parse(url);
      // Expo Router maneja automáticamente, pero se puede interceptar
      console.log('Deep link received:', parsed);
    });

    return () => sub.remove();
  }, []);
}
```

---

## Gestures con react-native-gesture-handler

```typescript
import { GestureDetector, Gesture } from 'react-native-gesture-handler';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withSpring,
  runOnJS,
} from 'react-native-reanimated';

// Swipe para eliminar (estilo iOS)
function SwipeableOrderCard({ order, onDelete }: Props) {
  const translateX = useSharedValue(0);

  const panGesture = Gesture.Pan()
    .onUpdate((e) => {
      if (e.translationX < 0) {
        translateX.value = e.translationX;
      }
    })
    .onEnd((e) => {
      if (e.translationX < -100) {
        // Confirmar eliminación
        translateX.value = withSpring(-300);
        runOnJS(onDelete)(order.id);
      } else {
        translateX.value = withSpring(0);
      }
    });

  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ translateX: translateX.value }],
  }));

  return (
    <GestureDetector gesture={panGesture}>
      <Animated.View style={animatedStyle}>
        <OrderCard order={order} />
      </Animated.View>
    </GestureDetector>
  );
}
```
