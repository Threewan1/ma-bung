<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Kirim reminder tiap 5 menit, withoutOverlapping biar tidak dobel eksekusi kalau jalannya lambat.
Schedule::command('reservations:send-reminders')
    ->everyFiveMinutes()
    ->withoutOverlapping();
