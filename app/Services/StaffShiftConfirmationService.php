<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ShiftType;
use App\Models\StaffShiftConfirmation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StaffShiftConfirmationService
{
    public function hasConfirmedShiftToday(int $userId): bool
    {
        return StaffShiftConfirmation::today($userId)->exists();
    }

    public function confirmShift(int $userId, ShiftType $shiftType, ?int $scheduleId = null, ?string $notes = null): StaffShiftConfirmation
    {
        return DB::transaction(function () use ($userId, $shiftType, $scheduleId, $notes) {
            // Check if already confirmed today
            $existing = StaffShiftConfirmation::today($userId)->first();

            if ($existing) {
                // Update existing confirmation
                $existing->update([
                    'shift_type' => $shiftType,
                    'staff_schedule_id' => $scheduleId,
                    'notes' => $notes,
                    'confirmed_at' => Carbon::now(),
                ]);

                return $existing->fresh();
            }

            // Create new confirmation
            return StaffShiftConfirmation::create([
                'user_id' => $userId,
                'staff_schedule_id' => $scheduleId,
                'confirmation_date' => Carbon::today(),
                'shift_type' => $shiftType,
                'confirmed_at' => Carbon::now(),
                'notes' => $notes,
            ]);
        });
    }

    public function getTodayConfirmation(int $userId): ?StaffShiftConfirmation
    {
        return StaffShiftConfirmation::today($userId)->first();
    }

    public function getStaffScheduleForToday(int $userId): ?object
    {
        $today = Carbon::today()->format('Y-m-d');
        $scheduleService = new StaffScheduleService();

        return $scheduleService->getStaffScheduleForDate($userId, $today);
    }
}
