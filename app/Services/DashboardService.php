<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    private const CACHE_TTL = 300; // 5 menit cache

    private const CACHE_KEY_PREFIX = 'dashboard_data_';

    public function getDashboardData(): array
    {
        // Cache key berdasarkan hari - lebih stabil dan tidak menumpuk
        $cacheKey = self::CACHE_KEY_PREFIX.Carbon::today()->format('Y-m-d');

        return Cache::remember($cacheKey, self::CACHE_TTL, function (): array {
            return [
                'stats' => $this->getStats(),
                'activities' => $this->getRecentActivities(),
                'charts' => $this->getChartData(),
                'insights' => $this->getManagerInsights(),
            ];
        });
    }

    /**
     * Invalidate dashboard cache
     * Panggil method ini saat ada perubahan data yang mempengaruhi dashboard
     */
    public static function invalidateCache(): void
    {
        // Invalidate cache hari ini
        Cache::forget(self::CACHE_KEY_PREFIX.Carbon::today()->format('Y-m-d'));
        // Invalidate cache kemarin (jika masih ada)
        Cache::forget(self::CACHE_KEY_PREFIX.Carbon::yesterday()->format('Y-m-d'));
    }

    private function getStats(): array
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $yesterday = $today->copy()->subDay();

        // Gabungkan semua query stats dalam satu query untuk mengurangi round-trip
        $sql = <<<'SQL'
SELECT
    (SELECT COUNT(*) FROM members WHERE status = ?) AS active_members,
    (SELECT COUNT(*) FROM attendances WHERE DATE(check_in_time) = ?) AS today_checkins,
    (SELECT COUNT(*) FROM attendances WHERE DATE(check_in_time) = ?) AS yesterday_checkins,
    (SELECT COUNT(*) FROM attendances WHERE check_in_time BETWEEN ? AND ?) AS weekly_attendance,
    (SELECT COUNT(*) FROM attendances WHERE check_in_time BETWEEN ? AND ?) AS last_week_attendance,
    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE created_at BETWEEN ? AND ?) AS monthly_revenue,
    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE created_at BETWEEN ? AND ?) AS last_month_revenue,
    (SELECT COUNT(*) FROM members WHERE status = ? AND created_at BETWEEN ? AND ?) AS current_month_members,
    (SELECT COUNT(*) FROM members WHERE status = ? AND created_at BETWEEN ? AND ?) AS last_month_members
