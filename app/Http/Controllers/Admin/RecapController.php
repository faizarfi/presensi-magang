<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecapController extends Controller
{
    public function index(Request $request)
    {
        // Jalankan evaluasi alpa otomatis terlebih dahulu
        AttendanceService::evaluateAllInterns();

        $interns = User::where('role', 'intern')->with('internProfile')->orderBy('name')->get();

        $internId = $request->get('intern_id');
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $status = $request->get('status');
        $division = $request->get('division');

        $query = Attendance::with(['user.internProfile', 'approver'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year);

        if ($internId) {
            $query->where('user_id', $internId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($division) {
            $query->whereHas('user.internProfile', function ($q) use ($division) {
                $q->where('division', $division);
            });
        }

        $attendances = $query->orderBy('date', 'asc')->get();

        // Ringkasan statistik
        $totalRecords = $attendances->count();
        $totalHadir = $attendances->where('status', 'hadir')->count();
        $totalIzinSakit = $attendances->where('status', 'tidak_hadir_keterangan')->count();
        $totalAlpa = $attendances->where('status', 'tidak_hadir_tanpa_keterangan')->count();
        $rate = $totalRecords > 0 ? round(($totalHadir / $totalRecords) * 100, 1) : 0;

        $selectedIntern = $internId ? User::with('internProfile')->find($internId) : null;
        $daysHeader = $this->buildDaysHeader($year, $month);

        // Siapkan matriks menyamping untuk seluruh peserta atau peserta terpilih
        $targetInterns = $selectedIntern ? collect([$selectedIntern]) : $interns;
        $allInternData = [];

        foreach ($targetInterns as $in) {
            $inAtts = Attendance::where('user_id', $in->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('date', 'asc')
                ->get();

            $matrix = $this->buildMonthlyMatrix($year, $month, $inAtts);
            $inHadir = $inAtts->where('status', 'hadir')->count();
            $inIzinSakit = $inAtts->where('status', 'tidak_hadir_keterangan')->count();
            $inAlpa = $inAtts->where('status', 'tidak_hadir_tanpa_keterangan')->count();
            $inTot = $inAtts->count();
            $hasRejection = $inAtts->where('approval_status', 'rejected')->isNotEmpty();
            $hasPending = $inAtts->where('approval_status', 'pending')->isNotEmpty();
            $overallAcc = $hasRejection ? 'Ngga' : ($hasPending ? 'Belum' : ($inHadir > 0 ? 'Setujui' : '-'));

            $allInternData[] = [
                'intern' => $in,
                'attendances' => $inAtts,
                'matrixDays' => $matrix,
                'totalHadir' => $inHadir,
                'totalIzinSakit' => $inIzinSakit,
                'totalAlpa' => $inAlpa,
                'total' => $inTot,
                'rate' => $inTot > 0 ? round(($inHadir / $inTot) * 100, 1) : 0,
                'overallAcc' => $overallAcc,
            ];
        }

        $matrixDays = !empty($allInternData) ? $allInternData[0]['matrixDays'] : [];

        $divisions = [
            'Subbagian Umum',
            'Statistik Sosial',
            'Statistik Produksi',
            'Statistik Distribusi',
            'Neraca Wilayah & Analisis Statistik',
            'Integrasi Pengolahan & Diseminasi Statistik (IPDS)',
        ];

        return view('admin.rekap.index', compact(
            'attendances',
            'interns',
            'selectedIntern',
            'internId',
            'month',
            'year',
            'status',
            'division',
            'divisions',
            'totalRecords',
            'totalHadir',
            'totalIzinSakit',
            'totalAlpa',
            'rate',
            'matrixDays',
            'allInternData',
            'daysHeader'
        ));
    }

    /**
     * Halaman cetak / export PDF resmi BPS Karanganyar (Full Seluruh Anak Magang, Format Menyamping)
     */
    public function exportPdf(Request $request)
    {
        $internsQuery = User::where('role', 'intern')->with('internProfile')->orderBy('name');

        $internId = $request->get('intern_id');
        $division = $request->get('division');
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        if ($internId) {
            $internsQuery->where('id', $internId);
        }

        if ($division) {
            $internsQuery->whereHas('internProfile', function ($q) use ($division) {
                $q->where('division', $division);
            });
        }

        $interns = $internsQuery->get();
        $daysHeader = $this->buildDaysHeader($year, $month);
        $officeConfig = AttendanceService::getOfficeConfig();

        // Data rekap matriks menyamping seluruh anak magang
        $allInternData = [];
        $grandTotalHadir = 0;
        $grandTotalIzinSakit = 0;
        $grandTotalAlpa = 0;
        $grandTotal = 0;

        foreach ($interns as $intern) {
            $attList = Attendance::where('user_id', $intern->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('date', 'asc')
                ->get();

            $matrix = $this->buildMonthlyMatrix($year, $month, $attList);
            $totHadir = $attList->where('status', 'hadir')->count();
            $totIzinSakit = $attList->where('status', 'tidak_hadir_keterangan')->count();
            $totAlpa = $attList->where('status', 'tidak_hadir_tanpa_keterangan')->count();
            $tot = $attList->count();
            $rate = $tot > 0 ? round(($totHadir / $tot) * 100, 1) : 0;

            $hasRejection = $attList->where('approval_status', 'rejected')->isNotEmpty();
            $hasPending = $attList->where('approval_status', 'pending')->isNotEmpty();
            $overallAcc = $hasRejection ? 'Ngga' : ($hasPending ? 'Belum' : ($totHadir > 0 ? 'Setujui' : '-'));

            $grandTotalHadir += $totHadir;
            $grandTotalIzinSakit += $totIzinSakit;
            $grandTotalAlpa += $totAlpa;
            $grandTotal += $tot;

            $allInternData[] = [
                'intern' => $intern,
                'attendances' => $attList,
                'matrixDays' => $matrix,
                'totalHadir' => $totHadir,
                'totalIzin' => $attList->where('status', 'tidak_hadir_keterangan')->where('sub_status', 'izin')->count(),
                'totalSakit' => $attList->where('status', 'tidak_hadir_keterangan')->where('sub_status', 'sakit')->count(),
                'totalIzinSakit' => $totIzinSakit,
                'totalAlpa' => $totAlpa,
                'total' => $tot,
                'rate' => $rate,
                'overallAcc' => $overallAcc,
            ];
        }

        $selectedIntern = $internId ? $interns->first() : null;
        $matrixDays = !empty($allInternData) ? $allInternData[0]['matrixDays'] : [];
        $attendances = collect(); // Jurnal harian ditiadakan sesuai instruksi user

        return view('admin.rekap.pdf', compact(
            'allInternData',
            'daysHeader',
            'interns',
            'selectedIntern',
            'month',
            'year',
            'division',
            'officeConfig',
            'grandTotalHadir',
            'grandTotalIzinSakit',
            'grandTotalAlpa',
            'grandTotal',
            'matrixDays',
            'attendances'
        ));
    }

    /**
     * Helper: Bangun daftar tanggal dalam bulan (1..30/31) beserta status hari libur
     */
    private function buildDaysHeader(int $year, int $month): array
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $daysHeader = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $currDate = Carbon::create($year, $month, $d);
            $dayStatus = \App\Models\Holiday::checkDayStatus($currDate);
            $daysHeader[] = [
                'day' => $d,
                'day_name' => $currDate->translatedFormat('D'),
                'is_off' => $dayStatus['is_off'],
                'off_name' => $dayStatus['name'] ?? null,
            ];
        }
        return $daysHeader;
    }

    /**
     * Helper: Bangun matriks presensi kalender menyamping
     */
    private function buildMonthlyMatrix(int $year, int $month, $attendances): array
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $attendancesByDate = $attendances->keyBy(function ($item) {
            return $item->date->format('Y-m-d');
        });

        $matrixDays = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $currDate = Carbon::create($year, $month, $d);
            $dateStr = $currDate->format('Y-m-d');
            $dayStatus = \App\Models\Holiday::checkDayStatus($currDate);
            $att = $attendancesByDate->get($dateStr);

            $dayData = [
                'day' => $d,
                'day_name' => $currDate->translatedFormat('D'),
                'date_str' => $dateStr,
                'is_off' => $dayStatus['is_off'],
                'off_name' => $dayStatus['name'] ?? null,
                'status_code' => '-',
                'status_label' => '-',
                'acc_label' => '-',
            ];

            if ($att) {
                if ($att->status === 'hadir') {
                    $dayData['status_code'] = 'H';
                    $dayData['status_label'] = 'Hadir';
                } elseif ($att->status === 'tidak_hadir_keterangan') {
                    $dayData['status_code'] = ($att->sub_status === 'sakit') ? 'S' : 'I';
                    $dayData['status_label'] = ($att->sub_status === 'sakit') ? 'Sakit' : 'Izin';
                } else {
                    $dayData['status_code'] = 'A';
                    $dayData['status_label'] = 'Alpa';
                }

                if ($att->approval_status === 'approved') {
                    $dayData['acc_label'] = 'Setujui';
                } elseif ($att->approval_status === 'rejected') {
                    $dayData['acc_label'] = 'Ngga';
                } else {
                    $dayData['acc_label'] = 'Belum';
                }
            } elseif ($dayStatus['is_off']) {
                $dayData['status_code'] = 'L';
                $dayData['status_label'] = 'Libur';
                $dayData['acc_label'] = '-';
            } elseif ($currDate->isPast()) {
                $dayData['status_code'] = 'A';
                $dayData['status_label'] = 'Alpa';
                $dayData['acc_label'] = 'Ngga';
            }

            $matrixDays[] = $dayData;
        }

        return $matrixDays;
    }

    /**
     * Ekspor ke Excel / CSV
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $internId = $request->get('intern_id');
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $query = Attendance::with(['user.internProfile', 'approver'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year);

        if ($internId) {
            $query->where('user_id', $internId);
        }

        $attendances = $query->orderBy('date', 'asc')->get();

        $monthName = Carbon::create($year, $month, 1)->translatedFormat('F_Y');
        $fileName = "Rekap_Presensi_BPS_Karanganyar_{$monthName}.csv";

        return response()->streamDownload(function () use ($attendances) {
            $handle = fopen('php://output', 'w');

            // Tambahkan UTF-8 BOM untuk Microsoft Excel
            fputs($handle, "\xEF\xBB\xBF");

            // Header kolom
            fputcsv($handle, [
                'No',
                'Nama Peserta',
                'NIM/NISN',
                'Asal Kampus/Sekolah',
                'Seksi/Unit BPS',
                'Tanggal',
                'Waktu Absen',
                'Status Kehadiran',
                'Keterangan Sub Status',
                'Jarak ke Kantor (meter)',
                'Uraian Aktivitas Harian',
                'Pembelajaran Diperoleh',
                'Kendala Dialami',
                'Status ACC Admin',
                'Disetujui Oleh',
                'Tanggal ACC',
                'Catatan Pembimbing',
            ], ';');

            $i = 1;
            foreach ($attendances as $item) {
                fputcsv($handle, [
                    $i++,
                    $item->user->name,
                    $item->user->internProfile->nim_nisn ?? '-',
                    $item->user->internProfile->institution ?? '-',
                    $item->user->internProfile->division ?? '-',
                    $item->date->format('Y-m-d'),
                    $item->check_in_time ?? '-',
                    $item->status_label,
                    $item->sub_status_label,
                    $item->distance_meters ? round($item->distance_meters, 1) . ' m' : '-',
                    $item->activity_description ?? '-',
                    $item->learning_obtained ?? '-',
                    $item->constraints_encountered ?? '-',
                    ucfirst($item->approval_status),
                    $item->approver->name ?? '-',
                    $item->approved_at ? $item->approved_at->format('Y-m-d H:i') : '-',
                    $item->approval_notes ?? '-',
                ], ';');
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
