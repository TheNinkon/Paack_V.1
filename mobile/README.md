# GiGi Rider App

El proyecto móvil vive en `mobile/rider-app`. Ya incluye una estructura Expo + TypeScript preparada para generar builds nativas con Dev Client. Usa la API de Laravel (`/api/rider`) para login, listado de bultos y registro de eventos.

## Pasos rápidos
1. **Instala dependencias**
   ```bash
   cd mobile/rider-app
   npm install
   npx expo install expo-secure-store @react-native-async-storage/async-storage @react-native-community/netinfo
   ```
2. **Configura el backend**
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/PAACK_GIGI_V.0.1/starter-kit
   php artisan serve --host 0.0.0.0 --port 8000
   ```
3. **Ajusta `.env`** en `mobile/rider-app` según dónde ejecutes la app (emulador Android `10.0.2.2`, simulador iOS `127.0.0.1`, dispositivo real `IP_LOCAL`).
4. **Genera proyectos nativos** (una sola vez) y corre en emuladores:
   ```bash
   npm run prebuild
   npm run android    # abre Android Studio y ejecuta en el emulador
   # o
   npm run ios        # abre Xcode para el simulador
   ```
5. **Hot reload / desarrollo**
   ```bash
   npm start -- --clear --localhost
   ```
   - Simulador iOS: Expo Go → `exp://localhost:8081`
   - Emulador Android: `adb reverse tcp:8081 tcp:8081` y abre `exp://localhost:8081`

## Funcionalidades incluidas
- Login de repartidores (token persistido en SecureStore/AsyncStorage).
- Listado de bultos con caché local y refresco automático al recuperar conexión.
- Scripts listos para Dev Client y builds nativas (`expo run:*`).

## Próximos pasos posibles
- Añadir detalle de bulto y cambio de estado.
- Integrar escáner de códigos de barras.
- Sincronización offline de eventos.
- Notificaciones push y tracking GPS opcional.
