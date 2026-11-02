<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Hitung jarak dua titik koordinat (meter) menggunakan Rumus Haversine
     */
    public static function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Meter

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return round($angle * $earthRadius, 2);
    }

    /**
     * Dapatkan konfigurasi kantor BPS Karanganyar dan jadwal presensi
     */
    public static function getOfficeConfig(): array
    {
        $today = Carbon::today();
        $isFriday = $today->isFriday();

        $morningStart = Setting::get('morning_start_time', '07:30');
        $morningEnd = Setting::get('morning_end_time', '08:00');
        $afternoonStart = Setting::get('afternoon_start_time', '16:00');
        $afternoonEndRegular = Setting::get('afternoon_end_time', '17:00');
        $afternoonEndFriday = Setting::get('afternoon_end_time_friday', '17:30');
        $afternoonEnd = $isFriday ? $afternoonEndFriday : $afternoonEndRegular;

        return [
            'name' => Setting::get('office_name', 'BPS Kabupaten Karanganyar'),
            'address' => Setting::get('office_address', 'Komplek Perkantoran Cangakan, Jl. Majapahit No. 11 B, Badran Asri, Bejen, Karanganyar 57712'),
            'latitude' => (float) Setting::get('office_latitude', -7.5978000),
            'longitude' => (float) Setting::get('office_longitude', 110.9505000),
            'radius' => (float) Setting::get('office_radius_meters', 150),
            'work_start_time' => $morningStart,
            'work_end_time' => $afternoonStart,
            'morning_start_time' => $morningStart,
            'morning_end_time' => $morningEnd,
            'afternoon_start_time' => $afternoonStart,
            'afternoon_end_time' => $afternoonEndRegular,
            'afternoon_end_time_friday' => $afternoonEndFriday,
            'today_afternoon_end_time' => $afternoonEnd,
            'is_friday' => $isFriday,
        ];
    }

    /**
     * Dapatkan status window jam buka / tutup presensi pagi & pulang secara real-time
     */
    public static function getAttendanceWindowStatus(?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now('Asia/Jakarta');
        $config = self::getOfficeConfig();
        $currentTime = $now->format('H:i');
        $isFriday = $config['is_friday'];
        $afternoonCloseTime = $config['today_afternoon_end_time'];

        // Status Pagi (07:30 - 08:00)
        $isMorningTooEarly = $currentTime < $config['morning_start_time']; // Sebelum 07:30
        $isMorningOnTime = ($currentTime >= $config['morning_start_time'] && $currentTime <= $config['morning_end_time']); // 07:30 - 08:00
        $isMorningLate = $currentTime > $config['morning_end_time']; // Lewat 08:00
        $isMorningOpen = $currentTime >= $config['morning_start_time']; // Buka mulai 07:30

        // Status Pulang (16:00 - 17:00 / 17:30 di hari Jumat)
        $isAfternoonTooEarly = $currentTime < $config['afternoon_start_time']; // Sebelum 16:00
        $isAfternoonOpen = ($currentTime >= $config['afternoon_start_time'] && $currentTime <= $afternoonCloseTime); // 16:00 - 17:00 (Jumat 17:30)
        $isAfternoonClosed = $currentTime > $afternoonCloseTime; // Lewat 17:00 / 17:30

        return [
            'current_time' => $currentTime,
            'is_friday' => $isFriday,
            'morning_start' => $config['morning_start_time'],
            'morning_end' => $config['morning_end_time'],
            'afternoon_start' => $config['afternoon_start_time'],
            'afternoon_end' => $afternoonCloseTime,
            'afternoon_end_friday' => $config['afternoon_end_time_friday'],
            'afternoon_end_regular' => $config['afternoon_end_time'],
            'is_morning_too_early' => $isMorningTooEarly,
            'is_morning_on_time' => $isMorningOnTime,
            'is_morning_late' => $isMorningLate,
            'is_morning_open' => $isMorningOpen,
            'is_afternoon_too_early' => $isAfternoonTooEarly,
            'is_afternoon_open' => $isAfternoonOpen,
            'is_afternoon_closed' => $isAfternoonClosed,
        ];
    }

    /**
     * Cek apakah koordinat user berada di dalam radius kantor BPS Karanganyar
     */
    public static function checkOfficeRadius(float $userLat, float $userLng): array
    {
        $config = self::getOfficeConfig();
        $distance = self::calculateHaversineDistance(
            $userLat,
            $userLng,
            $config['latitude'],
            $config['longitude']
        );

        $isInside = $distance <= $config['radius'];

        return [
            'is_inside' => $isInside,
            'distance_meters' => $distance,
            'allowed_radius' => $config['radius'],
            'office_lat' => $config['latitude'],
            'office_lng' => $config['longitude'],
            'office_name' => $config['name'],
        ];
    }

    /**
     * Evaluasi otomatis hari kerja lampau yang belum diabsen (lewat jam 12 malam dianggap kosong/alpa)
     */
    public static function evaluateAbsenceForIntern(User $user, ?Carbon $untilDate = null): int
    {
        if (!$user->isIntern() || !$user->internProfile) {
            return 0;
        }

        $profile = $user->internProfile;
        if (!$profile->start_date) {
            return 0;
        }

        $startDate = Carbon::parse($profile->start_date)->startOfDay();
        
        // Batas evaluasi: hari kemarin (karena hari ini masih ada kesempatan sampai 23:59:59)
        // Kecuali jika sekarang sudah lewat tengah malam dan mengevaluasi hari sebelumnya
        $endDate = $untilDate ? $untilDate->copy()->startOfDay() : Carbon::yesterday()->startOfDay();
        
        // Batasi juga dengan end_date magang jika sudah selesai
        if ($profile->end_date && $endDate->gt(Carbon::parse($profile->end_date))) {
            $endDate = Carbon::parse($profile->end_date)->startOfDay();
        }

        if ($startDate->gt($endDate)) {
            return 0;
        }

        $createdCount = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            // Cek apakah hari libur (Sabtu, Minggu, atau Tanggal Merah)
            $dayStatus = Holiday::checkDayStatus($current);

            if (!$dayStatus['is_off']) {
                // Hari kerja aktif: cek apakah sudah ada presensi
                $exists = Attendance::where('user_id', $user->id)
                    ->where('date', $current->toDateString())
                    ->exists();

                if (!$exists) {
                    Attendance::create([
                        'user_id' => $user->id,
                        'date' => $current->toDateString(),
                        'status' => 'tidak_hadir_tanpa_keterangan',
                        'sub_status' => 'alpa_otomatis',
                        'check_in_time' => null,
                        'activity_description' => 'Tidak melakukan presensi hingga batas waktu (Lewat jam 12 malam).',
                        'learning_obtained' => '-',
                        'constraints_encountered' => '-',
                        'is_confirmed' => true,
                        'approval_status' => 'approved',
                        'approval_notes' => 'Terekam otomatis oleh sistem (Alpa/Kosong).',
                    ]);
                    $createdCount++;
                }
            }

            $current->addDay();
        }

        return $createdCount;
    }

    /**
     * Evaluasi semua anak magang aktif
     */
    public static function evaluateAllInterns(): int
    {
        $interns = User::where('role', 'intern')->with('internProfile')->get();
        $total = 0;
        foreach ($interns as $intern) {
            $total += self::evaluateAbsenceForIntern($intern);
        }
        return $total;
    }

    /**
     * Ringkasan statistik kehadiran
     */
    public static function getInternStatistics(int $userId, ?int $month = null, ?int $year = null): array
    {
        $query = Attendance::where('user_id', $userId);

        if ($month) {
            $query->whereMonth('date', $month);
        }
        if ($year) {
            $query->whereYear('date', $year);
        }

        $attendances = $query->get();

        $hadir = $attendances->where('status', 'hadir')->count();
        $izinSakit = $attendances->where('status', 'tidak_hadir_keterangan')->count();
        $alpa = $attendances->where('status', 'tidak_hadir_tanpa_keterangan')->count();
        $total = $attendances->count();

        $approved = $attendances->where('approval_status', 'approved')->count();
        $pending = $attendances->where('approval_status', 'pending')->count();
        $rejected = $attendances->where('approval_status', 'rejected')->count();

        $attendanceRate = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'hadir' => $hadir,
            'izin_sakit' => $izinSakit,
            'alpa' => $alpa,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'attendance_rate' => $attendanceRate,
        ];
    }
}
