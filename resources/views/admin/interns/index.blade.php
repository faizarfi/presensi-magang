@extends('layouts.app')

@section('title', 'Manajemen Peserta Magang - SIM-MAGANG BPS')

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
                <span class="text-slate-800 font-semibold">Data Mahasiswa Magang</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Peserta Magang BPS</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola akun peserta, penempatan seksi BPS, data pembimbing lapangan, dan masa berlaku magang.</p>
        </div>

        <a href="{{ route('admin.interns.create') }}" class="h-10 px-4 rounded-lg bg-blue-800 hover:bg-blue-900 text-white font-medium text-xs sm:text-sm transition shadow-xs flex items-center gap-2 self-start sm:self-auto">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Tambah Peserta Baru</span>
        </a>
    </div>

    <!-- Intern Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($interns as $intern)
            @php
                $profile = $intern->internProfile;
                $totalHadir = $intern->attendances->where('status', 'hadir')->count();
                $totalAtt = $intern->attendances->count();
                $rate = $totalAtt > 0 ? round(($totalHadir / $totalAtt) * 100, 1) : 0;
            @endphp
            <div class="bg-white rounded-xl p-5 shadow-xs border border-slate-200 hover:border-slate-300 transition flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-11 h-11 rounded-full bg-blue-100 text-blue-900 border border-blue-200 flex items-center justify-center font-bold text-sm overflow-hidden flex-shrink-0">
                                @if($intern->avatar)
                                    <img src="{{ asset('storage/' . $intern->avatar) }}" alt="{{ $intern->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($intern->name, 0, 2)) }}
                                @endif
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900 text-sm hover:text-blue-800 transition">
                                    <a href="{{ route('admin.interns.show', $intern->id) }}">{{ $intern->name }}</a>
                                </h3>
                                <p class="text-xs text-slate-500 font-mono">{{ $profile->nim_nisn ?? 'NIM belum diatur' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap">
                                {{ $profile->duration_label ?? '3 Bulan' }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-blue-800 border border-blue-200 whitespace-nowrap font-mono">
                                {{ $rate }}% Hadir
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Kampus/Sekolah:</span>
                            <span class="font-medium text-slate-800 max-w-[170px] truncate text-right">{{ $profile->institution ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Seksi BPS:</span>
                            <span class="font-semibold text-blue-800 max-w-[170px] truncate text-right">{{ $profile->division ?? 'Subbagian Umum' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Pembimbing:</span>
                            <span class="font-medium text-slate-800 max-w-[170px] truncate text-right">{{ $profile->mentor_name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Periode ({{ $profile->duration_label ?? '3 Bulan' }}):</span>
                            <span class="text-slate-600 font-mono text-[11px]">
                                {{ $profile && $profile->start_date ? $profile->start_date->format('d/m/y') : '-' }} s/d {{ $profile && $profile->end_date ? $profile->end_date->format('d/m/y') : '-' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-1 border-t border-slate-50">
                            <span class="text-slate-500">Kunci HP (Anti-Titip):</span>
                            @if($intern->device_id)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200" title="{{ $intern->device_name }} • Terkunci sejak {{ $intern->device_locked_at?->format('d/m/Y') }}">
                                    <i data-lucide="smartphone" class="w-3 h-3"></i>
                                    <span class="max-w-[110px] truncate">{{ $intern->device_name ?? 'Terkunci' }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 bg-slate-100 px-2 py-0.5 rounded">
                                    <i data-lucide="unlock" class="w-3 h-3"></i> Belum Terkunci
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="{{ route('admin.interns.show', $intern->id) }}" class="text-xs font-semibold text-blue-800 hover:text-blue-900 inline-flex items-center gap-1">
                        <span>Lihat Logbook</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>

                    <div class="flex items-center space-x-1">
                        @if($intern->device_id)
                        <form action="{{ route('admin.interns.reset-device', $intern->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" onclick="return confirm('Reset kunci perangkat (HP) untuk {{ addslashes($intern->name) }}? Peserta akan dapat mendaftarkan HP barunya saat login.')"
                                    class="h-8 px-2 rounded-lg border border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-800 text-[11px] font-semibold flex items-center gap-1 transition"
                                    title="Reset Kunci Perangkat (HP)">
                                <i data-lucide="smartphone" class="w-3.5 h-3.5 text-amber-600"></i>
                                <span>Reset HP</span>
                            </button>
                        </form>
                        @endif

                        <a href="{{ route('admin.interns.edit', $intern->id) }}" class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-600 flex items-center justify-center transition" title="Edit Data">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </a>

                        <form id="deleteInternForm-{{ $intern->id }}" action="{{ route('admin.interns.destroy', $intern->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmDeleteIntern({{ $intern->id }}, '{{ addslashes($intern->name) }}')"
                                    class="h-8 w-8 rounded-lg border border-rose-200 hover:bg-rose-50 text-rose-600 flex items-center justify-center transition" title="Hapus">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-xl p-12 text-center border border-slate-200 text-slate-400">
                <i data-lucide="users" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                <p class="text-sm font-semibold text-slate-700">Belum ada data peserta magang yang terdaftar.</p>
                <p class="text-xs text-slate-500 mt-1">Klik tombol "Tambah Peserta Baru" untuk mendaftarkan akun magang.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $interns->links() }}
    </div>

</div>

@push('scripts')
<script>
    function confirmDeleteIntern(id, name) {
        Swal.fire({
            title: 'Hapus Peserta Magang?',
            text: `Apakah Anda yakin ingin menghapus data peserta "${name}"? Seluruh riwayat presensi dan berkas terkait akan terhapus.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Hapus Data',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteInternForm-' + id).submit();
            }
        });
    }
</script>
@endpush
@endsection
