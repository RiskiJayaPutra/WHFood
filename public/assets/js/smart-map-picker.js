/**
 * ============================================================================
 * WHFood - Smart Map Picker Component
 * ============================================================================
 * 
 * Komponen peta interaktif menggunakan Leaflet.js untuk memilih lokasi toko.
 * Fitur: Drag & Drop Pin, Geolocation API, Real-time coordinate capture.
 * 
 * @package     WHFood
 * @author      WHFood Development Team
 * @version     1.0.0
 * @since       2026-01-12
 */

'use strict';

/**
 * SmartMapPicker Class
 * 
 * Mengelola peta Leaflet dengan fitur:
 * - Drag & drop marker
 * - Geolocation (lokasi saat ini)
 * - Real-time coordinate capture ke hidden inputs
 */
class SmartMapPicker {
    /**
     * Koordinat default: Way Huwi, Lampung Selatan
     */
    static DEFAULT_LAT = -5.3698;
    static DEFAULT_LNG = 105.2486;
    static DEFAULT_ZOOM = 15;

    /**
     * Constructor
     * 
     * @param {string} containerId - ID elemen container peta
     * @param {Object} options - Opsi konfigurasi
     */
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.options = {
            latInputId: options.latInputId || 'latitude',
            lngInputId: options.lngInputId || 'longitude',
            addressInputId: options.addressInputId || 'address',
            initialLat: options.initialLat || SmartMapPicker.DEFAULT_LAT,
            initialLng: options.initialLng || SmartMapPicker.DEFAULT_LNG,
            zoom: options.zoom || SmartMapPicker.DEFAULT_ZOOM,
            ...options
        };

        // Instance references
        this.map = null;
        this.marker = null;
        this.isDestroyed = false;

        // Bind methods untuk event handlers (mencegah memory leaks)
        this.handleMarkerDragEnd = this.handleMarkerDragEnd.bind(this);
        this.handleMapClick = this.handleMapClick.bind(this);
        this.handleGeolocationSuccess = this.handleGeolocationSuccess.bind(this);
        this.handleGeolocationError = this.handleGeolocationError.bind(this);

