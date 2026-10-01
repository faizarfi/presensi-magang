@extends('layouts.app')

@section('title', 'Pusat Kendali Administrator - SIM-MAGANG BPS Karanganyar')

@section('content')
<div class="space-y-5 sm:space-y-6">

    <!-- Top Executive Banner -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-4 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 sm:gap-5">
        <div>
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs font-semibold text-slate-500 mb-1">
                <span class="flex items-center gap-1.5 text-blue-900 font-bold">
                    <i data-lucide="shield-check" class="w-4 h-4 text-blue-800"></i>
                    <span>Panel Pembimbing Lapangan BPS</span>
                </span>
                <span class="text-slate-300">•</span>
                <span>{{ $today->translatedFormat('l, d F Y') }}</span>
            </div>
            <h1 class="text-lg sm:text-2xl font-bold tracking-tight text-slate-900 leading-tight">Badan Pusat Statistik Kab. Karanganyar</h1>
            <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-2xl leading-relaxed">
                Pusat pengawasan presensi digital mandiri, verifikasi toleransi radius kantor BPS, dan persetujuan logbook mahasiswa magang.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto pt-1 md:pt-0">
            <a href="{{ route('admin.verifikasi.index') }}" class="h-10 px-4 rounded-lg bg-blue-800 hover:bg-blue-900 text-white font-semibold text-xs sm:text-sm transition shadow-xs inline-flex items-center justify-center gap-2 w-full sm:w-auto">
                <i data-lucide="check-check" class="w-4 h-4"></i>
                <span>Verifikasi Presensi (ACC)</span>
                @if($pendingCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full bg-amber-400 text-amber-950 text-[10px] font-bold">{{ $pendingCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.rekap.index') }}" class="h-10 px-4 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs sm:text-sm transition shadow-xs inline-flex items-center justify-center gap-2 w-full sm:w-auto">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-slate-500"></i>
                <span>Rekapitulasi PDF</span>
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards (2 cols on phone, 3 on tablet, 5 on desktop) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        <!-- Total Magang -->
        <div class="bg-white rounded-xl p-3.5 sm:p-4 shadow-xs border border-slate-200 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Peserta</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-50 text-blue-800 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2 sm:mt-3">
                <div class="text-xl sm:text-2xl font-bold text-slate-900 font-mono">{{ $totalInterns }}</div>
                <div class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5 truncate">Peserta aktif terdaftar</div>
            </div>
        </div>

        <!-- Hadir Hari Ini -->
        <div class="bg-white rounded-xl p-3.5 sm:p-4 shadow-xs border border-slate-200 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-xs font-semibold text-emerald-700 uppercase tracking-wider">Hadir Hari Ini</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2 sm:mt-3">
                <div class="text-xl sm:text-2xl font-bold text-emerald-700 font-mono">{{ $hadirToday }}</div>
                <div class="text-[10px] sm:text-[11px] text-emerald-700 mt-0.5 truncate">Di kantor BPS</div>
            </div>
        </div>

        <!-- Izin / Sakit -->
        <div class="bg-white rounded-xl p-3.5 sm:p-4 shadow-xs border border-slate-200 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-xs font-semibold text-amber-700 uppercase tracking-wider">Izin / Sakit</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2 sm:mt-3">
                <div class="text-xl sm:text-2xl font-bold text-amber-700 font-mono">{{ $izinSakitToday }}</div>
                <div class="text-[10px] sm:text-[11px] text-amber-700 mt-0.5 truncate">Disertai keterangan</div>
            </div>
        </div>

        <!-- Alpa Hari Ini -->
        <div class="bg-white rounded-xl p-3.5 sm:p-4 shadow-xs border border-slate-200 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-xs font-semibold text-rose-700 uppercase tracking-wider">Belum Presensi</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-rose-50 text-rose-700 flex items-center justify-center">
                    <i data-lucide="user-x" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2 sm:mt-3">
                <div class="text-xl sm:text-2xl font-bold text-rose-700 font-mono">{{ $alpaToday + $notYetAttendedCount }}</div>
                <div class="text-[10px] sm:text-[11px] text-rose-700 mt-0.5 truncate">S/d jam 24:00</div>
            </div>
        </div>

        <!-- Menunggu ACC (Spans 2 cols on phone, 1 on tablet/desktop) -->
        <div class="bg-white rounded-xl p-3.5 sm:p-4 shadow-xs border border-slate-200 flex flex-col justify-between col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-xs font-semibold text-blue-900 uppercase tracking-wider">Menunggu ACC</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-50 text-blue-800 flex items-center justify-center">
                    <i data-lucide="hourglass" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2 sm:mt-3 flex items-center justify-between sm:block">
                <div class="text-xl sm:text-2xl font-bold text-blue-900 font-mono">{{ $pendingCount }}</div>
                <a href="{{ route('admin.verifikasi.index') }}" class="text-[11px] text-blue-800 font-semibold hover:underline mt-0.5 inline-flex items-center gap-1">
                    <span>Verifikasi Sekarang</span>
                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Chart Trend & Pending Queue Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6">
        <!-- 7-Day Trend Chart (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-xl p-4 sm:p-5 shadow-xs border border-slate-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-2">
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Tren Kehadiran Magang 7 Hari Terakhir</h2>
                    <p class="text-xs text-slate-500">Perbandingan harian mahasiswa hadir di kantor BPS, berizin, dan alpa</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold pt-1 sm:pt-0">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Hadir</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span> Izin</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span> Alpa</span>
                </div>
            </div>

            <div class="pt-3 h-56 sm:h-64 relative">
                <canvas id="trendChart"
                    data-dates="{{ json_encode($chartDates) }}"
                    data-hadir="{{ json_encode($chartHadir) }}"
                    data-izin="{{ json_encode($chartIzin) }}"
                    data-alpa="{{ json_encode($chartAlpa) }}"></canvas>
            </div>
        </div>

        <!-- Pending ACC Mini Queue (1 col) -->
        <div class="bg-white rounded-xl p-4 sm:p-5 shadow-xs border border-slate-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900">Antrean Verifikasi ACC</h2>
                        <p class="text-xs text-slate-500">Dapat disetujui kapan saja selama magang</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 font-mono">
                        {{ $pendingCount }}
                    </span>
                </div>

                <div class="mt-3 space-y-2.5">
                    @forelse($recentPending as $item)
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-between gap-3 text-xs">
                            <div class="min-w-0">
                                <div class="font-bold text-slate-800 truncate">{{ $item->user->name }}</div>
                                <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                    <span>{{ $item->date->translatedFormat('d M Y') }}</span>
                                    <span>•</span>
                                    <span class="font-semibold {{ $item->status === 'hadir' ? 'text-emerald-700' : 'text-amber-700' }}">{{ $item->status_label }}</span>
                                </div>
                            </div>
                            <form id="quickAccForm-{{ $item->id }}" action="{{ route('admin.verifikasi.update', $item->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="approval_status" value="approved">
                                <input type="hidden" name="approval_notes" value="Disetujui oleh Pembimbing BPS.">
                                <button type="button" onclick="confirmQuickAcc({{ $item->id }}, '{{ addslashes($item->user->name) }}', '{{ $item->date->translatedFormat('d M Y') }}')"
                                        class="h-7 px-3 rounded-md bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs whitespace-nowrap shadow-xs transition flex items-center gap-1">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                    <span>ACC</span>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400">
                            <i data-lucide="check-check" class="w-6 h-6 mx-auto mb-1 text-emerald-600"></i>
                            <span class="text-xs text-slate-500">Semua presensi telah diverifikasi!</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                <a href="{{ route('admin.verifikasi.index') }}" class="text-xs font-semibold text-blue-800 hover:text-blue-950 inline-flex items-center gap-1">
                    <span>Lihat Seluruh Antrean Verifikasi</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Presensi Hari Ini Live Table (Responsive: Cards on Mobile, Table on Desktop) -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm sm:text-base font-bold text-slate-900">Daftar Kehadiran Hari Ini</h2>
                <p class="text-xs text-slate-500">{{ $today->translatedFormat('l, d F Y') }} — Tervalidasi radius geospasial kantor BPS Karanganyar</p>
            </div>
            <a href="{{ route('admin.rekap.index') }}" class="h-8 px-3 rounded-md border border-slate-300 text-xs font-medium text-slate-700 hover:bg-slate-50 transition self-start sm:self-auto flex items-center gap-1.5">
                <i data-lucide="file-text" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Lihat Rekapitulasi Lengkap</span>
            </a>
        </div>

        <!-- Mobile Card View (< sm screens) -->
        <div class="block sm:hidden divide-y divide-slate-100">
            @forelse($todayAttendances as $item)
                <div class="p-3.5 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-bold text-xs text-slate-900">{{ $item->user->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $item->user->internProfile->division ?? 'Subbagian Umum' }}</div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $item->status_badge_class }}">
                            {{ $item->status_label }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-600 font-mono">
                        <span>Waktu: <strong>{{ $item->check_in_time ? substr($item->check_in_time, 0, 5) . ' WIB' : '-' }}</strong></span>
                        @if($item->distance_meters !== null)
                            <span class="text-emerald-700">GPS: {{ round($item->distance_meters) }}m</span>
                        @endif
                    </div>

                    <div class="text-xs text-slate-600 line-clamp-2">
                        {{ $item->activity_description ?? '-' }}
                    </div>

                    <div class="flex items-center justify-between pt-1 text-xs">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $item->approval_badge_class }}">
                            {{ ucfirst($item->approval_status) }}
                        </span>
                        <a href="{{ route('admin.verifikasi.index', ['intern_id' => $item->user_id, 'date' => $item->date->toDateString()]) }}"
                           class="text-blue-800 font-semibold text-xs inline-flex items-center gap-1">
                            <span>Review & ACC</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-slate-400 text-xs">
                    <i data-lucide="clock" class="w-6 h-6 mx-auto mb-1 opacity-50"></i>
                    <span>Belum ada peserta magang yang mengirimkan presensi hari ini.</span>
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View (>= sm screens) -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Peserta Magang</th>
                        <th class="py-3 px-4">Seksi BPS</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Status & Jarak GPS</th>
                        <th class="py-3 px-4">Aktivitas Hari Ini</th>
                        <th class="py-3 px-4">Status ACC</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($todayAttendances as $item)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $item->user->name }}
                                <div class="text-[11px] font-normal text-slate-400">{{ $item->user->internProfile->institution ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                {{ $item->user->internProfile->division ?? 'Subbagian Umum' }}
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-700 whitespace-nowrap">
                                {{ $item->check_in_time ? substr($item->check_in_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $item->status_badge_class }}">
                                    {{ $item->status_label }}
                                </span>
                                @if($item->distance_meters !== null)
                                    <div class="text-[10px] text-emerald-700 font-semibold mt-0.5 flex items-center gap-0.5">
                                        <i data-lucide="map-pin" class="w-3 h-3"></i>
                                        <span>{{ round($item->distance_meters) }} m</span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600 max-w-xs truncate">
                                {{ $item->activity_description ?? '-' }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $item->approval_badge_class }}">
                                    {{ ucfirst($item->approval_status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.verifikasi.index', ['intern_id' => $item->user_id, 'date' => $item->date->toDateString()]) }}"
                                   class="text-blue-800 hover:text-blue-950 font-semibold inline-flex items-center gap-1">
                                    <span>Review</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <i data-lucide="clock" class="w-6 h-6 mx-auto mb-1 opacity-50"></i>
                                <span>Belum ada peserta magang yang mengirimkan presensi hari ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function confirmQuickAcc(id, name, date) {
        Swal.fire({
            title: 'Setujui Laporan Presensi?',
            html: `<div class="text-xs text-slate-600 text-left space-y-1">
                    <p>Mahasiswa: <strong>${name}</strong></p>
                    <p>Tanggal: <strong>${date}</strong></p>
                    <p class="mt-2 text-emerald-700">Laporan presensi dan jurnal aktivitas harian akan disetujui (ACC).</p>
                   </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Setujui (ACC)',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('quickAccForm-' + id).submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('trendChart');
        if (!canvas) return;

        const dates = JSON.parse(canvas.getAttribute('data-dates') || '[]');
        const hadir = JSON.parse(canvas.getAttribute('data-hadir') || '[]');
        const izin  = JSON.parse(canvas.getAttribute('data-izin') || '[]');
        const alpa  = JSON.parse(canvas.getAttribute('data-alpa') || '[]');

        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Hadir',
                        data: hadir,
                        backgroundColor: '#10b981',
                        borderRadius: 4,
                        barPercentage: 0.65,
                    },
                    {
                        label: 'Izin/Sakit',
                        data: izin,
                        backgroundColor: '#f59e0b',
                        borderRadius: 4,
                        barPercentage: 0.65,
                    },
                    {
                        label: 'Alpa',
                        data: alpa,
                        backgroundColor: '#ef4444',
                        borderRadius: 4,
                        barPercentage: 0.65,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        titleFont: { size: 11, weight: 'bold' },
                        bodyFont: { size: 11 },
                        cornerRadius: 6,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, family: 'monospace' }, color: '#64748b' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 10 }, color: '#64748b' },
                        grid: { color: '#f1f5f9' }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
