@extends('layouts.app')

@section('title', 'Riwayat Presensi & Logbook - SIM-MAGANG BPS')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('intern.dashboard') }}" class="hover:text-blue-800 flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Beranda</span>
                </a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Riwayat Logbook</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Riwayat Presensi & Jurnal Logbook</h1>
            <p class="text-xs text-slate-500 mt-0.5">Catatan harian aktivitas dan status verifikasi persetujuan (ACC) Pembimbing BPS</p>
        </div>

        <!-- Filter Form (Responsive 2-col grid on mobile, 4-col on desktop) -->
        <form method="GET" action="{{ route('intern.riwayat') }}" class="grid grid-cols-2 sm:grid-cols-4 gap-2 w-full md:w-auto">
            <select name="month" class="w-full h-10 text-xs font-semibold px-3 rounded-lg border border-slate-300 bg-white shadow-xs focus:ring-1 focus:ring-blue-800 outline-none">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>

            <select name="year" class="w-full h-10 text-xs font-semibold px-3 rounded-lg border border-slate-300 bg-white shadow-xs focus:ring-1 focus:ring-blue-800 outline-none">
                @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>

            <select name="status" class="w-full h-10 text-xs font-semibold px-3 rounded-lg border border-slate-300 bg-white shadow-xs focus:ring-1 focus:ring-blue-800 outline-none">
                <option value="">Semua Status</option>
                <option value="hadir" {{ $status == 'hadir' ? 'selected' : '' }}>Hadir di Kantor</option>
                <option value="tidak_hadir_keterangan" {{ $status == 'tidak_hadir_keterangan' ? 'selected' : '' }}>Izin / Sakit</option>
                <option value="tidak_hadir_tanpa_keterangan" {{ $status == 'tidak_hadir_tanpa_keterangan' ? 'selected' : '' }}>Alpa (Kosong)</option>
            </select>

            <button type="submit" class="w-full h-10 px-4 rounded-lg bg-blue-800 hover:bg-blue-900 text-white text-xs font-semibold transition shadow-xs inline-flex items-center justify-center gap-1.5">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Terapkan</span>
            </button>
        </form>
    </div>

    <!-- Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs text-center">
            <span class="text-[10px] uppercase font-semibold text-slate-400 block">Total Hari</span>
            <span class="text-xl font-bold text-slate-800 font-mono">{{ $stats['total'] }}</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs text-center">
            <span class="text-[10px] uppercase font-semibold text-emerald-700 block">Hadir</span>
            <span class="text-xl font-bold text-emerald-700 font-mono">{{ $stats['hadir'] }}</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs text-center">
            <span class="text-[10px] uppercase font-semibold text-amber-700 block">Izin / Sakit</span>
            <span class="text-xl font-bold text-amber-700 font-mono">{{ $stats['izin_sakit'] }}</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs text-center">
            <span class="text-[10px] uppercase font-semibold text-rose-700 block">Alpa</span>
            <span class="text-xl font-bold text-rose-700 font-mono">{{ $stats['alpa'] }}</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs text-center col-span-2 sm:col-span-1">
            <span class="text-[10px] uppercase font-semibold text-blue-900 block">Tingkat Disiplin</span>
            <span class="text-xl font-bold text-blue-900 font-mono">{{ $stats['attendance_rate'] }}%</span>
        </div>
    </div>

    <!-- Attendance Cards List -->
    <div class="space-y-4">
        @forelse($attendances as $item)
            <div id="item-{{ $item->id }}" class="bg-white rounded-xl p-5 shadow-xs border border-slate-200 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-lg {{ $item->status === 'hadir' ? 'bg-emerald-100 text-emerald-700' : ($item->status === 'tidak_hadir_keterangan' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }} flex items-center justify-center flex-shrink-0">
                            <i data-lucide="{{ $item->status === 'hadir' ? 'check' : ($item->status === 'tidak_hadir_keterangan' ? 'mail' : 'x') }}" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <span>{{ $item->date->translatedFormat('l, d F Y') }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $item->status_badge_class }}">
                                    {{ $item->status_label }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span class="flex items-center gap-1 font-mono text-slate-700">
                                    <i data-lucide="log-in" class="w-3.5 h-3.5 text-blue-700"></i>
                                    <span>Masuk: {{ $item->check_in_time ? substr($item->check_in_time, 0, 5) . ' WIB' : '-' }}</span>
                                </span>

                                @if($item->check_out_time)
                                    <span class="flex items-center gap-1 font-mono text-indigo-700 font-semibold">
                                        <i data-lucide="log-out" class="w-3.5 h-3.5 text-indigo-700"></i>
                                        <span>Pulang: {{ substr($item->check_out_time, 0, 5) }} WIB ({{ $item->work_duration_formatted }})</span>
                                    </span>
                                @endif

                                @if($item->is_late)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        Terlambat {{ $item->late_minutes }}m
                                    </span>
                                @endif

                                @if($item->distance_meters !== null)
                                    <span class="flex items-center gap-1 text-emerald-700 font-mono">
                                        <i data-lucide="map-pin" class="w-3 h-3"></i>
                                        <span>{{ round($item->distance_meters) }} m dari BPS</span>
                                    </span>
                                @endif
                                <span class="text-slate-600 font-medium">({{ $item->sub_status_label }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Status Badge -->
                    <div class="flex items-center space-x-2">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold border flex items-center gap-1.5 {{ $item->approval_badge_class }}">
                            @if($item->approval_status === 'approved')
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-700"></i>
                                <span>Disetujui (ACC)</span>
                            @elseif($item->approval_status === 'rejected')
                                <i data-lucide="x" class="w-3.5 h-3.5 text-rose-700"></i>
                                <span>Ditolak</span>
                            @else
                                <i data-lucide="hourglass" class="w-3.5 h-3.5 text-amber-700"></i>
                                <span>Menunggu ACC</span>
                            @endif
                        </span>
                    </div>
                </div>

                @if($item->is_late && $item->late_reason)
                    <div class="p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                        <span class="font-bold">Alasan Keterlambatan:</span> {{ $item->late_reason }}
                    </div>
                @endif

                <!-- Logbook Detail Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                    <!-- Uraian Aktivitas -->
                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="font-semibold text-slate-700 block mb-1 flex items-center gap-1.5">
                            <i data-lucide="list-todo" class="w-3.5 h-3.5 text-blue-700"></i>
                            <span>Aktivitas / Tugas:</span>
                        </span>
                        <p class="text-slate-600 leading-relaxed">{{ $item->activity_description ?? '-' }}</p>
                    </div>

                    <!-- Pembelajaran yang Diperoleh -->
                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="font-semibold text-slate-700 block mb-1 flex items-center gap-1.5">
                            <i data-lucide="lightbulb" class="w-3.5 h-3.5 text-amber-600"></i>
                            <span>Pembelajaran yang Diperoleh:</span>
                        </span>
                        <p class="text-slate-600 leading-relaxed">{{ $item->learning_obtained ?? '-' }}</p>
                    </div>

                    <!-- Kendala yang Dialami -->
                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="font-semibold text-slate-700 block mb-1 flex items-center gap-1.5">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-rose-600"></i>
                            <span>Kendala & Solusi:</span>
                        </span>
                        <p class="text-slate-600 leading-relaxed">{{ $item->constraints_encountered ?? '-' }}</p>
                    </div>
                </div>

                <!-- Lampiran & Feedback Pembimbing -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-1 text-xs">
                    <div class="flex flex-wrap items-center gap-2">
                        @if($item->photo)
                            <a href="{{ asset('storage/' . $item->photo) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 h-8 px-3 rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-medium transition">
                                <i data-lucide="camera" class="w-3.5 h-3.5 text-blue-700"></i>
                                <span>Selfie Masuk</span>
                            </a>
                        @endif

                        @if($item->check_out_photo)
                            <a href="{{ asset('storage/' . $item->check_out_photo) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 h-8 px-3 rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-medium transition">
                                <i data-lucide="camera" class="w-3.5 h-3.5 text-indigo-700"></i>
                                <span>Selfie Pulang</span>
                            </a>
                        @endif

                        @if($item->attachment_file)
                            <a href="{{ route('attendance.attachment', $item->id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 h-8 px-3 rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-medium transition">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-amber-700"></i>
                                <span>Surat Bukti (Privat)</span>
                            </a>
                        @endif
                    </div>

                    @if($item->approval_notes)
                        <div class="p-2.5 rounded-md bg-amber-50 border border-amber-200 text-amber-900 max-w-lg">
                            <span class="font-bold block mb-0.5 text-[11px]">Catatan Pembimbing:</span>
                            <span class="text-amber-800 leading-relaxed">{{ $item->approval_notes }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl p-10 text-center border border-slate-200 text-slate-400">
                <i data-lucide="folder-open" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                <p class="text-sm font-semibold text-slate-700">Tidak ada data presensi pada periode yang dipilih.</p>
                <p class="text-xs text-slate-400 mt-1">Silakan sesuaikan pilihan filter bulan atau tahun di atas.</p>
            </div>
        @endforelse

        <!-- Pagination -->
        <div class="pt-2">
            {{ $attendances->links() }}
        </div>
    </div>

</div>
@endsection
