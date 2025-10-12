<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('Users not found. Please run UserSeeder first.');

            return;
        }

        $this->command->info('Creating realistic activity log data...');

        // Activity patterns for different user types
        $activityPatterns = [
            'ADMIN' => [
                'LOGIN' => 0.15,
                'LOGOUT' => 0.15,
                'CHECK_IN' => 0.10,
                'CHECK_OUT' => 0.10,
                'CREATE_MEMBER' => 0.08,
                'UPDATE_MEMBER' => 0.12,
                'CREATE_PAYMENT' => 0.08,
                'GENERATE_REPORT' => 0.15,
                'UPDATE_PROFILE' => 0.07,
            ],
            'STAFF' => [
                'LOGIN' => 0.20,
                'LOGOUT' => 0.20,
                'CHECK_IN' => 0.25,
                'CHECK_OUT' => 0.25,
                'CREATE_MEMBER' => 0.05,
                'UPDATE_MEMBER' => 0.03,
                'CREATE_PAYMENT' => 0.02,
                'GENERATE_REPORT' => 0.00,
                'UPDATE_PROFILE' => 0.00,
            ],
        ];

        $actionDescriptions = [
            'LOGIN' => 'User berhasil login ke sistem',
            'LOGOUT' => 'User logout dari sistem',
            'CHECK_IN' => 'Melakukan check-in member',
            'CHECK_OUT' => 'Melakukan check-out member',
            'CREATE_MEMBER' => 'Membuat member baru',
            'UPDATE_MEMBER' => 'Mengupdate data member',
            'CREATE_PAYMENT' => 'Membuat pembayaran baru',
            'GENERATE_REPORT' => 'Membuat laporan',
            'UPDATE_PROFILE' => 'Mengupdate profil user',
        ];

        // Generate activity logs for each user
        foreach ($users as $user) {
            $userRole = $user->role;
            $pattern = $activityPatterns[$userRole] ?? $activityPatterns['STAFF'];

            // Determine number of activities based on user's registration date
            $userCreatedAt = $user->created_at;
            $daysSinceRegistration = $userCreatedAt->diffInDays(Carbon::now());
            $avgActivitiesPerDay = fake()->numberBetween(3, 8); // Realistic daily activity
            $totalActivities = $daysSinceRegistration * $avgActivitiesPerDay;

            // Generate activity logs
            for ($i = 0; $i < $totalActivities; $i++) {
                $action = $this->selectActionByWeight($pattern);
                $createdAt = fake()->dateTimeBetween($userCreatedAt, 'now');

                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => $action,
                    'description' => $actionDescriptions[$action],
                    'created_at' => $createdAt,
                ]);
            }
        }

        // Generate additional recent activities (last 7 days)
        $recentActivityCount = fake()->numberBetween(50, 150);
        $recentUsers = $users->random(min($recentActivityCount, $users->count()));

        foreach ($recentUsers as $user) {
            $activitiesCount = fake()->numberBetween(5, 25);

            for ($i = 0; $i < $activitiesCount; $i++) {
                $action = fake()->randomElement(array_keys($activityPatterns[$user->role]));
                $createdAt = fake()->dateTimeBetween('-7 days', 'now');

                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => $action,
                    'description' => $actionDescriptions[$action],
                    'created_at' => $createdAt,
                ]);
            }
        }

        // Generate today's activities
        $todayActivityCount = fake()->numberBetween(20, 80);
        $todayUsers = $users->random(min($todayActivityCount, $users->count()));

        foreach ($todayUsers as $user) {
            $todayActivities = fake()->numberBetween(1, 15);

            for ($i = 0; $i < $todayActivities; $i++) {
                $action = fake()->randomElement(array_keys($activityPatterns[$user->role]));
                $createdAt = fake()->dateTimeBetween('today', 'now');

                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => $action,
                    'description' => $actionDescriptions[$action],
                    'created_at' => $createdAt,
                ]);
            }
        }

        $this->command->info('Activity log data created successfully!');
    }

    private function selectActionByWeight(array $pattern): string
    {
        $totalWeight = array_sum($pattern);
        $random = fake()->randomFloat(2, 0, $totalWeight);
        $currentWeight = 0;

        foreach ($pattern as $action => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $action;
            }
        }

        return array_key_first($pattern);
    }
}
