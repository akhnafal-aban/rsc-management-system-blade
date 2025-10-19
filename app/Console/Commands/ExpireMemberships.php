<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\MemberStatus;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
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
        $expiredMemberNames = [];

        foreach ($expiredMembers as $member) {
            try {
                $member->update(['status' => MemberStatus::INACTIVE]);
                $expiredMemberNames[] = $member->name;

                Log::info("Expired membership for member: {$member->name} ({$member->member_code})");
            } catch (\Exception $e) {
                $this->error("Failed to expire membership for {$member->name}: {$e->getMessage()}");
                Log::error("Failed to expire membership for {$member->name}: {$e->getMessage()}");
            }
        }

        // Store expired member info for notification
        if (! empty($expiredMemberNames)) {
            Cache::put('last_expired_members', $expiredMemberNames, now()->addHours(1));
        }

        $this->info('Successfully expired ' . count($expiredMemberNames) . ' memberships.');
        Log::info('Membership expiration completed. Expired ' . count($expiredMemberNames) . ' memberships.');

        return Command::SUCCESS;
    }
}
