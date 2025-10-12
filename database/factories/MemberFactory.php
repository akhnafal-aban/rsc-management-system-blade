<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MemberStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'member_code' => 'MEM'.fake()->unique()->numberBetween(100000, 999999),
            'name' => fake()->name(),
            'phone' => fake()->optional(0.8)->numerify('08##########'),
            'email' => fake()->optional(0.6)->safeEmail(),
            'last_check_in' => fake()->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'total_visits' => fake()->numberBetween(0, 50),
            'exp_date' => fake()->dateTimeBetween('now', '+1 year'),
            'status' => fake()->randomElement([MemberStatus::ACTIVE, MemberStatus::INACTIVE]),
        ];
    }
}
