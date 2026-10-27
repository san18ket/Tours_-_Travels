/**
 * VoyageGo Chauffeur Profile & Settings Screen
 */
import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  Alert,
} from 'react-native';
import { useAuth } from '../context/AuthContext';
import { CONFIG } from '../config';

export default function ProfileScreen() {
  const { driver, logout } = useAuth();

  const handleLogout = () => {
    Alert.alert(
      'Sign Out Confirmation',
      'Are you sure you want to sign out of the chauffeur console? Background telemetry broadcasting will be halted.',
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Sign Out', style: 'destructive', onPress: logout },
      ]
    );
  };

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.content}>
      {/* Avatar Card */}
      <View style={styles.avatarCard}>
        <View style={styles.avatarCircle}>
          <Text style={styles.avatarText}>
            {driver?.name ? driver.name.charAt(0).toUpperCase() : 'C'}
          </Text>
        </View>
        <Text style={styles.driverName}>{driver?.name || 'Authorized Chauffeur'}</Text>
        <Text style={styles.roleTitle}>Executive Chauffeur • VoyageGo Fleet</Text>
        <View style={styles.ratingBadge}>
          <Text style={styles.ratingText}>⭐ {driver?.rating || '5.0'} / 5.0 Rating</Text>
        </View>
      </View>

      {/* Chauffeur Credentials */}
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Operational Credentials</Text>
        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Driving License Number</Text>
          <Text style={styles.infoValue}>{driver?.license_number || 'DL-PENDING'}</Text>
        </View>
        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>License Expiry Date</Text>
          <Text style={styles.infoValue}>{driver?.license_expiry || 'Verified'}</Text>
        </View>
        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Experience Level</Text>
          <Text style={styles.infoValue}>{driver?.experience_years || 1} Years</Text>
        </View>
        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Emergency Contact</Text>
          <Text style={styles.infoValue}>{driver?.emergency_contact || 'Operations Dispatch'}</Text>
        </View>
      </View>

      {/* Account Info */}
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Contact Details</Text>
        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Registered Mobile</Text>
          <Text style={styles.infoValue}>{driver?.phone || '—'}</Text>
        </View>
        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Email Address</Text>
          <Text style={styles.infoValue}>{driver?.email || '—'}</Text>
        </View>
      </View>

      {/* System Information */}
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Application &amp; Security</Text>
        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Native Console Version</Text>
          <Text style={styles.infoValue}>v{CONFIG.appVersion}</Text>
        </View>
        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Background Geolocation</Text>
          <Text style={[styles.infoValue, { color: '#10B981', fontWeight: '800' }]}>Enabled (OS Service)</Text>
        </View>
        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Offline Queueing</Text>
          <Text style={[styles.infoValue, { color: '#0284C7', fontWeight: '800' }]}>Active (AsyncStorage)</Text>
        </View>
      </View>

      {/* Sign Out Button */}
      <TouchableOpacity style={styles.logoutBtn} onPress={handleLogout}>
        <Text style={styles.logoutBtnText}>Sign Out of Chauffeur Console</Text>
      </TouchableOpacity>
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
  avatarCard: {
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    borderRadius: 14,
    padding: 24,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  avatarCircle: {
    width: 72,
    height: 72,
    borderRadius: 36,
    backgroundColor: '#0B192C',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  avatarText: {
    color: '#F59E0B',
    fontSize: 30,
    fontWeight: '800',
  },
  driverName: {
    fontSize: 20,
    fontWeight: '800',
    color: '#0B192C',
  },
  roleTitle: {
    fontSize: 13,
    color: '#64748B',
    marginTop: 2,
  },
  ratingBadge: {
    backgroundColor: '#FEF3C7',
    paddingHorizontal: 12,
    paddingVertical: 5,
    borderRadius: 20,
    marginTop: 10,
  },
  ratingText: {
    color: '#92400E',
    fontWeight: '800',
    fontSize: 12,
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
    marginBottom: 14,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  cardTitle: {
    fontSize: 13,
    fontWeight: '800',
    color: '#0B192C',
    marginBottom: 10,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#F8FAFC',
  },
  infoLabel: {
    fontSize: 13,
    color: '#64748B',
  },
  infoValue: {
    fontSize: 13,
    fontWeight: '700',
    color: '#0B192C',
  },
  logoutBtn: {
    backgroundColor: '#FEF2F2',
    borderWidth: 1,
    borderColor: '#EF4444',
    borderRadius: 10,
    paddingVertical: 14,
    alignItems: 'center',
    marginTop: 8,
  },
  logoutBtnText: {
    color: '#DC2626',
    fontSize: 14,
    fontWeight: '800',
  },
});
