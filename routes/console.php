<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic expiration of pending bookings every minute
Schedule::command('bookings:expire-pending --minutes=10')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
