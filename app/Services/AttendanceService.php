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
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    public function getTodayAttendances(int $perPage = 10, ?string $search = null, ?string $statusFilter = null): LengthAwarePaginator
    {
        return $this->getAttendancesByDate(Carbon::today()->format('Y-m-d'), $perPage, $search, $statusFilter);
    }

    public function getAttendancesByDate(
        string $date,
        int $perPage = 10,
        ?string $search = null,
        ?string $statusFilter = null
    ): LengthAwarePaginator {
    
        $dateCarbon = Carbon::parse($date);
        $start = $dateCarbon->startOfDay()->toDateTimeString();
        $end = $dateCarbon->endOfDay()->toDateTimeString();
    
        // Named bindings
        $params = [
            'start' => $start,
            'end' => $end,
        ];
    
        $conditions = "attendances.check_in_time BETWEEN :start AND :end";
    
        if (!empty($search)) {
            $conditions .= " AND (members.name LIKE :search OR members.member_code LIKE :search)";
            $params['search'] = "%{$search}%";
        }
    
        if ($statusFilter === 'checkin') {
            $conditions .= " AND attendances.check_out_time IS NULL";
        } elseif ($statusFilter === 'checkout') {
            $conditions .= " AND attendances.check_out_time IS NOT NULL";
        }
    
        // COUNT query optimized
        $countSql = "
            SELECT COUNT(*) AS total
            FROM attendances
            LEFT JOIN members ON attendances.member_id = members.id
            WHERE $conditions
        ";
    
        $total = DB::selectOne($countSql, $params)->total;
    
        // Add pagination parameters
        $params['limit'] = $perPage;
        $params['offset'] = (Paginator::resolveCurrentPage() - 1) * $perPage;
    
        // DATA query
        $dataSql = "
            SELECT
                attendances.id,
                attendances.member_id,
                attendances.check_in_time,
                attendances.check_out_time,
                attendances.created_by,
                attendances.updated_by,
                attendances.created_at,
                attendances.updated_at,
                members.member_code,
                members.name AS member_name,
                users.name AS creator_name
            FROM attendances
            LEFT JOIN members ON attendances.member_id = members.id
            LEFT JOIN users ON attendances.created_by = users.id
            WHERE $conditions
            ORDER BY attendances.check_in_time DESC
            LIMIT :limit OFFSET :offset
        ";
    
        $items = collect(DB::select($dataSql, $params));
    
        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            Paginator::resolveCurrentPage(),
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => [
                    'search' => $search,
                    'status' => $statusFilter,
                    'date' => $date,
                ]
            ]
        );
    }    

    public function searchMembers(string $query): Collection
    {
        $today = Carbon::today();
        $startOfDay = $today->startOfDay();
        $endOfDay = $today->endOfDay();

        $sql = <<<'SQL'
SELECT
    m.id,
    m.member_code,
    m.name,
    m.email,
    m.phone,
    m.status,
    m.exp_date,
    EXISTS (
        SELECT 1
        FROM attendances a
        WHERE a.member_id = m.id
          AND a.check_in_time BETWEEN ? AND ?
          AND a.check_out_time IS NULL
    ) AS has_checked_in_today
FROM members m
WHERE m.status IN (?, ?)
  AND (
        m.name LIKE ?
        OR m.member_code LIKE ?
        OR m.phone LIKE ?
      )
ORDER BY m.name
LIMIT 10
SQL;

        $likeQuery = '%'.$query.'%';

        $rows = DB::select($sql, [
            $startOfDay,
            $endOfDay,
            MemberStatus::ACTIVE->value,
            MemberStatus::EXPIRED->value,
            $likeQuery,
            $likeQuery,
            $likeQuery,
        ]);

        $transformed = array_map(static function ($row): array {
            $data = (array) $row;
            $hasCheckedInToday = (bool) $data['has_checked_in_today'];
            $isActive = $data['status'] === MemberStatus::ACTIVE->value;

            $data['has_checked_in_today'] = $hasCheckedInToday;
            $data['can_checkin'] = $isActive && ! $hasCheckedInToday;

            return $data;
        }, $rows);

        return Member::hydrate($transformed);
    }

    public function getMemberById(string $memberId): ?Member
    {
        $sql = <<<'SQL'
SELECT
    id,
    member_code,
    name,
    email,
    phone,
    status,
    exp_date,
    last_check_in,
    total_visits,
    created_at,
    updated_at
FROM members
WHERE member_code = ?
   OR id = ?
LIMIT 1
SQL;

        $result = DB::selectOne($sql, [$memberId, $memberId]);

        return $result ? Member::hydrate([(array) $result])->first() : null;
    }

    public function canCheckIn(Member $member): array
    {
        $today = Carbon::today();
        $startOfDay = $today->startOfDay();
        $endOfDay = $today->endOfDay();

        $sql = <<<'SQL'
SELECT
    id,
    member_id,
    check_in_time,
    check_out_time,
    created_by,
    updated_by,
    created_at,
    updated_at
FROM attendances
WHERE member_id = ?
  AND check_in_time BETWEEN ? AND ?
  AND check_out_time IS NULL
LIMIT 1
SQL;

        $todayAttendance = DB::selectOne($sql, [$member->id, $startOfDay, $endOfDay]);

        if ($todayAttendance) {
            $attendance = Attendance::hydrate([(array) $todayAttendance])->first();

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

        return $attendance->load(['member', 'creator']);
    }

    /**
     * @return array{
     *     success: bool,
     *     checked_in: array<int, array<string, mixed>>,
     *     skipped: array<int, array<string, mixed>>
     * }
     */
    public function checkInMembersBatch(array $memberIds, int $userId, int $autoCheckoutHours = 3): array
    {
        $uniqueIds = array_values(array_unique(array_map('intval', $memberIds)));

        if (empty($uniqueIds)) {
            return [
                'success' => false,
                'checked_in' => [],
                'skipped' => [],
            ];
        }

        $now = Carbon::now();
        $today = $now->toDateString();

        $members = Member::query()
            ->select(['id', 'name', 'status'])
            ->whereIn('id', $uniqueIds)
            ->get()
            ->keyBy('id');

        $skipped = [];

        foreach ($uniqueIds as $memberId) {
            if (! $members->has($memberId)) {
                $skipped[] = [
                    'member_id' => $memberId,
                    'reason' => 'Member tidak ditemukan',
                ];
            }
        }

        $eligibleMembers = $members->filter(function (Member $member) use (&$skipped): bool {
            if ($member->status !== MemberStatus::ACTIVE) {
                $skipped[] = [
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'reason' => 'Status member tidak aktif',
                ];

                return false;
            }

            return true;
        });

        if ($eligibleMembers->isEmpty()) {
            return [
                'success' => false,
                'checked_in' => [],
                'skipped' => $skipped,
            ];
        }

        $eligibleIds = $eligibleMembers->keys()->all();

        $existingAttendance = DB::table('attendances')
            ->select('member_id')
            ->whereIn('member_id', $eligibleIds)
            ->whereDate('check_in_time', $today)
            ->whereNull('check_out_time')
            ->pluck('member_id')
            ->all();

        if (! empty($existingAttendance)) {
            foreach ($existingAttendance as $memberId) {
                $member = $members->get((int) $memberId);
                $skipped[] = [
                    'member_id' => (int) $memberId,
                    'member_name' => $member?->name,
                    'reason' => 'Member sudah check-in dan belum check-out',
                ];
            }
        }

        $insertableIds = array_values(array_diff($eligibleIds, $existingAttendance));

        if (empty($insertableIds)) {
            return [
                'success' => false,
                'checked_in' => [],
                'skipped' => $skipped,
            ];
        }

        $rows = [];

        foreach ($insertableIds as $memberId) {
            $rows[] = [
                'member_id' => $memberId,
                'check_in_time' => $now,
                'created_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $attendanceIds = [];

        DB::transaction(function () use ($rows, $insertableIds, $now, $userId, &$attendanceIds): void {
            DB::table('attendances')->insert($rows);

            DB::table('members')
                ->whereIn('id', $insertableIds)
                ->update([
                    'last_check_in' => $now,
                    'total_visits' => DB::raw('total_visits + 1'),
                    'updated_at' => $now,
                ]);

            // Optimasi: gunakan whereBetween untuk timestamp daripada whereDate (lebih cepat dengan index)
            $startOfDay = $now->copy()->startOfDay();
            $endOfDay = $now->copy()->endOfDay();
            $attendanceIds = DB::table('attendances')
                ->whereIn('member_id', $insertableIds)
                ->whereBetween('check_in_time', [$startOfDay, $endOfDay])
                ->where('created_by', $userId)
                ->whereNull('check_out_time')
                ->pluck('id')
                ->all();
        });

        // Batch dispatch jobs untuk mengurangi overhead
        $delaySeconds = $autoCheckoutHours * 3600;
        foreach ($attendanceIds as $attendanceId) {
            AutoCheckOutJob::dispatch($attendanceId)
                ->delay(now()->addSeconds($delaySeconds));
        }

        // Ambil attendances hanya untuk response, tanpa eager loading yang tidak perlu
        $createdAttendances = Attendance::query()
            ->whereIn('id', $attendanceIds)
            ->get();

        $checkedIn = $createdAttendances->map(function (Attendance $attendance) use ($members, $now): array {
            $member = $members->get($attendance->member_id);

            return [
                'attendance_id' => $attendance->id,
                'member_id' => $attendance->member_id,
                'member_name' => $member?->name,
                'check_in_time' => $attendance->check_in_time?->format('H:i:s') ?? $now->format('H:i:s'),
            ];
        })->toArray();

        return [
            'success' => ! empty($checkedIn),
            'checked_in' => $checkedIn,
            'skipped' => $skipped,
        ];
    }

    public function checkOutMember(Attendance $attendance, int $userId): Attendance
    {
        $attendance->update([
            'check_out_time' => Carbon::now(),
            'updated_by' => $userId,
        ]);

        $this->cancelAutoCheckOutJob($attendance);

        return $attendance->fresh(['member', 'creator']);
    }

    public function getAttendanceById(string $attendanceId): ?Attendance
    {
        $sql = <<<'SQL'
SELECT
    attendances.id,
    attendances.member_id,
    attendances.check_in_time,
    attendances.check_out_time,
    attendances.created_by,
    attendances.updated_by,
    attendances.created_at,
    attendances.updated_at,
    members.member_code,
    members.name AS member_name,
    members.email AS member_email,
    members.phone AS member_phone,
    members.status AS member_status,
    members.exp_date AS member_exp_date,
    creators.name AS creator_name
FROM attendances
LEFT JOIN members ON attendances.member_id = members.id
LEFT JOIN users AS creators ON attendances.created_by = creators.id
WHERE attendances.id = ?
LIMIT 1
SQL;

        $result = DB::selectOne($sql, [$attendanceId]);

        if (! $result) {
            return null;
        }

        $attendance = Attendance::hydrate([(array) $result])->first();

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
        $dateCarbon = Carbon::parse($date);
        $startOfDay = $dateCarbon->startOfDay();
        $endOfDay = $dateCarbon->endOfDay();

        $sql = <<<'SQL'
SELECT
    attendances.check_in_time,
    attendances.check_out_time,
    members.member_code,
    members.name AS member_name,
    creators.name AS creator_name
FROM attendances
LEFT JOIN members ON attendances.member_id = members.id
LEFT JOIN users AS creators ON attendances.created_by = creators.id
WHERE attendances.check_in_time BETWEEN ? AND ?
ORDER BY attendances.check_in_time DESC
SQL;

        $attendances = DB::select($sql, [$startOfDay, $endOfDay]);

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
