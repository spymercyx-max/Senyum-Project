<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Purge data soft-deleted yang melewati masa retensi (default 60 hari) — tiap malam.
Schedule::command('senyum:purge-expired-deleted-data')->dailyAt('02:00');
