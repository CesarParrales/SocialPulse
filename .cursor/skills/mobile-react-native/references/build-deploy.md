# Build y Deploy con EAS

Notificaciones push (expo-notifications, tokens, deep linking) → `notifications.md`.

## EAS Build — Compilar en la Nube

```bash
# Instalar EAS CLI
npm install -g eas-cli
eas login

# Configurar proyecto (primera vez)
eas build:configure
```

```json
// eas.json — perfiles de build
{
  "cli": {
    "version": ">= 7.0.0",
    "appVersionSource": "remote"
  },
  "build": {
    "development": {
      "developmentClient": true,
      "distribution": "internal",
      "ios": {
        "simulator": false
      },
      "env": {
        "EXPO_PUBLIC_API_URL": "https://dev-api.myapp.com"
      }
    },
    "preview": {
      "distribution": "internal",
      "channel": "preview",
      "env": {
        "EXPO_PUBLIC_API_URL": "https://staging-api.myapp.com"
      }
    },
    "production": {
      "autoIncrement": true,
      "channel": "production",
      "env": {
        "EXPO_PUBLIC_API_URL": "https://api.myapp.com"
      }
    }
  },
  "submit": {
    "production": {
      "ios": {
        "appleId": "developer@myapp.com",
        "ascAppId": "1234567890",
        "appleTeamId": "XXXXXXXXXX"
      },
      "android": {
        "serviceAccountKeyPath": "./google-service-account.json",
        "track": "internal"
      }
    }
  }
}
```

```bash
# Comandos de build
eas build --platform ios --profile development    # Dev build iOS
eas build --platform android --profile development # Dev build Android
eas build --platform all --profile production      # Ambos para producción

# Submit a stores
eas submit --platform ios --profile production
eas submit --platform android --profile production

# OTA Update (sin pasar por stores)
eas update --branch production --message "Fix login bug"
```

---

## OTA Updates con expo-updates

```typescript
// hooks/useOTAUpdate.ts
import * as Updates from 'expo-updates';
import { useEffect, useState } from 'react';
import { Alert } from 'react-native';

export function useOTAUpdate() {
  const [isChecking, setIsChecking] = useState(false);

  useEffect(() => {
    checkForUpdate();
  }, []);

  const checkForUpdate = async () => {
    if (__DEV__) return;  // no en desarrollo

    try {
      setIsChecking(true);
      const update = await Updates.checkForUpdateAsync();

      if (update.isAvailable) {
        await Updates.fetchUpdateAsync();

        Alert.alert(
          'Update Available',
          'A new version of the app is available. Restart to apply the update.',
          [
            { text: 'Later', style: 'cancel' },
            {
              text: 'Restart Now',
              onPress: () => Updates.reloadAsync(),
            },
          ]
        );
      }
    } catch (error) {
      // No crashear si falla la verificación de update
      console.error('OTA update check failed:', error);
    } finally {
      setIsChecking(false);
    }
  };

  return { isChecking, checkForUpdate };
}
```

---

## app.json — Configuración del Proyecto

```json
{
  "expo": {
    "name": "MyApp",
    "slug": "myapp",
    "version": "1.0.0",
    "orientation": "portrait",
    "icon": "./assets/icon.png",
    "scheme": "myapp",
    "userInterfaceStyle": "automatic",

    "splash": {
      "image": "./assets/splash.png",
      "resizeMode": "contain",
      "backgroundColor": "#ffffff"
    },

    "ios": {
      "supportsTablet": false,
      "bundleIdentifier": "com.mycompany.myapp",
      "buildNumber": "1",
      "infoPlist": {
        "NSCameraUsageDescription": "Used to scan barcodes",
        "NSPhotoLibraryUsageDescription": "Used to select product photos"
      },
      "associatedDomains": ["applinks:myapp.com"]
    },

    "android": {
      "package": "com.mycompany.myapp",
      "versionCode": 1,
      "adaptiveIcon": {
        "foregroundImage": "./assets/adaptive-icon.png",
        "backgroundColor": "#ffffff"
      },
      "permissions": [
        "android.permission.CAMERA",
        "android.permission.READ_MEDIA_IMAGES"
      ]
    },

    "plugins": [
      "expo-router",
      "expo-secure-store",
      [
        "expo-notifications",
        {
          "icon": "./assets/notification-icon.png",
          "color": "#007AFF"
        }
      ]
    ],

    "extra": {
      "eas": {
        "projectId": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
      }
    }
  }
}
```

---

## CI/CD para Mobile con GitHub Actions

```yaml
# .github/workflows/mobile-ci.yml
name: Mobile CI

on:
  push:
    branches: [main]
  pull_request:

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'
      - run: npm ci
      - run: npm run type-check
      - run: npm run lint
      - run: npm test -- --coverage --watchAll=false

  build-preview:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'
      - run: npm ci
      - name: Setup EAS
        uses: expo/expo-github-action@v8
        with:
          eas-version: latest
          token: ${{ secrets.EXPO_TOKEN }}
      - name: Build preview
        run: eas build --platform all --profile preview --non-interactive
      - name: Submit to stores (production only)
        if: github.ref == 'refs/heads/main' && contains(github.event.head_commit.message, '[release]')
        run: eas submit --platform all --profile production --non-interactive
```
