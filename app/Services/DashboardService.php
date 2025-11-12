<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getDashboardData(): array
    {
        return [
            'stats' => $this->getStats(),
            'activities' => $this->getRecentActivities(),
            'charts' => $this->getChartData(),
            'insights' => $this->getManagerInsights(),
        ];
    }

    private function getStats(): array
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $sql = <<<'SQL'
SELECT
    COUNT(CASE WHEN status = ? THEN 1 END) AS active_members,
    (SELECT COUNT(*) FROM attendances WHERE DATE(check_in_time) = ?) AS today_checkins,
    (SELECT COUNT(*) FROM attendances WHERE check_in_time BETWEEN ? AND ?) AS weekly_attendance,
    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE created_at BETWEEN ? AND ?) AS monthly_revenue
FROM members
SQL;

        $stats = DB::selectOne($sql, [
            \App\Enums\MemberStatus::ACTIVE->value,
            $today->format('Y-m-d'),
            $startOfWeek,
            $endOfWeek,
            $startOfMonth,
            $endOfMonth,
        ]);

        $weeklyAttendance = (int) ($stats->weekly_attendance ?? 0);
        $weeklyAverage = $weeklyAttendance > 0 ? (int) round($weeklyAttendance / 7) : 0;
        $monthlyRevenue = (float) ($stats->monthly_revenue ?? 0);

        return [
            [
                'title' => 'Member Aktif',
                'value' => (int) ($stats->active_members ?? 0),
                'change' => $this->getMemberGrowthPercentage(),
                'icon' => 'users',
            ],
            [
                'title' => 'Check-in Hari Ini',
                'value' => (int) ($stats->today_checkins ?? 0),
                'change' => $this->getTodayAttendanceChange(),
                'icon' => 'user-check',
            ],
            [
                'title' => 'Rata-rata Mingguan',
                'value' => $weeklyAverage,
                'change' => $this->getWeeklyTrend(),
                'icon' => 'trending-up',
            ],
            [
                'title' => 'Pendapatan Bulanan',
                'value' => 'Rp '.number_format($monthlyRevenue, 0, ',', '.'),
                'change' => $this->getRevenueGrowthPercentage($startOfMonth, $endOfMonth),
                'icon' => 'dollar-sign',
            ],
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

    private function getMemberGrowthPercentage(): ?array
    {
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        $currentCountSql = <<<'SQL'
SELECT COUNT(*) AS total
FROM members
WHERE status = ?
  AND created_at BETWEEN ? AND ?
SQL;

        $currentCountResult = DB::selectOne($currentCountSql, [
            \App\Enums\MemberStatus::ACTIVE->value,
            $currentMonthStart,
            $currentMonthEnd,
        ]);

        $lastMonthCountSql = <<<'SQL'
SELECT COUNT(*) AS total
FROM members
WHERE status = ?
  AND created_at BETWEEN ? AND ?
SQL;

        $lastMonthCountResult = DB::selectOne($lastMonthCountSql, [
            \App\Enums\MemberStatus::ACTIVE->value,
            $startOfLastMonth,
            $endOfLastMonth,
        ]);

        $currentCount = (int) ($currentCountResult->total ?? 0);
        $lastMonthCount = (int) ($lastMonthCountResult->total ?? 0);

        if ($lastMonthCount === 0) {
            if ($currentCount > 0) {
                return [
                    'type' => 'increase',
                    'value' => '100%',
                ];
            }

            return [
                'type' => 'neutral',
            ];
        }

        $growth = (($currentCount - $lastMonthCount) / $lastMonthCount) * 100;

        if (! is_finite($growth) || $growth == 0) {
            return [
                'type' => 'stable',
                'value' => '0%',
            ];
        }

        return [
            'type' => $growth > 0 ? 'increase' : 'decrease',
            'value' => number_format(abs($growth), 1).'%',
        ];
    }

    private function getTodayAttendanceChange(): ?array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todaySql = 'SELECT COUNT(*) AS total FROM attendances WHERE DATE(check_in_time) = ?';
        $yesterdaySql = 'SELECT COUNT(*) AS total FROM attendances WHERE DATE(check_in_time) = ?';

        $todayCount = (int) (DB::selectOne($todaySql, [$today->format('Y-m-d')])->total ?? 0);
        $yesterdayCount = (int) (DB::selectOne($yesterdaySql, [$yesterday->format('Y-m-d')])->total ?? 0);

        // Tidak ada data pembanding
        if ($yesterdayCount === 0) {
            return ['type' => 'neutral'];
        }

        $change = (($todayCount - $yesterdayCount) / $yesterdayCount) * 100;

        // Jika ada data tapi tidak ada perubahan
        if ($change == 0) {
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

    private function getWeeklyAverage(): int
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        return (int) round(Attendance::whereBetween('check_in_time', [$startOfWeek, $endOfWeek])->count() / 7);
    }

    private function getWeeklyTrend(): ?array
    {
        $thisWeekSql = 'SELECT COUNT(*) AS total FROM attendances WHERE check_in_time BETWEEN ? AND ?';
        $lastWeekSql = 'SELECT COUNT(*) AS total FROM attendances WHERE check_in_time BETWEEN ? AND ?';

        $thisWeekRange = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
        $lastWeekRange = [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()];

        $thisWeek = (int) (DB::selectOne($thisWeekSql, $thisWeekRange)->total ?? 0);
        $lastWeek = (int) (DB::selectOne($lastWeekSql, $lastWeekRange)->total ?? 0);

        if ($lastWeek === 0) {
            return null;
        }

        $trend = (($thisWeek - $lastWeek) / $lastWeek) * 100;

        return [
            'type' => $trend >= 0 ? 'increase' : 'decrease',
            'value' => number_format(abs($trend), 1).'%',
        ];
    }

    private function getMonthlyRevenue(Carbon $startOfMonth, Carbon $endOfMonth): float
    {
        $sql = 'SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE created_at BETWEEN ? AND ?';

        return (float) (DB::selectOne($sql, [$startOfMonth, $endOfMonth])->total ?? 0.0);
    }

    private function getRevenueGrowthPercentage(Carbon $startOfMonth, Carbon $endOfMonth): ?array
    {
        $lastMonth = $startOfMonth->copy()->subMonth();
        $lastMonthEnd = $endOfMonth->copy()->subMonth();

        $currentRevenue = (float) $this->getMonthlyRevenue($startOfMonth, $endOfMonth);
        $lastMonthRevenue = (float) $this->getMonthlyRevenue($lastMonth, $lastMonthEnd);

        // Gunakan <= 0 untuk menghindari pembagian nol atau negatif kecil akibat floating point
        if ($lastMonthRevenue <= 0) {
            if ($currentRevenue > 0) {
                return [
                    'type' => 'increase',
                    'value' => '100%',
                ];
            }

            return [
                'type' => 'neutral',
            ];
        }

        // Pastikan tidak mungkin membagi nol
        $growth = 0;
        if ($lastMonthRevenue > 0) {
            $growth = (($currentRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
        }

        if (! is_finite($growth) || abs($growth) < 0.0001) {
            return [
                'type' => 'stable',
                'value' => '0%',
            ];
        }

        return [
            'type' => $growth > 0 ? 'increase' : 'decrease',
            'value' => number_format(abs($growth), 1).'%',
        ];
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