        // Initialize
        this.init();
    }

    /**
     * Initialize map
     */
    init() {
        const container = document.getElementById(this.containerId);
        if (!container) {
            console.error(`SmartMapPicker: Container #${this.containerId} not found`);
            return;
        }

        // Cek apakah Leaflet tersedia
        if (typeof L === 'undefined') {
            console.error('SmartMapPicker: Leaflet.js is not loaded');
            return;
        }

        this.createMap();
        this.createMarker();
        this.bindEvents();
        this.updateInputs(this.options.initialLat, this.options.initialLng);
    }

    /**
     * Create Leaflet map instance
     */
    createMap() {
        this.map = L.map(this.containerId, {
            center: [this.options.initialLat, this.options.initialLng],
            zoom: this.options.zoom,
            zoomControl: true,
            scrollWheelZoom: true,
            touchZoom: true,
            dragging: true,
            tap: true // Touch-friendly untuk mobile
        });

        // OpenStreetMap Tile Layer (Free)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(this.map);

        // Handle map resize untuk responsiveness
        setTimeout(() => {
            if (this.map && !this.isDestroyed) {
                this.map.invalidateSize();
            }
        }, 100);
    }

    /**
     * Create draggable marker
     */
    createMarker() {
        // Custom icon untuk marker
        const customIcon = L.divIcon({
            className: 'custom-map-marker',
            html: `
                <div class="marker-pin">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="marker-icon">
                        <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                </div>
            `,
            iconSize: [40, 50],
            iconAnchor: [20, 50],
            popupAnchor: [0, -50]
        });

        this.marker = L.marker([this.options.initialLat, this.options.initialLng], {
            draggable: true,
            icon: customIcon,
            autoPan: true,
            autoPanPadding: [50, 50]
        }).addTo(this.map);

        // Popup untuk marker
        this.marker.bindPopup('<strong>Lokasi Toko Anda</strong><br>Geser pin untuk mengubah lokasi').openPopup();
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Marker drag end
        this.marker.on('dragend', this.handleMarkerDragEnd);

        // Map click untuk pindah marker
        this.map.on('click', this.handleMapClick);
    }

    /**
     * Handle marker drag end event
     * 
     * @param {Object} event - Leaflet event object
     */
    handleMarkerDragEnd(event) {
        const position = event.target.getLatLng();
        this.updateInputs(position.lat, position.lng);
        this.reverseGeocode(position.lat, position.lng);
    }

    /**
     * Handle map click event
     * 
     * @param {Object} event - Leaflet event object
     */
    handleMapClick(event) {
        const { lat, lng } = event.latlng;
        this.setPosition(lat, lng);
    }

    /**
     * Set marker position
     * 
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @param {boolean} animate - Animate marker movement
     */
    setPosition(lat, lng, animate = true) {
        if (this.isDestroyed) return;

        const newLatLng = L.latLng(lat, lng);
        
        if (animate) {
            // Smooth animation untuk marker
            this.marker.setLatLng(newLatLng);
            this.map.panTo(newLatLng, { animate: true, duration: 0.5 });
        } else {
            this.marker.setLatLng(newLatLng);
            this.map.setView(newLatLng, this.options.zoom);
        }

        this.updateInputs(lat, lng);
        this.reverseGeocode(lat, lng);
    }

    /**
     * Update hidden input fields dengan koordinat
     * 
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     */
    updateInputs(lat, lng) {
        const latInput = document.getElementById(this.options.latInputId);
        const lngInput = document.getElementById(this.options.lngInputId);

        if (latInput) {
            latInput.value = lat.toFixed(8);
            // Trigger change event
            latInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (lngInput) {
            lngInput.value = lng.toFixed(8);
            // Trigger change event
            lngInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Update coordinate display jika ada
        const coordDisplay = document.getElementById('coordinateDisplay');
        if (coordDisplay) {
            coordDisplay.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }
    }

    /**
     * Reverse geocode koordinat ke alamat
     * Menggunakan Nominatim API (OpenStreetMap)
     * 
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     */
    async reverseGeocode(lat, lng) {
        const addressInput = document.getElementById(this.options.addressInputId);
        if (!addressInput) return;

        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
                {
                    headers: {
                        'Accept-Language': 'id'
                    }
                }
            );

            if (!response.ok) throw new Error('Geocoding failed');

            const data = await response.json();
            
            if (data.display_name) {
                addressInput.value = data.display_name;
                this.marker.setPopupContent(`<strong>Lokasi Toko</strong><br>${data.display_name}`);
            }
        } catch (error) {
            console.warn('Reverse geocoding failed:', error);
        }
    }

    /**
     * Gunakan lokasi saat ini (Geolocation API)
     */
    useCurrentLocation() {
        if (!navigator.geolocation) {
            this.showNotification('Geolocation tidak didukung oleh browser Anda', 'error');
            return;
        }

        // Show loading state
        const btn = document.getElementById('useLocationBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Mencari lokasi...
            `;
        }

        navigator.geolocation.getCurrentPosition(
            this.handleGeolocationSuccess,
            this.handleGeolocationError,
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    /**
     * Handle geolocation success
     * 
     * @param {GeolocationPosition} position - Position object
     */
    handleGeolocationSuccess(position) {
        const { latitude, longitude } = position.coords;
        this.setPosition(latitude, longitude, true);
        this.showNotification('Lokasi berhasil ditemukan!', 'success');
        this.resetLocationButton();
    }

    /**
     * Handle geolocation error
     * 
     * @param {GeolocationPositionError} error - Error object
     */
    handleGeolocationError(error) {
        let message = 'Gagal mendapatkan lokasi';
        
        switch (error.code) {
            case error.PERMISSION_DENIED:
                message = 'Akses lokasi ditolak. Silakan izinkan akses lokasi di browser.';
                break;
            case error.POSITION_UNAVAILABLE:
                message = 'Informasi lokasi tidak tersedia.';
                break;
            case error.TIMEOUT:
                message = 'Waktu pencarian lokasi habis. Silakan coba lagi.';
                break;
        }

        this.showNotification(message, 'error');
        this.resetLocationButton();
    }

    /**
     * Reset location button state
     */
    resetLocationButton() {
        const btn = document.getElementById('useLocationBtn');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 inline" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                </svg>
                Gunakan Lokasi Saya Saat Ini
            `;
        }
    }

    /**
     * Show notification toast
     * 
     * @param {string} message - Notification message
     * @param {string} type - 'success' | 'error' | 'info'
     */
    showNotification(message, type = 'info') {
        // Hapus notification lama jika ada
        const existingNotif = document.querySelector('.map-notification');
        if (existingNotif) {
            existingNotif.remove();
        }

        const bgColor = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500'
        }[type] || 'bg-blue-500';

        const notification = document.createElement('div');
        notification.className = `map-notification fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-[9999] flex items-center gap-2 animate-slide-up`;
        notification.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-2 hover:opacity-75">&times;</button>
        `;

        document.body.appendChild(notification);

        // Auto remove after 4 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.add('animate-fade-out');
                setTimeout(() => notification.remove(), 300);
            }
        }, 4000);
    }

    /**
     * Get current position
     * 
     * @returns {Object} Current lat/lng
     */
    getPosition() {
        if (!this.marker) return null;
        const pos = this.marker.getLatLng();
        return { lat: pos.lat, lng: pos.lng };
    }

    /**
     * Invalidate size (call after container resize)
     */
    invalidateSize() {
        if (this.map && !this.isDestroyed) {
            this.map.invalidateSize();
        }
    }

    /**
     * Destroy map instance dan cleanup
     * PENTING: Panggil ini untuk mencegah memory leaks
     */
    destroy() {
        this.isDestroyed = true;

        if (this.marker) {
            this.marker.off('dragend', this.handleMarkerDragEnd);
            this.marker.remove();
            this.marker = null;
        }

        if (this.map) {
            this.map.off('click', this.handleMapClick);
            this.map.remove();
            this.map = null;
        }
    }
}

// Export untuk module systems (jika digunakan)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SmartMapPicker;
}
