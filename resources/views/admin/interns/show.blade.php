@extends('layouts.app')

@section('title', 'Dossier Peserta Magang - ' . $intern->name . ' - SIM-MAGANG BPS')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-800 flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <span>/</span>
                <a href="{{ route('admin.interns.index') }}" class="hover:text-blue-800">Peserta Magang</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">{{ $intern->name }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Dossier & Logbook Kegiatan Magang</h1>
            <p class="text-xs text-slate-500 mt-0.5">Badan Pusat Statistik Kabupaten Karanganyar</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.interns.index') }}"
               class="h-10 px-4 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-xs sm:text-sm transition inline-flex items-center space-x-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>

            <a href="{{ route('admin.rekap.pdf', ['intern_id' => $intern->id]) }}" target="_blank"
               class="h-10 px-4 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-medium text-xs sm:text-sm transition shadow-xs inline-flex items-center space-x-1.5">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Cetak Berita Acara PDF</span>
            </a>

            <a href="{{ route('admin.interns.edit', $intern->id) }}"
               class="h-10 px-4 rounded-lg bg-blue-800 hover:bg-blue-900 text-white font-medium text-xs sm:text-sm transition shadow-xs inline-flex items-center space-x-1.5">
                <i data-lucide="pencil" class="w-4 h-4"></i>
                <span>Edit Data</span>
            </a>
        </div>
    </div>

    <!-- Student Academic Dossier Card -->
    <div class="bg-white rounded-xl p-5 sm:p-6 shadow-xs border border-slate-200 grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="text-center md:border-r border-slate-100 md:pr-6 flex flex-col items-center justify-center">
            <div class="w-20 h-20 rounded-full bg-blue-100 text-blue-900 border border-blue-200 flex items-center justify-center font-bold text-2xl overflow-hidden mb-3">
                @if($intern->avatar)
                    <img src="{{ asset('storage/' . $intern->avatar) }}" alt="{{ $intern->name }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($intern->name, 0, 2)) }}
                @endif
            </div>
            <h2 class="text-base font-bold text-slate-900">{{ $intern->name }}</h2>
            <p class="text-xs text-blue-800 font-medium">{{ $intern->email }}</p>
            <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-800 border border-blue-200">
                <i data-lucide="building-2" class="w-3.5 h-3.5 text-blue-800"></i>
                <span>Seksi {{ $intern->internProfile->division ?? 'Subbagian Umum' }}</span>
            </div>
        </div>

        <div class="md:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-semibold tracking-wider">NIM / NISN</span>
                <span class="font-bold text-slate-800 font-mono text-sm">{{ $intern->internProfile->nim_nisn ?? '-' }}</span>
            </div>
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-semibold tracking-wider">Asal Kampus / Sekolah</span>
                <span class="font-bold text-slate-800 text-sm">{{ $intern->internProfile->institution ?? '-' }}</span>
            </div>
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-semibold tracking-wider">Jurusan / Program Studi</span>
                <span class="font-medium text-slate-700">{{ $intern->internProfile->major ?? '-' }}</span>
            </div>
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-semibold tracking-wider">Pembimbing Lapangan BPS</span>
                <span class="font-semibold text-blue-800">{{ $intern->internProfile->mentor_name ?? '-' }}</span>
            </div>
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-semibold tracking-wider">Durasi & Periode Magang</span>
                <span class="font-bold text-slate-800 text-xs">
                    {{ $intern->internProfile->duration_label ?? '3 Bulan' }}
                    <span class="text-slate-500 font-normal">({{ $intern->internProfile->total_days ?? 0 }} Hari)</span>
                </span>
                <span class="text-slate-500 text-[11px] block mt-0.5">
                    {{ $intern->internProfile && $intern->internProfile->start_date ? $intern->internProfile->start_date->translatedFormat('d M Y') : '-' }} s/d {{ $intern->internProfile && $intern->internProfile->end_date ? $intern->internProfile->end_date->translatedFormat('d M Y') : '-' }}
                </span>
            </div>
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-semibold tracking-wider">Nomor Kontak WhatsApp</span>
                <span class="font-medium text-slate-700 font-mono">{{ $intern->phone ?? '-' }}</span>
            </div>
            <div class="sm:col-span-2">
                <span class="text-slate-500 block text-[10px] uppercase font-semibold tracking-wider">Alamat Domisili Selama Magang</span>
                <p class="text-slate-600 mt-0.5 leading-relaxed">{{ $intern->internProfile->address ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Attendance Stats Pill Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs text-center">
            <span class="text-[10px] uppercase font-semibold tracking-wider text-slate-500 block">Total Hari Kerja</span>
            <span class="text-xl font-bold text-slate-900 mt-0.5 block">{{ $stats['total'] }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs text-center">
            <span class="text-[10px] uppercase font-semibold tracking-wider text-emerald-700 block">Hadir</span>
            <span class="text-xl font-bold text-emerald-700 mt-0.5 block">{{ $stats['hadir'] }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs text-center">
            <span class="text-[10px] uppercase font-semibold tracking-wider text-amber-700 block">Izin / Sakit</span>
            <span class="text-xl font-bold text-amber-700 mt-0.5 block">{{ $stats['izin_sakit'] }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs text-center">
            <span class="text-[10px] uppercase font-semibold tracking-wider text-rose-700 block">Alpa</span>
            <span class="text-xl font-bold text-rose-700 mt-0.5 block">{{ $stats['alpa'] }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs text-center col-span-2 sm:col-span-1">
            <span class="text-[10px] uppercase font-semibold tracking-wider text-blue-800 block">Tingkat Kehadiran</span>
            <span class="text-xl font-bold text-blue-800 mt-0.5 block">{{ $stats['attendance_rate'] }}%</span>
        </div>
    </div>

    <!-- Complete Logbook Table -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Riwayat Presensi & Logbook Aktivitas Lengkap</h2>
                <p class="text-xs text-slate-500 mt-0.5">Catatan kehadiran harian, pembelajaran yang diperoleh, dan status ACC pembimbing BPS.</p>
            </div>
            <a href="{{ route('admin.rekap.pdf', ['intern_id' => $intern->id]) }}" target="_blank"
               class="h-9 px-3 rounded-lg border border-slate-300 text-xs font-medium text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Cetak Laporan</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-wider font-semibold text-[11px]">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Status & Jarak</th>
                        <th class="py-3 px-4">Uraian Aktivitas</th>
                        <th class="py-3 px-4">Pembelajaran</th>
                        <th class="py-3 px-4">Status ACC</th>
                        <th class="py-3 px-4 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($intern->attendances as $item)
                        <tr class="hover:bg-slate-50/60 transition align-top">
                            <td class="py-3.5 px-4 font-medium text-slate-800 whitespace-nowrap">
                                {{ $item->date->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-500 whitespace-nowrap">
                                {{ $item->check_in_time ? substr($item->check_in_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium border {{ $item->status_badge_class }}">
                                    {{ $item->status_label }}
                                </span>
                                @if($item->distance_meters !== null)
                                    <div class="text-[10px] text-emerald-700 font-medium mt-0.5 flex items-center gap-1">
                                        <i data-lucide="map-pin" class="w-3 h-3"></i>
                                        <span>{{ round($item->distance_meters) }} m dari BPS</span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 max-w-xs text-slate-700 leading-snug">
                                {{ $item->activity_description ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 max-w-xs text-slate-600 leading-snug">
                                {{ $item->learning_obtained ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium border {{ $item->approval_badge_class }}">
                                    {{ ucfirst($item->approval_status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.verifikasi.index', ['intern_id' => $intern->id, 'date' => $item->date->toDateString()]) }}"
                                   class="text-blue-800 hover:text-blue-900 font-medium inline-flex items-center gap-1 transition">
                                    <span>Review ACC</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                                    <p class="font-medium text-slate-600">Belum ada riwayat aktivitas yang tercatat untuk peserta ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
