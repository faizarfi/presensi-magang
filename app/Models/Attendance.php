<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'status',
        'sub_status',
        'check_in_time',
        'check_out_time',
        'is_late',
        'late_minutes',
        'late_reason',
        'latitude',
        'longitude',
        'check_out_latitude',
        'check_out_longitude',
        'distance_meters',
        'location_address',
        'activity_description',
        'learning_obtained',
        'constraints_encountered',
        'attachment_file',
        'photo',
        'check_out_photo',
        'is_confirmed',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_confirmed' => 'boolean',
            'is_late' => 'boolean',
            'late_minutes' => 'integer',
            'approved_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'check_out_latitude' => 'float',
            'check_out_longitude' => 'float',
            'distance_meters' => 'float',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'hadir' => 'Hadir',
            'tidak_hadir_keterangan' => 'Tidak Hadir (Izin/Sakit)',
            'tidak_hadir_tanpa_keterangan' => 'Tidak Hadir Tanpa Keterangan (Alpa)',
            default => ucfirst($this->status),
        };
    }

    public function getSubStatusLabelAttribute(): string
    {
        return match ($this->sub_status) {
            'sakit' => 'Sakit',
            'izin' => 'Izin Kepentingan',
            'dinas_luar' => 'Tugas / Dinas Luar',
            'alpa_otomatis' => 'Alpa (Otomatis Lewat Jam 12 Malam)',
            'hadir_kantor' => 'Hadir di Kantor BPS',
            default => $this->sub_status ? ucfirst(str_replace('_', ' ', $this->sub_status)) : '-',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'hadir' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'tidak_hadir_keterangan' => 'bg-amber-100 text-amber-800 border-amber-200',
            'tidak_hadir_tanpa_keterangan' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }

    public function getApprovalBadgeClassAttribute(): string
    {
        return match ($this->approval_status) {
            'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-300',
            'pending' => 'bg-amber-50 text-amber-700 border-amber-300',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-300',
            default => 'bg-slate-50 text-slate-700 border-slate-300',
        };
    }

    public function getWorkDurationFormattedAttribute(): string
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return '-';
        }

        $in = \Carbon\Carbon::createFromTimeString($this->check_in_time);
        $out = \Carbon\Carbon::createFromTimeString($this->check_out_time);

        if ($out->lt($in)) {
            return '-';
        }

        $totalMinutes = $in->diffInMinutes($out);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return "{$hours} jam {$minutes} menit";
    }

    public function getLateBadgeClassAttribute(): string
    {
        return $this->is_late
            ? 'bg-amber-100 text-amber-800 border-amber-300'
            : 'bg-emerald-100 text-emerald-800 border-emerald-300';
    }

    public function getLateLabelAttribute(): string
    {
        if ($this->status !== 'hadir') {
            return '-';
        }
        return $this->is_late ? "Terlambat ({$this->late_minutes} mnt)" : "Tepat Waktu";
    }
}
