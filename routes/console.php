<?php

use Illuminate\Foundation\Inspiring;

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\CheckTenancyExpiry;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(CheckTenancyExpiry::class)->daily();
Schedule::command('notifications:collection-alerts')->daily();
