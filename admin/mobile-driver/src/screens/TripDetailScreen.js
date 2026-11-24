/**
 * Trip Detail & Execution Screen
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
  const [startNotes, setStartNotes] = useState('');
  const [endNotes, setEndNotes] = useState('');
  const [startPhotoCaptured, setStartPhotoCaptured] = useState(false);
  const [startMeterCaptured, setStartMeterCaptured] = useState(false);
  const [endPhotoCaptured, setEndPhotoCaptured] = useState(false);
  const [endMeterCaptured, setEndMeterCaptured] = useState(false);
  const [showStartModal, setShowStartModal] = useState(false);
  const [showCompleteModal, setShowCompleteModal] = useState(false);
  const [showDeclineModal, setShowDeclineModal] = useState(false);
  const [declineReason, setDeclineReason] = useState('Schedule conflict');
  const [elapsedTime, setElapsedTime] = useState('00:00:00');

  useEffect(() => {
    fetchTripDetails();
  }, [dispatchId]);

  // Elapsed journey timer
  useEffect(() => {
    if (trip && trip.status === 'trip_started' && trip.start_time) {
      const startMs = new Date(trip.start_time.replace(/-/g, '/')).getTime();
      const interval = setInterval(() => {
        const now = Date.now();
        const diff = Math.max(0, now - startMs);
        const hrs = Math.floor(diff / 3600000);
        const mins = Math.floor((diff % 3600000) / 60000);
        const secs = Math.floor((diff % 60000) / 1000);
        const pad = (n) => String(n).padStart(2, '0');
        setElapsedTime(`${pad(hrs)}:${pad(mins)}:${pad(secs)}`);
      }, 1000);
      return () => clearInterval(interval);
    }
  }, [trip]);

  const fetchTripDetails = async () => {
    try {
      if (api.getTrip) {
        const detailRes = await api.getTrip(dispatchId).catch(() => null);
        if (detailRes && detailRes.success && detailRes.trip) {
          setTrip(detailRes.trip);
          setStartOdo(String(detailRes.trip.current_vehicle_odometer || detailRes.trip.start_odometer || ''));
          return;
        }
      }
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
      const res = await api.acceptTrip(dispatchId);
      if (res && res.success) {
        Alert.alert('Trip Accepted', 'You have accepted this trip assignment. Guest confirmation has been dispatched.');
      } else {
        Alert.alert('Notice', res?.message || 'Trip accepted');
      }
      await fetchTripDetails();
      await refreshProfile();
    } catch (e) {
      Alert.alert('Error', e.message || 'Could not accept trip');
    } finally {
      setActionLoading(false);
    }
  };

  const handleUnableToAccept = async () => {
    setActionLoading(true);
    try {
      const res = await api.unableToAcceptTrip(dispatchId, declineReason);
      setShowDeclineModal(false);
      Alert.alert('Status Updated', 'You have marked yourself unable to accept this assignment. Operations has been notified for reassignment.');
      await fetchTripDetails();
      await refreshProfile();
    } catch (e) {
      Alert.alert('Error', e.message || 'Could not update status');
    } finally {
      setActionLoading(false);
    }
  };

  const handleStartTrip = async () => {
    const odo = parseInt(startOdo, 10);
    const minOdo = parseInt(trip?.current_vehicle_odometer || '0', 10);

    if (isNaN(odo) || odo <= 0) {
      Alert.alert('Invalid Reading', 'Please enter a valid starting odometer reading.');
      return;
    }

    if (odo < minOdo) {
      Alert.alert('Odometer Error', `Starting odometer (${odo} km) cannot be less than vehicle reading (${minOdo} km).`);
      return;
    }

    setActionLoading(true);
    try {
      // Mock captured base64 data URIs for mobile vehicle inspection photos if snapped
      const extra = {};
      if (startPhotoCaptured) {
        extra.start_vehicle_base64 = 'data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
      }
      if (startMeterCaptured) {
        extra.start_odometer_base64 = 'data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
      }

      await api.startTrip(dispatchId, odo, startNotes, extra);
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
    const startVal = parseInt(trip?.start_odometer || trip?.current_vehicle_odometer || '0', 10);

    if (isNaN(odo) || odo < startVal) {
      Alert.alert('Invalid Reading', `End odometer must be greater than or equal to starting reading (${startVal} km).`);
      return;
    }

    setActionLoading(true);
    try {
      const extra = {};
      if (endPhotoCaptured) {
        extra.end_vehicle_base64 = 'data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
      }
      if (endMeterCaptured) {
        extra.end_odometer_base64 = 'data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
      }

      await api.completeTrip(dispatchId, odo, endNotes, extra);
      // Auto-stop background tracking
      await backgroundLocation.stopTracking();
      setShowCompleteModal(false);
      Alert.alert('Trip Completed', 'Trip marked completed. Vehicle released and ending inspection logged.');
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
  const isUnableToAccept = trip.status === 'unable_to_accept';
  const isAccepted = trip.status === 'driver_accepted' || trip.status === 'en_route_pickup';
  const isStarted = trip.status === 'trip_started';
  const isCompleted = trip.status === 'completed';

  const getStatusText = (status) => {
    switch (status) {
      case 'assigned': return 'Awaiting Acceptance';
      case 'unable_to_accept': return 'Unable to Accept';
      case 'driver_accepted':
      case 'accepted': return 'Accepted';
      case 'en_route_pickup':
      case 'en_route': return 'On the Way';
      case 'trip_started':
      case 'started': return 'Trip In Progress';
      case 'completed': return 'Completed';
      case 'cancelled': return 'Cancelled';
      default: return status ? status.replace('_', ' ') : 'Not specified';
    }
  };

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.content}>
      {/* Status Hero Card */}
      <View style={styles.heroCard}>
        <View style={styles.heroRow}>
          <Text style={styles.heroTitle}>{trip.booking_code || trip.dispatch_code}</Text>
          <View style={styles.statusPill}>
            <Text style={styles.statusPillText}>{getStatusText(trip.status)}</Text>
          </View>
        </View>
        <Text style={styles.heroSub}>{trip.tour_title || 'Private Driver Transfer'}</Text>
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
          <Text style={styles.vehPlate}>{trip.vehicle_number || 'VG-DRIVER'}</Text>
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

      {/* Driver Compensation / Trip Amount & Payment Status */}
      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Driver Compensation</Text>
        <View style={styles.amountBox}>
          <View>
            <Text style={styles.amountBoxLabel}>Trip Amount</Text>
            <Text style={styles.amountBoxValue}>
              {trip.driver_trip_amount != null
                ? `₹${Number(trip.driver_trip_amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                : 'Not specified'}
            </Text>
          </View>
          {trip.display_payment_status && (
            <View style={{ alignItems: 'flex-end' }}>
              <Text style={{ fontSize: 11, color: '#64748B', fontWeight: '600' }}>Payment Status</Text>
              <Text style={{ fontSize: 13, fontWeight: '800', color: trip.driver_payment_status === 'paid' ? '#16A34A' : (trip.driver_payment_status === 'partially_paid' ? '#4F46E5' : '#D97706') }}>
                {trip.display_payment_status}
              </Text>
            </View>
          )}
        </View>

        {Number(trip.driver_paid_amount || 0) > 0 && (
          <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginTop: 10, paddingTop: 10, borderTopWidth: 1, borderTopColor: '#E2E8F0' }}>
            <Text style={{ fontSize: 12, color: '#475569' }}>Disbursed: <Text style={{ fontWeight: '700', color: '#16A34A' }}>₹{Number(trip.driver_paid_amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</Text></Text>
            {trip.remaining_amount != null && (
              <Text style={{ fontSize: 12, color: '#475569' }}>Remaining: <Text style={{ fontWeight: '700', color: Number(trip.remaining_amount) > 0 ? '#D97706' : '#16A34A' }}>₹{Number(trip.remaining_amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</Text></Text>
            )}
          </View>
        )}

        {trip.payment_vouchers && trip.payment_vouchers.length > 0 && (
          <View style={{ marginTop: 12, paddingTop: 10, borderTopWidth: 1, borderTopColor: '#E2E8F0' }}>
            <Text style={{ fontSize: 11, fontWeight: '700', color: '#475569', textTransform: 'uppercase', marginBottom: 6 }}>Disbursement Vouchers</Text>
            {trip.payment_vouchers.map((v, vIdx) => (
              <View key={vIdx} style={{ flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 4 }}>
                <Text style={{ fontSize: 11, color: '#64748B' }}>{v.payment_date} • {v.payment_reference}</Text>
                <Text style={{ fontSize: 11, fontWeight: '700', color: '#16A34A' }}>+₹{Number(v.amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</Text>
              </View>
            ))}
          </View>
        )}
      </View>

      {/* Action Buttons */}
      <View style={styles.actionContainer}>
        {isAssigned && !showDeclineModal && (
          <View style={{ gap: 10 }}>
            <TouchableOpacity
              style={[styles.primaryBtn, { backgroundColor: '#10B981' }]}
              onPress={handleAcceptTrip}
              disabled={actionLoading}
            >
              <Text style={styles.btnText}>Accept Trip Assignment</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={[styles.cancelBtn, { borderColor: '#EF4444', borderWidth: 1.5, paddingVertical: 13, borderRadius: 12, alignItems: 'center' }]}
              onPress={() => setShowDeclineModal(true)}
              disabled={actionLoading}
            >
              <Text style={{ color: '#EF4444', fontWeight: '700', fontSize: 15 }}>Unable to Accept</Text>
            </TouchableOpacity>
          </View>
        )}

        {showDeclineModal && (
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Unable to Accept Assignment</Text>
            <Text style={styles.modalSubtitle}>Please specify a reason so dispatch operations can promptly reassign the trip.</Text>
            
            <View style={{ marginBottom: 12 }}>
              {['Schedule conflict', 'Vehicle maintenance', 'Health / Personal emergency', 'Other'].map((r) => (
                <TouchableOpacity
                  key={r}
                  style={{
                    paddingVertical: 10,
                    paddingHorizontal: 12,
                    borderRadius: 8,
                    marginBottom: 6,
                    backgroundColor: declineReason === r ? '#EFF6FF' : '#F8FAFC',
                    borderWidth: 1,
                    borderColor: declineReason === r ? '#2563EB' : '#E2E8F0',
                  }}
                  onPress={() => setDeclineReason(r)}
                >
                  <Text style={{ fontSize: 13, fontWeight: declineReason === r ? '700' : '500', color: declineReason === r ? '#1D4ED8' : '#334155' }}>
                    {declineReason === r ? '● ' : '○ '} {r}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>

            <View style={styles.modalBtnRow}>
              <TouchableOpacity
                style={styles.cancelBtn}
                onPress={() => setShowDeclineModal(false)}
              >
                <Text style={styles.cancelBtnText}>Back</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.confirmBtn, { backgroundColor: '#EF4444' }]}
                onPress={handleUnableToAccept}
                disabled={actionLoading}
              >
                <Text style={styles.confirmBtnText}>Submit Decline</Text>
              </TouchableOpacity>
            </View>
          </View>
        )}

        {isUnableToAccept && (
          <View style={{ backgroundColor: '#FEF2F2', borderColor: '#FCA5A5', borderWidth: 1, padding: 14, borderRadius: 12, alignItems: 'center' }}>
            <Text style={{ color: '#991B1B', fontWeight: '700', fontSize: 14 }}>Assignment Declined</Text>
            <Text style={{ color: '#B91C1C', fontSize: 12, marginTop: 4, textAlign: 'center' }}>
              You indicated you are unable to accept this duty ({trip.unable_to_accept_reason || 'Schedule conflict'}). Operations has been notified.
            </Text>
          </View>
        )}

        {isAccepted && !showStartModal && (
          <TouchableOpacity
            style={[styles.primaryBtn, { backgroundColor: '#10B981' }]}
            onPress={() => setShowStartModal(true)}
          >
            <Text style={styles.btnText}>Pre-Trip Inspection &amp; Start Journey</Text>
          </TouchableOpacity>
        )}

        {showStartModal && (
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Pre-Trip Vehicle Inspection</Text>
            <Text style={styles.modalSubtitle}>Vehicle last reading: {trip.current_vehicle_odometer || 0} km (must be &ge; this value).</Text>

            <Text style={{ fontSize: 12, fontWeight: '700', color: '#334155', marginBottom: 4 }}>Starting Meter Reading (km) *</Text>
            <TextInput
              style={styles.odoInput}
              keyboardType="numeric"
              placeholder="e.g. 25400"
              value={startOdo}
              onChangeText={setStartOdo}
            />

            {/* Inspection Photos (Snap Buttons) */}
            <View style={{ marginBottom: 12 }}>
              <Text style={{ fontSize: 11, fontWeight: '700', color: '#64748B', textTransform: 'uppercase', marginBottom: 6 }}>Inspection Photos</Text>
              <View style={{ flexDirection: 'row', gap: 8 }}>
                <TouchableOpacity
                  style={{ flex: 1, backgroundColor: startMeterCaptured ? '#DEF7EC' : '#F1F5F9', borderColor: startMeterCaptured ? '#10B981' : '#CBD5E1', borderWidth: 1, borderRadius: 8, padding: 8, alignItems: 'center' }}
                  onPress={() => setStartMeterCaptured(!startMeterCaptured)}
                >
                  <Text style={{ fontSize: 11, fontWeight: '700', color: startMeterCaptured ? '#03543F' : '#334155' }}>
                    {startMeterCaptured ? '✓ Meter Captured' : '📷 Snap Meter'}
                  </Text>
                </TouchableOpacity>

                <TouchableOpacity
                  style={{ flex: 1, backgroundColor: startPhotoCaptured ? '#DEF7EC' : '#F1F5F9', borderColor: startPhotoCaptured ? '#10B981' : '#CBD5E1', borderWidth: 1, borderRadius: 8, padding: 8, alignItems: 'center' }}
                  onPress={() => setStartPhotoCaptured(!startPhotoCaptured)}
                >
                  <Text style={{ fontSize: 11, fontWeight: '700', color: startPhotoCaptured ? '#03543F' : '#334155' }}>
                    {startPhotoCaptured ? '✓ Car Captured' : '🚗 Snap Car'}
                  </Text>
                </TouchableOpacity>
              </View>
            </View>

            <Text style={{ fontSize: 12, fontWeight: '700', color: '#334155', marginBottom: 4 }}>Departure Notes (Optional)</Text>
            <TextInput
              style={[styles.odoInput, { fontSize: 13, height: 60, textAlignVertical: 'top' }]}
              multiline
              placeholder="e.g. Fuel full, vehicle sanitized, passenger onboard..."
              value={startNotes}
              onChangeText={setStartNotes}
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
                <Text style={styles.confirmBtnText}>Confirm &amp; Start Trip</Text>
              </TouchableOpacity>
            </View>
          </View>
        )}

        {isStarted && !showCompleteModal && (
          <View style={{ marginBottom: 14 }}>
            {/* Live Journey Telemetry Card */}
            <View style={{ backgroundColor: '#0B192C', borderRadius: 12, padding: 14, marginBottom: 12, borderWidth: 1, borderColor: '#1E293B' }}>
              <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 }}>
                <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                  <View style={{ width: 8, height: 8, borderRadius: 4, backgroundColor: '#10B981' }} />
                  <Text style={{ color: '#10B981', fontWeight: '800', fontSize: 11, textTransform: 'uppercase' }}>GPS Telemetry Broadcasting</Text>
                </View>
                <Text style={{ color: '#38BDF8', fontWeight: '800', fontFamily: 'monospace', fontSize: 15 }}>{elapsedTime}</Text>
              </View>
              <Text style={{ color: '#E2E8F0', fontSize: 13, fontWeight: '700' }}>
                Trip Started: {trip.start_odometer || 0} KM
              </Text>
            </View>

            <TouchableOpacity
              style={[styles.primaryBtn, { backgroundColor: '#10B981' }]}
              onPress={() => setShowCompleteModal(true)}
            >
              <Text style={styles.btnText}>Complete Trip &amp; Log Inspection</Text>
            </TouchableOpacity>
          </View>
        )}

        {showCompleteModal && (
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Arrival Inspection &amp; Trip Complete</Text>
            <Text style={styles.modalSubtitle}>Trip started at {trip.start_odometer || 0} km.</Text>

            <Text style={{ fontSize: 12, fontWeight: '700', color: '#334155', marginBottom: 4 }}>Final End Odometer (km) *</Text>
            <TextInput
              style={styles.odoInput}
              keyboardType="numeric"
              placeholder="e.g. 25550"
              value={endOdo}
              onChangeText={setEndOdo}
            />

            {/* Computed Distance Indicator */}
            {parseInt(endOdo || '0', 10) >= parseInt(trip.start_odometer || '0', 10) && (
              <View style={{ backgroundColor: '#DEF7EC', padding: 8, borderRadius: 6, marginBottom: 12, flexDirection: 'row', justifyContent: 'space-between' }}>
                <Text style={{ color: '#03543F', fontSize: 12, fontWeight: '600' }}>Journey Distance:</Text>
                <Text style={{ color: '#03543F', fontSize: 12, fontWeight: '800' }}>
                  {Math.max(0, parseInt(endOdo || '0', 10) - parseInt(trip.start_odometer || '0', 10))} KM driven
                </Text>
              </View>
            )}

            {/* Inspection Photos (Snap Buttons) */}
            <View style={{ marginBottom: 12 }}>
              <Text style={{ fontSize: 11, fontWeight: '700', color: '#64748B', textTransform: 'uppercase', marginBottom: 6 }}>Arrival Photos</Text>
              <View style={{ flexDirection: 'row', gap: 8 }}>
                <TouchableOpacity
                  style={{ flex: 1, backgroundColor: endMeterCaptured ? '#DEF7EC' : '#F1F5F9', borderColor: endMeterCaptured ? '#10B981' : '#CBD5E1', borderWidth: 1, borderRadius: 8, padding: 8, alignItems: 'center' }}
                  onPress={() => setEndMeterCaptured(!endMeterCaptured)}
                >
                  <Text style={{ fontSize: 11, fontWeight: '700', color: endMeterCaptured ? '#03543F' : '#334155' }}>
                    {endMeterCaptured ? '✓ Meter Captured' : '📷 Snap Final Meter'}
                  </Text>
                </TouchableOpacity>

                <TouchableOpacity
                  style={{ flex: 1, backgroundColor: endPhotoCaptured ? '#DEF7EC' : '#F1F5F9', borderColor: endPhotoCaptured ? '#10B981' : '#CBD5E1', borderWidth: 1, borderRadius: 8, padding: 8, alignItems: 'center' }}
                  onPress={() => setEndPhotoCaptured(!endPhotoCaptured)}
                >
                  <Text style={{ fontSize: 11, fontWeight: '700', color: endPhotoCaptured ? '#03543F' : '#334155' }}>
                    {endPhotoCaptured ? '✓ Car Captured' : '🚗 Snap Returned Car'}
                  </Text>
                </TouchableOpacity>
              </View>
            </View>

            <Text style={{ fontSize: 12, fontWeight: '700', color: '#334155', marginBottom: 4 }}>Trip Notes (Optional)</Text>
            <TextInput
              style={[styles.odoInput, { fontSize: 13, height: 60, textAlignVertical: 'top' }]}
              multiline
              placeholder="e.g. Passenger dropped safely, toll paid, parked in yard..."
              value={endNotes}
              onChangeText={setEndNotes}
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
  amountBox: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: '#F0FDF4',
    padding: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#BBF7D0',
  },
  amountBoxLabel: {
    fontSize: 11,
    fontWeight: '700',
    color: '#166534',
    textTransform: 'uppercase',
  },
  amountBoxValue: {
    fontSize: 18,
    fontWeight: '900',
    color: '#15803D',
    marginTop: 2,
  },
  amountBoxNote: {
    fontSize: 11,
    color: '#166534',
    fontWeight: '500',
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
