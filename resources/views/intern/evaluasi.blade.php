@extends('layouts.app')

@section('title', 'Penilaian & Evaluasi Akhir Magang - SIM-MAGANG BPS')

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
                <span class="text-slate-800 font-semibold">Evaluasi Akhir Magang</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Lembar Penilaian & Evaluasi Akhir</h1>
            <p class="text-xs text-slate-500 mt-0.5">Badan Pusat Statistik Kabupaten Karanganyar</p>
        </div>

        @if($evaluation)
        <a href="{{ route('admin.evaluasi.print', $user->id) }}" target="_blank"
           class="h-10 px-4 rounded-lg bg-blue-800 hover:bg-blue-900 text-white text-xs font-semibold shadow-2xs transition inline-flex items-center gap-2">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Cetak Lembar Nilai Resmi</span>
        </a>
        @endif
    </div>

    @if($evaluation)
    <!-- Hero Score Card -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#001433] via-[#002855] to-[#004080] p-6 sm:p-8 text-white shadow-md">
        <!-- Background Ambient Glow -->
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-blue-400/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-2 max-w-lg">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold">
                    <i data-lucide="award" class="w-3.5 h-3.5"></i>
                    <span>Penilaian Akhir Resmi Terbit</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">
                    Selamat, {{ $user->name }}!
                </h2>
                <p class="text-xs sm:text-sm text-blue-100/80 leading-relaxed">
                    Evaluasi masa praktik kerja Anda di Badan Pusat Statistik Kabupaten Karanganyar telah disahkan oleh pembimbing lapangan.
                </p>
                <div class="pt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-blue-200/90 font-mono">
                    <span>Tanggal Terbit: {{ $evaluation->evaluation_date->translatedFormat('d F Y') }}</span>
                    <span>•</span>
                    <span>Pembimbing: {{ $evaluation->evaluator->name ?? 'Pembimbing BPS' }}</span>
                </div>
            </div>

            <!-- Score Showcase Badge -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 p-5 sm:p-6 text-center min-w-[170px] shadow-lg">
                <span class="text-[11px] font-bold text-blue-200 uppercase tracking-wider block">Nilai Akhir</span>
                <span class="text-4xl sm:text-5xl font-extrabold text-white font-mono block my-1">
                    {{ number_format($evaluation->final_score, 1) }}
                </span>
                <div class="inline-flex px-3 py-1 rounded-full text-xs font-bold border mt-1 {{ $evaluation->predicate_badge_class }}">
                    {{ $evaluation->predicate }}
                </div>
            </div>
        </div>
    </div>

    <!-- 5 Rubric Breakdowns -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-6 space-y-5">
        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
            Rincian Capaian 5 Dimensi Penilaian
        </h2>

        <div class="space-y-4 text-xs">
            <!-- 1. Kedisiplinan -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between font-semibold">
                    <span class="text-slate-800">1. Kedisiplinan & Ketepatan Waktu (Bobot 20%)</span>
                    <span class="font-mono font-bold text-blue-900">{{ number_format($evaluation->discipline_score, 1) }} / 100</span>
                </div>
                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-blue-700 rounded-full" style="width: {{ $evaluation->discipline_score }}%;"></div>
                </div>
            </div>

            <!-- 2. Tanggung Jawab -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between font-semibold">
                    <span class="text-slate-800">2. Tanggung Jawab & Integritas Kerja (Bobot 20%)</span>
                    <span class="font-mono font-bold text-blue-900">{{ number_format($evaluation->responsibility_score, 1) }} / 100</span>
                </div>
                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-blue-700 rounded-full" style="width: {{ $evaluation->responsibility_score }}%;"></div>
                </div>
            </div>

            <!-- 3. Inisiatif -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between font-semibold">
                    <span class="text-slate-800">3. Inisiatif & Kreativitas (Bobot 20%)</span>
                    <span class="font-mono font-bold text-blue-900">{{ number_format($evaluation->initiative_score, 1) }} / 100</span>
                </div>
                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-blue-700 rounded-full" style="width: {{ $evaluation->initiative_score }}%;"></div>
                </div>
            </div>

            <!-- 4. Kerjasama Tim -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between font-semibold">
                    <span class="text-slate-800">4. Kerjasama Tim & Komunikasi (Bobot 20%)</span>
                    <span class="font-mono font-bold text-blue-900">{{ number_format($evaluation->teamwork_score, 1) }} / 100</span>
                </div>
                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-blue-700 rounded-full" style="width: {{ $evaluation->teamwork_score }}%;"></div>
                </div>
            </div>

            <!-- 5. Kualitas Kerja -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between font-semibold">
                    <span class="text-slate-800">5. Kualitas Hasil Kerja & Laporan Akhir (Bobot 20%)</span>
                    <span class="font-mono font-bold text-blue-900">{{ number_format($evaluation->work_quality_score, 1) }} / 100</span>
                </div>
                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-blue-700 rounded-full" style="width: {{ $evaluation->work_quality_score }}%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Catatan & Ulasan Pembimbing -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-6 space-y-3">
        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
            Ulasan & Masukan Konstruktif Pembimbing Lapangan BPS
        </h3>
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 italic leading-relaxed">
            "{{ $evaluation->notes ?: 'Peserta magang telah menyelesaikan program dengan baik, disiplin, dan menunjukkan dedikasi yang tinggi selama bertugas di Badan Pusat Statistik Kabupaten Karanganyar.' }}"
        </div>
    </div>
    @else
    <!-- Pending State Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-8 text-center max-w-lg mx-auto space-y-4">
        <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 mx-auto flex items-center justify-center">
            <i data-lucide="hourglass" class="w-7 h-7"></i>
        </div>
        <div class="space-y-1.5">
            <h2 class="text-lg font-bold text-slate-900">Penilaian Akhir Magang Belum Diterbitkan</h2>
            <p class="text-xs text-slate-500 leading-relaxed">
                Pembimbing lapangan BPS Karanganyar belum menginput lembar evaluasi akhir Anda. Formulir penilaian biasanya diterbitkan menjelang atau setelah berakhirnya periode masa magang.
            </p>
        </div>

        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-left text-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-slate-500">Periode Magang:</span>
                <span class="font-semibold text-slate-800 font-mono">
                    {{ $user->internProfile->start_date?->translatedFormat('d M Y') }} - {{ $user->internProfile->end_date?->translatedFormat('d M Y') }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500">Tingkat Kehadiran Saat Ini:</span>
                <span class="font-bold text-blue-900 font-mono">{{ $stats['attendance_rate'] }}% ({{ $stats['hadir'] }} Hari Hadir)</span>
            </div>
        </div>

        <a href="{{ route('intern.dashboard') }}" class="h-10 px-5 rounded-lg bg-blue-800 hover:bg-blue-900 text-white text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Beranda</span>
        </a>
    </div>
    @endif

</div>
@endsection
