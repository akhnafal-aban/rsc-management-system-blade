@echo off
echo Testing Notification System...
echo.

echo 1. Clearing old notifications...
php artisan tinker --execute="Cache::forget('scheduled_command_notifications');"
echo.

echo 2. Creating new test notification...
php artisan test:notification
echo.

echo 3. Checking notification data...
php artisan tinker --execute="dd(app('App\Http\Controllers\NotificationController')->getScheduledCommandNotifications()->getData(true));"
echo.

echo 4. Starting development server (optional)...
echo You can now open your browser and check the notification badge.
echo Press Ctrl+C to stop the server.
echo.

php artisan serve