SQL;

        $stats = DB::selectOne($sql, [
            \App\Enums\MemberStatus::ACTIVE->value,
            $today->format('Y-m-d'),
            $yesterday->format('Y-m-d'),
            $startOfWeek,
            $endOfWeek,
            Carbon::now()->subWeek()->startOfWeek(),
            Carbon::now()->subWeek()->endOfWeek(),
            $startOfMonth,
            $endOfMonth,
            $startOfMonth->copy()->subMonth(),
            $endOfMonth->copy()->subMonth(),
            \App\Enums\MemberStatus::ACTIVE->value,
            $startOfMonth,
            $endOfMonth,
            \App\Enums\MemberStatus::ACTIVE->value,
            $startOfMonth->copy()->subMonth(),
            $endOfMonth->copy()->subMonth(),
        ]);

        $weeklyAttendance = (int) ($stats->weekly_attendance ?? 0);
        $weeklyAverage = $weeklyAttendance > 0 ? (int) round($weeklyAttendance / 7) : 0;
        $monthlyRevenue = (float) ($stats->monthly_revenue ?? 0);

        // Hitung perubahan langsung dari hasil query
        $todayCheckins = (int) ($stats->today_checkins ?? 0);
        $yesterdayCheckins = (int) ($stats->yesterday_checkins ?? 0);
        $todayChange = $this->calculateChange($todayCheckins, $yesterdayCheckins);

        $lastWeekAttendance = (int) ($stats->last_week_attendance ?? 0);
        $weeklyTrend = $this->calculateChange($weeklyAttendance, $lastWeekAttendance);

        $currentMonthMembers = (int) ($stats->current_month_members ?? 0);
        $lastMonthMembers = (int) ($stats->last_month_members ?? 0);
        $memberGrowth = $this->calculateChange($currentMonthMembers, $lastMonthMembers);

        $lastMonthRevenue = (float) ($stats->last_month_revenue ?? 0);
        $revenueGrowth = $this->calculateChange($monthlyRevenue, $lastMonthRevenue);

        return [
            [
                'title' => 'Member Aktif',
                'value' => (int) ($stats->active_members ?? 0),
                'change' => $memberGrowth,
                'icon' => 'users',
            ],
            [
                'title' => 'Check-in Hari Ini',
                'value' => $todayCheckins,
                'change' => $todayChange,
                'icon' => 'user-check',
            ],
            [
                'title' => 'Rata-rata Mingguan',
                'value' => $weeklyAverage,
                'change' => $weeklyTrend,
                'icon' => 'trending-up',
            ],
            [
                'title' => 'Pendapatan Bulanan',
                'value' => 'Rp '.number_format($monthlyRevenue, 0, ',', '.'),
                'change' => $revenueGrowth,
                'icon' => 'dollar-sign',
            ],
        ];
    }

    private function calculateChange(float|int $current, float|int $previous): ?array
    {
        if ($previous == 0) {
            if ($current > 0) {
                return [
                    'type' => 'increase',
                    'value' => '100%',
                ];
            }

            return ['type' => 'neutral'];
        }

        $change = (($current - $previous) / $previous) * 100;

        if (! is_finite($change) || abs($change) < 0.0001) {
            return [
                'type' => 'stable',
                'value' => '0%',
            ];
        }

        return [
            'type' => $change > 0 ? 'increase' : 'decrease',
            'value' => number_format(abs($change), 1).'%',
        ];
    }

    private function getRecentActivities(): array
    {
        return Attendance::with('member')
            ->latest('check_in_time')
            ->limit(10)
            ->get()
            ->map(function ($attendance) {
                return [
                    'name' => $attendance->member->name,
                    'time' => $attendance->check_in_time->diffForHumans(),
                    'type' => 'Check-in',
                ];
            })
            ->toArray();
    }

    private function getChartData(): array
    {
        return [
            'weekly_trend' => $this->getWeeklyTrendData(),
            'member_distribution' => $this->getMemberDistributionData(),
            'daily_activity' => $this->getDailyActivityData(),
        ];
    }

    private function getWeeklyTrendData(): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $sql = <<<'SQL'
SELECT
    DATE(check_in_time) AS date,
    COUNT(*) AS count
