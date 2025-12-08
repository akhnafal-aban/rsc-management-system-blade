<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\NonMemberVisit;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BusinessReportService
{
    public function getBusinessReport(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $endDate ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $startCarbon = Carbon::parse($startDate)->startOfDay();
        $endCarbon = Carbon::parse($endDate)->endOfDay();

        // Total Pemasukan
        $memberPayments = (float) Payment::whereBetween('created_at', [$startCarbon, $endCarbon])->sum('amount');
        $nonMemberPayments = (float) NonMemberVisit::whereBetween('visit_time', [$startCarbon, $endCarbon])->sum('amount');
        $totalRevenue = $memberPayments + $nonMemberPayments;

        // Jumlah Kunjungan
        $memberVisits = Attendance::whereBetween('check_in_time', [$startCarbon, $endCarbon])->count();
        $nonMemberVisits = NonMemberVisit::whereBetween('visit_time', [$startCarbon, $endCarbon])->count();
        $totalVisits = $memberVisits + $nonMemberVisits;

        // Konversi Non-Member ke Member
        $nonMembers = NonMemberVisit::whereBetween('visit_time', [$startCarbon, $endCarbon])
            ->distinct('phone')
            ->whereNotNull('phone')
            ->pluck('phone')
            ->toArray();

        $convertedMembers = 0;
        if (!empty($nonMembers)) {
            $convertedMembers = Member::whereIn('phone', $nonMembers)
                ->whereBetween('created_at', [$startCarbon, $endCarbon])
                ->count();
        }

        // Statistik Penggunaan Fasilitas (berdasarkan check-in)
        $dailyVisits = $this->getDailyVisitStats($startCarbon, $endCarbon);

        // Statistik Pendapatan per Kategori
        $revenueByCategory = [
            'membership_registration' => $this->getRegistrationRevenue($startCarbon, $endCarbon),
            'membership_extension' => $this->getExtensionRevenue($startCarbon, $endCarbon),
            'non_member_visits' => $nonMemberPayments,
        ];

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'revenue' => [
                'total' => $totalRevenue,
                'member_payments' => $memberPayments,
                'non_member_payments' => $nonMemberPayments,
                'by_category' => $revenueByCategory,
            ],
            'visits' => [
                'total' => $totalVisits,
                'member_visits' => $memberVisits,
                'non_member_visits' => $nonMemberVisits,
                'daily' => $dailyVisits,
            ],
            'conversions' => [
                'non_member_to_member' => $convertedMembers,
                'conversion_rate' => $nonMemberVisits > 0 ? ($convertedMembers / $nonMemberVisits) * 100 : 0,
            ],
            'members' => [
                'total' => Member::whereBetween('created_at', [$startCarbon, $endCarbon])->count(),
                'active' => Member::where('status', 'ACTIVE')->count(),
            ],
        ];
    }

    private function getDailyVisitStats(Carbon $startDate, Carbon $endDate): array
    {
        $memberVisits = Attendance::whereBetween('check_in_time', [$startDate, $endDate])
            ->selectRaw('DATE(check_in_time) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $nonMemberVisits = NonMemberVisit::whereBetween('visit_time', [$startDate, $endDate])
            ->selectRaw('DATE(visit_time) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $dates = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dateKey = $current->format('Y-m-d');
            $dates[] = [
                'date' => $dateKey,
                'member_visits' => $memberVisits[$dateKey] ?? 0,
                'non_member_visits' => $nonMemberVisits[$dateKey] ?? 0,
                'total' => ($memberVisits[$dateKey] ?? 0) + ($nonMemberVisits[$dateKey] ?? 0),
            ];
            $current->addDay();
        }

        return $dates;
    }

    private function getRegistrationRevenue(Carbon $startDate, Carbon $endDate): float
    {
        return (float) Payment::whereBetween('created_at', [$startDate, $endDate])
            ->where('notes', 'LIKE', '%Pendaftaran%')
            ->orWhere('notes', 'LIKE', '%pendaftaran member baru%')
            ->sum('amount');
    }

    private function getExtensionRevenue(Carbon $startDate, Carbon $endDate): float
    {
        return (float) Payment::whereBetween('created_at', [$startDate, $endDate])
            ->where(function ($query) {
                $query->where('notes', 'LIKE', '%Membership%')
                    ->orWhere('notes', 'LIKE', '%membership%')
                    ->orWhere('notes', 'LIKE', '%Perpanjangan%')
                    ->orWhere('notes', 'LIKE', '%perpanjangan%');
            })
            ->sum('amount');
    }
}
