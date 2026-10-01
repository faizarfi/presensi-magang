<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternEvaluation extends Model
{
    protected $fillable = [
        'user_id',
        'evaluator_id',
        'discipline_score',
        'responsibility_score',
        'initiative_score',
        'teamwork_score',
        'work_quality_score',
        'final_score',
        'predicate',
        'notes',
        'evaluation_date',
    ];

    protected function casts(): array
    {
        return [
            'discipline_score' => 'float',
            'responsibility_score' => 'float',
            'initiative_score' => 'float',
            'teamwork_score' => 'float',
            'work_quality_score' => 'float',
            'final_score' => 'float',
            'evaluation_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    /**
     * Hitung rata-rata tertimbang dan predikat
     */
    public static function computeEvaluation(float $d, float $r, float $i, float $t, float $w): array
    {
        $finalScore = round(($d + $r + $i + $t + $w) / 5, 2);

        if ($finalScore >= 85) {
            $predicate = 'A (Sangat Baik)';
        } elseif ($finalScore >= 75) {
            $predicate = 'B+ (Memuaskan)';
        } elseif ($finalScore >= 65) {
            $predicate = 'B (Baik)';
        } elseif ($finalScore >= 55) {
            $predicate = 'C (Cukup)';
        } else {
            $predicate = 'D (Kurang)';
        }

        return [
            'final_score' => $finalScore,
            'predicate' => $predicate,
        ];
    }

    public function getPredicateBadgeClassAttribute(): string
    {
        if (str_starts_with($this->predicate, 'A')) {
            return 'bg-emerald-100 text-emerald-800 border-emerald-300';
        } elseif (str_starts_with($this->predicate, 'B')) {
            return 'bg-blue-100 text-blue-800 border-blue-300';
        } elseif (str_starts_with($this->predicate, 'C')) {
            return 'bg-amber-100 text-amber-800 border-amber-300';
        } else {
            return 'bg-rose-100 text-rose-800 border-rose-300';
        }
    }
}
