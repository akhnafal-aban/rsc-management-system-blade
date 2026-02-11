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
        // EXPIRED Member past expiration date
        $expiredMembers = Member::where('status', MemberStatus::ACTIVE)
            ->where('exp_date', '<', Carbon::today()->toDateString())
            ->get();

        // INACTIVE Member 3 months after expiration
        $inactiveMembers = Member::where('status', MemberStatus::EXPIRED)
            ->where('exp_date', '<', Carbon::today()->subMonths(3)->toDateString())
            ->get();

        $wrongStatus = Member::where('status', MemberStatus::INACTIVE)
            ->where('exp_date', '>=', Carbon::today()->subMonths(3)->toDateString())
            ->get();

        // Exipired Members Check and excecution
        if ($expiredMembers->isEmpty()) {
            $this->info('No memberships to set as expired found.');
            Log::info('No memberships to set as EXPIRED today.');
        } else {
            foreach ($expiredMembers as $member) {
                try {
                    $member->update(['status' => MemberStatus::EXPIRED]);
                    NotificationController::addCommandNotification(
                        'Membership Expiration Check',
                        '',
                        null,
                        $member->name
                    );
                    Log::info("Set membership to EXPIRED for member: {$member->name} ({$member->member_code})");
                } catch (\Exception $e) {
                    $this->error("Failed to set membership to EXPIRED for {$member->name}: {$e->getMessage()}");
                    Log::error("Failed to set membership to EXPIRED for {$member->name}: {$e->getMessage()}");
                }
            }
            $this->info('Successfully set ' . $expiredMembers->count() . ' memberships to EXPIRED.');
            Log::info('Membership expiration completed. Expired ' . $expiredMembers->count() . ' memberships.');
        }


        // Inactive Members Check and excecution
        if ($inactiveMembers->isEmpty()) {
            $this->info('No memberships to set as inactive found.');
            Log::info('No memberships to set as INACTIVE today.');
        } else {
            foreach ($inactiveMembers as $member) {
                try {
                    $member->update(['status' => MemberStatus::INACTIVE]);
                    NotificationController::addCommandNotification(
                        'Membership Inactivation Check',
                        '',
                        null,
                        $member->name
                    );

                    Log::info("Set membership to INACTIVE for member: {$member->name} ({$member->member_code})");
                } catch (\Exception $e) {
                    $this->error("Failed to set membership to INACTIVE for {$member->name}: {$e->getMessage()}");
                    Log::error("Failed to set membership to INACTIVE for {$member->name}: {$e->getMessage()}");
                }
            }
            $this->info('Successfully set ' . $inactiveMembers->count() . ' memberships to INACTIVE.');
            Log::info('Membership inactivation completed. Set ' . $inactiveMembers->count() . ' memberships to INACTIVE.');
        }

        // Fix Wrong Status Members
        if ($wrongStatus->isNotEmpty()) {
            foreach ($wrongStatus as $member) {
                try {
                    $member->update(['status' => MemberStatus::EXPIRED]);
                    Log::info("Corrected membership status to EXPIRED for member: {$member->name} ({$member->member_code})");
                } catch (\Exception $e) {
                    $this->error("Failed to correct membership status for {$member->name}: {$e->getMessage()}");
                    Log::error("Failed to correct membership status for {$member->name}: {$e->getMessage()}");
                }
            }
            $this->info('Corrected ' . $wrongStatus->count() . ' memberships with wrong status to EXPIRED.');
            Log::info('Corrected ' . $wrongStatus->count() . ' memberships with wrong status to EXPIRED.');
        } else {
            $this->info('No memberships with wrong status found.');
            Log::info('No memberships with wrong status found.');
        }

        return Command::SUCCESS;
    }
}
