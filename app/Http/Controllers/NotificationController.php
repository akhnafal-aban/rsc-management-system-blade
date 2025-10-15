<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    public function getScheduledCommandNotifications(): JsonResponse
    {
        $notifications = Cache::get('scheduled_command_notifications', []);

        return response()->json([
            'notifications' => $notifications,
            'total' => count($notifications),
            'has_new' => ! empty($notifications) && $this->hasRecentNotifications($notifications),
        ]);
    }

    public function markNotificationsAsRead(): JsonResponse
    {
        $notifications = Cache::get('scheduled_command_notifications', []);

        // Mark all notifications as read
        foreach ($notifications as &$notification) {
            $notification['read'] = true;
        }

        Cache::put('scheduled_command_notifications', $notifications, now()->addDays(7));

        return response()->json(['success' => true]);
    }

    private function hasRecentNotifications(array $notifications): bool
    {
        $recentThreshold = now()->subMinutes(30)->timestamp;

        foreach ($notifications as $notification) {
            if (isset($notification['timestamp']) &&
                $notification['timestamp'] > $recentThreshold &&
                ! ($notification['read'] ?? false)) {
                return true;
            }
        }

        return false;
    }

    public static function addCommandNotification(string $commandName, string $status, ?string $message = null): void
    {
        $notifications = Cache::get('scheduled_command_notifications', []);

        $newNotification = [
            'command' => $commandName,
            'status' => $status, // 'success' or 'failed'
            'message' => $message,
            'timestamp' => now()->timestamp,
            'time' => now()->format('H:i:s'),
            'date' => now()->format('d M Y'),
            'read' => false,
        ];

        // Add new notification to the beginning of array
        array_unshift($notifications, $newNotification);

        // Keep only last 10 notifications
        if (count($notifications) > 10) {
            $notifications = array_slice($notifications, 0, 10);
        }

        Cache::put('scheduled_command_notifications', $notifications, now()->addDays(7));
    }
}
