<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCheckOut extends Command
{
    protected $signature = 'attendance:auto-checkout {--hours=5 : Hours after which to auto checkout} {--dry-run : Show what would be checked out without making changes}';

    protected $description = 'Automatically check out members who have been checked in for more than specified hours';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $this->info("Checking for members checked in for more than {$hours} hours...");

        $cutoffTime = Carbon::now()->subHours($hours);

        $pendingCheckouts = Attendance::with(['member'])
            ->whereNull('check_out_time')
            ->where('check_in_time', '<=', $cutoffTime)
            ->get();

        if ($pendingCheckouts->isEmpty()) {
            $this->info('No members found for auto check-out.');
            return Command::SUCCESS;
        }

        $this->info("Found {$pendingCheckouts->count()} members for auto check-out.");

        if ($this->option('dry-run')) {
            foreach ($pendingCheckouts as $attendance) {
                $hoursCheckedIn = Carbon::parse($attendance->check_in_time)->diffInHours(Carbon::now());
                $this->line("- {$attendance->member->name} ({$attendance->member->member_code}) - Checked in: {$attendance->check_in_time->format('H:i')} ({$hoursCheckedIn} hours ago)");
            }
            $this->warn('DRY RUN: No changes made.');
            return Command::SUCCESS;
        }

        $checkedOut = 0;

        foreach ($pendingCheckouts as $attendance) {
            try {
                $attendance->update([
                    'check_out_time' => Carbon::now(),
                    'updated_by' => 1, // System user ID
                ]);

                $attendance->member->update([
                    'last_check_in' => $attendance->check_in_time,
                    'total_visits' => $attendance->member->total_visits + 1,
                ]);

                $checkedOut++;
                Log::info("Auto check-out: {$attendance->member->name} ({$attendance->member->member_code}) at {$attendance->check_out_time}");
            } catch (\Exception $e) {
                Log::error("Auto check-out failed for {$attendance->member->name}: {$e->getMessage()}");
            }
        }

        $this->info("Automatically checked-out {$checkedOut} members.");

        return Command::SUCCESS;
    }
}
