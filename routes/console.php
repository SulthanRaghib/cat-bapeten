<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Exam Session Sweeper ────────────────────────────────────────────────
// Force-close ongoing exam sessions whose allowed duration has elapsed.
// Runs every minute, withoutOverlapping() prevents concurrent sweeps.
Schedule::command('app:force-close-expired-exams')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/sweeper.log'));
