@extends('layouts.app')

@section('title', 'Pengaturan Lokasi BPS - SIM-MAGANG BPS')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="settingsMap()">

    <!-- Header & Breadcrumbs -->
    <div>
        <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-800 flex items-center gap-1">
                <i data-lucide="home" class="w-3.5 h-3.5"></i>
                <span>Dashboard</span>
            </a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Pengaturan Lokasi BPS</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pengaturan Lokasi Kantor & Radius Kehadiran</h1>
        <p class="text-xs text-slate-500 mt-0.5">Konfigurasi koordinat geolokasi kantor BPS Kabupaten Karanganyar dan toleransi batas radius presensi hadir.</p>
    </div>

    <!-- Form & Interactive Map Grid -->
    <div class="bg-white rounded-xl p-6 sm:p-8 shadow-xs border border-slate-200 space-y-6">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Informasi Kantor -->
            <div>
                <h3 class="text-xs font-semibold text-blue-800 uppercase tracking-wider mb-3.5 flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-blue-800"></i>
                    <span>1. Identitas Instansi & Jam Pelayanan Magang</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label for="office_name" class="block font-medium text-slate-700 uppercase tracking-wider mb-1.5">Nama Instansi <span class="text-rose-500">*</span></label>
                        <input type="text" name="office_name" id="office_name" value="{{ old('office_name', $config['name']) }}" required
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="work_start_time" class="block font-medium text-slate-700 uppercase tracking-wider mb-1.5">Jam Masuk</label>
                            <input type="time" name="work_start_time" id="work_start_time" value="{{ old('work_start_time', $config['work_start_time']) }}" required
                                   class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none font-mono">
                        </div>
                        <div>
                            <label for="work_end_time" class="block font-medium text-slate-700 uppercase tracking-wider mb-1.5">Jam Pulang</label>
                            <input type="time" name="work_end_time" id="work_end_time" value="{{ old('work_end_time', $config['work_end_time']) }}" required
                                   class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none font-mono">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="office_address" class="block font-medium text-slate-700 uppercase tracking-wider mb-1.5">Alamat Kantor Lengkap <span class="text-rose-500">*</span></label>
                        <textarea name="office_address" id="office_address" rows="2" required
                                  class="w-full text-xs sm:text-sm p-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none leading-relaxed">{{ old('office_address', $config['address']) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Koordinat & Radius -->
            <div class="pt-5 border-t border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                    <h3 class="text-xs font-semibold text-blue-800 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-blue-800"></i>
                        <span>2. Titik Geospasial Kantor & Radius Validasi</span>
                    </h3>
                    <button type="button" @click="resetToBpsKaranganyar()"
                            class="h-9 px-3 rounded-lg border border-blue-200 bg-blue-50 text-blue-800 text-xs font-medium hover:bg-blue-100 transition self-start sm:self-auto flex items-center gap-1.5">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-blue-800"></i>
                        <span>Default BPS Karanganyar</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs mb-4">
                    <div>
                        <label for="office_latitude" class="block font-medium text-slate-700 uppercase tracking-wider mb-1.5">Latitude</label>
                        <input type="number" step="any" name="office_latitude" id="office_latitude"
                               x-model.number="lat" @input="updateMapPosition()" required
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none font-mono">
                    </div>

                    <div>
                        <label for="office_longitude" class="block font-medium text-slate-700 uppercase tracking-wider mb-1.5">Longitude</label>
                        <input type="number" step="any" name="office_longitude" id="office_longitude"
                               x-model.number="lng" @input="updateMapPosition()" required
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none font-mono">
                    </div>

                    <div>
                        <label for="office_radius_meters" class="block font-medium text-slate-700 uppercase tracking-wider mb-1.5">Toleransi Radius (Meter)</label>
                        <div class="relative">
                            <input type="number" step="1" min="10" max="5000" name="office_radius_meters" id="office_radius_meters"
                                   x-model.number="radius" @input="updateRadiusCircle()" required
                                   class="w-full h-10 text-xs sm:text-sm px-3 pr-12 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none font-semibold">
                            <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-slate-400 font-medium pointer-events-none">meter</span>
                        </div>
                    </div>
                </div>

                <!-- Petunjuk Interaktif -->
                <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200 mb-3 flex items-center gap-2 text-slate-600 text-xs">
                    <i data-lucide="info" class="w-4 h-4 text-blue-800 shrink-0"></i>
                    <span><strong>Petunjuk Geospasial:</strong> Geser (drag) pin kantor pada peta atau klik sembarang titik untuk menentukan lokasi baru. Lingkaran biru memvisualisasikan batas toleransi radius presensi magang.</span>
                </div>

                <!-- Peta Leaflet Interaktif -->
                <div class="rounded-xl overflow-hidden border border-slate-300 shadow-inner relative">
                    <div id="settingsMapContainer" class="w-full h-80 z-10"
                         data-initial-lat="{{ $config['latitude'] }}"
                         data-initial-lng="{{ $config['longitude'] }}"
                         data-initial-radius="{{ $config['radius'] }}"></div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-5 border-t border-slate-100 flex justify-end">
                <button type="submit" class="h-10 px-5 rounded-lg bg-blue-800 hover:bg-blue-900 text-white text-xs sm:text-sm font-medium transition shadow-xs flex items-center space-x-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Pengaturan Lokasi</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Card Ubah Kata Sandi Admin Pembimbing -->
    <div class="bg-white rounded-xl p-6 sm:p-8 shadow-xs border border-slate-200"
         x-data="{ showCurr: false, showNew: false, showConf: false }">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-xs font-semibold text-blue-800 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-blue-800"></i>
                    <span>3. Keamanan Akun Pembimbing BPS</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui kata sandi akun Admin Pembimbing BPS Karanganyar secara berkala.</p>
            </div>
            <span class="text-[11px] text-slate-400">Akun: <strong>{{ auth()->user()->email }}</strong></span>
        </div>

        <form action="{{ route('admin.settings.password') }}" method="POST" class="pt-5 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <!-- Kata Sandi Saat Ini -->
                <div>
                    <label for="admin_current_password" class="block font-medium text-slate-700 uppercase tracking-wider mb-1.5">
                        Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showCurr ? 'text' : 'password'" name="current_password" id="admin_current_password" placeholder="••••••••" required
                               class="w-full h-10 text-xs sm:text-sm px-3 pr-10 rounded-lg border {{ $errors->has('current_password') ? 'border-rose-400 ring-1 ring-rose-300' : 'border-slate-300' }} focus:ring-1 focus:ring-blue-800 outline-none font-medium">
                        <button type="button" @click="showCurr = !showCurr" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <i :data-lucide="showCurr ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <span class="text-[11px] text-rose-600 font-medium block mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Kata Sandi Baru -->
                <div>
                    <label for="admin_new_password" class="block font-medium text-slate-700 uppercase tracking-wider mb-1.5">
                        Kata Sandi Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showNew ? 'text' : 'password'" name="new_password" id="admin_new_password" placeholder="••••••••" required
                               class="w-full h-10 text-xs sm:text-sm px-3 pr-10 rounded-lg border {{ $errors->has('new_password') ? 'border-rose-400 ring-1 ring-rose-300' : 'border-slate-300' }} focus:ring-1 focus:ring-blue-800 outline-none font-medium">
                        <button type="button" @click="showNew = !showNew" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <i :data-lucide="showNew ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                        </button>
                    </div>
                    @error('new_password')
                        <span class="text-[11px] text-rose-600 font-medium block mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Konfirmasi Kata Sandi Baru -->
                <div>
                    <label for="admin_new_password_confirmation" class="block font-medium text-slate-700 uppercase tracking-wider mb-1.5">
                        Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showConf ? 'text' : 'password'" name="new_password_confirmation" id="admin_new_password_confirmation" placeholder="••••••••" required
                               class="w-full h-10 text-xs sm:text-sm px-3 pr-10 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 outline-none font-medium">
                        <button type="button" @click="showConf = !showConf" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <i :data-lucide="showConf ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-100">
                <span class="text-[11px] text-slate-500">
                    Kata sandi minimal 8 karakter. Pastikan akun pembimbing menggunakan kata sandi yang kuat.
                </span>
                <button type="submit" class="h-10 px-5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-xs sm:text-sm font-semibold transition shadow-xs flex items-center justify-center gap-2">
                    <i data-lucide="key" class="w-4 h-4"></i>
                    <span>Perbarui Kata Sandi Admin</span>
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
    function settingsMap() {
        const containerEl = document.getElementById('settingsMapContainer');
        const initialLat = containerEl ? parseFloat(containerEl.getAttribute('data-initial-lat') || '-7.5978') : -7.5978;
        const initialLng = containerEl ? parseFloat(containerEl.getAttribute('data-initial-lng') || '110.9505') : 110.9505;
        const initialRadius = containerEl ? parseFloat(containerEl.getAttribute('data-initial-radius') || '150') : 150;

        return {
            lat: initialLat,
            lng: initialLng,
            radius: initialRadius,
            map: null,
            marker: null,
            circle: null,

            init() {
                this.$nextTick(() => {
                    const container = document.getElementById('settingsMapContainer');
                    if (!container) return;

                    this.map = L.map('settingsMapContainer').setView([this.lat, this.lng], 17);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(this.map);

                    const icon = L.divIcon({
                        className: 'custom-admin-marker',
                        html: '<div style="background-color:#1e3a8a;color:white;width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(30,58,138,0.35);border:2px solid white;"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z\"/><path d=\"M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2\"/><path d=\"M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2\"/><path d=\"M10 6h4\"/><path d=\"M10 10h4\"/><path d=\"M10 14h4\"/><path d=\"M10 18h4\"/></svg></div>',
                        iconSize: [38, 38],
                        iconAnchor: [19, 19]
                    });

                    this.marker = L.marker([this.lat, this.lng], {
                        icon: icon,
                        draggable: true
                    }).addTo(this.map);

                    this.circle = L.circle([this.lat, this.lng], {
                        color: '#2563eb',
                        fillColor: '#3b82f6',
                        fillOpacity: 0.15,
                        radius: this.radius
                    }).addTo(this.map);

                    // Saat marker di-drag
                    this.marker.on('dragend', (e) => {
                        const pos = e.target.getLatLng();
                        this.lat = parseFloat(pos.lat.toFixed(7));
                        this.lng = parseFloat(pos.lng.toFixed(7));
                        this.circle.setLatLng(pos);
                    });

                    // Saat peta diklik
                    this.map.on('click', (e) => {
                        this.lat = parseFloat(e.latlng.lat.toFixed(7));
                        this.lng = parseFloat(e.latlng.lng.toFixed(7));
                        this.marker.setLatLng(e.latlng);
                        this.circle.setLatLng(e.latlng);
                    });
                });
            },

            updateMapPosition() {
                if (this.map && this.marker && this.circle && !isNaN(this.lat) && !isNaN(this.lng)) {
                    const latLng = [this.lat, this.lng];
                    this.marker.setLatLng(latLng);
                    this.circle.setLatLng(latLng);
                    this.map.panTo(latLng);
                }
            },

            updateRadiusCircle() {
                if (this.circle && !isNaN(this.radius)) {
                    this.circle.setRadius(this.radius);
                }
            },

            resetToBpsKaranganyar() {
                this.lat = -7.5978000;
                this.lng = 110.9505000;
                this.radius = 150;
                this.updateMapPosition();
                this.updateRadiusCircle();
                if (this.map) this.map.setView([this.lat, this.lng], 17);
            }
        }
    }
</script>
@endpush
@endsection