FROM attendances
WHERE check_in_time BETWEEN ? AND ?
GROUP BY DATE(check_in_time)
ORDER BY DATE(check_in_time)
SQL;

        $weeklyData = collect(DB::select($sql, [$startOfWeek, $endOfWeek]))->keyBy('date');

        $data = [];
        $labels = [];

        // Generate data untuk 7 hari dengan default 0 jika tidak ada data
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dateString = $date->format('Y-m-d');

            $count = $weeklyData->get($dateString, (object) ['count' => 0])->count;
            $data[] = (int) $count;
            $labels[] = $date->format('D');
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getMemberDistributionData(): array
    {
        $sql = <<<'SQL'
SELECT status, COUNT(*) AS total
FROM members
WHERE status IN (?, ?, ?)
GROUP BY status
SQL;

        $rows = DB::select($sql, [
            \App\Enums\MemberStatus::ACTIVE->value,
            \App\Enums\MemberStatus::EXPIRED->value,
            \App\Enums\MemberStatus::INACTIVE->value,
        ]);

        $counts = [
            \App\Enums\MemberStatus::ACTIVE->value => 0,
            \App\Enums\MemberStatus::EXPIRED->value => 0,
            \App\Enums\MemberStatus::INACTIVE->value => 0,
        ];

        foreach ($rows as $row) {
            $status = $row->status;
            $counts[$status] = (int) $row->total;
        }

        return [
            'labels' => ['Aktif', 'Expired', 'Tidak Aktif'],
            'data' => [
                $counts[\App\Enums\MemberStatus::ACTIVE->value],
                $counts[\App\Enums\MemberStatus::EXPIRED->value],
                $counts[\App\Enums\MemberStatus::INACTIVE->value],
            ],
            'colors' => ['#10B981', '#F59E0B', '#EF4444'],
        ];
    }

    private function getDailyActivityData(): array
    {
        // Data ini sudah diambil di getStats, gunakan cache atau ambil dari stats
        $today = Carbon::today();
        $target = 100; // Target harian
        $sql = 'SELECT COUNT(*) AS total FROM attendances WHERE DATE(check_in_time) = ?';
        $current = (int) (DB::selectOne($sql, [$today->format('Y-m-d')])->total ?? 0);
        $percentage = $target > 0 ? round(($current / $target) * 100, 1) : 0;

        return [
            'current' => $current,
            'target' => $target,
            'percentage' => $percentage,
        ];
    }

    private function getManagerInsights(): array
    {
        return [
            [
                'title' => 'Member dengan Aktivitas Tertinggi',
                'content' => $this->getTopActiveMember(),
                'type' => 'success',
                'icon' => 'icon-trophy',
            ],
            [
                'title' => 'Jam Puncak Kunjungan',
                'content' => $this->getPeakHours(),
                'type' => 'info',
                'icon' => 'icon-clock',
            ],
            [
                'title' => 'Member yang Perlu Diperhatikan',
                'content' => $this->getInactiveMembersAlert(),
                'type' => 'warning',
                'icon' => 'icon-alert-triangle',
            ],
            [
                'title' => 'Pendapatan vs Target',
                'content' => $this->getRevenueTargetStatus(),
                'type' => 'success',
                'icon' => 'icon-trending-up',
            ],
        ];
    }

    private function getWeeklyAverage(): int
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        return (int) round(Attendance::whereBetween('check_in_time', [$startOfWeek, $endOfWeek])->count() / 7);
    }

    private function getMonthlyRevenue(Carbon $startOfMonth, Carbon $endOfMonth): float
    {
        $sql = 'SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE created_at BETWEEN ? AND ?';

        return (float) (DB::selectOne($sql, [$startOfMonth, $endOfMonth])->total ?? 0.0);
    }

    private function getTopActiveMember(): string
    {
        $sql = <<<'SQL'
SELECT members.name AS member_name, COUNT(attendances.id) AS total_visits
FROM attendances
INNER JOIN members ON attendances.member_id = members.id
GROUP BY members.id, members.name
ORDER BY total_visits DESC
LIMIT 1
SQL;

        $result = DB::selectOne($sql);

        return $result
            ? $result->member_name.' ('.$result->total_visits.' kali kunjungan)'
            : 'Belum ada data';
    }

    private function getPeakHours(): string
    {
        $driver = DB::getDriverName();

        $hourExpression = match ($driver) {
            'sqlite' => "CAST(strftime('%H', check_in_time) AS INTEGER)",
            'pgsql' => 'EXTRACT(HOUR FROM check_in_time)',
            default => 'HOUR(check_in_time)',
        };

        $sql = <<<SQL
SELECT {$hourExpression} AS hour, COUNT(*) AS total
FROM attendances
WHERE check_in_time >= ?
GROUP BY {$hourExpression}
ORDER BY total DESC
LIMIT 1
SQL;

        $peakHour = DB::selectOne($sql, [now()->subDays(30)]);

        return $peakHour
            ? $peakHour->hour.':00 - '.((int) $peakHour->hour + 1).':00'
            : 'Belum ada data';
    }

    private function getInactiveMembersAlert(): string
    {
        $threshold = Carbon::now()->subDays(7);

        $sql = <<<'SQL'
SELECT COUNT(*) AS total
FROM members
WHERE status = ?
  AND last_check_in IS NOT NULL
  AND last_check_in < ?
SQL;

        $inactiveCount = (int) (DB::selectOne($sql, [
            \App\Enums\MemberStatus::ACTIVE->value,
            $threshold,
        ])->total ?? 0);

        return $inactiveCount > 0 ? $inactiveCount.' member belum check-in selama 7 hari terakhir' : 'Semua member aktif';
    }

    private function getRevenueTargetStatus(): string
    {
        $currentRevenue = $this->getMonthlyRevenue(
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        $target = 30000000;
        $percentage = ($currentRevenue / $target) * 100;

        return 'Rp '.number_format($currentRevenue, 0, ',', '.').' / Rp '.number_format($target, 0, ',', '.').' ('.number_format($percentage, 1).'%)';
    }
}
