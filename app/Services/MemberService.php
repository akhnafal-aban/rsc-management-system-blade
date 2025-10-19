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
            if ($filters['status'] === 'INACTIVE') {
                // INACTIVE includes both manually suspended AND expired members
                $query->where(function ($q) {
                    $q->where('status', 'INACTIVE')
                        ->orWhere(function ($sq) {
                            $sq->where('status', 'ACTIVE')
                                ->whereDate('exp_date', '<', Carbon::today());
                        });
                });
            } else {
                // ACTIVE means active and not expired
                $query->where('status', $filters['status'])
                    ->whereDate('exp_date', '>=', Carbon::today());
            }
        }

        return $query->paginate(10);
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

            // Create payment using PaymentService
            $amount = $this->membershipService->getMembershipPrice($membershipDuration);
            $this->paymentService->createPayment($member, $amount, $paymentMethod, $paymentNotes);

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

            // Update member data only (no membership extension logic)
            $member->update($data);

            // Invalidate member caches
            CacheService::invalidateMemberCaches();

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

            $newExpDate = now()->addMonths($duration)->toDateString();

            $updated = DB::table('members')
                ->where('id', $memberId)
                ->update(['exp_date' => $newExpDate]);

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
        $cacheKey = CacheService::getMemberSearchKey($query, 20);

        return Cache::remember($cacheKey, CacheService::CACHE_TTL_MEDIUM, function () use ($query) {
            return Member::select('id', 'member_code', 'name', 'exp_date', 'status')
                ->where(function ($q) use ($query) {
                    $q->where('member_code', 'like', "%{$query}%")
                        ->orWhere('name', 'like', "%{$query}%");
                })
                ->orderBy('name')
                ->limit(20)
                ->get()
                ->toArray();
        });
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

    private function generateMemberId(): string
    {
        $prefix = '12-';
        $lastMember = Member::orderBy('id', 'desc')->first();

        if ($lastMember) {
            $lastId = (int) substr($lastMember->member_code, 3);
            $newId = $lastId + 1;
        } else {
            $newId = 1;
        }

        return $prefix.$newId;
    }
}
