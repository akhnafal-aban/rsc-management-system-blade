<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MemberService
{
    public function __construct(
        private PaymentService $paymentService,
        private MembershipService $membershipService
    ) {}

    public function getAllMembers(array $filters = []): LengthAwarePaginator
    {
        $query = Member::with(['membership', 'attendances'])
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
            $query->where('status', $filters['status']);
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

            return $member->fresh(['membership', 'payments']);
        });
    }

    public function updateMember(int $id, array $data): Member
    {
        return DB::transaction(function () use ($id, $data) {
            $member = Member::findOrFail($id);

            // Extract membership dan payment data
            $membershipDuration = $data['membership_duration'];
            $paymentMethod = $data['payment_method'];
            $paymentNotes = $data['payment_notes'] ?? null;

            // Calculate exp_date automatically based on membership duration
            $data['exp_date'] = Carbon::now()->addMonths($membershipDuration)->toDateString();

            // Remove non-member fields dari data
            unset($data['membership_duration'], $data['payment_method'], $data['payment_notes']);

            // Update member
            $member->update($data);

            // Update membership using MembershipService
            $this->membershipService->createMembership($member, $membershipDuration);

            // Create new payment using PaymentService
            $amount = $this->membershipService->getMembershipPrice($membershipDuration);
            $this->paymentService->createPayment($member, $amount, $paymentMethod, $paymentNotes);

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

        return $member->fresh();
    }

    public function activateMember(int $id): Member
    {
        $member = Member::findOrFail($id);
        $member->update(['status' => \App\Enums\MemberStatus::ACTIVE]);

        return $member->fresh();
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
