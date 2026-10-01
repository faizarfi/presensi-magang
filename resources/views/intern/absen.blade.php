@extends('layouts.app')

@section('title', 'Presensi Harian & Logbook - SIM-MAGANG BPS')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Breadcrumb & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('intern.dashboard') }}" class="hover:text-blue-800 flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Beranda</span>
                </a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Presensi Harian</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Presensi Harian & Jurnal Logbook</h1>
            <p class="text-xs text-slate-500 mt-0.5">Badan Pusat Statistik Kabupaten Karanganyar • Jam Kerja {{ $workStartTime }} - {{ $workEndTime }} WIB</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="px-3.5 py-1.5 rounded-lg bg-white border border-slate-200 shadow-2xs text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Tanggal Presensi</span>
                <span class="text-xs font-bold text-slate-800 font-mono">{{ $today->translatedFormat('l, d F Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Banner Hari Libur / Tanggal Merah -->
    @if($dayStatus['is_off'])
        <div class="p-4 rounded-xl bg-purple-50/70 border border-purple-200 text-purple-900 flex items-start space-x-3 text-xs">
            <i data-lucide="coffee" class="w-5 h-5 text-purple-600 shrink-0 mt-0.5"></i>
            <div>
                <h3 class="font-bold text-sm text-purple-950">{{ $dayStatus['name'] }}</h3>
                <p class="mt-0.5 text-purple-800 leading-relaxed">
                    Hari ini adalah hari libur resmi (tidak ada kewajiban presensi reguler). Jika Anda sedang menjalankan tugas kedinasan khusus dari pembimbing BPS, Anda tetap dipersilakan mengisi laporan presensi di bawah ini.
                </p>
            </div>
        </div>
    @endif

    @if($existing)
        <!-- ====================================================
             KONDISI 1: SUDAH ABSEN MASUK HARI INI
             ==================================================== -->
        @if($existing->status === 'hadir' && !$existing->check_out_time)
            <!-- A. Hadir Kantor & Belum Presensi Pulang (CHECK-OUT FORM) -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-xs border border-blue-200 space-y-6"
                 x-data="checkoutForm()" x-init="initCheckoutMap()">
                
                <!-- Status Datang Banner -->
                <div class="p-4 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50/70 border border-blue-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-800 text-white flex items-center justify-center font-bold">
                            <i data-lucide="log-in" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-blue-800 block">Presensi Masuk Berhasil</span>
                            <span class="text-base font-extrabold text-slate-900 font-mono">{{ substr($existing->check_in_time, 0, 5) }} WIB</span>
                            <span class="text-xs text-slate-500 ml-1">({{ $existing->date->translatedFormat('d M Y') }})</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if($existing->is_late)
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 inline-flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>Terlambat {{ $existing->late_minutes }} Menit</span>
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                <span>Tepat Waktu</span>
                            </span>
                        @endif

                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold border {{ $existing->approval_badge_class }}">
                            {{ $existing->approval_status === 'approved' ? 'ACC Disetujui' : ($existing->approval_status === 'rejected' ? 'Ditolak' : 'Menunggu ACC') }}
                        </span>
                    </div>
                </div>

                <!-- Judul & Instruksi Pulang -->
                <div class="pb-2 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-900">Formulir Presensi Pulang (Check-Out)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Jam operasional pulang kantor BPS Karanganyar: <strong>{{ $workEndTime }} WIB</strong>. Pastikan Anda berada di lingkungan kantor saat menekan tombol presensi pulang.
                    </p>
                </div>

                <!-- Form Presensi Pulang -->
                <form id="checkoutSubmitForm" action="{{ route('intern.absen.checkout') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="latitude" :value="latitude">
                    <input type="hidden" name="longitude" :value="longitude">
                    <input type="hidden" name="photo_data" :value="photoData">

                    <!-- GPS Check-out Validation -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">1. Validasi Posisi GPS Kantor BPS</span>
                                <span class="text-[11px] text-slate-500">Radius maksimal {{ $officeConfig['radius'] }} meter dari titik kantor</span>
                            </div>
                            <button type="button" @click="detectLocation()" :disabled="detecting"
                                    class="h-9 px-4 rounded-lg bg-blue-800 hover:bg-blue-900 text-white text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5">
                                <i data-lucide="crosshair" class="w-3.5 h-3.5" :class="detecting ? 'animate-spin' : ''"></i>
                                <span x-text="detecting ? 'Mendeteksi...' : 'Deteksi GPS Pulang'"></span>
                            </button>
                        </div>

                        <!-- Status Banner GPS Pulang -->
                        <div class="p-3 rounded-lg border text-xs transition"
                             :class="geoStatus.isInside ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : (geoStatus.detected ? 'bg-rose-50 border-rose-300 text-rose-900' : 'bg-white border-slate-200 text-slate-600')">
                            <div class="font-bold" x-text="geoStatus.title"></div>
                            <div class="mt-0.5 text-xs" x-text="geoStatus.message"></div>
                        </div>

                        <!-- Mini Map Container for Checkout -->
                        <div class="rounded-lg overflow-hidden border border-slate-200 relative">
                            <div id="checkoutMap" class="w-full h-52 z-10"
                                 data-office-lat="{{ $officeConfig['latitude'] }}"
                                 data-office-lng="{{ $officeConfig['longitude'] }}"
                                 data-office-radius="{{ $officeConfig['radius'] }}"></div>
                        </div>
                    </div>

                    <!-- Selfie Kamera Check-out -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">2. Foto Selfie Pulang di Lingkungan BPS (Bukti Kehadiran)</span>
                            <span class="text-[11px] text-slate-500">Ambil foto selfie langsung via kamera HP/Webcam untuk verifikasi fisik</span>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <!-- Preview Box -->
                            <div class="w-40 h-40 rounded-xl border-2 border-dashed border-slate-300 bg-white flex items-center justify-center overflow-hidden shrink-0 relative">
                                <template x-if="photoData">
                                    <img :src="photoData" alt="Selfie Pulang" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!photoData">
                                    <div class="text-center p-3 text-slate-400">
                                        <i data-lucide="camera" class="w-8 h-8 mx-auto mb-1 opacity-50"></i>
                                        <span class="text-[10px] block">Belum ada foto</span>
                                    </div>
                                </template>
                            </div>

                            <div class="space-y-2 flex-1 w-full">
                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-700 block mb-1">Ambil Foto Langsung dari Kamera</span>
                                    <input type="file" name="photo" accept="image/*" capture="user"
                                           @change="handlePhotoUpload($event)"
                                           class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-800 hover:file:bg-blue-100 border border-slate-300 rounded-lg">
                                </label>
                                <p class="text-[11px] text-slate-400">Gunakan kamera depan smartphone Anda untuk hasil selfie yang jernih.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Check-out -->
                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <a href="{{ route('intern.dashboard') }}" class="w-full sm:w-auto h-10 px-5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-xs inline-flex items-center justify-center">
                            Kembali ke Beranda
                        </a>

                        <button type="button" @click="submitCheckout()" :disabled="!canSubmitCheckout()"
                                class="w-full sm:w-auto h-10 px-6 rounded-lg font-semibold text-xs text-white transition inline-flex items-center justify-center gap-2 shadow-2xs"
                                :class="canSubmitCheckout() ? 'bg-blue-800 hover:bg-blue-900 cursor-pointer' : 'bg-slate-300 cursor-not-allowed'">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span>Kirim Presensi Pulang (Check-Out)</span>
                        </button>
                    </div>
                </form>
            </div>

        @else
            <!-- B. Kartu Rekap Lengkap Presensi Hari Ini (Sudah Pulang atau Izin/Sakit) -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-xs border border-slate-200 text-center space-y-5">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 mx-auto flex items-center justify-center shadow-2xs">
                    <i data-lucide="check-check" class="w-7 h-7"></i>
                </div>

                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border mb-2 {{ $existing->status_badge_class }}">
                        {{ $existing->status_label }} ({{ $existing->sub_status_label }})
                    </span>
                    <h2 class="text-xl font-bold text-slate-900">Presensi Hari Ini Telah Lengkap</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Seluruh laporan kehadiran Anda untuk hari ini telah tercatat di server BPS Karanganyar.</p>
                </div>

                <!-- Info Grid: Masuk, Pulang, Durasi -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-lg mx-auto text-left">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Jam Masuk</span>
                        <span class="text-sm font-bold text-slate-900 font-mono">{{ $existing->check_in_time ? substr($existing->check_in_time, 0, 5) . ' WIB' : '-' }}</span>
                        @if($existing->is_late)
                            <span class="text-[10px] text-amber-700 font-bold block mt-0.5">Terlambat ({{ $existing->late_minutes }}m)</span>
                        @endif
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Jam Pulang</span>
                        <span class="text-sm font-bold text-slate-900 font-mono">{{ $existing->check_out_time ? substr($existing->check_out_time, 0, 5) . ' WIB' : '-' }}</span>
                        <span class="text-[10px] text-emerald-700 font-medium block mt-0.5">Tercatat di BPS</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Durasi Kerja</span>
                        <span class="text-sm font-bold text-blue-900 font-mono">{{ $existing->work_duration_formatted }}</span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Jam Efektif</span>
                    </div>
                </div>

                <!-- Foto Selfie Thumbnails -->
                @if($existing->photo || $existing->check_out_photo)
                <div class="flex justify-center items-center gap-4 pt-2">
                    @if($existing->photo)
                    <div class="text-center">
                        <a href="{{ asset('storage/' . $existing->photo) }}" target="_blank" class="block w-20 h-20 rounded-xl overflow-hidden border border-slate-200 shadow-2xs hover:opacity-90 transition">
                            <img src="{{ asset('storage/' . $existing->photo) }}" alt="Selfie Masuk" class="w-full h-full object-cover">
                        </a>
                        <span class="text-[10px] text-slate-500 font-medium mt-1 block">Foto Masuk</span>
                    </div>
                    @endif

                    @if($existing->check_out_photo)
                    <div class="text-center">
                        <a href="{{ asset('storage/' . $existing->check_out_photo) }}" target="_blank" class="block w-20 h-20 rounded-xl overflow-hidden border border-slate-200 shadow-2xs hover:opacity-90 transition">
                            <img src="{{ asset('storage/' . $existing->check_out_photo) }}" alt="Selfie Pulang" class="w-full h-full object-cover">
                        </a>
                        <span class="text-[10px] text-slate-500 font-medium mt-1 block">Foto Pulang</span>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Status ACC Info Box -->
                <div class="max-w-md mx-auto p-4 rounded-xl bg-slate-50 border border-slate-200 text-left text-xs space-y-2.5">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                        <span class="text-slate-500 font-medium">Status Verifikasi Pembimbing:</span>
                        <span class="px-2.5 py-0.5 rounded-full font-bold border {{ $existing->approval_badge_class }}">
                            {{ $existing->approval_status === 'approved' ? 'Disetujui (ACC)' : ($existing->approval_status === 'rejected' ? 'Ditolak' : 'Menunggu ACC') }}
                        </span>
                    </div>

                    @if($existing->approval_notes)
                        <div class="p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900">
                            <span class="font-bold block mb-0.5">Catatan Pembimbing:</span>
                            <p>{{ $existing->approval_notes }}</p>
                        </div>
                    @endif

                    <div class="pt-1">
                        <span class="font-semibold text-slate-700 block">Uraian Aktivitas:</span>
                        <p class="text-slate-600 mt-0.5 leading-relaxed">{{ $existing->activity_description }}</p>
                    </div>
                </div>

                <div class="pt-3 flex justify-center space-x-3">
                    <a href="{{ route('intern.dashboard') }}" class="h-10 px-4 text-xs font-semibold rounded-lg bg-blue-800 hover:bg-blue-900 text-white shadow-2xs inline-flex items-center gap-1.5 transition">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Kembali ke Beranda</span>
                    </a>
                    <a href="{{ route('intern.riwayat') }}" class="h-10 px-4 text-xs font-medium rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 shadow-2xs inline-flex items-center gap-1.5 transition">
                        <i data-lucide="history" class="w-4 h-4 text-slate-500"></i>
                        <span>Lihat Riwayat & Jurnal</span>
                    </a>
                </div>
            </div>
        @endif

    @else
        <!-- ====================================================
             KONDISI 2: BELUM ABSEN HARI INI (FORM PRESENSI MASUK)
             ==================================================== -->
        <form id="attendanceSubmitForm" action="{{ route('intern.absen.store') }}" method="POST" enctype="multipart/form-data"
              x-data="attendanceForm()" x-init="initMap()" class="space-y-6">
            @csrf

            <!-- Peringatan Keterlambatan Jam Masuk jika saat ini > 07:30 -->
            @if($isLateCurrent)
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-300 text-amber-950 space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-xs text-amber-900">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0"></i>
                    <span>Peringatan Keterlambatan Masuk Kantor BPS</span>
                </div>
                <p class="text-xs text-amber-800 leading-relaxed">
                    Waktu saat ini telah melewati jam operasional masuk kantor BPS Karanganyar (<strong>{{ $workStartTime }} WIB</strong>). Presensi Anda akan otomatis tercatat <strong>Terlambat</strong>. Silakan lampirkan alasan keterlambatan pada form di bawah.
                </p>
            </div>
            @endif

            <!-- STEP 1: Pilih Status Kehadiran (3 Opsi) -->
            <div class="bg-white rounded-xl p-5 sm:p-6 shadow-xs border border-slate-200">
                <div class="flex items-center space-x-3 mb-4 pb-3 border-b border-slate-100">
                    <div class="w-7 h-7 rounded-md bg-blue-50 text-blue-800 flex items-center justify-center font-bold text-xs">1</div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900">Pilih Status Kehadiran Hari Ini</h2>
                        <p class="text-xs text-slate-500">Pilih salah satu kondisi kehadiran Anda di BPS Karanganyar</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Opsi 1: Hadir di Kantor BPS -->
                    <label class="relative flex flex-col p-3.5 rounded-lg border cursor-pointer transition"
                           :class="status === 'hadir' ? 'border-blue-700 bg-blue-50/50 shadow-xs ring-1 ring-blue-700' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <input type="radio" name="status" value="hadir" x-model="status" @change="onStatusChange()" class="sr-only">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="w-7 h-7 rounded-md bg-blue-100 text-blue-800 flex items-center justify-center">
                                <i data-lucide="building-2" class="w-4 h-4"></i>
                            </div>
                            <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                  :class="status === 'hadir' ? 'border-blue-700 bg-blue-700' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="status === 'hadir'"></span>
                            </span>
                        </div>
                        <div class="text-xs font-bold text-slate-900">1. Hadir di Kantor BPS</div>
                        <div class="text-[11px] text-slate-500 mt-0.5 leading-snug">Wajib verifikasi GPS radius 150m & foto selfie.</div>
                    </label>

                    <!-- Opsi 2: Tidak Hadir dengan Keterangan -->
                    <label class="relative flex flex-col p-3.5 rounded-lg border cursor-pointer transition"
                           :class="status === 'tidak_hadir_keterangan' ? 'border-blue-700 bg-blue-50/50 shadow-xs ring-1 ring-blue-700' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <input type="radio" name="status" value="tidak_hadir_keterangan" x-model="status" @change="onStatusChange()" class="sr-only">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="w-7 h-7 rounded-md bg-amber-100 text-amber-800 flex items-center justify-center">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                            </div>
                            <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                  :class="status === 'tidak_hadir_keterangan' ? 'border-blue-700 bg-blue-700' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="status === 'tidak_hadir_keterangan'"></span>
                            </span>
                        </div>
                        <div class="text-xs font-bold text-slate-900">2. Tidak Hadir (Izin/Sakit)</div>
                        <div class="text-[11px] text-slate-500 mt-0.5 leading-snug">Sakit, Izin Kepentingan, atau Tugas Luar. Lokasi bebas.</div>
                    </label>

                    <!-- Opsi 3: Tidak Hadir tanpa Keterangan -->
                    <label class="relative flex flex-col p-3.5 rounded-lg border cursor-pointer transition"
                           :class="status === 'tidak_hadir_tanpa_keterangan' ? 'border-blue-700 bg-blue-50/50 shadow-xs ring-1 ring-blue-700' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <input type="radio" name="status" value="tidak_hadir_tanpa_keterangan" x-model="status" @change="onStatusChange()" class="sr-only">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="w-7 h-7 rounded-md bg-rose-100 text-rose-700 flex items-center justify-center">
                                <i data-lucide="user-x" class="w-4 h-4"></i>
                            </div>
                            <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                  :class="status === 'tidak_hadir_tanpa_keterangan' ? 'border-blue-700 bg-blue-700' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="status === 'tidak_hadir_tanpa_keterangan'"></span>
                            </span>
                        </div>
                        <div class="text-xs font-bold text-slate-900">3. Tanpa Keterangan (Alpa)</div>
                        <div class="text-[11px] text-slate-500 mt-0.5 leading-snug">Tercatat Alpa / otomatis jika lewat jam 12 malam.</div>
                    </label>
                </div>
            </div>

            <!-- STEP 2: Khusus HADIR -> Validasi Geolocation & Peta Leaflet & Foto Selfie -->
            <div x-show="status === 'hadir'" x-cloak class="bg-white rounded-xl p-5 sm:p-6 shadow-xs border border-slate-200 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-7 h-7 rounded-md bg-blue-50 text-blue-800 flex items-center justify-center font-bold text-xs">2</div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900">Validasi Posisi GPS & Foto Selfie Masuk</h2>
                            <p class="text-xs text-slate-500">Wajib berada dalam radius maksimal {{ $officeConfig['radius'] }} meter dari kantor BPS</p>
                        </div>
                    </div>

                    <div class="w-full sm:w-auto">
                        <button type="button" @click="detectLocation()" :disabled="detecting"
                                class="w-full sm:w-auto h-9 px-4 rounded-lg bg-blue-800 hover:bg-blue-900 text-white text-xs font-semibold transition inline-flex items-center justify-center gap-1.5 shadow-2xs">
                            <i data-lucide="crosshair" class="w-3.5 h-3.5" :class="detecting ? 'animate-spin' : ''"></i>
                            <span x-text="detecting ? 'Mendeteksi...' : 'Deteksi GPS Saya'"></span>
                        </button>
                    </div>
                </div>

                <!-- Status Deteksi Banner -->
                <div class="p-3.5 rounded-lg border text-xs transition"
                     :class="geoStatus.isInside ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : (geoStatus.detected ? 'bg-rose-50 border-rose-300 text-rose-900' : 'bg-slate-50 border-slate-200 text-slate-700')">
                    <div class="flex items-start space-x-2.5">
                        <div class="mt-0.5 flex-shrink-0">
                            <i data-lucide="map-pin" class="w-4 h-4" :class="geoStatus.isInside ? 'text-emerald-700' : (geoStatus.detected ? 'text-rose-700' : 'text-slate-400')"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-bold text-xs" x-text="geoStatus.title"></div>
                            <div class="mt-0.5 text-xs leading-relaxed" x-text="geoStatus.message"></div>
                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[11px] font-mono bg-white p-2 rounded-md border border-slate-200" x-show="geoStatus.detected">
                                <span>Lat: <strong x-text="latitude ? Number(latitude).toFixed(6) : '-'"></strong></span>
                                <span>Lng: <strong x-text="longitude ? Number(longitude).toFixed(6) : '-'"></strong></span>
                                <span>Jarak: <strong x-text="distanceMeters !== null ? distanceMeters + ' meter' : '-'"></strong></span>
                                <span>Radius BPS: <strong>{{ $officeConfig['radius'] }} m</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leaflet Interactive Map Container -->
                <div class="rounded-lg overflow-hidden border border-slate-200 relative">
                    <div id="map" class="w-full h-60 z-10"
                         data-office-lat="{{ $officeConfig['latitude'] }}"
                         data-office-lng="{{ $officeConfig['longitude'] }}"
                         data-office-radius="{{ $officeConfig['radius'] }}"
                         data-office-name="{{ $officeConfig['name'] }}"></div>
                </div>

                @if(app()->environment('local'))
                <!-- Tombol Simulasi Titik Lokasi Kantor (HANYA MUNCUL DI LOCAL DEV) -->
                <div class="p-3 bg-amber-50/70 rounded-lg border border-amber-200 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-amber-900">
                    <div class="flex items-center gap-1.5">
                        <span class="px-1.5 py-0.5 rounded bg-amber-200 text-amber-950 font-bold text-[10px]">DEV ONLY</span>
                        <span>Uji coba simulasi koordinat kantor BPS Karanganyar:</span>
                    </div>
                    <button type="button" @click="simulateAtOffice()"
                            class="h-8 px-3 rounded-md border border-amber-300 bg-white hover:bg-amber-100 text-amber-950 text-xs font-semibold whitespace-nowrap transition shadow-2xs flex items-center gap-1.5">
                        <i data-lucide="navigation" class="w-3 h-3 text-amber-700"></i>
                        <span>Simulasikan Titik Kantor</span>
                    </button>
                </div>
                @endif

                <!-- Section Selfie Kamera Masuk -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">Foto Selfie Masuk di Lingkungan BPS</span>
                        <span class="text-[11px] text-slate-500">Wajib melampirkan foto selfie kehadiran di kantor BPS</span>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="w-36 h-36 rounded-xl border-2 border-dashed border-slate-300 bg-white flex items-center justify-center overflow-hidden shrink-0 relative">
                            <template x-if="photoData">
                                <img :src="photoData" alt="Selfie Masuk" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!photoData">
                                <div class="text-center p-3 text-slate-400">
                                    <i data-lucide="camera" class="w-8 h-8 mx-auto mb-1 opacity-50"></i>
                                    <span class="text-[10px] block">Belum ada foto</span>
                                </div>
                            </template>
                        </div>

                        <div class="space-y-2 flex-1 w-full">
                            <label class="block">
                                <span class="text-xs font-semibold text-slate-700 block mb-1">Ambil Foto Langsung dari Kamera</span>
                                <input type="file" name="photo" accept="image/*" capture="user"
                                       @change="handlePhotoUpload($event)"
                                       class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-800 hover:file:bg-blue-100 border border-slate-300 rounded-lg">
                            </label>
                            <p class="text-[11px] text-slate-400">Gunakan kamera depan smartphone Anda. Format: JPG/PNG (Maks 5 MB).</p>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs for coordinates & selfie -->
                <input type="hidden" name="latitude" :value="latitude">
                <input type="hidden" name="longitude" :value="longitude">
                <input type="hidden" name="photo_data" :value="photoData">
                <input type="hidden" name="location_address" value="Komplek Perkantoran Cangakan, BPS Karanganyar">
            </div>

            <!-- STEP 2b: Khusus KETERANGAN (Sakit / Izin) -->
            <div x-show="status === 'tidak_hadir_keterangan'" x-cloak class="bg-white rounded-xl p-5 sm:p-6 shadow-xs border border-slate-200 space-y-4">
                <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                    <div class="w-7 h-7 rounded-md bg-blue-50 text-blue-800 flex items-center justify-center font-bold text-xs">2</div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900">Rincian Keterangan Tidak Hadir</h2>
                        <p class="text-xs text-slate-500">Pilih klasifikasi alasan dan lampirkan dokumen pendukung (disimpan aman di storage privat)</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="block text-xs font-semibold text-slate-700">Jenis Keterangan Tidak Hadir:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex items-center p-3 rounded-lg border cursor-pointer transition text-xs font-medium"
                               :class="subStatus === 'sakit' ? 'border-amber-500 bg-amber-50/60 text-amber-950 font-bold' : 'border-slate-200 hover:border-slate-300 text-slate-700'">
                            <input type="radio" name="sub_status" value="sakit" x-model="subStatus" class="sr-only">
                            <span class="w-3.5 h-3.5 rounded-full border mr-2 flex items-center justify-center"
                                  :class="subStatus === 'sakit' ? 'border-amber-600 bg-amber-600' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="subStatus === 'sakit'"></span>
                            </span>
                            <span>Sakit (Surat Dokter)</span>
                        </label>

                        <label class="flex items-center p-3 rounded-lg border cursor-pointer transition text-xs font-medium"
                               :class="subStatus === 'izin' ? 'border-blue-500 bg-blue-50/60 text-blue-950 font-bold' : 'border-slate-200 hover:border-slate-300 text-slate-700'">
                            <input type="radio" name="sub_status" value="izin" x-model="subStatus" class="sr-only">
                            <span class="w-3.5 h-3.5 rounded-full border mr-2 flex items-center justify-center"
                                  :class="subStatus === 'izin' ? 'border-blue-600 bg-blue-600' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="subStatus === 'izin'"></span>
                            </span>
                            <span>Izin Kepentingan</span>
                        </label>

                        <label class="flex items-center p-3 rounded-lg border cursor-pointer transition text-xs font-medium"
                               :class="subStatus === 'dinas_luar' ? 'border-indigo-500 bg-indigo-50/60 text-indigo-950 font-bold' : 'border-slate-200 hover:border-slate-300 text-slate-700'">
                            <input type="radio" name="sub_status" value="dinas_luar" x-model="subStatus" class="sr-only">
                            <span class="w-3.5 h-3.5 rounded-full border mr-2 flex items-center justify-center"
                                  :class="subStatus === 'dinas_luar' ? 'border-indigo-600 bg-indigo-600' : 'border-slate-300'">
                                <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="subStatus === 'dinas_luar'"></span>
                            </span>
                            <span>Tugas / Dinas Luar</span>
                        </label>
                    </div>
                </div>

                <!-- Unggah Berkas Bukti (Surat Sakit / Dispensasi) -->
                <div class="pt-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Lampirkan Dokumen Pendukung (Surat Dokter / Bukti Izin) <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-lg hover:border-blue-600 transition bg-slate-50/50">
                        <div class="space-y-1 text-center">
                            <i data-lucide="upload-cloud" class="mx-auto h-8 w-8 text-slate-400"></i>
                            <div class="flex text-xs text-slate-600 justify-center">
                                <label for="attachment_file" class="relative cursor-pointer rounded-md font-semibold text-blue-800 hover:text-blue-900 focus-within:outline-none">
                                    <span>Pilih Berkas Dokumen</span>
                                    <input id="attachment_file" name="attachment_file" type="file" accept=".pdf,.jpg,.jpeg,.png"
                                           @change="handleAttachmentChange($event)" class="sr-only">
                                </label>
                            </div>
                            <p class="text-[11px] text-slate-500">Format: PDF, JPG, PNG hingga 5 MB (Tersimpan Aman di Storage Privat BPS)</p>
                            <div x-show="attachmentFileName" class="mt-2 text-xs font-bold text-blue-900 bg-blue-100/70 py-1 px-3 rounded-full inline-block">
                                <span x-text="attachmentFileName"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Jurnal Logbook Harian -->
            <div class="bg-white rounded-xl p-5 sm:p-6 shadow-xs border border-slate-200 space-y-4">
                <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                    <div class="w-7 h-7 rounded-md bg-blue-50 text-blue-800 flex items-center justify-center font-bold text-xs">3</div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900">Jurnal Logbook Aktivitas & Pembelajaran</h2>
                        <p class="text-xs text-slate-500">Dokumentasikan aktivitas harian Anda selama bertugas di BPS Karanganyar</p>
                    </div>
                </div>

                <!-- Alasan Terlambat (Jika Saat Ini Melewati Jam 07:30) -->
                @if($isLateCurrent)
                <div class="p-3.5 rounded-lg bg-amber-50 border border-amber-300 text-amber-950 space-y-1.5">
                    <label for="late_reason" class="block text-xs font-bold text-amber-900">
                        Alasan / Keterangan Keterlambatan Masuk: <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" name="late_reason" id="late_reason" required
                           placeholder="Contoh: Terjebak macet/hujan lebat, urusan administrasi kampus mendesak..."
                           class="w-full text-xs h-9 px-3 rounded-lg border border-amber-300 focus:ring-2 focus:ring-amber-500 outline-none bg-white">
                </div>
                @endif

                <!-- Uraian Aktivitas -->
                <div>
                    <label for="activity_description" class="block text-xs font-semibold text-slate-700 mb-1">
                        1. Uraian Aktivitas yang Dikerjakan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="activity_description" id="activity_description" rows="3" required
                              placeholder="Deskripsikan rincian pekerjaan atau kegiatan yang Anda lakukan hari ini ..."
                              class="w-full text-xs sm:text-sm p-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none transition leading-relaxed">{{ old('activity_description') }}</textarea>
                </div>

                <!-- Pembelajaran yang Diperoleh -->
                <div x-show="status === 'hadir'">
                    <label for="learning_obtained" class="block text-xs font-semibold text-slate-700 mb-1">
                        2. Pembelajaran / Kompetensi yang Diperoleh <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="learning_obtained" id="learning_obtained" rows="2"
                              :required="status === 'hadir'"
                              placeholder="Keterampilan statistik, tools, atau wawasan baru yang Anda pelajari ..."
                              class="w-full text-xs sm:text-sm p-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none transition leading-relaxed">{{ old('learning_obtained') }}</textarea>
                </div>

                <!-- Kendala yang Dihadapi -->
                <div x-show="status === 'hadir'">
                    <label for="constraints_encountered" class="block text-xs font-semibold text-slate-700 mb-1">
                        3. Kendala yang Dihadapi & Solusinya <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="constraints_encountered" id="constraints_encountered" rows="2"
                              :required="status === 'hadir'"
                              placeholder="Hambatan yang Anda temui selama bertugas di BPS (apabila lancar, isikan tanda '-') ..."
                              class="w-full text-xs sm:text-sm p-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none transition leading-relaxed">{{ old('constraints_encountered', '-') }}</textarea>
                </div>
            </div>

            <!-- STEP 4: Checkbox Konfirmasi Integritas Data -->
            <div class="bg-slate-50 rounded-xl p-4 sm:p-5 border border-slate-200 space-y-2">
                <div class="flex items-start space-x-3">
                    <input type="checkbox" name="is_confirmed" id="is_confirmed" value="1" x-model="isConfirmed"
                           class="w-4 h-4 mt-0.5 text-blue-800 rounded border-slate-300 focus:ring-blue-800 cursor-pointer shrink-0" required>
                    <label for="is_confirmed" class="text-xs sm:text-sm text-slate-700 cursor-pointer select-none leading-relaxed font-medium">
                        Saya menyatakan dengan sebenarnya bahwa seluruh uraian aktivitas, waktu kehadiran, dan laporan logbook ini adalah sah sesuai penugasan di Badan Pusat Statistik Kabupaten Karanganyar dan siap dipertanggungjawabkan kepada Pembimbing BPS.
                    </label>
                </div>
            </div>

            <!-- Submit Button & Controls with SweetAlert Confirmation -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                <a href="{{ route('intern.dashboard') }}" class="w-full sm:w-auto h-10 px-5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-xs sm:text-sm inline-flex items-center justify-center transition">
                    Batal
                </a>

                <button type="button"
                        @click="handleFormSubmit()"
                        :disabled="!canSubmit()"
                        class="w-full sm:w-auto h-10 px-6 rounded-lg font-semibold text-xs sm:text-sm text-white transition inline-flex items-center justify-center gap-2 shadow-2xs"
                        :class="canSubmit() ? 'bg-blue-800 hover:bg-blue-900 cursor-pointer' : 'bg-slate-300 cursor-not-allowed'">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Kirim Laporan Presensi Masuk</span>
                </button>
            </div>
        </form>
    @endif

</div>

@push('scripts')
<script>
    // FORM PRESENSI MASUK
    function attendanceForm() {
        const mapEl = document.getElementById('map');
        const officeLat = mapEl ? parseFloat(mapEl.getAttribute('data-office-lat') || '-7.5978') : -7.5978;
        const officeLng = mapEl ? parseFloat(mapEl.getAttribute('data-office-lng') || '110.9505') : 110.9505;
        const officeRadius = mapEl ? parseFloat(mapEl.getAttribute('data-office-radius') || '150') : 150;
        const officeName = mapEl ? (mapEl.getAttribute('data-office-name') || 'BPS Kabupaten Karanganyar') : 'BPS Kabupaten Karanganyar';

        return {
            status: 'hadir',
            subStatus: 'sakit',
            attachmentFileName: '',
            isConfirmed: false,
            detecting: false,
            latitude: null,
            longitude: null,
            distanceMeters: null,
            photoData: '',
            map: null,
            userMarker: null,
            officeMarker: null,
            radiusCircle: null,
            connectLine: null,

            officeConfig: {
                lat: officeLat,
                lng: officeLng,
                radius: officeRadius,
                name: officeName
            },

            geoStatus: {
                detected: false,
                isInside: false,
                title: 'GPS Belum Terdeteksi',
                message: 'Klik tombol "Deteksi GPS Saya" di atas untuk memvalidasi posisi Anda di kantor BPS Karanganyar.'
            },

            initMap() {
                this.$nextTick(() => {
                    const mapElement = document.getElementById('map');
                    if (!mapElement) return;

                    this.map = L.map('map').setView([this.officeConfig.lat, this.officeConfig.lng], 17);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(this.map);

                    const officeIcon = L.divIcon({
                        className: 'custom-office-icon',
                        html: '<div style="background-color:#1e3a8a;color:white;width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(30,58,138,0.3);border:2px solid white;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg></div>',
                        iconSize: [36, 36],
                        iconAnchor: [18, 18]
                    });

                    this.officeMarker = L.marker([this.officeConfig.lat, this.officeConfig.lng], { icon: officeIcon })
                        .addTo(this.map)
                        .bindPopup('<strong>' + this.officeConfig.name + '</strong><br>Komplek Perkantoran Cangakan, Karanganyar')
                        .openPopup();

                    this.radiusCircle = L.circle([this.officeConfig.lat, this.officeConfig.lng], {
                        color: '#1e3a8a',
                        fillColor: '#1e3a8a',
                        fillOpacity: 0.12,
                        radius: this.officeConfig.radius
                    }).addTo(this.map);

                    this.detectLocation();
                });
            },

            detectLocation() {
                if (!navigator.geolocation) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perangkat Tidak Mendukung',
                        text: 'Peramban web Anda tidak mendukung Geolocation API.'
                    });
                    return;
                }

                this.detecting = true;
                this.geoStatus.title = 'Mencari Titik Koordinat GPS...';
                this.geoStatus.message = 'Pastikan GPS perangkat aktif dan izin lokasi telah disetujui.';

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.detecting = false;
                        this.processCoordinates(pos.coords.latitude, pos.coords.longitude);
                    },
                    (err) => {
                        this.detecting = false;
                        let msg = 'Gagal mengakses GPS. ';
                        if (err.code === 1) msg += 'Izin lokasi ditolak oleh browser. Silakan izinkan akses lokasi.';
                        else if (err.code === 2) msg += 'Posisi GPS tidak dapat ditemukan.';
                        else if (err.code === 3) msg += 'Waktu pencarian GPS habis.';
                        this.geoStatus.detected = false;
                        this.geoStatus.isInside = false;
                        this.geoStatus.title = 'Deteksi GPS Gagal';
                        this.geoStatus.message = msg;
                    },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            },

            simulateAtOffice() {
                const simLat = this.officeConfig.lat + (Math.random() - 0.5) * 0.0002;
                const simLng = this.officeConfig.lng + (Math.random() - 0.5) * 0.0002;
                this.processCoordinates(simLat, simLng);
            },

            processCoordinates(lat, lng) {
                this.latitude = lat;
                this.longitude = lng;

                const distance = this.haversine(lat, lng, this.officeConfig.lat, this.officeConfig.lng);
                this.distanceMeters = Math.round(distance);

                const isInside = this.distanceMeters <= this.officeConfig.radius;
                this.geoStatus.detected = true;
                this.geoStatus.isInside = isInside;

                if (isInside) {
                    this.geoStatus.title = 'Lokasi Valid (Di Lingkungan Kantor BPS)';
                    this.geoStatus.message = `Posisi Anda terverifikasi berjarak ${this.distanceMeters} meter dari kantor BPS Karanganyar. Laporan siap dikirim.`;
                } else {
                    this.geoStatus.title = 'Posisi di Luar Radius Kantor BPS Karanganyar';
                    this.geoStatus.message = `Jarak Anda terdeteksi ${this.distanceMeters} meter dari kantor BPS (Batas toleransi maksimal ${this.officeConfig.radius} meter).`;
                }

                if (this.map) {
                    if (this.userMarker) this.map.removeLayer(this.userMarker);
                    if (this.connectLine) this.map.removeLayer(this.connectLine);

                    const userColor = isInside ? '#10b981' : '#ef4444';
                    const userIcon = L.divIcon({
                        className: 'custom-user-icon',
                        html: `<div style="background-color:${userColor};color:white;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(0,0,0,0.25);border:2px solid white;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 16]
                    });

                    this.userMarker = L.marker([lat, lng], { icon: userIcon })
                        .addTo(this.map)
                        .bindPopup(`<strong>Posisi Anda</strong><br>Jarak: ${this.distanceMeters}m`);

                    this.connectLine = L.polyline([
                        [this.officeConfig.lat, this.officeConfig.lng],
                        [lat, lng]
                    ], { color: userColor, dashArray: '6, 8', weight: 2 }).addTo(this.map);

                    this.map.fitBounds([[this.officeConfig.lat, this.officeConfig.lng], [lat, lng]], { padding: [40, 40] });
                }
            },

            handlePhotoUpload(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.photoData = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },

            handleAttachmentChange(event) {
                const file = event.target.files[0];
                if (file) {
                    this.attachmentFileName = file.name;
                }
            },

            haversine(lat1, lon1, lat2, lon2) {
                const R = 6371000;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                          Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                          Math.sin(dLon/2) * Math.sin(dLon/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            },

            onStatusChange() {
                if (this.status === 'hadir' && this.map) {
                    setTimeout(() => { this.map.invalidateSize(); }, 200);
                }
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            },

            canSubmit() {
                if (!this.isConfirmed) return false;
                if (this.status === 'hadir') {
                    return this.geoStatus.detected && this.geoStatus.isInside;
                }
                return true;
            },

            handleFormSubmit() {
                if (!this.canSubmit()) {
                    if (this.status === 'hadir' && (!this.geoStatus.detected || !this.geoStatus.isInside)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Posisi Belum Memenuhi Syarat',
                            text: 'Presensi Hadir mewajibkan posisi Anda terverifikasi di kantor BPS Karanganyar.',
                            confirmButtonColor: '#1e3a8a'
                        });
                        return;
                    }
                    if (!this.isConfirmed) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Pernyataan Belum Dicentang',
                            text: 'Harap centang pernyataan kebenaran data pada formulir.',
                            confirmButtonColor: '#1e3a8a'
                        });
                        return;
                    }
                }

                Swal.fire({
                    title: 'Konfirmasi Kirim Presensi',
                    text: 'Apakah seluruh uraian aktivitas dan posisi Anda sudah sesuai?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1e3a8a',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Kirim Presensi Masuk',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        document.getElementById('attendanceSubmitForm').submit();
                    }
                });
            }
        }
    }

    // FORM PRESENSI PULANG (CHECK-OUT)
    function checkoutForm() {
        const mapEl = document.getElementById('checkoutMap');
        const officeLat = mapEl ? parseFloat(mapEl.getAttribute('data-office-lat') || '-7.5978') : -7.5978;
        const officeLng = mapEl ? parseFloat(mapEl.getAttribute('data-office-lng') || '110.9505') : 110.9505;
        const officeRadius = mapEl ? parseFloat(mapEl.getAttribute('data-office-radius') || '150') : 150;

        return {
            detecting: false,
            latitude: null,
            longitude: null,
            distanceMeters: null,
            photoData: '',
            map: null,
            userMarker: null,

            officeConfig: { lat: officeLat, lng: officeLng, radius: officeRadius },

            geoStatus: {
                detected: false,
                isInside: false,
                title: 'GPS Pulang Belum Terdeteksi',
                message: 'Klik "Deteksi GPS Pulang" untuk memvalidasi posisi Anda di kantor BPS.'
            },

            initCheckoutMap() {
                this.$nextTick(() => {
                    const el = document.getElementById('checkoutMap');
                    if (!el) return;

                    this.map = L.map('checkoutMap').setView([this.officeConfig.lat, this.officeConfig.lng], 17);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(this.map);

                    L.circle([this.officeConfig.lat, this.officeConfig.lng], {
                        color: '#1e3a8a',
                        fillColor: '#1e3a8a',
                        fillOpacity: 0.12,
                        radius: this.officeConfig.radius
                    }).addTo(this.map);

                    this.detectLocation();
                });
            },

            detectLocation() {
                if (!navigator.geolocation) {
                    Swal.fire({ icon: 'warning', title: 'Peramban Tidak Mendukung Geolocation' });
                    return;
                }

                this.detecting = true;
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.detecting = false;
                        this.latitude = pos.coords.latitude;
                        this.longitude = pos.coords.longitude;

                        const R = 6371000;
                        const dLat = (this.officeConfig.lat - this.latitude) * Math.PI / 180;
                        const dLon = (this.officeConfig.lng - this.longitude) * Math.PI / 180;
                        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                                  Math.cos(this.latitude * Math.PI / 180) * Math.cos(this.officeConfig.lat * Math.PI / 180) *
                                  Math.sin(dLon/2) * Math.sin(dLon/2);
                        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                        this.distanceMeters = Math.round(R * c);

                        this.geoStatus.detected = true;
                        this.geoStatus.isInside = this.distanceMeters <= this.officeConfig.radius;

                        if (this.geoStatus.isInside) {
                            this.geoStatus.title = 'Lokasi Valid di Kantor BPS';
                            this.geoStatus.message = `Jarak Anda ${this.distanceMeters}m dari kantor BPS. Presensi pulang siap dikirim.`;
                        } else {
                            this.geoStatus.title = 'Di Luar Radius Kantor BPS';
                            this.geoStatus.message = `Jarak terdeteksi ${this.distanceMeters}m (Maksimal ${this.officeConfig.radius}m).`;
                        }

                        if (this.map) {
                            if (this.userMarker) this.map.removeLayer(this.userMarker);
                            this.userMarker = L.marker([this.latitude, this.longitude]).addTo(this.map);
                            this.map.panTo([this.latitude, this.longitude]);
                        }
                    },
                    (err) => {
                        this.detecting = false;
                        this.geoStatus.title = 'Gagal Mendeteksi Lokasi';
                        this.geoStatus.message = 'Pastikan GPS aktif dan izin lokasi telah disetujui.';
                    },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            },

            handlePhotoUpload(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => { this.photoData = e.target.result; };
                    reader.readAsDataURL(file);
                }
            },

            canSubmitCheckout() {
                return this.geoStatus.detected && this.geoStatus.isInside;
            },

            submitCheckout() {
                if (!this.canSubmitCheckout()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Lokasi Belum Terverifikasi',
                        text: 'Presensi pulang wajib dilakukan di area kantor BPS Karanganyar.',
                        confirmButtonColor: '#1e3a8a'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Presensi Pulang',
                    text: 'Apakah Anda yakin ingin mengakhiri jam kerja dan mencatat presensi pulang sekarang?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1e3a8a',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Presensi Pulang',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        document.getElementById('checkoutSubmitForm').submit();
                    }
                });
            }
        }
    }
</script>
@endpush
@endsection
