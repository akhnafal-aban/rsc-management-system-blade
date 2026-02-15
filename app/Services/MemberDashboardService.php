<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class MemberDashboardService
{
    public function getDashboardData(User $user): ?array
    {
        if ($user->member_id === null) {
            return null;
        }

        $member = Member::with(['membership', 'attendances'])->find($user->member_id);
        if (! $member) {
            return null;
        }

        $latestMembership = $member->membership;
        $packageLabel = $latestMembership
            ? $this->resolvePackageLabel($latestMembership->duration_months)
            : null;
        $expDate = $member->exp_date;
        $today = Carbon::today();
        $daysRemaining = $expDate ? (int) $today->diffInDays($expDate, false) : 0;
        if ($daysRemaining < 0) {
            $daysRemaining = 0;
        }

        $thisMonthCount = (int) $member->attendances()
            ->whereBetween('check_in_time', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()])
            ->count();

        $totalVisits = (int) $member->total_visits;
        $lastAttendance = $member->attendances()->orderByDesc('check_in_time')->first();
        $lastVisit = $lastAttendance?->check_in_time?->format('Y-m-d');

        $currentStreak = $this->computeCurrentStreak($member);
        $avgPerWeek = $this->computeAveragePerWeek($member);

        $joinDate = $member->memberships()->orderBy('start_date')->first()?->start_date?->format('Y-m-d')
            ?? $member->created_at?->format('Y-m-d');

        $membershipPrice = $latestMembership ? $this->resolveMembershipPrice($latestMembership->duration_months) : null;

        Log::info('Dashboard data: ' . json_encode($member->email));

        return [
            'id' => $member->member_code,
            'name' => $member->name,
            'email' => $member->email ?? $user->email,
            'phone' => $member->phone,
            'join_date' => $joinDate,
            'membership' => [
                'package' => $packageLabel ?? (string) ($latestMembership?->duration_months ?? 0) . ' Bulan',
                'status' => $member->status->value,
                'expiry_date' => $expDate?->format('Y-m-d'),
                'days_remaining' => $daysRemaining,
                'price' => $membershipPrice !== null ? 'Rp ' . number_format($membershipPrice, 0, ',', '.') : null,
            ],
            'attendance' => [
                'this_month' => $thisMonthCount,
                'current_streak' => $currentStreak,
                'total_visits' => $totalVisits,
                'last_visit' => $lastVisit,
                'average_per_week' => round($avgPerWeek, 1),
            ],
        ];
    }

    private function resolvePackageLabel(int $durationMonths): ?string
    {
        $packages = \App\Models\Membership::getMembershipPackages();
        foreach ($packages as $p) {
            $days = $p['duration_days'] ?? 0;
            if ((int) round($days / 30) === $durationMonths || $days === $durationMonths * 30) {
                return $p['label'] ?? (string) $durationMonths . ' Bulan';
            }
        }
        return (string) $durationMonths . ' Bulan';
    }

    private function resolveMembershipPrice(int $durationMonths): ?int
    {
        $packages = \App\Models\Membership::getMembershipPackages();
        foreach ($packages as $p) {
            $days = $p['duration_days'] ?? 0;
            if ((int) round($days / 30) === $durationMonths || $days === $durationMonths * 30) {
                return (int) ($p['price'] ?? 0);
            }
        }
        return null;
    }

    private function computeCurrentStreak(Member $member): int
    {
        $dates = $member->attendances()
            ->select(DB::raw('DATE(check_in_time) as d'))
            ->distinct()
            ->orderBy('d')
            ->pluck('d')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->values()
            ->all();

        if (count($dates) === 0) {
            return 0;
        }

        $today = Carbon::today()->format('Y-m-d');
        if (! in_array($today, $dates, true)) {
            return 0;
        }

        $streak = 0;
        $current = Carbon::today();
        foreach (array_reverse($dates) as $d) {
            $day = Carbon::parse($d)->format('Y-m-d');
            if ($day === $current->format('Y-m-d')) {
                $streak++;
                $current->subDay();
            } else {
                break;
            }
        }
        return $streak;
    }

    private function computeAveragePerWeek(Member $member): float
    {
        $first = $member->attendances()->orderBy('check_in_time')->first()?->check_in_time;
        if (! $first) {
            return 0.0;
        }
        $weeks = max(1, (int) Carbon::today()->diffInWeeks($first));
        $total = (int) $member->total_visits;
        return $total / $weeks;
    }
}
