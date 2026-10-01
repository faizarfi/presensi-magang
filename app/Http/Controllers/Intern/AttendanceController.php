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
        $today = Carbon::today();

        // Cek apakah sudah absen hari ini
        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today->toDateString())
            ->first();

        $dayStatus = Holiday::checkDayStatus($today);
        $officeConfig = AttendanceService::getOfficeConfig();
        $workStartTime = Setting::get('work_start_time', '07:30');
        $workEndTime = Setting::get('work_end_time', '16:00');
        $isLateCurrent = Carbon::now()->format('H:i') > $workStartTime;

        return view('intern.absen', compact(
            'user',
            'today',
            'existing',
            'dayStatus',
            'officeConfig',
            'workStartTime',
            'workEndTime',
            'isLateCurrent'
        ));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $today = Carbon::today();

        // 1. Cek duplikasi absensi hari ini
        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today->toDateString())
            ->first();

        if ($existing) {
            return redirect()->route('intern.absen')
                ->with('error', 'Anda sudah melakukan presensi untuk hari ini (' . $today->translatedFormat('d F Y') . ').');
        }

        // 2. Validasi dasar
        $rules = [
            'status' => ['required', 'in:hadir,tidak_hadir_keterangan,tidak_hadir_tanpa_keterangan'],
            'is_confirmed' => ['required', 'accepted'],
        ];

        $customMessages = [
            'status.required' => 'Pilih salah satu status kehadiran.',
            'status.in' => 'Status kehadiran tidak valid.',
            'is_confirmed.accepted' => 'Anda harus mencentang konfirmasi kebenaran laporan sebelum mengirim.',
        ];

        // 3. Validasi khusus per status kehadiran
        if ($request->status === 'hadir') {
            $rules['latitude'] = ['required', 'numeric'];
            $rules['longitude'] = ['required', 'numeric'];
            $rules['activity_description'] = ['required', 'string', 'min:10'];
            $rules['learning_obtained'] = ['required', 'string', 'min:5'];
            $rules['constraints_encountered'] = ['required', 'string'];
            $rules['photo'] = ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'];
            $rules['late_reason'] = ['nullable', 'string', 'max:500'];

            $customMessages['latitude.required'] = 'Koordinat GPS Anda belum terdeteksi. Izinkan akses lokasi browser.';
            $customMessages['longitude.required'] = 'Koordinat GPS Anda belum terdeteksi. Izinkan akses lokasi browser.';
            $customMessages['activity_description.required'] = 'Uraian aktivitas harian wajib diisi.';
            $customMessages['activity_description.min'] = 'Uraian aktivitas minimal 10 karakter.';
            $customMessages['learning_obtained.required'] = 'Pembelajaran yang diperoleh wajib diisi.';
            $customMessages['constraints_encountered.required'] = 'Kendala yang dialami wajib diisi (isi tanda minus "-" jika tidak ada kendala).';
            $customMessages['photo.image'] = 'File dokumentasi harus berupa gambar (JPG/PNG).';
            $customMessages['photo.max'] = 'Ukuran foto dokumentasi maksimal 5 MB.';
        } elseif ($request->status === 'tidak_hadir_keterangan') {
            $rules['sub_status'] = ['required', 'in:sakit,izin,dinas_luar'];
            $rules['activity_description'] = ['required', 'string', 'min:5'];
            $rules['attachment_file'] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];

            $customMessages['sub_status.required'] = 'Pilih jenis keterangan tidak hadir (Sakit/Izin/Dinas Luar).';
            $customMessages['activity_description.required'] = 'Alasan / keterangan tidak hadir wajib diisi.';
            $customMessages['attachment_file.mimes'] = 'File lampiran harus bertipe PDF, JPG, atau PNG.';
            $customMessages['attachment_file.max'] = 'Ukuran file lampiran maksimal 5 MB.';
        }

        $validated = $request->validate($rules, $customMessages);

        // 4. Verifikasi Geolokasi Server-Side (Haversine Formula) jika Hadir
        $distance = null;
        $locationAddress = null;

        if ($request->status === 'hadir') {
            $lat = (float) $request->latitude;
            $lng = (float) $request->longitude;

            $geoCheck = AttendanceService::checkOfficeRadius($lat, $lng);
            $distance = $geoCheck['distance_meters'];

            if (!$geoCheck['is_inside']) {
                return back()->withInput()->withErrors([
                    'location' => "Posisi Anda berada di luar radius kantor BPS Karanganyar! Jarak Anda {$distance} meter dari kantor (Batas toleransi maksimal {$geoCheck['allowed_radius']} meter). Silakan lakukan presensi di lingkungan kantor BPS Karanganyar.",
                ]);
            }

            $locationAddress = $request->input('location_address', 'BPS Kabupaten Karanganyar');
        }

        // 5. Upload File (Foto Selfie atau Surat Lampiran)
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendances/photos', 'public');
        } elseif ($request->filled('photo_data')) {
            $photoData = $request->input('photo_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
                $rawBase64 = substr($photoData, strpos($photoData, ',') + 1);
                $ext = strtolower($type[1]);
                $decoded = base64_decode($rawBase64);
                if ($decoded !== false) {
                    $photoPath = 'attendances/photos/selfie_in_' . $user->id . '_' . time() . '.' . $ext;
                    Storage::disk('public')->put($photoPath, $decoded);
                }
            }
        }

        // Dokumen surat dokter / izin disimpan di storage PRIVAT (storage/app/private)
        $attachmentPath = null;
        if ($request->hasFile('attachment_file')) {
            $attachmentPath = $request->file('attachment_file')->store('attendances/attachments', 'local');
        }

        // 6. Deteksi Keterlambatan Jam Masuk
        $isLate = false;
        $lateMinutes = 0;
        $lateReason = null;
        $now = Carbon::now();

        if ($request->status === 'hadir') {
            $workStartTime = Setting::get('work_start_time', '07:30');
            $startTime = Carbon::createFromTimeString($workStartTime);

            if ($now->format('H:i:s') > $startTime->format('H:i:s')) {
                $isLate = true;
                $lateMinutes = (int) $startTime->diffInMinutes($now);
                $lateReason = $request->input('late_reason') ?? 'Terlambat tanpa keterangan rinci';
            }
        }

        // 7. Tentukan sub_status
        $subStatus = $request->sub_status;
        if ($request->status === 'hadir') {
            $subStatus = 'hadir_kantor';
        } elseif ($request->status === 'tidak_hadir_tanpa_keterangan') {
            $subStatus = 'alpa_mandiri';
        }

        // 8. Simpan Absensi Masuk
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today->toDateString(),
            'status' => $request->status,
            'sub_status' => $subStatus,
            'check_in_time' => $now->toTimeString(),
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
            'late_reason' => $lateReason,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'distance_meters' => $distance,
            'location_address' => $locationAddress,
            'activity_description' => $request->activity_description,
            'learning_obtained' => $request->learning_obtained,
            'constraints_encountered' => $request->constraints_encountered,
            'attachment_file' => $attachmentPath,
            'photo' => $photoPath,
            'is_confirmed' => true,
            'approval_status' => 'pending',
        ]);

        $msg = 'Laporan presensi masuk dan logbook aktivitas berhasil dikirim!';
        if ($isLate) {
            $msg .= " (Tercatat terlambat {$lateMinutes} menit)";
        }

        return redirect()->route('intern.dashboard')->with('success', $msg);
    }

    /**
     * Presensi Pulang (Check-Out)
     */
    public function checkout(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $today = Carbon::today();

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

        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], [
            'latitude.required' => 'Koordinat GPS Anda diperlukan untuk presensi pulang.',
            'longitude.required' => 'Koordinat GPS Anda diperlukan untuk presensi pulang.',
        ]);

        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;

        // Validasi radius kantor BPS
        $geoCheck = AttendanceService::checkOfficeRadius($lat, $lng);
        if (!$geoCheck['is_inside']) {
            return back()->with('error', "Posisi Anda berada di luar radius kantor BPS Karanganyar! Jarak: {$geoCheck['distance_meters']} m (Maks: {$geoCheck['allowed_radius']} m). Silakan lakukan presensi pulang di area kantor BPS.");
        }

        // Upload foto selfie check-out
        $checkoutPhotoPath = null;
        if ($request->hasFile('photo')) {
            $checkoutPhotoPath = $request->file('photo')->store('attendances/photos', 'public');
        } elseif ($request->filled('photo_data')) {
            $photoData = $request->input('photo_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
                $rawBase64 = substr($photoData, strpos($photoData, ',') + 1);
                $ext = strtolower($type[1]);
                $decoded = base64_decode($rawBase64);
                if ($decoded !== false) {
                    $checkoutPhotoPath = 'attendances/photos/selfie_out_' . $user->id . '_' . time() . '.' . $ext;
                    Storage::disk('public')->put($checkoutPhotoPath, $decoded);
                }
            }
        }

        $now = Carbon::now();
        $attendance->update([
            'check_out_time' => $now->toTimeString(),
            'check_out_latitude' => $lat,
            'check_out_longitude' => $lng,
            'check_out_photo' => $checkoutPhotoPath,
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
