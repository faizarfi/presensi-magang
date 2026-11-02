/**
 * Script Presensi Harian Peserta Magang BPS Karanganyar
 * Pure JavaScript - Tidak bercampur sintaks Blade
 */

function internAttendancePage() {
    const root = document.getElementById('internAbsenRoot');
    const hasCheckedIn = root ? root.dataset.hasCheckedIn === '1' : false;
    const hasCheckedOut = root ? root.dataset.hasCheckedOut === '1' : false;
    const isLocal = root ? root.dataset.isLocal === '1' : false;
    const officeLat = root ? parseFloat(root.dataset.officeLat) : -7.5978;
    const officeLng = root ? parseFloat(root.dataset.officeLng) : 110.9505;
    const officeRadius = root ? parseFloat(root.dataset.officeRadius) : 150;
    const officeName = root ? root.dataset.officeName : 'Kantor BPS Karanganyar';
    const subStatusVal = root ? (root.dataset.subStatus || 'sakit') : 'sakit';

    let defaultTab = 'pagi';
    if (hasCheckedIn && !hasCheckedOut) {
        defaultTab = 'pulang';
    } else if (hasCheckedOut) {
        defaultTab = 'pulang';
    }

    return {
        attachmentName: '',
        attachmentPreview: null,
        activeTab: defaultTab,
        subStatus: subStatusVal,
        activityDescription: '',
        isConfirmedPagi: false,
        isConfirmedPulang: false,
        submittingPagi: false,
        submittingPulang: false,
        submittingIzin: false,
        detecting: false,
        latitude: null,
        longitude: null,
        distanceMeters: null,
        map: null,
        userMarker: null,
        isLocalEnv: isLocal,

        officeConfig: {
            lat: officeLat,
            lng: officeLng,
            radius: officeRadius,
            name: officeName
        },

        geoStatus: {
            detected: false,
            isInside: false,
            title: 'Lokasi Belum Terdeteksi',
            message: 'Klik "Deteksi Lokasi Saya" untuk memvalidasi posisi Anda di kantor BPS Karanganyar.'
        },

        switchTab(tab) {
            this.activeTab = tab;
            this.$nextTick(() => {
                if (this.map && typeof this.map.invalidateSize === 'function') {
                    this.map.invalidateSize();
                }
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });
        },

        handleFileSelect(event) {
            const file = event.target.files ? event.target.files[0] : null;
            if (!file) {
                this.attachmentName = '';
                this.attachmentPreview = null;
                return;
            }
            this.attachmentName = file.name;
            if (file.type && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.attachmentPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                this.attachmentPreview = null;
            }
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        },

        removeAttachment() {
            this.attachmentName = '';
            this.attachmentPreview = null;
            const fileInput = document.getElementById('attachment_file_input');
            if (fileInput) fileInput.value = '';
            const camInput = document.getElementById('attachment_camera_input');
            if (camInput) camInput.value = '';
        },

        initPage() {
            const pulangTextEl = document.getElementById('pulang_activity');
            if (pulangTextEl) {
                this.activityDescription = pulangTextEl.value || '';
            }

            // Jam Digital Realtime & Countdown Jam Pulang
            const updateClockAndCountdown = () => {
                const now = new Date();
                const clockEl = document.getElementById('liveClockDisplay');
                if (clockEl) {
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');
                    clockEl.textContent = `${hours}:${minutes}:${seconds} WIB`;
                }

                // Hitung Mundur Menuju Jam 16:00 WIB
                const targetPulang = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 16, 0, 0);
                const diffMs = targetPulang - now;
                const countdownEl = document.getElementById('countdownPulangDisplay');
                const countdownDetailEl = document.getElementById('countdownPulangDetail');

                if (diffMs > 0) {
                    const diffSecs = Math.floor(diffMs / 1000);
                    const h = Math.floor(diffSecs / 3600);
                    const m = Math.floor((diffSecs % 3600) / 60);
                    const s = diffSecs % 60;
                    const formatted = `${h > 0 ? h + 'j ' : ''}${m}m ${s}s`;
                    const formattedLong = `${h > 0 ? h + ' jam ' : ''}${m} menit ${s} detik`;

                    if (countdownEl) countdownEl.textContent = formatted;
                    if (countdownDetailEl) countdownDetailEl.textContent = formattedLong;
                } else {
                    if (countdownEl) countdownEl.textContent = 'Sudah dibuka';
                    if (countdownDetailEl) countdownDetailEl.textContent = 'Waktu presensi kepulangan telah dibuka!';
                }
            };

            setInterval(updateClockAndCountdown, 1000);
            updateClockAndCountdown();

            // Inisialisasi peta Leaflet
            this.$nextTick(() => {
                this.initMap();
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });

            // Auto-deteksi lokasi saat halaman dimuat
            if (this.isLocalEnv) {
                // Di local environment, langsung berikan kemudahan koordinat kantor jika belum ada GPS
                this.simulateAtOffice();
            } else {
                this.detectLocation();
            }
        },

        initMap() {
            if (typeof L === 'undefined') return;

            const mapEl = document.getElementById('mapPagi');
            if (!mapEl) return;

            try {
                this.map = L.map('mapPagi', {
                    zoomControl: false,
                    attributionControl: false
                }).setView([this.officeConfig.lat, this.officeConfig.lng], 17);

                L.control.zoom({ position: 'bottomright' }).addTo(this.map);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(this.map);

                // Marker Kantor BPS
                const bpsIcon = L.divIcon({
                    className: 'bps-pin',
                    html: `<div style="background-color:#002855;color:white;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 10px rgba(0,40,85,0.4);border:2.5px solid white;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></div>`,
                    iconSize: [34, 34],
                    iconAnchor: [17, 17]
                });

                L.marker([this.officeConfig.lat, this.officeConfig.lng], { icon: bpsIcon }).addTo(this.map)
                    .bindPopup(`<strong>${this.officeConfig.name}</strong><br>Pusat Presensi Magang`)
                    .openPopup();

                // Lingkaran Radius Kantor BPS
                L.circle([this.officeConfig.lat, this.officeConfig.lng], {
                    color: '#0055a5',
                    fillColor: '#0055a5',
                    fillOpacity: 0.12,
                    radius: this.officeConfig.radius
                }).addTo(this.map);
            } catch (err) {
                console.warn('Gagal menginisialisasi Leaflet Map:', err);
            }
        },

        detectLocation() {
            if (!navigator.geolocation) {
                this.geoStatus = {
                    detected: false,
                    isInside: false,
                    title: 'Geolokasi Tidak Didukung',
                    message: 'Browser Anda tidak mendukung deteksi lokasi otomatis. Silakan gunakan Google Chrome / Edge versi terbaru.'
                };
                return;
            }

            this.detecting = true;
            this.geoStatus.title = 'Mendeteksi Posisi Anda...';
            this.geoStatus.message = 'Menghubungkan ke GPS satelit/jaringan perangkat...';

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.detecting = false;
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    this.latitude = lat;
                    this.longitude = lng;

                    const dist = Math.round(this.calcDistance(lat, lng, this.officeConfig.lat, this.officeConfig.lng));
                    this.distanceMeters = dist;
                    const isInside = dist <= this.officeConfig.radius;

                    this.geoStatus = {
                        detected: true,
                        isInside: isInside,
                        title: isInside ? 'Lokasi Valid di Area BPS' : 'Lokasi di Luar Radius BPS',
                        message: isInside
                            ? `Posisi Anda terverifikasi berada di lingkungan kantor (${dist} meter dari pusat kantor).`
                            : `Posisi Anda terdeteksi berjarak ${dist} meter dari kantor BPS (Toleransi: ${this.officeConfig.radius}m). Wajib berada di kantor.`
                    };

                    this.updateMapLocation(lat, lng, isInside);
                },
                (err) => {
                    this.detecting = false;
                    let msg = 'Izin akses lokasi ditolak oleh browser.';
                    if (err.code === 2) msg = 'Posisi tidak tersedia dari perangkat Anda.';
                    if (err.code === 3) msg = 'Waktu deteksi lokasi habis (timeout).';

                    this.geoStatus = {
                        detected: false,
                        isInside: false,
                        title: 'Gagal Mendeteksi Lokasi',
                        message: msg + ' Pastikan izin lokasi (GPS) pada browser telah diizinkan (Allow).'
                    };
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        },

        simulateAtOffice() {
            this.latitude = this.officeConfig.lat;
            this.longitude = this.officeConfig.lng;
            this.distanceMeters = 0;
            this.geoStatus = {
                detected: true,
                isInside: true,
                title: 'Lokasi Terverifikasi (Mode Pengujian)',
                message: 'Koordinat diset pada titik kantor BPS Karanganyar (jarak 0m).'
            };
            this.updateMapLocation(this.officeConfig.lat, this.officeConfig.lng, true);
        },

        updateMapLocation(lat, lng, isInside) {
            if (!this.map || typeof L === 'undefined') return;

            if (this.userMarker) {
                this.map.removeLayer(this.userMarker);
            }

            const userColor = isInside ? '#008850' : '#dc2626';
            const userIcon = L.divIcon({
                className: 'user-pin',
                html: `<div style="background-color:${userColor};color:white;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.3);border:2px solid white;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="7" r="4"/><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/></svg></div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });

            this.userMarker = L.marker([lat, lng], { icon: userIcon }).addTo(this.map)
                .bindPopup(`<strong>Posisi Anda</strong><br>Jarak: ${this.distanceMeters}m`);

            this.map.fitBounds([[this.officeConfig.lat, this.officeConfig.lng], [lat, lng]], { padding: [30, 30] });
        },

        calcDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        },

        submitPagi() {
            if (!this.latitude || !this.longitude) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Lokasi Belum Terdeteksi',
                        text: 'Silakan klik tombol "Deteksi Lokasi Saya" terlebih dahulu sebelum mengirim presensi masuk.'
                    });
                } else {
                    alert('Silakan klik tombol "Deteksi Lokasi Saya" terlebih dahulu sebelum mengirim presensi masuk.');
                }
                return;
            }

            if (!this.isLocalEnv && !this.geoStatus.isInside) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Di Luar Radius Kantor BPS',
                        text: `Posisi Anda terdeteksi berjarak ${this.distanceMeters} meter dari kantor BPS (Toleransi: ${this.officeConfig.radius}m). Presensi masuk wajib dilakukan di area kantor BPS Karanganyar.`
                    });
                } else {
                    alert(`Di Luar Radius Kantor BPS (${this.distanceMeters}m)`);
                }
                return;
            }

            if (!this.isConfirmedPagi) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Persetujuan Diperlukan',
                        text: 'Harap centang pernyataan kehadiran sebelum mengirim presensi.'
                    });
                } else {
                    alert('Harap centang pernyataan kehadiran sebelum mengirim presensi.');
                }
                return;
            }

            this.submittingPagi = true;
            const form = document.getElementById('formPresensiPagi');
            if (form) form.submit();
        },

        submitPulang() {
            const actDesc = document.getElementById('pulang_activity') ? document.getElementById('pulang_activity').value : '';
            if (!actDesc || actDesc.trim().length < 5) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Laporan Pekerjaan Wajib Diisi',
                        text: 'Silakan isi laporan aktivitas atau pekerjaan yang telah Anda selesaikan hari ini (minimal 5 karakter).'
                    });
                } else {
                    alert('Silakan isi laporan aktivitas atau pekerjaan yang telah Anda selesaikan hari ini (minimal 5 karakter).');
                }
                return;
            }

            if (!this.latitude || !this.longitude) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Lokasi Belum Terdeteksi',
                        text: 'Silakan klik tombol "Cek Lokasi Saya" terlebih dahulu untuk presensi pulang.'
                    });
                } else {
                    alert('Silakan klik tombol "Cek Lokasi Saya" terlebih dahulu untuk presensi pulang.');
                }
                return;
            }

            if (!this.isLocalEnv && !this.geoStatus.isInside) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Di Luar Radius Kantor BPS',
                        text: `Posisi Anda terdeteksi berjarak ${this.distanceMeters} meter dari kantor BPS. Presensi pulang wajib dilakukan di area kantor BPS Karanganyar.`
                    });
                } else {
                    alert(`Di Luar Radius Kantor BPS (${this.distanceMeters}m)`);
                }
                return;
            }

            if (!this.isConfirmedPulang) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Persetujuan Diperlukan',
                        text: 'Harap centang konfirmasi sebelum mengirim presensi pulang.'
                    });
                } else {
                    alert('Harap centang konfirmasi sebelum mengirim presensi pulang.');
                }
                return;
            }

            this.submittingPulang = true;
            const form = document.getElementById('formPresensiPulang');
            if (form) form.submit();
        }
    };
}

// Daftarkan ke window agar dapat dipanggil oleh Alpine.js
window.internAttendancePage = internAttendancePage;

if (typeof document !== 'undefined') {
    document.addEventListener('alpine:init', () => {
        if (typeof Alpine !== 'undefined' && typeof Alpine.data === 'function') {
            Alpine.data('internAttendancePage', internAttendancePage);
        }
    });
}
