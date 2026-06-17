# Estado, API y Modo Offline

## API Client con Axios

```typescript
// lib/api.ts
import axios from 'axios';
import * as SecureStore from 'expo-secure-store';

export const api = axios.create({
  baseURL: process.env.EXPO_PUBLIC_API_URL,
  timeout: 15000,
  headers: { 'Content-Type': 'application/json' },
});

// Request interceptor — agregar token
api.interceptors.request.use(async (config) => {
  const token = await SecureStore.getItemAsync('accessToken');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor — refresh token automático
let isRefreshing = false;
let failedQueue: Array<{ resolve: Function; reject: Function }> = [];

api.interceptors.response.use(
  response => response,
  async (error) => {
    const originalRequest = error.config;

    if (error.response?.status === 401 && !originalRequest._retry) {
      if (isRefreshing) {
        // Si ya está refrescando, encolar el request
        return new Promise((resolve, reject) => {
          failedQueue.push({ resolve, reject });
        }).then(token => {
          originalRequest.headers.Authorization = `Bearer ${token}`;
          return api(originalRequest);
        });
      }

      originalRequest._retry = true;
      isRefreshing = true;

      try {
        const refreshToken = await SecureStore.getItemAsync('refreshToken');
        const { data } = await axios.post(`${process.env.EXPO_PUBLIC_API_URL}/auth/refresh`, {
          refreshToken,
        });

        const { accessToken } = data;
        await SecureStore.setItemAsync('accessToken', accessToken);

        // Resolver queue
        failedQueue.forEach(({ resolve }) => resolve(accessToken));
        failedQueue = [];

        originalRequest.headers.Authorization = `Bearer ${accessToken}`;
        return api(originalRequest);

      } catch (refreshError) {
        // Refresh falló — logout
        failedQueue.forEach(({ reject }) => reject(refreshError));
        failedQueue = [];
        useAuthStore.getState().logout();
        return Promise.reject(refreshError);
      } finally {
        isRefreshing = false;
      }
    }

    return Promise.reject(error);
  }
);
```

---

## Auth Store con Zustand + SecureStore

```typescript
// store/auth.store.ts
import { create } from 'zustand';
import * as SecureStore from 'expo-secure-store';
import { router } from 'expo-router';

interface AuthStore {
  user: User | null;
  isAuthenticated: boolean;
  isInitialized: boolean;

  initialize: () => Promise<void>;
  login: (credentials: LoginCredentials) => Promise<void>;
  logout: () => Promise<void>;
  updateUser: (updates: Partial<User>) => void;
}

export const useAuthStore = create<AuthStore>()((set, get) => ({
  user: null,
  isAuthenticated: false,
  isInitialized: false,

  initialize: async () => {
    try {
      const token = await SecureStore.getItemAsync('accessToken');
      const userJson = await SecureStore.getItemAsync('user');

      if (token && userJson) {
        const user = JSON.parse(userJson);
        set({ user, isAuthenticated: true });
      }
    } catch (error) {
      // Tokens corruptos — limpiar
      await SecureStore.deleteItemAsync('accessToken');
      await SecureStore.deleteItemAsync('refreshToken');
      await SecureStore.deleteItemAsync('user');
    } finally {
      set({ isInitialized: true });
    }
  },

  login: async (credentials) => {
    const { data } = await api.post('/auth/login', credentials);
    const { accessToken, refreshToken, user } = data;

    // Guardar en SecureStore — encriptado por el OS
    await Promise.all([
      SecureStore.setItemAsync('accessToken', accessToken),
      SecureStore.setItemAsync('refreshToken', refreshToken),
      SecureStore.setItemAsync('user', JSON.stringify(user)),
    ]);

    set({ user, isAuthenticated: true });
    router.replace('/(app)/(tabs)');
  },

  logout: async () => {
    try {
      await api.post('/auth/logout');
    } catch {} // ignorar error de red en logout

    await Promise.all([
      SecureStore.deleteItemAsync('accessToken'),
      SecureStore.deleteItemAsync('refreshToken'),
      SecureStore.deleteItemAsync('user'),
    ]);

    set({ user: null, isAuthenticated: false });
    router.replace('/login');
  },

  updateUser: (updates) =>
    set(state => {
      const updated = state.user ? { ...state.user, ...updates } : null;
      if (updated) {
        SecureStore.setItemAsync('user', JSON.stringify(updated));
      }
      return { user: updated };
    }),
}));
```

---

## React Query en Mobile — Configuración Offline

