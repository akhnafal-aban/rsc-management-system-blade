<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Member;
use App\Models\NonMemberVisit;
use App\Models\Payment;
use Carbon\Carbon;
// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class PaymentHistoryService
{
    public function getAllPayments(?string $startDate = null, ?string $endDate = null, int $perPage = 15): LengthAwarePaginator
    {
        $memberPayments = $this->getMemberPayments($startDate, $endDate);
        $nonMemberPayments = $this->getNonMemberPayments($startDate, $endDate);

        // Combine and sort by created_at
        $allPayments = $memberPayments->concat($nonMemberPayments)->sortByDesc('created_at');

        // Manually paginate
        $currentPage = request()->get('page', 1);
        $perPage = $perPage;
        $items = $allPayments->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $total = $allPayments->count();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function getMemberPayments(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Payment::with('member');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        }

        return $query->get()->map(function (Payment $payment) {
            return [
                'id' => 'payment_' . $payment->id,
                'type' => 'member',
                'type_label' => 'Membership',
                'member_id' => $payment->member_id,
                'member_name' => $payment->member->name ?? 'N/A',
                'amount' => (float) $payment->amount,
                'payment_method' => $payment->method,
                'notes' => $payment->notes,
                'created_at' => $payment->created_at,
                'date' => $payment->created_at->format('Y-m-d'),
            ];
        });
    }

    private function getNonMemberPayments(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = NonMemberVisit::with('creator');

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->get()->map(function (NonMemberVisit $visit) {
            return [
                'id' => 'nonmember_' . $visit->id,
                'type' => 'non_member',
                'type_label' => 'Non-Member Visit',
                'member_id' => null,
                'member_name' => $visit->name,
                'amount' => (float) $visit->amount,
                'payment_method' => $visit->payment_method,
                'notes' => $visit->notes,
                'created_at' => $visit->visit_time,
                'date' => $visit->visit_time->format('Y-m-d'),
            ];
        });
    }

    public function getDailyRevenue(?string $date = null): array
    {
        $date = $date ?? Carbon::today()->format('Y-m-d');
        $startDate = Carbon::parse($date)->startOfDay();
        $endDate = Carbon::parse($date)->endOfDay();

        $memberRevenue = (float) Payment::whereBetween('created_at', [$startDate, $endDate])->sum('amount');
        $nonMemberRevenue = (float) NonMemberVisit::whereBetween('visit_time', [$startDate, $endDate])->sum('amount');

        return [
            'date' => $date,
            'member_revenue' => $memberRevenue,
            'non_member_revenue' => $nonMemberRevenue,
            'total_revenue' => $memberRevenue + $nonMemberRevenue,
        ];
    }

    public function getMonthlyRevenue(?string $month = null): array
    {
        $month = $month ?? Carbon::now()->format('Y-m');
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $memberRevenue = (float) Payment::whereBetween('created_at', [$startDate, $endDate])->sum('amount');
        $nonMemberRevenue = (float) NonMemberVisit::whereBetween('visit_time', [$startDate, $endDate])->sum('amount');

        return [
            'month' => $month,
            'member_revenue' => $memberRevenue,
            'non_member_revenue' => $nonMemberRevenue,
            'total_revenue' => $memberRevenue + $nonMemberRevenue,
        ];
    }

    public function getTotalRevenue(?string $startDate = null, ?string $endDate = null): float
    {
        $memberRevenue = 0.0;
        $nonMemberRevenue = 0.0;

        $paymentQuery = Payment::query();
        $visitQuery = NonMemberVisit::query();

        if ($startDate && $endDate) {
            $paymentQuery->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
            $visitQuery->byDateRange($startDate, $endDate);
        }

        $memberRevenue = (float) $paymentQuery->sum('amount');
        $nonMemberRevenue = (float) $visitQuery->sum('amount');

        return $memberRevenue + $nonMemberRevenue;
    }
}
