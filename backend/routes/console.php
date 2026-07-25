<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// SALON-OPS-ENHANCEMENTS.md, "Customers" (Tier 3) — birthday/anniversary
// reminders. Requires `php artisan schedule:run` to actually fire — same
// gap as the rest of the app's scheduling (nothing currently runs it; see
// docker-entrypoint.sh). A cron entry or supervisor job calling
// `php artisan schedule:run` every minute needs to be added to the
// production container/host before this goes live — tracked as a
// deployment task alongside "Queue the email sending" in the ops doc.
Schedule::command('customers:send-occasion-reminders')->dailyAt('08:00');
