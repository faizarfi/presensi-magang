<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\InternEvaluation;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    public function show()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load(['internProfile', 'evaluation.evaluator', 'attendances']);

        $evaluation = $user->evaluation;
        $stats = AttendanceService::getInternStatistics($user->id);
        $officeConfig = AttendanceService::getOfficeConfig();

        return view('intern.evaluasi', compact('user', 'evaluation', 'stats', 'officeConfig'));
    }
}
