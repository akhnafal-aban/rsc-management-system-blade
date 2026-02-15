<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Member;
use App\Models\Membership;
use Carbon\Carbon;

class MembershipService
{
    /**
     * Membuat membership baru berdasarkan package_key.
     */
    public function createMembership(Member $member, string $packageKey): Membership
    {
        $pricing = $this->calculatePackagePricing($packageKey);

        $durationDays = $pricing['duration_days'];
        $startDate = Carbon::now()->startOfDay();
        $endDate = $startDate->copy()->addDays($durationDays - 1);

        return Membership::create([
            'member_id' => $member->id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'duration_months' => 0,
        ]);
    }

    /**
     * Membuat membership extension berdasarkan package_key.
     */
    public function createMembershipExtension(Member $member, string $packageKey): Membership
    {
        $pricing = $this->calculatePackagePricing($packageKey);
        $durationDays = $pricing['duration_days'];

        $today = Carbon::today();
        $expDate = $member->exp_date ? Carbon::parse($member->exp_date) : null;

        if ($expDate !== null && $expDate->gte($today)) {
            $startDate = $expDate->copy()->addDay()->startOfDay();
        } else {
            $startDate = Carbon::now()->startOfDay();
        }

        $endDate = $startDate->copy()->addDays($durationDays - 1);

        return Membership::create([
            'member_id' => $member->id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'duration_months' => 0,
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

    /**
     * Menghitung informasi harga final sebuah package.
     *
     * Hasil:
     * [
     *   'package_key' => string,
     *   'label' => string,
     *   'base_price' => int,
     *   'discount_percent' => int,
     *   'final_price' => int,
     *   'duration_days' => int,
     * ]
     */
    public function calculatePackagePricing(string $packageKey): array
    {
        $package = Membership::getPackage($packageKey);

        if ($package === null) {
            throw new \InvalidArgumentException(sprintf('Paket membership "%s" tidak ditemukan.', $packageKey));
        }

        $basePrice = (int) $package['price'];
        $discountPercent = (int) $package['discount_percent'];
        $durationDays = (int) $package['duration_days'];
        $label = $package['label'] ?? $packageKey;

        $effectivePercent = max(0, min(100, $discountPercent));
        $finalPrice = intdiv($basePrice * (100 - $effectivePercent), 100);

        return [
            'package_key' => $packageKey,
            'label' => $label,
            'base_price' => $basePrice,
            'discount_percent' => $effectivePercent,
            'final_price' => $finalPrice,
            'duration_days' => $durationDays,
        ];
    }

    /**
     * Mengembalikan seluruh paket membership beserta harga finalnya.
     */
    public function getAllPackageOptions(): array
    {
        $packages = Membership::getMembershipPackages();

        $options = [];

        foreach ($packages as $key => $package) {
            $options[$key] = $this->calculatePackagePricing($key);
        }

        return $options;
    }
}
