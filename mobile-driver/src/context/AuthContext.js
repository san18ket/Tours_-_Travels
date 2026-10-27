/**
 * VoyageGo Authentication Context for React Native Driver App
 */
import React, { createContext, useState, useEffect, useContext } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { api, STORAGE_KEYS } from '../api/client';
import { backgroundLocation } from '../services/BackgroundLocationService';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [isLoading, setIsLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [driver, setDriver] = useState(null);
  const [activeTrip, setActiveTrip] = useState(null);
  const [isTracking, setIsTracking] = useState(false);

  useEffect(() => {
    checkAuthSession();

    const unsubTracking = backgroundLocation.subscribe((event, data) => {
      if (event === 'status_change') {
        setIsTracking(data.isTracking);
      }
    });

    return () => unsubTracking();
  }, []);

  const checkAuthSession = async () => {
    try {
      const token = await AsyncStorage.getItem(STORAGE_KEYS.ACCESS_TOKEN);
      if (token) {
        const rawDriver = await AsyncStorage.getItem(STORAGE_KEYS.DRIVER_DATA);
        if (rawDriver) setDriver(JSON.parse(rawDriver));
        setIsAuthenticated(true);

        // Fetch fresh profile in background
        refreshProfile();
      }
    } catch (e) {
      console.warn('Session check failed:', e);
    } finally {
      setIsLoading(false);
    }
  };

  const refreshProfile = async () => {
    try {
      const res = await api.getMe();
      if (res && res.success) {
        setDriver(res.driver);
        setActiveTrip(res.active_trip || null);
        await AsyncStorage.setItem(STORAGE_KEYS.DRIVER_DATA, JSON.stringify(res.driver));
      }
    } catch (e) {
      if (e.message === 'SESSION_EXPIRED') {
        logout();
      }
    }
  };

  const login = async (username, password) => {
    let deviceUuid = await AsyncStorage.getItem(STORAGE_KEYS.DEVICE_UUID);
    if (!deviceUuid) {
      deviceUuid = `uuid_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
      await AsyncStorage.setItem(STORAGE_KEYS.DEVICE_UUID, deviceUuid);
    }

    const res = await api.login({
      login: username,
      password: password,
      device_uuid: deviceUuid,
      device_name: 'Driver Smartphone',
      platform: 'android',
      app_version: '1.2.0',
    });

    if (res.success) {
      setDriver(res.driver);
      setActiveTrip(res.active_trip || null);
      setIsAuthenticated(true);
      return { success: true };
    }
    return { success: false, error: res.error || 'Authentication failed' };
  };

  const logout = async () => {
    await backgroundLocation.stopTracking();
    await api.logout();
    setDriver(null);
    setActiveTrip(null);
    setIsAuthenticated(false);
  };

  return (
    <AuthContext.Provider
      value={{
        isLoading,
        isAuthenticated,
        driver,
        activeTrip,
        isTracking,
        login,
        logout,
        refreshProfile,
        setActiveTrip,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
