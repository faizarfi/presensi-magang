<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternEvaluation;
use App\Models\Setting;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status'); // 'all', 'evaluated', 'pending'

        $query = User::where('role', 'intern')
            ->with(['internProfile', 'evaluation.evaluator', 'attendances']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('internProfile', function ($p) use ($search) {
                      $p->where('institution', 'like', "%{$search}%")
                        ->orWhere('major', 'like', "%{$search}%")
                        ->orWhere('division', 'like', "%{$search}%");
                  });
            });
        }

        if ($status === 'evaluated') {
            $query->has('evaluation');
        } elseif ($status === 'pending') {
            $query->doesntHave('evaluation');
        }

        $interns = $query->orderBy('name')->paginate(12)->withQueryString();

        $totalInterns = User::where('role', 'intern')->count();
        $evaluatedCount = InternEvaluation::count();
        $pendingCount = max(0, $totalInterns - $evaluatedCount);

        return view('admin.evaluasi.index', compact(
            'interns',
            'search',
            'status',
            'totalInterns',
            'evaluatedCount',
            'pendingCount'
        ));
    }

    public function edit(User $intern)
    {
        if ($intern->role !== 'intern') {
            abort(404);
        }

        $intern->load(['internProfile', 'evaluation', 'attendances']);

        // Hitung statistik presensi untuk membantu pembimbing
        $stats = AttendanceService::getInternStatistics($intern->id);
        $totalDays = $intern->attendances->count();
        $lateCount = $intern->attendances->where('is_late', true)->count();

        // Saran nilai kedisiplinan otomatis berdasarkan tingkat kehadiran
        $attendanceRate = $stats['attendance_rate'];
        $suggestedDiscipline = max(60, min(100, round($attendanceRate)));

        $evaluation = $intern->evaluation;

        return view('admin.evaluasi.form', compact(
            'intern',
            'evaluation',
            'stats',
            'lateCount',
            'suggestedDiscipline'
        ));
    }

    public function update(Request $request, User $intern)
    {
        if ($intern->role !== 'intern') {
            abort(404);
        }

        $request->validate([
            'discipline_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'responsibility_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'initiative_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'teamwork_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'work_quality_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'evaluation_date' => ['required', 'date'],
        ], [
            'discipline_score.required' => 'Nilai kedisiplinan wajib diisi (0-100).',
            'responsibility_score.required' => 'Nilai tanggung jawab wajib diisi (0-100).',
            'initiative_score.required' => 'Nilai inisiatif wajib diisi (0-100).',
            'teamwork_score.required' => 'Nilai kerjasama wajib diisi (0-100).',
            'work_quality_score.required' => 'Nilai kualitas hasil kerja wajib diisi (0-100).',
        ]);

        $d = (float) $request->discipline_score;
        $r = (float) $request->responsibility_score;
        $i = (float) $request->initiative_score;
        $t = (float) $request->teamwork_score;
        $w = (float) $request->work_quality_score;

        $computed = InternEvaluation::computeEvaluation($d, $r, $i, $t, $w);

        InternEvaluation::updateOrCreate(
            ['user_id' => $intern->id],
            [
                'evaluator_id' => Auth::id(),
                'discipline_score' => $d,
                'responsibility_score' => $r,
                'initiative_score' => $i,
                'teamwork_score' => $t,
                'work_quality_score' => $w,
                'final_score' => $computed['final_score'],
                'predicate' => $computed['predicate'],
                'notes' => $request->notes,
                'evaluation_date' => $request->evaluation_date,
            ]
        );

        return redirect()->route('admin.evaluasi.index')
            ->with('success', "Penilaian akhir magang untuk {$intern->name} berhasil disimpan dengan Nilai Akhir: {$computed['final_score']} ({$computed['predicate']}).");
    }

    public function print(User $intern)
    {
        if ($intern->role !== 'intern') {
            abort(404);
        }

        $intern->load(['internProfile', 'evaluation.evaluator', 'attendances']);

        if (!$intern->evaluation) {
            return back()->with('error', "Peserta magang {$intern->name} belum memiliki data evaluasi penilaian.");
        }

        $officeConfig = AttendanceService::getOfficeConfig();
        $stats = AttendanceService::getInternStatistics($intern->id);
        $evaluation = $intern->evaluation;

        return view('admin.evaluasi.print', compact(
            'intern',
            'evaluation',
            'officeConfig',
            'stats'
        ));
    }
}
