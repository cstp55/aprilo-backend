<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automated Weekly AI Marketing Campaign
// Evaluates lead vs converted ratio with AI, selects product angle, and dispatches tailored campaigns
Schedule::command('aprilo:weekly-marketing-campaign')
    ->weeklyOn(1, '09:00')
    ->withoutOverlapping()
    ->runInBackground();

