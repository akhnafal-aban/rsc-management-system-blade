<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Payment;
use Carbon\Carbon;

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
        $startOfWeek = $today->copy()->startOfWeek();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        return [
            [
                'title' => 'Member Aktif',
                'value' => Member::active()->count(),
                'change' => $this->getMemberGrowthPercentage(),
                'icon' => 'users',
            ],
            [
                'title' => 'Check-in Hari Ini',
                'value' => Attendance::whereDate('check_in_time', $today)->count(),
                'change' => $this->getTodayAttendanceChange(),
                'icon' => 'user-check',
            ],
            [
                'title' => 'Rata-rata Mingguan',
                'value' => $this->getWeeklyAverage(),
                'change' => $this->getWeeklyTrend(),
                'icon' => 'trending-up',
            ],
            [
                'title' => 'Pendapatan Bulanan',
                'value' => 'Rp '.number_format($this->getMonthlyRevenue($startOfMonth, $endOfMonth), 0, ',', '.'),
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
        $data = [];
        $labels = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $count = Attendance::whereDate('check_in_time', $date)->count();
            $data[] = $count;
            $labels[] = $date->format('D');
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getMemberDistributionData(): array
    {
        $activeMembers = Member::active()->count();
        $inactiveMembers = Member::inactive()->count();
        $expiredMembers = Member::expired()->count();

        return [
            'labels' => ['Aktif', 'Tidak Aktif', 'Kedaluwarsa'],
            'data' => [$activeMembers, $inactiveMembers, $expiredMembers],
            'colors' => ['#10B981', '#F59E0B', '#EF4444'],
        ];
    }

    private function getDailyActivityData(): array
    {
        $today = Carbon::today();
        $target = 100; // Target harian
        $current = Attendance::whereDate('check_in_time', $today)->count();
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
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        $currentCount = Member::active()
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        $lastMonthCount = Member::active()
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->count();

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

        $todayCount = Attendance::whereDate('check_in_time', $today)->count();
        $yesterdayCount = Attendance::whereDate('check_in_time', $yesterday)->count();

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
        $thisWeek = Attendance::whereBetween('check_in_time', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ])->count();

        $lastWeek = Attendance::whereBetween('check_in_time', [
            Carbon::now()->subWeek()->startOfWeek(),
            Carbon::now()->subWeek()->endOfWeek(),
        ])->count();

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
        return (float) Payment::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');
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
        $member = Member::withCount('attendances')
            ->orderBy('attendances_count', 'desc')
            ->first();

        return $member ? $member->name.' ('.$member->attendances_count.' kali kunjungan)' : 'Belum ada data';
    }

    private function getPeakHours(): string
    {
        $peakHour = Attendance::selectRaw('HOUR(check_in_time) as hour, COUNT(*) as count')
            ->whereDate('check_in_time', '>=', Carbon::now()->subDays(30))
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->first();

        return $peakHour ? $peakHour->hour.':00 - '.($peakHour->hour + 1).':00' : 'Belum ada data';
    }

    private function getInactiveMembersAlert(): string
    {
        $inactiveCount = Member::active()
            ->where('last_check_in', '<', Carbon::now()->subDays(7))
            ->count();

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
