<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CommandNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    private const CACHE_TTL = 300; // 5 menit cache untuk notifikasi

    private const CACHE_KEY = 'command_notifications_list';

    public function getScheduledCommandNotifications(): JsonResponse
    {
        // Cache key yang stabil - akan di-invalidate saat ada notifikasi baru
        $result = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function (): array {
            $notifications = CommandNotification::query()
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->map(function (CommandNotification $notification) {
                    $createdAt = $notification->created_at ?? now();
                    $checkoutAt = $notification->checkout_at;

                    return [
                        'id' => $notification->id,
                        'command' => $notification->command,
                        'status' => $notification->status,
                        'message' => $notification->message,
                        'member_name' => $notification->member_name,
                        'checkout_time' => $checkoutAt?->format('H:i'),
                        'timestamp' => $createdAt->timestamp,
                        'time' => $createdAt->format('H:i:s'),
                        'date' => $createdAt->format('d M Y'),
                        'read' => $notification->is_read,
                    ];
                })
                ->all();

            return [
                'notifications' => $notifications,
                'total' => count($notifications),
                'has_new' => $this->hasRecentNotifications($notifications),
            ];
        });

        return response()->json($result);
    }

    public function markNotificationsAsRead(): JsonResponse
    {
        CommandNotification::query()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Invalidate cache saat notifikasi di-mark as read
        Cache::forget(self::CACHE_KEY);

        return response()->json(['success' => true]);
    }

    private function hasRecentNotifications(array $notifications): bool
    {
        $recentThreshold = now()->subMinutes(30)->timestamp;

        foreach ($notifications as $notification) {
            if (($notification['timestamp'] ?? 0) > $recentThreshold &&
                ! ($notification['read'] ?? false)) {
                return true;
            }
        }

        return false;
    }

    public static function addCommandNotification(string $commandName, string $status, ?string $message = null, ?string $memberName = null, ?string $checkoutTime = null): void
    {
        CommandNotification::query()->create([
            'command' => $commandName,
            'status' => $status,
            'message' => $message,
            'member_name' => $memberName,
            'checkout_at' => $checkoutTime ? now()->setTimeFromTimeString($checkoutTime) : null,
            'is_read' => false,
        ]);

        // Invalidate cache saat ada notifikasi baru
        Cache::forget(self::CACHE_KEY);
    }
}
