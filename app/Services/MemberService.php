<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MemberStatus;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Closure;
use Illuminate\Support\Str;

class MemberService
{
    public function __construct(
        private PaymentService $paymentService,
        private MembershipService $membershipService
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

            return $member->fresh(['membership', 'payments']);
        });
    }

    public function searchMembers(string $query): array
    {
        $today = Carbon::today()->format('Y-m-d');

        $sql = <<<'SQL'
SELECT
    m.id,
    m.member_code,
    m.name,
    m.exp_date,
    m.status,
    EXISTS (
        SELECT 1
        FROM attendances a
        WHERE a.member_id = m.id
          AND DATE(a.check_in_time) = ?
          AND a.check_out_time IS NULL
    ) AS has_checked_in_today
FROM members m
WHERE m.member_code LIKE ?
   OR m.name LIKE ?
ORDER BY
    CASE WHEN m.member_code LIKE ? THEN 0 ELSE 1 END,
    m.member_code ASC
LIMIT 20
SQL;

        $likeQuery = '%' . $query . '%';
        $rows = DB::select($sql, [$today, $likeQuery, $likeQuery, $likeQuery]);

        return array_map(
            static function ($row) {
                $data = (array) $row;
                $data['exp_date_formatted'] = Carbon::parse($data['exp_date'])->format('d M Y');
                $data['has_checked_in_today'] = (bool) $data['has_checked_in_today'];
                $data['can_checkin'] = $data['status'] === MemberStatus::ACTIVE->value && ! $data['has_checked_in_today'];

                return $data;
            },
            $rows
        );
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
        $shouldBeExpired = ! $shouldBeActive && $expDate->diffInMonths($today) < 3;
        $shouldBeInactive = ! $shouldBeActive && $expDate->diffInMonths($today) >= 3;

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

        return $prefix . $newId;
    }
    
    public function exportMembersCallback(array $filters = []): Closure
    {
        return function () use ($filters) {
            $handle = fopen('php://output', 'w');

            // Header CSV
            fputcsv($handle, [
                'ID',
                'Member Code',
                'Name',
                'Email',
                'Phone',
                'Status',
                'Exp Date',
                'Last Check In',
                'Total Visits',
                'Created At',
                'Membership Name',
            ]);

            $query = Member::select(
                'id',
                'member_code',
                'name',
                'email',
                'phone',
                'status',
                'exp_date',
                'last_check_in',
                'total_visits',
                'created_at'
            )->with('membership')->orderBy('id');

            // Apply same filters seperti di getAllMembers
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
                    $query->where('status', 'ACTIVE')
                        ->whereDate('exp_date', '>=', Carbon::today());
                } elseif ($filters['status'] === 'EXPIRED') {
                    $query->where('status', 'EXPIRED');
                } elseif ($filters['status'] === 'INACTIVE') {
                    $query->where('status', 'INACTIVE');
                }
            }

            // Stream rows in chunks to avoid OOM
            $query->chunkById(200, function ($members) use ($handle) {
                foreach ($members as $m) {
                    fputcsv($handle, [
                        $m->id,
                        $m->member_code,
                        $m->name,
                        $m->email,
                        $m->phone,
                        // jika status berupa enum cast, ambil value; jika string, tetap string
                        (is_object($m->status) && property_exists($m->status, 'value')) ? $m->status->value : (string) $m->status,
                        $m->exp_date,
                        // last_check_in bisa null
                        $m->last_check_in ? $m->last_check_in->format('Y-m-d H:i:s') : '',
                        $m->total_visits ?? 0,
                        $m->created_at ? $m->created_at->format('Y-m-d H:i:s') : '',
                        optional($m->membership)->name ?? '',
                    ]);
                }
            });

            fclose($handle);
        };
    }
}
