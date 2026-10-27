/**
 * VoyageGo Chauffeur Dashboard Screen
 */
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  RefreshControl,
  ActivityIndicator,
  Alert,
} from 'react-native';
import { useAuth } from '../context/AuthContext';
import { backgroundLocation } from '../services/BackgroundLocationService';
import { locationQueue } from '../services/LocationQueueService';
import { api } from '../api/client';

export default function DashboardScreen({ navigation }) {
  const { driver, activeTrip, isTracking, refreshProfile } = useAuth();
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [pendingPoints, setPendingPoints] = useState(0);
  const [isSyncingQueue, setIsSyncingQueue] = useState(false);
  const [lastFix, setLastFix] = useState(null);

  useEffect(() => {
    checkQueue();
    const interval = setInterval(checkQueue, 5000);

    const unsub = backgroundLocation.subscribe((event, data) => {
      if (event === 'location_update') {
        setLastFix(data);
      }
    });

    return () => {
      clearInterval(interval);
      unsub();
    };
  }, []);

  const checkQueue = async () => {
    const count = await locationQueue.getPendingCount();
    setPendingPoints(count);
  };

  const onRefresh = async () => {
    setIsRefreshing(true);
    await refreshProfile();
    await checkQueue();
    setIsRefreshing(false);
  };

  const handleToggleTracking = async () => {
    try {
      if (isTracking) {
        await backgroundLocation.stopTracking();
        Alert.alert('Tracking Stopped', 'Background GPS broadcasting has been paused.');
      } else {
        await backgroundLocation.startTracking();
        Alert.alert('Tracking Active', 'Live telemetry is now broadcasting to VoyageGo central fleet dispatch.');
      }
    } catch (e) {
      if (e.message === 'LOCATION_PERMISSION_DENIED') {
        Alert.alert('Permission Required', 'Precise location permission is required to enable chauffeur tracking.');
      } else {
        Alert.alert('Error', e.message);
      }
    }
  };

  const handleManualQueueSync = async () => {
    if (pendingPoints === 0) {
      Alert.alert('Queue Clear', 'All telemetry points have been synchronized with the dispatch server.');
      return;
    }

    setIsSyncingQueue(true);
    try {
      const res = await locationQueue.flush();
      await checkQueue();
      if (res && res.success) {
        Alert.alert('Queue Synchronized', `Successfully transmitted ${res.count} cached GPS fixes to server.`);
      }
    } catch (e) {
      Alert.alert('Sync Delayed', 'Server unreachable. Cached breadcrumbs remain saved safely on device.');
    } finally {
      setIsSyncingQueue(false);
    }
  };

  const isOnDuty = (driver?.duty_status || 'off_duty') !== 'off_duty';

  return (
    <ScrollView
      style={styles.container}
      refreshControl={<RefreshControl refreshing={isRefreshing} onRefresh={onRefresh} tintColor="#F59E0B" />}
    >
      {/* Chauffeur Header */}
      <View style={styles.header}>
        <View style={styles.headerTop}>
          <View>
            <Text style={styles.welcomeText}>Chauffeur Console</Text>
            <Text style={styles.driverName}>{driver?.name || 'Authorized Chauffeur'}</Text>
          </View>
          <View style={styles.ratingBadge}>
            <Text style={styles.ratingText}>⭐ {driver?.rating || '5.0'}</Text>
          </View>
        </View>

        {/* Duty Status Bar */}
        <View style={styles.dutyBar}>
          <View style={styles.dutyPill}>
            <View style={[styles.statusDot, { backgroundColor: isOnDuty ? '#10B981' : '#94A3B8' }]} />
            <Text style={styles.dutyText}>
              Duty: <Text style={{ fontWeight: '800' }}>{isOnDuty ? 'ON DUTY' : 'OFF DUTY'}</Text>
            </Text>
          </View>
          <Text style={styles.vehicleText}>DL: {driver?.license_number || 'Verified'}</Text>
        </View>
      </View>

      <View style={styles.content}>
        {/* Background Telemetry Broadcast Card */}
        <View style={[styles.card, isTracking ? styles.cardActive : styles.cardInactive]}>
          <View style={styles.cardHeader}>
            <View style={styles.radarIconWrap}>
              <Text style={{ fontSize: 20 }}>📡</Text>
            </View>
            <View style={{ flex: 1, marginLeft: 12 }}>
              <Text style={styles.cardTitle}>Live GPS Telemetry</Text>
              <Text style={styles.cardSubtitle}>
                {isTracking ? 'Active Background Broadcasting' : 'Tracking Paused / Standby'}
              </Text>
            </View>
            <View style={[styles.signalBadge, { backgroundColor: isTracking ? '#10B981' : '#64748B' }]}>
              <Text style={styles.signalBadgeText}>{isTracking ? 'LIVE' : 'STANDBY'}</Text>
            </View>
          </View>

          {lastFix && (
            <View style={styles.fixDetails}>
              <Text style={styles.fixDetailText}>Lat: {lastFix.latitude?.toFixed(5)}  Lng: {lastFix.longitude?.toFixed(5)}</Text>
              <Text style={styles.fixDetailText}>Speed: {lastFix.speed_kmh} km/h • Acc: &plusmn;{lastFix.accuracy_m}m</Text>
            </View>
          )}

          <TouchableOpacity
            style={[styles.actionBtn, isTracking ? styles.stopBtn : styles.startBtn]}
            onPress={handleToggleTracking}
          >
            <Text style={styles.actionBtnText}>
              {isTracking ? 'Pause Telemetry Sharing' : 'Start Background Telemetry'}
            </Text>
          </TouchableOpacity>
        </View>

        {/* Offline Queue Status Card */}
        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={[styles.radarIconWrap, { backgroundColor: '#F1F5F9' }]}>
              <Text style={{ fontSize: 20 }}>💾</Text>
            </View>
            <View style={{ flex: 1, marginLeft: 12 }}>
              <Text style={styles.cardTitle}>Offline Geolocation Queue</Text>
              <Text style={styles.cardSubtitle}>
                {pendingPoints > 0 ? `${pendingPoints} breadcrumbs awaiting sync` : 'All telemetry points synchronized'}
              </Text>
            </View>
            <View style={[styles.queueCountBadge, pendingPoints > 0 && { backgroundColor: '#F59E0B' }]}>
              <Text style={styles.queueCountText}>{pendingPoints}</Text>
            </View>
          </View>

          <TouchableOpacity
            style={[styles.secondaryBtn, pendingPoints === 0 && { opacity: 0.5 }]}
            onPress={handleManualQueueSync}
            disabled={isSyncingQueue || pendingPoints === 0}
          >
            {isSyncingQueue ? (
              <ActivityIndicator color="#0B192C" />
            ) : (
              <Text style={styles.secondaryBtnText}>
                {pendingPoints > 0 ? `Transmit ${pendingPoints} Cached Fixes` : 'Offline Queue Synced'}
              </Text>
            )}
          </TouchableOpacity>
        </View>

        {/* Active Trip Hero Card (if assigned) */}
        {activeTrip ? (
          <View style={[styles.card, { borderColor: '#0284C7', borderWidth: 2 }]}>
            <View style={styles.activeTripRibbon}>
              <Text style={styles.activeTripRibbonText}>ACTIVE ASSIGNED TRIP</Text>
            </View>

            <Text style={styles.tripCode}>{activeTrip.booking_code || activeTrip.dispatch_code}</Text>
            <Text style={styles.tripGuest}>Guest: {activeTrip.customer_name || 'VIP Client'}</Text>

            <View style={styles.routeBox}>
              <Text style={styles.routeLine} numberOfLines={1}>📍 {activeTrip.pickup_address || 'Pickup Point'}</Text>
              <Text style={styles.routeLine} numberOfLines={1}>🏁 {activeTrip.drop_address || 'Destination'}</Text>
            </View>

            <TouchableOpacity
              style={styles.openTripBtn}
              onPress={() => navigation.navigate('TripDetail', { dispatchId: activeTrip.id })}
            >
              <Text style={styles.openTripBtnText}>Open Trip Management</Text>
            </TouchableOpacity>
          </View>
        ) : (
          <View style={styles.emptyTripCard}>
            <Text style={{ fontSize: 32, marginBottom: 8 }}>🧳</Text>
            <Text style={styles.emptyTripTitle}>No Active Dispatches</Text>
            <Text style={styles.emptyTripSubtitle}>
              You are currently on standby. Operations dispatch will assign luxury tours and transfers to your console.
            </Text>
          </View>
        )}
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8FAFC',
  },
  header: {
    backgroundColor: '#0B192C',
    paddingHorizontal: 20,
    paddingTop: 24,
    paddingBottom: 20,
    borderBottomLeftRadius: 20,
    borderBottomRightRadius: 20,
  },
  headerTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  welcomeText: {
    color: '#94A3B8',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 1,
    textTransform: 'uppercase',
  },
  driverName: {
    color: '#FFFFFF',
    fontSize: 22,
    fontWeight: '800',
    marginTop: 2,
  },
  ratingBadge: {
    backgroundColor: 'rgba(255, 255, 255, 0.1)',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 20,
  },
  ratingText: {
    color: '#F59E0B',
    fontSize: 13,
    fontWeight: '800',
  },
  dutyBar: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.06)',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 10,
  },
  dutyPill: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  statusDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    marginRight: 6,
  },
  dutyText: {
    color: '#E2E8F0',
    fontSize: 12,
  },
  vehicleText: {
    color: '#94A3B8',
    fontSize: 12,
  },
  content: {
    padding: 16,
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 14,
    padding: 16,
    marginBottom: 14,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    shadowColor: '#0B192C',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 6,
    elevation: 2,
  },
  cardActive: {
    borderColor: '#10B981',
    backgroundColor: '#F0FDF4',
  },
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  radarIconWrap: {
    width: 42,
    height: 42,
    borderRadius: 12,
    backgroundColor: '#E0F2FE',
    alignItems: 'center',
    justifyContent: 'center',
  },
  cardTitle: {
    fontSize: 15,
    fontWeight: '800',
    color: '#0B192C',
  },
  cardSubtitle: {
    fontSize: 12,
    color: '#64748B',
    marginTop: 2,
  },
  signalBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
  },
  signalBadgeText: {
    color: '#FFFFFF',
    fontSize: 10,
    fontWeight: '800',
  },
  fixDetails: {
    backgroundColor: '#FFFFFF',
    padding: 10,
    borderRadius: 8,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  fixDetailText: {
    fontSize: 12,
    color: '#334155',
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
  },
  actionBtn: {
    borderRadius: 8,
    paddingVertical: 12,
    alignItems: 'center',
  },
  startBtn: {
    backgroundColor: '#0284C7',
  },
  stopBtn: {
    backgroundColor: '#EF4444',
  },
  actionBtnText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '700',
  },
  queueCountBadge: {
    backgroundColor: '#94A3B8',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 12,
  },
  queueCountText: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: '800',
  },
  secondaryBtn: {
    backgroundColor: '#F1F5F9',
    borderRadius: 8,
    paddingVertical: 10,
    alignItems: 'center',
  },
  secondaryBtnText: {
    color: '#0B192C',
    fontSize: 13,
    fontWeight: '700',
  },
  activeTripRibbon: {
    backgroundColor: '#0284C7',
    alignSelf: 'flex-start',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 4,
    marginBottom: 8,
  },
  activeTripRibbonText: {
    color: '#FFFFFF',
    fontSize: 10,
    fontWeight: '800',
  },
  tripCode: {
    fontSize: 18,
    fontWeight: '800',
    color: '#0B192C',
    marginBottom: 4,
  },
  tripGuest: {
    fontSize: 13,
    color: '#334155',
    marginBottom: 10,
  },
  routeBox: {
    backgroundColor: '#F8FAFC',
    padding: 10,
    borderRadius: 8,
    marginBottom: 12,
  },
  routeLine: {
    fontSize: 12,
    color: '#475569',
    marginVertical: 2,
  },
  openTripBtn: {
    backgroundColor: '#0B192C',
    borderRadius: 8,
    paddingVertical: 12,
    alignItems: 'center',
  },
  openTripBtnText: {
    color: '#F59E0B',
    fontSize: 14,
    fontWeight: '700',
  },
  emptyTripCard: {
    alignItems: 'center',
    padding: 24,
    backgroundColor: '#FFFFFF',
    borderRadius: 14,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  emptyTripTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: '#0B192C',
    marginBottom: 4,
  },
  emptyTripSubtitle: {
    fontSize: 12,
    color: '#64748B',
    textAlign: 'center',
    lineHeight: 18,
  },
});
