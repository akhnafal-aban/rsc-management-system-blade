<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Member;
use App\Models\Membership;
use Carbon\Carbon;

class MembershipService
{
    public function createMembership(Member $member, int $durationMonths): Membership
    {
        $membershipData = $this->generateMembershipData($member, $durationMonths);

        return Membership::create($membershipData);
    }

    public function getActiveMembership(Member $member): ?Membership
    {
        return $member->membership()->where('end_date', '>=', Carbon::now()->toDateString())->first();
    }

    public function isMembershipActive(Member $member): bool
    {
        $activeMembership = $this->getActiveMembership($member);

        return $activeMembership !== null;
    }

    public function getMembershipById(int $id): Membership
    {
        return Membership::with('member')->findOrFail($id);
    }

    public function getMembershipPrice(int $durationMonths): int
    {
        return Membership::getPriceForDuration($durationMonths);
    }

    private function generateMembershipData(Member $member, int $durationMonths): array
    {
        $startDate = Carbon::now()->startOfDay();
        $endDate = $startDate->copy()->addMonths($durationMonths)->endOfDay();

        return [
            'member_id' => $member->id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'duration_months' => $durationMonths,
        ];
    }
}
