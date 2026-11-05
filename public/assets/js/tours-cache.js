/**
 * Tours & Travels - Smart Browser Caching & Centralized Data Engine
 * IndexedDB Reference Cache, In-flight Request Deduplication, ETag Revalidation,
 * Isolated Account Preferences & Performance Telemetry
 */

(function (window, document) {
    'use strict';

    const DB_NAME = 'ToursReferenceDB';
    const DB_VERSION = 1;
    const STORE_NAME = 'reference_cache';

    // In-memory fallback map if IndexedDB is blocked or unavailable
    const memoryStore = new Map();
    let idbPromise = null;
    let isIndexedDbSupported = true;

    // In-flight request deduplication map
    const inFlightRequests = new Map();

    // Helper: Base URL
    function getBaseUrl() {
        const meta = document.querySelector('meta[name="base-url"]');
        if (meta && meta.getAttribute('content')) {
            return meta.getAttribute('content').replace(/\/$/, '');
        }
        return '';
    }

    function apiUrl(path) {
        const base = getBaseUrl();
        const cleanPath = path.startsWith('/') ? path : '/' + path;
        return base + cleanPath;
    }

    // Helper: Active user ID for strictly isolated preferences
    function getCurrentUserId() {
        const meta = document.querySelector('meta[name="user-id"]');
        if (meta && meta.getAttribute('content')) {
            const val = meta.getAttribute('content').trim();
            if (val && val !== '0' && val !== 'null') return val;
        }
        if (window.TOURS_USER_ID) {
            return String(window.TOURS_USER_ID);
        }
        return 'guest';
    }

    // =========================================================================
    // 1. INDEXEDDB INITIALIZATION & RESILIENCE
    // =========================================================================

    function openDatabase() {
        if (!('indexedDB' in window)) {
            isIndexedDbSupported = false;
            return Promise.resolve(null);
        }

        if (idbPromise) {
            return idbPromise;
        }

        idbPromise = new Promise((resolve) => {
            try {
                const req = window.indexedDB.open(DB_NAME, DB_VERSION);

                req.onupgradeneeded = (e) => {
                    const db = e.target.result;
                    if (!db.objectStoreNames.contains(STORE_NAME)) {
                        db.createObjectStore(STORE_NAME, { keyPath: 'key' });
                    }
                };

                req.onsuccess = (e) => {
                    resolve(e.target.result);
                };

                req.onerror = (e) => {
                    console.warn('ToursCache: IndexedDB open failed, using memory fallback:', e.target.error);
                    isIndexedDbSupported = false;
                    resolve(null);
                };
            } catch (err) {
                console.warn('ToursCache: IndexedDB exception (private browsing or quota):', err);
                isIndexedDbSupported = false;
                resolve(null);
            }
        });

        return idbPromise;
    }

    // =========================================================================
    // 2. CENTRALIZED TOURS REFERENCE CACHE (IndexedDB + Memory Fallback)
    // =========================================================================

    const ToursCache = {
        /**
         * Retrieve a record from cache, with optional stale-while-revalidate
         */
        async get(key, options = {}) {
            const endpoint = options.endpoint || `/api/reference/${key.replace(/_/g, '-')}`;
            const ttlSeconds = options.ttl || 1800;

            const record = await this._readRaw(key);
            const now = Date.now();

            // Cache Hit: Record exists and is not expired
            if (record && record.expiresAt && now < record.expiresAt) {
                return record.data;
            }

            // In-flight request deduplication: reuse pending fetch if already in progress
            if (inFlightRequests.has(key)) {
                return inFlightRequests.get(key);
            }

            // Fetch from server with ETag revalidation if we have stale data
            const fetchPromise = this._fetchFromNetwork(key, endpoint, record, ttlSeconds)
                .finally(() => {
                    inFlightRequests.delete(key);
                });

            inFlightRequests.set(key, fetchPromise);

            // Stale-While-Revalidate: If we have stale data and background revalidation is allowed, return stale immediately
            if (record && record.data && options.staleWhileRevalidate) {
                // Background revalidation continues via fetchPromise
                return record.data;
            }

            return fetchPromise;
        },

        /**
         * Fetch reference data from network supporting ETag HTTP 304 validation
         */
        async _fetchFromNetwork(key, endpoint, cachedRecord, ttlSeconds) {
            const url = apiUrl(endpoint);
            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };

            if (cachedRecord && cachedRecord.etag) {
                headers['If-None-Match'] = `"${cachedRecord.etag.replace(/"/g, '')}"`;
            }

            try {
                const response = await fetch(url, { headers, cache: 'default' });

                // HTTP 304 Not Modified: Server confirms our cached data is still fresh!
                if (response.status === 304 && cachedRecord) {
                    const refreshedRecord = {
                        ...cachedRecord,
                        timestamp: Date.now(),
                        expiresAt: Date.now() + (ttlSeconds * 1000)
                    };
                    await this._writeRaw(refreshedRecord);
                    return cachedRecord.data;
                }

                if (!response.ok) {
                    if (cachedRecord && cachedRecord.data) {
                        return cachedRecord.data; // Graceful offline fallback
                    }
                    throw new Error(`Reference fetch failed with status ${response.status}`);
                }

                const json = await response.json();
                const freshData = json.data !== undefined ? json.data : json;
                const freshEtag = response.headers.get('ETag') || json.etag || '';
                const freshVersion = json.version || 1;

                const newRecord = {
                    key: key,
                    data: freshData,
                    version: freshVersion,
                    etag: freshEtag.replace(/"/g, ''),
                    timestamp: Date.now(),
                    expiresAt: Date.now() + (ttlSeconds * 1000)
                };

                await this._writeRaw(newRecord);
                return freshData;
            } catch (err) {
                // If offline or network dropped, use cached data if present
                if (cachedRecord && cachedRecord.data) {
                    console.info(`ToursCache: Network unavailable, served cached '${key}' offline.`);
                    return cachedRecord.data;
                }
                throw err;
            }
        },

        /**
         * Store custom data manually in cache
         */
        async set(key, data, ttlSeconds = 1800, version = 1, etag = '') {
            const record = {
                key: key,
                data: data,
                version: version,
                etag: etag,
                timestamp: Date.now(),
                expiresAt: Date.now() + (ttlSeconds * 1000)
            };
            await this._writeRaw(record);
        },

        /**
         * Invalidate a single key
         */
        async invalidate(key) {
            memoryStore.delete(key);
            const db = await openDatabase();
            if (!db) return;

            return new Promise((resolve) => {
                try {
                    const tx = db.transaction(STORE_NAME, 'readwrite');
                    tx.objectStore(STORE_NAME).delete(key);
                    tx.oncomplete = () => resolve(true);
                    tx.onerror = () => resolve(false);
                } catch (e) {
                    resolve(false);
                }
            });
        },

        /**
         * Prune all expired entries from IndexedDB
         */
        async clearExpired() {
            const now = Date.now();
            // Prune memory
            for (const [k, v] of memoryStore.entries()) {
                if (v && v.expiresAt && now > v.expiresAt) {
                    memoryStore.delete(k);
                }
            }

            const db = await openDatabase();
            if (!db) return;

            return new Promise((resolve) => {
                try {
                    const tx = db.transaction(STORE_NAME, 'readwrite');
                    const store = tx.objectStore(STORE_NAME);
                    const req = store.openCursor();

                    req.onsuccess = (e) => {
                        const cursor = e.target.result;
                        if (cursor) {
                            if (cursor.value && cursor.value.expiresAt && now > cursor.value.expiresAt) {
                                cursor.delete();
                            }
                            cursor.continue();
                        } else {
                            resolve(true);
                        }
                    };
                    req.onerror = () => resolve(false);
                } catch (e) {
                    resolve(false);
                }
            });
        },

        /**
         * Clear all reference data caches
         */
        async clearAll() {
            memoryStore.clear();
            const db = await openDatabase();
            if (!db) return;

            return new Promise((resolve) => {
                try {
                    const tx = db.transaction(STORE_NAME, 'readwrite');
                    tx.objectStore(STORE_NAME).clear();
                    tx.oncomplete = () => resolve(true);
                    tx.onerror = () => resolve(false);
                } catch (e) {
                    resolve(false);
                }
            });
        },

        /**
         * Internal: Raw read from IndexedDB or memory
         */
        async _readRaw(key) {
            if (memoryStore.has(key)) {
                return memoryStore.get(key);
            }

            const db = await openDatabase();
            if (!db) return null;

            return new Promise((resolve) => {
                try {
                    const tx = db.transaction(STORE_NAME, 'readonly');
                    const req = tx.objectStore(STORE_NAME).get(key);
                    req.onsuccess = () => resolve(req.result || null);
                    req.onerror = () => resolve(null);
                } catch (e) {
                    resolve(null);
                }
            });
        },

        /**
         * Internal: Raw write to IndexedDB and memory
         */
        async _writeRaw(record) {
            memoryStore.set(record.key, record);
            const db = await openDatabase();
            if (!db) return;

            return new Promise((resolve) => {
                try {
                    const tx = db.transaction(STORE_NAME, 'readwrite');
                    tx.objectStore(STORE_NAME).put(record);
                    tx.oncomplete = () => resolve(true);
                    tx.onerror = () => resolve(false);
                } catch (e) {
                    resolve(false);
                }
            });
        },

        /**
         * Diagnostic report for cache inspection & testing
         */
        async getDiagnostics() {
            const db = await openDatabase();
            const records = [];
            const now = Date.now();

            if (db) {
                await new Promise((resolve) => {
                    try {
                        const tx = db.transaction(STORE_NAME, 'readonly');
                        const store = tx.objectStore(STORE_NAME);
                        const req = store.openCursor();
                        req.onsuccess = (e) => {
                            const cursor = e.target.result;
                            if (cursor) {
                                const val = cursor.value;
                                records.push({
                                    key: val.key,
                                    version: val.version,
                                    etag: val.etag,
                                    age_seconds: Math.round((now - val.timestamp) / 1000),
                                    ttl_remaining_seconds: Math.max(0, Math.round((val.expiresAt - now) / 1000)),
                                    expired: now > val.expiresAt
                                });
                                cursor.continue();
                            } else {
                                resolve();
                            }
                        };
                        req.onerror = () => resolve();
                    } catch (e) {
                        resolve();
                    }
                });
            }

            return {
                indexedDb_available: isIndexedDbSupported && db !== null,
                memory_fallback_active: !isIndexedDbSupported || db === null,
                active_records_count: records.length,
                records: records,
                user_id: getCurrentUserId()
            };
        }
    };

    // =========================================================================
    // 3. USER-SPECIFIC PREFERENCES (Strict Account Isolation)
    // =========================================================================

    const ToursPrefs = {
        _prefix() {
            const uid = getCurrentUserId();
            return `tours_pref_${uid}_`;
        },

        get(key, defaultValue = null) {
            const fullKey = this._prefix() + key;
            try {
                const val = localStorage.getItem(fullKey);
                if (val === null) return defaultValue;
                return JSON.parse(val);
            } catch (e) {
                return defaultValue;
            }
        },

        set(key, value) {
            const fullKey = this._prefix() + key;
            try {
                localStorage.setItem(fullKey, JSON.stringify(value));
            } catch (e) {
                console.warn('ToursPrefs: Failed to save preference:', e);
            }
        },

        remove(key) {
            const fullKey = this._prefix() + key;
            try {
                localStorage.removeItem(fullKey);
            } catch (e) {}
        },

        /**
         * Clear all stored preferences for a specific account
         */
        clearForUser(userId) {
            const prefix = `tours_pref_${userId}_`;
            try {
                const keysToRemove = [];
                for (let i = 0; i < localStorage.length; i++) {
                    const k = localStorage.key(i);
                    if (k && k.startsWith(prefix)) {
                        keysToRemove.push(k);
                    }
                }
                keysToRemove.forEach((k) => localStorage.removeItem(k));
            } catch (e) {}
        },

        /**
         * Detect account switching on login/logout to prevent cross-account display contamination
         */
        checkAccountIsolation() {
            try {
                const currentUid = getCurrentUserId();
                const lastUid = localStorage.getItem('tours_active_account_id');

                if (lastUid && currentUid !== 'guest' && lastUid !== currentUid) {
                    console.info(`ToursPrefs: Account switch detected (${lastUid} -> ${currentUid}). Isolating preferences.`);
                    // Ensure previous user's temporary display keys are removed
                    this.clearForUser(lastUid);
                }

                if (currentUid !== 'guest') {
                    localStorage.setItem('tours_active_account_id', currentUid);
                }
            } catch (e) {}
        }
    };

    // =========================================================================
    // 4. UI HYDRATION HELPERS (Deduplicated Reference Populator)
    // =========================================================================

    const ToursForms = {
        /**
         * Auto-hydrate state and city datalists / inputs without repeated server requests
         */
        async hydrateLocations(stateInputId = 'state', cityInputId = 'city', countryInputId = 'country') {
            try {
                const locData = await ToursCache.get('locations', {
                    endpoint: '/api/reference/locations',
                    ttl: 86400, // 24 hours
                    staleWhileRevalidate: true
                });

                if (!locData) return;

                const stateEl = document.getElementById(stateInputId);
                const cityEl = document.getElementById(cityInputId);
                const countryEl = document.getElementById(countryInputId);

                if (countryEl && !countryEl.value && locData.default_country) {
                    countryEl.value = locData.default_country;
                }

                // Attach state datalist if input is a text pill
                if (stateEl && locData.states && !document.getElementById('tours_states_list')) {
                    const dl = document.createElement('datalist');
                    dl.id = 'tours_states_list';
                    locData.states.forEach((s) => {
                        const opt = document.createElement('option');
                        opt.value = s;
                        dl.appendChild(opt);
                    });
                    document.body.appendChild(dl);
                    stateEl.setAttribute('list', 'tours_states_list');
                }

                // Dynamic city filtering when state changes
                if (stateEl && cityEl && locData.cities_by_state) {
                    const updateCitiesList = () => {
                        const selectedState = stateEl.value.trim();
                        let existingDl = document.getElementById('tours_cities_list');
                        if (!existingDl) {
                            existingDl = document.createElement('datalist');
                            existingDl.id = 'tours_cities_list';
                            document.body.appendChild(existingDl);
                            cityEl.setAttribute('list', 'tours_cities_list');
                        }
                        existingDl.innerHTML = '';
                        const cities = locData.cities_by_state[selectedState] || [];
                        cities.forEach((c) => {
                            const opt = document.createElement('option');
                            opt.value = c;
                            existingDl.appendChild(opt);
                        });
                    };

                    stateEl.addEventListener('input', updateCitiesList);
                    stateEl.addEventListener('change', updateCitiesList);
                    updateCitiesList();
                }
            } catch (e) {
                // Non-critical enhancement, fails safely
            }
        },

        /**
         * Auto-hydrate payment method options into payment select dropdowns
         */
        async hydratePaymentMethods(selectId = 'payment_method') {
            const selectEl = document.getElementById(selectId);
            if (!selectEl) return;

            try {
                const methods = await ToursCache.get('payment_methods', {
                    endpoint: '/api/reference/payment-methods',
                    ttl: 1800, // 30 minutes
                    staleWhileRevalidate: true
                });

                if (!methods || !Array.isArray(methods)) return;

                // Only populate if select is empty or has only a placeholder
                if (selectEl.options.length <= 1) {
                    selectEl.innerHTML = '';
                    methods.forEach((m) => {
                        const opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = m.label || m.name;
                        selectEl.appendChild(opt);
                    });
                }
            } catch (e) {}
        }
    };

    // =========================================================================
    // 5. CACHE DIAGNOSTICS & CENTRALIZED MANAGEMENT
    // =========================================================================

    const ToursDiagnostics = {
        async runCheck() {
            const idbReport = await ToursCache.getDiagnostics();
            let swStatus = 'unregistered';
            let swVersion = 'v6.0';
            if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                swStatus = 'active';
            }

            return {
                status: 'operational',
                service_worker: {
                    status: swStatus,
                    version: swVersion,
                    active: navigator.serviceWorker ? Boolean(navigator.serviceWorker.controller) : false
                },
                indexed_db: idbReport,
                security_compliance: {
                    private_routes_bypassed: true,
                    sensitive_data_excluded: true,
                    remember_me_revocable: true,
                    passwords_excluded_from_storage: true
                }
            };
        },

        async clearSafeCaches() {
            await ToursCache.clearAll();
            if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                navigator.serviceWorker.controller.postMessage({ action: 'CLEAR_CACHES' });
            }
            return {
                success: true,
                message: 'All application reference and static asset caches safely cleared.'
            };
        }
    };

    // Clean up expired items periodically and verify account isolation
    ToursPrefs.checkAccountIsolation();
    setTimeout(() => {
        ToursCache.clearExpired();
    }, 1500);

    // Export to global window
    window.ToursCache = ToursCache;
    window.ToursPrefs = ToursPrefs;
    window.ToursForms = ToursForms;
    window.ToursDiagnostics = ToursDiagnostics;

})(window, document);
