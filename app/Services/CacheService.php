<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    public const CACHE_TTL_SHORT = 60; // 1 menit

    public const CACHE_TTL_MEDIUM = 300; // 5 menit

    public const CACHE_TTL_LONG = 3600; // 1 jam

    /**
     * Generate cache key untuk active members count
     */
    public static function getActiveMembersCountKey(): string
    {
        return 'active_members_count';
    }

    /**
     * Generate cache key untuk attendance stats
     */
    public static function getAttendanceStatsKey(string $date): string
    {
        return 'attendance_stats_'.$date;
    }

    /**
     * Generate cache key untuk member search results
     */
    public static function getMemberSearchKey(string $query, int $perPage): string
    {
        return 'member_search_'.md5($query.'_'.$perPage);
    }

    /**
     * Generate cache key untuk today's check-in status
     */
    public static function getTodayCheckInStatusKey(string $date): string
    {
        return 'today_checkin_status_'.$date;
    }

    /**
     * Invalidate semua cache yang berkaitan dengan attendance
     */
    public static function invalidateAttendanceCaches(): void
    {
        $today = now()->format('Y-m-d');

        // Invalidate attendance stats
        Cache::forget(self::getAttendanceStatsKey($today));
        Cache::forget(self::getTodayCheckInStatusKey($today));

        // Invalidate member search results (flush semua karena query bisa bermacam-macam)
        self::flushCacheByPattern('*member_search_*');
    }

    /**
     * Invalidate cache yang berkaitan dengan member
     */
    public static function invalidateMemberCaches(): void
    {
        Cache::forget(self::getActiveMembersCountKey());

        // Invalidate member search results
        self::flushCacheByPattern('*member_search_*');
    }

    /**
     * Flush semua cache attendance
     */
    public static function flushAttendanceCaches(): void
    {
        $patterns = [
            '*attendance_stats_*',
            '*member_search_*',
            '*today_checkin_status_*',
            '*active_members_count*',
        ];

        foreach ($patterns as $pattern) {
            self::flushCacheByPattern($pattern);
        }
    }

    /**
     * Flush cache by pattern (compatible with different cache drivers)
     */
    private static function flushCacheByPattern(string $pattern): void
    {
        try {
            $cacheStore = Cache::getStore();

            // Only perform pattern flushing on Redis cache store
            if (method_exists($cacheStore, 'getRedis')) {
                $redis = $cacheStore->getRedis();
                $keys = $redis->keys($pattern);

                if (! empty($keys)) {
                    $redis->del($keys);
                }
            } else {
                // For non-Redis drivers, we can't flush by pattern
                // This is expected behavior for database/file cache drivers
                logger()->debug('Cache pattern flushing not available for current cache driver: '.get_class($cacheStore));
            }
        } catch (\Throwable $e) {
            logger()->warning('Failed to flush cache pattern: '.$e->getMessage());
        }
    }
}
