<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EvaluateAbsenceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:evaluate-absence';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluasi hari kerja lampau dan tandai alpa/kosong otomatis bagi anak magang yang tidak absen hingga lewat jam 12 malam';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai evaluasi kehadiran magang BPS Karanganyar...');
        $count = \App\Services\AttendanceService::evaluateAllInterns();
        $this->info("Evaluasi selesai! Sebanyak {$count} catatan alpa otomatis berhasil dicatat.");
        return Command::SUCCESS;
    }
}
