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

    public function createMembershipExtension(Member $member, int $durationMonths): Membership
    {
        // Get current membership end date or use current date as start
        $currentMembership = $member->membership;
        $startDate = $currentMembership ? Carbon::parse($currentMembership->end_date)->addDay() : Carbon::now();
        $endDate = $startDate->copy()->addMonths($durationMonths);

        return Membership::create([
            'member_id' => $member->id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'duration_months' => $durationMonths,
        ]);
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

    public function getAvailableDurations(): array
    {
        // Currently available durations - can be moved to config/settings in the future
        return [
            1 => [
                'months' => 1,
                'price' => 150000,
                'label' => '1 Bulan',
                'enabled' => true,
            ],
            3 => [
                'months' => 3,
                'price' => 400000,
                'label' => '3 Bulan',
                'enabled' => true,
            ],
        ];
    }

    public function getEnabledDurations(): array
    {
        return array_filter($this->getAvailableDurations(), function ($duration) {
            return $duration['enabled'];
        });
    }

    public function isDurationValid(int $durationMonths): bool
    {
        $availableDurations = $this->getAvailableDurations();

        return isset($availableDurations[$durationMonths]) && $availableDurations[$durationMonths]['enabled'];
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
