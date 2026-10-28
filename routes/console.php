<?php

declare(strict_types=1);

use App\Support\PurgeOldExports;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Shared hosting has no supervisor: the cron-driven scheduler drains the
// database queue every minute (verification/reset/invitation e-mails, exports).
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->runInBackground();

Schedule::command('sanctum:prune-expired --hours=24')->daily();

Schedule::command('model:prune')->daily();

Schedule::call(new PurgeOldExports)->daily()->name('purge-old-exports');
