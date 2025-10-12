<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Member;
use Illuminate\Pagination\LengthAwarePaginator;

class MemberService
{
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
        $data['member_code'] = $this->generateMemberId();
        $data['status'] = $data['status'] ?? \App\Enums\MemberStatus::ACTIVE;

        return Member::create($data);
    }

    public function updateMember(int $id, array $data): Member
    {
        $member = Member::findOrFail($id);
        $member->update($data);

        return $member->fresh();
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
            'membership_status' => $member->membership?->status ?? 'Tidak ada membership',
            'membership_expiry' => $member->membership?->expiry_date,
        ];
    }

    private function generateMemberId(): string
    {
        $prefix = 'MEM';
        $lastMember = Member::orderBy('id', 'desc')->first();

        if ($lastMember) {
            $lastId = (int) substr($lastMember->member_code, 3);
            $newId = $lastId + 1;
        } else {
            $newId = 1;
        }

        return $prefix.str_pad((string) $newId, 6, '0', STR_PAD_LEFT);
    }
}
