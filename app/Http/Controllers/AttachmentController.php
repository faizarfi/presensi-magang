<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Unduh atau buka berkas lampiran secara aman (hanya pemilik atau Admin)
     */
    public function show(Attendance $attendance)
    {
        /** @var User $user */
        $user = Auth::user();

        // Keamanan: Hanya Admin BPS atau Intern pemilik data yang berhak mengakses
        if (!$user->isAdmin() && $user->id !== $attendance->user_id) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki hak akses untuk melihat dokumen medis / surat izin ini.');
        }

        $filePath = $attendance->attachment_file;

        if (!$filePath) {
            abort(404, 'Dokumen lampiran tidak ditemukan.');
        }

        // Cek disk privat (storage/app/private)
        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->response($filePath);
        }

        // Fallback untuk berkas lama yang tersimpan di disk public
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->response($filePath);
        }

        abort(404, 'Berkas fisik surat keterangan tidak ditemukan pada server.');
    }
}
