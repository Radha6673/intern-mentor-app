<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Jobs\CheckOverdueTasksJob;

Schedule::job(new CheckOverdueTasksJob)->dailyAt('9:30')->timezone('Asia/Kolkata');