```typescript
// lib/queryClient.ts
import { QueryClient } from '@tanstack/react-query';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { createAsyncStoragePersister } from '@tanstack/query-async-storage-persister';
import { PersistQueryClientProvider } from '@tanstack/react-query-persist-client';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 1000 * 60 * 5,    // 5 min
      gcTime: 1000 * 60 * 60 * 24, // 24h en caché (para offline)
      retry: 2,
      retryDelay: 1000,
      networkMode: 'offlineFirst',  // usar caché si sin red
    },
    mutations: {
      networkMode: 'offlineFirst',
      retry: 3,
    },
  },
});

// Persistir caché en AsyncStorage para offline
const asyncStoragePersister = createAsyncStoragePersister({
  storage: AsyncStorage,
  key: 'REACT_QUERY_CACHE',
  throttleTime: 1000,
});

// En el root layout
function App() {
  return (
    <PersistQueryClientProvider
      client={queryClient}
      persistOptions={{
        persister: asyncStoragePersister,
        maxAge: 1000 * 60 * 60 * 24,  // cache válido por 24h offline
        buster: process.env.EXPO_PUBLIC_APP_VERSION, // invalidar al actualizar
      }}
    >
      {/* ... */}
    </PersistQueryClientProvider>
  );
}
```

---

## Network Status y Offline UX

```typescript
// hooks/useNetworkStatus.ts
import NetInfo from '@react-native-community/netinfo';
import { useEffect, useState } from 'react';
import { useQueryClient } from '@tanstack/react-query';

export function useNetworkStatus() {
  const [isOnline, setIsOnline] = useState(true);
  const queryClient = useQueryClient();

  useEffect(() => {
    const unsubscribe = NetInfo.addEventListener(state => {
      const online = !!(state.isConnected && state.isInternetReachable);
      setIsOnline(online);

      if (online) {
        // Reconexión — re-fetch queries stale
        queryClient.refetchQueries({
          type: 'active',
          stale: true,
        });
      }
    });

    return unsubscribe;
  }, []);

  return { isOnline };
}

// Banner de offline visible
function OfflineBanner() {
  const { isOnline } = useNetworkStatus();

  if (isOnline) return null;

  return (
    <View style={styles.banner}>
      <Ionicons name="cloud-offline-outline" size={16} color="white" />
      <Text style={styles.text}>You're offline. Showing cached data.</Text>
    </View>
  );
}
```

---

## Optimistic Updates en Mobile

```typescript
// features/orders/hooks/useOrders.ts
export function useCancelOrder() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (orderId: string) =>
      api.post(`/orders/${orderId}/cancel`).then(r => r.data),

    onMutate: async (orderId) => {
      // Cancelar queries en vuelo
      await queryClient.cancelQueries({ queryKey: ['orders'] });

      // Snapshot del estado anterior
      const previousOrders = queryClient.getQueryData<Order[]>(['orders']);

      // Actualizar optimistamente
      queryClient.setQueryData<Order[]>(['orders'], old =>
        old?.map(order =>
          order.id === orderId
            ? { ...order, status: 'CANCELLED' }
            : order
        )
      );

      return { previousOrders };
    },

    onError: (_err, _orderId, context) => {
      // Revertir si falla
      if (context?.previousOrders) {
        queryClient.setQueryData(['orders'], context.previousOrders);
      }
    },

    onSettled: () => {
      queryClient.invalidateQueries({ queryKey: ['orders'] });
    },
  });
}
```

---

## Pull to Refresh

```typescript
import { FlatList, RefreshControl } from 'react-native';
import { useCallback } from 'react';

function OrdersList() {
  const { data, isLoading, refetch, isRefetching } = useOrders();

  const onRefresh = useCallback(() => {
    refetch();
  }, [refetch]);

  return (
    <FlatList
      data={data?.pages.flatMap(p => p.data) ?? []}
      keyExtractor={item => item.id}
      renderItem={({ item }) => <OrderCard order={item} />}

      // Pull to refresh
      refreshControl={
        <RefreshControl
          refreshing={isRefetching}
          onRefresh={onRefresh}
          tintColor="#007AFF"        // iOS color
          colors={['#007AFF']}       // Android colors
        />
      }

      // Infinite scroll
      onEndReached={() => {
        if (hasNextPage && !isFetchingNextPage) {
          fetchNextPage();
        }
      }}
      onEndReachedThreshold={0.3}   // trigger a 30% del final
      ListFooterComponent={
        isFetchingNextPage ? <ActivityIndicator /> : null
      }

      // Performance
      removeClippedSubviews={true}
      maxToRenderPerBatch={10}
      windowSize={10}
      initialNumToRender={10}
    />
  );
}
```
