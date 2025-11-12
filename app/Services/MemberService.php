<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MemberService
{
    public function __construct(
        private PaymentService $paymentService,
        private MembershipService $membershipService,
        private DashboardService $dashboardService
    ) {}

    public function getAllMembers(array $filters = []): LengthAwarePaginator
    {
        $query = Member::select('id', 'member_code', 'name', 'email', 'phone', 'status', 'exp_date', 'last_check_in', 'total_visits', 'created_at')
            ->with(['membership', 'attendances'])
            ->orderBy('created_at', 'desc');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('member_code', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'ACTIVE') {
                // ACTIVE means active and not expired
                $query->where('status', 'ACTIVE')
                    ->whereDate('exp_date', '>=', Carbon::today());
            } elseif ($filters['status'] === 'EXPIRED') {
                // EXPIRED means recently expired (less than 3 months)
                $query->where('status', 'EXPIRED');
            } elseif ($filters['status'] === 'INACTIVE') {
                // INACTIVE means expired more than 3 months ago
                $query->where('status', 'INACTIVE');
            }
        }

        return $query->paginate(5);
    }

    public function getMemberById(int $id): Member
    {
        return Member::with(['membership', 'attendances'])
            ->findOrFail($id);
    }

    public function createMember(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            // Generate member code dan set default status
            $data['member_code'] = $this->generateMemberId();
            $data['status'] = $data['status'] ?? \App\Enums\MemberStatus::ACTIVE;

            // Extract membership dan payment data
            $membershipDuration = (int) $data['membership_duration'];
            $paymentMethod = $data['payment_method'];
            $paymentNotes = $data['payment_notes'] ?? null;

            // Calculate exp_date automatically based on membership duration
            $data['exp_date'] = Carbon::now()->addMonths($membershipDuration)->toDateString();

            // Remove non-member fields dari data
            unset($data['membership_duration'], $data['payment_method'], $data['payment_notes']);

            // Create member
            $member = Member::create($data);

            // Create membership using MembershipService
            $this->membershipService->createMembership($member, $membershipDuration);

            // Create payments: Registration fee + Membership fee
            $this->createRegistrationPayment($member, $paymentMethod, $paymentNotes);
            $this->createMembershipPayment($member, $membershipDuration, $paymentMethod, $paymentNotes);

            // Invalidate member caches
            CacheService::invalidateMemberCaches();

            // Invalidate dashboard cache
            $this->dashboardService->invalidateDashboardCache();

            return $member->fresh(['membership', 'payments']);
        });
    }

    public function updateMember(int $id, array $data): Member
    {
        return DB::transaction(function () use ($id, $data) {
            $member = Member::findOrFail($id);
            $originalExpDate = $member->exp_date;
            $originalStatus = $member->status;

            // Update member data
            $member->update($data);

            // Always check and update status based on exp_date after any update
            $this->autoUpdateMemberStatus($member);

            // Invalidate member caches
            CacheService::invalidateMemberCaches();

            // Invalidate dashboard cache if status might have changed
            $this->dashboardService->invalidateDashboardCache();

            return $member->fresh(['membership', 'payments']);
        });
    }

    public function deleteMember(int $id): bool
    {
        $member = Member::findOrFail($id);

        return $member->delete();
    }

    public function suspendMember(int $id): Member
    {
        $member = Member::findOrFail($id);
        $member->update(['status' => \App\Enums\MemberStatus::INACTIVE]);

        // Invalidate member caches
        CacheService::invalidateMemberCaches();

        return $member->fresh();
    }

    public function activateMember(int $id): Member
    {
        $member = Member::findOrFail($id);
        $member->update(['status' => \App\Enums\MemberStatus::ACTIVE]);

        // Invalidate member caches
        CacheService::invalidateMemberCaches();

        return $member->fresh();
    }

    public function extendMembership(int $memberId, int $duration, string $paymentMethod, ?string $paymentNotes = null): Member
    {
        return DB::transaction(function () use ($memberId, $duration, $paymentMethod, $paymentNotes) {
            $member = Member::findOrFail($memberId);

            // Extend from current exp_date, or from now if exp_date is in the past
            $currentExpDate = Carbon::parse($member->exp_date);
            $today = Carbon::today();

            $baseDate = $currentExpDate->isFuture() ? $currentExpDate : $today;
            $newExpDate = $baseDate->addMonths($duration)->toDateString();

            // Update exp_date
            $member->update(['exp_date' => $newExpDate]);

            // Auto-update status based on new exp_date
            $this->autoUpdateMemberStatus($member);

            // Create new membership record for extension
            $this->membershipService->createMembershipExtension($member, $duration);

            // Create new payment using PaymentService
            $amount = $this->membershipService->getMembershipPrice($duration);
            $this->paymentService->createPayment($member, $amount, $paymentMethod, $paymentNotes);

            // Invalidate member caches
            CacheService::invalidateMemberCaches();

            // Invalidate dashboard cache
            $this->dashboardService->invalidateDashboardCache();

            return $member->fresh(['membership', 'payments']);
        });
    }

    public function searchMembers(string $query): array
    {
        // Reduce cache TTL for search results to avoid stale data
        $cacheKey = CacheService::getMemberSearchKey($query, 20);

        return Cache::remember($cacheKey, CacheService::CACHE_TTL_SHORT, function () use ($query) {
            $members = Member::select('id', 'member_code', 'name', 'exp_date', 'status')
                ->where(function ($q) use ($query) {
                    $q->where('member_code', 'like', "%{$query}%")
                        ->orWhere('name', 'like', "%{$query}%");
                })
                ->orderByRaw("CASE WHEN member_code LIKE '%{$query}%' THEN 1 ELSE 2 END")
                ->orderBy('member_code')
                ->limit(20)
                ->get()
                ->toArray();

            // Add formatted exp_date to each member
            return array_map(function ($member) {
                $member['exp_date_formatted'] = \Carbon\Carbon::parse($member['exp_date'])->format('d M Y');

                return $member;
            }, $members);
        });
    }

    public function clearSearchCache(?string $query = null): void
    {
        if ($query) {
            $cacheKey = CacheService::getMemberSearchKey($query, 20);
            Cache::forget($cacheKey);
        } else {
            // Clear all member search caches
            Cache::flush(); // This is more aggressive, use with caution
        }
    }

    public function getMemberStats(Member $member): array
    {
        $totalVisits = $member->attendances()->count();
        $lastCheckIn = $member->attendances()
            ->latest()
            ->first();

        return [
            'total_visits' => $totalVisits,
            'last_check_in' => $lastCheckIn?->created_at,
            'membership_status' => $member->membership ? 'Aktif' : 'Tidak ada membership',
            'membership_expiry' => $member->membership?->end_date,
        ];
    }

    public function updateMemberStatusBasedOnExpDate(Member $member): Member
    {
        $today = Carbon::today();
        $expDate = Carbon::parse($member->exp_date);
        $currentStatus = $member->status;

        // Determine what the status should be based on exp_date and activity
        $shouldBeActive = $expDate->isFuture() || $expDate->isToday();
        $shouldBeExpired = !$shouldBeActive && $expDate->diffInMonths($today) < 3;
        $shouldBeInactive = !$shouldBeActive && $expDate->diffInMonths($today) >= 3;

        if ($shouldBeActive && $currentStatus !== \App\Enums\MemberStatus::ACTIVE) {
            // Member should be active - activate
            $member->update(['status' => \App\Enums\MemberStatus::ACTIVE]);
        } elseif ($shouldBeExpired && $currentStatus !== \App\Enums\MemberStatus::EXPIRED) {
            // Member should be expired - set to expired
            $member->update(['status' => \App\Enums\MemberStatus::EXPIRED]);
        } elseif ($shouldBeInactive && $currentStatus !== \App\Enums\MemberStatus::INACTIVE) {
            // Member should be inactive - set to inactive
            $member->update(['status' => \App\Enums\MemberStatus::INACTIVE]);
        }

        return $member;
    }

    public function autoUpdateMemberStatus(Member $member): Member
    {
        return $this->updateMemberStatusBasedOnExpDate($member);
    }

    public function bulkUpdateMemberStatuses(): array
    {
        $today = Carbon::today();
        $threeMonthsAgo = $today->copy()->subMonths(3);

        $updatedCount = 0;
        $results = [];

        // Get all members that need status update
        $allMembers = Member::all();

        foreach ($allMembers as $member) {
            $expDate = Carbon::parse($member->exp_date);
            $currentStatus = $member->status;
            $newStatus = null;

            // Determine new status based on expiration date
            if ($expDate->isFuture() || $expDate->isToday()) {
                $newStatus = \App\Enums\MemberStatus::ACTIVE;
            } elseif ($expDate->diffInMonths($today) < 3) {
                $newStatus = \App\Enums\MemberStatus::EXPIRED;
            } else {
                $newStatus = \App\Enums\MemberStatus::INACTIVE;
            }

            // Update status if different
            if ($currentStatus !== $newStatus) {
                $member->update(['status' => $newStatus]);
                $updatedCount++;
                
                $results[] = [
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'old_status' => $currentStatus->value,
                    'new_status' => $newStatus->value,
                    'reason' => $this->getStatusChangeReason($currentStatus, $newStatus, $expDate, $today),
                ];
            }
        }

        // Invalidate caches
        CacheService::invalidateMemberCaches();
        $this->dashboardService->invalidateDashboardCache();

        return [
            'total_updated' => $updatedCount,
            'details' => $results,
        ];
    }

    private function getStatusChangeReason($oldStatus, $newStatus, $expDate, $today): string
    {
        if ($newStatus === \App\Enums\MemberStatus::ACTIVE) {
            return 'Membership renewed or extended';
        } elseif ($newStatus === \App\Enums\MemberStatus::EXPIRED) {
            return 'Membership expired recently';
        } elseif ($newStatus === \App\Enums\MemberStatus::INACTIVE) {
            return 'Membership expired more than 3 months ago';
        }
        
        return 'Status updated';
    }

    public function getMemberExpirationStatus(Member $member): array
    {
        $today = Carbon::today();
        $expDate = Carbon::parse($member->exp_date);
        $daysUntilExpiry = $today->diffInDays($expDate, false);

        return [
            'is_expired' => $expDate->isPast(),
            'is_expiring_soon' => $daysUntilExpiry <= 7 && $daysUntilExpiry >= 0,
            'days_until_expiry' => $daysUntilExpiry,
            'exp_date_formatted' => $expDate->format('d M Y'),
            'status' => $member->status,
        ];
    }

    private function createRegistrationPayment(Member $member, string $paymentMethod, ?string $paymentNotes = null): void
    {
        $registrationFee = 50000; // Biaya pendaftaran member baru
        $this->paymentService->createPayment(
            $member,
            $registrationFee,
            $paymentMethod,
            $paymentNotes ? "Pendaftaran: {$paymentNotes}" : 'Biaya pendaftaran member baru'
        );
    }

    private function createMembershipPayment(Member $member, int $membershipDuration, string $paymentMethod, ?string $paymentNotes = null): void
    {
        $membershipAmount = $this->membershipService->getMembershipPrice($membershipDuration);
        $this->paymentService->createPayment(
            $member,
            $membershipAmount,
            $paymentMethod,
            $paymentNotes ? "Membership {$membershipDuration} bulan: {$paymentNotes}" : "Biaya membership {$membershipDuration} bulan"
        );
    }

    public function getRegistrationFee(): int
    {
        return 50000; // Biaya pendaftaran member baru
    }

    public function getTotalRegistrationCost(int $membershipDuration): int
    {
        $registrationFee = $this->getRegistrationFee();
        $membershipCost = $this->membershipService->getMembershipPrice($membershipDuration);

        return $registrationFee + $membershipCost;
    }

    public function getAvailableMembershipDurations(): array
    {
        return $this->membershipService->getEnabledDurations();
    }

    public function validateMembershipDuration(int $duration): bool
    {
        return $this->membershipService->isDurationValid($duration);
    }

    private function generateMemberId(): string
    {
        $prefix = '12-';
        $lastMember = Member::orderBy('member_code', 'desc')->first();

        if ($lastMember) {
            $lastId = (int) substr($lastMember->member_code, 3);
            $newId = $lastId + 1;
        } else {
            $newId = 1;
        }

        return $prefix.$newId;
    }
}
