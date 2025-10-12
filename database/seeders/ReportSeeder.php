<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('Users not found. Please run UserSeeder first.');

            return;
        }

        $this->command->info('Creating realistic report data...');

        // Generate historical reports
        $this->generateHistoricalReports($users);

        // Generate recent reports
        $this->generateRecentReports($users);

        // Generate today's reports
        $this->generateTodaysReports($users);

        $this->command->info('Report data created successfully!');
    }

    private function generateHistoricalReports($users): void
    {
        $adminUsers = $users->where('role', 'ADMIN');

        if ($adminUsers->isEmpty()) {
            return;
        }

        // Generate monthly reports for the last 6 months
        for ($i = 6; $i >= 1; $i--) {
            $startDate = Carbon::now()->subMonths($i)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $generatedBy = $adminUsers->random();

            Report::create([
                'format' => 'MONTHLY',
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
                'generated_by' => $generatedBy->id,
                'created_at' => fake()->dateTimeBetween($endDate, $endDate->addDays(5)),
            ]);
        }

        // Generate weekly reports for the last 4 weeks
        for ($i = 4; $i >= 1; $i--) {
            $startDate = Carbon::now()->subWeeks($i)->startOfWeek();
            $endDate = $startDate->copy()->endOfWeek();
            $generatedBy = $adminUsers->random();

            Report::create([
                'format' => 'WEEKLY',
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
                'generated_by' => $generatedBy->id,
                'created_at' => fake()->dateTimeBetween($endDate, $endDate->addDays(2)),
            ]);
        }

        // Generate some daily reports for the last month (randomly)
        $dailyReportsCount = fake()->numberBetween(10, 20);
        for ($i = 0; $i < $dailyReportsCount; $i++) {
            $reportDate = fake()->dateTimeBetween('-30 days', '-7 days');
            $generatedBy = $adminUsers->random();

            Report::create([
                'format' => 'DAILY',
                'period_start' => $reportDate->format('Y-m-d'),
                'period_end' => $reportDate->format('Y-m-d'),
                'generated_by' => $generatedBy->id,
                'created_at' => fake()->dateTimeBetween($reportDate, $reportDate->copy()->addDays(1)),
            ]);
        }
    }

    private function generateRecentReports($users): void
    {
        $adminUsers = $users->where('role', 'ADMIN');

        if ($adminUsers->isEmpty()) {
            return;
        }

        // Generate reports for the current month
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();

        Report::create([
            'format' => 'MONTHLY',
            'period_start' => $currentMonthStart->format('Y-m-d'),
            'period_end' => $currentMonthEnd->format('Y-m-d'),
            'generated_by' => $adminUsers->random()->id,
            'created_at' => fake()->dateTimeBetween($currentMonthStart, 'now'),
        ]);

        // Generate reports for the current week
        $currentWeekStart = Carbon::now()->startOfWeek();
        $currentWeekEnd = Carbon::now()->endOfWeek();

        Report::create([
            'format' => 'WEEKLY',
            'period_start' => $currentWeekStart->format('Y-m-d'),
            'period_end' => $currentWeekEnd->format('Y-m-d'),
            'generated_by' => $adminUsers->random()->id,
            'created_at' => fake()->dateTimeBetween($currentWeekStart, 'now'),
        ]);

        // Generate some recent daily reports
        $recentDailyReports = fake()->numberBetween(3, 7);
        for ($i = 0; $i < $recentDailyReports; $i++) {
            $reportDate = fake()->dateTimeBetween('-7 days', '-1 day');
            $generatedBy = $adminUsers->random();

            Report::create([
                'format' => 'DAILY',
                'period_start' => $reportDate->format('Y-m-d'),
                'period_end' => $reportDate->format('Y-m-d'),
                'generated_by' => $generatedBy->id,
                'created_at' => fake()->dateTimeBetween($reportDate, $reportDate->copy()->addDays(1)),
            ]);
        }
    }

    private function generateTodaysReports($users): void
    {
        $adminUsers = $users->where('role', 'ADMIN');

        if ($adminUsers->isEmpty()) {
            return;
        }

        // Generate today's daily report
        $today = Carbon::today();

        Report::create([
            'format' => 'DAILY',
            'period_start' => $today->format('Y-m-d'),
            'period_end' => $today->format('Y-m-d'),
            'generated_by' => $adminUsers->random()->id,
            'created_at' => fake()->dateTimeBetween('today', 'now'),
        ]);

        // Sometimes generate multiple reports on the same day
        if (fake()->boolean(30)) { // 30% chance
            Report::create([
                'format' => 'DAILY',
                'period_start' => $today->format('Y-m-d'),
                'period_end' => $today->format('Y-m-d'),
                'generated_by' => $adminUsers->random()->id,
                'created_at' => fake()->dateTimeBetween('today', 'now'),
            ]);
        }
    }
}
