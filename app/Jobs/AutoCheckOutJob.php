<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\CommandNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoCheckOutJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly int $attendanceId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Find the attendance record
            $attendance = Attendance::with('member')->find($this->attendanceId);

            if (! $attendance) {
                Log::warning("AutoCheckOutJob: Attendance with ID {$this->attendanceId} not found");

                return;
            }

            // Check if already checked out
            if ($attendance->check_out_time) {
                Log::info("AutoCheckOutJob: Attendance {$this->attendanceId} already checked out");

                return;
            }

            // Verify member still exists and is active
            if (! $attendance->member) {
                Log::warning("AutoCheckOutJob: Member not found for attendance {$this->attendanceId}");

                return;
            }

            $checkoutTime = Carbon::now();

            // Update attendance record
            $attendanceUpdated = $attendance->update([
                'check_out_time' => $checkoutTime,
                'updated_by' => 1, // System user ID
            ]);

            if (! $attendanceUpdated) {
                Log::error("AutoCheckOutJob: Failed to update attendance record for ID {$this->attendanceId}");
                throw new \Exception('Failed to update attendance record');
            }

            // Update member's statistics
            $memberUpdated = $attendance->member->update([
                'last_check_in' => $attendance->check_in_time,
                'total_visits' => $attendance->member->total_visits + 1,
            ]);

            if (! $memberUpdated) {
                Log::warning("AutoCheckOutJob: Failed to update member statistics for ID {$attendance->member->id}");
                // Don't throw exception here as the main checkout is successful
            }

            CommandNotification::query()->create([
                'command' => 'Auto Check-out Process',
                'status' => 'success',
                'message' => null,
                'member_name' => $attendance->member->name,
                'checkout_at' => $checkoutTime,
                'is_read' => false,
            ]);

            Log::info("AutoCheckOutJob: Successfully checked out member {$attendance->member->name} at {$checkoutTime}");

        } catch (\Exception $e) {
            Log::error("AutoCheckOutJob: Exception occurred for attendance {$this->attendanceId}: {$e->getMessage()}");
            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("AutoCheckOutJob: Job failed after {$this->tries} attempts for attendance {$this->attendanceId}. Error: {$exception->getMessage()}");

        try {
            $attendance = Attendance::with('member')->find($this->attendanceId);

            if ($attendance && $attendance->member) {
                CommandNotification::query()->create([
                    'command' => 'Auto Check-out Process',
                    'status' => 'failed',
                    'message' => "Gagal auto checkout untuk {$attendance->member->name}: {$exception->getMessage()}",
                    'member_name' => $attendance->member->name,
                    'checkout_at' => null,
                    'is_read' => false,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("AutoCheckOutJob: Failed to create failure notification: {$e->getMessage()}");
        }
    }
}
