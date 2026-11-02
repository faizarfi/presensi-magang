@extends('layouts.app')

@section('title', 'Presensi Harian Magang - BPS Karanganyar')

@section('content')
<div id="internAbsenRoot" class="max-w-4xl mx-auto space-y-5 px-1 sm:px-2"
     data-has-existing="{{ $existing ? '1' : '0' }}"
     data-has-checked-in="{{ ($existing && $existing->check_in_time) ? '1' : '0' }}"
     data-has-checked-out="{{ ($existing && $existing->check_out_time) ? '1' : '0' }}"
     data-is-afternoon-open="{{ $window['is_afternoon_open'] ? '1' : '0' }}"
     data-office-lat="{{ (float) $officeConfig['latitude'] }}"
     data-office-lng="{{ (float) $officeConfig['longitude'] }}"
     data-office-radius="{{ (float) $officeConfig['radius'] }}"
     data-office-name="{{ $officeConfig['name'] }}"
     data-is-local="{{ app()->environment('local') ? '1' : '0' }}"
     data-sub-status="{{ old('sub_status', $existing?->sub_status ?? 'sakit') }}"
     x-data="internAttendancePage()" x-init="initPage()">

    <!-- ============================================================== -->
    <!-- 1. HEADER & REALTIME CLOCK (Modern & Responsif)                -->
    <!-- ============================================================== -->
    <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 shadow-xs border border-slate-200/90">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center space-x-2 text-xs text-slate-500 mb-1.5">
                    <a href="{{ route('intern.dashboard') }}" class="hover:text-bps-700 transition flex items-center gap-1 font-medium">
                        <i data-lucide="home" class="w-3.5 h-3.5"></i>
                        <span>Beranda</span>
                    </a>
                    <span class="text-slate-300">/</span>
                    <span class="text-bps-900 font-semibold">Presensi Harian</span>
                </nav>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Presensi Harian Magang</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-bps-50 text-bps-900 border border-bps-200 uppercase tracking-wider">
                        BPS Karanganyar
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 flex items-center gap-1">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                    <span>Komplek Perkantoran Cangakan, Karanganyar</span>
                </p>
            </div>

            <!-- Jam Real-Time WIB Card -->
            <div class="self-stretch sm:self-auto flex items-center justify-between sm:justify-end gap-3 bg-gradient-to-r from-bps-50/80 to-slate-50/70 p-3.5 sm:px-4 sm:py-3 rounded-2xl border border-bps-100">
                <div class="text-left sm:text-right">
                    <div class="flex items-center sm:justify-end gap-1.5 text-[10px] uppercase font-bold text-bps-700 tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>Waktu Real-Time (WIB)</span>
                    </div>
                    <div class="flex items-center sm:justify-end gap-1.5 font-mono text-lg sm:text-xl font-extrabold text-bps-900 mt-0.5">
                        <i data-lucide="clock" class="w-4 h-4 text-bps-700"></i>
                        <span id="liveClockDisplay">{{ $now->format('H:i:s') }} WIB</span>
                    </div>
                    <span class="text-[11px] text-slate-500 font-medium block">{{ $today->translatedFormat('l, d F Y') }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Ringkasan Jadwal Operasional BPS -->
        <div class="mt-5 pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Card Pagi -->
            <div class="p-3.5 rounded-2xl border transition {{ $window['is_morning_on_time'] ? 'bg-emerald-50/60 border-emerald-300 text-emerald-950' : ($window['is_morning_late'] ? 'bg-amber-50/60 border-amber-300 text-amber-950' : 'bg-slate-50/70 border-slate-200 text-slate-700') }}">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-600">
                            <i data-lucide="sunrise" class="w-4 h-4"></i>
                        </div>
                        <span class="font-bold text-xs sm:text-sm">1. Presensi Pagi (Datang)</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase font-mono {{ $window['is_morning_on_time'] ? 'bg-emerald-200 text-emerald-900' : ($window['is_morning_late'] ? 'bg-amber-200 text-amber-900' : 'bg-slate-200 text-slate-700') }}">
                        @if($window['is_morning_too_early'])
                            Buka {{ $window['morning_start'] }}
                        @elseif($window['is_morning_on_time'])
                            Sedang Buka
                        @else
                            Lewat (Terlambat)
                        @endif
                    </span>
                </div>
                <div class="text-[11px] sm:text-xs text-slate-600 space-y-0.5 pl-9">
                    <div>Jam Buka: <strong class="text-slate-900 font-mono">{{ $window['morning_start'] }} - {{ $window['morning_end'] }} WIB</strong></div>
                    <div class="text-slate-500">Cukup verifikasi lokasi GPS kantor (tanpa isi jurnal kerja).</div>
                </div>
            </div>

            <!-- Card Pulang -->
            <div class="p-3.5 rounded-2xl border transition {{ $window['is_afternoon_open'] ? 'bg-bps-50/70 border-bps-300 text-bps-900' : ($window['is_afternoon_closed'] ? 'bg-rose-50/60 border-rose-300 text-rose-950' : 'bg-slate-50/70 border-slate-200 text-slate-700') }}">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-bps-100 flex items-center justify-center text-bps-700">
                            <i data-lucide="sunset" class="w-4 h-4"></i>
                        </div>
                        <span class="font-bold text-xs sm:text-sm">2. Presensi Pulang</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase font-mono {{ $window['is_afternoon_open'] ? 'bg-bps-200 text-bps-900' : ($window['is_afternoon_closed'] ? 'bg-rose-200 text-rose-900' : 'bg-slate-200 text-slate-700') }}">
                        @if($window['is_afternoon_too_early'])
                            <span>Buka {{ $window['afternoon_start'] }}</span>
                            <span class="hidden sm:inline text-[9px] font-mono text-bps-700 font-bold ml-1">(<span id="countdownPulangDisplay">...</span>)</span>
                        @elseif($window['is_afternoon_open'])
                            Sedang Buka
                        @else
                            Tutup
                        @endif
                    </span>
                </div>
                <div class="text-[11px] sm:text-xs text-slate-600 space-y-0.5 pl-9">
                    <div>Jam Buka: <strong class="text-slate-900 font-mono">{{ $window['afternoon_start'] }} - {{ $window['afternoon_end'] }} WIB</strong>
                        @if($window['is_friday']) <span class="text-bps-700 font-semibold">(Jumat s.d. 17:30)</span> @endif
                    </div>
                    <div class="text-slate-500">Wajib isi ringkasan laporan pekerjaan & lokasi GPS.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Hari Libur (Jika Ada) -->
    @if($dayStatus['is_off'])
        <div class="p-4 rounded-2xl bg-purple-50/90 border border-purple-200 text-purple-900 flex items-start gap-3 text-xs sm:text-sm shadow-2xs">
            <i data-lucide="coffee" class="w-5 h-5 text-purple-600 shrink-0 mt-0.5"></i>
            <div>
                <h3 class="font-bold text-purple-950">{{ $dayStatus['name'] }}</h3>
                <p class="mt-0.5 text-purple-800 text-xs leading-relaxed">
                    Hari ini adalah hari libur resmi. Presensi kehadiran reguler tidak diwajibkan kecuali terdapat instruksi penugasan khusus dari Pembimbing BPS.
                </p>
            </div>
        </div>
    @endif

    <!-- Alert Kesalahan / Validasi Form -->
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 space-y-2 shadow-2xs">
            <div class="flex items-center gap-2 font-bold text-xs sm:text-sm text-rose-950">
                <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-600 shrink-0"></i>
                <span>Harap perhatikan catatan berikut sebelum mengirim formulir:</span>
            </div>
            <ul class="list-disc list-inside text-xs text-rose-800 space-y-1 ml-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- 2. STATUS PRESENSI HARI INI (Jika Sudah Melakukan Aksi)        -->
    <!-- ============================================================== -->
    @if($existing)
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-xs border border-blue-200/90 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <span class="text-[10px] uppercase font-bold text-blue-700 tracking-wider block">Catatan Kehadiran Anda</span>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2 mt-0.5">
                        <span>{{ $existing->status_label }}</span>
                        <span class="text-xs font-normal text-slate-500">({{ $existing->sub_status_label }})</span>
                    </h2>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-xl text-xs font-bold border {{ $existing->approval_badge_class }}">
                        {{ $existing->approval_status === 'approved' ? 'ACC Disetujui' : ($existing->approval_status === 'rejected' ? 'Ditolak' : 'Menunggu ACC') }}
                    </span>
                    @if($existing->check_in_time && $existing->check_out_time)
                        <span class="px-3 py-1 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-300 flex items-center gap-1">
                            <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                            <span>Lengkap</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- Mini Summary Grid 3 Kolom -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/80">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Jam Masuk</span>
                    <span class="text-sm sm:text-base font-bold text-slate-900 font-mono mt-0.5 block">
                        {{ $existing->check_in_time ? substr($existing->check_in_time, 0, 5) . ' WIB' : '-' }}
                    </span>
                    @if($existing->is_late)
                        <span class="text-[10px] text-amber-700 font-bold block mt-1">Terlambat ({{ $existing->late_minutes }}m)</span>
                    @else
                        <span class="text-[10px] text-emerald-700 font-medium block mt-1">Tepat Waktu</span>
                    @endif
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/80">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Jam Pulang</span>
                    @if($existing->check_out_time)
                        <span class="text-sm sm:text-base font-bold text-slate-900 font-mono mt-0.5 block">
                            {{ substr($existing->check_out_time, 0, 5) . ' WIB' }}
                        </span>
                        <span class="text-[10px] text-emerald-700 font-medium block mt-1">Sudah Presensi Pulang</span>
                    @else
                        <span class="text-sm sm:text-base font-bold text-amber-700 font-mono mt-0.5 block">Belum Pulang</span>
                        <span class="text-[10px] text-slate-500 block mt-1">Buka pukul 16:00 WIB</span>
                    @endif
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/80 col-span-2 sm:col-span-1">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Lokasi Presensi</span>
                    <span class="text-xs font-semibold text-slate-800 block truncate mt-0.5">{{ $existing->location_address ?: 'Kantor BPS Karanganyar' }}</span>
                    <span class="text-[10px] text-slate-500 block mt-1">Radius Terverifikasi</span>
                </div>
            </div>

            @if($existing->check_in_time && !$existing->check_out_time)
                <div class="p-3.5 rounded-xl bg-blue-50/80 border border-blue-200 text-blue-950 text-xs flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-blue-700 shrink-0"></i>
                        <span>Presensi masuk Anda telah tercatat. Saat jam pulang tiba (16:00 WIB), silakan beralih ke tab <strong>Presensi Pulang</strong> di bawah.</span>
                    </div>
                </div>
            @elseif($existing->check_in_time && $existing->check_out_time)
                <div class="p-3.5 rounded-xl bg-emerald-50/80 border border-emerald-200 text-emerald-950 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="party-popper" class="w-4 h-4 text-emerald-700 shrink-0"></i>
                        <span>Presensi hari ini telah lengkap (Masuk & Pulang). Terima kasih atas dedikasi Anda di BPS Karanganyar!</span>
                    </div>
                    <a href="{{ route('intern.dashboard') }}" class="h-8 px-3 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs inline-flex items-center justify-center gap-1.5 shrink-0 self-start sm:self-auto transition">
                        <span>Ke Beranda</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            @endif
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- 3. SEGMENTED NAVIGATION TABS (Desain Bersih & Elegan)          -->
    <!-- ============================================================== -->
    <div class="bg-white rounded-2xl p-2 shadow-xs border border-slate-200/90">
        <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-100/90 rounded-xl text-xs sm:text-sm font-semibold">
            <!-- Tab 1: Pagi (Datang) -->
            <button type="button" @click="switchTab('pagi')"
                    :class="activeTab === 'pagi' ? 'bg-white text-bps-900 font-bold shadow-xs border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-white/40'"
                    class="py-2.5 sm:py-3 px-2 sm:px-4 rounded-lg flex items-center justify-center gap-1.5 sm:gap-2 transition cursor-pointer">
                <i data-lucide="sunrise" class="w-4 h-4 text-amber-500 shrink-0"></i>
                <span class="truncate">Presensi Masuk</span>
                @if($existing && $existing->check_in_time)
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0" title="Sudah Masuk"></span>
                @endif
            </button>

            <!-- Tab 2: Pulang (Sore) -->
            <button type="button" @click="switchTab('pulang')"
                    :class="activeTab === 'pulang' ? 'bg-white text-bps-900 font-bold shadow-xs border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-white/40'"
                    class="py-2.5 sm:py-3 px-2 sm:px-4 rounded-lg flex items-center justify-center gap-1.5 sm:gap-2 transition cursor-pointer">
                <i data-lucide="sunset" class="w-4 h-4 text-bps-700 shrink-0"></i>
                <span class="truncate">Presensi Pulang</span>
                @if($existing && $existing->check_out_time)
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0" title="Sudah Pulang"></span>
                @endif
            </button>

            <!-- Tab 3: Izin / Sakit -->
            <button type="button" @click="switchTab('izin')"
                    :class="activeTab === 'izin' ? 'bg-white text-bps-900 font-bold shadow-xs border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-white/40'"
                    class="py-2.5 sm:py-3 px-2 sm:px-4 rounded-lg flex items-center justify-center gap-1.5 sm:gap-2 transition cursor-pointer">
                <i data-lucide="file-text" class="w-4 h-4 text-bps-orange shrink-0"></i>
                <span class="truncate">Izin / Sakit</span>
            </button>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 4. PANEL 1: PRESENSI MASUK (PAGI) - HANYA LOKASI GPS KANTOR    -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'pagi'" x-cloak class="space-y-4">
        <form id="formPresensiPagi" action="{{ route('intern.absen.store') }}" method="POST" class="space-y-4"
              @submit.prevent="submitPagi()">
            @csrf
            <input type="hidden" name="status" value="hadir">
            <input type="hidden" name="latitude" :value="latitude">
            <input type="hidden" name="longitude" :value="longitude">
            <input type="hidden" name="location_address" value="Komplek Perkantoran Cangakan, BPS Karanganyar">

            <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 shadow-xs border border-slate-200/90 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="sunrise" class="w-5 h-5 text-amber-500"></i>
                            <span>Formulir Presensi Masuk (Pagi)</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Jam buka: 07:30 - 08:00 WIB (Hanya konfirmasi lokasi GPS kantor, tanpa laporan tugas)</p>
                    </div>

                    @if($existing && $existing->check_in_time)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 self-start sm:self-auto">
                            Sudah Presensi Masuk
                        </span>
                    @endif
                </div>

                <!-- Status Jam Buka Pagi -->
                @if($window['is_morning_too_early'])
                    <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200 text-amber-900 text-xs sm:text-sm flex items-start gap-3">
                        <i data-lucide="clock" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="font-bold text-amber-950">Presensi kedatangan belum dibuka.</strong>
                            <p class="mt-0.5 text-xs text-amber-800 leading-relaxed">
                                Presensi pagi baru dibuka pada pukul <strong>07:30 WIB</strong>. Tombol simpan akan aktif secara otomatis saat jam telah tiba.
                            </p>
                        </div>
                    </div>
                @elseif($window['is_morning_on_time'])
                    <div class="p-3.5 rounded-2xl bg-emerald-50/80 border border-emerald-200 text-emerald-950 text-xs sm:text-sm flex items-center gap-2.5">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                        <span>Waktu presensi pagi sedang dibuka! Melakukan presensi sebelum pukul 08:00 WIB otomatis tercatat <strong>Tepat Waktu</strong>.</span>
                    </div>
                @else
                    <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-300 text-amber-950 text-xs sm:text-sm space-y-2.5">
                        <div class="flex items-center gap-2 font-bold text-amber-900">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0"></i>
                            <span>Waktu Telah Melewati Pukul 08:00 WIB (Terlambat)</span>
                        </div>
                        <p class="text-xs text-amber-800 leading-relaxed">
                            Batas waktu presensi tepat waktu adalah pukul 08:00 WIB. Presensi kedatangan Anda tetap diproses dan akan dicatat <strong>Terlambat</strong>.
                        </p>
                        <div>
                            <label for="pagi_late_reason" class="block font-semibold text-slate-800 text-xs mb-1">
                                Alasan Keterlambatan <span class="text-slate-400 font-normal">(Opsional):</span>
                            </label>
                            <input type="text" name="late_reason" id="pagi_late_reason"
                                   placeholder="Contoh: Terkendala cuaca hujan, kendala transportasi, urusan administrasi..."
                                   class="w-full h-10 px-3.5 rounded-xl border border-amber-300 bg-white text-xs outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                        </div>
                    </div>
                @endif

                <!-- Validasi Lokasi GPS Kantor BPS -->
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/80 border border-slate-200 space-y-3.5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <div>
                            <span class="text-xs sm:text-sm font-bold text-slate-900 block">Deteksi Lokasi GPS Kantor BPS</span>
                            <span class="text-[11px] text-slate-500">Wajib berada dalam radius maksimal {{ $officeConfig['radius'] }} meter dari kantor BPS Karanganyar</span>
                        </div>

                        <button type="button" @click="detectLocation()" :disabled="detecting"
                                class="h-9 px-4 rounded-xl bg-bps-900 hover:bg-bps-800 text-white text-xs font-semibold transition inline-flex items-center justify-center gap-1.5 shadow-2xs self-start sm:self-auto cursor-pointer">
                            <i data-lucide="crosshair" class="w-3.5 h-3.5" :class="detecting ? 'animate-spin' : ''"></i>
                            <span x-text="detecting ? 'Mencari Titik GPS...' : 'Deteksi Lokasi Saya'"></span>
                        </button>
                    </div>

                    <!-- Status Deteksi Banner -->
                    <div class="p-3.5 rounded-xl border text-xs transition"
                         :class="geoStatus.isInside ? 'bg-emerald-50/90 border-emerald-300 text-emerald-950' : (geoStatus.detected ? 'bg-rose-50/90 border-rose-300 text-rose-950' : 'bg-white border-slate-200 text-slate-600')">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="map-pin" class="w-4 h-4" :class="geoStatus.isInside ? 'text-emerald-700' : (geoStatus.detected ? 'text-rose-600' : 'text-slate-400')"></i>
                                <span class="font-bold" x-text="geoStatus.title"></span>
                            </div>
                            <span x-show="geoStatus.detected" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono"
                                  :class="geoStatus.isInside ? 'bg-emerald-200 text-emerald-950' : 'bg-rose-200 text-rose-950'"
                                  x-text="geoStatus.isInside ? 'Radius Valid (' + distanceMeters + 'm)' : 'Di Luar Radius (' + distanceMeters + 'm)'"></span>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-600 leading-relaxed" x-text="geoStatus.message"></p>
                    </div>

                    <!-- Mini Leaflet Map Preview -->
                    <div class="rounded-2xl overflow-hidden border border-slate-200/90 relative h-48 sm:h-56 w-full z-10 shadow-2xs">
                        <div id="mapPagi" class="w-full h-full"></div>
                    </div>

                    @if(app()->environment('local'))
                    <!-- Shortcut Simulasi Koordinat Kantor (Development Only) -->
                    <div class="p-2.5 bg-amber-50/80 rounded-xl border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] text-amber-900">
                        <span>Simulasi titik kantor BPS (Development Mode):</span>
                        <button type="button" @click="simulateAtOffice()" class="px-3 py-1 rounded-lg bg-amber-200 hover:bg-amber-300 text-amber-950 font-bold transition self-start sm:self-auto cursor-pointer">
                            Gunakan Titik Kantor BPS
                        </button>
                    </div>
                    @endif
                </div>

                <!-- Checkbox Konfirmasi -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-start gap-3">
                    <input type="checkbox" name="is_confirmed" id="confirm_pagi" value="1" x-model="isConfirmedPagi" required
                           class="w-4 h-4 mt-0.5 text-bps-900 rounded border-slate-300 focus:ring-bps-900 cursor-pointer shrink-0">
                    <label for="confirm_pagi" class="text-xs text-slate-700 cursor-pointer select-none leading-relaxed">
                        Saya menyatakan hadir di lingkungan kantor Badan Pusat Statistik Kabupaten Karanganyar hari ini secara sah dan benar.
                    </label>
                </div>

                <!-- Tombol Submit Pagi -->
                <div>
                    @if($existing && $existing->check_in_time)
                        <button type="button" disabled
                                class="w-full h-12 rounded-2xl bg-slate-100 border border-slate-200 text-slate-400 font-bold text-xs sm:text-sm cursor-not-allowed inline-flex items-center justify-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-600"></i>
                            <span>Anda Sudah Melakukan Presensi Masuk (Pukul {{ substr($existing->check_in_time, 0, 5) }} WIB)</span>
                        </button>
                    @elseif($window['is_morning_too_early'])
                        <button type="button" disabled
                                class="w-full h-12 rounded-2xl bg-slate-200 text-slate-500 font-bold text-xs sm:text-sm cursor-not-allowed inline-flex items-center justify-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                            <span>Presensi Masuk Dibuka Pukul {{ $window['morning_start'] }} WIB</span>
                        </button>
                    @else
                        <button type="submit" :disabled="submittingPagi"
                                class="w-full h-12 rounded-2xl font-bold text-xs sm:text-sm text-white transition inline-flex items-center justify-center gap-2 shadow-xs cursor-pointer {{ $window['is_morning_late'] ? 'bg-amber-600 hover:bg-amber-700' : 'bg-bps-900 hover:bg-bps-800' }}">
                            <template x-if="submittingPagi">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Menyimpan Presensi Masuk...</span>
                                </span>
                            </template>
                            <template x-if="!submittingPagi">
                                <span class="inline-flex items-center gap-2">
                                    <i data-lucide="log-in" class="w-4 h-4"></i>
                                    <span>{{ $window['is_morning_late'] ? 'Presensi Masuk Sekarang (Terlambat)' : 'Presensi Masuk Sekarang (Tepat Waktu)' }}</span>
                                </span>
                            </template>
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- ============================================================== -->
    <!-- 5. PANEL 2: PRESENSI PULANG (SORE) - LAPORAN KERJA & GPS       -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'pulang'" x-cloak class="space-y-4">
        <form id="formPresensiPulang" action="{{ route('intern.absen.store') }}" method="POST" class="space-y-4"
              @submit.prevent="submitPulang()">
            @csrf
            <input type="hidden" name="status" value="pulang">
            <input type="hidden" name="latitude" :value="latitude">
            <input type="hidden" name="longitude" :value="longitude">
            <input type="hidden" name="location_address" value="Komplek Perkantoran Cangakan, BPS Karanganyar">

            <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 shadow-xs border border-slate-200/90 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="sunset" class="w-5 h-5 text-bps-700"></i>
                            <span>Presensi Pulang & Laporan Aktivitas</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Jam operasional: 16:00 - {{ $window['afternoon_end'] }} WIB @if($window['is_friday']) (Khusus Jumat s.d. 17:30 WIB) @endif</p>
                    </div>

                    @if($existing && $existing->check_out_time)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 self-start sm:self-auto">
                            Sudah Presensi Pulang
                        </span>
                    @endif
                </div>

                @if(!$existing || !$existing->check_in_time)
                    <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200 text-amber-900 text-xs sm:text-sm flex items-start gap-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="font-bold text-amber-950">Anda belum melakukan presensi masuk!</strong>
                            <p class="mt-0.5 text-xs text-amber-800 leading-relaxed">
                                Presensi pulang hanya dapat diakses setelah mencatat presensi masuk. Silakan beralih ke tab <strong>Presensi Masuk</strong>.
                            </p>
                        </div>
                    </div>
                @elseif($window['is_afternoon_too_early'])
                    <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-bps-50/90 to-slate-50/80 border border-bps-200 text-bps-900 text-xs sm:text-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-bps-700 text-white flex items-center justify-center shrink-0 shadow-2xs mt-0.5">
                                <i data-lucide="timer" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <strong class="font-bold text-bps-900 block">Presensi kepulangan belum dibuka.</strong>
                                <p class="mt-0.5 text-xs text-bps-800 leading-relaxed">
                                    Presensi kepulangan & laporan pekerjaan dibuka pukul <strong>16:00 WIB</strong>.
                                </p>
                            </div>
                        </div>
                        <div class="px-3.5 py-2 rounded-xl bg-white border border-bps-200 shadow-xs flex items-center gap-2 self-start sm:self-auto shrink-0">
                            <span class="w-2 h-2 rounded-full bg-bps-700 animate-ping"></span>
                            <span class="text-[11px] text-slate-500 font-medium">Dibuka dalam:</span>
                            <span id="countdownPulangDetail" class="font-mono text-xs sm:text-sm font-bold text-bps-900">Menghitung...</span>
                        </div>
                    </div>
                @elseif($window['is_afternoon_closed'])
                    <div class="p-4 rounded-2xl bg-rose-50/80 border border-rose-200 text-rose-900 text-xs sm:text-sm flex items-start gap-3">
                        <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="font-bold text-rose-950">Batas waktu presensi kepulangan hari ini telah ditutup.</strong>
                            <p class="mt-0.5 text-xs text-rose-800 leading-relaxed">
                                Presensi pulang ditutup pada pukul <strong>{{ $window['afternoon_end'] }} WIB</strong>. Jika ada kendala kedinasan, silakan hubungi Pembimbing BPS Anda.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="p-3.5 rounded-2xl bg-emerald-50/80 border border-emerald-200 text-emerald-950 text-xs sm:text-sm flex items-center gap-2.5">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                        <span>Waktu presensi kepulangan sedang dibuka (s.d. {{ $window['afternoon_end'] }} WIB). Silakan isi laporan di bawah.</span>
                    </div>
                @endif

                <!-- SATU KOTAK FORMULIR RINGKAS: LAPORAN AKTIVITAS / PEKERJAAN HARI INI -->
                <div>
                    <label for="pulang_activity" class="block text-xs sm:text-sm font-bold text-slate-800 mb-1.5 flex items-center justify-between">
                        <span>Laporan Aktivitas & Pekerjaan Hari Ini <span class="text-rose-500">*</span></span>
                        <span class="text-[11px] font-normal text-slate-400">Wajib diisi (minimal 5 karakter)</span>
                    </label>
                    <textarea name="activity_description" id="pulang_activity" rows="4" required
                              x-model="activityDescription"
                              placeholder="Tuliskan ringkasan tugas atau pekerjaan yang telah Anda selesaikan hari ini di BPS Karanganyar... (Contoh: Membantu entri data sensus pertanian, menyusun rekapitulasi data inflasi, menyortir dokumen survei tahunan)"
                              class="w-full text-xs sm:text-sm p-4 rounded-2xl border border-slate-300 focus:ring-2 focus:ring-bps-700/20 focus:border-bps-700 outline-none transition leading-relaxed shadow-2xs">{{ old('activity_description', $existing?->activity_description) }}</textarea>
                    <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Laporan ini langsung tersimpan ke sistem verifikasi Pembimbing BPS.</span>
                    </p>
                </div>

                <!-- Deteksi Titik GPS Pulang -->
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/80 border border-slate-200 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <div>
                            <span class="text-xs sm:text-sm font-bold text-slate-900 block">Titik GPS Kepulangan</span>
                            <span class="text-[11px] text-slate-500">Radius kantor BPS maksimal {{ $officeConfig['radius'] }} meter</span>
                        </div>

                        <button type="button" @click="detectLocation()" :disabled="detecting"
                                class="h-9 px-4 rounded-xl bg-bps-700 hover:bg-bps-800 text-white text-xs font-semibold transition inline-flex items-center justify-center gap-1.5 shadow-2xs self-start sm:self-auto cursor-pointer">
                            <i data-lucide="crosshair" class="w-3.5 h-3.5" :class="detecting ? 'animate-spin' : ''"></i>
                            <span x-text="detecting ? 'Mencari Titik GPS...' : 'Cek Lokasi Saya'"></span>
                        </button>
                    </div>

                    <div class="p-3.5 rounded-xl border text-xs"
                         :class="geoStatus.isInside ? 'bg-emerald-50/90 border-emerald-300 text-emerald-950' : (geoStatus.detected ? 'bg-rose-50/90 border-rose-300 text-rose-950' : 'bg-white border-slate-200 text-slate-600')">
                        <div class="flex items-center justify-between">
                            <span class="font-bold" x-text="geoStatus.title"></span>
                            <span x-show="geoStatus.detected" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono"
                                  :class="geoStatus.isInside ? 'bg-emerald-200 text-emerald-950' : 'bg-rose-200 text-rose-950'"
                                  x-text="geoStatus.isInside ? 'Radius Valid (' + distanceMeters + 'm)' : 'Di Luar Radius (' + distanceMeters + 'm)'"></span>
                        </div>
                    </div>
                </div>

                <!-- Checkbox Konfirmasi -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-start gap-3">
                    <input type="checkbox" name="is_confirmed" id="confirm_pulang" value="1" x-model="isConfirmedPulang" required
                           class="w-4 h-4 mt-0.5 text-bps-700 rounded border-slate-300 focus:ring-bps-700 cursor-pointer shrink-0">
                    <label for="confirm_pulang" class="text-xs text-slate-700 cursor-pointer select-none leading-relaxed">
                        Saya menyatakan telah menyelesaikan tugas harian dan laporan yang dikirimkan adalah benar sesuai pekerjaan di BPS Karanganyar.
                    </label>
                </div>

                <!-- Tombol Submit Pulang -->
                <div>
                    @if($existing && $existing->check_out_time)
                        <button type="button" disabled
                                class="w-full h-12 rounded-2xl bg-slate-100 border border-slate-200 text-slate-400 font-bold text-xs sm:text-sm cursor-not-allowed inline-flex items-center justify-center gap-2">
                            <i data-lucide="check-check" class="w-4 h-4 text-emerald-600"></i>
                            <span>Presensi Pulang Hari Ini Telah Selesai (Pukul {{ substr($existing->check_out_time, 0, 5) }} WIB)</span>
                        </button>
                    @elseif(!$existing || !$existing->check_in_time)
                        <button type="button" disabled
                                class="w-full h-12 rounded-2xl bg-slate-200 text-slate-500 font-bold text-xs sm:text-sm cursor-not-allowed inline-flex items-center justify-center gap-2">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                            <span>Lakukan Presensi Masuk Terlebih Dahulu</span>
                        </button>
                    @elseif($window['is_afternoon_too_early'])
                        <button type="button" disabled
                                class="w-full h-12 rounded-2xl bg-slate-200 text-slate-500 font-bold text-xs sm:text-sm cursor-not-allowed inline-flex items-center justify-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                            <span>Presensi Pulang Dibuka Pukul {{ $window['afternoon_start'] }} WIB</span>
                        </button>
                    @elseif($window['is_afternoon_closed'])
                        <button type="button" disabled
                                class="w-full h-12 rounded-2xl bg-rose-100 text-rose-700 font-bold text-xs sm:text-sm cursor-not-allowed inline-flex items-center justify-center gap-2">
                            <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                            <span>Batas Presensi Pulang Telah Ditutup ({{ $window['afternoon_end'] }} WIB)</span>
                        </button>
                    @else
                        <button type="submit" :disabled="submittingPulang"
                                class="w-full h-12 rounded-2xl bg-bps-700 hover:bg-bps-800 font-bold text-xs sm:text-sm text-white transition inline-flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                            <template x-if="submittingPulang">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Menyimpan Presensi Pulang...</span>
                                </span>
                            </template>
                            <template x-if="!submittingPulang">
                                <span class="inline-flex items-center gap-2">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    <span>Kirim Presensi Pulang & Laporan Hari Ini</span>
                                </span>
                            </template>
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- ============================================================== -->
    <!-- 6. PANEL 3: IZIN / SAKIT (TIDAK HADIR)                         -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'izin'" x-cloak class="space-y-4">
        <form action="{{ route('intern.absen.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4"
              @submit="submittingIzin = true">
            @csrf
            <input type="hidden" name="status" value="tidak_hadir_keterangan">

            <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 shadow-xs border border-slate-200/90 space-y-5">
                <div class="pb-4 border-b border-slate-100">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-5 h-5 text-amber-600"></i>
                        <span>Keterangan Berhalangan Hadir (Izin / Sakit)</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Bebas dari batasan radius kantor BPS. Lampirkan surat dokter atau surat permohonan izin.</p>
                </div>

                <!-- Pilihan Jenis Keterangan -->
                <div class="space-y-2">
                    <label class="block text-xs sm:text-sm font-bold text-slate-800">Jenis Keterangan:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs sm:text-sm">
                        <label class="flex items-center p-3.5 rounded-2xl border cursor-pointer transition"
                               :class="subStatus === 'sakit' ? 'border-amber-500 bg-amber-50/70 font-bold text-amber-950 shadow-2xs' : 'border-slate-200 hover:border-slate-300 text-slate-700 bg-white'">
                            <input type="radio" name="sub_status" value="sakit" x-model="subStatus" class="sr-only">
                            <span class="w-4 h-4 rounded-full border mr-2.5 flex items-center justify-center shrink-0"
                                  :class="subStatus === 'sakit' ? 'border-amber-600 bg-amber-600' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="subStatus === 'sakit'"></span>
                            </span>
                            <span>Sakit (Surat Dokter)</span>
                        </label>

                        <label class="flex items-center p-3.5 rounded-2xl border cursor-pointer transition"
                               :class="subStatus === 'izin' ? 'border-bps-700 bg-bps-50/70 font-bold text-bps-900 shadow-2xs' : 'border-slate-200 hover:border-slate-300 text-slate-700 bg-white'">
                            <input type="radio" name="sub_status" value="izin" x-model="subStatus" class="sr-only">
                            <span class="w-4 h-4 rounded-full border mr-2.5 flex items-center justify-center shrink-0"
                                  :class="subStatus === 'izin' ? 'border-bps-700 bg-bps-700' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="subStatus === 'izin'"></span>
                            </span>
                            <span>Izin Kepentingan</span>
                        </label>

                        <label class="flex items-center p-3.5 rounded-2xl border cursor-pointer transition"
                               :class="subStatus === 'dinas_luar' ? 'border-purple-500 bg-purple-50/70 font-bold text-purple-950 shadow-2xs' : 'border-slate-200 hover:border-slate-300 text-slate-700 bg-white'">
                            <input type="radio" name="sub_status" value="dinas_luar" x-model="subStatus" class="sr-only">
                            <span class="w-4 h-4 rounded-full border mr-2.5 flex items-center justify-center shrink-0"
                                  :class="subStatus === 'dinas_luar' ? 'border-purple-600 bg-purple-600' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="subStatus === 'dinas_luar'"></span>
                            </span>
                            <span>Tugas / Dinas Luar</span>
                        </label>
                    </div>
                </div>

                <!-- Alasan / Uraian -->
                <div>
                    <label for="izin_alasan" class="block text-xs sm:text-sm font-bold text-slate-800 mb-1.5">
                        Alasan / Keterangan Tidak Hadir <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="activity_description" id="izin_alasan" rows="3" required
                              placeholder="Deskripsikan alasan mengapa Anda tidak dapat hadir di kantor BPS hari ini..."
                              class="w-full text-xs sm:text-sm p-3.5 rounded-2xl border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none leading-relaxed shadow-2xs">{{ old('activity_description', $existing?->activity_description) }}</textarea>
                </div>

                <!-- Lampiran Dokumen dengan Pilihan Kamera Langsung & Berkas -->
                <div class="space-y-2.5">
                    <label class="block text-xs sm:text-sm font-bold text-slate-800 flex items-center justify-between">
                        <span>Lampirkan Berkas Pendukung (Surat Dokter / Dokumen Izin)</span>
                        <span class="text-slate-400 font-normal text-xs">(Opsional)</span>
                    </label>

                    <!-- Pilihan 2 Aksi: Kamera Langsung HP atau Pilih Berkas Dokumen -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <!-- Pilihan 1: Foto Kamera HP Langsung -->
                        <label class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/80 hover:bg-bps-50/70 hover:border-bps-300 transition cursor-pointer flex items-center gap-3 group shadow-2xs">
                            <input type="file" name="attachment_file_camera" id="attachment_camera_input" accept="image/*" capture="environment" class="sr-only"
                                   @change="handleFileSelect($event)">
                            <div class="w-9 h-9 rounded-xl bg-bps-100 text-bps-900 flex items-center justify-center shrink-0 group-hover:scale-105 transition shadow-2xs">
                                <i data-lucide="camera" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-slate-900 block group-hover:text-bps-900">Foto Kamera Langsung</span>
                                <span class="text-[10px] text-slate-500 block truncate">Ambil foto surat dokter via kamera HP</span>
                            </div>
                        </label>

                        <!-- Pilihan 2: Pilih Dokumen / PDF dari Galeri -->
                        <label class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/80 hover:bg-slate-100/90 hover:border-slate-300 transition cursor-pointer flex items-center gap-3 group shadow-2xs">
                            <input type="file" name="attachment_file" id="attachment_file_input" accept=".pdf,.jpg,.jpeg,.png" class="sr-only"
                                   @change="handleFileSelect($event)">
                            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0 group-hover:scale-105 transition shadow-2xs">
                                <i data-lucide="file-up" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-slate-900 block">Pilih Berkas / PDF</span>
                                <span class="text-[10px] text-slate-500 block truncate">PDF, JPG, PNG hingga 10 MB</span>
                            </div>
                        </label>
                    </div>

                    <!-- Pratinjau Berkas yang Dipilih -->
                    <template x-if="attachmentName">
                        <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-950 text-xs flex items-center justify-between gap-3 shadow-2xs">
                            <div class="flex items-center gap-3 min-w-0">
                                <template x-if="attachmentPreview">
                                    <img :src="attachmentPreview" alt="Pratinjau Foto" class="w-11 h-11 rounded-xl object-cover border border-emerald-300 shrink-0 shadow-2xs">
                                </template>
                                <template x-if="!attachmentPreview">
                                    <div class="w-11 h-11 rounded-xl bg-emerald-200 flex items-center justify-center text-emerald-800 shrink-0">
                                        <i data-lucide="file-check" class="w-5 h-5"></i>
                                    </div>
                                </template>
                                <div class="min-w-0">
                                    <span class="font-bold text-emerald-900 block truncate" x-text="attachmentName"></span>
                                    <span class="text-[10px] text-emerald-700 block">Berkas siap dilampirkan</span>
                                </div>
                            </div>
                            <button type="button" @click="removeAttachment()" title="Hapus Berkas"
                                    class="p-1.5 rounded-xl text-emerald-700 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer shrink-0">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Checkbox Konfirmasi -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-start gap-3">
                    <input type="checkbox" name="is_confirmed" id="confirm_izin" value="1" required
                           class="w-4 h-4 mt-0.5 text-bps-900 rounded border-slate-300 focus:ring-bps-900 cursor-pointer shrink-0">
                    <label for="confirm_izin" class="text-xs text-slate-700 cursor-pointer select-none leading-relaxed">
                        Saya menyatakan keterangan tidak hadir ini adalah benar dan dapat dipertanggungjawabkan kepada Pembimbing BPS Karanganyar.
                    </label>
                </div>

                <!-- Tombol Submit Izin -->
                <button type="submit" :disabled="submittingIzin"
                        class="w-full h-12 rounded-2xl bg-amber-600 hover:bg-amber-700 font-bold text-xs sm:text-sm text-white transition inline-flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Kirim Keterangan Izin / Sakit</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/intern-absen.js') }}"></script>
@endpush
