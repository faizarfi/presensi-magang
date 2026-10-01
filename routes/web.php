<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Intern\DashboardController as InternDashboardController;
use App\Http\Controllers\Intern\AttendanceController as InternAttendanceController;
use App\Http\Controllers\Intern\ProfileController as InternProfileController;
use App\Http\Controllers\Intern\EvaluationController as InternEvaluationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\VerificationController as AdminVerificationController;
use App\Http\Controllers\Admin\RecapController as AdminRecapController;
use App\Http\Controllers\Admin\InternManagementController as AdminInternManagementController;
use App\Http\Controllers\Admin\HolidayController as AdminHolidayController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\EvaluationController as AdminEvaluationController;

// Halaman Utama: Redirect berdasarkan auth
Route::get('/', function () {
    if (Auth::check()) {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('intern.dashboard');
    }
    return redirect()->route('login');
});

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Unduh / Buka Dokumen Medis & Surat Izin Secara Aman (Private Storage)
Route::get('/attachments/{attendance}', [AttachmentController::class, 'show'])
    ->middleware('auth')
    ->name('attendance.attachment');

// ==========================================
// KELOMPOK ROUTE PESERTA MAGANG (INTERN)
// ==========================================
Route::middleware(['auth', 'role:intern'])->prefix('intern')->name('intern.')->group(function () {
    Route::get('/dashboard', [InternDashboardController::class, 'index'])->name('dashboard');
    
    // Presensi Datang, Pulang & Logbook
    Route::get('/absen', [InternAttendanceController::class, 'create'])->name('absen');
    Route::post('/absen', [InternAttendanceController::class, 'store'])->name('absen.store');
    Route::post('/absen/checkout', [InternAttendanceController::class, 'checkout'])->name('absen.checkout');
    Route::get('/riwayat', [InternAttendanceController::class, 'history'])->name('riwayat');

    // Penilaian & Evaluasi Akhir Magang
    Route::get('/evaluasi', [InternEvaluationController::class, 'show'])->name('evaluasi');

    // Profil
    Route::get('/profil', [InternProfileController::class, 'show'])->name('profil');
    Route::put('/profil', [InternProfileController::class, 'update'])->name('profil.update');
});

// ==========================================
// KELOMPOK ROUTE ADMIN / PEMBIMBING BPS
// ==========================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Verifikasi & ACC (Bisa ACC kapan saja tanpa batas waktu)
    Route::get('/verifikasi', [AdminVerificationController::class, 'index'])->name('verifikasi.index');
    Route::put('/verifikasi/{attendance}', [AdminVerificationController::class, 'update'])->name('verifikasi.update');
    Route::post('/verifikasi/bulk-approve', [AdminVerificationController::class, 'bulkApprove'])->name('verifikasi.bulk');

    // Rekapitulasi Presensi & Ekspor
    Route::get('/rekap', [AdminRecapController::class, 'index'])->name('rekap.index');
    Route::get('/rekap/export-pdf', [AdminRecapController::class, 'exportPdf'])->name('rekap.pdf');
    Route::get('/rekap/export-csv', [AdminRecapController::class, 'exportCsv'])->name('rekap.csv');

    // Manajemen Peserta Magang & Reset Device Lock
    Route::post('/interns/{intern}/reset-device', [AdminInternManagementController::class, 'resetDevice'])->name('interns.reset-device');
    Route::resource('interns', AdminInternManagementController::class);

    // Modul Penilaian Akhir & Form Evaluasi Magang
    Route::get('/evaluasi', [AdminEvaluationController::class, 'index'])->name('evaluasi.index');
    Route::get('/evaluasi/{intern}/edit', [AdminEvaluationController::class, 'edit'])->name('evaluasi.edit');
    Route::put('/evaluasi/{intern}', [AdminEvaluationController::class, 'update'])->name('evaluasi.update');
    Route::get('/evaluasi/{intern}/print', [AdminEvaluationController::class, 'print'])->name('evaluasi.print');

    // Manajemen Hari Libur & Tanggal Merah
    Route::get('/holidays', [AdminHolidayController::class, 'index'])->name('holidays.index');
    Route::post('/holidays', [AdminHolidayController::class, 'store'])->name('holidays.store');
    Route::delete('/holidays/{holiday}', [AdminHolidayController::class, 'destroy'])->name('holidays.destroy');

    // Pengaturan Lokasi BPS & Radius
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
    Route::put('/settings/password', [AdminSettingController::class, 'updatePassword'])->name('settings.password');
});
