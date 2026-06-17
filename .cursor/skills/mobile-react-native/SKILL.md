---
name: mobile-react-native
description: >
  Guía React Native con Expo: navegación con Expo Router, estado offline,
  notificaciones push, builds y deploy a stores con EAS. Usar cuando el usuario
  mencione React Native, Expo, apps móviles con JavaScript/TypeScript, Expo Router,
  React Navigation, notificaciones push, builds de iOS/Android, EAS Build, o cuando
  diga "cómo hago una app mobile", "cómo navego en React Native", "cómo manejo
  offline en mobile", "cómo publico en App Store/Google Play", o cualquier variante
  relacionada con desarrollo mobile cross-platform con React Native.
---

# Mobile React Native Skill

React Native con Expo — desarrollo mobile de producción.

**Expo Router y Navegación → `references/navigation.md`**
**Estado, API y Offline → `references/state-api.md`**
**Componentes y UI Nativos → `references/ui-native.md`**
**Notificaciones Push → `references/notifications.md`**
**Build y Deploy (EAS) → `references/build-deploy.md`**

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — perfiles EAS, URLs API por ambiente.
2. `eas.json` / `.env` patterns si project-memory los referencia.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** workflow/build profile acordado → project-memory; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory (managed vs dev build, API URLs).
1. **Decidir el workflow**: ¿necesita módulo nativo custom? → dev build; si no → managed workflow + Expo Router (ver comparativa abajo). Crea el proyecto según Setup Inicial.
2. **Implementar el feature**: navegación con `references/navigation.md`, datos/offline con `references/state-api.md`, UI con `references/ui-native.md`, push con `references/notifications.md`.
3. **Validar el proyecto**: ejecuta `npx expo-doctor` y verifica que sale limpio (sin issues); corrige dependencias con `npx expo install --fix` si avisa de versiones incompatibles.
4. **Dev build** (`references/build-deploy.md`): `eas build --profile development` e instala en dispositivo físico; verifica el feature en iOS y Android.
5. **Preview**: `eas build --profile preview` para QA interna. Checklist de `eas.json` antes de buildear:
   - Perfiles `development` / `preview` / `production` definidos
   - Variables `EXPO_PUBLIC_*` correctas por perfil (API URLs por ambiente)
   - `channel` configurado por perfil para OTA updates
   - `autoIncrement: true` en production
6. **Production**: `eas build --profile production` y `eas submit`; gate previo: `npx expo-doctor` limpio y tests/type-check en verde (`npm run type-check && npm test`).
7. **Post-release**: patches de JS via OTA con `eas update --branch production`; cambios nativos requieren build nuevo.
8. **Validación y cierre** — ejecutar `## Validación`; registrar gaps en `LEARNINGS.md`.

---

## Defaults si falta contexto

Asume y **declara** estos supuestos en lugar de preguntar (máx. 1 pregunta solo si es bloqueante):

- **¿Módulo nativo custom? → dev build; si no → managed workflow + Expo Router.**
- Estado de servidor → React Query con persistencia para offline; estado cliente → Zustand.
- Tokens/credenciales → `expo-secure-store` (nunca AsyncStorage).
- Builds → EAS Build con 3 perfiles (development/preview/production).
- Push → `expo-notifications` + Expo Push API.
- Listas → FlashList/FlatList; imágenes → `expo-image`.

---

## Expo vs Bare React Native

```
Expo Managed Workflow (recomendado para empezar):
  ✅ Setup en minutos — sin Xcode/Android Studio para desarrollar
  ✅ OTA updates (actualizar JS sin pasar por stores)
  ✅ EAS Build para compilar en la nube
  ✅ Expo SDK: cámara, notificaciones, sensores, etc. pre-configurados
  ✅ Expo Router para navegación file-based (como Next.js)
  ❌ Si necesitas módulo nativo no disponible en Expo → usar bare o dev build

Expo with Dev Build (sweet spot para proyectos serios):
  ✅ Todo lo de Managed +
  ✅ Módulos nativos custom
  ✅ Sigue usando Expo SDK y EAS
  → Crear dev build: eas build --profile development

Bare React Native (solo si hay razón específica):
  ✅ Control total sobre código nativo
  ❌ Necesitas Mac para iOS
  ❌ Sin beneficios de Expo
  ❌ Mayor overhead de configuración y mantenimiento
```

---

## Setup Inicial

```bash
# Crear proyecto con Expo Router
npx create-expo-app@latest MyApp --template tabs

# Estructura generada con Expo Router (file-based routing)
MyApp/
├── app/
│   ├── _layout.tsx         # Root layout
│   ├── (tabs)/             # Tab navigation group
│   │   ├── _layout.tsx     # Tab navigator config
│   │   ├── index.tsx       # Tab 1: Home
│   │   └── profile.tsx     # Tab 2: Profile
│   ├── orders/
│   │   ├── index.tsx       # /orders
│   │   └── [id].tsx        # /orders/:id
│   └── +not-found.tsx      # 404
├── components/
├── constants/
├── hooks/
└── assets/

# Instalar dependencias comunes
npx expo install \
  @tanstack/react-query \
  zustand \
  react-hook-form \
  zod \
  @hookform/resolvers \
  axios \
  @react-native-async-storage/async-storage \
  react-native-safe-area-context \
  react-native-screens \
  expo-secure-store \
  expo-notifications \
  expo-image
```

---

## Estructura de Proyecto Recomendada

