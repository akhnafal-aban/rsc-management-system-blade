<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Controllers\NotificationController;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    protected $signature = 'test:notification';

    protected $description = 'Test command untuk membuat notifikasi';

    public function handle(): int
    {
        $this->info('Creating test notification...');
        
        // Create a test notification
        NotificationController::addCommandNotification(
            'Test Notification',
            'success',
            'Ini adalah notifikasi test yang dibuat pada ' . now()->format('Y-m-d H:i:s')
        );
        
        $this->info('Test notification created successfully!');
        
        return Command::SUCCESS;
    }
}
