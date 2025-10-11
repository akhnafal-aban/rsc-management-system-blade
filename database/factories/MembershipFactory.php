<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::now()->subMonths(rand(0, 6))->startOfMonth();
        $duration = $this->faker->randomElement([1, 3, 6, 12]);

        return [
            'member_id'       => $this->faker->numberBetween(1, 221),
            'start_date'      => $start->format('Y-m-d'),
            'end_date'        => $start->copy()->addMonths($duration)->format('Y-m-d'),
            'duration_months' => $duration,
            'created_at'      => $start->format('Y-m-d H:i:s'),
        ];
    }
}
