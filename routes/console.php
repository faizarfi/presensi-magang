<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Otomatisasi evaluasi alpa setelah jam 12 malam (00:01 WIB setiap hari)
Schedule::command('attendance:evaluate-absence')->dailyAt('00:01');
