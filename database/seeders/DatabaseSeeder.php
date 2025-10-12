<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');

        // Clear existing data (optional - uncomment if needed)
        // $this->command->info('Clearing existing data...');
        // $this->clearExistingData();

        // Run seeders in correct order to maintain relationships
        $this->call([
            UserSeeder::class,        // Create users first (needed for foreign keys)
            MemberSeeder::class,      // Create members
            MembershipSeeder::class,  // Create memberships (depends on members)
            AttendanceSeeder::class,  // Create attendance (depends on members & users)
            PaymentSeeder::class,     // Create payments (depends on members)
            // ActivityLogSeeder::class, // Create activity logs (depends on users)
            // ReportSeeder::class,      // Create reports (depends on users)
        ]);

        $this->command->info('Database seeding completed successfully!');
    }

    private function clearExistingData(): void
    {
        // Uncomment the following lines if you want to clear existing data
        // \App\Models\Report::truncate();
        // \App\Models\ActivityLog::truncate();
        // \App\Models\Payment::truncate();
        // \App\Models\Attendance::truncate();
        // \App\Models\Membership::truncate();
        // \App\Models\Member::truncate();
        // \App\Models\User::truncate();
    }
}
