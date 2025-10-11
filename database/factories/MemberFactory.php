<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_code'   => $this->faker->unique()->regexify('12-25[0-9]{3}'),
            'name'          => strtoupper($this->faker->firstName()),
            'phone'         => $this->faker->numerify('08##########'),
            'email'         => null,
            'last_check_in' => null,
            'total_visits'  => 0,
            'exp_date'      => Carbon::create(2011, 11, 1),
            'status'        => 'ACTIVE',
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
