/**
 * VoyageGo Chauffeur Mobile Application Configuration
 */
import { Platform } from 'react-native';

// For Android Emulator, 10.0.2.2 maps to the host machine's localhost (XAMPP Apache).
// For physical devices on the same Wi-Fi network, replace with your development machine's local IP (e.g. 192.168.1.100).
export const DEFAULT_HOST = Platform.OS === 'android' ? 'http://10.0.2.2' : 'http://localhost';
export const APP_BASE_PATH = '/All-Projects/MY-PROJECTS/Tours_travels';

export const API_BASE_URL = `${DEFAULT_HOST}${APP_BASE_PATH}/api/mobile/v1`;

export const CONFIG = {
  apiBaseUrl: API_BASE_URL,
  gpsIntervalMs: 10000, // 10 seconds background interval
  highAccuracy: true,
  distanceFilterMeters: 5, // report if moved 5+ meters
  offlineSyncMaxBatch: 50,
  maxQueueSize: 2000,
  appVersion: '1.2.0',
};
