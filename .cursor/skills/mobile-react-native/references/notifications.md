# Notificaciones Push — expo-notifications

## Setup

```bash
npx expo install expo-notifications expo-device expo-constants
```

```json
// app.json — plugin con icono y color (Android)
{
  "expo": {
    "plugins": [
      [
        "expo-notifications",
        {
          "icon": "./assets/notification-icon.png",
          "color": "#007AFF"
        }
      ]
    ]
  }
}
```

Requisitos:
- **Dispositivo físico** — los push remotos no llegan a simuladores/emuladores.
- **iOS**: necesitas un dev build o build de EAS (no funciona en Expo Go desde SDK 53); EAS gestiona la APNs key automáticamente con `eas credentials`.
- **Android**: FCM — sube el `google-services.json` y EAS configura el resto.

---

## Permisos y Token

```typescript
// lib/notifications.ts
import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { Platform } from 'react-native';

// Configurar cómo se muestran las notificaciones cuando la app está abierta
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: true,
  }),
});

export async function registerForPushNotifications(): Promise<string | null> {
  // Solo en dispositivos reales
  if (!Device.isDevice) {
    console.log('Push notifications only work on real devices');
    return null;
  }

  // Verificar/solicitar permisos
  const { status: existingStatus } = await Notifications.getPermissionsAsync();
  let finalStatus = existingStatus;

  if (existingStatus !== 'granted') {
    const { status } = await Notifications.requestPermissionsAsync();
    finalStatus = status;
  }

  if (finalStatus !== 'granted') {
    return null;
  }

  // Canal de notificación para Android (obligatorio en Android 8+)
  if (Platform.OS === 'android') {
    await Notifications.setNotificationChannelAsync('orders', {
      name: 'Order Updates',
      importance: Notifications.AndroidImportance.HIGH,
      vibrationPattern: [0, 250, 250, 250],
      lightColor: '#007AFF',
    });
  }

  // Obtener token de Expo (identifica este dispositivo en el servicio de push de Expo)
  const token = await Notifications.getExpoPushTokenAsync({
    projectId: process.env.EXPO_PUBLIC_PROJECT_ID,
  });

  return token.data; // "ExponentPushToken[xxxx]"
}
```

UX de permisos: no pidas permiso en el primer arranque; pide en un momento con contexto ("¿Quieres recibir actualizaciones de tus pedidos?") — la tasa de aceptación sube mucho.

---

## Hook de Registro + Listeners

```typescript
// hooks/usePushNotifications.ts
// En el root layout — registrar token y reaccionar a notificaciones
export function usePushNotifications() {
  const { user } = useAuthStore();
  const queryClient = useQueryClient();

  useEffect(() => {
    if (!user) return;

    let isMounted = true;

    registerForPushNotifications().then(token => {
      if (token && isMounted) {
        // Guardar token en el backend, asociado al usuario y plataforma
        api.post('/users/push-token', { token, platform: Platform.OS });
      }
    });

    // Listener: notificación recibida con app abierta (foreground)
    const receivedSub = Notifications.addNotificationReceivedListener(notification => {
      const data = notification.request.content.data;

      // Revalidar datos según tipo de notificación
      if (data.type === 'order_status_changed') {
        queryClient.invalidateQueries({ queryKey: ['orders', data.orderId] });
      }
    });

    // Listener: usuario toca la notificación (app en foreground o background)
    const responseSub = Notifications.addNotificationResponseReceivedListener(response => {
      const data = response.notification.request.content.data;

      if (data.type === 'order_status_changed') {
        router.push(`/orders/${data.orderId}`);
      }
    });

    return () => {
      isMounted = false;
      receivedSub.remove();
      responseSub.remove();
    };
  }, [user?.id]);
}
```

---

## Push Remoto con EAS / Expo Push API

El backend envía al servicio de Expo, que enruta a APNs/FCM — sin gestionar certificados propios.

```bash
# Prueba rápida desde terminal
curl -X POST https://exp.host/--/api/v2/push/send \
  -H "Content-Type: application/json" \
  -d '{
    "to": "ExponentPushToken[xxxx]",
    "title": "Pedido enviado",
    "body": "Tu pedido ORD-001 está en camino",
    "data": { "type": "order_status_changed", "orderId": "ord_1" },
    "channelId": "orders"
  }'
```

```typescript
// Backend Node — expo-server-sdk
import { Expo } from 'expo-server-sdk';

const expo = new Expo({ accessToken: process.env.EXPO_ACCESS_TOKEN });

export async function sendOrderUpdate(pushToken: string, orderId: string) {
  if (!Expo.isExpoPushToken(pushToken)) return;

  const tickets = await expo.sendPushNotificationsAsync([
    {
      to: pushToken,
      sound: 'default',
      title: 'Pedido actualizado',
      body: 'Tu pedido cambió de estado',
      data: { type: 'order_status_changed', orderId },
      channelId: 'orders',
    },
  ]);

  // Revisar tickets/receipts: DeviceNotRegistered → eliminar token de la BD
  for (const ticket of tickets) {
    if (ticket.status === 'error' &&
        ticket.details?.error === 'DeviceNotRegistered') {
      await removePushToken(pushToken);
    }
  }
}
```

Producción: envía en lotes con `expo.chunkPushNotifications()` y consulta receipts después (~15 min) para limpiar tokens muertos.

---

## Deep Linking desde Notificación

El campo `data` de la notificación lleva la ruta/IDs; el handler navega con Expo Router.

```typescript
// Caso 1 y 2 (foreground/background): addNotificationResponseReceivedListener (arriba)

// Caso 3: app CERRADA (terminated) — la notificación que abrió la app
// El listener puede no dispararse; usar el hook de "última respuesta":
import * as Notifications from 'expo-notifications';
import { router } from 'expo-router';
import { useEffect } from 'react';

export function useNotificationDeepLink() {
  const lastResponse = Notifications.useLastNotificationResponse();

  useEffect(() => {
    const data = lastResponse?.notification.request.content.data;
    if (data?.type === 'order_status_changed' && data.orderId) {
      // Esperar a que el router esté montado antes de navegar
      router.push(`/orders/${data.orderId}`);
    }
  }, [lastResponse]);
}
```

Convención recomendada: el backend siempre manda `data: { route: '/orders/123' }` y el cliente hace `router.push(data.route)` — un solo punto de mapeo.

```json
// app.json — scheme para deep links (también desde fuera de notificaciones)
{ "expo": { "scheme": "myapp" } }
```

---

## Checklist Push Producción

- [ ] Permiso pedido con contexto, no en el primer arranque
- [ ] Token enviado al backend en login y refrescado; eliminado en logout
- [ ] Canal Android creado antes de la primera notificación
- [ ] Deep link funciona en los 3 estados: foreground, background, terminated
- [ ] Backend limpia tokens `DeviceNotRegistered`
- [ ] Probado en dispositivo físico iOS y Android
