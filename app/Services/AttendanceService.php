<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MemberStatus;
use App\Jobs\AutoCheckOutJob;
use App\Models\Attendance;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    public function getTodayAttendances(int $perPage = 10, ?string $search = null, ?string $statusFilter = null): LengthAwarePaginator
    {
        return $this->getAttendancesByDate(Carbon::today()->format('Y-m-d'), $perPage, $search, $statusFilter);
    }

    public function getAttendancesByDate(string $date, int $perPage = 10, ?string $search = null, ?string $statusFilter = null): LengthAwarePaginator
    {
        $query = Attendance::with(['member', 'creator'])
            ->whereDate('check_in_time', $date);

        // Apply search filter
        if ($search) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('member_code', 'LIKE', "%{$search}%");
            });
        }

        // Apply status filter
        if ($statusFilter === 'checkin') {
            $query->whereNull('check_out_time');
        } elseif ($statusFilter === 'checkout') {
            $query->whereNotNull('check_out_time');
        }

        return $query->orderBy('check_in_time', 'desc')->paginate($perPage);
    }

    public function getTodayStats(): array
    {
        return $this->getStatsByDate(Carbon::today()->format('Y-m-d'));
    }

    public function getStatsByDate(string $date): array
    {
        return [
            'total_checkins' => Attendance::whereDate('check_in_time', $date)->count(),
            'active_members' => Member::active()->count(),
            'checked_in_today' => Attendance::whereDate('check_in_time', $date)
                ->distinct('member_id')
                ->count(),
        ];
    }

    public function searchMembers(string $query): Collection
    {
        return Member::active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('member_code', 'LIKE', "%{$query}%")
                    ->orWhere('phone', 'LIKE', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function getMemberById(string $memberId): ?Member
    {
        // Try to find by member_code first, then by id
        return Member::where('member_code', $memberId)
            ->orWhere('id', $memberId)
            ->first();
    }

    public function searchActiveMembers(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Member::select('id', 'member_code', 'name', 'exp_date', 'status');

        if (! empty($search = trim($search ?? ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('member_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $members = $query
            ->orderByRaw("CASE WHEN status = 'ACTIVE' THEN 1 ELSE 2 END") // ACTIVE first, then INACTIVE
            ->orderBy('member_code')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $memberIds = $members->pluck('id');
        $todayAttendances = Attendance::whereIn('member_id', $memberIds)
            ->whereDate('check_in_time', Carbon::today())
            ->whereNull('check_out_time')
            ->pluck('member_id')
            ->toArray();

        $members->getCollection()->transform(function ($member) use ($todayAttendances) {
            $member->has_checked_in_today = in_array($member->id, $todayAttendances);

            return $member;
        });

        return $members;
    }

    public function canCheckIn(Member $member): array
    {
        $todayAttendance = Attendance::where('member_id', $member->id)
            ->whereDate('check_in_time', Carbon::today())
            ->whereNull('check_out_time')
            ->first();

        if ($todayAttendance) {
            return [
                'can_checkin' => false,
                'can_checkout' => true,
                'attendance' => $todayAttendance,
                'message' => 'Member sudah check-in hari ini dan belum check-out',
            ];
        }

        if ($member->status !== MemberStatus::ACTIVE) {
            return [
                'can_checkin' => false,
                'can_checkout' => false,
                'attendance' => null,
                'message' => 'Status member tidak aktif',
            ];
        }

        if ($member->exp_date < Carbon::today()) {
            return [
                'can_checkin' => false,
                'can_checkout' => false,
                'attendance' => null,
                'message' => 'Keanggotaan sudah expired',
            ];
        }

        return [
            'can_checkin' => true,
            'can_checkout' => false,
            'attendance' => null,
            'message' => 'Member dapat check-in',
        ];
    }

    public function checkDuplicateCheckInToday(Member $member): array
    {
        $todayAttendance = Attendance::where('member_id', $member->id)
            ->whereDate('check_in_time', Carbon::today())
            ->whereNull('check_out_time')
            ->first();

        if ($todayAttendance) {
            return [
                'can_checkin' => false,
                'message' => 'Member sudah melakukan check-in hari ini pada '.$todayAttendance->check_in_time->format('H:i:s'),
            ];
        }

        return [
            'can_checkin' => true,
            'message' => 'Member dapat melakukan check-in',
        ];
    }

    public function checkInMember(Member $member, int $userId, int $autoCheckoutHours = 3): Attendance
    {
        $checkInTime = Carbon::now();

        $attendance = Attendance::create([
            'member_id' => $member->id,
            'check_in_time' => $checkInTime,
            'created_by' => $userId,
        ]);

        // Update member's last check-in and total visits
        $member->update([
            'last_check_in' => $checkInTime,
            'total_visits' => $member->total_visits + 1,
        ]);

        // Dispatch delayed auto-checkout job
        $this->scheduleAutoCheckOut($attendance, $autoCheckoutHours);

        return $attendance->load(['member', 'creator']);
    }

    public function checkOutMember(Attendance $attendance, int $userId): Attendance
    {
        $attendance->update([
            'check_out_time' => Carbon::now(),
            'updated_by' => $userId,
        ]);

        // Cancel any pending auto-checkout job for this attendance
        $this->cancelAutoCheckOutJob($attendance);

        return $attendance->fresh(['member', 'creator']);
    }

    public function getAttendanceById(string $attendanceId): ?Attendance
    {
        return Attendance::with(['member', 'creator'])->find($attendanceId);
    }

    public function exportTodayAttendances(): array
    {
        return $this->exportAttendancesByDate(Carbon::today()->format('Y-m-d'));
    }

    public function exportAttendancesByDate(string $date): array
    {
        $attendances = Attendance::with(['member', 'creator'])
            ->whereDate('check_in_time', $date)
            ->orderBy('check_in_time', 'desc')
            ->get();

        $data = [];
        $data[] = ['Member ID', 'Nama', 'Waktu Check-in', 'Waktu Check-out', 'Staff', 'Status'];

        foreach ($attendances as $attendance) {
            $data[] = [
                $attendance->member->member_code,
                $attendance->member->name,
                $attendance->check_in_time->format('H:i:s'),
                $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : '-',
                $attendance->creator->name ?? 'System',
                $attendance->check_out_time ? 'Check Out' : 'Check In',
            ];
        }

        return $data;
    }

    /**
     * Schedule auto checkout job for the given attendance.
     */
    private function scheduleAutoCheckOut(Attendance $attendance, int $hours): void
    {
        try {
            // Calculate delay in seconds
            $delaySeconds = $hours * 3600; // Convert hours to seconds

            // Dispatch the job with delay
            AutoCheckOutJob::dispatch($attendance->id)
                ->delay(now()->addSeconds($delaySeconds));

            Log::info("Scheduled auto checkout for attendance {$attendance->id} in {$hours} hours");
        } catch (\Exception $e) {
            Log::error("Failed to schedule auto checkout for attendance {$attendance->id}: {$e->getMessage()}");
        }
    }

    /**
     * Cancel pending auto checkout job for the given attendance.
     * Note: This is a simplified implementation. In a production environment,
     * you might want to store job IDs and cancel them explicitly.
     */
    private function cancelAutoCheckOutJob(Attendance $attendance): void
    {
        try {
            // For now, we rely on the job's own validation to skip if already checked out
            // In a more advanced implementation, you could store job IDs and cancel them
            Log::info("Manual checkout detected for attendance {$attendance->id}, auto checkout will be skipped");
        } catch (\Exception $e) {
            Log::error("Error during auto checkout cancellation for attendance {$attendance->id}: {$e->getMessage()}");
        }
    }
}
