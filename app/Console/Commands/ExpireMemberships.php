<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\MemberStatus;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireMemberships extends Command
{
    protected $signature = 'memberships:expire {--dry-run : Show what would be expired without making changes}';

    protected $description = 'Expire memberships that have passed their expiration date';

    public function handle(): int
    {
        $this->info('Checking for expired memberships...');

        $expiredMembers = Member::where('status', MemberStatus::ACTIVE)
            ->where('exp_date', '<', Carbon::today()->toDateString())
            ->get();

        if ($expiredMembers->isEmpty()) {
            $this->info('No expired memberships found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$expiredMembers->count()} expired memberships:");

        foreach ($expiredMembers as $member) {
            $this->line("- {$member->name} ({$member->member_code}) - Expired: {$member->exp_date}");
        }

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN: No changes made.');

            return Command::SUCCESS;
        }

        $updated = Member::where('status', MemberStatus::ACTIVE)
            ->where('exp_date', '<', Carbon::today()->toDateString())
            ->update(['status' => MemberStatus::INACTIVE]);

        $this->info("Automatically expired {$updated} memberships.");


        return Command::SUCCESS;
    }
}
