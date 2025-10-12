<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amounts = [150000, 200000, 300000, 450000, 500000, 750000, 1000000, 1200000];
        $amount = fake()->randomElement($amounts);
        $method = fake()->randomElement(['CASH', 'TRANSFER', 'EWALLET']);
        $createdAt = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'member_id' => Member::factory(),
            'amount' => $amount,
            'method' => $method,
            'notes' => fake()->optional(0.4)->sentence(),
            'created_at' => $createdAt,
        ];
    }

    public function withMember(int $memberId): static
    {
        return $this->state(fn (array $attributes) => [
            'member_id' => $memberId,
        ]);
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => fake()->randomElement([150000, 200000, 300000]),
            'notes' => 'Pembayaran bulanan membership',
        ]);
    }

    public function quarterly(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => fake()->randomElement([450000, 500000, 750000]),
            'notes' => 'Pembayaran 3 bulan membership',
        ]);
    }

    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => fake()->randomElement([1000000, 1200000, 1500000]),
            'notes' => 'Pembayaran tahunan membership',
        ]);
    }

    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'CASH',
            'notes' => 'Pembayaran tunai',
        ]);
    }

    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'TRANSFER',
            'notes' => 'Transfer bank',
        ]);
    }

    public function ewallet(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'EWALLET',
            'notes' => fake()->randomElement(['Gopay', 'OVO', 'DANA', 'ShopeePay']),
        ]);
    }
}
