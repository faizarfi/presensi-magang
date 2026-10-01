<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternProfile extends Model
{
    protected $fillable = [
        'user_id',
        'nim_nisn',
        'institution',
        'major',
        'division',
        'mentor_name',
        'start_date',
        'end_date',
        'duration_months',
        'address',
        'bio',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'duration_months' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper: Total durasi magang dalam hari
    public function getTotalDaysAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) return 0;
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    // Helper: Hari yang telah berlalu
    public function getElapsedDaysAttribute(): int
    {
        if (!$this->start_date) return 0;
        $today = now()->startOfDay();
        if ($today < $this->start_date) return 0;
        if ($this->end_date && $today > $this->end_date) return $this->total_days;
        return $this->start_date->diffInDays($today) + 1;
    }

    // Helper: Sisa hari magang
    public function getRemainingDaysAttribute(): int
    {
        if (!$this->end_date) return 0;
        $today = now()->startOfDay();
        if ($today > $this->end_date) return 0;
        return $today->diffInDays($this->end_date);
    }

    // Helper: Durasi dalam bulan (angka)
    public function getDurationMonthsDisplayAttribute(): int
    {
        if ($this->duration_months && $this->duration_months > 0) {
            return (int) $this->duration_months;
        }
        if ($this->start_date && $this->end_date) {
            $days = $this->start_date->diffInDays($this->end_date) + 1;
            $months = (int) round($days / 30);
            return max(1, min(12, $months));
        }
        return 3;
    }

    // Helper: Label durasi berformat (contoh: "3 Bulan")
    public function getDurationLabelAttribute(): string
    {
        return $this->duration_months_display . ' Bulan';
    }
}
