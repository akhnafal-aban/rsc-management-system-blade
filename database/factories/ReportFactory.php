<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $format = fake()->randomElement(['DAILY', 'WEEKLY', 'MONTHLY']);
        $periodStart = fake()->dateTimeBetween('-6 months', '-1 month');

        $periodEnd = match ($format) {
            'DAILY' => $periodStart,
            'WEEKLY' => Carbon::parse($periodStart)->addWeek(),
            'MONTHLY' => Carbon::parse($periodStart)->addMonth(),
            default => Carbon::parse($periodStart)->addMonth(),
        };

        return [
            'format' => $format,
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'generated_by' => User::factory(),
            'created_at' => fake()->dateTimeBetween($periodEnd, 'now'),
        ];
    }

    public function withUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'generated_by' => $userId,
        ]);
    }

    public function daily(): static
    {
        $date = fake()->dateTimeBetween('-3 months', '-1 week');

        return $this->state(fn (array $attributes) => [
            'format' => 'DAILY',
            'period_start' => $date->format('Y-m-d'),
            'period_end' => $date->format('Y-m-d'),
            'created_at' => fake()->dateTimeBetween($date, 'now'),
        ]);
    }

    public function weekly(): static
    {
        $startDate = fake()->dateTimeBetween('-3 months', '-1 week');
        $endDate = Carbon::parse($startDate)->addWeek();

        return $this->state(fn (array $attributes) => [
            'format' => 'WEEKLY',
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
            'created_at' => fake()->dateTimeBetween($endDate, 'now'),
        ]);
    }

    public function monthly(): static
    {
        $startDate = fake()->dateTimeBetween('-6 months', '-1 month');
        $endDate = Carbon::parse($startDate)->addMonth();

        return $this->state(fn (array $attributes) => [
            'format' => 'MONTHLY',
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
            'created_at' => fake()->dateTimeBetween($endDate, 'now'),
        ]);
    }

    public function thisMonth(): static
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        return $this->state(fn (array $attributes) => [
            'format' => 'MONTHLY',
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
            'created_at' => fake()->dateTimeBetween($startDate, 'now'),
        ]);
    }
}
