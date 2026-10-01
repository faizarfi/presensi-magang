@extends('layouts.app')

@section('title', 'Portal Masuk Resmi - SIM-MAGANG BPS Karanganyar')

@section('content')
<div class="min-h-screen w-full flex flex-col lg:grid lg:grid-cols-12 bg-slate-50 relative overflow-hidden"
    x-data="{ showPass: false }">

    <!-- ========================================================
         LEFT SIDE: BPS EXECUTIVE CORPORATE SHOWCASE (Desktop)
         ======================================================== -->
    <div class="lg:col-span-6 xl:col-span-7 hidden lg:flex flex-col justify-between relative bg-gradient-to-br from-[#001433] via-[#002250] to-[#003470] text-white p-10 xl:p-14 overflow-hidden select-none">
        <!-- Ambient Glowing Background Circles -->
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-blue-500/15 blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 -right-24 w-80 h-80 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -left-12 w-96 h-96 rounded-full bg-blue-700/20 blur-3xl pointer-events-none"></div>

        <!-- Subtle Geometric Matrix Grid SVG Overlay -->
        <div class="absolute inset-0 opacity-[0.035] pointer-events-none" style="background-image: radial-gradient(rgba(255,255,255,0.8) 1px, transparent 1px); background-size: 24px 24px;"></div>

        <!-- Top Header & Brand Identity -->
        <div class="relative z-10">
            <div class="flex items-center gap-3.5">
                <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md p-2.5 border border-white/20 shadow-xl flex items-center justify-center flex-shrink-0">
                    <img src="{{ asset('images/bps-logo.png') }}" alt="Logo BPS RI" class="w-full h-full object-contain filter drop-shadow">
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs tracking-wider uppercase font-semibold text-blue-200/90">Republik Indonesia</span>
                        <span class="text-white/40">•</span>
                        <span class="text-[11px] tracking-wider uppercase font-medium text-emerald-400 bg-emerald-950/60 border border-emerald-500/30 px-2 py-0.5 rounded-full">Portal Resmi</span>
                    </div>
                    <h2 class="text-lg xl:text-xl font-bold text-white tracking-tight leading-tight mt-0.5">BADAN PUSAT STATISTIK</h2>
                    <p class="text-xs font-semibold text-blue-300 tracking-wider uppercase">KABUPATEN KARANGANYAR</p>
                </div>
            </div>
        </div>

        <!-- Middle Showcase Headline & Core Features -->
        <div class="relative z-10 my-auto py-10 space-y-8">
            <div class="space-y-3 max-w-xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-900/60 border border-blue-400/30 text-blue-200 text-xs font-medium backdrop-blur-sm">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-blue-300"></i>
                    <span>SIM-MAGANG • Sistem Presensi GPS & Logbook Digital</span>
                </div>
                <h1 class="text-3xl xl:text-4xl font-extrabold text-white tracking-tight leading-[1.15]">
                    Integritas & Akurasi Data <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-300 via-sky-200 to-emerald-300">Magang Statistik</span>
                </h1>
                <p class="text-sm text-blue-100/80 leading-relaxed font-normal">
                    Platform resmi Badan Pusat Statistik Kabupaten Karanganyar untuk pemantauan kehadiran presisi berbasis radius kantor, pencatatan logbook capaian harian, dan evaluasi berkala oleh pembimbing lapangan.
                </p>
            </div>

            <!-- 3 Glass Feature Cards -->
            <div class="grid grid-cols-1 gap-3 max-w-xl">
                <div class="p-3.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.09] border border-white/10 backdrop-blur-md transition flex items-start space-x-3.5">
                    <div class="w-9 h-9 rounded-lg bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-blue-300 flex-shrink-0 mt-0.5">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-white tracking-wide">Validasi Geofencing GPS (Radius 150m)</h3>
                        <p class="text-[11px] text-blue-100/70 mt-0.5 leading-snug">Presensi otomatis mendeteksi posisi koordinat perangkat di kantor BPS Cangakan Karanganyar.</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.09] border border-white/10 backdrop-blur-md transition flex items-start space-x-3.5">
                    <div class="w-9 h-9 rounded-lg bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-emerald-300 flex-shrink-0 mt-0.5">
                        <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-white tracking-wide">Logbook Kegiatan & Pembelajaran Terstruktur</h3>
                        <p class="text-[11px] text-blue-100/70 mt-0.5 leading-snug">Dokumentasi tugas statistik harian, kompetensi yang dipelajari, dan kendala operasional lapangan.</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.09] border border-white/10 backdrop-blur-md transition flex items-start space-x-3.5">
                    <div class="w-9 h-9 rounded-lg bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-300 flex-shrink-0 mt-0.5">
                        <i data-lucide="file-check" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-white tracking-wide">Verifikasi Pembimbing & Rekap Berita Acara PDF</h3>
                        <p class="text-[11px] text-blue-100/70 mt-0.5 leading-snug">Persetujuan (ACC) berjenjang serta cetak Berita Acara Rekapitulasi Presensi resmi berstandar BPS RI.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Office Operational Pill -->
        <div class="relative z-10 pt-6 border-t border-white/10 flex flex-wrap items-center justify-between gap-4 text-xs text-blue-200/80">
            <div class="flex items-center gap-2">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span class="font-medium text-white/90">Kantor BPS Karanganyar Aktif</span>
                <span class="text-white/40">•</span>
                <span class="text-blue-300">Jam Kerja 07:30 - 16:00 WIB</span>
            </div>
            <div class="flex items-center gap-1.5 text-blue-200/70 font-mono text-[11px]">
                <i data-lucide="map" class="w-3.5 h-3.5 text-blue-300"></i>
                <span>Komplek Perkantoran Cangakan, Karanganyar</span>
            </div>
        </div>
    </div>

    <!-- ========================================================
         RIGHT SIDE: ULTRA-SLEEK PROFESSIONAL AUTH FORM
         ======================================================== -->
    <div class="lg:col-span-6 xl:col-span-5 flex flex-col justify-between min-h-screen p-5 sm:p-8 lg:p-12 relative z-10">

        <!-- Top Security Badge Header -->
        <div class="flex items-center justify-between pb-4">
            <!-- Mobile Brand Indicator (Only visible on < lg) -->
            <div class="flex items-center gap-2.5 lg:hidden">
                <div class="w-9 h-9 rounded-xl bg-white shadow-xs p-1.5 border border-slate-200 flex items-center justify-center">
                    <img src="{{ asset('images/bps-logo.png') }}" alt="Logo BPS" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-900 leading-tight block">BPS KARANGANYAR</span>
                    <span class="text-[10px] text-blue-800 font-semibold tracking-wider block uppercase">SIM-MAGANG</span>
                </div>
            </div>

            <div class="ml-auto inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                <i data-lucide="lock" class="w-3 h-3 text-emerald-600"></i>
                <span>SSL 256-Bit Terenkripsi</span>
            </div>
        </div>

        <!-- Center Login Card -->
        <div class="w-full max-w-md mx-auto my-auto py-6 space-y-6">

            <!-- Greetings & Title -->
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-tight">
                    Masuk ke Akun
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed">
                    Silakan masukkan alamat email dan kata sandi Anda untuk mengakses portal presensi SIM-MAGANG.
                </p>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-6 sm:p-8">
                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf
                    <!-- Device Binding Identification (Anti-Titip Absen) -->
                    <input type="hidden" name="device_id" id="device_id">
                    <input type="hidden" name="device_name" id="device_name">

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                placeholder="nama@bps.go.id / email@kampus.ac.id"
                                class="block w-full h-11 pl-10 pr-3 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-800/20 focus:border-blue-800 transition outline-none text-slate-800 bg-slate-50/40 focus:bg-white font-medium">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input :type="showPass ? 'text' : 'password'" name="password" id="password" required
                                placeholder="Masukkan kata sandi akun Anda"
                                class="block w-full h-11 pl-10 pr-11 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-800/20 focus:border-blue-800 transition outline-none text-slate-800 bg-slate-50/40 focus:bg-white font-medium">
                            <button type="button" @click="showPass = !showPass; $nextTick(() => lucide.createIcons())"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 transition focus:outline-none"
                                tabindex="-1" aria-label="Tampilkan sandi">
                                <i :data-lucide="showPass ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Checkbox & Support Note -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center text-slate-600 cursor-pointer select-none group">
                            <input type="checkbox" name="remember" checked class="w-4 h-4 text-blue-800 border-slate-300 rounded focus:ring-blue-800 transition">
                            <span class="ml-2 font-medium text-slate-700 group-hover:text-slate-900">Ingat sesi login</span>
                        </label>
                        <span class="text-[11px] text-slate-400">Karanganyar 57712</span>
                    </div>

                    <!-- Main Submit Button -->
                    <button type="submit"
                        class="group w-full h-11 px-5 rounded-xl text-xs sm:text-sm font-semibold text-white bg-blue-800 hover:bg-blue-900 active:scale-[0.99] transition shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                        <span>Masuk ke Portal SIM-MAGANG</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </form>
            </div>

            <!-- Footer Location & Security Note -->
            <div class="text-center text-xs text-slate-400 space-y-1">
                <p class="flex items-center justify-center gap-1.5 text-slate-500 font-medium text-[11px]">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Komplek Perkantoran Cangakan, Jl. Majapahit No. 11 B, Karanganyar</span>
                </p>
                <p class="text-[10px] text-slate-400 leading-tight">
                    Presensi wajib divalidasi pada koordinat GPS kantor BPS. Hubungi Seksi IPDS jika ada kendala login.
                </p>
            </div>

        </div>

        <!-- Footer Bottom Copyright -->
        <div class="text-center pt-3 text-[11px] text-slate-400">
            &copy; {{ date('Y') }} Badan Pusat Statistik Kabupaten Karanganyar • SIM-MAGANG Terpadu
        </div>

    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.lucide) {
            lucide.createIcons();
        }

        // Device Binding Fingerprint Generator
        let deviceKey = 'bps_sim_magang_device_uuid';
        let deviceId = localStorage.getItem(deviceKey);
        if (!deviceId) {
            deviceId = 'dev_' + Math.random().toString(36).substring(2, 10) + '_' + Date.now().toString(36);
            localStorage.setItem(deviceKey, deviceId);
        }
        
        const deviceIdInput = document.getElementById('device_id');
        const deviceNameInput = document.getElementById('device_name');
        
        if (deviceIdInput) deviceIdInput.value = deviceId;

        // Identifikasi Tipe Perangkat
        let ua = navigator.userAgent;
        let deviceName = 'Browser Web Standar';
        if (/Android/i.test(ua)) {
            deviceName = 'Smartphone Android';
        } else if (/iPhone/i.test(ua)) {
            deviceName = 'Apple iPhone';
        } else if (/iPad/i.test(ua)) {
            deviceName = 'Apple iPad';
        } else if (/Windows NT/i.test(ua)) {
            deviceName = 'Komputer Windows';
        } else if (/Macintosh/i.test(ua)) {
            deviceName = 'Apple Mac';
        } else if (/Linux/i.test(ua)) {
            deviceName = 'Linux Device';
        }

        if (deviceNameInput) deviceNameInput.value = deviceName;
    });
</script>
@endpush
@endsection