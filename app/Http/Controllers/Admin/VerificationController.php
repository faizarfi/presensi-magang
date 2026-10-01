<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $internId = $request->get('intern_id');
        $date = $request->get('date');

        $query = Attendance::with(['user.internProfile', 'approver']);

        if ($status && $status !== 'all') {
            $query->where('approval_status', $status);
        }

        if ($internId) {
            $query->where('user_id', $internId);
        }

        if ($date) {
            $query->where('date', $date);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();

        $interns = User::where('role', 'intern')->orderBy('name')->get();
        $pendingCount = Attendance::where('approval_status', 'pending')->count();
        $approvedCount = Attendance::where('approval_status', 'approved')->count();
        $rejectedCount = Attendance::where('approval_status', 'rejected')->count();

        return view('admin.verifikasi.index', compact(
            'attendances',
            'interns',
            'status',
            'internId',
            'date',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'approval_status' => ['required', 'in:approved,rejected'],
            'approval_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $attendance->update([
            'approval_status' => $request->approval_status,
            'approved_by' => Auth::id(),
            'approved_at' => Carbon::now(),
            'approval_notes' => $request->approval_notes,
        ]);

        $statusText = $request->approval_status === 'approved' ? 'disetujui (ACC)' : 'ditolak';

        return back()->with('success', "Laporan presensi atas nama {$attendance->user->name} tanggal {$attendance->date->translatedFormat('d M Y')} berhasil {$statusText}.");
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'attendance_ids' => ['required', 'array'],
            'attendance_ids.*' => ['exists:attendances,id'],
        ], [
            'attendance_ids.required' => 'Pilih minimal satu data presensi untuk disetujui sekaligus.',
        ]);

        $count = Attendance::whereIn('id', $request->attendance_ids)
            ->where('approval_status', '!=', 'approved')
            ->update([
                'approval_status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => Carbon::now(),
                'approval_notes' => $request->input('bulk_notes', 'Disetujui secara massal oleh Pembimbing BPS.'),
            ]);

        return back()->with('success', "Berhasil menyetujui (Bulk ACC) {$count} laporan presensi sekaligus.");
    }
}
