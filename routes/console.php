<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('clients:anonymize-expired')->dailyAt('02:30')->withoutOverlapping();
Schedule::command('audit-logs:prune-expired')->dailyAt('03:00')->withoutOverlapping();
Schedule::command('order-files:prune-expired')->dailyAt('03:30')->withoutOverlapping();
Schedule::command('quote-offers:expire')->dailyAt('00:10')->withoutOverlapping();
