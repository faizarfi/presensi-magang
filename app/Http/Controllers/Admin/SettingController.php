<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    public function index()
    {
        $config = AttendanceService::getOfficeConfig();

        return view('admin.settings.index', compact('config'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'office_name' => ['required', 'string', 'max:255'],
            'office_address' => ['required', 'string', 'max:500'],
            'office_latitude' => ['required', 'numeric', 'between:-90,90'],
            'office_longitude' => ['required', 'numeric', 'between:-180,180'],
            'office_radius_meters' => ['required', 'numeric', 'min:10', 'max:5000'],
            'morning_start_time' => ['nullable', 'string'],
            'morning_end_time' => ['nullable', 'string'],
            'afternoon_start_time' => ['nullable', 'string'],
            'afternoon_end_time' => ['nullable', 'string'],
            'afternoon_end_time_friday' => ['nullable', 'string'],
            'work_start_time' => ['nullable', 'string'],
            'work_end_time' => ['nullable', 'string'],
        ], [
            'office_name.required' => 'Nama instansi wajib diisi.',
            'office_address.required' => 'Alamat kantor wajib diisi.',
            'office_latitude.required' => 'Latitude kantor wajib diisi.',
            'office_longitude.required' => 'Longitude kantor wajib diisi.',
            'office_radius_meters.required' => 'Toleransi radius kehadiran wajib diisi.',
            'office_radius_meters.min' => 'Toleransi radius minimal 10 meter.',
        ]);

        $morningStart = $request->input('morning_start_time') ?: ($request->input('work_start_time') ?: '07:30');
        $morningEnd = $request->input('morning_end_time') ?: '08:00';
        $afternoonStart = $request->input('afternoon_start_time') ?: ($request->input('work_end_time') ?: '16:00');
        $afternoonEnd = $request->input('afternoon_end_time') ?: '17:00';
        $afternoonEndFriday = $request->input('afternoon_end_time_friday') ?: '17:30';

        Setting::set('office_name', $request->office_name, 'Nama Instansi');
        Setting::set('office_address', $request->office_address, 'Alamat Kantor');
        Setting::set('office_latitude', $request->office_latitude, 'Latitude Kantor');
        Setting::set('office_longitude', $request->office_longitude, 'Longitude Kantor');
        Setting::set('office_radius_meters', $request->office_radius_meters, 'Toleransi Radius (meter)');
        Setting::set('morning_start_time', $morningStart, 'Jam Buka Presensi Pagi');
        Setting::set('morning_end_time', $morningEnd, 'Batas Tepat Waktu Presensi Pagi');
        Setting::set('afternoon_start_time', $afternoonStart, 'Jam Buka Presensi Pulang');
        Setting::set('afternoon_end_time', $afternoonEnd, 'Jam Tutup Presensi Pulang (Senin - Kamis)');
        Setting::set('afternoon_end_time_friday', $afternoonEndFriday, 'Jam Tutup Presensi Pulang (Jumat)');
        Setting::set('work_start_time', $morningStart, 'Jam Mulai Kerja');
        Setting::set('work_end_time', $afternoonStart, 'Jam Selesai Kerja');

        return back()->with('success', 'Pengaturan jadwal presensi dan lokasi kantor BPS Karanganyar berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'new_password.required' => 'Kata sandi baru wajib diisi.',
            'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'new_password.min' => 'Kata sandi baru minimal 8 karakter.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini tidak cocok.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Kata sandi Admin Pembimbing BPS berhasil diperbarui.');
    }
}
