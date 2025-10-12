<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $members = Member::all();
        $users = User::all();

        if ($members->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Members or Users not found. Please run MemberSeeder and UserSeeder first.');

            return;
        }

        $this->command->info('Creating realistic attendance data...');

        // Generate attendance for each member based on their total_visits
        foreach ($members as $member) {
            $totalVisits = $member->total_visits;
            $createdAt = $member->created_at;
            $lastCheckIn = $member->last_check_in;

            if ($totalVisits > 0) {
                // Generate random attendance dates within the member's active period
                $attendanceDates = [];
                $startDate = $createdAt;
                $endDate = $lastCheckIn ?: Carbon::now();

                // Create realistic attendance pattern
                for ($i = 0; $i < $totalVisits; $i++) {
                    $date = fake()->dateTimeBetween($startDate, $endDate);

                    // Avoid duplicate dates
                    while (in_array($date->format('Y-m-d'), $attendanceDates)) {
                        $date = fake()->dateTimeBetween($startDate, $endDate);
                    }

                    $attendanceDates[] = $date->format('Y-m-d');
                }

                // Sort dates chronologically
                sort($attendanceDates);

                // Create attendance records
                foreach ($attendanceDates as $date) {
                    $checkInTime = Carbon::parse($date)->setTime(
                        fake()->numberBetween(6, 22), // 6 AM to 10 PM
                        fake()->numberBetween(0, 59)
                    );

                    $checkOutTime = fake()->optional(0.85)->passthrough(
                        Carbon::parse($checkInTime)->addHours(fake()->numberBetween(1, 8))
                    );

                    Attendance::create([
                        'member_id' => $member->id,
                        'check_in_time' => $checkInTime,
                        'check_out_time' => $checkOutTime,
                        'created_by' => $users->random()->id,
                        'updated_by' => fake()->optional(0.2)->passthrough($users->random()->id),
                        'created_at' => $checkInTime,
                        'updated_at' => $checkOutTime ?? $checkInTime,
                    ]);
                }
            }
        }

        // Generate additional recent attendance for active members
        $activeMembers = Member::where('status', 'ACTIVE')
            ->where('total_visits', '>', 0)
            ->inRandomOrder()
            ->take(20)
            ->get();

        foreach ($activeMembers as $member) {
            // Generate 1-3 additional recent visits
            $recentVisits = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $recentVisits; $i++) {
                $checkInTime = fake()->dateTimeBetween('-2 weeks', 'now');
                $checkOutTime = fake()->optional(0.9)->passthrough(
                    Carbon::parse($checkInTime)->addHours(fake()->numberBetween(1, 6))
                );

                Attendance::create([
                    'member_id' => $member->id,
                    'check_in_time' => $checkInTime,
                    'check_out_time' => $checkOutTime,
                    'created_by' => $users->random()->id,
                    'updated_by' => fake()->optional(0.1)->passthrough($users->random()->id),
                    'created_at' => $checkInTime,
                    'updated_at' => $checkOutTime ?? $checkInTime,
                ]);
            }
        }

        // Generate today's attendance for some members
        $todayMembers = Member::where('status', 'ACTIVE')
            ->inRandomOrder()
            ->take(fake()->numberBetween(5, 15))
            ->get();

        foreach ($todayMembers as $member) {
            $checkInTime = fake()->dateTimeBetween('today 06:00', 'today 20:00');
            $checkOutTime = fake()->optional(0.6)->passthrough(
                Carbon::parse($checkInTime)->addHours(fake()->numberBetween(1, 4))
            );

            Attendance::create([
                'member_id' => $member->id,
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'created_by' => $users->random()->id,
                'updated_by' => fake()->optional(0.1)->passthrough($users->random()->id),
                'created_at' => $checkInTime,
                'updated_at' => $checkOutTime ?? $checkInTime,
            ]);
        }

        $this->command->info('Attendance data created successfully!');
    }
}
