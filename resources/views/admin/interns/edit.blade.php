@extends('layouts.app')

@section('title', 'Perbarui Data Peserta - ' . $intern->name . ' - SIM-MAGANG BPS')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Breadcrumbs -->
    <div>
        <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-800 flex items-center gap-1">
                <i data-lucide="home" class="w-3.5 h-3.5"></i>
                <span>Dashboard</span>
            </a>
            <span>/</span>
            <a href="{{ route('admin.interns.index') }}" class="hover:text-blue-800">Peserta Magang</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Edit Data</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Perbarui Data Peserta Magang</h1>
        <p class="text-xs text-slate-500 mt-0.5">Edit informasi akun, penempatan seksi BPS Karanganyar, atau perbarui kata sandi akun.</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl p-6 sm:p-8 shadow-xs border border-slate-200">
        <form action="{{ route('admin.interns.update', $intern->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Bagian 1: Akun Akses Login -->
            <div>
                <h3 class="text-xs font-semibold text-blue-800 uppercase tracking-wider mb-3.5 flex items-center gap-2">
                    <i data-lucide="key" class="w-4 h-4 text-blue-800"></i>
                    <span>1. Informasi Akun Login</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-medium text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap Peserta <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $intern->name) }}" required
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-medium text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email', $intern->email) }}" required
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-medium text-slate-700 uppercase tracking-wider mb-1.5">Ubah Kata Sandi (Kosongkan jika tidak diganti)</label>
                        <input type="password" name="password" id="password" placeholder="••••••••"
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-medium text-slate-700 uppercase tracking-wider mb-1.5">Nomor WhatsApp / HP</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $intern->phone) }}" placeholder="08xxxxxxxxxx"
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none font-mono">
                    </div>
                </div>
            </div>

            <!-- Bagian 2: Data Akademik -->
            <div class="pt-5 border-t border-slate-100">
                <h3 class="text-xs font-semibold text-blue-800 uppercase tracking-wider mb-3.5 flex items-center gap-2">
                    <i data-lucide="graduation-cap" class="w-4 h-4 text-blue-800"></i>
                    <span>2. Data Akademik & Institusi Pendidikan</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="nim_nisn" class="block text-xs font-medium text-slate-700 uppercase tracking-wider mb-1.5">NIM / NISN</label>
                        <input type="text" name="nim_nisn" id="nim_nisn" value="{{ old('nim_nisn', $intern->internProfile->nim_nisn ?? '') }}"
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none font-mono">
                    </div>

                    <div>
                        <label for="institution" class="block text-xs font-medium text-slate-700 uppercase tracking-wider mb-1.5">Asal Kampus / Sekolah <span class="text-rose-500">*</span></label>
                        <input type="text" name="institution" id="institution" value="{{ old('institution', $intern->internProfile->institution ?? '') }}" required
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                    </div>

                    <div>
                        <label for="major" class="block text-xs font-medium text-slate-700 uppercase tracking-wider mb-1.5">Jurusan / Program Studi <span class="text-rose-500">*</span></label>
                        <input type="text" name="major" id="major" value="{{ old('major', $intern->internProfile->major ?? '') }}" required
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                    </div>
                </div>
            </div>

            <!-- Bagian 3: Penempatan & Periode Magang di BPS -->
            <div class="pt-5 border-t border-slate-100">
                <h3 class="text-xs font-semibold text-blue-800 uppercase tracking-wider mb-3.5 flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-blue-800"></i>
                    <span>3. Penempatan di BPS Kabupaten Karanganyar</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="division" class="block text-xs font-medium text-slate-700 uppercase tracking-wider mb-1.5">Seksi Penempatan <span class="text-rose-500">*</span></label>
                        <select name="division" id="division" required
                                class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                            @foreach($divisions as $div)
                                <option value="{{ $div }}" {{ old('division', $intern->internProfile->division ?? '') == $div ? 'selected' : '' }}>{{ $div }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="mentor_name" class="block text-xs font-medium text-slate-700 uppercase tracking-wider mb-1.5">Nama Pembimbing Lapangan BPS</label>
                        <input type="text" name="mentor_name" id="mentor_name" value="{{ old('mentor_name', $intern->internProfile->mentor_name ?? '') }}"
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                    </div>

                    <!-- Durasi Magang Selector (1 - 6 Bulan) -->
                    <div class="sm:col-span-2">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-medium text-slate-700 uppercase tracking-wider">
                                Durasi Magang (Pilihan Bulan) <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-500" id="durationHint">Pilih opsi 1 s/d 6 bulan untuk otomatis menghitung tanggal selesai</span>
                        </div>
                        
                        <input type="hidden" name="duration_months" id="duration_months" value="{{ old('duration_months', $intern->internProfile->duration_months_display ?? 3) }}">
                        
                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                            @foreach([1, 2, 3, 4, 5, 6] as $m)
                                <button type="button" onclick="selectDuration({{ $m }})"
                                        id="durationBtn_{{ $m }}"
                                        class="duration-btn h-10 rounded-lg text-xs sm:text-sm font-semibold transition border flex items-center justify-center gap-1 {{ (old('duration_months', $intern->internProfile->duration_months_display ?? 3) == $m) ? 'bg-blue-800 text-white border-blue-800 shadow-xs ring-2 ring-blue-700/20' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                                    <span>{{ $m }} Bulan</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="start_date" class="block text-xs font-medium text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Mulai Magang <span class="text-rose-500">*</span></label>
                        <input type="date" name="start_date" id="start_date"
                               value="{{ old('start_date', $intern->internProfile && $intern->internProfile->start_date ? $intern->internProfile->start_date->format('Y-m-d') : '') }}" required
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                    </div>

                    <div>
                        <label for="end_date" class="block text-xs font-medium text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Selesai Magang <span class="text-rose-500">*</span></label>
                        <input type="date" name="end_date" id="end_date"
                               value="{{ old('end_date', $intern->internProfile && $intern->internProfile->end_date ? $intern->internProfile->end_date->format('Y-m-d') : '') }}" required
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                    </div>

                    <!-- Summary Info Box -->
                    <div class="sm:col-span-2">
                        <div id="durationSummary" class="p-3 rounded-lg bg-blue-50/70 border border-blue-200 text-xs text-blue-900 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="calendar-range" class="w-4 h-4 text-blue-700 flex-shrink-0"></i>
                                <span id="durationSummaryText">Estimasi Masa Magang</span>
                            </div>
                            <span class="text-[11px] text-blue-800 font-mono hidden sm:inline" id="durationDateRangeText"></span>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="block text-xs font-medium text-slate-700 uppercase tracking-wider mb-1.5">Alamat Domisili Selama Magang</label>
                        <textarea name="address" id="address" rows="2"
                                  class="w-full text-xs sm:text-sm p-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">{{ old('address', $intern->internProfile->address ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-5 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('admin.interns.index') }}" class="h-10 px-4 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs sm:text-sm font-medium transition inline-flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Kembali</span>
                </a>
                <button type="submit" class="h-10 px-5 rounded-lg bg-blue-800 hover:bg-blue-900 text-white text-xs sm:text-sm font-medium transition shadow-xs flex items-center space-x-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
    function selectDuration(months) {
        document.getElementById('duration_months').value = months;
        
        // Update button active state
        [1, 2, 3, 4, 5, 6].forEach(m => {
            const btn = document.getElementById('durationBtn_' + m);
            if (!btn) return;
            if (m === months) {
                btn.className = 'duration-btn h-10 rounded-lg text-xs sm:text-sm font-semibold transition border flex items-center justify-center gap-1 bg-blue-800 text-white border-blue-800 shadow-xs ring-2 ring-blue-700/20';
            } else {
                btn.className = 'duration-btn h-10 rounded-lg text-xs sm:text-sm font-semibold transition border flex items-center justify-center gap-1 bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200';
            }
        });

        // Recalculate end date
        const startDateVal = document.getElementById('start_date').value;
        if (startDateVal) {
            const newEndDate = calculateEndDate(startDateVal, months);
            if (newEndDate) {
                document.getElementById('end_date').value = newEndDate;
            }
        }
        updateDurationSummary();
    }

    function calculateEndDate(dateStr, months) {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        if (parts.length !== 3) return '';
        const y = parseInt(parts[0], 10);
        const m = parseInt(parts[1], 10) - 1;
        const d = parseInt(parts[2], 10);
        
        const date = new Date(y, m, d);
        date.setMonth(date.getMonth() + Number(months));
        date.setDate(date.getDate() - 1);
        
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    function updateDurationSummary() {
        const startVal = document.getElementById('start_date').value;
        const endVal = document.getElementById('end_date').value;
        const durationMonths = parseInt(document.getElementById('duration_months').value) || 0;
        
        const summaryText = document.getElementById('durationSummaryText');
        const rangeText = document.getElementById('durationDateRangeText');
        if (!summaryText || !rangeText) return;
        
        if (startVal && endVal) {
            const d1 = new Date(startVal);
            const d2 = new Date(endVal);
            const diffTime = d2.getTime() - d1.getTime();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            if (diffDays > 0) {
                const monthsLabel = durationMonths > 0 ? `${durationMonths} Bulan` : `${Math.max(1, Math.round(diffDays / 30))} Bulan`;
                summaryText.innerHTML = `Estimasi Masa Magang: <strong>${monthsLabel}</strong> (~${diffDays} Hari Kalender)`;
                rangeText.textContent = `${startVal} s/d ${endVal}`;
            } else {
                summaryText.innerHTML = `<span class="text-rose-600 font-semibold">Perhatian: Tanggal selesai harus lebih akhir dari tanggal mulai</span>`;
                rangeText.textContent = '';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const initialDuration = parseInt(document.getElementById('duration_months').value) || 3;
        // Don't overwrite end_date initially on edit unless empty, just update styling & summary
        [1, 2, 3, 4, 5, 6].forEach(m => {
            const btn = document.getElementById('durationBtn_' + m);
            if (!btn) return;
            if (m === initialDuration) {
                btn.className = 'duration-btn h-10 rounded-lg text-xs sm:text-sm font-semibold transition border flex items-center justify-center gap-1 bg-blue-800 text-white border-blue-800 shadow-xs ring-2 ring-blue-700/20';
            } else {
                btn.className = 'duration-btn h-10 rounded-lg text-xs sm:text-sm font-semibold transition border flex items-center justify-center gap-1 bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200';
            }
        });
        updateDurationSummary();
        
        document.getElementById('start_date').addEventListener('change', function() {
            const curMonths = parseInt(document.getElementById('duration_months').value) || 3;
            const newEndDate = calculateEndDate(this.value, curMonths);
            if (newEndDate) {
                document.getElementById('end_date').value = newEndDate;
            }
            updateDurationSummary();
        });
        
        document.getElementById('end_date').addEventListener('change', function() {
            updateDurationSummary();
        });
    });
</script>
@endpush
@endsection
