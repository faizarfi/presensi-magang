@extends('layouts.app')

@section('title', 'Beranda Peserta Magang - SIM-MAGANG BPS Karanganyar')

@section('content')
<div class="space-y-5 sm:space-y-6">

    <!-- Executive Profile & Welcome Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 sm:gap-5">
            <div class="flex items-start sm:items-center space-x-3 sm:space-x-4">
                <div class="w-13 h-13 sm:w-16 sm:h-16 rounded-full bg-blue-100 text-blue-900 border border-blue-200 flex items-center justify-center text-lg sm:text-xl font-bold flex-shrink-0 overflow-hidden shadow-2xs">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <h1 class="text-lg sm:text-2xl font-bold text-slate-900 tracking-tight leading-tight">{{ $user->name }}</h1>
                        <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-blue-50 text-blue-800 border border-blue-200">
                            Peserta Magang
                        </span>
                    </div>
                    <div class="text-xs text-slate-600 mt-1 flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <span class="font-medium text-slate-800">{{ $profile->institution ?? 'Institusi Belum Diatur' }}</span>
                        <span class="text-slate-300 hidden sm:inline">•</span>
                        <span>{{ $profile->major ?? '-' }}</span>
                        <span class="text-slate-300 hidden sm:inline">•</span>
                        <span class="inline-flex items-center font-semibold text-blue-900 bg-blue-50 px-2 py-0.5 rounded border border-blue-200 text-[11px]">
                            Seksi {{ $profile->division ?? 'Subbagian Umum' }}
                        </span>
                    </div>
                    <p class="text-[11px] sm:text-xs text-slate-400 mt-1">
                        NIM/NISN: <span class="font-mono text-slate-600 font-semibold">{{ $profile->nim_nisn ?? '-' }}</span> • Pembimbing: <span class="text-slate-600 font-medium">{{ $profile->mentor_name ?? 'Drs. Suryanto, M.Si' }}</span>
                    </p>
                </div>
            </div>

            <!-- Tombol Aksi Cepat Presensi (Full-width on mobile, neat on desktop) -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto pt-2 lg:pt-0">
                @if(!$todayAttendance)
                    <a href="{{ route('intern.absen') }}" class="h-10 px-5 text-xs sm:text-sm font-semibold rounded-lg bg-blue-800 hover:bg-blue-900 text-white shadow-xs inline-flex items-center justify-center gap-2 transition w-full sm:w-auto">
                        <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                        <span>Isi Presensi Hari Ini</span>
                    </a>
                @else
                    <a href="{{ route('intern.absen') }}" class="h-10 px-5 text-xs sm:text-sm font-semibold rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 shadow-xs inline-flex items-center justify-center gap-2 transition w-full sm:w-auto">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                        <span>Lihat Detail Presensi</span>
                    </a>
                @endif
                <a href="{{ route('intern.riwayat') }}" class="h-10 px-4 text-xs sm:text-sm font-medium rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 shadow-xs inline-flex items-center justify-center gap-1.5 transition w-full sm:w-auto">
                    <i data-lucide="history" class="w-4 h-4 text-slate-500"></i>
                    <span>Riwayat</span>
                </a>
            </div>
        </div>

        <!-- Progress Bar Masa Magang -->
        @if($totalDays > 0)
        <div class="mt-5 pt-4 border-t border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs text-slate-600 mb-2">
                <span class="font-medium flex items-center gap-1.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-blue-700"></i>
                    <span>Periode Magang ({{ $profile->duration_label ?? '3 Bulan' }}): <strong>Hari ke-{{ min($elapsedDays, $totalDays) }} dari {{ $totalDays }} hari</strong></span>
                </span>
                <span class="font-mono font-semibold text-slate-800">{{ $progress }}% Selesai (Sisa {{ $remainingDays }} hari)</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                <div class="bg-blue-700 h-2 rounded-full transition-all duration-700"
                     @style(['width: ' . $progress . '%'])></div>
            </div>
            <div class="flex flex-col sm:flex-row justify-between text-[11px] text-slate-400 mt-2 gap-1">
                <span>Mulai: <strong class="text-slate-600">{{ $profile->start_date ? $profile->start_date->translatedFormat('d M Y') : '-' }}</strong></span>
                <span>Berakhir: <strong class="text-slate-600">{{ $profile->end_date ? $profile->end_date->translatedFormat('d M Y') : '-' }}</strong></span>
            </div>
        </div>
        @endif
    </div>

    <!-- Status Presensi Hari Ini & Lokasi Kantor BPS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6">
        <!-- Status Hari Ini (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-xl p-4 sm:p-6 shadow-xs border border-slate-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-800 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900">Status Kehadiran Hari Ini</h2>
                            <p class="text-xs text-slate-500">{{ $today->translatedFormat('l, d F Y') }}</p>
                        </div>
                    </div>

                    @if($dayStatus['is_off'])
                        <span class="px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-purple-50 text-purple-800 border border-purple-200 flex items-center gap-1.5">
                            <i data-lucide="coffee" class="w-3.5 h-3.5 text-purple-600"></i>
                            <span>{{ $dayStatus['name'] }}</span>
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1.5">
                            <i data-lucide="briefcase" class="w-3.5 h-3.5 text-emerald-600"></i>
                            <span>Hari Kerja Aktif</span>
                        </span>
                    @endif
                </div>

                <div class="py-4">
                    @if($dayStatus['is_off'] && !$todayAttendance)
                        <!-- Hari Libur -->
                        <div class="p-4 rounded-xl bg-purple-50/60 border border-purple-100 flex items-start space-x-3 text-xs">
                            <i data-lucide="calendar" class="w-5 h-5 text-purple-600 shrink-0 mt-0.5"></i>
                            <div>
                                <h3 class="font-bold text-purple-900">{{ $dayStatus['name'] }}</h3>
                                <p class="text-purple-700 mt-0.5 leading-relaxed">
                                    Hari ini merupakan hari libur resmi (tidak ada kewajiban presensi reguler). Anda dapat beristirahat atau tetap mengisi presensi apabila sedang menjalankan penugasan dinas khusus.
                                </p>
                            </div>
                        </div>
                    @elseif($todayAttendance)
                        <!-- Sudah Presensi -->
                        <div class="space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-lg {{ $todayAttendance->status === 'hadir' ? 'bg-emerald-100 text-emerald-800' : ($todayAttendance->status === 'tidak_hadir_keterangan' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }} flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="{{ $todayAttendance->status === 'hadir' ? 'check' : ($todayAttendance->status === 'tidak_hadir_keterangan' ? 'mail' : 'x') }}" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="text-[11px] text-slate-500">Status Kehadiran Tercatat</div>
                                        <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                            <span>{{ $todayAttendance->status_label }}</span>
                                            <span class="text-slate-500 font-normal">({{ $todayAttendance->sub_status_label }})</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 text-xs pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200">
                                    @if($todayAttendance->check_in_time)
                                        <div>
                                            <span class="text-slate-400 block text-[10px]">Waktu</span>
                                            <span class="font-bold text-slate-800 font-mono">{{ substr($todayAttendance->check_in_time, 0, 5) }} WIB</span>
                                        </div>
                                    @endif
                                    @if($todayAttendance->distance_meters !== null)
                                        <div>
                                            <span class="text-slate-400 block text-[10px]">Jarak dari BPS</span>
                                            <span class="font-bold text-emerald-700 font-mono">{{ round($todayAttendance->distance_meters) }} m</span>
                                        </div>
                                    @endif
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">Verifikasi ACC</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border gap-1 {{ $todayAttendance->approval_badge_class }}">
                                            @if($todayAttendance->approval_status === 'approved')
                                                <i data-lucide="check" class="w-3 h-3 text-emerald-700"></i> Disetujui
                                            @elseif($todayAttendance->approval_status === 'rejected')
                                                <i data-lucide="x" class="w-3 h-3 text-rose-700"></i> Ditolak
                                            @else
                                                <i data-lucide="hourglass" class="w-3 h-3 text-amber-700"></i> Menunggu
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Ringkasan Logbook -->
                            <div class="p-3.5 rounded-xl bg-white border border-slate-200 text-xs">
                                <span class="font-semibold text-slate-700 block mb-1">Aktivitas yang Dilaporkan:</span>
                                <p class="text-slate-600 leading-relaxed">{{ $todayAttendance->activity_description ?? '-' }}</p>
                                @if($todayAttendance->approval_notes)
                                    <div class="mt-2.5 pt-2 border-t border-slate-100 text-amber-900 bg-amber-50/70 p-2.5 rounded-md border border-amber-200">
                                        <span class="font-bold block">Catatan Pembimbing:</span>
                                        <p class="mt-0.5">{{ $todayAttendance->approval_notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Belum Presensi (Clean alert banner with well-aligned CTA button) -->
                        <div class="p-4 sm:p-5 rounded-xl bg-amber-50/80 border border-amber-200/90 flex flex-col md:flex-row md:items-center justify-between gap-3.5">
                            <div class="flex items-start space-x-3 text-xs">
                                <i data-lucide="alert-circle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                                <div>
                                    <h3 class="font-bold text-amber-950 text-xs sm:text-sm">Laporan Presensi Belum Dikirim</h3>
                                    <p class="text-amber-900 mt-0.5 leading-relaxed">
                                        Batas pengisian presensi mandiri adalah sebelum pukul <strong>23:59 WIB</strong>. Lewat jam 12 malam otomatis tercatat alpa. Untuk status Hadir, Anda wajib berada di kantor BPS Karanganyar.
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('intern.absen') }}" class="w-full md:w-auto h-9 px-4 text-xs font-semibold rounded-lg bg-blue-800 hover:bg-blue-900 text-white shadow-xs inline-flex items-center justify-center gap-1.5 transition whitespace-nowrap flex-shrink-0">
                                <span>Kirim Presensi Sekarang</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-500 gap-2">
                <span>Jam Kerja BPS: <strong>{{ $officeConfig['work_start_time'] }} - {{ $officeConfig['work_end_time'] }} WIB</strong></span>
                <span class="text-emerald-700 font-medium flex items-center gap-1">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    <span>Verifikasi Koordinat Geospasial Aktif</span>
                </span>
            </div>
        </div>

        <!-- Titik Lokasi Kantor BPS Widget (1 col) -->
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-xs border border-slate-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 pb-3 sm:pb-4 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-800 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900">Titik Kantor BPS</h2>
                        <p class="text-xs text-slate-500">Kabupaten Karanganyar</p>
                    </div>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <div class="text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <i data-lucide="navigation" class="w-3 h-3 text-slate-500"></i>
                            <span>Alamat Resmi</span>
                        </div>
                        <p class="text-slate-600 leading-relaxed text-[11px] sm:text-xs">{{ $officeConfig['address'] }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-center">
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200">
                            <span class="text-[10px] text-slate-500 block">Radius Validasi</span>
                            <span class="text-xs sm:text-sm font-bold text-slate-800 font-mono">{{ $officeConfig['radius'] }} meter</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200">
                            <span class="text-[10px] text-slate-500 block">Koordinat Presisi</span>
                            <span class="text-[11px] sm:text-xs font-semibold text-slate-700 font-mono block truncate">{{ round($officeConfig['latitude'], 4) }}, {{ round($officeConfig['longitude'], 4) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                <a href="{{ route('intern.absen') }}" class="text-xs font-semibold text-blue-800 hover:text-blue-950 inline-flex items-center gap-1">
                    <span>Buka Peta Presensi Geospasial</span>
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Statistik Kehadiran Bulan Ini Cards -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="bar-chart-2" class="w-4 h-4 text-blue-800"></i>
                <span>Statistik Presensi Bulan Ini ({{ $today->translatedFormat('F Y') }})</span>
            </h2>
            <a href="{{ route('intern.riwayat') }}" class="text-xs font-semibold text-blue-800 hover:text-blue-950 flex items-center gap-1">
                <span>Lihat Riwayat Lengkap</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Hadir -->
            <div class="bg-white rounded-xl p-3.5 sm:p-4 shadow-xs border border-slate-200">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider">Hadir</span>
                    <div class="w-7 h-7 rounded-md bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-bold text-slate-900 font-mono">{{ $stats['hadir'] }} <span class="text-xs font-normal text-slate-500 font-sans">hari</span></div>
                <div class="text-[10px] sm:text-[11px] text-emerald-700 font-medium mt-0.5 truncate">Presensi di kantor BPS</div>
            </div>

            <!-- Izin / Sakit -->
            <div class="bg-white rounded-xl p-3.5 sm:p-4 shadow-xs border border-slate-200">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider">Izin / Sakit</span>
                    <div class="w-7 h-7 rounded-md bg-amber-50 text-amber-700 flex items-center justify-center">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-bold text-slate-900 font-mono">{{ $stats['izin_sakit'] }} <span class="text-xs font-normal text-slate-500 font-sans">hari</span></div>
                <div class="text-[10px] sm:text-[11px] text-amber-700 font-medium mt-0.5 truncate">Disertai keterangan</div>
            </div>

            <!-- Alpa / Kosong -->
            <div class="bg-white rounded-xl p-3.5 sm:p-4 shadow-xs border border-slate-200">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider">Alpa (Kosong)</span>
                    <div class="w-7 h-7 rounded-md bg-rose-50 text-rose-700 flex items-center justify-center">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-bold text-slate-900 font-mono">{{ $stats['alpa'] }} <span class="text-xs font-normal text-slate-500 font-sans">hari</span></div>
                <div class="text-[10px] sm:text-[11px] text-rose-700 font-medium mt-0.5 truncate">Tanpa presensi s/d 24:00</div>
            </div>

            <!-- Tingkat Kehadiran % -->
            <div class="bg-white rounded-xl p-3.5 sm:p-4 shadow-xs border border-slate-200">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider">Disiplin</span>
                    <div class="w-7 h-7 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center">
                        <i data-lucide="percent" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-bold text-slate-900 font-mono">{{ $stats['attendance_rate'] }}%</div>
                <div class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5 truncate">{{ $stats['approved'] }} disetujui • {{ $stats['pending'] }} pending</div>
            </div>
        </div>
    </div>

    <!-- Riwayat 5 Catatan Terakhir (Card layout on mobile, Table on desktop) -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm sm:text-base font-bold text-slate-900">5 Catatan Presensi & Logbook Terakhir</h2>
                <p class="text-xs text-slate-500">Riwayat jurnal aktivitas harian dan status persetujuan pembimbing</p>
            </div>
            <a href="{{ route('intern.riwayat') }}" class="h-8 px-3 text-xs font-medium rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 shadow-xs inline-flex items-center gap-1 transition">
                <span>Buka Semua</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <!-- Mobile Card List View (< sm screens) -->
        <div class="block sm:hidden divide-y divide-slate-100">
            @forelse($recentAttendances as $item)
                <div class="p-3.5 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="font-bold text-xs text-slate-900">
                            {{ $item->date->translatedFormat('d M Y') }}
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $item->status_badge_class }}">
                            {{ $item->status_label }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-500 font-mono">
                        <span>Waktu: <strong class="text-slate-700">{{ $item->check_in_time ? substr($item->check_in_time, 0, 5) . ' WIB' : '-' }}</strong></span>
                        @if($item->distance_meters !== null)
                            <span class="text-emerald-700">GPS: {{ round($item->distance_meters) }}m</span>
                        @endif
                    </div>

                    <div class="text-xs text-slate-600 line-clamp-2">
                        {{ $item->activity_description ?? '-' }}
                    </div>

                    <div class="flex items-center justify-between pt-1 text-xs">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border gap-1 {{ $item->approval_badge_class }}">
                            @if($item->approval_status === 'approved')
                                <i data-lucide="check" class="w-3 h-3 text-emerald-700"></i> Disetujui
                            @elseif($item->approval_status === 'rejected')
                                <i data-lucide="x" class="w-3 h-3 text-rose-700"></i> Ditolak
                            @else
                                <i data-lucide="hourglass" class="w-3 h-3 text-amber-700"></i> Menunggu ACC
                            @endif
                        </span>
                        <a href="{{ route('intern.riwayat') }}#item-{{ $item->id }}" class="text-blue-800 font-semibold text-[11px] inline-flex items-center gap-0.5">
                            <span>Detail</span>
                            <i data-lucide="chevron-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-slate-400 text-xs">
                    <i data-lucide="folder-open" class="w-6 h-6 mx-auto mb-1 opacity-60"></i>
                    <span>Belum ada catatan presensi yang tersimpan.</span>
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View (>= sm screens) -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Uraian Aktivitas</th>
                        <th class="py-3 px-4">Status ACC</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentAttendances as $item)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $item->date->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-600 whitespace-nowrap">
                                {{ $item->check_in_time ? substr($item->check_in_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $item->status_badge_class }}">
                                    {{ $item->status_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600 max-w-xs truncate">
                                {{ $item->activity_description ?? '-' }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border flex items-center gap-1 w-fit {{ $item->approval_badge_class }}">
                                    @if($item->approval_status === 'approved')
                                        <i data-lucide="check" class="w-3 h-3 text-emerald-700"></i> Disetujui
                                    @elseif($item->approval_status === 'rejected')
                                        <i data-lucide="x" class="w-3 h-3 text-rose-700"></i> Ditolak
                                    @else
                                        <i data-lucide="hourglass" class="w-3 h-3 text-amber-700"></i> Menunggu
                                    @endif
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('intern.riwayat') }}#item-{{ $item->id }}" class="text-blue-800 hover:text-blue-950 font-semibold inline-flex items-center gap-1">
                                    <span>Detail</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                <i data-lucide="folder-open" class="w-6 h-6 mx-auto mb-1 opacity-60"></i>
                                <span>Belum ada catatan presensi yang tersimpan.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
