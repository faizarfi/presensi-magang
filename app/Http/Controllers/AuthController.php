<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            return $user->role === 'admin'
                ? redirect()->route('admin.dashboard')
                : redirect()->route('intern.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            // Pencegahan Titip Absen: Device Binding khusus Peserta Magang
            if ($user->role === 'intern') {
                $deviceId = $request->input('device_id');
                $deviceName = $request->input('device_name', 'Perangkat Mobile/PC');

                if ($deviceId) {
                    if (!$user->device_id) {
                        // Pertama kali login: kunci akun ke perangkat ini
                        $user->update([
                            'device_id' => $deviceId,
                            'device_name' => $deviceName,
                            'device_locked_at' => now(),
                        ]);
                    } elseif ($user->device_id !== $deviceId) {
                        // Mencoba login dari perangkat berbeda -> Blokir & Logout
                        Auth::logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();

                        return back()->withErrors([
                            'email' => "AKSES DITOLAK (Cegah Titip Absen): Akun Anda telah terkunci pada perangkat lain [{$user->device_name}]. Sistem hanya mengizinkan 1 perangkat utama per peserta. Silakan hubungi Pembimbing/Admin BPS Karanganyar untuk mereset kunci perangkat jika Anda berganti HP.",
                        ])->onlyInput('email');
                    }
                }
            }

            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Selamat datang kembali, ' . $user->name . ' (Admin BPS)');
            } else {
                return redirect()->intended(route('intern.dashboard'))
                    ->with('success', 'Selamat datang kembali, ' . $user->name . ' (Peserta Magang)');
            }
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar dari sistem.');
    }
}
