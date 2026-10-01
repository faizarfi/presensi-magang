<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('internProfile');
        $profile = $user->internProfile;

        // Jalankan evaluasi alpa otomatis untuk hari-hari lampau
        AttendanceService::evaluateAbsenceForIntern($user);

        $today = Carbon::today();
        $dayStatus = Holiday::checkDayStatus($today);

        // Cari absensi hari ini
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today->toDateString())
            ->first();

        // Statistik bulan ini
        $stats = AttendanceService::getInternStatistics($user->id, $today->month, $today->year);

        // 5 Absensi terakhir
        $recentAttendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        // Konfigurasi kantor
        $officeConfig = AttendanceService::getOfficeConfig();

        // Hitung progress periode magang
        $progress = 0;
        $totalDays = $profile ? $profile->total_days : 0;
        $elapsedDays = $profile ? $profile->elapsed_days : 0;
        $remainingDays = $profile ? $profile->remaining_days : 0;
        if ($totalDays > 0) {
            $progress = min(100, round(($elapsedDays / $totalDays) * 100));
        }

        return view('intern.dashboard', compact(
            'user',
            'profile',
            'today',
            'dayStatus',
            'todayAttendance',
            'stats',
            'recentAttendances',
            'officeConfig',
            'progress',
            'totalDays',
            'elapsedDays',
            'remainingDays'
        ));
    }
}
