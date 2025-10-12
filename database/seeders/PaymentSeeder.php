<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $members = Member::all();

        if ($members->isEmpty()) {
            $this->command->warn('Members not found. Please run MemberSeeder first.');
            return;
        }

        $this->command->info('Creating payment data...');

        $paymentMethods = ['CASH', 'TRANSFER', 'EWALLET'];
        $amounts = [150000, 200000, 250000, 300000];

        foreach ($members as $member) {
            // Generate 1-5 payments per member
            $paymentCount = fake()->numberBetween(1, 5);

            for ($i = 0; $i < $paymentCount; $i++) {
                Payment::create([
                    'member_id' => $member->id,
                    'amount' => fake()->randomElement($amounts),
                    'method' => fake()->randomElement($paymentMethods),
                    'notes' => fake()->optional(0.5)->sentence(),
                    'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
                ]);
            }
        }

        $this->command->info('Payment data created successfully!');
    }
}
