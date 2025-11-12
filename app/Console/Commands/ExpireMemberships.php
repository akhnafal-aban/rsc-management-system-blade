<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\MemberStatus;
use App\Http\Controllers\NotificationController;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireMemberships extends Command
{
    protected $signature = 'memberships:expire {--dry-run : Show what would be expired without making changes}';

    protected $description = 'Expire memberships that have passed their expiration date';

    public function handle(): int
    {
        // $this->info('Checking for expired memberships...');

        $expiredMembers = Member::where('status', MemberStatus::ACTIVE)
            ->where('exp_date', '<', Carbon::today()->toDateString())
            ->get();

        if ($expiredMembers->isEmpty()) {
            $this->info('No expired memberships found.');

            return Command::SUCCESS;
        }

        // $this->info("Found {$expiredMembers->count()} expired memberships:");

        // foreach ($expiredMembers as $member) {
        //     $this->line("- {$member->name} ({$member->member_code}) - Expired: {$member->exp_date}");
        // }

        // if ($this->option('dry-run')) {
        //     $this->warn('DRY RUN: No changes made.');

        //     return Command::SUCCESS;
        // }

        // Langsung proses tanpa konfirmasi (non-interactive mode)
        foreach ($expiredMembers as $member) {
            try {
                $member->update(['status' => MemberStatus::INACTIVE]);
                NotificationController::addCommandNotification(
                    'Membership Expiration Check',
                    'success',
                    null,
                    $member->name
                );

                Log::info("Expired membership for member: {$member->name} ({$member->member_code})");
            } catch (\Exception $e) {
                $this->error("Failed to expire membership for {$member->name}: {$e->getMessage()}");
                Log::error("Failed to expire membership for {$member->name}: {$e->getMessage()}");
            }
        }

        if ($expiredMembers->isEmpty()) {
            NotificationController::addCommandNotification(
                'Membership Expiration Check',
                'success',
                'Tidak ada keanggotaan yang expired hari ini'
            );
        }

        $this->info('Successfully expired '.$expiredMembers->count().' memberships.');
        Log::info('Membership expiration completed. Expired '.$expiredMembers->count().' memberships.');

        return Command::SUCCESS;
    }
}
