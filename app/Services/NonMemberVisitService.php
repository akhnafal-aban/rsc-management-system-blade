<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NonMemberVisit;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class NonMemberVisitService
{
    public function createNonMemberVisit(array $data, int $userId): NonMemberVisit
    {
        if (! array_key_exists('amount', $data) || $data['amount'] === null || $data['amount'] === '') {
            $data['amount'] = $this->getDefaultVisitAmount();
        }
        $data['created_by'] = $userId;
        $data['visit_time'] = Carbon::now();

        $visit = NonMemberVisit::create($data);

        // Invalidate dashboard cache
        DashboardService::invalidateCache();

        return $visit->load('creator');
    }

    public function getNonMemberVisits(?string $startDate = null, ?string $endDate = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = NonMemberVisit::with('creator')->latest('visit_time');

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->paginate($perPage);
    }

    public function getTodayNonMemberVisits(): int
    {
        return NonMemberVisit::today()->count();
    }

    public function getTotalRevenue(?string $startDate = null, ?string $endDate = null): float
    {
        $query = NonMemberVisit::query();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return (float) $query->sum('amount');
    }

    public function getNonMemberVisitById(int $id): NonMemberVisit
    {
        return NonMemberVisit::with('creator')->findOrFail($id);
    }

    public function getDefaultVisitAmount(): int
    {
        $fees = \App\Models\Membership::getFees();

        return $fees['non_member_visit_daily'] ?? 0;
    }
}
