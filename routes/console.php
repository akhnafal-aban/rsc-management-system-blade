<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule membership expiration check to run daily at midnight
Schedule::command('memberships:expire')
    ->dailyAt('00:00')
    // ->everyMinute()
    ->withoutOverlapping() // Disabled for testing
    ->runInBackground() // Disabled for testing - run in foreground to see output
    ->onSuccess(function () {
        // Log success if needed
        Log::info('Membership expiration check completed successfully');

        // Get expired members from cache
        $expiredMembers = Cache::get('last_expired_members', []);

        if (! empty($expiredMembers)) {
            // Create notification for each expired member
            foreach ($expiredMembers as $memberName) {
                NotificationController::addCommandNotification(
                    'Membership Expiration Check',
                    'success',
                    null,
                    $memberName,
                    null // No checkout time for membership expiration
                );
            }
        } else {
            // No members expired, send generic notification
            NotificationController::addCommandNotification(
                'Membership Expiration Check',
                'success',
                'Tidak ada keanggotaan yang expired hari ini'
            );
        }
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

// Schedule member status update to run daily at 01:00
Schedule::command('members:update-status')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Member status update completed successfully');
        
        NotificationController::addCommandNotification(
            'Member Status Update',
            'success',
            'Member statuses have been updated successfully'
        );
    })
    ->onFailure(function () {
        Log::error('Member status update failed');
        
        NotificationController::addCommandNotification(
            'Member Status Update',
            'failed',
            'Member status update failed'
        );
    });

// Test command for notifications - runs every 30 seconds for testing
// Schedule::command('test:notification')
//     ->everyThirtySeconds()
//     ->onSuccess(function () {
//         NotificationController::addCommandNotification(
//             'Test Notification',
//             'success',
//             'Ini adalah notifikasi test yang berjalan setiap 30 detik'
//         );
//     });
