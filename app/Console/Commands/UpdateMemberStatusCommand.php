<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MemberService;
use Illuminate\Console\Command;

class UpdateMemberStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update member statuses based on expiration dates and activity';

    public function __construct(private MemberService $memberService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting member status update...');

        try {
            $result = $this->memberService->bulkUpdateMemberStatuses();

            $this->info("Updated {$result['total_updated']} members");

            if ($result['total_updated'] > 0) {
                $this->table(
                    ['Member ID', 'Member Name', 'Old Status', 'New Status', 'Reason'],
                    array_map(function ($detail) {
                        return [
                            $detail['member_id'],
                            $detail['member_name'],
                            $detail['old_status'],
                            $detail['new_status'],
                            $detail['reason'],
                        ];
                    }, $result['details'])
                );
            }

            $this->info('Member status update completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to update member statuses: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
