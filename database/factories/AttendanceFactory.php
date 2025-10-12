<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkInTime = fake()->dateTimeBetween('-6 months', 'now');
        $checkOutTime = fake()->optional(0.8)->dateTimeBetween($checkInTime, '+8 hours');
        $createdBy = User::inRandomOrder()->first()?->id ?? 1;

        return [
            'member_id' => Member::factory(),
            'check_in_time' => $checkInTime,
            'check_out_time' => $checkOutTime,
            'created_by' => $createdBy,
            'updated_by' => fake()->optional(0.3)->randomElement([$createdBy]),
            'created_at' => $checkInTime,
            'updated_at' => $checkOutTime ?? $checkInTime,
        ];
    }

    public function withMember(int $memberId): static
    {
        return $this->state(fn (array $attributes) => [
            'member_id' => $memberId,
        ]);
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_time' => fake()->dateTimeBetween('today 06:00', 'today 22:00'),
            'check_out_time' => fake()->optional(0.7)->dateTimeBetween('today 14:00', 'today 23:59'),
        ]);
    }

    public function thisWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_time' => fake()->dateTimeBetween('this week 06:00', 'this week 22:00'),
            'check_out_time' => fake()->optional(0.8)->dateTimeBetween('this week 14:00', 'this week 23:59'),
        ]);
    }

    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_time' => fake()->dateTimeBetween('this month 06:00', 'this month 22:00'),
            'check_out_time' => fake()->optional(0.8)->dateTimeBetween('this month 14:00', 'this month 23:59'),
        ]);
    }
}
