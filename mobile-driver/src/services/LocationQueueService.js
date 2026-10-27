/**
 * Persistent Offline Location Queue Service
 * Stores breadcrumbs during network loss and flushes in batches upon reconnection
 */
import AsyncStorage from '@react-native-async-storage/async-storage';
import { api } from '../api/client';
import { CONFIG } from '../config';

const QUEUE_STORAGE_KEY = '@voyagego_offline_location_queue';

class LocationQueueService {
  constructor() {
    this.isFlushing = false;
    this.retryBackoffMs = 2000;
  }

  async getQueue() {
    try {
      const raw = await AsyncStorage.getItem(QUEUE_STORAGE_KEY);
      return raw ? JSON.parse(raw) : [];
    } catch (e) {
      console.warn('Failed to read offline queue from storage:', e);
      return [];
    }
  }

  async saveQueue(queue) {
    try {
      // Limit queue size to avoid filling device storage
      const trimmed = queue.slice(-CONFIG.maxQueueSize);
      await AsyncStorage.setItem(QUEUE_STORAGE_KEY, JSON.stringify(trimmed));
    } catch (e) {
      console.warn('Failed to save offline queue to storage:', e);
    }
  }

  /**
   * Enqueue a new location breadcrumb with unique point ID
   */
  async enqueue(location) {
    const queue = await this.getQueue();
    const pointId = location.point_id || `pt_${Date.now()}_${Math.random().toString(36).substr(2, 6)}`;

    // Avoid duplicate point if coordinates and timestamp are identical
    const isDuplicate = queue.some(
      (p) => p.latitude === location.latitude && p.longitude === location.longitude && p.timestamp === location.timestamp
    );

    if (isDuplicate) return;

    const breadcrumb = {
      point_id: pointId,
      latitude: location.latitude,
      longitude: location.longitude,
      speed_kmh: location.speed_kmh || 0,
      heading_degrees: location.heading_degrees || null,
      accuracy_m: location.accuracy_m || null,
      battery_level: location.battery_level || null,
      timestamp: location.timestamp || new Date().toISOString(),
      queued_at: new Date().toISOString(),
    };

    queue.push(breadcrumb);
    await this.saveQueue(queue);
    console.log(`[OfflineQueue] Enqueued fix #${pointId}. Total pending: ${queue.length}`);
  }

  /**
   * Flush pending queue in chronological order to server
   */
  async flush() {
    if (this.isFlushing) return;
    this.isFlushing = true;

    try {
      let queue = await this.getQueue();
      if (queue.length === 0) {
        this.isFlushing = false;
        return { success: true, count: 0 };
      }

      // Sort by timestamp ascending
      queue.sort((a, b) => new Date(a.timestamp).getTime() - new Date(b.timestamp).getTime());

      // Take batch
      const batch = queue.slice(0, CONFIG.offlineSyncMaxBatch);
      const res = await api.syncGpsBatch(batch);

      if (res && res.success) {
        const syncedIds = res.synced_ids || batch.map((p) => p.point_id);
        const remaining = queue.filter((p) => !syncedIds.includes(p.point_id));
        await this.saveQueue(remaining);

        console.log(`[OfflineQueue] Successfully flushed ${syncedIds.length} points. Remaining: ${remaining.length}`);
        this.retryBackoffMs = 2000; // Reset backoff on success

        this.isFlushing = false;
        // If more items remain, trigger next batch
        if (remaining.length > 0) {
          setTimeout(() => this.flush(), 1000);
        }
        return { success: true, count: syncedIds.length, remaining: remaining.length };
      }
    } catch (error) {
      console.warn(`[OfflineQueue] Flush failed (network or server offline). Retrying in ${this.retryBackoffMs}ms:`, error.message);
      // Exponential backoff up to 60s
      this.retryBackoffMs = Math.min(this.retryBackoffMs * 1.5, 60000);
    } finally {
      this.isFlushing = false;
    }
  }

  async getPendingCount() {
    const queue = await this.getQueue();
    return queue.length;
  }

  async clearQueue() {
    await AsyncStorage.removeItem(QUEUE_STORAGE_KEY);
  }
}

export const locationQueue = new LocationQueueService();
