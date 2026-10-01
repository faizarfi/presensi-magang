<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Holiday extends Model
{
    protected $fillable = [
        'holiday_date',
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
        ];
    }

    /**
     * Cek apakah tanggal tertentu adalah hari libur (Sabtu, Minggu, atau Tanggal Merah)
     * 
     * @param \DateTimeInterface|string $date
     * @return array{is_off: bool, type: string, name: string, description?: string}
     */
    public static function checkDayStatus(\DateTimeInterface|string $date): array
    {
        $carbon = Carbon::parse($date);
        
        // 1. Cek Akhir Pekan (Sabtu = 6, Minggu = 0)
        if ($carbon->isWeekend()) {
            return [
                'is_off' => true,
                'type' => 'weekend',
                'name' => $carbon->isSaturday() ? 'Hari Libur Akhir Pekan (Sabtu)' : 'Hari Libur Akhir Pekan (Minggu)',
            ];
        }

        // 2. Cek Tanggal Merah dari database
        $holiday = self::where('holiday_date', $carbon->toDateString())->first();
        if ($holiday) {
            return [
                'is_off' => true,
                'type' => 'national_holiday',
                'name' => $holiday->name,
                'description' => $holiday->description,
            ];
        }

        return [
            'is_off' => false,
            'type' => 'workday',
            'name' => 'Hari Kerja Aktif',
        ];
    }
}
