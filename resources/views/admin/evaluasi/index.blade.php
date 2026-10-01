@extends('layouts.app')

@section('title', 'Penilaian & Evaluasi Akhir Magang - BPS Karanganyar')

@section('content')
<div class="space-y-6">

    <!-- Top Breadcrumb & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-800 flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Penilaian Akhir Magang</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Evaluasi & Penilaian Akhir Magang</h1>
            <p class="text-xs text-slate-500 mt-0.5">Lembar Penilaian Resmi Peserta Magang BPS Kabupaten Karanganyar</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-700 shadow-2xs">
                <i data-lucide="award" class="w-4 h-4 text-amber-500"></i>
                <span>Ternilai: <strong>{{ $evaluatedCount }}</strong> / {{ $totalInterns }} Peserta</span>
            </span>
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">Total Peserta Magang</span>
                <span class="text-xl font-bold text-slate-900">{{ $totalInterns }}</span>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">Sudah Dinilai (Selesai)</span>
                <span class="text-xl font-bold text-emerald-700">{{ $evaluatedCount }}</span>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">Menunggu Penilaian</span>
                <span class="text-xl font-bold text-amber-700">{{ $pendingCount }}</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs">
        <form method="GET" action="{{ route('admin.evaluasi.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Cari nama peserta, asal kampus, atau seksi/bidang..."
                    class="w-full h-10 pl-9 pr-3 text-xs rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-800/20 focus:border-blue-800 outline-none">
            </div>

            <div class="flex items-center gap-2">
                <select name="status" class="h-10 px-3 text-xs rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-800/20 focus:border-blue-800 outline-none">
                    <option value="">Semua Status Evaluasi</option>
                    <option value="evaluated" {{ $status === 'evaluated' ? 'selected' : '' }}>Sudah Dinilai</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Belum Dinilai</option>
                </select>

                <button type="submit" class="h-10 px-4 rounded-lg bg-blue-800 hover:bg-blue-900 text-white text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>

                @if($search || $status)
                <a href="{{ route('admin.evaluasi.index') }}" class="h-10 px-3 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-medium transition inline-flex items-center">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table of Interns and Evaluations -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Peserta Magang</th>
                        <th class="py-3 px-4">Asal Kampus / Sekolah</th>
                        <th class="py-3 px-4">Penempatan Seksi</th>
                        <th class="py-3 px-4 text-center">Status Evaluasi</th>
                        <th class="py-3 px-4 text-center">Nilai Akhir</th>
                        <th class="py-3 px-4 text-center">Predikat</th>
                        <th class="py-3 px-4 text-right">Aksi Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($interns as $intern)
                    @php
                        $eval = $intern->evaluation;
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($intern->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">{{ $intern->name }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $intern->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-800">
                            {{ $intern->internProfile->institution ?? '-' }}
                            <div class="text-[10px] text-slate-500">{{ $intern->internProfile->major ?? '' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700">
                                {{ $intern->internProfile->division ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($eval)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i data-lucide="check" class="w-3 h-3"></i> Sudah Dinilai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i data-lucide="clock" class="w-3 h-3"></i> Belum Dinilai
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono">
                            @if($eval)
                                <span class="text-sm font-extrabold text-blue-900">{{ number_format($eval->final_score, 1) }}</span>
                                <span class="text-[10px] text-slate-400 block">/ 100</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($eval)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $eval->predicate_badge_class }}">
                                    {{ $eval->predicate }}
                                </span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                <a href="{{ route('admin.evaluasi.edit', $intern->id) }}"
                                   class="h-8 px-3 rounded-lg bg-blue-800 hover:bg-blue-900 text-white font-semibold text-[11px] transition shadow-2xs inline-flex items-center gap-1.5">
                                    <i data-lucide="{{ $eval ? 'edit-3' : 'plus-circle' }}" class="w-3.5 h-3.5"></i>
                                    <span>{{ $eval ? 'Edit Nilai' : 'Input Nilai' }}</span>
                                </a>

                                @if($eval)
                                <a href="{{ route('admin.evaluasi.print', $intern->id) }}" target="_blank"
                                   class="h-8 px-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-[11px] transition shadow-2xs inline-flex items-center gap-1"
                                   title="Cetak Lembar Penilaian Resmi BPS">
                                    <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-600"></i>
                                    <span>Cetak</span>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                            <p>Tidak ada data peserta magang yang sesuai filter pencarian.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($interns->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $interns->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
