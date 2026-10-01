@extends('layouts.app')

@section('title', 'Formulir Penilaian Magang - ' . $intern->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6"
     x-data="{
        discipline: {{ old('discipline_score', $evaluation->discipline_score ?? $suggestedDiscipline) }},
        responsibility: {{ old('responsibility_score', $evaluation->responsibility_score ?? 85) }},
        initiative: {{ old('initiative_score', $evaluation->initiative_score ?? 80) }},
        teamwork: {{ old('teamwork_score', $evaluation->teamwork_score ?? 85) }},
        workQuality: {{ old('work_quality_score', $evaluation->work_quality_score ?? 85) }},

        get finalScore() {
            let avg = (Number(this.discipline) + Number(this.responsibility) + Number(this.initiative) + Number(this.teamwork) + Number(this.workQuality)) / 5;
            return avg.toFixed(1);
        },

        get predicate() {
            let s = Number(this.finalScore);
            if (s >= 85) return 'A (Sangat Baik)';
            if (s >= 75) return 'B+ (Memuaskan)';
            if (s >= 65) return 'B (Baik)';
            if (s >= 55) return 'C (Cukup)';
            return 'D (Kurang)';
        },

        get predicateColor() {
            let s = Number(this.finalScore);
            if (s >= 85) return 'text-emerald-700 bg-emerald-50 border-emerald-300';
            if (s >= 75) return 'text-blue-700 bg-blue-50 border-blue-300';
            if (s >= 65) return 'text-indigo-700 bg-indigo-50 border-indigo-300';
            if (s >= 55) return 'text-amber-700 bg-amber-50 border-amber-300';
            return 'text-rose-700 bg-rose-50 border-rose-300';
        }
     }">

    <!-- Top Breadcrumb & Title -->
    <div>
        <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
            <a href="{{ route('admin.evaluasi.index') }}" class="hover:text-blue-800 flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Daftar Evaluasi</span>
            </a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Formulir Penilaian</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
            {{ $evaluation ? 'Edit Penilaian Akhir Magang' : 'Formulir Penilaian Akhir Magang' }}
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">Badan Pusat Statistik Kabupaten Karanganyar</p>
    </div>

    <!-- Intern Info & Attendance Telemetry Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-900 font-extrabold text-base flex items-center justify-center shrink-0">
                    {{ strtoupper(substr($intern->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">{{ $intern->name }}</h2>
                    <p class="text-xs text-slate-500 font-mono">{{ $intern->email }} • {{ $intern->internProfile->nim_nisn ?? 'Tanpa NIM' }}</p>
                    <p class="text-xs text-blue-800 font-semibold mt-0.5">
                        {{ $intern->internProfile->institution ?? '-' }} ({{ $intern->internProfile->major ?? '-' }})
                    </p>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Seksi Penempatan</span>
                <span class="text-xs font-bold text-slate-800">{{ $intern->internProfile->division ?? 'BPS Karanganyar' }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">
                    Periode: {{ $intern->internProfile->start_date?->translatedFormat('d M Y') }} - {{ $intern->internProfile->end_date?->translatedFormat('d M Y') }}
                </span>
            </div>
        </div>

        <!-- Telemetri Rekam Jejak Presensi -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1">
            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Tingkat Kehadiran</span>
                <span class="text-base font-extrabold text-blue-900">{{ $stats['attendance_rate'] }}%</span>
                <span class="text-[10px] text-slate-500 block">Saran Disiplin: {{ $suggestedDiscipline }}</span>
            </div>
            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Hari Hadir</span>
                <span class="text-base font-extrabold text-emerald-700">{{ $stats['hadir'] }} Hari</span>
                <span class="text-[10px] text-slate-500 block">dari {{ $stats['total'] }} rekaman</span>
            </div>
            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Keterlambatan</span>
                <span class="text-base font-extrabold {{ $lateCount > 0 ? 'text-amber-700' : 'text-slate-700' }}">{{ $lateCount }} Kali</span>
                <span class="text-[10px] text-slate-500 block">lewat dari 07:30</span>
            </div>
            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Izin / Alpa</span>
                <span class="text-base font-extrabold text-slate-700">{{ $stats['izin_sakit'] }} Izin / {{ $stats['alpa'] }} Alpa</span>
                <span class="text-[10px] text-slate-500 block">terdata sistem</span>
            </div>
        </div>
    </div>

    <!-- Main Score Input Form -->
    <form method="POST" action="{{ route('admin.evaluasi.update', $intern->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Score Preview Real-time Sticky Bar -->
        <div class="p-4 rounded-xl bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 text-white shadow-md flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-200 block">Proyeksi Nilai Akhir Evaluasi</span>
                <h3 class="text-sm font-semibold text-white mt-0.5">Kalkulasi Otomatis Rata-Rata Komponen BPS</h3>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <span class="text-[10px] uppercase tracking-wider text-blue-200 block">Nilai Angka</span>
                    <span class="text-3xl font-extrabold text-white font-mono" x-text="finalScore"></span>
                    <span class="text-xs text-blue-200">/ 100</span>
                </div>
                <div class="px-3.5 py-1.5 rounded-lg border text-xs font-bold" :class="predicateColor" x-text="predicate"></div>
            </div>
        </div>

        <!-- 5 Rubric Criteria Cards -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs divide-y divide-slate-100 p-6 space-y-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                5 Dimensi Penilaian Kinerja Peserta Magang (Bobot @ 20%)
            </h2>

            <!-- Kriteria 1: Kedisiplinan -->
            <div class="pt-4 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <div>
                        <label class="text-xs font-bold text-slate-900 block">1. Kedisiplinan & Ketepatan Waktu (Discipline & Punctuality)</label>
                        <span class="text-[11px] text-slate-500 leading-snug">Ketaatan jam kerja, kepatuhan presensi masuk/pulang kantor BPS, dan tata tertib instansi.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" name="discipline_score" min="0" max="100" step="0.5"
                               x-model="discipline" required
                               class="w-20 h-9 px-2.5 text-center text-xs font-bold font-mono rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-800 outline-none text-blue-900">
                        <span class="text-xs font-semibold text-slate-400">/ 100</span>
                    </div>
                </div>
                <input type="range" min="0" max="100" step="1" x-model="discipline" class="w-full accent-blue-800">
            </div>

            <!-- Kriteria 2: Tanggung Jawab & Integritas -->
            <div class="pt-4 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <div>
                        <label class="text-xs font-bold text-slate-900 block">2. Tanggung Jawab & Integritas (Responsibility & Integrity)</label>
                        <span class="text-[11px] text-slate-500 leading-snug">Komitmen menyelesaikan tugas statistik tepat waktu, menjaga kerahasiaan data BPS, dan kejujuran.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" name="responsibility_score" min="0" max="100" step="0.5"
                               x-model="responsibility" required
                               class="w-20 h-9 px-2.5 text-center text-xs font-bold font-mono rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-800 outline-none text-blue-900">
                        <span class="text-xs font-semibold text-slate-400">/ 100</span>
                    </div>
                </div>
                <input type="range" min="0" max="100" step="1" x-model="responsibility" class="w-full accent-blue-800">
            </div>

            <!-- Kriteria 3: Inisiatif & Kreativitas -->
            <div class="pt-4 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <div>
                        <label class="text-xs font-bold text-slate-900 block">3. Inisiatif & Kreativitas (Initiative & Creativity)</label>
                        <span class="text-[11px] text-slate-500 leading-snug">Keaktifan bertanya, kemampuan memecahkan masalah, dan memberikan ide perbaikan dalam tim.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" name="initiative_score" min="0" max="100" step="0.5"
                               x-model="initiative" required
                               class="w-20 h-9 px-2.5 text-center text-xs font-bold font-mono rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-800 outline-none text-blue-900">
                        <span class="text-xs font-semibold text-slate-400">/ 100</span>
                    </div>
                </div>
                <input type="range" min="0" max="100" step="1" x-model="initiative" class="w-full accent-blue-800">
            </div>

            <!-- Kriteria 4: Kerjasama Tim & Komunikasi -->
            <div class="pt-4 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <div>
                        <label class="text-xs font-bold text-slate-900 block">4. Kerjasama Tim & Komunikasi (Teamwork & Communication)</label>
                        <span class="text-[11px] text-slate-500 leading-snug">Kemampuan berkolaborasi dengan pegawai BPS dan sesama peserta, etika bertutur kata, dan keramahan.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" name="teamwork_score" min="0" max="100" step="0.5"
                               x-model="teamwork" required
                               class="w-20 h-9 px-2.5 text-center text-xs font-bold font-mono rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-800 outline-none text-blue-900">
                        <span class="text-xs font-semibold text-slate-400">/ 100</span>
                    </div>
                </div>
                <input type="range" min="0" max="100" step="1" x-model="teamwork" class="w-full accent-blue-800">
            </div>

            <!-- Kriteria 5: Kualitas Hasil Kerja & Laporan -->
            <div class="pt-4 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <div>
                        <label class="text-xs font-bold text-slate-900 block">5. Kualitas Hasil Kerja & Laporan Akhir (Work Quality & Output)</label>
                        <span class="text-[11px] text-slate-500 leading-snug">Kerapian data olahan, akurasi input statistik, ketelitian, dan kelengkapan laporan akhir magang.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" name="work_quality_score" min="0" max="100" step="0.5"
                               x-model="workQuality" required
                               class="w-20 h-9 px-2.5 text-center text-xs font-bold font-mono rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-800 outline-none text-blue-900">
                        <span class="text-xs font-semibold text-slate-400">/ 100</span>
                    </div>
                </div>
                <input type="range" min="0" max="100" step="1" x-model="workQuality" class="w-full accent-blue-800">
            </div>
        </div>

        <!-- Tanggal Evaluasi & Catatan Pembimbing -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-6 space-y-4">
            <div>
                <label for="evaluation_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Tanggal Penilaian Resmi
                </label>
                <input type="date" name="evaluation_date" id="evaluation_date" required
                       value="{{ old('evaluation_date', $evaluation?->evaluation_date?->toDateString() ?? date('Y-m-d')) }}"
                       class="h-10 px-3 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-800 outline-none">
            </div>

            <div>
                <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Ulasan & Masukan Konstruktif Pembimbing BPS
                </label>
                <textarea name="notes" id="notes" rows="4"
                          placeholder="Tuliskan catatan apresiasi, kekuatan peserta, atau hal-hal yang perlu ditingkatkan untuk masa depan akademik/kariernya..."
                          class="w-full p-3 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-800 outline-none leading-relaxed">{{ old('notes', $evaluation->notes ?? '') }}</textarea>
                <span class="text-[11px] text-slate-400 block mt-1">Catatan ini akan dicetak pada lembar evaluasi resmi yang diserahkan ke universitas/sekolah.</span>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="flex items-center justify-end space-x-3 pt-2">
            <a href="{{ route('admin.evaluasi.index') }}"
               class="h-10 px-4 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit"
                    class="h-10 px-5 rounded-lg bg-blue-800 hover:bg-blue-900 text-white text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Simpan Lembar Penilaian</span>
            </button>
        </div>
    </form>

</div>
@endsection
