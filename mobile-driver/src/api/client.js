/**
 * VoyageGo API Client with Automatic Token Injection & Refresh Interceptors
 */
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_BASE_URL } from '../config';

const STORAGE_KEYS = {
  ACCESS_TOKEN: '@voyagego_access_token',
  REFRESH_TOKEN: '@voyagego_refresh_token',
  DRIVER_DATA: '@voyagego_driver_profile',
  DEVICE_UUID: '@voyagego_device_uuid',
};

class ApiClient {
  constructor() {
    this.baseUrl = API_BASE_URL;
    this.isRefreshing = false;
    this.refreshSubscribers = [];
  }

  async getAccessToken() {
    return await AsyncStorage.getItem(STORAGE_KEYS.ACCESS_TOKEN);
  }

  async getRefreshToken() {
    return await AsyncStorage.getItem(STORAGE_KEYS.REFRESH_TOKEN);
  }

  async saveAuthTokens(accessToken, refreshToken) {
    if (accessToken) await AsyncStorage.setItem(STORAGE_KEYS.ACCESS_TOKEN, accessToken);
    if (refreshToken) await AsyncStorage.setItem(STORAGE_KEYS.REFRESH_TOKEN, refreshToken);
  }

  async clearTokens() {
    await AsyncStorage.multiRemove([
      STORAGE_KEYS.ACCESS_TOKEN,
      STORAGE_KEYS.REFRESH_TOKEN,
      STORAGE_KEYS.DRIVER_DATA,
    ]);
  }

  async request(endpoint, options = {}) {
    const url = `${this.baseUrl}${endpoint}`;
    const token = await this.getAccessToken();

    const headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
      ...(options.headers || {}),
    };

    const config = {
      ...options,
      headers,
    };

    if (config.body && typeof config.body === 'object') {
      config.body = JSON.stringify(config.body);
    }

    try {
      const response = await fetch(url, config);

      // Handle 401 Unauthorized / Token Expired
      if (response.status === 401 && !endpoint.includes('/auth/login') && !endpoint.includes('/auth/refresh')) {
        const refreshed = await this.tryRefreshToken();
        if (refreshed) {
          // Retry original request with newly refreshed token
          const newToken = await this.getAccessToken();
          headers['Authorization'] = `Bearer ${newToken}`;
          const retryResponse = await fetch(url, { ...config, headers });
          return await retryResponse.json();
        } else {
          await this.clearTokens();
          throw new Error('SESSION_EXPIRED');
        }
      }

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        throw new Error(data.error || `HTTP_${response.status}`);
      }

      return data;
    } catch (error) {
      if (error.message === 'SESSION_EXPIRED') throw error;
      // Network failure or offline
      throw error;
    }
  }

  async tryRefreshToken() {
    if (this.isRefreshing) {
      return new Promise((resolve) => {
        this.refreshSubscribers.push(resolve);
      });
    }

    this.isRefreshing = true;
    try {
      const refreshToken = await this.getRefreshToken();
      if (!refreshToken) return false;

      const response = await fetch(`${this.baseUrl}/auth/refresh`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ refresh_token: refreshToken }),
      });

      const data = await response.json();
      if (response.ok && data.success && data.auth) {
        await this.saveAuthTokens(data.auth.access_token, data.auth.refresh_token);
        this.notifySubscribers(true);
        return true;
      }
      this.notifySubscribers(false);
      return false;
    } catch (e) {
      this.notifySubscribers(false);
      return false;
    } finally {
      this.isRefreshing = false;
    }
  }

  notifySubscribers(success) {
    this.refreshSubscribers.forEach((callback) => callback(success));
    this.refreshSubscribers = [];
  }

  // API Methods
  async login(credentials) {
    const res = await this.request('/auth/login', {
      method: 'POST',
      body: credentials,
    });
    if (res.success && res.auth) {
      await this.saveAuthTokens(res.auth.access_token, res.auth.refresh_token);
      if (res.driver) {
        await AsyncStorage.setItem(STORAGE_KEYS.DRIVER_DATA, JSON.stringify(res.driver));
      }
    }
    return res;
  }

  async logout() {
    try {
      await this.request('/auth/logout', { method: 'POST' });
    } catch (e) {
      // Ignore network errors on logout
    } finally {
      await this.clearTokens();
    }
  }

  async getMe() {
    return await this.request('/driver/me', { method: 'GET' });
  }

  async getTrips() {
    return await this.request('/driver/trips', { method: 'GET' });
  }

  async getTrackingStatus() {
    return await this.request('/tracking/status', { method: 'GET' });
  }

  async startTracking() {
    return await this.request('/tracking/start', { method: 'POST' });
  }

  async stopTracking() {
    return await this.request('/tracking/stop', { method: 'POST' });
  }

  async syncGpsBatch(locations) {
    return await this.request('/gps', {
      method: 'POST',
      body: { locations },
    });
  }

  async syncGpsSingle(fix) {
    return await this.request('/gps', {
      method: 'POST',
      body: fix,
    });
  }
}

export const api = new ApiClient();
export { STORAGE_KEYS };
