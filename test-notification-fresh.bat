@echo off
echo Testing Fresh Notification System...
echo.

echo 1. Clearing all notifications...
php artisan tinker --execute="Cache::forget('scheduled_command_notifications');"
echo.

echo 2. Creating fresh test notification...
php artisan test:notification
echo.

echo 3. Verifying notification is unread...
php artisan tinker --execute="$data = app('App\Http\Controllers\NotificationController')->getScheduledCommandNotifications()->getData(true); echo 'has_new: ' . ($data['has_new'] ? 'true' : 'false') . PHP_EOL; echo 'notifications count: ' . count($data['notifications']) . PHP_EOL; echo 'first notification read status: ' . ($data['notifications'][0]['read'] ? 'true' : 'false') . PHP_EOL;"
echo.

echo 4. Starting development server...
echo Open your browser and check the notification badge.
echo The red badge should now appear!
echo Press Ctrl+C to stop the server.
echo.

php artisan serve
