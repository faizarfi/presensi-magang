@extends('layouts.app')

@section('title', 'Verifikasi Presensi - SIM-MAGANG BPS')

@section('content')
<div class="space-y-6" x-data="{ selectedIds: [], selectAll: false, activeModalItem: null }">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-800 flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Verifikasi Presensi</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Verifikasi & ACC Presensi</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Evaluasi dan persetujuan laporan presensi dan logbook kegiatan harian peserta magang BPS.
            </p>
        </div>

        <!-- Bulk Action Button -->
        <div x-show="selectedIds.length > 0" x-cloak class="flex items-center gap-2">
            <form id="bulkAccForm" action="{{ route('admin.verifikasi.bulk') }}" method="POST">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="attendance_ids[]" :value="id">
                </template>
                <button type="button" @click="handleBulkAcc()"
                        class="h-10 px-4 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs sm:text-sm transition shadow-xs flex items-center gap-2">
                    <i data-lucide="check-check" class="w-4 h-4"></i>
                    <span>Setujui (<span x-text="selectedIds.length"></span>) Terpilih (Bulk ACC)</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Bar & Status Tabs -->
    <div class="bg-white rounded-xl p-4 sm:p-5 shadow-xs border border-slate-200 space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Tabs (Horizontal scroll on phone) -->
            <div class="flex items-center gap-1 overflow-x-auto max-w-full p-1 bg-slate-100 rounded-lg text-xs font-medium text-slate-600 flex-nowrap">
                <a href="{{ route('admin.verifikasi.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}"
                   class="px-3 py-1.5 rounded-md transition whitespace-nowrap flex-shrink-0 {{ $status === 'pending' ? 'bg-white text-slate-900 font-semibold shadow-xs' : 'hover:text-slate-900' }}">
                    Menunggu ACC
                    @if($pendingCount > 0)
                        <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] bg-amber-500 text-white font-bold">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.verifikasi.index', array_merge(request()->except('status'), ['status' => 'approved'])) }}"
                   class="px-3 py-1.5 rounded-md transition whitespace-nowrap flex-shrink-0 {{ $status === 'approved' ? 'bg-white text-slate-900 font-semibold shadow-xs' : 'hover:text-slate-900' }}">
                    Disetujui ({{ $approvedCount }})
                </a>
                <a href="{{ route('admin.verifikasi.index', array_merge(request()->except('status'), ['status' => 'rejected'])) }}"
                   class="px-3 py-1.5 rounded-md transition whitespace-nowrap flex-shrink-0 {{ $status === 'rejected' ? 'bg-white text-slate-900 font-semibold shadow-xs' : 'hover:text-slate-900' }}">
                    Ditolak ({{ $rejectedCount }})
                </a>
                <a href="{{ route('admin.verifikasi.index', array_merge(request()->except('status'), ['status' => 'all'])) }}"
                   class="px-3 py-1.5 rounded-md transition whitespace-nowrap flex-shrink-0 {{ $status === 'all' ? 'bg-white text-slate-900 font-semibold shadow-xs' : 'hover:text-slate-900' }}">
                    Semua Data
                </a>
            </div>

            <!-- Filter Controls -->
            <form method="GET" action="{{ route('admin.verifikasi.index') }}" class="flex flex-col sm:flex-row sm:items-center gap-2 w-full lg:w-auto">
                <input type="hidden" name="status" value="{{ $status }}">

                <select name="intern_id" class="w-full sm:w-auto h-10 text-xs font-medium px-3 rounded-lg border border-slate-300 bg-white shadow-xs focus:ring-1 focus:ring-blue-800 outline-none">
                    <option value="">Semua Peserta Magang</option>
                    @foreach($interns as $in)
                        <option value="{{ $in->id }}" {{ $internId == $in->id ? 'selected' : '' }}>{{ $in->name }}</option>
                    @endforeach
                </select>

                <input type="date" name="date" value="{{ $date }}"
                       class="w-full sm:w-auto h-10 text-xs font-medium px-3 rounded-lg border border-slate-300 bg-white shadow-xs focus:ring-1 focus:ring-blue-800 outline-none">

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 sm:flex-initial h-10 px-4 rounded-lg bg-blue-800 hover:bg-blue-900 text-white text-xs font-medium transition shadow-xs inline-flex items-center justify-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Saring</span>
                    </button>

                    @if($internId || $date)
                        <a href="{{ route('admin.verifikasi.index', ['status' => $status]) }}" class="h-10 px-3 inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Verification List / Table -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <!-- Mobile Card List View (< md screens) -->
        <div class="block md:hidden divide-y divide-slate-100">
            @forelse($attendances as $item)
                <div class="p-4 space-y-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start space-x-2.5">
                            <input type="checkbox" value="{{ $item->id }}" x-model="selectedIds"
                                   class="w-4 h-4 mt-0.5 rounded text-blue-800 border-slate-300 focus:ring-blue-800 cursor-pointer">
                            <div>
                                <div class="font-bold text-xs text-slate-900">{{ $item->user->name }}</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $item->user->internProfile->institution ?? '-' }} • Seksi {{ $item->user->internProfile->division ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border whitespace-nowrap {{ $item->status_badge_class }}">
                            {{ $item->status_label }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-500 font-mono bg-slate-50 p-2 rounded-lg border border-slate-100">
                        <div>
                            <span>Masuk: {{ $item->check_in_time ? substr($item->check_in_time, 0, 5) . ' WIB' : 'Tanpa Waktu' }}</span>
                            @if($item->check_out_time)
                                <span class="text-blue-800 font-bold block">Pulang: {{ substr($item->check_out_time, 0, 5) }} WIB ({{ $item->work_duration_formatted }})</span>
                            @endif
                        </div>
                        <div class="text-right">
                            <span>{{ $item->date->translatedFormat('d M Y') }}</span>
                            @if($item->is_late)
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 block mt-0.5">
                                    Terlambat {{ $item->late_minutes }}m
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($item->is_late && $item->late_reason)
                        <div class="text-[11px] bg-amber-50/80 p-2 rounded border border-amber-200 text-amber-900 leading-snug">
                            <span class="font-bold">Alasan Terlambat:</span> {{ $item->late_reason }}
                        </div>
                    @endif

                    <div class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                        {{ $item->activity_description ?? '-' }}
                    </div>

                    @if($item->photo || $item->check_out_photo || $item->attachment_file)
                        <div class="flex flex-wrap items-center gap-2 pt-0.5">
                            @if($item->photo)
                                <a href="{{ asset('storage/' . $item->photo) }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded bg-blue-50 text-blue-800 border border-blue-200">
                                    <i data-lucide="camera" class="w-3 h-3"></i>
                                    <span>Selfie Masuk</span>
                                </a>
                            @endif
                            @if($item->check_out_photo)
                                <a href="{{ asset('storage/' . $item->check_out_photo) }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded bg-indigo-50 text-indigo-800 border border-indigo-200">
                                    <i data-lucide="camera" class="w-3 h-3"></i>
                                    <span>Selfie Pulang</span>
                                </a>
                            @endif
                            @if($item->attachment_file)
                                <a href="{{ route('attendance.attachment', $item->id) }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300 font-semibold">
                                    <i data-lucide="shield-check" class="w-3 h-3 text-amber-700"></i>
                                    <span>Buka Surat Bukti (Privat)</span>
                                </a>
                            @endif
                        </div>
                    @endif

                    <div class="flex items-center justify-between pt-1 text-xs">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border flex items-center gap-1 {{ $item->approval_badge_class }}">
                            @if($item->approval_status === 'approved')
                                <i data-lucide="check-circle" class="w-3 h-3 text-emerald-600"></i> Disetujui
                            @elseif($item->approval_status === 'rejected')
                                <i data-lucide="x-circle" class="w-3 h-3 text-rose-600"></i> Ditolak
                            @else
                                <i data-lucide="clock" class="w-3 h-3 text-amber-600"></i> Menunggu ACC
                            @endif
                        </span>

                        <div class="flex items-center gap-2">
                            @if($item->approval_status !== 'approved')
                                <button type="button" onclick="confirmSingleAcc({{ $item->id }}, '{{ addslashes($item->user->name) }}', '{{ $item->date->translatedFormat('d M Y') }}')"
                                        class="h-7 px-2.5 rounded-md bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs transition inline-flex items-center gap-1">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                    <span>ACC</span>
                                </button>
                            @endif
                            <button type="button" @click="activeModalItem = {{ json_encode($item) }}; $nextTick(() => lucide.createIcons())"
                                    class="h-7 px-2.5 rounded-md border border-slate-300 text-slate-700 font-medium text-xs transition inline-flex items-center gap-1">
                                <span>Telaah</span>
                                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-xs">
                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                    <span>Tidak ada data presensi yang sesuai filter.</span>
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View (>= md screens) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-wider font-semibold text-[11px]">
                        <th class="py-3.5 px-4 w-10">
                            <input type="checkbox" @change="
                                selectAll = !selectAll;
                                selectedIds = selectAll ? [{{ $attendances->pluck('id')->join(',') }}] : [];
                            " class="w-4 h-4 rounded text-blue-800 border-slate-300 focus:ring-blue-800 cursor-pointer">
                        </th>
                        <th class="py-3.5 px-4">Peserta & Kampus</th>
                        <th class="py-3.5 px-4">Tanggal & Jam</th>
                        <th class="py-3.5 px-4">Status & Jarak GPS</th>
                        <th class="py-3.5 px-4">Uraian Logbook</th>
                        <th class="py-3.5 px-4">Status ACC</th>
                        <th class="py-3.5 px-4 text-right">Tindakan Evaluasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <input type="checkbox" value="{{ $item->id }}" x-model="selectedIds"
                                       class="w-4 h-4 rounded text-blue-800 border-slate-300 focus:ring-blue-800 cursor-pointer">
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-900 text-sm">{{ $item->user->name }}</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $item->user->internProfile->institution ?? '-' }} • Seksi {{ $item->user->internProfile->division ?? '-' }}
                                </div>
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="text-slate-800 font-medium">{{ $item->date->translatedFormat('d M Y') }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">
                                    Masuk: {{ $item->check_in_time ? substr($item->check_in_time, 0, 5) . ' WIB' : '-' }}
                                </div>
                                @if($item->check_out_time)
                                    <div class="text-[11px] text-blue-800 font-bold font-mono">
                                        Pulang: {{ substr($item->check_out_time, 0, 5) }} WIB
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-medium">
                                        Durasi: {{ $item->work_duration_formatted }}
                                    </div>
                                @endif
                                @if($item->is_late)
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 mt-0.5">
                                        Terlambat {{ $item->late_minutes }}m
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium border {{ $item->status_badge_class }}">
                                    {{ $item->status_label }}
                                </span>
                                @if($item->distance_meters !== null)
                                    <div class="text-[10px] text-emerald-700 font-medium mt-1 flex items-center gap-1 font-mono">
                                        <i data-lucide="map-pin" class="w-3 h-3"></i>
                                        <span>{{ round($item->distance_meters) }} m dari BPS</span>
                                    </div>
                                @endif
                                <div class="text-[10px] text-slate-400 mt-0.5">({{ $item->sub_status_label }})</div>
                            </td>

                            <td class="py-3.5 px-4 max-w-xs">
                                <p class="text-slate-700 line-clamp-2 leading-relaxed">{{ $item->activity_description ?? '-' }}</p>
                                @if($item->is_late && $item->late_reason)
                                    <div class="text-[10px] text-amber-900 bg-amber-50 p-1.5 rounded border border-amber-200 mt-1">
                                        <strong>Alasan Terlambat:</strong> {{ $item->late_reason }}
                                    </div>
                                @endif
                                @if($item->learning_obtained && $item->learning_obtained !== '-')
                                    <div class="text-[11px] text-slate-500 mt-1 italic line-clamp-1">
                                        Wawasan: {{ $item->learning_obtained }}
                                    </div>
                                @endif
                                @if($item->photo || $item->check_out_photo || $item->attachment_file)
                                    <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-[11px] font-medium">
                                        @if($item->photo)
                                            <a href="{{ asset('storage/' . $item->photo) }}" target="_blank"
                                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-50 text-blue-800 border border-blue-200 hover:bg-blue-100 transition">
                                                <i data-lucide="camera" class="w-3 h-3"></i>
                                                <span>Selfie Masuk</span>
                                            </a>
                                        @endif
                                        @if($item->check_out_photo)
                                            <a href="{{ asset('storage/' . $item->check_out_photo) }}" target="_blank"
                                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-50 text-indigo-800 border border-indigo-200 hover:bg-indigo-100 transition">
                                                <i data-lucide="camera" class="w-3 h-3"></i>
                                                <span>Selfie Pulang</span>
                                            </a>
                                        @endif
                                        @if($item->attachment_file)
                                            <a href="{{ route('attendance.attachment', $item->id) }}" target="_blank"
                                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300 hover:bg-amber-200 transition font-semibold">
                                                <i data-lucide="shield-check" class="w-3 h-3 text-amber-700"></i>
                                                <span>Surat Bukti (Privat)</span>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium border flex items-center gap-1 w-fit {{ $item->approval_badge_class }}">
                                    @if($item->approval_status === 'approved')
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i> Disetujui
                                    @elseif($item->approval_status === 'rejected')
                                        <i data-lucide="x-circle" class="w-3.5 h-3.5 text-rose-600"></i> Ditolak
                                    @else
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600"></i> Menunggu ACC
                                    @endif
                                </span>
                                @if($item->approved_at)
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono">{{ $item->approved_at->translatedFormat('d M H:i') }}</div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- Tombol ACC Cepat -->
                                    @if($item->approval_status !== 'approved')
                                        <form id="quickApproveForm-{{ $item->id }}" action="{{ route('admin.verifikasi.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="approval_status" value="approved">
                                            <input type="hidden" name="approval_notes" value="Disetujui oleh Pembimbing BPS.">
                                            <button type="button" onclick="confirmSingleAcc({{ $item->id }}, '{{ addslashes($item->user->name) }}', '{{ $item->date->translatedFormat('d M Y') }}')"
                                                    title="Langsung Setujui (ACC)"
                                                    class="h-8 w-8 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white border border-emerald-200 transition flex items-center justify-center">
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Tombol Review Lengkap -->
                                    <button type="button" @click="activeModalItem = {{ json_encode($item) }}; $nextTick(() => lucide.createIcons())"
                                            class="h-8 px-3 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs transition inline-flex items-center gap-1">
                                        <span>Telaah</span>
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <span class="font-medium text-slate-600">Tidak ada data presensi yang sesuai filter.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-slate-100">
            {{ $attendances->links() }}
        </div>
    </div>

    <!-- Review & Evaluation Modal -->
    <div x-show="activeModalItem" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 space-y-5 relative border border-slate-200" @click.away="activeModalItem = null">
            <!-- Modal Header -->
            <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                <div>
                    <span class="text-xs font-semibold text-blue-800 uppercase tracking-wider block">Telaah Logbook & Presensi</span>
                    <h3 class="text-lg font-bold text-slate-900 mt-0.5" x-text="activeModalItem?.user?.name"></h3>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="activeModalItem?.date + ' — ' + (activeModalItem?.check_in_time || 'Tanpa Jam')"></p>
                </div>
                <button type="button" @click="activeModalItem = null" class="w-8 h-8 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-500 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Detail Information List -->
            <div class="space-y-3.5 text-xs">
                <!-- Status & Geolokasi -->
                <div class="grid grid-cols-2 gap-3 p-3.5 bg-slate-50 rounded-lg border border-slate-200">
                    <div>
                        <span class="text-slate-500 font-medium block uppercase text-[10px]">Status Kehadiran</span>
                        <span class="font-bold text-slate-900 text-sm capitalize" x-text="activeModalItem?.status"></span>
                        <div class="text-slate-500 mt-0.5" x-text="activeModalItem?.sub_status"></div>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium block uppercase text-[10px]">Validasi Lokasi GPS</span>
                        <span class="font-semibold text-emerald-700" x-text="activeModalItem?.distance_meters ? Math.round(activeModalItem.distance_meters) + ' meter dari kantor BPS' : 'Bebas Lokasi'"></span>
                    </div>
                </div>

                <!-- Logbook Content -->
                <div class="space-y-2.5">
                    <div class="p-3.5 rounded-lg bg-white border border-slate-200">
                        <span class="font-semibold text-slate-800 block mb-1">1. Uraian Aktivitas & Tugas:</span>
                        <p class="text-slate-700 whitespace-pre-line leading-relaxed" x-text="activeModalItem?.activity_description || '-'"></p>
                    </div>

                    <div class="p-3.5 rounded-lg bg-white border border-slate-200">
                        <span class="font-semibold text-slate-800 block mb-1">2. Pembelajaran & Wawasan Diperoleh:</span>
                        <p class="text-slate-700 whitespace-pre-line leading-relaxed" x-text="activeModalItem?.learning_obtained || '-'"></p>
                    </div>

                    <div class="p-3.5 rounded-lg bg-white border border-slate-200">
                        <span class="font-semibold text-slate-800 block mb-1">3. Kendala & Hambatan:</span>
                        <p class="text-slate-700 whitespace-pre-line leading-relaxed" x-text="activeModalItem?.constraints_encountered || '-'"></p>
                    </div>
                </div>

                <!-- Media Preview -->
                <div class="flex items-center gap-3 pt-1" x-show="activeModalItem?.photo || activeModalItem?.attachment_file">
                    <template x-if="activeModalItem?.photo">
                        <a :href="'/storage/' + activeModalItem?.photo" target="_blank" class="h-9 px-3 rounded-lg bg-blue-50 text-blue-800 font-medium border border-blue-200 inline-flex items-center gap-1.5 hover:bg-blue-100 transition">
                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                            <span>Buka Foto Kegiatan</span>
                        </a>
                    </template>
                    <template x-if="activeModalItem?.attachment_file">
                        <a :href="'/storage/' + activeModalItem?.attachment_file" target="_blank" class="h-9 px-3 rounded-lg bg-amber-50 text-amber-800 font-medium border border-amber-200 inline-flex items-center gap-1.5 hover:bg-amber-100 transition">
                            <i data-lucide="paperclip" class="w-3.5 h-3.5"></i>
                            <span>Buka Dokumen Lampiran</span>
                        </a>
                    </template>
                </div>
            </div>

            <!-- Form Tindakan ACC / Tolak -->
            <template x-if="activeModalItem">
                <form :action="'/admin/verifikasi/' + activeModalItem.id" method="POST" class="pt-4 border-t border-slate-100 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Catatan Evaluasi Pembimbing BPS (Opsional)
                        </label>
                        <textarea name="approval_notes" rows="2" placeholder="Tuliskan catatan apresiasi, koreksi metodologi, atau arahan tugas selanjutnya..."
                                  class="w-full text-xs p-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 focus:border-blue-800 outline-none leading-relaxed"
                                  :value="activeModalItem.approval_notes || ''"></textarea>
                    </div>

                    <div class="flex items-center justify-between gap-3 pt-2">
                        <button type="submit" name="approval_status" value="rejected"
                                class="h-10 px-4 rounded-lg border border-rose-300 text-rose-700 hover:bg-rose-50 text-xs font-medium transition flex items-center gap-1.5">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            <span>Tolak Laporan</span>
                        </button>

                        <button type="submit" name="approval_status" value="approved"
                                class="h-10 px-5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium transition shadow-xs flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Setujui (ACC) Laporan</span>
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function confirmSingleAcc(id, name, date) {
        Swal.fire({
            title: 'Setujui Laporan Presensi?',
            html: `<div class="text-xs text-slate-600 text-left space-y-1">
                    <p>Mahasiswa: <strong>${name}</strong></p>
                    <p>Tanggal: <strong>${date}</strong></p>
                    <p class="mt-2 text-emerald-700 font-medium">Laporan presensi dan logbook aktivitas akan disetujui (ACC).</p>
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
                document.getElementById('quickApproveForm-' + id).submit();
            }
        });
    }

    function handleBulkAcc() {
        const count = document.querySelectorAll('input[name="attendance_ids[]"]').length;
        Swal.fire({
            title: 'Konfirmasi Persetujuan Massal (Bulk ACC)',
            text: `Apakah Anda yakin ingin menyetujui ${count} laporan presensi magang terpilih sekaligus?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Setujui Semua',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((res) => {
            if (res.isConfirmed) {
                document.getElementById('bulkAccForm').submit();
            }
        });
    }
</script>
@endpush
@endsection
