<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule membership expiration check to run daily at midnight
Schedule::command('memberships:expire')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        // Log success if needed
        Log::info('Membership expiration check completed successfully');

        // Add notification
        NotificationController::addCommandNotification(
            'Membership Expiration Check',
            'success',
            'Sistem memeriksa dan memperbarui status keanggotaan yang sudah expired'
        );
    })
    ->onFailure(function () {
        // Log failure if needed
        Log::error('Membership expiration check failed');

        // Add notification
        NotificationController::addCommandNotification(
            'Membership Expiration Check',
            'failed',
            'Membership expiration check failed'
        );
    });

// Auto checkout is now handled by delayed jobs dispatched during check-in
// This provides more precise timing and better performance compared to cron-based scanning
