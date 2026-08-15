<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('due_reminder:send')->everyMinute();
Schedule::command('rent:generate-due')->daily();
Schedule::command('reminder:send')->daily();
Schedule::command('property_alerts:send')->daily();
Schedule::command('applicants:send-expiry-matching-properties')->daily();

Schedule::command('maintenance_items_alerts:send')->daily();
Schedule::command('property_documents_alerts:send')->daily();
Schedule::command('tenancy_agreements_alerts:send')->daily();
Schedule::command('property_agreements_alerts:send')->daily();
Schedule::command('applicants_alerts:send')->daily();
