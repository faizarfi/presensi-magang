@extends('layouts.app')

@section('title', 'Manajemen Hari Libur & Tanggal Merah - SIM-MAGANG BPS')

@section('content')
<div class="space-y-6" x-data="{ addModalOpen: false }">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-800 flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Hari Libur</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Hari Libur & Tanggal Merah</h1>
            <p class="text-xs text-slate-500 mt-0.5">Daftar hari libur nasional Indonesia & kebijakan hari libur BPS Karanganyar.</p>
        </div>

        <button type="button" @click="addModalOpen = true; $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); })"
                class="h-10 px-4 rounded-lg bg-blue-800 hover:bg-blue-900 text-white font-medium text-xs sm:text-sm transition shadow-xs flex items-center space-x-2 self-start sm:self-auto">
            <i data-lucide="calendar-plus" class="w-4 h-4"></i>
            <span>Tambah Tanggal Merah</span>
        </button>
    </div>

    <!-- Info Akhir Pekan Banner -->
    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 flex items-start space-x-3 text-xs">
        <i data-lucide="info" class="w-5 h-5 text-blue-800 shrink-0 mt-0.5"></i>
        <div class="leading-relaxed">
            <strong>Informasi Kebijakan Hari Kerja:</strong> Hari <strong>Sabtu dan Minggu</strong> secara otomatis diatur sebagai hari libur akhir pekan. Peserta magang tidak wajib melakukan presensi dan tidak akan dihitung alpa pada akhir pekan serta daftar tanggal merah di bawah ini.
        </div>
    </div>

    <!-- Holidays List Card -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="calendar" class="w-4 h-4 text-blue-800"></i>
                <h2 class="text-sm font-bold text-slate-900">
                    Daftar Tanggal Merah Tahun {{ $year }} ({{ $holidays->count() }} Hari)
                </h2>
            </div>

            <form method="GET" action="{{ route('admin.holidays.index') }}" class="flex items-center space-x-2">
                <select name="year" onchange="this.form.submit()" class="h-9 text-xs font-medium px-3 rounded-lg border border-slate-300 bg-white focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                    @for($y = now()->year - 1; $y <= now()->year + 2; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-wider font-semibold text-[11px]">
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Tanggal Libur</th>
                        <th class="py-3.5 px-4">Hari</th>
                        <th class="py-3.5 px-4">Nama Hari Libur / Perayaan</th>
                        <th class="py-3.5 px-4">Keterangan</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($holidays as $idx => $holiday)
                        @php
                            $isPast = $holiday->holiday_date < now()->toDateString();
                            $isToday = $holiday->holiday_date == now()->toDateString();
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition {{ $isToday ? 'bg-blue-50/50 font-semibold' : '' }}">
                            <td class="py-3.5 px-4 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-4 whitespace-nowrap font-semibold text-slate-800">
                                {{ $holiday->holiday_date->translatedFormat('d F Y') }}
                                @if($isToday)
                                    <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] bg-blue-800 text-white font-medium">Hari Ini</span>
                                @elseif($isPast)
                                    <span class="ml-1.5 text-[10px] text-slate-400 font-normal">(Lewat)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-600">
                                {{ $holiday->holiday_date->translatedFormat('l') }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-900">
                                {{ $holiday->name }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $holiday->description ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <form id="deleteHolidayForm-{{ $holiday->id }}" action="{{ route('admin.holidays.destroy', $holiday->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDeleteHoliday({{ $holiday->id }}, '{{ addslashes($holiday->name) }}')"
                                            class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-rose-50 hover:border-rose-200 text-slate-400 hover:text-rose-600 inline-flex items-center justify-center transition" title="Hapus Tanggal Merah">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                                    <p class="font-medium text-slate-600">Belum ada hari libur yang terdaftar pada tahun {{ $year }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Hari Libur -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4 relative border border-slate-200" @click.away="addModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar-plus" class="w-5 h-5 text-blue-800"></i>
                    <h3 class="text-base font-bold text-slate-900">Tambah Tanggal Merah Baru</h3>
                </div>
                <button type="button" @click="addModalOpen = false" class="w-8 h-8 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('admin.holidays.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label for="holiday_date" class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Libur <span class="text-rose-500">*</span></label>
                    <input type="date" name="holiday_date" id="holiday_date" required
                           class="w-full h-10 text-xs px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                </div>

                <div>
                    <label for="name" class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Hari Libur / Perayaan <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="name" required placeholder="Contoh: Hari Raya Idul Fitri / Cuti Bersama"
                           class="w-full h-10 text-xs px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none">
                </div>

                <div>
                    <label for="description" class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Tambahan (Opsional)</label>
                    <textarea name="description" id="description" rows="2" placeholder="Keterangan tambahan..."
                              class="w-full text-xs p-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" @click="addModalOpen = false" class="h-10 px-4 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="h-10 px-5 rounded-lg bg-blue-800 hover:bg-blue-900 text-white font-medium shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Hari Libur</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function confirmDeleteHoliday(id, name) {
        Swal.fire({
            title: 'Hapus Hari Libur?',
            text: `Apakah Anda yakin ingin menghapus tanggal merah "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteHolidayForm-' + id).submit();
            }
        });
    }
</script>
@endpush
@endsection
