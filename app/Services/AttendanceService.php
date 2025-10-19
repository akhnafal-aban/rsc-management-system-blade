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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    public function getTodayAttendances(int $perPage = 10, ?string $search = null, ?string $statusFilter = null): LengthAwarePaginator
    {
        return $this->getAttendancesByDate(Carbon::today()->format('Y-m-d'), $perPage, $search, $statusFilter);
    }

    public function getAttendancesByDate(string $date, int $perPage = 10, ?string $search = null, ?string $statusFilter = null): LengthAwarePaginator
    {
        // Optimized query dengan JOIN untuk menghindari N+1 problem
        $query = DB::table('attendances')
            ->leftJoin('members', 'attendances.member_id', '=', 'members.id')
            ->leftJoin('users as creators', 'attendances.created_by', '=', 'creators.id')
            ->select([
                'attendances.id',
                'attendances.member_id',
                'attendances.check_in_time',
                'attendances.check_out_time',
                'attendances.created_by',
                'attendances.updated_by',
                'attendances.created_at',
                'attendances.updated_at',
                'members.member_code',
                'members.name as member_name',
                'creators.name as creator_name',
            ])
            ->whereDate('attendances.check_in_time', $date);

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('members.name', 'LIKE', "%{$search}%")
                    ->orWhere('members.member_code', 'LIKE', "%{$search}%");
            });
        }

        // Apply status filter
        if ($statusFilter === 'checkin') {
            $query->whereNull('attendances.check_out_time');
        } elseif ($statusFilter === 'checkout') {
            $query->whereNotNull('attendances.check_out_time');
        }

        return $query->orderBy('attendances.check_in_time', 'desc')->paginate($perPage);
    }

    public function searchMembers(string $query): Collection
    {
        $cacheKey = CacheService::getMemberSearchKey($query, 10);

        return Cache::remember($cacheKey, CacheService::CACHE_TTL_MEDIUM, function () use ($query) {
            $results = DB::table('members')
                ->select([
                    'id',
                    'member_code',
                    'name',
                    'email',
                    'phone',
                    'status',
                    'exp_date',
                ])
                ->where('status', \App\Enums\MemberStatus::ACTIVE)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('member_code', 'LIKE', "%{$query}%")
                        ->orWhere('phone', 'LIKE', "%{$query}%");
                })
                ->orderBy('name')
                ->limit(10)
                ->get();

            // Convert to Eloquent Collection untuk kompatibilitas
            return Member::hydrate($results->toArray());
        });
    }

    public function getMemberById(string $memberId): ?Member
    {
        // Optimized query dengan single database call
        $result = DB::table('members')
            ->select([
                'id',
                'member_code',
                'name',
                'email',
                'phone',
                'status',
                'exp_date',
                'last_check_in',
                'total_visits',
                'created_at',
                'updated_at',
            ])
            ->where('member_code', $memberId)
            ->orWhere('id', $memberId)
            ->first();

        return $result ? Member::hydrate([$result])->first() : null;
    }

    public function canCheckIn(Member $member): array
    {
        $today = Carbon::today();

        // Single optimized query untuk cek attendance hari ini
        $todayAttendance = DB::table('attendances')
            ->select([
                'id',
                'member_id',
                'check_in_time',
                'check_out_time',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ])
            ->where('member_id', $member->id)
            ->whereDate('check_in_time', $today)
            ->whereNull('check_out_time')
            ->first();

        if ($todayAttendance) {
            $attendance = Attendance::hydrate([$todayAttendance])->first();

            return [
                'can_checkin' => false,
                'can_checkout' => true,
                'attendance' => $attendance,
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

        return [
            'can_checkin' => true,
            'can_checkout' => false,
            'attendance' => null,
            'message' => 'Member dapat check-in',
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

        // Invalidate relevant caches
        CacheService::invalidateAttendanceCaches();

        // Invalidate dashboard cache
        $dashboardService = app(DashboardService::class);
        $dashboardService->invalidateDashboardCache();

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

        // Invalidate relevant caches
        CacheService::invalidateAttendanceCaches();

        // Invalidate dashboard cache
        $dashboardService = app(DashboardService::class);
        $dashboardService->invalidateDashboardCache();

        return $attendance->fresh(['member', 'creator']);
    }

    public function getAttendanceById(string $attendanceId): ?Attendance
    {
        // Optimized query dengan JOIN untuk menghindari N+1 problem
        $result = DB::table('attendances')
            ->leftJoin('members', 'attendances.member_id', '=', 'members.id')
            ->leftJoin('users as creators', 'attendances.created_by', '=', 'creators.id')
            ->select([
                'attendances.id',
                'attendances.member_id',
                'attendances.check_in_time',
                'attendances.check_out_time',
                'attendances.created_by',
                'attendances.updated_by',
                'attendances.created_at',
                'attendances.updated_at',
                'members.member_code',
                'members.name as member_name',
                'members.email as member_email',
                'members.phone as member_phone',
                'members.status as member_status',
                'members.exp_date as member_exp_date',
                'creators.name as creator_name',
            ])
            ->where('attendances.id', $attendanceId)
            ->first();

        if (! $result) {
            return null;
        }

        // Convert to Eloquent model dengan relationships
        $attendance = Attendance::hydrate([$result])->first();

        // Manually set relationships untuk kompatibilitas
        $member = new Member([
            'id' => $result->member_id,
            'member_code' => $result->member_code,
            'name' => $result->member_name,
            'email' => $result->member_email,
            'phone' => $result->member_phone,
            'status' => $result->member_status,
            'exp_date' => $result->member_exp_date,
        ]);

        $creator = new \App\Models\User([
            'id' => $result->created_by,
            'name' => $result->creator_name,
        ]);

        $attendance->setRelation('member', $member);
        $attendance->setRelation('creator', $creator);

        return $attendance;
    }

    public function exportTodayAttendances(): array
    {
        return $this->exportAttendancesByDate(Carbon::today()->format('Y-m-d'));
    }

    public function exportAttendancesByDate(string $date): array
    {
        // Optimized query dengan single JOIN untuk export
        $attendances = DB::table('attendances')
            ->leftJoin('members', 'attendances.member_id', '=', 'members.id')
            ->leftJoin('users as creators', 'attendances.created_by', '=', 'creators.id')
            ->select([
                'attendances.check_in_time',
                'attendances.check_out_time',
                'members.member_code',
                'members.name as member_name',
                'creators.name as creator_name',
            ])
            ->whereDate('attendances.check_in_time', $date)
            ->orderBy('attendances.check_in_time', 'desc')
            ->get();

        $data = [];
        $data[] = ['Member ID', 'Nama', 'Waktu Check-in', 'Waktu Check-out', 'Staff', 'Status'];

        foreach ($attendances as $attendance) {
            $checkInTime = Carbon::parse($attendance->check_in_time);
            $checkOutTime = $attendance->check_out_time ? Carbon::parse($attendance->check_out_time) : null;

            $data[] = [
                $attendance->member_code,
                $attendance->member_name,
                $checkInTime->format('H:i:s'),
                $checkOutTime ? $checkOutTime->format('H:i:s') : '-',
                $attendance->creator_name ?? 'System',
                $checkOutTime ? 'Check Out' : 'Check In',
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
