/**
 * VoyageGo Chauffeur Trips List Screen
 */
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  RefreshControl,
  ActivityIndicator,
} from 'react-native';
import { api } from '../api/client';

export default function TripsScreen({ navigation }) {
  const [trips, setTrips] = useState([]);
  const [filter, setFilter] = useState('all');
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);

  useEffect(() => {
    fetchTrips();
  }, []);

  const fetchTrips = async () => {
    try {
      const res = await api.getTrips();
      if (res && res.success) {
        setTrips(res.trips || []);
      }
    } catch (e) {
      console.warn('Failed to fetch trips:', e.message);
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  };

  const onRefresh = () => {
    setIsRefreshing(true);
    fetchTrips();
  };

  const filteredTrips = trips.filter((t) => {
    if (filter === 'all') return true;
    if (filter === 'assigned') return t.status === 'assigned';
    if (filter === 'active') return ['driver_accepted', 'en_route_pickup', 'trip_started'].includes(t.status);
    if (filter === 'completed') return t.status === 'completed';
    return true;
  });

  const getStatusBadge = (status) => {
    switch (status) {
      case 'trip_started':
      case 'en_route_pickup':
        return { text: 'In Progress', bg: '#0284C7', color: '#FFF' };
      case 'driver_accepted':
        return { text: 'Accepted', bg: '#F59E0B', color: '#FFF' };
      case 'assigned':
        return { text: 'Assigned', bg: '#E2E8F0', color: '#0F172A' };
      case 'completed':
        return { text: 'Completed', bg: '#10B981', color: '#FFF' };
      default:
        return { text: status, bg: '#94A3B8', color: '#FFF' };
    }
  };

  const renderTripItem = ({ item }) => {
    const badge = getStatusBadge(item.status);
    return (
      <TouchableOpacity
        style={styles.tripCard}
        onPress={() => navigation.navigate('TripDetail', { dispatchId: item.id })}
      >
        <View style={styles.cardTop}>
          <Text style={styles.tripCode}>{item.booking_code || item.dispatch_code}</Text>
          <View style={[styles.statusBadge, { backgroundColor: badge.bg }]}>
            <Text style={[styles.statusText, { color: badge.color }]}>{badge.text}</Text>
          </View>
        </View>

        <Text style={styles.guestName}>Guest: {item.customer_name || 'VIP Client'}</Text>

        <View style={styles.routeContainer}>
          <Text style={styles.routeText} numberOfLines={1}>📍 {item.pickup_address || 'Pickup Point'}</Text>
          <Text style={styles.routeText} numberOfLines={1}>🏁 {item.drop_address || 'Drop-off Destination'}</Text>
        </View>

        <View style={styles.cardFooter}>
          <Text style={styles.dateText}>📅 {item.start_date || 'Today'}</Text>
          <Text style={styles.vehicleText}>🚗 {item.vehicle_number || 'Assigned Vehicle'}</Text>
        </View>
      </TouchableOpacity>
    );
  };

  return (
    <View style={styles.container}>
      {/* Filter Chips */}
      <View style={styles.filterBar}>
        {['all', 'assigned', 'active', 'completed'].map((f) => (
          <TouchableOpacity
            key={f}
            style={[styles.filterChip, filter === f && styles.filterChipActive]}
            onPress={() => setFilter(f)}
          >
            <Text style={[styles.filterText, filter === f && styles.filterTextActive]}>
              {f.charAt(0).toUpperCase() + f.slice(1)}
            </Text>
          </TouchableOpacity>
        ))}
      </View>

      {isLoading ? (
        <View style={styles.centered}>
          <ActivityIndicator size="large" color="#0284C7" />
        </View>
      ) : (
        <FlatList
          data={filteredTrips}
          keyExtractor={(item) => String(item.id)}
          renderItem={renderTripItem}
          contentContainerStyle={styles.listContent}
          refreshControl={<RefreshControl refreshing={isRefreshing} onRefresh={onRefresh} tintColor="#F59E0B" />}
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={{ fontSize: 36, marginBottom: 8 }}>📋</Text>
              <Text style={styles.emptyTitle}>No Trips Found</Text>
              <Text style={styles.emptySubtitle}>There are currently no trips matching this filter.</Text>
            </View>
          }
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8FAFC',
  },
  filterBar: {
    flexDirection: 'row',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0',
    gap: 8,
  },
  filterChip: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
    backgroundColor: '#F1F5F9',
  },
  filterChipActive: {
    backgroundColor: '#0B192C',
  },
  filterText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#64748B',
  },
  filterTextActive: {
    color: '#FFFFFF',
  },
  listContent: {
    padding: 16,
  },
  tripCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    shadowColor: '#0B192C',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  cardTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 6,
  },
  tripCode: {
    fontSize: 16,
    fontWeight: '800',
    color: '#0B192C',
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
  },
  statusText: {
    fontSize: 11,
    fontWeight: '800',
  },
  guestName: {
    fontSize: 13,
    color: '#334155',
    marginBottom: 10,
    fontWeight: '500',
  },
  routeContainer: {
    backgroundColor: '#F8FAFC',
    padding: 10,
    borderRadius: 8,
    marginBottom: 10,
  },
  routeText: {
    fontSize: 12,
    color: '#475569',
    marginVertical: 2,
  },
  cardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 8,
    borderTopWidth: 1,
    borderTopColor: '#F1F5F9',
  },
  dateText: {
    fontSize: 11,
    color: '#64748B',
  },
  vehicleText: {
    fontSize: 11,
    color: '#0284C7',
    fontWeight: '700',
  },
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  emptyContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    padding: 40,
  },
  emptyTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#0B192C',
  },
  emptySubtitle: {
    fontSize: 12,
    color: '#64748B',
    marginTop: 4,
  },
});
