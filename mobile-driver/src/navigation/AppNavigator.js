/**
 * Navigation Hierarchy for VoyageGo Chauffeur Mobile Application
 */
import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';

import { useAuth } from '../context/AuthContext';
import LoginScreen from '../screens/LoginScreen';
import DashboardScreen from '../screens/DashboardScreen';
import TripsScreen from '../screens/TripsScreen';
import TripDetailScreen from '../screens/TripDetailScreen';
import ActiveTrackingScreen from '../screens/ActiveTrackingScreen';
import ProfileScreen from '../screens/ProfileScreen';

const Stack = createNativeStackNavigator();
const Tab = createBottomTabNavigator();

function TabIcon({ symbol, label, focused }) {
  return (
    <View style={styles.tabItem}>
      <Text style={[styles.tabSymbol, focused && styles.tabSymbolActive]}>{symbol}</Text>
      <Text style={[styles.tabLabel, focused && styles.tabLabelActive]}>{label}</Text>
    </View>
  );
}

function MainTabNavigator() {
  return (
    <Tab.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: '#0B192C' },
        headerTintColor: '#FFFFFF',
        headerTitleStyle: { fontWeight: '800', fontSize: 17 },
        tabBarStyle: {
          backgroundColor: '#0B192C',
          borderTopColor: 'rgba(255, 255, 255, 0.1)',
          height: 64,
          paddingBottom: 8,
          paddingTop: 8,
        },
        tabBarShowLabel: false,
      }}
    >
      <Tab.Screen
        name="DashboardTab"
        component={DashboardScreen}
        options={{
          title: 'Command Center',
          tabBarIcon: ({ focused }) => <TabIcon symbol="🎛️" label="Overview" focused={focused} />,
        }}
      />
      <Tab.Screen
        name="TripsTab"
        component={TripsScreen}
        options={{
          title: 'Assigned Trips',
          tabBarIcon: ({ focused }) => <TabIcon symbol="🚗" label="Trips" focused={focused} />,
        }}
      />
      <Tab.Screen
        name="RadarTab"
        component={ActiveTrackingScreen}
        options={{
          title: 'Live Telemetry',
          tabBarIcon: ({ focused }) => <TabIcon symbol="📡" label="Radar" focused={focused} />,
        }}
      />
      <Tab.Screen
        name="ProfileTab"
        component={ProfileScreen}
        options={{
          title: 'Chauffeur Profile',
          tabBarIcon: ({ focused }) => <TabIcon symbol="👤" label="Account" focused={focused} />,
        }}
      />
    </Tab.Navigator>
  );
}

export default function AppNavigator() {
  const { isAuthenticated, isLoading } = useAuth();

  if (isLoading) {
    return (
      <View style={styles.splash}>
        <View style={styles.splashLogo}>
          <Text style={styles.splashLogoText}>VG</Text>
        </View>
        <Text style={styles.splashText}>VoyageGo Chauffeur Suite</Text>
      </View>
    );
  }

  return (
    <NavigationContainer>
      <Stack.Navigator screenOptions={{ headerShown: false }}>
        {!isAuthenticated ? (
          <Stack.Screen name="Login" component={LoginScreen} />
        ) : (
          <>
            <Stack.Screen name="MainTabs" component={MainTabNavigator} />
            <Stack.Screen
              name="TripDetail"
              component={TripDetailScreen}
              options={{
                headerShown: true,
                title: 'Trip Details',
                headerStyle: { backgroundColor: '#0B192C' },
                headerTintColor: '#FFFFFF',
                headerTitleStyle: { fontWeight: '800' },
              }}
            />
          </>
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
}

const styles = StyleSheet.create({
  tabItem: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  tabSymbol: {
    fontSize: 18,
    opacity: 0.6,
  },
  tabSymbolActive: {
    opacity: 1,
  },
  tabLabel: {
    fontSize: 10,
    fontWeight: '600',
    color: '#94A3B8',
    marginTop: 2,
  },
  tabLabelActive: {
    color: '#F59E0B',
    fontWeight: '800',
  },
  splash: {
    flex: 1,
    backgroundColor: '#0B192C',
    alignItems: 'center',
    justifyContent: 'center',
  },
  splashLogo: {
    width: 68,
    height: 68,
    borderRadius: 20,
    backgroundColor: '#0284C7',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  splashLogoText: {
    color: '#FFF',
    fontSize: 28,
    fontWeight: '800',
  },
  splashText: {
    color: '#F59E0B',
    fontSize: 13,
    fontWeight: '700',
    letterSpacing: 1,
  },
});
