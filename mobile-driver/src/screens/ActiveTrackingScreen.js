/**
 * VoyageGo Live Active Telemetry & Background Tracking Radar Screen
 */
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  Platform,
  Alert,
} from 'react-native';
import { backgroundLocation } from '../services/BackgroundLocationService';
import { locationQueue } from '../services/LocationQueueService';
import { useAuth } from '../context/AuthContext';

export default function ActiveTrackingScreen() {
  const { isTracking, driver } = useAuth();
  const [fix, setFix] = useState(backgroundLocation.getLastFix());
  const [logs, setLogs] = useState([]);
  const [pendingCount, setPendingCount] = useState(0);

  useEffect(() => {
    updatePending();

    const unsub = backgroundLocation.subscribe((event, data) => {
      if (event === 'location_update') {
        setFix(data);
        setLogs((prev) => [
          {
            id: String(Date.now()),
            time: new Date().toLocaleTimeString(),
            lat: data.latitude.toFixed(5),
            lng: data.longitude.toFixed(5),
            speed: data.speed_kmh,
            acc: data.accuracy_m,
          },
          ...prev.slice(0, 19), // keep last 20 in view
        ]);
        updatePending();
      }
    });

    return () => unsub();
  }, []);

  const updatePending = async () => {
    const count = await locationQueue.getPendingCount();
    setPendingCount(count);
  };

  const handleToggle = async () => {
    try {
      if (isTracking) {
        await backgroundLocation.stopTracking();
      } else {
        await backgroundLocation.startTracking();
      }
    } catch (e) {
      Alert.alert('Error', e.message);
    }
  };

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.content}>
      {/* Radar Status Hero */}
      <View style={[styles.hero, isTracking ? styles.heroActive : styles.heroStandby]}>
        <View style={styles.heroTop}>
          <Text style={styles.heroHeading}>Live Telemetry Radar</Text>
          <View style={[styles.statusBadge, { backgroundColor: isTracking ? '#10B981' : '#64748B' }]}>
            <Text style={styles.statusBadgeText}>{isTracking ? 'BROADCASTING' : 'STANDBY'}</Text>
          </View>
        </View>
        <Text style={styles.heroSub}>
          {isTracking
            ? 'Transmitting high-accuracy background fixes to dispatch'
            : 'Background GPS transmission paused'}
        </Text>

        <TouchableOpacity
          style={[styles.toggleBtn, isTracking ? styles.stopBtn : styles.startBtn]}
          onPress={handleToggle}
        >
          <Text style={styles.toggleBtnText}>
            {isTracking ? 'Pause Telemetry Sharing' : 'Start Live Telemetry'}
          </Text>
        </TouchableOpacity>
      </View>

      {/* Metrics Grid */}
      <View style={styles.grid}>
        <View style={styles.gridItem}>
          <Text style={styles.metricLabel}>CURRENT SPEED</Text>
          <Text style={styles.metricValue}>{fix ? fix.speed_kmh : '0.0'}</Text>
          <Text style={styles.metricUnit}>km/h</Text>
        </View>

        <View style={styles.gridItem}>
          <Text style={styles.metricLabel}>GPS ACCURACY</Text>
          <Text style={[styles.metricValue, { color: '#0284C7' }]}>
            {fix && fix.accuracy_m !== null ? `&plusmn;${fix.accuracy_m}` : '—'}
          </Text>
          <Text style={styles.metricUnit}>meters</Text>
        </View>

        <View style={styles.gridItem}>
          <Text style={styles.metricLabel}>HEADING</Text>
          <Text style={styles.metricValue}>
            {fix && fix.heading_degrees !== null ? `${Math.round(fix.heading_degrees)}&deg;` : '—'}
          </Text>
          <Text style={styles.metricUnit}>azimuth</Text>
        </View>

        <View style={styles.gridItem}>
          <Text style={styles.metricLabel}>OFFLINE QUEUE</Text>
          <Text style={[styles.metricValue, pendingCount > 0 && { color: '#F59E0B' }]}>
            {pendingCount}
          </Text>
          <Text style={styles.metricUnit}>cached fixes</Text>
        </View>
      </View>

      {/* Geolocation Coordinates Box */}
      <View style={styles.coordBox}>
        <Text style={styles.coordTitle}>WGS-84 Geospatial Coordinates</Text>
        <View style={styles.coordRow}>
          <Text style={styles.coordLabel}>Latitude:</Text>
          <Text style={styles.coordValue}>{fix ? fix.latitude?.toFixed(6) : '28.613900'}</Text>
        </View>
        <View style={styles.coordRow}>
          <Text style={styles.coordLabel}>Longitude:</Text>
          <Text style={styles.coordValue}>{fix ? fix.longitude?.toFixed(6) : '77.209000'}</Text>
        </View>
        <View style={styles.coordRow}>
          <Text style={styles.coordLabel}>Last Fix Time:</Text>
          <Text style={styles.coordValue}>
            {fix ? new Date(fix.timestamp).toLocaleTimeString() : 'Awaiting GPS lock'}
          </Text>
        </View>
      </View>

      {/* Real-time Breadcrumb Feed */}
      <View style={styles.card}>
        <Text style={styles.cardHeading}>Recent Telemetry Transmissions</Text>
        {logs.length === 0 ? (
          <Text style={styles.emptyLogs}>No transmissions recorded in current session.</Text>
        ) : (
          logs.map((log) => (
            <View key={log.id} style={styles.logRow}>
              <Text style={styles.logTime}>{log.time}</Text>
              <Text style={styles.logCoords}>
                {log.lat}, {log.lng}
              </Text>
              <Text style={styles.logSpeed}>{log.speed} km/h</Text>
              <Text style={styles.logAcc}>&plusmn;{log.acc}m</Text>
            </View>
          ))
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
  content: {
    padding: 16,
    paddingBottom: 40,
  },
  hero: {
    borderRadius: 14,
    padding: 18,
    marginBottom: 16,
  },
  heroActive: {
    backgroundColor: '#064E3B',
  },
  heroStandby: {
    backgroundColor: '#0B192C',
  },
  heroTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4,
  },
  heroHeading: {
    color: '#FFFFFF',
    fontSize: 18,
    fontWeight: '800',
  },
  heroSub: {
    color: '#CBD5E1',
    fontSize: 12,
    marginBottom: 14,
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
  },
  statusBadgeText: {
    color: '#FFFFFF',
    fontSize: 10,
    fontWeight: '800',
  },
  toggleBtn: {
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
  toggleBtnText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '700',
  },
  grid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
    marginBottom: 16,
  },
  gridItem: {
    flex: 1,
    minWidth: '45%',
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 14,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    alignItems: 'center',
  },
  metricLabel: {
    fontSize: 10,
    fontWeight: '700',
    color: '#64748B',
    letterSpacing: 0.5,
    marginBottom: 4,
  },
  metricValue: {
    fontSize: 22,
    fontWeight: '800',
    color: '#0B192C',
  },
  metricUnit: {
    fontSize: 11,
    color: '#94A3B8',
    marginTop: 2,
  },
  coordBox: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 14,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    marginBottom: 16,
  },
  coordTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: '#0B192C',
    marginBottom: 10,
  },
  coordRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 4,
    borderBottomWidth: 1,
    borderBottomColor: '#F8FAFC',
  },
  coordLabel: {
    fontSize: 12,
    color: '#64748B',
  },
  coordValue: {
    fontSize: 12,
    fontWeight: '700',
    color: '#0B192C',
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 14,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  cardHeading: {
    fontSize: 14,
    fontWeight: '800',
    color: '#0B192C',
    marginBottom: 10,
  },
  emptyLogs: {
    fontSize: 12,
    color: '#94A3B8',
    textAlign: 'center',
    paddingVertical: 16,
  },
  logRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 6,
    borderBottomWidth: 1,
    borderBottomColor: '#F1F5F9',
  },
  logTime: {
    fontSize: 11,
    color: '#64748B',
  },
  logCoords: {
    fontSize: 11,
    color: '#0B192C',
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
  },
  logSpeed: {
    fontSize: 11,
    color: '#0284C7',
    fontWeight: '600',
  },
  logAcc: {
    fontSize: 11,
    color: '#64748B',
  },
});
