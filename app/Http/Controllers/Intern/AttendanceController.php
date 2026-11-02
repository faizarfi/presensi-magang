<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\User;
use App\Services\AttendanceService;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function create()
    {
        /** @var User $user */
        $user = Auth::user();
        $today = Carbon::today('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');

        // Cek apakah sudah absen hari ini
        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today->toDateString())
            ->first();

        $dayStatus = Holiday::checkDayStatus($today);
        $officeConfig = AttendanceService::getOfficeConfig();
        $window = AttendanceService::getAttendanceWindowStatus($now);
        $workStartTime = $officeConfig['morning_start_time'];
        $workEndTime = $officeConfig['afternoon_start_time'];
        $isLateCurrent = $window['is_morning_late'];

        return view('intern.absen', compact(
            'user',
            'today',
            'now',
            'existing',
            'dayStatus',
            'officeConfig',
            'window',
            'workStartTime',
            'workEndTime',
            'isLateCurrent'
        ));
    }

    public function store(Request $request)
    {
        ini_set('memory_limit', '512M');

        /** @var User $user */
        $user = Auth::user();
        $today = Carbon::today('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');
        $window = AttendanceService::getAttendanceWindowStatus($now);

        // Cek data presensi hari ini
        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today->toDateString())
            ->first();

        // 1. Validasi dasar
        $rules = [
            'status' => ['required', 'in:hadir,pulang,tidak_hadir_keterangan,tidak_hadir_tanpa_keterangan'],
            'is_confirmed' => ['required', 'accepted'],
        ];

        $customMessages = [
            'status.required' => 'Pilih jenis presensi yang ingin Anda kirim.',
            'status.in' => 'Status presensi tidak valid.',
            'is_confirmed.accepted' => 'Anda harus mencentang konfirmasi kebenaran data presensi sebelum mengirim.',
        ];

        // 2. Validasi per status
        if ($request->status === 'hadir') {
            // Cek apakah belum jam 07:30
            if ($window['is_morning_too_early']) {
                return back()->withInput()->withErrors([
                    'status' => "Presensi pagi belum dibuka! Presensi kedatangan dibuka mulai pukul {$window['morning_start']} WIB.",
                ]);
            }

            if ($existing && $existing->check_in_time) {
                return back()->with('info', "Anda sudah melakukan presensi masuk hari ini pada pukul " . substr($existing->check_in_time, 0, 5) . " WIB.");
            }

            // Validasi GPS (tanpa foto selfie dan tanpa isian jurnal di pagi hari)
            $rules['latitude'] = ['required', 'numeric'];
            $rules['longitude'] = ['required', 'numeric'];
            $rules['late_reason'] = ['nullable', 'string', 'max:500'];

            $customMessages['latitude.required'] = 'Titik lokasi GPS diperlukan untuk presensi masuk.';
            $customMessages['longitude.required'] = 'Titik lokasi GPS diperlukan untuk presensi masuk.';

        } elseif ($request->status === 'pulang') {
            if (!$existing || !$existing->check_in_time) {
                return back()->withErrors([
                    'status' => 'Anda belum melakukan presensi masuk untuk hari ini. Presensi pulang hanya dapat dilakukan setelah presensi masuk.',
                ]);
            }

            if ($existing->status !== 'hadir') {
                return back()->withErrors([
                    'status' => 'Presensi pulang hanya berlaku bagi peserta magang yang berstatus Hadir di kantor.',
                ]);
            }

            if ($existing->check_out_time) {
                return back()->with('info', 'Anda sudah melakukan presensi pulang hari ini pada pukul ' . substr($existing->check_out_time, 0, 5) . ' WIB.');
            }

            // Cek waktu kepulangan (Buka jam 16:00, Tutup jam 17:00 / Jumat 17:30)
            if ($window['is_afternoon_too_early']) {
                return back()->withInput()->withErrors([
                    'status' => "Presensi pulang belum dibuka! Presensi kepulangan dibuka mulai pukul {$window['afternoon_start']} WIB.",
                ]);
            }

            if ($window['is_afternoon_closed']) {
                return back()->withInput()->withErrors([
                    'status' => "Batas waktu presensi pulang hari ini telah ditutup pada pukul {$window['afternoon_end']} WIB. Silakan konfirmasi ke pembimbing magang Anda.",
                ]);
            }

            // Saat pulang ADA ISINYA (Laporan Aktivitas & Pekerjaan Hari Ini) + GPS
            $rules['latitude'] = ['required', 'numeric'];
            $rules['longitude'] = ['required', 'numeric'];
            $rules['activity_description'] = ['required', 'string', 'min:5'];

            $customMessages['latitude.required'] = 'Titik lokasi GPS diperlukan untuk presensi pulang.';
            $customMessages['longitude.required'] = 'Titik lokasi GPS diperlukan untuk presensi pulang.';
            $customMessages['activity_description.required'] = 'Silakan isi laporan aktivitas / kegiatan yang telah Anda kerjakan hari ini sebelum melakukan presensi pulang.';
            $customMessages['activity_description.min'] = 'Laporan aktivitas minimal berisi 5 karakter.';

        } elseif ($request->status === 'tidak_hadir_keterangan') {
            $rules['sub_status'] = ['required', 'in:sakit,izin,dinas_luar'];
            $rules['activity_description'] = ['required', 'string', 'min:5'];
            $rules['attachment_file'] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'];
            $rules['attachment_file_camera'] = ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:10240'];

            $customMessages['sub_status.required'] = 'Pilih jenis keterangan tidak hadir (Sakit / Izin Kepentingan / Dinas Luar).';
            $customMessages['activity_description.required'] = 'Alasan / keterangan tidak hadir wajib diisi minimal 5 karakter.';
            $customMessages['activity_description.min'] = 'Alasan / keterangan tidak hadir minimal 5 karakter.';
            $customMessages['attachment_file.mimes'] = 'File lampiran harus bertipe PDF, JPG, atau PNG.';
            $customMessages['attachment_file.max'] = 'Ukuran file lampiran maksimal 10 MB.';
            $customMessages['attachment_file_camera.mimes'] = 'Foto kamera harus bertipe JPG atau PNG.';
            $customMessages['attachment_file_camera.max'] = 'Ukuran foto maksimal 10 MB.';
        }

        $validated = $request->validate($rules, $customMessages);

        // 3. Verifikasi Geolokasi Server-Side (Haversine Formula) jika Hadir atau Pulang
        $distance = null;
        $locationAddress = 'BPS Kabupaten Karanganyar';
        $lat = null;
        $lng = null;

        if (in_array($request->status, ['hadir', 'pulang']) && $request->filled('latitude')) {
            $lat = (float) $request->latitude;
            $lng = (float) $request->longitude;

            $geoCheck = AttendanceService::checkOfficeRadius($lat, $lng);
            $distance = $geoCheck['distance_meters'];

            if (!$geoCheck['is_inside']) {
                if (app()->environment('local')) {
                    $lat = $geoCheck['office_lat'];
                    $lng = $geoCheck['office_lng'];
                    $distance = 0;
                    $locationAddress = 'BPS Kabupaten Karanganyar (Simulasi Localhost)';
                } else {
                    return back()->withInput($request->except(['attachment_file']))->withErrors([
                        'location' => "Posisi Anda berada di luar radius kantor BPS Karanganyar! Jarak terdeteksi {$distance} meter dari kantor (Batas radius maksimal {$geoCheck['allowed_radius']} meter). Silakan lakukan presensi di lingkungan kantor BPS Karanganyar.",
                    ]);
                }
            } else {
                $locationAddress = $request->input('location_address', 'BPS Kabupaten Karanganyar');
            }
        } elseif (in_array($request->status, ['hadir', 'pulang']) && app()->environment('local') && !$request->filled('latitude')) {
            $office = AttendanceService::getOfficeConfig();
            $lat = $office['latitude'];
            $lng = $office['longitude'];
            $distance = 0;
            $locationAddress = 'BPS Kabupaten Karanganyar (Simulasi Localhost)';
        }

        // Upload attachment jika ada (untuk izin/sakit)
        $attachmentPath = null;
        if ($request->hasFile('attachment_file')) {
            $attachmentPath = $request->file('attachment_file')->store('attendances/attachments', 'local');
        } elseif ($request->hasFile('attachment_file_camera')) {
            $attachmentPath = $request->file('attachment_file_camera')->store('attendances/attachments', 'local');
        }

        // 4. Penanganan Status HADIR (Presensi Masuk Pagi)
        if ($request->status === 'hadir') {
            $isLate = false;
            $lateMinutes = 0;
            $lateReason = null;
            $morningEndTime = Carbon::createFromTimeString($window['morning_end']);

            if ($now->format('H:i:s') > $morningEndTime->format('H:i:s')) {
                $isLate = true;
                $lateMinutes = (int) $morningEndTime->diffInMinutes($now);
                $lateReason = $request->input('late_reason') ?: 'Terlambat hadir kantor';
            }

            $actDesc = 'Presensi kedatangan di kantor BPS Karanganyar.';

            Attendance::create([
                'user_id' => $user->id,
                'date' => $today->toDateString(),
                'status' => 'hadir',
                'sub_status' => 'hadir_kantor',
                'check_in_time' => $now->toTimeString(),
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes,
                'late_reason' => $lateReason,
                'latitude' => $lat,
                'longitude' => $lng,
                'distance_meters' => $distance,
                'location_address' => $locationAddress,
                'activity_description' => $actDesc,
                'learning_obtained' => null,
                'constraints_encountered' => '-',
                'attachment_file' => null,
                'photo' => null,
                'is_confirmed' => true,
                'approval_status' => 'pending',
            ]);

            $msg = "Presensi masuk berhasil dicatat pada pukul {$now->format('H:i')} WIB!";
            if ($isLate) {
                $msg .= " (Tercatat terlambat {$lateMinutes} menit). Selamat beraktivitas!";
            } else {
                $msg .= " (Tepat waktu). Selamat beraktivitas di BPS Karanganyar!";
            }

            return redirect()->route('intern.dashboard')->with('success', $msg);
        }

        // 5. Penanganan Status PULANG (Check-Out)
        if ($request->status === 'pulang') {
            if ($existing) {
                $existing->check_out_time = $now->toTimeString();
                if ($lat) $existing->check_out_latitude = $lat;
                if ($lng) $existing->check_out_longitude = $lng;
                $existing->activity_description = $request->activity_description;
                if ($request->filled('learning_obtained')) {
                    $existing->learning_obtained = $request->learning_obtained;
                }
                if ($request->filled('constraints_encountered')) {
                    $existing->constraints_encountered = $request->constraints_encountered;
                }
                $existing->save();

                return redirect()->route('intern.dashboard')
                    ->with('success', "Presensi pulang dan laporan aktivitas berhasil disimpan pada pukul {$now->format('H:i')} WIB! Selamat beristirahat.");
            }
        }

        // 6. Penanganan Status TIDAK HADIR KETERANGAN (Izin / Sakit)
        if ($request->status === 'tidak_hadir_keterangan') {
            if ($existing) {
                $existing->status = 'tidak_hadir_keterangan';
                $existing->sub_status = $request->sub_status;
                $existing->activity_description = $request->activity_description;
                if ($attachmentPath) {
                    $existing->attachment_file = $attachmentPath;
                }
                $existing->save();

                return redirect()->route('intern.dashboard')
                    ->with('success', 'Keterangan izin / sakit hari ini berhasil diperbarui!');
            }

            Attendance::create([
                'user_id' => $user->id,
                'date' => $today->toDateString(),
                'status' => 'tidak_hadir_keterangan',
                'sub_status' => $request->sub_status,
                'check_in_time' => null,
                'is_late' => false,
                'late_minutes' => 0,
                'late_reason' => null,
                'latitude' => null,
                'longitude' => null,
                'distance_meters' => null,
                'location_address' => null,
                'activity_description' => $request->activity_description,
                'learning_obtained' => null,
                'constraints_encountered' => null,
                'attachment_file' => $attachmentPath,
                'photo' => null,
                'is_confirmed' => true,
                'approval_status' => 'pending',
            ]);

            return redirect()->route('intern.dashboard')
                ->with('success', 'Laporan izin / sakit berhasil dikirim ke pembimbing!');
        }

        // 7. Fallback (Alpa Mandiri)
        if ($existing) {
            $existing->status = 'tidak_hadir_tanpa_keterangan';
            $existing->sub_status = 'alpa_mandiri';
            $existing->save();
            return redirect()->route('intern.dashboard')->with('success', 'Status presensi berhasil diperbarui!');
        }

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today->toDateString(),
            'status' => 'tidak_hadir_tanpa_keterangan',
            'sub_status' => 'alpa_mandiri',
            'is_confirmed' => true,
            'approval_status' => 'pending',
        ]);

        return redirect()->route('intern.dashboard')->with('success', 'Presensi berhasil dicatat.');
    }

    /**
     * Presensi Pulang (Check-Out)
     */
    public function checkout(Request $request)
    {
        ini_set('memory_limit', '512M');

        /** @var User $user */
        $user = Auth::user();
        $today = Carbon::today('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');
        $window = AttendanceService::getAttendanceWindowStatus($now);

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today->toDateString())
            ->first();

        if (!$attendance) {
            return back()->with('error', 'Anda belum melakukan presensi masuk untuk hari ini.');
        }

        if ($attendance->status !== 'hadir') {
            return back()->with('error', 'Presensi pulang hanya berlaku bagi kehadiran di kantor (status Hadir).');
        }

        if ($attendance->check_out_time) {
            return back()->with('error', 'Anda sudah melakukan presensi pulang hari ini pukul ' . substr($attendance->check_out_time, 0, 5) . ' WIB.');
        }

        // Cek window kepulangan
        if ($window['is_afternoon_too_early']) {
            return back()->with('error', "Presensi pulang belum dibuka! Presensi pulang dibuka mulai pukul {$window['afternoon_start']} WIB.");
        }

        if ($window['is_afternoon_closed']) {
            return back()->with('error', "Batas waktu presensi pulang hari ini telah ditutup pada pukul {$window['afternoon_end']} WIB.");
        }

        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'activity_description' => ['required', 'string', 'min:5'],
        ], [
            'latitude.required' => 'Koordinat GPS Anda diperlukan untuk presensi pulang.',
            'longitude.required' => 'Koordinat GPS Anda diperlukan untuk presensi pulang.',
            'activity_description.required' => 'Silakan isi laporan aktivitas / kegiatan yang telah Anda kerjakan hari ini.',
            'activity_description.min' => 'Laporan aktivitas minimal 5 karakter.',
        ]);

        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;

        // Validasi radius kantor BPS
        $geoCheck = AttendanceService::checkOfficeRadius($lat, $lng);
        if (!$geoCheck['is_inside']) {
            if (app()->environment('local')) {
                $lat = $geoCheck['office_lat'];
                $lng = $geoCheck['office_lng'];
            } else {
                return back()->withInput()->with('error', "Posisi Anda berada di luar radius kantor BPS Karanganyar! Jarak: {$geoCheck['distance_meters']} m (Maks: {$geoCheck['allowed_radius']} m). Silakan lakukan presensi pulang di area kantor BPS.");
            }
        }

        $attendance->update([
            'check_out_time' => $now->toTimeString(),
            'check_out_latitude' => $lat,
            'check_out_longitude' => $lng,
            'activity_description' => $request->activity_description,
        ]);

        return redirect()->route('intern.dashboard')
            ->with('success', "Presensi pulang berhasil dicatat pada pukul {$now->format('H:i')} WIB! Selamat beristirahat.");
    }

    public function history(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Pastikan alpa hari lampau telah dievaluasi
        AttendanceService::evaluateAbsenceForIntern($user);

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $status = $request->get('status');

        $query = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year);

        if ($status && in_array($status, ['hadir', 'tidak_hadir_keterangan', 'tidak_hadir_tanpa_keterangan'])) {
            $query->where('status', $status);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();

        $stats = AttendanceService::getInternStatistics($user->id, (int) $month, (int) $year);

        return view('intern.riwayat', compact('user', 'attendances', 'stats', 'month', 'year', 'status'));
    }
}
