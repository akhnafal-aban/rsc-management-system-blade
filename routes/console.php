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

// Schedule auto check-out for members checked in for more than 5 hours
// Run every hour to check for members who need auto check-out
Schedule::command('attendance:auto-checkout --hours=3')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Auto check-out process completed successfully');

        // Add notification with more detailed information
        NotificationController::addCommandNotification(
            'Auto Check-out Process',
            'success',
            'Sistem otomatis melakukan check-out untuk member yang sudah lebih dari 3 jam di gym'
        );
    })
    ->onFailure(function () {
        Log::error('Auto check-out process failed');

        // Add notification
        NotificationController::addCommandNotification(
            'Auto Check-out Process',
            'failed',
            'Auto check-out process failed'
        );
    });
