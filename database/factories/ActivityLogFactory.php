<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $actions = [
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

        $action = fake()->randomElement(array_keys($actions));
        $description = $actions[$action];
        $createdAt = fake()->dateTimeBetween('-3 months', 'now');

        return [
            'user_id' => User::factory(),
            'action' => $action,
            'description' => $description,
            'created_at' => $createdAt,
        ];
    }

    public function withUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    public function login(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'LOGIN',
            'description' => 'User berhasil login ke sistem',
        ]);
    }

    public function checkIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'CHECK_IN',
            'description' => 'Melakukan check-in member',
        ]);
    }

    public function createMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'CREATE_MEMBER',
            'description' => 'Membuat member baru',
        ]);
    }

    public function createPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'CREATE_PAYMENT',
            'description' => 'Membuat pembayaran baru',
        ]);
    }

    public function generateReport(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'GENERATE_REPORT',
            'description' => 'Membuat laporan',
        ]);
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('today', 'now'),
        ]);
    }

    public function thisWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('this week', 'now'),
        ]);
    }
}
