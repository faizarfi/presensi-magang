<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Presensi Seluruh Peserta Magang - BPS Karanganyar</title>
    <link rel="icon" type="image/png" href="{{ asset('images/bps-logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 10px; color: #000; background: #fff; padding: 0 !important; }
            @page {
                size: A4 landscape;
                margin: 0.8cm;
            }
            .page-break { page-break-after: always; }
        }
        body { font-family: 'Times New Roman', Times, serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-4 sm:p-6">

    <!-- Action Bar (Hidden when printing) -->
    <div class="no-print max-w-7xl mx-auto mb-4 flex items-center justify-between bg-white p-4 rounded-xl shadow-xs border border-slate-200">
        <div class="flex items-center space-x-2 font-sans text-xs">
            <span class="font-bold text-slate-800">Format Rekapitulasi Presensi Bulanan Menyamping (A4 Landscape)</span>
            <span class="text-slate-400">•</span>
            <span class="text-slate-600">Total Peserta: <strong>{{ count($allInternData) }} Orang Anak Magang</strong></span>
            <span class="text-slate-400">•</span>
            <span class="text-slate-600">Periode: <strong>{{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}</strong></span>
        </div>
        <div class="flex items-center space-x-2 font-sans text-xs">
            <button onclick="window.print()" class="h-9 px-4 rounded-lg bg-blue-800 hover:bg-blue-900 text-white font-medium inline-flex items-center gap-2 shadow-xs transition">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak / Simpan PDF</span>
            </button>
            <button onclick="window.close()" class="h-9 px-4 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium inline-flex items-center transition">
                Tutup
            </button>
        </div>
    </div>

    <!-- Official Document Sheet (A4 Landscape Container) -->
    <div class="max-w-7xl mx-auto bg-white p-6 sm:p-10 shadow-lg rounded-xl border border-slate-200 text-slate-900 leading-normal">

        <!-- KOP SURAT RESMI BPS KABUPATEN KARANGANYAR -->
        <div class="flex items-center justify-between pb-2">
            <div class="w-24 text-center flex-shrink-0">
                <img src="{{ asset('images/bps-logo.png') }}" alt="Logo BPS" class="h-16 w-auto mx-auto object-contain">
            </div>

            <div class="flex-1 text-center px-4">
                <h2 class="text-sm sm:text-base font-bold uppercase tracking-wider">BADAN PUSAT STATISTIK</h2>
                <h1 class="text-base sm:text-lg font-extrabold uppercase tracking-wide">KABUPATEN KARANGANYAR</h1>
                <p class="text-[11px] mt-0.5">{{ $officeConfig['address'] ?? 'Komplek Perkantoran Cangakan, Jl. Majapahit No. 11 B, Badran Asri, Bejen, Karanganyar 57712' }}</p>
                <p class="text-[10px] text-slate-600">Telepon: (0271) 495047 | Pos-el: bps3313@bps.go.id | Laman: karanganyarkab.bps.go.id</p>
            </div>

            <div class="w-24"></div>
        </div>

        <!-- Double Divider Line Kop Surat -->
        <div class="border-t-2 border-black mt-1"></div>
        <div class="border-t border-black mt-0.5 mb-4"></div>

        <!-- Document Title -->
        <div class="text-center mb-4">
            <h3 class="text-sm sm:text-base font-bold uppercase tracking-wider underline">
                DAFTAR REKAPITULASI PRESENSI BULANAN PESERTA MAGANG
            </h3>
            <p class="text-xs font-semibold mt-1 font-sans text-slate-700">
                Periode: Bulan {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F') }} {{ $year }}
                @if($division) • Seksi {{ $division }} @endif
            </p>
        </div>

        <!-- ========================================================
             1. TABEL MASTER REKAPITULASI PRESENSI MENYAMPING (SEMUA PESERTA)
             ======================================================== -->
        <div class="mb-6">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-slate-500 text-center text-[10px]">
                    <thead>
                        <!-- Baris 1: Header Kolom Utama & Tanggal Menyamping -->
                        <tr class="bg-slate-100 font-bold">
                            <th rowspan="2" class="border border-slate-500 p-1 w-7">No</th>
                            <th rowspan="2" class="border border-slate-500 p-1 text-left w-40">Nama Peserta Magang</th>
                            <th rowspan="2" class="border border-slate-500 p-1 text-left w-32">Seksi Penempatan</th>
                            <th colspan="{{ count($daysHeader) }}" class="border border-slate-500 p-1">
                                Tanggal Presensi Bulan {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F') }} {{ $year }}
                            </th>
                            <th colspan="4" class="border border-slate-500 p-1 w-24 bg-slate-200">Rekapitulasi</th>
                            <th rowspan="2" class="border border-slate-500 p-1 w-16 bg-slate-200">Status ACC</th>
                        </tr>
                        <!-- Baris 2: Nomor Tanggal (1 s/d 30/31) & Sub Rekap -->
                        <tr class="bg-slate-100 font-bold text-[9px]">
                            @foreach($daysHeader as $dh)
                                <th class="border border-slate-500 p-0.5 w-6 {{ $dh['is_off'] ? 'bg-rose-50 text-rose-700' : '' }}">
                                    {{ $dh['day'] }}
                                </th>
                            @endforeach
                            <th class="border border-slate-500 p-0.5 w-6 text-emerald-800 bg-slate-100">H</th>
                            <th class="border border-slate-500 p-0.5 w-6 text-amber-800 bg-slate-100">I</th>
                            <th class="border border-slate-500 p-0.5 w-6 text-blue-800 bg-slate-100">S</th>
                            <th class="border border-slate-500 p-0.5 w-6 text-rose-800 bg-slate-100">A</th>
                        </tr>
                        <!-- Baris 3: Hari (Sen, Sel, Rab, Kam...) -->
                        <tr class="bg-slate-50 text-[8px] text-slate-600">
                            <th colspan="3" class="border border-slate-500 p-0.5 text-right pr-2">Hari :</th>
                            @foreach($daysHeader as $dh)
                                <th class="border border-slate-500 p-0.5 {{ $dh['is_off'] ? 'text-rose-600 font-semibold' : '' }}">
                                    {{ substr($dh['day_name'], 0, 3) }}
                                </th>
                            @endforeach
                            <th colspan="5" class="border border-slate-500 p-0.5 bg-slate-100"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allInternData as $idx => $item)
                            <tr class="hover:bg-slate-50/50">
                                <td class="border border-slate-500 p-1 font-semibold">{{ $idx + 1 }}</td>
                                <td class="border border-slate-500 p-1 text-left font-bold text-slate-900 whitespace-nowrap">
                                    {{ $item['intern']->name }}
                                    <div class="text-[8px] text-slate-500 font-normal font-sans">NIM: {{ $item['intern']->internProfile->nim_nisn ?? '-' }}</div>
                                </td>
                                <td class="border border-slate-500 p-1 text-left text-[9px] text-slate-700">
                                    {{ $item['intern']->internProfile->division ?? 'Subbagian Umum' }}
                                    <div class="text-[8px] text-slate-400 font-sans truncate">{{ $item['intern']->internProfile->institution ?? '-' }}</div>
                                </td>

                                <!-- Kolom Tanggal Menyamping -->
                                @foreach($item['matrixDays'] as $day)
                                    <td class="border border-slate-500 p-0.5 font-bold text-[9px]
                                        {{ $day['status_code'] === 'H' ? 'text-emerald-700 bg-emerald-50/30' : '' }}
                                        {{ $day['status_code'] === 'I' ? 'text-amber-700 bg-amber-50/40' : '' }}
                                        {{ $day['status_code'] === 'S' ? 'text-blue-700 bg-blue-50/40' : '' }}
                                        {{ $day['status_code'] === 'A' ? 'text-rose-700 bg-rose-50/40' : '' }}
                                        {{ $day['status_code'] === 'L' ? 'text-slate-400 bg-slate-100' : '' }}
                                    ">
                                        {{ $day['status_code'] }}
                                    </td>
                                @endforeach

                                <!-- Rekap H, I, S, A -->
                                <td class="border border-slate-500 p-1 font-bold text-emerald-800 bg-emerald-50/20">
                                    {{ $item['totalHadir'] }}
                                </td>
                                <td class="border border-slate-500 p-1 font-semibold text-amber-800">
                                    {{ $item['totalIzin'] ?? (isset($item['attendances']) ? $item['attendances']->where('status', 'tidak_hadir_keterangan')->where('sub_status', 'izin')->count() : 0) }}
                                </td>
                                <td class="border border-slate-500 p-1 font-semibold text-blue-800">
                                    {{ $item['totalSakit'] ?? (isset($item['attendances']) ? $item['attendances']->where('status', 'tidak_hadir_keterangan')->where('sub_status', 'sakit')->count() : 0) }}
                                </td>
                                <td class="border border-slate-500 p-1 font-bold text-rose-800 bg-rose-50/20">
                                    {{ $item['totalAlpa'] }}
                                </td>

                                <!-- Status ACC: Setujui / Ngga / Belum -->
                                <td class="border border-slate-500 p-1 font-bold text-[9px]
                                    {{ $item['overallAcc'] === 'Setujui' ? 'text-emerald-700 bg-emerald-50/50' : ($item['overallAcc'] === 'Ngga' ? 'text-rose-700 bg-rose-50/50' : 'text-amber-700 bg-amber-50/40') }}">
                                    {{ $item['overallAcc'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($daysHeader) + 8 }}" class="border border-slate-500 p-4 text-center text-slate-500">
                                    Tidak ada data peserta magang pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Keterangan Kode & Status ACC -->
            <div class="flex flex-wrap items-center justify-between text-[10px] text-slate-700 mt-2 font-sans px-1">
                <div class="flex items-center gap-3">
                    <span class="font-bold">Kode Presensi:</span>
                    <span><strong class="text-emerald-700">H</strong> = Hadir</span>
                    <span><strong class="text-amber-700">I</strong> = Izin</span>
                    <span><strong class="text-blue-700">S</strong> = Sakit</span>
                    <span><strong class="text-rose-700">A</strong> = Alpa (Kosong)</span>
                    <span><strong class="text-slate-500">L</strong> = Libur / Akhir Pekan</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-bold">Status ACC Pembimbing:</span>
                    <span class="text-emerald-700 font-bold">Setujui</span>
                    <span>/</span>
                    <span class="text-rose-700 font-bold">Ngga</span>
                </div>
            </div>
        </div>

        <!-- ========================================================
             2. RINCIAN MATRIKS STATUS ACC TANGGAL MENYAMPING PER PESERTA
             ======================================================== -->
        <div class="mb-6 space-y-4">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 border-b border-slate-300 pb-1">
                Rincian Status ACC Presensi Menyamping Per Peserta Magang
            </h4>

            @foreach($allInternData as $idx => $item)
                <div class="p-3 bg-slate-50 rounded border border-slate-300 {{ !$loop->last ? 'mb-3' : '' }}">
                    <div class="flex items-center justify-between text-xs mb-1.5 font-sans">
                        <div class="flex items-center space-x-2">
                            <span class="font-bold text-slate-900">{{ $idx + 1 }}. {{ $item['intern']->name }}</span>
                            <span class="text-slate-400">•</span>
                            <span class="text-slate-600">NIM: {{ $item['intern']->internProfile->nim_nisn ?? '-' }}</span>
                            <span class="text-slate-400">•</span>
                            <span class="text-slate-600">Seksi: {{ $item['intern']->internProfile->division ?? '-' }}</span>
                        </div>
                        <div class="text-[11px]">
                            <span class="text-slate-600">Total Hadir: <strong class="text-emerald-700">{{ $item['totalHadir'] }} Hari</strong></span>
                            <span class="mx-1 text-slate-300">|</span>
                            <span class="text-slate-600">Status ACC Keseluruhan:
                                <strong class="{{ $item['overallAcc'] === 'Setujui' ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $item['overallAcc'] }}
                                </strong>
                            </span>
                        </div>
                    </div>

                    <!-- Matriks 4 Baris: Tanggal, Hari, Kehadiran, Status ACC -->
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-slate-400 text-center text-[9px]">
                            <thead>
                                <tr class="bg-slate-200 font-bold">
                                    <th class="border border-slate-400 p-0.5 w-16 text-left">Tanggal</th>
                                    @foreach($item['matrixDays'] as $m)
                                        <th class="border border-slate-400 p-0.5 {{ $m['is_off'] ? 'bg-rose-50 text-rose-700' : '' }}">
                                            {{ $m['day'] }}
                                        </th>
                                    @endforeach
                                    <th class="border border-slate-400 p-0.5 bg-slate-300">Total</th>
                                </tr>
                                <tr class="bg-slate-100 text-[8px] text-slate-600">
                                    <th class="border border-slate-400 p-0.5 text-left">Hari</th>
                                    @foreach($item['matrixDays'] as $m)
                                        <th class="border border-slate-400 p-0.5 {{ $m['is_off'] ? 'text-rose-600 font-semibold' : '' }}">
                                            {{ substr($m['day_name'], 0, 3) }}
                                        </th>
                                    @endforeach
                                    <th class="border border-slate-400 p-0.5 bg-slate-200">{{ count($item['matrixDays']) }} H</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-slate-400 p-0.5 text-left font-bold bg-slate-100">Presensi</td>
                                    @foreach($item['matrixDays'] as $m)
                                        <td class="border border-slate-400 p-0.5 font-bold {{ $m['status_code'] === 'H' ? 'text-emerald-700 bg-emerald-50/40' : ($m['status_code'] === 'L' ? 'text-slate-400 bg-slate-100' : ($m['status_code'] === 'A' ? 'text-rose-700 bg-rose-50/40' : 'text-amber-700')) }}">
                                            {{ $m['status_code'] }}
                                        </td>
                                    @endforeach
                                    <td class="border border-slate-400 p-0.5 font-bold text-emerald-800 bg-slate-100">
                                        {{ $item['totalHadir'] }} H
                                    </td>
                                </tr>
                                <tr>
                                    <td class="border border-slate-400 p-0.5 text-left font-bold bg-slate-100">Status ACC</td>
                                    @foreach($item['matrixDays'] as $m)
                                        <td class="border border-slate-400 p-0.5 font-bold text-[8px] {{ $m['acc_label'] === 'Setujui' ? 'text-emerald-700 bg-emerald-50/40' : ($m['acc_label'] === 'Ngga' ? 'text-rose-700 bg-rose-50/40' : 'text-slate-400') }}">
                                            {{ $m['acc_label'] }}
                                        </td>
                                    @endforeach
                                    <td class="border border-slate-400 p-0.5 font-bold text-blue-900 bg-slate-100">
                                        {{ $item['totalHadir'] }} ACC
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Lembar Pengesahan & Tanda Tangan Pembimbing Lapangan -->
        <div class="grid grid-cols-2 text-xs text-center pt-4 border-t border-slate-200">
            <div>
                <p>Mengetahui,</p>
                <p class="font-bold">Kasubbag Umum BPS Kab. Karanganyar</p>
                <div class="h-16"></div>
                <p class="font-bold underline">Drs. Suryanto, M.Si</p>
                <p class="text-[11px] text-slate-600">NIP: 19780512 200501 1 002</p>
            </div>

            <div>
                <p>Karanganyar, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold">Pembimbing Lapangan Magang</p>
                <div class="h-16"></div>
                <p class="font-bold underline">Drs. Suryanto, M.Si</p>
                <p class="text-[11px] text-slate-600">NIP: 19780512 200501 1 002</p>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
