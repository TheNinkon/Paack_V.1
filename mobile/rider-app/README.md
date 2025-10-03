# Rider App (Expo + Dev Client)

Este es un reinicio del proyecto móvil para GiGi. Se mantiene la integración con la API Laravel (`/api/rider`) pero con una estructura más simple y preparada para generar builds nativos (Android/iOS) usando **Expo Dev Client** o `expo run:*`.

## 1. Requisitos
- Node.js 18+
- Expo CLI (`npm install -g expo-cli`) opcional
- Android Studio + SDK / Xcode (para ejecutar en emuladores)
- Acceso al backend local (`php artisan serve --host 0.0.0.0 --port 8000`)

## 2. Instalación
```bash
cd mobile/rider-app
npm install
```

## 3. Variables de entorno
Crea un `.env` tomando como base `.env.example` y ajusta la URL según el destino:
```
# Emulador Android
EXPO_PUBLIC_API_BASE_URL=http://10.0.2.2:8000/api/rider

# Simulador iOS
EXPO_PUBLIC_API_BASE_URL=http://127.0.0.1:8000/api/rider

# Dispositivo real
EXPO_PUBLIC_API_BASE_URL=http://<IP_DE_TU_MAC>:8000/api/rider
```

## 4. Flujo recomendado

### 4.1 Desarrollo en emuladores con Dev Client
```bash
npm run prebuild            # genera android/ e ios/ (una sola vez)
npm run android             # abre Android Studio y corre en el emulador
# o
npm run ios                 # abre Xcode y corre en el simulador
```

Los comandos `expo run:*` generan una build nativa con dev client integrado. Después de la instalación se puede hacer hot reload con Metro (`npm start`).

### 4.2 Expo Go (cuando no quieras prebuild)
```bash
npm start -- --clear --localhost
```
- Simulador iOS: abre Expo Go y carga `exp://localhost:8081`.
- Emulador Android: `adb reverse tcp:8081 tcp:8081` y abre `exp://localhost:8081`.

## 5. Características actuales
- **Login** contra `POST /api/rider/login`.
- **Listado de bultos** (`GET /api/rider/parcels`) con cache local y refresco auto al recuperar conexión.
- **Token seguro** con `expo-secure-store` (fallback a AsyncStorage en web).
- Configuración de paquete/bundle (`com.gigi.rider`) lista para generar builds (AAB/IPA) con `eas build` si se desea.

## 6. Próximos pasos sugeridos
1. Añadir pantalla de detalle y actualización de estado (`POST /parcels/{id}/events`).
2. Integrar escáner (`expo-barcode-scanner` o dev client con módulo nativo).
3. Implementar cola offline para estados y sincronización.
4. Configurar notificaciones push usando `expo-notifications`.

## 7. Notas
- El backend debe estar corriendo durante las pruebas (`php artisan serve --host 0.0.0.0 --port 8000`).
- Ejecuta `npm run doctor` si Expo avisa sobre versiones incompatibles y sigue las sugerencias (`expo install ...`).
- Si necesitas builds distribuidas, inicia sesión en Expo (`eas login`) y usa `npx eas build -p android/ios`.
