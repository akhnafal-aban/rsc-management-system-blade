<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ShiftType;
use App\Models\StaffSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StaffScheduleService
{
    public function createOrUpdateSchedule(int $userId, string $date, ShiftType $shiftType, ?string $notes = null, int $createdBy): StaffSchedule
    {
        return DB::transaction(function () use ($userId, $date, $shiftType, $notes, $createdBy) {
            $schedule = StaffSchedule::updateOrCreate(
                [
                    'user_id' => (int) $userId,
                    'schedule_date' => $date,
                ],
                [
                    'shift_type' => $shiftType,
                    'notes' => $notes,
                    'created_by' => (int) $createdBy,
                ]
            );

            return $schedule->load(['user', 'creator']);
        });
    }

    public function getScheduleByDateRange(string $startDate, string $endDate, ?int $userId = null): Collection
    {
        $query = StaffSchedule::with(['user', 'creator'])
            ->byDateRange($startDate, $endDate)
            ->orderBy('schedule_date')
            ->orderBy('shift_type');

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    public function getScheduleForMonth(string $month, ?int $userId = null): Collection
    {
        $startDate = Carbon::parse($month)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($month)->endOfMonth()->format('Y-m-d');

        return $this->getScheduleByDateRange($startDate, $endDate, $userId);
    }

    public function getScheduleByDate(string $date, ?int $userId = null): Collection
    {
        $query = StaffSchedule::with(['user', 'creator'])
            ->where('schedule_date', $date)
            ->orderBy('shift_type');

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    public function deleteSchedule(int $scheduleId): bool
    {
        $schedule = StaffSchedule::findOrFail($scheduleId);

        return $schedule->delete();
    }

    public function getScheduleById(int $scheduleId): StaffSchedule
    {
        return StaffSchedule::with(['user', 'creator'])->findOrFail($scheduleId);
    }

    public function getStaffScheduleForDate(int $userId, string $date): ?StaffSchedule
    {
        return StaffSchedule::where('user_id', $userId)
            ->where('schedule_date', $date)
            ->first();
    }

    public function getAllStaff(): Collection
    {
        return User::where('role', 'STAFF')->orderBy('name')->get();
    }
}
