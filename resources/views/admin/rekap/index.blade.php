@extends('layouts.app')

@section('title', 'Rekapitulasi Presensi - SIM-MAGANG BPS')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-800 flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Rekapitulasi Presensi</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Rekapitulasi Presensi & Jurnal Magang</h1>
            <p class="text-xs text-slate-500 mt-0.5">Laporan komprehensif kehadiran, toleransi geospasial, dan rekapitulasi penilaian kinerja magang BPS.</p>
        </div>

        <!-- Export Buttons (Stacked on mobile, row on desktop) -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('admin.rekap.pdf', request()->query()) }}" target="_blank"
               class="h-10 px-4 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-medium text-xs sm:text-sm transition shadow-xs inline-flex items-center justify-center gap-2 w-full sm:w-auto">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Cetak / Ekspor PDF Resmi</span>
            </a>

            <a href="{{ route('admin.rekap.csv', request()->query()) }}"
               class="h-10 px-4 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs sm:text-sm transition shadow-xs inline-flex items-center justify-center gap-2 w-full sm:w-auto">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Ekspor Excel (.CSV)</span>
            </a>
        </div>
    </div>

    <!-- Filter Form Bar -->
    <div class="bg-white rounded-xl p-4 sm:p-5 shadow-xs border border-slate-200">
        <form method="GET" action="{{ route('admin.rekap.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <!-- Peserta -->
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Peserta Magang</label>
                <select name="intern_id" class="w-full h-10 text-xs font-medium px-3 rounded-lg border border-slate-300 bg-white focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                    <option value="">Semua Peserta</option>
                    @foreach($interns as $in)
                        <option value="{{ $in->id }}" {{ $internId == $in->id ? 'selected' : '' }}>{{ $in->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Seksi BPS -->
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Seksi Penempatan</label>
                <select name="division" class="w-full h-10 text-xs font-medium px-3 rounded-lg border border-slate-300 bg-white focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                    <option value="">Semua Seksi</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div }}" {{ $division == $div ? 'selected' : '' }}>{{ $div }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Bulan -->
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Bulan</label>
                <select name="month" class="w-full h-10 text-xs font-medium px-3 rounded-lg border border-slate-300 bg-white focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Tahun -->
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Tahun</label>
                <select name="year" class="w-full h-10 text-xs font-medium px-3 rounded-lg border border-slate-300 bg-white focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                    @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status Presensi</label>
                <div class="flex items-center gap-1.5">
                    <select name="status" class="w-full h-10 text-xs font-medium px-3 rounded-lg border border-slate-300 bg-white focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                        <option value="">Semua Status</option>
                        <option value="hadir" {{ $status == 'hadir' ? 'selected' : '' }}>Hadir Kantor</option>
                        <option value="tidak_hadir_keterangan" {{ $status == 'tidak_hadir_keterangan' ? 'selected' : '' }}>Izin / Sakit</option>
                        <option value="tidak_hadir_tanpa_keterangan" {{ $status == 'tidak_hadir_tanpa_keterangan' ? 'selected' : '' }}>Alpa</option>
                    </select>
                    <button type="submit" class="h-10 px-3.5 rounded-lg bg-blue-800 hover:bg-blue-900 text-white text-xs font-medium transition shadow-xs flex-shrink-0 flex items-center justify-center">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary KPI Pills -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs text-center">
            <span class="text-[10px] uppercase font-semibold text-slate-400 block">Total Catatan</span>
            <span class="text-xl font-bold text-slate-800 font-mono">{{ $totalRecords }}</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs text-center">
            <span class="text-[10px] uppercase font-semibold text-emerald-700 block">Hadir</span>
            <span class="text-xl font-bold text-emerald-700 font-mono">{{ $totalHadir }}</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs text-center">
            <span class="text-[10px] uppercase font-semibold text-amber-700 block">Izin / Sakit</span>
            <span class="text-xl font-bold text-amber-700 font-mono">{{ $totalIzinSakit }}</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs text-center">
            <span class="text-[10px] uppercase font-semibold text-rose-700 block">Alpa</span>
            <span class="text-xl font-bold text-rose-700 font-mono">{{ $totalAlpa }}</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs text-center col-span-2 sm:col-span-1">
            <span class="text-[10px] uppercase font-semibold text-blue-900 block">Tingkat Disiplin</span>
            <span class="text-xl font-bold text-blue-900 font-mono">{{ $rate }}%</span>
        </div>
    </div>

    <!-- ========================================================
         TABEL REKAPITULASI PRESENSI MENYAMPING (SEMUA PESERTA MAGANG)
         ======================================================== -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="grid" class="w-4 h-4 text-blue-800"></i>
                    <span>Tabel Rekapitulasi Presensi Menyamping (Bulan {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }})</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Format matriks horizontal kalender presensi harian dan status verifikasi ACC pembimbing.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500 font-medium">Total: <strong>{{ count($allInternData) }} Peserta</strong></span>
                <a href="{{ route('admin.rekap.pdf', request()->query()) }}" target="_blank"
                   class="h-8 px-3 rounded-lg bg-blue-800 hover:bg-blue-900 text-white text-xs font-medium inline-flex items-center gap-1.5 transition shadow-xs">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Cetak PDF</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto p-4">
            <table class="w-full border-collapse border border-slate-300 text-center text-xs">
                <thead>
                    <tr class="bg-slate-100 font-bold text-slate-700">
                        <th rowspan="2" class="border border-slate-300 p-2 w-8">No</th>
                        <th rowspan="2" class="border border-slate-300 p-2 text-left min-w-[160px]">Nama Peserta</th>
                        <th rowspan="2" class="border border-slate-300 p-2 text-left min-w-[130px]">Seksi BPS</th>
                        <th colspan="{{ count($daysHeader) }}" class="border border-slate-300 p-1.5 bg-slate-50 text-[11px] font-semibold">
                            Tanggal Presensi (1 s/d {{ count($daysHeader) }})
                        </th>
                        <th colspan="4" class="border border-slate-300 p-1.5 w-24 bg-slate-100">Total</th>
                        <th rowspan="2" class="border border-slate-300 p-2 w-20 bg-slate-100">Status ACC</th>
                    </tr>
                    <tr class="bg-slate-50 font-semibold text-[10px] text-slate-600">
                        @foreach($daysHeader as $dh)
                            <th class="border border-slate-300 p-1 w-6 {{ $dh['is_off'] ? 'bg-rose-50 text-rose-600 font-bold' : '' }}" title="{{ $dh['day_name'] }}">
                                {{ $dh['day'] }}
                            </th>
                        @endforeach
                        <th class="border border-slate-300 p-1 w-6 text-emerald-800 font-bold bg-slate-50">H</th>
                        <th class="border border-slate-300 p-1 w-6 text-amber-800 font-bold bg-slate-50">I</th>
                        <th class="border border-slate-300 p-1 w-6 text-blue-800 font-bold bg-slate-50">S</th>
                        <th class="border border-slate-300 p-1 w-6 text-rose-800 font-bold bg-slate-50">A</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allInternData as $idx => $item)
                        <tr class="hover:bg-blue-50/20 transition">
                            <td class="border border-slate-300 p-2 font-mono text-slate-500">{{ $idx + 1 }}</td>
                            <td class="border border-slate-300 p-2 text-left font-semibold text-slate-900 whitespace-nowrap">
                                <a href="{{ route('admin.rekap.index', array_merge(request()->query(), ['intern_id' => $item['intern']->id])) }}" class="hover:text-blue-800 hover:underline">
                                    {{ $item['intern']->name }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-normal font-sans">{{ $item['intern']->internProfile->institution ?? '-' }}</div>
                            </td>
                            <td class="border border-slate-300 p-2 text-left text-xs text-slate-600">
                                {{ $item['intern']->internProfile->division ?? '-' }}
                            </td>

                            <!-- Tanggal 1 s/d 30/31 Menyamping -->
                            @foreach($item['matrixDays'] as $day)
                                <td class="border border-slate-300 p-1 font-bold text-[10px]
                                    {{ $day['status_code'] === 'H' ? 'text-emerald-700 bg-emerald-50/40' : '' }}
                                    {{ $day['status_code'] === 'I' ? 'text-amber-700 bg-amber-50/50' : '' }}
                                    {{ $day['status_code'] === 'S' ? 'text-blue-700 bg-blue-50/50' : '' }}
                                    {{ $day['status_code'] === 'A' ? 'text-rose-700 bg-rose-50/50' : '' }}
                                    {{ $day['status_code'] === 'L' ? 'text-slate-300 bg-slate-100/70' : '' }}
                                " title="Tgl {{ $day['day'] }} ({{ $day['day_name'] }}): {{ $day['status_label'] }} - Status ACC: {{ $day['acc_label'] }}">
                                    {{ $day['status_code'] }}
                                </td>
                            @endforeach

                            <td class="border border-slate-300 p-1 font-bold text-emerald-800 bg-emerald-50/20 font-mono">{{ $item['totalHadir'] }}</td>
                            <td class="border border-slate-300 p-1 font-semibold text-amber-800 font-mono">{{ $item['attendances']->where('status', 'tidak_hadir_keterangan')->where('sub_status', 'izin')->count() }}</td>
                            <td class="border border-slate-300 p-1 font-semibold text-blue-800 font-mono">{{ $item['attendances']->where('status', 'tidak_hadir_keterangan')->where('sub_status', 'sakit')->count() }}</td>
                            <td class="border border-slate-300 p-1 font-bold text-rose-800 bg-rose-50/20 font-mono">{{ $item['totalAlpa'] }}</td>

                            <td class="border border-slate-300 p-1.5 font-bold text-xs whitespace-nowrap">
                                @if($item['overallAcc'] === 'Setujui')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Setujui
                                    </span>
                                @elseif($item['overallAcc'] === 'Ngga')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        Ngga
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Belum
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($daysHeader) + 8 }}" class="p-8 text-center text-slate-400">
                                Tidak ada data peserta magang pada kriteria saringan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-600 gap-2">
            <div class="flex items-center gap-3">
                <span class="font-semibold text-slate-700">Kode:</span>
                <span><strong class="text-emerald-700">H</strong> Hadir</span>
                <span><strong class="text-amber-700">I</strong> Izin</span>
                <span><strong class="text-blue-700">S</strong> Sakit</span>
                <span><strong class="text-rose-700">A</strong> Alpa</span>
                <span><strong class="text-slate-400">L</strong> Libur</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-semibold text-slate-700">Status ACC:</span>
                <span class="text-emerald-800 font-bold">Setujui</span>
                <span>/</span>
                <span class="text-rose-800 font-bold">Ngga</span>
            </div>
        </div>
    </div>

    <!-- Tabular Records (Mobile Cards + Desktop Table) -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h2 class="text-sm font-bold text-slate-900">
                Rincian Riwayat Harian Presensi ({{ $attendances->count() }} Catatan)
            </h2>
            <div class="text-xs text-slate-500">
                Periode: <strong>{{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}</strong>
            </div>
        </div>

        <!-- Mobile Card View (< md screens) -->
        <div class="block md:hidden divide-y divide-slate-100">
            @forelse($attendances as $index => $item)
                <div class="p-4 space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-bold text-xs text-slate-900">{{ $item->user->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $item->user->internProfile->institution ?? '-' }} • Seksi {{ $item->user->internProfile->division ?? '-' }}</div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border whitespace-nowrap {{ $item->status_badge_class }}">
                            {{ $item->status_label }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-500 font-mono bg-slate-50 p-2 rounded-lg border border-slate-100">
                        <span>{{ $item->date->format('d/m/Y') }} • {{ $item->check_in_time ? substr($item->check_in_time, 0, 5) . ' WIB' : '-' }}</span>
                        @if($item->distance_meters !== null)
                            <span class="text-emerald-700 font-bold">GPS: {{ round($item->distance_meters) }}m</span>
                        @endif
                    </div>

                    <div class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                        {{ $item->activity_description ?? '-' }}
                    </div>

                    <div class="flex items-center justify-between pt-1 text-[11px]">
                        <span class="px-2 py-0.5 rounded-full font-semibold border {{ $item->approval_badge_class }}">
                            {{ ucfirst($item->approval_status) }}
                        </span>
                        <span class="text-slate-400">
                            Oleh: <strong class="text-slate-600">{{ $item->approver->name ?? '-' }}</strong>
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-xs">
                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                    <span>Tidak ada data presensi pada kriteria saringan ini.</span>
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View (>= md screens) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-wider font-semibold text-[11px]">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Peserta</th>
                        <th class="py-3 px-4">Seksi BPS</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Waktu & Status</th>
                        <th class="py-3 px-4">Jarak GPS</th>
                        <th class="py-3 px-4">Uraian Aktivitas</th>
                        <th class="py-3 px-4">Status ACC</th>
                        <th class="py-3 px-4">Diverifikasi Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $index => $item)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 text-center font-medium text-slate-400 font-mono">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-900">{{ $item->user->name }}</div>
                                <div class="text-[10px] text-slate-500">{{ $item->user->internProfile->institution ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">
                                {{ $item->user->internProfile->division ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-800 whitespace-nowrap font-mono">
                                {{ $item->date->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium border {{ $item->status_badge_class }}">
                                    {{ $item->status_label }}
                                </span>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $item->check_in_time ? substr($item->check_in_time, 0, 5) . ' WIB' : '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap font-mono text-slate-700">
                                @if($item->distance_meters !== null)
                                    <span class="text-emerald-700 font-medium">{{ round($item->distance_meters) }} m</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 max-w-xs truncate text-slate-600" title="{{ $item->activity_description }}">
                                {{ $item->activity_description ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium border {{ $item->approval_badge_class }}">
                                    {{ ucfirst($item->approval_status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-600">
                                {{ $item->approver->name ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                <span>Tidak ada data presensi pada kriteria saringan ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
