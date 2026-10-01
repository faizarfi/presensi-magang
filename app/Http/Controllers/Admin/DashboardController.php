<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Jalankan evaluasi alpa otomatis untuk seluruh peserta magang
        AttendanceService::evaluateAllInterns();

        $today = Carbon::today();
        $dayStatus = Holiday::checkDayStatus($today);

        $totalInterns = User::where('role', 'intern')->count();

        // Absensi hari ini
        $todayAttendances = Attendance::with(['user.internProfile'])
            ->where('date', $today->toDateString())
            ->get();

        $hadirToday = $todayAttendances->where('status', 'hadir')->count();
        $izinSakitToday = $todayAttendances->where('status', 'tidak_hadir_keterangan')->count();
        $alpaToday = $todayAttendances->where('status', 'tidak_hadir_tanpa_keterangan')->count();

        // Belum absen hari ini (jika hari kerja aktif)
        $attendedUserIds = $todayAttendances->pluck('user_id')->toArray();
        $notYetAttendedCount = $dayStatus['is_off'] ? 0 : max(0, $totalInterns - count($attendedUserIds));

        // Pending verifikasi / ACC
        $pendingCount = Attendance::where('approval_status', 'pending')->count();

        // 5 Absensi pending terbaru
        $recentPending = Attendance::with(['user.internProfile'])
            ->where('approval_status', 'pending')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        // Statistik 7 hari terakhir untuk grafik
        $chartDates = [];
        $chartHadir = [];
        $chartIzin = [];
        $chartAlpa = [];

        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $chartDates[] = $d->translatedFormat('d M');

            $dayRecords = Attendance::where('date', $d->toDateString())->get();
            $chartHadir[] = $dayRecords->where('status', 'hadir')->count();
            $chartIzin[] = $dayRecords->where('status', 'tidak_hadir_keterangan')->count();
            $chartAlpa[] = $dayRecords->where('status', 'tidak_hadir_tanpa_keterangan')->count();
        }

        return view('admin.dashboard', compact(
            'today',
            'dayStatus',
            'totalInterns',
            'hadirToday',
            'izinSakitToday',
            'alpaToday',
            'notYetAttendedCount',
            'pendingCount',
            'todayAttendances',
            'recentPending',
            'chartDates',
            'chartHadir',
            'chartIzin',
            'chartAlpa'
        ));
    }
}
