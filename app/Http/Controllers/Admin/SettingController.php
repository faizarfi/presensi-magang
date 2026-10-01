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
            'work_start_time' => ['required', 'string'],
            'work_end_time' => ['required', 'string'],
        ], [
            'office_name.required' => 'Nama instansi wajib diisi.',
            'office_address.required' => 'Alamat kantor wajib diisi.',
            'office_latitude.required' => 'Latitude kantor wajib diisi.',
            'office_longitude.required' => 'Longitude kantor wajib diisi.',
            'office_radius_meters.required' => 'Toleransi radius kehadiran wajib diisi.',
            'office_radius_meters.min' => 'Toleransi radius minimal 10 meter.',
        ]);

        Setting::set('office_name', $request->office_name, 'Nama Instansi');
        Setting::set('office_address', $request->office_address, 'Alamat Kantor');
        Setting::set('office_latitude', $request->office_latitude, 'Latitude Kantor');
        Setting::set('office_longitude', $request->office_longitude, 'Longitude Kantor');
        Setting::set('office_radius_meters', $request->office_radius_meters, 'Toleransi Radius (meter)');
        Setting::set('work_start_time', $request->work_start_time, 'Jam Mulai Kerja');
        Setting::set('work_end_time', $request->work_end_time, 'Jam Selesai Kerja');

        return back()->with('success', 'Pengaturan lokasi kantor BPS Karanganyar dan toleransi radius berhasil diperbarui.');
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