```
app/                          # Rutas (Expo Router)
├── _layout.tsx               # Root — providers globales
├── (auth)/                   # Route group sin segmento en URL
│   ├── _layout.tsx           # Layout solo para auth screens
│   ├── login.tsx             # /login
│   └── register.tsx          # /register
├── (app)/                    # App autenticada
│   ├── _layout.tsx           # Verifica auth, redirige si no está logueado
│   ├── (tabs)/               # Bottom tabs
│   │   ├── _layout.tsx
│   │   ├── index.tsx         # Home tab
│   │   ├── orders.tsx        # Orders tab
│   │   └── profile.tsx       # Profile tab
│   └── orders/
│       └── [id].tsx          # Order detail (stack dentro de tab)
└── +not-found.tsx

components/
├── ui/                       # Primitivos reutilizables
│   ├── Button.tsx
│   ├── Input.tsx
│   └── Card.tsx
└── features/                 # Componentes por dominio
    └── orders/
        ├── OrderCard.tsx
        └── OrderStatusBadge.tsx

features/                     # Lógica por dominio
├── orders/
│   ├── hooks/
│   │   ├── useOrders.ts
│   │   └── useOrderDetail.ts
│   ├── api/
│   │   └── orders.api.ts
│   └── types/
│       └── order.types.ts
└── auth/

lib/
├── api.ts                    # Axios instance con interceptors
├── queryClient.ts
└── storage.ts                # AsyncStorage helpers

store/
└── auth.store.ts             # Zustand stores globales
```

---

## Root Layout con Providers

```typescript
// app/_layout.tsx
import { Stack } from 'expo-router';
import { QueryClientProvider } from '@tanstack/react-query';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { queryClient } from '@/lib/queryClient';
import { useAuthStore } from '@/store/auth.store';
import { useEffect } from 'react';
import * as SplashScreen from 'expo-splash-screen';

// Mantener splash screen mientras carga
SplashScreen.preventAutoHideAsync();

export default function RootLayout() {
  const { initialize, isInitialized } = useAuthStore();

  useEffect(() => {
    initialize().finally(() => SplashScreen.hideAsync());
  }, []);

  if (!isInitialized) return null;

  return (
    <GestureHandlerRootView style={{ flex: 1 }}>
      <SafeAreaProvider>
        <QueryClientProvider client={queryClient}>
          <Stack screenOptions={{ headerShown: false }}>
            <Stack.Screen name="(auth)" />
            <Stack.Screen name="(app)" />
            <Stack.Screen name="+not-found" />
          </Stack>
        </QueryClientProvider>
      </SafeAreaProvider>
    </GestureHandlerRootView>
  );
}
```

---

## Checklist Mobile Producción

### Performance
- [ ] FlatList o FlashList (no ScrollView + map para listas)
- [ ] expo-image en lugar de Image de React Native
- [ ] useMemo/useCallback donde hay re-renders costosos
- [ ] InteractionManager para tareas pesadas post-animación
- [ ] Hermes engine activo (default en Expo)

### UX Mobile
- [ ] Loading states en todas las operaciones async
- [ ] Pull to refresh en listas
- [ ] Offline mode con datos cacheados
- [ ] Haptic feedback en acciones importantes
- [ ] Keyboard avoiding en formularios
- [ ] Safe area insets respetados

### Seguridad
- [ ] Tokens en expo-secure-store (no AsyncStorage)
- [ ] Certificate pinning si la app maneja datos sensibles
- [ ] No logs en producción (datos sensibles)
- [ ] Ofuscación de código en builds de producción

### Build
- [ ] eas.json configurado para development/staging/production
- [ ] Variables de entorno separadas por ambiente (.env.production)
- [ ] Versión de app y build number incrementados correctamente
- [ ] OTA updates configurados para patches rápidos

---

## Ejemplo input → output

**Input:** "Pantalla de login con token en SecureStore y deep link post-auth."

**Output:** ruta `app/(auth)/login.tsx`; React Query login mutation; token en `expo-secure-store`; deep link `app://dashboard`. Gates: `npx expo-doctor` limpio; probado iOS + Android.

---

## Validación

| Gate | Comando | Criterio |
|------|---------|----------|
| Expo doctor | `npx expo-doctor` | sin issues |
| Types | `npm run type-check` / `npx tsc --noEmit` | exit 0 |
| Tests | `npm test` | exit 0 |
| Device | emulador/dispositivo | iOS y Android |
| EAS profile | `eas.json` | perfil correcto para destino |

---

## Entregable

Al cerrar una tarea con esta skill, entrega:

```markdown
## Implementación RN/Expo — <feature>

**Workflow**: managed | dev build (motivo si es dev build)
**Rutas**: pantallas añadidas en app/ (Expo Router)
**Estado**: qué vive en React Query / Zustand / SecureStore
**Push/deep links**: tipos de notificación y rutas destino (si aplica)

### Verificación
- [ ] `npx expo-doctor` limpio
- [ ] `npm run type-check` sin errores
- [ ] Probado en dispositivo/emulador iOS y Android
- [ ] Perfil de eas.json correcto para el destino (dev/preview/production)

### Pendientes / riesgos
- ...
```

---

## Skills relacionadas

- `react-patterns` — hooks, React Query, Zustand y formularios (base compartida)
- `mobile-flutter` — alternativa con Flutter (comparativa en esa skill)
- `ui-mobile-native` — diseño visual y patrones de UI móvil
- `api-design` — diseño de las APIs que consume la app
- `testing-strategy` — estrategia global de tests

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
