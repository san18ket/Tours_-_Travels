# VoyageGo Chauffeur Mobile Application (React Native)

Dedicated Native Android & iOS Chauffeur Console with Secure Background Geolocation Tracking, Offline Telemetry Queue, and Dispatch Operations.

---

## Key Features

1. **Native Background Location Tracking**:
   - Broadcasts real-time GPS coordinates while the screen is locked, in standby, or when navigating with other mapping apps.
   - Utilizes Android `FOREGROUND_SERVICE` with `foregroundServiceType="location"`.
   - Adheres strictly to chauffeur privacy: location broadcasting is disabled when off-duty.

2. **Persistent Offline GPS Queue**:
   - When traveling through tunnels, rural zones, or cellular dead zones, breadcrumbs are safely cached locally in AsyncStorage.
   - Automatically flushes queued points in chronological order upon network reconnection with exponential backoff.
   - Backend deduplicates and validates timestamps so offline sync never overwrites a newer live fix.

3. **Trip Execution Workflow**:
   - View assigned trips, itinerary, passenger contact with direct calling.
   - Accept trip assignments.
   - Start trip with verified start odometer reading.
   - Complete trip with validated end odometer reading.

4. **Security & Authentication**:
   - Separate from browser cookies: uses cryptographic Bearer access tokens and revocable refresh tokens.
   - Device binding via hardware UUID in the `driver_mobile_devices` table.
   - Instant token revocation upon logout or administrative disablement.

---

## Setup & Running Instructions

### Prerequisites
- Node.js >= 18
- npm or yarn
- Android SDK & Android Studio (for Android builds) or Xcode (for iOS builds on macOS)

### 1. Install Dependencies
```bash
cd mobile-driver
npm install
```

### 2. Configure Backend Host
Open `src/config.js`:
- **Android Emulator**: Uses `http://10.0.2.2/All-Projects/MY-PROJECTS/Tours_travels/api/mobile/v1` (Default).
- **Physical Device**: Replace `10.0.2.2` with your development PC's local Wi-Fi IP address (e.g., `http://192.168.1.105/All-Projects/MY-PROJECTS/Tours_travels/api/mobile/v1`).
- **Production Server**: Use your HTTPS production domain (e.g., `https://api.voyagego.com/api/mobile/v1`).

### 3. Run on Android Emulator or Device
Ensure your Android emulator is running or a device is connected via USB with USB Debugging enabled:
```bash
adb devices
npm run android
```

### 4. Run Metro Bundler
```bash
npm start
```
