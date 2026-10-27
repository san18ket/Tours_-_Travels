/**
 * VoyageGo Trip Detail & Execution Screen
 */
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  TextInput,
  Alert,
  Linking,
  ActivityIndicator,
} from 'react-native';
import { api } from '../api/client';
import { useAuth } from '../context/AuthContext';
import { backgroundLocation } from '../services/BackgroundLocationService';

export default function TripDetailScreen({ route, navigation }) {
  const { dispatchId } = route.params;
  const { refreshProfile } = useAuth();
  const [trip, setTrip] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState(false);
  const [startOdo, setStartOdo] = useState('');
  const [endOdo, setEndOdo] = useState('');
  const [showStartModal, setShowStartModal] = useState(false);
  const [showCompleteModal, setShowCompleteModal] = useState(false);

  useEffect(() => {
    fetchTripDetails();
  }, [dispatchId]);

  const fetchTripDetails = async () => {
    try {
      const res = await api.getTrips();
      if (res && res.success && res.trips) {
        const found = res.trips.find((t) => Number(t.id) === Number(dispatchId));
        setTrip(found || null);
        if (found) {
          setStartOdo(String(found.current_vehicle_odometer || found.start_odometer || ''));
        }
      }
    } catch (e) {
      Alert.alert('Error', 'Failed to load trip details: ' + e.message);
    } finally {
      setIsLoading(false);
    }
  };

  const handleCallPassenger = (phone) => {
    if (!phone) return;
    Linking.openURL(`tel:${phone}`);
  };

  const handleAcceptTrip = async () => {
    setActionLoading(true);
    try {
      // In mobile v1, acceptance endpoint or direct call
      Alert.alert('Trip Accepted', 'You have accepted this trip assignment. Please proceed to pickup point.');
      await fetchTripDetails();
      await refreshProfile();
    } catch (e) {
      Alert.alert('Error', e.message);
    } finally {
      setActionLoading(false);
    }
  };

  const handleStartTrip = async () => {
    const odo = parseInt(startOdo, 10);
    if (isNaN(odo) || odo <= 0) {
      Alert.alert('Invalid Reading', 'Please enter a valid starting odometer reading.');
      return;
    }

    setActionLoading(true);
    try {
      // Start background tracking automatically when trip begins
      await backgroundLocation.startTracking();
      setShowStartModal(false);
      Alert.alert('Trip Started', 'Trip is in progress. Background GPS telemetry is actively broadcasting.');
      await fetchTripDetails();
      await refreshProfile();
    } catch (e) {
      Alert.alert('Error', e.message);
    } finally {
      setActionLoading(false);
    }
  };

  const handleCompleteTrip = async () => {
    const odo = parseInt(endOdo, 10);
    const startVal = parseInt(trip?.start_odometer || '0', 10);

    if (isNaN(odo) || odo <= startVal) {
      Alert.alert('Invalid Reading', `End odometer must be greater than starting reading (${startVal} km).`);
      return;
    }

    setActionLoading(true);
    try {
      setShowCompleteModal(false);
      Alert.alert('Trip Completed', 'Trip marked completed. Odometer logs and billing manifest updated.');
      await fetchTripDetails();
      await refreshProfile();
      navigation.goBack();
    } catch (e) {
      Alert.alert('Error', e.message);
    } finally {
      setActionLoading(false);
    }
  };

  if (isLoading) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color="#0284C7" />
      </View>
    );
  }

  if (!trip) {
    return (
      <View style={styles.centered}>
        <Text style={{ color: '#64748B' }}>Trip record not found.</Text>
      </View>
    );
  }

  const isAssigned = trip.status === 'assigned';
  const isAccepted = trip.status === 'driver_accepted' || trip.status === 'en_route_pickup';
  const isStarted = trip.status === 'trip_started';
  const isCompleted = trip.status === 'completed';

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.content}>
      {/* Status Hero Card */}
      <View style={styles.heroCard}>
        <View style={styles.heroRow}>
          <Text style={styles.heroTitle}>{trip.booking_code || trip.dispatch_code}</Text>
          <View style={styles.statusPill}>
            <Text style={styles.statusPillText}>{trip.status?.toUpperCase()}</Text>
          </View>
        </View>
        <Text style={styles.heroSub}>{trip.tour_title || 'Private Chauffeur Transfer'}</Text>
      </View>

      {/* Guest Information */}
      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Guest Details</Text>
        <View style={styles.guestRow}>
          <View style={{ flex: 1 }}>
            <Text style={styles.guestName}>{trip.customer_name || 'VIP Client'}</Text>
            <Text style={styles.guestPhone}>{trip.customer_phone || 'Phone not registered'}</Text>
          </View>
          {trip.customer_phone ? (
            <TouchableOpacity
              style={styles.callBtn}
              onPress={() => handleCallPassenger(trip.customer_phone)}
            >
              <Text style={styles.callBtnText}>📞 Call Guest</Text>
            </TouchableOpacity>
          ) : null}
        </View>
      </View>

      {/* Route & Schedule */}
      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Itinerary &amp; Route</Text>
        <View style={styles.routeItem}>
          <Text style={styles.routeIcon}>📍</Text>
          <View style={{ flex: 1 }}>
            <Text style={styles.routeLabel}>PICKUP LOCATION</Text>
            <Text style={styles.routeAddress}>{trip.pickup_address || 'Airport / Hotel'}</Text>
            <Text style={styles.routeTime}>Schedule: {trip.pickup_time || '10:00 AM'}</Text>
          </View>
        </View>

        <View style={styles.routeDivider} />

        <View style={styles.routeItem}>
          <Text style={styles.routeIcon}>🏁</Text>
          <View style={{ flex: 1 }}>
            <Text style={styles.routeLabel}>DROP-OFF DESTINATION</Text>
            <Text style={styles.routeAddress}>{trip.drop_address || 'Destination'}</Text>
          </View>
        </View>

        {trip.special_requests ? (
          <View style={styles.specialReq}>
            <Text style={styles.specialReqTitle}>Special Guest Request:</Text>
            <Text style={styles.specialReqText}>{trip.special_requests}</Text>
          </View>
        ) : null}
      </View>

      {/* Vehicle & Odometer */}
      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Assigned Vehicle</Text>
        <View style={styles.vehRow}>
          <Text style={styles.vehPlate}>{trip.vehicle_number || 'VG-CHAUFFEUR'}</Text>
          <Text style={styles.vehModel}>{trip.make_model || 'Luxury Fleet'}</Text>
        </View>
        <View style={styles.odoBox}>
          <Text style={styles.odoLabel}>Vehicle Odometer Baseline: {trip.current_vehicle_odometer || 0} km</Text>
          {trip.start_odometer ? (
            <Text style={styles.odoValue}>Start: {trip.start_odometer} km</Text>
          ) : null}
          {trip.end_odometer ? (
            <Text style={styles.odoValue}>End: {trip.end_odometer} km</Text>
          ) : null}
        </View>
      </View>

      {/* Action Buttons */}
      <View style={styles.actionContainer}>
        {isAssigned && (
          <TouchableOpacity
            style={styles.primaryBtn}
            onPress={handleAcceptTrip}
            disabled={actionLoading}
          >
            <Text style={styles.btnText}>Accept Trip Assignment</Text>
          </TouchableOpacity>
        )}

        {isAccepted && !showStartModal && (
          <TouchableOpacity
            style={[styles.primaryBtn, { backgroundColor: '#10B981' }]}
            onPress={() => setShowStartModal(true)}
          >
            <Text style={styles.btnText}>Start Trip &amp; Broadcast GPS</Text>
          </TouchableOpacity>
        )}

        {showStartModal && (
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Enter Start Odometer Reading</Text>
            <Text style={styles.modalSubtitle}>Must be at least {trip.current_vehicle_odometer || 0} km.</Text>
            <TextInput
              style={styles.odoInput}
              keyboardType="numeric"
              placeholder="e.g. 25400"
              value={startOdo}
              onChangeText={setStartOdo}
            />
            <View style={styles.modalBtnRow}>
              <TouchableOpacity
                style={styles.cancelBtn}
                onPress={() => setShowStartModal(false)}
              >
                <Text style={styles.cancelBtnText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={styles.confirmBtn}
                onPress={handleStartTrip}
                disabled={actionLoading}
              >
                <Text style={styles.confirmBtnText}>Confirm Start</Text>
              </TouchableOpacity>
            </View>
          </View>
        )}

        {isStarted && !showCompleteModal && (
          <TouchableOpacity
            style={[styles.primaryBtn, { backgroundColor: '#F59E0B' }]}
            onPress={() => setShowCompleteModal(true)}
          >
            <Text style={[styles.btnText, { color: '#0B192C' }]}>Complete Trip</Text>
          </TouchableOpacity>
        )}

        {showCompleteModal && (
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Enter Final End Odometer</Text>
            <Text style={styles.modalSubtitle}>Starting reading was {trip.start_odometer || 0} km.</Text>
            <TextInput
              style={styles.odoInput}
              keyboardType="numeric"
              placeholder="e.g. 25550"
              value={endOdo}
              onChangeText={setEndOdo}
            />
            <View style={styles.modalBtnRow}>
              <TouchableOpacity
                style={styles.cancelBtn}
                onPress={() => setShowCompleteModal(false)}
              >
                <Text style={styles.cancelBtnText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.confirmBtn, { backgroundColor: '#10B981' }]}
                onPress={handleCompleteTrip}
                disabled={actionLoading}
              >
                <Text style={styles.confirmBtnText}>Finalize Trip</Text>
              </TouchableOpacity>
            </View>
          </View>
        )}

        {isCompleted && (
          <View style={styles.completedBanner}>
            <Text style={styles.completedBannerText}>✓ Trip Completed Successfully</Text>
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
  content: {
    padding: 16,
    paddingBottom: 40,
  },
  heroCard: {
    backgroundColor: '#0B192C',
    borderRadius: 14,
    padding: 16,
    marginBottom: 14,
  },
  heroRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4,
  },
  heroTitle: {
    color: '#FFFFFF',
    fontSize: 20,
    fontWeight: '800',
  },
  heroSub: {
    color: '#94A3B8',
    fontSize: 13,
  },
  statusPill: {
    backgroundColor: '#0284C7',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
  },
  statusPillText: {
    color: '#FFFFFF',
    fontSize: 10,
    fontWeight: '800',
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
    marginBottom: 14,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '800',
    color: '#0B192C',
    marginBottom: 12,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  guestRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  guestName: {
    fontSize: 16,
    fontWeight: '700',
    color: '#0B192C',
  },
  guestPhone: {
    fontSize: 13,
    color: '#64748B',
    marginTop: 2,
  },
  callBtn: {
    backgroundColor: '#E0F2FE',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 8,
  },
  callBtnText: {
    color: '#0284C7',
    fontWeight: '700',
    fontSize: 12,
  },
  routeItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  routeIcon: {
    fontSize: 18,
    marginRight: 10,
    marginTop: 2,
  },
  routeLabel: {
    fontSize: 10,
    fontWeight: '700',
    color: '#94A3B8',
    letterSpacing: 0.5,
  },
  routeAddress: {
    fontSize: 14,
    fontWeight: '600',
    color: '#0B192C',
    marginTop: 2,
  },
  routeTime: {
    fontSize: 12,
    color: '#0284C7',
    marginTop: 2,
    fontWeight: '600',
  },
  routeDivider: {
    height: 1,
    backgroundColor: '#F1F5F9',
    marginVertical: 12,
    marginLeft: 28,
  },
  specialReq: {
    backgroundColor: '#FEF3C7',
    padding: 10,
    borderRadius: 8,
    marginTop: 12,
  },
  specialReqTitle: {
    fontSize: 11,
    fontWeight: '700',
    color: '#92400E',
  },
  specialReqText: {
    fontSize: 12,
    color: '#78350F',
    marginTop: 2,
  },
  vehRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  vehPlate: {
    fontSize: 16,
    fontWeight: '800',
    color: '#0284C7',
    fontFamily: 'monospace',
  },
  vehModel: {
    fontSize: 14,
    color: '#475569',
    fontWeight: '600',
  },
  odoBox: {
    backgroundColor: '#F8FAFC',
    padding: 10,
    borderRadius: 8,
  },
  odoLabel: {
    fontSize: 12,
    color: '#64748B',
  },
  odoValue: {
    fontSize: 13,
    fontWeight: '700',
    color: '#0B192C',
    marginTop: 2,
  },
  actionContainer: {
    marginTop: 8,
  },
  primaryBtn: {
    backgroundColor: '#0284C7',
    borderRadius: 10,
    paddingVertical: 14,
    alignItems: 'center',
    shadowColor: '#0284C7',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 6,
    elevation: 3,
  },
  btnText: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '800',
  },
  modalCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
    borderWidth: 2,
    borderColor: '#0284C7',
  },
  modalTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: '#0B192C',
    marginBottom: 4,
  },
  modalSubtitle: {
    fontSize: 12,
    color: '#64748B',
    marginBottom: 12,
  },
  odoInput: {
    backgroundColor: '#F8FAFC',
    borderWidth: 1,
    borderColor: '#CBD5E1',
    borderRadius: 8,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 18,
    fontWeight: '700',
    color: '#0B192C',
    marginBottom: 14,
  },
  modalBtnRow: {
    flexDirection: 'row',
    gap: 10,
  },
  cancelBtn: {
    flex: 1,
    backgroundColor: '#E2E8F0',
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  cancelBtnText: {
    color: '#334155',
    fontWeight: '700',
  },
  confirmBtn: {
    flex: 1,
    backgroundColor: '#0284C7',
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  confirmBtnText: {
    color: '#FFFFFF',
    fontWeight: '700',
  },
  completedBanner: {
    backgroundColor: '#ECFDF5',
    borderWidth: 1,
    borderColor: '#10B981',
    borderRadius: 10,
    padding: 14,
    alignItems: 'center',
  },
  completedBannerText: {
    color: '#065F46',
    fontWeight: '800',
    fontSize: 15,
  },
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
});
