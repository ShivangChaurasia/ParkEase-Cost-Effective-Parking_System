<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('parking:process-closures')->daily()->at('00:30')->timezone('Asia/Kolkata');
Schedule::command('bookings:update-status')->everyFiveMinutes();
