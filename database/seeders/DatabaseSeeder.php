<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'aban',
            'email' => 'aban@aban',
            'password' => 'password'
        ]);

        $this->call([
            // UserSeeder::class,
            MemberSeeder::class,
            MembershipSeeder::class,
            // AttendanceSeeder::class,
            // PaymentSeeder::class,
        ]);
    }
}
